<?php
$logged_user_id = get_current_user_id();
$is_guest = empty($logged_user_id);

$theme_uri = get_stylesheet_directory_uri();

$view_orders = $atts['view_orders'];

$user_role = '';
$user_roles = get_current_user_role();
if($user_roles)
	$user_role = $user_roles[0];

if(in_array('um_custom_role_2', $user_roles))
{
	$view_orders = FALSE;
	$is_parent = TRUE;
}
else
{
	$is_parent = FALSE;
}

$current_user_id = 0;
$user_name = '';

$all = !empty($atts['all']) ? $atts['all'] : 0;

if(empty($all))
{
	$parent_facility = get_user_meta($logged_user_id, 'parent_facility', TRUE);
	if($parent_facility)
        $current_user_id = $parent_facility;
	else
        $current_user_id = get_current_user_id();
	
	$user_name = get_event_user_name();
	if($user_name)
	{
		$user_id = user_id_by_meta('facility_name_slug', $user_name, 0);
		if($user_id)
			$user = get_user_by('ID', $user_id);
		else
			$user = get_user_by('slug', $user_name);
		
		if($user)
		{
		    $current_user_id = $user->ID;
			$private_group_lesson_ids = get_private_group_lesson_ids(0);
		}
	}
}

vd($user_name, '$user_name');
vd($current_user_id, '$current_user_id');

$user_name = get_query_var('user_name');
if($user_name)
{
	$user_id = user_id_by_meta('facility_name_slug', $user_name, 0);
	if($user_id)
		$user = get_user_by('ID', $user_id);
	else
		$user = get_user_by('slug', $user_name);
	
	if($user)
	{
	    $current_user_id = $user->ID;
		$private_group_lesson_ids = get_private_group_lesson_ids(0);
	}
}

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$offset = ($paged - 1) * $atts['events_count'];
$post_status = !empty($_REQUEST['status']) ? $_REQUEST['status'] : 'publish';
$upcoming_ajax = '';

$location_ajax = '';
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

$date_range_ajax = '';
$date_start_ajax = $date_end_ajax = '';

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
	$date_range_ajax = $_REQUEST['date_range'];
	
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

$event_type_ajax = '';
if(!empty($_REQUEST['event_type']))
{
	$event_type_ajax = $_REQUEST['event_type'];
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

$activity_ajax = '';
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

$time = time();
?>
<style>
.events_grid.events_list {
	display: inline-grid;
	width: 100%;
	gap: 6px !important;
	grid-template-columns: 100%;
}
</style>
<?php
$is_facility = FALSE;
if(isset($_COOKIE['user_role']) && $_COOKIE['user_role'] !== 'um_custom_role_2')
	$is_facility = TRUE;

$events = [];

if (!empty($posts)) {
    foreach ($posts as $post) {
        $start_date = get_post_meta($post->ID, 'etn_start_date', true); // e.g. 2025-10-01
        $end_date   = get_post_meta($post->ID, 'etn_end_date', true);   // e.g. 2025-10-01
        $start_time = get_post_meta($post->ID, 'etn_start_time', true); // e.g. 14:00
        $end_time   = get_post_meta($post->ID, 'etn_end_time', true);   // e.g. 16:30
		
		$start_time = normalize_time($start_time);
		$end_time   = normalize_time($end_time);
		
        // Build ISO datetime strings if times are present
        $start = $start_date . ($start_time ? 'T' . $start_time : '');
        $end   = $end_date   . ($end_time   ? 'T' . $end_time : '');




		//COLORS
			$availability = 'all';

			$is_subsciption = $is_lesson = FALSE;
			
			$categories = get_the_terms($post->ID, 'etn_category');
			$categories_arr = [];
			
			if($categories && !is_wp_error($categories))
			{
				foreach($categories as $category)
				{
					$categories_arr[] = rtrim($category->name, 's');
					
					if($category->name == 'Monthly Subscription')
						$is_subsciption = TRUE;
					elseif($category->name == 'Lessons')
						$is_lesson = TRUE;
				}
				
				$etn_categories = implode(', ', $categories_arr);
			}
			
			if($is_lesson)
			{
				$booked_charged_orders = get_booked_orders($post->ID, TRUE);
				$orders_1_to_1_count = has_orders_for_product($post->ID, $booked_charged_orders);
				
				if($orders_1_to_1_count)
				{
					$etn_categories = 'Private Lesson';
				}
				else
				{
					$custom_categories = get_post_meta($post->ID, 'etn_categories', TRUE);
					if($custom_categories)
						$etn_categories = $custom_categories;
				}
			}
			//vd($lesson_custom_data, '$lesson_custom_data');
			$tags = wp_get_post_terms($post->ID, 'etn_tags');
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
			
			$etn_tags = $etn_tags ? '<strong>Activity:</strong> ' . $etn_tags . '&nbsp;&nbsp;' : '';
			
			$age_group = get_post_meta($post->ID, 'player_age', true);
			if(!$age_group)
 				$age_group = get_post_meta($post->ID, 'age_group', true);
			
			$age_group = $age_group ? '<strong>Age group:</strong> ' . $age_group . '&nbsp;&nbsp;' : '';
			
			$level = get_post_meta($post->ID, 'player_level', true);
			if($level == '')
 				$level = get_post_meta($post->ID, 'level', TRUE);
			//vd($level, '$level');
			
			$level = $level ? '<strong>Level:</strong> ' . $level . '&nbsp;&nbsp;' : '';
            $tshirt_size = get_post_meta($post->ID, 'tshirt_size', true);
            $jersey_size = get_post_meta($post->ID, 'jersey_size', true);
            $pants_size = get_post_meta($post->ID, 'pants_size', true);
            $shorts_size = get_post_meta($post->ID, 'shorts_size', true);
			$room_area = get_post_meta($post->ID, 'room_area', true);
			
			$avaiilable_tickets = 0;
			$price = '';
			
			$event_attendees = get_event_attendees($post->ID, $is_lesson);
			
			//vd(count($event_attendees), 'count($event_attendees)');
			
			if($event_attendees && $is_facility)
			{
				$toggle_class = 'toggle_event_attendies';
				$toggle_js = ' onclick="javascript:toggle_event_attendies(' . $post->ID . ')"';
			}
			else
			{
				$toggle_class = '';
				$toggle_js = '';
			}
			
			if($is_subsciption)
			{
				$product = wc_get_product($post->ID);
				if($product)
				{
					if($product->managing_stock())
						$avaiilable_tickets = $product->get_stock_quantity();
					else
						$avaiilable_tickets = 'unlimited';
				}
				
				$price_value = get_subscription_price_per_month($post->ID);
				if($price_value)
					$price = '<span class="events_page_price event_price">$' . $price_value . '</span>';
				
				$avaiilable = $avaiilable_tickets;
			}
			elseif($is_lesson)
			{
				$etn_total_avaiilable_tickets = get_post_meta($post->ID, 'etn_total_avaiilable_tickets', true);
				
				$avaiilable_tickets = 0;
				$booked_seats = get_booked_seats($post->ID);
				
				$booked_charged_orders = get_booked_orders($post->ID, TRUE);
				$orders_count = has_orders_for_product($post->ID, $booked_charged_orders);
				
				if(!$orders_count)
				{
					if($etn_total_avaiilable_tickets) 
					{
						$avaiilable_tickets = $etn_total_avaiilable_tickets - $booked_seats;
						if($avaiilable_tickets < 0)
							$avaiilable_tickets = 0;
					}
				}
				
				$price = '<span class="events_page_price lesson_price">' . wrap_prices_with_span(get_post_meta($post->ID, 'lesson_price', true)) . '</span>';
				
				if($is_parent)
					$avaiilable = $avaiilable_tickets . ' remaining';
				else
					$avaiilable = $booked_seats . '/' . $etn_total_avaiilable_tickets;
				
				if($avaiilable_tickets <= 0)
				{
					$color_class = ' row-red';
					$availability = 'none';
				}
				elseif($avaiilable_tickets < $etn_total_avaiilable_tickets)
				{
					$color_class = ' row-yellow';
					$availability = 'limited';
				}
				else
				{
					$color_class = ' row-green';
					$availability = 'all';
				}
			}
			else
			{
	            $etn_total_avaiilable_tickets = get_post_meta($post->ID, 'etn_total_avaiilable_tickets', true);
	            //!!!!!!!!!!!
				$etn_total_sold_tickets = count($event_attendees);//get_post_meta($post->ID, 'etn_total_sold_tickets', true);
	            //!!!!!!!!!!!
				if($etn_total_sold_tickets == '')
					$etn_total_sold_tickets = 0;
				if($etn_total_avaiilable_tickets)
		            $avaiilable_tickets = $etn_total_avaiilable_tickets - $etn_total_sold_tickets;
				
				$meta_key = 'etn_ticket_variations';
				$serialized_value = get_post_meta($post->ID, $meta_key, true);
				$ticket_variations = maybe_unserialize($serialized_value);
				if($ticket_variations && !empty($ticket_variations[0]['etn_ticket_price']))
					$price = '<span class="events_page_price event_price">$' . $ticket_variations[0]['etn_ticket_price'] . '</span>';
				
				if($is_parent)
					$avaiilable = $avaiilable_tickets . ' remaining';
				else
					$avaiilable = $etn_total_sold_tickets . '/' . $etn_total_avaiilable_tickets;
			}
			$event_location = '';
			//vd($price, '$price');
			
			$etn_location_arr = get_post_meta($post->ID, 'etn_location', true);
			if(!empty($etn_location_arr[0]))
			{
				$event_location_id = $etn_location_arr[0];
				if($event_location_id)
				{
					$term = get_term( $event_location_id );
					$event_location = remove_suffix_from_term_name($term->name);
				}
			}
		
		// /COLORS
		
        $events[] = [
            'id'    => $post->ID,
            'title' => get_the_title($post),
            'start' => $start,
            'end'   => $end,
            'url'   => get_permalink($post),
			'availability' => $availability,
        ];
    }
}
?>

<style>
.fc-day-today {
    background-color: rgba(23, 160, 178, 0.4) !important;
}

.event-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 6px;
    vertical-align: middle;
}

.fc-day.fc-past {
    background: #f0f0f0 !important;
    color: #999 !important;
}
.fc-event-title,
.fc-event-main {
    white-space: normal !important;
    overflow-wrap: anywhere;
}

</style>
<div id="calendar"></div>

<script>
console.log('ajax_user_name');
console.log(ajax_user_name);
console.log('ajax_current_user_id');
console.log(ajax_current_user_id);

function create_event(dateStr)
{
<?php
	$user_role = '';
	$user_roles = get_current_user_role();
	if($user_roles)
		$user_role = $user_roles[0];
	
	if(!empty($user_role))
	{
		if($user_role !== 'um_custom_role_2')
		{
?>
	show_system_message(
						'Create New Event?', 
						'',
						'success',
						'Yes',
						'/create-event/?date=' + dateStr, 
						'Cancel',
						''
	);
<?php
		}
	}
?>
}

function init() {

	function getUrlParam(name) {
		const url = new URL(window.location.href);
		return url.searchParams.get(name);
	}

	let dateRange = getUrlParam('date_range');
	let initialDate = null;

	if (dateRange && dateRange.length === 6) {
		let year  = dateRange.substring(0, 4);
		let month = dateRange.substring(4, 6);
		initialDate = `${year}-${month}-01`;
	}

	var events = <?php echo wp_json_encode($events); ?>;
	var calendarEl = document.getElementById('calendar');

	var calendar = new FullCalendar.Calendar(calendarEl, {
		initialView: 'dayGridMonth',
		initialDate: initialDate || new Date(),

		eventContent: function(info) {
			let color = '#ccc';

			if (info.event.extendedProps.availability === 'all') {
				color = '#8ce98c';
			} 
			else if (info.event.extendedProps.availability === 'limited') {
				color = '#ffd87a';
			} 
			else if (info.event.extendedProps.availability === 'none') {
				color = '#ffb3b3';
			}

			let wrapper = document.createElement('div');
			wrapper.innerHTML = `
				<span class="event-dot" style="background:${color}"></span>
				<span class="fc-event-title">${info.event.title}</span>
			`;

			return { domNodes: [wrapper] };
		},

		// ? FIRST PREPARATION STEP ?
		events: function(fetchInfo, successCallback, failureCallback) {
			let post_data = {
				mode: "calendar",
		
				// reuse ALL your existing filters
				current_user_id: ajax_current_user_id,
				paged: 1,
				posts_per_page: -1,
				status: '<?=$post_status?>',
				logged_user_id: '<?=$logged_user_id?>',
				upcoming: '<?=$upcoming_ajax?>',
				location: '<?=$location_ajax?>',
				date_range: '<?=$date_range_ajax?>',
				date_start: fetchInfo.startStr,
				date_end: fetchInfo.endStr,
				event_type: '<?=$event_type_ajax?>',
				activity: '<?=$activity_ajax?>',
				view_orders: '<?=$view_orders?>',
				user_name: ajax_user_name,
				user_role: '<?=$user_role?>',
				past: '<?=$past?>',
				time: '<?=$time?>',
				all: '<?=$all?>'
			};
		
			jQuery.ajax({
				type: "POST",
				url: "<?=$theme_uri?>/events-ajax.php",
				data: post_data,
				dataType: "json",
		
				success: function(response) {
					console.log('response');
					console.log(response);
					
					successCallback(response.events);  // FullCalendar expects array of events
				},
		
				error: function(err) {
					console.log(err);
					failureCallback(err);
				}
			});
		},

		dateClick: function(info) {
			create_event(info.dateStr);
		}
	});

	calendar.render();

	console.log('events');
	console.log(events);
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}
</script>