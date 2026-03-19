<?php
if($_SERVER['REMOTE_ADDR'] == '188.163.75.165')
{
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
}
?>
<?php
$logged_user_id = get_current_user_id();

$events = [];
$attendies_count = 0;
$is_lesson = 0;
$price = '';

$simulate_checkout_vendor = get_user_meta($logged_user_id, 'simulate_checkout_vendor', TRUE);
//vd($simulate_checkout_vendor, '$simulate_checkout_vendor');

if($simulate_checkout_vendor)
{
	delete_user_meta($logged_user_id, 'simulate_checkout_vendor');
	wp_set_current_user($simulate_checkout_vendor);
	wp_set_auth_cookie($simulate_checkout_vendor);
	
?>
<script>
	window.location.reload();
</script>
<?php
}
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
//if(1)
if($_SERVER['REMOTE_ADDR'] !== '188.163.75.13')
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
.arrow-down {
	background-image: url('/wp-content/themes/greenshift-child/img/arrow-down.png');
	width:12px;
	height:7px;
	display: inline-block;
	float: right;
	margin: 11px;
}
.arrow-up {
	background-image: url('/wp-content/themes/greenshift-child/img/arrow-up.png');
	width:12px;
	height:7px;
	display: inline-block;
	float: right;
	margin: 11px;
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
	$product_name = '';
	
    $atts = shortcode_atts(array(
		'order_events_count' => -1,
    ), $atts);
	
	$display_statuses = array(
		'processing' => 'Paid',
		'completed' => 'Paid',
		'cancelled' => 'Uncollectable',
		'refunded' => 'Refund (full)',
		'partial-refund' => 'Partial Refund',
		'pending' => 'Failed',
		'trash' => 'Deleted',
	);
	
	$event_post = $current_user_id = $event_id = 0;
	$post_title = $etn_start_date = $etn_start_time = $event_order_class = '';
	
	$book_data = [];
	
	$event_name = get_query_var('event_name');
	if($event_name)
	{
		$event_order_class = ' event_orders';
		
		//if(current_user_can( 'manage_options' ))
		{
			$event_post = null;
			
			if(is_numeric($event_name))
			{
				$event_post = get_post($event_name);
			}
			elseif ( $posts = get_posts( array( 
			    'name' => $event_name, 
			    'post_type' => ['etn', 'product'],
			    'post_status' => 'publish',
			    'posts_per_page' => 1
			) ) ) $event_post = $posts[0];
			
			if ($event_post && !is_null($event_post)){
				$current_user_id = $event_post->post_author;
				$event_id = $event_post->ID;
				$date_start = get_post_meta($event_id, 'etn_start_date', true);
				$timestamp = strtotime($date_start);
				$etn_start_date = date('j F, Y', $timestamp);
				$etn_start_time = get_post_meta($event_id, 'etn_start_time', true);
				$product_name = $post_title = get_the_title($event_id);
			}
		}
	}
	
	if(!empty($event_post))
	{
		if($logged_user_id == $event_post->post_author || current_user_can('edit_post', $event_post->ID))
		{
			//legal user
			$event_category = 0;
			$categories = get_the_terms($event_post->ID, 'etn_category');
			if(!empty($categories[0]))
				$event_category = $categories[0]->term_id;
			
			if($event_category == 31)//lesson
			{
				$book_data = get_post_meta($event_post->ID, 'book_data');
				$is_lesson = 1;
			}
			
			$price = get_event_price($event_post->ID);
		}
		else
		{
			return;
		}
	}
	
	if($event_post)
		$events = [$event_post];
?>
<style>
<?php
if($is_lesson)
{
?>
.charge-added-user {
	display: none;
}
<?php
}

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
<script>
var book_group_event_loading = 0;
var group_select = 2;

function book_group_event_ajax(user_id, product_id)
{
	var players = [];
	var player_select = document.querySelectorAll('.player_select');
	if(player_select)
	{
		for(var i = 0; i < player_select.length; i++)
		{
			if(player_select[i].value)
			{
				var tshirt_size = document.getElementById('tshirt_size_' + i);
				var tshirt_size_value = tshirt_size ? tshirt_size.value : '';
				var jersey_size = document.getElementById('jersey_size_' + i);
				var jersey_size_value = jersey_size ? jersey_size.value : '';
				var shorts_size = document.getElementById('shorts_size_' + i);
				var shorts_size_value = shorts_size ? shorts_size.value : '';
				var pants_size = document.getElementById('pants_size_' + i);
				var pants_size_value = pants_size ? pants_size.value : '';
				console.log('tshirt_size');
				console.log(tshirt_size);
				console.log('tshirt_size_value');
				console.log(tshirt_size_value);
				players[players.length] = {
											player:player_select[i].value, 
											tshirt_size:tshirt_size_value,
											jersey_size:jersey_size_value,
											shorts_size:shorts_size_value,
											pants_size:pants_size_value
										};
			}
			
		}
	}
	
	var first_name = '';
	var last_name = '';
	var first_name_0 = document.getElementById('first_name_0');
	if(first_name_0)
		first_name = first_name_0.value;
	var last_name_0 = document.getElementById('last_name_0');
	if(last_name_0)
		last_name = last_name_0.value;
	var save_player = 0;
	var checkbox_0 = document.getElementById('checkbox_0'); 
	if(checkbox_0)
		if(checkbox_0.checked)
			save_player = 1;
	
	console.log('first_name');
	console.log(first_name);
	console.log('last_name');
	console.log(last_name);
	console.log('save_player');
	console.log(save_player);
	//console.log('players');
	//console.log(players);
	//return;
	
	var post_data = {
			event_id: product_id,
			user_id: user_id,
			players: players,
			group_select: group_select,//a global variable, set to 2 for a publik lesson
			first_name: [first_name],
			last_name: [last_name],
			save_player: save_player,
			security: '<?php echo wp_create_nonce('book-group-event-nonce'); ?>',
			unbook: 0,
		};
	
	show_loader();
	jQuery.ajax({
		type: 'POST',
		dataType: 'html',
		url: '<?=get_stylesheet_directory_uri()?>/wph_book_group_event_ajax.php',
		data: post_data,
		beforeSend : function () {
			book_group_event_loading = 1;
		},
		success: function (data)
		{
			book_group_event_loading = 0;
			if(data)
			{
				var response = JSON.parse(data);
				console.dir('response');
				console.dir(response);
				
				if(response.success)
				{
					//show_message_box('Success', 'You have successfully booked the event');
					//show_event_link();
					
					open_new_window = false;
					show_system_message(
										'Event Booked', 
										'You have successfully booked the event', 
										'success',
										'', 
										'',
										'close',
										'.'
					);
					
					return false;
				}
				else
				{
					if(response.message)
						show_message_box('Error', response.message);
					else
						show_message_box('Error', 'The event was not booked');
				}
			}
			else
			{	
			}
		},
		error : function (jqXHR, textStatus, errorThrown) {
			book_group_event_loading = 0;
			console.log(jqXHR);
		},
	});
	
	return false;
}

var recommended_price = 0;

function expand_oevent(event_id)
{
	console.log('event_id');
	console.log('order_child_' + event_id);
	var order_children = document.querySelectorAll('.order_child_' + event_id);
	console.log('order_children');
	console.log(order_children);
	
	var arrow = document.getElementById('arrow_' + event_id);
	if(arrow.classList.contains('arrow-down'))
	{
		arrow.classList.remove('arrow-down');
		arrow.classList.add('arrow-up');
		for(var i = 0; i < order_children.length; i++)
		{
			order_children[i].style.display = 'table-row';
		}
	}
	else
	{
		arrow.classList.remove('arrow-up');
		arrow.classList.add('arrow-down');
		for(var i = 0; i < order_children.length; i++)
		{
			order_children[i].style.display = 'none';
		}
	}
}

function displaySuggestions(suggestions, added_trs_ajax) {
    const suggestionsContainer = document.getElementById('suggestions');
    suggestionsContainer.innerHTML = '';
    if (suggestions.length > 0) {
        suggestions.forEach(suggestion => {
            const item = document.createElement('div');
            item.className = 'suggestion-item';

            const link = document.createElement('a');
            link.href = '#';
			
			var charge_method = 'create_payment_link';
			var button_value = 'Send Link';

			if(suggestion.orders_count !== undefined)
				if(suggestion.orders_count)
				{
					item.className = 'suggestion-item has_orders';
					charge_method = 'charge_added_user';
					button_value = 'Charge';
				}
			
			if(suggestion.card_on_file !== undefined)
				if(suggestion.card_on_file == 0)
				{
					item.className = 'suggestion-item';
					charge_method = 'create_payment_link';
					button_value = 'Send Link';
				}
			
			link.addEventListener('click', function(event) {
				event.preventDefault();
				set_user_input(added_trs_ajax, suggestion.ID, suggestion.display_name, suggestion.children);
				var charge_button = document.querySelector('#charge_' + added_trs_ajax);
				if(charge_button)
				{
					charge_button.setAttribute('onclick', charge_method + '(' + added_trs_ajax + ')');
					charge_button.innerHTML = button_value;
				}
				//document.querySelector('#charge_' + added_trs_ajax).addEventListener('click', charge_method);
			});
			
			var a_text =  suggestion.display_name;
			var address =  '';
			
			if(suggestion.city !== undefined)
				if(suggestion.city)
					address = suggestion.city;
			
			if(suggestion.state !== undefined)
				if(suggestion.state)
					address = address ? address + ', ' + suggestion.state : suggestion.state;
					
			if(address)
				a_text += ' (' + address + ')';
			
            const nameSpan = document.createElement('span');
            nameSpan.textContent = a_text;

            //link.appendChild(icon);
            link.appendChild(nameSpan);

            item.appendChild(link);
            suggestionsContainer.appendChild(item);
        });
        suggestionsContainer.style.display = 'block';
    } else {
        suggestionsContainer.style.display = 'none';
    }
}

function displaySuggestions_old(suggestions, added_trs_ajax) {
    const suggestionsContainer = document.getElementById('suggestions');
    suggestionsContainer.innerHTML = '';
    if (suggestions.length > 0) {
        suggestions.forEach(suggestion => {
			console.log('added_trs_ajax');
			console.log(added_trs_ajax);
            const item = document.createElement('div');
            item.className = 'suggestion-item';

            const link = document.createElement('a');
            link.href = '#';

			link.addEventListener('click', function(event) {
				event.preventDefault();
				set_user_input(added_trs_ajax, suggestion.ID, suggestion.display_name, suggestion.children)
			});
			
            const nameSpan = document.createElement('span');
            nameSpan.textContent = suggestion.display_name;

            //link.appendChild(icon);
            link.appendChild(nameSpan);

            item.appendChild(link);
            suggestionsContainer.appendChild(item);
        });
        suggestionsContainer.style.display = 'block';
    } else {
        suggestionsContainer.style.display = 'none';
    }
}

var user_children = [];

function set_user_input(added_trs, ID, display_name, children)
{
	user_children = children;
	//document.querySelector('#user_' + added_trs).innerHTML = display_name;
	document.querySelector('#user_' + added_trs).innerHTML = 'Choose your player';
	add_player_dynamically(added_trs, display_name, children);
	document.querySelector('#input_' + added_trs).innerHTML = '<div class="dollar-input-wrapper"><input id="amount_' + added_trs + '" type="text"value="<?=$price?>"></div><input id="searchEventsInput' + added_trs + '" type="hidden" value="<?=addslashes($product_name)?>">';
	document.querySelector('#user_id_' + added_trs).value = ID;
	//alert(display_name);
}

function show_input(a)
{
	var search_facility_input = a.parentNode.querySelector('.search_facility_input');
	search_facility_input.style.display = 'block';
	search_facility_input.focus();
	//var links = a.parentNode.querySelectorAll('a');
	var links = a.parentNode.querySelectorAll('.a_add_new_user');
	
	for(var i = 0; i < links.length; i++)
	{
		links[i].style.display = 'none';
	}
	
}

var added_trs = 0;

function add_order_payment_with_email(item_id, product_id)
{
	if(added_trs)
		return;
	added_trs++;
	
    jQuery('html, body').animate({
        scrollTop: jQuery('.events-table.attendees-table').offset().top
    }, 1000);
	
	console.log('added_trs');
	console.log(added_trs);

    const newRow = document.createElement('tr');
    newRow.id = 'added_user_' + added_trs;
    newRow.className = 'order_child order_child_' + item_id + ' order_' + item_id;
    newRow.style.display = 'table-row';
    newRow.innerHTML = `
        <td class="order-events-table-td-1" style="">&nbsp;</td>
        <td id="user_` + added_trs + `" class="order-events-table-td-2">
			<div class="search_facility">
				<a class="a_search_user" href="javascript:void(0)" onclick="javascript:show_input(this)" style="display:block;">Search User</a>
				<a class="a_add_new_user" target="_blank" href="/add-user/" style="display:block;">Add New User</a>
				<input id="searchInput` + added_trs + `" type="text" class="search_facility_input" placeholder="Search User" oninput="javascript:get_users_ajax(` + item_id + `, this.value, ` + added_trs + `)">
				<div id="suggestions" style=""></div>
			</div>	
		</td>
        <td id="input_` + added_trs + `" class="order-events-table-td-3">&nbsp;</td>
        <td id="select_` + added_trs + `" class="order-events-table-td-4" style="">&nbsp;</td>
        <td class="order-events-table-td-5">
<?php
if($is_lesson)
{
?>
            <button onclick="book_added_user(` + added_trs + `)" id="book_` + added_trs + `" class="button-action" value="Add">Add</button>
<?php
}
else
{
?>
            <button onclick="create_payment_link(` + added_trs + `)" id="charge_` + added_trs + `" class="button-action charge-added-user" value="Charge">Charge</button>
<?php
}
?>
            <button onclick="delete_added_user(` + added_trs + `)" id="delete_` + added_trs + `" class="button-action delete-added-user" value="Cancel">Cancel</button>
			<input type="hidden" id="user_id_` + added_trs + `" value="">
			<input type="hidden" id="product_id_` + added_trs + `" value="` + product_id + `">
        </td>
    `;
	
	//var selector = '#orders-body tr.order_' + item_id;
	var selector = '#orders-body tr.order_parent';
    const rows = document.querySelectorAll(selector);
	
    if (rows.length > 0) {
	    const firstOrderRow = rows[0];
    	firstOrderRow.insertAdjacentElement('afterend', newRow);
    } else {
        document.getElementById('orders-body').appendChild(newRow);
    }
}

function create_payment_link(added_user)
{
	//https://rezbetadev.wpenginepowered.com/wp-json/custom/v1/create-link/?user_id=147&product_id=6914&amount=40
	var user_input = document.getElementById('user_id_' + added_user);
	var user_id = user_input ? user_input.value : 0;
	
	if(!user_id)
	{
		user_input = document.getElementById('user_player_id_' + added_user);
		user_id = user_input ? user_input.value : 0;
	}

	var product_input = document.getElementById('product_id_' + added_user);
	var product_id = product_input ? product_input.value : 0;
	
	if(!product_id)
	{
		product_input = document.getElementById('product_player_id_' + added_user);
		product_id = product_input ? product_input.value : 0;
	}
	
	var amount_input = document.getElementById('amount_' + added_user);
	var amount = amount_input ? amount_input.value : 0;
	
	if(!amount)
	{
		amount_input = document.getElementById('amount_player_' + added_user);
		amount = amount_input ? amount_input.value : 0;
	}
	
	var attendee_input = document.getElementById('player_' + added_user);
	var attendee_name = '';
	if(attendee_input)
	{
		var attendee_name = attendee_input.getAttribute('value');
		
		if(!attendee_name)
			attendee_name = attendee_input.value;
	}
	
	//alert(attendee_name);
	//return;
	
	if('Add New Player' == attendee_name)
	{
		var first_name = document.getElementById('first_name_0').value;
		var last_name = document.getElementById('last_name_0').value;
		attendee_name = first_name + ' ' + last_name;
		add_new_player_ajax(user_id, first_name, last_name);
	}
	
	attendee_name = encodeURIComponent(attendee_name);
	
	if(typeof user_id == 'undefined' || !user_id || user_id == '0')
	{
		show_system_message(
							'Error',
							'Please specify a user',
							'error',
							'',
							'',
							'Close',
							''
		);
		
		return;
	}
	
	if(typeof product_id == 'undefined' || !product_id || product_id == '0')
	{
		show_system_message(
							'Error',
							'Please specify an event',
							'error',
							'',
							'',
							'Close',
							''
		);
		
		return;
	}
	
	if(typeof amount == 'undefined' || !amount || amount == '0')
	{
		show_system_message(
							'Error',
							'Please specify the payment amount',
							'error',
							'',
							'',
							'Close',
							''
		);
		
		return;
	}
	
	var payment_link = '<?=get_site_url()?>/wp-json/custom/v1/create-link/?user_id=' + user_id + '&product_id=' + product_id + '&amount=' + amount + '&attendee_name=' + attendee_name;
	//alert(payment_link);
	//return;
	var event_name = document.getElementById('searchEventsInput' + added_user).value;
	//alert(event_name);
	
	send_link_ajax(product_id, user_id, payment_link, event_name, attendee_name);
}

var add_new_player_loading = 0;

function add_new_player_ajax(user_id, first_name, last_name)
{
	if(add_new_player_loading)
		return;
	console.log('add_new_player_ajax()');

	if(!user_id)
	{
		show_system_message(
							'Error', 
							'no user_id', 
							'error',
							'',
							'', 
							'Close',
							''
		);
	
		return;
	}
	
	if(!first_name)
	{
		show_system_message(
							'Error', 
							'no first name', 
							'error',
							'',
							'', 
							'Close',
							''
		);
	
		return;
	}
	
	if(!last_name)
	{
		show_system_message(
							'Error', 
							'no last name', 
							'error',
							'',
							'', 
							'Close',
							''
		);
	
		return;
	}
	
	var post_data = {
			user_id: user_id,
			first_name: [first_name],
			last_name: [last_name],
			save_player: 1,
			security: '<?php echo wp_create_nonce('add-new-player-nonce'); ?>',
		};
	show_loader();
	jQuery.ajax({
		type: 'POST',
		dataType: 'html',
		url: '<?=get_stylesheet_directory_uri()?>/wph-add-new-player-ajax.php',
		data: post_data,
		beforeSend : function () {
			waiting(1, 0);//show the "wait please" popup
			add_new_player_loading = 1;
		},
		success: function (data)
		{
			add_new_player_loading = 0;
			if(data)
			{
				var response = JSON.parse(data);
				console.dir('response');
				console.dir(response);
				
				if(response.success)
				{
					waiting(0, 0);//hide the "wait please" popup
					show_system_message(
										'Success',
										'The new player has been added',
										'success',
										'',
										'',
										'Close',
										'.'
					);
				}
				else
				{
					if(response.message)
						show_system_message(
											'Error', 
											response.message, 
											'error',
											'',
											'', 
											'Close',
											''
						);
					else
						show_system_message(
											'Error', 
											'The new player has not been added',
											'error',
											'',
											'', 
											'Close',
											''
						);
				}
			}
			else
			{	
			}
		},
		error : function (jqXHR, textStatus, errorThrown) {
			add_new_player_loading = 0;
			console.log(jqXHR);
		},
	});
	
	return false;
}

var resend_payment_link_loading = 0;

function resend_payment_link_ajax(button)
{
	var user_id = button.dataset.user_id;
	var order_id = button.dataset.order_id;
	var product_id = button.dataset.product_id;
	var event_name = button.dataset.event_name;
	
	console.log('user_id');
	console.log(user_id);
	console.log('order_id');
	console.log(order_id);
	console.log('product_id');
	console.log(product_id);
	console.log('event_name');
	console.log(event_name);
	//return;
	if(resend_payment_link_loading)
		return;
	console.log('resend_payment_link_ajax()');

	if(!order_id)
	{
		show_system_message(
							'Error', 
							'no order_id', 
							'error',
							'',
							'', 
							'Close',
							''
		);
	
		return;
	}
	
	if(!product_id)
	{
		show_system_message(
							'Error', 
							'no product_id', 
							'error',
							'',
							'', 
							'Close',
							''
		);
	
		return;
	}
	
	var post_data = {
			user_id: user_id,
			order_id: order_id,
			product_id: product_id,
			event_name: event_name,
			security: '<?php echo wp_create_nonce('get-payment-link'); ?>',
		};
	show_loader();
	jQuery.ajax({
		type: 'POST',
		dataType: 'html',
		url: '<?=get_stylesheet_directory_uri()?>/wph-get-payment-link.php',
		data: post_data,
		beforeSend : function () {
			waiting(1, 0);//show the "wait please" popup
			resend_payment_link_loading = 1;
		},
		success: function (data)
		{
			resend_payment_link_loading = 0;
			if(data)
			{
				var response = JSON.parse(data);
				console.dir('response');
				console.dir(response);
				
				if(response.success)
				{
					waiting(0, 0);//hide the "wait please" popup
					if(response.payment_link)
					{
						
						send_link_ajax(response.product_id, response.user_id, response.payment_link, response.event_name);					
					}
					else
						show_system_message(
											'Error', 
											'Could not get the payment link', 
											'error',
											'',
											'', 
											'Close',
											''
						);
				}
				else
				{
					if(response.message)
						show_system_message(
											'Error', 
											response.message, 
											'error',
											'',
											'', 
											'Close',
											''
						);
					else
						show_system_message(
											'Error', 
											'success == FALSE',
											'error',
											'',
											'', 
											'Close',
											''
						);
				}
			}
			else
			{	
			}
		},
		error : function (jqXHR, textStatus, errorThrown) {
			resend_payment_link_loading = 0;
			console.log(jqXHR);
		},
	});
	
	return false;
}

function add_order_payment(item_id, product_id)
{
	added_trs++;
	console.log('added_trs');
	console.log(added_trs);

	var arrow = document.getElementById('arrow_' + item_id);
	if(arrow.classList.contains('arrow-down'))
		expand_oevent(item_id);
	
    const newRow = document.createElement('tr');
    newRow.id = 'added_user_' + added_trs;
    newRow.className = 'order_child order_child_' + item_id + ' order_' + item_id;
    newRow.style.display = 'table-row';
    newRow.innerHTML = `
        <td class="order-events-table-td-1">&nbsp;</td>
        <td id="user_` + added_trs + `" class="order-events-table-td-2">
			<div class="search_facility">
				<a class="a_search_user" href="javascript:void(0)" onclick="javascript:show_input(this)" style="display:block;">Search User</a>
				<a class="a_add_new_user" target="_blank" href="/add-user/" style="display:block;">Add New User</a>
				<input id="searchInput` + added_trs + `" type="text" class="search_facility_input" oninput="javascript:get_users_ajax(` + item_id + `, this.value, ` + added_trs + `)">
				<div id="suggestions" style="display:none;"></div>
			</div>	
		</td>
        <td id="input_` + added_trs + `" class="order-events-table-td-3">&nbsp;</td>
        <td id="select_` + added_trs + `" class="order-events-table-td-4">&nbsp;</td>
        <td class="order-events-table-td-5">
            <button onclick="book_added_user(` + added_trs + `)" id="book_` + added_trs + `" class="button-action" value="Add">Add</button>
            <button onclick="charge_added_user(` + added_trs + `)" id="charge_` + added_trs + `" class="button-action charge-added-user" value="Charge">Charge</button>
            <button onclick="delete_added_user(` + added_trs + `)" id="delete_` + added_trs + `" class="button-action delete-added-user" value="Cancel">Cancel</button>
			<input type="hidden" id="user_id_` + added_trs + `" value="">
			<input type="hidden" id="product_id_` + added_trs + `" value="` + product_id + `">
        </td>
    `;
	
	var selector = '#orders-body tr.order_' + item_id;
    const rows = document.querySelectorAll(selector);
	
    if (rows.length > 0) {
	    const firstOrderRow = rows[0];
    	firstOrderRow.insertAdjacentElement('afterend', newRow);
    } else {
        document.getElementById('orders-body').appendChild(newRow);
    }
}

function add_order_payment_booked(player_id, product_id, user_id, player_name, has_orders)
{
	var amount_value = '';
	if(1
		&& booked_seats !== undefined
		&& price_ranges !== undefined
		&& price_ranges[booked_seats] !== undefined
	)
	{
		amount_value = price_ranges[booked_seats];
	}
	
    var input_player_td = document.getElementById('input_player_td_' + player_id);
	if(input_player_td)
	{
		input_player_td.innerHTML = '<input id="amount_player_' + player_id + '" type="text" value="' + amount_value + '">';
	}
	
	var charge_method = 'charge_added_player';
	var button_value = 'Charge';
	if(!has_orders)
	{
		charge_method = 'create_payment_link';
		button_value = 'Send Link';
	}
	
    var actions_player_td = document.getElementById('actions_player_td_' + player_id);
	if(actions_player_td)//player_name
	{
		actions_player_td.innerHTML = `<button onclick="` + charge_method + `(` + player_id + `)" id="charge_player_` + player_id + `" class="button-action" value="Charge">` + button_value + `</button>
            <!-- button onclick="delete_added_user_player(` + player_id + `)" id="delete_player_` + player_id + `" class="button-action delete-added-user" value="Delete">Cancel</button -->
			<input type="hidden" id="user_player_id_` + player_id + `" value="` + user_id + `">
			<input type="hidden" id="product_player_id_` + player_id + `" value="` + product_id + `">
			<input type="hidden" id="player_name_` + player_id + `" value="` + player_name + `">
			<input id="searchEventsInput` + player_id + `" type="hidden" value="<?=addslashes($product_name)?>">
			`;
	}
}

function book_added_user(added_user)
{
	var book_amount = document.getElementById('amount_' + added_user);
	if(!book_amount)
	{
		show_system_message(
							'Error',
							'Please select user',
							'error',
							'',
							'',
							'Close',
							''
		);
		
		return;
	}
	
	waiting(1, 0);//show the "wait please" popup
	
	var user_id = document.getElementById('user_id_' + added_user).value;
	var product_id = document.getElementById('product_id_' + added_user).value;
	console.log('user_children');
	console.log(user_children);
	//alert(document.getElementById('player_' + added_user).value);
	book_group_event_ajax(user_id, product_id);
	//add_player_dynamically(added_user);
	
	//create_custom_order_ajax(user_id, product_id, book_amount_value, 0, button_id, 0, '');
	//start_checkout(user_id, product_id, book_amount_value, '');
}

function add_player_dynamically(player_id, player_name, children)
{
        var player_label = document.createElement("label");
        player_label.id = "player_label_" + player_id;
		player_label.className = "dynamically_added";
        player_label.innerHTML = "Select Player*";
        var select = document.createElement("select");
        select.name = "player[]";
        select.id = "player_" + player_id;
		select.className = "dynamically_added player_select input_required";
		select.style.display == 'block';
		select.setAttribute('data-label', "Select Player");
		select.setAttribute('data-player_id', player_id);
/*
        var option_empty = document.createElement("option");
        option_empty.id = "option_1";
        option_empty.value = "";
        option_empty.text = "Select Player";
        select.appendChild(option_empty);
*/
        var option_parent = document.createElement("option");
        option_parent.id = "option_1";
        option_parent.value = player_name;
        option_parent.text = player_name;
        select.appendChild(option_parent);
		
		let i = 1;
		
		if (Object.keys(children).length > 0) {
		    for (const [childId, childName] of Object.entries(children)) {
		        i++;
		        let option = document.createElement("option");
		        option.id = `option_${i}`;
		        option.value = childName.replace("'", "\\'"); // Replacing single quotes for safety
		        option.text = childName;
		        select.appendChild(option);
		    }
		}
		
		i++;

        var option_add_new_player = document.createElement("option");
        option_add_new_player.id = `option_${i}`;
        option_add_new_player.value = "Add New Player";
        option_add_new_player.text = "Add New Player";
        select.appendChild(option_add_new_player);

		var select_td = document.querySelector('#user_' + added_trs);
		select_td.appendChild(select);
		
		select.addEventListener('change', function()
		{
			if('Add New Player' == this.value)
			{
				var first_name_label = document.getElementById("first_name_label_0");
				first_name_label.style.display = 'block';
				var first_name = document.getElementById("first_name_0");
				first_name.style.display = 'block';
				var last_name_label = document.getElementById("last_name_label_0");
				last_name_label.style.display = 'block';
				var last_name = document.getElementById("last_name_0");
				last_name.style.display = 'block';
				var checkbox_label = document.getElementById("checkbox_label_0");
				checkbox_label.style.display = 'block';
			}
			else
			{
				var first_name_label = document.getElementById("first_name_label_0");
				first_name_label.style.display = 'none';
				var first_name = document.getElementById("first_name_0");
				first_name.style.display = 'none';
				var last_name_label = document.getElementById("last_name_label_0");
				last_name_label.style.display = 'none';
				var last_name = document.getElementById("last_name_0");
				last_name.style.display = 'none';
				var checkbox_label = document.getElementById("checkbox_label_0");
				checkbox_label.style.display = 'none';
			}
		});
		
        var first_name_label = document.createElement("label");
        first_name_label.id = "first_name_label_0";
		first_name_label.className = "dynamically_added";
        first_name_label.innerHTML = "First Name*";
        first_name_label.style.display = 'none';
		
        var input_first_name = document.createElement("input");
        input_first_name.id = "first_name_0";
        input_first_name.name = "first_name[]";
        input_first_name.type = "text";
		input_first_name.className = "dynamically_added input_required";
		input_first_name.setAttribute('data-label', "First Name");
        input_first_name.style.display = 'none';
		
        var last_name_label = document.createElement("label");
        last_name_label.id = "last_name_label_0";
		last_name_label.className = "dynamically_added input_required";
        last_name_label.innerHTML = "Last Name*";
        last_name_label.style.display = 'none';
		
        var input_last_name = document.createElement("input");
        input_last_name.id = "last_name_0";
        input_last_name.name = "last_name[]";
        input_last_name.type = "text";
		input_last_name.className = "dynamically_added input_required";
		input_last_name.setAttribute('data-label', "Last Name");
        input_last_name.style.display = 'none';
		
        var checkbox_label = document.createElement("label");
        checkbox_label.id = "checkbox_label_0";
		checkbox_label.className = "dynamically_added";
		checkbox_label.style.display = "block";
        checkbox_label.innerHTML = '<input id="checkbox_0" name="save_player[]" type="checkbox" class="dynamically_added">Save player to the account';
        checkbox_label.style.display = 'none';

		select_td.appendChild(first_name_label);
		select_td.appendChild(input_first_name);
		select_td.appendChild(last_name_label);
		select_td.appendChild(input_last_name);
		select_td.appendChild(checkbox_label);
}

	function add_new_player_inputs_dynamically()
	{
        var first_name_label = document.createElement("label");
        first_name_label.id = "first_name_label_0";
		first_name_label.className = "dynamically_added";
        first_name_label.innerHTML = "First Name*";
        first_name_label.style.display = 'none';
		
        var input_first_name = document.createElement("input");
        input_first_name.id = "first_name_0";
        input_first_name.name = "first_name[]";
        input_first_name.type = "text";
		input_first_name.className = "dynamically_added input_required";
		input_first_name.setAttribute('data-label', "First Name");
        input_first_name.style.display = 'none';
		
		var form = document.querySelector(form_selector);
		etn_variable_total_price = form.querySelector(element_selector);
		if(etn_variable_total_price)
		{
			etn_variable_total_price.parentNode.insertBefore(first_name_label, etn_variable_total_price);
			etn_variable_total_price.parentNode.insertBefore(input_first_name, etn_variable_total_price);
		}

        var last_name_label = document.createElement("label");
        last_name_label.id = "last_name_label_0";
		last_name_label.className = "dynamically_added input_required";
        last_name_label.innerHTML = "Last Name*";
        last_name_label.style.display = 'none';
		
        var input_last_name = document.createElement("input");
        input_last_name.id = "last_name_0";
        input_last_name.name = "last_name[]";
        input_last_name.type = "text";
		input_last_name.className = "dynamically_added input_required";
		input_last_name.setAttribute('data-label', "Last Name");
        input_last_name.style.display = 'none';
		
		etn_variable_total_price = form.querySelector(element_selector);
		if(etn_variable_total_price)
		{
			etn_variable_total_price.parentNode.insertBefore(last_name_label, etn_variable_total_price);
			etn_variable_total_price.parentNode.insertBefore(input_last_name, etn_variable_total_price);
		}
		
		console.log('element_selector');
		console.log(element_selector);
		
        var checkbox_label = document.createElement("label");
        checkbox_label.id = "checkbox_label_0";
		checkbox_label.className = "dynamically_added";
		checkbox_label.style.display = "block";
        checkbox_label.innerHTML = '<input id="checkbox_' + player_id + '" name="save_player[]" type="checkbox" class="dynamically_added">Save player to the account';
        checkbox_label.style.display = 'none';
		
		if(etn_variable_total_price)
		{
			//etn_variable_total_price.parentNode.insertBefore(input_checkbox, etn_variable_total_price);
			etn_variable_total_price.parentNode.insertBefore(checkbox_label, etn_variable_total_price);
		}
	}


function charge_added_user(added_user)
{
	var charge_amount = document.getElementById('amount_' + added_user);
	if(!charge_amount)
	{
		show_system_message(
							'Error',
							'Please select user',
							'error',
							'',
							'',
							'Close',
							''
		);
		
		return;
	}
	
	var charge_amount_value = document.getElementById('amount_' + added_user).value;
	if(!charge_amount_value)
	{
		show_system_message(
							'Error',
							'Please insert the charge amount',
							'error',
							'',
							'',
							'Close',
							''
		);
	
		return;
	}
	
	var button_id = 'charge_' + added_user;
	waiting(1, 0);//show the "wait please" popup
	
	var user_id = document.getElementById('user_id_' + added_user).value;
	var product_id = document.getElementById('product_id_' + added_user).value;
	
	var player_name = '';
	var player = document.getElementById('player_' + added_user);
	if(player)
		player_name = player.value;
	
	var first_name = '';
	var last_name = '';
	var first_name_0 = document.getElementById('first_name_0');
	if(first_name_0)
		first_name = first_name_0.value;
	var last_name_0 = document.getElementById('last_name_0');
	if(last_name_0)
		last_name = last_name_0.value;
	var save_player = 0;
	var checkbox_0 = document.getElementById('checkbox_0'); 
	if(checkbox_0)
		if(checkbox_0.checked)
			save_player = 1;
	
	start_checkout(user_id, product_id, charge_amount_value, player_name, first_name, last_name, save_player);
}

function start_checkout(user_id, product_id, charge_amount_value, player_name, first_name, last_name, save_player)
{
	var inner_frame = document.getElementById('inner_frame');
	inner_frame.src = '/?user=' + user_id + '&product_id=' + product_id + '&simulate_checkout=1&vendor=<?=$current_user_id?>&price=' + charge_amount_value + '&player_name=' + player_name + '&first_name[]=' + first_name + '&last_name[]=' + last_name + '&save_player=' + save_player;
}

function charge_added_player(added_player)
{
	var charge_amount = document.getElementById('amount_player_' + added_player);
	if(!charge_amount)
	{
		show_system_message(
							'Error',
							'Please select user',
							'error',
							'',
							'',
							'Close',
							''
		);
		
		return;
	}
	
	var charge_amount_player_value = document.getElementById('amount_player_' + added_player).value;
	if(!charge_amount_player_value)
	{
		show_system_message(
							'Error',
							'Please insert the charge amount',
							'error',
							'',
							'',
							'Close',
							''
		);
	
		return;
	}
	
	var player_name = document.getElementById('player_name_' + added_player).value;
	
	var first_name = '';
	var last_name = '';
	var first_name_0 = document.getElementById('first_name_0');
	if(first_name_0)
		first_name = first_name_0.value;
	var last_name_0 = document.getElementById('last_name_0');
	if(last_name_0)
		last_name = last_name_0.value;
	var save_player = 0;
	var checkbox_0 = document.getElementById('checkbox_0'); 
	if(checkbox_0)
		if(checkbox_0.checked)
			save_player = 1;

	waiting(1, 0);//show the "wait please" popup
	
	var user_id = document.getElementById('user_player_id_' + added_player).value;
	var product_id = document.getElementById('product_player_id_' + added_player).value;
	start_checkout(user_id, product_id, charge_amount_player_value, player_name, first_name, last_name, save_player);
	//start_checkout(user_id, product_id, charge_amount_player_value, player_name);
}

function refund_cancel(order_id, button_id)
{
	var button = document.getElementById(button_id);
	//alert(button);
	var amount_td = button.parentNode.parentNode.querySelector('.order-events-table-td-3');
	amount_td.innerHTML = amount_td.dataset.html;
}

function refund_order(order_id, item_id, button, attendee_id, is_normal_order)
{
	var amount_td = button.parentNode.parentNode.querySelector('.order-events-table-td-3');
	amount_td.dataset.html = amount_td.innerHTML;
	if(is_normal_order)
		amount_td.innerHTML = '<input id="refund_amount_' + order_id + '" style="display: block;" type="text" value="' + amount_td.dataset.amount + '"><button class="button-action submit_refund" onclick="refund_order_ajax(' + order_id + ', \'' + item_id + '\', \'' + attendee_id + '\')">Ok</button><button class="button-action submit_refund" onclick="refund_cancel(' + order_id + ', \'' + button.id + '\')">Cancel</button>';
	else
		amount_td.innerHTML = '<input id="refund_amount_' + order_id + '" style="display: block;" type="text" value="' + amount_td.dataset.amount + '"><button class="button-action submit_refund" onclick="stripe_refund_ajax(' + order_id + ', \'' + item_id + '\', \'' + attendee_id + '\')">Ok</button><button class="button-action submit_refund" onclick="refund_cancel(' + order_id + ', \'' + button.id + '\')">Cancel</button>';
}
//create_custom_order_ajax(user_id, product_id, amount, refund, button_id, added_player, player_name)

var processing_attendee_id = 0;

function refund_order_ajax(order_id, item_id, attendee_id)
{
	//alert(attendee_id);
	//return;
	
	if(processing_attendee_id)
	{
		alert('Another request is in process, please wait');
		return;
	}
	//alert('item_id ' + item_id);
	var refund_amount_input = document.getElementById('refund_amount_' + order_id);
	if(!refund_amount_input.value)
	{
		show_system_message(
							'Error',
							'Please insert amount',
							'error',
							'',
							'',
							'Close',
							''
		);
		return;
	}
	waiting(1, 0);//show the "wait please" popup
	processing_attendee_id = attendee_id;
	
	var line_item_totals = {};
	line_item_totals[item_id] = 0;
	
	console.log('attendee_id');
	console.log(attendee_id);
	
	var data = {
	    action: 'woocommerce_refund_line_items',
	    order_id: order_id,
	    refund_amount: refund_amount_input.value,
	    refunded_amount: 0,
	    refund_reason: '',
	    line_item_qtys: JSON.stringify({}),
	    line_item_totals: JSON.stringify(line_item_totals),
	    line_item_tax_totals: JSON.stringify({}),
	    api_refund: true,
	    restock_refunded_items: true,
	    security: '<?=wp_create_nonce('order-item');?>'
	};
	
	console.log('data');
	console.log(data);
	
		jQuery.post('<?=admin_url('admin-ajax.php')?>', data)//refund via Dokan
	    .done(function(response) {
		
			console.log('admin-ajax.php response');
			console.log(response);


		    if (response.success) {
			
				if(processing_attendee_id)
				{
					set_attendee_status_ajax(processing_attendee_id);
					processing_attendee_id = 0;
				}
				
				show_system_message(
									'Success',
									'Refund successful:<br>' + response.data.message,
									'success',
									'',
									'',
									'Close',
									'.'
				);
		    } else {
				if(response.data !== undefined && response.data.error !== undefined)
				{
					
				}
				
				show_system_message(
									'Error', 
									'Refund failed:<br>' + response.data.error, 
									'error',
									'',
									'', 
									'Close',
									''
				);
		    }
	    })
	    .fail(function(jqXHR, textStatus, errorThrown) {
	
			console.log('textStatus');
			console.log(textStatus);
			console.log('errorThrown');
			console.log(errorThrown);
			
			var message = 'Request failed';
			var response = JSON.parse(jqXHR.responseText);
			console.log('response');
			console.log(response);
			if (response.data && Array.isArray(response.data)) {
			    message = 'Request failed:<br>' + response.data[0].message;
			}
			
			show_system_message(
								'Error', 
								message,
								'error',
								'',
								'', 
								'Close',
								''
			);
	        console.log('Error details:', jqXHR.responseText);
	    });
	//old function, now it's off
	//create_custom_order_ajax(0, 0, refund_amount_input.value, order_id, button_id, 0, '');
}

function stripe_refund_ajax(order_id, item_id, attendee_id)
{
	var refund_amount_input = document.getElementById('refund_amount_' + order_id);
	if(!refund_amount_input.value)
	{
		show_system_message(
							'Error',
							'Please insert amount',
							'error',
							'',
							'',
							'Close',
							''
		);
		return;
	}
	waiting(1, 0);//show the "wait please" popup
	processing_attendee_id = attendee_id;
	
	var data = {
	    action: 'woocommerce_refund_line_items',
	    order_id: order_id,
	    item_id: item_id,
	    attendee_id: attendee_id,
	    refund_amount: refund_amount_input.value,
	    security: '<?=wp_create_nonce('order-item');?>'
	};

	show_loader();
	jQuery.ajax({
		type: 'POST',
		dataType: 'html',
		data: data,
		url: '<?=get_stylesheet_directory_uri()?>/stripe-refund.php',
		success: function (data)
		{
			if(data)
			{
				var response = JSON.parse(data);
				console.dir('response');
				console.dir(response);
				
			    if (response.success) {
				
					if(processing_attendee_id)
					{
						set_attendee_status_ajax(processing_attendee_id);
						processing_attendee_id = 0;
					}
					
					show_system_message(
										'Success',
										'Refund successful<br>' + response.message,
										'success',
										'',
										'',
										'Close',
										'.'
					);
			    } else {
					show_system_message(
										'Error', 
										'Refund failed:<br>' + response.message,
										'error',
										'',
										'', 
										'Close',
										''
					);
			    }
		    }
		},
		error : function (jqXHR, textStatus, errorThrown) {
			processing_attendee_id = 0;
			alert(errorThrown);
			console.log(jqXHR);

			var message = 'Request failed';
			var response = JSON.parse(jqXHR.responseText);
			console.log('response');
			console.log(response);
			if (response.data && Array.isArray(response.data)) {
			    message = 'Request failed:<br>' + response.data[0].message;
			}
			
			show_system_message(
								'Error', 
								message,
								'error',
								'',
								'', 
								'Close',
								''
			);
	        console.log('Error details:', jqXHR.responseText);
		},
	});
	
	return false;
}

function set_attendee_status_ajax(processing_attendee_id)
{
	var post_data = {
			post_id: processing_attendee_id,
			meta_key: 'etn_status_custom',
			meta_value: 'refund',
			security: '<?php echo wp_create_nonce('edit-post-nonce'); ?>',
		};

	jQuery.ajax({
		type: 'POST',
		dataType: 'html',
		data: post_data,
		url: '<?=get_stylesheet_directory_uri()?>/set_post_meta.php',
		success: function (data)
		{
			processing_attendee_id = 0;
			if(data)
			{
				var response = JSON.parse(data);
				console.dir('response');
				console.dir(response);
				
				if(response.success)
				{
					//alert(response.message);
				}
				else
				{
					//alert('Wrong');
				}
			}
			else
			{	
			}
		},
		error : function (jqXHR, textStatus, errorThrown) {
			processing_attendee_id = 0;
			alert(errorThrown);
			console.log(jqXHR);
		},
	});
	
	return false;
}

//remove a dynamically added row
function delete_added_user(added_user)
{
	var added_user_tr = document.getElementById('added_user_' + added_user);
	if(added_user_tr)
	{
		added_user_tr.remove();
		added_trs = 0;
	}
}

var get_users_loading = 0;

function get_users_ajax(item_id, search, added_trs_ajax)
{
	if(search.length < 3)
		return;
	console.log('get_users_ajax()');

	var post_data = {
			role: 'um_custom_role_2',
			item_id: item_id,
			vendor_id: <?=$current_user_id?>,
			search: search,
			added_trs: added_trs_ajax,
			security: '<?php echo wp_create_nonce('get-users-nonce'); ?>',
		};
	
	jQuery.ajax({
		type: 'POST',
		dataType: 'html',
		url: '<?=get_stylesheet_directory_uri()?>/wph-get-users-ajax.php',
		data: post_data,
		beforeSend : function () {
			get_users_loading = 1;
		},
		success: function (data)
		{
			get_users_loading = 0;
			if(data)
			{
				var response = JSON.parse(data);
				console.dir('response');
				console.dir(response);
				
				if(response.success)
				{
					if(response.users)
					{
						displaySuggestions(response.users, response.added_trs);
					}
				}
				else
				{
					if(response.message)
						show_system_message(
											'Error', 
											response.message, 
											'error',
											'',
											'', 
											'Close',
											''
						);
					else
						show_system_message(
											'Error', 
											'Something wrong', 
											'error',
											'',
											'', 
											'Close',
											''
						);
				}
			}
			else
			{	
			}
		},
		error : function (jqXHR, textStatus, errorThrown) {
			get_users_loading = 0;
			console.log(jqXHR);
		},
	});
	
	return false;
}

var send_link_loading = 0;

function send_link_ajax(product_id, user_id, payment_link, event_name)
{
	if(send_link_loading)
		return;
	console.log('send_link_ajax()');

	if(!user_id)
	{
		show_system_message(
							'Error', 
							'no user_id', 
							'error',
							'',
							'', 
							'Close',
							''
		);
	
		return;
	}
	
	if(!payment_link)
	{
		show_system_message(
							'Error', 
							'no link', 
							'error',
							'',
							'', 
							'Close',
							''
		);
	
		return;
	}
	
	var post_data = {
			user_id: user_id,
			product_id: product_id,
			payment_link: payment_link,
			event_name: event_name,
			security: '<?php echo wp_create_nonce('send-link-nonce'); ?>',
		};
	
	console.dir('post_data');
	console.dir(post_data);
	show_loader();
	jQuery.ajax({
		type: 'POST',
		dataType: 'html',
		url: '<?=get_stylesheet_directory_uri()?>/wph-send-link-ajax.php',
		data: post_data,
		beforeSend : function () {
			waiting(1, 0);//show the "wait please" popup
			send_link_loading = 1;
		},
		success: function (data)
		{
			send_link_loading = 0;
			if(data)
			{
				var response = JSON.parse(data);
				console.dir('response');
				console.dir(response);
				
				if(response.success)
				{
					waiting(0, 0);//hide the "wait please" popup
					show_system_message(
										'Success',
										'The email was sent',
										'success',
										'',
										'',
										'Close',
										'.'
					);
					/*
					var delete_1 = document.getElementById('delete_1');
					if(delete_1)
						delete_1.click();
					*/
				}
				else
				{
					if(response.message)
						show_system_message(
											'Error', 
											response.message, 
											'error',
											'',
											'', 
											'Close',
											''
						);
					else
						show_system_message(
											'Error', 
											'The email was not sent',
											'error',
											'',
											'', 
											'Close',
											''
						);
				}
			}
			else
			{	
			}
		},
		error : function (jqXHR, textStatus, errorThrown) {
			send_link_loading = 0;
			console.log(jqXHR);
		},
	});
	
	return false;
}

var create_custom_order_loading = 0;
//not used currently
function create_custom_order_ajax(user_id, product_id, amount, refund, button_id, added_player, player_name)
{
	var post_data = {
			user_id: user_id,
			product_id: product_id,
			amount: amount,
			refund: refund,
			button_id: button_id,
			added_player: added_player,
			player_name: player_name,
			security: '<?php echo wp_create_nonce('create-custom-order-nonce'); ?>',
		};
	show_loader();
	jQuery.ajax({
		type: 'POST',
		dataType: 'html',
		url: '<?=get_stylesheet_directory_uri()?>/wph-create-custom-order-ajax.php',
		data: post_data,
		beforeSend : function () {
			create_custom_order_loading = 1;
		},
		success: function (data)
		{
			create_custom_order_loading = 0;
			if(data)
			{
				var response = JSON.parse(data);
				console.dir('response');
				console.dir(response);
				
				if(response.button_id)
				{
					waiting(0, button_id);//hide the "wait please" popup				
				}
				
				if(response.success)
				{
					var message = response.CONTENT;
					if(response.message)
						message += '<br>' + response.message;
					show_system_message(
										'Success',
										message,
										'success',
										'',
										'',
										'Close',
										'.'
					);
				}
				else
				{
					if(response.message)
						show_system_message(
											'Error', 
											response.message, 
											'error',
											'',
											'', 
											'Close',
											''
						);
					else
						show_system_message(
											'Error', 
											'Something wrong', 
											'error',
											'',
											'', 
											'Close',
											''
						);
				}
			}
			else
			{	
			}
		},
		error : function (jqXHR, textStatus, errorThrown) {
			create_custom_order_loading = 0;
			console.log(jqXHR);
		},
	});
	
	return false;
}
</script>
<?php
if(!empty($event_order_class))
{
?>
<script>
document.querySelector('.wp-block-post-title').querySelector('a').innerHTML = '<?=str_replace("'", "&#39;", $post_title);?> - Orders';
</script>
<?php
}
?>
<?php
include('system-message.php');

$wph_is_site_admin = wph_is_site_creator();
$lesson_price = $event_category_name = '';
$event_category = 0;
$is_subscription = FALSE;
$wcs_installed = function_exists( 'wcs_get_subscriptions_for_order' );

$already_booked = $this_user_booked = FALSE;

$categories = get_the_terms($event_id, 'etn_category');
if(!empty($categories[0]))
{
	$event_category = $categories[0]->term_id;
	$event_category_name = $categories[0]->name;
}
?>
<div class="dashboard-widget order_events order-events-table-wrapper">
	<div class="widget-title"> Orders</div>
<?php
	$booked_seats = get_booked_seats($event_id);
	
	$arrow_class = 'arrow-down';
	if($event_order_class)//if a single event
	{
		$arrow_class = 'arrow-up';
?>
	<div class="events-datetime">
		<strong>Date:</strong> <?=$etn_start_date?><br>
		<strong>Time:</strong> <?=$etn_start_time?>
<?php
		if('Lessons' == $event_category_name)
		{
			$lesson_price = get_post_meta($event_id, 'lesson_price', TRUE);
			//$booked_orders = get_booked_orders($event_id, FALSE, 'completed');
			
			
			
			//$booked_seats = count($booked_orders);
			$recommended_price = $private_price = 0;
			
			$meta_key = 'etn_ticket_variations';
			$serialized_value = get_post_meta($event_id, $meta_key, true);
			$ticket_variations = maybe_unserialize($serialized_value);
			
			if($ticket_variations)
				$recommended_price = $private_price = $ticket_variations[0]['etn_ticket_price'];
			
			if($lesson_price)
			{
				$pattern = '/(Private \(1-on-1\)|\d+-\d+|\d+) \(.*?\): \$(\d+)/';
				preg_match_all($pattern, $lesson_price, $matches, PREG_SET_ORDER);
				
				$price_ranges = [];
				foreach ($matches as $match) {
				    $range = $match[1];
				    $price = (int)$match[2];
					
				    // Handle "Private (1-on-1)" and ranges like "2-3"
				    if (strpos($range, 'Private') !== false) {
				        $price_ranges[] = ['min' => 1, 'max' => 1, 'price' => $price, 'private' => true];
				    } elseif (strpos($range, '-') !== false) {
				        list($min, $max) = explode('-', $range);
				        $min = (int)$min;
				        $max = (int)$max;
				        $price_ranges[] = ['min' => $min, 'max' => $max, 'price' => $price];
				    } else {
				        $value = (int)$range;
				        $price_ranges[] = ['min' => $value, 'max' => $value, 'price' => $price];
				    }
				}
				
				function get_price($booked_seats, $price_ranges) {
				    if ($booked_seats < 2) {
				        foreach ($price_ranges as $range) {
				            if (isset($range['private']) && $range['private'] === true) {
				                return $range['price'];
				            }
				        }
				    }
					
				    foreach ($price_ranges as $range) {
				        if ($booked_seats >= $range['min'] && $booked_seats <= $range['max']) {
							//vd($range, '$range');
				            return $range['price'];
				        }
				    }
				    return 0;
				}
				
				if($booked_seats >= 2)
					$recommended_price = get_price($booked_seats, $price_ranges);
			}
?>
		<script>
		var booked_seats = <?=$booked_seats?>;
		var price_ranges = new Array();
		price_ranges[0] = '<?=$private_price?>';
		price_ranges[1] = '<?=$private_price?>';
<?php
			if(!empty($price_ranges))
			{
			    foreach ($price_ranges as $range)
				{
					for($i = $range['min']; $i <= $range['max']; $i++)
					{
?>
		price_ranges[<?=$i?>] = '<?=$range['price']?>';
<?php
					}
			    }
			}
?>
		var recommended_price = '<?=$recommended_price?>';
		
		</script>
		<br><strong>Booked seats:</strong> <?=$booked_seats?><br>
		<strong>Seat price:</strong> $<?=$recommended_price?><br>
		<strong>Lesson price:</strong> <?=$lesson_price?><br>
<?php
		}
		elseif('Monthly Subscription' == $event_category_name)
		{
			$is_subscription = TRUE;
		}
?>
	</div>
	<div class="order-events-actions" style="float: right;"><button id="add_order" onclick="javascript:document.querySelector('.event-button-add').click();" class="button-add">Add A Participant</button></div>
<?php
	}
?>
	<table class="order-events-table events-table attendees-table">
		<thead>
		<tr>
			<th class="order-events-table-th-1" scope="col">IDs</th>
			<th class="order-events-table-th-2" scope="col">Client</th>
			<th class="order-events-table-th-3" scope="col">Total Amount</th>
			<th class="order-events-table-th-4" scope="col">Payment Status</th>
			<th class="order-events-table-th-5" scope="col">Action</th>
		</tr>
		</thead>
		<tbody id="orders-body">
<?php
	$count = count($events);
	$completed_orders_count = 0;
	
	foreach($events as $event)
	{
		$product_id = $event->ID;
		
		$product_name = get_the_title($product_id);
		$permalink = get_permalink($product_id);
		
		$payment_status = '';
		$paid = 1;
		
		$totals = 0;
		$count_items = 0;
		
		$orders = get_orders_by_product_id( $product_id );
		
		foreach($orders as $order)
		{
			$totals += $order->get_subtotal();
			$items = $order->get_items();
			$count_items += count($items);
			//$completed_orders_count++;
		}
		
		$all_totals = 0;
?>
		<tr class="order_parent<?=$event_order_class?> order_<?=$event->ID?>" id="order_parent_<?=$event->ID?>">
			<td class="order-events-table-td-1"><a href="<?=$permalink?>"><?=$product_name?></a><br>
				<strong>Event ID</strong> <?=$product_id?>
			</td>
			<td class="order-events-table-td-2" onclick="expand_oevent(<?=$event->ID?>)"><span id="count_items"><?=$count_items?></span><span id="arrow_<?=$event->ID?>" class="<?=$arrow_class?>"></span></td>
			<td class="order-events-table-td-3" id="all_totals">$<?=number_format( (float) $totals, 2, '.', '' )?></td>
			<td class="order-events-table-td-4"><?=$payment_status?></td>
			<td class="order-events-table-td-5">
				<button onclick="javascript:add_order_payment_with_email(<?=$event->ID?>, <?=$product_id?>)" class="button-add event-button-add button-action"<?php if($event_order_class) echo ' style="display:none;"';?>>Begin Checkout</button>
			</td>
		</tr>
<?php
		$stripe_links = get_user_stripe_links($current_user_id);
		//vd($stripe_links, '$stripe_links');
		
		if($stripe_links)
		{
			foreach($stripe_links as $order)
			{
				$continue = TRUE;
				if(!empty($order->post_content))
				{
					$link_profuct_id = get_url_param($order->post_content, 'product_id');
					
					if(!empty($link_profuct_id) && $link_profuct_id == $product_id)
					{
						$continue = FALSE;
					}
				}
				
				if($continue)
					continue;
				
				$customer_link = $attendee_names_html = $product_name = $display_status = '';
				$customer_id = get_post_meta($order->ID, 'user_id', TRUE);
				
				if($customer_id)
				{
				    $customer = get_userdata($customer_id);
				    if($customer)
					{
						$attendee_name = get_url_param($order->post_content, 'attendee_name');
						
						if($attendee_name)
						{
							if($customer->display_name !== $attendee_name)
								$attendee_names_html = $customer->display_name . ' (for ' . $attendee_name . ')';
							else
								$attendee_names_html = $customer->display_name;
						}
						
						$customer_link = '<a href="/edit-profile/' . $customer->ID . '/">' . $attendee_names_html . '</a>';
					}
				}
				
				$product_id = get_post_meta($order->ID, 'product_id', TRUE);
				$event = get_post($product_id);
				$title = get_the_title($product_id);
				if($title)
				{
					$product_name = '<a href="/orders/' . $event->ID . '/">' . $title . '</a>';
				}
				
				$amount = get_url_param($order->post_content, 'amount');
				$attendee_name = urlencode($attendee_name);
				
				$payment_link = get_site_url() . '/wp-json/custom/v1/create-link/?user_id=' . $customer_id . '&product_id=' . $product_id . '&amount=' . $amount . '&attendee_name=' . $attendee_name;
				$display_status = '<button onclick="send_link_ajax(' . $product_id . ', ' . $customer_id . ', \'' . $payment_link . '\', \'' . addslashes($title) . '\')" id="resend_1" class="button-action" value="Resend">Resend</button>';
				$delete_link = '<button onclick="delete_link(' . $order->ID . ')" class="button-action delete-added-user" value="Delete">Delete</button>';
				//send_link_ajax(product_id, user_id, payment_link, event_name)
?>
		<tr class="order_parent order_<?=$order->ID?>" id="order_parent_<?=$order->ID?>">
			<td class="order-events-table-td-1">&nbsp;</td>
			<td class="order-events-table-td-2"><?=$customer_link?></td>
			<td class="order-events-table-td-3">$<?=number_format( (float) $amount, 2, '.', '' )?></td>
			<td class="order-events-table-td-4">&nbsp;</td>
			<td class="order-events-table-td-5"><?=$display_status?><?=$delete_link?></td>
		</tr>
<?php
			}
		}
		
		$displayed_attendees = [];
		$attendies_count = 0;
		//vd($orders, '$orders');
		if($orders)
		{
			foreach($orders as $order)
			{
				$payment_status = $order->get_status();
				if('trash' === $payment_status)
					continue;
				
				
				
				//if($payment_status == 'completed' || $payment_status == 'refunded')
					//$display_status = 'Paid';
				//else
					$display_status = wph_get_order_status($order);
				
				$order_id = $order->get_id();
				vd($display_status, '$display_status ' . $order_id);
				
				$user_agent = $order->get_meta( '_wc_order_attribution_user_agent', true );
				$is_normal_order = $user_agent ? 1 : 0;
				
				$item_id = 0;
				foreach ( $order->get_items() as $item_id => $item ) {
					$iid = $item->get_id();
					$item_product_id = get_product_id_by_order_item_id( $iid );
					if($item_product_id == $product_id)
					{
						$item_id = $iid;
						break;
					}
				}
				
				$customer_id = $order->get_customer_id();
				$customer_link = $customer_name = '';
				
				if($customer_id)
				{
				    $customer = get_userdata($customer_id);
				    if($customer)
					{
						$customer_link = '/edit-profile/' . $customer->ID . '/';
						$customer_name = $customer->display_name;
					}
				}
				
				$tooltip = 'the payment process still needs to be completed.';
				
				$stripe_url = '';
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
						}
					}
				}
				
				if($stripe_url)
				{
					$display_status = '<a target="_blank" title="' . $tooltip . '" href="' . $stripe_url . '">' . $display_status . '</a>';
				}
				else
				{
					$payment_intent_id = $order->get_meta('_stripe_intent_id');
					if($payment_intent_id)
						$display_status = '<a target="_blank" title="' . $tooltip . '" href="https://dashboard.stripe.com/payments/' . $payment_intent_id . '">' . $display_status . '</a>';
				}
				
				$wc_product = wc_get_product($product_id);
				
			    $attendee_args = array(
			        'post_type'      => 'etn-attendee',
			        'post_status'      => 'publish',
			        'posts_per_page' => -1,
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key'     => 'etn_attendee_order_id',
							'value'   => $order_id,
							'compare' => '=',
						),
					),
			    );
				
				if($event_id)
				{
					$attendee_args['meta_query'][] = array(
														'key'     => 'etn_event_id',
														'value'   => $event_id,
														'compare' => '=',
													);
				}
				
				$attendees = get_posts($attendee_args);
				
				$attendees_array = [];
				$k = 0;
				$i = 0;
				
				if($attendees)
				{
					foreach($attendees as $attendee)
					{
						$attendees_array[$k]['ID'] = $attendee->ID;
						$attendees_array[$k]['name'] = $attendee->post_title;
						$attendees_array[$k]['etn_status'] = get_post_meta($attendee->ID, 'etn_status', TRUE);
						$attendees_array[$k]['etn_ticket_price'] = floatval(get_post_meta($attendee->ID, 'etn_ticket_price', TRUE));
						$status = $order->get_status();
						
						//if($attendees_array[$k]['etn_status'] == 'success')
						if($status == 'completed')
						{
							$attendees_array[$k]['payment_status'] = 'Paid';
							$payment_status = 'Paid';
						}
						else
						{
							$payment_status = $attendees_array[$k]['payment_status'] = ucfirst($status);
						}
						
						$attendees_array[$k]['etn_ticket_price'] = get_post_meta($attendee->ID, 'etn_ticket_price', TRUE);
						$k++;
					}
				}
				else
				{
					$price = $wc_product->get_price();
					$attendees_array[$k] = array(
											'ID' => 0,
											'name' => $customer_name,
											'etn_ticket_price' => $price,
											);
					
					$status = $order->get_status();
					
					if($status == 'completed')
					{
						$attendees_array[$k]['payment_status'] = 'Paid';
						$payment_status = 'Paid';
					}
					else
					{
						$etn_status = isset($attendees_array['etn_status']) ? ucfirst($attendees_array['etn_status']) : 'Failed';
						$payment_status = $attendees_array[$k]['payment_status'] = $etn_status;
					}
				}
				
				$counter = 0;
				
				foreach($attendees_array as $attendees_element)
				{
					//vd($attendees_element['ID'], '$attendees_element');
					if(in_array($attendees_element['ID'], $displayed_attendees))
						continue;
					$displayed_attendees[] = $attendees_element['ID'];
					$etn_status_custom = get_post_meta($attendees_element['ID'], 'etn_status_custom', true);
					
					if($etn_status_custom == 'refund')
					{
						$attendee_display_status = 'Refund (full)';
						//continue;
					}
					else
						$attendee_display_status = $display_status;
					$counter++;
					$all_totals += (float)$attendees_element['etn_ticket_price'];
					$for = $attendees_element['name'] !== $customer_name ? ' (for ' . $attendees_element['name'] . ')' : '';
					$order_ID = $wph_is_site_admin ? '<a href="/wp-admin/admin.php?page=wc-orders&action=edit&id=' . $order_id . '">order_ID</a>' : 'order ID';
					
					$attendies_count++;
					//vd($attendies_count, '$attendies_count');
?>
		<tr class="order_child order_child_<?=$event->ID?> order_<?=$event->ID?>">
			<td class="order-events-table-td-1">
				<strong><?=$order_ID?></strong> <a href="/order/<?=$order_id?>/"><?=$order_id?></a><br>
				<!-- strong>customer ID</strong> <?=$customer_id?><br>
				<strong>attendee ID</strong> <?=$attendees_element['ID']?> -->
			</td>
			<td class="order-events-table-td-2"><a href="<?=$customer_link?>"><?=$customer_name?></a><?=$for?></td>
			<td class="order-events-table-td-3" id="attendee_<?=$attendees_element['ID']?>" data-html="" data-amount="<?=$attendees_element['etn_ticket_price']?>">$<?=number_format( (float) $attendees_element['etn_ticket_price'], 2, '.', '' )?></td>
			<td class="order-events-table-td-4"><?=$attendee_display_status?></td>
			<td class="order-events-table-td-5">
<?php
					if($display_status == 'Pending')
					{
?>
				<button data-event_name="<?=addslashes($title)?>" data-product_id="<?=$event->ID?>" data-user_id="<?=$customer_id?>" data-order_id="<?=$order->ID?>" onclick="resend_payment_link_ajax(this)" id="resend_1" class="button-action" value="Resend">Resend</button>
<?php
					}
					else
					{
?>
				<button class="button-action" onclick="window.location.href='/order/'+<?=$order_id?>;" value="View">View</button>
				<button id="refund_button_<?=$order_id?>_<?=$counter?>" onclick="refund_order(<?=$order_id?>, <?=$item_id?>, this, <?=$attendees_element['ID']?>, <?=$is_normal_order?>)" class="button-action" value="Refund">Refund</button>
<?php
					}
					
					if($is_subscription && $wcs_installed)
					{
					    $subscriptions = wcs_get_subscriptions_for_order( $order_id, array( 'subscriptions_per_page' => -1 ) );
					    
					    if ( ! empty( $subscriptions ) ) {
					        foreach ( $subscriptions as $subscription ) {
					            // Ensure the subscription belongs to the specified customer
					            if ( $subscription->get_user_id() == $customer_id ) {
								
					                // Check if the product is part of the subscription
					                foreach ( $subscription->get_items() as $item ) {
					                    if ( $item->get_product_id() == $product_id ) {
					                        // Get the subscription status
											
					                        $status = $subscription->get_status(); // e.g. 'active', 'cancelled', 'on-hold', 'expired'
											if($status != 'cancelled' && $status != 'expired')
												display_vendor_subscription_management_links( $subscription->get_id(), $status );
					                        echo '<br>' . $status;
					                        break 2; // Exit both loops
					                    }
					                }
					            }
					        }
					    } else {
							echo '<br>not available';
					        //echo "No subscriptions found for this order.";
					    }
					}
?>
			</td>
		</tr>
<?php
				}
			}
		}
	}
	
	$j = 1;
	if($book_data)//for lessons, booked but not charged yet
	{
		$count_book_data = count($book_data);
		$product_id = $event_id;
		$product_name = get_the_title($product_id);
		$permalink = get_permalink($product_id);
		
		foreach($book_data as $book_data_item)
		{
			vd($book_data_item, 'book_data_item');
			$customer_id = $book_data_item['user_id'];
			
			$has_orders = 0;
			$has_methods = 0;
			
			vd($customer_id, '$customer_id');
			if($customer_id)
			{
			    $customer = get_userdata($customer_id);
			    if($customer)
				{
					$customer_link = '/edit-profile/' . $customer->ID . '/';
					$customer_name = $customer->display_name;
				}
				
				$has_orders = has_orders($current_user_id, $customer_id);
				
				if(!$has_orders) {
					$booked_a_lesson = booked_a_lesson($current_user_id, $customer_id);
					vd($booked_a_lesson, '$booked_a_lesson');
					$has_orders = $booked_a_lesson;
				}
				
				$saved_methods = wc_get_customer_saved_methods_list($customer_id);
				if($saved_methods)
					$has_methods = 1;
				else
					$has_orders = 0;
				//vd($customer_id, '$customer_id 2');
			}
			
			//vd($has_orders, $customer_name . ' ID ' . $customer_id . ' $has_orders');
			
			$payment_status = '';
			$display_status = isset($display_statuses[$payment_status]) ? $display_statuses[$payment_status] : '&nbsp;';
			
			if(!empty($book_data_item['players']))
			{
				//vd($book_data_item['players'], '$book_data_item[\'players\']');
				foreach($book_data_item['players'] as $player)
				{
					//vd($player, '$player');
					if(!is_array($player) || !empty($player['charged']) || !empty($player['link_id']))
						continue;
					$attendies_count++;
					
					//vd($attendies_count, '$attendies_count');
					
					$for = $player['player']
					 !== $customer_name ? 
					 ' (for ' . $player['player'] . ')' 
					 : '';
?>
		<tr id="player_<?=$j?>" value="<?=addslashes($player['player'])?>" class="order_child order_child_<?=$event_id?> order_<?=$event_id?>">
			<td class="order-events-table-td-1">
			</td>
			<td class="order-events-table-td-2"><a href="<?=$customer_link?>"><?=$customer_name?></a><?=$for?></td>
			<td class="order-events-table-td-3" id="input_player_td_<?=$j?>">&nbsp;</td>
			<td class="order-events-table-td-4"><?=$display_status?></td>
			<td class="order-events-table-td-5" id="actions_player_td_<?=$j?>">
<?php
					//if($has_methods)
					if(1)
					{
?>
				<button onclick="javascript:add_order_payment_booked(<?=$j?>, <?=$product_id?>, <?=$book_data_item['user_id']?>, '<?=addslashes($player['player'])?>', <?=$has_orders?>)" class="button-add event-button-add button-action">Begin Checkout</button>
<?php
					}
					else
					{
?>
				<button onclick="javascript:alert('No saved payment methods')" class="button-add event-button-add button-action" disabled>No Card</button>
<?php
					}
?>
			</td>
		</tr>
<?php
					$j++;
				}
			}
		}
	}
	
	if(!$events && !$book_data)
	{
?>
		<tr>
			<td colspan="5">No events found</td>
		</tr>
<?php
	}
?>
		</tbody>
	</table>
<?php
	if(!empty($all_totals))
	{
?>
	<script>
	document.getElementById('all_totals').innerHTML = '$<?=number_format( (float) $all_totals, 2, '.', '' )?>';
	</script>
<?php
	}
	
	if(!empty($attendies_count))
	{
?>
	<script>
	document.getElementById('count_items').innerHTML = '<?=$attendies_count?>';
	</script>
<?php
	}
	
	//vd($attendies_count, '$attendies_count');
?>
</div>
<div id="inner_frame_wrap">
	<iframe id="inner_frame" style="width:100%;height:200px;"></iframe>
</div>
<?php
	if(!empty($_REQUEST['add_player']))
	{
?>
<script>
document.addEventListener("DOMContentLoaded", function() {
	var add_order = document.getElementById('add_order');
	if(add_order)
	{
		add_order.click();
		
	    jQuery('html, body').animate({
	        scrollTop: jQuery('#orders-body').offset().top
	    }, 1000);
	}
});
</script>
<?php
	}
?>
<script>
function delete_link(post_id) {

    if (!confirm('Are you sure you want to delete this item?')) {
        return;
    }

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            action: 'rez_delete_post',
            post_id: post_id,
            nonce: '<?php echo wp_create_nonce("rez_delete_post_nonce"); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Delete failed');
        }
    });
}
</script>