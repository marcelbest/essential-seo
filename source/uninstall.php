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
