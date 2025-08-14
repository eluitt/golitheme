<?php
/**
 * Cron Optimizer (safe)
 * - آرام‌کردن WP-Cron در ساعات شلوغ
 * - اجرای شبانهٔ Jobهای سنگین
 * - جلوگیری از اجراهای هم‌زمان
 * - تعریف بازهٔ هفتگی
 */
if ( ! defined('ABSPATH') ) exit;

/* 0) تعریف بازهٔ هفتگی (weekly) چون پیش‌فرض WP ندارد */
add_filter('cron_schedules', function($schedules){
    if ( ! isset($schedules['weekly']) ) {
        $schedules['weekly'] = [
            'interval' => 7 * DAY_IN_SECONDS,
            'display'  => __('Once Weekly', 'gfx')
        ];
    }
    return $schedules;
});

/* 1) کاهش تداخل doing_cron (بدون دست‌کاری DB، فقط فاصله‌گذاری درخواست‌های پشت‌سرهم) */
add_filter('pre_update_option__transient_doing_cron', function($value, $old){
    static $last = 0;
    // اگر کمتر از 30 ثانیه از آخرین set گذشت، مقدار قبلی رو نگه دار تا lock بیهوده عوض نشه
    if ( ( time() - $last ) < 30 ) {
        return $old;
    }
    $last = time();
    return $value;
}, 10, 2);

/* 2) کم‌کردن فشار به‌هنگام ساعات کاری (09–18): هر 120 ثانیه یک‌بار اجازهٔ cron external بده */
add_filter('cron_request', function($req){
    $h = (int) date('H');
    if ( $h >= 9 && $h <= 18 ) {
        static $last_peak = 0;
        if ( ( time() - $last_peak ) < 120 ) {
            return false; // این درخواست cron رو فعلاً لغو کن
        }
        $last_peak = time();
    }
    // timeout منطقی‌تر برای لوکال
    $req['args']['timeout'] = 5;
    return $req;
});

/* 3) سبک‌کردن کران‌های هسته در محیط توسعه (در پروداکشن پیشنهاد نمی‌شود) */
add_action('init', function(){
    // اگر محیط تولید نیست، بررسی نسخه/آپدیت‌ها رو رقیق کن
    if ( defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE !== 'production' ) {
        remove_action('wp_version_check',   'wp_version_check');
        remove_action('wp_update_plugins',  'wp_update_plugins');
        remove_action('wp_update_themes',   'wp_update_themes');
    }
});

/* 4) ووکامرس: جمع‌کردن سشن‌ها فقط شب‌ها (۲ صبح) */
add_action('init', function(){
    if ( ! class_exists('WooCommerce') ) return;

    // هر Job زمان‌بندی‌شدهٔ پیش‌فرض پاک شود تا خودمان زمان‌دهی کنیم
    wp_clear_scheduled_hook('woocommerce_cleanup_sessions');

    // ثبت Job شبانه اگر موجود نیست
    if ( ! wp_next_scheduled('gfx_wc_night_cleanup') ) {
        // بهتره از ساعت وردپرس استفاده کنیم
        $ts = strtotime( 'tomorrow 2:00' ); // سرور لوکال
        wp_schedule_event( $ts, 'daily', 'gfx_wc_night_cleanup' );
    }
});

// اجرای واقعی cleanup شبانه
add_action('gfx_wc_night_cleanup', function(){
    if ( class_exists('WC_Session_Handler') ) {
        WC_Session_Handler::cleanup_sessions();
    }
});

/* 5) جلوگیری از اجراهای هم‌زمان (قفل نرم 60 ثانیه‌ای) */
add_action('wp_loaded', function(){
    if ( defined('DOING_CRON') && DOING_CRON ) return;

    if ( isset($_GET['doing_wp_cron']) ) {
        $t = get_transient('doing_cron');
        if ( $t && ( time() - (int)$t ) < 60 ) {
            // اگر کمتر از 60 ثانیه از اجرای قبلی گذشته، خطای ملایم بده
            wp_die( 'Cron already running', 'Cron Lock', ['response' => 423] );
        }
    }
});