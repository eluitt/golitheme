<?php
/**
 * 40 – WooCommerce Lite
 * لاغرکردن ووکامرس در ادمین و فرانت
 */
if ( ! defined('ABSPATH') ) exit;

if ( ! defined('WOOCOMMERCE_ADMIN_DISABLED') ) define('WOOCOMMERCE_ADMIN_DISABLED', true);

add_action('plugins_loaded', function(){
    if ( ! class_exists('WooCommerce') ) return;

    // خاموشی کامل WC Admin UI
    add_filter('woocommerce_admin_disabled',  '__return_true',  999);
    add_filter('woocommerce_admin_features',  '__return_empty_array', 999);

    // تله‌متری و پیشنهادهای مارکت
    add_filter('woocommerce_allow_tracking',              '__return_false', 999);
    add_filter('woocommerce_allow_marketplace_suggestions','__return_false', 999);
    // بعضی نسخه‌ها:
    add_filter('woocommerce_show_marketplace_suggestions', '__return_false', 999);

    if ( get_option('woocommerce_allow_tracking') !== 'no' ) {
        update_option('woocommerce_allow_tracking', 'no');
    }
}, 5);

// حذف CSS/JS در صفحات غیر فروشگاهی فرانت
add_action('wp_enqueue_scripts', function(){
    if ( ! class_exists('WooCommerce') ) return;
    if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) return;

    foreach ( ['woocommerce-general','woocommerce-layout','woocommerce-smallscreen','woocommerce-inline','woocommerce-blocks-style','wc-block-style'] as $style ) {
        wp_dequeue_style($style);
    }
    foreach ( ['woocommerce','wc-add-to-cart','wc-cart-fragments','wc-checkout','wc-add-to-cart-variation','wc-price-slider','wc-single-product','wc-cart','wc-blocks-vendors'] as $script ) {
        wp_dequeue_script($script);
        wp_deregister_script($script);
    }
}, 99);
