<?php
/**
 * Newsletter band (above the footer). Posts to a Pardot Form Handler.
 * On/off and handler URL: config.json → newsletter. Copy: lang/*.json → newsletter.
 *
 * Flow: JS sends the form into a hidden iframe → Pardot redirects that iframe to the
 * Success/Error Location (/?ff_nl=ok|error, see inc/newsletter.php) → that page tells
 * this page the result with postMessage → message shown in the active language.
 * Without JS the browser goes to Pardot and comes back to /?ff_nl_done=ok#newsletter.
 */

defined( 'ABSPATH' ) || exit;

if ( ! ff_newsletter_enabled() || in_array( ff_current_route(), (array) ff_config( 'newsletter.hide_on_routes', array() ), true ) ) {
	return; // Off, or hidden on this page (config.json → newsletter.hide_on_routes).
}

$ff_nl     = (array) ff_config( 'newsletter', array() );
$ff_fields = (array) ( $ff_nl['fields'] ?? array() );
$ff_done   = isset( $_GET['ff_nl_done'] ) ? sanitize_key( wp_unslash( $_GET['ff_nl_done'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$ff_privacy = sprintf(
	'<a href="%s">%s</a>',
	esc_url( home_url( '/privacy-policy/' ) ),
	esc_html( (string) ff_t( 'legal.privacy' ) )
);
?>
<section class="newsletter" id="newsletter" aria-labelledby="ff-nl-title">
	<div class="container">
	<div class="newsletter__inner">
		<div class="newsletter__intro">
			<span class="newsletter__icon" aria-hidden="true"><?php echo ff_icon( 'mail', 26 ); // phpcs:ignore ?><span class="module__dot ff-pulse"></span></span>
			<p class="eyebrow"><?php ff_e( 'newsletter.eyebrow' ); ?></p>
			<h2 class="newsletter__title" id="ff-nl-title"><?php ff_e( 'newsletter.title' ); ?></h2>
			<p class="newsletter__text"><?php ff_e( 'newsletter.text' ); ?></p>
		</div>

		<form class="newsletter__form" method="post" action="<?php echo esc_url( ff_newsletter_action() ); ?>" novalidate data-ff-newsletter<?php echo ff_newsletter_action() ? '' : ' data-ff-preview'; ?>
			data-msg-ok="<?php echo ff_attr( 'newsletter.success' ); ?>"
			data-msg-error="<?php echo ff_attr( 'newsletter.error' ); ?>"
			data-msg-email="<?php echo ff_attr( 'newsletter.invalid_email' ); ?>"
			data-msg-name="<?php echo ff_attr( 'newsletter.invalid_name' ); ?>"
			data-msg-last="<?php echo ff_attr( 'newsletter.invalid_last_name' ); ?>"
			data-origins="<?php echo esc_attr( wp_json_encode( ff_newsletter_origins() ) ); ?>">

			<div class="newsletter__fields">
				<div class="field">
					<label for="ff-nl-name"><?php ff_e( 'newsletter.first_name' ); ?></label>
					<input id="ff-nl-name" name="<?php echo esc_attr( $ff_fields['first_name'] ?? 'first_name' ); ?>" type="text" autocomplete="given-name" required maxlength="40">
				</div>
				<div class="field">
					<label for="ff-nl-last"><?php ff_e( 'newsletter.last_name' ); ?></label>
					<input id="ff-nl-last" name="<?php echo esc_attr( $ff_fields['last_name'] ?? 'last_name' ); ?>" type="text" autocomplete="family-name" required maxlength="80">
				</div>
				<div class="field">
					<label for="ff-nl-email"><?php ff_e( 'newsletter.email' ); ?></label>
					<input id="ff-nl-email" name="<?php echo esc_attr( $ff_fields['email'] ?? 'email' ); ?>" type="email" autocomplete="email" inputmode="email" required maxlength="80">
				</div>
				<button class="btn btn--primary newsletter__submit" type="submit"><?php ff_e( 'newsletter.submit' ); ?></button>
			</div>

			<?php // Honeypot: invisible to people. Bots that fill it are dropped by JS and flagged by a Pardot automation rule. ?>
			<div class="newsletter__hp" aria-hidden="true">
				<label for="ff-nl-hp">Website</label>
				<input id="ff-nl-hp" name="<?php echo esc_attr( $ff_fields['honeypot'] ?? 'website' ); ?>" type="text" tabindex="-1" autocomplete="off">
			</div>
			<?php if ( ! empty( $ff_fields['language'] ) ) : ?>
				<input type="hidden" name="<?php echo esc_attr( $ff_fields['language'] ); ?>" value="<?php echo esc_attr( ff_lang() ); ?>">
			<?php endif; ?>
			<?php if ( ! empty( $ff_fields['page'] ) ) : ?>
				<input type="hidden" name="<?php echo esc_attr( $ff_fields['page'] ); ?>" value="" data-ff-page-url>
			<?php endif; ?>

			<p class="newsletter__consent">
				<?php echo wp_kses( sprintf( (string) ff_t( 'newsletter.consent' ), $ff_privacy ), array( 'a' => array( 'href' => array() ) ) ); ?>
			</p>
			<p class="newsletter__status" role="status" aria-live="polite" data-ff-nl-status<?php echo $ff_done ? ' data-state="' . esc_attr( 'ok' === $ff_done ? 'ok' : 'error' ) . '"' : ''; ?>>
				<?php
				if ( 'ok' === $ff_done ) {
					ff_e( 'newsletter.success' );
				} elseif ( $ff_done ) {
					ff_e( 'newsletter.error' );
				}
				?>
			</p>
		</form>
	</div>
	</div>
</section>
<?php if ( ! ff_newsletter_action() ) : ?><!-- Newsletter: config.json → newsletter.action is empty, nothing is sent to Pardot yet. --><?php endif; ?>
