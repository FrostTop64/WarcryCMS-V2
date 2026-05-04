<?PHP
if (!defined('init_pages')) { header('HTTP/1.0 404 not found'); exit; }
if (!$CURUSER->getPermissions()->isAllowed(PERMISSION_MEDIA_MOVIES)) { $CORE->ErrorBox('You do not have the required permissions.'); }
if ($success = $ERRORS->successPrint(array('avatar_media'))) { echo $success; }
if ($error = $ERRORS->DoPrint('avatar_media')) { echo $error; }
$avatarDir = $config['RootPath'] . '/resources/avatars';
$manifestFile = $avatarDir . '/avatars.json';
$customAvatars = array();
if (file_exists($manifestFile)) { $decoded = json_decode(file_get_contents($manifestFile), true); if (is_array($decoded)) { $customAvatars = $decoded; } }
$rankOptions = array(RANK_ROOKIE => 'Rookie / Members', RANK_PARTICIPANT => 'Participant', RANK_MEMBER => 'Member', RANK_VETERAN => 'Veteran', RANK_SENIOR_MEMBER => 'Senior Member', RANK_ADDICT => 'Addict', RANK_STAFF_MEMBER => 'Staff Only');
?>
<nav id="secondary" class="disable-tabbing"><ul><li class="current"><a href="index.php?page=avatars">Avatars</a></li></ul></nav>
<section id="content"><div class="tab" id="maintab">
  <h2>Avatar Management</h2>
  <div class="notice">Add clean gallery avatars for members or staff. They appear automatically on <strong>index.php?page=avatars</strong>.</div>
  <div class="admin-card">
    <form method="post" action="execute.php?take=avatars&action=upload" enctype="multipart/form-data" class="form pro-form">
      <section><label>Avatar Category <small>Choose who can use this avatar.</small></label><div class="field-stack"><select name="rank"><?php foreach ($rankOptions as $rankId => $rankLabel): ?><option value="<?php echo (int)$rankId; ?>"><?php echo htmlspecialchars($rankLabel); ?></option><?php endforeach; ?></select></div></section>
      <section><label>Avatar Image <small>JPG, PNG, GIF<?php echo function_exists('imagecreatefromwebp') ? ', WEBP' : ''; ?>. Square image recommended. Max 5 MB.</small></label><div class="field-stack"><input type="file" name="avatar" accept="image/*" required></div></section>
      <section><label></label><div><button type="submit" class="button primary">Upload Avatar</button></div></section>
    </form>
  </div>
  <h2>Custom Uploaded Avatars</h2>
  <?php if (count($customAvatars) > 0): ?>
    <ul class="imagelist">
    <?php foreach ($customAvatars as $item): $id=isset($item['id'])?(int)$item['id']:0; $rank=isset($item['rank'])?(int)$item['rank']:0; $file=isset($item['file'])?basename($item['file']):''; if ($id <= 0 || $file == '') { continue; } $rankName=isset($rankOptions[$rank])?$rankOptions[$rank]:'Rank '.$rank; ?>
      <li><img src="<?php echo $config['BaseURL']; ?>/resources/avatars/<?php echo htmlspecialchars($file); ?>?v=<?php echo time(); ?>" alt="Avatar"><span><a href="<?php echo $config['BaseURL']; ?>/resources/avatars/<?php echo htmlspecialchars($file); ?>" target="_blank" class="name"><?php echo htmlspecialchars($rankName); ?></a><a href="execute.php?take=avatars&action=delete&id=<?php echo $id; ?>" class="delete" onclick="return deletecheck('Are you sure you want to delete this avatar?');"></a></span></li>
    <?php endforeach; ?>
    </ul>
  <?php else: ?><div class="admin-card"><p class="muted">No custom avatars uploaded yet. Upload one above and it will be added to the public avatar selector.</p></div><?php endif; ?>
  <div class="clear"></div>
</div></section>
