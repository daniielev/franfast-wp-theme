<?php
/**
 * Content helpers.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Imported posts and legal pages may contain shortcodes from the previous page builder (Divi: [et_pb_*]).
 * Without the builder they would print as text. Remove the wrappers and keep what is inside.
 */
add_filter(
	'the_content',
	static function ( string $content ): string {
		if ( false === strpos( $content, '[et_pb_' ) ) {
			return $content;
		}
		return (string) preg_replace( '/\[\/?et_pb_[^\]]*\]/', '', $content );
	},
	5
);

/**
 * Excerpts: no "[...]" and a sensible length.
 */
add_filter( 'excerpt_more', static fn() => '…' );
add_filter( 'excerpt_length', static fn() => 28 );

/**
 * Date in the active language using lang/*.json → common.months / common.date_format.
 */
function ff_date( $timestamp ): string {
	$ts     = (int) $timestamp;
	$months = (array) ff_t( 'common.months' );
	$format = (string) ff_t( 'common.date_format' );
	$month  = $months[ (int) wp_date( 'n', $ts ) - 1 ] ?? wp_date( 'M', $ts );
	return strtr( $format ?: '{M} {d}, {Y}', array( '{M}' => $month, '{d}' => wp_date( 'j', $ts ), '{Y}' => wp_date( 'Y', $ts ) ) );
}

/**
 * Reading time in minutes.
 */
function ff_reading_time( int $post_id ): int {
	$words = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) );
	return max( 1, (int) ceil( $words / 220 ) );
}

/**
 * Categories that have published posts in a language (for the filter chips).
 */
function ff_categories_with_posts( string $lang ): array {
	$key   = 'ff_cats_' . $lang;
	$terms = wp_cache_get( $key, 'franfast' );
	if ( false === $terms ) {
		$terms = array_values(
			array_filter(
				get_categories( array( 'hide_empty' => true ) ),
				static fn( $t ) => ! in_array( $t->slug, array( 'uncategorized', ff_blog_category() ), true ) && ff_term_has_posts( $t, $lang )
			)
		);
		wp_cache_set( $key, $terms, 'franfast', HOUR_IN_SECONDS );
	}
	return $terms;
}

/**
 * Posts built with the old page builder can have an empty body (only builder wrappers) while the
 * real text sits in the builder's backup field (_et_pb_old_content). Show that text when the body is empty.
 */
add_filter(
	'the_content',
	static function ( string $content ): string {
		if ( ! is_singular( 'post' ) || ! in_the_loop() ) {
			return $content;
		}
		$text = trim( wp_strip_all_tags( (string) preg_replace( array( '/\[\/?[a-z_]+[^\]]*\]/i', '/<!--.*?-->/s' ), '', $content ) ) );
		if ( '' !== $text ) {
			return $content;
		}
		$old = (string) get_post_meta( get_the_ID(), '_et_pb_old_content', true );
		return '' !== trim( wp_strip_all_tags( $old ) ) ? wpautop( (string) preg_replace( '/\[\/?et_pb_[^\]]*\]/', '', $old ) ) : $content;
	},
	4
);

/**
 * Clean presentation markup pasted from the old site (inline styles, <font>, empty spans, Divi classes)
 * so posts use the theme typography.
 */
add_filter(
	'the_content',
	static function ( string $content ): string {
		if ( ! is_singular( 'post' ) ) {
			return $content;
		}
		$content = (string) preg_replace( '/\s(style|face|color|size|align|data-et-[a-z-]+)="[^"]*"/i', '', $content );
		$content = (string) preg_replace( '/\sclass="(?:[^"]*\b(?:et_|Mso|western)[^"]*)"/i', '', $content );
		$content = (string) preg_replace( '#</?(font|span)[^>]*>#i', '', $content );
		$content = (string) preg_replace( '#<p>(\s|&nbsp;|<br\s*/?>)*</p>#i', '', $content );
		return $content;
	},
	20
);
