<?php
/**
 * Site footer: brand + contact, link columns, legal (compliance) links, phones, trust band with badges.
 */

defined( 'ABSPATH' ) || exit;
$ff_org = (array) ff_config( 'org', array() );
?>
</main>

<?php get_template_part( 'template-parts/newsletter' ); ?>

<footer class="site-footer">
	<div class="container site-footer__grid">

		<div class="site-footer__brand">
			<a href="<?php echo esc_url( ff_home_url() ); ?>" class="site-footer__logo">
				<img src="<?php echo esc_url( ff_asset( 'img/franfast-logo.svg' ) ); ?>" alt="FranFast" width="99" height="30" loading="lazy">
			</a>
			<p class="site-footer__tagline"><?php ff_e( 'footer.tagline' ); ?></p>
			<address class="site-footer__address">
				<span class="site-footer__line"><?php echo ff_icon( 'pin', 16 ); // phpcs:ignore ?>
					<span><?php echo esc_html( ( $ff_org['street'] ?? '' ) . ', ' . ( $ff_org['city'] ?? '' ) . ', ' . ( $ff_org['region'] ?? '' ) . ' ' . ( $ff_org['postal_code'] ?? '' ) . ', USA' ); ?></span>
				</span>
				<?php if ( ! empty( $ff_org['email'] ) ) : ?>
					<span class="site-footer__line"><?php echo ff_icon( 'mail', 16 ); // phpcs:ignore ?>
						<a href="mailto:<?php echo esc_attr( antispambot( $ff_org['email'] ) ); ?>"><?php echo esc_html( antispambot( $ff_org['email'] ) ); ?></a>
					</span>
				<?php endif; ?>
			</address>
			<ul class="site-footer__social" aria-label="<?php echo ff_attr( 'footer.social_label' ); ?>">
				<?php foreach ( (array) ff_config( 'social', array() ) as $social ) : ?>
					<?php if ( empty( $social['url'] ) ) { continue; } ?>
					<li><a href="<?php echo esc_url( $social['url'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( ff_t( 'social.' . $social['key'] ) . ' ' . ff_t( 'common.opens_new_tab' ) ); ?>"><?php echo ff_icon( $social['key'], 18 ); // phpcs:ignore ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php foreach ( (array) ff_config( 'footer_columns', array() ) as $column ) : ?>
			<nav class="site-footer__col" aria-labelledby="ff-foot-<?php echo esc_attr( $column['key'] ); ?>">
				<h2 class="site-footer__heading" id="ff-foot-<?php echo esc_attr( $column['key'] ); ?>"><?php ff_e( 'footer.col_' . $column['key'] ); ?></h2>
				<ul>
					<?php foreach ( (array) ( $column['links'] ?? array() ) as $link ) : ?>
						<li><a href="<?php echo esc_url( ff_item_url( $link ) ); ?>"><?php ff_e( 'nav.' . $link['key'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endforeach; ?>

		<nav class="site-footer__col site-footer__col--legal" aria-labelledby="ff-foot-legal">
			<h2 class="site-footer__heading" id="ff-foot-legal"><?php ff_e( 'footer.col_legal' ); ?></h2>
			<ul>
				<?php foreach ( (array) ff_config( 'legal', array() ) as $legal ) : ?>
					<li><a href="<?php echo esc_url( home_url( $legal['path'] ) ); ?>"><?php ff_e( 'legal.' . $legal['key'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<div class="site-footer__col site-footer__col--phones">
			<h2 class="site-footer__heading" id="ff-foot-contact"><?php ff_e( 'footer.col_contact' ); ?></h2>
			<dl class="site-footer__phones" aria-labelledby="ff-foot-contact">
				<?php foreach ( (array) ff_config( 'phones', array() ) as $phone ) : ?>
					<div>
						<dt><?php ff_e( 'phones.' . $phone['key'] ); ?></dt>
						<dd><a href="tel:<?php echo esc_attr( $phone['tel'] ); ?>"><?php echo esc_html( $phone['display'] ); ?></a></dd>
					</div>
				<?php endforeach; ?>
			</dl>
		</div>
	</div>

	<?php if ( ! is_front_page() ) : // Compact SOC 2 band (the home page has the full one above the FAQ). ?>
		<div class="trust-band trust-band--compact">
			<div class="container trust-band__inner">
				<div class="trust-band__copy">
					<span class="trust-band__icon"><?php echo ff_icon( 'shield', 24 ); // phpcs:ignore ?></span>
					<div>
						<p class="trust-band__title"><?php ff_e( 'home.trust.title' ); ?></p>
						<p><?php ff_e( 'home.trust.text' ); ?></p>
					</div>
				</div>
				<a class="btn btn--secondary" href="<?php echo esc_url( (string) ff_config( 'trust_center' ) ); ?>" target="_blank" rel="noopener"><?php ff_e( 'home.trust.cta' ); ?><span class="screen-reader-text"> <?php ff_e( 'common.opens_new_tab' ); ?></span></a>
			</div>
		</div>
	<?php endif; ?>

	<div class="container site-footer__badges">
		<p class="badges__label"><?php ff_e( 'footer.badges_label' ); ?></p>
		<ul class="badges" aria-label="<?php echo ff_attr( 'footer.badges_label' ); ?>">
			<?php foreach ( (array) ff_config( 'badges', array() ) as $badge ) : ?>
				<?php
				$label = (string) ff_t( 'badges.' . $badge['key'] );
				$inner = ! empty( $badge['img'] )
				? sprintf(
					'<img src="%1$s" alt="%2$s" loading="lazy" decoding="async"%3$s>',
					esc_url( ff_img_src( (string) $badge['img'] ) ),
					esc_attr( $label ),
					! empty( $badge['width'] ) ? sprintf( ' width="%d" height="%d"', (int) $badge['width'], (int) $badge['height'] ) : ''
				)
				: '<span class="badge-chip">' . ( 'soc2' === $badge['key'] ? ff_icon( 'shield', 16 ) : '' ) . esc_html( $label ) . '</span>';
				?>
				<li class="badges__item badges__item--<?php echo esc_attr( $badge['key'] ); ?>">
				<?php if ( ! empty( $badge['url'] ) ) : ?>
					<a href="<?php echo esc_url( $badge['url'] ); ?>" target="_blank" rel="noopener"><?php echo $inner; // phpcs:ignore ?></a>
				<?php else : ?>
					<?php echo $inner; // phpcs:ignore ?>
				<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<div class="container site-footer__bottom">
		<?php
		$ff_legal = esc_html( (string) ( $ff_org['legal_name'] ?? '' ) );
		if ( ! empty( $ff_org['legal_url'] ) ) { // config.json → org.legal_url
			$ff_legal = '<a class="link-ext" href="' . esc_url( $ff_org['legal_url'] ) . '" target="_blank" rel="noopener">' . $ff_legal . ' ' . ff_icon( 'external', 12 ) . '<span class="screen-reader-text"> ' . esc_html( (string) ff_t( 'common.opens_new_tab' ) ) . '</span></a>';
		}
		?>
		<p><?php echo sprintf( esc_html( (string) ff_t( 'footer.trademark' ) ), esc_html( wp_date( 'Y' ) ), $ff_legal ); // phpcs:ignore -- parts escaped above ?> · <?php ff_e( 'footer.built_on' ); ?></p>
		<p class="site-footer__notice"><?php ff_e( 'footer.salesforce_notice' ); ?></p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
