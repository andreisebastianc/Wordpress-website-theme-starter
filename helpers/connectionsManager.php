<?php
// @TODO1 Make class
// @TODO2 maybe make it a singleton
// @TODO3 detail functionality


/**
 * @todo fix display of multiple elements on the same line
 *
 */
function displayConnectionWidget($post,$field)
{
	$toRender = null;
	$toEcho = '';
	$connection = getConnectionName($post->post_type, $field['to_connect']);
	$existingConnections = getConnections($post->post_type, $field['to_connect'], $post->ID);

	switch ($field['element']) {
	case 'checkbox':
		$toRender = getTitleAndIdOfPostType($field['to_connect']);

		for ($i = 0; $i < count($toRender); $i++) {
			$toEcho = '';
			if(in_array($toRender[$i][0],$existingConnections)){
				$toEcho = 'CHECKED';
			}
			echo '<input type="checkbox" name="', $connection, '[]" id="', $toRender[$i][0], '"value="', $toRender[$i][0], '" ', $toEcho,'/>';
			echo '<label for="',$toRender[$i][0],'">',$toRender[$i][1],'</label>';
		}
		break;

	default:
		echo 'Connection is not set';
		break;
	}
}

/**
 *
 */
function getConnectionName($postType,$connectedPostType){
	return 'wp_'.$postType.'_has_'.$connectedPostType;
}

/**
 *
 * @param $connection		the connection
 * @param $post_id			the id of the post that has connections
 * @param $array_of_ids		the ids for which the connection is made
 */
function updateConnections($connection, $post_id, $array_of_ids){
	global $wpdb;

	// remove all previous connections
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM $connection
			WHERE with_connection_id = %s"
			, $post_id
		)
	);

	$connections = array();
	for ($i = 0; $i < count($array_of_ids); $i++) {
		$connections[] = array(
			'with_connection_id' => $post_id,
			'connection_id' => $array_of_ids[$i]
		);
	}

	// add new connections
	foreach ( $connections as $con ){
		$wpdb->insert( $connection, $con );
	}
}

/**
 *
 *
 *
 */
function getConnections($postType, $connectedPostType, $post_id){
	global $wpdb;
	$toReturn = array();

	$connection = getConnectionName($postType, $connectedPostType);

	$connections = $wpdb->get_results(
		"
		SELECT connection_id
		FROM $connection
		WHERE with_connection_id = '$post_id'
		"
	);

	foreach ($connections as $connection) {
		$toReturn[] = $connection->connection_id;
	}

	return $toReturn;
}

/**
 * @todo REFACTOR
 * @todo comments
 */
function createConnection($postType, $connectToPostType){
	global $wpdb;
	$postType = 'event';
	$connectToPostType = 'partners';
	$search_table = $wpdb->prefix . $postType."_has_".$connectToPostType;
	if($wpdb->get_var("show tables like '$search_table'") !== $search_table)
	{
		$sql = "CREATE TABLE ". $search_table . " (
			id mediumint(12) NOT NULL AUTO_INCREMENT,
			with_connection_id mediumint(9) NOT NULL,
			connection_id mediumint(9) NOT NULL,
			UNIQUE KEY id (id));";
		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		dbDelta($sql);
	}
	if (!isset($wpdb->stats))
	{
		$wpdb->stats = $search_table;
		//add the shortcut so you can use $wpdb->stats
		$wpdb->tables[] = str_replace($wpdb->prefix, '', $search_table);
	}
}

//add_action('init', 'createConnection');

