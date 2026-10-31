<?php
/**
 * Contact (/contact-us/, /es/contacto/, /pt/contato/).
 * Form: Pardot form in an iframe (config.json → contact_form). The resize listener below replaces the
 * global HFCM snippet "Pardot Iframe Resize": it loads only here and accepts messages only from the Pardot domain.
 * Copy: lang/*.json → contact. No demo band and no newsletter here (config.json → newsletter.hide_on_routes).
 */

defined( 'ABSPATH' ) || exit;
get_header();

$ff_org    = (array) ff_config( 'org', array() );
$ff_form   = (array) ff_config( 'contact_form', array() );
$ff_src    = (string) ( $ff_form['src'] ?? '' );
$ff_origin = (string) ( $ff_form['origin'] ?? '' );
$ff_email  = (string) ( $ff_org['email'] ?? '' );
$ff_social = array_filter( (array) ff_config( 'social', array() ), static fn( $s ) => ! empty( $s['url'] ) );
?>
<section class="listing-head">
	<div class="container listing-head__inner">
		<p class="eyebrow"><?php ff_e( 'contact.eyebrow' ); ?></p>
		<h1 class="h1 listing-head__title"><?php ff_e( 'contact.title' ); ?></h1>
	</div>
</section>

<section class="section contact">
	<div class="container split">
		<div class="split__main contact__form">
			<?php if ( $ff_src ) : ?>
				<iframe id="pardot-form-iframe" class="contact__iframe" src="<?php echo esc_url( add_query_arg( 'iframeId', 'pardot-form-iframe', $ff_src ) ); ?>" title="<?php echo ff_attr( 'contact.form_title' ); ?>" width="100%" height="<?php echo (int) ( $ff_form['height'] ?? 900 ); ?>" loading="eager"></iframe>
			<?php endif; ?>
		</div>

		<aside class="split__side contact__info">
			<div class="contact-card">
				<?php if ( $ff_email ) : ?>
					<div class="contact-card__block">
						<h2 class="contact-card__title"><span class="contact-card__icon"><?php echo ff_icon( 'mail', 18 ); // phpcs:ignore ?></span><?php ff_e( 'contact.email_label' ); ?></h2>
						<p><a class="contact-card__email" href="mailto:<?php echo esc_attr( $ff_email ); ?>"><?php echo esc_html( $ff_email ); ?></a></p>
					</div>
				<?php endif; ?>

				<div class="contact-card__block">
					<h2 class="contact-card__title"><span class="contact-card__icon"><?php echo ff_icon( 'phone', 18 ); // phpcs:ignore ?></span><?php ff_e( 'contact.phones_label' ); ?></h2>
					<dl class="contact__phones">
						<?php foreach ( (array) ff_config( 'phones', array() ) as $ff_phone ) : ?>
							<div>
								<dt><?php ff_e( 'phones.' . $ff_phone['key'] ); ?></dt>
								<dd><a href="tel:<?php echo esc_attr( $ff_phone['tel'] ); ?>"><?php echo esc_html( $ff_phone['display'] ); ?></a></dd>
							</div>
						<?php endforeach; ?>
					</dl>
				</div>

				<?php if ( $ff_social ) : ?>
					<div class="contact-card__block">
						<h2 class="contact-card__title"><?php ff_e( 'contact.follow_label' ); ?></h2>
						<ul class="contact__social">
							<?php foreach ( $ff_social as $ff_s ) : ?>
								<li><a href="<?php echo esc_url( $ff_s['url'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( ff_t( 'social.' . $ff_s['key'] ) . ' ' . ff_t( 'common.opens_new_tab' ) ); ?>"><?php echo ff_icon( $ff_s['key'], 20 ); // phpcs:ignore ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</aside>
	</div>
</section>

<?php if ( $ff_src && $ff_origin ) : ?>
<script>
	// Resize the Pardot iframe (the form layout posts { pardotFormHeight }).
	window.addEventListener( 'message', function ( event ) {
		if ( event.origin !== <?php echo wp_json_encode( $ff_origin ); ?> || ! event.data || ! event.data.pardotFormHeight ) {
			return;
		}
		var height = parseInt( event.data.pardotFormHeight, 10 );
		var iframe = document.getElementById( 'pardot-form-iframe' );
		if ( iframe && height > 0 ) {
			iframe.style.height = height + 'px';
		}
	} );
</script>
<?php endif; ?>
<?php
get_footer();
