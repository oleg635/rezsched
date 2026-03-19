<?php
/*
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
*/
//echo 'wph_save_post_meta_ajax.php';
//return;

ob_start();

$message = '';
$created_waiver = 0;

function ensure_unique_post_name($post_id) {
    // Get the current post
    $post = get_post($post_id);
    if (!$post) {
        return; // Invalid post ID
    }
	
    // Get the post_name (slug) of the current post
    $original_post_name = $post->post_name;
	
    // Query for posts with the same post_name
    $args = [
        'post_type' => $post->post_type,
        'post_status' => 'publish',
        'name' => $original_post_name,
        'posts_per_page' => -1,
        'orderby' => 'ID',
        'order' => 'ASC',
    ];
    $duplicate_posts = get_posts($args);
	
	echo '$duplicate_posts ';
	var_dump($duplicate_posts);
	
    // If there are no duplicates, exit
    if (count($duplicate_posts) <= 1) {
        return;
    }
	
	echo 'count($duplicate_posts) ' . count($duplicate_posts);
	
    // Iterate over duplicates and assign unique post_name
    $counter = 1;
    foreach ($duplicate_posts as $duplicate_post) {
        // Skip the first one, as it is already correct
        if ($counter === 1 && $duplicate_post->ID === $post_id) {
            $counter++;
            continue;
        }
		
        // Generate a unique post_name
        $new_post_name = $original_post_name . '-' . $counter;
		
        // Check if this new post_name is unique
        while (post_name_exists($new_post_name, $duplicate_post->ID)) {
            $counter++;
            $new_post_name = $original_post_name . '-' . $counter;
        }
		
        // Update the duplicate post's post_name
        wp_update_post([
            'ID' => $duplicate_post->ID,
            'post_name' => $new_post_name,
        ]);
		
        $counter++;
    }
}

function post_name_exists($post_name, $except) {
    global $wpdb;
    $query = $wpdb->prepare("SELECT post_name FROM $wpdb->posts WHERE ID <> " . $except . " AND post_name = %s", $post_name);
    return $wpdb->get_var($query) ? true : false;
}


function delete_duplicate_events($parent_post_id, $parent_date_start, $parent_time_start)
{
	$args = array(
	    'post_type'      => 'etn',
	    'post_parent'    => $parent_post_id,
	    'meta_query'     => array(
	        'relation' => 'AND',
	        array(
	            'key'     => 'etn_start_date',
	            'value'   => $parent_date_start,
	            'compare' => '='
	        ),
	        array(
	            'key'     => 'etn_start_time',
	            'value'   => $parent_time_start,
	            'compare' => '='
	        )
	    ),
	    'posts_per_page' => -1,
	);
	
	$posts = get_posts($args);
	$count = count($posts);
	if (!empty($posts)) {
	    foreach ($posts as $post) {
			if($post->ID !== $parent_post_id)
				wp_delete_post($post->ID, FALSE); // 'true' to force delete without moving to trash
	    }
	}
	
	return $count;
}

function get_parent_and_children_posts($post_id, $event_bulk_edit)
{
	$posts = array();
    $post = get_post($post_id);
	
    if ($post->post_parent != 0) {
        $parent_post = get_post($post->post_parent);
		
		$parent_date_start = get_post_meta($parent_post->ID, 'etn_start_date', true);
		$parent_time_start = get_post_meta($parent_post->ID, 'etn_start_time', true);
		
		//$count = delete_duplicate_events($parent_post_id, $parent_date_start, $parent_time_start);
		//vd($count, 'delete_duplicate_events()');
		
        $children_posts = get_posts(array(
            'post_type'   => $post->post_type,
            'post_parent' => $parent_post->ID,
            'numberposts' => -1,
            'post_status' => 'any',
			'orderby'        => 'ID',
			'order'          => 'ASC',
        ));
		
		if($event_bulk_edit == 2)//All events
		{
			$posts[] = $post->post_parent;
			foreach($children_posts as $child)
			{
				$posts[] = $child->ID;
			}
		}
		elseif($event_bulk_edit == 1)//This and following events
		{
			foreach($children_posts as $child)
			{
				if($child->ID >= $post_id)
					$posts[] = $child->ID;
			}
		}
    }
	else
	{
		$parent_date_start = get_post_meta($post_id, 'etn_start_date', true);
		$parent_time_start = get_post_meta($post_id, 'etn_start_time', true);
		
		//$count = delete_duplicate_events($post_id, $parent_date_start, $parent_time_start);
		//vd($count, 'delete_duplicate_events()');
		
        $children_posts = get_posts(array(
            'post_type'   => $post->post_type,
            'post_parent' => $post->ID,
            'numberposts' => -1,
            'post_status' => 'any',
			'orderby'        => 'ID',
			'order'          => 'ASC',
        ));
		
		$posts[] = $post->ID;
		
		foreach($children_posts as $child)
			$posts[] = $child->ID;
    }
	
	return $posts;
}

require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
$success = TRUE;

$log_filename = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph_delete_events_ajax.txt';
	wph_log_data_global($log_filename,
		array(
			'$_REQUEST' => $_REQUEST,
		), TRUE, FALSE
	);

if(!empty($_POST['event_id']))
{
	/*
	wph_log_data_global($log_filename,
		array(
			'$_REQUEST' => $_REQUEST,
		), TRUE, FALSE
	);
	*/
    check_ajax_referer('edit-post-nonce', 'security');
/*
	wph_log_data_global($log_filename,
		array(
			'AFTER check_ajax_referer' => TRUE,
		), TRUE, FALSE
	);
*/
	echo ' event_bulk_edit ' . $_REQUEST['event_bulk_edit'] . ' ';
	$post = get_post($_POST['event_id']);
	if($post)
	{
		$event_bulk_edit = isset($_REQUEST['event_bulk_edit']) ? $_REQUEST['event_bulk_edit'] : 2;
		vd($event_bulk_edit, '$event_bulk_edit');
		if($event_bulk_edit)
			$bulk_posts = get_parent_and_children_posts($_POST['event_id'], $event_bulk_edit);
		else
			$bulk_posts = [$_POST['event_id']];
		vd($bulk_posts, '$bulk_posts');
		
		if($bulk_posts)
		{
			foreach($bulk_posts as $post_id)
			{
				echo '$post_id ' . $post_id . ' ';
				wp_delete_post($post_id, FALSE);
			}
		}
	}
	
	$success = TRUE;
}
	
	wph_log_data_global($log_filename,
		array(
			'$bulk_posts' => $bulk_posts,
			'$bulk_posts' => $bulk_posts,
			'$post' => $post,
		), TRUE, FALSE
	);
/*
wph_log_data_global($log_filename,
	array(
		'AFTER ALL' => TRUE,
	), TRUE, FALSE
);
*/
$CONTENT = ob_get_contents();
$response = array('success' => $success, '$message' => $message, '$_REQUEST' => $_REQUEST, 'CONTENT' => $CONTENT);
ob_end_clean();
/*
wph_log_data_global($log_filename,
	array(
		'AFTER ob_get_contents' => TRUE,
	), TRUE, FALSE
);
*/
echo json_encode($response);
?>