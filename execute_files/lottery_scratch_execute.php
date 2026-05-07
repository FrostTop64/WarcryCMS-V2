<?php
if (!defined('init_executes')) { header('HTTP/1.0 404 not found'); exit; }
$CORE->loggedInOrReturn();
require_once $config['RootPath'].'/engine/helpers/account_modules.php';
require_once $config['RootPath'].'/engine/helpers/lottery.php';
$ERRORS->NewInstance('lottery');
$ERRORS->onSuccess('Ticket revealed. Your reward was added to your website inventory.', '/index.php?page=lottery');
warcry_lottery_ensure_tables();
if (!warcry_lottery_enabled()) { $ERRORS->Add('The lottery module is currently disabled.'); $ERRORS->Check('/index.php?page=account'); }
$accountId = (int)$CURUSER->get('id');
$ticketId = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
if ($ticketId <= 0) { $ERRORS->Add('Invalid lottery ticket.'); $ERRORS->Check('/index.php?page=lottery'); }
$s = $DB->prepare("SELECT * FROM `lottery_tickets` WHERE `id`=:id AND `account_id`=:acc AND `status`='active' LIMIT 1");
$s->execute(array(':id'=>$ticketId, ':acc'=>$accountId));
$ticket = $s->fetch();
if (!$ticket) { $ERRORS->Add('No active ticket found. Buy a ticket before scratching.'); $ERRORS->Check('/index.php?page=lottery'); }
$up = $DB->prepare("UPDATE `lottery_tickets` SET `status`='revealed', `revealed_at`=:time WHERE `id`=:id AND `account_id`=:acc AND `status`='active' LIMIT 1");
$up->execute(array(':time'=>time(), ':id'=>$ticketId, ':acc'=>$accountId));
if ($up->rowCount() < 1) { $ERRORS->Add('This ticket was already revealed.'); $ERRORS->Check('/index.php?page=lottery'); }
$ins = $DB->prepare("INSERT INTO `lottery_inventory` (`account_id`,`realm_id`,`prize_id`,`ticket_id`,`item_entry`,`item_name`,`icon`,`quantity`,`status`,`created_at`) VALUES (:acc,:realm,:prize,:ticket,:entry,:name,:icon,:qty,'pending',:time)");
$ins->execute(array(':acc'=>$accountId, ':realm'=>(int)$ticket['realm_id'], ':prize'=>(int)$ticket['prize_id'], ':ticket'=>(int)$ticket['id'], ':entry'=>(int)$ticket['item_entry'], ':name'=>$ticket['item_name'], ':icon'=>$ticket['icon'], ':qty'=>(int)$ticket['quantity'], ':time'=>time()));
$ERRORS->triggerSuccess(); exit;
?>
