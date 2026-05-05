<?PHP
if (!defined('init_pages')) { header('HTTP/1.0 404 not found'); exit; }
if (!$CURUSER->getPermissions()->isAllowed(PERMISSION_MEDIA_MOVIES)) { $CORE->ErrorBox('You do not have the required permissions.'); }
function wc_admin_base_media($config){ return rtrim($config['BaseURL'], '/') . '/admin'; }
function wc_yt_id_media($url){ if (preg_match('~(?:v=|youtu\.be/|embed/|shorts/|live/)([A-Za-z0-9_-]{6,})~', (string)$url, $m)) return $m[1]; return preg_match('~^[A-Za-z0-9_-]{6,}$~',(string)$url)?$url:''; }
?>
<section id="content"><div class="tab" id="maintab">
  <div class="wc-media-head"><div><h2>Media Library</h2><p>Manage YouTube videos and wallpapers from one clean page.</p></div><a class="button primary" href="<?php echo wc_admin_base_media($config); ?>/index.php?page=movie-add">+ Add Video</a></div>
  <?php if (isset($_GET['success'])): ?><div class="wc-alert success">Saved successfully.</div><?php endif; ?>
  <?php if (isset($_GET['error'])): ?><div class="wc-alert error">Action failed. Please try again.</div><?php endif; ?>
  <h2>Movies / Videos</h2>
  <div class="wc-video-grid">
    <?php
    $res = $DB->query('SELECT `id`,`name`,`short_text`,`youtube`,`status` FROM `movies` ORDER BY `id` DESC;');
    if ($res && $res->rowCount() > 0):
      while ($arr = $res->fetch()):
        $yt = wc_yt_id_media($arr['youtube']);
        $thumb = $yt ? 'https://img.youtube.com/vi/' . htmlspecialchars($yt, ENT_QUOTES, 'UTF-8') . '/hqdefault.jpg' : '';
    ?>
    <div class="wc-video-card">
      <div class="wc-video-thumb" style="background-image:url('<?php echo $thumb; ?>')"><span>▶</span></div>
      <div class="wc-video-body">
        <h3><?php echo htmlspecialchars(stripslashes($arr['name']), ENT_QUOTES, 'UTF-8'); ?></h3>
        <p><?php echo htmlspecialchars(stripslashes($arr['short_text']), ENT_QUOTES, 'UTF-8'); ?></p>
        <em><?php echo ((int)$arr['status'] === 1 ? 'Active' : 'Hidden'); ?> · YouTube</em>
      </div>
      <div class="wc-video-actions">
        <a class="button" href="<?php echo rtrim($config['BaseURL'], '/'); ?>/index.php?page=open-video&id=<?php echo (int)$arr['id']; ?>" target="_blank">View</a>
        <a class="button" href="<?php echo wc_admin_base_media($config); ?>/index.php?page=movie-edit&id=<?php echo (int)$arr['id']; ?>">Edit</a>
        <a class="button danger" href="<?php echo wc_admin_base_media($config); ?>/execute.php?take=delete&action=movie&id=<?php echo (int)$arr['id']; ?>" onclick="return deletecheck('Are you sure you want to delete this video?');">Delete</a>
      </div>
    </div>
    <?php endwhile; else: ?>
      <div class="wc-empty">There are no videos yet. Click <strong>+ Add Video</strong>.</div>
    <?php endif; unset($res); ?>
  </div>

  <?php
  $wallDir = $config['RootPath'] . '/uploads/media/wallpapers';
  $manifestFile = $wallDir . '/wallpapers.json';
  $wallpapers = array();
  if (file_exists($manifestFile)) { $decoded = json_decode(file_get_contents($manifestFile), true); if (is_array($decoded)) { $wallpapers = $decoded; } }
  ?>
  <h2 style="margin-top:32px;">Wallpaper Media</h2>
  <div class="wc-wall-card">
    <form method="post" action="<?php echo wc_admin_base_media($config); ?>/execute.php?take=wallpaper&action=upload" enctype="multipart/form-data" class="wc-wall-form">
      <section><label>Wallpaper Title<small>Name displayed on the public wallpapers page.</small></label><div><input type="text" name="title" placeholder="Example: Horde Citadel"></div></section>
      <section><label>Wallpaper Image<small>JPG, PNG, GIF<?php echo function_exists('imagecreatefromwebp') ? ', WEBP' : ''; ?>. Max 12 MB.</small></label><div><input type="file" name="wallpaper" accept="image/*" required></div></section>
      <section><label></label><div><button type="submit" class="button primary">Upload Wallpaper</button></div></section>
    </form>
  </div>
  <h2>Uploaded Wallpapers</h2>
  <?php if (count($wallpapers) > 0): ?><ul class="imagelist">
  <?php foreach ($wallpapers as $item): $title=isset($item['title'])?stripslashes($item['title']):'Wallpaper'; $file=isset($item['file'])?basename($item['file']):''; $thumb=isset($item['thumb'])?basename($item['thumb']):$file; if ($file == '') { continue; } ?>
    <li><img src="<?php echo $config['BaseURL']; ?>/uploads/media/wallpapers/thumbs/<?php echo htmlspecialchars($thumb); ?>" alt="<?php echo htmlspecialchars($title); ?>"><span><a href="<?php echo $config['BaseURL']; ?>/uploads/media/wallpapers/<?php echo htmlspecialchars($file); ?>" target="_blank" class="name"><?php echo htmlspecialchars(strlen($title)>20?substr($title,0,20).'...':$title); ?></a><a href="<?php echo wc_admin_base_media($config); ?>/execute.php?take=wallpaper&action=delete&file=<?php echo urlencode($file); ?>" class="delete" onclick="return deletecheck('Are you sure you want to delete this wallpaper?');"></a></span></li>
  <?php endforeach; ?></ul><?php else: ?><div class="wc-empty">No wallpapers uploaded yet.</div><?php endif; ?>
  <div class="clear"></div>

</div></section>
<style>
.wc-media-head{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:22px;padding:20px;border:1px solid rgba(255,255,255,.1);border-radius:14px;background:linear-gradient(135deg,rgba(255,255,255,.055),rgba(255,255,255,.018))}.wc-media-head h2{margin:0 0 6px}.wc-media-head p{margin:0;color:#aaa}.wc-alert{padding:14px 16px;margin:0 0 16px;border-radius:12px}.wc-alert.success{background:rgba(40,160,90,.14);border:1px solid rgba(40,160,90,.35);color:#fff}.wc-alert.error{background:rgba(210,22,43,.14);border:1px solid rgba(210,22,43,.4);color:#fff}.wc-video-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px;margin-top:12px}.wc-video-card{border:1px solid rgba(255,255,255,.12);border-radius:16px;background:rgba(255,255,255,.035);padding:14px}.wc-video-thumb{height:160px;border-radius:12px;background:#060606 center/cover no-repeat;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.08)}.wc-video-thumb span{width:56px;height:56px;line-height:56px;text-align:center;border-radius:50%;background:#d3192e;color:#fff;font-size:23px;box-shadow:0 10px 30px rgba(211,25,46,.35)}.wc-video-body h3{margin:13px 0 6px;color:#fff}.wc-video-body p{min-height:38px;color:#bbb}.wc-video-body em{color:#d7a53d;font-style:normal}.wc-video-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px}.button.danger{background:#6d1020!important}.wc-empty{padding:20px;border:1px solid rgba(255,255,255,.1);border-radius:14px;background:rgba(255,255,255,.03);color:#aaa}.wc-wall-card{padding:18px;border:1px solid rgba(255,255,255,.1);border-radius:14px;background:rgba(255,255,255,.03);margin:12px 0 18px}.wc-wall-form section{padding:12px 0;border-bottom:1px solid rgba(255,255,255,.07)}.wc-wall-form label{font-weight:700}.wc-wall-form label small{display:block;color:#999;font-weight:400;margin-top:5px}.wc-wall-form input{width:100%;box-sizing:border-box;background:#050505;color:#fff;border:1px solid rgba(255,255,255,.12);border-radius:10px;padding:13px 14px}
</style>
