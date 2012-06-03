<?php

/**
 * generates an ICS file for a post or for multiple post type entries
 *
 * @param @post_type	the post type, eg. "event"
 * @param @post_id		optional - used for generating an ics with for a single entry
 */
function getICSForPost($post_type, $post_id = null){
	$ics_contents  = "BEGIN:VCALENDAR\n";
	$ics_contents .= "VERSION:2.0\n";
	$ics_contents .= "PRODID:-//andreicimpean/publicexpert/ v2.0//EN\n";

	$args = array(
		'post_type' => $post_type,
		'post_status' => 'publish'
	);

	if ($post_id != null) {
		$args['posts_per_page'] = 1;
		$args['page_id'] = $post_id;
	}
	else {
		$filename = '../wp-content/themes/publex/calendar/publicexpert-'.$post_type.'.ics';
	}

	$query = null;
	$query = new WP_Query($args);
	if($query->have_posts()) {
		while($query->have_posts()) : $query->the_post();

		$name = get_the_title();
		$description = get_the_excerpt();

		// filename for single entry
		if ($post_id != null) {
			$filename = '../wp-content/themes/publex/calendar/publicexpert-'.get_the_ID().'.ics';
		}

		$location = 'Sibiu, Romania';
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
		$ics_contents .= "ORGANIZER;CN=Public Expert:MAILTO:office@publicexpert.ro\n";
		$ics_contents .= "DTSTART;TZID=Europe/Bucharest:"     . $start_date . "T". $start_time . "00Z\n";
		$ics_contents .= "DTEND;TZID=Europe/Bucharest:"       . $start_date . "T". $end_time . "00Z\n";
		$ics_contents .= "LOCATION:"    . $location . "\n";
		$ics_contents .= "DESCRIPTION:" . $description . "\n";
		$ics_contents .= "SUMMARY:"     . $name . "\n";
		$ics_contents .= "END:VEVENT\n";

endwhile;
	}

	wp_reset_query();

	$ics_contents .= "END:VCALENDAR\n";


	if(!file_exists($filename)){
		if (!$handle = fopen($filename,'x')) {
			// error handling
			return 'Suna Andrei! Problema mare cu applicaţia numărul 1';
			exit();
		}
		if (fwrite($handle, $ics_contents) === FALSE) {
			// error handling
			return 'Suna Andrei! Problema mare cu applicaţia numărul 2';
			exit();
		}
		fclose($handle);
	}

	return true;
}

/**
 * generates an ics file for a post
 *
 * @TODO finish comments
 * @TODO add prefix support
 */
function remakeICSCache($post_type, $post_id){
	// remove file
	$filename = '../wp-content/themes/publex/calendar/'.$post_id.'.ics';
	unlink($filename);
	getICSForPost($post_type);
	return getICSForPost($post_type, $post_id);
}
?>
