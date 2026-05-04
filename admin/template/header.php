<?php
if (!defined('init_template')) { header('HTTP/1.0 404 not found'); exit; }
$displayName = htmlspecialchars($CURUSER->get('displayName'), ENT_QUOTES, 'UTF-8');
$currentPage = isset($pageName) ? $pageName : 'home';
$MENU = array(
  array('title'=>'Dashboard','page'=>'home','match'=>'home','permission'=>false,'icon'=>'◆'),
  array('title'=>'News','page'=>'news','match'=>'news,news-post,news-edit','permission'=>PERMISSION_NEWS,'icon'=>'N'),
  array('title'=>'Articles','page'=>'articles','match'=>'articles,new-article,edit-article','permission'=>PERMISSION_ARTICLES,'icon'=>'A'),
  array('title'=>'Item Store','page'=>'store','match'=>'store,store-add','permission'=>PERMISSION_STORE,'icon'=>'S'),
  array('title'=>'Media','page'=>'media','match'=>'media,movie-add','permission'=>PERMISSION_MEDIA_MOVIES,'icon'=>'M'),
  array('title'=>'Forums','page'=>'forums','match'=>'forums,forum-cats','permission'=>array(PERMISSION_FORUMS, PERMISSION_FORUM_CATS),'icon'=>'F'),
  array('title'=>'Users','page'=>'users','match'=>'users,user-preview','permission'=>PERMISSION_PREV_USERS,'icon'=>'U'),
  array('title'=>'Tickets','page'=>'tickets','match'=>'tickets','permission'=>PERMISSION_TICKETS,'icon'=>'T'),
  array('title'=>'Changelogs','page'=>'changelogs','match'=>'changelogs','permission'=>false,'icon'=>'C'),
  array('title'=>'Avatars','page'=>'avatars','match'=>'avatars','permission'=>PERMISSION_MEDIA_MOVIES,'icon'=>'★'),
  array('title'=>'Top Vote','page'=>'topvote','match'=>'topvote','permission'=>false,'icon'=>'V'),
  array('title'=>'Realms','page'=>'realms','match'=>'realms','permission'=>false,'icon'=>'R'),
  array('title'=>'Account Modules','page'=>'account-modules','match'=>'account-modules','permission'=>PERMISSION_STORE,'icon'=>'$'),
  array('title'=>'Site Logs','page'=>'site-logs','match'=>'site-logs,logs,logs-pw,logs-sp,logs-as,logs-lvl,logs-fc,logs-customiz,logs-igg,logs-boost','permission'=>PERMISSION_LOGS,'icon'=>'L'),
  array('title'=>'Settings','page'=>'settings','match'=>'settings','permission'=>false,'icon'=>'⚙'),
);
function warcry_admin_allowed($perm, $CURUSER) {
  if (!$perm) return true;
  if (is_array($perm)) { foreach ($perm as $p) { if ($CURUSER->getPermissions()->isAllowed($p)) return true; } return false; }
  return $CURUSER->getPermissions()->isAllowed($perm);
}
$moduleTitle = 'Dashboard';
foreach ($MENU as $item) { if (in_array($currentPage, explode(',', $item['match']))) { $moduleTitle = $item['title']; break; } }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Warcry Admin Panel</title>
  <link rel="stylesheet" href="template/css/reset.css">
  <link rel="stylesheet" href="template/css/buttons.css">
  <link rel="stylesheet" href="template/css/datatables.css">
  <link rel="stylesheet" href="template/css/fileuploader.css">
  <link rel="stylesheet" href="template/css/edit-box.css">
  <link rel="stylesheet" href="template/css/bbcode-default.css">
  <link rel="stylesheet" href="template/css/media.css">
  <link rel="stylesheet" href="template/css/main.css?v=warcry-pro-2026-05-04">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
  <script>window.jQuery || document.write('<script src="template/js/jquery-1.7.js"><\/script>'); var $currentTab = null; $(function(){ $currentTab = $('#maintab'); });</script>
</head>
<body>
<div id="container" class="warcry-admin">
  <header class="admin-hero">
    <div class="brand-block">
      <a class="brand-logo" href="index.php?page=home" aria-label="Warcry Admin Home"><img src="template/img/logo.png" alt="Warcry CMS"></a>
      <div class="brand-copy"><h1 id="logo">Warcry Admin Panel</h1><p>Clean CMS administration • AzerothCore / WotLK</p></div>
    </div>
    <div id="userinfo"><span>Signed in as</span> <strong><?php echo $displayName; ?></strong> <a class="logout-btn" href="logout.php">Logout</a></div>
  </header>
  <div class="module-head"><div><span>CURRENT MODULE</span><h2><?php echo htmlspecialchars($moduleTitle, ENT_QUOTES, 'UTF-8'); ?></h2></div><div class="ready-pill"><i></i> Admin panel ready</div></div>
  <div id="application" class="admin-shell">
    <nav id="primary" class="admin-nav"><strong class="nav-title">Navigation</strong><ul>
<?php foreach ($MENU as $item): if (!warcry_admin_allowed($item['permission'], $CURUSER)) continue; $pages = explode(',', $item['match']); $active = in_array($currentPage, $pages); ?>
      <li class="<?php echo $active ? 'current' : ''; ?>"><a href="index.php?page=<?php echo htmlspecialchars($item['page']); ?>"><span class="nav-ico"><?php echo htmlspecialchars($item['icon']); ?></span><span><?php echo htmlspecialchars($item['title']); ?></span></a></li>
<?php endforeach; ?>
    </ul><input type="text" id="search" placeholder="Search this page..."></nav>
