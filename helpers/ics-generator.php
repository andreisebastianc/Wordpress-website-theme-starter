<?php

/**
 * @TODO transform working code into elegant class
 * @note currently makes assumptions
 * requires post type meta: start_date and end_date in order to build the ics file
 * @note currently doesn't support mass regeneration of ics files
 *
 * HOW TO USE:
 * - add start and end date meta for the post type you want to generate ics for
 * - hook appropriate method for ics generation
 * - check file permissions
 */

// saves all the calendar files at this location
define('CALENDARPATH','wp-content/themes/theme/calendar/');
// will be used before as prefix for filenames
define('CALENDARFILEPREFIX','prefix');

/**
 * @TODO after refactoring this method could be used also as a full cache
 * regeneration method for all posts of a given post type
 *
 * generates an ICS file for a post or for multiple post type entries
 *
 * @param $post_type	the post type, eg. "event"
 * @param $post_id		optional - used for generating an ics with for a single entry
 */
function getICSForPost($post_type, $post_id = null){

	// vcalendar header
	$ics_contents  = "BEGIN:VCALENDAR\n";
	$ics_contents .= "VERSION:2.0\n";
	$ics_contents .= "PRODID:-//andreicimpean/".CALENDARFILEPREFIX."/ v2.0//EN\n";

	$args = array(
		'post_type' => $post_type,
		'post_status' => 'publish'
	);

	if ($post_id != null) {
		$args['posts_per_page'] = 1;
		$args['page_id'] = $post_id;
	}
	else {
		$filename = '../'.CALENDARPATH.CALENDARFILEPREFIX.'-'.$post_type.'.ics';
	}

	$query = null;
	$query = new WP_Query($args);
	if($query->have_posts()) {
		while($query->have_posts()) : $query->the_post();

		$name = get_the_title();
		$description = get_the_excerpt();

		// filename for single entry
		if ($post_id != null) {
			$filename = '../'.CALENDARPATH.CALENDARFILEPREFIX.'-'.get_the_ID().'.ics';
		}

		$location = 'Change here';
		$location = str_replace(",", "\\,",$location);

		$start_date = get_post_meta(get_the_ID(),'start_date',true);
		$start_date = str_replace("-", "",$start_date);

		$end_date = get_post_meta(get_the_ID(),'end_date',true);
		if($end_date === ''){
			$end_date = $start_date;
		}
		else{
			$end_date = str_replace("-", "",$end_date);
		}

		$start_time = get_post_meta(get_the_ID(),'start_time',true);
		$end_time = get_post_meta(get_the_ID(),'end_time',true);
		$start_time = str_replace(":", "", $start_time);
		$end_time = str_replace(":", "", $end_time);

		$ics_contents .= "BEGIN:VEVENT\n";
		$ics_contents .= "UID:office@publicexpert.com\n";
		$ics_contents .= "DTSTAMP:"     . date('Ymd') . "T". date('His') . "Z\n";
		$ics_contents .= "ORGANIZER;CN=Change here:MAILTO:Change here email address\n";
		$ics_contents .= "DTSTART;TZID=Europe/Bucharest:"     . $start_date . "T". $start_time . "00Z\n";
		$ics_contents .= "DTEND;TZID=Europe/Bucharest:"       . $start_date . "T". $end_time . "00Z\n";
		$ics_contents .= "LOCATION:"    . $location . "\n";
		$ics_contents .= "DESCRIPTION:" . $description . "\n";
		$ics_contents .= "SUMMARY:"     . $name . "\n";
		$ics_contents .= "END:VEVENT\n";
		$ics_contents .= "END:VCALENDAR\n";

		endwhile;
	}

	wp_reset_query();

	// writes an ics file with the information
	// the resulting file is to be linked in the theme
	if(!file_exists($filename)){
		if (!$handle = fopen($filename,'x')) {
			// error handling
			return 'error [1] with ics manager';
			exit();
		}
		if (fwrite($handle, $ics_contents) === FALSE) {
			// error handling
			return 'error [2] with ics manager';
			exit();
		}
		fclose($handle);
	}

	return true;
}

/**
 * @TODO better clearing method
 * generates an ics file for a post
 * this should be called whenever a post who needs an ics file generated
 * is saved
 *
 * @param $post_type the post type for what to generate a big ics file
 * @param $post_id 	 the post id representing the entry that will get an ics file
 */
function remakeICSCache($post_type, $post_id){

	// remove file
	$filename = '../'.CALENDARPATH.CALENDARFILEPREFIX.'-'.$post_id.'.ics';
	// removes cached file
	unlink($filename);

	// @TODO this should be improved
	$filename = '../'.CALENDARPATH.CALENDARFILEPREFIX.'-'.$post_type.'.ics';
	unlink($filename);

	// regenerate ics file for post type
	getICSForPost($post_type);
	// regenerate ics file for post with given id and return result
	return getICSForPost($post_type, $post_id);
}
?>
