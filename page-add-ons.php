<?php
/**
 * Add-ons (/add-ons/, /es/complementos/, /pt/complementos/).
 * Partners: config.json → partners (key, name, category, img, width, height). Copy: lang/*.json → addons.
 * Filter: chips are plain links (?filter=payments) so the page works without JavaScript;
 * main.js filters in place when JS is available. Canonical stays on the clean URL (inc/seo.php).
 */

defined( 'ABSPATH' ) || exit;
get_header();

$ff_partners = (array) ff_config( 'partners', array() );
$ff_cats     = (array) ff_t( 'addons.categories' );
$ff_used     = array_unique( array_column( $ff_partners, 'category' ) );
$ff_cats     = array_intersect_key( $ff_cats, array_flip( $ff_used ) ); // Only categories that have partners, in JSON order.
$ff_filter   = isset( $_GET['filter'] ) ? sanitize_key( wp_unslash( $_GET['filter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
if ( ! isset( $ff_cats[ $ff_filter ] ) ) {
	$ff_filter = '';
}
$ff_base = ff_url( 'add_ons' );
?>
<section class="listing-head">
	<div class="container listing-head__inner">
		<p class="eyebrow"><?php ff_e( 'addons.eyebrow' ); ?></p>
		<h1 class="h1 listing-head__title"><?php ff_e( 'addons.title' ); ?></h1>
		<p class="lead"><?php ff_e( 'addons.lead' ); ?></p>
		<p class="listing-head__note"><?php ff_e( 'addons.note' ); ?></p>
	</div>
</section>

<section class="section listing" id="addons">
	<div class="container stack-md">
		<nav class="chips" aria-label="<?php echo ff_attr( 'addons.filter_label' ); ?>" data-ff-filter>
			<ul>
				<li><a class="chip<?php echo '' === $ff_filter ? ' is-active' : ''; ?>" href="<?php echo esc_url( $ff_base . '#addons' ); ?>" data-filter=""<?php echo '' === $ff_filter ? ' aria-current="true"' : ''; ?>><?php ff_e( 'addons.all' ); ?></a></li>
				<?php foreach ( $ff_cats as $ff_key => $ff_label ) : ?>
					<li><a class="chip<?php echo $ff_key === $ff_filter ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'filter', $ff_key, $ff_base ) . '#addons' ); ?>" data-filter="<?php echo esc_attr( $ff_key ); ?>"<?php echo $ff_key === $ff_filter ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $ff_label ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<ul class="addon-grid" data-ff-filter-list>
			<?php foreach ( $ff_partners as $ff_p ) : ?>
				<?php
				$ff_cat  = (string) ( $ff_p['category'] ?? '' );
				$ff_text = (string) ff_t( 'addons.partners.' . $ff_p['key'] );
				?>
				<li class="addon-card" data-cat="<?php echo esc_attr( $ff_cat ); ?>"<?php echo $ff_filter && $ff_filter !== $ff_cat ? ' hidden' : ''; ?>>
					<div class="addon-card__logo">
						<?php if ( ! empty( $ff_p['img'] ) ) : ?>
							<img src="<?php echo esc_url( ff_img_src( (string) $ff_p['img'] ) ); ?>" alt="<?php echo esc_attr( $ff_p['name'] ); ?>"<?php echo ! empty( $ff_p['width'] ) ? ' width="' . (int) $ff_p['width'] . '" height="' . (int) $ff_p['height'] . '"' : ''; ?> loading="lazy" decoding="async">
						<?php else : ?>
							<span class="addon-card__name"><?php echo esc_html( $ff_p['name'] ); ?></span>
						<?php endif; ?>
					</div>
					<h2 class="screen-reader-text"><?php echo esc_html( $ff_p['name'] ); ?></h2>
					<?php if ( $ff_text ) : ?><p class="addon-card__text"><?php echo esc_html( $ff_text ); ?></p><?php endif; ?>
					<?php if ( isset( $ff_cats[ $ff_cat ] ) ) : ?><span class="addon-card__cat"><?php echo esc_html( $ff_cats[ $ff_cat ] ); ?></span><?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php
get_template_part( 'template-parts/cta-band' );
get_footer();
