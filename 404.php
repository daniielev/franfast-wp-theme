<?php
/**
 * Not found, in the language of the requested URL.
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="section section--center">
	<div class="container container--narrow not-found">
		<p class="eyebrow"><?php ff_e( 'not_found.eyebrow' ); ?></p>
		<h1 class="h2"><?php ff_e( 'not_found.title' ); ?></h1>
		<p class="lead"><?php ff_e( 'not_found.text' ); ?></p>
		<?php get_search_form(); ?>
		<p><a class="btn btn--primary" href="<?php echo esc_url( ff_home_url() ); ?>"><?php ff_e( 'not_found.cta' ); ?></a></p>
	</div>
</section>
<?php
get_footer();
