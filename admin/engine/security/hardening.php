<?php
/**
 * Warcry CMS V2 - Admin security hardening shim.
 * Delegates to the root security/hardening.php so both contexts share a single
 * implementation of warcry_e / warcry_csrf_* / warcry_security_headers.
 */
if (!defined('init_engine')) {
    header('HTTP/1.0 404 not found');
    exit;
}

require_once __DIR__ . '/../../../engine/security/hardening.php';
