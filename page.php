<?php
/**
 * Default page: legal and other content pages edited in WordPress (Privacy Policy, Terms, Cookie Policy, AUP, Subprocessors, Refund...).
 * Marketing pages (Home, About, Add-ons, Reviews, Contact) have their own page-{slug}.php file.
 * Pages with 4+ H2 headings get a table of contents ("On this page"); H2s get anchors (inc/legal.php).
 */

defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	list( $ff_content, $ff_toc ) = ff_content_toc( (string) apply_filters( 'the_content', get_the_content() ) );
	$ff_has_toc = count( $ff_toc ) >= 4;
	?>
	<article <?php post_class( 'section' ); ?>>
		<div class="container container--narrow">
			<header class="page-head">
				<h1 class="page-head__title"><?php the_title(); ?></h1>
				<p class="page-head__meta muted"><?php echo esc_html( get_the_modified_date() ); ?></p>
			</header>
			<?php if ( $ff_has_toc ) : ?>
				<details class="legal-toc" open>
					<summary class="legal-toc__title"><?php ff_e( 'common.on_this_page' ); ?></summary>
					<ol class="legal-toc__list">
						<?php foreach ( $ff_toc as $ff_item ) : ?>
							<li><a href="#<?php echo esc_attr( $ff_item['id'] ); ?>"><?php echo esc_html( $ff_item['text'] ); ?></a></li>
						<?php endforeach; ?>
					</ol>
				</details>
			<?php endif; ?>
			<div class="prose">
				<?php echo $ff_content; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output ?>
			</div>
		</div>
	</article>
	<?php
endwhile;

get_footer();
