<?php
/**
 * 90 – Dev Tools (DEV only)
 * ابزارهای مخصوص توسعه؛ روی پروداکشن خاموش باشد
 */
if ( ! defined('ABSPATH') ) exit;

if ( ! GFX_IS_DEV ) return; // در PROD لود نشود

// 1) خاموش‌کردن لاگ‌های jQuery-Migrate
function gfx_mute_jquery_migrate(){
    if ( wp_script_is('jquery-migrate','registered') ) {
        wp_add_inline_script('jquery-migrate','jQuery.migrateMute=true;jQuery.migrateTrace=false;','before');
    }
}
add_action('admin_enqueue_scripts','gfx_mute_jquery_migrate',0);
add_action('wp_enqueue_scripts','gfx_mute_jquery_migrate',0);

// 2) اجبار به نسخه‌های min ری‌اکت هسته (اگر پلاگینی dev می‌آورد)
add_action('wp_default_scripts', function(WP_Scripts $s){
    $react    = includes_url('js/dist/vendor/react.min.js');
    $reactDom = includes_url('js/dist/vendor/react-dom.min.js');

    foreach ( ['react','react-dom'] as $h ) {
        if ( isset($s->registered[$h]) ) {
            $s->registered[$h]->src  = ($h==='react') ? $react : $reactDom;
            $s->registered[$h]->deps = ($h==='react') ? [] : ['react'];
            $s->registered[$h]->ver  = 'wp';
        }
    }
}, 0);

// 3) استاب هندل‌های مفقود (برای جلوگیری از Missing Dependencies)
add_action('wp_loaded', function(){
    foreach ( ['wp-dom-ready','wc-tracks'] as $handle ) {
        if ( ! wp_script_is($handle,'registered') ) {
            wp_register_script($handle, plugin_dir_url(__DIR__) . 'stub.js', [], '1.0', true);
        }
    }
}, 1);