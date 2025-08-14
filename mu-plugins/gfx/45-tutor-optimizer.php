<?php
/**
 * Tutor LMS Optimizer
 * - کم‌کردن نویز ادمین
 * - Dequeue Assetهای سنگین در ادمین عمومی
 * - افزودن ایندکس‌های لازم
 * - کش کوئری‌های پرهزینه داشبورد
 * - پاکسازی سفارش‌های قدیمی unpaid (هفتگی)
 */
if ( ! defined('ABSPATH') ) exit;

/* 1) نوتیف‌های Tutor فقط وقتی در خودِ صفحات Tutor نیستیم حذف شوند */
add_action('current_screen', function($screen){
    if ( ! class_exists('TUTOR') ) return;
    if ( $screen && strpos($screen->id,'tutor') !== false ) return; // در صفحات خودش دست نزن
    add_action('admin_init', function(){
        remove_all_actions('admin_notices', 999);
        remove_all_actions('network_admin_notices', 999);
        remove_all_actions('user_admin_notices', 999);
    }, 1);
});

/* 2) حذف ویجت‌های داشبوردِ Tutor */
add_action('wp_dashboard_setup', function(){
    remove_meta_box('tutor_dashboard_overview',        'dashboard', 'normal');
    remove_meta_box('tutor_instructor_earnings_chart', 'dashboard', 'normal');
}, 100);

/* 3) Dequeue Assetهای Tutor در ادمین عمومی (handleها را با QM تطبیق بده) */
add_action('admin_enqueue_scripts', function($hook){
    if ( ! function_exists('tutor') ) return;
    if ( strpos($hook,'tutor') !== false ) return; // صفحات خودش
    foreach ( ['tutor-admin','tutor-admin-chart-js'] as $h ) {
        wp_dequeue_script($h);
    }
    foreach ( ['tutor-admin'] as $h ) {
        wp_dequeue_style($h);
    }
}, 100);

/* 4) ایندکس‌های لازم برای جدول tutor_orders (یک‌بار) */
add_action('admin_init', function(){
    if ( ! class_exists('TUTOR') ) return;
    if ( get_option('gfx_tutor_indexes_added') ) return;

    global $wpdb;
    $table = $wpdb->prefix . 'tutor_orders';
    if ( $wpdb->get_var( $wpdb->prepare("SHOW TABLES LIKE %s", $table) ) !== $table ) return;

    $existing = $wpdb->get_col("SHOW INDEX FROM $table WHERE Key_name != 'PRIMARY'");
    $indexes  = [
        'idx_payment_status'       => "ALTER TABLE $table ADD INDEX idx_payment_status (payment_status)",
        'idx_order_type'           => "ALTER TABLE $table ADD INDEX idx_order_type (order_type)",
        'idx_payment_order_type'   => "ALTER TABLE $table ADD INDEX idx_payment_order_type (payment_status, order_type)",
    ];
    foreach ($indexes as $name => $sql) {
        if ( ! in_array($name, $existing, true) ) {
            $wpdb->query($sql);
        }
    }
    update_option('gfx_tutor_indexes_added', time());
});

/* 5) کش‌کردن داده‌های داشبورد (مثال: تعداد unpaid) */
add_filter('tutor_dashboard_data', function($data){
    $key   = 'tutor_unpaid_orders_count';
    $count = get_transient($key);
    if ($count === false) {
        global $wpdb;
        $table = $wpdb->prefix . 'tutor_orders';
        $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE payment_status='unpaid' AND order_type='single_order'");
        set_transient($key, $count, 5 * MINUTE_IN_SECONDS);
    }
    $data['unpaid_orders_count'] = $count;
    return $data;
}, 10);

/* پاک‌کردن کش هنگام تغییر وضعیت */
add_action('tutor_order_status_changed', function(){ delete_transient('tutor_unpaid_orders_count'); });
add_action('tutor_after_order_complete', function(){ delete_transient('tutor_unpaid_orders_count'); });

/* 6) کم‌کردن هزینهٔ صفحات admin Tutor */
add_action('current_screen', function($screen){
    if ( $screen && strpos($screen->id,'tutor') !== false ) {
        add_filter('tutor_load_dashboard_stats', '__return_false'); // اگر فیلتر موجود است
        add_filter('tutor_admin_per_page', function($n){ return min($n, 20); });
    }
});

/* 7) تمیزکاری فرانت (در صورت ثبت‌شدن hookها) */
add_action('wp', function(){
    if ( ! is_admin() && class_exists('TUTOR') ) {
        remove_action('wp_footer', 'tutor_load_dashboard_template_part');
        remove_action('wp_head',   'tutor_social_meta');
    }
});

/* 8) پاکسازی هفتگی سفارش‌های unpaid قدیمی‌تر از 30 روز */
if ( ! wp_next_scheduled('gfx_cleanup_old_tutor_orders') ) {
    wp_schedule_event( time(), 'weekly', 'gfx_cleanup_old_tutor_orders' );
}
add_action('gfx_cleanup_old_tutor_orders', function(){
    if ( ! class_exists('TUTOR') ) return;
    global $wpdb;
    $table = $wpdb->prefix . 'tutor_orders';
    $deleted = $wpdb->query("DELETE FROM $table WHERE payment_status='unpaid' AND order_type='single_order' AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    if ( $deleted > 0 ) {
        delete_transient('tutor_unpaid_orders_count');
        error_log("Tutor: Cleaned up $deleted old unpaid orders");
    }
});