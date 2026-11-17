<?php
/**
 * Shared listing layout: blog home, news, category, tag, search and date archives.
 * Args: eyebrow, title, lead, query (WP_Query|null = main query), empty, filters (bool),
 *       active_term (int: 0 = "All posts" active, term_id = that category, -1 = none).
 */

defined( 'ABSPATH' ) || exit;

$ff_args  = wp_parse_args(
	$args ?? array(),
	array( 'eyebrow' => '', 'title' => '', 'lead' => '', 'query' => null, 'empty' => ff_t( 'blog.empty_text' ), 'filters' => true, 'active_term' => 0 )
);
$ff_query = $ff_args['query'] instanceof WP_Query ? $ff_args['query'] : $GLOBALS['wp_query'];
?>
<section class="listing-head">
	<div class="container listing-head__inner">
		<?php if ( $ff_args['eyebrow'] ) : ?><p class="eyebrow"><?php echo esc_html( $ff_args['eyebrow'] ); ?></p><?php endif; ?>
		<h1 class="h1 listing-head__title"><?php echo esc_html( $ff_args['title'] ); ?></h1>
		<?php if ( $ff_args['lead'] ) : ?><p class="lead"><?php echo esc_html( $ff_args['lead'] ); ?></p><?php endif; ?>
		<?php get_search_form(); ?>
	</div>
</section>

<section class="section listing">
	<div class="container stack-md">
		<?php if ( $ff_args['filters'] ) : ?>
			<?php
			$ff_terms = ff_categories_with_posts( ff_lang() );
			$ff_bc    = get_category_by_slug( ff_blog_category() );
			if ( $ff_bc && ff_term_has_posts( $ff_bc, ff_lang() ) ) {
				array_unshift( $ff_terms, $ff_bc ); // "Blog" right after "All posts".
			}
			?>
			<?php if ( count( $ff_terms ) >= 1 ) : ?>
				<nav class="chips" aria-label="<?php echo ff_attr( 'blog.filter_label' ); ?>">
					<ul>
						<li><a class="chip<?php echo 0 === (int) $ff_args['active_term'] ? ' is-active' : ''; ?>" href="<?php echo esc_url( ff_url( 'blog' ) ); ?>"<?php echo 0 === (int) $ff_args['active_term'] ? ' aria-current="page"' : ''; ?>><?php ff_e( 'blog.all_posts' ); ?></a></li>
						<?php foreach ( $ff_terms as $ff_term ) : ?>
							<?php $ff_on = (int) $ff_args['active_term'] === (int) $ff_term->term_id; ?>
							<li><a class="chip<?php echo $ff_on ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $ff_term ) ); ?>"<?php echo $ff_on ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $ff_term->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $ff_query->have_posts() ) : ?>
			<ul class="post-grid">
				<?php
				while ( $ff_query->have_posts() ) :
					$ff_query->the_post();
					get_template_part( 'template-parts/post-card' );
				endwhile;
				wp_reset_postdata();
				?>
			</ul>
			<?php
			$ff_links = paginate_links(
				array(
					'total'     => (int) $ff_query->max_num_pages,
					'current'   => max( 1, (int) get_query_var( 'paged' ) ),
					'type'      => 'list',
					'mid_size'  => 1,
					'prev_text' => ff_icon( 'arrow', 16 ) . '<span class="screen-reader-text">' . esc_html( ff_t( 'blog.prev' ) ) . '</span>',
					'next_text' => '<span class="screen-reader-text">' . esc_html( ff_t( 'blog.next' ) ) . '</span>' . ff_icon( 'arrow', 16 ),
				)
			);
			if ( $ff_links ) {
				echo '<nav class="pagination" aria-label="' . esc_attr( ff_t( 'blog.pagination' ) ) . '">' . $ff_links . '</nav>'; // phpcs:ignore
			}
			?>
		<?php else : ?>
			<div class="empty-state">
				<span class="empty-state__icon"><?php echo ff_icon( 'search', 28 ); // phpcs:ignore ?></span>
				<h2 class="h3"><?php ff_e( 'blog.empty_title' ); ?></h2>
				<p><?php echo esc_html( $ff_args['empty'] ); ?></p>
				<a class="btn btn--secondary" href="<?php echo esc_url( ff_url( 'blog' ) ); ?>"><?php ff_e( 'blog.back' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>
