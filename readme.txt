=== Essential SEO ===
Contributors: Marcel Best
Donate link: https://www.paypal.me/marcelbest79
Tags: seo
Requires at least: 6.0
Tested up to: 7.0.2
Requires PHP: 8.2
Stable tag: 1.4.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple SEO WordPress plugin, which provides just the essentials.

== Description ==

This SEO WordPress plugin provides just the essentials needed to make your WordPress site fully search engine friendly with just a few settings. After installation go to the settings page, make your settings, and you are all set.

Each post and page gets a custom meta description field. If left empty, the plugin falls back to the post excerpt, and if that is also empty, to the site-wide default description set in the plugin settings.

Posts and pages can individually be marked as noindex directly in the editor — they are excluded from the WordPress sitemap automatically.

For tracking, paste your complete GA4 or GTM snippet into the Scripts field — preconnect hints and the GTM noscript fallback are added automatically.

== Installation ==

1. Upload the entire `essential-seo` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Essential SEO screen to configure the plugin

== Frequently Asked Questions ==

== Screenshots ==

Screenshots can be viewed on GitHub — copy the URL into your browser.

1. https://raw.githubusercontent.com/marcelbest/essential-seo/main/source/assets/screenshot-1.png — The plugin is accessible under Settings → Essential SEO in the WordPress admin menu.
2. https://raw.githubusercontent.com/marcelbest/essential-seo/main/source/assets/screenshot-2.png — The settings page lets you configure the title separator, default description, share image and tracking scripts, choose whether to exclude logged-in editors from tracking, and regenerate the auto-generated OG images.
3. https://raw.githubusercontent.com/marcelbest/essential-seo/main/source/assets/screenshot-3.png — The SEO meta box can be enabled per editor via Preferences → Document settings → SEO.
4. https://raw.githubusercontent.com/marcelbest/essential-seo/main/source/assets/screenshot-4.png — The SEO meta box on a page — enter a custom meta description or leave empty to fall back to the excerpt or site default.
5. https://raw.githubusercontent.com/marcelbest/essential-seo/main/source/assets/screenshot-5.png — The SEO meta box works the same way on posts.
6. https://raw.githubusercontent.com/marcelbest/essential-seo/main/source/assets/screenshot-6.png — The plugin outputs clean meta tags in the HTML head — description, Open Graph tags and more.

== Changelog ==

= 1.4.2 =

* Tested against WordPress 7.0.2 — no code changes were needed, the compatibility header now names the exact release the plugin was verified on

= 1.4.1 =

* Added the "Requires at least" and "Requires PHP" headers — WordPress now blocks activation and updates on sites running WordPress older than 6.0 or PHP older than 8.2, instead of letting the plugin fail later

= 1.4.0 =

* Added an "Exclude editors from tracking" option — when enabled, the tracking snippet (and GTM noscript fallback) is no longer output for logged-in users who can edit posts (administrators, editors, authors, contributors), keeping your own visits out of analytics without configuring filters in GA4
* Added German translations (de_DE, de_CH) for the new option
* Fixed an undefined-array-key warning that could appear for newly added checkbox settings on existing installs

= 1.3.1 =

* Added an og:image:alt tag, using the featured image's alt text from the media library
* Added a plugin icon, shown on the plugins and updates screens
* Updated the settings page screenshot to reflect the new OG image section

= 1.3.0 =

* OG images are now generated automatically as 1200×630 JPEG — independent of the original upload format and WP media size settings
* Generated images are stored in wp-content/uploads/esseo-og/ and never added to the media library
* OG image is regenerated automatically when a post's featured image is changed or removed
* Added "Regenerate OG images" button on the settings page to backfill existing posts
* Plugin uninstall now removes all generated OG images and the esseo-og folder

= 1.2.2 =

* Added a live character counter to the SEO meta description field
* German translations now use the informal "du" form
* Fixed double-escaping of special characters (&, quotes) in meta and Open Graph descriptions
* Fixed admin notices not showing on the settings page
* Description length limit is now multibyte-safe (umlauts count as one character)
* Various code cleanup and reliability improvements under the hood

= 1.2.1 =

* Removed Polylang integration

= 1.2.0 =

* Added noindex option to the SEO meta box — posts and pages can now be marked as noindex directly in the editor; excluded from the WordPress sitemap automatically
* Added German translations (de_DE, de_CH) for the noindex field

= 1.1.9 =

* Screenshots tab: plain text URLs with explanatory note

= 1.1.8 =

* Screenshots tab: attempting to display links via HTML a tags

= 1.1.7 =

* Screenshots tab: attempting to display images via HTML img tags

= 1.1.6 =

* Fixed plugin details popup — description, screenshots and changelog tabs now display correctly

= 1.1.5 =

* Added plugin banner and screenshots to the WordPress plugin details popup
* Updated meta description character count guidance (recommended 150–160 chars)

= 1.1.4 =

* Updated Plugin Update Checker library to v5.7

= 1.1.3 =

* Fixed automatic plugin updates — all previous versions could never be installed via the WordPress update mechanism due to a misconfigured ZIP source in the update checker

= 1.1.2 =

* Fixed PHP 8.5 fatal error on settings page when options have never been saved
* Fixed PHP 8.5 fatal error when post thumbnail image size is unavailable
* Fixed automatic plugin updates installing incorrectly due to wrong ZIP source
* Tested with WordPress 7.0

= 1.1.1 =

* Category, tag and custom taxonomy descriptions are now used as meta description when set

= 1.1.0 =

* Modernised for WordPress 6.x
* Fixed nested function declarations (potential fatal error)
* Removed robots meta handling — WordPress manages this natively since 5.7
* Removed keywords meta tag (ignored by search engines since 2009)
* Replaced separate GA/GTM ID fields with a single free-form Header Scripts field
* Preconnect hints for Google domains added automatically from script snippet
* GTM noscript fallback in footer added automatically from script snippet
* Removed Mobile_Detect library dependency
* Update checker switched from self-hosted to GitHub
* SCSS source files added
* Meta description now falls back to post excerpt, then to global default
* Requires WordPress 6.0 or higher

= 1.0.7 =

* New update checker integration added

= 1.0.6 =

* Google tag-manager option added for tracking
* Minor cosmetic fixes

= 1.0.5 =

* Admin rewrite
* More title separators added
* Field length set and error messages improved
* Translation rewrite
* Overall plugin security improved

= 1.0.4 =

* Translation refined
* Meta description field input secured

= 1.0.3 =

* German translation refined

= 1.0.2 =

* User role check added

= 1.0.1 =

* Plugin Update Checker integrated

= 1.0 =

* Essential SEO
