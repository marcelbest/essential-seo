<?php

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * OG image generation
 *
 * Generates a 1200x630 JPEG for each post with a featured image and stores
 * it in wp-content/uploads/esseo-og/. The URL is saved as post meta so the
 * og:image tag always gets a platform-safe file — regardless of the original
 * upload format or the WP media size settings.
 *
 * Files are never added to the media library to prevent accidental deletion.
 */

define( 'ESSEO_OG_META_KEY', '_esseo_og_image' );
define( 'ESSEO_OG_SUBDIR',   'esseo-og' );
define( 'ESSEO_OG_WIDTH',    1200 );
define( 'ESSEO_OG_HEIGHT',   630 );
define( 'ESSEO_OG_QUALITY',  72 );

/**
 * Returns path and URL of the esseo-og upload folder.
 *
 * @return array{path: string, url: string}
 */
function esseo_og_upload_dir() {
    $upload = wp_upload_dir();
    return array(
        'path' => $upload['basedir'] . '/' . ESSEO_OG_SUBDIR,
        'url'  => $upload['baseurl'] . '/' . ESSEO_OG_SUBDIR,
    );
}

/**
 * Generates the OG JPEG for a post and saves its URL as post meta.
 *
 * @param int $post_id
 * @param int $attachment_id
 * @return string|false URL on success, false on failure.
 */
function esseo_generate_og_image( $post_id, $attachment_id ) {
    $source = get_attached_file( $attachment_id );
    if ( ! $source || ! file_exists( $source ) ) {
        return false;
    }

    $editor = wp_get_image_editor( $source );
    if ( is_wp_error( $editor ) ) {
        return false;
    }

    $resized = $editor->resize( ESSEO_OG_WIDTH, ESSEO_OG_HEIGHT, false );
    if ( is_wp_error( $resized ) ) {
        return false;
    }

    $editor->set_quality( ESSEO_OG_QUALITY );

    $og = esseo_og_upload_dir();
    wp_mkdir_p( $og['path'] );

    $dest = $og['path'] . '/' . (int) $post_id . '-og.jpg';
    $saved = $editor->save( $dest, 'image/jpeg' );
    if ( is_wp_error( $saved ) ) {
        return false;
    }

    $url = $og['url'] . '/' . (int) $post_id . '-og.jpg';
    update_post_meta( $post_id, ESSEO_OG_META_KEY, $url );

    return $url;
}

/**
 * Deletes the OG JPEG and post meta for a post.
 *
 * @param int $post_id
 */
function esseo_delete_og_image( $post_id ) {
    $og   = esseo_og_upload_dir();
    $file = $og['path'] . '/' . (int) $post_id . '-og.jpg';

    if ( file_exists( $file ) ) {
        wp_delete_file( $file );
    }

    delete_post_meta( $post_id, ESSEO_OG_META_KEY );
}

/**
 * Returns the OG image data for a post: URL, width, height and alt text.
 * Generates the image on the fly if not yet cached. The alt text is taken
 * from the featured image's media-library alt attribute.
 *
 * @param int $post_id
 * @return array{url: string, width: int, height: int, alt: string}|false False when no image is available.
 */
function esseo_get_og_image( $post_id ) {
    $attachment_id = get_post_thumbnail_id( $post_id );
    if ( ! $attachment_id ) {
        return false;
    }

    $alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

    $url = get_post_meta( $post_id, ESSEO_OG_META_KEY, true );
    if ( ! $url ) {
        $url = esseo_generate_og_image( $post_id, (int) $attachment_id );
    }

    if ( $url ) {
        return array(
            'url'    => $url,
            'width'  => ESSEO_OG_WIDTH,
            'height' => ESSEO_OG_HEIGHT,
            'alt'    => $alt,
        );
    }

    // Generation failed — fall back to the WP image size used before 1.3.0,
    // reporting its actual dimensions instead of the OG target size.
    $fallback = wp_get_attachment_image_src( $attachment_id, 'large' );
    if ( $fallback ) {
        return array(
            'url'    => $fallback[0],
            'width'  => (int) $fallback[1],
            'height' => (int) $fallback[2],
            'alt'    => $alt,
        );
    }

    return false;
}

/**
 * Regenerate OG image when a featured image is set or changed.
 */
add_action( 'updated_post_meta', 'esseo_on_thumbnail_id_updated', 10, 4 );
add_action( 'added_post_meta',   'esseo_on_thumbnail_id_updated', 10, 4 );

function esseo_on_thumbnail_id_updated( $meta_id, $post_id, $meta_key, $meta_value ) {
    if ( '_thumbnail_id' !== $meta_key ) {
        return;
    }
    if ( ! in_array( get_post_type( $post_id ), array( 'post', 'page' ), true ) ) {
        return;
    }
    esseo_generate_og_image( (int) $post_id, (int) $meta_value );
}

/**
 * Delete OG image when the featured image is removed.
 */
add_action( 'deleted_post_meta', 'esseo_on_thumbnail_id_deleted', 10, 4 );

function esseo_on_thumbnail_id_deleted( $meta_ids, $post_id, $meta_key, $meta_value ) {
    if ( '_thumbnail_id' !== $meta_key ) {
        return;
    }
    if ( ! in_array( get_post_type( $post_id ), array( 'post', 'page' ), true ) ) {
        return;
    }
    esseo_delete_og_image( (int) $post_id );
}

/**
 * Handles the "Regenerate OG images" admin action (GET request with nonce).
 * Processes all published posts/pages that have a featured image.
 */
add_action( 'admin_init', 'esseo_handle_regenerate_og' );

function esseo_handle_regenerate_og() {
    if ( ! isset( $_GET['esseo_regenerate_og'] ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    check_admin_referer( 'esseo_regenerate_og' );

    set_time_limit( 0 );
    ignore_user_abort( true );

    $posts = get_posts( array(
        'post_type'    => array( 'post', 'page' ),
        'post_status'  => 'publish',
        'nopaging'     => true,
        'fields'       => 'ids',
        'meta_key'     => '_thumbnail_id',
        'meta_compare' => 'EXISTS',
    ) );

    $count = 0;
    foreach ( $posts as $post_id ) {
        $attachment_id = get_post_thumbnail_id( $post_id );
        if ( $attachment_id && esseo_generate_og_image( $post_id, $attachment_id ) ) {
            $count++;
        }
    }

    set_transient( 'esseo_regenerate_og_result', $count, 60 );
    delete_option( 'esseo_og_regen_needed' );

    wp_safe_redirect( admin_url( 'options-general.php?page=essential-seo&esseo_og_regenerated=1' ) );
    exit;
}

/**
 * On the first request after upgrading to 1.3.0+, set the regen-needed flag.
 * Not triggered on fresh installs because esseo_install() sets esseo_version first.
 */
add_action( 'plugins_loaded', 'esseo_check_og_version' );

function esseo_check_og_version() {
    $stored = get_option( 'esseo_version', '0' );
    if ( version_compare( $stored, '1.3.0', '<' ) ) {
        update_option( 'esseo_og_regen_needed', '1', false );
        update_option( 'esseo_version', ESSEO_VERSION, false );
    }
}

/**
 * Admin notice after upgrading to 1.3.0 — asks the user to regenerate OG images.
 */
add_action( 'admin_notices', 'esseo_og_regen_needed_notice' );

function esseo_og_regen_needed_notice() {
    if ( ! get_option( 'esseo_og_regen_needed' ) ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $url = admin_url( 'options-general.php?page=essential-seo' );
    echo '<div class="notice notice-warning is-dismissible"><p>';
    printf(
        /* translators: %s: link to the Essential SEO settings page */
        wp_kses(
            __( 'Essential SEO was updated – OG images for existing posts need to be regenerated once. <a href="%s">Go to settings</a>', 'essential-seo' ),
            array( 'a' => array( 'href' => array() ) )
        ),
        esc_url( $url )
    );
    echo '</p></div>';
}

/**
 * Shows the regeneration result notice in admin.
 */
add_action( 'admin_notices', 'esseo_regenerate_og_notice' );

function esseo_regenerate_og_notice() {
    if ( ! isset( $_GET['esseo_og_regenerated'] ) ) {
        return;
    }

    $count = get_transient( 'esseo_regenerate_og_result' );
    delete_transient( 'esseo_regenerate_og_result' );

    if ( false === $count ) {
        return;
    }

    echo '<div class="notice notice-success is-dismissible"><p>';
    printf(
        /* translators: %d: number of generated images */
        esc_html( _n( '%d OG image generated.', '%d OG images generated.', $count, 'essential-seo' ) ),
        (int) $count
    );
    echo '</p></div>';
}
