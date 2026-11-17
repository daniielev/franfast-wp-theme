<?php
/**
 * Legal pages support (config.json → legal, legal_redirects).
 *  - Serves downloadable files declared in config.json → legal[].file (e.g. /dpa.pdf → assets/docs/…pdf), versioned in the theme.
 *  - 301 redirects for old legal URLs (config.json → legal_redirects).
 *  - Heading anchors + table of contents for content pages rendered by page.php.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Path of the current request relative to the site root, e.g. "/dpa.pdf" or "/caup/".
 */
function ff_request_path(): string {
	$path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ); // phpcs:ignore
	$base = rtrim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
	if ( '' !== $base && 0 === strpos( $path, $base ) ) {
		$path = substr( $path, strlen( $base ) );
	}
	return '/' . ltrim( $path, '/' );
}

/**
 * Downloads and redirects run before WordPress queries the database (no 404, no theme output).
 */
add_action(
	'parse_request',
	static function (): void {
		$path = ff_request_path();

		// Old URL → new URL (301). Trailing slash optional on the old URL.
		foreach ( (array) ff_config( 'legal_redirects', array() ) as $from => $to ) {
			if ( untrailingslashit( $path ) === untrailingslashit( (string) $from ) ) {
				wp_safe_redirect( home_url( (string) $to ), 301 );
				exit;
			}
		}

		// Theme-hosted files (e.g. /dpa.pdf).
		foreach ( (array) ff_config( 'legal', array() ) as $item ) {
			if ( empty( $item['file'] ) || $path !== $item['path'] ) {
				continue;
			}
			$file = FF_DIR . '/' . ltrim( (string) $item['file'], '/' );
			if ( ! is_readable( $file ) ) {
				return; // Missing file → normal WordPress 404.
			}
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true ); // Keep page caches (WP Fastest Cache) away from the binary response.
			}
			while ( ob_get_level() ) {
				ob_end_clean();
			}
			$name = (string) ( $item['download_name'] ?? basename( $file ) );
			status_header( 200 );
			header( 'Content-Type: application/pdf' );
			header( 'Content-Disposition: ' . ( empty( $item['download'] ) ? 'inline' : 'attachment' ) . '; filename="' . str_replace( '"', '', $name ) . '"' );
			header( 'Content-Length: ' . filesize( $file ) );
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', (int) filemtime( $file ) ) . ' GMT' );
			header( 'Cache-Control: public, max-age=86400' );
			header( 'X-Content-Type-Options: nosniff' );
			if ( 'HEAD' !== ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) { // phpcs:ignore
				readfile( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			}
			exit;
		}
	},
	0
);

/**
 * Adds id="…" to H2s without one and returns [content, toc items].
 *
 * @param string $content Rendered post content.
 * @return array{0:string,1:array<int,array{id:string,text:string}>}
 */
function ff_content_toc( string $content ): array {
	$toc  = array();
	$used = array();
	$content = preg_replace_callback(
		'/<h2(\s[^>]*)?>(.*?)<\/h2>/is',
		static function ( array $m ) use ( &$toc, &$used ): string {
			$attrs = $m[1] ?? '';
			$text  = trim( wp_strip_all_tags( $m[2] ) );
			if ( preg_match( '/\sid=["\']([^"\']+)["\']/', $attrs, $idm ) ) {
				$id = $idm[1];
			} else {
				$id = sanitize_title( preg_replace( '/^\d+(\.\d+)*\.?\s*/', '', $text ) ) ?: 'section';
				$base = $id;
				$n    = 2;
				while ( isset( $used[ $id ] ) ) {
					$id = $base . '-' . $n++;
				}
				$attrs .= ' id="' . esc_attr( $id ) . '"';
			}
			$used[ $id ] = true;
			$toc[]       = array( 'id' => $id, 'text' => $text );
			return '<h2' . $attrs . '>' . $m[2] . '</h2>';
		},
		$content
	);
	return array( (string) $content, $toc );
}
