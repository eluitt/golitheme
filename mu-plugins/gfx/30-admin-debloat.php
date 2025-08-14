<?php
/**
 * 30 – Admin Debloat
 * سبک‌سازی داشبورد و ادمین
 */
if ( ! defined('ABSPATH') ) exit;

// 1) حذف ویجت‌های غیر ضروری داشبورد (news و … که بیرون تماس می‌گیرند)
add_action('wp_dashboard_setup', function(){
    // هسته
    remove_meta_box('dashboard_primary',   'dashboard', 'side');   // وردپرس خبرها
    remove_meta_box('dashboard_secondary', 'dashboard', 'side');   // وردپرس افزوده‌ها
    remove_meta_box('dashboard_quick_press','dashboard','side');   // سریع‌نویس
    remove_meta_box('dashboard_site_health','dashboard','normal'); // اگر نمی‌خوایش، حذفش کن

    // افزونه‌ها (در صورت ثبت)
    remove_meta_box('tutor_dashboard_overview',        'dashboard', 'normal');
    remove_meta_box('tutor_instructor_earnings_chart', 'dashboard', 'normal');
}, 100);

// 2) Dequeue کتابخانه‌های سنگین روی صفحات عمومی ادمین
add_action('admin_enqueue_scripts', function($hook){
    // صفحات خاص افزونه‌ها را دست نزن (مثلاً Woo/Tutor/GamiPress)
    if ( strpos($hook,'woocommerce')!==false || strpos($hook,'tutor')!==false || strpos($hook,'gamipress')!==false ) {
        return;
    }

    foreach ( ['select2','select2-css','cmb2-scripts','cmb2-styles','wp-color-picker','iris'] as $h ) {
        wp_dequeue_style($h);
        wp_dequeue_script($h);
    }
}, 1000);

// 3) Heartbeat کندتر (کاهش update_option های بیهوده)
add_filter('heartbeat_settings', function($s){
    $s['interval'] = 60; // پیش‌فرض ~15s
    return $s;
}, 999);

// 4) اختیاری: پرش از داشبورد به لیست نوشته‌ها (اگر داشبورد نمی‌خوای)
// add_action('load-index.php', function(){
//     wp_redirect( admin_url('edit.php') ); // یا صفحه سبک دلخواه
//     exit;
// });