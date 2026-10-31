<?php
/**
 * Fallback template. Blog listings, archives and search get their own templates in phase 3.
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="section">
	<div class="container container--narrow">
		<?php if ( have_posts() ) : ?>
			<ul class="post-list">
				<?php while ( have_posts() ) : the_post(); ?>
					<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> <span class="muted">· <?php echo esc_html( get_the_date() ); ?></span></li>
				<?php endwhile; ?>
			</ul>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/placeholder' ); ?>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
