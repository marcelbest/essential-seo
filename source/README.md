# Essential SEO

A simple SEO WordPress plugin that provides just the essentials. Works well with Polylang for multi-language sites.

## Features

- Custom meta description per post and page (falls back to excerpt, then to site default)
- Open Graph meta tags
- Configurable title separator
- Free-form header scripts field (GA4, GTM, etc.) with automatic preconnect hints and GTM noscript fallback
- Polylang integration for translatable default settings

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher

## Installation

1. Upload the `essential-seo` folder to `/wp-content/plugins/`
2. Activate the plugin via the WordPress Plugins screen
3. Go to **Settings → Essential SEO** to configure

## Development

Source files are in the `source/` folder. CSS is written in SCSS and compiled via CodeKit.

### Structure

```
source/
├── essential-seo.php
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

Bump the version in `essential-seo.php` and `readme.txt`, then create a matching release tag on GitHub. The plugin update checker picks it up automatically.

## License

GPLv2 or later — see [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html)
