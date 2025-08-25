<?php
/**
 * Plugin Name: WC Lite & Clean (MU)
 * Description: مینیمال‌سازی ووکامرس بدون خطای Missing Dependencies
 * Author: Golinaab / Elias
 * Version: 1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/*--------------------------------------------------------------
  1) غیرفعال‌سازی کامل WooCommerce Admin + Tracking
--------------------------------------------------------------*/
add_filter( 'woocommerce_allow_tracking', '__return_false',         999 );
add_filter( 'woocommerce_admin_disabled', '__return_true',          999 );
add_filter( 'woocommerce_admin_features', '__return_empty_array',   999 );
define( 'WOOCOMMERCE_ADMIN_DISABLED', true ); // fallback
add_action( 'init', function () {
	if ( class_exists( 'WC_Tracker' ) ) {
		wp_clear_scheduled_hook( 'woocommerce_tracker_send_event' );
	}
}, 20 );

/*--------------------------------------------------------------
  2) خاموش‌کردن نوتیفیکیشن‌های WooCommerce Helper / Connect
--------------------------------------------------------------*/
add_action( 'admin_init', function () {
	remove_all_actions( 'admin_notices',           20 ); // اغلب نوتیفیکیشن‌های ووکامرس
	remove_all_actions( 'network_admin_notices',   20 );
}, 20 );

/*--------------------------------------------------------------
  3) جلوگیری از خطای Missing Dependencies
--------------------------------------------------------------*/
add_action( 'admin_enqueue_scripts', function () {
	wp_register_script( 'wc-admin-app', false ); // جعلی
	wp_register_script( 'wc-tracks',    false );
	wp_register_script( 'wp-dom-ready', false ); // (وردپرس Core از 5.6 دارد، ولی برای اطمینان)
}, 1 );

/*--------------------------------------------------------------
  4) حذف استایل‌ها و اسکریپت‌های ووکامرس از صفحات غیر فروشگاهی
--------------------------------------------------------------*/
add_action( 'wp_enqueue_scripts', function () {

	if ( ! class_exists( 'WooCommerce' ) ) return;

	if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
		return; // در صفحات فروشگاهی نیاز داریم
	}

	// Styles
	foreach ( [
		'woocommerce-general',
		'woocommerce-layout',
		'woocommerce-smallscreen',
		'woocommerce-inline',
		'woocommerce-blocks-style',
		'wc-block-style',
	] as $style ) {
		wp_dequeue_style( $style );
	}

	// Scripts
	foreach ( [
		'wc-add-to-cart',
		'wc-cart-fragments',
		'woocommerce',
		'wc-checkout',
		'wc-add-to-cart-variation',
		'wc-price-slider',
		'wc-single-product',
		'wc-cart',
		'wc-blocks-vendors',
	] as $script ) {
		wp_dequeue_script( $script );
	}
}, 99 );

/*--------------------------------------------------------------
  5) حذف اسکریپت‌ها و استایل‌های WooCommerce Admin در پیشخوان
--------------------------------------------------------------*/
add_action( 'admin_enqueue_scripts', function () {
	foreach ( [
		'wc-admin-app',
		'wc-admin-css',
		'woocommerce_admin_styles',
		'wc-admin-dashboard',
		'wc-onboarding',
	] as $handle ) {
		wp_dequeue_script(    $handle );
		wp_deregister_script( $handle );
		wp_dequeue_style(     $handle );
		wp_deregister_style(  $handle );
	}
}, 100 );

/*--------------------------------------------------------------
  6) حذف ویجت‌ها و متاباکس‌های اضافی داشبورد
--------------------------------------------------------------*/
add_action( 'wp_dashboard_setup', function () {
	remove_meta_box( 'woocommerce_dashboard_status', 'dashboard', 'normal' );

	// ویجت‌های وردپرس
	foreach ( [ 'dashboard_quick_press', 'dashboard_recent_drafts', 'dashboard_primary', 'dashboard_secondary' ] as $w ) {
		remove_meta_box( $w, 'dashboard', 'side' );
	}
}, 99 );

/*--------------------------------------------------------------
  7) غیرفعال‌کردن نظرات محصولات
--------------------------------------------------------------*/
add_filter( 'woocommerce_product_reviews_enabled', '__return_false' );
