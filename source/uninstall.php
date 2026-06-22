<?php

// Only run when WordPress triggers uninstall
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

delete_option( 'esseo_options' );
delete_post_meta_by_key( 'esseo_meta_boxes' );

// The _noindex post meta is now owned by the plugin. It originally lived in the
// theme, but that handling has since been removed, so the plugin is the only
// consumer and the key is cleaned up here on uninstall as well.
delete_post_meta_by_key( '_noindex' );

delete_option( 'esseo_version' );
delete_option( 'esseo_og_regen_needed' );

// Remove generated OG images and their post meta.
delete_post_meta_by_key( '_esseo_og_image' );

$upload  = wp_upload_dir();
$og_path = $upload['basedir'] . '/esseo-og';

require_once ABSPATH . 'wp-admin/includes/file.php';

global $wp_filesystem;
if ( WP_Filesystem() && $wp_filesystem->is_dir( $og_path ) ) {
    $wp_filesystem->delete( $og_path, true );
}
