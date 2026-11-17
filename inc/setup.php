<?php
/**
 * Theme supports, assets and <head> cleanup.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function (): void {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_image_size( 'ff-card', 720, 405, true );
	}
);

/**
 * Asset URL with a cache-busting version from the file time.
 */
function ff_asset( string $path ): string {
	return FF_URI . '/assets/' . ltrim( $path, '/' );
}

function ff_asset_version( string $path ): string {
	$file = FF_DIR . '/assets/' . ltrim( $path, '/' );
	return is_readable( $file ) ? (string) filemtime( $file ) : FF_VERSION;
}

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		wp_enqueue_style( 'franfast', ff_asset( 'css/main.css' ), array(), ff_asset_version( 'css/main.css' ) );
		if ( ff_config( 'motion.enabled', true ) ) { // Hover effects + entrance/scroll animations (config.json → motion.enabled).
			wp_enqueue_style( 'franfast-motion', ff_asset( 'css/motion.css' ), array( 'franfast' ), ff_asset_version( 'css/motion.css' ) );
		}
		wp_enqueue_script(
			'franfast',
			ff_asset( 'js/main.js' ),
			array(),
			ff_asset_version( 'js/main.js' ),
			array( 'strategy' => 'defer', 'in_footer' => true )
		);

		// Block styles only where post content is rendered (posts, legal pages); not on JSON-driven pages.
		if ( ! ( is_singular() && null === ff_current_route() ) ) {
			wp_dequeue_style( 'wp-block-library' );
		}
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'classic-theme-styles' );
		wp_dequeue_style( 'global-styles' );
	},
	20
);

/**
 * Preload the two fonts used above the fold.
 */
add_action(
	'wp_head',
	static function (): void {
		foreach ( array( 'ubuntu-sans-latin-400-normal.woff2', 'ubuntu-sans-latin-500-normal.woff2' ) as $font ) {
			printf(
				'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
				esc_url( ff_asset( 'fonts/' . $font ) )
			);
		}
	},
	2
);

/**
 * Head cleanup: nothing the site does not use.
 */
add_action(
	'init',
	static function (): void {
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'rest_output_link_wp_head' );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
		remove_action( 'wp_head', 'rel_canonical' );            // inc/seo.php prints canonical + hreflang.
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'emoji_svg_url', '__return_false' );
		remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
		remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
	}
);

/**
 * <html lang> follows the active language.
 */
add_filter(
	'language_attributes',
	static function ( string $output ): string {
		$lang = str_replace( '_', '-', ff_lang_meta( ff_lang(), 'locale' ) );
		return (string) preg_replace( '/lang="[^"]*"/', 'lang="' . esc_attr( $lang ) . '"', $output );
	}
);

// Tracking tags (GTM, Google Ads, Salesforce chat, Pardot resize) are managed in the HFCM plugin.

/**
 * Inline SVG icons used by the templates.
 */
function ff_icon( string $name, int $size = 20 ): string {
	$paths = array(
		'shield'   => '<path d="M12 3l8 3v6c0 4.4-3.4 8-8 9-4.6-1-8-4.6-8-9V6z"/><path d="M9 12l2 2 4-4"/>',
		'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'menu'     => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'    => '<path d="M6 6l12 12M18 6L6 18"/>',
		'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
		'phone'    => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
		'pin'      => '<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
		'search'   => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>',
		'external' => '<path d="M14 5h5v5M19 5l-8 8M18 14v4a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h4"/>',
		'check'       => '<path d="M5 12l5 5L20 7"/>',
		'play'        => '<path d="M8 5l11 7-11 7z"/>',
		'chevron'     => '<path d="M6 9l6 6 6-6"/>',
		'development' => '<circle cx="9" cy="8" r="3.5"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><path d="M19 8v6M16 11h6"/>',
		'opening'     => '<path d="M4 9l1.5-5h13L20 9"/><path d="M4 9c0 1.7 1.3 3 3 3s3-1.3 3-3c0 1.7 1.3 3 3 3s3-1.3 3-3c0 1.7 1.3 3 3 3"/><path d="M5 12v8h14v-8"/><path d="M10 20v-5h4v5"/>',
		'operations'  => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1"/><path d="M9 13l2 2 4-4"/>',
		'portal'      => '<path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/>',
		'royalty'     => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6M9 12h6M9 16h3"/>',
		'ai'          => '<circle cx="12" cy="5" r="2"/><circle cx="5" cy="19" r="2"/><circle cx="19" cy="19" r="2"/><path d="M12 7v5M12 12l-5.5 5.5M12 12l5.5 5.5"/>',
	);
	$fill = array(
		'linkedin' => '<path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9.75h4v11H3zm6.5 0h3.8v1.5h.06c.53-1 1.83-2.06 3.77-2.06 4.03 0 4.77 2.65 4.77 6.1v5.46h-4v-4.84c0-1.16-.02-2.64-1.61-2.64-1.61 0-1.86 1.26-1.86 2.56v4.92h-4z"/>',
		'youtube'  => '<path d="M22.5 7.2a2.8 2.8 0 0 0-2-2C18.7 4.7 12 4.7 12 4.7s-6.7 0-8.5.5a2.8 2.8 0 0 0-2 2A29 29 0 0 0 1 12a29 29 0 0 0 .5 4.8 2.8 2.8 0 0 0 2 2c1.8.5 8.5.5 8.5.5s6.7 0 8.5-.5a2.8 2.8 0 0 0 2-2A29 29 0 0 0 23 12a29 29 0 0 0-.5-4.8zM9.8 15.1V8.9L15.5 12z"/>',
		'facebook' => '<path d="M14 8.5V6.8c0-.8.2-1.3 1.4-1.3H17V2.6A21 21 0 0 0 14.6 2.5C12.2 2.5 10.6 4 10.6 6.6v1.9H8V11.7h2.6v9.8H14v-9.8h2.7l.4-3.2z"/>',
	);
	if ( isset( $fill[ $name ] ) ) {
		return sprintf( '<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">%2$s</svg>', $size, $fill[ $name ] );
	}
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return sprintf(
		'<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%2$s</svg>',
		$size,
		$paths[ $name ]
	);
}

/**
 * Image source from config: full URL, site path (/wp-content/…) or a path inside assets/.
 */
function ff_img_src( string $src ): string {
	if ( preg_match( '#^https?://#', $src ) ) {
		return $src;
	}
	// Site path (e.g. /wp-content/uploads/…): follows the current domain (local, staging, .ai).
	return 0 === strpos( $src, '/' ) ? home_url( $src ) : ff_asset( $src );
}

/**
 * Link for a nav/footer item from config.json: { route, hash } or { url }.
 */
function ff_item_url( array $item ): string {
	if ( ! empty( $item['url'] ) ) {
		return (string) $item['url'];
	}
	return ff_url( (string) ( $item['route'] ?? 'home' ), null, (string) ( $item['hash'] ?? '' ) );
}

/**
 * Is a nav item the current page? (Anchors on the home page never are.)
 */
function ff_item_is_current( array $item ): bool {
	if ( ! empty( $item['hash'] ) || empty( $item['route'] ) ) {
		return false;
	}
	$current = ff_current_route();
	if ( $current === $item['route'] ) {
		return true;
	}
	// Blog posts and archives belong to "blog".
	return 'blog' === $item['route'] && ( is_singular( 'post' ) || is_category() || is_tag() || is_search() );
}

/**
 * FranFast logo as inline SVG (header). Inline so motion.css can animate the mark:
 * the blue diamond and the two white "FF" triangles get their own classes.
 */
function ff_logo_svg( string $class = '' ): string {
	static $svg = null;
	if ( null === $svg ) {
		$raw = (string) @file_get_contents( FF_DIR . '/assets/img/franfast-logo.svg' ); // phpcs:ignore
		$raw = (string) preg_replace( '#<metadata>.*?</metadata>#s', '', $raw );
		$raw = (string) preg_replace( '#<\?xml.*?\?>#s', '', $raw );
		$tri = 0;
		$raw = (string) preg_replace_callback(
			'#<path class="st0"#',
			static function () use ( &$tri ) {
				++$tri;
				return '<path class="st0 ff-logo__tri ff-logo__tri--' . ( 1 === $tri ? 'a' : 'b' ) . '"';
			},
			$raw
		);
		$raw = (string) preg_replace( '#<path class="st1" d="M138\.71#', '<path class="st1 ff-logo__mark" d="M138.71', $raw, 1 );
		$raw = (string) preg_replace( '#\s+xmlns:c2pa="[^"]*"#', '', $raw );
		// Unique style class names so they cannot clash with page CSS.
		$raw = str_replace( array( '.st0{', '.st1{', '.st2{', 'class="st0', 'class="st1', 'class="st2' ), array( '.ffl0{', '.ffl1{', '.ffl2{', 'class="ffl0', 'class="ffl1', 'class="ffl2' ), $raw );
		$svg = trim( $raw );
	}
	return $class ? (string) preg_replace( '#^<svg #', '<svg class="' . esc_attr( $class ) . '" width="144" height="44" focusable="false" ', $svg, 1 ) : $svg;
}

