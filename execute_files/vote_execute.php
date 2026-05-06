<?PHP
if (!defined('init_executes'))
{
    header('HTTP/1.0 404 not found');
    exit;
}

$CORE->loggedInOrReturn();
$CORE->load_CoreModule('coin.activity');

function wc_vote_get_ip()
{
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] !== '')
    {
        $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($parts[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) { return $ip; }
    }
    if (isset($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
    {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
}

function wc_vote_ensure_ip_column($DB)
{
    static $done = false;
    if ($done) { return; }
    $done = true;

    try
    {
        $check = $DB->query("SHOW COLUMNS FROM `vote_data` LIKE 'ip_address'");
        if ($check && $check->rowCount() == 0)
        {
            $DB->query("ALTER TABLE `vote_data` ADD `ip_address` varchar(45) NOT NULL DEFAULT '' AFTER `siteid`");
            $DB->query("ALTER TABLE `vote_data` ADD INDEX `idx_vote_site_ip_time` (`siteid`, `ip_address`, `timestamp`)");
            $DB->query("ALTER TABLE `vote_data` ADD INDEX `idx_vote_account_site_time` (`account`, `siteid`, `timestamp`)");
        }
    }
    catch (Exception $e)
    {
        // Keep the vote page usable if the DB user cannot alter tables; account cooldown still protects rewards.
    }
}

function wc_vote_seconds_from_cooldown($cooldownTime)
{
    $cooldownTime = strtolower(trim((string)$cooldownTime));

    // IMPORTANT: the admin panel may save only "24". In Warcry this means 24 hours, not 24 seconds.
    if ($cooldownTime === '') { return 12 * 60 * 60; }
    if (preg_match('/^\d+$/', $cooldownTime)) { return max(1, (int)$cooldownTime) * 60 * 60; }
    if (preg_match('/^(\d+)\s*h(?:our|ours|rs?)?$/i', $cooldownTime, $m)) { return max(1, (int)$m[1]) * 60 * 60; }
    if (preg_match('/^(\d+)\s*m(?:in|inute|inutes)?$/i', $cooldownTime, $m)) { return max(1, (int)$m[1]) * 60; }
    if (preg_match('/^(\d+)\s*d(?:ay|ays)?$/i', $cooldownTime, $m)) { return max(1, (int)$m[1]) * 24 * 60 * 60; }

    $future = strtotime('+' . $cooldownTime);
    if ($future === false || $future <= time()) { return 12 * 60 * 60; }
    return max(60, $future - time());
}

$siteid = (isset($_GET['site']) ? (int)$_GET['site'] : 0);
$accountId = (int)$CURUSER->get('id');
$userIp = wc_vote_get_ip();

$cooldownTime = isset($config['VOTE']['Cooldown']) ? $config['VOTE']['Cooldown'] : '12 hours';
$pointsPerVote = isset($config['VOTE']['PPV']) ? (int)$config['VOTE']['PPV'] : 2;
$ipCheck = isset($config['VOTE']['IP_CHECK']) ? (bool)$config['VOTE']['IP_CHECK'] : true;

$VoteData = new VoteSitesData();
$ERRORS->NewInstance('vote');

if ($siteid <= 0)
{
    $ERRORS->Add('Please select a valid voting website.');
}

$voteSitesData = false;
if ($siteid > 0)
{
    $voteSitesData = $VoteData->get($siteid);
    if (!$voteSitesData)
    {
        $ERRORS->Add('Please select a valid voting website.');
    }
    else
    {
        if (isset($voteSitesData['reward_silver'])) { $pointsPerVote = (int)$voteSitesData['reward_silver']; }
        if (isset($voteSitesData['cooldown']) && trim($voteSitesData['cooldown']) != '') { $cooldownTime = trim($voteSitesData['cooldown']); }
    }
}
unset($VoteData);

$cooldownSeconds = wc_vote_seconds_from_cooldown($cooldownTime);
$cooldownUntil = time() + $cooldownSeconds;
$ERRORS->onSuccess('Congratulation, you have recieved '.$pointsPerVote.' Silver coins.', '/index.php?page=vote');

// Account cooldown from account_data. This is the main protection per account + per site.
$accountCooldown = (int)$CURUSER->getCooldown('votingsite'.$siteid);
if ($accountCooldown > time())
{
    $ERRORS->Add('The voting website is on cooldown.');
}

wc_vote_ensure_ip_column($DB);

// Reliable DB checks. These protect even if the in-memory account object has not refreshed yet.
if ($siteid > 0 && $accountId > 0)
{
    try
    {
        $since = date('Y-m-d H:i:s', time() - $cooldownSeconds);

        $st = $DB->prepare("SELECT `timestamp` FROM `vote_data` WHERE `account` = :acc AND `siteid` = :site AND `timestamp` >= :since ORDER BY `id` DESC LIMIT 1");
        $st->bindParam(':acc', $accountId, PDO::PARAM_INT);
        $st->bindParam(':site', $siteid, PDO::PARAM_INT);
        $st->bindParam(':since', $since, PDO::PARAM_STR);
        $st->execute();
        if ($st->rowCount() > 0)
        {
            $ERRORS->Add('The voting website is on cooldown for your account.');
        }
        unset($st);

        if ($ipCheck === true)
        {
            $st = $DB->prepare("SELECT `timestamp` FROM `vote_data` WHERE `siteid` = :site AND `ip_address` = :ip AND `timestamp` >= :since ORDER BY `id` DESC LIMIT 1");
            $st->bindParam(':site', $siteid, PDO::PARAM_INT);
            $st->bindParam(':ip', $userIp, PDO::PARAM_STR);
            $st->bindParam(':since', $since, PDO::PARAM_STR);
            $st->execute();
            if ($st->rowCount() > 0)
            {
                $CURUSER->setCooldown('votingsite'.$siteid, $cooldownUntil);
                $ERRORS->Add('The website failed to update your Silver coins. Reason: Someone has already voted from this IP.');
            }
            unset($st);
        }
    }
    catch (Exception $e)
    {
        // Do not expose database details publicly.
    }
}

$ERRORS->Check('/index.php?page=vote');

// Add vote log first so account/IP locks exist immediately before the external vote page can be spam-clicked.
try
{
    $voteTime = $CORE->getTime();
    $insert = $DB->prepare("INSERT INTO `vote_data` (`account`, `siteid`, `ip_address`, `timestamp`) VALUES (:acc, :site, :ip, :time);");
    $insert->bindParam(':acc', $accountId, PDO::PARAM_INT);
    $insert->bindParam(':site', $siteid, PDO::PARAM_INT);
    $insert->bindParam(':ip', $userIp, PDO::PARAM_STR);
    $insert->bindParam(':time', $voteTime, PDO::PARAM_STR);
    $insert->execute();
    unset($insert);
}
catch (Exception $e)
{
    // Fallback for older DBs if the column could not be created.
    $voteTime = $CORE->getTime();
    $insert = $DB->prepare("INSERT INTO `vote_data` (`account`, `siteid`, `timestamp`) VALUES (:acc, :site, :time);");
    $insert->bindParam(':acc', $accountId, PDO::PARAM_INT);
    $insert->bindParam(':site', $siteid, PDO::PARAM_INT);
    $insert->bindParam(':time', $voteTime, PDO::PARAM_STR);
    $insert->execute();
    unset($insert);
}

// Set account cooldown immediately and independently from the reward update.
$CURUSER->setCooldown('votingsite'.$siteid, $cooldownUntil);

$year = (int)date('Y');
$month = (int)date('n');

$insert = $DB->prepare("INSERT IGNORE INTO `votecounter` (`account`, `year`, `month`) VALUES (:acc, :year, :month);");
$insert->bindParam(':acc', $accountId, PDO::PARAM_INT);
$insert->bindParam(':year', $year, PDO::PARAM_INT);
$insert->bindParam(':month', $month, PDO::PARAM_INT);
$insert->execute();
unset($insert);

$update = $DB->prepare("UPDATE `votecounter` SET `counter` = `counter` + 1 WHERE `account` = :acc AND `year` = :year AND `month` = :month LIMIT 1;");
$update->bindParam(':acc', $accountId, PDO::PARAM_INT);
$update->bindParam(':year', $year, PDO::PARAM_INT);
$update->bindParam(':month', $month, PDO::PARAM_INT);
$update->execute();
unset($update);

$CURUSER->setLastVoteTime($CORE->getTime());

$update = $DB->prepare("UPDATE `account_data` SET `silver` = silver + :points WHERE `id` = :acc LIMIT 1;");
$update->bindParam(':acc', $accountId, PDO::PARAM_INT);
$update->bindParam(':points', $pointsPerVote, PDO::PARAM_INT);
$update->execute();

if ($update->rowCount() > 0)
{
    $ca = new CoinActivity();
    $ca->set_SourceType(CA_SOURCE_TYPE_REWARD);
    $ca->set_SourceString($voteSitesData['name'] . ' Vote');
    $ca->set_CoinsType(CA_COIN_TYPE_SILVER);
    $ca->set_ExchangeType(CA_EXCHANGE_TYPE_PLUS);
    $ca->set_Amount($pointsPerVote);
    $ca->execute();
    unset($ca);

    $ERRORS->triggerSuccess();
}
else
{
    $ERRORS->Add('The website failed to update your Silver coins.');
}
unset($update);

$ERRORS->Check('/index.php?page=vote');
exit;
