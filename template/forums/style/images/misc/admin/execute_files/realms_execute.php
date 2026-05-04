<?php
if (!defined('init_executes')) { header('HTTP/1.0 404 not found'); exit; }

$CORE->loggedInOrReturn();
$action = isset($_GET['action']) ? $_GET['action'] : '';

function warcry_realms_redirect($suffix = '')
{
    global $config;
    header('Location: '.$config['BaseURL'].'/admin/index.php?page=realms'.$suffix);
    exit;
}

function warcry_clean_text($key, $default = '')
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

function warcry_realms_export($realms)
{
    ksort($realms);
    $php = "<?php\n";
    $php .= "if (!defined('init_config')) { header('HTTP/1.0 404 not found'); exit; }\n";
    $php .= "// This file is managed by Warcry Admin Panel > Realms.\n";
    $php .= '$realms_config = ' . var_export($realms, true) . ";\n";
    return $php;
}

function warcry_realms_save_file($realms)
{
    global $config;
    $file = $config['RootPath'] . '/configuration/realms.custom.php';
    $tmp = $file . '.tmp';
    $data = warcry_realms_export($realms);
    if (@file_put_contents($tmp, $data, LOCK_EX) === false) { return false; }
    if (!@rename($tmp, $file)) { @unlink($tmp); return false; }
    return true;
}

if ($action === 'save')
{
    global $realms_config;
    if (!isset($realms_config) || !is_array($realms_config)) { $realms_config = array(); }

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = warcry_clean_text('name');
    $descr = warcry_clean_text('descr', 'Blizzlike');
    $address = warcry_clean_text('address', '127.0.0.1');
    $port = isset($_POST['port']) ? (int)$_POST['port'] : 8085;
    $dbHost = warcry_clean_text('db_host', '127.0.0.1');
    $dbName = warcry_clean_text('db_name', 'characters');
    $dbUser = warcry_clean_text('db_user');
    $dbPass = warcry_clean_text('db_pass');
    $dbEncoding = warcry_clean_text('db_encoding', 'utf8');

    if ($id <= 0 || $name === '' || $address === '' || $port <= 0 || $dbHost === '' || $dbName === '' || $dbUser === '')
    {
        warcry_realms_redirect('&error=1');
    }

    $realmType = warcry_clean_text('realm_type', $descr);
    $realms_config[$id] = array(
        'name'          => $name,
        'descr'         => $descr,
        'realm_type'    => $realmType,
        'expansion'     => warcry_clean_text('expansion', 'Wrath of the Lich King'),
        'rates'         => warcry_clean_text('rates', '1x'),
        'website_note'  => warcry_clean_text('website_note'),
        'active'        => isset($_POST['active']) ? 1 : 0,
        'Database'      => array(
            'host'      => $dbHost,
            'name'      => $dbName,
            'user'      => $dbUser,
            'pass'      => $dbPass,
            'encoding'  => $dbEncoding,
        ),
        'address'       => $address,
        'port'          => (string)$port,
        'soap_protocol' => warcry_clean_text('soap_protocol', 'http') === 'https' ? 'https' : 'http',
        'soap_address'  => warcry_clean_text('soap_address', '127.0.0.1'),
        'soap_port'     => (string)(isset($_POST['soap_port']) ? (int)$_POST['soap_port'] : 7878),
        'soap_user'     => warcry_clean_text('soap_user'),
        'soap_pass'     => warcry_clean_text('soap_pass'),
        'UPDATE_TIME'   => warcry_clean_text('update_time', '10 minutes'),
    );

    if (!warcry_realms_save_file($realms_config)) { warcry_realms_redirect('&error=write'); }
    warcry_realms_redirect('&saved=1');
}
else if ($action === 'delete')
{
    global $realms_config;
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0 || !isset($realms_config[$id]) || count($realms_config) <= 1) { warcry_realms_redirect('&error=1'); }
    unset($realms_config[$id]);
    if (!warcry_realms_save_file($realms_config)) { warcry_realms_redirect('&error=write'); }
    warcry_realms_redirect('&deleted=1');
}

warcry_realms_redirect('&error=1');
