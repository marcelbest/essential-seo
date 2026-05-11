<?php

// Only run when WordPress triggers uninstall
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

delete_option('esseo_options');
delete_post_meta_by_key('esseo_meta_boxes');
