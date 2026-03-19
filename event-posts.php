<?php
	$upcoming = !empty($atts['upcoming']);
	$upcoming_class = $upcoming ? ' upcoming_event' : '';
	$clone_nonce = wp_create_nonce('clone_event_nonce');
	$archive_nonce = wp_create_nonce('archive_event_nonce');
	$return_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	
	if(isset($_COOKIE['user_role']) && $_COOKIE['user_role'] !== 'um_custom_role_2')
		$is_facility = TRUE;
?>
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
	/*display: flex;*/
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
p.need_to_cancel {
	width: 100%;
}

@media (min-width: 1001px) {
    p.need_to_cancel {
        width: 100%;
    }
}

.event_facility strong {
    color: var(--Gray-Palette-100, #6A7682);
    font-family: "DM Sans";
    font-size: 14px;
    font-style: normal;
    font-weight: 500;
    line-height: 140%;
}

.need_to_cancel, event_facility {
	margin-top: 16px;
	display: block !important;
}

<?php
if($upcoming)
{
?>
.events-page-wrapper .events_page_grid_element_footer {
    margin-top: 0!important;
}
<?php
}
?>

.events-page-wrapper .events_page_grid_element_footer span {
    position: relative;
    top: 6px;
}

.event_facility {
    display: block;
    margin-top: 20px;
}

.event_price_top_right {
    float: right;
    margin: 0;
    color: #fff;
    background: #148e9e;
    font-size: 14px;
    padding: 4px 6px 2px 6px;
    border-radius: 8px;
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
</script>
<?php
//vd($posts, '$posts');
?>
<?php
	//$lesson_custom_data = array();
	//$lesson_custom_data = get_group_lesson_custom_data();
	
	if($posts)
	{
		foreach ($posts as $post)
		{
			$color_class = ' row-white';
			$permalink = get_permalink($post->ID);
            $start_date = get_post_meta($post->ID, 'etn_start_date', true);
            $start_time = get_post_meta($post->ID, 'etn_start_time', true);
			$date_etn_start_timestamp = '';
			
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
			
			$age_group = get_post_meta($post->ID, 'player_age', true);
			if(!$age_group)
 				$age_group = get_post_meta($post->ID, 'age_group', true);
			
			$level = get_post_meta($post->ID, 'player_level', true);
			if(!$level)
 				$level = get_post_meta($post->ID, 'level', true);
			//vd($level, '$level');
			
            $tshirt_size = get_post_meta($post->ID, 'tshirt_size', true);
            $jersey_size = get_post_meta($post->ID, 'jersey_size', true);
            $pants_size = get_post_meta($post->ID, 'pants_size', true);
            $shorts_size = get_post_meta($post->ID, 'shorts_size', true);
			$room_area = get_post_meta($post->ID, 'room_area', true);
			
			$avaiilable_tickets = 0;
			$price_value = $price = '';
			
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
	            $etn_total_sold_tickets = get_post_meta($post->ID, 'etn_total_sold_tickets', true);
				if($etn_total_sold_tickets == '')
					$etn_total_sold_tickets = 0;
				if($etn_total_avaiilable_tickets)
		            $avaiilable_tickets = $etn_total_avaiilable_tickets - $etn_total_sold_tickets;
				
				$meta_key = 'etn_ticket_variations';
				$serialized_value = get_post_meta($post->ID, $meta_key, true);
				$ticket_variations = maybe_unserialize($serialized_value);
				if($ticket_variations && !empty($ticket_variations[0]['etn_ticket_price']))
				{
					$price = '<span class="events_page_price event_price">$' . $ticket_variations[0]['etn_ticket_price'] . '</span>';
					$price_value = '$' . $ticket_variations[0]['etn_ticket_price'];
				}
				
				if($ticket_variations && isset($ticket_variations[0]['etn_sold_tickets']))
				{
		            $avaiilable_tickets = $etn_total_avaiilable_tickets - $ticket_variations[0]['etn_sold_tickets'];
				}
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
			
			//$permalink = '/etn/' . $post->post_name;
			$title = $post->post_title;
			if($_SERVER['REMOTE_ADDR'] == '188.163.73.151' || $_SERVER['REMOTE_ADDR'] == '92.60.179.128')
				$title .= ' ' . $post->order_id . ' ' . $post->order_status;
?>
		<div class="events_page_grid_element<?=$upcoming_class?><?=$color_class?>" id="grid_element_<?=$post->ID?>">
			<div class="events_page_grid_element_header">
				<span class="events_page_date"><?=$formatted_date?></span>
<?php
			if($upcoming && !empty($post->next_payment))
			{
?>
				<span class="events_page_date next_payment"><?=$post->next_payment?></span>
<?php
			}
			
			if($upcoming && $is_lesson)
			{
?>
				<a class="invite_others" href="<?=$permalink?>#invite_others">Invite Others</a>
<?php
			}
			
			if(current_user_can('edit_post', $post->ID) || $post->post_author == $logged_user_id)
			{
?>
				<span class="event_menu" data-post_id="<?=$post->ID?>">...</span>
				<span class="events_page_grid_element_actions" onclick="edit_event_btn_click(<?=$post->ID?>)"></span>
<?php
			}
			elseif(!$upcoming && ($is_parent || $is_guest) && $price_value !== '')
			{
?>
				<span class="event_price_top_right"><?=$price_value?></span>
<?php
			}
?>
			</div>
			<div class="events_page_event_title"><a href="<?=$permalink?>"><?=$title?></a><?=$date_etn_start_timestamp?></div>
			<div class="event_properties">
<?php
			if(!$upcoming)
			{
?>
				<span class="event_property events_page_event_location">&nbsp;<?=$event_location?></span>
				<span class="event_property events_page_event_room"><?php if($room_area) { ?><strong>Room:&nbsp;</strong><?=$room_area?><?php } ?></span>
				<span class="event_property events_page_event_age_group"><?php if($age_group) { ?><strong>Age&nbsp;group:&nbsp;</strong><?=$age_group?><?php } ?></span>
				<span class="event_property events_page_event_activity"><?php if($etn_tags) { ?><strong>Activity:&nbsp;</strong><?=$etn_tags?><?php } ?></span>
				<span class="event_property events_page_event_level"><?php if($level) { ?><strong>Level:&nbsp;</strong><?=$level?><?php } ?></span>
				<span class="event_property events_page_event_type"><?php if($etn_categories) { ?><strong>Event&nbsp;Type:&nbsp;</strong><?=$etn_categories?><?php } ?></span>
<?php
			}
			else
			{
				$vendor_name = get_vendor_name($post->post_author);
				//vd($post->attendees, '$post->attendees');
?>
				<span class="event_property"><strong>Location:&nbsp;</strong><?=$event_location?></span>
				<span class="event_property events_page_event_room"><?php if($room_area) { ?><strong>Room:&nbsp;</strong><?=$room_area?><?php } ?></span>
				<span class="event_property events_page_event_age_group"><strong>Attendee:&nbsp;</strong><?=$post->attendees?></span>
<?php
			}
?>
			</div>
			<div class="events_page_grid_element_footer">
<?php
			if($upcoming)
			{
				$phone = get_user_meta($post->post_author, 'phone', TRUE);
				$formatted_phone = formatPhoneNumber($phone);
				
				//vd(formatPhoneNumber('0555555555'), '$formatted_phone');
				
				if($phone)
				{
?>

				<span class="need_to_cancel">Need to cancel? Contact the Facility: <a href="tel:<?=$phone?>"><?=$formatted_phone?></a></span>
				<span class="event_facility"><strong>facility:&nbsp;</strong> <?=$vendor_name?></span>
<?php
				}
			}
?>
<?php
			//vd($upcoming);
			if($upcoming)
			{
?>
				<!--div class="footer-properties">
					<span class="events_page_remaining_count"><?=$avaiilable_tickets?> remaining</span>
					<?=$price?>
				</div -->
<?php
			}
			else
			{
				$price = !empty($post->price) ? '<span class="events_page_price event_price">$' . $post->price . '</span>' : '';
?>
				<div class="footer-properties">
					<span class="events_page_remaining_count"><?=$avaiilable_tickets?> remaining</span>
				</div>
				<!-- div class="footer-properties">
					<?=$price?>
				</div -->
<?php
			}
?>
<?php
			if(!$upcoming)
			{
				if($logged_user_id && $view_orders && (current_user_can('edit_post', $post->ID) || $post->post_author == $logged_user_id))
				{
?>
				<a href="/orders/<?=$post->ID?>" class="etn-btn events_page_grid_element_button">View Orders</a>
<?php
				}
				else
				{
?>
				<a href="<?=$permalink?>" class="etn-btn events_page_grid_element_button">Pricing and Details</a>
<?php
				}
			}
?>
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
