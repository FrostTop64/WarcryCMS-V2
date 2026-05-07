<?php
if (!defined('init_executes')) { header('HTTP/1.0 404 not found'); exit; }
$CORE->loggedInOrReturn();
require_once $config['RootPath'].'/engine/helpers/account_modules.php';
require_once $config['RootPath'].'/engine/helpers/lottery.php';
$CORE->load_CoreModule('coin.activity');
$ERRORS->NewInstance('lottery');
$ERRORS->onSuccess('Ticket purchased. Scratch it to reveal your reward.', '/index.php?page=lottery');
warcry_lottery_ensure_tables();
if (!warcry_lottery_enabled()) { $ERRORS->Add('The lottery module is currently disabled.'); $ERRORS->Check('/index.php?page=account'); }
$accountId = (int)$CURUSER->get('id');
if (warcry_lottery_active_ticket($accountId)) { $ERRORS->Add('You already have a purchased ticket. Scratch it before buying another one.'); $ERRORS->Check('/index.php?page=lottery'); }
$cost = warcry_lottery_cost();
$prize = warcry_lottery_pick_prize();
if (!$prize) { $ERRORS->Add('No lottery prizes are currently available.'); $ERRORS->Check('/index.php?page=lottery'); }
$up = $DB->prepare("UPDATE `account_data` SET `gold` = (`gold` - :cost) WHERE `id`=:id AND `gold` >= :costcheck LIMIT 1");
$up->execute(array(':cost'=>$cost, ':costcheck'=>$cost, ':id'=>$accountId));
if ($up->rowCount() < 1) { $ERRORS->Add('You do not have enough Gold Coins for a lottery ticket.'); $ERRORS->Check('/index.php?page=lottery'); }
$ins = $DB->prepare("INSERT INTO `lottery_tickets` (`account_id`,`realm_id`,`prize_id`,`item_entry`,`item_name`,`icon`,`quantity`,`status`,`created_at`) VALUES (:acc,:realm,:prize,:entry,:name,:icon,:qty,'active',:time)");
$ins->execute(array(':acc'=>$accountId, ':realm'=>(int)$CURUSER->GetRealm(), ':prize'=>(int)$prize['id'], ':entry'=>(int)$prize['item_entry'], ':name'=>$prize['item_name'], ':icon'=>$prize['icon'], ':qty'=>(int)$prize['quantity'], ':time'=>time()));
$ca = new CoinActivity(); $ca->set_SourceType(CA_SOURCE_TYPE_NONE); $ca->set_SourceString('Lottery Ticket'); $ca->set_CoinsType(CA_COIN_TYPE_GOLD); $ca->set_ExchangeType(CA_EXCHANGE_TYPE_MINUS); $ca->set_Amount($cost); $ca->execute();
$ERRORS->triggerSuccess(); exit;
?>
