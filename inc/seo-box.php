<?php
/**
 * "SEO" meta box for pages and posts (native, replaces Yoast's box).
 *
 * Posts and legal pages: one set of fields (the post's own language).
 * Marketing pages (Home, About, Add-ons, Reviews, Contact…): one set per language (EN/ES/PT/FR),
 * because one WordPress page renders every language. Empty fields fall back to lang/*.json → seo.
 *
 * Post meta (EN / single set has no suffix; other languages add _{code}):
 *  _ff_seo_title[_es]        <title> and og:title
 *  _ff_seo_description[_es]  meta description and og:description
 *  _ff_seo_image[_es]        og:image URL (1200 × 630 recommended)
 *  _ff_noindex               1 = noindex (whole page)
 *
 * Old Yoast values (_yoast_wpseo_title, _yoast_wpseo_metadesc, _yoast_wpseo_opengraph-image) are read as a fallback
 * for English and pre-filled in the box, so imported content keeps its SEO until it is saved here.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta key for a field in a language ('' suffix for the default language or single-language posts).
 */
function ff_seo_key( string $field, string $lang = '' ): string {
	return '_ff_seo_' . $field . ( $lang && $lang !== ff_default_lang() ? '_' . $lang : '' );
}

/**
 * Does this page render several languages from JSON (marketing page / front page)?
 */
function ff_seo_is_multilang_page( int $post_id ): bool {
	if ( 'page' !== get_post_type( $post_id ) ) {
		return false;
	}
	if ( (int) get_option( 'page_on_front' ) === $post_id ) {
		return true;
	}
	$slug = (string) get_post_field( 'post_name', $post_id );
	return in_array( $slug, array_filter( (array) ff_t( 'routes', ff_default_lang() ) ), true );
}

/**
 * Yoast value with its basic %%variables%% replaced (only used as fallback for the default language).
 */
function ff_seo_yoast( int $post_id, string $field ): string {
	$keys  = array( 'title' => '_yoast_wpseo_title', 'description' => '_yoast_wpseo_metadesc', 'image' => '_yoast_wpseo_opengraph-image' );
	$value = isset( $keys[ $field ] ) ? (string) get_post_meta( $post_id, $keys[ $field ], true ) : '';
	if ( '' === $value || 'image' === $field ) {
		return $value;
	}
	$value = strtr(
		$value,
		array(
			'%%title%%'    => wp_strip_all_tags( get_the_title( $post_id ) ),
			'%%sitename%%' => (string) ff_t( 'seo.site_name' ),
			'%%sep%%'      => (string) ff_t( 'seo.separator' ),
			'%%page%%'     => '',
		)
	);
	$value = (string) preg_replace( '/%%[a-z_]+%%/i', '', $value ); // Unknown Yoast variables.
	return trim( (string) preg_replace( '/\s{2,}/', ' ', $value ) );
}

/**
 * Stored value for the front end: theme field → Yoast (default language only) → ''.
 */
function ff_seo_meta( int $post_id, string $field, string $lang = '' ): string {
	$value = (string) get_post_meta( $post_id, ff_seo_key( $field, $lang ), true );
	if ( '' === $value && ( '' === $lang || $lang === ff_default_lang() ) ) {
		$value = ff_seo_yoast( $post_id, $field );
	}
	return $value;
}

add_action(
	'init',
	static function (): void {
		$keys = array( '_ff_noindex' );
		foreach ( array( 'title', 'description', 'image' ) as $field ) {
			foreach ( ff_languages() as $lang ) {
				$keys[] = ff_seo_key( $field, $lang );
			}
		}
		foreach ( array( 'post', 'page' ) as $type ) {
			foreach ( array_unique( $keys ) as $key ) {
				register_post_meta(
					$type,
					$key,
					array(
						'type'          => '_ff_noindex' === $key ? 'integer' : 'string',
						'single'        => true,
						'show_in_rest'  => false,
						'auth_callback' => static fn( $allowed, $meta_key, $post_id ) => current_user_can( 'edit_post', $post_id ),
					)
				);
			}
		}
	}
);

add_action(
	'add_meta_boxes',
	static function ( string $post_type ): void {
		if ( in_array( $post_type, array( 'post', 'page' ), true ) ) {
			add_meta_box( 'ff-seo', __( 'SEO', 'franfast' ), 'ff_render_seo_box', $post_type, 'normal', 'high' );
		}
	}
);

add_action(
	'admin_enqueue_scripts',
	static function ( string $hook ): void {
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			wp_enqueue_media();
		}
	}
);

function ff_render_seo_box( WP_Post $post ): void {
	wp_nonce_field( 'ff_seo_box', 'ff_seo_box_nonce' );
	$multi = ff_seo_is_multilang_page( $post->ID );
	$langs = $multi ? ff_languages() : array( '' );
	$route = null;
	if ( $multi ) {
		$route = (int) get_option( 'page_on_front' ) === $post->ID ? 'home' : array_search( (string) $post->post_name, (array) ff_t( 'routes', ff_default_lang() ), true );
	}
	?>
	<style>
		.ff-seo__tabs { display: flex; gap: 4px; margin: 4px 0 12px; border-bottom: 1px solid #dcdcde; }
		.ff-seo__tab { border: 1px solid #dcdcde; border-bottom: 0; background: #f6f7f7; padding: 6px 14px; cursor: pointer; border-radius: 4px 4px 0 0; margin-bottom: -1px; }
		.ff-seo__tab[aria-selected="true"] { background: #fff; font-weight: 600; }
		.ff-seo__panel[hidden] { display: none; }
		.ff-seo__row { margin: 0 0 14px; }
		.ff-seo__row label { display: block; font-weight: 600; margin-bottom: 4px; }
		.ff-seo__row input[type=text], .ff-seo__row input[type=url], .ff-seo__row textarea { width: 100%; }
		.ff-seo__count { float: right; font-weight: 400; color: #646970; }
		.ff-seo__count.is-long { color: #b32d2e; }
		.ff-seo__img { display: flex; gap: 8px; align-items: center; }
		.ff-seo__img img { max-height: 60px; border: 1px solid #dcdcde; }
		.ff-seo__note { color: #646970; margin: 2px 0 0; }
	</style>
	<?php if ( $multi ) : ?>
		<p class="ff-seo__note"><?php esc_html_e( 'This page is shown in every language. Fill each tab; an empty field uses the default text from the theme (lang/*.json → seo).', 'franfast' ); ?></p>
		<div class="ff-seo__tabs" role="tablist">
			<?php foreach ( $langs as $i => $lang ) : ?>
				<button type="button" class="ff-seo__tab" role="tab" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" data-ff-seo-tab="<?php echo esc_attr( $lang ); ?>"><?php echo esc_html( ff_lang_meta( $lang, 'label' ) ); ?></button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php foreach ( $langs as $i => $lang ) : ?>
		<?php
		$code    = $lang ?: ff_default_lang();
		$title   = (string) get_post_meta( $post->ID, ff_seo_key( 'title', $lang ), true );
		$desc    = (string) get_post_meta( $post->ID, ff_seo_key( 'description', $lang ), true );
		$image   = (string) get_post_meta( $post->ID, ff_seo_key( 'image', $lang ), true );
		$from_yo = false;
		if ( $code === ff_default_lang() ) { // Pre-fill from Yoast so imported values can be reviewed and saved.
			if ( '' === $title && ff_seo_yoast( $post->ID, 'title' ) ) { $title = ff_seo_yoast( $post->ID, 'title' ); $from_yo = true; }
			if ( '' === $desc && ff_seo_yoast( $post->ID, 'description' ) ) { $desc = ff_seo_yoast( $post->ID, 'description' ); $from_yo = true; }
			if ( '' === $image && ff_seo_yoast( $post->ID, 'image' ) ) { $image = ff_seo_yoast( $post->ID, 'image' ); $from_yo = true; }
		}
		$ph_title = $route ? (string) ff_t( 'seo.' . $route . '.title', $code ) : wp_strip_all_tags( get_the_title( $post ) ) . ' | FranFast';
		$ph_desc  = $route ? (string) ff_t( 'seo.' . $route . '.description', $code ) : __( 'Empty = excerpt of the content.', 'franfast' );
		$sfx      = $lang ? '_' . $lang : '';
		?>
		<div class="ff-seo__panel" data-ff-seo-panel="<?php echo esc_attr( $lang ); ?>"<?php echo $i > 0 ? ' hidden' : ''; ?>>
			<?php if ( $from_yo ) : ?><p class="ff-seo__note"><em><?php esc_html_e( 'Pre-filled from Yoast. Review and update the page to keep it.', 'franfast' ); ?></em></p><?php endif; ?>
			<p class="ff-seo__row">
				<label for="ff_seo_title<?php echo esc_attr( $sfx ); ?>"><?php esc_html_e( 'SEO title', 'franfast' ); ?> <span class="ff-seo__count" data-ff-count-for="ff_seo_title<?php echo esc_attr( $sfx ); ?>" data-max="60"></span></label>
				<input type="text" name="ff_seo[<?php echo esc_attr( $code ); ?>][title]" id="ff_seo_title<?php echo esc_attr( $sfx ); ?>" value="<?php echo esc_attr( $title ); ?>" maxlength="90" placeholder="<?php echo esc_attr( $ph_title ); ?>">
				<span class="ff-seo__note"><?php esc_html_e( 'Also used as og:title. Recommended: 50–60 characters.', 'franfast' ); ?></span>
			</p>
			<p class="ff-seo__row">
				<label for="ff_seo_description<?php echo esc_attr( $sfx ); ?>"><?php esc_html_e( 'Meta description', 'franfast' ); ?> <span class="ff-seo__count" data-ff-count-for="ff_seo_description<?php echo esc_attr( $sfx ); ?>" data-max="155"></span></label>
				<textarea name="ff_seo[<?php echo esc_attr( $code ); ?>][description]" id="ff_seo_description<?php echo esc_attr( $sfx ); ?>" rows="3" maxlength="300" placeholder="<?php echo esc_attr( $ph_desc ); ?>"><?php echo esc_textarea( $desc ); ?></textarea>
				<span class="ff-seo__note"><?php esc_html_e( 'Also used as og:description. Recommended: 120–155 characters.', 'franfast' ); ?></span>
			</p>
			<div class="ff-seo__row">
				<label for="ff_seo_image<?php echo esc_attr( $sfx ); ?>"><?php esc_html_e( 'Social image (og:image)', 'franfast' ); ?></label>
				<div class="ff-seo__img">
					<input type="url" name="ff_seo[<?php echo esc_attr( $code ); ?>][image]" id="ff_seo_image<?php echo esc_attr( $sfx ); ?>" value="<?php echo esc_attr( $image ); ?>" placeholder="<?php echo esc_attr( 'post' === $post->post_type ? __( 'Empty = featured image', 'franfast' ) : (string) ff_config( 'og_image', '' ) ); ?>">
					<button type="button" class="button" data-ff-seo-media="ff_seo_image<?php echo esc_attr( $sfx ); ?>"><?php esc_html_e( 'Select image', 'franfast' ); ?></button>
				</div>
				<p class="ff-seo__note"><?php esc_html_e( '1200 × 630 px recommended. Empty = featured image (posts) or the site default.', 'franfast' ); ?></p>
			</div>
		</div>
	<?php endforeach; ?>

	<p class="ff-seo__row">
		<label style="font-weight:400"><input type="checkbox" name="ff_noindex" value="1" <?php checked( (bool) get_post_meta( $post->ID, '_ff_noindex', true ) ); ?>> <?php esc_html_e( 'Hide from search engines (noindex)', 'franfast' ); ?></label>
	</p>
	<script>
	(function () {
		var box = document.getElementById('ff-seo'); if (!box) { return; }
		box.querySelectorAll('[data-ff-seo-tab]').forEach(function (tab) {
			tab.addEventListener('click', function () {
				box.querySelectorAll('[data-ff-seo-tab]').forEach(function (t) { t.setAttribute('aria-selected', t === tab ? 'true' : 'false'); });
				box.querySelectorAll('[data-ff-seo-panel]').forEach(function (p) { p.hidden = p.getAttribute('data-ff-seo-panel') !== tab.getAttribute('data-ff-seo-tab'); });
			});
		});
		box.querySelectorAll('[data-ff-count-for]').forEach(function (c) {
			var field = document.getElementById(c.getAttribute('data-ff-count-for')), max = parseInt(c.getAttribute('data-max'), 10);
			var upd = function () { var n = field.value.length; c.textContent = n + ' / ' + max; c.classList.toggle('is-long', n > max); };
			field.addEventListener('input', upd); upd();
		});
		box.querySelectorAll('[data-ff-seo-media]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (!window.wp || !wp.media) { return; }
				var input = document.getElementById(btn.getAttribute('data-ff-seo-media'));
				var frame = wp.media({ title: btn.textContent, library: { type: 'image' }, multiple: false });
				frame.on('select', function () { var a = frame.state().get('selection').first().toJSON(); input.value = a.url; });
				frame.open();
			});
		});
	})();
	</script>
	<?php
}

add_action(
	'save_post',
	static function ( int $post_id, WP_Post $post ): void {
		if ( ! in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
			return;
		}
		if ( ! isset( $_POST['ff_seo_box_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ff_seo_box_nonce'] ) ), 'ff_seo_box' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$data  = isset( $_POST['ff_seo'] ) && is_array( $_POST['ff_seo'] ) ? wp_unslash( $_POST['ff_seo'] ) : array(); // phpcs:ignore -- sanitized per field below.
		$multi = ff_seo_is_multilang_page( $post_id );
		foreach ( $data as $code => $fields ) {
			$code = sanitize_key( $code );
			if ( ! ff_is_lang( $code ) || ! is_array( $fields ) ) {
				continue;
			}
			$lang   = $multi ? $code : '';
			$values = array(
				'title'       => sanitize_text_field( (string) ( $fields['title'] ?? '' ) ),
				'description' => sanitize_textarea_field( (string) ( $fields['description'] ?? '' ) ),
				'image'       => esc_url_raw( (string) ( $fields['image'] ?? '' ) ),
			);
			foreach ( $values as $field => $value ) {
				$key = ff_seo_key( $field, $lang );
				'' !== $value ? update_post_meta( $post_id, $key, $value ) : delete_post_meta( $post_id, $key );
			}
		}
		! empty( $_POST['ff_noindex'] ) ? update_post_meta( $post_id, '_ff_noindex', 1 ) : delete_post_meta( $post_id, '_ff_noindex' );
	},
	10,
	2
);
