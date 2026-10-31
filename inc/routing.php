<?php
/**
 * Language routing without plugins.
 *
 * English keeps today's URLs (/, /about-us/, /our-blog/, /blog/{slug}/, /category/{slug}/).
 * Other languages live under a folder: /es/, /es/sobre-nosotros/, /es/blog/{slug}/, /es/category/{slug}/.
 * Translated page slugs come from routes{} in lang/*.json and map to the English WordPress page,
 * so each page exists once in WordPress and its template renders the copy for the active language.
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'query_vars',
	static function ( array $vars ): array {
		$vars[] = 'ff_lang';
		return $vars;
	}
);

/**
 * Rewrite rules for every non-default language.
 */
function ff_register_rewrites(): void {
	$default   = ff_default_lang();
	$en_routes = (array) ff_t( 'routes', $default );

	foreach ( ff_languages() as $lang ) {
		if ( $lang === $default ) {
			continue;
		}
		$p   = preg_quote( $lang, '#' );
		$tail = '&ff_lang=' . $lang;

		// Search: /es/search/term/ (the /es/?s=term form also works).
		add_rewrite_rule( "^{$p}/search/(.+?)/page/?([0-9]{1,})/?$", 'index.php?s=$matches[1]&paged=$matches[2]' . $tail, 'top' );
		add_rewrite_rule( "^{$p}/search/(.+?)/?$", 'index.php?s=$matches[1]' . $tail, 'top' );

		// Categories and tags.
		foreach ( array( 'category' => 'category_name', 'tag' => 'tag' ) as $base => $var ) {
			add_rewrite_rule( "^{$p}/{$base}/(.+?)/page/?([0-9]{1,})/?$", "index.php?{$var}=\$matches[1]&paged=\$matches[2]" . $tail, 'top' );
			add_rewrite_rule( "^{$p}/{$base}/(.+?)/?$", "index.php?{$var}=\$matches[1]" . $tail, 'top' );
		}

		// Translated page routes → English WordPress page.
		foreach ( (array) ff_t( 'routes', $lang ) as $route => $slug ) {
			$slug    = trim( (string) $slug, '/' );
			$en_slug = isset( $en_routes[ $route ] ) ? trim( (string) $en_routes[ $route ], '/' ) : '';
			if ( '' === $slug || '' === $en_slug ) {
				continue;
			}
			$s = preg_quote( $slug, '#' );
			add_rewrite_rule( "^{$p}/{$s}/page/?([0-9]{1,})/?$", "index.php?pagename={$en_slug}&paged=\$matches[1]" . $tail, 'top' );
			add_rewrite_rule( "^{$p}/{$s}/?$", "index.php?pagename={$en_slug}" . $tail, 'top' );
		}

		// Single posts follow the English structure /%category%/%postname%/ under the language folder:
		// /es/blog/{slug}/, /es/news/{slug}/.
		add_rewrite_rule( '^' . $p . '/(?!(?:category|tag|search|page)/)[^/]+/([^/]+)/?$', 'index.php?name=$matches[1]' . $tail, 'top' );

		// Language home: /es/.
		add_rewrite_rule( "^{$p}/?$", 'index.php?ff_lang=' . $lang, 'top' );
	}
}
add_action( 'init', 'ff_register_rewrites' );

/**
 * Flush rewrite rules only when the routes in the JSON files change.
 */
add_action(
	'init',
	static function (): void {
		$routes = array();
		foreach ( ff_languages() as $lang ) {
			$routes[ $lang ] = ff_t( 'routes', $lang );
		}
		$hash = md5( (string) wp_json_encode( array( $routes, FF_VERSION ) ) );
		if ( get_option( 'ff_rewrite_hash' ) !== $hash ) {
			flush_rewrite_rules( false );
			update_option( 'ff_rewrite_hash', $hash, true );
		}
	},
	99
);

add_action( 'after_switch_theme', static function (): void { delete_option( 'ff_rewrite_hash' ); } );

/**
 * /es/ alone → the static front page.
 */
add_filter(
	'request',
	static function ( array $qv ): array {
		if ( empty( $qv['ff_lang'] ) ) {
			return $qv;
		}
		$others = array_diff_key( $qv, array( 'ff_lang' => true ) );
		if ( empty( $others ) && 'page' === get_option( 'show_on_front' ) ) {
			$front = (int) get_option( 'page_on_front' );
			if ( $front ) {
				$qv['page_id'] = $front;
			}
		}
		return $qv;
	}
);

/**
 * Keep WordPress from "correcting" language URLs back to English ones.
 */
add_filter(
	'redirect_canonical',
	static function ( $redirect ) {
		return get_query_var( 'ff_lang' ) ? false : $redirect;
	}
);

/**
 * Redirects:
 *  - ?ff_lang=xx on an English URL → the language home (avoids duplicate content).
 *  - A post opened under the wrong language folder → its real URL.
 */
add_action(
	'template_redirect',
	static function (): void {
		if ( isset( $_GET['ff_lang'] ) && ff_is_lang( $_GET['ff_lang'] ) && 0 === count( array_diff_key( $_GET, array( 'ff_lang' => 1 ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
			if ( '' === $path || trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' ) === $path ) {
				wp_safe_redirect( ff_home_url( sanitize_key( $_GET['ff_lang'] ) ), 301 ); // phpcs:ignore
				exit;
			}
		}

		// The old posts page (/our-blog/, /es/blog/, /pt/blog/) moved to the blog category.
		if ( is_home() && ! is_front_page() ) {
			$paged = max( 1, (int) get_query_var( 'paged' ) );
			$url   = ff_url( 'blog', get_query_var( 'ff_lang' ) ?: ff_default_lang() );
			wp_safe_redirect( $paged > 1 ? $url . 'page/' . $paged . '/' : $url, 301 );
			exit;
		}

		if ( is_singular( 'post' ) && ! is_preview() ) {
			$id       = get_queried_object_id();
			$url_lang = get_query_var( 'ff_lang' ) ?: ff_default_lang();
			$req_path  = trailingslashit( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) );
			$real_path = trailingslashit( (string) wp_parse_url( (string) get_permalink( $id ), PHP_URL_PATH ) );
			if ( ff_post_lang( $id ) !== $url_lang || ( $url_lang !== ff_default_lang() && $req_path !== $real_path ) ) {
				wp_safe_redirect( get_permalink( $id ), 301 );
				exit;
			}
		}
	}
);

/**
 * Language of a post (posts without the field are English).
 */
function ff_post_lang( int $post_id ): string {
	$lang = get_post_meta( $post_id, '_ff_lang', true );
	return ff_is_lang( $lang ) ? $lang : ff_default_lang();
}

/**
 * meta_query that limits posts to a language.
 */
function ff_lang_meta_query( string $lang ): array {
	if ( $lang === ff_default_lang() ) {
		return array(
			'relation' => 'OR',
			array( 'key' => '_ff_lang', 'compare' => 'NOT EXISTS' ),
			array( 'key' => '_ff_lang', 'value' => $lang ),
		);
	}
	return array( array( 'key' => '_ff_lang', 'value' => $lang ) );
}

/**
 * Listings, archives, search and feeds only show posts in the active language.
 * Search is limited to posts (page copy lives in the JSON files, not in post content).
 */
add_action(
	'pre_get_posts',
	static function ( WP_Query $q ): void {
		if ( is_admin() || ! $q->is_main_query() ) {
			return;
		}
		if ( ! ( $q->is_home() || $q->is_archive() || $q->is_search() || $q->is_feed() ) ) {
			return;
		}
		$lang = $q->get( 'ff_lang' );
		$lang = ff_is_lang( $lang ) ? $lang : ff_default_lang();

		$meta   = (array) $q->get( 'meta_query' );
		$meta[] = ff_lang_meta_query( $lang );
		$q->set( 'meta_query', $meta );

		if ( $q->is_search() ) {
			$q->set( 'post_type', 'post' );
		}
	}
);

/**
 * Post permalinks for translated posts: /es/blog/{slug}/.
 */
add_filter(
	'post_link',
	static function ( string $url, WP_Post $post ): string {
		$lang = ff_post_lang( $post->ID );
		if ( $lang === ff_default_lang() || '' === $post->post_name || 'publish' !== $post->post_status ) {
			return $url;
		}
		return ff_localize_url( $url, $lang ); // /news/{slug}/ → /es/news/{slug}/.
	},
	10,
	2
);

/**
 * Category and tag links follow the language being viewed.
 * ff_raw_term_links( true ) temporarily disables this to get the English link.
 */
function ff_raw_term_links( ?bool $set = null ): bool {
	static $raw = false;
	if ( null !== $set ) {
		$raw = $set;
	}
	return $raw;
}

function ff_filter_term_link( string $url, $term, string $taxonomy ): string {
	if ( ff_raw_term_links() || is_admin() || wp_doing_ajax() || ! in_array( $taxonomy, array( 'category', 'post_tag' ), true ) ) {
		return $url;
	}
	$lang = ff_lang();
	return $lang === ff_default_lang() ? $url : ff_localize_url( $url, $lang );
}
add_filter( 'term_link', 'ff_filter_term_link', 10, 3 );

/**
 * Published translations of a post: [ lang => post_id ].
 */
function ff_post_translations( int $post_id ): array {
	$original = (int) get_post_meta( $post_id, '_ff_translation_of', true );
	$original = $original ?: $post_id;

	$ids   = get_posts(
		array(
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'fields'           => 'ids',
			'numberposts'      => 20,
			'meta_key'         => '_ff_translation_of', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'       => $original,            // phpcs:ignore WordPress.DB.SlowDBQuery
			'suppress_filters' => true,
		)
	);
	$ids[] = $original;
	$ids[] = $post_id;

	$map = array();
	foreach ( array_unique( array_map( 'intval', $ids ) ) as $id ) {
		if ( 'publish' !== get_post_status( $id ) ) {
			continue;
		}
		$lang = ff_post_lang( $id );
		if ( ! isset( $map[ $lang ] ) || $id === $post_id ) {
			$map[ $lang ] = $id;
		}
	}
	return $map;
}

/**
 * URL of the current view in another language, or null when it does not exist.
 * Used by the language switcher and by hreflang.
 */
function ff_alternate_url( string $lang ): ?string {
	$route = ff_current_route();
	if ( null !== $route ) {
		if ( null === ff_route_slug( $route, $lang ) && $lang !== ff_default_lang() ) {
			return null;
		}
		return ff_url( $route, $lang );
	}

	if ( is_singular( 'post' ) ) {
		$map = ff_post_translations( get_queried_object_id() );
		return isset( $map[ $lang ] ) ? get_permalink( $map[ $lang ] ) : null;
	}

	if ( is_category() || is_tag() ) {
		$term = get_queried_object();
		if ( ! $term instanceof WP_Term || ! ff_term_has_posts( $term, $lang ) ) {
			return null;
		}
		ff_raw_term_links( true );
		$url = get_term_link( $term );
		ff_raw_term_links( false );
		return is_wp_error( $url ) ? null : ff_localize_url( $url, $lang );
	}

	return null;
}

/**
 * Does a term have published posts in a language? (Avoids hreflang to empty archives.)
 */
function ff_term_has_posts( WP_Term $term, string $lang ): bool {
	$ids = get_posts(
		array(
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'fields'           => 'ids',
			'numberposts'      => 1,
			'no_found_rows'    => true,
			'tax_query'        => array( array( 'taxonomy' => $term->taxonomy, 'terms' => $term->term_id ) ), // phpcs:ignore
			'meta_query'       => ff_lang_meta_query( $lang ), // phpcs:ignore
			'suppress_filters' => true,
		)
	);
	return ! empty( $ids );
}
