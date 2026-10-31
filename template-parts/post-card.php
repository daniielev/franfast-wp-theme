<?php
/**
 * Post card used in every listing (blog, news, category, tag, search, related).
 */

defined( 'ABSPATH' ) || exit;
$ff_cat = get_the_category();
$ff_cat = $ff_cat ? $ff_cat[0] : null;
?>
<li class="post-card">
	<a class="post-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'ff-card', array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ) ); ?>
		<?php else : ?>
			<span class="post-card__placeholder"><?php echo ff_icon( 'ai', 32 ); // phpcs:ignore ?></span>
		<?php endif; ?>
	</a>
	<div class="post-card__body">
		<p class="post-card__meta">
			<?php if ( $ff_cat ) : ?><span class="post-card__cat"><?php echo esc_html( $ff_cat->name ); ?></span> · <?php endif; ?>
			<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( ff_date( get_the_date( 'U' ) ) ); ?></time>
		</p>
		<h3 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p class="post-card__excerpt"><?php echo esc_html( ff_excerpt_for( get_the_ID(), 150 ) ); ?></p>
		<span class="post-card__more" aria-hidden="true"><?php ff_e( 'blog.read_more' ); ?> <?php echo ff_icon( 'arrow', 16 ); // phpcs:ignore ?></span>
	</div>
</li>
