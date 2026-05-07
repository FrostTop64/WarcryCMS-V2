<?php
if (!defined('init_engine') && !defined('init_pages') && !defined('init_executes')) { header('HTTP/1.0 404 not found'); exit; }

function warcry_lottery_h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function warcry_lottery_ensure_tables()
{
    global $DB;
    $DB->query("CREATE TABLE IF NOT EXISTS `lottery_prizes` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `item_entry` int(10) unsigned NOT NULL,
        `item_name` varchar(120) NOT NULL,
        `icon` varchar(80) NOT NULL DEFAULT 'inv_misc_questionmark',
        `quantity` int(10) unsigned NOT NULL DEFAULT 1,
        `weight` int(10) unsigned NOT NULL DEFAULT 10,
        `enabled` tinyint(1) unsigned NOT NULL DEFAULT 1,
        `created_at` int(10) unsigned NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        KEY `enabled_weight` (`enabled`, `weight`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

    $DB->query("CREATE TABLE IF NOT EXISTS `lottery_tickets` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `account_id` int(10) unsigned NOT NULL,
        `realm_id` int(10) unsigned NOT NULL DEFAULT 1,
        `prize_id` int(10) unsigned NOT NULL DEFAULT 0,
        `item_entry` int(10) unsigned NOT NULL,
        `item_name` varchar(120) NOT NULL,
        `icon` varchar(80) NOT NULL DEFAULT 'inv_misc_questionmark',
        `quantity` int(10) unsigned NOT NULL DEFAULT 1,
        `status` enum('active','revealed','cancelled') NOT NULL DEFAULT 'active',
        `created_at` int(10) unsigned NOT NULL DEFAULT 0,
        `revealed_at` int(10) unsigned DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `account_status` (`account_id`, `status`),
        KEY `realm_id` (`realm_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

    $DB->query("CREATE TABLE IF NOT EXISTS `lottery_inventory` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `account_id` int(10) unsigned NOT NULL,
        `realm_id` int(10) unsigned NOT NULL DEFAULT 1,
        `prize_id` int(10) unsigned NOT NULL DEFAULT 0,
        `ticket_id` int(10) unsigned NOT NULL DEFAULT 0,
        `item_entry` int(10) unsigned NOT NULL,
        `item_name` varchar(120) NOT NULL,
        `icon` varchar(80) NOT NULL DEFAULT 'inv_misc_questionmark',
        `quantity` int(10) unsigned NOT NULL DEFAULT 1,
        `status` enum('pending','claimed') NOT NULL DEFAULT 'pending',
        `character_name` varchar(32) DEFAULT NULL,
        `created_at` int(10) unsigned NOT NULL DEFAULT 0,
        `claimed_at` int(10) unsigned DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `account_status` (`account_id`, `status`),
        KEY `ticket_id` (`ticket_id`),
        KEY `realm_id` (`realm_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

    // Safe migration for older installs of this module.
    // PDO on this CMS may only emit warnings instead of throwing exceptions,
    // so we must check the column first to avoid "Duplicate column name" output.
    $colCheck = $DB->query("SHOW COLUMNS FROM `lottery_inventory` LIKE 'ticket_id'");
    $hasTicketId = ($colCheck && $colCheck->fetch()) ? true : false;
    if (!$hasTicketId) {
        $DB->query("ALTER TABLE `lottery_inventory` ADD `ticket_id` int(10) unsigned NOT NULL DEFAULT 0 AFTER `prize_id`");
    }

    $c = $DB->query("SELECT COUNT(*) AS total FROM `lottery_prizes`")->fetch();
    if ((int)$c['total'] === 0) {
        $defaults = array(
            array(50730,'Glorenzelg, High-Blade of the Silver Hand','inv_sword_156',1,3),
            array(50737,'Havoc\'s Call, Blade of Lordaeron Kings','inv_axe_113',1,3),
            array(50732,'Bloodsurge, Kel\'Thuzad\'s Blade of Agony','inv_sword_155',1,3),
            array(50734,'Royal Scepter of Terenas II','inv_mace_115',1,3),
            array(50738,'Mithrios, Bronzebeard\'s Legacy','inv_shield_74',1,3),
            array(50429,'Archus, Greatstaff of Antonidas','inv_staff_107',1,3),
            array(49908,'Primordial Saronite','inv_misc_enggizmos_14',1,12),
            array(36913,'Saronite Bar','inv_ingot_13',20,18),
            array(36910,'Titanium Ore','inv_ore_platinum_01',20,14),
            array(36925,'Majestic Zircon','inv_jewelcrafting_gem_32',3,10),
            array(36931,'Ametrine','inv_jewelcrafting_gem_35',3,10),
            array(35624,'Eternal Earth','spell_nature_strengthofearthtotem02',5,14),
            array(35625,'Eternal Life','spell_nature_protectionformnature',5,14),
            array(41595,'Spellweave','inv_fabric_spellweave',4,10),
            array(45087,'Runed Orb','inv_misc_runedorb_01',2,8)
        );
        $s = $DB->prepare("INSERT INTO `lottery_prizes` (`item_entry`,`item_name`,`icon`,`quantity`,`weight`,`enabled`,`created_at`) VALUES (:entry,:name,:icon,:qty,:weight,1,:time)");
        $now = time();
        foreach ($defaults as $d) { $s->execute(array(':entry'=>$d[0], ':name'=>$d[1], ':icon'=>$d[2], ':qty'=>$d[3], ':weight'=>$d[4], ':time'=>$now)); }
    }
}

function warcry_lottery_cost() { require_once $GLOBALS['config']['RootPath'].'/engine/helpers/account_modules.php'; return max(1, (int)warcry_account_module_get('lottery_ticket_price', 3)); }
function warcry_lottery_title() { require_once $GLOBALS['config']['RootPath'].'/engine/helpers/account_modules.php'; return warcry_account_module_get('lottery_title', 'Lottery Scratch Ticket'); }
function warcry_lottery_enabled() { require_once $GLOBALS['config']['RootPath'].'/engine/helpers/account_modules.php'; return warcry_account_module_enabled('lottery'); }
function warcry_lottery_icon_url($icon, $size='large') { $icon = strtolower(preg_replace('/[^a-z0-9_\-]/i', '', (string)$icon)); if ($icon === '') { $icon = 'inv_misc_questionmark'; } return 'https://wow.zamimg.com/images/wow/icons/'.$size.'/'.$icon.'.jpg'; }

function warcry_lottery_prizes($enabledOnly = true)
{
    global $DB; warcry_lottery_ensure_tables();
    $sql = "SELECT * FROM `lottery_prizes`" . ($enabledOnly ? " WHERE `enabled`=1 AND `weight`>0" : "") . " ORDER BY `enabled` DESC, `item_name` ASC";
    return $DB->query($sql)->fetchAll();
}

function warcry_lottery_pick_prize()
{
    $prizes = warcry_lottery_prizes(true); $total = 0;
    foreach ($prizes as $p) { $total += max(0, (int)$p['weight']); }
    if ($total <= 0) { return false; }
    $roll = mt_rand(1, $total); $sum = 0;
    foreach ($prizes as $p) { $sum += max(0, (int)$p['weight']); if ($roll <= $sum) { return $p; } }
    return end($prizes);
}

function warcry_lottery_active_ticket($accountId)
{
    global $DB; warcry_lottery_ensure_tables();
    $s = $DB->prepare("SELECT * FROM `lottery_tickets` WHERE `account_id`=:acc AND `status`='active' ORDER BY `id` DESC LIMIT 1");
    $s->execute(array(':acc'=>(int)$accountId));
    $row = $s->fetch();
    return $row ? $row : false;
}

function warcry_lottery_inventory($accountId, $status = null)
{
    global $DB; warcry_lottery_ensure_tables();
    if ($status === 'pending' || $status === 'claimed') {
        $s = $DB->prepare("SELECT * FROM `lottery_inventory` WHERE `account_id`=:acc AND `status`=:status ORDER BY `id` DESC");
        $s->execute(array(':acc'=>(int)$accountId, ':status'=>$status));
    } else {
        $s = $DB->prepare("SELECT * FROM `lottery_inventory` WHERE `account_id`=:acc ORDER BY `id` DESC LIMIT 50");
        $s->execute(array(':acc'=>(int)$accountId));
    }
    return $s->fetchAll();
}
?>
