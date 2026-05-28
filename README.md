# Essential SEO

A simple SEO WordPress plugin that provides just the essentials. Works well with Polylang for multi-language sites.

## Features

- Custom meta description per post and page (falls back to excerpt, then to site default)
- Category, tag and custom taxonomy descriptions used as meta description when set
- Open Graph meta tags
- Configurable title separator
- Free-form header scripts field (GA4, GTM, etc.) with automatic preconnect hints and GTM noscript fallback
- Polylang integration for translatable default settings

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher

## Installation

1. Download the latest `essential-seo-v*.zip` from the [Releases](https://github.com/marcelbest/essential-seo/releases) page
2. In WordPress, go to **Plugins → Add New → Upload Plugin**
3. Upload the ZIP and click **Install Now**
4. Activate the plugin
5. Go to **Settings → Essential SEO** to configure

Once installed, the plugin checks for updates automatically — future updates appear in the WordPress dashboard like any other plugin.

## Development

Source files are in the `source/` folder. CSS is written in SCSS and compiled via CodeKit.

### Structure

```
source/
├── essential-seo.php
├── assets/
│   ├── banner-772x250.png
│   ├── banner-1544x500.png
│   ├── banner-1544x500.psd
│   └── screenshot-*.png
├── css/
│   └── style.scss
├── js/
│   └── script.js
├── includes/
│   ├── functions.php
│   ├── html_header.php
│   ├── meta_boxes.php
│   └── plugin-update-checker/
└── languages/
```

### Releases

1. Make your changes in `source/`
2. Bump the version in `source/essential-seo.php` and `source/readme.txt`
3. Build with CodeKit
4. Double-click `create-release-zip.command` to generate the ZIP
5. Push commits to GitHub
6. On GitHub: Releases → Draft a new release → set tag to `v1.x.x` → upload the ZIP as asset

The plugin update checker picks up the new tag automatically and notifies WordPress sites with the plugin installed.

## License

GPLv2 or later — see [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html)
