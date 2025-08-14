<?php
/**
 * 60 – Compat: L10N & Remote
 * رفع هشدارهای i18n و حذف منابع دورِ پرریسک
 */
if ( ! defined('ABSPATH') ) exit;

// اگر src خالی/نامعتبر بود، تلاش برای load textdomain نکن
add_filter('load_script_textdomain_relative_path', function($relative,$src){
    if ( empty($src) ) return false;
    return $relative;
}, 1, 2);

// استاب‌ها (مثل wc-tracks/wp-dom-ready) را ترجمه نکن
add_filter('load_script_textdomain', function($translations,$file,$handle){
    $skip = ['wp-dom-ready','wc-tracks'];
    if ( in_array($handle, $skip, true) ) return false;
    return $translations;
}, 10, 3);

// الگوهای بلاک از راه دور را کلاً غیرفعال کن (کاهش تماس بیرونی)
add_filter('should_load_remote_block_patterns', '__return_false');