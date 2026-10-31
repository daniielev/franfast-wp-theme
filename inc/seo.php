<?php
/**
 * Native SEO: <title>, meta description, canonical, hreflang, Open Graph/Twitter, JSON-LD, robots.
 * Replaces Yoast. Per-page/post overrides come from the "SEO" meta box (inc/seo-box.php), with old Yoast values as fallback.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Everything the <head> needs for the current view, computed once.
 */
function ff_seo(): array {
	static $seo = null;
	if ( null !== $seo ) {
		return $seo;
	}

	$lang  = ff_lang();
	$paged = max( 1, (int) get_query_var( 'paged' ) );
	$route = ff_current_route();
	$site  = (string) ff_t( 'seo.site_name' );
	$sep   = ' ' . ff_t( 'seo.separator' ) . ' ';

	$seo = array(
		'title'       => $site,
		'description' => '',
		'canonical'   => '',
		'alternates'  => array(),
		'type'        => 'website',
		'image'       => (string) ff_config( 'og_image', '' ),
		'noindex'     => false,
	);

	if ( null !== $route ) {
		$seo['title']       = (string) ff_t( 'seo.' . $route . '.title' ) ?: $site;
		$seo['description'] = (string) ff_t( 'seo.' . $route . '.description' );
		$seo['canonical']   = ff_url( $route, $lang );
		$seo['noindex']     = in_array( $route, (array) ff_config( 'noindex_routes', array() ), true );
		// Marketing page edited in WordPress: its SEO box (per language) overrides the JSON defaults.
		$page_id = is_page() ? get_queried_object_id() : ( 'home' === $route ? (int) get_option( 'page_on_front' ) : 0 );
		if ( $page_id ) {
			$seo['title']       = ff_seo_meta( $page_id, 'title', $lang ) ?: $seo['title'];
			$seo['description'] = ff_seo_meta( $page_id, 'description', $lang ) ?: $seo['description'];
			$seo['image']       = ff_seo_meta( $page_id, 'image', $lang ) ?: $seo['image'];
			$seo['noindex']     = $seo['noindex'] || (bool) get_post_meta( $page_id, '_ff_noindex', true );
		}
	} elseif ( is_singular() ) {
		$id                 = get_queried_object_id();
		$custom_title       = ff_seo_meta( $id, 'title' );
		$custom_desc        = ff_seo_meta( $id, 'description' );
		$seo['title']       = $custom_title ?: wp_strip_all_tags( get_the_title( $id ) ) . $sep . $site;
		$seo['description'] = $custom_desc ?: ff_excerpt_for( $id, 155 );
		$seo['canonical']   = (string) get_permalink( $id );
		$seo['noindex']     = (bool) get_post_meta( $id, '_ff_noindex', true );
		if ( is_singular( 'post' ) ) {
			$seo['type'] = 'article';
		}
		$custom_image = ff_seo_meta( $id, 'image' );
		if ( $custom_image ) {
			$seo['image'] = $custom_image;
		} elseif ( has_post_thumbnail( $id ) ) {
			$seo['image'] = (string) get_the_post_thumbnail_url( $id, 'large' );
		}
	} elseif ( is_category() || is_tag() ) {
		$term               = get_queried_object();
		$name               = $term instanceof WP_Term ? $term->name : '';
		$seo['title']       = sprintf( (string) ff_t( is_category() ? 'seo.category' : 'seo.tag' ), $name ) . $sep . $site;
		$desc               = $term instanceof WP_Term ? wp_strip_all_tags( term_description( $term ) ) : '';
		$seo['description'] = $desc ?: sprintf( (string) ff_t( 'seo.category_description' ), $name );
		ff_raw_term_links( true );
		$link = $term instanceof WP_Term ? get_term_link( $term ) : '';
		ff_raw_term_links( false );
		$seo['canonical'] = is_string( $link ) ? ff_localize_url( $link, $lang ) : '';
	} elseif ( is_search() ) {
		$seo['title']   = sprintf( (string) ff_t( 'seo.search' ), get_search_query( false ) ) . $sep . $site;
		$seo['noindex'] = true;
	} elseif ( is_404() ) {
		$seo['title']   = (string) ff_t( 'seo.not_found' ) . $sep . $site;
		$seo['noindex'] = true;
	} elseif ( is_archive() ) {
		$seo['title']   = wp_strip_all_tags( get_the_archive_title() ) . $sep . $site;
		$seo['noindex'] = true; // Date/author archives are thin; categories carry the SEO value.
	}

	// Pagination: own canonical, "Page N" in the title, hreflang only on page 1.
	if ( $paged > 1 && $seo['canonical'] ) {
		$seo['canonical'] = trailingslashit( $seo['canonical'] ) . 'page/' . $paged . '/';
		$seo['title']    .= ' – ' . sprintf( (string) ff_t( 'seo.paged' ), $paged );
	}

	if ( 1 === $paged && ! $seo['noindex'] && $seo['canonical'] ) {
		foreach ( ff_languages() as $code ) {
			$url = $code === $lang ? $seo['canonical'] : ff_alternate_url( $code );
			if ( $url ) {
				$seo['alternates'][ $code ] = $url;
			}
		}
		if ( count( $seo['alternates'] ) < 2 ) {
			$seo['alternates'] = array(); // A page without translations needs no hreflang.
		}
	}

	$seo = apply_filters( 'ff_seo', $seo );
	return $seo;
}

/**
 * Plain-text excerpt for meta descriptions.
 */
function ff_excerpt_for( int $post_id, int $length = 155 ): string {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return '';
	}
	$text = $post->post_excerpt ?: strip_shortcodes( $post->post_content );
	$text = (string) preg_replace( '/\[\/?[a-z_]+[^\]]*\]/i', ' ', $text ); // Unregistered shortcodes (old builder).
	$text = (string) preg_replace( '/<[^>]+>/', ' ', $text );                // Keep word breaks between tags.
	$text = trim( (string) preg_replace( '/\s+/', ' ', wp_strip_all_tags( $text ) ) );
	if ( mb_strlen( $text ) <= $length ) {
		return $text;
	}
	$cut = mb_substr( $text, 0, $length - 1 );
	$cut = preg_replace( '/\s+\S*$/u', '', $cut );
	return rtrim( $cut, " ,.;:" ) . '…';
}

add_filter(
	'pre_get_document_title',
	static function (): string {
		return ff_seo()['title'];
	}
);

add_filter(
	'wp_robots',
	static function ( array $robots ): array {
		if ( ff_seo()['noindex'] ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
			unset( $robots['max-image-preview'] );
		}
		return $robots;
	}
);

/**
 * Meta, canonical, hreflang and social tags.
 */
add_action(
	'wp_head',
	static function (): void {
		$seo    = ff_seo();
		$lang   = ff_lang();
		$locale = ff_lang_meta( $lang, 'locale' );
		$out    = array();

		if ( $seo['description'] ) {
			$out[] = '<meta name="description" content="' . esc_attr( $seo['description'] ) . '">';
		}
		if ( $seo['canonical'] && ! $seo['noindex'] ) {
			$out[] = '<link rel="canonical" href="' . esc_url( $seo['canonical'] ) . '">';
		}
		foreach ( $seo['alternates'] as $code => $url ) {
			$out[] = '<link rel="alternate" hreflang="' . esc_attr( ff_lang_meta( $code, 'hreflang' ) ) . '" href="' . esc_url( $url ) . '">';
		}
		if ( isset( $seo['alternates'][ ff_default_lang() ] ) ) {
			$out[] = '<link rel="alternate" hreflang="x-default" href="' . esc_url( $seo['alternates'][ ff_default_lang() ] ) . '">';
		}

		// Open Graph + Twitter.
		$out[] = '<meta property="og:site_name" content="' . esc_attr( (string) ff_t( 'seo.site_name' ) ) . '">';
		$out[] = '<meta property="og:type" content="' . esc_attr( $seo['type'] ) . '">';
		$out[] = '<meta property="og:title" content="' . esc_attr( $seo['title'] ) . '">';
		if ( $seo['description'] ) {
			$out[] = '<meta property="og:description" content="' . esc_attr( $seo['description'] ) . '">';
		}
		if ( $seo['canonical'] ) {
			$out[] = '<meta property="og:url" content="' . esc_url( $seo['canonical'] ) . '">';
		}
		$out[] = '<meta property="og:locale" content="' . esc_attr( $locale ) . '">';
		foreach ( array_keys( $seo['alternates'] ) as $code ) {
			if ( $code !== $lang ) {
				$out[] = '<meta property="og:locale:alternate" content="' . esc_attr( ff_lang_meta( $code, 'locale' ) ) . '">';
			}
		}
		if ( $seo['image'] ) {
			$out[] = '<meta property="og:image" content="' . esc_url( $seo['image'] ) . '">';
			$img_id = attachment_url_to_postid( $seo['image'] );
			$img    = $img_id ? wp_get_attachment_metadata( $img_id ) : array();
			if ( ! empty( $img['width'] ) ) {
				$out[] = '<meta property="og:image:width" content="' . (int) $img['width'] . '">';
				$out[] = '<meta property="og:image:height" content="' . (int) $img['height'] . '">';
			}
		}
		$out[] = '<meta name="twitter:card" content="' . ( $seo['image'] ? 'summary_large_image' : 'summary' ) . '">';
		if ( 'article' === $seo['type'] ) {
			$id    = get_queried_object_id();
			$out[] = '<meta property="article:published_time" content="' . esc_attr( get_the_date( DATE_W3C, $id ) ) . '">';
			$out[] = '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( DATE_W3C, $id ) ) . '">';
		}

		echo implode( "\n", $out ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
	},
	1
);

/**
 * JSON-LD: Organization + WebSite on every page, WebPage/BlogPosting + BreadcrumbList by context.
 */
add_action(
	'wp_head',
	static function (): void {
		if ( is_404() || is_search() ) {
			return;
		}
		$seo  = ff_seo();
		$lang = ff_lang();
		$home = ff_home_url( $lang );
		$org  = (array) ff_config( 'org', array() );

		$contact_points = array();
		foreach ( (array) ff_config( 'phones', array() ) as $phone ) {
			$point = array(
				'@type'       => 'ContactPoint',
				'telephone'   => $phone['tel'] ?? '',
				'contactType' => 'sales',
			);
			if ( ! empty( $phone['key'] ) && 'OTHER' !== $phone['key'] ) {
				$point['areaServed'] = 'UK' === $phone['key'] ? 'GB' : $phone['key'];
			}
			$contact_points[] = $point;
		}

		$organization = array(
			'@type'              => 'Organization',
			'@id'                => home_url( '/#organization' ),
			'name'               => $org['name'] ?? 'FranFast',
			'url'                => home_url( '/' ),
			'logo'               => ff_asset( 'img/franfast-logo.svg' ),
			'email'              => $org['email'] ?? '',
			'address'            => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => $org['street'] ?? '',
				'addressLocality' => $org['city'] ?? '',
				'addressRegion'   => $org['region'] ?? '',
				'postalCode'      => $org['postal_code'] ?? '',
				'addressCountry'  => $org['country'] ?? '',
			),
			'contactPoint'       => $contact_points,
			'parentOrganization' => array(
				'@type' => 'Organization',
				'name'  => $org['parent_name'] ?? '',
				'url'   => $org['parent_url'] ?? '',
			),
			'sameAs'             => array_values( array_filter( wp_list_pluck( (array) ff_config( 'social', array() ), 'url' ) ) ),
		);

		$graph = array(
			$organization,
			array(
				'@type'      => 'WebSite',
				'@id'        => home_url( '/#website' ),
				'url'        => home_url( '/' ),
				'name'       => (string) ff_t( 'seo.site_name' ),
				'publisher'  => array( '@id' => home_url( '/#organization' ) ),
				'inLanguage' => array_map( static fn( $c ) => ff_lang_meta( $c, 'hreflang' ), ff_languages() ),
			),
		);

		$url   = $seo['canonical'] ?: $home;
		$crumb = array( array( ff_t( 'breadcrumbs.home' ), $home ) );

		if ( is_singular( 'post' ) ) {
			$id     = get_queried_object_id();
			$crumb[] = array( ff_t( 'breadcrumbs.blog' ), ff_url( 'blog', $lang ) );
			$crumb[] = array( wp_strip_all_tags( get_the_title( $id ) ), $url );
			$article = array(
				'@type'            => 'BlogPosting',
				'@id'              => $url . '#article',
				'headline'         => wp_strip_all_tags( get_the_title( $id ) ),
				'description'      => $seo['description'],
				'datePublished'    => get_the_date( DATE_W3C, $id ),
				'dateModified'     => get_the_modified_date( DATE_W3C, $id ),
				'inLanguage'       => ff_lang_meta( $lang, 'hreflang' ),
				'mainEntityOfPage' => $url,
				'author'           => array( '@id' => home_url( '/#organization' ) ),
				'publisher'        => array( '@id' => home_url( '/#organization' ) ),
			);
			if ( $seo['image'] ) {
				$article['image'] = $seo['image'];
			}
			$graph[] = $article;
		} else {
			$route = ff_current_route();
			if ( 'home' !== $route ) {
				$crumb[] = array( wp_strip_all_tags( preg_replace( '/\s[|–].*$/u', '', $seo['title'] ) ), $url );
			}
			$graph[] = array(
				'@type'      => 'WebPage',
				'@id'        => $url . '#webpage',
				'url'        => $url,
				'name'       => $seo['title'],
				'inLanguage' => ff_lang_meta( $lang, 'hreflang' ),
				'isPartOf'   => array( '@id' => home_url( '/#website' ) ),
			);
			if ( 'home' === $route ) {
				$graph[] = array(
					'@type'               => 'SoftwareApplication',
					'name'                => 'FranFast',
					'applicationCategory' => 'BusinessApplication',
					'operatingSystem'     => 'Web, iOS, Android',
					'description'         => $seo['description'],
					'publisher'           => array( '@id' => home_url( '/#organization' ) ),
				);
			}
		}

		if ( count( $crumb ) > 1 ) {
			$items = array();
			foreach ( $crumb as $i => $c ) {
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => $i + 1,
					'name'     => (string) $c[0],
					'item'     => (string) $c[1],
				);
			}
			$graph[] = array( '@type' => 'BreadcrumbList', 'itemListElement' => $items );
		}

		$json = wp_json_encode(
			array( '@context' => 'https://schema.org', '@graph' => $graph ),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG
		);
		echo '<script type="application/ld+json">' . $json . "</script>\n"; // phpcs:ignore
	},
	3
);
