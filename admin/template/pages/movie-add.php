<?PHP
if (!defined('init_pages')) { header('HTTP/1.0 404 not found'); exit; }
if (!$CURUSER->getPermissions()->isAllowed(PERMISSION_MEDIA_MOVIES)) { $CORE->ErrorBox('You do not have the required permissions.'); }
function wc_admin_base($config){ return rtrim($config['BaseURL'], '/') . '/admin'; }
function wc_old($key){ return isset($_POST[$key]) ? htmlspecialchars(stripslashes($_POST[$key]), ENT_QUOTES, 'UTF-8') : ''; }
?>
<nav id="secondary" class="disable-tabbing"><ul><li><a href="<?php echo wc_admin_base($config); ?>/index.php?page=media">Media Library</a></li><li class="current"><a href="<?php echo wc_admin_base($config); ?>/index.php?page=movie-add">Add Video</a></li></ul></nav>
<section id="content"><div class="tab" id="maintab">
  <div class="wc-media-head"><div><h2>Add YouTube Video</h2><p>Paste a YouTube link. MP4 upload is removed, so videos are simple and reliable.</p></div><a class="button" href="<?php echo wc_admin_base($config); ?>/index.php?page=media">Back</a></div>
  <?php if (isset($_GET['error'])): ?><div class="wc-alert error">The video was not saved. Please verify the title, YouTube link, short description and description.</div><?php endif; ?>
  <form method="post" action="<?php echo wc_admin_base($config); ?>/execute.php?take=add_movie" class="wc-media-form">
    <div class="wc-form-grid">
      <section><label>Title*<small>Displayed on the website.</small></label><div><input name="name" type="text" required maxlength="250" placeholder="Video title" /></div></section>
      <section><label>YouTube Link*<small>Works with watch, shorts, live, embed and youtu.be.</small></label><div><input name="youtube" type="url" required maxlength="250" placeholder="https://www.youtube.com/watch?v=..." /></div></section>
    </div>
    <section><label>Short Description*<small>Small text used in video cards.</small></label><div><textarea name="short_text" rows="3" maxlength="180" required placeholder="Short description..."></textarea></div></section>
    <section><label>Description*<small>Plain text. Clean display, no broken editor.</small></label><div><textarea name="text" rows="8" required placeholder="Full video description..."></textarea></div></section>
    <p><button type="submit" class="button primary submit">Create Video</button></p>
  </form>
</div></section>
<style>
.wc-media-head{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:18px;padding:18px;border:1px solid rgba(255,255,255,.1);border-radius:14px;background:rgba(255,255,255,.035)}.wc-media-head h2{margin:0 0 6px}.wc-media-head p{margin:0;color:#aaa}.wc-alert{padding:14px 16px;margin:0 0 16px;border-radius:12px}.wc-alert.error{background:rgba(210,22,43,.14);border:1px solid rgba(210,22,43,.4);color:#fff}.wc-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.wc-media-form section{padding:16px 0;border-bottom:1px solid rgba(255,255,255,.08)}.wc-media-form label{font-weight:700}.wc-media-form label small{display:block;color:#999;font-weight:400;margin-top:5px}.wc-media-form input,.wc-media-form textarea,.wc-media-form select{width:100%;box-sizing:border-box;background:#050505;color:#fff;border:1px solid rgba(255,255,255,.12);border-radius:10px;padding:13px 14px;outline:none}.wc-media-form textarea{resize:vertical;min-height:90px;line-height:1.5}.wc-media-form input:focus,.wc-media-form textarea:focus{border-color:#d3192e;box-shadow:0 0 0 2px rgba(211,25,46,.18)}@media(max-width:900px){.wc-form-grid{grid-template-columns:1fr}.wc-media-head{display:block}}
</style>
