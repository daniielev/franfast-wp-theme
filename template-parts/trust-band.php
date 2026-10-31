<?php
/**
 * SOC 2 Type II band (blue). On the home page it sits above the FAQ; on every other page it opens the footer.
 * Copy: lang/*.json → home.trust.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="trust-band" id="trust" aria-labelledby="ff-trust-title">
	<div class="container trust-band__inner">
		<div class="trust-band__copy">
			<span class="trust-band__icon"><?php echo ff_icon( 'shield', 28 ); // phpcs:ignore ?></span>
			<div>
				<h2 class="trust-band__title" id="ff-trust-title"><?php ff_e( 'home.trust.title' ); ?></h2>
				<p><?php ff_e( 'home.trust.text' ); ?></p>
			</div>
		</div>
		<a class="btn btn--secondary btn--lg" href="<?php echo esc_url( (string) ff_config( 'trust_center' ) ); ?>" target="_blank" rel="noopener"><?php ff_e( 'home.trust.cta' ); ?><span class="screen-reader-text"> <?php ff_e( 'common.opens_new_tab' ); ?></span></a>
	</div>
</section>
