<?php
/* Throttle core update checks to admin/cron to avoid slow INSERT on options */
if ( ! defined('ABSPATH') ) exit;

/**
 * وقتی در فرانت‌اند هستیم (نه CRON، نه WP-CLI، نه ادمین)،
 * مقدار فعلی ترنزینت را برگردان و مانع از set/remote-check شو.
 */
add_filter('pre_site_transient_update_core', function ($val) {
    if (
        ! is_admin()
        && ! (defined('DOING_CRON') && DOING_CRON)
        && ! (defined('WP_CLI') && WP_CLI)
    ) {
        return get_site_transient('update_core'); // از مقدار موجود استفاده کن، DB ننویس
    }
    return $val;
});

