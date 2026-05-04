<?PHP
if (!defined('init_pages')) { header('HTTP/1.0 404 not found'); exit; }
if (!$CURUSER->getPermissions()->isAllowed(PERMISSION_MEDIA_MOVIES)) { $CORE->ErrorBox('You do not have the required permissions.'); }
if ($success = $ERRORS->successPrint(array('add_movie', 'delete_movie', 'wallpaper_media'))) { echo $success; }
if ($error = $ERRORS->DoPrint('delete_movie')) { echo $error; }
if ($error = $ERRORS->DoPrint('wallpaper_media')) { echo $error; }
$wallDir = $config['RootPath'] . '/uploads/media/wallpapers';
$manifestFile = $wallDir . '/wallpapers.json';
$wallpapers = array();
if (file_exists($manifestFile)) { $decoded = json_decode(file_get_contents($manifestFile), true); if (is_array($decoded)) { $wallpapers = $decoded; } }
?>
<nav id="secondary" class="disable-tabbing"><ul><li class="current"><a href="index.php?page=media">Media</a></li><li><a href="index.php?page=movie-add">New Movie</a></li></ul></nav>
<section id="content"><div class="tab" id="maintab">
  <h2>Wallpaper Media</h2>
  <div class="admin-card">
    <form method="post" action="execute.php?take=wallpaper&action=upload" enctype="multipart/form-data" class="form pro-form">
      <section><label>Wallpaper Title <small>Name displayed on the public wallpapers page.</small></label><div class="field-stack"><input type="text" name="title" value="" placeholder="Example: Horde Citadel"></div></section>
      <section><label>Wallpaper Image <small>JPG, PNG, GIF<?php echo function_exists('imagecreatefromwebp') ? ', WEBP' : ''; ?>. Max 12 MB.</small></label><div class="field-stack"><input type="file" name="wallpaper" accept="image/*" required></div></section>
      <section><label></label><div><button type="submit" class="button primary">Upload Wallpaper</button></div></section>
    </form>
  </div>
  <h2>Uploaded Wallpapers</h2>
  <?php if (count($wallpapers) > 0): ?><ul class="imagelist">
  <?php foreach ($wallpapers as $item): $title=isset($item['title'])?stripslashes($item['title']):'Wallpaper'; $file=isset($item['file'])?basename($item['file']):''; $thumb=isset($item['thumb'])?basename($item['thumb']):$file; if ($file == '') { continue; } ?>
    <li><img src="<?php echo $config['BaseURL']; ?>/uploads/media/wallpapers/thumbs/<?php echo htmlspecialchars($thumb); ?>" alt="<?php echo htmlspecialchars($title); ?>"><span><a href="<?php echo $config['BaseURL']; ?>/uploads/media/wallpapers/<?php echo htmlspecialchars($file); ?>" target="_blank" class="name"><?php echo htmlspecialchars(strlen($title)>20?substr($title,0,20).'...':$title); ?></a><a href="execute.php?take=wallpaper&action=delete&file=<?php echo urlencode($file); ?>" class="delete" onclick="return deletecheck('Are you sure you want to delete this wallpaper?');"></a></span></li>
  <?php endforeach; ?></ul><?php else: ?><div class="admin-card"><p class="muted">No wallpapers uploaded yet. The public page will stay empty until you upload your own media here.</p></div><?php endif; ?>
  <div class="clear"></div>
  <h2>Movies Management</h2>
  <?php $res = $DB->query("SELECT `id`, `name`, `image`, `dirname` FROM `movies` ORDER BY `id` DESC;"); if ($res->rowCount() > 0): ?><ul class="imagelist">
  <?php while ($arr = $res->fetch()): $movieName=stripslashes($arr['name']); ?>
    <li><img src="<?php echo $config['BaseURL']; ?>/uploads/media/movies/<?php echo $arr['dirname']; ?>/thumbnails/medium_<?php echo $arr['image']; ?>" alt="<?php echo htmlspecialchars($movieName); ?>"><span><a href="<?php echo $config['BaseURL']; ?>/index.php?page=open-video&id=<?php echo $arr['id']; ?>" target="_new" class="name ajax cboxElement"><?php echo htmlspecialchars(strlen($movieName)>20?substr($movieName,0,20).'...':$movieName); ?></a><a href="execute.php?take=delete&action=movie&id=<?php echo $arr['id']; ?>" class="delete" onclick="return deletecheck('Are you sure you want to delete this movie?');"></a></span></li>
  <?php endwhile; ?></ul><?php else: ?><div class="admin-card"><p class="muted">There are no movies.</p></div><?php endif; unset($res); ?>
  <div class="clear"></div>
</div></section>
<script>$(document).ready(function(){ $('.imagelist img').hover(function(){ $(this).stop().animate({ opacity: '0.82'}, 'fast'); }, function(){ $(this).stop().animate({ opacity: '1'}, 'fast'); }); });</script>
