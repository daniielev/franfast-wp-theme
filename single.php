<?php
/**
 * Single blog post. Content comes from the WordPress editor; the frame follows the FranFast design.
 */

defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$ff_id    = get_the_ID();
	$ff_cats  = get_the_category();
	$ff_cat   = $ff_cats ? $ff_cats[0] : null;
	$ff_trans = ff_post_translations( $ff_id );
	unset( $ff_trans[ ff_lang() ] );
	$ff_url   = rawurlencode( (string) get_permalink() );
	$ff_title = rawurlencode( wp_strip_all_tags( get_the_title() ) );
	?>
	<article <?php post_class( 'post' ); ?>>
		<header class="post-head<?php echo has_post_thumbnail() ? ' post-head--with-cover' : ''; ?>">
			<div class="container container--narrow post-head__inner">
				<nav class="breadcrumb" aria-label="Breadcrumb">
					<ol>
						<li><a href="<?php echo esc_url( ff_home_url() ); ?>"><?php ff_e( 'breadcrumbs.home' ); ?></a></li>
						<?php if ( $ff_cat ) : ?>
							<li><a href="<?php echo esc_url( ff_blog_category() === $ff_cat->slug ? ff_url( 'blog' ) : get_term_link( $ff_cat ) ); ?>"><?php echo esc_html( $ff_cat->name ); ?></a></li>
						<?php else : ?>
							<li><a href="<?php echo esc_url( ff_url( 'blog' ) ); ?>"><?php ff_e( 'breadcrumbs.blog' ); ?></a></li>
						<?php endif; ?>
					</ol>
				</nav>
				<h1 class="post-head__title"><?php the_title(); ?></h1>
				<p class="post-head__meta">
					<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( ff_date( get_the_date( 'U' ) ) ); ?></time>
				</p>
				<?php if ( $ff_trans ) : ?>
					<p class="post-head__langs"><?php ff_e( 'blog.also_in' ); ?>:
						<?php foreach ( $ff_trans as $ff_code => $ff_pid ) : ?>
							<a href="<?php echo esc_url( get_permalink( $ff_pid ) ); ?>" hreflang="<?php echo esc_attr( $ff_code ); ?>" lang="<?php echo esc_attr( $ff_code ); ?>"><?php echo esc_html( ff_lang_meta( $ff_code, 'name' ) ); ?></a>
						<?php endforeach; ?>
					</p>
				<?php endif; ?>
			</div>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="post-cover">
				<?php the_post_thumbnail( 'full', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async', 'sizes' => '(max-width: 1100px) 100vw, 1040px' ) ); ?>
			</figure>
		<?php endif; ?>

		<div class="post-paper-wrap">
		<div class="container post-paper">
			<div class="prose post-content">
				<?php the_content(); ?>
			</div>

			<footer class="post-foot">
				<?php $ff_tags = get_the_tags(); ?>
				<?php if ( $ff_tags ) : ?>
					<ul class="chips chips--sm">
						<?php foreach ( $ff_tags as $ff_tag ) : ?>
							<li><a class="chip" href="<?php echo esc_url( get_term_link( $ff_tag ) ); ?>">#<?php echo esc_html( $ff_tag->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<div class="share">
					<span class="share__label"><?php ff_e( 'blog.share' ); ?></span>
					<a class="share__btn" href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo esc_attr( $ff_url ); ?>" target="_blank" rel="noopener" aria-label="LinkedIn <?php echo ff_attr( 'common.opens_new_tab' ); ?>"><?php echo ff_icon( 'linkedin', 18 ); // phpcs:ignore ?></a>
					<a class="share__btn" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr( $ff_url ); ?>" target="_blank" rel="noopener" aria-label="Facebook <?php echo ff_attr( 'common.opens_new_tab' ); ?>"><?php echo ff_icon( 'facebook', 18 ); // phpcs:ignore ?></a>
					<a class="share__btn" href="https://x.com/intent/post?url=<?php echo esc_attr( $ff_url ); ?>&amp;text=<?php echo esc_attr( $ff_title ); ?>" target="_blank" rel="noopener" aria-label="X <?php echo ff_attr( 'common.opens_new_tab' ); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.8 3h3.1l-6.8 7.7L22 21h-6.2l-4.9-6.4L5.3 21H2.2l7.3-8.3L2 3h6.4l4.4 5.8zm-1.1 16.2h1.7L7.4 4.7H5.6z"/></svg></a>
					<button type="button" class="share__btn" data-ff-copy="<?php echo esc_url( get_permalink() ); ?>" data-copied="<?php echo ff_attr( 'blog.copied' ); ?>" aria-label="<?php echo ff_attr( 'blog.copy_link' ); ?>"><?php echo ff_icon( 'external', 18 ); // phpcs:ignore ?></button>
					<span class="share__status" role="status" aria-live="polite"></span>
				</div>
			</footer>
		</div>
		</div>
	</article>

	<?php
	$ff_adjacent = static function ( string $dir ) use ( $ff_id ) {
		$q = new WP_Query(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => 1,
				'post__not_in'        => array( $ff_id ),
				'orderby'             => 'date',
				'order'               => 'prev' === $dir ? 'DESC' : 'ASC',
				'date_query'          => array( array( 'prev' === $dir ? 'before' : 'after' => get_the_date( 'Y-m-d H:i:s', $ff_id ), 'inclusive' => false ) ),
				'meta_query'          => ff_lang_meta_query( ff_lang() ), // phpcs:ignore
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		return $q->posts ? $q->posts[0] : null;
	};
	$ff_prev = $ff_adjacent( 'prev' );
	$ff_next = $ff_adjacent( 'next' );
	if ( $ff_prev || $ff_next ) :
		?>
		<div class="post-nav-wrap"><nav class="post-nav container container--narrow" aria-label="<?php echo ff_attr( 'blog.pagination' ); ?>">
			<?php if ( $ff_prev ) : ?>
				<a class="post-nav__link post-nav__link--prev" href="<?php echo esc_url( get_permalink( $ff_prev ) ); ?>" rel="prev">
					<span class="post-nav__label"><?php echo ff_icon( 'arrow', 16 ); // phpcs:ignore ?> <?php ff_e( 'blog.prev_post' ); ?></span>
					<span class="post-nav__title"><?php echo esc_html( get_the_title( $ff_prev ) ); ?></span>
				</a>
			<?php else : ?><span></span><?php endif; ?>
			<?php if ( $ff_next ) : ?>
				<a class="post-nav__link post-nav__link--next" href="<?php echo esc_url( get_permalink( $ff_next ) ); ?>" rel="next">
					<span class="post-nav__label"><?php ff_e( 'blog.next_post' ); ?> <?php echo ff_icon( 'arrow', 16 ); // phpcs:ignore ?></span>
					<span class="post-nav__title"><?php echo esc_html( get_the_title( $ff_next ) ); ?></span>
				</a>
			<?php endif; ?>
		</nav></div>
		<?php
	endif;
endwhile;

get_footer();
