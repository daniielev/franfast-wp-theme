<?php
/**
 * Translations and language helpers.
 *
 * Strings live in /lang/{code}.json. Keys are dot paths: ff_t( 'footer.trust_cta' ).
 * Missing keys fall back to English, then to an empty string (never a PHP notice).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read and decode a JSON file once per request.
 */
function ff_read_json( string $file ): array {
	static $cache = array();
	if ( isset( $cache[ $file ] ) ) {
		return $cache[ $file ];
	}
	$data = array();
	if ( is_readable( $file ) ) {
		$decoded = json_decode( (string) file_get_contents( $file ), true );
		if ( is_array( $decoded ) ) {
			$data = $decoded;
		} elseif ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( 'FranFast theme: invalid JSON in ' . $file . ' (' . json_last_error_msg() . ')' );
		}
	}
	$cache[ $file ] = $data;
	return $data;
}

/**
 * Get a nested value with a dot path.
 */
function ff_array_get( $data, string $path, $fallback = null ) {
	foreach ( explode( '.', $path ) as $segment ) {
		if ( ! is_array( $data ) || ! array_key_exists( $segment, $data ) ) {
			return $fallback;
		}
		$data = $data[ $segment ];
	}
	return $data;
}

/**
 * Site configuration (config.json).
 */
function ff_config( ?string $path = null, $fallback = null ) {
	$config = ff_read_json( FF_DIR . '/config.json' );
	return null === $path ? $config : ff_array_get( $config, $path, $fallback );
}

function ff_languages(): array {
	$langs = ff_config( 'languages', array( 'en' ) );
	return is_array( $langs ) && $langs ? array_values( $langs ) : array( 'en' );
}

function ff_default_lang(): string {
	return (string) ff_config( 'default_language', 'en' );
}

function ff_is_lang( $lang ): bool {
	return is_string( $lang ) && in_array( $lang, ff_languages(), true );
}

/**
 * All strings for one language.
 */
function ff_strings( string $lang ): array {
	if ( ! ff_is_lang( $lang ) ) {
		return array();
	}
	return ff_read_json( FF_DIR . '/lang/' . $lang . '.json' );
}

/**
 * Current language.
 * Order: ?ff_lang query var (set by our rewrites) → language of the queried post → first URL segment → default.
 */
function ff_lang(): string {
	global $wp_query;

	if ( isset( $wp_query ) ) {
		$var = get_query_var( 'ff_lang' );
		if ( ff_is_lang( $var ) ) {
			return $var;
		}
		if ( is_singular( 'post' ) ) {
			return ff_post_lang( get_queried_object_id() );
		}
	}

	$path      = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	$home_path = trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
	if ( '' !== $home_path && 0 === strpos( $path, $home_path ) ) {
		$path = trim( substr( $path, strlen( $home_path ) ), '/' );
	}
	$first = explode( '/', $path )[0] ?? '';
	if ( $first !== ff_default_lang() && ff_is_lang( $first ) ) {
		return $first;
	}
	return ff_default_lang();
}

/**
 * Translate a key. Returns string|array (lists are allowed). Falls back to the default language.
 */
function ff_t( string $key, ?string $lang = null ) {
	$lang  = $lang ?? ff_lang();
	$value = ff_array_get( ff_strings( $lang ), $key );
	if ( null === $value && $lang !== ff_default_lang() ) {
		$value = ff_array_get( ff_strings( ff_default_lang() ), $key );
	}
	return $value ?? '';
}

/**
 * Echo an escaped string.
 */
function ff_e( string $key, ?string $lang = null ): void {
	$value = ff_t( $key, $lang );
	echo esc_html( is_string( $value ) ? $value : '' );
}

/**
 * Escaped string for attributes.
 */
function ff_attr( string $key, ?string $lang = null ): string {
	$value = ff_t( $key, $lang );
	return esc_attr( is_string( $value ) ? $value : '' );
}

/**
 * Language meta (_meta block of each JSON).
 */
function ff_lang_meta( string $lang, string $field ): string {
	return (string) ff_array_get( ff_strings( $lang ), '_meta.' . $field, $lang );
}

/**
 * Slug of a route in a language. Example: ff_route_slug( 'about', 'es' ) → 'sobre-nosotros'.
 */
function ff_route_slug( string $route, string $lang ): ?string {
	$slug = ff_array_get( ff_strings( $lang ), 'routes.' . $route );
	if ( null === $slug && $lang !== ff_default_lang() ) {
		return null; // Route not translated: no URL in that language.
	}
	return is_string( $slug ) ? trim( $slug, '/' ) : null;
}

/**
 * Home URL for a language: / or /es/.
 */
function ff_home_url( ?string $lang = null ): string {
	$lang = $lang ?? ff_lang();
	return $lang === ff_default_lang() ? home_url( '/' ) : home_url( '/' . $lang . '/' );
}

/**
 * URL of a route in a language, with optional #hash.
 */
function ff_url( string $route, ?string $lang = null, string $hash = '' ): string {
	$lang = $lang ?? ff_lang();
	if ( 'blog' === $route ) { // The blog lives in the "blog" category: /category/blog/, /es/category/blog/.
		$url = ff_home_url( $lang ) . trim( (string) get_option( 'category_base' ) ?: 'category', '/' ) . '/' . ff_blog_category() . '/';
		return $hash ? $url . '#' . rawurlencode( $hash ) : $url;
	}
	$slug = ff_route_slug( $route, $lang );
	if ( null === $slug ) {
		$lang = ff_default_lang();
		$slug = (string) ff_route_slug( $route, $lang );
	}
	$url = '' === $slug ? ff_home_url( $lang ) : ff_home_url( $lang ) . $slug . '/';
	return $hash ? $url . '#' . rawurlencode( $hash ) : $url;
}

/**
 * Route key of the current request (home, about, blog...) or null.
 */
function ff_current_route(): ?string {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_category( ff_blog_category() ) ) {
		return 'blog';
	}
	if ( is_page() ) {
		$slug = (string) get_post_field( 'post_name', get_queried_object_id() );
		$key  = array_search( $slug, (array) ff_t( 'routes', ff_default_lang() ), true );
		return false === $key ? null : (string) $key;
	}
	return null;
}

/**
 * Prefix an English URL with a language folder: /category/x/ → /es/category/x/.
 */
function ff_localize_url( string $url, string $lang ): string {
	if ( $lang === ff_default_lang() ) {
		return $url;
	}
	$home = home_url( '/' );
	if ( 0 !== strpos( $url, $home ) ) {
		return $url;
	}
	$rest = substr( $url, strlen( $home ) );
	foreach ( ff_languages() as $code ) { // Avoid double prefixes.
		if ( 0 === strpos( $rest, $code . '/' ) ) {
			$rest = substr( $rest, strlen( $code ) + 1 );
			break;
		}
	}
	return $home . $lang . '/' . $rest;
}

/**
 * Slug of the category that acts as the blog home (config.json → blog_category).
 */
function ff_blog_category(): string {
	return sanitize_title( (string) ff_config( 'blog_category', 'blog' ) );
}
