<?php
if (!defined('init_config')) { header('HTTP/1.0 404 not found'); exit; }
// This file is managed by Warcry Admin Panel > Realms.
$realms_config = array (
  1 => 
  array (
    'name' => 'AzerothCore',
    'descr' => 'Blizzlike',
    'realm_type' => 'Blizzlike',
    'expansion' => 'Wrath of the Lich King',
    'rates' => '1x',
    'website_note' => '',
    'active' => 1,
    'Database' => 
    array (
      'host' => '127.0.0.1',
      'name' => 'characters',
      'user' => 'Ghost',
      'pass' => 'ascent',
      'encoding' => 'utf8',
    ),
    'address' => '127.0.0.1',
    'port' => '8085',
    'soap_protocol' => 'http',
    'soap_address' => '127.0.0.1',
    'soap_port' => '7878',
    'soap_user' => 'Keithus',
    'soap_pass' => 'wattzhammer',
    'UPDATE_TIME' => '10 minutes',
  ),
);
