<?php
if (!defined('init_pages')) { header('HTTP/1.0 404 not found'); exit; }
$CORE->loggedInOrReturn();
require_once $config['RootPath'].'/engine/helpers/account_modules.php';
require_once $config['RootPath'].'/engine/helpers/lottery.php';
warcry_lottery_ensure_tables();
if (!warcry_lottery_enabled()) {
    $TPL->SetTitle('Lottery Disabled'); $TPL->LoadHeader();
    echo '<div class="content_holder"><div class="container_2 account"><div class="cont-image"><div class="container_3 account_sub_header"><div class="grad"><div class="page-title">Lottery Disabled</div><a href="'.$config['BaseURL'].'/index.php?page=account">Back to account</a></div></div><p style="padding:30px;text-align:center;">This module is currently disabled by staff.</p></div></div></div>';
    $TPL->LoadFooter(); exit;
}
$title = warcry_lottery_title();
$desc = warcry_account_module_get('lottery_description', 'Scratch a ticket with your mouse for a random item. The item is only added to your website inventory after the ticket is revealed.');
$cost = warcry_lottery_cost();
$accountId = (int)$CURUSER->get('id');
$activeTicket = warcry_lottery_active_ticket($accountId);
$pending = warcry_lottery_inventory($accountId, 'pending');
$claimed = warcry_lottery_inventory($accountId, 'claimed');
$TPL->SetTitle($title); $TPL->LoadHeader();
?>
<div class="content_holder">
    <div class="sub-page-title"><div id="title"><h1>Account Panel<p></p><span></span></h1></div></div>
    <div class="container_2 account" align="center"><div class="cont-image">
        <?php if ($e=$ERRORS->DoPrint('lottery')) echo $e.'<br><br>'; if ($s=$ERRORS->successPrint('lottery')) echo $s.'<br><br>'; ?>
        <div class="container_3 account_sub_header"><div class="grad"><div class="page-title"><?php echo warcry_lottery_h($title); ?></div><a href="<?php echo $config['BaseURL']; ?>/index.php?page=account">Back to account</a></div></div>
        <style>
            .lottery-wrap{padding:22px 25px 35px;text-align:left;color:#9d9077}.lottery-desc{text-align:center;line-height:1.55;margin:0 0 20px}.lottery-grid{display:grid;grid-template-columns:360px 1fr;gap:22px;align-items:start}.ticket-box,.inventory-box{background:rgba(0,0,0,.25);border:1px solid rgba(170,137,59,.28);box-shadow:0 0 18px rgba(0,0,0,.35), inset 0 1px 0 rgba(255,255,255,.04);padding:18px;border-radius:6px}.scratch-card{position:relative;width:320px;height:185px;margin:0 auto 15px;background:linear-gradient(135deg,#21160e,#5b1a16,#b18837);border:1px solid #7b6128;overflow:hidden;box-shadow:0 0 18px rgba(0,0,0,.55)}.scratch-card .prize-preview{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center}.scratch-card .prize-preview img{width:58px;height:58px;border:1px solid #c6a354;background:#111;padding:2px}.scratch-card .prize-preview strong{color:#f4d28a;font-size:16px;margin-top:8px;padding:0 12px}.scratch-card .prize-preview small{color:#c8b98e;margin-top:5px}.scratch-card canvas{position:absolute;inset:0;cursor:crosshair}.scratch-card.locked canvas{cursor:not-allowed;pointer-events:none}.scratch-card.locked:after{content:'Buy a ticket first';position:absolute;left:0;right:0;bottom:12px;text-align:center;color:#2b2118;font-weight:bold}.lottery-actions{text-align:center}.lottery-actions input{margin-top:8px}.coin-cost{color:#f4d28a;font-weight:bold}.lottery-item{display:grid;grid-template-columns:48px 1fr auto;gap:12px;align-items:center;padding:10px;border-bottom:1px solid rgba(255,255,255,.06)}.lottery-item img{width:42px;height:42px;border:1px solid #6f5528}.lottery-item strong{display:block;color:#d6b66a}.lottery-item small{display:block;color:#7f7564;margin-top:2px}.lottery-item select{max-width:190px;background:#16120d;color:#d6b66a;border:1px solid #5d4623;border-radius:3px;padding:6px;outline:none}.lottery-item select option{background:#16120d;color:#d6b66a}.lottery-empty{text-align:center;padding:25px;color:#756d61}.lottery-tabs{margin:18px 0 10px;color:#d6b66a;font-size:16px}.lottery-note{text-align:center;color:#b5a179;font-size:12px;margin-top:8px}.reveal-overlay{position:fixed;left:-9999px;top:-9999px}@media(max-width:900px){.lottery-grid{grid-template-columns:1fr}.scratch-card{width:100%}}
        </style>
        <div class="lottery-wrap">
            <div class="lottery-desc"><?php echo nl2br(warcry_lottery_h($desc)); ?><br>Ticket cost: <span class="coin-cost"><?php echo (int)$cost; ?> Gold Coin(s)</span>. Current balance: <span class="coin-cost"><?php echo (int)$CURUSER->get('gold'); ?> Gold Coin(s)</span>.</div>
            <div class="lottery-grid">
                <div class="ticket-box">
                    <div class="scratch-card<?php echo $activeTicket ? '' : ' locked'; ?>" id="scratchCard">
                        <div class="prize-preview">
                            <?php if ($activeTicket): ?>
                                <img src="<?php echo warcry_lottery_icon_url($activeTicket['icon']); ?>" alt="">
                                <strong><?php echo warcry_lottery_h($activeTicket['item_name']); ?> x<?php echo (int)$activeTicket['quantity']; ?></strong>
                                <small>Revealed! Adding reward...</small>
                            <?php else: ?>
                                <img src="<?php echo warcry_lottery_icon_url('inv_misc_questionmark'); ?>" alt="">
                                <strong>Scratch Ticket Locked</strong>
                                <small>Buy a ticket before scratching</small>
                            <?php endif; ?>
                        </div>
                        <canvas id="scratchCanvas" width="320" height="185" data-enabled="<?php echo $activeTicket ? '1' : '0'; ?>"></canvas>
                    </div>
                    <?php if ($activeTicket): ?>
                        <div class="lottery-note">Scratch at least 60% of the ticket to reveal and add the item to your website inventory.</div>
                        <form id="revealTicketForm" class="reveal-overlay" action="<?php echo $config['BaseURL']; ?>/execute.php?take=lottery_scratch" method="post"><input type="hidden" name="ticket_id" value="<?php echo (int)$activeTicket['id']; ?>"></form>
                    <?php else: ?>
                        <form class="lottery-actions" action="<?php echo $config['BaseURL']; ?>/execute.php?take=lottery_buy" method="post"><input type="submit" value="BUY TICKET - <?php echo (int)$cost; ?> GOLD"></form>
                    <?php endif; ?>
                </div>
                <div class="inventory-box">
                    <div class="lottery-tabs">Website Inventory - claim when you want</div>
                    <?php if (!$pending): ?><div class="lottery-empty">No pending lottery rewards yet.</div><?php else: foreach ($pending as $it): ?>
                        <form class="lottery-item" action="<?php echo $config['BaseURL']; ?>/execute.php?take=lottery_claim" method="post">
                            <img src="<?php echo warcry_lottery_icon_url($it['icon']); ?>" alt=""><div><strong><?php echo warcry_lottery_h($it['item_name']); ?> x<?php echo (int)$it['quantity']; ?></strong><small>Won <?php echo date('Y-m-d H:i', (int)$it['created_at']); ?></small></div>
                            <div><input type="hidden" name="inventory_id" value="<?php echo (int)$it['id']; ?>"><?php
                            $CORE->load_ServerModule('character'); $chars = new server_Character(); $opts='';
                            if ($chars->setRealm($CURUSER->GetRealm()) && ($res=$chars->getAccountCharacters())) { while ($c=$res->fetch()) { $opts .= '<option value="'.warcry_lottery_h($c['name']).'">'.warcry_lottery_h($c['name']).' - Lv '.(int)$c['level'].'</option>'; } }
                            echo '<select name="character">'.$opts.'</select><input type="submit" value="CLAIM">'; unset($chars); ?></div>
                        </form>
                    <?php endforeach; endif; ?>
                    <div class="lottery-tabs">Recent Claimed Rewards</div>
                    <?php if (!$claimed): ?><div class="lottery-empty">No claimed rewards yet.</div><?php else: $i=0; foreach ($claimed as $it): if (++$i>10) break; ?>
                        <div class="lottery-item"><img src="<?php echo warcry_lottery_icon_url($it['icon']); ?>" alt=""><div><strong><?php echo warcry_lottery_h($it['item_name']); ?> x<?php echo (int)$it['quantity']; ?></strong><small>Sent to <?php echo warcry_lottery_h($it['character_name']); ?> on <?php echo date('Y-m-d H:i', (int)$it['claimed_at']); ?></small></div><div></div></div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
        <script>
        (function(){var canvas=document.getElementById('scratchCanvas'); if(!canvas)return; var enabled=canvas.getAttribute('data-enabled')==='1'; var ctx=canvas.getContext('2d'); var revealed=false;
        function cover(){var g=ctx.createLinearGradient(0,0,320,185);g.addColorStop(0,'#4b4b4b');g.addColorStop(.45,'#c9c9c9');g.addColorStop(.7,'#8b8b8b');g.addColorStop(1,'#545454');ctx.globalCompositeOperation='source-over';ctx.fillStyle=g;ctx.fillRect(0,0,320,185);ctx.fillStyle='#2b2118';ctx.font='bold 22px Georgia';ctx.textAlign='center';ctx.fillText(enabled?'SCRATCH TO REVEAL':'SCRATCH TICKET',160,86);ctx.font='13px Georgia';ctx.fillText(enabled?'Reveal the item before it enters inventory':'Buy a ticket before scratching',160,116);} cover(); if(!enabled)return;
        var down=false; function pos(e){var r=canvas.getBoundingClientRect(), t=e.touches?e.touches[0]:e; return {x:(t.clientX-r.left)*(canvas.width/r.width), y:(t.clientY-r.top)*(canvas.height/r.height)};} function scratch(e){if(!down||revealed)return; e.preventDefault(); var p=pos(e); ctx.globalCompositeOperation='destination-out'; ctx.beginPath(); ctx.arc(p.x,p.y,19,0,Math.PI*2); ctx.fill(); checkReveal();}
        function checkReveal(){var img=ctx.getImageData(0,0,canvas.width,canvas.height).data, clear=0; for(var i=3;i<img.length;i+=16){ if(img[i]===0) clear++; } var total=img.length/16; if(clear/total>.60){ revealed=true; canvas.style.pointerEvents='none'; setTimeout(function(){var f=document.getElementById('revealTicketForm'); if(f)f.submit();},650); }}
        canvas.addEventListener('mousedown',function(e){down=true;scratch(e)}); window.addEventListener('mouseup',function(){down=false}); canvas.addEventListener('mousemove',scratch); canvas.addEventListener('touchstart',function(e){down=true;scratch(e)},{passive:false}); canvas.addEventListener('touchmove',scratch,{passive:false}); window.addEventListener('touchend',function(){down=false});})();
        </script>
    </div></div>
</div>
<?php $TPL->LoadFooter(); ?>
