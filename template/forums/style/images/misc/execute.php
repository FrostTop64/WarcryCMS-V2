<?PHP
include_once 'engine/initialize.php';
if (!class_exists('Permissions') && file_exists($config['RootPath'].'/admin/engine/classes/permissions.php')) { require_once $config['RootPath'].'/admin/engine/classes/permissions.php'; }
if (file_exists($config['RootPath'].'/admin/engine/classes/admin.guard.php')) { require_once $config['RootPath'].'/admin/engine/classes/admin.guard.php'; }

define("init_executes", true);
 
$execute = ((isset($_GET['take'])) ? $_GET['take'] : NULL);
      
$file = $config['RootPath'].'/admin/execute_files/'.$execute.'_execute.php';

if ($execute !== 'login' && function_exists('warcry_admin_require_panel_access')) {
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
	'wallpaper',
	'edit_storeitem',
	'add_storeitem',
	'change_user_rank',
	'save_settings',
	'avatars',
	'manage_user',
	'forum_manage',
);

if (in_array($execute, $allowed))
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