<?PHP
include_once 'engine/initialize.php';

define("init_executes", true);

$execute = (isset($_GET['take']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['take']) : NULL);
$file = $config['RootPath'].'/execute_files/'.$execute.'_execute.php';

$allowed = array(
    'login',
    'register',
    'set_realm',
    'changepass',
    'changemail',
    'changedname',
    'precovery',
    'precovery_finish',
    'post_topic',
    'post_reply',
    'edit_reply',
    'submit_bug',
    'update_bugreport',
    'delete_bugreport',
    'screenshot',
    'vote',
    'redeem_pcode',
    'buyItems',
    'item_refund',
    'purchase_gold',
    'purchase_boost',
    'faction',
    'recustomization',
    'level',
    'armorset',
    'teleport',
    'unstuck',
    'manage_user',
    'lottery_buy',
    'lottery_scratch',
    'lottery_claim'
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
?>
