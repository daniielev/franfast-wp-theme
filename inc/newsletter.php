<?php
/**
 * Newsletter (Pardot Form Handler) helpers + the tiny Success/Error Location endpoint.
 *
 * Pardot Form Handler settings:
 *   Success Location: https://franfast.io/?ff_nl=ok
 *   Error Location:   https://franfast.io/?ff_nl=error
 */

defined( 'ABSPATH' ) || exit;

function ff_newsletter_enabled(): bool {
	return ! empty( ff_config( 'newsletter.enabled' ) );
}

/**
 * Form Handler URL, or '' while the handler does not exist yet (form shows, nothing is sent).
 */
function ff_newsletter_action(): string {
	$action = (string) ff_config( 'newsletter.action', '' );
	return ( $action && wp_http_validate_url( $action ) ) ? $action : '';
}

/**
 * Origins allowed to report the result (this site + any extra in config, e.g. production while testing locally).
 */
function ff_newsletter_origins(): array {
	$origins = array( ff_origin( home_url( '/' ) ) );
	foreach ( (array) ff_config( 'newsletter.result_origins', array() ) as $url ) {
		$origins[] = ff_origin( (string) $url );
	}
	return array_values( array_unique( array_filter( $origins ) ) );
}

function ff_origin( string $url ): string {
	$p = wp_parse_url( $url );
	if ( empty( $p['scheme'] ) || empty( $p['host'] ) ) {
		return '';
	}
	return $p['scheme'] . '://' . $p['host'] . ( isset( $p['port'] ) ? ':' . $p['port'] : '' );
}

/**
 * /?ff_nl=ok|error → minimal page (no theme, no tracking) that reports to the parent page.
 * Opened top-level (no JS on the form) it sends the visitor back to the home page with the message.
 */
add_action(
	'template_redirect',
	static function (): void {
		if ( ! isset( $_GET['ff_nl'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		$state = 'ok' === sanitize_key( wp_unslash( $_GET['ff_nl'] ) ) ? 'ok' : 'error'; // phpcs:ignore
		$back  = add_query_arg( 'ff_nl_done', $state, ff_home_url() ) . '#newsletter';

		nocache_headers();
		header( 'X-Robots-Tag: noindex, nofollow', true );
		header( 'Content-Type: text/html; charset=utf-8' );
		?>
<!doctype html><html><head><meta charset="utf-8"><meta name="robots" content="noindex,nofollow"><title>Newsletter</title><noscript><meta http-equiv="refresh" content="0;url=<?php echo esc_attr( $back ); ?>"></noscript></head><body>
<script>
(function () {
  var state = <?php echo wp_json_encode( $state ); ?>;
  if (window.parent && window.parent !== window) {
    window.parent.postMessage({ ffNewsletter: state }, '*');
  } else {
    window.location.replace(<?php echo wp_json_encode( $back ); ?>);
  }
})();
</script>
<noscript><p><a href="<?php echo esc_url( $back ); ?>">FranFast</a></p></noscript>
</body></html>
		<?php
		exit;
	},
	0
);

/**
 * Pages with ?ff_nl_done are messages, not content.
 */
add_filter(
	'wp_robots',
	static function ( array $robots ): array {
		if ( isset( $_GET['ff_nl_done'] ) ) { // phpcs:ignore
			$robots['noindex'] = true;
		}
		return $robots;
	}
);
