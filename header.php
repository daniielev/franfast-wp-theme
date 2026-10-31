<?php
/**
 * Site header: logo, main navigation, language switcher, primary CTA.
 */

defined( 'ABSPATH' ) || exit;
$ff_lang = ff_lang();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#FFFFFF">
<link rel="icon" href="<?php echo esc_url( ff_asset( 'img/favicon.svg' ) ); ?>" type="image/svg+xml">
<?php wp_head(); ?>
</head>
<body <?php body_class( array( 'lang-' . $ff_lang, ff_config( 'motion.enabled', true ) ? 'ff-motion' : 'ff-no-motion' ) ); ?>>
<?php wp_body_open(); ?>
<?php if ( ff_config( 'motion.progress_bar', true ) ) : ?><div class="ff-progress" aria-hidden="true"><span data-ff-progress></span></div><?php endif; ?>
<a class="skip-link" href="#main"><?php ff_e( 'common.skip' ); ?></a>

<header class="site-header" data-ff-header>
	<div class="container site-header__inner">
		<a class="site-header__brand" href="<?php echo esc_url( ff_home_url() ); ?>" aria-label="<?php echo esc_attr( 'FranFast — ' . ff_t( 'common.home' ) ); ?>">
			<?php echo ff_logo_svg( 'site-header__logo' ); // phpcs:ignore -- theme SVG file ?>
		</a>

		<?php $ff_call = (array) ( ff_config( 'phones', array() )[0] ?? array() ); // First phone in config.json (US). ?>
		<?php if ( ! empty( $ff_call['tel'] ) ) : ?>
			<a class="site-header__call" href="tel:<?php echo esc_attr( $ff_call['tel'] ); ?>" aria-label="<?php echo esc_attr( ff_t( 'common.call' ) . ' ' . $ff_call['display'] ); ?>"><?php echo ff_icon( 'phone', 20 ); // phpcs:ignore ?></a>
		<?php endif; ?>
		<a class="btn btn--primary site-header__cta" href="<?php echo esc_url( ff_url( 'contact' ) ); ?>"><?php ff_e( 'common.book_demo' ); ?></a>

		<button class="site-header__toggle" type="button" aria-expanded="false" aria-controls="site-panel"
			data-label-open="<?php echo ff_attr( 'common.menu' ); ?>" data-label-close="<?php echo ff_attr( 'common.close_menu' ); ?>">
			<span class="icon-open"><?php echo ff_icon( 'menu', 22 ); // phpcs:ignore ?></span>
			<span class="icon-close"><?php echo ff_icon( 'close', 22 ); // phpcs:ignore ?></span>
			<span class="screen-reader-text" data-ff-toggle-label><?php ff_e( 'common.menu' ); ?></span>
		</button>

		<div class="site-header__panel" id="site-panel">
			<nav class="site-nav" aria-label="<?php echo ff_attr( 'common.main_nav' ); ?>">
				<ul class="site-nav__list">
					<?php foreach ( (array) ff_config( 'nav', array() ) as $item ) : ?>
						<li><a href="<?php echo esc_url( ff_item_url( $item ) ); ?>"<?php echo ff_item_is_current( $item ) ? ' aria-current="page"' : ''; ?>><?php ff_e( 'nav.' . $item['key'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
			<div class="site-header__actions">
				<?php get_template_part( 'template-parts/lang-switch' ); ?>
				<a class="btn btn--primary" href="<?php echo esc_url( ff_url( 'contact' ) ); ?>"><?php ff_e( 'common.book_demo' ); ?></a>
			</div>
		</div>
	</div>
</header>

<main id="main" tabindex="-1">
