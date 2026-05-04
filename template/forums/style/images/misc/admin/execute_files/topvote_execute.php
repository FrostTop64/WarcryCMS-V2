<?php
if (!defined('init_executes'))
{
    header('HTTP/1.0 404 not found');
    exit;
}

$CORE->loggedInOrReturn();
$action = isset($_GET['action']) ? $_GET['action'] : '';

$DB->query("CREATE TABLE IF NOT EXISTS `vote_sites` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(120) NOT NULL,
    `url` text NOT NULL,
    `img` text NOT NULL,
    `reward_silver` int(10) NOT NULL DEFAULT 2,
    `cooldown` varchar(32) NOT NULL DEFAULT '',
    `position` int(10) NOT NULL DEFAULT 0,
    `active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

function topvote_redirect($suffix)
{
    global $config;
    header('Location: '.$config['BaseURL'].'/admin/index.php?page=topvote'.$suffix);
    exit;
}

if ($action === 'save')
{
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $url = isset($_POST['url']) ? trim($_POST['url']) : '';
    $img = isset($_POST['img']) ? trim($_POST['img']) : '';
    $reward = isset($_POST['reward_silver']) ? (int)$_POST['reward_silver'] : 2;
    $cooldown = isset($_POST['cooldown']) ? trim($_POST['cooldown']) : '';
    $position = isset($_POST['position']) ? (int)$_POST['position'] : 0;
    $active = isset($_POST['active']) ? (int)$_POST['active'] : 1;

    if ($name === '' || $url === '' || $img === '') { topvote_redirect('&error=1'); }
    if (!preg_match('#^https?://#i', $url)) { topvote_redirect('&error=1'); }
    if (!preg_match('#^https?://#i', $img)) { topvote_redirect('&error=1'); }
    if ($reward < 0) { $reward = 0; }
    if ($position < 0) { $position = 0; }
    $active = $active === 1 ? 1 : 0;

    if ($id > 0)
    {
        $st = $DB->prepare("UPDATE `vote_sites` SET `name`=:name, `url`=:url, `img`=:img, `reward_silver`=:reward, `cooldown`=:cooldown, `position`=:position, `active`=:active, `updated_at`=NOW() WHERE `id`=:id LIMIT 1");
        $st->execute(array(':name'=>$name, ':url'=>$url, ':img'=>$img, ':reward'=>$reward, ':cooldown'=>$cooldown, ':position'=>$position, ':active'=>$active, ':id'=>$id));
    }
    else
    {
        $st = $DB->prepare("INSERT INTO `vote_sites` (`name`,`url`,`img`,`reward_silver`,`cooldown`,`position`,`active`) VALUES (:name,:url,:img,:reward,:cooldown,:position,:active)");
        $st->execute(array(':name'=>$name, ':url'=>$url, ':img'=>$img, ':reward'=>$reward, ':cooldown'=>$cooldown, ':position'=>$position, ':active'=>$active));
    }
    topvote_redirect('&saved=1');
}
else if ($action === 'delete')
{
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) { topvote_redirect('&error=1'); }
    $st = $DB->prepare("DELETE FROM `vote_sites` WHERE `id` = :id LIMIT 1");
    $st->execute(array(':id'=>$id));
    topvote_redirect('&deleted=1');
}

topvote_redirect('&error=1');
