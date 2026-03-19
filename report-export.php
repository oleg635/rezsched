<?php
if($_SERVER['REMOTE_ADDR'] == '188.163.75.165')
{
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
}

$current_user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : get_current_user_id();
$cache_dir = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/json-cached-data';

if(1
	&& empty($_REQUEST['psd']) 
	&& empty($_REQUEST['ped']) 
	&& empty($_REQUEST['esd']) 
	&& empty($_REQUEST['eed']) 
	&& empty($_REQUEST['timeframe']) 
)
{
	$_REQUEST['timeframe'] = '30';
}

$timeframe = !empty($_REQUEST['timeframe']) ? $_REQUEST['timeframe'] : '';

$current_user_dir_ok = true;
$cache_current_user_dir = $cache_dir . '/' . $current_user_id;
if ( ! is_dir( $cache_current_user_dir ) ) {
	if ( ! wp_mkdir_p( $cache_current_user_dir ) ) {
		error_log( "Failed to create cache dir: $dir" );
		$current_user_dir_ok = false;
	}
}

$ob_start = FALSE;
?>
<style>
#first_name_0, #last_name_0 {
	border: 1px solid #ddd !important;
	border-radius: 15px !important;
	height: 41px !important;
	background: white;
}

.player_select {
	display: block;
}

label.dynamically_added {
	margin-top: 12px;
}

table.order-events-table button.button-action.delete-added-user {
	border: 1px solid #c2451e;
	color: #c2451e;
}
table.order-events-table button.button-action.delete-added-user:hover {
	border: #fff;
	color: #fff;
	background: #c2451e;
}
.search_facility {
    position: relative;
    display: inline-block;
}

.search-icon {
    position: absolute;
    left: 24px;
    top: 50%;
    transform: translateY(-50%);
    width: 24px;
    height: 24px;
    background-image: url('/wp-content/themes/greenshift-child/img/Search_Magnifying_Glass.svg');
    background-size: contain;
    background-repeat: no-repeat;
    pointer-events: none;
}

.search_facility_input, .suggestion-item {
    padding-left: 38px;
}

.suggestion-item .search-icon {
    left: 10px;
}
.order-container .order-events-actions button.button-add {
	display: initial;
}
#inner_frame_wrap { 
	padding: 0; 
	overflow: hidden; 
	border: none;
	position: relative;
	width: 320px; 
	width: 100%;
	height: 240px;
	margin-top:12px;
	/*margin-bottom:12px;*/
}
#inner_frame {
<?php
//if($_SERVER['REMOTE_ADDR'] == '188.163.75.165')
if(1)
{
?>
	opacity: 0;
<?php
}
?>
	width: 100%;
	height: 100%;
	border: none;
	border: 1px solid #aaa;
	transform-origin: 0 0;
}
.arrow-right {
	/*background-image: url('/wp-content/themes/greenshift-child/img/arrow-right.svg');*/
	width:8px;
	height:12px;
	display: inline-block;
	float: left;
	margin: 6px 12px 0 0;
}
.arrow-down {
	background-image: url('/wp-content/themes/greenshift-child/img/arrow-down.png');
	width:12px;
	height:7px;
	display: inline-block;
	float: left;
	margin: 6px 12px 0 0;
}
.arrow-up {
	background-image: url('/wp-content/themes/greenshift-child/img/arrow-up.png');
	width:12px;
	height:7px;
	display: inline-block;
	float: left;
	margin: 6px 12px 0 0;
}
.search_facility_input {
    padding-left: 12px !important;
	display: none;
}

#suggestions {
    border: 1px solid #ccc;
    display: none;
    position: absolute;
    max-height: 200px;
    overflow-y: auto;
    background: white;
    z-index: 1000;
}
.suggestion-item {
    padding: 10px!important;
    cursor: pointer;
    border-left: 4px solid white;
}
.suggestion-item:hover {
    background-color: #f0f0f0;
}

div#suggestions {
    width: initial !important;
    padding: initial !important;
    width: auto;
}

.submit_refund {
	padding: 8px;
    margin-top: 8px;
}
strong a {
	font-weight:bold;
}

.dollar-input-wrapper {
    position: relative;
}
.dollar-input-wrapper::before {
    content: '$';
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
}
.dollar-input-wrapper.hide-dollar::before {
    display: none;
}
.dollar-input-wrapper input {
    padding-left: 20px;
}
.has_orders {
	
}
.has_orders::after {
  content: '\2714';
  color: green; /* Set the color of the checkmark */
  margin-left: 8px; /* Add spacing between the element and the checkmark */
  font-size: 16px; /* Adjust the size of the checkmark */
  display: inline-block; /* Ensure it behaves like text */
}
.suggestion-item:hover {
    background-color: #f0f0f0;
}
</style>

<?php
    $atts = shortcode_atts(array(
		'order_events_count' => -1,
    ), $atts);
?>
<style>
<?php
if(!$event_name)//if the page is not for single event orders
{
?>
.order_child {
	display: none;
}
<?php
}
else
{
?>
.arrow-down, .arrow-up {
	/*display: none;*/
}
<?php
}
?>

table.order-events-table .etn-btn {
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
</style>
<?php
include('system-message.php');

if(!function_exists('etn_event_is_within_request_range'))
{
function etn_event_is_within_request_range($post_id) {
    // Determine range start and end
	/*
    if ($timeframe && in_array($_REQUEST['timeframe'], ['30', '365']))
	{
        $range_start = strtotime('-' . intval($_REQUEST['timeframe']) . ' days');
        $range_end   = time(); // now
    }
	else
	*/
	{
        $range_start = !empty($_REQUEST['esd']) ? strtotime($_REQUEST['esd']) : strtotime('01/01/1970');
        $range_end   = !empty($_REQUEST['eed']) ? strtotime($_REQUEST['eed']) : strtotime('01/01/3000');
    }

    // Get event start/end
    $event_start = strtotime(get_post_meta($post_id, 'etn_start_date', true));
    $event_end_raw = get_post_meta($post_id, 'etn_end_date', true);
    $event_end = $event_end_raw ? strtotime($event_end_raw) : $event_start;

    // Invalid event dates
    if (!$event_start || !$event_end) {
        return false;
    }

    // Check if event range is fully inside the selected timeframe
    return ($event_start >= $range_start && $event_end <= $range_end);
}
}

$events_by_activity = $revenues = [];
$activity_types = get_all_activity_types();
$event_types = get_all_event_types();
$event_types[26] = 'Monthly Subscription';

if(!function_exists('etn_get_events_activity_data'))
{
function etn_get_events_activity_data($current_user_id, $activity_types, $event_types, &$events_by_activity, &$revenues)
{
	$stripe_url = $payment_intent_id = '';
	
   $args = [
		//'status' => ['wc-completed', 'wc-refunded', 'wc-pending'],
		'status' => ['wc-completed', 'wc-refunded'],
		'limit'  => -1,
		'offset'  => 0,
		'orderby' => 'ID',
		'order' => 'DESC',
		'meta_query' => [
			[
				'key'     => '_dokan_vendor_id',
				'value'   => $current_user_id,
				'compare' => '=',
			],
		],
	];
	
	if ($timeframe && in_array($timeframe, ['30', '365']))
	{
		$days = intval($_REQUEST['timeframe']);
		
		$date_query = [
			'after'     => date('Y-m-d 00:00:00', strtotime("-{$days} days")),
			'before'    => date('Y-m-d 23:59:59'), // now
			'inclusive' => true,
		];
		
		$args['date_query'] = [$date_query];
	}
	elseif (!empty($_REQUEST['psd']) || !empty($_REQUEST['ped'])) {
		$date_query = [];
	
		if (!empty($_REQUEST['psd'])) {
			$date_query['after'] = sanitize_text_field($_REQUEST['psd']);
		}
	
		if (!empty($_REQUEST['ped'])) {
			$date_query['before'] = sanitize_text_field($_REQUEST['ped']) . ' 23:59:59';
		}
	
		$date_query['inclusive'] = true;
	
		$args['date_query'] = [$date_query];
	}
	else
	{
		$days = 30;
		
		$date_query = [
			'after'     => date('Y-m-d 00:00:00', strtotime("-{$days} days")),
			'before'    => date('Y-m-d 23:59:59'), // now
			'inclusive' => true,
		];
		
		$args['date_query'] = [$date_query];
	}
	
	//vd($args, '$args');
	
	$orders = wc_get_orders($args);
	//vd($orders, '$orders');
	//$orders_count = $orders ? count($orders) : 0;
	
	foreach($orders as $order)
	{
		$order_date_time = $order->get_date_created()->date('n/j/y');
		//vd($order->ID, '$order->ID');
		$items = $order->get_items();
		//vd(count($items), 'count($items)');
		$customer_id = $order->get_customer_id();
		//vd($customer_id, '$customer_id');
		$customer_link = $customer_name = $customer_display_name = '';
		
		if($customer_id)
		{
		    $customer = get_userdata($customer_id);
		    if($customer)
			{
				$customer_display_name = $customer->display_name;
				$customer_link = '<a href="/edit-profile/' . $customer->ID . '/">' . $customer->display_name . '</a>';
			}
		}
		
		$order_status = $display_status = wph_get_order_status($order);
		//data-post_title
		$stripe_url = $payment_intent_id = '';
		
		if($display_status == 'Pending')
		{
			
		}
		else
		{
			$dokan_vendor_id = $order->get_meta('_dokan_vendor_id');
			if($dokan_vendor_id)
			{
				$vendor_id = get_user_meta($dokan_vendor_id, 'dokan_connected_vendor_id', true);
				if($vendor_id)
				{
					$charge_id_meta_key = '_dokan_stripe_charge_id_' . $dokan_vendor_id;
					$charge_id = $order->get_meta($charge_id_meta_key);
					
					if($charge_id)
					{
					    $stripe_url = "https://dashboard.stripe.com/{$vendor_id}/payments/{$charge_id}";
					    //echo $stripe_url;
					}
				}
			}
			
			if($stripe_url)
			{
				$display_status = '<a target="_blank" href="' . $stripe_url . '">' . $display_status . '</a>';
			}
			else
			{
				$payment_intent_id = $order->get_meta('_stripe_intent_id');
				//vd($payment_intent_id, '$payment_intent_id');
				if($payment_intent_id)
				{
					$display_status = '<a target="_blank" href="https://dashboard.stripe.com/payments/' . $payment_intent_id . '">' . $display_status . '</a>';
				    $stripe_url = 'https://dashboard.stripe.com/payments/' . $payment_intent_id;
				}
			}
			
		}
		
		//$orders[$order->ID]
		$gross_total	= (float)$order->get_total();           // e.g. $100.00
		$total_refund	= $order->get_total_refunded();  // e.g. $20.00
		$revenue		= $gross_total - $total_refund;  // = $80.00
		
		$revenues[$order->ID] = array('gross_total' => $gross_total, 'total_refund' => $total_refund, 'revenue' => $revenue);
		
		$products = get_order_products($order->ID);
		
		//vd($products, '$products');
		
		if($products)
		{
			foreach($products as $product)
			{
				$event_name = $event_category = $event_datetime = $post_title = $product_name = '';
				$event = get_post($product['product_id']);
				//vd($event, '$event');
				
				$event_data = [];
				
				$event_activity = 0;
				if($event)
				{
					//if(!empty($_REQUEST['esd']) || !empty($_REQUEST['eed']) || $timeframe)
					if(!empty($_REQUEST['esd']) || !empty($_REQUEST['eed']))
					{
						$within = etn_event_is_within_request_range($event->ID);
						//vd($within, '$within');
						if(!$within)
							continue;
					}
					
					$event_datetime = format_event_datetime($event->ID, 'n/j/y');
					$event->datetime = $event_datetime;
					$event->order_id = $order->ID;
					$event->order = $order;
					$event->display_status = $display_status;
					$event->order_status = strtolower(str_replace(['(', ')', ' '], ['', '', '-'], $order_status));
					$event->order_datetime = $order_date_time;
					$event->customer_id = $customer_id;
					$event->customer_link = $customer_link;
					$event->stripe_url = $stripe_url;
					$event->payment_intent_id = $payment_intent_id;
					$attendee_names_html = get_order_attendees_for($order->ID, $product['product_id'], $customer_display_name);
					$event->attendee_names_html = $attendee_names_html;
					
					//$category = get_post_term($event->ID, 'etn_category');
					$categories = wp_get_post_terms($event->ID, 'etn_category');
					if(!empty($categories[0]))
						$event_category = $categories[0]->term_id;
					$event->category = $event_category && !empty($event_types[$event_category]) ? $event_types[$event_category] : '';
					
					//vd($event_category, '$event_category');
					
					$tags = wp_get_post_terms($event->ID, 'etn_tags');
					if(!empty($tags[0]))
					{
						$event_activity = $tags[0]->term_id;
						$event->activity = !empty($activity_types[$event_activity]) ? $activity_types[$event_activity] : '';
						if($event_activity && $event_category)
						{
							$events_by_activity[$event->activity][$event->category][] = $event;
						}
					}
				}
				else
				{
					$event_name = 'event deleted';
					//event deleted
				}
				
				//vd($event_activity, $post_title);
				
				if($display_status == 'Pending')
				{
					//$display_status = '<button data-event_name="' . addslashes($post_title) . '" data-product_id="' . $product['product_id'] . '" data-user_id="' . $customer_id . '" data-order_id="' . $order->ID . '" onclick="resend_payment_link_ajax(this)" id="resend_1" class="button-action charge-added-user" value="Resend">Resend</button>';
				}
			}
		}
		else
		{
			//product deleted
		}
	}
	
	if($timeframe && $timeframe == '365')
	{
		$cache_dir = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/json-cached-data';
		$events_by_activity_file = $cache_dir . '/events_by_activity_' . $current_user_id . '_' . date('Y-m-d') . '.json';
		$revenues_file = $cache_dir . '/revenues_' . $current_user_id . '_' . date('Y-m-d') . '.json';
	    file_put_contents($events_by_activity_file, serialize($events_by_activity));
	    file_put_contents($revenues_file, serialize($revenues));
		//serialize($posts)
	}
}
}

//function get_json_data($vendor_id, &$birth_years, &$genders, &$attendee_data_json, &$selected_values)

if($timeframe && $timeframe == '365')
{
	$cache_dir = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/json-cached-data';
	$events_by_activity_file = $cache_dir . '/events_by_activity_' . $current_user_id . '_' . date('Y-m-d') . '.json';
	$revenues_file = $cache_dir . '/revenues_' . $current_user_id . '_' . date('Y-m-d') . '.json';
	//vd(is_file($events_by_activity_file), $events_by_activity_file);
	//vd(is_file($revenues_file), $revenues_file);
	
	if(!file_exists($events_by_activity_file) || !file_exists($revenues_file))
	{
		etn_get_events_activity_data($current_user_id, $activity_types, $event_types, $events_by_activity, $revenues);
	}
	else
	{
	    $file_get_contents = file_get_contents($events_by_activity_file);
		//vd($file_get_contents, '$file_get_contents');
	    $events_by_activity = unserialize($file_get_contents);
	    $file_get_contents = file_get_contents($revenues_file);
	    $revenues = unserialize($file_get_contents);
	}
}
else
{
	etn_get_events_activity_data($current_user_id, $activity_types, $event_types, $events_by_activity, $revenues);
}
//vd($events_by_activity, '$events_by_activity');
//vd($revenues, '$revenues');

//foreach
?>
<style>
table.report-table thead {
	background:#dee4eb;
}

table.report-table .activity {
	background:#c3ccd6;
}

table.report-table .activity td {
	text-align: left;
	padding: 18px 24px;
	font-weight: 800;
}

table.report-table .lesson-type {
	background:#c3ccd6;
}

table.report-table .lesson-type td {
	text-align: left;
	padding: 18px 40px;
	font-weight: 800;
}

table.report-table tr th {
	border-radius: 20px 20px 0px 0px!important;
	text-align: left;
}

table.report-table .event-type td {
	text-align: left;
	padding: 18px 40px;
}

table.report-table .event-data-tr {
    border-bottom: 2px solid #dee4eb;
}

table.report-table .event-data-td {
    border: none;
}

table.report-table .event-data-sub-head td {
	font-weight: 600;
	font-size: 14px;
}

table.report-table tr td a {
    text-decoration: underline;
}

table.report-table tr td a:hover {
    text-decoration-line: none !important;
}

table.report-table tr td.event-data-status span {
	padding: 0;
	border-radius: 12px;
	min-width: 90px;
	display: inline-block;
}

table.report-table tr td.event-data-status span a {
	font-size: 12px;
}

table.report-table tr td.event-data-status span.status-paid {
	background: #cbf6b4;
}

table.report-table tr.status-refund-full td,
table.report-table tr.status-partial-refund td,
table.report-table tr.status-refund-full td a,
table.report-table tr.status-partial-refund td a {
	color: #f73206;
}

table.report-table tr td.event-data-status span.status-refund-full,
table.report-table tr td.event-data-status span.status-partial-refund {
	background: #f99d80;
}

table.report-table tr td.event-data-status span.status-refund-full a,
table.report-table tr td.event-data-status span.status-partial-refund a {
	color: initial;
}
/*  */

.spinner {
	display: inline-block;
	width: 16px;
	height: 16px;
	border: 2px solid #ccc;
	border-top: 2px solid #0073aa;
	border-radius: 50%;
	animation: spin 0.6s linear infinite;
	margin-right: 6px;
	margin-left: 12px;
	vertical-align: middle;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>

<script>
async function fetchCacheByFullPath(fullPath) {
  const url = `/wp-json/rez/v1/cache?cache_file=${encodeURIComponent(fullPath)}`;
  const res = await fetch(url, { headers: { 'X-WP-Nonce': window.wpApiSettings?.nonce || '' } });
  const data = await res.json();
  if (!res.ok || !data.ok) throw new Error(data?.message || 'Not found');
  return data.content; // HTML fragment
}

async function expand_children(parent_id)
{
	var parent_element = document.getElementById(parent_id);
	
	var spinner = '<span id="loading"><span class="spinner"></span>&nbsp;&nbsp;</span>';
	var inner_td = parent_element.querySelector('.heading_td');
	if(inner_td)
	{
		//console.log('inner_td');
		//console.log(inner_td);
		inner_td.innerHTML += spinner;
	}
	
	console.log('children class');
	console.log('child-' + parent_id);
	var order_children = document.querySelectorAll('.child-' + parent_id);
	console.log('order_children');
	console.log(order_children);
	
	var arrow = document.getElementById('arrow_' + parent_id);
	if(arrow.classList.contains('arrow-up'))
	{
		arrow.classList.remove('arrow-up');
		arrow.classList.add('arrow-down');
		for(var i = 0; i < order_children.length; i++)
		{
			order_children[i].style.display = 'none';
			//order_children[i].click();
		}
	}
	else
	{
		arrow.classList.remove('arrow-down');
		arrow.classList.add('arrow-up');
		
		if(order_children.length === 0)
		{
			console.log('parent_id');
			console.log(parent_id);
			
			//const parts = parent_id.split("_");
			const cache_dir = parent_id.replaceAll('_', '/');
			console.log('cache_dir');
			console.log(cache_dir);
			
			const json_cached_data_dir = '<?=$cache_dir?>/<?=$current_user_id?>';
			console.log('json_cached_data_dir');
			console.log(json_cached_data_dir);
			
			const timeframe = '<?php if($timeframe) echo '-' . $timeframe;?>';
			
			const cache_file = json_cached_data_dir + '/' + cache_dir + '/tr' + timeframe + '.html';
			console.log('cache_file');
			console.log(cache_file);
			
			const cached_html = await fetchCacheByFullPath(cache_file);
			console.log('cached_html');
			console.log(cached_html);
			
			//alert(parent_id + "\r\n" + cached_html);
			
			if(cached_html)
			{
				parent_element.insertAdjacentHTML('afterend', cached_html);
				document.getElementById('loading').style.display = 'none';
				
				order_children = document.querySelectorAll('.child-' + parent_id);
			}
			
		}
		
		for(var i = 0; i < order_children.length; i++)
		{
			order_children[i].style.display = 'table-row';
		}
	}

	var loading = document.getElementById('loading');
	if(loading)
		loading.remove();
}
</script>
<style>
.events-page-wrapper .etn_search_shortcode.etn_search_wrapper {
    margin-top: 12px;
}

.events-page-wrapper .etn_search_shortcode.etn_search_wrapper h3 {
    padding-top: initial;
}

#inner_frame_wrap { 
	padding: 0; 
	overflow: hidden; 
	/*border: 1px solid #AAA;*/
	border: none;
	position: relative;
	width: 320px; 
	/*width: 100%;*/
	height: 240px;
	margin-top:12px;
	/*margin-bottom:12px;*/
}
#inner_frame {
	opacity: 0;
	width: 100%;
	height: 100%;
	border: none;
	transform-origin: 0 0;
}

.event_property {
	text-align: left;
}

.events_page_grid_element_actions {
    color: var(--Accent-color-100, #17A0B2);
    font-family: "DM Sans";
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 140%;
    cursor: pointer;
    float: right;
}
	
.events_page_grid_element_actions:after {
    content: url("https://rezbetadev.wpenginepowered.com/wp-content/uploads/2024/05/Edit_Pencil_01.svg");
    margin-left: 8px;
    position: relative;
    top: 4px;
}

.edit_element {
	display:none;
}

.row-green {
	background:#cbf6b4;
}
.row-yellow {
	background:#ffe8aa;
}
.row-red {
	background:#ffd6c9;
}

.order-events-table td {
	border: 1px solid #fff;
}

.event-description {
	border: none !important;
}


.event_date_header {
	background:var(--Gray-Palette-60, #DEE4EB);
}

.first_last {
	/*border-top: 3px solid #fff;
	border-right: 0;
	border-bottom: 3px solid #fff;
	border-left: 0;*/
}
.table_action {
    background: none!important;
}
.table_action:hover {
    background: #117886!important;
}
	
.add_player_ {
    background: #17a0b2!important;
    border: 1px solid #117886;
    color: white!important;
    cursor: pointer;
	
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
}	
.add_player {
    border-radius: var(--Corner-Medium-small, 10px);
    background: var(--Accent-color-100, #17A0B2);
    padding: 8px 18px 12px 18px;
    color: var(--Additional-colors-White, #FFF)!important;
    font-family: "DM Sans";
    font-size: 16px;
    font-style: normal;
    font-weight: 400;
    line-height: 140%;
}	
.add_player:before {
    content: url(https://rezbetadev.wpenginepowered.com/wp-content/uploads/2024/04/Add_Plus.svg);
    position: relative;
    top: 7px;
    left: -4px;
}	

.report-page-wrapper #psd_label {
    grid-area: l1;
}
.report-page-wrapper #ped_label {
    grid-area: l2;
}
.report-page-wrapper #esd_label {
    grid-area: l3;
}
.report-page-wrapper #eed_label {
    grid-area: l4;
}
.report-page-wrapper #timeframe_label {
    grid-area: l5;
}

.report-page-wrapper #psd {
    grid-area: s1;
}
.report-page-wrapper #ped {
    grid-area: s2;
}
.report-page-wrapper #esd {
    grid-area: s3;
}
.report-page-wrapper #eed {
    grid-area: s4;
}
.report-page-wrapper #timeframe {
    grid-area: s5;
}

.report-page-wrapper #filter_submit {
    grid-area: b2;
 	background: #17a0b2 !important;
}
.report-page-wrapper #filter_submit:hover {
 	background: #117886 !important;
}

.report-page-wrapper #date_of_purchase_label {
    grid-area: t1;
}
.report-page-wrapper #date_of_event_label {
    grid-area: t2;
}

.report-page-wrapper .input-group.filter-input-group {
    display: grid;
    grid-template-areas: "t1 t1 t2 t2 t3 t3" "l1 l2 l3 l4 l5 b" "s1 s2 s3 s4 s5 b";
}

.report-page-wrapper .clear_all {
    padding-top: initial;
}
.date-range-picker {
    border-radius: var(--Corner-Medium-small, 10px);
    border: 1px solid var(--Gray-Palette-80, #A8B5C2);
    background: var(--Additional-colors-White, #FFF);
    height: 40px;
}
.price-column {
	width: 100px;
}

.order-events-table td {
    border: none;
}

#export_CSV {
	float: right;
}

.export-csv-button {
	border-radius: var(--Corner-Medium-small, 10px);
	padding: 9px 16px;
	font-family: "DM Sans";
	font-style: normal;
	font-weight: 400;
	line-height: 140%;
	position: relative;

	padding-left: 28px; /* space for the icon */
	font-size: 16px;
	display: inline-flex;
	align-items: center;
	border: 2px solid #1aa0af;
	border-radius: 8px;
	color: #1aa0af;
	background: white;
	cursor: pointer;
	padding: 10px 16px;
	text-decoration: none;
	transition: background 0.2s;
	float: right;
}	
.export-csv-button:hover {
	background: #e6f9fb;
}

.export-csv-button::before {
	content: '';
	background-image: url('/wp-content/themes/greenshift-child/img/export_CSV.svg');
	background-size: contain;
	background-repeat: no-repeat;
	display: inline-block;
	width: 16px;
	height: 16px;
	margin-right: 8px;
	position: relative;
	top: 1px;
}

.export-button-wrapper {
	margin: -32px 0 100px 0;
}

table.report-table tr td a.event-link {
    text-decoration: none;
    cursor: text!important;
}
</style>
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />		
<script>
jQuery(document).ready(function() {
	jQuery(function() {
	    // Initialize date range picker for both inputs
	    jQuery('input[name="psd"], input[name="ped"]').daterangepicker({
	        autoUpdateInput: false,
	        locale: {
	            cancelLabel: 'Clear'
	        },
	        maxSpan: {
	            days: 365  // Limit the range to 1 year
	        },
	        // Set start and end dates based on input values or default to today
	        startDate: jQuery('#psd').val() ? moment(jQuery('#psd').val(), 'MM/DD/YYYY') : moment(),
	        endDate: jQuery('#ped').val() ? moment(jQuery('#ped').val(), 'MM/DD/YYYY') : moment()
	    });
	
	    jQuery('#psd').on('apply.daterangepicker', function(ev, picker) {
	        jQuery(this).val(picker.startDate.format('MM/DD/YYYY'));
	
	        // Set the maximum date for #ped to one year after #psd
	        const maxEndDate = picker.startDate.clone().add(1, 'year');
	        
	        // Update the #ped picker with the new min and max dates
	        jQuery('#ped').data('daterangepicker').minDate = picker.startDate;
	        jQuery('#ped').data('daterangepicker').maxDate = maxEndDate;
	
	        // If the current end date is beyond the new max date, adjust it
	        if (picker.endDate.isAfter(maxEndDate)) {
	            jQuery('#ped').data('daterangepicker').setEndDate(maxEndDate);
	            jQuery('#ped').val(maxEndDate.format('MM/DD/YYYY'));
	        } else {
	            jQuery('#ped').val(picker.endDate.format('MM/DD/YYYY'));
	        }
			clear_inputs('esd', 'eed', 'timeframe', '');
	    });
	
	    jQuery('#ped').on('apply.daterangepicker', function(ev, picker) {
	        jQuery(this).val(picker.endDate.format('MM/DD/YYYY'));
	        jQuery('#psd').val(picker.startDate.format('MM/DD/YYYY'));
			clear_inputs('esd', 'eed', 'timeframe', '');
	    });
	
	    jQuery('#psd').on('cancel.daterangepicker', function(ev, picker) {
	        jQuery(this).val('');
	    });
	
	    jQuery('#ped').on('cancel.daterangepicker', function(ev, picker) {
	        jQuery(this).val('');
	    });
	    // Initialize date range picker for both inputs
	    jQuery('input[name="esd"], input[name="eed"]').daterangepicker({
	        autoUpdateInput: false,
	        locale: {
	            cancelLabel: 'Clear'
	        },
	        maxSpan: {
	            days: 365  // Limit the range to 1 year
	        },
	        // Set start and end dates based on input values or default to today
	        startDate: jQuery('#esd').val() ? moment(jQuery('#esd').val(), 'MM/DD/YYYY') : moment(),
	        endDate: jQuery('#eed').val() ? moment(jQuery('#eed').val(), 'MM/DD/YYYY') : moment()
	    });
	
	    jQuery('#esd').on('apply.daterangepicker', function(ev, picker) {
	        jQuery(this).val(picker.startDate.format('MM/DD/YYYY'));
	
	        // Set the maximum date for #eed to one year after #esd
	        const maxEndDate = picker.startDate.clone().add(1, 'year');
	        
	        // Update the #eed picker with the new min and max dates
	        jQuery('#eed').data('daterangepicker').minDate = picker.startDate;
	        jQuery('#eed').data('daterangepicker').maxDate = maxEndDate;
	
	        // If the current end date is beyond the new max date, adjust it
	        if (picker.endDate.isAfter(maxEndDate)) {
	            jQuery('#eed').data('daterangepicker').setEndDate(maxEndDate);
	            jQuery('#eed').val(maxEndDate.format('MM/DD/YYYY'));
	        } else {
	            jQuery('#eed').val(picker.endDate.format('MM/DD/YYYY'));
	        }
			clear_inputs('psd', 'ped', 'timeframe', '');
	    });
	
	    jQuery('#eed').on('apply.daterangepicker', function(ev, picker) {
	        jQuery(this).val(picker.endDate.format('MM/DD/YYYY'));
	        jQuery('#esd').val(picker.startDate.format('MM/DD/YYYY'));
			clear_inputs('psd', 'ped', 'timeframe', '');
	    });
	
	    jQuery('#esd').on('cancel.daterangepicker', function(ev, picker) {
	        jQuery(this).val('');
	    });
	
	    jQuery('#eed').on('cancel.daterangepicker', function(ev, picker) {
	        jQuery(this).val('');
	    });
	});
});

function clear_inputs(input1, input2, input3, input4)
{
	if(input1 !=='')
		document.getElementById(input1).value = '';
	if(input2 !=='')
		document.getElementById(input2).value = '';
	if(input3 !=='')
		document.getElementById(input3).value = '';
	if(input4 !=='')
		document.getElementById(input4).value = '';
}
</script>
<?php
$psd = !empty($_REQUEST['psd']) ? $_REQUEST['psd'] : '';
$ped = !empty($_REQUEST['ped']) ? $_REQUEST['ped'] : '';
$esd = !empty($_REQUEST['esd']) ? $_REQUEST['esd'] : '';
$eed = !empty($_REQUEST['eed']) ? $_REQUEST['eed'] : '';

//vd($esd, '$esd');
//vd($eed, '$eed');
$timeframes = ['30' => 'Last 30 days', '365' => 'Last Year'];

?>
<div class="events-page-wrapper report-page-wrapper">
	<div class="etn_search_shortcode etn_search_wrapper">
		<h3 style="display: inline-block;">Filter by:</h3><a class="clear_all" href="." style="">Clear All</a><!-- a class="clear_all" href="javascript:void(0)" onclick="clear_inputs('psd', 'ped', 'esd', 'eed')" style="">Clear All</a -->
		<form method="GET" class="etn_event_inline_form filter_form" action="<?=get_url_without_pagination()?>">
		<div class="etn-event-search-wrapper">
			<div class="input-group filter-input-group">
				<label id="date_of_purchase_label">Date of Purchase</label>
				<label id="date_of_event_label">Date of Event</label>
				<label for="psd" id="psd_label">Start date</label>
				<input onchange="clear_inputs('esd', 'eed', 'timeframe', '')" autocomplete="off" id="psd" name="psd" type="text" placeholder="" class="start-event-field date-range-picker" value="<?=$psd?>">
				<label for="ped" id="ped_label">End date</label>
				<input onchange="clear_inputs('esd', 'eed', 'timeframe', '')" autocomplete="off" id="ped" name="ped" type="text" placeholder="" class="end-event-field date-range-picker" value="<?=$ped?>">
				<label for="esd" id="esd_label">Start date</label>
				<input onchange="clear_inputs('psd', 'ped', 'timeframe', '')" autocomplete="off" id="esd" name="esd" type="text" placeholder="" class="start-event-field date-range-picker" value="<?=$esd?>">
				<label for="eed" id="eed_label">End date</label>
				<input onchange="clear_inputs('psd', 'ped', 'timeframe', '')" autocomplete="off" id="eed" name="eed" type="text" placeholder="" class="end-event-field date-range-picker" value="<?=$eed?>">
				<label for="timeframe" id="timeframe_label">Timeframe</label>
				<select onchange="clear_inputs('psd', 'ped', 'esd', 'eed')" name="timeframe" id="timeframe" fdprocessedid="t4aqku">
					<option value="">Select Timeframe</option>
<?php
	foreach($timeframes as $days => $option)
	{
		$selected = $_REQUEST['timeframe'] == $days ? ' selected' : '';
?>
							<option value="<?=$days?>"<?=$selected?>><?=$option?></option>
<?php
	}
?>
						</select>
				<div class="search-button-wrapper">
					<button type="submit" id="filter_submit" class="etn-btn etn-btn-primary">Filter Now</button>
				</div>
			</div>
		</div>
		</form>
	</div>
</div>
<div class="export-button-wrapper">
	<button id="export" type="button" class="export-csv-button">Export CSV</button>
</div>

<table id="report-table">
<tbody>
<tr>
	<td>Date Purchased</td>
	<td>Order</td>
	<td>Event Name</td>
	<td>Date of Event</td>
	<td>Client</td>
	<td>Revenue</td>
	<td>Activity Type</td>
	<td>Event Type</td>
	<td>Lesson Type</td>
	<td>Status</td>
</tr>
<?php
foreach($events_by_activity as $activity_name => $activity)
{
	$revenue_activity = 0;
	foreach($activity as $event_type_name => $events)
	{
		foreach($events as $event)
			if(!empty($revenues[$event->order_id]['revenue']))
				$revenue_activity += $revenues[$event->order_id]['revenue'];
	}
	
	foreach($activity as $event_type_name => $events)
	{
		if($event_type_name === 'Lessons')
		{
			$lesson_types = [];
			foreach($events as $event)
			{
				$lesson_type = 'Public Group';
				$etn_categories = get_post_meta($event->ID, 'etn_categories', TRUE);
				if(!$etn_categories)
				{
					$booked_charged_orders = get_booked_orders($event->ID, TRUE);
					$orders_1_to_1_count = has_orders_for_product($event->ID, $booked_charged_orders);
					if($orders_1_to_1_count)
						$lesson_type = '1-on-1';
				}
				elseif($etn_categories == 'Lesson Private Group')
					$lesson_type = 'Private Group';
				//elseif($etn_categories == 'Lesson Public Group')
					//$lesson_type = 'Group';
				
				//vd($lesson_type, '$lesson_type');
				
				$lesson_types[$lesson_type][] = $event;
			}
			
			foreach($lesson_types as $lesson_type_name => $events)
			{
				foreach($events as $event)
				{
					$revenue = $gross_total = $gross_total_amount = 0;
					
					//if(!empty($revenues[$event->order_id]['revenue']))
						//$revenue = wc_price($revenues[$event->order_id]['revenue']);
					
					if(!empty($revenues[$event->order_id]['gross_total']))
					{
						$gross_total = wc_price($revenues[$event->order_id]['gross_total']);
						$gross_total_amount = $revenues[$event->order_id]['gross_total'];
					}
					
					$display_status = strpos($event->display_status, '') !== FALSE ? 'Paid' : $event->display_status;
?>
<tr>
	<td><?=$event->order_datetime?></td>
	<td>#<?=$event->order_id?></td>
	<td><?=$event->post_title?></td>
	<td><?=$event->datetime?></td>
	<td><?=$event->customer_link?><?=$event->attendee_names_html?></td>
	<td><?=$gross_total?></td>
	<td><?=$activity_name?></td>
	<td><?=$event_type_name?></td>
	<td><?=$lesson_type_name?></td>
	<td><?=$display_status?></td>
</tr>
<?php
					$refunds = $event->order->get_refunds();
					
					foreach($refunds as $refund)
					{
					    $refund_date = $refund->get_date_created();
						$refund_amount = $refund->get_amount();
						$refund_total = wc_price($refund_amount);
						$refund_status = $refund_amount >= $gross_total_amount ? 'Refund (full)' : 'Partial Refund';
?>
<tr>
	<td><?=$refund_date->date('n/j/y')?></td>
	<td>#<?=$refund->ID?></td>
	<td><?=$event->post_title?></td>
	<td><?=$event->datetime?></td>
	<td><?=$event->customer_link?><?=$event->attendee_names_html?></td>
	<td>-<?=$refund_total?></td>
	<td><?=$activity_name?></td>
	<td><?=$event_type_name?></td>
	<td><?=$lesson_type_name?></td>
	<td><?=$refund_status?></td>
</tr>
<?php
					}
				}
			}
		}
		else//if(0)// --------- not lessons ---------
		{
			foreach($events as $event)
			{
				$revenue = 0;
				
				//if(!empty($revenues[$event->order_id]['revenue']))
					//$revenue = wc_price($revenues[$event->order_id]['revenue']);
				
				if(!empty($revenues[$event->order_id]['gross_total']))
				{
					$gross_total = wc_price($revenues[$event->order_id]['gross_total']);
					$gross_total_amount = $revenues[$event->order_id]['gross_total'];
				}
				
				$display_status = strpos($event->display_status, '') !== FALSE ? 'Paid' : $event->display_status;
?>
<tr>
	<td><?=$event->order_datetime?></td>
	<td>#<?=$event->order_id?></td>
	<td><?=$event->post_title?></td>
	<td><?=$event->datetime?></td>
	<td><?=$event->customer_link?><?=$event->attendee_names_html?></td>
	<td><?=$gross_total?></td>
	<td><?=$activity_name?></td>
	<td><?=$event_type_name?></td>
	<td></td>
	<td><?=$display_status?></td>
</tr>
<?php
				$refunds = $event->order->get_refunds();
				
				foreach($refunds as $refund)
				{
				    $refund_date = $refund->get_date_created();
					$refund_amount = $refund->get_amount();
					$refund_total = wc_price($refund_amount);
					$refund_status = $refund_amount >= $gross_total_amount ? 'Refund (full)' : 'Partial Refund';
					if(!empty($event->stripe_url))
						$refund_status = '<a target="_blank" href="' . $event->stripe_url . '">' . $refund_status . '</a>';
?>
<tr>
	<td><?=$refund_date->date('n/j/y')?></td>
	<td>#<?=$refund->ID?></td>
	<td><?=$event->post_title?></td>
	<td><?=$event->datetime?></td>
	<td><?=$event->customer_link?><?=$event->attendee_names_html?></td>
	<td>-<?=$refund_total?></td>
	<td><?=$activity_name?></td>
	<td><?=$event_type_name?></td>
	<td></td>
	<td><?=$refund_status?></td>
</tr>
<?php
				}
			}
		}// --------- /not lessons ---------
	}
}
?>
</tbody>
</table>
<?php
$range_start = $range_end = '';

if(!empty($_REQUEST['esd']))
	$range_start = date('mdY', strtotime($_REQUEST['esd']));
elseif(!empty($_REQUEST['psd']))
	$range_start = date('mdY', strtotime($_REQUEST['psd']));

if(!empty($_REQUEST['eed']))
	$range_end = date('mdY', strtotime($_REQUEST['eed']));
elseif(!empty($_REQUEST['ped']))
	$range_end = date('mdY', strtotime($_REQUEST['ped']));

$current_user = wp_get_current_user();
$csv_name = implode('-', array_filter([str_replace(' ', '-', $current_user->display_name), $range_start, $range_end])) . '.csv';
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const table = document.getElementById('report-table');
  let csv = '';

  for (let row of table.rows) {
    let rowData = [];

    for (let cell of row.cells) {
      const colspan = parseInt(cell.getAttribute('colspan')) || 1;

      // Escape quotes and trim
      const text = cell.textContent.trim().replace(/"/g, '""');

      // Push the value and pad with empty cells if colspan > 1
      rowData.push('"' + text + '"');
      for (let i = 1; i < colspan; i++) {
        rowData.push('""'); // extra empty columns
      }
    }

    csv += rowData.join(',') + "\r\n";
  }

  // Trigger download
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.setAttribute('href', url);
  link.setAttribute('download', '<?=$csv_name?>');
  link.style.display = 'none';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
});
</script>
