<?php
add_filter( 'acf/settings/save_json', 'gn_acf_json_save_point' );
function gn_acf_json_save_point( $path ) {
$path = get_stylesheet_directory() . '/acf-json';
if ( ! is_dir( $path ) ) {
wp_mkdir_p( $path );
}
return $path;
}

add_filter( 'acf/settings/load_json', 'gn_acf_json_load_point' );
function gn_acf_json_load_point( $paths ) {
$paths[] = get_stylesheet_directory() . '/acf-json';
return $paths;
}
