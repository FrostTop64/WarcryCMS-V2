<?php
if (!defined('init_executes')) { header('HTTP/1.0 404 not found'); exit; }
require_once $config['RootPath'].'/engine/helpers/lottery.php';
warcry_lottery_ensure_tables();
$ERRORS->NewInstance('account_modules');
$ERRORS->onSuccess('Lottery prize pool updated.', '/index.php?page=account-modules&cat=lottery');
$action = isset($_POST['action']) ? $_POST['action'] : 'save';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($action === 'delete' && $id > 0) {
    $s=$DB->prepare("DELETE FROM `lottery_prizes` WHERE `id`=:id LIMIT 1"); $s->execute(array(':id'=>$id));
    $ERRORS->triggerSuccess(); exit;
}
$item = isset($_POST['item_entry']) ? max(1, (int)$_POST['item_entry']) : 0;
$name = isset($_POST['item_name']) ? trim($_POST['item_name']) : '';
$icon = isset($_POST['icon']) ? strtolower(preg_replace('/[^a-z0-9_\-]/i', '', $_POST['icon'])) : 'inv_misc_questionmark';
$qty = isset($_POST['quantity']) ? max(1, min(200, (int)$_POST['quantity'])) : 1;
$weight = isset($_POST['weight']) ? max(0, min(100000, (int)$_POST['weight'])) : 10;
$enabled = (isset($_POST['enabled']) && $_POST['enabled'] == '1') ? 1 : 0;
if ($item <= 0 || $name === '') { $ERRORS->Add('Item ID and item name are required.'); $ERRORS->Check('/index.php?page=account-modules&cat=lottery'); }
if ($action === 'add') {
    $s=$DB->prepare("INSERT INTO `lottery_prizes` (`item_entry`,`item_name`,`icon`,`quantity`,`weight`,`enabled`,`created_at`) VALUES (:entry,:name,:icon,:qty,:weight,:enabled,:time)");
    $s->execute(array(':entry'=>$item, ':name'=>$name, ':icon'=>$icon, ':qty'=>$qty, ':weight'=>$weight, ':enabled'=>$enabled, ':time'=>time()));
} else {
    if ($id <= 0) { $ERRORS->Add('Invalid prize selected.'); $ERRORS->Check('/index.php?page=account-modules&cat=lottery'); }
    $s=$DB->prepare("UPDATE `lottery_prizes` SET `item_entry`=:entry, `item_name`=:name, `icon`=:icon, `quantity`=:qty, `weight`=:weight, `enabled`=:enabled WHERE `id`=:id LIMIT 1");
    $s->execute(array(':entry'=>$item, ':name'=>$name, ':icon'=>$icon, ':qty'=>$qty, ':weight'=>$weight, ':enabled'=>$enabled, ':id'=>$id));
}
$ERRORS->triggerSuccess(); exit;
?>
