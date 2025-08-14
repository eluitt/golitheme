<?php
/**
 * 00 – Env Boot (minimal)
 * فقط فلگ کمکی؛ هیچ کانستنتی را بازتعریف نمی‌کند
 */
if ( ! defined('ABSPATH') ) exit;

if ( ! defined('GFX_IS_DEV') ) {
    $env = defined('WP_ENVIRONMENT_TYPE') ? WP_ENVIRONMENT_TYPE : 'production';
    define('GFX_IS_DEV', in_array($env, ['development','local','staging'], true));
}