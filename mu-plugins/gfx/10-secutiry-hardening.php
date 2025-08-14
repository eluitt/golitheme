<?php
/**
 * 10 – Security Hardening
 * سفت‌وسازی‌های کم‌ریسک
 */
if ( ! defined('ABSPATH') ) exit;

// جلوگیری از ویرایش فایل‌ها از داخل ادمین
if ( ! defined('DISALLOW_FILE_EDIT') ) define('DISALLOW_FILE_EDIT', true);

// XML-RPC اگر لازم نداری
add_filter('xmlrpc_enabled', '__return_false');

// جلوگیری از enumeration ساده /?author=1
if ( ! is_admin() ) {
    add_action('init', function(){
        if ( isset($_REQUEST['author']) ) {
            wp_redirect( home_url(), 301 );
            exit;
        }
    });
}

// حذف meta generator
remove_action('wp_head', 'wp_generator');
