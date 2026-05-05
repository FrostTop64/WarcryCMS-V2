<?php
if (!defined('init_engine'))
{
    header('HTTP/1.0 404 not found');
    exit;
}

class LevelsData
{
    private $data = array();

    public function __construct()
    {
        global $config;

        if (!function_exists('warcry_level_packages') && isset($config['RootPath']))
        {
            $helper = $config['RootPath'] . '/engine/helpers/account_modules.php';
            if (file_exists($helper))
            {
                require_once $helper;
            }
        }

        if (function_exists('warcry_level_packages'))
        {
            $this->data = warcry_level_packages();
        }
        else
        {
            $this->data = array(
                1 => array('level' => 60, 'money' => 20000000, 'rewardGold' => 2000, 'bags' => 4, 'bagsId' => 14155, 'bagSlots' => 16, 'price' => 4, 'priceCurrency' => CURRENCY_GOLD),
                2 => array('level' => 70, 'money' => 30000000, 'rewardGold' => 3000, 'bags' => 4, 'bagsId' => 14156, 'bagSlots' => 18, 'price' => 6, 'priceCurrency' => CURRENCY_GOLD),
                3 => array('level' => 80, 'money' => 50000000, 'rewardGold' => 5000, 'bags' => 4, 'bagsId' => 21876, 'bagSlots' => 20, 'price' => 8, 'priceCurrency' => CURRENCY_GOLD)
            );
        }
        return true;
    }

    public function get($key)
    {
        if (!isset($this->data[$key]))
        {
            return false;
        }
        return $this->data[$key];
    }

    public function getAll()
    {
        return $this->data;
    }

    public function __destruct()
    {
        unset($this->data);
        return true;
    }
}
