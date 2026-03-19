<?php
    $atts = shortcode_atts(array(
		'waivers_count' => 20,
		'show_actions' => TRUE,
		'show_title' => FALSE,
		'show_filters' => FALSE,
		'show_view_all' => TRUE,
    ), $atts);
	
	$current_user_id = !empty($_REQUEST['user']) ? $_REQUEST['user'] : get_current_user_id();
	
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	
    $offset = ($paged - 1) * $atts['waivers_count'];
	
	//$post_status = !empty($_REQUEST['status']) ? $_REQUEST['status'] : 'publish';
	$post_status = !empty($_REQUEST['status']) ? $_REQUEST['status'] : 'publish,draft';
	
    $args = array(
        'post_type'      => 'waiver',
        'post_status'	=> $post_status,
        'author'         => $current_user_id,
        'posts_per_page' => $atts['waivers_count'], // Retrieve specified number of posts
        'offset'         => $offset, // Offset for pagination
    );
	
	if (!empty($_REQUEST['search'])) {
	    $args['s'] = sanitize_text_field($_REQUEST['search']);
	}
	
    $posts = get_posts($args);
	//vd($posts);
?>
<style>
.waivers_table-container td.waivers-table-td-3 a:first-child:after {
	display:none;
}
</style>
<div class="dashboard-widget waivers-widget waivers-table-wrapper">
<?php
	if($atts['show_actions'])
	{
?>
	<div class="waivers_page_actions">
		<a href="/create-waiver/" class="etn-btn etn-btn-primary add_waiver">Add Waiver</a><!-- show_actions -->
	</div>
<?php
	}
?>
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
	$search = !empty($_REQUEST['search']) ? $_REQUEST['search'] : '';
?>
	<table class="waivers-table">
		<thead>
		<tr>
			<th class="waivers-table-th-1" scope="col"><input id="waivers_toggle_all" class="waivers-table-checkbox" type="checkbox" /></th>
			<th class="waivers-table-th-2" scope="col"><button id="waivers_delete_button" class="waivers-table-input waivers-delete-button">Delete</button></th>
			<th class="waivers-table-th-3" scope="col">&nbsp;</th>
			<th class="waivers-table-th-4" scope="col"><form onsubmit="javascript:do_search(event)"><input value="<?=$search?>" type="text" id="waivers_search" name="search" class="waivers-table-input waivers-search-input" placeholder="Search" /></form></th>
			<th class="waivers-table-th-5" scope="col">&nbsp;</th>
			<th class="waivers-table-th-6" scope="col">
											<select onchange="javascript:filter_by()" id="waivers_filter" class="waivers-table-input waivers-filter-select">
												<option value="">Filter By</option>

<?php
		$statuses = ['publish' => 'Publish', 'draft' => 'Draft'];
		$selected_status = !empty($_REQUEST['status']) ? $_REQUEST['status'] : '';
		foreach($statuses as $key => $status)
		{
			$selected = $selected_status == $key ? ' selected' : '';
?>
												<option value="<?=$key?>"<?=$selected?>><?=$status?></option>
<?php
		}
?>						
											</select>
			</th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td class="waivers-table-td-1 table-title">&nbsp;</td>
			<td class="waivers-table-td-2 table-title">Waiver Name</td>
			<td class="waivers-table-td-3 table-title">Applies to</td>
			<td class="waivers-table-td-4 table-title">Date Published</td>
			<td class="waivers-table-td-5 table-title">Status</td>
			<td class="waivers-table-td-6 table-title">Action</td>
		</tr>
<?php
	if($posts)
	{
		foreach ($posts as $post)
		{
			$permalink = get_permalink($post->ID);
			$permalink = str_replace('/waiver/', '/waivers/', $permalink);
			//$permalink = '/waivers/' . $post->post_name . '/';
			
            $waiver_applies_to = '';
			$args = array(
			    'post_type'      => 'any',
			    'post_status'    => 'publish',
			    'posts_per_page' => -1,
				'orderby' => 'ID',
				'order' => 'ASC',
			    'meta_query'     => array(
			        array(
			            'key'     => 'waiver',
			            'value'   => $post->ID,
			            'compare' => '='
			        )
			    )
			);
			
			$events = get_posts($args);
			
			$prev_name = '';
			
			if(!empty($events))
			{
			    foreach ($events as $event) {
					if($event->post_title == $prev_name)
						continue;
					$prev_name = $event->post_title;
					
					if($_SERVER['REMOTE_ADDR'] == '92.60.179.128')
						$waiver_applies_to .= '<a href="/etn/' . $event->post_name . '">' . $event->post_title . '</a><br>';
					else
						$waiver_applies_to .= $event->post_title . '<br>';
			    }
			}
			
			$waiver_applies_to = substr($waiver_applies_to, 0, strlen($waiver_applies_to) - 4);
			//vd($events, '$events');
			//vd($waiver_applies_to, '$waiver_applies_to');
			//$waiver_applies_to_title = '2-5 Players Hockey Training';
			//if($waiver_applies_to)
				//$waiver_applies_to_title = get_the_title($waiver_applies_to);
			
			$nonce = wp_create_nonce('clone_event_nonce');
?>
		<tr>
			<td class="waivers-table-td-1"><input id="waivers_<?=$post->ID?>" class="waivers-table-checkbox" type="checkbox" value="<?=$post->ID?>" /></td>
			<td class="waivers-table-td-2"><div class="waivers-table-waiver-title"><a href="<?=$permalink?>"><?=get_the_title($post->ID)?></a></div></td>
			<td class="waivers-table-td-3"><?=$waiver_applies_to?></td>
			<td class="waivers-table-td-4"><?=get_the_date('m/d/Y \a\t g:i a', $post->ID);?></td>
			<td class="waivers-table-td-5"><?=ucfirst($post->post_status)?></td>
			<td class="waivers-table-td-6"><a class="waivers-table-action" href="/create-waiver/?id=<?=$post->ID?>">Edit</a><a class="waivers-table-action" href="<?php echo home_url('/clone-event/' . $post->ID . '/?_wpnonce=' . $nonce); ?>">Clone</a></td>
		</tr>
<?php
		}
	}
	else
	{
?>
		<tr>
			<td colspan="6">No waivers found</td>
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
		
	if($atts['show_actions'])
	{
?>
	<script>
	document.getElementById('waivers_toggle_all').addEventListener('click', function() {
	    const isChecked = this.checked;
	    document.querySelectorAll('.waivers-table-checkbox').forEach(function(checkbox) {
	        checkbox.checked = isChecked;
	    });
	});
		
	document.getElementById('waivers_delete_button').addEventListener('click', function() {
	    const selectedPosts = [];
	    document.querySelectorAll('.waivers-table-checkbox:checked').forEach(function(checkbox) {
	        selectedPosts.push(checkbox.value);
	    });
	
	    if (selectedPosts.length === 0) {
	        alert('Please select at least one post to delete.');
	        return;
	    }
	
	    fetch('/wp-admin/admin-ajax.php', {
	        method: 'POST',
	        headers: {
	            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
	        },
	        body: new URLSearchParams({
	            'action': 'delete_posts',
	            'post_ids': selectedPosts.join(','),
	            'nonce': '<?=wp_create_nonce('delete_posts_nonce')?>'
	        })
	    })
	    .then(response => response.json())
	    .then(data => {
	        if (data.success) {
				window.location.reload();
	            /*
	            selectedPosts.forEach(function(postId) {
	                const row = document.getElementById('waivers_' + postId).closest('tr');
	                row.parentNode.removeChild(row);
	            });
				*/
	        } else {
	            alert('Failed to delete posts: ' + data.message);
	        }
	    })
	    .catch(error => {
	        console.error('Error:', error);
	    });
	});	
	
	</script>
<?php
	}
?>