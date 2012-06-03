<?php

define('PRODUCTION',false);
define('WITH_ICS',true);
define('DEBUG',false);

// @TODO don't forget to update path of calendar files generated
define('CALENDARPATH','wp-content/themes/theme/calendar/');

if (DEBUG) {
	$wpdb->show_errors();
	define(‘WP_DEBUG’, true);
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
} else {
	define(‘WP_DEBUG’, false);
	error_reporting(E_ALL);
	ini_set('display_errors', '0');
}

//include_once('helpers/commons.php');
//include_once('helpers/ajax-handling.php');
//include_once('helpers/ics-generator.php');
//include_once('helpers/connectionsManager.php');

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
			'primary' => __( 'Primary Navigation', 'theme-name' ),
		) );

		// images sizes
		// example:
		//set_post_thumbnail_size( 140, 90, true );

		// setup image sizes here
		// example:
		// add_image_size('banner-size', 940, 450, true);

		// create connection between two post types
		// example:
		// createConnection('event','partners');
	}
endif;

// rss feeds
function new_feed_request($qv) {
	if ( isset($qv['feed']) && !isset($qv['post_type'])){
		$qv['post_type'] = array('event','portfolio');
	}
	return $qv;
}
// uncomment for rss
// add_filter('request', 'new_feed_request');

// Custom WordPress Login Logo
function login_css() {
	wp_enqueue_style( 'login_css', get_template_directory_uri() . '/css/login.css' );
}
// uncomment for custom login css
// add_action('login_head', 'login_css');

/**
 * adds the javascript/jquery scripts to the website in the wordpress way
 */
function enqueue_scripts_method() {
	wp_register_script('jquery_new',
		get_template_directory_uri() . '/js/jquery.js',
		'1.0' );
	wp_enqueue_script('jquery_new');

	// example
	/*
	wp_register_script('maps_script',
		'http://maps.google.com/maps/api/js?sensor=false',
		'1.0' );
	wp_enqueue_script('maps_script');
	 */
}
// uncomment for custom scripts on page
// add_action('wp_enqueue_scripts', 'enqueue_scripts_method');

///
// set the default excerpt length
//
function excerpt_length( $length ) {
	return 200;
}
add_filter( 'excerpt_length', 'excerpt_lenght' );

// CUSTOM POST TYPES SECTION

/**
 *
 */
add_action('init', 'post_type_portfolio');
function post_type_portfolio(){
	$labels =array(
		'name' => _x('Entry','post type general name'),
		'singular_name' => _x('Entry','post type singular name'),
		'add_new' => _x('Add new','portfolio item'),
		'add_new_item' => __('Add new entry'),
		'edit_item' => __('Edit entry'),
		'new_item' => __('New entry'),
		'view_item' => __('View entry'),
		'search_items' => __('Search entry'),
		'not_found' => __('Nothing was found'),
		'not_found_in_trash' => __('Nothing was found in trash'),
		'parent_item_colon' => ''
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => true,
		'menu_icon' => '',
		'rewrite' => array(
			'slug' => 'portfolio'
		),
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => null,
		'supports' => array('title','excerpt','editor','thumbnail')
	);

	register_post_type('portfolio',$args);
}
include_once('meta/meta_actions.php');

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
		if ($field['type'] === 'connection') {
			// get connections here from table
		} else {
			// get current post meta data
			$meta = get_post_meta($post->ID, $field['id'], true);
		}

		if(!isset($field['hide']) || !$field['hide']){
			echo '<tr>',
				'<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
				'<td>';
		}
		switch ($field['type']) {

		case 'connection':
			displayConnectionWidget($post,$field);
			break;
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
		case 'checkbox':
			$to_echo = '';
			if($meta == 1){
				$to_echo = 'CHECKED';
			}
			echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"value="', $meta ? $meta : $field['std'], '" ', $to_echo,'/>';
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
	// verify nonce
	if (isset($_POST['meta_box_nonce']) && !wp_verify_nonce($_POST['meta_box_nonce'], basename(__FILE__))) {
		return $post_id;
	}

	// check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}

	// check permissions
	if (isset($_POST['post_type']) && $_POST['post_type'] == 'page') {
		if (!current_user_can('edit_page', $post_id)) {
			return $post_id;
		}
	} elseif (!current_user_can('edit_post', $post_id)) {
		return $post_id;
	}

	if( isset($_POST['meta_box'])){
		$metabox = $_POST['meta_box'];
		global $$metabox;

		//goes through all the fields in the array with the meta fields descriptions
		//and operates based on case on the previous meta values
		foreach (${$metabox}['fields'] as $field) {
			if ($field['type'] === 'connection') {
				updateConnections($field['connection'],$post_id,$_POST[$field['connection']]);
			} else {
				$old = get_post_meta($post_id, $field['id'], true);
				$new = $_POST[$field['id']];

				if ($new && $new != $old) {
					update_post_meta($post_id, $field['id'], $new);
				} elseif ('' == $new && $old) {
					delete_post_meta($post_id, $field['id'], $old);
				}
			}
		}
	}

	if(WITH_ICS){
		$postType = get_post_type();
		if ($postType === "event" || $postType === "portfolio") {
			remakeICSCache($postType,$post_id);
		}
	}
}
add_action('save_post', 'save_data');

// Clean interface for users

function remove_dashboard_widgets(){
	global$wp_meta_boxes;
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
}

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

function remove_submenus() {
	global $submenu;
	unset($submenu['index.php'][10]); // Removes 'Updates'.
	unset($submenu['themes.php'][5]); // Removes 'Themes'.
	unset($submenu['options-general.php'][15]); // Removes 'Writing'.
	unset($submenu['options-general.php'][25]); // Removes 'Discussion'.
	unset($submenu['edit.php'][16]); // Removes 'Tags'.
}

if(PRODUCTION){
	add_action('wp_dashboard_setup', 'remove_dashboard_widgets');
	add_action('admin_menu', 'remove_menu_items');
	add_action('admin_menu', 'remove_submenus');
}
