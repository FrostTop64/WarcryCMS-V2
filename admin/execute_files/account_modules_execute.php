<?php
if (!defined('init_executes')) { header('HTTP/1.0 404 not found'); exit; }
require_once $config['RootPath'].'/engine/helpers/account_modules.php';
$ERRORS->NewInstance('account_modules');
$returnCat = (isset($_POST['return_cat']) ? preg_replace('/[^a-z0-9\-]/i', '', $_POST['return_cat']) : 'shop');
$ERRORS->onSuccess('Account modules settings saved.', '/index.php?page=account-modules&cat=' . $returnCat);
$defaults = warcry_account_modules_defaults();
foreach ($defaults as $key => $default) {
    if (!isset($_POST[$key])) { continue; }
    $value = $_POST[$key];
    if (preg_match('/(_enabled)$/', $key)) { $value = ($value == '1') ? '1' : '0'; }
    if (preg_match('/(_price|_rate|_unit|_min|_max|_level|_reward_gold|_bags|_bag_item|_bag_slots)$/', $key)) { $value = (string)max(0, (int)$value); }
    warcry_account_module_set($key, $value);
}
$ERRORS->triggerSuccess();
exit;
?>