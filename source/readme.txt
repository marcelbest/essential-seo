=== Essential SEO ===
Contributors: Marcel Best
Donate link: https://www.paypal.me/marcelbest79
Tags: seo
Requires at least: 6.0
Tested up to: 7.0
Stable tag: 1.1.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple SEO WordPress plugin, which provides just the essentials. It works well together with Polylang.

== Description ==

This SEO WordPress plugin provides just the essentials needed to make your WordPress site fully search engine friendly with just a few settings. After installation go to the settings page, make your settings, and you are all set.

Each post and page gets a custom meta description field. If left empty, the plugin falls back to the post excerpt, and if that is also empty, to the site-wide default description set in the plugin settings.

The plugin is not dependent on Polylang, but works well with it for multi-language websites. For tracking, paste your complete GA4 or GTM snippet into the Scripts field — preconnect hints and the GTM noscript fallback are added automatically.

== Installation ==

1. Upload the entire `essential-seo` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Essential SEO screen to configure the plugin

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

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
