<?php
/**
 * Search form. Searches posts in the active language.
 */

defined( 'ABSPATH' ) || exit;
$ff_id = wp_unique_id( 'ff-search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( ff_home_url() ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $ff_id ); ?>"><?php ff_e( 'common.search_label' ); ?></label>
	<input class="search-form__input" type="search" id="<?php echo esc_attr( $ff_id ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo ff_attr( 'common.search_placeholder' ); ?>">
	<button class="btn btn--primary search-form__btn" type="submit"><?php echo ff_icon( 'search', 18 ); // phpcs:ignore ?><span class="screen-reader-text"><?php ff_e( 'common.search' ); ?></span></button>
</form>
