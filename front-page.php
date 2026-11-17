<?php
/**
 * Home — built from "Home — EN (desktop)" (Claude Design). Copy: lang/*.json → home.
 * Sections: hero + agent demo · client logos · why · platform · headless · AI · proof ·
 * segments · expand · enterprise · implementation · trust · FAQ · demo CTA.
 */

defined( 'ABSPATH' ) || exit;
get_header();

$h       = (array) ff_t( 'home' );
$contact = ff_url( 'contact' );
$home    = (array) ff_config( 'home', array() );
$appx    = (string) ff_config( 'appexchange_url', '' );

// Value from the home block, with English fallback.
$t = static function ( string $path ) use ( $h ) {
	$v = ff_array_get( $h, $path );
	return null === $v ? ff_t( 'home.' . $path, ff_default_lang() ) : $v;
};

$scenarios = (array) $t( 'agent.scenarios' );
$first     = $scenarios[0] ?? array( 'persona' => '', 'prompt' => '', 'steps' => array() );
?>

<!-- Hero -->
<section class="hero" id="top">
	<div class="container hero__inner">
		<div class="hero__copy">
			<p class="eyebrow"><?php echo esc_html( $t( 'hero.eyebrow' ) ); ?></p>
			<h1 class="h1 hero__title"><?php echo esc_html( $t( 'hero.title_1' ) ); ?><br><?php echo esc_html( $t( 'hero.title_2' ) ); ?></h1>
			<p class="lead hero__lead"><?php echo esc_html( $t( 'hero.lead' ) ); ?></p>
			<div class="btn-row">
				<a class="btn btn--primary btn--lg" href="<?php echo esc_url( $contact ); ?>"><?php echo esc_html( $t( 'hero.cta_primary' ) ); ?></a>
				<a class="btn btn--secondary btn--lg" href="#headless"><?php echo esc_html( $t( 'hero.cta_secondary' ) ); ?></a>
			</div>
			<p class="hero__proof"><?php echo esc_html( $t( 'hero.proof' ) ); ?></p>
		</div>

		<div class="agent-card" data-ff-agent data-autoplay="<?php echo ff_config( 'motion.agent_autoplay', true ) ? '1' : '0'; ?>"
			data-working="<?php echo esc_attr( $t( 'agent.working' ) ); ?>"
			data-done="<?php echo esc_attr( $t( 'agent.done' ) ); ?>"
			data-asked-by="<?php echo esc_attr( $t( 'agent.asked_by' ) ); ?>">
			<div class="agent-card__top">
				<div class="agent-card__name">
					<span class="agent-card__mark"><?php echo ff_icon( 'ai', 18 ); // phpcs:ignore ?></span>
					<span><?php echo esc_html( $t( 'agent.name' ) ); ?></span>
				</div>
				<span class="status-pill"><span class="pulse-dot ff-pulse" aria-hidden="true"></span><span data-ff-agent-status><?php echo esc_html( $t( 'agent.done' ) ); ?></span></span>
			</div>
			<p class="agent-card__persona" data-ff-agent-persona><?php echo esc_html( sprintf( $t( 'agent.asked_by' ), $first['persona'] ) ); ?></p>
			<div class="agent-card__prompt" data-ff-agent-prompt><?php echo esc_html( $first['prompt'] ); ?></div>
			<div class="agent-card__log" aria-live="polite" data-ff-agent-log>
				<?php foreach ( array_slice( (array) $first['steps'], -3 ) as $i => $step ) : ?>
					<div class="agent-line is-done<?php echo 0 === $i ? ' is-faded' : ''; ?>"><?php echo ff_icon( 'check', 18 ); // phpcs:ignore ?><span><?php echo esc_html( $step ); ?></span></div>
				<?php endforeach; ?>
			</div>
			<div class="agent-card__foot">
				<div class="agent-card__dots" role="group" aria-label="<?php echo esc_attr( $t( 'agent.dots_label' ) ); ?>" data-ff-agent-dots>
					<?php foreach ( $scenarios as $i => $unused ) : ?><button type="button" class="agent-card__dot<?php echo 0 === $i ? ' is-active' : ''; ?>" aria-label="<?php echo esc_attr( sprintf( $t( 'agent.dot_label' ), $i + 1, count( $scenarios ) ) ); ?>" aria-pressed="<?php echo 0 === $i ? 'true' : 'false'; ?>" data-ff-agent-dot="<?php echo (int) $i; ?>"></button><?php endforeach; ?>
				</div>
				<?php if ( '' !== (string) ( $home['hours_recovered'] ?? '' ) ) : ?>
					<div class="agent-card__metric">
						<span><?php echo esc_html( $t( 'agent.hours_label' ) ); ?></span>
						<strong class="mono"><?php echo esc_html( $home['hours_recovered'] ); ?> h</strong>
					</div>
				<?php endif; ?>
			</div>
			<script type="application/json" data-ff-agent-data><?php echo wp_json_encode( $scenarios, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP ); ?></script>
		</div>
	</div>
</section>

<!-- Client logos -->
<section class="logo-strip" id="customers" aria-label="<?php echo esc_attr( $t( 'logos.label' ) ); ?>">
	<div class="container logo-strip__inner">
		<p class="logo-strip__label"><?php echo esc_html( $t( 'logos.label' ) ); ?></p>
		<ul class="logo-strip__list" data-ff-marquee>
			<?php foreach ( (array) ff_config( 'clients', array() ) as $client ) : ?>
				<?php if ( empty( $client['img'] ) ) : // Logo pending: show the name. ?>
					<li><span class="logo-strip__name"><?php echo esc_html( $client['name'] ); ?></span></li>
				<?php else : ?>
				<li><img src="<?php echo esc_url( ff_img_src( (string) $client['img'] ) ); ?>" alt="<?php echo esc_attr( $client['name'] ); ?>"
					<?php echo ! empty( $client['width'] ) ? 'width="' . (int) $client['width'] . '" height="' . (int) $client['height'] . '"' : ''; ?>
					style="height:<?php echo (int) $client['display_height']; ?>px" loading="lazy" decoding="async"></li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<!-- Why brands switch -->
<section class="section section--top-only">
	<div class="container split">
		<div class="split__main stack-sm">
			<p class="eyebrow"><?php echo esc_html( $t( 'why.eyebrow' ) ); ?></p>
			<h2 class="h2"><?php echo esc_html( $t( 'why.title' ) ); ?></h2>
			<p class="lead"><?php echo esc_html( $t( 'why.lead' ) ); ?></p>
			<a class="btn btn--secondary" href="<?php echo esc_url( $contact ); ?>"><?php echo esc_html( $t( 'why.cta' ) ); ?></a>
		</div>
		<ul class="split__side pain-list">
			<?php foreach ( (array) $t( 'why.pains' ) as $pain ) : ?>
				<li class="pain"><strong><?php echo esc_html( $pain['title'] ); ?></strong><span><?php echo esc_html( $pain['text'] ); ?></span></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<!-- Core platform -->
<section class="section" id="platform">
	<div class="container stack-lg">
		<header class="section-head">
			<p class="eyebrow"><?php echo esc_html( $t( 'platform.eyebrow' ) ); ?></p>
			<h2 class="h2"><?php echo esc_html( $t( 'platform.title' ) ); ?></h2>
			<p class="lead"><?php echo esc_html( $t( 'platform.lead' ) ); ?></p>
		</header>
		<ul class="grid grid--3">
			<?php foreach ( (array) $t( 'platform.modules' ) as $module ) : ?>
				<?php $is_ai = ! empty( $module['highlight'] ); ?>
				<li class="card module<?php echo $is_ai ? ' module--ai' : ''; ?>"<?php echo $is_ai ? ' id="ai"' : ''; ?>>
					<span class="module__icon"><?php echo ff_icon( (string) $module['icon'], 28 ); // phpcs:ignore ?><span class="module__dot<?php echo $is_ai ? ' ff-pulse' : ''; ?>" aria-hidden="true"></span></span>
					<h3 class="module__title"><?php echo esc_html( $module['title'] ); ?></h3>
					<p class="module__text"><?php echo esc_html( $module['text'] ); ?></p>
					<?php if ( ! empty( $module['note'] ) ) : ?><p class="module__note"><?php echo esc_html( $module['note'] ); ?></p><?php endif; ?>
					<?php if ( ! empty( $module['anchor'] ) ) : ?><a class="module__link" href="<?php echo esc_url( ff_url( 'pillars', null, (string) $module['anchor'] ) ); ?>"><?php echo esc_html( $t( 'platform.see_options' ) ); ?> <?php echo ff_icon( 'arrow', 16 ); // phpcs:ignore ?></a><?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<!-- Headless -->
<section class="section section--gray" id="headless">
	<div class="container headless">
		<div class="headless__intro">
			<div class="headless__copy stack-sm">
				<p class="eyebrow"><?php echo esc_html( $t( 'headless.eyebrow' ) ); ?></p>
				<h2 class="h2"><?php echo esc_html( $t( 'headless.title' ) ); ?></h2>
				<p class="lead"><?php echo esc_html( $t( 'headless.lead' ) ); ?></p>
			</div>
			<div class="note-card note-card--slack">
				<div class="note-card__head">
					<img src="<?php echo esc_url( ff_asset( 'img/slack-logo.svg' ) ); ?>" alt="Slack" width="86" height="22" loading="lazy">
					<strong><?php echo esc_html( $t( 'headless.slack_title' ) ); ?></strong>
				</div>
				<span><strong><?php echo esc_html( $t( 'headless.slack_lead' ) ); ?></strong> <?php echo esc_html( $t( 'headless.slack_text' ) ); ?></span>
			</div>
		</div>
		<ol class="layers headless__layers">
			<?php foreach ( (array) $t( 'headless.layers' ) as $layer ) : ?>
				<li class="layer layer--<?php echo esc_attr( $layer['style'] ); ?>">
					<div class="layer__label"><strong><?php echo esc_html( $layer['title'] ); ?></strong><span><?php echo esc_html( $layer['sub'] ); ?></span></div>
					<ul class="layer__chips">
						<?php foreach ( (array) $layer['items'] as $chip ) : ?><li><?php echo esc_html( $chip ); ?></li><?php endforeach; ?>
					</ul>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<!-- FranFast AI -->
<section class="section" id="franfast-ai">
	<div class="container stack-md">
		<header class="section-head">
			<p class="eyebrow"><?php echo esc_html( $t( 'ai.eyebrow' ) ); ?></p>
			<h2 class="h2"><?php echo esc_html( $t( 'ai.title' ) ); ?></h2>
		</header>
		<ul class="grid grid--3">
			<?php foreach ( (array) $t( 'ai.cards' ) as $card ) : ?>
				<li class="card card--sm ai-card"><span class="ai-card__label"><span class="dot" aria-hidden="true"></span><?php echo esc_html( $card['label'] ); ?></span><p><?php echo esc_html( $card['text'] ); ?></p></li>
			<?php endforeach; ?>
		</ul>
		<?php $video = (string) ( $home['video_url'] ?? '' ); ?>
		<div class="video-card" data-ff-video="<?php echo esc_attr( $video ); ?>">
			<?php if ( $video ) : ?>
				<button type="button" class="video-card__play" aria-label="<?php echo esc_attr( $t( 'ai.video_play' ) ); ?>" data-ff-video-play><?php echo ff_icon( 'play', 24 ); // phpcs:ignore ?></button>
			<?php endif; ?>
			<p class="video-card__title"><?php echo esc_html( $t( 'ai.video_title' ) ); ?></p>
			<p class="video-card__text"><?php echo esc_html( $t( 'ai.video_text' ) ); ?></p>
		</div>
	</div>
</section>

<!-- Proof -->
<section class="section section--gray">
	<div class="container stack-md">
		<div class="section-head section-head--row">
			<h2 class="h2"><?php echo esc_html( $t( 'proof.title' ) ); ?></h2>
			<div class="rating">
				<span><strong><?php echo esc_html( $t( 'proof.rating' ) ); ?></strong> · <?php
				echo $appx ? '<a class="link-ext" href="' . esc_url( $appx ) . '" target="_blank" rel="noopener">' . esc_html( $t( 'proof.rating_text' ) ) . ' ' . ff_icon( 'external', 14 ) . '<span class="screen-reader-text"> ' . esc_html( (string) ff_t( 'common.opens_new_tab' ) ) . '</span></a>' : esc_html( $t( 'proof.rating_text' ) ); // phpcs:ignore
				?></span>
				<a class="link-arrow" href="<?php echo esc_url( ff_url( 'reviews' ) ); ?>"><?php echo esc_html( $t( 'proof.cta' ) ); ?> <?php echo ff_icon( 'arrow', 16 ); // phpcs:ignore ?></a>
			</div>
		</div>
		<ul class="grid grid--3">
			<?php foreach ( (array) $t( 'proof.stories' ) as $story ) : ?>
				<li class="card story">
					<span class="story__brand"><?php echo esc_html( $story['brand'] ); ?></span>
					<?php if ( ! empty( $story['stat'] ) ) : ?>
						<span class="story__stat mono"><?php echo esc_html( $story['stat'] ); ?></span>
						<span class="story__stat-label"><?php echo esc_html( $story['stat_label'] ); ?></span>
					<?php elseif ( ! empty( $story['badge'] ) ) : ?>
						<span class="story__badge"><?php echo esc_html( $t( 'proof.review_badge' ) ); ?></span>
					<?php endif; ?>
					<blockquote class="story__quote"><p>“<?php echo esc_html( $story['quote'] ); ?>”</p></blockquote>
					<span class="story__author"><?php echo esc_html( $story['author'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<!-- Segments -->
<section class="section section--top-only">
	<div class="container stack-md">
		<h2 class="h2"><?php echo esc_html( $t( 'segments.title' ) ); ?></h2>
		<ul class="grid grid--3">
			<?php foreach ( (array) $t( 'segments.items' ) as $seg ) : ?>
				<li class="card segment<?php echo ! empty( $seg['featured'] ) ? ' segment--featured' : ''; ?>">
					<h3 class="segment__title"><?php echo esc_html( $seg['title'] ); ?></h3>
					<p><?php echo esc_html( $seg['text'] ); ?></p>
					<a class="link-arrow" href="<?php echo esc_url( $contact ); ?>"><?php echo esc_html( $seg['cta'] ); ?> <?php echo ff_icon( 'arrow', 16 ); // phpcs:ignore ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<!-- Expand -->
<section class="section" id="expand">
	<div class="container stack-md">
		<header class="section-head">
			<p class="eyebrow"><?php echo esc_html( $t( 'expand.eyebrow' ) ); ?></p>
			<h2 class="h2 h2--sm"><?php echo esc_html( $t( 'expand.title' ) ); ?></h2>
			<p class="lead lead--sm"><?php echo esc_html( $t( 'expand.lead' ) ); ?></p>
		</header>
		<ul class="grid grid--4">
			<?php foreach ( (array) $t( 'expand.items' ) as $addon ) : ?>
				<li class="addon"><strong><?php echo esc_html( $addon['title'] ); ?></strong><span><?php echo esc_html( $addon['text'] ); ?></span></li>
			<?php endforeach; ?>
		</ul>
		<p><a class="btn btn--secondary" href="<?php echo esc_url( ff_url( 'add_ons' ) ); ?>"><?php echo esc_html( $t( 'expand.cta' ) ); ?> <?php echo ff_icon( 'arrow', 16 ); // phpcs:ignore ?></a></p>
	</div>
</section>

<!-- Enterprise -->
<section class="section section--navy">
	<div class="container split split--center">
		<div class="split__main stack-sm">
			<p class="eyebrow eyebrow--cyan"><?php echo esc_html( $t( 'enterprise.eyebrow' ) ); ?></p>
			<h2 class="h2 h2--sm"><?php echo esc_html( $t( 'enterprise.title' ) ); ?></h2>
			<p class="lead lead--sm"><?php echo esc_html( $t( 'enterprise.lead' ) ); ?></p>
			<a class="btn btn--inverse btn--lg" href="<?php echo esc_url( $contact ); ?>"><?php echo esc_html( $t( 'enterprise.cta' ) ); ?></a>
		</div>
		<div class="split__side stack-sm">
		<ul class="stat-grid">
			<?php foreach ( (array) $t( 'enterprise.stats' ) as $ff_si => $stat ) : ?>
				<li class="stat<?php echo ! empty( $stat['live'] ) ? ' stat--live' : ''; ?>">
					<span class="stat__label"><?php if ( ! empty( $stat['live'] ) ) : ?><span class="pulse-dot ff-pulse" aria-hidden="true"></span><?php endif; ?><?php echo esc_html( $stat['label'] ); ?><?php echo 0 === $ff_si ? '<span aria-hidden="true">*</span>' : ''; ?></span>
					<span class="stat__value mono"<?php echo preg_match( '/^\d+$/', (string) $stat['value'] ) ? ' data-ff-count="' . esc_attr( $stat['value'] ) . '"' : ''; ?>><?php echo esc_html( $stat['value'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php if ( $t( 'enterprise.stats_note' ) ) : ?><p class="stat-note"><?php echo esc_html( $t( 'enterprise.stats_note' ) ); ?></p><?php endif; ?>
		</div>
	</div>
</section>

<!-- Implementation -->
<section class="section section--gray-flat">
	<div class="container stack-md">
		<header class="section-head section-head--wide">
			<p class="eyebrow"><?php echo esc_html( $t( 'implementation.eyebrow' ) ); ?></p>
			<h2 class="h2"><?php echo esc_html( $t( 'implementation.title' ) ); ?></h2>
			<p class="lead"><?php echo esc_html( $t( 'implementation.lead' ) ); ?></p>
		</header>
		<ol class="grid grid--3 steps">
			<?php foreach ( (array) $t( 'implementation.steps' ) as $i => $step ) : ?>
				<li class="step"><span class="step__num mono<?php echo 2 === $i ? ' step__num--teal' : ''; ?>"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><strong><?php echo esc_html( $step['title'] ); ?></strong><span><?php echo esc_html( $step['text'] ); ?></span></li>
			<?php endforeach; ?>
		</ol>
		<p class="footnote"><?php echo esc_html( $t( 'implementation.footnote' ) ); ?></p>
	</div>
</section>

<!-- Trust -->
<?php get_template_part( 'template-parts/trust-band' ); ?>

<!-- FAQ -->
<section class="section faq-section">
	<div class="container container--narrow">
		<h2 class="h2 faq__title"><?php echo esc_html( $t( 'faq.title' ) ); ?></h2>
		<div class="faq">
			<?php foreach ( (array) $t( 'faq.items' ) as $item ) : ?>
				<details class="faq__item">
					<summary><?php echo esc_html( $item['q'] ); ?><?php echo ff_icon( 'chevron', 20 ); // phpcs:ignore ?></summary>
					<?php
					foreach ( (array) $item['a'] as $para ) {
						$para = $appx
							? str_replace( array( '{appexchange}', '{/appexchange}' ), array( '<a href="' . esc_url( $appx ) . '" target="_blank" rel="noopener">', '</a>' ), $para )
							: str_replace( array( '{appexchange}', '{/appexchange}' ), array( '<span class="faq__link-pending">', '</span>' ), $para );
						echo '<p>' . wp_kses( $para, array( 'strong' => array(), 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ), 'span' => array( 'class' => array() ) ) ) . '</p>'; // phpcs:ignore
					}
					?>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- Demo CTA -->
<section class="cta-band" id="demo">
	<div class="container cta-band__inner">
		<div class="stack-xs">
			<h2 class="cta-band__title"><?php echo esc_html( $t( 'demo.title' ) ); ?></h2>
			<p class="cta-band__text"><?php echo esc_html( $t( 'demo.text' ) ); ?></p>
		</div>
		<a class="btn btn--inverse btn--lg" href="<?php echo esc_url( $contact ); ?>"><?php echo esc_html( $t( 'demo.cta' ) ); ?></a>
	</div>
</section>

<?php
// FAQ structured data (same copy as the page).
$faq_ld = array();
foreach ( (array) $t( 'faq.items' ) as $item ) {
	$faq_ld[] = array(
		'@type'          => 'Question',
		'name'           => $item['q'],
		'acceptedAnswer' => array(
			'@type' => 'Answer',
			'text'  => wp_strip_all_tags( str_replace( array( '{appexchange}', '{/appexchange}' ), '', implode( ' ', (array) $item['a'] ) ) ),
		),
	);
}
echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faq_ld ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . "</script>\n"; // phpcs:ignore

get_footer();
