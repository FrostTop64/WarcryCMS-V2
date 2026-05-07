<?php
if (!defined('init_executes')) { header('HTTP/1.0 404 not found'); exit; }
$CORE->loggedInOrReturn();
require_once $config['RootPath'].'/engine/helpers/lottery.php';
$CORE->load_ServerModule('character'); $CORE->load_ServerModule('commands');
$ERRORS->NewInstance('lottery'); $ERRORS->onSuccess('Reward claimed and mailed to your character.', '/index.php?page=lottery');
warcry_lottery_ensure_tables();
$id = isset($_POST['inventory_id']) ? (int)$_POST['inventory_id'] : 0; $char = isset($_POST['character']) ? trim($_POST['character']) : '';
if ($id <= 0 || $char === '') { $ERRORS->Add('Please select a reward and a character.'); $ERRORS->Check('/index.php?page=lottery'); }
$s=$DB->prepare("SELECT * FROM `lottery_inventory` WHERE `id`=:id AND `account_id`=:acc AND `status`='pending' LIMIT 1"); $s->execute(array(':id'=>$id, ':acc'=>(int)$CURUSER->get('id'))); $it=$s->fetch();
if (!$it) { $ERRORS->Add('This reward is not available anymore.'); $ERRORS->Check('/index.php?page=lottery'); }
$chars = new server_Character(); $realm=(int)$CURUSER->GetRealm();
if (!$chars->setRealm($realm) || !$chars->isMyCharacter(false, $char)) { $ERRORS->Add('The selected character does not belong to this account.'); $ERRORS->Check('/index.php?page=lottery'); }
$items = array(); for ($i=0; $i<max(1,(int)$it['quantity']); $i++) { $items[] = (int)$it['item_entry']; }
$command = new server_Commands(); $sent = $command->sendItems($char, implode(' ', $items), 'Lottery Reward Delivery', $realm);
if ($sent !== true) { $ERRORS->Add('The website failed to mail this reward. Please contact staff.'); $ERRORS->Check('/index.php?page=lottery'); }
$u=$DB->prepare("UPDATE `lottery_inventory` SET `status`='claimed', `character_name`=:char, `claimed_at`=:time WHERE `id`=:id AND `account_id`=:acc AND `status`='pending' LIMIT 1");
$u->execute(array(':char'=>$char, ':time'=>time(), ':id'=>$id, ':acc'=>(int)$CURUSER->get('id')));
$ERRORS->triggerSuccess(); exit;
?>
