<?php
/**
 * "Book a demo" band (copy from home.demo). Used on home, blog and inner pages.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="cta-band" id="demo">
	<div class="container cta-band__inner">
		<div class="stack-xs">
			<h2 class="cta-band__title"><?php ff_e( 'home.demo.title' ); ?></h2>
			<p class="cta-band__text"><?php ff_e( 'home.demo.text' ); ?></p>
		</div>
		<a class="btn btn--inverse btn--lg" href="<?php echo esc_url( ff_url( 'contact' ) ); ?>"><?php ff_e( 'home.demo.cta' ); ?></a>
	</div>
</section>
