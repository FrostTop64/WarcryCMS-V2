<?php
if (!defined('init_config'))
{
    header('HTTP/1.0 404 not found');
    exit;
}

// Server core type
$server_config['CORE'] = 'trinity';

// Default realm configuration.
// The admin panel writes custom realms to configuration/realms.custom.php.
$realms_config = array();
$realms_config[1] = array(
    'name'          => 'AzerothCore',
    'descr'         => 'Blizzlike',
    'realm_type'    => 'Blizzlike',
    'expansion'     => 'Wrath of the Lich King',
    'rates'         => '1x',
    'website_note'  => '',
    'active'        => 1,
    'Database'      => array(
        'host'      => '127.0.0.1',
        'name'      => 'characters',
        'user'      => 'Ghost',
        'pass'      => 'ascent',
        'encoding'  => 'utf8'
    ),
    'address'       => '127.0.0.1',
    'port'          => '8085',
    'soap_protocol' => 'http',
    'soap_address'  => '127.0.0.1',
    'soap_port'     => '7878',
    'soap_user'     => 'Keithus',
    'soap_pass'     => 'wattzhammer',
    'UPDATE_TIME'   => '10 minutes',
);

$warcryCustomRealmsFile = __DIR__ . '/realms.custom.php';
if (is_file($warcryCustomRealmsFile))
{
    include $warcryCustomRealmsFile;
}
