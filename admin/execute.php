<?PHP
include_once 'engine/initialize.php';

define("init_executes", true);
 
$execute = (isset($_GET['take']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['take']) : NULL);
      
$file = $config['RootPath'].'/admin/execute_files/'.$execute.'_execute.php';

if ($execute !== 'login') {
    warcry_admin_require_panel_access();
}  

$allowed = array(
	'login',
	'cropImage',
	'addNews',
	'editNews',
	'delete',
	'add_armorsetcat',
	'edit_armorsetcat',
	'add_forumcat',
	'edit_forumcat',
	'add_armorset',
	'edit_armorset',
	'approve_report',
	'disapprove_report',
	'edit_bugreport',
	'add_pcode',
	'add_article',
	'edit_article',
	'grant_permissions',
	'chuckedUpload',
	'add_movie',
	'edit_movie',
	'wallpaper',
	'edit_storeitem',
	'add_storeitem',
	'change_user_rank',
	'save_settings',
	'avatars',
	'manage_user',
	'topvote',
	'forum_manage',
	'realms',
	'account_modules',
	'ticket_manage',
	'lottery_prize',
);

if (in_array($execute, $allowed, true))
{
	if (file_exists($file))
	{
		require_once $file;
	}
	else
	{
		header('HTTP/1.0 404 not found');
		exit;
	}
}
else
{
	header('HTTP/1.0 404 not found');
	exit;
}
