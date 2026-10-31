<?php
/**
 * Temporary block for templates scheduled in later phases.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="section placeholder">
	<div class="container container--narrow">
		<p class="eyebrow"><?php echo esc_html( strtoupper( ff_lang() ) ); ?></p>
		<h1 class="h2"><?php ff_e( 'placeholder.title' ); ?></h1>
		<p class="lead"><?php ff_e( 'placeholder.text' ); ?></p>
	</div>
</section>
