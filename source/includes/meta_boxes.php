<?php

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

// Hide our meta from the_meta() and custom fields UI
add_filter( 'is_protected_meta', function( $protected, $meta_key ) {
    return in_array( $meta_key, array( 'esseo_meta_boxes', '_noindex' ), true ) ? true : $protected;
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

    // Noindex
    $noindex = get_post_meta( $post->ID, '_noindex', true );
    ?><p>
        <label>
            <input type="hidden" name="esseo_meta_boxes[noindex]" value="0">
            <input type="checkbox" name="esseo_meta_boxes[noindex]" value="1" <?php checked( $noindex, '1' ); ?>>
            <?php _e( 'Do not index this post/page (noindex)', 'essential-seo' ); ?>
        </label>
    </p><?php

    // SEO description
    ?><p><?php

        ?><label for="esseo_meta_description"><?php _e( 'Meta description (recommended: 150–160 chars, max. 320) — leave empty to use the excerpt or the site default', 'essential-seo' ); ?></label><?php
        ?><br><?php
        ?><textarea name="esseo_meta_boxes[description]" id="esseo_meta_description" class="esseo-meta-description" rows="2" cols="30" style="width:100%;" maxlength="320"><?php

        if ( isset( $meta['description'] ) && ! empty( $meta['description'] ) ) {
            echo esc_textarea( $meta['description'] );
        }

        ?></textarea><?php
        ?><br><?php
        ?><span class="esseo-charcount description" aria-live="polite"></span><?php

    ?></p><?php

}

function esseo_save_seo_meta_boxes( $post_id ) {

    // Verify nonce
    $seo_meta_box_nonce = isset( $_POST['esseo_meta_box_nonce'] ) ? $_POST['esseo_meta_box_nonce'] : '';

    if ( ! wp_verify_nonce( $seo_meta_box_nonce, basename( ESSEO_PLUGIN ) ) ) {
        return;
    }

    // Check autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Bail if our meta box was not part of this request
    if ( ! isset( $_POST['esseo_meta_boxes'] ) ) {
        return;
    }

    // Check permissions for both pages and posts
    $post_type = isset( $_POST['post_type'] ) ? $_POST['post_type'] : '';

    if ( 'page' === $post_type ) {
        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }
    } else {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    $user_id = get_current_user_id();
    $old     = get_post_meta( $post_id, 'esseo_meta_boxes', true );
    $posted  = $_POST['esseo_meta_boxes'];

    /**
     * Sanitize textarea content
     *
     * @link https://codex.wordpress.org/Validating_Sanitizing_and_Escaping_User_Data
     * @link https://codex.wordpress.org/Function_Reference/sanitize_meta
     * @link https://developer.wordpress.org/plugins/security/data-validation/
     */
    $description = isset( $posted['description'] ) ? sanitize_textarea_field( $posted['description'] ) : '';
    $description = sanitize_text_field( trim( $description ) );

    // Max of 320 characters (multibyte-safe, so umlauts count as one char like the textarea maxlength)
    if ( mb_strlen( $description ) > 320 ) {
        $description = mb_substr( $description, 0, 320 );
        set_transient( "seo_meta_box_error_msg_{$post_id}_{$user_id}", __( '<strong>SEO:</strong> Your meta-description had more than 320 characters. It was shortened for you.', 'essential-seo' ), 0 );
    }

    // Store the description only; noindex is kept in its own _noindex meta key.
    // ( update_post_meta() unslashes the value, so no addslashes() here. )
    if ( '' !== $description ) {
        update_post_meta( $post_id, 'esseo_meta_boxes', array( 'description' => $description ) );
    } elseif ( $old ) {
        delete_post_meta( $post_id, 'esseo_meta_boxes' );
    }

    // Save noindex separately to keep the _noindex meta key consistent across theme and plugin.
    $noindex_value = isset( $posted['noindex'] ) && '1' === $posted['noindex'] ? '1' : '0';
    update_post_meta( $post_id, '_noindex', $noindex_value );

}
