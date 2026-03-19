<?php
    $atts = shortcode_atts(array(
		'waivers_count' => 20,
		'show_actions' => TRUE,
		'show_title' => FALSE,
		'show_filters' => FALSE,
		'show_view_all' => TRUE,
    ), $atts);
	
	
	$user_param = !empty($_REQUEST['user']) ? '&user=' . $_REQUEST['user'] : '';
	
	$sort = !empty($_REQUEST['sort']) ? $_REQUEST['sort'] : '';
	$sort_order = !empty($_REQUEST['order']) ? $_REQUEST['order'] : 'desc';
	
	$sort_orders = array(
						'waiver_title' => ['desc' => 'asc', 'asc' => 'desc'],
						'event_title' => ['desc' => 'asc', 'asc' => 'desc'],
						'time_signed' => ['desc' => 'asc', 'asc' => 'desc'],
				);
	
	//$sort_order_title = 
	//vd($waiver_ids, '$waiver_ids');
	
	$current_user_id = !empty($_REQUEST['user']) ? $_REQUEST['user'] : get_current_user_id();
	
	
	
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	
    $offset = ($paged - 1) * $atts['waivers_count'];
	
	//$post_status = !empty($_REQUEST['status']) ? $_REQUEST['status'] : 'publish';
	$post_status = !empty($_REQUEST['status']) ? $_REQUEST['status'] : 'publish,draft';
	
	global $wpdb;
	
	$order_dir = '';
	$order_by = 'ORDER BY meta_value';
	if($sort_order)
		$order_dir = ' ' . $sort_order;
	/*
	$query = "SELECT meta_key, meta_value FROM $wpdb->usermeta WHERE user_id = " . $current_user_id . " AND meta_key LIKE 'waiver_%' " . $order_by . $order_dir;
	//vd($query, '$query');
	$results = $wpdb->get_results($query, ARRAY_A);
	$waiver_ids = array(0);
	// Display the results
	if (!empty($results)) {
	    foreach ($results as $meta) {
			//echo '<p>Meta Key: ' . esc_html($meta['meta_key']) . '<br>Meta Value: ' . esc_html($meta['meta_value']) . '</p>';
			$waiver_ids[] = intval(str_replace('waiver_', '', $meta['meta_key']));
	    }
	} else {
	    echo '<p>No waiver meta found for this user.</p>';
	}
	
	//vd($waiver_ids, '$waiver_ids');
	
    $args = array(
        'post_type'		=> 'waiver',
        'post_status'	=> $post_status,
        'posts_per_page' => $atts['waivers_count'],
        'offset'		=> $offset,
        'post__in'		=> $waiver_ids,
    );
	
	if (!empty($_REQUEST['search'])) {
	    $args['s'] = sanitize_text_field($_REQUEST['search']);
	}
	
	if($sort == 'title')
	    $args['orderby'] = 'post_title';
	elseif($sort == 'date')
	    $args['orderby'] = 'post__in';
	
	if($sort_order)
	    $args['order'] = $sort_order;
	
    $posts = get_posts($args);
	//vd($posts);
	*/
?>
<div class="dashboard-widget waivers-widget waivers-table-wrapper">
<?php
	if($atts['show_title'])
	{
?>
	<div class="widget-title"> Waivers</div>
<?php
	}
?>
	<script>



	function filter_by()
	{
		var search_word = document.getElementById('waivers_search').value;
		var waivers_filter = document.getElementById('waivers_filter').value;
		var url = './?';
		if(waivers_filter !== '')
			url += '&status=' + waivers_filter;
		if(search_word !== '')
			url += '&search=' + search_word;
		//alert(search_word);
		window.location.href = url;
	}
	
	function do_search(event)
	{
		event.preventDefault();
		filter_by();
	}
	</script>
<?php
	$signed_waivers = get_user_meta($current_user_id, 'signed_waivers');
	
function sort_signed_waivers_by($signed_waivers, $key, $direction = 'asc') {
    if (!empty($signed_waivers)) {
        usort($signed_waivers, function($a, $b) use ($key, $direction) {
            if (!isset($a[$key]) || !isset($b[$key])) {
                return 0; // If the key doesn't exist, treat as equal
            }

            // Sort numerically if the key is 'waiver_id' or 'time_signed'
            if (is_numeric($a[$key]) && is_numeric($b[$key])) {
                return $direction === 'asc' ? ($a[$key] - $b[$key]) : ($b[$key] - $a[$key]);
            }

            // Sort alphabetically otherwise
            return $direction === 'asc' ? strcmp($a[$key], $b[$key]) : strcmp($b[$key], $a[$key]);
        });
    }

    return $signed_waivers;
}
	
	if($signed_waivers && $sort)
	{
		
		$signed_waivers = sort_signed_waivers_by($signed_waivers, $sort, $sort_order);
	}
	
	//vd($signed_waivers, '$signed_waivers');
	
	$search = !empty($_REQUEST['search']) ? $_REQUEST['search'] : '';
?>
	<table class="waivers-table">
		<thead>
		<tr>
			<th class="waivers-parent-table-th-1" scope="col"><a href="?sort=waiver_title&order=<?=$sort_orders['waiver_title'][$sort_order]?><?=$user_param?>">Waiver Name</a></th>
			<th class="waivers-parent-table-th-2" scope="col"><a href="?sort=event_title&order=<?=$sort_orders['event_title'][$sort_order]?><?=$user_param?>">Applies to</a></th>
			<th class="waivers-parent-table-th-3" scope="col"><a href="?sort=time_signed&order=<?=$sort_orders['time_signed'][$sort_order]?><?=$user_param?>">Date Signed</a></th>
			<th class="waivers-parent-table-th-4" scope="col">Action</th>
		</tr>
		</thead>
		<tbody>
<?php
/*
	if($sort == 'title')
	    $args['orderby'] = 'post_title';
	elseif($sort == 'date')
	    $args['orderby'] = 'post__in';
*/
	//vd($signed_waivers, '$signed_waivers');
	if($signed_waivers)
	{
		foreach($signed_waivers as $signed_waiver)
		{
			//$permalink = get_permalink($signed_waiver['waiver_id']);
			//$permalink = str_replace('/waiver/', '/waivers/', $permalink);
			
			$permalink = '/signed-waiver/' . $signed_waiver['waiver_id'] . '/' . $current_user_id;
			
			$event_permalink = get_permalink($signed_waiver['event_id']);
			if(!$event_permalink)
			{
				$event_permalink = 'javascript:void(0)';
				$onclick = ' onclick="alert(\'The event does not exist\')"';
			}
			else
			{
				$onclick = '';
			}
			
			//vd($onclick, "$onclick");
			$date_sign = date('m/d/Y \a\t g:i a', $signed_waiver['time_signed']);
?>
		<tr>
			<td class="waivers-parent-table-td-1"><div class="waivers-table-waiver-title"><a class="waiver-view" href="<?=$permalink?>"><?=$signed_waiver['waiver_title']?></a></div></td>
			<td class="waivers-parent-table-td-2"><div class="waivers-table-waiver-title"><a<?=$onclick?> class="waiver-view" href="<?=$event_permalink?>"><?=$signed_waiver['event_title']?></a></div></td>
			<td class="waivers-parent-table-td-3"><?=$date_sign?></td>
			<td class="waivers-parent-table-td-4"><a class="waiver-view" href="<?=$permalink?>">View</a></td>
		</tr>
<?php
		}
	}
	else
	{
?>
		<tr>
			<td colspan="4">No posts found</td>
		</tr>
<?php
	}
?>
		</tbody>
	</table>
</div>
<?php
		$total_posts = count_user_posts($current_user_id, 'waiver');
        $total_pages = ceil($total_posts / $atts['waivers_count']);
        if ($total_posts > $atts['waivers_count']) {
?>
<div class="pagination-container">
<?php
            echo paginate_links(array(
                'total'   => $total_pages,
                'current' => $paged,
            ));
?>
</div>
<?php
        }
		
?>