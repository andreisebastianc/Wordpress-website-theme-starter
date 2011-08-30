<?php

//@todo only for development
define(‘WP_DEBUG’, true);

/** Tell WordPress to run twentyten_setup() when the 'after_setup_theme' hook is run. */
add_action( 'after_setup_theme', 'theme_setup' );

if ( ! function_exists( 'theme_setup' ) ):
    function theme_setup() {

        // This theme styles the visual editor with editor-style.css to match the theme style.
        add_editor_style();

        // This theme uses post thumbnails
        add_theme_support( 'post-thumbnails' );

        // Add default posts and comments RSS feed links to head
        add_theme_support( 'automatic-feed-links' );

        // Make theme available for translation
        // Translations can be filed in the /languages/ directory
        load_theme_textdomain( 'default', TEMPLATEPATH . '/languages' );

        $locale = get_locale();
        // i don't know how to use this yet
        $locale_file = TEMPLATEPATH . "/languages/$locale.php";
        if ( is_readable( $locale_file ) )
            require_once( $locale_file );

        // This theme uses wp_nav_menu() in one location.
        register_nav_menus( array(
            'primary' => __( 'Primary Navigation', 'default' ),
        ) );

        //set_post_thumbnail_size( 220, 160, true );

        //add_image_size('icon-size', 45, 40, false);
        //add_image_size('banner-size', 940, 350, false);
    }
endif;

///
// set the default excerpt length
//
function excerpt_length( $length ) {
    //return 40;
}
add_filter( 'excerpt_length', 'excerpt_lenght' );

// important include to avoid clutter
//@TODO add helpers
 include_once('helpers/commons.php');

/* custom post types */
// @TODO add example of improved way of handling post types

// Callback function to show fields in meta box
// @TODO proper comments here
function default_meta_show_box($post,$meta_box) {
    // Use nonce for verification
    echo '<input type="hidden" name="meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';

    echo '<table class="form-table">';
    ///
    //@TODO research strange bug - basically i wanted to send the fields of each metabox but right now it constructs a
    //different array at the callback args construction
    //@TODO display image when needed - maybe save thumb as a cache
    //
    foreach ($meta_box['args'] as $field) {
        // get current post meta data
        $meta = get_post_meta($post->ID, $field['id'], true);

        echo '<tr>',
            '<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
            '<td>';
        switch ($field['type']) {

        case 'hidden':
            echo '<input type="hidden" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />',
                '<br />', $field['desc'];
            break;
            //If Text
        case 'text':
            echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />',
                '<br />', $field['desc'];
            break;

        case 'date':
            echo '<input type="date" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />',
                '<br />', $field['desc'];
            break;

        case 'time':
            echo '<input type="time" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />',
                '<br />', $field['desc'];
            break;

        case 'range':
            echo '<input type="range" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" min="0" max="10"/>',
                '<br />', $field['desc'];
            break;

            //If Text Area
        case 'textarea':
            echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>',
                '<br />', $field['desc'];
            break;

            //If Button
        case 'button':
            echo '<input type="button" name="', $field['id'], '" id="', $field['id'], '"value="', $meta ? $meta : $field['std'], '" />';
            break;
        }
        echo 	'<td>',
            '</tr>';
    }

    echo '</table>';
}

///
// taxonomy
///

add_action('init','build_taxonomies');
function build_taxonomies() {
  //@TODO add taxonomy example
}


// Save data from meta box
// no more writting crappy code, this handles all :) ( of course, given
// that you respect example that is not here right now )
// @TODO add comments here
function save_data($post_id) {
    //@TODO comment here VERY IMPORTANT!!!!
    $metabox = $_POST['meta_box'];
    global $$metabox;

    // verify nonce
    if (!wp_verify_nonce($_POST['meta_box_nonce'], basename(__FILE__))) {
        return $post_id;
    }

    // check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // check permissions
    if ('page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return $post_id;
        }
    } elseif (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    foreach (${$metabox}['fields'] as $field) {
        $old = get_post_meta($post_id, $field['id'], true);
        $new = $_POST[$field['id']];

        if ($new && $new != $old) {
            update_post_meta($post_id, $field['id'], $new);
        } elseif ('' == $new && $old) {
            delete_post_meta($post_id, $field['id'], $old);
        }
    }
}
