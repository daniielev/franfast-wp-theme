# FranFast WordPress theme

Classic theme, no page builder. Site plugins: **WP Fastest Cache**, **Cookiebot**, **HFCM** (all tracking tags: GTM, Google Ads, Salesforce chat, Pardot iframe resize) and **Limit Login Attempts Reloaded**. The theme prints no tracking tags of its own.

## WordPress settings (once)
1. **Settings → Permalinks**: Custom structure `/%category%/%postname%/` (same as production: /blog/{slug}/, /news/{slug}/), Category base `category`, Tag base `tag`
   (without the bases WordPress would use `/blog/category/…`; production uses `/category/…`).
2. **Settings → Reading**: Homepage = page `Home`. The blog home is the category `blog` (`/category/blog/`, `/es/category/blog/`); the old `/our-blog/` page 301-redirects there (config.json → blog_category).
3. Pages that must exist in English (slug = file):
   `about-us` → page-about-us.php · `add-ons` → page-add-ons.php · `reviews` → page-reviews.php (Customers) ·
   `contact-us` → page-contact-us.php · `our-news` · legal pages (default `page.php`).
   Their copy comes from `lang/*.json`, not from the editor.

## Files
| Path | What |
|---|---|
| `config.json` | Non-translated data: phones, legal links, social, badges, nav structure, Trust Center, GTM ID |
| `lang/en.json` `es.json` `pt.json` | All copy, routes (translated slugs) and SEO title/description per page |
| `inc/routing.php` | `/es/` and `/pt/` URLs, post language filter, localized links |
| `inc/seo.php` | title, description, canonical, hreflang, Open Graph, JSON-LD, robots |
| `inc/post-lang.php` | "Language & SEO" box in each post |
| `inc/sitemap.php` | Adds ES/PT pages to `/wp-sitemap.xml` |

## Translations
- Edit the JSON (UTF-8). A key missing in ES/PT shows the English text.
- Changing a value in `routes` changes that page URL; rewrite rules refresh automatically.
- Validate before upload: `python3 -m json.tool lang/es.json`.

## Blog posts in ES/PT
Create a normal post → box **Language & SEO** → Language = Español/Português → *Translation of* = English original.
URL becomes `/es/{category}/{slug}/` (e.g. `/es/news/{slug}/`). Posts without the field are English.

## Newsletter (Pardot Form Handler)
`config.json → newsletter`: `enabled` (true/false) and `action` (Form Handler URL). The band only shows when both are set.
Pardot Form Handler:
- External Field Names = `config.json → newsletter.fields` (first_name, email, website, preferred_language, signup_page).
- Success Location `https://franfast.io/?ff_nl=ok` · Error Location `https://franfast.io/?ff_nl=error`
  (for local tests use `http://franfast.local/?ff_nl=ok` in a test handler).
- Honeypot: map `website` to a custom field; automation rule "Honeypot is not empty" → add to a spam list / remove from newsletter list.
Copy in `lang/*.json → newsletter`.
