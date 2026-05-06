<?php
if (!defined('init_ajax')) {
    header('HTTP/1.0 404 not found');
    exit;
}

warcry_render_purchase_log_dt($DB, $CURUSER, 'PSTORE_CUSTOMIZE');
