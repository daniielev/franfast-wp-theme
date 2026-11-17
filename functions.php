<?php
/**
 * FranFast theme bootstrap.
 *
 * Structure:
 *  inc/i18n.php       JSON strings + language helpers (ff_t, ff_lang, ff_url)
 *  inc/routing.php    /es/ and /pt/ rewrites, language filters for posts, localized links
 *  inc/setup.php      theme supports, assets, head cleanup
 *  inc/seo.php        title, meta, canonical, hreflang, Open Graph, JSON-LD, robots
 *  inc/sitemap.php    adds ES/PT pages to the native WordPress sitemap
 *  inc/post-lang.php  native meta box: language, "translation of"
 *  inc/seo-box.php    native SEO meta box for pages and posts (title, description, og:image, noindex; per language on marketing pages)
 *  inc/content.php    content helpers (legacy page-builder shortcode cleanup)
 *  inc/newsletter.php Pardot Form Handler newsletter (on/off in config.json)
 *  inc/legal.php      legal pages: theme-hosted downloads (/dpa.pdf), old-URL 301s, heading anchors + TOC
 *
 * Site plugins: WP Fastest Cache, Cookiebot, HFCM (tracking tags), Limit Login Attempts.
 */

defined( 'ABSPATH' ) || exit;

define( 'FF_VERSION', '0.1.1' );
define( 'FF_DIR', get_template_directory() );
define( 'FF_URI', get_template_directory_uri() );

require_once FF_DIR . '/inc/i18n.php';
require_once FF_DIR . '/inc/routing.php';
require_once FF_DIR . '/inc/setup.php';
require_once FF_DIR . '/inc/seo.php';
require_once FF_DIR . '/inc/sitemap.php';
require_once FF_DIR . '/inc/post-lang.php';
require_once FF_DIR . '/inc/seo-box.php';
require_once FF_DIR . '/inc/content.php';
require_once FF_DIR . '/inc/newsletter.php';
require_once FF_DIR . '/inc/legal.php';
