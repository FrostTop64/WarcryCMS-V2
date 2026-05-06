<?php
if (!defined('init_config'))
{	
	header('HTTP/1.0 404 not found');
	exit;
}

$config['SiteName'] = 'warcry';

$config['RootPath'] = dirname(__DIR__); 		// Auto-detects site folder, works on WAMP/XAMPP/Linux (No slash at the end)
// Auto-detects the correct local URL (WAMP/XAMPP/Linux). No slash at the end.
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '127.0.0.1';
$folder = basename($config['RootPath']);
// Containerized installs (Apache vhost docroot = project root) and any explicit
// override skip the folder suffix; classic XAMPP/WAMP installs keep it.
$baseUrlOverride = getenv('WARCRY_BASE_URL');
if ($baseUrlOverride !== false && $baseUrlOverride !== '') {
    $config['BaseURL'] = rtrim($baseUrlOverride, '/');
} elseif (in_array($folder, array('html', 'www', 'public', 'public_html'), true)) {
    $config['BaseURL'] = $scheme . '://' . $host;
} else {
    $config['BaseURL'] = $scheme . '://' . $host . '/' . $folder;
}

//Must be unique for each website
$config['AuthCookieName'] = 'WarcryCMS';

//Minifier Settings
//StyleFolderURL rewrites the URLs for the image in the CSS files
if (in_array($folder, array('html', 'www', 'public', 'public_html'), true) || $baseUrlOverride) {
    $config['StyleFolderURL'] = '/template/style/';
} else {
    $config['StyleFolderURL'] = '/' . basename($config['RootPath']) . '/template/style/'; //(With slash at the end)
}

// WAMP/PHP 8 fix: load CSS/JS files directly instead of the old resources/min system.
// This fixes the white/no-theme page when the minifier fails locally.
$config['DisableMinify'] = true;

//E-mail Address
$config['Email'] = 'info@localhost';

//Time settings
$config['TimeZone'] = 'Europe/Berlin';
$config['TimeZoneOffset'] = '+1';

//Warcry WoW Database URL
$config['WoWDB_URL'] = $scheme . '://' . $host;	//(No slash at the end)
//Complete URL to the power.js
$config['WoWDB_JS'] = $config['WoWDB_URL'] . '/power.js';
// Show technical database errors only in local development. Keep false in production.
$config['DEBUG'] = false;
