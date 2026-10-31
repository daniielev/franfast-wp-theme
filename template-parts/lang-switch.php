<?php
/**
 * Language switcher: crawlable links to the same page in each language.
 * If the current page has no version in a language, the link goes to that language's home.
 */

defined( 'ABSPATH' ) || exit;
$ff_current = ff_lang();
?>
<nav class="lang-switch" aria-label="<?php echo ff_attr( 'common.language' ); ?>">
	<ul class="lang-switch__list">
		<?php foreach ( ff_languages() as $code ) : ?>
			<?php
			$url = $code === $ff_current ? '' : ( ff_alternate_url( $code ) ?? ff_home_url( $code ) );
			$name = ff_lang_meta( $code, 'name' );
			?>
			<li>
				<?php if ( $code === $ff_current ) : ?>
					<span class="lang-switch__item is-current" aria-current="true" lang="<?php echo esc_attr( ff_lang_meta( $code, 'hreflang' ) ); ?>" title="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( ff_lang_meta( $code, 'label' ) ); ?></span>
				<?php else : ?>
					<a class="lang-switch__item" href="<?php echo esc_url( $url ); ?>" hreflang="<?php echo esc_attr( ff_lang_meta( $code, 'hreflang' ) ); ?>" lang="<?php echo esc_attr( ff_lang_meta( $code, 'hreflang' ) ); ?>" title="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( ff_lang_meta( $code, 'label' ) ); ?></a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
