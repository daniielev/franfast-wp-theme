<?php
/**
 * Customers / reviews (/reviews/, /es/resenas/, /pt/avaliacoes/).
 * Testimonials: config.json → reviews (verbatim from production; quotes stay in English in every language).
 * Page copy: lang/*.json → reviews; rating line reuses home.proof. Ends with the demo band (→ Contact),
 * which replaces the old "Request a Demo" form on this page.
 */

defined( 'ABSPATH' ) || exit;
get_header();

$ff_reviews = (array) ff_config( 'reviews', array() );
$ff_appx    = (string) ff_config( 'appexchange_url', '' );
?>
<section class="listing-head">
	<div class="container listing-head__inner">
		<p class="eyebrow"><?php ff_e( 'reviews.eyebrow' ); ?></p>
		<h1 class="h1 listing-head__title"><?php ff_e( 'reviews.title' ); ?></h1>
		<p class="rating rating--inverse"><span><strong><?php ff_e( 'home.proof.rating' ); ?></strong> · <?php
		echo $ff_appx ? '<a href="' . esc_url( $ff_appx ) . '" target="_blank" rel="noopener">' . esc_html( (string) ff_t( 'home.proof.rating_text' ) ) . ' ' . ff_icon( 'external', 14 ) . '<span class="screen-reader-text"> ' . esc_html( (string) ff_t( 'common.opens_new_tab' ) ) . '</span></a>' : esc_html( (string) ff_t( 'home.proof.rating_text' ) ); // phpcs:ignore
		?></span></p>
	</div>
</section>

<section class="section section--gray-flat">
	<div class="container">
		<ul class="review-wall">
			<?php foreach ( $ff_reviews as $ff_r ) : ?>
				<li class="review">
					<figure class="review__inner">
						<?php if ( ! empty( $ff_r['logo'] ) ) : ?>
							<div class="review__logo"><img src="<?php echo esc_url( ff_img_src( (string) $ff_r['logo'] ) ); ?>" alt="<?php echo esc_attr( (string) $ff_r['company'] ); ?>" loading="lazy" decoding="async"></div>
						<?php endif; ?>
						<blockquote class="review__quote">
							<?php if ( ! empty( $ff_r['title'] ) ) : ?><p class="review__title"><?php echo esc_html( (string) $ff_r['title'] ); ?></p><?php endif; ?>
							<p>“<?php echo esc_html( (string) $ff_r['quote'] ); ?>”</p>
						</blockquote>
						<figcaption class="review__author">
							<span class="review__avatar" aria-hidden="true"><?php echo esc_html( implode( '', array_map( static fn( $w ) => mb_substr( $w, 0, 1 ), array_slice( explode( ' ', (string) $ff_r['name'] ), 0, 2 ) ) ) ); ?></span>
							<span><strong><?php echo esc_html( (string) $ff_r['name'] ); ?></strong><br><?php echo esc_html( (string) $ff_r['role'] ); ?></span>
						</figcaption>
					</figure>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php
get_template_part( 'template-parts/cta-band' );
get_footer();
