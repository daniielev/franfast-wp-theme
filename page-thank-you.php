<?php
/**
 * Thank-you page after the Pardot contact form (/thank-you/, /es/gracias/, /pt/obrigado/). noindex.
 * Background: config.json → thank_you.background. Badges: config.json → built_on.
 */

defined( 'ABSPATH' ) || exit;
get_header();
$ff_bg = (string) ff_config( 'thank_you.background', '' );
$ff_bg = $ff_bg ? ff_img_src( $ff_bg ) : '';
?>
<section class="ty-hero"<?php echo $ff_bg ? ' style="--ty-bg:url(\'' . esc_url( $ff_bg ) . '\')"' : ''; ?>>
	<div class="container ty-hero__inner">
		<span class="ty-hero__check" aria-hidden="true"><?php echo ff_icon( 'check', 32 ); // phpcs:ignore ?></span>
		<p class="eyebrow eyebrow--cyan"><?php ff_e( 'thank_you.eyebrow' ); ?></p>
		<h1 class="h1 ty-hero__title"><?php ff_e( 'thank_you.title' ); ?></h1>
		<p class="lead ty-hero__text"><?php ff_e( 'thank_you.text_1' ); ?></p>
		<p class="lead ty-hero__text"><?php ff_e( 'thank_you.text_2' ); ?></p>
		<a class="btn btn--inverse btn--lg" href="<?php echo esc_url( ff_url( 'reviews' ) ); ?>"><?php ff_e( 'thank_you.cta' ); ?> <?php echo ff_icon( 'arrow', 18 ); // phpcs:ignore ?></a>
	</div>
</section>

<section class="built-on" aria-labelledby="ff-built-on">
	<div class="container built-on__inner">
		<h2 class="built-on__title" id="ff-built-on"><?php ff_e( 'thank_you.built_on' ); ?></h2>
		<ul class="built-on__list">
			<?php foreach ( (array) ff_config( 'built_on', array() ) as $ff_b ) : ?>
				<li><img src="<?php echo esc_url( ff_img_src( (string) $ff_b['img'] ) ); ?>" alt="<?php echo esc_attr( $ff_b['name'] ); ?>" width="<?php echo (int) $ff_b['width']; ?>" height="<?php echo (int) $ff_b['height']; ?>" style="height:<?php echo (int) $ff_b['display_height']; ?>px" loading="lazy" decoding="async"></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php
get_footer();
