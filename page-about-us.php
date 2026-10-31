<?php
/**
 * About (/about-us/, /es/sobre-nosotros/, /pt/sobre-nos/).
 * Copy: lang/*.json → about (EN verbatim from production; FAQ left out on purpose, it is outdated).
 * Data: config.json → about (video, Pledge 1% image, Fast Cloud "about" URL for the Read More links).
 */

defined( 'ABSPATH' ) || exit;
get_header();

$ff_about   = (array) ff_config( 'about', array() );
$ff_company = (string) ( $ff_about['company_url'] ?? '' );
$ff_video   = (string) ( $ff_about['video_url'] ?? '' );
$ff_ext     = static function ( string $label ) use ( $ff_company ): string {
	if ( ! $ff_company ) {
		return '';
	}
	return '<a class="btn btn--secondary" href="' . esc_url( $ff_company ) . '" target="_blank" rel="noopener">' . esc_html( $label ) . ' ' . ff_icon( 'external', 16 ) . '<span class="screen-reader-text"> ' . esc_html( (string) ff_t( 'common.opens_new_tab' ) ) . '</span></a>';
};
?>
<section class="listing-head">
	<div class="container listing-head__inner">
		<p class="eyebrow"><?php ff_e( 'about.hero.eyebrow' ); ?></p>
		<h1 class="h1 listing-head__title"><?php ff_e( 'about.hero.title' ); ?></h1>
	</div>
</section>

<section class="section">
	<div class="container split split--center">
		<div class="split__main stack-md">
			<p class="lead"><?php ff_e( 'about.hero.text' ); ?></p>
			<div class="about-mission">
				<p class="about-mission__label"><?php ff_e( 'about.mission.label' ); ?></p>
				<h2 class="about-mission__title"><?php ff_e( 'about.mission.title' ); ?></h2>
				<p><?php ff_e( 'about.mission.text' ); ?></p>
			</div>
			<?php echo $ff_ext( (string) ff_t( 'about.mission.cta' ) ); // phpcs:ignore ?>
		</div>
		<div class="split__side">
			<div class="video-card about-video" data-ff-video="<?php echo esc_attr( $ff_video ); ?>">
				<?php if ( $ff_video ) : ?>
					<button type="button" class="video-card__play" aria-label="<?php echo ff_attr( 'about.video.play' ); ?>" data-ff-video-play><?php echo ff_icon( 'play', 24 ); // phpcs:ignore ?></button>
				<?php endif; ?>
				<p class="video-card__title"><?php ff_e( 'about.video.title' ); ?></p>
				<p class="video-card__text"><?php ff_e( 'about.video.text' ); ?></p>
			</div>
		</div>
	</div>
</section>

<section class="section section--gray">
	<div class="container stack-md">
		<header class="section-head section-head--wide">
			<p class="eyebrow"><?php ff_e( 'about.experience.eyebrow' ); ?></p>
			<h2 class="h2"><?php ff_e( 'about.experience.title' ); ?></h2>
			<p class="lead lead--sm"><?php ff_e( 'about.experience.text' ); ?></p>
		</header>
		<ul class="about-points">
			<?php foreach ( (array) ff_t( 'about.experience.points' ) as $ff_point ) : ?>
				<li><?php echo ff_icon( 'check', 22 ); // phpcs:ignore ?><span><?php echo esc_html( (string) $ff_point ); ?></span></li>
			<?php endforeach; ?>
		</ul>
		<div><?php echo $ff_ext( (string) ff_t( 'about.experience.cta' ) ); // phpcs:ignore ?></div>
	</div>
</section>

<section class="section">
	<div class="container split split--center">
		<?php if ( ! empty( $ff_about['pledge_image'] ) ) : ?>
			<div class="split__side">
				<img class="about-pledge__img" src="<?php echo esc_url( ff_img_src( (string) $ff_about['pledge_image'] ) ); ?>" alt="" width="<?php echo (int) ( $ff_about['pledge_image_width'] ?? 0 ); ?>" height="<?php echo (int) ( $ff_about['pledge_image_height'] ?? 0 ); ?>" loading="lazy" decoding="async">
			</div>
		<?php endif; ?>
		<div class="split__main stack-sm">
			<p class="eyebrow"><?php ff_e( 'about.pledge.eyebrow' ); ?></p>
			<h2 class="h2"><?php ff_e( 'about.pledge.title' ); ?></h2>
			<p class="lead lead--sm"><?php ff_e( 'about.pledge.text' ); ?></p>
			<div class="card card--sm">
				<h3 class="h3"><?php ff_e( 'about.pledge.item_title' ); ?></h3>
				<p><?php ff_e( 'about.pledge.item_text' ); ?></p>
			</div>
		</div>
	</div>
</section>

<section class="section section--navy">
	<div class="container stack-md">
		<header class="section-head section-head--wide">
			<p class="eyebrow eyebrow--cyan"><?php ff_e( 'about.excellence.eyebrow' ); ?></p>
			<h2 class="h2"><?php ff_e( 'about.excellence.title' ); ?></h2>
		</header>
		<ul class="grid grid--3">
			<?php foreach ( (array) ff_t( 'about.excellence.items' ) as $ff_item ) : ?>
				<li class="about-pillar">
					<h3 class="about-pillar__title"><?php echo esc_html( (string) $ff_item['title'] ); ?></h3>
					<p><?php echo esc_html( (string) $ff_item['text'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php
get_template_part( 'template-parts/cta-band' );
get_footer();
