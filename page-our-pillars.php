<?php
/**
 * Our pillars (/our-pillars/, /es/nuestros-pilares/, /pt/nossos-pilares/, /fr/nos-piliers/).
 * One section per core module with the integrations ("Options") it supports; each section is linkable: /our-pillars/#royalty-management.
 * Module title/text/icon: lang/*.json → home.platform.modules (same copy as the Home cards, matched by "anchor").
 * Page copy + options: lang/*.json → pillars. FranFast AI closes the page (no options).
 */

defined( 'ABSPATH' ) || exit;
get_header();

$ff_modules = array();
$ff_ai      = null;
foreach ( (array) ff_t( 'home.platform.modules' ) as $ff_m ) {
	if ( ! empty( $ff_m['anchor'] ) ) {
		$ff_modules[] = $ff_m;
	} elseif ( ! empty( $ff_m['highlight'] ) ) {
		$ff_ai = $ff_m;
	}
}
$ff_options = (array) ff_t( 'pillars.options' );
?>
<section class="listing-head">
	<div class="container listing-head__inner">
		<p class="eyebrow"><?php ff_e( 'pillars.eyebrow' ); ?></p>
		<h1 class="h1 listing-head__title"><?php ff_e( 'pillars.title' ); ?></h1>
		<p class="lead"><?php ff_e( 'pillars.lead' ); ?></p>
		<nav class="chips pillars-toc" aria-label="<?php echo ff_attr( 'pillars.toc_label' ); ?>">
			<ul>
				<?php foreach ( $ff_modules as $ff_m ) : ?>
					<li><a class="chip" href="#<?php echo esc_attr( $ff_m['anchor'] ); ?>"><?php echo esc_html( $ff_m['title'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
	</div>
</section>

<?php foreach ( $ff_modules as $ff_i => $ff_m ) : ?>
	<?php $ff_opts = (array) ( $ff_options[ $ff_m['anchor'] ] ?? array() ); ?>
	<section class="section pillar<?php echo $ff_i % 2 ? ' section--gray-flat' : ''; ?>" id="<?php echo esc_attr( $ff_m['anchor'] ); ?>" aria-labelledby="ff-pillar-<?php echo esc_attr( $ff_m['anchor'] ); ?>">
		<div class="container pillar__inner">
			<div class="pillar__main stack-sm">
				<span class="module__icon"><?php echo ff_icon( (string) $ff_m['icon'], 28 ); // phpcs:ignore ?><span class="module__dot" aria-hidden="true"></span></span>
				<h2 class="h2 h2--sm" id="ff-pillar-<?php echo esc_attr( $ff_m['anchor'] ); ?>"><?php echo esc_html( $ff_m['title'] ); ?></h2>
				<p class="lead lead--sm"><?php echo esc_html( $ff_m['text'] ); ?></p>
			</div>
			<?php if ( $ff_opts ) : ?>
				<div class="pillar__options">
					<h3 class="pillar__options-title"><?php ff_e( 'pillars.options_label' ); ?></h3>
					<ul class="pillar__list">
						<?php foreach ( $ff_opts as $ff_o ) : ?>
							<li><?php echo ff_icon( 'arrow', 16 ); // phpcs:ignore ?><span><?php echo esc_html( (string) $ff_o ); ?></span></li>
						<?php endforeach; ?>
					</ul>
					<p class="pillar__note"><?php ff_e( 'pillars.options_note' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>
<?php endforeach; ?>

<?php if ( $ff_ai ) : ?>
	<section class="section">
		<div class="container">
			<div class="card module module--ai pillar-ai">
				<span class="module__icon"><?php echo ff_icon( (string) $ff_ai['icon'], 28 ); // phpcs:ignore ?><span class="module__dot ff-pulse" aria-hidden="true"></span></span>
				<h2 class="module__title"><?php echo esc_html( $ff_ai['title'] ); ?></h2>
				<p class="module__text"><?php echo esc_html( $ff_ai['text'] ); ?></p>
				<p><a class="btn btn--secondary" href="<?php echo esc_url( ff_url( 'home', null, 'franfast-ai' ) ); ?>"><?php ff_e( 'pillars.ai_link' ); ?></a></p>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php
get_template_part( 'template-parts/cta-band' );
get_footer();
