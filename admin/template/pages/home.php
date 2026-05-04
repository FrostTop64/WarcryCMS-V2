<?php
if (!defined('init_pages')) { header('HTTP/1.0 404 not found'); exit; }
if ($error = $ERRORS->DoPrint('permissions')) { echo $error; }
unset($error);
?>
<nav id="secondary" class="disable-tabbing"><ul><li class="current"><a href="#maintab">Dashboard</a></li></ul></nav>
<section id="content">
  <div class="tab" id="maintab">
    <div class="notice"><strong>Warcry CMS Admin</strong> — panneau administratif modernisé, plus propre, plus lisible et plus simple à naviguer.</div>
    <div class="admin-grid">
      <div class="stat-card"><span>Status</span><strong>Online</strong><small class="muted">Admin panel ready</small></div>
      <div class="stat-card"><span>Theme</span><strong>AAA</strong><small class="muted">Warcry red / silver style</small></div>
      <div class="stat-card"><span>Avatars</span><strong>Ready</strong><small class="muted">Staff & member gallery</small></div>
      <div class="stat-card"><span>UI</span><strong>Clean</strong><small class="muted">Old boxes visually refreshed</small></div>
    </div>
    <h3>Quick navigation</h3>
    <div class="quick-actions">
      <a class="quick-card" href="index.php?page=news"><strong>Manage News</strong><p>Create, edit and control website news posts.</p></a>
      <a class="quick-card" href="index.php?page=articles"><strong>Manage Articles</strong><p>Control articles and content pages.</p></a>
      <a class="quick-card" href="index.php?page=avatars"><strong>Avatar Gallery</strong><p>Add custom avatars for staff and members.</p></a>
      <a class="quick-card" href="index.php?page=store"><strong>Item Store</strong><p>Add or edit shop items.</p></a>
      <a class="quick-card" href="index.php?page=users"><strong>Users</strong><p>Review accounts and permissions.</p></a>
      <a class="quick-card" href="index.php?page=settings"><strong>Settings</strong><p>Update homepage, slider and CMS options.</p></a>
    </div>
  </div>
</section>
