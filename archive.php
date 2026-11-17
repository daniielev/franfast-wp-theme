<?php
/**
 * Category, tag and date archives share the blog listing layout.
 */

defined( 'ABSPATH' ) || exit;
get_header();

$ff_term    = get_queried_object();
$ff_is_term = $ff_term instanceof WP_Term;
get_template_part(
	'template-parts/post-listing',
	null,
	array(
		'eyebrow'     => is_tag() ? ff_t( 'blog.tag_eyebrow' ) : ( is_category() ? ff_t( 'blog.category_eyebrow' ) : ff_t( 'blog.eyebrow' ) ),
		'title'       => $ff_is_term ? $ff_term->name : wp_strip_all_tags( get_the_archive_title() ),
		'lead'        => $ff_is_term ? wp_strip_all_tags( term_description( $ff_term ) ) : '',
		'active_term' => is_category() && $ff_is_term ? (int) $ff_term->term_id : -1,
	)
);
get_template_part( 'template-parts/cta-band' );
get_footer();
