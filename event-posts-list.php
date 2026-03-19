<?php
	$upcoming = !empty($atts['upcoming']);
	$upcoming_class = $upcoming ? ' upcoming_event' : '';
	$clone_nonce = wp_create_nonce('clone_event_nonce');
	$archive_nonce = wp_create_nonce('archive_event_nonce');
	$return_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	
	$is_parent = FALSE;
	
	if(isset($_COOKIE['user_role']))
	{ 
		if($_COOKIE['user_role'] !== 'um_custom_role_2')
			$is_facility = TRUE;
		else
			$is_parent = TRUE;
	}
	
	//vd($_COOKIE['page_view'], '$_COOKIE[\'page_view\']');
	//vd($_COOKIE['user_role'], '$_COOKIE[\'user_role\']');
?>
<style>
.address .pin{
  width: 1em; height: 1em;
  margin-right: .10em;
  fill: currentColor;
  vertical-align: -0.15em;
}
</style>
<style>
.events_grid.events_list {
	display: inline-grid;
	width: 100%;
	gap: 6px !important;
	grid-template-columns: 100%;
}
</style>
<style>
.popup_menu {
    display: none;
    position: absolute;
    background-color: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #ccc;
    z-index: 1000;
	border-radius: 12px;
}
.popup_option {
    padding: 10px 20px;
    cursor: pointer;
}
.popup_option:hover {
    background-color: #f0f0f0;
}
.upcoming_event span.events_page_date {
    color: initial;
}
.events-page-wrapper .upcoming_event .events_page_event_title {
    border-bottom: none;
}
.footer-properties {
	display: inline-block;
}
.footer-properties span {
	display: block;
}

.events_page_grid_element_footer {
	display: flex;
	justify-content: space-between;
	align-items: center;
}
.events_page_price {
	color: #148e9e;
}
.event_price {
	font-size: 22px;
	font-weight: bold;
	margin-top: 18px;
}
a.etn-btn.events_page_grid_element_button {
	/*margin-top: 20%;*/
}
.per_month {
	display: inline-block !important;
	font-size: initial;
	top: initial !important;
	margin-left: 6px;
	font-weight: 400;
}
.lesson_price_value {
	display: inline-block !important;
	top: initial !important;
	font-weight: bold;
}

.event-attendies{
	text-align: left;
	font-weight: normal;
	display:none;
}
</style>

  <style>
    .schedule {
      max-width: 1200px;
      margin: 0 auto;
      padding: 10px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .schedule-header {
      display: flex;
      font-weight: bold;
      background-color: #dee4eb;
      color: #333;
      padding: 10px;
      border-radius: 16px;
    }

    .schedule-header div {
      flex: 1;
      text-align: center;
    }

    .event-row {
      display: flex;
      align-items: center;
      padding: 15px 10px;
      border-bottom: 1px solid #ddd;
    }

    .event-date {
      flex: 1;
      text-align: center;
      font-weight: bold;
      font-size: 16px;
      color: #555;
	  text-transform:uppercase;
    }

    .event-date .event-time {
      font-weight: normal;
      font-size: 14px;
    }
	
    .event-details,
    .booked-seats {
      flex: 2;
      text-align: left;
    }

    .event-details a {
      text-decoration: none;
    }

	.event-type {
      flex: 1;
	}

    .event-type span {
      display: block;
      font-size: 14px;
      color: #666;
    }

    .event-details {
      font-size: 14px;
      color: #666;
    }

    .booked-seats {
      flex: 1;
      text-align: center;
      font-weight: bold;
      color: #333;
    }

    .action {
      flex: 1;
      text-align: center;
    }

    .action a {
      padding: 8px 15px;
      background-color: #f4f4f4;
      border: 1px solid #ccc;
      border-radius: 5px;
      color: #007bff;
      text-decoration: none;
      font-size: 14px;
    }

    .no-border {
      border-bottom: none;
    }
	
	.event-row {
	    border-radius: 16px;
	}
	
    .date-separator {
      display: flex;
      align-items: center;
      text-align: center;
	  font-size:20px;
      margin: 20px 0;
    }

    .date-separator::before,
    .date-separator::after {
      content: "";
      flex: 1;
      border-top: 1px solid #b0bec5; /* Adjust color to match your design */
      margin: 0 10px;
    }

    .date-separator span {
      font-weight: bold;
      color: #333; /* Adjust text color */
      white-space: nowrap;
    }


button.button-action {
    border-radius: 12px;
    border: 1px solid var(--Accent-color-100, #17A0B2);
    color: var(--Accent-color-100, #17A0B2);
    font-family: "DM Sans";
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 140%;
    background: white;
    padding: 12px 18px;
    margin-right: 4px;
    cursor: pointer;
}
.table_action {
    background: none !important;
}
<?php
if(isset($_COOKIE['user_role']) && $_COOKIE['user_role'] !== 'um_custom_role_2')
{
?>
.toggle_event_attendies::after {
    content: "\25BC"; /* Unicode character for a down arrow */
    font-size: 14px; /* Adjust font size if needed */
    margin-left: 5px; /* Add space between the text and the arrow */
    color: inherit; /* Use the same color as the parent element */
}

.toggle_event_attendies_ex::after {
    content: "\25B2"; /* Unicode character for a down arrow */
    font-size: 14px; /* Adjust font size if needed */
    margin-left: 5px; /* Add space between the text and the arrow */
    color: inherit; /* Use the same color as the parent element */
}
<?php
}
?>

  </style>

<style>
/* General styles for alignment */
.schedule-header div, .desktop_element.event-row div {
  flex: 1;
}

.event-row .event-date {
  flex: 1;
  text-align: center;
}

.event-row .event-type, 
.event-row .event-details, 
.event-row .booked-seats, 
.event-row .action {
  flex: 1; /* Each block takes equal space */
}

.schedule-header div:nth-child(1) {
  flex-basis: 20%;
}
.schedule-header div:nth-child(2) {
  flex-basis: 15%;
}
.schedule-header div:nth-child(3) {
  flex-basis: 35%;
}
.schedule-header div:nth-child(4) {
  flex-basis: 10%;
}
.schedule-header div:nth-child(5) {
  flex-basis: 20%;
}

.desktop_element.event-row div:nth-child(1) {
  flex-basis: 20%;
}
.desktop_element.event-row div:nth-child(2) {
  flex-basis: 15%;
}
.desktop_element.event-row div:nth-child(3) {
  flex-basis: 35%;
}
.desktop_element.event-row div:nth-child(4) {
  flex-basis: 10%;
}
.desktop_element.event-row div:nth-child(5) {
  flex-basis: 20%;
}
</style>
  
  
<script>
function archive_event(post_id)
{
	if(confirm('Are you sure you want to archive this event?'))
	{
		window.location.href = '/archive-event/' + post_id + '/?_wpnonce=<?=$archive_nonce?>&return_url=<?=$return_url?>';
	}
}

    document.addEventListener('click', function(event) {
        const popupMenu = document.getElementById('popup_menu');

        if (event.target.classList.contains('event_menu')) {
            //const postID = event.target.getAttribute('onclick').match(/\d+/)[0];
			var post_id = event.target.dataset.post_id;
            showPopupMenu(event, post_id);
        } else if (!popupMenu.contains(event.target)) {
            popupMenu.style.display = 'none';
        }
    });

    function showPopupMenu(event, postID) {
		console.log('event.pageX');
		console.log(event.pageX);
		console.log('event.pageX');
		console.log(event.pageX);
        const popupMenu = document.getElementById('popup_menu');
		
		if(window.mobileCheck())
		{
	        popupMenu.style.left = `${event.pageX}px`;
	        popupMenu.style.top = `${event.pageY - 110}px`;
		}
		else
		{
	        popupMenu.style.left = `${event.pageX - 330}px`;
	        popupMenu.style.top = `${event.pageY - 110}px`;
		}
		
        popupMenu.style.display = 'block';
        popupMenu.dataset.postId = postID;
    }

    function duplicate_event() {
        const popupMenu = document.getElementById('popup_menu');
        const postID = popupMenu.dataset.postId;
        //alert(`Duplicate event with ID: ${postID}`);
        popupMenu.style.display = 'none';
		window.location.href = '/clone-event/' + postID + '/?_wpnonce=<?=$clone_nonce?>&return_url=<?=$return_url?>';
    }

    function archive_event() {
        const popupMenu = document.getElementById('popup_menu');
        const postID = popupMenu.dataset.postId;
        //alert(`Archive event with ID: ${postID}`);
        popupMenu.style.display = 'none';
		if(confirm('Are you sure you want to archive this event?'))
		{
			window.location.href = '/archive-event/' + postID + '/?_wpnonce=<?=$archive_nonce?>&return_url=<?=$return_url?>';
		}
    }


</script>
<script>
function menu_click(event_id)
{
	window.location.href = '/create-event/?event=' + event_id;
}

function toggle_event_attendies(event_id)
{
	console.log('event_id');
	console.log(event_id);
	var attendies = document.getElementById('attendies_' + event_id);
	console.log('attendies.style.display');
	console.log(attendies.style.display);
	var toggle = document.getElementById('toggle_' + event_id);
	
	if(attendies.style.display !== 'block')
	{
		attendies.style.display = 'block';
		toggle.classList.add('toggle_event_attendies_ex');
	}
	else
	{
		attendies.style.display = 'none';
		toggle.classList.remove('toggle_event_attendies_ex');
	}
}
</script>
<?php
//vd($posts, '$posts');
?>
<?php
	//$lesson_custom_data = array();
	//$lesson_custom_data = get_group_lesson_custom_data();
//vd($lesson_custom_data, '$lesson_custom_data');






	if($posts)
	{
		foreach ($posts as $post)
		{
			$color_class = ' row-white';
			
			$permalink = get_permalink($post->ID);
            $start_date = get_post_meta($post->ID, 'etn_start_date', true);
            $start_time = get_post_meta($post->ID, 'etn_start_time', true);
            if(!empty($start_date) && !empty($start_time))
			{
                $datetime_str = $start_date . ' ' . $start_time;
                $timestamp = strtotime($datetime_str);
				
				$month = date('F Y', $timestamp);
				$day = date('D - M d, Y', $timestamp);
				
				if(!isset($months[$month]))
				{
					$months[$month] = 1;
?>
	<div class="date-separator">
	  <span><?=$month?></span>
	</div>
	
<?php
				}
				
				if(!isset($days[$day]))
				{
					$days[$day] = 1;
?>
	
	<div class="schedule-header">
      <div><?=$day?></div>
      <div class="desktop_element" style="text-align: left;">Event Type</div>
      <div class="desktop_element" style="text-align: left;">Event Details</div>
      <div class="desktop_element">Booked Seats</div>
      <div class="desktop_element">Action</div>
    </div>
<?php
				}
			}
			
            $end_date = get_post_meta($post->ID, 'etn_end_date', true);
            $end_time = get_post_meta($post->ID, 'etn_end_time', true);
			$date_etn_end_timestamp = '';
			
            $etn_start_timestamp = intval(get_post_meta($post->ID, 'etn_start_timestamp', true));
			if($_SERVER['REMOTE_ADDR'] == '92.60.179.128')
			//if(0)
			{
				$date_etn_start_timestamp = '<br>get_post_meta ' . $post->ID . ' ' . $etn_start_timestamp . ' ' . date('Y-m-d H:i', $etn_start_timestamp) . '<br>';
	            $start_date = get_post_meta($post->ID, 'etn_start_date', true);
	            $start_time = get_post_meta($post->ID, 'etn_start_time', true);
				
	            if(!empty($start_date) && !empty($start_time))
				{
	                $datetime_str = $start_date . ' ' . $start_time;
	                $timestamp = strtotime($datetime_str);
					//update_post_meta($post->ID, 'etn_start_timestamp', $timestamp);
					//$date_etn_start_timestamp .= ' start_date ' . $start_date . '<br>';
					//$date_etn_start_timestamp .= ' start_time ' . $start_time . '<br>';
					$date_etn_start_timestamp .= ' calculate ' . $timestamp . ' ' . date('Y-m-d H:i', $timestamp) . '<br>';
	            }
			}
			
			$timestamp = strtotime($start_date . ' ' . $start_time);
			$formatted_date = date('D M, d Y h:i A', $timestamp);
			
			$week_day = date('D', $timestamp);
			$formatted_day = date('d', $timestamp);
			
			$timestamp_end = strtotime($end_date . ' ' . $end_time);
			$formatted_time = date('h:i A', $timestamp) . ' - ' . date('h:i A', $timestamp_end);
			
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
			vd($level, '$level');
			
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
					$color_class = ' row-red';
				elseif($avaiilable_tickets < $etn_total_avaiilable_tickets)
					$color_class = ' row-yellow';
				else
					$color_class = ' row-green';
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
			
			$event_location = $address = '';
			
			$etn_location_arr = get_post_meta($post->ID, 'etn_location', true);
			if(!empty($etn_location_arr[0]))
			{
				$event_location_id = $etn_location_arr[0];
				if($event_location_id)
				{
					$term = get_term( $event_location_id );
					$event_location = remove_suffix_from_term_name($term->name);
					
					$address_meta = get_term_meta($event_location_id, 'address', FALSE);
					$term_location = get_term( $event_location_id );
					$location_name = $term_location ? $term_location->name : '';
					
					if($address_meta)
					foreach($address_meta as $address_part)
					{
						if(strlen($address) < strlen($address_part))
							$address = $address_part;
					}
				}
			}
			
			if($location_name)
				$location_name = '<br><span class="address">
				  <svg class="pin" viewBox="0 0 24 24" aria-hidden="true">
				    <path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/>
				  </svg><strong>' . $location_name . '</strong>
				</span>';
			if($address)
				$address = '<br>' . $address;
			
			//$permalink = '/etn/' . $post->post_name;
			$title = $post->post_title;
			if($_SERVER['REMOTE_ADDR'] == '188.163.73.151' || $_SERVER['REMOTE_ADDR'] == '92.60.179.128')
				$title .= ' ' . $post->order_id . ' ' . $post->order_status;
			//vd($permalink, $post->ID . ' ' . $post->post_title);
			//'yoda-classes'
?>

    <div class="desktop_element event-row<?=$color_class?>" class="events_page_grid_element<?=$upcoming_class?>" id="grid_element_<?=$post->ID?>">
      <div class="event-date">
        <?=$week_day?><br><?=$formatted_day?>
        <div class="event-time"><?=$formatted_time?></div>
      </div>
      <div class="event-type">
        <span><?=$etn_categories?></span>
      </div>
      <div class="event-details">
        <strong><a href="<?=$permalink?>"><?=$title?></a></strong><br>
        <?=$age_group?><?=$etn_tags?><?=$level?><?=$location_name?><?=$address?>
      </div>
      <div class="booked-seats">
<?php
?>
	  	<span id="toggle_<?=$post->ID?>" class="<?=$toggle_class?>"<?=$toggle_js?>><?=$avaiilable?></span>
		<div class="event-attendies" id="attendies_<?=$post->ID?>">
<?php
			foreach($event_attendees as $event_attendee)
				echo $event_attendee . '<br>';
?>
		</div>
	  </div>
      <div class="action">
<?php
			if(!$upcoming)
			{
				if($logged_user_id && $view_orders && (current_user_can('edit_post', $post->ID) || $post->post_author == $logged_user_id))
				{
?>
		<button onclick="window.location.href='/orders/<?=$post->ID?>'" class="table_action button-add event-button-add button-action">View Orders</button>
		<button onclick="window.location.href='/clone-event/<?=$post->ID?>/?_wpnonce=<?=$clone_nonce?>'" class="table_action button-add event-button-add button-action" style="margin-top:8px;">Duplicate</button>
<?php
				}
				else
				{
?>
		<button onclick="window.location.href='<?=$permalink?>'" class="table_action button-add event-button-add button-action">Pricing and Details</button>
<?php
				}
			}
?>
	  </div>
    </div>

	<div class="mobile_element event-row<?=$color_class?>">
		<div class="card-header">
			<span class="events_page_date"><?=$week_day?> - <?=$formatted_day?>, <?=$formatted_time?></span>
		</div>
		<div class="card-title"><strong><a href="<?=$permalink?>"><?=$title?></a></strong></div>
		<div class="card-content">
			<p><span class="event_property events_page_event_location"><?=$event_location?></span></p>
			<p><?=$age_group?></p>
			<p><?=$level?></p>
			<p><?=$etn_tags?></p>
			<p><strong>Event Type:</strong> <?=$etn_categories?></p>
			<p><strong>Booked seats:</strong> <?=$avaiilable?></p>
		</div>
		<div class="action">
			<button onclick="window.location.href='<?=$permalink?>'" class="table_action button-add event-button-add button-action">Pricing and Details</button>
		</div>
	</div>	
	
<?php
		}
	}
	else
	{
		if($upcoming)
		{
?>
			<p>You do not have any upcoming events yet. <a href="/">Search Events</a></p>
<?php
		}
		else
		{
?>
			<p>No events found</p>
<?php
		}
	}
?>
