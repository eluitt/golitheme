<!doctype html>
<html <?php language_attributes(); ?> dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'gn-body' ); ?>>
<?php wp_body_open(); ?>
<header class="gn-site-header" role="banner">
<div class="gn-container">
<button class="gn-nav-toggle" aria-controls="gn-mobile-panel" aria-expanded="false" aria-label="<?php esc_attr_e( 'باز کردن منو', 'golitheme' ); ?>">☰</button>
<a class="gn-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
<form class="gn-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'جستجو', 'golitheme' ); ?>">
<label class="gn-sr-only" for="gn-search-input"><?php esc_html_e( 'جستجو', 'golitheme' ); ?></label>
<input id="gn-search-input" class="gn-search-input" type="search" name="s" placeholder="<?php esc_attr_e( 'جستجو...', 'golitheme' ); ?>" autocomplete="off" aria-autocomplete="list" aria-expanded="false" aria-owns="gn-search-listbox" role="combobox" />
<div id="gn-search-popover" class="gn-search-popover" role="region" aria-live="polite" hidden>
<div class="gn-search-section" aria-label="<?php esc_attr_e( 'محصولات', 'golitheme' ); ?>">
<ul id="gn-search-listbox" class="gn-search-list" role="listbox"></ul>
</div>
<div class="gn-search-section" aria-label="<?php esc_attr_e( 'دوره‌ها', 'golitheme' ); ?>">
<ul id="gn-search-courses" class="gn-search-list" role="listbox"></ul>
</div>
</div>
</form>
<nav class="gn-primary-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'golitheme' ); ?>">
<?php
wp_nav_menu( [
'theme_location' => 'primary',
'menu_id'        => 'primary-menu',
'container'      => false,
] );
?>
</nav>
<a class="gn-account" href="<?php echo esc_url( function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'myaccount' ) ) : wp_login_url() ); ?>"><?php esc_html_e( 'حساب', 'golitheme' ); ?></a>
</div>
<div id="gn-overlay" class="gn-overlay" hidden></div>
<aside id="gn-mobile-panel" class="gn-mobile-panel" aria-hidden="true" tabindex="-1">
<button class="gn-panel-close" aria-label="<?php esc_attr_e( 'بستن منو', 'golitheme' ); ?>">×</button>
<nav class="gn-mobile-nav" aria-label="<?php esc_attr_e( 'منوی موبایل', 'golitheme' ); ?>">
<?php
wp_nav_menu( [
'theme_location' => 'primary',
'menu_id'        => 'mobile-menu',
'container'      => false,
] );
?>
</nav>
<form class="gn-search-mobile" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
<label class="gn-sr-only" for="gn-search-input-mobile"><?php esc_html_e( 'جستجو', 'golitheme' ); ?></label>
<input id="gn-search-input-mobile" class="gn-search-input" type="search" name="s" placeholder="<?php esc_attr_e( 'جستجو...', 'golitheme' ); ?>" />
</form>
</aside>
</header>
