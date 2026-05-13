<?php

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Register translatable strings with Polylang
 *
 * @link https://polylang.wordpress.com/documentation/documentation-for-developers/functions-reference/
 */
add_action('plugins_loaded', 'esseo_register_polylang_strings');

function esseo_register_polylang_strings() {

    if ( ! function_exists( 'pll_the_languages' ) ) return;

    $options = get_option('esseo_options');
    $registered = false;

    if ( ! empty( $options['esseo_default_description'] ) ) {
        pll_register_string(__('Default description', 'essential-seo'), $options['esseo_default_description'], 'Essential SEO');
        $registered = true;
    }

    if ( ! empty( $options['esseo_share_img'] ) ) {
        pll_register_string(__('Default share image', 'essential-seo'), $options['esseo_share_img'], 'Essential SEO');
        $registered = true;
    }

    if ( $registered ) {
        set_transient('essential-seo-translation-possible', true, 0);
    } else {
        delete_transient('essential-seo-translation-possible');
    }

}


/**
 * Meta tags in <head>
 *
 * @link https://developer.wordpress.org/reference/hooks/wp_head/
 */
add_action('wp_head', 'esseo_main_meta_tags', 1);

function esseo_main_meta_tags() {

    $options = get_option('esseo_options');

    $default_title = esc_attr(get_bloginfo('name'));

    $default_title_seperator = (!empty($options['esseo_title_seperator'])) ? $options['esseo_title_seperator'] : '|';

    $default_description = isset($options['esseo_default_description']) ? esc_attr($options['esseo_default_description']) : '';
    $share_img           = isset($options['esseo_share_img']) ? esc_attr($options['esseo_share_img']) : '';

    if ( function_exists( 'pll_the_languages' ) ) {
        $default_description = esc_attr(pll__($options['esseo_default_description']));
        $share_img           = esc_attr(pll__($options['esseo_share_img']));
    }

    $description      = '';
    $og_title         = '';
    $og_type          = '';
    $og_url           = '';
    $share_img_width  = 0;
    $share_img_height = 0;

    if ( is_single() || ( is_page() && ! is_front_page() ) ) {

        while (have_posts()) {

            the_post();

            $post_id         = get_the_ID();
            $esseo_meta_boxes = get_post_meta($post_id, 'esseo_meta_boxes', true);

            if ( ! empty( $esseo_meta_boxes['description'] ) ) {
                $description = esc_html(esc_attr($esseo_meta_boxes['description']));
            } elseif (has_excerpt()) {
                $description = esc_html(wp_strip_all_tags(get_the_excerpt()));
            } else {
                $description = esc_html($default_description);
            }

            $og_title = '<meta property="og:title" content="' . esc_attr(get_the_title()) . ' ' . $default_title_seperator . ' ' . $default_title . '">';
            $og_type  = '<meta property="og:type" content="article">';
            $og_url   = '<meta property="og:url" content="' . esc_url(get_permalink()) . '">';

            if ( has_post_thumbnail() ) {
                $thumbnail_img    = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'large' );
                $share_img        = $thumbnail_img[0];
                $share_img_width  = $thumbnail_img[1];
                $share_img_height = $thumbnail_img[2];
            }

        }

    } else {

        $description = esc_html($default_description);
        $og_title    = '<meta property="og:title" content="' . $default_title . '">';

        if ( is_category() || is_tag() || is_tax() ) {
            $term_description = term_description();
            if ( ! empty( $term_description ) ) {
                $description = esc_html(wp_strip_all_tags($term_description));
            }
        }

        $categories = get_the_category();
        if ( ! empty( $categories ) && ! is_front_page() ) {
            $og_title = '<meta property="og:title" content="' . esc_attr($categories[0]->name) . ' ' . $default_title_seperator . ' ' . $default_title . '">';
        }

        $tag = single_tag_title('', false);
        if ( ! empty( $tag ) ) {
            $og_title = '<meta property="og:title" content="' . esc_attr($tag) . ' ' . $default_title_seperator . ' ' . $default_title . '">';
        }

        $og_type = '<meta property="og:type" content="website">';
        $og_url  = '<meta property="og:url" content="' . esc_url(home_url()) . '">';

    }

    // Description
    if ( ! empty( $description ) ) {
        ?><meta name="description" content="<?php echo $description; ?>"><?php
    }

    // Open Graph
    if ( ! empty( $options['esseo_checkbox_og'] ) ) {

        echo $og_title;
        echo $og_type;
        echo $og_url;

        ?><meta property="og:locale" content="<?php echo get_locale(); ?>"><?php
        ?><meta property="og:site_name" content="<?php echo $default_title; ?>"><?php

        if ( ! empty( $description ) ) {
            ?><meta property="og:description" content="<?php echo $description; ?>"><?php
        }

        if ( ! empty( $share_img ) ) {
            ?><meta property="og:image" content="<?php echo esc_attr( $share_img ); ?>"><?php
            if ( ! empty( $share_img_width ) ) {
                ?><meta property="og:image:width" content="<?php echo (int) $share_img_width; ?>"><?php
                ?><meta property="og:image:height" content="<?php echo (int) $share_img_height; ?>"><?php
            }
        }

    }

}


/**
 * Open Graph namespace prefix on <html>
 *
 * @link http://ogp.me/
 */
add_action('after_setup_theme', 'esseo_add_open_graph_prefix');

function esseo_add_open_graph_prefix() {

    $options = get_option('esseo_options');

    if ( ! empty( $options['esseo_checkbox_og'] ) ) {
        add_filter('language_attributes', 'esseo_opengraph_doctype');
    }

}

function esseo_opengraph_doctype($output) {
    return $output . ' prefix="og: http://ogp.me/ns#"';
}


/**
 * Title separator
 *
 * @link https://developer.wordpress.org/reference/hooks/document_title_separator/
 */
add_filter('document_title_separator', 'esseo_document_title_separator');

function esseo_document_title_separator($sep) {

    $options = get_option('esseo_options');

    if ( ! empty( $options['esseo_title_seperator'] ) ) {
        $sep = $options['esseo_title_seperator'];
    }

    return $sep;

}


/**
 * Header scripts
 *
 * Outputs the user-supplied snippet. Automatically prepends preconnect hints
 * for known Google domains and adds a GTM noscript fallback to the footer.
 */
add_action('wp_head', 'esseo_add_header_scripts', 5);

function esseo_add_header_scripts() {

    $options = get_option('esseo_options');
    $scripts = isset($options['esseo_header_scripts']) ? trim($options['esseo_header_scripts']) : '';

    if ( empty( $scripts ) ) return;

    if ( strpos( $scripts, 'google-analytics.com' ) !== false ) {
        echo '<link rel="preconnect" href="https://www.google-analytics.com">' . "\n";
    }

    if ( strpos( $scripts, 'googletagmanager.com' ) !== false ) {
        echo '<link rel="preconnect" href="https://www.googletagmanager.com">' . "\n";
    }

    echo $scripts . "\n";

}


/**
 * GTM noscript fallback in footer
 *
 * Extracts the GTM-XXXXXX ID from the header snippet and outputs the
 * recommended <noscript> iframe at the bottom of <body>.
 */
add_action('wp_footer', 'esseo_add_gtm_noscript');

function esseo_add_gtm_noscript() {

    $options = get_option('esseo_options');
    $scripts = isset($options['esseo_header_scripts']) ? $options['esseo_header_scripts'] : '';

    if ( empty( $scripts ) ) return;

    if ( preg_match( '/GTM-[A-Z0-9]+/', $scripts, $matches ) ) {
        $gtm_id = esc_attr($matches[0]);
        echo '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . $gtm_id . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . "\n";
    }

}
