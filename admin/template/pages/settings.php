<?php
if (!defined('init_pages')) { header('HTTP/1.0 404 not found'); exit; }
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$DB->query("CREATE TABLE IF NOT EXISTS `site_settings` (`name` varchar(64) NOT NULL, `value` text NOT NULL, PRIMARY KEY (`name`)) ENGINE=MyISAM DEFAULT CHARSET=utf8");
function setting_get_admin($DB, $name, $default='') { $s=$DB->prepare("SELECT `value` FROM `site_settings` WHERE `name`=:n LIMIT 1"); $s->execute(array(':n'=>$name)); $r=$s->fetch(); return $r ? $r['value'] : $default; }
if ($success = $ERRORS->successPrint(array('site_settings'))) { echo $success; }
if ($error = $ERRORS->DoPrint('site_settings')) { echo $error; }
$siteName = setting_get_admin($DB, 'site_name', 'Warcry');
$realmlist = setting_get_admin($DB, 'realmlist', 'logon.project-reborn.com');
$homeTitle = setting_get_admin($DB, 'home_welcome_title', 'Welcome to WarcryCMS V2');
$homeText = setting_get_admin($DB, 'home_welcome_text', "We are a growing server with 2 realms 1 blizzlike and 1 fun realm instant 255 with much custom content.");
$footerCopy = setting_get_admin($DB, 'footer_copyright', setting_get_admin($DB, 'copyright', 'Copyright &copy; <b>WarcryCMS</b>&trade; 2026. All Rights Reserved.'));
$favicon = setting_get_admin($DB, 'favicon_path', setting_get_admin($DB, 'favicon', 'template/style/images/favicon.ico'));
$socials = array(
  'facebook' => array('title'=>'Facebook', 'label'=>setting_get_admin($DB,'social_facebook_label','Facebook'), 'url'=>setting_get_admin($DB,'social_facebook_url',''), 'enabled'=>setting_get_admin($DB,'social_facebook_enabled','1')),
  'twitter'  => array('title'=>'Twitter / X', 'label'=>setting_get_admin($DB,'social_twitter_label','Twitter'), 'url'=>setting_get_admin($DB,'social_twitter_url',''), 'enabled'=>setting_get_admin($DB,'social_twitter_enabled','1')),
  'youtube'  => array('title'=>'YouTube', 'label'=>setting_get_admin($DB,'social_youtube_label','YouTube'), 'url'=>setting_get_admin($DB,'social_youtube_url','http://www.youtube.com/user/WarcryWoW1'), 'enabled'=>setting_get_admin($DB,'social_youtube_enabled','1')),
);
?>
<nav id="secondary" class="disable-tabbing"><ul><li class="current"><a href="index.php?page=settings">Site Settings</a></li><li><a href="#social-networks">Social Networks</a></li></ul></nav>
<section id="content"><div class="tab" id="maintab"><h2>Site Settings</h2><div class="notice">Customize your public site name, realmlist, homepage welcome text, footer copyright and favicon.</div>
<div class="admin-card"><form method="post" action="execute.php?take=save_settings" enctype="multipart/form-data" class="form pro-form">
<?php echo function_exists('warcry_csrf_field') ? warcry_csrf_field() : ''; ?>
<section><label>Site Name <small>Name used across the public website and admin references.</small></label><div class="field-stack"><input type="text" name="site_name" value="<?php echo h($siteName); ?>" required></div></section>
<section><label>Server Realmlist <small>This changes the public “set realmlist” box.</small></label><div class="field-stack"><input type="text" name="realmlist" value="<?php echo h($realmlist); ?>" required placeholder="logon.yourserver.com"></div></section>
<section><label>Homepage Welcome Title <small>The large title displayed on the home banner.</small></label><div class="field-stack"><input type="text" name="home_welcome_title" value="<?php echo h($homeTitle); ?>" required></div></section>
<section><label>Homepage Welcome Text <small>Line breaks are kept on the website.</small></label><div class="field-stack"><textarea name="home_welcome_text" rows="8" required><?php echo h($homeText); ?></textarea></div></section>
<section><label>Copyright <small>Simple HTML is allowed, for example &lt;b&gt; and &amp;copy;.</small></label><div class="field-stack"><textarea name="footer_copyright" rows="5" required><?php echo h($footerCopy); ?></textarea></div></section>
<section><label>Current Favicon <small>The active favicon used by the public website.</small></label><div class="field-inline"><span class="badge"><?php echo h($favicon); ?></span><?php if ($favicon): ?><img src="../<?php echo h($favicon); ?>" alt="favicon" style="width:28px;height:28px;object-fit:contain;border-radius:6px;"> <?php endif; ?></div></section>
<section><label>Upload New Favicon <small>Recommended: .ico or 32x32 PNG.</small></label><div class="field-stack"><input type="file" name="favicon" accept=".ico,.png,.jpg,.jpeg,.gif,.webp"></div></section>
<section><label></label><div><button type="submit" class="button primary">Save Settings</button></div></section>
</form></div>

<div class="admin-card" id="social-networks" style="margin-top:22px;">
<form method="post" action="execute.php?take=save_settings" class="form pro-form" autocomplete="off">
<?php echo function_exists('warcry_csrf_field') ? warcry_csrf_field() : ''; ?>
<input type="hidden" name="settings_action" value="social_networks">
<h2>Social Networks</h2>
<div class="notice">Customize the social buttons displayed on the homepage social bar. Disable a network or leave the URL empty to hide it.</div>
<?php foreach ($socials as $key => $social): ?>
<section>
  <label><?php echo h($social['title']); ?> <small>Homepage social button.</small></label>
  <div class="field-stack">
    <label class="field-inline" style="gap:8px;"><input type="checkbox" name="social_<?php echo h($key); ?>_enabled" value="1" <?php echo ((string)$social['enabled'] === '1' ? 'checked' : ''); ?>> Enabled</label>
    <input type="text" name="social_<?php echo h($key); ?>_label" value="<?php echo h($social['label']); ?>" maxlength="40" placeholder="Button label">
    <input type="url" name="social_<?php echo h($key); ?>_url" value="<?php echo h($social['url']); ?>" maxlength="255" placeholder="https://...">
  </div>
</section>
<?php endforeach; ?>
<section><label></label><div><button type="submit" class="button primary">Save Social Networks</button></div></section>
</form>
</div>

<div class="admin-card" style="margin-top:22px;">
<form method="post" action="execute.php?take=save_settings" class="form pro-form" autocomplete="off">
<?php echo function_exists('warcry_csrf_field') ? warcry_csrf_field() : ''; ?>
<input type="hidden" name="security_action" value="admin_panel_code">
<h2>ACP Security Code Generator</h2>
<div class="notice">Change the extra security code required before the AdminCP account login screen. The code is saved as a one-way hash in <b>configuration/Admin_panel.php</b>.</div>
<section><label>New ACP Code <small>Minimum 4 characters. Use a strong private code.</small></label><div class="field-stack"><input type="password" name="admin_panel_code_new" placeholder="New admin panel code" required autocomplete="new-password"></div></section>
<section><label>Confirm ACP Code <small>Type the same code again.</small></label><div class="field-stack"><input type="password" name="admin_panel_code_confirm" placeholder="Confirm new code" required autocomplete="new-password"></div></section>
<section><label></label><div><button type="submit" class="button primary">Generate & Apply ACP Code</button></div></section>
</form>
</div>
</div></section>
