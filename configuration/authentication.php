<?php
if (!defined('init_config'))
{
	header('HTTP/1.0 404 not found');
	exit;
}

$auth_config['DatabaseHost']     = getenv('WARCRY_AUTH_HOST')     ?: 'localhost';
$auth_config['DatabaseUser']     = getenv('WARCRY_AUTH_USER')     ?: 'Ghost';
$auth_config['DatabasePass']     = getenv('WARCRY_AUTH_PASS')     ?: 'ascent';
$auth_config['DatabaseName']     = getenv('WARCRY_AUTH_NAME')     ?: 'auth';
$auth_config['DatabaseEncoding'] = getenv('WARCRY_AUTH_ENCODING') ?: 'utf8';
