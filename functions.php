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
        load_theme_textdomain( 'twentyten', TEMPLATEPATH . '/languages' );

        $locale = get_locale();
        // i don't know how to use this yet
        $locale_file = TEMPLATEPATH . "/languages/$locale.php";
        if ( is_readable( $locale_file ) )
            require_once( $locale_file );

        // This theme uses wp_nav_menu() in one location.
        register_nav_menus( array(
            'primary' => __( 'Primary Navigation', 'twentyten' ),
        ) );


        set_post_thumbnail_size( 220, 160, true );

        add_image_size('icon-size', 45, 40, false);
        add_image_size('banner-size', 940, 350, false);
    }
endif;

///
// set the default excerpt length
//
function excerpt_length( $length ) {
//    return 40;
}
add_filter( 'excerpt_length', 'excerpt_lenght' );

// important include to avoid clutter
// include_once('helpers/commons.php');

/* custom post types */

/**
 * custom post type setup example
 * build arrays using this example, create another meta file in the meta folder
 * following the example and your mind, and let the rest being handled by the meta
 * builder and meta save functions
 */

/**
 * @TODO check this as EXAMPLE
 * builds an array for the book custom post type
 */
add_action('init', 'post_type_books');
function post_type_books()
{
    $labels = array(
        'name' => _x('Book post', 'post type general name'),
        'singular_name' => _x('Book', 'post type singular name'),
        'add_new' => _x('Add new', 'add_book'),
        'add_new_item' => __('Add new book')
    );
    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        'hierarchical' => true,
        'menu_position' => null,
        //	'taxonomies' => array('book_category'),
        'supports' => array('title','editor','excerpt','thumbnail'));
    register_post_type('book',$args);
}
include_once('meta/meta_books.php');


/**
 * builds the genre taxonomy for the books and author custom post types
 */
add_action('init','build_taxonomies');
function build_taxonomies() {
    $labels = array(
        'name' => _x('Book genre','taxonomy general name'),
        'singular_name' => _x('Genre','taxonomy singular name'),
        'search_items' => __('Search after genre'),
        'all_items' => __('All genres'),
        'parent_item' => __('Genre parent'),
        'parent_item_colon' => __('Genre parent'),
        'edit_item' => __('Edit genre'),
        'update_item' => __('Update genre'),
        'add_new_item' => __('Add genre'),
        'new_item_name' => __('New name for genre'),
    );
    register_taxonomy('gen',array('book'),array('hierarchical' => true,'labels' => $labels));
}

/***
 * used by custom post types build with the supplied model to construct the meta fields and box, based on the fields
 * described in the array supplied
 * it includes elements ready for html5
 * @param $post
 * @param $meta_box the meta_box array used to build the meta attached for a custom post type
 */
function default_meta_show_box($post,$meta_box) {
    // Use nonce for verification
    echo '<input type="hidden" name="meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';

    echo '<table class="form-table">';
    ///
    //@todo research strange bug - basically i wanted to send the fields of each metabox but right now it constructs a
    //different array at the callback args construction
    //
    foreach ($meta_box['args'] as $field) {
        // get current post meta data
        $meta = get_post_meta($post->ID, $field['id'], true);

        echo '<tr>',
            '<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
            '<td>';
        switch ($field['type']) {

        case 'hidden':
            echo '<input type="hidden" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />';
            break;
            //If Text
        case 'time':
        case 'date':
        case 'text':
            echo '<input type="',$field['type'], '" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />',
                '<br />', $field['desc'];
            break;

       /*
        * refactored above, not removed for testing
        case 'date':
            echo '<input type="date" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />',
                '<br />', $field['desc'];
            break;

        case 'time':
            echo '<input type="time" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />',
                '<br />', $field['desc'];
            break;
       */

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

/**
 * saves meta data for a custom post type
 *
 * handles the $_POST data in order to save the meta information for the custom post types;
 * gets the array describing the fields in the custom post type from a $_POST element and for each field, it queries the
 * database for existing meta value and based on the case, it either updates the meta value with the new information or
 * removes the meta field from the database if the user supplied an empty value
 */
function save_data($post_id) {
    // mambo jambo stuff to actually get the array used
    // might not be pretty, but for the backend is actually decent enough to use, and allows this function to work
    // properly
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

    //goes through all the fields in the array with the meta fields descriptions
    //and operates based on case on the previous meta values
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
