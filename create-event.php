<?php
date_default_timezone_set('America/New_York');

function generate_weekday_checkboxes($recurrence_weekly_day = FALSE)
{
	if(!$recurrence_weekly_day)
		$recurrence_weekly_day = array();
    $weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    
    foreach ($weekdays as $index => $day)
	{
		$checked = in_array($index, $recurrence_weekly_day) ? ' checked' : '';
?>
        <label class="weekday-checkbox">
        	<input type="checkbox" name="weekday[]" id="weekday_<?=$index?>" class="recurrence_weekly_day" value="<?=$index?>"<?=$checked?>>
			<?=$day?>
		</label>
<?php
    }
}

function get_post_term($post_id, $term_taxonomy) {
    global $wpdb;

    // Prepare the SQL query to fetch the term names associated with the post ID and taxonomy.
    $query = $wpdb->prepare("
        SELECT t.name 
        FROM {$wpdb->prefix}term_relationships AS tr
        INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->prefix}terms AS t ON tt.term_id = t.term_id
        WHERE tr.object_id = %d AND tt.taxonomy = %s
    ", $post_id, $term_taxonomy);

    // Execute the query and get the results.
    $terms = $wpdb->get_col($query);

    // Return the array of term names (or an empty array if none are found).
    return $terms;
}

	$current_user_id = get_current_user_id();
	$post_title = $post_content = $age_group = $level = $total_capacity = 
	$room_area = $date_start_normal = $date_end_normal = $date_start = $date_end = $time_start = $time_end = 
	$price = $lesson_price = '';
	$ID = $event_id = $event_category = $event_activity = $event_location = 0;
	$recurrence_weekly_day = array();
	
	if(!empty($_REQUEST['date']))
	{
		$date_start = $date_end = reformat_to_american_date($_REQUEST['date']);
	}
	
	$post_status = 'publish';
	/*
	$lesson_price = 'Private (1-on-1): $100' . "<br>" . '
2-3 (Group): $80 per' . "<br>" . '
4-6 (Group): $75 per' . "<br>" . '
6-8 (Group): $70 per';
	*/
	
	$lesson_price = 'Private (1-on-1): $195' . "<br>" . '
2 (Group): $110 per' . "<br>" . '
3 (Group): $90 per' . "<br>" . '
4 (Group): $80 per' . "<br>" . '
5 (Group): $70 per' . "<br>" . '
6 (Group): $60 per';
	
	//vd($_REQUEST['event'], '$_REQUEST[\'event\']');
	
	if(!empty($_REQUEST['event']))
	{
		$event_post = get_post(intval($_REQUEST['event']));
		
		//vd($event_post, '$event_post 1');
		
		if($event_post)
		{
			if(current_user_can('edit_post', $_REQUEST['event']) || $event_post->post_author == $current_user_id)
			{
				//$post_status = $event_post->post_status;
				$event_id = $event_post->ID;
			}
		}
	}
	
	if($event_id)
	{
		$post_title = $event_post->post_title;
		$post_content = $event_post->post_content;
		
		$recurrence_weekly_day = get_post_meta($event_id, 'recurrence_weekly_day', true);
		
		$date_start_normal = $date_start = get_post_meta($event_id, 'etn_start_date', true);
		vd($date_start_normal, '$date_start_normal');
		if($date_start)
			$date_start = reformat_to_american_date($date_start);
		
		$time_start = get_post_meta($event_id, 'etn_start_time', true);
		
		$date_end_normal = $date_end = get_post_meta($event_id, 'etn_end_date', true);
		
		if($date_end)
			$date_end = reformat_to_american_date($date_end);
		
		$time_end = get_post_meta($event_id, 'etn_end_time', true);
		$lesson_price = get_post_meta($event_id, 'lesson_price', true);
		
		$etn_location = get_the_terms($event_id, 'etn_location');
		$categories = get_the_terms($event_id, 'etn_category');
		
		$category = get_post_term($event_id, 'etn_category');
		$location = get_the_terms($event_id, 'etn_location');
		
		if(!empty($categories[0]))
			$event_category = $categories[0]->term_id;
		
		//vd($event_category, '$event_category');
		
		$price = get_event_price($event_id);
		//vd($price, '$price');
		
		$tags = wp_get_post_terms($event_id, 'etn_tags');
		if(!empty($tags[0]))
			$event_activity = $tags[0]->term_id;
		
		//$etn_tags = get_post_term($event_id, 'etn_tags');
		
		$age_group = get_post_meta($event_id, 'age_group', true);
		$level = get_post_meta($event_id, 'level', true);
		$event_waiver = get_post_meta($event_id, 'waiver', true);
		
		$tshirt_size = get_post_meta($event_id, 'tshirt_size', true);
		$jersey_size = get_post_meta($event_id, 'jersey_size', true);
		$pants_size = get_post_meta($event_id, 'pants_size', true);
		$shorts_size = get_post_meta($event_id, 'shorts_size', true);
		
		$etn_location_arr = get_post_meta($event_id, 'etn_location', true);
		if(!empty($etn_location_arr[0]))
			$event_location = $etn_location_arr[0];
		
		$room_area = get_post_meta($event_id, 'room_area', true);
		
		$total_capacity = get_post_meta($event_id, 'etn_total_avaiilable_tickets', true);
		if(!$total_capacity)
			$total_capacity = 0;
		/*
		$price = get_post_meta($event_id, '_price', true);
		if(!$price)
			$price = '';
		*/
		$recurring_enabled = get_post_meta($event_id, 'recurring_enabled', true);
		$recurring_event = $recurring_enabled == 'yes' ? 1 : 0;
		
		$checkbox_public_view = get_post_meta($event_id, 'checkbox_public_view', true);
	}
?>

<style>
.empty_value {
	border: 1px solid #f00!important;
}

.waiting {
    position: relative;
    display: inline-block;
}

.waiting::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;  /* Adjust the size */
    height: 20px;
    margin-left: -10px; /* Center the spinner horizontally */
    margin-top: -10px;  /* Center the spinner vertically */
    border: 3px solid rgba(0, 0, 0, 0.3);
    border-radius: 50%;
    border-top-color: #000;  /* Spinner color */
    animation: spin 1s ease infinite; /* Rotating animation */
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
<script>
var bulk_posts = [];

function parseTicketVariations(text, total_capacity) {
    const cleanedText = text.replace(/<\/?p>/g, '');
    
    const lines = cleanedText.split('<br />');
	console.log('lines');
	console.log(lines);
    
    const ticketVariations = [];
    
    const regex = /(.+): \$(\d+)/;
    
    for (const line of lines) {
        const match = line.match(regex);
        if (match) {
            const ticketName = match[1].trim();
            const ticketPrice = parseInt(match[2], 10);
            
			console.log('ticketName');
			console.log(ticketName);
			
			if(ticketName.indexOf('Private') !== -1)
			{
	            const ticketObject = {
	                etn_ticket_name: ticketName,
	                etn_ticket_price: ticketPrice,
	                etn_avaiilable_tickets: total_capacity,
	                etn_min_ticket: '',
	                etn_max_ticket: '',
					start_date: '1970-01-01',
					end_date: '2070-01-01'
	            };
	            ticketVariations.push(ticketObject);
				break;
			}
        }
    }
    
	console.log('ticketVariations');
	console.log(ticketVariations);
    return ticketVariations;
}

function pre_save_post_rest_api(event)
{
	event.preventDefault();
	save_post_rest_api(event);
}

var inputs_are_valid = true;

function validate_input(input_id)
{
	var input = document.getElementById(input_id);
	input.classList.remove('empty_value');
	
	var select2_container = input.parentNode.querySelector('.select2-container');
	if(select2_container)
		select2_container.classList.remove('empty_value');
	else
		input.classList.remove('empty_value');
	
	var val = input.value;
	if(val == '')
	{
		if(inputs_are_valid)
		{
			console.log('validate_input(' + input_id + ')');
		    jQuery('html, body').animate({
		        scrollTop: jQuery('#' + input_id).offset().top
		    }, 1000);
		}
		
		inputs_are_valid = false;
		if(select2_container)
			select2_container.classList.add('empty_value');
		else
			input.classList.add('empty_value');
	}
	
	return val;
}

function validate_area(input_id)
{
	var input = document.getElementById(input_id);
	input.classList.remove('empty_value');
	
	if(typeof tinymce !== 'undefined')
	{
		var val = tinymce.get(input_id).getContent();
	}
	else if(typeof CKEDITOR !== 'undefined')
	{
		eval('var val = CKEDITOR.instances.' + input_id + '.getData();');
	}
	else
	{
		var val = jQuery('#' + input_id).val();
	}
	
	if(val == '')
	{
		if(inputs_are_valid)
		{
		    jQuery('html, body').animate({
		        scrollTop: jQuery('#' + input_id).offset().top
		    }, 1000);
		}
		
		inputs_are_valid = false;
		input.classList.add('empty_value');
	}
	
	return val;
}

function reformat_date(originalDate)
{
	//return originalDate;
	if (originalDate.includes('-'))
	{
	    var parts = originalDate.split('-');
	    
	    if (parts.length !== 3) {
	        return originalDate;
	    }
	    
	    const [year, month, day] = parts;
	    return `${month}/${day}/${year}`;
	}
	else if (originalDate.includes('/'))
	{
		var parts = originalDate.split("/");
		if (parts.length === 3 && parts[2] && parts[0] && parts[1]) {
		    var reformattedDate = `${parts[2]}-${parts[0]}-${parts[1]}`;
		    console.log(reformattedDate);
			return reformattedDate;
		} else {
		    console.error("Invalid date format");
			return originalDate;
		}
	}
	else
		return originalDate;
}

function arraysAreEqual(arr1, arr2) {
    if (arr1.length !== arr2.length) {
        return false;
    }
	
    for (let i = 0; i < arr1.length; i++) {
        if (arr1[i] !== arr2[i]) {
            return false;
        }
    }
	
    return true;
}

var recreate_recurring_events = false;

function save_post_rest_api(event)
{
	if(event)
		event.preventDefault();
	
	console.log('save_post_rest_api()');
	inputs_are_valid = true;

	var post_title = validate_input('post_title');
	var post_content = validate_area('post_content');
	
	console.log('post_content');
	console.log(post_content);
	
	var featured_image = jQuery('#featured_image').val();
	
	var event_type = validate_input('etn_event_type');
	
	var etn_category = [event_type];
	
	var activity_type = validate_input('etn_event_activity_type');
	
	var etn_tags = [activity_type];
	var waiver = validate_input('waiver');
	if(waiver == -1)
	{
		var waiver_title = validate_input('waiver_title');
		var waiver_content = validate_area('waiver_content');
	}
	else
	{
		var waiver_title = jQuery('#waiver_title').val();
		var waiver_content = jQuery('#waiver_content').val();
	}
	
	var recurring_enabled = jQuery('#recurring_event').prop('checked') ? 'yes' :  'no';
	
		var recurrenceData = {
		  recurrence_freq: "week",
		  recurrence_span: 1,
		  recurrence_ends_on: "event_end",
		  recurring_thumb: "no",
		  recurrence_weekly_day: [1]
		};
		
		var jsonData = JSON.stringify(recurrenceData);
		
	var etn_event_recurrence = jsonData;
	var recurrenceData = null;
	
	//if(recurring_enabled == 'yes')
	{
		var recurrence_weekly_days_arr = new Array();
		
	    var recurrence_weekly_day = document.querySelectorAll('.recurrence_weekly_day');
		var j = 0;
		if(recurrence_weekly_day)
		{
			for(var i = 0; i < recurrence_weekly_day.length; i++)
			{
				if(recurrence_weekly_day[i].checked)
				{
					recurrence_weekly_days_arr[j] = parseInt(recurrence_weekly_day[i].value, 10);
					j++;
				}
			}
		}
		
		console.log('recurrence_weekly_days_arr');
		console.log(recurrence_weekly_days_arr);
<?php
		$recurrence_weekly_day_int = array();
		if($recurrence_weekly_day)
		{
			foreach($recurrence_weekly_day as $key => $day)
				$recurrence_weekly_day_int[$key] = intval($day);
		}

?>
<?php
	if($event_id)
	{
?>
		var recurrence_weekly_day = <?php echo json_encode($recurrence_weekly_day_int); ?>;
		console.log('$recurrence_weekly_day');
		console.log(recurrence_weekly_day);
		
		if(!arraysAreEqual(recurrence_weekly_days_arr, recurrence_weekly_day))
			recreate_recurring_events = true; 
		//alert('recreate_recurring_events ' + recreate_recurring_events);
		
		/*
		$recurrence_weekly_day
		<?php var_dump($recurrence_weekly_day) ?>
		*/
<?php
		if($recurrence_weekly_day)
		{
			foreach($recurrence_weekly_day as $day)
			{
?>
		console.log('<?=$day?>');
<?php
			}
		}
	}
?>
		//return false;
		
		recurrenceData = {
		    recurrence_freq: jQuery('#recurrence_freq').val(),
		    recurrence_span: jQuery('#recurrence_span').val(),
		    recurrence_ends_on: "event_end",
		    recurring_thumb: "no",
		    recurrence_weekly_day: recurrence_weekly_days_arr
		};	
		console.log('etn_event_recurrence');
		console.log(etn_event_recurrence);
		console.log('recurrence_weekly_days_arr');
		console.log(recurrence_weekly_days_arr);
	}
	
	var etn_event_location = jQuery('#etn_event_location').val();
	var etn_event_location_type = jQuery('#etn_event_location_type').val();
	
	var etn_location_val = validate_input('etn_location');
	var etn_location = [etn_location_val];
	
	if(etn_location_val == 'add_new')
	{
		var street_address_line_1 = validate_input('street_address_line_1');
		var street_address_line_2 = jQuery('#street_address_line_2').val();
		var city = validate_input('city');
		var state = validate_input('state');
		var zip = validate_input('zip');
	}
	else
	{
		var street_address_line_1 = jQuery('#street_address_line_1').val();
		var street_address_line_2 = jQuery('#street_address_line_2').val();
		var city = jQuery('#city').val();
		var state = jQuery('#state').val();
		var zip = jQuery('#zip').val();
	}
	
	var total_capacity = validate_input('total_capacity');
	
	console.log('event_type'); 
	console.log(event_type);
	
	if(event_type == 31)
	{
		var event_price = jQuery('#event_price').val();
		var lesson_price = validate_area('lesson_price');
	}
	else
	{
		var event_price = validate_input('event_price');
		if(typeof tinymce !== 'undefined')
			var lesson_price = tinymce.get('lesson_price').getContent();
		else if(typeof CKEDITOR !== 'undefined')
			var lesson_price = CKEDITOR.instances.lesson_price.getData();
		else
			var lesson_price = jQuery('#lesson_price').val();
	}
	//alert('lesson_price ' + lesson_price);
	if(event_type == 31)
	{
		var event_price = jQuery('#event_price').val();
		var lesson_price = validate_area('lesson_price');
	}
	
	if(event_type == 26)
		var etn_start_date = jQuery('#date_start').val();
	else
		var etn_start_date = validate_input('date_start');
	
	etn_start_date = reformat_date(etn_start_date);
	
	if(recurring_enabled == 'yes')
		var etn_end_date = validate_input('date_end');
	else
		var etn_end_date = jQuery('#date_end').val();
	
	etn_end_date = reformat_date(etn_end_date);
	
	if(etn_end_date == '')
		etn_end_date = etn_start_date;
	
<?php
	if($event_id)
	{
?>
	if(recurring_enabled == 'yes')
	{
		if(etn_start_date !== '<?=$date_start_normal?>' || etn_end_date !== '<?=$date_end_normal?>')
		{
			recreate_recurring_events = true;
		}
	}
<?php
	}
?>
	var etn_start_time = validate_input('time_start');
	var etn_end_time = validate_input('time_end');
	
	//'existing_location'
	
	//return;
	if(!inputs_are_valid)
		return false;
	
	waiting(1, 0);
	
	var bodyData = {
	    post_author: '<?=$current_user_id?>',
	    post_type: 'etn',
	    post_status: '<?=$post_status?>',
	    post_title: post_title,
	    post_content: post_content,
	    _thumbnail_id: featured_image,
	    etn_event_schedule: [],
	    etn_category: etn_category,
	    etn_event_location_type: etn_event_location_type,
	    etn_location: etn_location,
	    etn_tags: etn_tags,
	    etn_event_organizer: '',
	    etn_event_speaker: '',
	    etn_event_socials: [],
	    etn_event_logo: '',
	    etn_event_location: etn_event_location,
	    etn_is_virtual: '',
	    event_etzone: 'America/New_York',
	    etn_start_time: etn_start_time,
	    etn_end_time: etn_end_time,
	    etn_start_date: etn_start_date,
	    etn_end_date: etn_end_date,
	    etn_registration_deadline: '',
	    recurring_enabled: recurring_enabled,
	    etn_event_recurrence: recurrenceData,
	    etn_ticket_availability: false,
	    etn_ticket_variations: [{
	        etn_ticket_name: post_title + ' - ticket',
	        etn_ticket_price: event_price,
	        etn_avaiilable_tickets: total_capacity,
	        etn_min_ticket: '',
	        etn_max_ticket: '',
			start_date: '1970-01-01',
			end_date: '2070-01-01'
	    }],
	    banner_bg_image: '',
	    _etn_buddy_group_id: '',
	    event_external_link: '',
	    etn_google_meet: 'no',
	    etn_google_meet_link: '',
	    etn_google_meet_short_description: '',
	    etn_select_speaker_schedule_type: '',
	    etn_event_faq: null,
	    etn_event_certificate: 0,
		event_id: <?=$event_id?>,
		etn_custom_create: 1,
		address: street_address_line_1,
		street_address_line_1: street_address_line_1,
		street_address_line_2: street_address_line_2,
		city: city,
		state: state,
		zip: zip,
		waiver: waiver,
		waiver_title: waiver_title,
		waiver_content: waiver_content,
	};
	
	//alert(etn_category);
	//return;
	
	if(etn_category == 31)//Lesson
	{
		var etn_ticket_variations = parseTicketVariations(lesson_price, total_capacity);
		console.log('etn_ticket_variations');
		console.log(etn_ticket_variations);
		bodyData.etn_ticket_variations = etn_ticket_variations;
	}
	
	//show_message_box('Success', etn_event_location_type);
	//return;
	
	if(etn_event_location_type == 'new_location')
	{
		save_location_ajax(bodyData);
	}
	else
	{
		if(etn_category == 26)//subscription
			create_subscription_ajax(bodyData);
		else
			save_post_rest_api_call(bodyData);
	}
}

var save_post_meta_loading = 0;

function save_post_meta_ajax(bodyData, post_id, message)
{
	//alert('event_bulk_edit ' + event_bulk_edit);
	console.log('save_post_meta_ajax()');

	var room_area = jQuery('#room_area').val();
	var age_group = jQuery('#age_group').val();
	var level = jQuery('#level').val();
	var waiver = jQuery('#waiver').val();
	var waiver_title = jQuery('#waiver_title').val();
	var waiver_content = jQuery('#waiver_content').val();
	var checkbox_public_view = jQuery('#checkbox_public_view').prop('checked') ? 1 : 0;
	var tshirt_size = jQuery('#tshirt_size').prop('checked') ? 1 : 0;
	var jersey_size = jQuery('#jersey_size').prop('checked') ? 1 : 0;
	var shorts_size = jQuery('#shorts_size').prop('checked') ? 1 : 0;
	var pants_size = jQuery('#pants_size').prop('checked') ? 1 : 0;
	var featured_image = jQuery('#featured_image').val();
	
	if(typeof tinymce !== 'undefined')
	{
		var lesson_price = tinymce.get('lesson_price').getContent();
	}
	else if(typeof CKEDITOR !== 'undefined')
	{
		var lesson_price = CKEDITOR.instances.lesson_price.getData();
	}
	else
	{
		var lesson_price = jQuery('#lesson_price').val();
	}
	
	var post_data = {
			bodyData: bodyData,
			post_id: post_id,
			room_area: room_area,
			age_group: age_group,
			level: level,
			waiver: waiver,
			waiver_title: waiver_title,
			waiver_content: waiver_content,
			checkbox_public_view: checkbox_public_view,
			tshirt_size: tshirt_size,
			jersey_size: jersey_size,
			shorts_size: shorts_size,
			pants_size: pants_size,
			lesson_price: lesson_price,
			featured_image: featured_image,
			event_bulk_edit: event_bulk_edit,
			message: message,
			security: '<?php echo wp_create_nonce('edit-post-nonce'); ?>',
		};
	
	//featured_image
	
	console.log('post_data');
	console.log(post_data);
	
	jQuery.ajax({
		type: 'POST',
		dataType: 'html',
		url: '<?=get_stylesheet_directory_uri()?>/wph_save_post_meta_ajax.php',
		data: post_data,
		beforeSend : function () {
			save_post_meta_loading = 1;
		},
		success: function (data)
		{
			save_post_meta_loading = 0;
			waiting(0, 0);
			if(data)
			{
				var response = JSON.parse(data);
				console.dir('response');
				console.dir(response);
				
				//if(0)
				if(response.success)
				{
					if(response.message)
						show_system_message(
											'Event Saved.', 
											response.message, 
											'success', 
											'Back To Events',
											'/events/', 
											'Create an Event',
											'/create-event/'
						);
					else
						show_system_message(
											'Event Saved.', 
											'',
											'success',
											'Back To Events',
											'/events/', 
											'Create an Event',
											'/create-event/'
						);
				}
				else
				{
					if(response.message)
						show_system_message(
											'Event hasn\'t been created', 
											'[span]Error![/span] Your event has not been created. Go back to the event and check if everything is filled in correctly.<br>[span]' + response.message + '[/span]', 
											'error',
											'Continue Editing',
											'', 
											'Create an Event',
											'/create-event/'
						);
					else
						show_system_message(
											'Event hasn\'t been created', 
											'[span]Error![/span] Your event has not been created. Go back to the event and check if everything is filled in correctly.', 
											'error',
											'Continue Editing',
											'', 
											'Create an Event',
											'/create-event/'
						);
				}
			}
			else
			{	
			}
		},
		error : function (jqXHR, textStatus, errorThrown) {
			waiting(0, 0);
			show_system_message(
								'Error', 
								'Something wrong', 
								'error',
								'',
								'', 
								'Close',
								''
			);
			save_post_meta_loading = 0;
			console.log(jqXHR);
		},
	});
	
	return false;
}

function create_subscription_ajax(bodyData)
{
	//alert('event_bulk_edit ' + event_bulk_edit);
	console.log('create_subscription_ajax()');

	var room_area = jQuery('#room_area').val();
	var age_group = jQuery('#age_group').val();
	var level = jQuery('#level').val();
	var waiver = jQuery('#waiver').val();
	var checkbox_public_view = jQuery('#checkbox_public_view').prop('checked') ? 1 : 0;
	var tshirt_size = jQuery('#tshirt_size').prop('checked') ? 1 : 0;
	var jersey_size = jQuery('#jersey_size').prop('checked') ? 1 : 0;
	var shorts_size = jQuery('#shorts_size').prop('checked') ? 1 : 0;
	var pants_size = jQuery('#pants_size').prop('checked') ? 1 : 0;
	var featured_image = jQuery('#featured_image').val();
	var event_price = jQuery('#event_price').val();
	
	var post_data = {
			bodyData: bodyData,
			room_area: room_area,
			age_group: age_group,
			level: level,
			waiver: waiver,
			checkbox_public_view: checkbox_public_view,
			event_price: event_price,
			tshirt_size: tshirt_size,
			jersey_size: jersey_size,
			shorts_size: shorts_size,
			pants_size: pants_size,
			pants_size: pants_size,
			featured_image: featured_image,
			event_bulk_edit: event_bulk_edit,
			security: '<?php echo wp_create_nonce('edit-post-nonce'); ?>',
		};
	
	//featured_image
	
	console.log('post_data');
	console.log(post_data);
	
	jQuery.ajax({
		type: 'POST',
		dataType: 'html',
		url: '<?=get_stylesheet_directory_uri()?>/wph_create_subscription_ajax.php',
		data: post_data,
		beforeSend : function () {
			save_post_meta_loading = 1;
		},
		success: function (data)
		{
			waiting(0, 0);
			save_post_meta_loading = 0;
			if(data)
			{
				var response = JSON.parse(data);
				console.dir('response');
				console.dir(response);
				
				//if(0)
				if(response.success)
				{
					if(response.message)
						show_system_message(
											'Subscription Saved.', 
											response.message, 
											'success', 
											'Back To Events',
											'/events/', 
											'Create an Event',
											'/create-event/'
						);
					else
						show_system_message(
											'Subscription Saved.', 
											'',
											'success',
											'Back To Events',
											'/events/', 
											'Create an Event',
											'/create-event/'
						);
				}
				else
				{
					if(response.message)
						show_system_message(
											'Subscription hasn\'t been created', 
											'[span]Error![/span] Your event has not been created. Go back to the event and check if everything is filled in correctly.<br>[span]' + response.message + '[/span]', 
											'error',
											'Continue Editing',
											'', 
											'Create an Event',
											'/create-event/'
						);
					else
						show_system_message(
											'Subscription hasn\'t been created', 
											'[span]Error![/span] Your event has not been created. Go back to the event and check if everything is filled in correctly.', 
											'error',
											'Continue Editing',
											'', 
											'Create an Event',
											'/create-event/'
						);
				}
			}
			else
			{	
			}
		},
		error : function (jqXHR, textStatus, errorThrown) {
			waiting(0, 0);
			show_system_message(
								'Error', 
								'Something wrong', 
								'error',
								'',
								'', 
								'Close',
								''
			);
			save_post_meta_loading = 0;
			console.log(jqXHR);
		},
	});
	
	return false;
}

function delete_events(event_id)
{
	console.log('delete_events()');
	
	var post_data = {
			event_id: event_id,
			event_bulk_edit: event_bulk_edit,
			security: '<?php echo wp_create_nonce('edit-post-nonce'); ?>',
		};
	
	//featured_image
	
	console.log('post_data');
	console.log(post_data);
	
	jQuery.ajax({
		type: 'POST',
		dataType: 'html',
		url: '<?=get_stylesheet_directory_uri()?>/wph_delete_events_ajax.php',
		data: post_data,
		beforeSend : function () {
			save_post_meta_loading = 1;
		},
		success: function (data)
		{
			if(data)
			{
				var response = JSON.parse(data);
				console.dir('delete_events response');
				console.dir(response);
				
				//if(0)
				if(response.success)
				{
				}
				else
				{
				}
			}
			else
			{	
			}
		},
		error : function (jqXHR, textStatus, errorThrown) {
			save_post_meta_loading = 0;
			console.log(jqXHR);
		},
	});
	
	return false;
}

function save_post_rest_api_call(bodyData)
{
	console.log('save_post_rest_api_call() bodyData');
	console.log(bodyData);
	
<?php
	if($event_id)
	{
?>
	var rest_action = 'update';
<?php
	}
	else
	{
?>
	var rest_action = 'create';
<?php
	}
	
	if($event_id)
	{
?>
	if(recreate_recurring_events)
	{
		delete_events(<?=$event_id?>);
		rest_action = 'create';
	}
<?php
	}
?>
		
	//alert(rest_action);
	//return false;

	//show_message_box('Success', 'save_post_rest_api_call');
	//return;
	const method = 'POST';
	const route = '/wp-json/eventin/v1/events/' + rest_action;
	const baseUrl = window.location.origin;
	const url = baseUrl + route;
	
	const authorization = 'Basic ZGVtbzphY2Nlc3M=';

	const fetchOptions = {
	    method: method,
	    headers: {
	        'Content-Type': 'application/json',
	        'Authorization': authorization,
	    },
	    body: JSON.stringify(bodyData),
	};

	fetch(url, fetchOptions)
    	.then(response =>
		{
        	if (!response.ok)
			{
				waiting(0, 0);
				show_message_box('Error', 'Network response was not ok');
	            throw new Error('Network response was not ok');
	        }
		
        	return response.json();
	    })
	    .then(data => {
	        console.log('Response data:', data);
			
			if(data.status_code == 1 && data.content.event.id !== undefined && data.content.event.id)
			{
				var message = data.messages[0] !== undefined ? data.messages[0] : '';
				save_post_meta_ajax(bodyData, data.content.event.id, message)
			}
			else
			{
				if(data.messages[0] !== undefined)
					show_system_message(
										'Event hasn\'t been created', 
										'[span]Error![/span] ' + data.messages[0], 
										'error',
										'Continue Editing',
										'', 
										'Create an Event',
										'/create-event/'
					);
			}
	    })
	    .catch(error => {
	        //console.error('There was a problem with the fetch operation:', error);
			waiting(0, 0);
			show_message_box('Error', error);
	    });
}

var save_location_loading = 0;

function save_location_ajax(bodyData)
{
	console.log('save_location_ajax()');
	console.log('bodyData');
	console.log(bodyData);
	jQuery.ajax({
		type: 'POST',
		dataType: 'html',
		url: '<?=get_stylesheet_directory_uri()?>/wph_create_event_location_ajax.php',
		data: bodyData,
		beforeSend : function () {
			save_location_loading = 1;
		},
		success: function (data)
		{
			save_location_loading = 0;
			if(data)
			{
				var response = JSON.parse(data);
				console.dir('save_location_ajax() response');
				console.dir(response);
				
				if(response.success)
				{
					if(response.bodyData)
					{
						console.log('before save_post_rest_api_call()');
						console.log('response.bodyData');
						console.log(response.bodyData);
						
						save_post_rest_api_call(response.bodyData);
					}
					else
					{
						waiting(0, 0);
						show_message_box('Error', 'No response.bodyData');
					}
				}
				else
				{
					waiting(0, 0);
					if(response.message)
						show_message_box('Error', response.message);
					else
						show_message_box('Error', 'Location not saved');
				}
			}
			else
			{	
			}
		},
		error : function (jqXHR, textStatus, errorThrown) {
			save_location_loading = 0;
			console.log(jqXHR);
		},
	});
	
	return false;
}

function prepare_variables()
{
	var post_title = jQuery('#post_title').val();

	if(typeof tinymce !== 'undefined')
	{
		var post_content = tinymce.get('post_content').getContent();
	}
	else if(typeof CKEDITOR !== 'undefined')
	{
		var post_content = CKEDITOR.instances.post_content.getData();
	}
	else
	{
		var post_content = jQuery('#post_content').val();
	}
	
	var featured_image = jQuery('#featured_image').val();
	var event_type = jQuery('#etn_event_type').val();
	var activity_type = jQuery('#etn_event_activity_type').val();
	
	var post_data = {
			post_id: <?=$event_id?>,
			post_title: post_title,
			post_content: post_content,
			featured_image: featured_image,
			event_type: event_type,
			security: '<?php echo wp_create_nonce('edit-post-nonce'); ?>',
		};
	
	return post_data;
}
</script>
   <style>
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

       #event_price {
           padding-left: 20px; /* Adjust this value as needed */
           /*box-sizing: border-box;*/
       }
   </style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('event_price');
    var wrapper = document.querySelector('.dollar-input-wrapper');

    function setCaretPosition(ctrl, pos) {
        if (ctrl.setSelectionRange) {
            ctrl.focus();
            ctrl.setSelectionRange(pos, pos);
        } else if (ctrl.createTextRange) {
            var range = ctrl.createTextRange();
            range.collapse(true);
            range.moveEnd('character', pos);
            range.moveStart('character', pos);
            range.select();
        }
    }

    function toggleDollarSign() {
        if (input.value === '') {
            wrapper.classList.add('hide-dollar');
			first_input = true;
        } else {
            wrapper.classList.remove('hide-dollar');
        }
    }
	
	function formatValue(value) {
	    if (!value) return '';
	
	    value = value.replace(/[^\d.]/g, '');
		console.log('value before');
		console.log(value);
	
	    var parts = value.split('.');
	    value = parts[0] + (parts[1] ? '.' + parts[1].slice(0, 2) : '');
	
	    if (value === '.' || value === '') {
	        return '';
	    }
	
	    return parseFloat(value).toFixed(2);
	}					
	
	var first_input = true;

    input.addEventListener('input', function (e) {
        var value = input.value;
		console.log('value');
		console.log(value);
        var caretPosition = input.selectionStart;

        // Calculate new value and format it
        var newValue = formatValue(value);

        // Set the formatted value as the input value
        input.value = newValue;
		console.log('newValue');
		console.log(newValue);

        // Restore caret position
        var newCaretPosition = caretPosition;
        if (newValue.length > value.length) {
            newCaretPosition += newValue.length - value.length;
			if(first_input)
			{
				newCaretPosition -= 3;
				first_input = false;
			}
			console.log('newCaretPosition 1');
			console.log(newCaretPosition);
        } else if (newValue.length < value.length) {
            newCaretPosition -= value.length - newValue.length;
			console.log('newCaretPosition 2');
			console.log(newCaretPosition);
        }
        setCaretPosition(input, newCaretPosition);

        // Toggle dollar sign visibility
        toggleDollarSign();
    });

    input.addEventListener('blur', function () {
        var value = input.value;
        if (value) {
            input.value = formatValue(value);
        }

        // Toggle dollar sign visibility
        toggleDollarSign();
    });

    input.addEventListener('focus', function () {
        // Toggle dollar sign visibility
        toggleDollarSign();
    });

    // Initial check to set the correct visibility on page load
    toggleDollarSign();
});
</script>
<?php
include('system-message.php');
?>
<script>
function close_bulk_options()
{
	var schedule_popup = document.getElementById('bulk_options');
	schedule_popup.style.display = 'none';
}

function show_bulk_options(event)
{
	if(event)
	    event.preventDefault();
	console.log('show_bulk_options');
	var schedule_popup = document.getElementById('bulk_options');
	schedule_popup.style.display = 'flex';
	schedule_popup.style.zIndex = 100;
	
	return false;
}

var event_bulk_edit = 2;
var do_archive_post = 0;

function enable_bulk_options()
{
	var this_event = document.getElementById('this_event');
	var this_and_following = document.getElementById('this_and_following');
	var all_events = document.getElementById('all_events');
	
	if(this_event.checked)
		event_bulk_edit = 0;
	else if(this_and_following.checked)
		event_bulk_edit = 1;
	else if(all_events.checked)
		event_bulk_edit = 2;
	
	close_bulk_options();
	if(do_archive_post)
		archive_event();
	else
		save_post_rest_api(0);
}
</script>
<section class="ff_add--social-media" id="bulk_options" style="display:none;">
<div class="social_media--popup">
<a href="javascript:void(0)" class="close--popup" onclick="javascript:close_bulk_options()">
<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<g id="vuesax/linear/add">
<g id="vuesax/linear/add_2">
<g id="add">
<path id="Vector" d="M6.46484 6.46436L13.5359 13.5354" stroke="#87888C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
<path id="Vector_2" d="M6.46409 13.5354L13.5352 6.46436" stroke="#87888C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
</g>
</g>
</g>
</svg>
</a>
<h2 class="popupt--title" id="bulk_options_title"></h2>
<div style id="bulk_options_text" class="bulk_options_text">
	<fieldset>
	<legend>Edit recurring event</legend>
	<div>
		<input type="radio" id="this_event" name="bulk_option" value="0" checked />
		<label for="huey">This event</label>
	</div>
	
	<div>
		<input type="radio" id="this_and_following" name="bulk_option" value="1" />
		<label for="dewey">This and following events</label>
	</div>
	
	<div>
		<input type="radio" id="all_events" name="bulk_option" value="2" />
		<label for="louie">All events</label>
	</div>
	</fieldset>
</div>
<div class="add--link-fields">
<a type="button" onclick="javascript:close_bulk_options()" href="javascript:void(0)" class="add-btn">Cancel</a>
<a type="button" onclick="javascript:enable_bulk_options()" href="javascript:void(0)" class="add-btn">Ok</a>
</div>
</div>
</section>
<?php
vd($event_post->post_parent, '$event_post->post_parent');
?>
<script>
<?php
if((!empty($recurring_event) || !empty($event_post->post_parent)) && empty($_GET['cloned']))
{
	$is_recurring_event = '1';
	$form_submit = 'show_bulk_options';
}
else
{
	$is_recurring_event = '0';
	$form_submit = 'save_post_rest_api';
}
?> 
var is_recurring_event = <?=$is_recurring_event?>;
</script>
<?php
if($event_id)
{
?>
<a href="<?=get_permalink($event_id)?>/">View</a>
<?php
}
?>
<div class="etn-frontend-dashboard create_event_container" id="create_event_container">
	<div class="frontend-attendee-list" id="frontend-attendee-list">
		<form id="create_event_form" action="" method="post" onsubmit="javascript:<?=$form_submit?>(event)" autocomplete="on" class="ant-form ant-form-vertical css-qgg3xn">
		<script src="/ckeditor/ckeditor.js"></script>
		<div class="ant-row etn-mltv-general-basic-info etn-mltv-general-block css-qgg3xn" style="row-gap: 40px;">
			<div class="ant-col ant-col-24 css-qgg3xn">
				<div class="ant-form-item css-qgg3xn">
					<div class="ant-row ant-form-item-row css-qgg3xn">
						<div class="ant-col ant-form-item-label css-qgg3xn">
							<label for="post_title" class="ant-form-item-required" title="Event title">Event title <span class="required_field">*</span></label>
						</div>
						<div class="ant-col ant-form-item-control css-qgg3xn">
							<div class="ant-form-item-control-input">
								<div class="ant-form-item-control-input-content">
									<input id="post_title" name="post_title" type="text" placeholder="Name your event" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$post_title?>">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="ant-col ant-col-24 css-qgg3xn">
				<div class="ant-form-item css-qgg3xn">
					<div class="ant-row ant-form-item-row css-qgg3xn">
						<div class="ant-col ant-form-item-label css-qgg3xn">
							<label for="post_content" class="ant-form-item-required" title="Event description">Event description <span class="required_field">*</span></label>
						</div>
						<div class="ant-col ant-form-item-control css-qgg3xn">
							<div class="ant-form-item-control-input">
								<div class="ant-form-item-control-input-content">
									<textarea id="post_content" name="post_content"><?=$post_content?></textarea>
									<script>
									CKEDITOR.replace( 'post_content', {
										height: 300,
									});
									</script>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="ant-row etn-mltv-general-basic-info etn-mltv-general-block css-qgg3xn" style="row-gap: 40px;">
			<script>
			function handleLogoClick(image_title, image_id, hidden_id) {
			  var mediaUploader = wp.media({
			    title: 'Select or Upload ' + image_title,
			    button: {
			      text: 'Select ' + image_title
			    },
			    multiple: false
			  });
			
			  mediaUploader.on('select', function() {
			    var attachment = mediaUploader.state().get('selection').first().toJSON();
			
			    var logoImage = document.getElementById(image_id);
			    logoImage.src = attachment.url;
			    logoImage.alt = attachment.title;
			    logoImage.title = attachment.title;
			    logoImage.style.display = 'block';
				
				console.log('attachment.id');
				console.log(attachment.id);
				
				document.getElementById(hidden_id).value = attachment.id;
				document.getElementById('clear_icon').style.display = 'inline-block';
			  });
			
			  mediaUploader.open();
			}
			
			function remove_logo(image_id, hidden_id)
			{
				var logo_image = document.getElementById(image_id);
				logo_image.src = '';
				logo_image.style.display = 'none';
				document.getElementById(hidden_id).value = '';
				document.getElementById('clear_icon').style.display = 'none';
			}
			</script>
			<script>
			function handle_gallery_add_click() {
			    var mediaFrame = wp.media({
			      title: 'Select Images',
			      multiple: true,
			      library: {
			        type: 'image'
			      },
			      button: {
			        text: 'Select'
			      }
			    });
			
				var inner_html = '';
				
			    mediaFrame.on('select', function() {
					var selectedImages = mediaFrame.state().get('selection');
					var imageIds = [];
			
					selectedImages.each(function(image) {
					  var imageId = image.id;
					  
						console.log('image.attributes.url');
						console.log(image.attributes.url);
						
						inner_html += '<div id="gallery_image_' + image.id + '" class="gallery--item"><span class="clear--icon" onclick="event.stopPropagation(); remove_gallery_image(' + image.id + ')"></span><img src="' + image.attributes.url + '" alt="Gallery Image"></div>';
						imageIds.push(imageId);
					});
					
					var gallery_container = document.getElementById('gallery_container');
					gallery_container.innerHTML += inner_html;
					
					var gallery_images = document.getElementById('gallery_images');
					var gallery_images_value = gallery_images.value;
					var image_ids = gallery_images_value.split(',');
					
					image_ids = image_ids.concat(imageIds);
					
					gallery_images.value = image_ids.join(',');
			    });
			
			    mediaFrame.open();
			}
			
			function remove_gallery_image(image_id)
			{
				var gallery_images = document.getElementById('gallery_images');
				var gallery_images_value = gallery_images.value;
				
				var image_ids = gallery_images_value.split(',');
				
				var index_to_remove = image_ids.indexOf(image_id.toString());
				
				if (index_to_remove !== -1) {
				  image_ids.splice(index_to_remove, 1);
				}
				
				var updated_gallery_images_value = image_ids.join(',');
				
				gallery_images.value = updated_gallery_images_value;

				var image = document.getElementById('gallery_image_' + image_id);
				image.src = '';
				image.style.display = 'none';
			}
			</script>
			<style>
			.gallery--container .gallery--item {
				display: inline-flex;
				flex-grow: 1;
				clear: both;
				width: 170px;
				height: 126px;
				object-fit: cover;
				object-position: center center;
				border-radius: 20px;
				border: 1px solid #eaeaea;
				padding: 10px;
				text-align: center;
				appearance: none;
				background: url(<?=get_stylesheet_directory_uri()?>/img/gallery-placeholder-icon.svg);
				background-size: 48px 48px;
				background-repeat: no-repeat;
				background-position: center center;
				cursor: pointer;
				position: relative;
				overflow: hidden;
			}
			.gallery--container .gallery--item-image {
				display: inline-flex;
				flex-grow: 1;
				clear: both;
				width: 170px;
				height: 126px;
				object-fit: cover;
				object-position: center center;
				border-radius: 20px;
				border: 1px solid #eaeaea;
				padding: 10px;
				text-align: center;
				appearance: none;
				background-size: 48px 48px;
				background-repeat: no-repeat;
				background-position: center center;
				cursor: pointer;
				position: relative;
				overflow: hidden;
			}
			.gallery--item-image .clear--icon {
				content: url(<?=get_stylesheet_directory_uri()?>/img/remove-icon.svg);
				display: inline-block;
				display: none;
				width: 20px;
				height: 20px;
				position: absolute;
				top: 10px;
				right: 10px;
				z-index: 2;
				cursor: pointer;
			}				
			.gallery--item img {
				margin: auto;
			}
			</style>
			
<?php
	$thumbnailUrl = '';
	$thumbnail_img = '<img id="featured-image" src="" style="display:none;" alt="Featured Image">';
	$thumbnailId = get_post_thumbnail_id($event_id);
	if($thumbnailId)
	{
		$thumbnailUrl = wp_get_attachment_image_src($thumbnailId, 'thumbnail');
		if($thumbnailUrl)
			$thumbnail_img = '<img id="featured-image" src="' . esc_url($thumbnailUrl[0]) . '" alt="Featured Image">';
	}
	else
		$thumbnailId = '';
?>

			<div class="ant-col ant-col-24 css-qgg3xn">
				<h3>Upload banner image</h3>
				<div class="ant-form-item css-qgg3xn">
					<div class="gallery-event-row">
						<div class="gallery-image-1">
							<div class="gallery--container">
								<div class="gallery--item main" onclick="handleLogoClick('Featured Image', 'featured-image', 'featured_image')">
									<span id="clear_icon" class="clear--icon" onclick="event.stopPropagation(); remove_logo('featured-image', 'featured_image')">X</span>
									<?=$thumbnail_img?>
									<input type="hidden" id="featured_image" name="featured_image" value="<?=$thumbnailId?>">
								</div>
							</div>
						</div>
					</div>
				</div>
				<button type="button" class="etn-btn etn-btn-primary upload-btn" onclick="handleLogoClick('Featured Image', 'featured-image', 'featured_image')">upload</button>
				<div class="addevents-fields-container" style="margin-left: -8px; margin-right: -8px; row-gap: 16px;">
					<div class="addevents-fields-event_type" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="ant-row ant-form-item-row css-qgg3xn">
								<div class="ant-col ant-form-item-label css-qgg3xn">
									<label for="event_type" class="ant-form-item-required" title="Event type">Event type <span class="required_field">*</span></label>
								</div>
								<div class="ant-col ant-form-item-control css-qgg3xn">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<style>
											.display_none_important {
												display:none!important;
											}
											</style>
											<script>
											var weekday_checkboxes_row_initial;
											var date_start_initial = '01/01/2025';
											var date_start_save;
											
											function show_lesson_prices(this_value)
											{
												console.log('show_lesson_prices(' + this_value + ')');
												
												if(this_value == 26)
												{
													console.log('if(this_value == 26)');
													console.log(this_value);
													//
													var single_event = document.getElementById('single_event');
													single_event.checked = true;
													var recurring_event = document.getElementById('recurring_event');
													recurring_event.checked = false;
													
													var single_event_radio = document.querySelector('.single_event-radio');
													single_event_radio.style.display = 'none';
													var recurring_event_radio = document.querySelector('.recurring_event-radio');
													recurring_event_radio.style.display = 'none';
													//var date_start = document.querySelector('#date_start');
													//if(date_start.value == '')
														//date_start.value = date_start_initial;
													var shedule_dates = document.querySelector('#shedule_dates');
													//shedule_dates.classList.add('display_none_important');
													
													var weekday_checkboxes_row = document.querySelector('.weekday-checkboxes-row');
													weekday_checkboxes_row_initial = weekday_checkboxes_row.style.display;
													weekday_checkboxes_row.style.display = 'block';
													return;
												}
												else
												{
													var single_event_radio = document.querySelector('.single_event-radio');
													single_event_radio.style.display = 'initial';
													var recurring_event_radio = document.querySelector('.recurring_event-radio');
													recurring_event_radio.style.display = 'initial';
													var shedule_dates = document.querySelector('#shedule_dates');
													shedule_dates.classList.remove('display_none_important');

													//var date_start = document.querySelector('#date_start');
													//if(date_start.value !== '')
														//date_start_initial = date_start.value;
													
													var weekday_checkboxes_row = document.querySelector('.weekday-checkboxes-row');
													weekday_checkboxes_row.style.display = weekday_checkboxes_row_initial;
												}
												
												var single_price = document.getElementById('single_price');
												var lesson_prices = document.getElementById('lesson_prices');
												console.log('single_price');
												console.log(single_price);
												console.log('lesson_prices');
												console.log(lesson_prices);
												if(this_value == 31)
												{
													console.log('if(this_value == 31)');
													console.log(this_value);
													jQuery('#lesson_prices').css("display", "block");
													jQuery('#single_price').css("display", "none");
												}
												else
												{
													console.log('if(this_value !== 31)');
													console.log(this_value);
													jQuery('#lesson_prices').css("display", "none");
													jQuery('#single_price').css("display", "block");
												}
											}
											</script>
<?php
	if($event_category == 0)
	{
		$event_types = get_all_event_types();
		unset($event_types[26]);
	}
	elseif($event_category == 26)
	{
		$event_types = array(26 => "Monthly Subscription");
	}
	else
	{
		$event_types = get_all_event_types();
		unset($event_types[26]);
	}
	//vd($event_category, '$event_category');
	//vd($event_types, '$event_types');
?>
											<select onchange="show_lesson_prices(this.value)" name="event_type" id="etn_event_type" class="etn_event_select2 etn_event_select etn_event_type">
												<option></option>
<?php
	if($event_types)
	{
		foreach($event_types as $event_type_id => $event_type)
		{
			$selected = $event_type_id == $event_category ? ' selected' : '';
?>
												<option value="<?=$event_type_id?>"<?=$selected?>><?=rtrim($event_type, 's')?></option>
<?php
		}
	}
?>
											</select>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="addevents-fields-activity_type" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="ant-row ant-form-item-row css-qgg3xn">
								<div class="ant-col ant-form-item-label css-qgg3xn">
									<label for="event_type" class="ant-form-item-required" title="Activity type">Activity type <span class="required_field">*</span></label>
								</div>
								<div class="ant-col ant-form-item-control css-qgg3xn">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<select name="activity" id="etn_event_activity_type" class="etn_event_select2 etn_event_select etn_event_activity_type">
												<option value="">Search activity type</option>
<?php
	$activity_types = get_all_activity_types();
	if($activity_types)
	{
		foreach($activity_types as $activity_type_id => $activity_type)
		{
			$selected = $activity_type_id == $event_activity ? ' selected' : '';
?>
												<option value="<?=$activity_type_id?>"<?=$selected?>><?=$activity_type?></option>
<?php
		}
	}
?>
											</select>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="addevents-fields-waiver" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="ant-row ant-form-item-row css-qgg3xn">
								<div class="ant-col ant-form-item-label css-qgg3xn">
									<label for="event_type" class="ant-form-item-required" title="Event waiver">Event waiver <span class="required_field">*</span></label>
								</div>
								<div class="ant-col ant-form-item-control css-qgg3xn">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<select name="waiver" id="waiver" onchange="add_new_waiver(this)" class="etn_event_select2 etn_event_select waiver">
												<option value="">Search event waivers</option>
<?php
	$waivers = get_user_waivers($current_user_id);
	if($waivers)
	{
		foreach($waivers as $waiver_id => $waiver)
		{
			$selected = $waiver_id == $event_waiver ? ' selected' : '';
?>
												<option value="<?=$waiver_id?>"<?=$selected?>><?=$waiver?></option>
<?php
		}
	}
?>
												<option value="-1">Add new waiver</option>
											</select>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<script>
			function add_new_waiver(select)
			{
				var waiver_blocks = document.getElementsByClassName('waiver_block');
				console.log('waiver_blocks');
				console.log(waiver_blocks);
				var style_display = select.value == -1 ? 'block' : 'none';
				if(waiver_blocks && waiver_blocks.length)
				{
					if(select.value == -1)
						for(var i = 0; i < waiver_blocks.length; i++)
							waiver_blocks[i].classList.remove('display_none_important');
					else
						for(var i = 0; i < waiver_blocks.length; i++)
							waiver_blocks[i].classList.add('display_none_important');
				}
			}
			</script>
			<div class="event-info-row-1 waiver_block display_none_important">
				<div class="ant-form-item css-qgg3xn">
					<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;width: 100%;">
						<div class="ant-form-item css-qgg3xn">
							<div class="ant-row ant-form-item-row css-qgg3xn">
								<p>(your waiver will be saved when you click the Submit button below)</p>
								<div class="age_group-lable">
									<label for="waiver_title" class="ant-form-item-required" title="Waiver Name">Waiver Name <span class="required_field">*</span></label>
								</div>
								<div class="age_group-field">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<input id="waiver_title" name="waiver_title" type="text" placeholder="Enter Waiver Name" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="ant-col ant-col-24 css-qgg3xn waiver_block display_none_important">
				<div class="ant-form-item css-qgg3xn">
					<div class="ant-row ant-form-item-row css-qgg3xn">
						<div class="ant-col ant-form-item-label css-qgg3xn">
							<label for="waiver_content" class="ant-form-item-required" title="event description">Waiver Agreement Terms <span class="required_field">*</span></label>
						</div>
						<div class="ant-col ant-form-item-control css-qgg3xn">
							<div class="ant-form-item-control-input">
								<div class="ant-form-item-control-input-content">
									<textarea id="waiver_content" name="waiver_content"></textarea>
									<script>
									/*
									tinymce.init({
									    selector: '#waiver_content',
									    plugins: 'autoresize link lists',
									    toolbar: 'undo redo | formatselect | bold italic | link | bullist numlist',
									    height: 600,
									});
									*/
									CKEDITOR.replace( 'waiver_content', {
										height: 300,
									});
									</script>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<style>
		.add_new_location {
			display: none;
		}
		</style>
		<div class="ant-row etn-mltv-general-basic-info etn-mltv-general-block css-qgg3xn" style="row-gap: 40px;">
			<div class="ant-col ant-col-24 css-qgg3xn">
				<h4>Event location</h4>
				<div class="ant-row css-qgg3xn">
					<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="event-location-row">
								<div class="location-field-label">
									<label for="location" class="ant-form-item-required" title="Location">Location <span class="required_field">*</span></label>
								</div>
								<div class="location-field-select">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<script>
											function add_new_location_form(this_value)
											{
												if(this_value === 'add_new')
												{
													jQuery('.add_new_location').css("display", "block");
													jQuery('#etn_event_location_type').val('new_location');
													
												}
												else
												{
													jQuery('.add_new_location').css("display", "none");
													jQuery('#etn_event_location_type').val('existing_location');
												}
											}
											</script>
<?php
		$locations = get_all_event_locations_current_user();
?>
											<input type="hidden" name="etn_event_location_type" id="etn_event_location_type" value="existing_location">
											<select onchange="add_new_location_form(this.value)" name="location" id="etn_location" class="etn_event_select2 etn_event_select etn_location">
												<option></option>
												<option value="add_new">Add new location</option>
<?php
		if($locations)
		{
			foreach($locations as $location_id => $location)
			{
				$selected = $location_id == $event_location ? ' selected' : '';
?>
												<option value="<?=$location_id?>"<?=$selected?>><?=$location?></option>
<?php
			}
		}
?>
												</select>
											
										</div>
									</div>
								</div>
								<div class="room-label">
									<label for="room_area" class="" title="Room/Area">Room/Area</label>
								</div>
								<div class="room-text-field">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<input id="room_area" name="room_area" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$room_area?>">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="ant-row css-qgg3xn add_new_location">
					<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="add_new_location-col">

								<div class="name-location-label">
									<label for="etn_event_location" class="ant-form-item-required" title="Name">Location Name <span class="required_field">*</span></label>
								</div>
								<div class="name-location-field">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<input id="etn_event_location" name="etn_event_location" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="">
										</div>
									</div>
								</div>

								<div class="street-1-label">
									<label for="street_address_line_1" class="ant-form-item-required" title="Street address line 1">Street address line 1 <span class="required_field">*</span></label>
								</div>
								<div class="street-1-input">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<input id="street_address_line_1" name="street_address_line_1" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="ant-row css-qgg3xn add_new_location">
					<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="add_new_location-col-2">
								<div class="street-2-label">
									<label for="street_address_line_2" class="ant-form-item-required" title="Street address line 2">Street address line 2</label>
								</div>
								<div class="street-2-input">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<input id="street_address_line_2" name="street_address_line_2" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="">
										</div>
									</div>
								</div>
								<div class="city-label">
									<label for="city" class="ant-form-item-required" title="City">City <span class="required_field">*</span></label>
								</div>
								<div class="city-input">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<input id="city" name="city" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="ant-row css-qgg3xn add_new_location">
					<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="add_new_location-col-2">
								<div class="state-label">
									<label for="state" class="ant-form-item-required" title="State">State <span class="required_field">*</span></label>
								</div>
								<div class="state-input">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<select name="state" id="state" class="etn_event_select2 etn_event_select etn_event_location">
												<option></option>
												<option value="AL">Alabama</option>
												<option value="AK">Alaska</option>
												<option value="AZ">Arizona</option>
												<option value="AR">Arkansas</option>
												<option value="CA">California</option>
												<option value="CO">Colorado</option>
												<option value="CT">Connecticut</option>
												<option value="DE">Delaware</option>
												<option value="DC">District Of Columbia</option>
												<option value="FL">Florida</option>
												<option value="GA">Georgia</option>
												<option value="HI">Hawaii</option>
												<option value="ID">Idaho</option>
												<option value="IL">Illinois</option>
												<option value="IN">Indiana</option>
												<option value="IA">Iowa</option>
												<option value="KS">Kansas</option>
												<option value="KY">Kentucky</option>
												<option value="LA">Louisiana</option>
												<option value="ME">Maine</option>
												<option value="MD">Maryland</option>
												<option value="MA">Massachusetts</option>
												<option value="MI">Michigan</option>
												<option value="MN">Minnesota</option>
												<option value="MS">Mississippi</option>
												<option value="MO">Missouri</option>
												<option value="MT">Montana</option>
												<option value="NE">Nebraska</option>
												<option value="NV">Nevada</option>
												<option value="NH">New Hampshire</option>
												<option value="NJ">New Jersey</option>
												<option value="NM">New Mexico</option>
												<option value="NY">New York</option>
												<option value="NC">North Carolina</option>
												<option value="ND">North Dakota</option>
												<option value="OH">Ohio</option>
												<option value="OK">Oklahoma</option>
												<option value="OR">Oregon</option>
												<option value="PA">Pennsylvania</option>
												<option value="RI">Rhode Island</option>
												<option value="SC">South Carolina</option>
												<option value="SD">South Dakota</option>
												<option value="TN">Tennessee</option>
												<option value="TX">Texas</option>
												<option value="UT">Utah</option>
												<option value="VT">Vermont</option>
												<option value="VA">Virginia</option>
												<option value="WA">Washington</option>
												<option value="WV">West Virginia</option>
												<option value="WI">Wisconsin</option>
												<option value="WY">Wyoming</option>
											</select>
										</div>
									</div>
								</div>
								<div class="zip-label">
									<label for="zip" class="ant-form-item-required" title="Zip">Zip <span class="required_field">*</span></label>
								</div>
								<div class="zip-input">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<input id="zip" name="zip" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
		
<script>
jQuery(document).ready(function() {
/*
	jQuery("#etn_location").select2({
	    placeholder: "Search location",
	    allowClear: true
	});
	
    jQuery('#etn_event_type').select2({
	    placeholder: "Search event type",
	    allowClear: true
	});
	
    jQuery('#etn_event_activity_type').select2({
	    placeholder: "Search activity type",
	    allowClear: true
	});
	
    jQuery('#waiver').select2({
	    placeholder: "Search event waivers",
	    allowClear: true
	});
	
    jQuery('#state').select2({
	    placeholder: "Search state",
	    allowClear: true
	});
*/
	jQuery('.add_new_location').css("display", "none");
	
	show_lesson_prices(<?=$event_category?>);
});
</script>		
		<div class="event-info-container" style="row-gap: 40px;">
			<div class="event-info-row-1">
				<div class="ant-form-item css-qgg3xn">
					<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="ant-row ant-form-item-row css-qgg3xn">
								<div class="age_group-lable">
									<label for="age_group" class="" title="Add age group">Add age group</label>
								</div>
								<div class="age_group-field">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<input id="age_group" name="age_group" type="text" placeholder="Enter here" class="ant-input css-qgg3xn create_event_input create_event_input_text age_group" value="<?=$age_group?>">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="ant-row ant-form-item-row css-qgg3xn">
								<div class="add_level-lable">
									<label for="level" class="" title="Add level">Add level</label>
								</div>
								<div class="add_level-field">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<input id="level" name="level" type="text" placeholder="Enter here" class="ant-input css-qgg3xn create_event_input create_event_input_text level" value="<?=$level?>">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="event-info-row-2">
				<div class="ant-form-item css-qgg3xn">
					<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="ant-row ant-form-item-row css-qgg3xn">
								<div class="total_capacity-lable">
									<label for="total_capacity" class="ant-form-item-required" title="Total capacity">Total capacity <span class="required_field">*</span></label>
								</div>
								<div class="total_capacity-field">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<input id="total_capacity" name="total_capacity" type="number" placeholder="Enter here" class="ant-input css-qgg3xn create_event_input create_event_input_text total_capacity" value="<?=$total_capacity?>">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="single_price" class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="ant-row ant-form-item-row css-qgg3xn">
								<div class="event_price-label">
									<label for="event_price" class="ant-form-item-required" title="Event price">Event price <span class="required_field">*</span></label>
								</div>
								<div class="event_price-field">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content dollar-input-wrapper">
											<input id="event_price" name="event_price" type="text" placeholder="Enter here" class="ant-input css-qgg3xn create_event_input create_event_input_text level" value="<?=$price?>">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="lesson_prices" class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;display:none;">
						<div class="ant-form-item css-qgg3xn">
							<div class="ant-row ant-form-item-row css-qgg3xn">
								<div class="event_price-label">
									<label for="lesson_price" class="ant-form-item-required" title="Lesson price details">Lesson price details <span class="required_field">*</span></label>
								</div>
								<div class="event_price-field">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<textarea id="lesson_price" name="lesson_price"><?=$lesson_price?></textarea>
											<script>
											/*
											tinymce.init({
											    selector: '#lesson_price',
											    plugins: 'autoresize link lists',
											    toolbar: 'undo redo | formatselect | bold italic | link | bullist numlist',
											    height: 300,
											});
											*/
											CKEDITOR.replace( 'lesson_price', {
												height: 300,
											});
											</script>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
		<script>
		function switch_day(checkbox_id)
		{
			var span_closed = document.getElementById('closed_' + checkbox_id);
			var span_open = document.getElementById('open_' + checkbox_id);
			
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
		}
		</script>
		<style>
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
		</style>
		<div class="ant-row etn-mltv-general-basic-info etn-mltv-general-block css-qgg3xn" style="row-gap: 40px;">
			<div class="hide-public-row">
				<h3>Hide from public view</h3>
				<div class="status--btn">
				<label class="switch">
					<input name="checkbox_public_view" id="checkbox_public_view" type="checkbox"<?php if(!empty($checkbox_public_view)) echo ' checked'; ?>>
					<span onclick="switch_day('public_view')" class="slider round"></span>
					<span class="status--text"></span>
				</label>
				<span id="closed_public_view" class="status--text closed" style="display: block;"></span>
				<span id="open_public_view" class="status--text open" style="display: none;"></span>
				</div>				
			</div>
		</div>
		<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
		<!-- 
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.13.18/jquery.timepicker.min.css">
		-->
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
		<!-- 
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.13.18/jquery.timepicker.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
		-->
		
		<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
		<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
		<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
		<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />		
		<script>
		jQuery(document).ready(function() {
			jQuery(function() {
			    // Initialize date range picker for both inputs
			    jQuery('input[name="date_start"], input[name="date_end"]').daterangepicker({
			        autoUpdateInput: false,
			        locale: {
			            cancelLabel: 'Clear'
			        },
			        maxSpan: {
			            days: 365  // Limit the range to 1 year
			        },
			        // Set start and end dates based on input values or default to today
			        startDate: jQuery('#date_start').val() ? moment(jQuery('#date_start').val(), 'MM/DD/YYYY') : moment(),
			        endDate: jQuery('#date_end').val() ? moment(jQuery('#date_end').val(), 'MM/DD/YYYY') : moment()
			    });
			
			    jQuery('#date_start').on('apply.daterangepicker', function(ev, picker) {
			        jQuery(this).val(picker.startDate.format('MM/DD/YYYY'));
			
			        // Set the maximum date for #date_end to one year after #date_start
			        const maxEndDate = picker.startDate.clone().add(1, 'year');
			        
			        // Update the #date_end picker with the new min and max dates
			        jQuery('#date_end').data('daterangepicker').minDate = picker.startDate;
			        jQuery('#date_end').data('daterangepicker').maxDate = maxEndDate;
			
			        // If the current end date is beyond the new max date, adjust it
			        if (picker.endDate.isAfter(maxEndDate)) {
			            jQuery('#date_end').data('daterangepicker').setEndDate(maxEndDate);
			            jQuery('#date_end').val(maxEndDate.format('MM/DD/YYYY'));
			        } else {
			            jQuery('#date_end').val(picker.endDate.format('MM/DD/YYYY'));
			        }
			    });
			
			    jQuery('#date_end').on('apply.daterangepicker', function(ev, picker) {
			        jQuery(this).val(picker.endDate.format('MM/DD/YYYY'));
			        jQuery('#date_start').val(picker.startDate.format('MM/DD/YYYY'));
			    });
			
			    jQuery('#date_start').on('cancel.daterangepicker', function(ev, picker) {
			        jQuery(this).val('');
			    });
			
			    jQuery('#date_end').on('cancel.daterangepicker', function(ev, picker) {
			        jQuery(this).val('');
			    });
			});
		});
		</script>
		<script>
		function schedule_form()
		{
			var recurring_enabled = jQuery('#recurring_event').prop('checked') ? 1 : 0;
			if(recurring_enabled)
			{
				jQuery('.schedule_element').css("display", "block");
				jQuery('#shedule-fields').removeClass('shedule-columns-2');
				jQuery('#shedule-fields').addClass('shedule-columns-3');
				jQuery('#date_end_required').css("display", "inline-block");
			}
			else
			{
				jQuery('.schedule_element').css("display", "none");
				jQuery('#shedule-fields').removeClass('shedule-columns-3');
				jQuery('#shedule-fields').addClass('shedule-columns-2');
				jQuery('#date_end_required').css("display", "none");
			}
		}
		</script>
		
		<style>
		.time-picker-dropdown {
		    display: none;
		    position: absolute;
		    background-color: white;
		    border: 1px solid #ccc;
		    max-height: 200px;
		    overflow-y: auto;
		    z-index: 1000;
		}
		
		.time-picker-dropdown div {
		    padding: 8px;
		    cursor: pointer;
		}
		
		.time-picker-dropdown div:hover {
		    background-color: #f0f0f0;
		}
		</style>
	    <script>
        class TimePicker {
            constructor(inputId) {
                this.inputElement = document.getElementById(inputId);
                this.createTimePicker();
                this.attachEvents();
            }

            createTimePicker() {
                this.dropdown = document.createElement('div');
                this.dropdown.className = 'time-picker-dropdown';
                document.body.appendChild(this.dropdown);

                const times = this.generateTimeOptions();
                times.forEach(time => {
                    const timeOption = document.createElement('div');
                    timeOption.innerText = time;
                    this.dropdown.appendChild(timeOption);
                    timeOption.addEventListener('click', () => {
						console.log('timeOption.click');
                        this.inputElement.value = time;
                        this.hideDropdown();
                    });
                });
            }

            generateTimeOptions() {
                const times = [];
                const periods = ['AM', 'PM'];

                for (let i = 0; i < 24; i++) {
                    const period = periods[Math.floor(i / 12)];
                    const hour = i % 12 === 0 ? 12 : i % 12;
                    times.push(`${hour}:00 ${period}`);
                    times.push(`${hour}:30 ${period}`);
                }

                return times;
            }

            attachEvents() {
                this.inputElement.addEventListener('focus', () => {
					//console.log('focus');
                    this.showDropdown();
                });
				
                document.addEventListener('click', (e) => {
                    if (!this.inputElement.contains(e.target) && !this.dropdown.contains(e.target)) {
						//console.log('click');
                        this.hideDropdown();
                    }
                });
            }

            showDropdown() {
                const rect = this.inputElement.getBoundingClientRect();
                this.dropdown.style.left = `${rect.left}px`;
                this.dropdown.style.top = `${rect.bottom + window.scrollY}px`;
                this.dropdown.style.width = `${rect.width}px`;
                this.dropdown.style.display = 'block';
            }

            hideDropdown() {
				//console.log('hideDropdown');
                this.dropdown.style.display = 'none';
            }
        }
	    </script>
		<h2>Schedule</h2>
		<div class="shedule-container" style="row-gap: 40px;margin-bottom: 138px;">
			<div class="shedule-row">
				<div class="radio-row">
				<label class="single_event-radio">
					<input onclick="schedule_form()" type="radio" id="single_event" name="recurring_event" value="0"<?php if(empty($recurring_event)) echo ' checked'; ?>>
					Single Event
				</label>
				<label class="recurring_event-radio">
					<input onclick="schedule_form()" type="radio" id="recurring_event" name="recurring_event" value="1"<?php if(!empty($recurring_event)) echo ' checked'; ?>>
					Recurring Event
				</label>
				</div>
				<div class="shedule-columns-1" id="shedule_dates">
					<div class="shedule-col" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="ant-row ant-form-item-row css-qgg3xn">
								<div class="ant-col ant-form-item-label css-qgg3xn">
									<label for="date_start" class="start-event-label" title="Start">Start <span class="required_field">*</span></label>
								</div>
								<div class="ant-col ant-form-item-control css-qgg3xn">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<input autocomplete="off" id="date_start" name="date_start" type="text" placeholder="" class="start-event-field" value="<?=$date_start?>">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="shedule-col-1" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="ant-row ant-form-item-row css-qgg3xn">
								<div class="ant-col ant-form-item-label css-qgg3xn">
									<label for="date_end" class="end-event-label" title="End">End <span id="date_end_required" style="display:none;" class="required_field">*</span></label>
								</div>
								<div class="ant-col ant-form-item-control css-qgg3xn">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<input autocomplete="off" id="date_end" name="date_end" type="text" placeholder="" class="end-event-field" value="<?=$date_end?>">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="recurring-event-time-1" class="shedule-row-2 recurring-event-time">
				<div class="schedule_element weekday-checkboxes-row" style="display: none;">
<?php
		generate_weekday_checkboxes($recurrence_weekly_day);
?>
				</div>
				<div id="shedule-fields" class="shedule-columns-2">
					<div class="shedule-col" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="ant-row ant-form-item-row css-qgg3xn">
								<div class="start-event-label">
									<label for="time_start" class="start-event-label" title="From">From <span class="required_field">*</span></label>
								</div>
								<div class="ant-col ant-form-item-control css-qgg3xn">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<input autocomplete="off" id="time_start" name="time_start" type="text" placeholder="" class="time_start start-event-field" value="<?=$time_start?>">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="shedule-col-1" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="ant-row ant-form-item-row css-qgg3xn">
								<div class="to_event-label">
									<label for="time_end" class="end_event-label" title="To">To <span class="required_field">*</span></label>
								</div>
								<div class="ant-col ant-form-item-control css-qgg3xn">
									<div class="ant-form-item-control-input">
										<div class="to_event-field">
											<input autocomplete="off" id="time_end" name="time_end" type="text" placeholder="" class="time_end end-event-field" value="<?=$time_end?>">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="shedule-col-2 schedule_element" style="display: none; padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="ant-row ant-form-item-row css-qgg3xn">
								<div class="start-event-label">
									<label for="repeats_every" class="repeats-every-label" title="Frequency">Frequency <span class="required_field">*</span></label>
								</div>
								<div class="ant-col ant-form-item-control css-qgg3xn">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
									        <select name="recurrence_span" id="recurrence_span"> 
									            <option value="1">weekly</option>
									            <option value="2">bi-weekly</option>
									        </select>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="shedule-col-3" style="display: none; padding-left: 8px; padding-right: 8px;" style="display:none;">
						<div class="ant-form-item css-qgg3xn">
							<div class="ant-row ant-form-item-row css-qgg3xn">
								<div class="to_event-label">
									<label for="recurrence_freq" class="frequency-label" title="Frequency">Frequency <span class="required_field">*</span></label>
								</div>
								<div class="ant-col ant-form-item-control css-qgg3xn">
									<div class="ant-form-item-control-input">
										<div class="to_event-field">
									        <select name="recurrence_freq" id="recurrence_freq">
									            <option value="day">Daily</option>
									            <option value="week" selected>Weekly</option>
									            <option value="month">Monthly</option>
									            <option value="year">Yearly</option>
									        </select>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="schedule_element add-another-time-row" style="display: none;">
				<button type="button" style="display: none;" onclick="javascript:add_another_time()">Add another time</button>
			</div>
			<div style="clear:both;"></div>
		</div>
		<script>
        new TimePicker('time_start');
        new TimePicker('time_end');
		
		function add_another_time()
		{
			var originalDiv = document.getElementById('recurring-event-time-1');
			var newDiv = originalDiv.cloneNode(true);
			
			var new_block_index = document.getElementsByClassName('recurring-event-time').length + 1;
			
			var newId = 'recurring-event-time-' + new_block_index;
			newDiv.id = newId;
			newDiv.classList.add('schedule_element');
			
		    var lastRecurringEventTime = document.querySelectorAll('.recurring-event-time');
		    var lastElement = lastRecurringEventTime[lastRecurringEventTime.length - 1];
		    lastElement.parentNode.insertBefore(newDiv, lastElement.nextSibling);

			var new_element = document.getElementById(newId);
			
			console.log('new_element');
			console.log(new_element);
			
			var time_start = new_element.querySelector('.time_start');
			var time_start_id = 'time_start' + new_block_index;
			time_start.id = time_start_id;
			new TimePicker(time_start_id);
			
			var time_end = new_element.querySelector('.time_end');
			var time_end_id = 'time_end' + new_block_index;
			time_end.id = time_end_id;
			new TimePicker(time_end_id);
			
			console.log('time_start');
			console.log(time_start);
		}
		
		<?php if(!empty($recurring_event)) { ?> schedule_form(); <?php } ?>
		</script>
		<style>
		.time-picker {
		position: absolute;
		display: none;
		border: 1px solid #ccc;
		background-color: #fff;
		padding: 5px;
		z-index: 12;
		  }
		
		  .time-picker ul {
		    list-style: none;
		    padding: 0;
		    margin: 0;
		    height: 200px;
		    overflow-y: scroll;
		  }
		
		  .time-picker ul li {
		    cursor: pointer;
		    padding: 5px;
		  }
		
		  .hour-section {
		    float: left;
		    width: 33.33%;
		  }
		
		  .minute-section {
		    float: left;
		    width: 33.33%;
		  }
		
		  .ampm-section {
		    float: left;
		    width: 33.33%;
		  }
		</style>
		
		<script>
		  var selectedHour = '00';
		  var selectedMinute = '00';
		  var selectedAmPm = 'AM';
		
		  function toggleTimePicker(input_id) {
		    var timePicker = document.getElementById(input_id + '_timePicker');
		    if (timePicker.style.display === 'block') {
		      timePicker.style.display = 'none';
		    } else {
		      timePicker.style.display = 'block';
		    }
		  }
		
		  function populateAMPM(input_id) {
		    var ampmSection = document.getElementById(input_id + '_ampmList');
			
		    for (var i = 0; i < 2; i++) {
		      var li = document.createElement('li');
		      li.textContent = !i ? 'AM' : 'PM';
		      li.addEventListener('click', function() {
		        selectedAmPm = this.textContent;
				console.log('selectedAmPm');
				console.log(selectedAmPm);
				updateInput(input_id);
		        this.parentElement.querySelectorAll('li').forEach(function(item) {
		          item.classList.remove('selected');
		        });
		        this.classList.add('selected');
		      });
		      ampmSection.appendChild(li);
		    }
		  }
		
		  function populateHours(input_id) {
		    var hourSection = document.getElementById(input_id + '_hourList');
		    for (var i = 1; i <= 12; i++) {
		      var li = document.createElement('li');
		      li.textContent = i;
		      li.addEventListener('click', function() {
		        selectedHour = this.textContent < 10 ? '0' + this.textContent : this.textContent;
				console.log('selectedHour');
				console.log(selectedHour);
				updateInput(input_id);
		        this.parentElement.querySelectorAll('li').forEach(function(item) {
		          item.classList.remove('selected');
		        });
		        this.classList.add('selected');
		      });
		      hourSection.appendChild(li);
		    }
		  }
		
		  function populateMinutes(input_id) {
		    var minuteSection = document.getElementById(input_id + '_minuteList');
		    for (var i = 0; i < 60; i++) {
		      var li = document.createElement('li');
		      li.textContent = i < 10 ? '0' + i : i;
		      li.addEventListener('click', function() {
		        selectedMinute = this.textContent !== '' ? this.textContent : '00';
				console.log('selectedMinute');
				console.log(selectedMinute);
				updateInput(input_id);
		        this.parentElement.querySelectorAll('li').forEach(function(item) {
		          item.classList.remove('selected');
		        });
		        this.classList.add('selected');
		      });
		      minuteSection.appendChild(li);
		    }
		  }
		  function updateTime(event, input_id) {
		  	event.preventDefault();
		    updateInput(input_id);
		    toggleTimePicker(input_id);
		  }
		
		  function updateInput(input_id) {
		    document.getElementById(input_id).value = selectedHour + ':' + selectedMinute + ' ' + selectedAmPm;
		  }
		</script>
		<div class="additional_info-container" style="row-gap: 40px;display:none;">
			<div class="ant-col ant-col-24 css-qgg3xn">
				<h3>Request additional client info</h3>
				<p>Choose additional questions to ask the client</p>
				<div class="ant-form-item css-qgg3xn">
					<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="additiona_info-checkboxes">
								<label class="T-Shirt">
								  <input type="checkbox" name="tshirt_size" id="tshirt_size" value="1"<?php if(!empty($tshirt_size)) echo ' checked'; ?>>
								  T-Shirt Size
								</label>
								<label class="Jersey">
								  <input type="checkbox" name="jersey_size" id="jersey_size" value="1"<?php if(!empty($jersey_size)) echo ' checked'; ?>>
								  Jersey Size
								</label>
								<label class="Shorts">
								  <input type="checkbox" name="shorts_size" id="shorts_size" value="1"<?php if(!empty($shorts_size)) echo ' checked'; ?>>
								  Shorts Size
								</label>
								<label class="Pants">
								  <input type="checkbox" name="pants_size" id="pants_size" value="1"<?php if(!empty($pants_size)) echo ' checked'; ?>>
								  Sweatshirt
								</label>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
<style>
.waiting-wrapper {
    position: relative;
}

.waiting-wrapper::before {
    content: "";
    position: absolute;
    top: 20px;
    right: 112px;
    width: 20px;
    height: 20px;
    margin-left: -10px;
    margin-top: -10px;
    border: 3px solid rgba(0, 0, 0, 0.3);
    border-radius: 50%;
    border-top-color: #000;
    animation: spin 1s ease infinite;
    z-index: 10;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.create_event-container  #create_event_archive {
    border-radius: 12px;
    background: var(--Accent-color-100, #17A0B2);
    width: 89px;
    color: var(--Additional-colors-White, #FFF);
    font-family: "DM Sans";
    font-size: 16px;
    font-style: normal;
    font-weight: 400;
    line-height: 140%;
    padding: 12px 18px;
}
</style>		
		<div id="submit_wrapper" class="events_page_actions">
<?php
	if($event_id)
	{
?>
			<script>
			function archive_event_bulk_options()
			{
				do_archive_post = 1;
				show_bulk_options(0);
			}
			
			function archive_event()
			{
				waiting(1, 0);
				var post_data = {
						post_id: <?=$event_id?>,
						post_status: 'trash',
						message: '',
						event_bulk_edit: event_bulk_edit,
						security: '<?php echo wp_create_nonce('edit-post-nonce'); ?>',
					};
				
				console.log('post_data');
				console.log(post_data);
				
				jQuery.ajax({
					type: 'POST',
					dataType: 'html',
					url: '<?=get_stylesheet_directory_uri()?>/wph_save_post_meta_ajax.php',
					data: post_data,
					beforeSend : function () {
						save_post_meta_loading = 1;
					},
					success: function (data)
					{
						save_post_meta_loading = 0;
						waiting(0, 0);
						if(data)
						{
							var response = JSON.parse(data);
							console.dir('response');
							console.dir(response);
							
							if(response.success)
							{
								if(response.message)
									show_system_message(
														'Event Saved.', 
														response.message, 
														'success', 
														'Back To Events',
														'/events/', 
														'Create an Event',
														'/create-event/'
									);
								else
									show_system_message(
														'Event Saved.', 
														'',
														'success',
														'Back To Events',
														'/events/', 
														'Create an Event',
														'/create-event/'
									);
							}
							else
							{
								if(response.message)
									show_system_message(
														'Event hasn\'t been created', 
														'[span]Error![/span] Your event has not been created. Go back to the event and check if everything is filled in correctly.<br>[span]' + response.message + '[/span]', 
														'error',
														'Continue Editing',
														'', 
														'Create an Event',
														'/create-event/'
									);
								else
									show_system_message(
														'Event hasn\'t been created', 
														'[span]Error![/span] Your event has not been created. Go back to the event and check if everything is filled in correctly.', 
														'error',
														'Continue Editing',
														'', 
														'Create an Event',
														'/create-event/'
									);
							}
						}
						else
						{	
						}
					},
					error : function (jqXHR, textStatus, errorThrown) {
						waiting(0, 0);
						show_system_message(
											'Error', 
											'Something wrong', 
											'error',
											'',
											'', 
											'Close',
											''
						);
						save_post_meta_loading = 0;
						console.log(jqXHR);
					},
				});
				
				return false;
			}
			</script>
			<button type="button" onclick="archive_event_bulk_options()" id="create_event_archive" value="Archive" class="etn-btn etn-btn-primary create_event_archive">Archive</button>
<?php
	}
?>
			<input type="submit" id="create_event_submit" value="Submit" class="etn-btn etn-btn-primary create_event_submit">
		</div>
		</form>
	</div>
</div>

