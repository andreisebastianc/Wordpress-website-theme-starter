<?php

//@todo comments here
function curPageURL() {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

/**
 * simple breadcrumb generator for particular case of curl
 */
function the_breadcrumb() {
	global $post;
	echo '<div class="breadcrumbs">';
	if (!is_home()) {
		echo 'You are here:&nbsp;&nbsp;';
		echo '<a href="';
		echo get_option('home');
		echo '">Home</a> » ';
		$post_type = get_post_type_object($post->post_type);
		if($post->post_type){
			echo '<a href="'.get_bloginfo('url').'/'.$post_type->rewrite['slug'].'">';
			echo $post_type->labels->name;
			echo '</a>';
		}
		if (is_category() || is_single()) {
			the_category();
			if (is_single()) {
				echo " » ";
				the_title();
			}
		} elseif (is_page()) {
			echo the_title();
		}
	}
	echo '</div>';
}

/**
 * simple back button
 */
function the_backbutton($string_to_show){
	global $post;
	// echo '<a class="read-more alignright" href="'.get_bloginfo('url').'/'.$post->post_type.'">'.$string_to_show.'</a>';
	echo '<a class="read-more alignright" href="'.get_bloginfo('url').'/portofoliu">'.$string_to_show.'</a>';
}

/**
 * simple query builder for additional texts in template files
 */
function getAdditionalData($identifier,$post_type){
	$args = array(
		'meta_key' => 'page',
		'meta_value' => $identifier,
		'post_type' => $post_type,
		'post_status' => 'publish',
		'posts_per_page'=> 1
	);
	$additional_data_query = new WP_Query($args);
	return $additional_data_query;
}


/**
 * @TODO refactor for general fields
 *
 * gets all the posts from a post types where the meta value is a date compared to NOW
 *
 * @param $compare	the comparation mark eg '<' or '>'
 */
function getPostByMetaDateCompare($postType, $meta_key, $meta_value, $compare){
	$args = array(
		'post_type' => $postType,
		'meta_query'=> array(
			array(
				'key' => $meta_key,
				'compare' => $compare,
				'value' => $meta_value,
				'type' => 'DATE',
			)),
		'meta_key' => 'start_date',
		'orderby' => 'meta_value',
		'order' => 'DESC'
	);
	$query = null;
	$query = new WP_Query($args);
	return $query;
}

/**
 *
 */
function getTitleAndIdOfPostType($postType){
	$args = array(
		'post_type' => $postType,
		'post_status' => 'publish',
		'caller_get_posts' => 1
	);
	$query = null;
	$query = new WP_Query($args);
	$toReturn = array();
	while($query->have_posts()) {
		$query->the_post();
		array_push($toReturn, array(get_the_id(), get_the_title()));
	}
	return $toReturn;
}

function getTitleLinked($query){
}
/**
 *
 */
function getPostsWithSuppliedIds($postType, $arrayOfIds){
	$args = array(
		'post_type' => $postType,
		'post_status' => 'publish',
		'post__in' => $arrayOfIds,
		'caller_get_posts' => 1
	);
	$query = null;
	$query = new WP_Query($args);
	return $query;
}

add_shortcode('gallery','gallery_bypass');
function gallery_bypass($attr){
	global $post, $wp_locale;

	$output = gallery_shortcode($attr);

	//remove link
	if($attr['link'] == "none") {
		$output = preg_replace(array('/<a[^>]*>/', '/<\/a>/'), '', $output);
	}

	return $output;
}

//@todo fix this
// remove default style for gallery
//add_filter('gallery_style',
//	create_function(
//		'$simple-gallery',
//		'return preg_replace("#<style type=\'text/css\'>(.*?)</style>#s", "", $css);'
//	)
//);
