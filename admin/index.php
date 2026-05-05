<?php

include_once 'engine/initialize.php';

define('init_pages', true);

$pageName = (isset($_GET['page']) && $_GET['page'] !== '') ? preg_replace('/[^a-zA-Z0-9_\-=]/', '', (string)$_GET['page']) : 'home';

//list the allowed pages
$allowed = array(
    'home',
    'news', 'news-post', 'news-edit',
    'articles', 'new-article', 'edit-article',
    'store', 'store-add',
    'media', 'movie-add', 'movie-edit',
    'forums', 'forum-cats',
    'users', 'user-preview',
    'tickets',
    'changelogs',
    'avatars',
    'topvote',
    'settings',
    'realms',
    'account-modules',
    'site-logs',
    'logs', 'logs-pw', 'logs-sp', 'logs-as', 'logs-lvl', 'logs-fc', 'logs-customiz', 'logs-igg', 'logs-boost',
);

ob_start();

$CORE->loggedInOrReturn();

$CORE->LoadHeader();

if (in_array($pageName, $allowed))
{
	if (!file_exists($config['RootPath'] . '/admin/template/pages/'.$pageName.'.php'))
	{
		ob_end_clean();
		echo 'Error: The page file is missing.';
		die;
	}
	else
	{
		include_once $config['RootPath'] . '/admin/template/pages/'.$pageName.'.php';
	}
}
else
{
	ob_end_clean();
	echo 'Error: Page not allowed.';
	die;
}

$CORE->LoadFooter();

exit;