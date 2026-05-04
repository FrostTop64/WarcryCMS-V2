<?php
if (!defined('init_engine')) {
    header('HTTP/1.0 404 not found');
    exit;
}

function warcry_admin_public_index_url()
{
    global $config;
    return rtrim($config['BaseURL'], '/') . '/index.php';
}

function warcry_admin_redirect_public()
{
    header('Location: ' . warcry_admin_public_index_url());
    exit;
}

function warcry_admin_is_allowed_account($accountId)
{
    $accountId = (int)$accountId;
    if ($accountId <= 0) return false;
    if (!class_exists('Permissions')) {
        $permFile = dirname(__FILE__) . '/permissions.php';
        if (file_exists($permFile)) require_once $permFile;
    }
    if (class_exists('Permissions')) {
        $perms = new Permissions($accountId);
        return $perms->IsAllowedToUseACP();
    }
    return false;
}

function warcry_admin_require_panel_access()
{
    global $CURUSER;
    if (!$CURUSER || !$CURUSER->isOnline()) {
        warcry_admin_redirect_public();
    }
    $id = (int)$CURUSER->get('id');
    if ($id <= 0 || !warcry_admin_is_allowed_account($id)) {
        $_SESSION = array();
        warcry_admin_redirect_public();
    }
}
?>
