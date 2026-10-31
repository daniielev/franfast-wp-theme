<?php
/**
 * Blog home (Settings → Reading → Posts page "Our Blog"): /our-blog/, /es/blog/, /pt/blog/.
 */

defined( 'ABSPATH' ) || exit;
get_header();
get_template_part( 'template-parts/post-listing', null, array( 'eyebrow' => ff_t( 'blog.eyebrow' ), 'title' => ff_t( 'blog.title' ), 'lead' => ff_t( 'blog.lead' ) ) );
get_template_part( 'template-parts/cta-band' );
get_footer();
