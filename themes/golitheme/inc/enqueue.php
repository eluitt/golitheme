<?php
add_action( 'wp_enqueue_scripts', 'gn_enqueue_assets' );
function gn_enqueue_assets() {
$theme   = wp_get_theme();
$version = $theme->get( 'Version' );

$fonts_rel = '/assets/styles/fonts.css';
$fonts_abs = get_template_directory() . $fonts_rel;
$fonts_uri = get_template_directory_uri() . $fonts_rel;
wp_enqueue_style( 'gn-fonts', $fonts_uri, [], file_exists( $fonts_abs ) ? filemtime( $fonts_abs ) : $version );

$css_rel = '/assets/styles/dist.css';
$css_abs = get_template_directory() . $css_rel;
$css_uri = get_template_directory_uri() . $css_rel;
wp_enqueue_style( 'gn-frontend', $css_uri, [ 'gn-fonts' ], file_exists( $css_abs ) ? filemtime( $css_abs ) : $version, 'all' );

$js_rel = '/assets/scripts/app.js';
$js_abs = get_template_directory() . $js_rel;
if ( file_exists( $js_abs ) ) {
$js_uri = get_template_directory_uri() . $js_rel;
wp_enqueue_script( 'gn-frontend', $js_uri, [], filemtime( $js_abs ), true );
add_filter( 'script_loader_tag', function ( $tag, $handle, $src ) {
if ( 'gn-frontend' === $handle ) {
return '<script src="' . esc_url( $src ) . '" defer></script>';
}
return $tag;
}, 10, 3 );
		wp_localize_script( 'gn-frontend', 'gnSearch', [
			'restUrl' => esc_url_raw( rest_url( 'gn/v1/search' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'isHome'  => true,
			'i18n'    => [
				'products'    => __( 'محصولات', 'golitheme' ),
				'courses'     => __( 'دوره‌ها', 'golitheme' ),
				'viewAll'     => __( 'مشاهده همه', 'golitheme' ),
				'noResults'   => __( 'موردی یافت نشد', 'golitheme' ),
				'placeholder' => __( 'جستجو...', 'golitheme' ),
			],
		] );
}
}
