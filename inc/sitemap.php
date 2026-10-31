<?php
/**
 * Native WordPress sitemap (/wp-sitemap.xml) extended with translated pages.
 * English pages and all posts (with their /es/ and /pt/ permalinks) come from the core providers.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	static function (): void {
		if ( ! class_exists( 'WP_Sitemaps_Provider' ) ) {
			return;
		}

		/**
		 * Provider "languages": /es/, /es/sobre-nosotros/, /pt/... for every translated route.
		 */
		final class FF_Sitemaps_Languages extends WP_Sitemaps_Provider {
			public function __construct() {
				$this->name        = 'languages';
				$this->object_type = 'languages';
			}

			public function get_url_list( $page_num, $object_subtype = '' ) {
				$urls = array();
				foreach ( ff_languages() as $lang ) {
					if ( $lang === ff_default_lang() ) {
						continue;
					}
					foreach ( array_keys( (array) ff_t( 'routes', $lang ) ) as $route ) {
						if ( null === ff_route_slug( $route, $lang ) || in_array( $route, (array) ff_config( 'noindex_routes', array() ), true ) ) {
							continue;
						}
						$page_id = 'home' === $route ? (int) get_option( 'page_on_front' ) : ff_route_page_id( $route );
						if ( 'blog' === $route ) {
							$page_id = 0; // Category archive, always listed.
						} elseif ( 'home' !== $route && ! $page_id ) {
							continue; // The English page does not exist yet.
						}
						$entry = array( 'loc' => ff_url( $route, $lang ) );
						if ( $page_id ) {
							$entry['lastmod'] = get_post_modified_time( DATE_W3C, true, $page_id );
						}
						$urls[] = $entry;
					}
				}
				return $urls;
			}

			public function get_max_num_pages( $object_subtype = '' ) {
				return 1;
			}
		}

		wp_register_sitemap_provider( 'languages', new FF_Sitemaps_Languages() );
	}
);

/**
 * ID of the English WordPress page behind a route (0 if missing).
 */
function ff_route_page_id( string $route ): int {
	$slug = ff_route_slug( $route, ff_default_lang() );
	if ( ! $slug ) {
		return 0;
	}
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	return $page && 'publish' === $page->post_status ? (int) $page->ID : 0;
}

// No author sitemaps (single corporate author, thin pages).
add_filter(
	'wp_sitemaps_add_provider',
	static function ( $provider, string $name ) {
		return 'users' === $name ? false : $provider;
	},
	10,
	2
);

// noindex routes (thank-you) and noindex posts stay out of the core sitemap.
add_filter(
	'wp_sitemaps_posts_query_args',
	static function ( array $args, string $post_type ): array {
		$exclude = array();
		if ( 'page' === $post_type ) {
			if ( (int) get_option( 'page_for_posts' ) ) {
				$exclude[] = (int) get_option( 'page_for_posts' ); // Redirects to /category/blog/.
			}
			foreach ( (array) ff_config( 'noindex_routes', array() ) as $route ) {
				$id = ff_route_page_id( (string) $route );
				if ( $id ) {
					$exclude[] = $id;
				}
			}
		}
		if ( 'post' === $post_type ) {
			$exclude = array_merge( $exclude, get_posts( array( 'post_type' => 'post', 'fields' => 'ids', 'numberposts' => 200, 'meta_key' => '_ff_noindex', 'meta_value' => 1, 'suppress_filters' => true ) ) ); // phpcs:ignore
		}
		if ( $exclude ) {
			$args['post__not_in'] = array_merge( $args['post__not_in'] ?? array(), $exclude );
		}
		return $args;
	},
	10,
	2
);
