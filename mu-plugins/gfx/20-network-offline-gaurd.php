<?php
/**
 * 20 – Network / Offline Guard
 * هدف: قطع تله‌متری‌ها و منابع بیرونی پرریسک (خصوصاً ایران/لوکال) و جلوگیری از time-out
 * - Jetpack را به حالت توسعه (بدون اتصال wp.com) می‌برد
 * - WooCommerce Tracks/Telemetry را خاموش می‌کند
 * - اسکریپت‌های stats.wp.com/pixel.wp.com را از صف لود حذف می‌کند
 * - هر درخواست HTTP به دامنه‌های بلاک‌شده را قبل از خروج می‌بلعد (200 OK ساختگی)
 * - گزینه‌ی «حالت آفلاین» اختیاری برای توقف دریافت ترجمه/آپدیت‌ها (فقط اگر واقعاً لازم داری)
 *
 * نکته: این ماژول ترافیک “wordpress.org” را بلاک نمی‌کند (برای آپدیت/ترجمه نیاز است).
 */

if ( ! defined('ABSPATH') ) exit;

/*--------------------------------------------------------------
| 1) Jetpack: اجبار به حالت توسعه (هیچ تماس با wp.com)
|    باید قبل از لود جت‌پک اعمال شود
--------------------------------------------------------------*/
if ( ! defined('JETPACK_DEV_DEBUG') ) {
    define('JETPACK_DEV_DEBUG', true);
}
add_filter('jetpack_development_mode', '__return_true', 1000);

/*--------------------------------------------------------------
| 2) WooCommerce Tracking/Telemetry را خاموش کن
|    و کرانِ ارسال تله‌متری را پاک کن
--------------------------------------------------------------*/
add_action('plugins_loaded', function () {
    if ( ! class_exists('WooCommerce') ) return;

    // خاموشی مطلق تله‌متری
    add_filter('woocommerce_allow_tracking', '__return_false', 999);

    // اگر قبلاً روشن بوده، در options هم خاموشش کن
    if ( get_option('woocommerce_allow_tracking') !== 'no' ) {
        update_option('woocommerce_allow_tracking', 'no');
    }

    // توقف تسک‌های ارسال تله‌متری
    if ( class_exists('WC_Tracker') ) {
        wp_clear_scheduled_hook('woocommerce_tracker_send_event');
    }
}, 5);

/*--------------------------------------------------------------
| 3) جلوگیری از لود w.js و پیکسل‌های wp.com در صف اسکریپت‌ها
|    (حتی اگر افزونه‌ای تلاش کند آن‌ها را enqueue کند)
--------------------------------------------------------------*/
add_filter('script_loader_src', function ($src) {
    if ( ! is_string($src) ) return $src;

    if ( strpos($src, 'stats.wp.com') !== false || strpos($src, 'pixel.wp.com') !== false ) {
        return false; // اجازه‌ی لود نده
    }
    return $src;
}, PHP_INT_MAX);

// کمکی: اگر هندل‌هایی مثل stats/wpcom-stats/wc-tracks صف شدند، dequeue کن
add_action('admin_enqueue_scripts', function () {
    foreach ( ['stats','wpcom-stats','jetpack-stats','woocommerce-tracks','wc-tracks'] as $h ) {
        if ( wp_script_is($h, 'enqueued') ) {
            wp_dequeue_script($h);
        }
    }
}, 1000);

// خنثی‌سازی آبجکت‌های ردیابی برای جلوگیری از خطا در برخی اسکریپت‌های تزریقی
add_action('admin_head', function () {
    echo "<script>window._tkq=window._tkq||[];window._stq=window._stq||[];</script>";
}, 1);

/*--------------------------------------------------------------
| 4) بستن دامنه‌های بیرونیِ مزاحم در لایه‌ی HTTP
|    هر درخواست به این دامنه‌ها “قبل از خروج” بلعیده می‌شود
|    لیست را با فیلتر `gfx_blocked_hosts` قابل‌گسترش گذاشتیم
--------------------------------------------------------------*/
add_filter('pre_http_request', function ($pre, $args, $url) {
    // دامنه‌های پیش‌فرض برای بلاک
    $blocked = [
        'stats.wp.com',
        'pixel.wp.com',
        'gamipress.com',
        // در صورت نیاز اضافه کن…
    ];

    // اجازه بده از بیرون لیست را تغییر دهند
    $blocked = apply_filters('gfx_blocked_hosts', $blocked, $args, $url);

    foreach ($blocked as $host) {
        if ( stripos($url, $host) !== false ) {
            // پاسخ ساختگی 200 برگردان تا cURL/HTTP Error ایجاد نشود
            return [
                'headers'  => [],
                'body'     => '',
                'response' => ['code' => 200, 'message' => 'OK (blocked by MU)'],
                'cookies'  => [],
                'filename' => null,
            ];
        }
    }
    return $pre; // سایر دامنه‌ها: روال عادی
}, 10, 3);

/*--------------------------------------------------------------
| 5) حالت «آفلاینِ اختیاری» (فقط اگر واقعاً لازم داری)
|    با تعریف کانستنت GFX_OFFLINE=true در wp-config می‌توانی
|    ترجمه/چک آپدیت‌ها را موقتاً خاموش کنی تا timeout نخورند.
|    ⚠️ روی پروداکشن با احتیاط استفاده کن.
--------------------------------------------------------------*/
/*if ( defined('GFX_OFFLINE') && GFX_OFFLINE ) {
    add_filter('can_load_language_packs', '__return_false', 999);
    add_filter('pre_site_transient_update_core',    '__return_null', 999);
    add_filter('pre_site_transient_update_plugins', '__return_null', 999);
    add_filter('pre_site_transient_update_themes',  '__return_null', 999);
}*/