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
date_default_timezone_set('America/New_York');

function delete_bi_weekly( $parent_post_id ) {
	
	$posts_to_delete = array();
    // Fetch the parent post's start date
    $start_date = get_post_meta( $parent_post_id, 'etn_start_date', true );
    $end_date = get_post_meta( $parent_post_id, 'etn_end_date', true );
	
    // Find the first Sunday after the start date (by adding days to get to the next Sunday)
    $day_of_week = date( 'w', strtotime( $start_date ) ); // 0 (for Sunday) through 6 (for Saturday)
    $days_to_sunday = (7 - $day_of_week) % 7;
    $first_sunday_date = date( 'Y-m-d', strtotime( "+$days_to_sunday days", strtotime( $start_date ) ) );
	
    // Retrieve all child posts
    $child_posts = get_posts( array(
        'post_type'   => 'etn',
        'post_parent' => $parent_post_id,
        'post_status' => 'publish',
        'posts_per_page' => -1, // Get all child posts
        'fields' => 'ids',
		'orderby'    => 'ID',
		'sort_order' => 'DESC',
    ));
	$i = 0;
	$non_legal_week_start = $first_sunday_date;
	
	//vd($non_legal_week_start, '$non_legal_week_start');
	//vd($end_date, '$end_date');
	//return FALSE;
    // Iterate through non-legal week timeframes and delete posts
    while($non_legal_week_start < $end_date)
	{
        // Define the non-legal week timeframe (Sunday to Saturday)
        $non_legal_week_start = $first_sunday_date;
        $non_legal_week_end = date( 'Y-m-d', strtotime( '+6 days', strtotime( $non_legal_week_start ) ) );
		$i++;
		//echo '$i ' . $i  . ' $non_legal_week_start <strong>' . $non_legal_week_start  . '</strong> $non_legal_week_end <strong>' . $non_legal_week_end . '</strong><br>';
        // Iterate over child posts and delete those within the non-legal week
        foreach ( $child_posts as $key => $child_post_id ) {
            $child_start_date = get_post_meta( $child_post_id, 'etn_start_date', true );
			
			//echo '<br>' . $child_post_id . ' <strong>' . $child_start_date . '</strong><br>';
			
            if ( $child_start_date >= $non_legal_week_start && $child_start_date <= $non_legal_week_end ) {
				$posts_to_delete[] = $child_post_id;
                wp_delete_post( $child_post_id, true );
				unset( $child_posts[ $key ] );
            }
        }
		
        // Move to the next non-legal week (add two weeks to the Sunday date)
        $first_sunday_date = date( 'Y-m-d', strtotime( '+2 weeks', strtotime( $first_sunday_date ) ) );
		
		if($i > 100)
		{
			echo '<br>endless loop!<br>';
			break;
		}
    }
	
	//vd($child_posts, '$child_posts');
	/*
	echo '<strong>$child_posts</strong><br>';
	foreach($child_posts as $child_post)
	{
	    $start_date = get_post_meta( $child_post, 'etn_start_date', true );
	    $end_date = get_post_meta( $child_post, 'etn_end_date', true );
		echo $child_post . ' <strong>' . $start_date . '</strong> ' . ' <strong>' . $end_date . '</strong><br>';
	}
	
	echo '<strong>$posts_to_delete</strong><br>';
	foreach($posts_to_delete as $child_post)
	{
	    $start_date = get_post_meta( $child_post, 'etn_start_date', true );
	    $end_date = get_post_meta( $child_post, 'etn_end_date', true );
		echo $child_post . ' <strong>' . $start_date . '</strong> ' . ' <strong>' . $end_date . '</strong><br>';
	}
	*/
	return $posts_to_delete;
}

function ensure_unique_post_name($post_id) {
    $post = get_post($post_id);
    if (!$post) {
        return; // Invalid post ID
    }
	
    $original_post_name = $post->post_name;
	
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

$log_filename = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph_save_post_meta_ajax.txt';

if(!empty($_POST['post_id']))
{
	remove_product_from_all_carts($_POST['post_id']);
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
	$post = get_post($_POST['post_id']);
	if($post)
	{
		if(isset($_REQUEST['bodyData']['etn_ticket_variations'][0]['etn_avaiilable_tickets']))
		{
			$etn_total_avaiilable_tickets = $_REQUEST['bodyData']['etn_ticket_variations'][0]['etn_avaiilable_tickets'];
			$etn_ticket_variations = $_REQUEST['bodyData']['etn_ticket_variations'];
			$etn_ticket_price = $_REQUEST['bodyData']['etn_ticket_variations'][0]['etn_ticket_price'];
		}
		
		if(!empty($_REQUEST['bodyData']['etn_event_recurrence']['recurrence_weekly_day']))
			$recurrence_weekly_day = $_REQUEST['bodyData']['etn_event_recurrence']['recurrence_weekly_day'];
		else
			$recurrence_weekly_day = array();
		
		if(!empty($_REQUEST['bodyData']['etn_event_recurrence']['recurrence_span']))
			$recurrence_span = $_REQUEST['bodyData']['etn_event_recurrence']['recurrence_span'];
		else
			$recurrence_span = 1;
		vd($recurrence_span, 'recurrence_span');
		if(!empty($recurrence_span) && $recurrence_span == 2)
		{
			$posts_to_delete = delete_bi_weekly($_POST['post_id'], $recurrence_weekly_day);
		}
		
		$parent_date_start = get_post_meta($_POST['post_id'], 'etn_start_date', true);
		$parent_time_start = get_post_meta($_POST['post_id'], 'etn_start_time', true);
		
		if (!empty($parent_date_start) && !empty($parent_time_start)) {
			$datetime_str = $parent_date_start . ' ' . $parent_time_start;
			$timestamp = strtotime($datetime_str);
			update_post_meta($_POST['post_id'], 'etn_start_timestamp', $timestamp);
		}
		
		vd($_REQUEST['bodyData']['post_content'], '$_REQUEST[\'bodyData\'][\'post_content\']');
		$post_content = !empty($_REQUEST['bodyData']['post_content']) ? $_REQUEST['bodyData']['post_content'] : $post->post_content;
		
		//ensure_unique_post_name($_POST['post_id']);
		$updated_post = array(
		    'ID'           => $_POST['post_id'],
		    'post_author'   => $post->post_author,
		    'post_title'   => $post->post_title,
		    'post_content' => $post_content,
		);
		
		if(!empty($_POST['post_status']))
		{
		    $updated_post['post_status'] = $_POST['post_status'];
			echo 'post_status ' . $_POST['post_status'] . ' ';;
		}
		
		if(!$post->post_parent)
		{
			$child_posts = get_parent_and_children_posts($_POST['post_id'], 2);
			if($child_posts)
			{
				//$count = delete_duplicate_events($_POST['post_id'], $parent_date_start, $parent_time_start);
				
				$author_post = array(
				    'post_author'	=> $post->post_author,
				);
				
				foreach($child_posts as $post_id)
				{
					$author_post['ID'] = $post_id;
					wp_update_post($author_post);
				}
			}
		}
		
		$ticket_variations = get_post_meta($_POST['post_id'], 'etn_ticket_variations', true);
		if($ticket_variations)
		{
			$ticket_variations = maybe_unserialize($ticket_variations);
			$ticket_price = isset($etn_ticket_price) ? $etn_ticket_price : $ticket_variations[0]['etn_ticket_price'];
			$total_capacity = isset($etn_total_avaiilable_tickets) ? $etn_total_avaiilable_tickets : $ticket_variations[0]['etn_avaiilable_tickets'];
			
			echo ' $ticket_price ' . $ticket_price . ' ';
			echo ' $total_capacity ' . $total_capacity . ' ';
		}
		
		$event_bulk_edit = isset($_REQUEST['event_bulk_edit']) ? $_REQUEST['event_bulk_edit'] : 2;
		vd($event_bulk_edit, '$event_bulk_edit');
		if($event_bulk_edit)
			$bulk_posts = get_parent_and_children_posts($_POST['post_id'], $event_bulk_edit);
		else
			$bulk_posts = [$_POST['post_id']];
		vd($bulk_posts, '$bulk_posts');
		
		if($bulk_posts)
		{
			foreach($bulk_posts as $post_id)
			{
				if(isset($_REQUEST['featured_image']))
				{
					if($_REQUEST['featured_image'])
						set_post_thumbnail($post_id, $_REQUEST['featured_image']);
					else
						delete_post_thumbnail($post_id);
				}
				
				if(!empty($_REQUEST['event_type']))
				{
					$term = get_term_by('slug', $_REQUEST['event_type'], 'etn_category');
					
					if($term)
					{
						wp_set_post_terms($post_id, $term->term_id, 'category');
					}
				}
				
				if(isset($_REQUEST['age_group']))
					update_post_meta($post_id, 'age_group', $_REQUEST['age_group']);
				
				if(isset($_REQUEST['level']))
				{
					//echo ' post_id ' . $post_id;
					//echo ' level ' . $_REQUEST['level'];
					update_post_meta($post_id, 'level', $_REQUEST['level']);
				}
				
				vd($recurrence_weekly_day, '$recurrence_weekly_day');
				
				if($recurrence_weekly_day)
				{
					update_post_meta($post_id, 'recurrence_weekly_day', $recurrence_weekly_day);
				}
				
				if(isset($_REQUEST['waiver']))
				{
					if($_REQUEST['waiver'] == -1)
					{
						if($created_waiver)
						{
							update_post_meta($post_id, 'waiver', $created_waiver);
						}
						else
						{
							$post_data = array(
							);
							
							$post_data['post_status'] = 'publish';
							
							$post_data['post_type'] = 'waiver';
							
							if(isset($_REQUEST['waiver_title']))
								$post_data['post_title'] = sanitize_text_field($_REQUEST['waiver_title']);
							
							if(isset($_REQUEST['waiver_content']))
								$post_data['post_content'] = wp_kses_post($_REQUEST['waiver_content']);
							
							$created_waiver = wp_insert_post($post_data);
							if(!$created_waiver)
							{
								$message = 'Create waiver failed';
							}
							else
							{
								update_post_meta($post_id, 'waiver', $created_waiver);
							}
						}
					}
					else
					{
						update_post_meta($post_id, 'waiver', $_REQUEST['waiver']);
					}
				}
				
				if(isset($_REQUEST['checkbox_public_view']))
					update_post_meta($post_id, 'checkbox_public_view', $_REQUEST['checkbox_public_view']);
				
				if(isset($_REQUEST['tshirt_size']))
					update_post_meta($post_id, 'tshirt_size', $_REQUEST['tshirt_size']);
				
				if(isset($_REQUEST['jersey_size']))
					update_post_meta($post_id, 'jersey_size', $_REQUEST['jersey_size']);
				
				if(isset($_REQUEST['shorts_size']))
					update_post_meta($post_id, 'shorts_size', $_REQUEST['shorts_size']);
				
				if(isset($_REQUEST['pants_size']))
					update_post_meta($post_id, 'pants_size', $_REQUEST['pants_size']);
				
				if(isset($_REQUEST['room_area']))
					update_post_meta($post_id, 'room_area', $_REQUEST['room_area']);
				
				if(isset($_REQUEST['street_address_line_1']))
					update_post_meta($post_id, 'street_address_line_1', $_REQUEST['street_address_line_1']);
				
				if(isset($_REQUEST['street_address_line_2']))
					update_post_meta($post_id, 'street_address_line_2', $_REQUEST['street_address_line_2']);
				
				if(isset($_REQUEST['city']))
					update_post_meta($post_id, 'city', $_REQUEST['city']);
				
				if(isset($_REQUEST['state']))
					update_post_meta($post_id, 'state', $_REQUEST['state']);
				
				if(isset($_REQUEST['zip']))
					update_post_meta($post_id, 'zip', $_REQUEST['zip']);
				
				if(isset($_REQUEST['lesson_price']))
					update_post_meta($post_id, 'lesson_price', $_REQUEST['lesson_price']);
				
				//vd($_REQUEST, '$_REQUEST');
				
				//if(isset($etn_total_avaiilable_tickets))
					//update_post_meta($post_id, 'etn_total_avaiilable_tickets', $etn_total_avaiilable_tickets);
				//$etn_ticket_variations = $_REQUEST['bodyData']['etn_ticket_variations'];
				
				if(isset($ticket_price))
				{
					$etn_ticket_variations = get_post_meta($post_id, 'etn_ticket_variations', true);
					$etn_ticket_variations = maybe_unserialize($etn_ticket_variations);
					
					$etn_ticket_variations[0]['etn_ticket_price'] = $ticket_price;
					
					if(isset($total_capacity))
					{
						$etn_ticket_variations[0]['etn_avaiilable_tickets'] = $total_capacity;
						$etn_ticket_variations[0]['etn_max_ticket'] = $total_capacity;
						update_post_meta($post_id, 'etn_total_avaiilable_tickets', $total_capacity);
					}
					
				    //$etn_ticket_variations = maybe_serialize($etn_ticket_variations);
				    $res_update = update_post_meta($post_id, 'etn_ticket_variations', $etn_ticket_variations);
					echo ' $ticket_price ' . $ticket_price . ' ';
					echo ' $total_capacity ' . $total_capacity . ' ';
				}
				
				if(!empty($updated_post))
				{
					$updated_post['ID'] = $post_id;
					wp_update_post($updated_post);
				}
			}
		}
	}
	
	$success = TRUE;
}
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