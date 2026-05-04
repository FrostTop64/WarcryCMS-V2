<?php
if (!defined('init_engine'))
{
    header('HTTP/1.0 404 not found');
    exit;
}

class VoteSitesData
{
    public $data = array();

    private $defaults = array(
        1 => array('name' => 'XtremeTop100', 'url' => 'http://www.xtremetop100.com/in.php?site=1132331157', 'img' => 'http://www.xtremeTop100.com/votenew.jpg', 'reward_silver' => 2, 'cooldown' => '', 'position' => 1, 'active' => 1),
        2 => array('name' => 'TOPG', 'url' => 'http://topg.org/World-Of-Warcraft/in-354373', 'img' => 'http://topg.org/topg.gif', 'reward_silver' => 2, 'cooldown' => '', 'position' => 2, 'active' => 1),
        3 => array('name' => 'Top100Arena', 'url' => 'http://www.top100arena.com/in.asp?id=78675', 'img' => 'http://www.top100arena.com/hit.asp?id=78675&c=WoW&t=2', 'reward_silver' => 2, 'cooldown' => '', 'position' => 3, 'active' => 1),
        4 => array('name' => 'OpenWoW', 'url' => 'http://www.openwow.com/?vote=2302', 'img' => 'http://cdn.openwow.com/toplist/vote_small.jpg', 'reward_silver' => 2, 'cooldown' => '', 'position' => 4, 'active' => 1),
        5 => array('name' => 'GameSites200', 'url' => 'http://www.gamesites200.com/wowprivate/in.php?id=10780', 'img' => 'http://www.gamesites200.com/wowprivate/vote.gif', 'reward_silver' => 2, 'cooldown' => '', 'position' => 5, 'active' => 1),
        6 => array('name' => 'WoWStatus', 'url' => 'http://www.wowstatus.net/in.php?server=776723', 'img' => 'http://www.wowstatus.net/includes/images/vote.gif', 'reward_silver' => 2, 'cooldown' => '', 'position' => 6, 'active' => 1),
    );

    public function __construct()
    {
        $this->load();
        return true;
    }

    private function ensureTable()
    {
        global $DB;
        if (!isset($DB) || !$DB) { return false; }
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
        return true;
    }

    private function load()
    {
        global $DB;
        if (!$this->ensureTable()) { $this->data = $this->defaults; return; }

        $count = 0;
        try { $count = (int)$DB->query("SELECT COUNT(*) FROM `vote_sites`")->fetchColumn(); } catch (Exception $e) { $count = 0; }
        if ($count === 0)
        {
            $ins = $DB->prepare("INSERT INTO `vote_sites` (`id`,`name`,`url`,`img`,`reward_silver`,`cooldown`,`position`,`active`) VALUES (:id,:name,:url,:img,:reward,:cooldown,:position,:active)");
            foreach ($this->defaults as $id => $site)
            {
                $ins->execute(array(':id'=>$id, ':name'=>$site['name'], ':url'=>$site['url'], ':img'=>$site['img'], ':reward'=>$site['reward_silver'], ':cooldown'=>$site['cooldown'], ':position'=>$site['position'], ':active'=>$site['active']));
            }
        }

        $this->data = array();
        $stmt = $DB->query("SELECT `id`,`name`,`url`,`img`,`reward_silver`,`cooldown`,`position`,`active` FROM `vote_sites` WHERE `active` = 1 ORDER BY `position` ASC, `id` ASC");
        foreach ($stmt->fetchAll() as $row)
        {
            $id = (int)$row['id'];
            $this->data[$id] = array(
                'name' => $row['name'],
                'url' => $row['url'],
                'img' => $row['img'],
                'reward_silver' => (int)$row['reward_silver'],
                'cooldown' => $row['cooldown'],
                'position' => (int)$row['position'],
                'active' => (int)$row['active'],
            );
        }
    }

    public function get($key)
    {
        if (!isset($this->data[$key])) { return false; }
        return $this->data[$key];
    }

    public function __destruct()
    {
        unset($this->data);
        return true;
    }
}
