<?php
if (!defined('init_pages'))
{
    header('HTTP/1.0 404 not found');
    exit;
}

$CORE->loggedInOrReturn();

function wc_vote_e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function wc_vote_image_src($value)
{
    $value = trim((string)$value);

    if ($value === '') {
        return '';
    }

    // Allow normal remote images and safe local CMS paths only.
    if (preg_match('#^https?://#i', $value)) {
        return $value;
    }

    if (preg_match('#^(?:template|uploads|images|img|assets)/[A-Za-z0-9_./%+\-]+$#', $value)) {
        return $value;
    }

    return '';
}

function wc_vote_seconds_from_cooldown($cooldownTime)
{
    $cooldownTime = strtolower(trim((string)$cooldownTime));

    if ($cooldownTime === '') { return 12 * 60 * 60; }
    if (preg_match('/^\d+$/', $cooldownTime)) { return max(1, (int)$cooldownTime) * 60 * 60; }
    if (preg_match('/^(\d+)\s*h(?:our|ours|rs?)?$/i', $cooldownTime, $m)) { return max(1, (int)$m[1]) * 60 * 60; }
    if (preg_match('/^(\d+)\s*m(?:in|inute|inutes)?$/i', $cooldownTime, $m)) { return max(1, (int)$m[1]) * 60; }
    if (preg_match('/^(\d+)\s*d(?:ay|ays)?$/i', $cooldownTime, $m)) { return max(1, (int)$m[1]) * 24 * 60 * 60; }

    $future = strtotime('+' . $cooldownTime);
    if ($future === false || $future <= time()) { return 12 * 60 * 60; }
    return max(60, $future - time());
}

function wc_vote_data_has_ip_column($DB)
{
    static $has = null;
    if ($has !== null) { return $has; }
    $has = false;
    try
    {
        $check = $DB->query("SHOW COLUMNS FROM `vote_data` LIKE 'ip_address'");
        $has = ($check && $check->rowCount() > 0);
    }
    catch (Exception $e) { $has = false; }
    return $has;
}

function wc_vote_get_ip()
{
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] !== '')
    {
        $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($parts[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) { return $ip; }
    }
    if (isset($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) { return $_SERVER['HTTP_CLIENT_IP']; }
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
}

function wc_vote_latest_vote_timestamp($DB, $siteid, $accountId, $ip, $useIp)
{
    $latest = 0;
    try
    {
        $st = $DB->prepare("SELECT `timestamp` FROM `vote_data` WHERE `account` = :acc AND `siteid` = :site ORDER BY `id` DESC LIMIT 1");
        $st->execute(array(':acc'=>(int)$accountId, ':site'=>(int)$siteid));
        $row = $st->fetch();
        if ($row && !empty($row['timestamp'])) { $latest = max($latest, (int)strtotime($row['timestamp'])); }
    }
    catch (Exception $e) {}

    if ($useIp && wc_vote_data_has_ip_column($DB))
    {
        try
        {
            $st = $DB->prepare("SELECT `timestamp` FROM `vote_data` WHERE `siteid` = :site AND `ip_address` = :ip ORDER BY `id` DESC LIMIT 1");
            $st->execute(array(':site'=>(int)$siteid, ':ip'=>$ip));
            $row = $st->fetch();
            if ($row && !empty($row['timestamp'])) { $latest = max($latest, (int)strtotime($row['timestamp'])); }
        }
        catch (Exception $e) {}
    }

    return $latest;
}

//Set the title
$TPL->SetTitle('Vote for us');
//CSS
$TPL->AddCSS('template/style/page-vote.css');
//Print the header
$TPL->LoadHeader();

?>
<div class="content_holder">

<div class="sub-page-title">
    <div id="title"><h1>Account Panel<p></p><span></span></h1></div>
  
    <div class="quick-menu">
        <a class="arrow" href="#"></a>
        <ul class="dropdown-qmenu">
            <li><a href="<?php echo $config['BaseURL']; ?>/index.php?page=store">Store</a></li>
            <li><a href="<?php echo $config['BaseURL']; ?>/index.php?page=teleporter">Teleporter</a></li>
            <li><a href="<?php echo $config['BaseURL']; ?>/index.php?page=buycoins">Buy Coins</a></li>
            <li><a href="<?php echo $config['BaseURL']; ?>/index.php?page=pstore">Premium Store</a></li>
            <li><a href="<?php echo $config['BaseURL']; ?>/index.php?page=unstuck">Unstuck</a></li>
            <li><a href="<?php echo $config['BaseURL']; ?>/index.php?page=settings">Settings & Options</a></li>
            <!--<li id="messages-ddm">
                <a href="<?php echo $config['BaseURL']; ?>/index.php?page=pm">
                    <b>55</b> <i>Private Messages</i>
                </a>
            </li>-->
        </ul>
    </div>
</div>
 
    <div class="container_2 account" align="center">
     <div class="cont-image">
    
    <?php
    if ($error = $ERRORS->DoPrint('vote'))
    {
        echo $error, '<br><br>';
    }           
    if ($error = $ERRORS->successPrint('vote'))
    {
        echo $error, '<br><br>';
    }           
    unset($error);
    ?>
   
      <div class="container_3 account_sub_header">
         <div class="grad">
            <div class="page-title">Vote</div>
            <a href="<?php echo $config['BaseURL'], '/index.php?page=account'; ?>">Back to account</a>
         </div>
      </div>
      
      <!-- VOTE -->
        <div class="vote-page">
            
            <div class="page-desc-holder">
                With every vote you will recieve <font color="#808080"><b>2 silver</b></font> coins. <br/>
                You can spend your coins for amazing stuff on our website.
            </div>
            
            <div class="container_3 account-wide" align="center">
             
                    <ul class="vote-sites-cont">
                        
                        <?php
                            
                            $VoteSites = new VoteSitesData();
                            
                            foreach ($VoteSites->data as $id => $data)
                            {
                                $id = (int)$id;
                                $siteName = isset($data['name']) ? $data['name'] : 'Vote Site';
                                $siteUrl = isset($data['url']) ? trim((string)$data['url']) : '#';
                                $siteImg = wc_vote_image_src(isset($data['img']) ? $data['img'] : '');
                                $imageMarkup = $siteImg !== ''
                                    ? '<span class="vote-site-image"><img src="'.wc_vote_e($siteImg).'" alt="'.wc_vote_e($siteName).'" loading="lazy" onerror="this.style.display=\'none\';this.parentNode.className+=\' vote-site-image-empty\';"></span>'
                                    : '<span class="vote-site-image vote-site-image-empty"></span>';

                                $cooldownSeconds = wc_vote_seconds_from_cooldown(isset($data['cooldown']) && trim((string)$data['cooldown']) !== '' ? $data['cooldown'] : (isset($config['VOTE']['Cooldown']) ? $config['VOTE']['Cooldown'] : '12 hours'));
                                $accountCooldown = (int)$CURUSER->getCooldown('votingsite'.$id);
                                $latestVote = wc_vote_latest_vote_timestamp($DB, $id, (int)$CURUSER->get('id'), wc_vote_get_ip(), isset($config['VOTE']['IP_CHECK']) ? (bool)$config['VOTE']['IP_CHECK'] : true);
                                $cooldown = max($accountCooldown, ($latestVote > 0 ? $latestVote + $cooldownSeconds : 0));
                                
                                //if the site is availible for voting
                                if (time() > $cooldown)
                                {
                                    echo '
                                    <li>
                                      <a href="', $config['BaseURL'], '/execute.php?take=vote&amp;site=', $id, '" onclick="window.open(', wc_vote_e(json_encode($siteUrl)), ', \'_newtab\'); return true;">
                                        ', $imageMarkup, '
                                        <p>You can vote now!</p>
                                      </a>
                                    </li>';
                                }
                                else
                                {
                                    //convert the cooldown to minutes and stuff
                                    $cooldownArr = $CORE->convertCooldown($cooldown);
                                    
                                    echo '
                                    <li class="not-active">
                                      <a href="', wc_vote_e($siteUrl), '" rel="noopener">
                                        ', $imageMarkup, '
                                        <p>';
                                        
                                        if ($cooldownArr['days'] > 0)
                                        {
                                            echo (int)$cooldownArr['days'], ' day', ((int)$cooldownArr['days'] > 1 ? 's' : ''), ' ', (int)$cooldownArr['hours'], ' hours until vote!';
                                        }
                                        else if ($cooldownArr['hours'] > 0)
                                        {
                                            echo (int)$cooldownArr['hours'], ' hours until vote!';
                                        }
                                        else if ($cooldownArr['minutes'] > 0)
                                        {
                                            echo (int)$cooldownArr['minutes'], ' minutes until vote!';
                                        }
                                        else if ($cooldownArr['seconds'] > 0)
                                        {
                                            echo (int)$cooldownArr['seconds'], ' seconds until vote!';
                                        }
                                        
                                        echo ' 
                                        </p>
                                      </a>
                                    </li>';
                                    
                                    unset($cooldownArr);
                                }
                                unset($cooldown, $siteName, $siteUrl, $siteImg, $imageMarkup);
                            }
                            
                            unset($VoteSites, $data, $id);
                        ?>
                                                    
                    </ul>
             
            </div>
            
        </div>
      <!-- VOTE.End -->
    
     </div>
    </div>
 
</div>

</div>

<?php

$TPL->LoadFooter();

?>
