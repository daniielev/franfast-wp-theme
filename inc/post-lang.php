<?php
/**
 * "Language" box for posts (native meta box, no plugin). SEO fields live in the "SEO" box (inc/seo-box.php).
 *
 * Fields (post meta):
 *  _ff_lang             en | es | pt (empty = en)
 *  _ff_translation_of   ID of the English original (only for es/pt posts)
 */

defined( 'ABSPATH' ) || exit;

const FF_META_KEYS = array( '_ff_lang', '_ff_translation_of' );

add_action(
	'init',
	static function (): void {
		foreach ( FF_META_KEYS as $key ) {
			register_post_meta(
				'post',
				$key,
				array(
					'type'          => '_ff_translation_of' === $key || '_ff_noindex' === $key ? 'integer' : 'string',
					'single'        => true,
					'show_in_rest'  => false,
					'auth_callback' => static fn( $allowed, $meta_key, $post_id ) => current_user_can( 'edit_post', $post_id ),
				)
			);
		}
	}
);

add_action(
	'add_meta_boxes_post',
	static function (): void {
		add_meta_box( 'ff-lang-seo', __( 'Language', 'franfast' ), 'ff_render_lang_box', 'post', 'side', 'high' );
	}
);

function ff_render_lang_box( WP_Post $post ): void {
	wp_nonce_field( 'ff_lang_seo', 'ff_lang_seo_nonce' );
	// Keep the selects inside the narrow editor sidebar (long post titles made the panel scroll sideways).
	echo '<style>#ff-lang-seo .inside{overflow-x:hidden}#ff-lang-seo select{display:block;width:100%;max-width:100%;min-width:0;box-sizing:border-box;text-overflow:ellipsis}#ff-lang-seo .description{display:block;overflow-wrap:anywhere}</style>';
	$lang   = ff_post_lang( $post->ID );
	$of     = (int) get_post_meta( $post->ID, '_ff_translation_of', true );

	$originals = get_posts(
		array(
			'post_type'        => 'post',
			'post_status'      => array( 'publish', 'draft', 'future', 'pending', 'private' ),
			'numberposts'      => 300,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'exclude'          => array( $post->ID ),
			'meta_query'       => ff_lang_meta_query( ff_default_lang() ), // phpcs:ignore
			'suppress_filters' => true,
		)
	);
	?>
	<p>
		<label for="ff_lang"><strong><?php esc_html_e( 'Language', 'franfast' ); ?></strong></label><br>
		<select name="ff_lang" id="ff_lang" style="width:100%">
			<?php foreach ( ff_languages() as $code ) : ?>
				<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $lang, $code ); ?>><?php echo esc_html( ff_lang_meta( $code, 'name' ) ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p class="ff-translation-of">
		<label for="ff_translation_of"><strong><?php esc_html_e( 'Translation of (English original)', 'franfast' ); ?></strong></label><br>
		<select name="ff_translation_of" id="ff_translation_of" style="width:100%">
			<option value="0"><?php esc_html_e( '— None —', 'franfast' ); ?></option>
			<?php foreach ( $originals as $original ) : ?>
				<option value="<?php echo (int) $original->ID; ?>" <?php selected( $of, $original->ID ); ?>><?php echo esc_html( wp_trim_words( get_the_title( $original ), 7 ) ); ?></option>
			<?php endforeach; ?>
		</select>
		<span class="description"><?php esc_html_e( 'Only for posts in another language (ES, PT, FR). Links the versions for the language switcher and hreflang.', 'franfast' ); ?></span>
	</p>
	<?php
	$translations = 'publish' === $post->post_status ? ff_post_translations( $post->ID ) : array();
	unset( $translations[ $lang ] );
	if ( $translations ) :
		?>
		<p><strong><?php esc_html_e( 'Linked versions', 'franfast' ); ?></strong><br>
		<?php foreach ( $translations as $code => $id ) : ?>
			<?php echo esc_html( strtoupper( $code ) ); ?>: <a href="<?php echo esc_url( (string) get_edit_post_link( $id ) ); ?>"><?php echo esc_html( wp_trim_words( get_the_title( $id ), 8 ) ); ?></a><br>
		<?php endforeach; ?>
		</p>
	<?php endif; ?>
	<?php
}

add_action(
	'save_post_post',
	static function ( int $post_id ): void {
		if ( ! isset( $_POST['ff_lang_seo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ff_lang_seo_nonce'] ) ), 'ff_lang_seo' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$lang = isset( $_POST['ff_lang'] ) ? sanitize_key( wp_unslash( $_POST['ff_lang'] ) ) : ff_default_lang();
		$lang = ff_is_lang( $lang ) ? $lang : ff_default_lang();
		update_post_meta( $post_id, '_ff_lang', $lang );

		$of = isset( $_POST['ff_translation_of'] ) ? absint( $_POST['ff_translation_of'] ) : 0;
		if ( $lang === ff_default_lang() || ! $of || $of === $post_id || ff_post_lang( $of ) !== ff_default_lang() ) {
			delete_post_meta( $post_id, '_ff_translation_of' );
		} else {
			// One translation per language per original.
			$taken = get_posts(
				array(
					'post_type'        => 'post',
					'post_status'      => 'any',
					'fields'           => 'ids',
					'numberposts'      => 1,
					'exclude'          => array( $post_id ),
					'meta_query'       => array( // phpcs:ignore
						array( 'key' => '_ff_translation_of', 'value' => $of ),
						array( 'key' => '_ff_lang', 'value' => $lang ),
					),
					'suppress_filters' => true,
				)
			);
			if ( $taken ) {
				set_transient( 'ff_lang_notice_' . get_current_user_id(), (int) $taken[0], 60 );
			} else {
				update_post_meta( $post_id, '_ff_translation_of', $of );
			}
		}

	}
);

add_action(
	'admin_notices',
	static function (): void {
		$key   = 'ff_lang_notice_' . get_current_user_id();
		$taken = (int) get_transient( $key );
		if ( ! $taken ) {
			return;
		}
		delete_transient( $key );
		printf(
			'<div class="notice notice-warning is-dismissible"><p>%s <a href="%s">%s</a></p></div>',
			esc_html__( 'Not linked: that original already has a translation in this language.', 'franfast' ),
			esc_url( (string) get_edit_post_link( $taken ) ),
			esc_html( get_the_title( $taken ) )
		);
	}
);

/**
 * Posts list: language column and filter.
 */
add_filter(
	'manage_post_posts_columns',
	static function ( array $columns ): array {
		$columns['ff_lang'] = __( 'Lang', 'franfast' );
		return $columns;
	}
);

add_action(
	'manage_post_posts_custom_column',
	static function ( string $column, int $post_id ): void {
		if ( 'ff_lang' === $column ) {
			echo esc_html( strtoupper( ff_post_lang( $post_id ) ) );
		}
	},
	10,
	2
);

add_action(
	'restrict_manage_posts',
	static function ( string $post_type ): void {
		if ( 'post' !== $post_type ) {
			return;
		}
		$current = isset( $_GET['ff_lang_filter'] ) ? sanitize_key( wp_unslash( $_GET['ff_lang_filter'] ) ) : ''; // phpcs:ignore
		echo '<select name="ff_lang_filter"><option value="">' . esc_html__( 'All languages', 'franfast' ) . '</option>';
		foreach ( ff_languages() as $code ) {
			printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $code ), selected( $current, $code, false ), esc_html( ff_lang_meta( $code, 'name' ) ) );
		}
		echo '</select>';
	}
);

add_action(
	'pre_get_posts',
	static function ( WP_Query $q ): void {
		if ( ! is_admin() || ! $q->is_main_query() || 'post' !== $q->get( 'post_type' ) || empty( $_GET['ff_lang_filter'] ) ) { // phpcs:ignore
			return;
		}
		$lang = sanitize_key( wp_unslash( $_GET['ff_lang_filter'] ) ); // phpcs:ignore
		if ( ff_is_lang( $lang ) ) {
			$q->set( 'meta_query', ff_lang_meta_query( $lang ) );
		}
	}
);

/**
 * "Create ES/PT version" row actions on English posts (native replacement for Yoast Duplicate Post).
 * Copies title, content, excerpt, categories, tags and featured image into a draft linked to the original.
 */
add_filter(
	'post_row_actions',
	static function ( array $actions, WP_Post $post ): array {
		if ( 'post' !== $post->post_type || ff_post_lang( $post->ID ) !== ff_default_lang() || ! current_user_can( 'edit_post', $post->ID ) ) {
			return $actions;
		}
		$existing = ff_translation_ids_any_status( $post->ID );
		foreach ( ff_languages() as $code ) {
			if ( $code === ff_default_lang() ) {
				continue;
			}
			$label = strtoupper( $code );
			if ( isset( $existing[ $code ] ) ) {
				$actions[ 'ff_edit_' . $code ] = sprintf( '<a href="%s">%s</a>', esc_url( (string) get_edit_post_link( $existing[ $code ] ) ), esc_html( sprintf( __( 'Edit %s version', 'franfast' ), $label ) ) );
			} else {
				$url = wp_nonce_url( admin_url( 'admin-post.php?action=ff_translate&post=' . $post->ID . '&lang=' . $code ), 'ff_translate_' . $post->ID . '_' . $code );
				$actions[ 'ff_new_' . $code ] = sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( sprintf( __( 'Create %s version', 'franfast' ), $label ) ) );
			}
		}
		return $actions;
	},
	10,
	2
);

/**
 * Translations of an English original in any status: [ lang => id ].
 */
function ff_translation_ids_any_status( int $original ): array {
	$ids = get_posts(
		array(
			'post_type'        => 'post',
			'post_status'      => array( 'publish', 'draft', 'pending', 'future', 'private' ),
			'fields'           => 'ids',
			'numberposts'      => 20,
			'meta_key'         => '_ff_translation_of', // phpcs:ignore
			'meta_value'       => $original,            // phpcs:ignore
			'suppress_filters' => true,
		)
	);
	$map = array();
	foreach ( $ids as $id ) {
		$map[ ff_post_lang( (int) $id ) ] = (int) $id;
	}
	return $map;
}

add_action(
	'admin_post_ff_translate',
	static function (): void {
		$id   = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		$lang = isset( $_GET['lang'] ) ? sanitize_key( wp_unslash( $_GET['lang'] ) ) : '';
		check_admin_referer( 'ff_translate_' . $id . '_' . $lang );
		$src = get_post( $id );
		if ( ! $src || 'post' !== $src->post_type || ! ff_is_lang( $lang ) || $lang === ff_default_lang() || ! current_user_can( 'edit_post', $id ) || ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'This translation cannot be created.', 'franfast' ) );
		}
		$existing = ff_translation_ids_any_status( $id );
		if ( isset( $existing[ $lang ] ) ) {
			wp_safe_redirect( (string) get_edit_post_link( $existing[ $lang ], 'raw' ) );
			exit;
		}
		$new = wp_insert_post(
			wp_slash(
				array(
					'post_type'    => 'post',
					'post_status'  => 'draft',
					'post_author'  => get_current_user_id(),
					'post_title'   => $src->post_title,
					'post_content' => $src->post_content,
					'post_excerpt' => $src->post_excerpt,
					'post_name'    => '',
				)
			),
			true
		);
		if ( is_wp_error( $new ) ) {
			wp_die( esc_html( $new->get_error_message() ) );
		}
		wp_set_post_categories( $new, wp_get_post_categories( $id ) );
		wp_set_post_tags( $new, wp_get_post_tags( $id, array( 'fields' => 'names' ) ) );
		$thumb = get_post_thumbnail_id( $id );
		if ( $thumb ) {
			set_post_thumbnail( $new, $thumb );
		}
		update_post_meta( $new, '_ff_lang', $lang );
		update_post_meta( $new, '_ff_translation_of', $id );
		wp_safe_redirect( (string) get_edit_post_link( $new, 'raw' ) );
		exit;
	}
);

/**
 * Block editor sidebar: long values (an e-mail used as author name, long slugs, post titles in our selects)
 * made the whole sidebar scroll sideways. Let them wrap inside the panel.
 */
add_action(
	'admin_head-post.php',
	'ff_editor_sidebar_fix'
);
add_action(
	'admin_head-post-new.php',
	'ff_editor_sidebar_fix'
);
function ff_editor_sidebar_fix(): void {
	echo '<style>.interface-complementary-area,.editor-sidebar{overflow-x:hidden}'
		. '.editor-post-panel__row,.editor-post-panel__row-control{min-width:0}'
		. '.editor-post-panel__row-control,.editor-post-panel__row-control .components-button{max-width:100%;white-space:normal;overflow-wrap:anywhere;word-break:break-word;height:auto;text-align:left}'
		. '.edit-post-meta-boxes-area #poststuff .postbox .inside{overflow-x:hidden}</style>';
}
