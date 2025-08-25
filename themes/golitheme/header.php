<!doctype html>
<html <?php language_attributes(); ?> dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'gn-body' ); ?>>
<?php wp_body_open(); ?>
<header class="gn-site-header">
<div class="gn-container">
<a class="gn-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
<nav class="gn-primary-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'golitheme' ); ?>">
<?php
wp_nav_menu( [
'theme_location' => 'primary',
'menu_id'        => 'primary-menu',
'container'      => false,
] );
?>
</nav>
</div>
</header>
