<?php
add_action( 'after_setup_theme', 'gn_setup_theme' );
function gn_setup_theme() {
load_theme_textdomain( 'golitheme', get_template_directory() . '/languages' );
add_theme_support( 'title-tag' );
add_theme_support( 'post-thumbnails' );
add_theme_support( 'responsive-embeds' );
add_theme_support( 'editor-styles' );
add_theme_support( 'align-wide' );
add_theme_support( 'automatic-feed-links' );
add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ] );

register_nav_menus( [
'primary' => __( 'Primary Menu', 'golitheme' ),
] );

add_editor_style( 'assets/styles/dist.css' );
}
