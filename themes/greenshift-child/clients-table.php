<?php
$user_roles = get_current_user_role();

if(!in_array('administrator', $user_roles) && !in_array('um_custom_role_1', $user_roles) && !in_array('seller', $user_roles))
{
	return;
}

if($_SERVER['REMOTE_ADDR'] == '188.163.75.165')
{
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
}

$selected_values = $attendee_data = $unique = $birth_years = $genders = [];



$search = isset($_REQUEST['search']) ? sanitize_text_field($_REQUEST['search']) : '';



	$birth_year = !empty($_REQUEST['birth_year']) ? $_REQUEST['birth_year'] : '';
	if($birth_year)
		$selected_values = explode(',', $birth_year);
	
	$gender = !empty($_REQUEST['gender']) ? $_REQUEST['gender'] : '';
	$numeric_search = $search_email = '';
	$search = isset($_REQUEST['search']) ? sanitize_text_field($_REQUEST['search']) : '';
	
	if (!filter_var($search, FILTER_VALIDATE_EMAIL)) {
	    // $search is NOT an email
		$numeric_search = preg_replace('/\D/', '', $search);
		if(strlen($numeric_search) < 4)
			$numeric_search = '';
	}
	else
	{
		if (filter_var($search, FILTER_VALIDATE_EMAIL)) {
			$search_email = strtolower(trim($search));
		}
	}

$current_user_id = get_current_user_id();
if(!$current_user_id)
	return;

$attendees_max = 8;
$role = 'um_custom_role_2';
$vendor_id = !empty($_REQUEST['vendor_id']) ? $_REQUEST['vendor_id'] : $current_user_id;

// Make sure the folder exists
//$cache_dir = __DIR__ . '/json-data';
$cache_dir = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/json-cached-data';

if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}

$birth_years_file = $cache_dir . "/birth_years_$vendor_id.json";
$genders_file = $cache_dir . "/genders_$vendor_id.json";

$attendees_file = $cache_dir . "/attendees_$vendor_id.json";
//$unique_file = $cache_dir . "/attendees_$vendor_id.json";

//vd($unique_file, '$unique_file');
/*
if (file_exists($birth_years_file)) {
    unlink($birth_years_file);
}

if (file_exists($genders_file)) {
    unlink($genders_file);
}
*/
//if(1)
/*
vd(file_exists($birth_years_file), 'file_exists($birth_years_file)');
vd(file_exists($genders_file), 'file_exists($genders_file)');
vd(file_exists($unique_file), 'file_exists("' . $unique_file . '")');
*/
if(!file_exists($birth_years_file) || !file_exists($genders_file) || !file_exists($attendees_file))
//if(1)
{
	vd('get_json_data()');
	get_json_data($vendor_id, $birth_years, $genders, $attendee_data, $selected_values);
} else {
    $birth_years = json_decode(file_get_contents($birth_years_file), true);
    $genders = json_decode(file_get_contents($genders_file), true);
    $attendee_data = json_decode(file_get_contents($attendees_file), true);
}

//vd($birth_years, '$birth_years');
//vd($genders, '$genders');


// ================ Filter attendies ==================
$seen_names = [];

foreach($attendee_data as $attendee)
{
    if(!in_array($attendee['attendee_name'], $seen_names))
	{
		if($search_email)
		{
			if ($search_email !== $attendee['email']) {
			    continue;
			}
		}
		elseif($numeric_search)
		{
			$numeric_phone = preg_replace('/\D/', '', $attendee['phone']);
			if (substr($numeric_search, -4) !== substr($numeric_phone, -4)) {
			    continue;
			}
			
			//vd($numeric_search, '$numeric_search');
		}
		else
		{
			if($search)
			{
				if (1
					&& strpos($attendee['display_name'], $search) === FALSE
					&& strpos($attendee['user_login'], $search) === FALSE
					&& strpos($attendee['user_nicename'], $search) === FALSE
					&& strpos($attendee['attendee_name'], $search) === FALSE
				)
				{
				    continue;
				}
			}
		}
		
		if($birth_year && $gender)
		{
			if(in_array($attendee['year'], $selected_values) && $attendee['gender'] == $gender)
			{
		        $seen_names[] = $attendee['attendee_name'];
    		    $unique[] = $attendee;
			}
		}
		elseif($birth_year)
		{
			if(in_array($attendee['year'], $selected_values))
			{
		        $seen_names[] = $attendee['attendee_name'];
    		    $unique[] = $attendee;
			}
		}
		elseif($gender)
		{
			if($attendee['gender'] == $gender)
			{
		        $seen_names[] = $attendee['attendee_name'];
    		    $unique[] = $attendee;
			}
		}
		else
		{
	        $seen_names[] = $attendee['attendee_name'];
   		    $unique[] = $attendee;
		}
    }
}

// 1. Get requested sort field from query
$sort_field = isset($_GET['sort']) ? $_GET['sort'] : null;

// 2. If it's a supported field, sort it
$allowed_fields = ['last_name', 'first_name', 'attendee_last_name', 'attendee_first_name', 'year'];

if (in_array($sort_field, $allowed_fields)) {
    sort_attendees_by_field($unique, $sort_field);
}
// ================ Filter attendies ==================

$attendees_count = $unique ? count($unique) : 0;

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$offset = ($paged - 1) * $attendees_max;
$max_index = $offset + $attendees_max;

if (isset($_GET['export_csv'])) {
    $filename = 'clients-list-' . date('Y-m-d-H-i') . '.csv';
	
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=$filename");
    header('Pragma: no-cache');
    header('Expires: 0');
	
    $output = fopen('php://output', 'w');
	
    // Define the columns you want to export
    $fields = [
        'last_name'           => 'Parent Last Name',
        'first_name'          => 'Parent First Name',
        'email'               => 'Email',
        'phone'               => 'Phone Number',
        'attendee_last_name'  => 'Player Last Name',
        'attendee_first_name' => 'Player First Name',
        'year'                => 'Birth year',
        'gender'              => 'Gender',
    ];
	
    // Output header row (custom labels)
    fputcsv($output, array_values($fields));
	
    // Output filtered data rows
    if (!empty($unique) && is_array($unique)) {
        foreach ($unique as $row) {
            $filtered = [];
            foreach ($fields as $key => $label) {
                $filtered[] = $row[$key] ?? ''; // use empty string if key is missing
            }
            fputcsv($output, $filtered);
        }
    }
	
    fclose($output);
    exit;
}

include('system-message.php');
?>
<style>
.tooltip-wrapper {
  position: relative;
  display: table-cell; /* Ensures the tooltip stays within the table cell */
  cursor: pointer;
}

.tooltip-wrapper .tooltiptext {
  visibility: hidden;
  width: auto;
  max-width: 150px; /* Set a maximum width to wrap long text */
  background-color: #333;
  color: #fff;
  text-align: left;
  border-radius: 5px;
  padding: 5px 10px;
  position: absolute;
  z-index: 1;
  top: 50%; /* Centers the tooltip vertically */
  left: -150px; /* Adjust positioning to the left */
  margin-top: -16px; /* Adjust for height */
  opacity: 0;
  transition: opacity 0.3s;
  word-wrap: break-word; /* Ensures text breaks into multiple lines */
  white-space: normal; /* Allows text to wrap */
}

.tooltip-wrapper:hover .tooltiptext {
  visibility: visible;
  opacity: 1;
}
</style>

<style>
#birth_year {
	margin-top: 8px;
}
table button.button-action.delete-added-user {
	border: 1px solid #c2451e;
	color: #c2451e;
}
table button.button-action.delete-added-user:hover {
	border: #fff;
	color: #fff;
	background: #c2451e;
}
</style>
<?php
if(1)//$atts['show_filters']
{
	//$locations = get_all_event_locations_current_user($current_user_id);
?>

	<script>
	function switch_day(checkbox_id)
	{
		var span_closed = document.getElementById('closed_' + checkbox_id);
		var span_open = document.getElementById('open_' + checkbox_id);
		var posts_container = document.getElementById('posts-container');
		
		console.log('span_closed.style.display');
		console.log(span_closed.style.display);
		
		if(span_closed.style.display == 'none')
		{
			span_closed.style.display = 'block';
			span_open.style.display = 'none';
		}
		else
		{
			span_closed.style.display = 'none';
			span_open.style.display = 'block';
		}
		
		if(document.getElementById('checkbox_' + checkbox_id).checked)
		{
			file = 'event-posts.php';
			setCookie("page_view", "grid", 7);
			posts_container.classList.remove('events_list');
		}
		else
		{
			file = 'event-posts-list.php';
			setCookie("page_view", "list", 7);
			posts_container.classList.add('events_list');
		}
		
		paged = 0;
		document.getElementById('posts-container').innerHTML = '';
		get_events_ajax();
	}

	function setCookie(name, value, days) {
	    const date = new Date();
	    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000)); // Convert days to milliseconds
	    const expires = "expires=" + date.toUTCString();
	    document.cookie = name + "=" + value + ";" + expires + ";path=/";
	}
	</script>
	<style>
	.etn_search_shortcode.etn_search_wrapper {
	    margin-top: 12px;
		margin: 0;
	}

	.etn_search_shortcode.etn_search_wrapper h3 {
	    padding-top: initial;
	}

	.status--btn {
	    display: flex;
	    flex-wrap: wrap;
	    align-items: center;
	    min-width: 165px
	}
	.status--btn .switch {
	    position: relative;
	    display: inline-block;
	    width: 36px;
	    height: 20px
	}
	.status--btn .switch input {
	    opacity: 0;
	    width: 0;
	    height: 0;
	    outline: none
	}
	.status--btn .slider {
	    position: absolute;
	    cursor: pointer;
	    top: 0;
	    left: 0;
	    right: 0;
	    bottom: 0;
	    background-color: #eaeaea;
	    -webkit-transition: .3s;
	    transition: .3s
	}
	.status--btn .slider:before {
	    position: absolute;
	    content: "";
	    height: 16px;
	    width: 16px;
	    left: 2px;
	    bottom: 2px;
	    background-color: #fff;
	    -webkit-transition: .3s;
	    transition: .3s
	}
	.status--btn input:checked+.slider {
	    background-color: #117886;
	}
	.status--btn input:focus+.slider {
	    box-shadow: 0 0 1px #117886;
	}
	.status--btn input:checked+.slider:before {
	    -webkit-transform: translateX(16px);
	    -ms-transform: translateX(16px);
	    transform: translateX(16px)
	}
	.status--btn .slider.round {
	    border-radius: 20px
	}
	.status--btn .slider.round:before {
	    border-radius: 50%
	}
	.status--btn .status--text {
	    font-size: 16px;
	    line-height: 1;
	    font-weight: 400;
	    margin-left: 15px
	}
	.status--btn {
	    justify-content: initial;
	}
	
	.switch_lanel {
	    font-size: 16px;
	}
	</style>
	<script>
	function filter_form_submit(event) {
	  // Prevent default form submission
	  event.preventDefault();
	  
	  var birth_year = validate_multiselect('birth_year', 'birth_year');
	  //console.log('birth_year');
	  //console.log(birth_year);
	  //return;
	
	  // Get form values
	  //const birth_year = document.getElementById("birth_year").value;
	  const gender = document.getElementById("gender").value;
	  const search_value = document.getElementById("search").value;
	  const queryParams = new URLSearchParams();
	
	  if (birth_year) queryParams.append("birth_year", birth_year);
	  if (gender) queryParams.append("gender", gender);
	  if (search_value) queryParams.append("search", search_value);
	  
	  // Construct the URL
	  const redirectUrl = `<?=get_url_without_pagination()?>?${queryParams.toString()}`;
	
	  // Redirect to the composed URL
	  window.location.href = redirectUrl;
	}
	</script>
	<style>
	.filter-h3 {
	    color: var(--Text-Body, #1B1D21);
	    font-family: Poppins;
	    font-size: 18px;
	    font-style: normal;
	    font-weight: 500;
	    line-height: 140%;
	    margin-bottom: 1px;
	}
	
	.etn-event-search-wrapper {
	    align-items: normal;
	    background: initial;
	    box-shadow: none;
	    margin-bottom: 30px;
	}
	
	@media (max-width: 1024px) {
	  .input-group-year    { order: 1; }
	  .input-group-gender  { order: 2; }
	  .input-group-search  { order: 3; }
	  .input-group-filter  { order: 4; }
	  .input-group-export  { order: 5; }
	  .input-group-total  { order: 6; }
	}
	
	.etn-event-search-wrapper .input-group {
	    display: initial;
    	padding: 0;
	    margin-right: 20px;
	}
	
	.etn_search_wrapper .etn-event-search-wrapper .search-button-wrapper button.etn-btn.etn-btn-primary, #load-more {
	    border-radius: var(--Corner-Medium-small, 10px);
	    background: var(--Accent-color-100, #17A0B2) !important;
	    padding: 9px 16px !important;
	    color: var(--Additional-colors-White, #FFF) !important;
	    font-family: "DM Sans";
	    font-size: 16px;
	    font-style: normal;
	    font-weight: 400;
	    line-height: 140%;
	    position: relative;
	    border: none;
	}
	
	.clear_all {
	    float: none;
	    padding-top: 0;
	    margin-top: 0;
	    color: #888;
	    text-decoration: none;
	    position: relative;
	    right: initial;
	}
	
	.etn-event-search-wrapper .etn_event_select {
	    border: 1px solid var(--border);
	    width: 100%;
	    font-style: italic;
		margin-top: 6px;
	}
	
	.etn-event-search-wrapper .search-input {
	    border: 1px solid var(--border);
	    width: 100%;
		margin-top: 6px;
	    border-radius: 15px !important;
	    height: 41px !important;
		background:#fff;
	}
	
	.etn-event-search-wrapper .search-button-wrapper {
	    align-items: baseline;
	    display: block;
	    justify-content: space-between;
		padding: 28px 0 0 0;
	}
	
	#filter_submit {
	    margin-right: 12px;
	}
	
	#export_CSV {
		float: right;
		
	}
	
	.export-csv-button {
		border-radius: var(--Corner-Medium-small, 10px);
		padding: 9px 16px;
		font-family: "DM Sans";
		font-size: 16px;
		font-style: normal;
		font-weight: 400;
		line-height: 140%;
		position: relative;
		border: none;

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
	
	.attendees-table-wrapper table.attendees-table.events-table tr th {
		font-weight: bold;
	}
	.attendees-table-wrapper table.attendees-table.events-table tr th a {
		text-decoration: none;
	}
	
	.column-sortable {
	  position: relative;
	  padding-right: 20px; /* space for arrows */
	  cursor: pointer;
	}
	
	.column-sortable::after {
	  content: "";
	  position: absolute;
	  right: 5px;
	  top: 50%;
	  transform: translateY(-50%);
	  width: 10px;
	  height: 12px;
	  background-image: url('/wp-content/themes/greenshift-child/img/arrows.svg');
	  background-size: contain;
	  background-repeat: no-repeat;
	  background-position: center;
	  pointer-events: none;
	}
	
	.clients-container input#search {
	    margin-top: initial;
	}
	
	.etn_search_shortcode.etn_search_wrapper .input-group-search {
	    margin-top: 40px;
	}
	
	.search-wrapper {
	  position: relative;
	  display: inline-block;
	}
	
	.search-wrapper::before {
	  content: '';
	  position: absolute;
	  left: 12px;
	  top: 50%;
	  transform: translateY(-50%);
	  width: 18px;
	  height: 18px;
	  background-image: url('/wp-content/themes/greenshift-child/img/Search_Magnifying_Glass.svg');
	  background-size: contain;
	  background-repeat: no-repeat;
	  pointer-events: none;
	}
	
	.search-wrapper input.search-input {
	  padding-left: 40px; /* Add space for the icon */
	  height: 40px;
	  border-radius: 20px;
	  border: none;
	  outline: none;
	  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
	}
	
	.clear_all {
		display: inline-block;
	}
	
	.etn_search_shortcode.etn_search_wrapper .input-group-total {
	    margin-top: 40px;
	    text-align: right;
		padding: 24px 0 0 0;
	}
	
	@media (max-width: 1024px) {
		.etn-event-search-wrapper .input-group {
		    margin-right: 0;
		}
		#filter_submit {
		    margin-right: 0;
		}
		.search-wrapper {
			width: 100%;
		}
		.search-button-wrapper {
			text-align: center;
		}
		.clear_all {
		    margin-top: 24px;
			display: block;
		}
		.export-csv-button {
		    float: none;
		}		
		.etn_search_shortcode.etn_search_wrapper .input-group-total {
			padding: 0;
		}
	}
	</style>
	<div class="etn_search_shortcode etn_search_wrapper">
		<a href="/users/" style="color:#8593a3">Coaches</a>
		<span style="color:#17a0b2;margin: 0 8px;">|</span>
		<a href="/clients/" style="color:#17a0b2">Clients</a>
	</div>
<style>
.multiselect-wrapper {
  width: 300px;
  position: relative;
  font-family: sans-serif;
}

.multiselect {
  border: 1px solid #ccc;
  padding: 5px;
  border-radius: 4px;
  min-height: 40px;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  cursor: pointer;
}

.selected-options {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  flex: 1;
}

.selected-options .option-tag {
  background-color: #eee;
  padding: 2px 6px;
  border-radius: 3px;
  display: flex;
  align-items: center;
}

.selected-options .option-tag span {
  margin-right: 5px;
}

.option-tag .remove-btn {
  cursor: pointer;
  font-weight: bold;
}

.dropdown-arrow {
  margin-left: auto;
  padding-left: 5px;
}

.options-dropdown {
  display: none;
  position: absolute;
  top: 100%;
  left: 0;
  z-index: 10;
  background: white;
  border: 1px solid #ccc;
  width: 100%;
  max-height: 200px;
  overflow-y: auto;
  border-radius: 0 0 4px 4px;
}

.options-dropdown label {
  display: block;
  padding: 5px 10px;
  cursor: pointer;
}

.options-dropdown label:hover {
  background: #f0f0f0;
}
</style>
<script>
function init_multiselects()
{
	document.querySelectorAll('.multiselect-wrapper').forEach(wrapper => {
	  const toggle = wrapper.querySelector('.multiselect');
	  const dropdown = wrapper.querySelector('.options-dropdown');
	  const selectedContainer = wrapper.querySelector('.selected-options');
	  const checkboxes = wrapper.querySelectorAll('input[type="checkbox"]');
	
	  // Toggle dropdown
	  toggle.addEventListener('click', () => {
	    const isOpen = dropdown.style.display === 'block';
	    document.querySelectorAll('.options-dropdown').forEach(d => d.style.display = 'none'); // close all others
	    dropdown.style.display = isOpen ? 'none' : 'block';
	  });
	
	  // Click outside to close
	  document.addEventListener('click', (e) => {
	    if (!wrapper.contains(e.target)) {
	      dropdown.style.display = 'none';
	    }
	  });
	
	  // Handle checkbox changes
	  checkboxes.forEach(checkbox => {
	    checkbox.addEventListener('change', () => {
	      const value = checkbox.value;
		  const label = checkbox.dataset.label || value;
	      if (checkbox.checked) {
	        if (!selectedContainer.querySelector(`[data-value="${value}"]`)) {
	          const tag = document.createElement('div');
	          tag.className = 'option-tag';
	          tag.dataset.value = value;
	          tag.innerHTML = `<span>${label}</span><!-- span class="remove-btn">&times;</span -->`;
	          //tag.querySelector('.remove-btn').addEventListener('click', () => {
	            //checkbox.checked = false;
	            //tag.remove();
	          //});
	          selectedContainer.appendChild(tag);
	        }
	      } else {
	        const tag = selectedContainer.querySelector(`[data-value="${value}"]`);
	        if (tag) tag.remove();
	      }
	    });
	  });
	});
}

function validate_multiselect(input_id, input_name)
{
	console.log('input_id - '); console.log(input_id);
	console.log('input_name - '); console.log(input_name);
	var input = document.getElementById(input_id);
	input.classList.remove('empty_value');
	
	var val = jQuery('input[name="' + input_name + '[]"]:checked')
		  .map(function() { console.log('this.value = '); console.log(this.value); return this.value; })
		  .get()
		  .join(',');
	
	return val;
}

document.addEventListener("DOMContentLoaded", function() {
	init_multiselects();
});
</script>



	<div class="etn_search_shortcode etn_search_wrapper">
		<h3 class="filter-h3" style="display: inline-block;">Filter by:</h3>
		<form onsubmit="javascript:filter_form_submit(event)" method="GET" class="etn_event_inline_form filter_form">
			<div class="etn-event-search-wrapper">
				<div class="input-group input-group-year">
					<label for="birth_year">Birth Year</label>
<?php
function render_birth_year_multiselect($input_id, $birth_years, $selected_values = []) {
    //if (!$birth_years) return;

    ?>
    <div class="multiselect-wrapper">
        <div class="multiselect" id="<?=$input_id?>">
            <div class="selected-options">
                <?php foreach ($selected_values as $value): ?>
                    <?php if (in_array($value, $birth_years)): ?>
                        <div class="option-tag" data-value="<?= esc_attr($value) ?>">
                            <span><?=$value?></span>
                            <!-- span class="remove-btn">&times;</span -->
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="dropdown-arrow">&#9662;</div>
        </div>
        <div class="options-dropdown">
            <?php foreach ($birth_years as $birth_year): ?>
                <label>
                    <input data-label="<?=$birth_year?>" type="checkbox" name="birth_year[]" value="<?=$birth_year?>"<?=in_array($birth_year, $selected_values) ? ' checked' : ''?>>
                    <?=$birth_year?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

	render_birth_year_multiselect('birth_year', $birth_years, $selected_values);
?>
				</div>
				<div class="input-group input-group-gender">
					<label for="etn_event_location">Gender</label>
					<select name="gender" id="gender" class="gender etn_event_select2 etn_event_select etn_event_location">
						<option value="">Select gender</option>
<?php
    foreach ($genders as $gender_option)
	{
		$selected = $gender_option == $gender ? ' selected' : '';
?>
						<option value="<?=$gender_option?>"<?=$selected?>><?=$gender_option?></option>
<?php
	}
?>
					</select>
				</div>
				<div class="input-group input-group-filter">
					<div class="search-button-wrapper">
						<button id="filter_submit" type="submit" class="etn-btn etn-btn-primary filter-submit">Filter Now</button>
						<a class="clear_all" href=".">Reset</a>
					</div>
				</div>
				<div class="input-group input-group-export">
					<div class="search-button-wrapper">
						<button id="export" type="button" class="export-csv-button">Export CSV</button>
					</div>
				</div>
				<div class="input-group input-group-search">
					<div class="search-wrapper">
						<input class="etn_event_text search-input" type="text" id="search" name="search" value=<?=$search?>>
					</div>
				</div>
				<div class="input-group input-group-dummy"></div>
				<div class="input-group input-group-dummy"></div>
				<div class="input-group input-group-total"><?=$attendees_count?> users</div>
			</div>
		</form>
	</div>
<?php
}
?>
<div class="dashboard-widget attendees events events-table-wrapper attendees-table-wrapper">
	<style>
	.attendees-table-th-1, .attendees-table-td-1 {
		
	}
	
	.dashboard-widget.events.events-table-wrapper table.attendees-table.events-table tbody td {
		text-align: initial;
	}	
	td.order-events-table-td-3 input, td.order-events-table-td-4 input, td.order-events-table-td-5 input  {
		border-radius: var(--Corner-Medium-small, 10px);
		border: 1px solid var(--Gray-Palette-80, #A8B5C2);
		background: var(--Additional-colors-White, #FFF);
		color: var(--Gray-Palette-90, #8593A3);
		font-feature-settings: 'clig' off, 'liga' off;
		font-family: "DM Sans";
		font-size: 14px;
		font-style: italic;
		font-weight: 400;
		line-height: 140%;
	}
	
	.attendees-table a {
		text-decoration: none;
	}
	.attendees-table a:hover {
		text-decoration: underline;
	}
	</style>
	<div class="attendees-box">
	<script>
	document.getElementById('export').addEventListener('click', function () {
		const url = new URL(window.location.href);
		url.searchParams.set('export_csv', '1');
		window.location.href = url.toString();
	});
	</script>	
	<table class="events-table attendees-table"><!--  -->
		<thead>
		<tr>
			<th class="attendees-table-th-1 column-sortable" scope="col"><a href="?sort=last_name">Parent Last Name</a></th>
			<th class="attendees-table-th-2 column-sortable" scope="col"><a href="?sort=first_name">Parent First Name</a></th>
			<th class="attendees-table-th-3" scope="col">Email</th>
			<th class="attendees-table-th-4" scope="col">Phone Number</th>
			<th class="attendees-table-th-5 column-sortable" scope="col"><a href="?sort=attendee_last_name">Player Last Name</a></th>
			<th class="attendees-table-th-6 column-sortable" scope="col"><a href="?sort=attendee_first_name">Player First Name</a></th>
			<th class="attendees-table-th-7 column-sortable" scope="col"><a href="?sort=year">Birth year</a></th>
			<th class="attendees-table-th-8" scope="col">Gender</th>
		</tr>
		</thead>
		<tbody id="orders-body">
<?php
	//foreach($unique as $attendee)
	for($i = $offset; $i < $max_index; $i++)
	{
		if(!isset($unique[$i]))
			break;
?>
		<tr class="user_parent user_<?=$unique[$i]['ID']?>" id="user_parent_<?=$unique[$i]['ID']?>">
			<td class="order-events-table-td-1" title="<?=$unique[$i]['display_name']?>"><?=$unique[$i]['last_name']?></td>
			<td class="order-events-table-td-2" title="<?=$unique[$i]['display_name']?>"><?=$unique[$i]['first_name']?></td>
			<td class="order-events-table-td-3""><a href="/edit-profile/<?=$unique[$i]['user_id']?>/"><?=$unique[$i]['email']?></a></td>
			<td class="order-events-table-td-4""><?=$unique[$i]['phone']?></td>
			<td class="order-events-table-td-5""><?=$unique[$i]['attendee_last_name']?></td>
			<td class="order-events-table-td-6"><?=$unique[$i]['attendee_first_name']?></td>
			<td class="order-events-table-td-6"><?=$unique[$i]['year']?></td>
			<td class="order-events-table-td-6"><?=$unique[$i]['gender']?></td>
		</tr>
<?php
	}
?>
		</tbody>
	</table>
	</div>
<?php
if($attendees_count > $attendees_max)
{
	$total_pages = $attendees_count ? ceil($attendees_count / $attendees_max) : 0;
	$paginate_links = paginate_links(array(
	    'total'     => $total_pages,
	    'current'   => $paged,
	    'prev_text' => '&lt;', // "<"
	    'next_text' => '&gt;', // ">"
	));
?>
	<div class="pagination-container"><?=$paginate_links?></div>
<?php
}
?>
</div>
<br>
<br>
<br>
<br>

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
