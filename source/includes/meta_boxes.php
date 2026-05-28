<?php

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

// Hide our meta from the_meta() and custom fields UI
add_filter( 'is_protected_meta', function( $protected, $meta_key ) {
    return $meta_key === 'esseo_meta_boxes' ? true : $protected;
}, 10, 2 );

/**
 * Enable support for custom SEO description on posts and pages
 *
 * @link https://developer.wordpress.org/reference/functions/add_meta_box/
 * @link https://codex.wordpress.org/Custom_Fields
 * @link https://developer.wordpress.org/reference/classes/wp_screen/
 */

function esseo_add_seo_meta_boxes() {
    add_meta_box(
        'esseo_meta_boxes', // $id
        __( 'SEO', 'essential-seo' ), // $title
        'esseo_show_seo_meta_boxes', // $callback
        ['post', 'page'], // $screen
        'normal', // $context
        'high' // $priority
    );
}

add_action( 'add_meta_boxes', 'esseo_add_seo_meta_boxes' );

function esseo_show_seo_meta_boxes() {

    global $post;
    $meta    = get_post_meta( $post->ID, 'esseo_meta_boxes', true );
    $user_id = get_current_user_id();

    // Show error, if there is one
    if ( false !== ( $msg = get_transient( "seo_meta_box_error_msg_{$post->ID}_{$user_id}" ) ) && $msg ) {

        // Show error message
        $error_msg = '<div class="notice notice-error is-dismissible"><p>' . $msg . '</p></div>';
        echo $error_msg;

        // Cleanup
        delete_transient( "seo_meta_box_error_msg_{$post->ID}_{$user_id}" );
    }

    ?><input type="hidden" name="esseo_meta_box_nonce" value="<?php echo wp_create_nonce( basename( ESSEO_PLUGIN ) ); ?>"><?php

    // SEO description
    ?><p><?php

        ?><label for="esseo_meta_boxes[description]"><?php _e( 'Meta description (recommended: 150–160 chars, max. 320) — leave empty to use the excerpt or the site default', 'essential-seo' ); ?></label><?php
        ?><br><?php
        ?><textarea name="esseo_meta_boxes[description]" id="esseo_meta_boxes[description]" rows="2" cols="30" style="width:100%;" maxlength="320"><?php

        if ( isset( $meta['description'] ) && ! empty( $meta['description'] ) ) {
            echo esc_textarea( $meta['description'] );
        }

        ?></textarea><?php

    ?></p><?php

}

function esseo_save_seo_meta_boxes( $post_id ) {

    // Verify nonce
    $seo_meta_box_nonce = isset( $_POST['esseo_meta_box_nonce'] ) ? $_POST['esseo_meta_box_nonce'] : '';

    if ( ! wp_verify_nonce( $seo_meta_box_nonce, basename( ESSEO_PLUGIN ) ) ) {
        return $post_id;
    }

    // Check autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }

    // Check permissions
    if ( 'page' === $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return $post_id;
        } elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }
    }

    $old     = get_post_meta( $post_id, 'esseo_meta_boxes', true );
    $new     = $_POST['esseo_meta_boxes'];
    $user_id = get_current_user_id();

    /**
     * Sanitize textarea content
     *
     * @link https://codex.wordpress.org/Validating_Sanitizing_and_Escaping_User_Data
     * @link https://codex.wordpress.org/Function_Reference/sanitize_meta
     * @link https://developer.wordpress.org/plugins/security/data-validation/
     */

    $seo_meta_boxes_description = sanitize_textarea_field( $new['description'] );

    // accept the input only after stripping out all html, extra white space etc!
    $new_description_tmp = trim( $seo_meta_boxes_description );
    $new_description_tmp = sanitize_text_field( $new_description_tmp );
    // need to add slashes still before sending to the database
    $new['description'] = addslashes( $new_description_tmp );

    if ( $new && $new !== $old ) {

        if ( strlen( $new['description'] ) > 320 ) {
            // max of 320 chars
            $new['description'] = substr( $new['description'], 0, 320 );
            // save transient to db
            set_transient( "seo_meta_box_error_msg_{$post_id}_{$user_id}", __( '<strong>SEO:</strong> Your meta-description had more than 320 characters. It was shortened for you.', 'essential-seo' ), 0 );
        }

        update_post_meta( $post_id, 'esseo_meta_boxes', $new );

    } elseif ( '' === $new && $old ) {
        delete_post_meta( $post_id, 'esseo_meta_boxes', $old );
    }

}
