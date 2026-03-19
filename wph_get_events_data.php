<?php
define('WP_USE_THEMES', false);
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

	$log_filename = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph_get_events_data.txt';
	
	wph_log_data_global($log_filename,
		array(
			'$_POST' => $_POST,
			'$_REQUEST' => $_REQUEST,
		), TRUE, FALSE
	);

$posts_data = array();

if (isset($_POST['event_ids'])) {
    $event_ids = $_POST['event_ids'];
	
    foreach ($event_ids as $event_id)
	{
        $post = get_post($event_id);
		
        if($post && $post->post_status === 'publish')
		{
            $start_date = get_post_meta($event_id, 'etn_start_date', true);
            $start_time = get_post_meta($event_id, 'etn_start_time', true);
			
			$timestamp = strtotime($start_date . ' ' . $start_time);
			$formatted_date = date('M d, Y h:i A', $timestamp);
			
			$categories = get_the_terms($event_id, 'etn_category');
			$categories_arr = [];
			$etn_categories = 'none';
			
			if($categories && !is_wp_error($categories))
			{
				foreach($categories as $category)
				{
					$categories_arr[] = rtrim($category->name, 's');
				}
				
				$etn_categories = implode(', ', $categories_arr);
			}
			
			$tags = wp_get_post_terms($event_id, 'etn_tags');
			$tags_arr = [];
			$etn_tags = 'none';
			
			if($tags && !is_wp_error($tags))
			{
				foreach($tags as $tag)
				{
					$tags_arr[] = $tag->name;
				}
				
				$etn_tags = implode(', ', $tags_arr);
			}
			
            $age_group = get_post_meta($event_id, 'age_group', true);
            $level = get_post_meta($event_id, 'level', true);
			/*
            $etn_location_arr = get_post_meta($event_id, 'etn_location', true);
			
			$etn_location_slug = $term = $etn_location = '';
			$etn_location_id = 0;
			
			if(!empty($etn_location_arr[0]))
			{
				$etn_location_id = $etn_location_arr[0];
				$term = get_term($etn_location_id);
				if($term)
				{
					$etn_location = $term->name;
					$etn_location_slug = $term->slug;
				}
				else
					$etn_location = 'get_term() error';
			}
			*/
            $tshirt_size = get_post_meta($event_id, 'tshirt_size', true);
            $jersey_size = get_post_meta($event_id, 'jersey_size', true);
            $pants_size = get_post_meta($event_id, 'pants_size', true);
            $shorts_size = get_post_meta($event_id, 'shorts_size', true);
			
			$avaiilable_tickets = 0;
            $etn_total_avaiilable_tickets = get_post_meta($event_id, 'etn_total_avaiilable_tickets', true);
            $etn_total_sold_tickets = get_post_meta($event_id, 'etn_total_sold_tickets', true);
			if($etn_total_avaiilable_tickets && $etn_total_sold_tickets)
	            $avaiilable_tickets = $etn_total_avaiilable_tickets - $etn_total_sold_tickets;
			
            $etn_event_location = get_post_meta($event_id, 'etn_event_location', true);
			
			
            $posts_data[$post->ID] = array(
                'id' => $post->ID,
                'post_title' => $post->post_title,
                'post_permalink' => get_permalink($post->ID),
                'etn_start_date' => $start_date,
                'formatted_date' => $formatted_date,
                'etn_categories' => $etn_categories,
                'etn_tags' => $etn_tags,
                'age_group' => $age_group,
                'level' => $level,
                'etn_location' => $etn_event_location,
                'etn_location_id' => $etn_location_id,
                'etn_location_slug' => $etn_location_slug,
                'tshirt_size' => $tshirt_size,
                'jersey_size' => $jersey_size,
                'pants_size' => $pants_size,
                'shorts_size' => $shorts_size,
                'etn_total_avaiilable_tickets' => $etn_total_avaiilable_tickets,
                'etn_total_sold_tickets' => $etn_total_sold_tickets,
                'avaiilable_tickets' => $avaiilable_tickets,
            );
        }
    }
	
    header('Content-Type: application/json');
    echo json_encode($posts_data);
} else {
    header('Content-Type: application/json');
    echo json_encode($posts_data);
}
?>
