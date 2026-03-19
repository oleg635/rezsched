<?php
    $atts = shortcode_atts(array(
		'attendees_count' => 4,
		'show_title' => TRUE,
		'show_filters' => FALSE,
		'show_view_all' => TRUE,
    ), $atts);
	
	if($atts['show_filters'])
		$atts['attendees_count'] = 20;
	
	$current_user_id = get_current_user_id();
	
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	
    $offset = ($paged - 1) * $atts['attendees_count'];
	
	$all_user_events = $user_events = get_user_events($current_user_id, $fields = 'ids');
	
	$posts = [];
	if($all_user_events)
	{
		if(!empty($_REQUEST['event']))
			$user_events = [intval($_REQUEST['event'])];
		elseif(!empty($_REQUEST['month']))
		{
			$year = (substr($_REQUEST['month'], 0, 4));
			$month = (substr($_REQUEST['month'], 4, 2));
			
			if($year && $month)
			{
				$args_etn = array(
			        'post_type'      => 'etn',
			        'post_status'	=> 'publish',
			        'author'         => $current_user_id,
				    'fields'         => 'ids',
				    'posts_per_page' => -1,
				    'meta_query' => array(
				        array(
				            'key'     => 'etn_start_date',
				            'value'   => $year . '-' . $month . '-',
				            'compare' => 'LIKE',
				        ),
				    ),
				);
			}
			
			//get_user_events($current_user_id, $fields = 'ids');
			$user_events = get_posts( $args_etn );
		}
		
			//vd($args, '$args');
			//vd($user_events, '$user_events');
			
	    $args = array(
	        'post_type'      => 'etn-attendee',
	        'post_status'      => 'publish',
	        'posts_per_page' => $atts['attendees_count'],
	        'offset'         => $offset,
			'meta_query' => array(
					'relation' => 'AND',
					array(
					'key'     => 'etn_event_id',
					'value'   => $user_events,
					'compare' => 'IN',
					'type'    => 'NUMERIC',
				),
			),
	    );
		//?etn_event_date_range=&etn_event=1&etn_payment_status=Success&etn_ticket_status=&post_type=etn
		//
		if(!empty($_REQUEST['payment_status']))
		{
			$args['meta_query'][] = array(
		            'key'     => 'etn_status',
		            'value'   => $_REQUEST['payment_status'],
		            'compare' => '=',
		        );
		}
		
		if(!empty($_REQUEST['ticket_status']))
		{
			$args['meta_query'][] = array(
		            'key'     => 'etn_attendeee_ticket_status',
		            'value'   => $_REQUEST['ticket_status'],
		            'compare' => '=',
		        );
		}
		/*
		$args = array(
		    'post_type'  => 'etn-attendee',
		    'meta_query' => array(
		        'relation' => 'AND', // Set relation to 'AND' to apply all meta queries
		        array(
		            'key'     => 'etn_event_id',
		            'value'   => array(1, 2, 3),
		            'compare' => 'IN',
		            'type'    => 'NUMERIC',
		        ),
		        array(
		            'key'     => 'etn_status',
		            'value'   => 'your_value_here',
		            'compare' => '=', // Adjust compare as needed
		        ),
		        array(
		            'key'     => 'etn_attendeee_ticket_status',
		            'value'   => 'your_value_here',
		            'compare' => '=', // Adjust compare as needed
		        ),
		    ),
		);
		
		*/
		
	    $posts = get_posts($args);
	}
	
	$posts_count = $posts ? count($posts) : 0;
	//vd($posts);
?>
<div class="dashboard-widget attendees events events-table-wrapper attendees-table-wrapper">
<?php
	if($atts['show_title'])
	{
?>
	<div class="widget-title"> Attendees List</div>
<?php
	}
	
	if($atts['show_filters'])
	{
?>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
	    const bulk_actions_form = document.getElementById('bulk_actions_form');
	    const formatSelect = document.getElementById('format');
	
	    bulk_actions_form.addEventListener('submit', function(event) {

		    const selectedPosts = [];
		    document.querySelectorAll('.attendees-table-checkbox:checked').forEach(function(checkbox) {
		        selectedPosts.push(checkbox.value);
		    });

		    if (selectedPosts.length === 0) {
				event.preventDefault();
		        alert('Please select at least one post to delete.');
		        return;
		    }
			
			document.getElementById('selected_posts').value = selectedPosts.join(',');
		
	        if (formatSelect.value === 'trash') {
	            event.preventDefault();

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
			        } else {
			            alert('Failed to delete posts: ' + data.message);
			        }
			    })
			    .catch(error => {
			        console.error('Error:', error);
			    });
	        }
	    });
	});
		
	function process_bulk_action()
	{
		
	}
	</script>
	<div class="etn_search_shortcode etn_search_wrapper">
		<form method="GET" id="bulk_actions_form" class="etn_event_inline_form bulk_actions_form" action="/wp-content/themes/greenshift-child/wph-export-posts.php">
			<div class="etn-event-search-wrapper">
				<!-- Bulk actions -->
				<div class="input-group">
					<select id="format" name="format" class="etn_event_select2 etn_event_select bulk_actions">
						<option value="">Bulk actions</option>
						<option value="trash">Move to Trash</option>
						<option value="json">Export JSON</option>
						<option value="csv">Export CSV</option>
					</select>
					<div class="search-button-wrapper">
						<input id="selected_posts" type="hidden" name="post" value="">
						<input type="hidden" name="etn-action" value="export">
						<input type="hidden" name="post_type" value="etn-attendee">
						<button type="submit" class="etn-btn etn-btn-primary">Apply</button>
					</div>
				</div>
				<!-- // Bulk actions -->
			</div>
		</form>
		<form method="GET" class="etn_event_inline_form filter_form">
			<div class="etn-event-search-wrapper">
				<!-- Dates -->
				<div class="input-group">
					<select name="month" class="etn_event_select2 etn_event_select etn_event_date_range">
						<option value="">All Dates</option>
<?php
		if($all_user_events)
		{
			$dates = [];
		    foreach ( $all_user_events as $post_id ) {
				$event_date = get_post_meta($post_id, 'etn_start_date', TRUE);
		        $post_date = mysql2date( 'Ym', $event_date );
		        $formatted_date = mysql2date( 'F Y', $event_date );
				
		        if ( ! isset( $dates[ $post_date ] ) ) {
		            $dates[ $post_date ] = $formatted_date;
		        }
		    }
			
			if($dates)
			{
				krsort( $dates );
				$selected_date = !empty($_REQUEST['month']) ? $_REQUEST['month'] : 0;
				foreach($dates as $key => $value)
				{
					if($value)
					{
						$selected = $selected_date == $key ? ' selected' : '';
?>
						<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
<?php
					}
				}
			}
		}
?>						
					</select>
					<select name="event" id="event" class="etn_event_select2 etn_event_select etn_event">
						<option value="">All Events</option>
<?php
		if($all_user_events)
		{
			$selected_event = !empty($_REQUEST['event']) ? $_REQUEST['event'] : 0;
			foreach($all_user_events as $user_event)
			{
				$selected = $selected_event == $user_event ? ' selected' : '';
?>
						<option value="<?=$user_event?>"<?=$selected?>><?=get_the_title($user_event)?></option>
<?php
			}
		}
?>						
					</select>
					<select name="payment_status" class="etn_event_select2 etn_event_select etn_payment_status">
						<option value="">All Payment Status</option>
<?php
		$payment_statuses = array('success' => 'Success', 'failed' => 'Failed');
		$selected_payment_status = !empty($_REQUEST['payment_status']) ? $_REQUEST['payment_status'] : 0;
		foreach($payment_statuses as $key => $value)
		{
			$selected = $selected_payment_status == $key ? ' selected' : '';
?>
						<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
<?php
		}
?>						
					</select>
					<select name="ticket_status" class="etn_event_select2 etn_event_select etn_ticket_status">
						<option value="">All Ticket Status</option>
<?php
		$ticket_statuses = array('used' => 'Used', 'unused' => 'Unused');
		$selected_ticket_status = !empty($_REQUEST['ticket_status']) ? $_REQUEST['ticket_status'] : 0;
		foreach($ticket_statuses as $key => $value)
		{
			$selected = $selected_ticket_status == $key ? ' selected' : '';
?>
						<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
<?php
		}
?>						
					</select>
					<div class="search-button-wrapper">
						<button type="submit" class="etn-btn etn-btn-primary">Filter</button>
					</div>
					<span class="tickets_number"><?=$posts_count?>&nbsp;<?php if($posts_count != 1) echo 'items'; else echo 'item'; ?></span>
				</div>
            </div>
        </div>
		</form>
		
	</div>
	
<?php
	}
?>
	<script>
	function show_ticket(attendee_id, etn_info_edit_token)
	{
		window.open('/etn-attendee?etn_action=download_ticket&attendee_id=' + attendee_id + '&etn_info_edit_token=' + etn_info_edit_token);
	}
	</script>
	<div class="attendees-box">
	<table class="attendees-table events-table">
		<thead>
		<tr>
<?php
	if($atts['show_filters'])
	{
?>
			<th class="attendees-table-th-1" scope="col"><input id="attendees_toggle_all" type="checkbox"></th>
			<th class="attendees-table-th-2" scope="col">Ticket ID</th>
			<th class="attendees-table-th-3" scope="col">Attendee ID</th>
			<th class="attendees-table-th-4" scope="col">Name</th>
			<th class="attendees-table-th-5" scope="col">Email</th>
			<th class="attendees-table-th-6" scope="col">Event</th>
			<th class="attendees-table-th-7" scope="col">Payment Status</th>
			<th class="attendees-table-th-8" scope="col">Ticket Status</th>
			<th class="attendees-table-th-9" scope="col">Action</th>
<?php
	}
	else
	{
?>
			<th class="attendees-table-th-1" scope="col">Ticket ID</th>
			<th class="attendees-table-th-2" scope="col">Attendee ID</th>
			<th class="attendees-table-th-3" scope="col">Name</th>
			<th class="attendees-table-th-4" scope="col">Email</th>
			<th class="attendees-table-th-5" scope="col">Event</th>
			<th class="attendees-table-th-6" scope="col">Payment Status</th>
			<th class="attendees-table-th-7" scope="col">Ticket Status</th>
			<th class="attendees-table-th-8" scope="col">Action</th>
<?php
	}
?>
		</tr>
		</thead>
		<tbody>
<?php
	if($posts)
	{
		foreach ($posts as $post)
		{
			//$permalink = get_permalink($post->ID);
			$post_meta = get_post_meta($post->ID);
			
			//vd($post_meta);
			
			$ticket = '';
			if(!empty($post_meta['etn_info_edit_token']))
				$ticket = '<button onclick="show_ticket(' . $post->ID . ', \'' . $post_meta['etn_info_edit_token'][0] . '\')" class="button-action" value="Ticket">Ticket</button>';
			
			$etn_status = !empty($post_meta['etn_status'][0]) ? $post_meta['etn_status'][0] : '';
			$etn_attendeee_ticket_status = !empty($post_meta['etn_attendeee_ticket_status'][0]) ? $post_meta['etn_attendeee_ticket_status'][0] : '';
			$etn_unique_ticket_id = !empty($post_meta['etn_unique_ticket_id'][0]) ? $post_meta['etn_unique_ticket_id'][0] : '';
			
			$attendeee_data = get_userdata( $post->post_author );
			if($attendeee_data)
				$attendeee_email = $attendeee_data->user_email;
			
			$event_id = get_post_meta($post->ID, 'etn_event_id', TRUE);
			$event_html = '&nbsp;';
			if(isset($post_meta['etn_event_id']))
			{
				$post_event = get_post($event_id);
				if($post_event)
				{
					$event_html = '<a href="' . get_permalink($post_event->ID) . '">' . get_the_title($post_event->ID) . '</a>';
				}
			}
?>
		<tr>
<?php
	if($atts['show_filters'])
	{
?>
			<td class="attendees-table-td-1"><input id="cb-select-<?=$post->ID?>" class="attendees-table-checkbox" type="checkbox" name="post[]" value="<?=$post->ID?>"></td>
			<td class="attendees-table-td-2"><?=$etn_unique_ticket_id?></td>
			<td class="attendees-table-td-3"><?=$post->ID?></td>
			<td class="attendees-table-td-4"><div class="attendees-table-attendee-title events-table-event-title"><?=get_the_title($post->ID)?></div></td>
			<td class="attendees-table-td-5"><?=$attendeee_email?></td>
			<td class="attendees-table-td-6"><?=$event_html?></td>
			<td class="attendees-table-td-7"><?=$etn_status?></td>
			<td class="attendees-table-td-8"><input type="checkbox" />&nbsp;<?=$etn_attendeee_ticket_status?></td>
			<td class="attendees-table-td-9"><?=$ticket?></td>
<?php
	}
	else
	{
?>
			<td class="attendees-table-td-1"><?=$etn_unique_ticket_id?></td>
			<td class="attendees-table-td-2"><?=$post->ID?></td>
			<td class="attendees-table-td-3"><div class="attendees-table-attendee-title events-table-event-title"><?=get_the_title($post->ID)?></div></td>
			<td class="attendees-table-td-4"><?=$attendeee_email?></td>
			<td class="attendees-table-td-5"><?=$event_html?></td>
			<td class="attendees-table-td-6"><?=$etn_status?></td>
			<td class="attendees-table-td-7"><input type="checkbox" />&nbsp;<?=$etn_attendeee_ticket_status?></td>
			<td class="attendees-table-td-8"><?=$ticket?></td>
<?php
	}
?>
		</tr>
<?php
		}
	}
	else
	{
?>
		<tr>
			<td colspan="9">No Attendees found</td>
		</tr>
<?php
	}
?>
		</tbody>
	</table>
<?php
	if($atts['show_view_all'])
	{
?>
	<div class="attendees-table-view-all events-table-view-all"><a href="/attendees/">View All Attendees List</a></div>
<?php
	}
?>
</div>
</div>
	<script>
	var attendees_toggle_all = document.getElementById('attendees_toggle_all');
	if(attendees_toggle_all)
	{
		attendees_toggle_all.addEventListener('click', function() {
		    const isChecked = this.checked;
		    document.querySelectorAll('.attendees-table-checkbox').forEach(function(checkbox) {
		        checkbox.checked = isChecked;
		    });
		});
	}
	</script>
<?php
	if($atts['show_filters'])
	{
	    $args['posts_per_page'] = -1;
	    $posts = get_posts($args);
		$total_posts = $posts ? count($posts) : 0;
        $total_pages = ceil($total_posts / $atts['attendees_count']);
		
        if ($total_posts > $atts['attendees_count']) {
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
	}
?>