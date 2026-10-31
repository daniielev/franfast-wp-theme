<?php
/**
 * Search results (posts in the active language) with the blog listing layout.
 */

defined( 'ABSPATH' ) || exit;
get_header();
global $wp_query;
get_template_part(
	'template-parts/post-listing',
	null,
	array(
		'eyebrow' => ff_t( 'blog.search_eyebrow' ),
		'title'   => sprintf( (string) ff_t( 'blog.search_title' ), get_search_query( false ) ),
		'lead'    => sprintf( (string) ff_t( 'blog.search_count' ), (int) $wp_query->found_posts ),
		'empty'   => ff_t( 'blog.search_empty' ),
		'filters' => false,
	)
);
get_footer();
