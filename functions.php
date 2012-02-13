<?php

//define(‘WP_DEBUG’, true);

// important include to avoid clutter
include_once('helpers/commons.php');

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

        // images sizes
        set_post_thumbnail_size( 140, 90, true );

        add_image_size('icon-size', 45, 40, true);
        add_image_size('social-size', 40, 40, false);
        add_image_size('partner-size', 120, 120, false);
        add_image_size('banner-size', 950, 348, true);
        add_image_size('content-size', 830, 350, true);
    }
endif;

/**
 * adds the javascript/jquery scripts to the website in the wordpress way
 */
function enqueue_scripts_method() {
    //wp_enqueue_script("jquery");
    wp_register_script('jquery_new',
        get_templat9e_directory_uri() . '/js/jquery.js',
        '1.0' );
    wp_enqueue_script('jquery_new');

    wp_register_script('helper_scripts',
        get_template_directory_uri() . '/js/helpers.js',
        '1.0' );
    wp_enqueue_script('helper_scripts');

    wp_register_script('active_scripts',
        get_template_directory_uri() . '/js/scripts.js',
        '1.0', 1 );
    wp_enqueue_script('active_scripts');
}
add_action('wp_enqueue_scripts', 'enqueue_scripts_method');

///
// set the default excerpt length
//
function excerpt_length( $length ) {
    return 40;
}
add_filter( 'excerpt_length', 'excerpt_lenght' );


///
// custom post type for SOCIAL MEDIA LINKS
///
add_action('init', 'post_type_smc_socialmedia');
function post_type_smc_socialmedia()
{
    $labels = array(
        'name' => _x('Social Media', 'post type general name'),
        'singular_name' => _x('Profil', 'post type singular name'),
        'add_new' => _x('Adaugă nou', 'adauga_socialmedia'),
        'add_new_item' => __('Adaugă profil social media')
    );

    $args = array(
        'labels' => $labels,
        'public' => false,
        'publicly_queryable' => false,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        'hierarchical' => true,
        'menu_position' => null,
        'supports' => array('title','thumbnail'));

    register_post_type('socialmedia',$args);
}
include_once('meta/meta_social.php');

/***
 * used by custom post types build with the supplied model to construct the meta fields and box, based on the fields
 * described in the array supplied
 *
 * it includes elements ready for html5
 *
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

        if(!$field['hide']){
            echo '<tr>',
                '<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
                '<td>';
        }
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
        echo  '<td>',
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

add_action('save_post', 'save_data');

//cleanup

function remove_dashboard_widgets(){
  global$wp_meta_boxes;
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
  unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
  unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
}

add_action('wp_dashboard_setup', 'remove_dashboard_widgets');

function remove_menu_items() {
  global $menu;
  $restricted = array(__('Links'), __('Comments'), __('Settings'),
    __('Plugins'), __('Tools'), __('Users'));
  end ($menu);
  while (prev($menu)){
      $value = explode(' ',$menu[key($menu)][0]);
      if(in_array($value[0] != NULL?$value[0]:"" , $restricted)){
                unset($menu[key($menu)]);}
      }
  }

add_action('admin_menu', 'remove_menu_items');

function remove_submenus() {
  global $submenu;
  unset($submenu['index.php'][10]); // Removes 'Updates'.
  unset($submenu['themes.php'][5]); // Removes 'Themes'.
  unset($submenu['options-general.php'][15]); // Removes 'Writing'.
  unset($submenu['options-general.php'][25]); // Removes 'Discussion'.
  unset($submenu['edit.php'][16]); // Removes 'Tags'.
}

add_action('admin_menu', 'remove_submenus');
