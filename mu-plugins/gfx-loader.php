<?php
/**
 * MU Loader – Golinaab
 * همهٔ ماژول‌ها را به ترتیب نام فایل از پوشهٔ gfx لود می‌کند.
 */
if ( ! defined('ABSPATH') ) exit;

$dir = __DIR__ . '/gfx';
if ( is_dir($dir) ) {
    $files = glob($dir . '/*.php');
    sort($files, SORT_NATURAL | SORT_FLAG_CASE); // ترتیب عددی 00,20,25,30,...
    foreach ($files as $file) {
        require_once $file;
    }
}