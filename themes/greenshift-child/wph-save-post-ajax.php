<?php
/*
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
*/
//echo 'wph-save-post-ajax.php';
//return;

ob_start();

require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
$success = TRUE;
$post_id = 0;
$message = '';

$log_filename = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph-save-post-ajax.txt';

if(isset($_POST['post_id']) && !empty($_REQUEST['post_title']))
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
    $post_id = $_REQUEST['post_id'];
	
	// Prepare the post data
	$post_data = array(
	);
	
	$do_update = 0;
	
	if(!empty($_REQUEST['post_id']))
	{
		$post_data['ID'] = $_REQUEST['post_id'];
	}
	
	if(!empty($_REQUEST['post_status']))
		$post_data['post_status'] = $_REQUEST['post_status'];
	else
		$post_data['post_status'] = 'publish';
	
	if(!empty($_REQUEST['post_type']))
		$post_data['post_type'] = $_REQUEST['post_type'];
	else
		$post_data['post_type'] = 'post';
	
	if(isset($_REQUEST['post_title']))
		$post_data['post_title'] = sanitize_text_field($_REQUEST['post_title']);
	
	if(isset($_REQUEST['post_content']))
		$post_data['post_content'] = wp_kses_post($_REQUEST['post_content']);
	
	if(isset($_REQUEST['post_excerpt']))
		$post_data['post_excerpt'] = $_REQUEST['post_excerpt'];
	
	if($post_id)
	{
		$update_post = wp_update_post($post_data);
		if(!$update_post)
			$success = FALSE;
	}
	else
	{
		$post_id = wp_insert_post($post_data);
		if(!$post_id)
			$success = FALSE;
	}
	/*
	wph_log_data_global($log_filename,
		array(
			'AFTER wp_update_post' => TRUE,
		), TRUE, FALSE
	);
	*/
	if($success)
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
				wp_set_post_terms($post_id, $term->term_id, 'etn_category');
			}
		}
		
/*
			event_type: event_type,
			activity_type: activity_type,
			event_waiver: event_waiver,
			event_location: event_location,
			event_room_area: event_room_area,
			age_group: age_group,
			level: level,
			total_capacity: total_capacity,
			event_price: event_price,
			public_view: checkbox_public_view,
			date_start: date_start,
			date_end: date_end,
			time_start: time_start,
			time_end: time_end,
			tshirt_size: tshirt_size,
			jersey_size: jersey_size,
			shorts_size: shorts_size,
			pants_size: pants_size,
*/		
		if(isset($_REQUEST['service_ids']))
			update_post_meta($post_id, 'service_ids', $_REQUEST['service_ids']);
	}
}
else
{
	$success = FALSE;
	if(empty($_REQUEST['post_title']))
		$message = 'Empty post title';
}
/*
wph_log_data_global($log_filename,
	array(
		'AFTER ALL' => TRUE,
	), TRUE, FALSE
);
*/
$CONTENT = ob_get_contents();
$response = array('success' => $success, 'post_id' => $post_id, 'message' => $message, 'CONTENT' => $CONTENT);
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