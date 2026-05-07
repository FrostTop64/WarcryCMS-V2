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
        'levels_enabled' => '1',
        'levels_title' => 'Character Level Up',
        'levels_description' => 'Select one of the available level packages and receive the level, reward gold and bags on your character.',
        'levels_1_level' => '60',
        'levels_1_reward_gold' => '2000',
        'levels_1_bags' => '4',
        'levels_1_bag_item' => '14155',
        'levels_1_bag_slots' => '16',
        'levels_1_price' => '4',
        'levels_2_level' => '70',
        'levels_2_reward_gold' => '3000',
        'levels_2_bags' => '4',
        'levels_2_bag_item' => '14156',
        'levels_2_bag_slots' => '18',
        'levels_2_price' => '6',
        'levels_3_level' => '80',
        'levels_3_reward_gold' => '5000',
        'levels_3_bags' => '4',
        'levels_3_bag_item' => '21876',
        'levels_3_bag_slots' => '20',
        'levels_3_price' => '8',
        'lottery_enabled' => '1',
        'lottery_ticket_price' => '3',
        'lottery_title' => 'Lottery Scratch Ticket',
        'lottery_description' => 'Scratch a ticket with your mouse for a random Wrath of the Lich King item. Rewards are stored in your website inventory and can be claimed to any character later.',
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
function warcry_level_packages()
{
    $packages = array();
    for ($i = 1; $i <= 3; $i++)
    {
        $targetLevel = max(1, (int)warcry_account_module_get('levels_'.$i.'_level', 1));
        $rewardGold = max(0, (int)warcry_account_module_get('levels_'.$i.'_reward_gold', 0));
        $bags = max(0, (int)warcry_account_module_get('levels_'.$i.'_bags', 0));
        $bagItem = max(0, (int)warcry_account_module_get('levels_'.$i.'_bag_item', 0));
        $bagSlots = max(0, (int)warcry_account_module_get('levels_'.$i.'_bag_slots', 0));
        $price = max(0, (int)warcry_account_module_get('levels_'.$i.'_price', 0));

        $packages[$i] = array(
            'level' => $targetLevel,
            'money' => $rewardGold * 10000,
            'rewardGold' => $rewardGold,
            'bags' => $bags,
            'bagsId' => $bagItem,
            'bagSlots' => $bagSlots,
            'price' => $price,
            'priceCurrency' => defined('CURRENCY_GOLD') ? CURRENCY_GOLD : 'gold'
        );
    }
    return $packages;
}

?>