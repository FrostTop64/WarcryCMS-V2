<?php
if (!defined('init_pages')) { header('HTTP/1.0 404 not found'); exit; }
require_once $config['RootPath'].'/engine/helpers/account_modules.php';
function ham($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
if ($success = $ERRORS->successPrint(array('account_modules'))) { echo $success; }
if ($error = $ERRORS->DoPrint('account_modules')) { echo $error; }
$d = warcry_account_modules_defaults();
foreach ($d as $k => $v) { $d[$k] = warcry_account_module_get($k, $v); }
?>
<nav id="secondary" class="disable-tabbing"><ul><li class="current"><a href="index.php?page=account-modules">Account Modules</a></li></ul></nav>
<section id="content">
  <div class="tab" id="maintab">
    <h2>Account Modules Manager</h2>
    <div class="notice">Manage the services shown in the Account panel and control prices without editing PHP files.</div>
    <div class="admin-card">
      <form method="post" action="execute.php?take=account_modules" class="form pro-form">
        <h3>Purchase In-Game Gold</h3>
        <section><label>Enabled</label><div class="field-inline"><select name="purchase_gold_enabled"><option value="1"<?php echo $d['purchase_gold_enabled']=='1'?' selected':''; ?>>Enabled</option><option value="0"<?php echo $d['purchase_gold_enabled']=='0'?' selected':''; ?>>Disabled</option></select></div></section>
        <section><label>Title</label><div class="field-stack"><input type="text" name="purchase_gold_title" value="<?php echo ham($d['purchase_gold_title']); ?>"></div></section>
        <section><label>Description</label><div class="field-stack"><textarea name="purchase_gold_description" rows="3"><?php echo ham($d['purchase_gold_description']); ?></textarea></div></section>
        <section><label>Price Rate <small>Gold coins charged per unit.</small></label><div class="field-inline"><input type="text" name="purchase_gold_rate" value="<?php echo ham($d['purchase_gold_rate']); ?>"> <span class="badge">Gold Coins</span></div></section>
        <section><label>Gold Unit <small>Example: 1000 means 1 price rate per 1000 gold.</small></label><div class="field-stack"><input type="text" name="purchase_gold_unit" value="<?php echo ham($d['purchase_gold_unit']); ?>"></div></section>
        <section><label>Min / Max Gold</label><div class="field-inline"><input type="text" name="purchase_gold_min" value="<?php echo ham($d['purchase_gold_min']); ?>"><input type="text" name="purchase_gold_max" value="<?php echo ham($d['purchase_gold_max']); ?>"></div></section>
        <hr>
        <h3>Faction Change</h3>
        <section><label>Enabled</label><div class="field-inline"><select name="faction_enabled"><option value="1"<?php echo $d['faction_enabled']=='1'?' selected':''; ?>>Enabled</option><option value="0"<?php echo $d['faction_enabled']=='0'?' selected':''; ?>>Disabled</option></select></div></section>
        <section><label>Title</label><div class="field-stack"><input type="text" name="faction_title" value="<?php echo ham($d['faction_title']); ?>"></div></section>
        <section><label>Description</label><div class="field-stack"><textarea name="faction_description" rows="3"><?php echo ham($d['faction_description']); ?></textarea></div></section>
        <section><label>Price</label><div class="field-inline"><input type="text" name="faction_price" value="<?php echo ham($d['faction_price']); ?>"> <span class="badge">Gold Coins</span></div></section>
        <hr>
        <h3>Character Re-customization</h3>
        <section><label>Enabled</label><div class="field-inline"><select name="recustomization_enabled"><option value="1"<?php echo $d['recustomization_enabled']=='1'?' selected':''; ?>>Enabled</option><option value="0"<?php echo $d['recustomization_enabled']=='0'?' selected':''; ?>>Disabled</option></select></div></section>
        <section><label>Title</label><div class="field-stack"><input type="text" name="recustomization_title" value="<?php echo ham($d['recustomization_title']); ?>"></div></section>
        <section><label>Description</label><div class="field-stack"><textarea name="recustomization_description" rows="3"><?php echo ham($d['recustomization_description']); ?></textarea></div></section>
        <section><label>Price</label><div class="field-inline"><input type="text" name="recustomization_price" value="<?php echo ham($d['recustomization_price']); ?>"> <span class="badge">Gold Coins</span></div></section>
        <section><label></label><div><button type="submit" class="button primary">Save Modules</button></div></section>
      </form>
    </div>
  </div>
</section>
