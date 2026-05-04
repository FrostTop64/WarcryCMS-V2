<?php
if (!defined('init_engine'))
{
    header('HTTP/1.0 404 not found');
    exit;
}

class AvatarGallery
{
    private $manifestFile = null;

    public $data = array(
        RANK_ROOKIE => array(
            0 => 'rookie_avatar_1.jpg',
            1 => 'rookie_avatar_2.jpg',
            2 => 'rookie_avatar_3.jpg',
            3 => 'rookie_avatar_4.jpg',
            4 => 'rookie_avatar_5.jpg',
            5 => 'rookie_avatar_6.jpg',
            6 => 'rookie_avatar_7.jpg',
            7 => 'rookie_avatar_8.jpg',
            8 => 'rookie_avatar_9.jpg',
            9 => 'rookie_avatar_10.jpg',
        ),
        RANK_PARTICIPANT => array(),
        RANK_MEMBER => array(),
        RANK_VETERAN => array(),
        RANK_SENIOR_MEMBER => array(),
        RANK_ADDICT => array(),
        RANK_STAFF_MEMBER => array(
            100 => 'staff_1.jpg',
            102 => 'staff_2.jpg',
            103 => 'staff_3.jpg',
            104 => 'staff_4.jpg',
            105 => 'staff_5.jpg',
            106 => 'staff_6.jpg',
            107 => 'staff_7.jpg',
            108 => 'staff_8.jpg',
            109 => 'staff_9.jpg',
            110 => 'staff_10.jpg',
            111 => 'staff_11.jpg',
            112 => 'staff_12.jpg',
            113 => 'staff_13.jpg',
            114 => 'staff_14.jpg',
        ),
    );

    public function __construct()
    {
        global $config;
        $this->manifestFile = $config['RootPath'] . '/resources/avatars/avatars.json';
        $this->loadCustomAvatars();
        return true;
    }

    private function loadCustomAvatars()
    {
        if (!$this->manifestFile || !file_exists($this->manifestFile))
            return;

        $json = file_get_contents($this->manifestFile);
        $custom = json_decode($json, true);

        if (!is_array($custom))
            return;

        foreach ($custom as $item)
        {
            if (!isset($item['id']) || !isset($item['rank']) || !isset($item['file']))
                continue;

            $id = (int)$item['id'];
            $rank = (int)$item['rank'];
            $file = basename($item['file']);

            if ($id <= 0 || $file == '')
                continue;

            if (!isset($this->data[$rank]) || !is_array($this->data[$rank]))
                $this->data[$rank] = array();

            $this->data[$rank][$id] = $file;
        }
    }

    public function getGalleries()
    {
        return $this->data;
    }

    public function get($id)
    {
        foreach ($this->data as $rank => $avatars)
        {
            if (isset($avatars[(int)$id]))
            {
                return new Avatar((int)$id, $avatars[(int)$id], $rank, AVATAR_TYPE_GALLERY);
            }
        }

        return false;
    }

    public function __destruct()
    {
        unset($this->data);
    }
}
