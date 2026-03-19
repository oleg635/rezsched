<?php
ob_start();

require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
$success = TRUE;
$post_id = 0;
$message = '';

function search_post_ids_by_title($post_type = 'etn', $author = 0, $search = '') {
    global $wpdb;
	
    $search = '%' . $search . '%';
	
	vd($search, '$search');
	
    $query = '
        SELECT ID 
        FROM ' . $wpdb->posts . '
        WHERE post_type = "' . $post_type . '"
          AND post_author = "' . $author . '"
          AND post_title LIKE "' . $search . '"
          AND post_status = "publish"
        ';
	
	vd($query, '$query');
	
    return $wpdb->get_col($query);
}

function filter_post_title_like($where) {
    global $wpdb;
    if (!empty($GLOBALS['post_title_search'])) {
        $search = esc_sql($GLOBALS['post_title_search']);
        $where .= " AND {$wpdb->posts}.post_title LIKE '%{$search}%'";
    }
    return $where;
}

if(1)
{
    check_ajax_referer('get-events-nonce', 'security');
	
	$vendor_id = $current_user_id = !empty($_REQUEST['vendor_id']) ? $_REQUEST['vendor_id'] : 0;
    $search = isset($_REQUEST['search']) ? sanitize_text_field($_REQUEST['search']) : '';
	
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph-get-events-ajax.txt',
		array(
			'$search' => $search,
		), TRUE, FALSE
	);
	
	$post_status = !empty($_REQUEST['status']) ? $_REQUEST['status'] : 'publish';
	
	$args_sbscr = array(
	    'post_type' => 'product',
	    'post_status' => $post_status,
	    'author' => $current_user_id,
	    'posts_per_page' => -1,
	    'orderby' => 'ID',
	    'order' => 'ASC',
	    'meta_query' => array(
	        'relation' => 'AND', // This allows us to handle different meta queries for different post types
	        // Meta query for WooCommerce subscription products
	           array(
	               'key' => '_subscription_period',
	               'compare' => 'EXISTS'
	           ),
	    ),
	    'tax_query' => array(
	        'relation' => 'AND',
	    ),
	);
	
	if($search)
	{
		$post_ids = search_post_ids_by_title('product', $current_user_id, $search);
		$post_ids = array_merge([0], $post_ids);
		$args_sbscr['post__in'] = $post_ids;
	}
	
	vd($args_sbscr, '$args_sbscr');
	
	$parent_posts = get_parent_posts($current_user_id);
	vd($parent_posts, '$parent_posts');
	
	$args = array(
		'post_type'      => 'etn',
		'post_status'	=> $post_status,
		'author'         => $current_user_id,
		'posts_per_page' => -1,
		'meta_key'       => 'etn_start_timestamp',
		'orderby'        => 'meta_value',
		'post__not_in' => $parent_posts,
		'order'          => 'ASC',
		'meta_query' => array(
			'relation' => 'AND',
		),
		'tax_query' => array(
			'relation' => 'AND',
		),
	);
	
	if($search)
	{
		$post_ids = search_post_ids_by_title('etn', $current_user_id, $search);
		$post_ids = array_merge([0], $post_ids);
		$args['post__in'] = $post_ids;
	}
	
	vd($args, '$args');
	
	if(!empty($_REQUEST['past']))
	{
		$past = $_REQUEST['past'];
		$args['order'] = 'DESC';
		$compare = '<';
	}
	else
	{
		$past = 0;
		$compare = '>=';
	}
	
	$time = time();
	
	$args['meta_query'][] = array(
		'key'     => 'etn_start_timestamp',
		'value'   => $time,
		'compare' => $compare,
	);
	
	if(!empty($_REQUEST['location']))
	{
		$location_ajax = $_REQUEST['location'];
		$location = get_term($_REQUEST['location']);
		
		if($location)
		{
			$args['meta_query'][] = array(
		            'key'     => 'etn_location',
		            'value'   => '"' . $location->term_id . '"',
		            'compare' => 'LIKE',
		        );
			
			$args_sbscr['meta_query'][] = array(
		            'key'     => 'etn_location',
		            'value'   => '"' . $location->term_id . '"',
		            'compare' => 'LIKE',
		        );
		}
	}
	
	if(!empty($_REQUEST['date_start']) && !empty($_REQUEST['date_end']))
	{
		$date_start_ajax = $_REQUEST['date_start'];
		$date_end_ajax = $_REQUEST['date_end'];
	    $args['meta_query'] = array(
	        'relation' => 'AND', // Ensures both conditions are met
	        array(
	            'key'     => 'etn_start_date',
	            'value'   => $_REQUEST['date_start'],
	            'compare' => '>=',
	            'type'    => 'DATE', // Specify the type if storing dates
	        ),
	        array(
	            'key'     => 'etn_end_date',
	            'value'   => $_REQUEST['date_end'],
	            'compare' => '<=',
	            'type'    => 'DATE', // Specify the type if storing dates
	        ),
	    );
	}
	elseif(!empty($_REQUEST['date_range']))
	{
		$month_from = $_REQUEST['date_range'];
		$month_to = $_REQUEST['date_range'];
		
		$date_from = !empty($_REQUEST['date_from']) ? $_REQUEST['date_from'] : '01';
		$date_to = !empty($_REQUEST['date_to']) ? $_REQUEST['date_to'] : '31';
		
		$month_from = substr($month_from, 0, 4) . '-' . substr($month_from, 4, 2);
		$month_to = substr($month_to, 0, 4) . '-' . substr($month_to, 4, 2);
		
		$from = $month_from . '-' . $date_from;
		$to = $month_to . '-' . $date_to;
		
	    $args['meta_query'] = array(
	        'relation' => 'AND', // Ensures both conditions are met
	        array(
	            'key'     => 'etn_start_date',
	            'value'   => $from,
	            'compare' => '>=',
	            'type'    => 'DATE', // Specify the type if storing dates
	        ),
	        array(
	            'key'     => 'etn_end_date',
	            'value'   => $to,
	            'compare' => '<=',
	            'type'    => 'DATE', // Specify the type if storing dates
	        ),
	    );
	}
	
	if(!empty($_REQUEST['event_type']))
	{
		$category = get_term($_REQUEST['event_type']);
		
		if($category)
		{
			$args['tax_query'][] = array(
		            'taxonomy'     => 'etn_category',
		            'field'   => 'id',
					'terms' => $_REQUEST['event_type'],
		        );
			
			$args_sbscr['tax_query'][] = array(
		            'taxonomy'     => 'etn_category',
		            'field'   => 'id',
					'terms' => $_REQUEST['event_type'],
		        );
		}
	}
	
	if(!empty($_REQUEST['activity']))
	{
		$activity_ajax = $_REQUEST['activity'];
		$tag = get_term($_REQUEST['activity']);
		
		if($tag)
		{
			$args['tax_query'][] = array(
		            'taxonomy'     => 'etn_tags',
		            'field'   => 'id',
					'terms' => $_REQUEST['activity'],
		        );
			
			$args_sbscr['tax_query'][] = array(
		            'taxonomy'     => 'etn_tags',
		            'field'   => 'id',
					'terms' => $_REQUEST['activity'],
		        );
		}
	}
	
	$sbscr = get_posts($args_sbscr);
	$posts = get_posts($args);
	
	vd($args, '$args');
	
	$posts = array_merge($sbscr, $posts);
	
	foreach($posts as $post)
	{
		$price = (float) get_event_price($post->ID);
			$post->price = $price ? $price : '';
	}
}
else
{
	$success = FALSE;
}

$CONTENT = ob_get_contents();
$response = array('success' => $success, 'search' => $search, 'vendor_id' => $vendor_id, 'added_trs' => $_REQUEST['added_trs'], 'events' => $posts, 'message' => $message, 'CONTENT' => $CONTENT);
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