<?php
include_once 'engine/initialize.php';
if ($CURUSER->isOnline()) {
    if (function_exists('warcry_admin_is_allowed_account') && warcry_admin_is_allowed_account((int)$CURUSER->get('id'))) {
        header('Location: '.$config['BaseURL'].'/admin/index.php');
        exit;
    }
    header('Location: '.$config['BaseURL'].'/index.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Warcry Admin Login</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
  <script>window.jQuery || document.write('<script src="template/js/jquery-1.7.js"><\/script>');</script>
  <script src="login/js/jquery.validate.js"></script>
  <script src="login/js/notifications.js"></script>
  <script src="login/js/js.js"></script>
  <link rel="stylesheet" href="login/css/reset.css">
  <link rel="stylesheet" href="login/css/style.css?v=warcry-pro-2026-05-04">
</head>
<body class="warcry-login">
  <ul id="notifications"></ul>
  <main class="login-shell">
    <section class="login-panel">
      <div class="login-brand">
        <div class="brand-mark"><img src="template/img/logo.png" alt="Warcry CMS"></div>
        <div><h1>Warcry Admin</h1><p>Secure CMS Control Panel</p></div>
      </div>
      <?php if ($error = $ERRORS->DoPrint('login')) { echo '<div class="login-alert">'.$error.'</div>'; unset($error); } ?>
      <form name="login" action="execute.php?take=login" method="post" novalidate class="login-form">
        <label>Username</label><input type="text" name="username" placeholder="Enter username" class="required" autocomplete="username">
        <label>Password</label><input type="password" name="password" placeholder="Enter password" class="required" autocomplete="current-password">
        <button type="submit" id="loginbutton">Login</button>
      </form>
      <div class="login-foot">Warcry CMS • Professional Admin UI</div>
    </section>
  </main>
</body>
</html>
