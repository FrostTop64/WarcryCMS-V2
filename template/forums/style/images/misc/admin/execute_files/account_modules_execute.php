<?php
if (!defined('init_executes')) { header('HTTP/1.0 404 not found'); exit; }
require_once $config['RootPath'].'/engine/helpers/account_modules.php';
$ERRORS->NewInstance('account_modules');
$ERRORS->onSuccess('Account modules settings saved.', '/admin/index.php?page=account-modules');
$defaults = warcry_account_modules_defaults();
foreach ($defaults as $key => $default) {
    $value = isset($_POST[$key]) ? $_POST[$key] : $default;
    if (preg_match('/(_enabled)$/', $key)) { $value = ($value == '1') ? '1' : '0'; }
    if (preg_match('/(_price|_rate|_unit|_min|_max)$/', $key)) { $value = (string)max(0, (int)$value); }
    warcry_account_module_set($key, $value);
}
$ERRORS->triggerSuccess();
exit;
?>