<?php
/**
 * News (/our-news/, /es/noticias/, /pt/noticias/): posts in the category set in config.json → news_category.
 */

defined( 'ABSPATH' ) || exit;
get_header();

$ff_news = new WP_Query(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'category_name'  => (string) ff_config( 'news_category', 'news' ),
		'paged'          => max( 1, (int) get_query_var( 'paged' ) ),
		'meta_query'     => ff_lang_meta_query( ff_lang() ), // phpcs:ignore
	)
);
get_template_part( 'template-parts/post-listing', null, array( 'eyebrow' => ff_t( 'blog.news_eyebrow' ), 'title' => ff_t( 'blog.news_title' ), 'lead' => ff_t( 'blog.news_lead' ), 'query' => $ff_news, 'filters' => false ) );
get_template_part( 'template-parts/cta-band' );
get_footer();
