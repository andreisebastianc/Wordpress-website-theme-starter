<?php

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

?>
