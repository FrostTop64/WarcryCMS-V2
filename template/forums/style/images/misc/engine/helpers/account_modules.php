<?php
if (!defined('init_engine') && !defined('init_pages') && !defined('init_executes')) { header('HTTP/1.0 404 not found'); exit; }

function warcry_account_modules_defaults()
{
    return array(
        'purchase_gold_enabled' => '1',
        'purchase_gold_rate' => '1',
        'purchase_gold_unit' => '1000',
        'purchase_gold_min' => '1000',
        'purchase_gold_max' => '100000',
        'purchase_gold_title' => 'In-Game Gold',
        'purchase_gold_description' => 'Purchase in-game gold and receive it by mail on the selected character.',
        'faction_enabled' => '1',
        'faction_price' => '5',
        'faction_title' => 'Faction Change',
        'faction_description' => 'The faction change cannot be reversed. To change back, the service must be purchased again.',
        'recustomization_enabled' => '1',
        'recustomization_price' => '5',
        'recustomization_title' => 'Character Re-customization',
        'recustomization_description' => 'Allows the selected character to customize appearance on next login.',
    );
}

function warcry_account_modules_ensure_table()
{
    global $DB;
    $DB->query("CREATE TABLE IF NOT EXISTS `site_settings` (`name` varchar(64) NOT NULL, `value` text NOT NULL, PRIMARY KEY (`name`)) ENGINE=MyISAM DEFAULT CHARSET=utf8");
}

function warcry_account_module_get($name, $default = null)
{
    global $DB;
    warcry_account_modules_ensure_table();
    $defaults = warcry_account_modules_defaults();
    if ($default === null && isset($defaults[$name])) { $default = $defaults[$name]; }
    $s = $DB->prepare("SELECT `value` FROM `site_settings` WHERE `name` = :name LIMIT 1");
    $s->execute(array(':name' => 'account_module_'.$name));
    $r = $s->fetch();
    return $r ? $r['value'] : $default;
}

function warcry_account_module_set($name, $value)
{
    global $DB;
    warcry_account_modules_ensure_table();
    $key = 'account_module_'.$name;
    $s = $DB->prepare("REPLACE INTO `site_settings` (`name`, `value`) VALUES (:name, :value)");
    $s->execute(array(':name' => $key, ':value' => $value));
}

function warcry_account_module_enabled($module)
{
    return warcry_account_module_get($module.'_enabled', '1') == '1';
}

function warcry_account_module_price($module, $default = 0)
{
    return max(0, (int)warcry_account_module_get($module.'_price', $default));
}

function warcry_purchase_gold_settings()
{
    $unit = max(1, (int)warcry_account_module_get('purchase_gold_unit', 1000));
    $rate = max(1, (int)warcry_account_module_get('purchase_gold_rate', 1));
    $min = max($unit, (int)warcry_account_module_get('purchase_gold_min', $unit));
    $max = max($min, (int)warcry_account_module_get('purchase_gold_max', 100000));
    return array('unit' => $unit, 'rate' => $rate, 'min' => $min, 'max' => $max);
}

function warcry_round_gold_amount($amount, $unit, $min, $max)
{
    $amount = (int)$amount;
    if ($amount < $min) { $amount = $min; }
    if ($amount > $max) { $amount = $max; }
    $left = $amount % $unit;
    if ($left > 0) { $amount = $amount - $left + $unit; }
    if ($amount > $max) { $amount = $max; }
    return $amount;
}

function warcry_purchase_gold_cost($amount)
{
    $s = warcry_purchase_gold_settings();
    $amount = warcry_round_gold_amount($amount, $s['unit'], $s['min'], $s['max']);
    return (int)ceil($amount / $s['unit']) * $s['rate'];
}
?>