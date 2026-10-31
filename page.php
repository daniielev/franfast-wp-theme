<?php
/**
 * Default page: legal and other content pages edited in WordPress (Privacy Policy, Terms, DPA...).
 * Marketing pages (Home, About, Add-ons, Reviews, Contact) have their own page-{slug}.php file.
 */

defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'section' ); ?>>
		<div class="container container--narrow">
			<header class="page-head">
				<h1 class="page-head__title"><?php the_title(); ?></h1>
				<p class="page-head__meta muted"><?php echo esc_html( get_the_modified_date() ); ?></p>
			</header>
			<div class="prose">
				<?php the_content(); ?>
			</div>
		</div>
	</article>
	<?php
endwhile;

get_footer();
