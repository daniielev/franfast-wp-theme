<?php
/**
 * All posts: /blog/, /es/blog/, /pt/blog/, /fr/blog/ (rewrite ff_all_posts). "All posts" chip active.
 * The old Posts page /our-blog/ redirects here.
 */

defined( 'ABSPATH' ) || exit;
get_header();
get_template_part( 'template-parts/post-listing', null, array( 'eyebrow' => ff_t( 'blog.eyebrow' ), 'title' => ff_t( 'blog.title' ), 'lead' => ff_t( 'blog.lead' ) ) );
get_template_part( 'template-parts/cta-band' );
get_footer();
