<?php
/*
Plugin Name: Essential SEO
Plugin URI: https://github.com/marcelbest/essential-seo
Description: A simple SEO WordPress plugin, which provides just the essentials.
Version: 1.3.1
Author: Marcel Best
Author URI: https://marcelbest.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: essential-seo
Domain Path: /languages
*/

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Define Constant(s)
 */

define( 'ESSEO_VERSION', '1.3.1' );
define( 'ESSEO_PLUGIN', __FILE__ );
define( 'ESSEO_PLUGIN_BASENAME', plugin_basename( ESSEO_PLUGIN ) );
define( 'ESSEO_PLUGIN_NAME', trim( dirname( ESSEO_PLUGIN_BASENAME ), '/' ) );
define( 'ESSEO_SHORTNAME', 'esseo' );
define( 'ESSEO_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );

require_once ESSEO_PLUGIN_DIR . '/includes/functions.php';
require ESSEO_PLUGIN_DIR . '/includes/meta_boxes.php';
require ESSEO_PLUGIN_DIR . '/includes/html_header.php';
require ESSEO_PLUGIN_DIR . '/includes/og-image.php';
