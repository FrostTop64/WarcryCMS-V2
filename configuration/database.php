<?php
if (!defined('init_config'))
{
	header('HTTP/1.0 404 not found');
	exit;
}

//Default PDO Error handler
$PDO_config['errorHandler'] = PDO::ERRMODE_EXCEPTION;
//Default PDO Fetch Mode
$PDO_config['fetch'] = PDO::FETCH_ASSOC;

//Website Database Connection Info (env overrides for containerized/dev setups)
$config['DatabaseHost']     = getenv('WARCRY_DB_HOST')     ?: 'localhost';
$config['DatabaseUser']     = getenv('WARCRY_DB_USER')     ?: 'Ghost';
$config['DatabasePass']     = getenv('WARCRY_DB_PASS')     ?: 'ascent';
$config['DatabaseName']     = getenv('WARCRY_DB_NAME')     ?: 'warcry';
$config['DatabaseEncoding'] = getenv('WARCRY_DB_ENCODING') ?: 'utf8';
