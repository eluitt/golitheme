<?php
/**
 * Throttle Tutor LMS transients to avoid per-request DB churn.
 * - برای get_transient('tutor_plugin_info') از کش محلی استفاده کن
 * - TTL را به‌صورت کنترل‌شده تنظیم کن تا مدام delete_option صدا نشود
 */
if ( ! defined('ABSPATH') ) exit;

const GFX_TUTOR_INFO_TTL = 30 * MINUTE_IN_SECONDS; // می‌توانی 5m/1h بگذاری

// اگر کش خودمان موجود است، قبل از DB همان را بده
add_filter('pre_transient_tutor_plugin_info', function ($pre) {
    $cached = get_transient('gfx_tutor_plugin_info_cache');
    return ($cached !== false) ? $cached : $pre;
}, 10, 1);

// وقتی Tutor مقدار را ست کرد، یک کپی با TTL کنترل‌شده ذخیره کن
add_action('set_transient_tutor_plugin_info', function ($transient, $value, $expiration) {
    set_transient('gfx_tutor_plugin_info_cache', $value, GFX_TUTOR_INFO_TTL);
}, 10, 3);

// اگر مقدار اصلی پاک شد، کش کمکی ما را الکی پاک نکن (fallback بماند)
add_action('deleted_transient', function ($transient) {
    if ($transient === 'tutor_plugin_info') {
        // هیچ کاری نکن؛ کش خودمان برقرار می‌ماند
    }
}, 10, 1);
