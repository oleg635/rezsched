<?php
/*
if($_SERVER['REMOTE_ADDR'] == '188.163.75.165')
{
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
}
*/
$timeframe = !empty($_REQUEST['timeframe']) ? $_REQUEST['timeframe'] : '';

$events_by_activity = $revenues = [];
$activity_types = get_all_product_categories( 0, $is_top_level = 1 );
$event_types = get_all_product_categories( 0, $is_top_level = 0 );

$event_types[26] = 'Monthly Subscription';
$event_types[0] = 'No Subcategory';

$current_user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : get_current_user_id();
$cache_dir = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/json-cached-data';

global $wpdb;

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

$table = $wpdb->prefix . 'rez_order_summary';

// base query (note: keep your current style)
$sql = 'SELECT s.* FROM ' . $table . ' s
LEFT JOIN ' . $wpdb->postmeta . ' pm
  ON pm.post_id = s.event_id AND pm.meta_key = "_rez_source_id"
WHERE s.dokan_vendor_id = "' . $current_user_id . '"
  AND s.product_category <> 0
  AND pm.meta_id IS NULL'; // <-- exclude products that have _rez_source_id

$psd = $ped = $esd = $eed = '';
$where = '';

if(!empty($_REQUEST['timeframe']))
{
	$tz     = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone( wp_timezone_string() ?: 'UTC' );
	$today  = new DateTimeImmutable('today', $tz);
	
	if($_REQUEST['timeframe'] == 30)
	{
		$start = $today->sub(new DateInterval('P29D'));
		$end   = $today;
	}
	elseif($_REQUEST['timeframe'] == 365)
	{
		$start = $today->sub(new DateInterval('P364D'));
		$end   = $today;
	}
	
	$start_param = $start->format('Y-m-d');
	$end_param = $end->format('Y-m-d');
	
	$where = ' AND date_purchased >= "' . $start_param . '" AND date_purchased <= "' . $end_param . '"';
}
elseif(!empty($_REQUEST['psd']) && !empty($_REQUEST['ped']) )
{
    $dt = DateTime::createFromFormat('m/d/Y', sanitize_text_field( wp_unslash($_REQUEST['psd']) ));
    if ( $dt instanceof DateTime ) $psd = $dt->format('Y-m-d');
	
    $dt = DateTime::createFromFormat('m/d/Y', sanitize_text_field( wp_unslash($_REQUEST['ped']) ));
    if ( $dt instanceof DateTime ) $ped = $dt->format('Y-m-d');
	
	if($psd && $ped)
	{
		$where = ' AND date_purchased >= "' . $psd . '" AND date_purchased <= "' . $ped . '"';
	}
	//vd($where, '$where 2');
}
elseif(!empty($_REQUEST['esd']) && !empty($_REQUEST['eed']) )
{
    $dt = DateTime::createFromFormat('m/d/Y', sanitize_text_field( wp_unslash($_REQUEST['esd']) ));
    if ( $dt instanceof DateTime ) $esd = $dt->format('Y-m-d');
	
    $dt = DateTime::createFromFormat('m/d/Y', sanitize_text_field( wp_unslash($_REQUEST['eed']) ));
    if ( $dt instanceof DateTime ) $eed = $dt->format('Y-m-d');
	
	if($esd && $eed)
	{
		$where = ' AND date_of_event >= "' . $esd . '" AND date_of_event <= "' . $eed . '"';
	}
}
else
{
	$tz     = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone( wp_timezone_string() ?: 'UTC' );
	$today  = new DateTimeImmutable('today', $tz);
	
	$start = $today->sub(new DateInterval('P29D'));
	$end   = $today;
	
	$start_param = $start->format('Y-m-d');
	$end_param = $end->format('Y-m-d');
	
	$where = ' AND date_purchased >= "' . $start_param . '" AND date_purchased <= "' . $end_param . '"';
	//vd($where, '$where 4');
}


$sql .= $where;
$sql .= ' ORDER BY date_purchased DESC, order_id DESC';

$rows = $wpdb->get_results($sql, ARRAY_A);
//vd($rows, '$rows');

$events_by_activity_grouped = [];

foreach($rows as $row)
{
	$activity = isset($activity_types[$row['product_category']]) ? $activity_types[$row['product_category']] : 0;
	$category = isset($event_types[$row['product_subcategory']]) ? $event_types[$row['product_subcategory']] : 0;
	
	if(!$category)
		$category = '0';
	
	if($activity && $category)
	{
		$events_by_activity[$activity][$category][] = $row;
		
		if(empty($events_by_activity_grouped[$activity][$category][$row['event_id']]))
		{
			$events_by_activity_grouped[$activity][$category][$row['event_id']] = $row;
			$events_by_activity_grouped[$activity][$category][$row['event_id']]['count'] = 0;
		}
		else
			$events_by_activity_grouped[$activity][$category][$row['event_id']]['revenue'] += $row['revenue'];
			$events_by_activity_grouped[$activity][$category][$row['event_id']]['count'] += 1;
	}
}

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
<style>
#filter_submit, #export {
    border-radius: var(--Corner-Medium-small, 10px);
    background: var(--Accent-color-100, #17A0B2) !important;
    padding: 9px 16px !important;
    color: var(--Additional-colors-White, #FFF) !important;
    font-family: "DM Sans";
    font-size: 16px;
    font-style: normal;
    font-weight: 400;
    line-height: 140%;
    border: none;
	margin:0;
	top:0;
}

 #export {
    margin-top: 32px;
}


</style>

<?php
    $atts = shortcode_atts(array(
		'order_events_count' => -1,
    ), $atts);
?>
<style>
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

table.report-table .event-data-td, 
table.recent-purchases-table td, 
table.recent-purchases-table th 
{
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

table.report-table tr td.event-data-status span,
table.recent-purchases-table tr td.event-data-status span 
{
	padding: 0;
	border-radius: 12px;
	min-width: 90px;
	display: inline-block;
	text-align: center;
}

table.report-table tr td.event-data-status span a,
table.recent-purchases-table tr td.event-data-status span a
{
	font-size: 12px;
}

table.report-table tr td.event-data-status span.status-paid,
table.recent-purchases-table tr td span.status-paid
{
	background: #cbf6b4;
}

table.report-table tr.status-refund-full td,
table.report-table tr.status-partial-refund td,
table.report-table tr.status-refund-full td a,
table.report-table tr.status-partial-refund td a,
table.recent-purchases-table tr.status-refund-full td,
table.recent-purchases-table tr.status-partial-refund td,
table.recent-purchases-table tr.status-refund-full td a,
table.recent-purchases-table tr.status-partial-refund td a
{
	color: #f73206;
}

table.report-table tr td.event-data-status span.status-refund-full,
table.report-table tr td.event-data-status span.status-partial-refund,
table.recent-purchases-table tr td.event-data-status span.status-refund-full,
table.recent-purchases-table tr td.event-data-status span.status-partial-refund
{
	background: #f99d80;
}

table.report-table tr td.event-data-status span.status-refund-full a,
table.report-table tr td.event-data-status span.status-partial-refund a,
table.recent-purchases-table tr td.event-data-status span.status-refund-full a,
table.recent-purchases-table tr td.event-data-status span.status-partial-refund a
{
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
  const url = `/wp-json/rez/v1/cache?cache_file=${fullPath}`;
  const res = await fetch(url, { headers: { 'X-WP-Nonce': window.wpApiSettings?.nonce || '' } });
  const data = await res.json();
  //if (!res.ok || !data.ok) throw new Error(data?.message || 'Not found');
  if (!res.ok || !data.ok)
	  return '';
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
	console.log('order_children');
	console.log(order_children);
	
	var arrow = document.getElementById('arrow_' + parent_id);
	if(arrow.classList.contains('arrow-up'))
	{
		arrow.classList.remove('arrow-up');
		arrow.classList.add('arrow-down');
		
		var order_children = document.querySelectorAll('.subchild-' + parent_id);
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
		
		var order_children = document.querySelectorAll('.child-' + parent_id);
		
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
			
			const cache_file = json_cached_data_dir + '/' + cache_dir + '/tr.html';
			console.log('cache_file');
			console.log(cache_file);
			
			const cached_html = await fetchCacheByFullPath(cache_file);
			//console.log('cached_html');
			//console.log(cached_html);
			
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
    grid-area: l3;
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
    grid-area: s3;
}

.report-page-wrapper #filter_submit {
    grid-area: s4;
 	background: #17a0b2 !important;
}
.report-page-wrapper #filter_submit:hover {
 	background: #117886 !important;
}
.report-page-wrapper #export {
    grid-area: b;
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
    background-image: url('/wp-content/themes/greenshift-child/img/export_CSV_white.svg');
    background-size: contain;
    background-repeat: no-repeat;
    display: inline-block;
    width: 16px;
    height: 16px;
    margin-right: 8px;
    position: relative;
    top: 0;
}
.export-button-wrapper {
	margin: -32px 0 100px 0;
}
table.report-table tr td a.event-link {
    text-decoration: none;
    cursor: text!important;
}
</style>

<style>
/* Section */
.recent-purchases {
    margin-top: 24px;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

/* Title */
.recent-purchases h2 {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 14px;
    color: #0f172a;
}

/* Table */
.recent-purchases-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: #fff;
}

/* Header */
.recent-purchases-table thead th {
    background: #e8edf2;
    padding: 14px 16px;
    font-size: 14px;
    font-weight: 500;
    text-align: left;
    color: #334155;
}

.recent-purchases-table thead th:first-child {
    border-top-left-radius: 18px;
}

.recent-purchases-table thead th:last-child {
    border-top-right-radius: 18px;
    text-align: right;
}

/* Body rows */
.recent-purchases-table tbody tr {
    background: #ffffff;
}

.recent-purchases-table tbody td {
    padding: 16px;
    font-size: 15px;
    color: #0f172a;
    border-bottom: 1px solid #e5eaf0;
    vertical-align: middle;
}

/* Revenue column */
.recent-purchases-table td.revenue {
    text-align: right;
    font-weight: 500;
}

/* Links */
.order-link,
.client-link {
    color: #0f172a;
    text-decoration: underline;
    cursor: pointer;
}

.order-link:hover,
.client-link:hover {
    text-decoration: none;
}

/* Status pills */
.status {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 500;
    line-height: 1;
    white-space: nowrap;
}
/* Empty state */
.recent-purchases p {
    color: #64748b;
    font-size: 14px;
}

/* Mobile layout */
@media (max-width: 768px) {

    .recent-purchases-table thead {
        display: none;
    }

    .recent-purchases-table tbody tr {
        display: block;
        margin-bottom: 12px;
        border-bottom: 2px solid #e5eaf0;
    }

    .recent-purchases-table tbody td {
        display: flex;
        justify-content: space-between;
        padding: 10px 14px;
        border: none;
    }

    .recent-purchases-table tbody td::before {
        content: attr(data-label);
        font-weight: 500;
        color: #64748b;
    }

    .recent-purchases-table td.revenue {
        text-align: left;
    }
}

/* Search form */
.client-search-form {
    margin-bottom: 28px;
    max-width: 420px;
}

/* Label */
.client-search-label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #0f172a;
    margin-bottom: 8px;
}

/* Input wrapper */
.client-search-input-wrapper {
    position: relative;
}

/* Search icon */
.client-search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    opacity: 0.6;
    pointer-events: none;
    background-repeat: no-repeat;
    background-size: contain;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E");
}

/* Input */
.client-search-form input[type="text"] {
    width: 100%;
    padding: 12px 14px 12px 40px;
    font-size: 14px;
    border-radius: 12px;
    border: 1px solid #cbd5e1;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

/* Focus */
.client-search-form input[type="text"]:focus {
    border-color: #94a3b8;
    box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.25);
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

$timeframes = ['30' => 'Last 30 days', '365' => 'Last Year'];

if(!empty($_REQUEST['sync']) && function_exists('rez_populate_order_summary_incremental'))
{
	$res = rez_populate_order_summary_incremental($per_page = 200, $order_id = 20990, $current_user_id);
}
?>
<?php
if(0 && function_exists('rez_populate_order_summary_incremental'))
{
?>
	<a class="etn-btn etn-btn-primary" style="background: #17a0b2 !important;" href="./?sync=1">Sync Orders</a>
<?php
}
?>
<div class="events-page-wrapper report-page-wrapper">
	<div class="dashboard-widget events events-page-wrapper">
		<a href="/products/" style="color:#17a0b2;">Product List</a>&nbsp;
		<a href="/product-checkout/" class="past_events_a" style="color:#17a0b2;">Product Checkout</a>&nbsp;
		<a href="/products-report/" class="past_events_a" style="color:#17a0b2;">Reports For POS</a>&nbsp;
	</div>
	<div class="etn_search_shortcode etn_search_wrapper">
		<h3 style="display: inline-block;">Filter by:</h3><a class="clear_all" href="." style="">Clear All</a><!-- a class="clear_all" href="javascript:void(0)" onclick="clear_inputs('psd', 'ped', 'esd', 'eed')" style="">Clear All</a -->
		<form method="GET" class="etn_event_inline_form filter_form" action="<?=get_url_without_pagination()?>">
		<div class="etn-event-search-wrapper">
			<div class="input-group filter-input-group">
				<label for="psd" id="psd_label">Date Range</label>
				<input onchange="clear_inputs('esd', 'eed', 'timeframe', '')" autocomplete="off" id="psd" name="psd" type="text" placeholder="" class="start-event-field date-range-picker" value="<?=$psd?>">
				<input onchange="clear_inputs('esd', 'eed', 'timeframe', '')" autocomplete="off" id="ped" name="ped" type="text" placeholder="" class="end-event-field date-range-picker" value="<?=$ped?>">
				<label for="esd" id="esd_label" style="display:none;">Start date</label>
				<input style="display:none;" onchange="clear_inputs('psd', 'ped', 'timeframe', '')" autocomplete="off" id="esd" name="esd" type="text" placeholder="" class="start-event-field date-range-picker" value="<?=$esd?>">
				<label for="eed" id="eed_label" style="display:none;">End date</label>
				<input style="display:none;" onchange="clear_inputs('psd', 'ped', 'timeframe', '')" autocomplete="off" id="eed" name="eed" type="text" placeholder="" class="end-event-field date-range-picker" value="<?=$eed?>">
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
				<button type="submit" id="filter_submit" class="etn-btn etn-btn-primary">Filter Now</button>
				<button id="export" type="button" class="export-csv-button">Export</button>
				<!-- a target="_blank" class="export-csv-button" href="/export-report/?<?=$_SERVER['QUERY_STRING']?>">Export CSV</a -->
			</div>
		</div>
<?php
if(!empty($_REQUEST['opt']))
{
?>
			<input type="hidden" name="opt" value="1">
<?php
}
?>
		</form>
	</div>
</div>
<table class="order-events-table events-table report-table">
<thead>
<tr>
	<th>Type</th>
	<th>Total revenue</th>
</tr>
</thead>
<tbody>
<?php
foreach($events_by_activity_grouped as $activity_name => $activity)
{
	$arrow_class = 'arrow-down';
	$activity_name_valid = preg_replace('/[^A-Za-z]+/', '-', $activity_name);
	//$activity_class = strtolower($activity_name_valid);
	$activity_class = $activity_name_valid;
	
	$revenue_activity = 0;
	foreach($activity as $event_type_name => $events)
	{
		foreach($events as $event)
		{
			$revenue_activity += $event['revenue'];
		}
	}
	
	ob_start();
?>
<tr id="<?=$activity_class?>" class="activity class-1">
	<td onclick="expand_children('<?=$activity_class?>')" class="heading_td order-events-table-td-1"><span id="arrow_<?=$activity_class?>" class="<?=$arrow_class?>"></span><?=$activity_name?></td>
	<td class="price-column" style="padding: 20px 12px !important;"><?=wc_price($revenue_activity)?></td>
</tr>
<?php
	$tr_html = ob_get_clean();
	echo $tr_html;
	
	//if($timeframe)
	{
		$dir_ok = true;
		$cache_activity_dir = $cache_dir . '/' . $current_user_id . '/' . $activity_name_valid;
		
		if ( ! is_dir( $cache_activity_dir ) ) {
			if ( ! $wp_mkdir_p = wp_mkdir_p( $cache_activity_dir ) ) {
				$dir_ok = false;
			}
		}
		
		if($dir_ok) {
			$tr_file = $cache_activity_dir . '/tr.html';
		    $file_put_contents = file_put_contents($tr_file, $tr_html);
		}
	}
	
	foreach($activity as $event_type_name => $events)
	{
		//else // --------- not lessons ---------
		{
			$event_type_name_valid = preg_replace('/[^A-Za-z]+/', '-', $event_type_name);
			//$event_type_class = $activity_class . '-' . strtolower($event_type_name_valid);
			$event_type_class = $activity_class . '_' . $event_type_name_valid;
			ob_start();
?>
<tr id="<?=$event_type_class?>" class="class-7 event-data-tr event-type child-<?=$activity_class?> subchild-<?=$activity_class?>" style="display:none;">
	<td onclick="expand_children('<?=$event_type_class?>')" class="heading_td order-events-table-td-1"><span id="arrow_<?=$event_type_class?>" class="<?=$arrow_class?>"></span><?=$event_type_name?></td>
<?php
			$revenue_event_type = 0;
			foreach($events as $event)
				$revenue_event_type += $event['revenue']; 
?>
	<td class="price-column" style="padding: 20px 12px !important;"><?=wc_price($revenue_event_type)?></td>
</tr>
<?php
			$tr_html = ob_get_clean();
			echo $tr_html;
			
			//if($timeframe)
			{
				$type_dir_ok = true;
				$cache_type_dir = $cache_dir . '/' . $current_user_id . '/' . $activity_name_valid . '/' . $event_type_name_valid;
				
				if ( ! is_dir( $cache_type_dir ) ) {
					if ( ! $wp_mkdir_p = wp_mkdir_p( $cache_type_dir ) ) {
						$type_dir_ok = false;
					}
				}
			}
			
			ob_start();
			foreach($events as $event)
			{
				$revenue = $gross_total = $gross_total_amount = 0;
				
				$gross_total = wc_price($event['revenue']);
				$gross_total_amount = $event['revenue'];
				
				$display_status = strpos($event['display_status'], '') !== FALSE ? 'Paid' : $event['display_status'];
				if(!empty($event['stripe_url']))
					$display_status = '<a target="_blank" href="' . $event['stripe_url'] . '">' . $display_status . '</a>';
				
				$dt  = DateTime::createFromFormat('Y-m-d', $event['date_purchased']);
				$date_purchased = $dt ? $dt->format('n/j/y') : '';
				$dt  = DateTime::createFromFormat('Y-m-d', $event['date_of_event']);
				$date_of_event = $dt ? $dt->format('n/j/y') : '';
?>
<tr class="class-9 event-data-tr status-paid child-<?=$event_type_class?> subchild-<?=$event_type_class?> subchild-<?=$activity_class?>" style="display:none;">
	<td class="event-data-td" style="text-align: left;padding-left: 62px;"><a class="event-link" href="/orders/<?=$event['event_id']?>/"><?=$event['event_name']?> (x<?=$event['count']?>)</a></td>
	<td class="price-column" style="text-align: left;"><?=$gross_total?></td>
</tr>
<?php
				//$refunds = $event->order->get_refunds();
				$refunds = !empty($event['refunds_data']) ? json_decode($event['refunds_data'], true) : [];
				
				foreach($refunds as $refund)
				{
				    $refund_id = $refund['refund_id'];
				    $refund_date_display = $refund['refund_date_display'];
				    $refund_amount = $refund['refund_amount'];
				    $refund_total = $refund['refund_total'];
				    $refund_status = $refund['refund_status'];
?>
<tr class="class-10 event-data-tr status-partial-refund child-<?=$event_type_class?> subchild-<?=$event_type_class?> subchild-<?=$activity_class?>" style="display:none;">
	<td class="event-data-td" style="text-align: left;padding-left: 62px;"><a class="event-link" href="/orders/<?=$event['event_id']?>/"><?=$event['event_name']?></a></td>
	<td class="price-column" style="text-align: left;"><?=$refund_total?></td>
</tr>
<?php
				}
			}
			
			$tr_html = ob_get_clean();
			//if(!$timeframe)//if no cache exists
				//echo $tr_html;
			
			//if($timeframe)
			{
				if($type_dir_ok) {
					$tr_file = $cache_type_dir . '/tr.html';
				    $file_put_contents = file_put_contents($tr_file, $tr_html);
				}
			}
			
		}// --------- /not lessons ---------
	}
}
?>
</tbody>
</table>

<table id="export-table" style="<?php if($_SERVER['REMOTE_ADDR'] !== '188.163.75.13') echo 'display:none;'; ?>">
<tbody>
<tr>
	<td>Date Purchased</td>
	<td>Order</td>
	<td>Product Name</td>
	<td>Client</td>
	<td>Revenue</td>
	<td>Category</td>
	<td>Subcategory</td>
	<td>Status</td>
</tr>
<?php
//if(0)
foreach($events_by_activity as $activity_name => $activity)
{
	$revenue_activity = 0;
	foreach($activity as $event_type_name => $events)
	{
		foreach($events as $event)
			$revenue_activity += $event['revenue'];
			//if(!empty($revenues[$event['order_id']]['revenue']))
				//$revenue_activity += $revenues[$event['order_id']]['revenue'];
	}
	
	foreach($activity as $event_type_name => $events)
	{
		if($event_type_name === 'Lessons')
		{
			$lesson_types = [];
			foreach($events as $event)
			{
				$lesson_type = 'Public Group';
				$etn_categories = get_post_meta($event['event_id'], 'etn_categories', TRUE);
				if(!$etn_categories)
				{
					$booked_charged_orders = get_booked_orders($event['event_id'], TRUE);
					$orders_1_to_1_count = has_orders_for_product($event['event_id'], $booked_charged_orders);
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
					
					//if(!empty($revenues[$event['order_id']]['revenue']))
						//$revenue = wc_price($revenues[$event['order_id']]['revenue']);
					
					$gross_total = wc_price($event['revenue']);
					$gross_total_amount = $event['revenue'];
					
					$display_status = strpos($event['display_status'], '') !== FALSE ? 'Paid' : $event['display_status'];
					
					$dt  = DateTime::createFromFormat('Y-m-d', $event['date_purchased']);
					$date_purchased = $dt ? $dt->format('n/j/y') : '';
					$dt  = DateTime::createFromFormat('Y-m-d', $event['date_of_event']);
					$date_of_event = $dt ? $dt->format('n/j/y') : '';
?>
<tr>
	<td><?=$date_purchased?></td>
	<td>#<?=$event['order_id']?></td>
	<td><?=$event['event_name']?></td>
	<td><?=$event['customer_link']?><?=$event['attendee_names_html']?></td>
	<td><?=$gross_total?></td>
	<td><?=$activity_name?></td>
	<td><?=$event_type_name?></td>
	<td><?=$display_status?></td>
</tr>
<?php
					$refunds = !empty($event['refunds_data']) ? json_decode($event['refunds_data'], true) : [];
					
					foreach($refunds as $refund)
					{
					    $refund_id = $refund['refund_id'];
					    $refund_date_display = $refund['refund_date_display'];
					    $refund_amount = $refund['refund_amount'];
					    $refund_total = $refund['refund_total'];
					    $refund_status = $refund['refund_status'];
?>
<tr>
	<td><?=$refund_date->date('n/j/y')?></td>
	<td>#<?=$refund_id?></td>
	<td><?=$refund_date_display?></td>
	<td><?=$event['customer_link']?><?=$event['attendee_names_html']?></td>
	<td>-<?=$refund_total?></td>
	<td><?=$activity_name?></td>
	<td><?=$event_type_name?></td>
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
				
				$gross_total = wc_price($event['revenue']);
				$gross_total_amount = $event['revenue'];
				
				$display_status = strpos($event['display_status'], '') !== FALSE ? 'Paid' : $event['display_status'];
					$display_status = strpos($event['display_status'], '') !== FALSE ? 'Paid' : $event['display_status'];
					if(!empty($event['stripe_url']))
						$display_status = '<a target="_blank" href="' . $event['stripe_url'] . '">' . $display_status . '</a>';
				
				$dt  = DateTime::createFromFormat('Y-m-d', $event['date_purchased']);
				$date_purchased = $dt ? $dt->format('n/j/y') : '';
				$dt  = DateTime::createFromFormat('Y-m-d', $event['date_of_event']);
				$date_of_event = $dt ? $dt->format('n/j/y') : '';
?>
<tr>
	<td><?=$date_purchased?></td>
	<td>#<?=$event['order_id']?></td>
	<td><?=$event['event_name']?></td>
	<td><?=$event['customer_link']?><?=$event['attendee_names_html']?></td>
	<td><?=$gross_total?></td>
	<td><?=$activity_name?></td>
	<td><?=$event_type_name?></td>
	<td><?=$display_status?></td>
</tr>
<?php
				$refunds = !empty($event['refunds_data']) ? json_decode($event['refunds_data'], true) : [];
				
				foreach($refunds as $refund)
				{
				    $refund_id = $refund['refund_id'];
				    $refund_date_display = $refund['refund_date_display'];
				    $refund_amount = $refund['refund_amount'];
				    $refund_total = $refund['refund_total'];
				    $refund_status = $refund['refund_status'];
?>
<tr>
	<td><?=$refund_date_display?></td>
	<td>#<?=$refund_id?></td>
	<td><?=$event['event_name']?></td>
	<td><?=$event['customer_link']?><?=$event['attendee_names_html']?></td>
	<td>-<?=$refund_total?></td>
	<td><?=$activity_name?></td>
	<td><?=$event_type_name?></td>
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
$search = '';

if ( isset($_GET['search']) && $_GET['search'] !== '' ) {
    $search = trim( wp_unslash( $_GET['search'] ) );
}

$tz     = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone( wp_timezone_string() ?: 'UTC' );
$today  = new DateTimeImmutable('today', $tz);

$start  = $today->sub(new DateInterval('P29D'));

$start_param = $start->format('Y-m-d');
$end_param   = $today->format('Y-m-d');

$table   = $wpdb->prefix . 'rez_order_summary';
$postmeta = $wpdb->postmeta;

$sql = "
SELECT
    s.order_id,
    MAX(s.date_purchased)        AS date_purchased,
    MAX(s.order_status)          AS order_status,
    MAX(s.display_status)        AS display_status,
    MAX(s.customer_id)           AS customer_id,
    MAX(s.customer_link)         AS customer_link,
    MAX(s.stripe_url)            AS stripe_url,
    s.dokan_vendor_id,
    s.vendor_id,
    MAX(s.revenue)               AS revenue,
    GROUP_CONCAT(s.refunds_data) AS refunds_data
FROM {$table} s
LEFT JOIN {$postmeta} pm
  ON pm.post_id = s.event_id
 AND pm.meta_key = '_rez_source_id'
WHERE s.dokan_vendor_id = %d
  AND s.product_category <> 0
  AND s.date_purchased BETWEEN %s AND %s
  AND pm.meta_id IS NULL
";

$params = [
    (int) $current_user_id,
    $start_param,
    $end_param,
];

if ($search !== '') {
    $sql .= " AND s.customer_link LIKE %s ";
    $params[] = '%' . $wpdb->esc_like($search) . '%';
}

$sql .= "
GROUP BY s.order_id
ORDER BY date_purchased DESC, order_id DESC
";

$prepared = $wpdb->prepare($sql, $params);






$rows = $wpdb->get_results( $prepared, ARRAY_A );


?>
<?php if ( 1 ) : ?>
<section class="recent-purchases">

	<form method="get" class="client-search-form">
	
	    <label for="client-search" class="client-search-label">
	        Client
	    </label>
	
	    <div class="client-search-input-wrapper">
	        <span class="client-search-icon"></span>
	        <input
	            type="text"
	            id="client-search"
	            name="search"
	            placeholder="Search user..."
	            value="<?php echo isset($_GET['search']) ? esc_attr($_GET['search']) : ''; ?>"
	        />
	    </div>
	
	</form>

    <h2>Recent Purchases</h2>

    <table class="recent-purchases-table">
        <thead>
            <tr>
                <th>Date Purchased</th>
                <th>Order #</th>
                <th>Client</th>
                <th>Payment Status</th>
                <th style="text-align:left;">Revenue</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ( $rows as $row ) :

				$revenue = $gross_total = $gross_total_amount = 0;
				$event = $row;
				
				$display_status = wph_get_order_status($event['order_id']);
				
				if($display_status == 'Refund (full)')
					$display_status = 'Full Refund';
				
				if(!empty($event['stripe_url']))
					$display_status = '<a target="_blank" href="' . $event['stripe_url'] . '">' . $display_status . '</a>';
				
				//$display_status = strpos($event['display_status'], '') !== FALSE ? 'Paid' : $event['display_status'];
				if(!empty($event['stripe_url']))
					$display_status = '<a target="_blank" href="' . $event['stripe_url'] . '">' . $display_status . '</a>';
				
				$dt  = DateTime::createFromFormat('Y-m-d', $event['date_purchased']);
				$date_purchased = $dt ? $dt->format('n/j/y') : '';
				$dt  = DateTime::createFromFormat('Y-m-d', $event['date_of_event']);
			
            $date = date_i18n(
                'n/j/y',
                strtotime( $row['date_purchased'] )
            );

            $revenue = (float) $row['revenue'];
            $revenue_formatted = ( $revenue < 0 ? '-' : '' ) . '$' . number_format( abs( $revenue ), 2 );

            $order_url   = admin_url( 'post.php?post=' . (int) $row['order_id'] . '&action=edit' );
            $client_url  = ! empty( $row['customer_id'] )
                ? admin_url( 'user-edit.php?user_id=' . (int) $row['customer_id'] )
                : '#';

			$is_refunded = strpos($display_status, 'Refund') !== FALSE;
			
			vd($event['revenue'], '$event[\'revenue\']');
			
			//if(!empty($revenues[$event['order_id']]['gross_total']))
			{
				$gross_total = wc_price($event['revenue']);
				$gross_total_amount = $event['revenue'];
			}
            ?>
            <tr class="<?php echo $is_refunded ? 'status-partial-refund' : 'status-paid'; ?>">
                <td class="<?php echo $is_refunded ? 'text-danger' : ''; ?>">
                    <?=$date_purchased?>
                </td>

                <td>
                    <a href="/order/<?=$event['order_id']?>">#<?=$event['order_id']?></a>
                </td>

                <td>
                    <?=$event['customer_link']?><?=$event['attendee_names_html']?>
                </td>

                <td class="event-data-status">
                    <span class="<?php if($is_refunded) echo 'status-partial-refund'; else echo 'status-paid';?>"><?=$display_status?></span>
                </td>

                <td class="revenue <?php echo $is_refunded ? 'text-danger' : ''; ?>" style="font-weight: bold;text-align:left;">
                    <?=$gross_total?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</section>

<?php else : ?>

<p>No recent purchases.</p>

<?php endif; ?>



<div id="inner_frame_wrap">
	<iframe id="inner_frame" style="width:100%;height:200px;"></iframe>
</div>
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

document.getElementById('export').addEventListener('click', function () {
  const table = document.getElementById('export-table');
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
<?php
?>