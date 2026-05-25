<?php

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Plugin Update Checker
 *
 * @link https://github.com/YahnisElsts/plugin-update-checker/
 */

require_once 'plugin-update-checker/plugin-update-checker.php';

$esseo_UpdateChecker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
    'https://github.com/marcelbest/essential-seo/',
    ESSEO_PLUGIN,
    ESSEO_PLUGIN_NAME
);

/**
 * Load plugin textdomain.
 * 
 * Make sure that the language files have the plugin textdomain in front ex.: 'plug-in-textdomain-de_CH'
 * 
 * @link https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/
 * @link https://developer.wordpress.org/reference/functions/load_plugin_textdomain/
 */

add_action( 'plugins_loaded', ESSEO_SHORTNAME . '_load_textdomain' );

function esseo_load_textdomain() {
    load_plugin_textdomain( ESSEO_PLUGIN_NAME, false, dirname( ESSEO_PLUGIN_BASENAME ) . '/languages' );
}

// Plugin activation
function esseo_install() {

    // Creates new database field
    // And auto loads the settings -> 'yes'
    add_option( 'esseo_options', '', '', true );

    // Saves the state of the plugin
    // @link https://codex.wordpress.org/Function_Reference/set_transient
    set_transient( 'essential-seo-activation', true, 0 );

    // Save default values only on fresh install, never overwrite existing settings
    if ( get_option( 'esseo_options' ) === false ) {
        $default = [
            ESSEO_SHORTNAME . '_checkbox_og'         => '0',
            ESSEO_SHORTNAME . '_title_seperator'     => '|',
            ESSEO_SHORTNAME . '_default_description' => '',
            ESSEO_SHORTNAME . '_share_img'           => '',
            ESSEO_SHORTNAME . '_header_scripts'      => '',
        ];

        update_option( 'esseo_options', $default );
    }

}

register_activation_hook( ESSEO_PLUGIN, 'esseo_install' );

// Plugin deactivation — data is preserved; cleanup happens in uninstall.php
function esseo_remove() {

    delete_transient( 'essential-seo-activation' );
    delete_transient( 'essential-seo-translation-possible' );

}

register_deactivation_hook( ESSEO_PLUGIN, 'esseo_remove' );


// Show notice on activation
add_action('admin_notices', 'esseo_install_notice');

function esseo_install_notice() {

    // Check transient
    if ( get_transient( 'essential-seo-activation' ) ) {
        
        ?><div class="updated notice is-dismissible"><?php

            ?><p><?php

                _e('Thank you for using this plugin.<br><strong>It requires some important settings.</strong>', 'essential-seo');

            ?><br><br><a href="options-general.php?page=<?php echo ESSEO_PLUGIN_NAME; ?>"><?php

                _e('To the settings page', 'essential-seo');

            ?></a></p><?php

        ?></div><?php
        
        // Delete transient
        delete_transient( 'essential-seo-activation' );
    }

}

// ================

/**
 * Admin
 * 
 * @link http://wpsettingsapi.jeroensormani.com/settings-generator
 */

function esseo_add_admin_menu() { 
    add_submenu_page( 'options-general.php', 'Essential SEO', 'Essential SEO', 'manage_options', ESSEO_PLUGIN_NAME, 'esseo_settings_page_fn' );
}

/**
 * Specify Hooks/Filters
 */

add_action( 'init', 'esseo_check_roles_after_everything_is_loaded' );

function esseo_check_roles_after_everything_is_loaded() {

    $current_user = wp_get_current_user();

    if ( current_user_can( 'edit_posts' ) ) {

        add_action( 'save_post', 'esseo_save_seo_meta_boxes' );

    }

    if ( current_user_can( 'edit_others_posts' ) ) {

        add_action( 'admin_menu', 'esseo_add_admin_menu' );
        add_action( 'admin_init', 'esseo_settings_init' );

    }

}

/**
 * Helper function for defining variables for the current page
 *
 * @return array
 */

function esseo_get_settings(){

    $output = array();
     
    // put together the output array 
    $output['esseo_option_name']       = 'esseo_options';
    $output['esseo_page_title']        = __( 'Essential SEO Settings Page','essential-seo');
    $output['esseo_page_sections']     = esseo_options_page_sections();
    $output['esseo_page_fields']       = esseo_options_page_fields();
    $output['esseo_contextual_help']   = ''; // https://developer.wordpress.org/reference/classes/WP_Screen/add_help_tab/
     
    return $output;

}

/*
 * Register settings
 */

function esseo_settings_init() {

    // get the settings sections array
    $settings_output    = esseo_get_settings();
    $esseo_option_name = $settings_output['esseo_option_name'];
     
    // settings
    // register_setting( $option_group, $option_name, $sanitize_callback );
    register_setting( $esseo_option_name, $esseo_option_name, 'esseo_validate_options' );

    // sections
    // add_settings_section( $id, $title, $callback, $page );
    if ( ! empty( $settings_output['esseo_page_sections'] ) ) {

        // call the "add_settings_section" for each!
        foreach ( $settings_output['esseo_page_sections'] as $id => $title ) {
            add_settings_section( $id, $title, 'esseo_section_fn', ESSEO_PLUGIN );
        }

    }

    // fields
    if ( ! empty( $settings_output['esseo_page_fields'] ) ) {
        // call the "add_settings_field" for each!
        foreach ($settings_output['esseo_page_fields'] as $option) {
            esseo_create_settings_field($option);
        }
    }

}

/*
 * Admin Settings Page HTML
 * 
 * @return echoes output
 */

function esseo_settings_page_fn() {

    // get the settings sections array
    $settings_output = esseo_get_settings();

    ?><div class="wrap"><?php
        ?><h2><?php echo $settings_output['esseo_page_title']; ?></h2><?php

        ?><p><?php

            _e('In order to let this plugin enhance your website, please go carefully through each setting here. Once set, you can leave it as is and do the rest of the work on each post and page. They have now a custom fields to let you write a description for search engines.', 'essential-seo');

        ?></p><?php

        // Check for transient
        if ( get_transient( 'essential-seo-translation-possible' ) ) {

            ?><div class="polylang-notice"><?php

                // Do translations here
                ?><p><strong><?php

                    _e('Great', 'essential-seo');

                    ?>!</strong> <?php

                    _e('You have Polylang installed. You can translate your default settings for all languages here', 'essential-seo');

                ?>:</p><?php

                ?><p><?php

                    ?><a href="admin.php?page=mlang_strings&s&group=Essential+SEO&paged=1"><?php

                        _e('Go to Polylang settings', 'essential-seo');

                    ?></a><?php

                ?></p><?php

            ?></div><?php

        }

        ?><form action="options.php" method="post"><?php

            // http://codex.wordpress.org/Function_Reference/settings_fields
            settings_fields( $settings_output['esseo_option_name'] );

            // http://codex.wordpress.org/Function_Reference/do_settings_sections
            do_settings_sections( ESSEO_PLUGIN );
            
            submit_button();

        ?></form><?php

    ?></div><?php

}

// ================

/**
 * Define settings sections
 *
 * array key=$id, array value=$title in: add_settings_section( $id, $title, $callback, $page );
 * @return array
 */
function esseo_options_page_sections() {
     
    $sections = [];
    $sections['og_section']      = __('The Open Graph protocol', 'essential-seo');
    $sections['header_section']  = __('HTML head tags', 'essential-seo');
    $sections['scripts_section'] = __('Scripts', 'essential-seo');

    return $sections;
}

/*
 * Section HTML, displayed before the first option
 * @return echoes output
 */

function esseo_section_fn($desc) {

    // print_r($desc);
    // Array ( [id] => txt_section [title] => Text Form Fields [callback] => esseo_section_fn )
    switch ($desc['id']) {
        case 'og_section':
            
            ?><p><?php

                _e('The <a href="http://ogp.me/" target="_blank">Open Graph protocol</a> enables any web page to become a rich object in a social graph.', 'essential-seo');

            ?></p><?php

            break;
        
        case 'header_section':

            ?><p><?php

                _e( "Here you can set the defaults for your website's description, keywords, share image and the title.", 'essential-seo' );

            ?></p><?php

            break;

        case 'scripts_section':

            ?><p><?php

                _e('Paste your complete tracking snippet here (GA4, GTM, etc.). Preconnect hints and the GTM noscript fallback are added automatically.', 'essential-seo');

            ?></p><?php

            break;

        default:

            ?><p><?php

                _e('Settings for this section', 'essential-seo');

            ?></p><?php

            break;
    }

}


/**
 * Define our form fields (settings) 
 *
 * @return array
 */
function esseo_options_page_fields() {

    // Open Graph section
    $options[] = array(
        'section' => 'og_section',
        'id'      => ESSEO_SHORTNAME . '_checkbox_og',
        'title'   => __( 'Enable Open Graph', 'essential-seo' ),
        'desc'    => '',
        'type'    => 'checkbox',
        'std'     => 0, // 0 for off
    );

    // Header section
    $options[] = array(
        'section' => 'header_section',
        'id'      => ESSEO_SHORTNAME . '_title_seperator',
        'title'   => __( 'Title separator', 'essential-seo' ),
        'desc'    => __( 'Default & recommended: |', 'essential-seo' ),
        'type'    => 'select',
        'std'     => '1',
        // &verbar; | &dash; | &ndash; | &mdash; | : | :: | &Verbar; | &raquo;
        'choices' => [ '', '|', '-', '–', '—', ':', '::', '‖', '»' ],
    );

    $options[] = array(
        'section'     => 'header_section',
        'id'          => ESSEO_SHORTNAME . '_default_description',
        'title'       => __( 'Default description', 'essential-seo' ),
        'desc'        => __( "Your default description of your website (from about 160 up to 320 chars). No HTML!<br>The description should correspont to your website's (text-)content.", 'essential-seo' ),
        'type'        => 'textarea',
        'std'         => '',
        'maxlength'   => '320',
        'field_class' => 'nohtml',
    );

    $options[] = array(
        'section'     => 'header_section',
        'id'          => ESSEO_SHORTNAME . '_share_img',
        'title'       => __( 'Share image path', 'essential-seo' ),
        'desc'        => __( 'The default share image path is needed here, please set one. http://your.domain/img-path<br>If a page or post has a feature image it will be used instead.', 'essential-seo' ),
        'type'        => 'text',
        'std'         => '',
        'maxlength'   => '255',
        'field_class' => 'url',
    );

    // Scripts section
    $options[] = array(
        'section'     => 'scripts_section',
        'id'          => ESSEO_SHORTNAME . '_header_scripts',
        'title'       => __( 'Header Scripts', 'essential-seo' ),
        'desc'        => __( 'Paste your complete tracking snippet here (GA4, GTM, etc.). The plugin automatically adds preconnect hints for known Google domains and a GTM noscript fallback in the footer.', 'essential-seo' ),
        'type'        => 'textarea',
        'std'         => '',
        'field_class' => 'scripts',
    );
     
    return $options;
}

// ================

/*
 * Validate input
 * 
 * @return array
 */

function esseo_validate_options($input) {
     
    // for enhanced security, create a new empty array
    $valid_input = array();
     
    // collect only the values we expect and fill the new $valid_input array i.e. whitelist our option IDs
     
        // get the settings sections array
        $settings_output = esseo_get_settings();
        
        $options = $settings_output['esseo_page_fields'];
         
        // run a foreach and switch on option type
        foreach ( $options as $option ) {

            switch ( $option['type'] ) {
                case 'text':

                    //switch validation based on the field_class!
                    switch ( $option['field_class'] ) {
                        //for numeric 
                        case 'numeric':
                            //accept the input only when numeric!
                            $input[$option['id']]       = trim( $input[$option['id']] ); // trim whitespace
                            $valid_input[$option['id']] = ( is_numeric( $input[$option['id']] ) ) ? $input[$option['id']] : 'Expecting a Numeric value!';

                            // register error
                            if ( is_numeric( $input[$option['id']] ) == false ) {
                                add_settings_error(
                                    $option['id'], // setting title
                                    ESSEO_SHORTNAME . '_txt_numeric_error', // error ID
                                    __('Expecting a Numeric value! Please fix.','essential-seo'), // error message
                                    'error' // type of message
                                );
                            }
                        break;
                        
                        //for multi-numeric values (separated by a comma)
                        case 'multinumeric':
                            //accept the input only when the numeric values are comma separated
                            $input[$option['id']] = trim( $input[$option['id']] ); // trim whitespace

                            if ( $input[$option['id']] != '' ) {
                                // /^-?\d+(?:,\s?-?\d+)*$/ matches: -1 | 1 | -12,-23 | 12,23 | -123, -234 | 123, 234  | etc.
                                $valid_input[$option['id']] = ( preg_match( '/^-?\d+(?:,\s?-?\d+)*$/', $input[$option['id']] ) == 1 ) ? $input[$option['id']] : __( 'Expecting comma separated numeric values', 'essential-seo' );
                            } else {
                                $valid_input[$option['id']] = $input[$option['id']];
                            }

                            // register error
                            if ( $input[$option['id']] != '' && preg_match( '/^-?\d+(?:,\s?-?\d+)*$/', $input[$option['id']] ) != 1 ) {
                                add_settings_error(
                                    $option['id'], // setting title
                                    ESSEO_SHORTNAME . '_txt_multinumeric_error', // error ID
                                    __('Expecting comma separated numeric values! Please fix.','essential-seo'), // error message
                                    'error' // type of message
                                );
                            }
                        break;
                        
                        // for no html
                        case 'nohtml':
                            // accept the input only after stripping out all html, extra white space etc!
                            $input[$option['id']]       = sanitize_text_field( $input[$option['id']] ); // need to add slashes still before sending to the database
                            $valid_input[$option['id']] = addslashes( $input[$option['id']] );
                        break;

                        // for nohtml_nospaces_lowercase
                        case 'nohtml_nospaces_lowercase':
                            // accept the input only after stripping out all html, extra white space etc!
                            $input[$option['id']]       = sanitize_text_field( $input[$option['id']] ); // need to add slashes still before sending to the database
                            $input[$option['id']]       = strtolower( str_replace( ' ', '', $input[$option['id']] ) ); // only lower case, no spaces
                            $valid_input[$option['id']] = addslashes( $input[$option['id']] );
                        break;

                        // for url
                        case 'url':
                            // accept the input only when the url has been sanitized for database usage with esc_url_raw()
                            $input[$option['id']]       = trim( $input[$option['id']] ); // trim whitespace
                            $valid_input[$option['id']] = esc_url_raw( $input[$option['id']] );
                        break;
                        
                        //for email
                        case 'email':
                            //accept the input only after the email has been validated
                            $input[$option['id']] = trim( $input[$option['id']] ); // trim whitespace
                            if ( $input[$option['id']] != '' ) {
                                $valid_input[$option['id']] = ( is_email( $input[$option['id']] ) !== false ) ? $input[$option['id']] : __( 'Invalid email! Please re-enter!', 'essential-seo' );
                            } elseif ( $input[$option['id']] == '' ) {
                                $valid_input[$option['id']] = __( 'This setting field cannot be empty! Please enter a valid email address.', 'essential-seo' );
                            }

                            // register error
                            if ( is_email( $input[$option['id']] ) == false || $input[$option['id']] == '' ) {
                                add_settings_error(
                                    $option['id'], // setting title
                                    ESSEO_SHORTNAME . '_txt_email_error', // error ID
                                    __('Please enter a valid email address.','essential-seo'), // error message
                                    'error' // type of message
                                );
                            }
                        break;
                        
                        // a "cover-all" fall-back when the class argument is not set
                        default:
                            // accept only a few inline html elements
                            $allowed_html = array(
                                'a'      => array( 'href' => array(), 'title' => array() ),
                                'b'      => array(),
                                'em'     => array(),
                                'i'      => array(),
                                'strong' => array(),
                            );

                            $input[$option['id']]       = trim( $input[$option['id']] ); // trim whitespace
                            $input[$option['id']]       = force_balance_tags( $input[$option['id']] ); // find incorrectly nested or missing closing tags and fix markup
                            $input[$option['id']]       = wp_kses( $input[$option['id']], $allowed_html ); // need to add slashes still before sending to the database
                            $valid_input[$option['id']] = addslashes( $input[$option['id']] );
                        break;
                    }

                    // Max length limit
                    if ( isset( $option['maxlength'] ) && ! empty( $option['maxlength'] ) ) {
                        if ( strlen( $valid_input[$option['id']] ) > $option['maxlength'] ) {
                            $valid_input[$option['id']] = substr( $valid_input[$option['id']], 0, $option['maxlength'] );
                        }
                    }

                break;
                 
                case 'multi-text':
                    // this will hold the text values as an array of 'key' => 'value'
                    unset( $textarray );

                    $text_values = array();
                    foreach ( $option['choices'] as $k => $v ) {
                        // explode the connective
                        $pieces = explode( '|', $v );

                        $text_values[] = $pieces[1];
                    }

                    foreach ( $text_values as $v ) {

                        // Check that the option isn't empty
                        if ( ! empty( $input[$option['id'] . '|' . $v] ) ) {
                            // If it's not null, make sure it's sanitized, add it to an array
                            switch ( $option['class'] ) {
                                // different sanitation actions based on the class create you own cases as you need them

                                // for numeric input
                                case 'numeric':
                                    // accept the input only if is numeric!
                                    $input[$option['id'] . '|' . $v] = trim( $input[$option['id'] . '|' . $v] ); // trim whitespace
                                    $input[$option['id'] . '|' . $v] = ( is_numeric( $input[$option['id'] . '|' . $v] ) ) ? $input[$option['id'] . '|' . $v] : '';
                                break;

                                // a "cover-all" fall-back when the class argument is not set
                                default:
                                    // strip all html tags and white-space.
                                    $input[$option['id'] . '|' . $v] = sanitize_text_field( $input[$option['id'] . '|' . $v] ); // need to add slashes still before sending to the database
                                    $input[$option['id'] . '|' . $v] = addslashes( $input[$option['id'] . '|' . $v] );
                                break;
                            }
                            // pass the sanitized user input to our $textarray array
                            $textarray[$v] = $input[$option['id'] . '|' . $v];

                        } else {
                            $textarray[$v] = '';
                        }
                    }
                    // pass the non-empty $textarray to our $valid_input array
                    if ( ! empty( $textarray ) ) {
                        $valid_input[$option['id']] = $textarray;
                    }

                    // Max length limit
                    if ( isset( $option['maxlength'] ) && ! empty( $option['maxlength'] ) ) {
                        if ( strlen( $valid_input[$option['id']] ) > $option['maxlength'] ) {
                            $valid_input[$option['id']] = substr($valid_input[$option['id']], 0, $option['maxlength']);
                        }
                    }

                break;
                 
                case 'textarea':
                    //switch validation based on the class!
                    switch ( $option['field_class'] ) {
                        // for raw script/html content — only admins (manage_options) can save this
                        case 'scripts':
                            $valid_input[$option['id']] = wp_unslash( $input[$option['id']] );
                        break;

                        // for only inline html
                        case 'inlinehtml':
                            // accept only inline html
                            $input[$option['id']]       = trim( $input[$option['id']] ); // trim whitespace
                            $input[$option['id']]       = force_balance_tags( $input[$option['id']] ); // find incorrectly nested or missing closing tags and fix markup
                            $input[$option['id']]       = addslashes( $input[$option['id']] ); // wp_filter_kses expects content to be escaped!
                            $valid_input[$option['id']] = wp_filter_kses( $input[$option['id']] ); // calls stripslashes then addslashes
                        break;

                        // for no html
                        case 'nohtml':
                            // accept the input only after stripping out all html, extra white space etc!
                            $input[$option['id']]       = sanitize_text_field( $input[$option['id']] ); // need to add slashes still before sending to the database
                            $valid_input[$option['id']] = addslashes( $input[$option['id']] );
                        break;
                         
                        // for allowlinebreaks
                        case 'allowlinebreaks':
                            // accept the input only after stripping out all html, extra white space etc!
                            $input[$option['id']]       = wp_strip_all_tags( $input[$option['id']] ); // need to add slashes still before sending to the database
                            $valid_input[$option['id']] = addslashes( $input[$option['id']] );
                        break;

                        // a "cover-all" fall-back when the class argument is not set
                        default:
                            // accept only limited html
                            $allowed_html = array(
                                'a'          => array( 'href' => array(), 'title' => array() ),
                                'b'          => array(),
                                'blockquote' => array( 'cite' => array() ),
                                'br'         => array(),
                                'dd'         => array(),
                                'dl'         => array(),
                                'dt'         => array(),
                                'em'         => array(),
                                'i'          => array(),
                                'li'         => array(),
                                'ol'         => array(),
                                'p'          => array(),
                                'q'          => array( 'cite' => array() ),
                                'strong'     => array(),
                                'ul'         => array(),
                                'h1'         => array( 'align' => array(), 'class' => array(), 'id' => array(), 'style' => array() ),
                                'h2'         => array( 'align' => array(), 'class' => array(), 'id' => array(), 'style' => array() ),
                                'h3'         => array( 'align' => array(), 'class' => array(), 'id' => array(), 'style' => array() ),
                                'h4'         => array( 'align' => array(), 'class' => array(), 'id' => array(), 'style' => array() ),
                                'h5'         => array( 'align' => array(), 'class' => array(), 'id' => array(), 'style' => array() ),
                                'h6'         => array( 'align' => array(), 'class' => array(), 'id' => array(), 'style' => array() ),
                            );

                            $input[$option['id']]       = trim( $input[$option['id']] ); // trim whitespace
                            $input[$option['id']]       = force_balance_tags( $input[$option['id']] ); // find incorrectly nested or missing closing tags and fix markup
                            $input[$option['id']]       = wp_kses( $input[$option['id']], $allowed_html ); // need to add slashes still before sending to the database
                            $valid_input[$option['id']] = addslashes( $input[$option['id']] );
                        break;
                    }

                    // Max length limit
                    if ( isset( $option['maxlength'] ) && ! empty( $option['maxlength'] ) ) {
                        if ( strlen( $valid_input[$option['id']] ) > $option['maxlength'] ) {
                            $valid_input[$option['id']] = substr( $valid_input[$option['id']], 0, $option['maxlength'] );
                        }
                    }

                break;
                 
                case 'select':
                    // check to see if the selected value is in our approved array of values!
                    $valid_input[$option['id']] = ( in_array( $input[$option['id']], $option['choices'] ) ? $input[$option['id']] : '' );
                break;

                case 'select2':
                    // process $select_values
                    $select_values = array();
                    foreach ( $option['choices'] as $k => $v ) {
                        // explode the connective
                        $pieces = explode( '|', $v );

                        $select_values[] = $pieces[1];
                    }
                    // check to see if selected value is in our approved array of values!
                    $valid_input[$option['id']] = ( in_array( $input[$option['id']], $select_values ) ? $input[$option['id']] : '' );
                break;

                case 'checkbox':
                    // if it's not set, default to null!
                    if ( ! isset( $input[$option['id']] ) ) {
                        $input[$option['id']] = null;
                    }
                    // Our checkbox value is either 0 or 1
                    $valid_input[$option['id']] = ( $input[$option['id']] == 1 ? 1 : 0 );
                break;
                 
                case 'multi-checkbox':
                    unset( $checkboxarray );
                    $check_values = array();
                    foreach ( $option['choices'] as $k => $v ) {
                        // explode the connective
                        $pieces = explode( '|', $v );

                        $check_values[] = $pieces[1];
                    }

                    foreach ( $check_values as $v ) {

                        // Check that the option isn't null
                        if ( ! empty( $input[$option['id'] . '|' . $v] ) ) {
                            // If it's not null, make sure it's true, add it to an array
                            $checkboxarray[$v] = 'true';
                        } else {
                            $checkboxarray[$v] = 'false';
                        }
                    }
                    // Take all the items that were checked, and set them as the main option
                    if ( ! empty( $checkboxarray ) ) {
                        $valid_input[$option['id']] = $checkboxarray;
                    }
                break;
                 
            }
        }

    // return validated input
    return $valid_input;
}


/*
 * Form Fields HTML
 * All form field types share the same function!!
 * @return echoes output
 */
function esseo_form_field_fn( $args = array() ) {

    $type        = isset( $args['type'] )        ? $args['type']        : '';
    $id          = isset( $args['id'] )          ? $args['id']          : '';
    $desc        = isset( $args['desc'] )        ? $args['desc']        : '';
    $std         = isset( $args['std'] )         ? $args['std']         : '';
    $choices     = isset( $args['choices'] )     ? $args['choices']     : array();
    $maxlength   = isset( $args['maxlength'] )   ? $args['maxlength']   : '';
    $field_class = isset( $args['field_class'] ) ? $args['field_class'] : '';

    // get the settings sections array
    $settings_output   = esseo_get_settings();
    $esseo_option_name = $settings_output['esseo_option_name'];
    $options           = get_option( $esseo_option_name );

    // Ensure options is an array (get_option returns '' if never saved)
    if ( ! is_array( $options ) ) {
        $options = [];
    }

    // pass the standard value if the option is not yet set in the database
    if ( ! isset( $options[$id] ) && $type != 'checkbox' ) {
        $options[$id] = $std;
    }

    // additional field class. output only if the field_class is defined in the create_setting arguments
    $field_class = ( $field_class != '' ) ? ' ' . $field_class : '';
     
     
    // switch html display based on the setting type.
    switch ( $type ) {
        case 'text':
            $options[$id] = stripslashes( $options[$id] );
            $options[$id] = esc_attr( $options[$id] );
            echo "<input class='regular-text$field_class' type='text' id='$id' name='" . $esseo_option_name . "[$id]' value='$options[$id]'";
            echo ( $maxlength != '' ) ? " maxlength='{$maxlength}'" : '';
            echo '>';
            echo ( $desc != '' ) ? "<br><br><span class='description'>$desc</span>" : '';
        break;
         
        case 'multi-text':
            foreach ( $choices as $item ) {
                $item = explode( '|', $item ); // cat_name|cat_slug
                $item[0] = esc_html__( $item[0], 'essential-seo' );
                if ( ! empty( $options[$id] ) ) {
                    foreach ( $options[$id] as $option_key => $option_val ) {
                        if ( $item[1] == $option_key ) {
                            $value = $option_val;
                        }
                    }
                } else {
                    $value = '';
                }
                echo "<span>$item[0]:</span> <input class='$field_class' type='text' id='$id|$item[1]' name='" . $esseo_option_name . "[$id|$item[1]]' value='$value'";
                echo ( $maxlength != '' ) ? " maxlength='{$maxlength}'" : '';
                echo '><br>';
            }
            echo ( $desc != '' ) ? "<br><span class='description'>$desc</span>" : '';
        break;

        case 'textarea':
            $options[$id] = stripslashes( $options[$id] );
            $options[$id] = esc_html( $options[$id] );
            echo "<textarea class='textarea$field_class' type='text' id='$id' name='" . $esseo_option_name . "[$id]' rows='4' cols='20'";
            echo ( $maxlength != '' ) ? " maxlength='{$maxlength}'" : '';
            echo ">$options[$id]</textarea>";
            echo ( $desc != '' ) ? "<br><br><span class='description'>$desc</span>" : '';
        break;
         
        case 'select':
            echo "<select id='$id' class='select$field_class' name='" . $esseo_option_name . "[$id]'>";
            foreach ( $choices as $item ) {
                $value    = esc_attr( $item );
                $item     = esc_html( $item );
                $selected = ( $options[$id] == $value ) ? 'selected="selected"' : '';
                echo "<option value='$value' $selected>$item</option>";
            }
            echo '</select>';
            echo ( $desc != '' ) ? "<br><br><span class='description'>$desc</span>" : '';
        break;

        case 'select2':
            echo "<select id='$id' class='select$field_class' name='" . $esseo_option_name . "[$id]'>";
            foreach ( $choices as $item ) {
                $item     = explode( '|', $item );
                $item[0]  = esc_html( $item[0] );
                $selected = ( $options[$id] == $item[1] ) ? 'selected="selected"' : '';
                echo "<option value='$item[1]' $selected>$item[0]</option>";
            }
            echo '</select>';
            echo ( $desc != '' ) ? "<br><br><span class='description'>$desc</span>" : '';
        break;

        case 'checkbox':
            echo "<input class='checkbox$field_class' type='checkbox' id='$id' name='" . $esseo_option_name . "[$id]' value='1' " . checked( $options[$id], 1, false ) . '>';
            echo ( $desc != '' ) ? "<br><br><span class='description'>$desc</span>" : '';
        break;

        case 'multi-checkbox':
            foreach ( $choices as $item ) {
                $item    = explode( '|', $item );
                $item[0] = esc_html( $item[0] );
                $checked = '';

                if ( isset( $options[$id][$item[1]] ) ) {
                    if ( $options[$id][$item[1]] == 'true' ) {
                        $checked = 'checked="checked"';
                    }
                }

                echo "<input class='checkbox$field_class' type='checkbox' id='$id|$item[1]' name='" . $esseo_option_name . "[$id|$item[1]]' value='1' $checked> $item[0] <br />";
            }
            echo ( $desc != '' ) ? "<br><br><span class='description'>$desc</span>" : '';
        break;
    }
}

/**
 * Helper function for registering our form field settings
 *
 * src: http://alisothegeek.com/2011/01/wordpress-settings-api-tutorial-1/
 * @param (array) $args The array of arguments to be used in creating the field
 * @return function call
 */

function esseo_create_settings_field( $args = array() ) {
    // default array to overwrite when calling the function
    $defaults = array(
        'id'      => 'default_field',                    // the ID of the setting in our options array, and the ID of the HTML form element
        'title'   => 'Default Field',                    // the label for the HTML form element
        'desc'    => 'This is a default description.',   // the description displayed under the HTML form element
        'std'     => '',                                 // the default value for this setting
        'type'    => 'text',                             // the HTML form element to use
        'section' => 'main_section',                     // the section this setting belongs to — must match the array key of a section in wptuts_options_page_sections()
        'choices' => array(),                            // (optional): the values in radio buttons or a drop-down menu
        'maxlength' => '',
        'field_class'   => ''                            // the HTML form element field_class. Also used for validation purposes!
    );
     
    $parsed      = wp_parse_args( $args, $defaults );
    $id          = $parsed['id'];
    $title       = $parsed['title'];
    $desc        = $parsed['desc'];
    $std         = $parsed['std'];
    $type        = $parsed['type'];
    $section     = $parsed['section'];
    $choices     = $parsed['choices'];
    $maxlength   = $parsed['maxlength'];
    $field_class = $parsed['field_class'];
     
    // additional arguments for use in form field output in the function esseo_form_field_fn!
    $field_args = array(
        'type'      => $type,
        'id'        => $id,
        'desc'      => $desc,
        'std'       => $std,
        'choices'   => $choices,
        'maxlength' => $maxlength,
        'label_for' => $id,
        'field_class'     => $field_class
    );
 
    add_settings_field( $id, $title, 'esseo_form_field_fn', ESSEO_PLUGIN, $section, $field_args );

}


// ================

/**
 * Helper function for creating admin messages
 * src: http://www.wprecipes.com/how-to-show-an-urgent-message-in-the-wordpress-admin-area
 *
 * @param (string) $message The message to echo
 * @param (string) $msgclass The message class
 * @return echoes the message
 */
function esseo_show_msg($message, $msgclass = 'info') {

    // hidden with css, but needed for error handling with jQuery
    echo "<div class='esseo-message $msgclass'>$message</div>";
}

/**
 * Callback function for displaying admin messages
 *
 * @return calls esseo_show_msg()
 */
function esseo_admin_msgs() {
    
    // check for our settings page - need this in conditional further down
    $esseo_settings_pg = isset($_GET['page']) ? strpos($_GET['page'], ESSEO_PLUGIN_NAME) : '';
    // collect setting errors/notices: //http://codex.wordpress.org/Function_Reference/get_settings_errors
    $set_errors = get_settings_errors(); 
     
    //display admin message only for the admin to see, only on our settings page and only when setting errors/notices are returned! 
    if ( current_user_can( 'manage_options' ) && false !== $esseo_settings_pg && ! empty( $set_errors ) ) {

        // Show only on settings page
        if ( ! empty( $esseo_settings_pg ) ) {

            // have our settings successfully been updated?
            if ( $set_errors[0]['code'] == 'settings_updated' && isset( $_GET['settings-updated'] ) ) {
                
                esseo_show_msg("<p>" . $set_errors[0]['message'] . "</p>", 'updated');

            // have errors been found?

            } else {
                // there may be more than one so run a foreach loop.
                foreach ( $set_errors as $set_error ) {
                    // set the title attribute to match the error "setting title" - need this in js file
                    esseo_show_msg("<p class='setting-error-message' data-title='" . $set_error['setting'] . "'>" . $set_error['message'] . "</p>", 'error');
                }
            }

        }

    }
}
 
// admin messages hook!
add_action('admin_notices', 'esseo_admin_msgs');


// Admin style
add_action('admin_enqueue_scripts', 'esseo_options_page_style');

function esseo_options_page_style( $hook ) {

    if ( 'settings_page_' . ESSEO_PLUGIN_NAME !== $hook ) {
        return;
    }

    // https://codex.wordpress.org/Function_Reference/plugin_dir_url
    wp_enqueue_style( 'esseo-style', plugin_dir_url( ESSEO_PLUGIN ) . 'css/style.css', array(), ESSEO_VERSION );
    wp_enqueue_script( 'esseo-script', plugin_dir_url( ESSEO_PLUGIN ) . 'js/script.js', array( 'jquery' ), ESSEO_VERSION, true );
}
