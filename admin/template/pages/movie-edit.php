<?PHP
if (!defined('init_pages')) { header('HTTP/1.0 404 not found'); exit; }
if (!$CURUSER->getPermissions()->isAllowed(PERMISSION_MEDIA_MOVIES)) { $CORE->ErrorBox('You do not have the required permissions.'); }
function wc_admin_base_edit($config){ return rtrim($config['BaseURL'], '/') . '/admin'; }
function wc_yt_id_admin($url) { if (preg_match('~(?:v=|youtu\.be/|embed/|shorts/|live/)([A-Za-z0-9_-]{6,})~', (string)$url, $m)) return $m[1]; return preg_match('~^[A-Za-z0-9_-]{6,}$~', (string)$url) ? $url : ''; }
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$res = $DB->prepare('SELECT * FROM `movies` WHERE `id`=:id LIMIT 1;');
$res->bindParam(':id', $id, PDO::PARAM_INT); $res->execute();
if ($id <= 0 || $res->rowCount() == 0) { echo '<section id="content"><div class="tab"><h2>Video not found</h2><p><a class="button" href="index.php?page=media">Back to Media</a></p></div></section>'; return; }
$row = $res->fetch();
$yt = wc_yt_id_admin($row['youtube']);
$thumb = $yt ? 'https://img.youtube.com/vi/' . htmlspecialchars($yt, ENT_QUOTES, 'UTF-8') . '/hqdefault.jpg' : '';
?>
<nav id="secondary" class="disable-tabbing"><ul><li><a href="<?php echo wc_admin_base_edit($config); ?>/index.php?page=media">Media Library</a></li><li class="current"><a href="<?php echo wc_admin_base_edit($config); ?>/index.php?page=movie-edit&id=<?php echo $id; ?>">Edit Video</a></li></ul></nav>
<section id="content"><div class="tab" id="maintab">
  <div class="wc-media-head"><div><h2>Edit YouTube Video</h2><p>Modify title, link, description and active status.</p></div><a class="button" href="<?php echo wc_admin_base_edit($config); ?>/index.php?page=media">Back</a></div>
  <?php if (isset($_GET['error'])): ?><div class="wc-alert error">The video was not updated. Please verify all required fields.</div><?php endif; ?>
  <?php if ($thumb): ?><div class="wc-preview" style="background-image:url('<?php echo $thumb; ?>')"><span>▶</span></div><?php endif; ?>
  <form method="post" action="<?php echo wc_admin_base_edit($config); ?>/execute.php?take=edit_movie" class="wc-media-form">
    <input type="hidden" name="id" value="<?php echo $id; ?>" />
    <div class="wc-form-grid">
      <section><label>Title*</label><div><input name="name" type="text" required maxlength="250" value="<?php echo htmlspecialchars(stripslashes($row['name']), ENT_QUOTES, 'UTF-8'); ?>" /></div></section>
      <section><label>YouTube Link*</label><div><input name="youtube" type="url" required maxlength="250" value="<?php echo htmlspecialchars($row['youtube'], ENT_QUOTES, 'UTF-8'); ?>" /></div></section>
    </div>
    <section><label>Short Description*</label><div><textarea name="short_text" rows="3" maxlength="180" required><?php echo htmlspecialchars(stripslashes($row['short_text']), ENT_QUOTES, 'UTF-8'); ?></textarea></div></section>
    <section><label>Description*</label><div><textarea name="text" rows="8" required><?php echo htmlspecialchars(stripslashes($row['descr']), ENT_QUOTES, 'UTF-8'); ?></textarea></div></section>
    <section><label>Status</label><div><select name="status"><option value="1"<?php echo ((int)$row['status'] === 1 ? ' selected' : ''); ?>>Active</option><option value="0"<?php echo ((int)$row['status'] === 0 ? ' selected' : ''); ?>>Hidden</option></select></div></section>
    <p><button type="submit" class="button primary submit">Save Video</button></p>
  </form>
</div></section>
<style>
.wc-media-head{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:18px;padding:18px;border:1px solid rgba(255,255,255,.1);border-radius:14px;background:rgba(255,255,255,.035)}.wc-media-head h2{margin:0 0 6px}.wc-media-head p{margin:0;color:#aaa}.wc-alert{padding:14px 16px;margin:0 0 16px;border-radius:12px}.wc-alert.error{background:rgba(210,22,43,.14);border:1px solid rgba(210,22,43,.4);color:#fff}.wc-preview{width:360px;height:205px;background-size:cover;background-position:center;border-radius:14px;border:1px solid rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;margin-bottom:14px}.wc-preview span{width:58px;height:58px;line-height:58px;text-align:center;border-radius:50%;background:#d3192e;color:#fff;font-size:24px}.wc-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.wc-media-form section{padding:16px 0;border-bottom:1px solid rgba(255,255,255,.08)}.wc-media-form label{font-weight:700}.wc-media-form input,.wc-media-form textarea,.wc-media-form select{width:100%;box-sizing:border-box;background:#050505;color:#fff;border:1px solid rgba(255,255,255,.12);border-radius:10px;padding:13px 14px;outline:none}.wc-media-form textarea{resize:vertical;min-height:90px;line-height:1.5}.wc-media-form input:focus,.wc-media-form textarea:focus,.wc-media-form select:focus{border-color:#d3192e;box-shadow:0 0 0 2px rgba(211,25,46,.18)}@media(max-width:900px){.wc-form-grid{grid-template-columns:1fr}.wc-media-head{display:block}}
</style>
