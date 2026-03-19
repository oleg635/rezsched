<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

$current_user_id = $logged_user_id = get_current_user_id();

if(!$current_user_id)
{
?>
<script>
	window.location.href = '/';
</script>
<?php
	return;
}

$events = [];
$attendies_count = 0;

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


//$current_user_id = get_current_user_id();
?>

<style>
.clear_all {
    float: right;
    padding-top: initial;
    margin-top: 1.8rem;
    color: #1591a1;
    text-decoration: none;
    position: absolute;
    right: 0px;
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

td input, td textarea {
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
	margin-bottom:12px;
}
<?php
//if(!$event_name)
if(0)
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
</style>
<script>
var recommended_price = 0;

function expand_event(event_id)
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
			console.log('added_trs_ajax');
			console.log(added_trs_ajax);
            const item = document.createElement('div');
            item.className = 'suggestion-item';

            const link = document.createElement('a');
            link.href = '#';

			link.addEventListener('click', function(event) {
				event.preventDefault();
				set_user_input(added_trs_ajax, suggestion.ID, suggestion.display_name)
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

function set_user_input(added_trs, ID, display_name)
{
	document.querySelector('#user_' + added_trs).innerHTML = display_name;
	document.querySelector('#input_' + added_trs).innerHTML = '<input id="amount_' + added_trs + '" type="text">';
	document.querySelector('#user_id_' + added_trs).value = ID;
	//alert(display_name);
}

function show_input(a)
{
	var search_facility_input = a.parentNode.querySelector('.search_facility_input');
	search_facility_input.style.display = 'block';
	search_facility_input.focus();
	//a.style.display = 'none';
	var links = a.parentNode.querySelectorAll('a');
	for(var i = 0; i < links.length; i++)
	{
		links[i].style.display = 'none';
	}
}

var added_trs = 0;

function add_order_payment(item_id, product_id)
{
	added_trs++;
	console.log('added_trs');
	console.log(added_trs);

	var arrow = document.getElementById('arrow_' + item_id);
	if(arrow.classList.contains('arrow-down'))
		expand_event(item_id);
	
    const newRow = document.createElement('tr');
    newRow.id = 'added_user_' + added_trs;
    newRow.className = 'order_child order_child_' + item_id + ' order_' + item_id;
    newRow.style.display = 'table-row';
    newRow.innerHTML = `
        <td class="order-events-table-td-1">&nbsp;</td>
        <td id="user_` + added_trs + `" class="order-events-table-td-2">
			<div class="search_facility">
				<a class="a_search_user" href="javascript:void(0)" onclick="javascript:show_input(this)" style="display:block;">Search User</a>
				<a class="a_add_new_user" target="_blank" href="/add-user/">Add New User</a>
				<input id="searchInput` + added_trs + `" type="text" class="search_facility_input" oninput="javascript:get_users_ajax(` + item_id + `, this.value, ` + added_trs + `)">
				<div id="suggestions" style="display:none;"></div>
			</div>	
		</td>
        <td id="input_` + added_trs + `" class="order-events-table-td-3">&nbsp;</td>
		<td class="order-events-table-td-4">&nbsp;</td>
        <td class="order-events-table-td-6">
            <button onclick="charge_added_user(` + added_trs + `)" id="charge_` + added_trs + `" class="button-action" value="Charge">Charge</button>
            <button onclick="delete_added_user(` + added_trs + `)" id="delete_` + added_trs + `" class="button-action delete-added-user" value="Cancel">Cancel</button>
			<input type="hidden" id="user_id_` + added_trs + `" value="">
			<input type="hidden" id="product_id_` + added_trs + `" value="` + product_id + `">
        </td>
    `;
	
	var selector = '#orders-body tr.order_' + item_id;
    const rows = document.querySelectorAll(selector);
	
	console.log('item_id');
	console.log(item_id);
	console.log('selector');
	console.log(selector);
	console.log('rows');
	console.log(rows);
    if (rows.length > 0) {
	    const firstOrderRow = rows[0];
    	firstOrderRow.insertAdjacentElement('afterend', newRow);
    } else {
        document.getElementById('orders-body').appendChild(newRow);
    }
}

function delete_player(player_id, product_id, user_id, player_name)
{
	console.log('player_id');
	console.log(player_id);
}

function add_order_payment_booked(player_id, product_id, user_id, player_name)
{
	console.log('player_id');
	console.log(player_id);
	
	var amount_value = '';
	/*
	if(1
		&& booked_seats !== undefined
		&& price_ranges !== undefined
		&& price_ranges[booked_seats + 1] !== undefined
	)
	{
		amount_value = price_ranges[booked_seats + 1];
	}
	*/
    var input_player_td = document.getElementById('input_player_td_' + player_id);
	if(input_player_td)
	{
		//input_player_td.innerHTML = '<input id="amount_player_' + player_id + '" type="text" value="' + amount_value + '">';
	}
	
    //var actions_player_td = document.getElementById('actions_player_td_' + player_id);
	//if(actions_player_td)//player_name
	{
		var amount_player = document.getElementById('amount_player_' + player_id);
		if(amount_player)
			amount_player.style.display = 'block';
	    var add_order = document.getElementById('add_order_' + player_id);
		if(add_order)
			add_order.style.display = 'none';
	    var charge_player = document.getElementById('charge_player_' + player_id);
		if(charge_player)
			charge_player.style.display = 'inline-block';
	    var delete_player = document.getElementById('delete_player_' + player_id);
		if(delete_player)
			delete_player.style.display = 'inline-block';
		/*
		actions_player_td.innerHTML += `<button onclick="charge_added_player(` + player_id + `)" id="charge_player_` + player_id + `" class="button-action" value="Charge">Charge</button>
            <button onclick="delete_added_user_player(` + player_id + `)" id="delete_player_` + player_id + `" class="button-action delete-added-user" value="Cancel">Cancel</button>
			<input type="hidden" id="user_player_id_` + player_id + `" value="` + user_id + `">
			<input type="hidden" id="product_player_id_` + player_id + `" value="` + product_id + `">
			<input type="hidden" id="player_name_` + player_id + `" value="` + player_name + `">`;
		*/
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
	waiting(1, button_id);
	
	var user_id = document.getElementById('user_id_' + added_user).value;
	var product_id = document.getElementById('product_id_' + added_user).value;
	//alert('charge_amount ' + charge_amount + ', user_id ' + document.getElementById('user_id_' + added_user).value);
	//save_event_description_ajax(user_id, product_id, charge_amount_value, 0, button_id, 0, '');
	start_checkout(user_id, product_id, charge_amount_player_value, player_name);
}

function start_checkout(user_id, product_id, charge_amount_value, player_name)
{
	var inner_frame = document.getElementById('inner_frame');
	inner_frame.src = '/?user=' + user_id + '&product_id=' + product_id + '&simulate_checkout=1&vendor=<?=$current_user_id?>&price=' + charge_amount_value + '&player_name=' + player_name;
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
		//alert('Please insert charge amount');
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
	var button_id = 'charge_player_' + added_player;
	waiting(1, button_id);
	
	var user_id = document.getElementById('user_player_id_' + added_player).value;
	var product_id = document.getElementById('product_player_id_' + added_player).value;
	
	//save_event_description_ajax(user_id, product_id, charge_amount_player_value, 0, button_id, added_player, player_name);
	start_checkout(user_id, product_id, charge_amount_player_value, player_name);
}

function create_payment_link(added_player)
{
	//https://rezbetadev.wpenginepowered.com/wp-json/custom/v1/create-link/?user_id=147&product_id=6914&amount=40
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
	
	var amount = document.getElementById('amount_player_' + added_player).value;
	if(!amount)
	{
		//alert('Please insert charge amount');
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
	//var button_id = 'charge_player_' + added_player;
	//waiting(1, button_id);
	
	var user_id = document.getElementById('user_player_id_' + added_player).value;
	var product_id = document.getElementById('product_player_id_' + added_player).value;
	/*
	if('Add New Player' == attendee_name)
	{
		var first_name = document.getElementById('first_name_0').value;
		var last_name = document.getElementById('last_name_0').value;
		attendee_name = first_name + ' ' + last_name;
		add_new_player_ajax(user_id, first_name, last_name);
	}
	*/
	attendee_name = encodeURIComponent(player_name);
	
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
	var event_name = document.getElementById('event_name_' + added_player).value;
	
	console.log('product_id');
	console.log(product_id);
	console.log('user_id');
	console.log(user_id);
	console.log('payment_link');
	console.log(payment_link);
	console.log('event_name');
	console.log(event_name);
	//return;
	send_link_ajax(product_id, user_id, payment_link, event_name);
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
	
	//alert('show_loader();');
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

function refund_order(order_id, button)
{
	var amount_td = button.parentNode.parentNode.querySelector('.order-events-table-td-3');
	amount_td.dataset.html = amount_td.innerHTML;
	amount_td.innerHTML = '<input id="refund_amount_' + order_id + '" style="display: block;" type="text" value="' + amount_td.dataset.amount + '"><button class="button-action submit_refund" onclick="refund_order_ajax(' + order_id + ', \'' + button.id + '\')">Ok</button><button class="button-action submit_refund" onclick="refund_cancel(' + order_id + ', \'' + button.id + '\')">Cancel</button>';
}

function refund_order_ajax(order_id, button_id)
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
	waiting(1, 0);
	
	var data = {
	    action: 'dokan_refund_request',
	    order_id: order_id,
	    refund_amount: refund_amount_input.value,
	    refunded_amount: 0,
	    refund_reason: '',
	    line_item_qtys: {},
	    line_item_totals: {},
	    line_item_tax_totals: {},
	    api_refund: true,
	    restock_refunded_items: true,
	    security: '<?=wp_create_nonce('order-item');?>'
	};
	
	
	jQuery.post('<?=admin_url('admin-ajax.php')?>', data)
    .done(function(response) {
	    if (response.success) {
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
			show_system_message(
								'Error', 
								'Refund failed:<br>' + response.data.message, 
								'error',
								'',
								'', 
								'Close',
								''
			);
	    }
    })
    .fail(function(jqXHR, textStatus, errorThrown) {

		var message = 'Request failed';
		var response = JSON.parse(jqXHR.responseText);
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

	//save_event_description_ajax(0, 0, refund_amount_input.value, order_id, button_id, 0, '');
}

function delete_added_user(added_user)
{
	var added_user_tr = document.getElementById('added_user_' + added_user);
	if(added_user_tr)
	{
		added_user_tr.remove();
	}
}

function delete_added_user_player(player_id)
{
/*
    var input_player_td = document.getElementById('input_player_td_' + player_id);
	if(input_player_td)
	{
		input_player_td.innerHTML = '';
	}
*/
    var amount_player = document.getElementById('amount_player_' + player_id);
	if(amount_player)
		amount_player.style.display = 'none';
    var add_order = document.getElementById('add_order_' + player_id);
	if(add_order)
		add_order.style.display = 'inline-block';
    var charge_player = document.getElementById('charge_player_' + player_id);
	if(charge_player)
		charge_player.style.display = 'none';
    var delete_player = document.getElementById('delete_player_' + player_id);
	if(delete_player)
		delete_player.style.display = 'none';
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
						//alert(response.item_id);
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

var save_event_description_loading = 0;

function save_event_description_ajax(event_id, user_id, delete_player)
{
	//var group_select = document.getElementById('group_select').value;
	var player_age = document.getElementById('player_age_' + event_id).value;
	var player_level = document.getElementById('player_level_' + event_id).value;
	var event_description = document.getElementById('event_description_' + event_id).value;
	var post_data = {
			event_id: event_id,
			user_id: user_id,
			player_age: player_age,
			player_level: player_level,
			event_description: event_description,
			delete_player: delete_player,
			security: '<?php echo wp_create_nonce('book-group-event-nonce'); ?>',
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
					
					var message = response.message ? response.message : 'Description has been saved';
					
					open_new_window = false;
					show_system_message(
										'Success', 
										message, 
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
</script>
<style>
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
</style>
<style>
.order-container .order-events-actions button.button-add {
	display: initial;
}
</style>
<?php
	include('system-message.php');

	
	$parent_posts = get_parent_posts($current_user_id);
	$post_status = 'publish';
	
//get ALL lessons first

	$args = array(
		'post_type'      => 'etn',
		'post_status'	=> $post_status,
		'author'         => $current_user_id,
		'posts_per_page' => -1,
		'meta_key'       => 'etn_start_timestamp',
		'orderby'        => 'meta_value', // Use meta_value_num for numeric sorting
		'post__not_in' => $parent_posts,
	    'offset'         => 0,
	    'posts_per_page' => -1,
		'order'          => 'DESC',
		'meta_query' => array(
				'relation' => 'AND',
		),
		'tax_query' => array(
				'relation' => 'AND',
		),
	);
	
	$args['tax_query'][] = array(
            'taxonomy'     => 'etn_category',
            'field'   => 'id',
			'terms' => 31,
	);

$date_from = $date_to = '';

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

$all_booked_event_posts = get_posts($args);

//================================
	$sold_out = [0];
	
	foreach($all_booked_event_posts as $event)
	{
		$product_id = $event->ID;
		$book_data = get_post_meta($event->ID, 'book_data');
		//vd($book_data, '$book_data');
		if($book_data)
		{
			$count_book_data = count($book_data);
			//vd($count_book_data, '$count_book_data');
			$product_id = $event_id = $event->ID;
			
			$visible_players = 0;
			foreach($book_data as $book_data_item)
			{
				//if($product_id == 6563)
					//vd($book_data_item, 'book_data_item');
				
				if(!empty($book_data_item['players']))
				{
					foreach($book_data_item['players'] as $player)
					{
						if(!is_array($player))
							continue;
						if(empty($player['charged']))
						{
							$visible_players++;
						}
					}
				}
				else
					$visible_players++;
			}
			
			if(empty($visible_players))
			{
				$sold_out[] = $product_id;
			}
		}
	}
	
	$parent_posts = array_merge($parent_posts, $sold_out);
	
	$max_events = 20;
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	$offset = ($paged - 1) * $max_events;
	
	$total_events = count($all_booked_event_posts);
	$total_events -= count($sold_out);
	
	//vd($sold_out, '$sold_out');
	//================================
	
	$args = array(
		'post_type'      => 'etn',
		'post_status'	=> $post_status,
		'author'         => $current_user_id,
		'posts_per_page' => -1,
		'meta_key'       => 'etn_start_timestamp',
		'orderby'        => 'meta_value',
		'post__not_in' => $parent_posts,
	    'offset'         => $offset,
	    'posts_per_page' => $max_events,
		'order'          => 'DESC',
		'meta_query' => array(
				'relation' => 'AND',
		),
		'tax_query' => array(
				'relation' => 'AND',
		),
	);
	
	$args['tax_query'][] = array(
            'taxonomy'     => 'etn_category',
            'field'   => 'id',
			'terms' => 31,
	);

if(!empty($_REQUEST['date_start']) && !empty($_REQUEST['date_end']))
{
	$date_from = $_REQUEST['date_from'];
	$date_to = $_REQUEST['date_to'];
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

	$booked_event_posts = get_posts($args);
	
	//vd($args, '$args');
	//vd($booked_events, '$booked_events');
?>
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

.events-page-wrapper .input-group.filter-input-group {
    display: grid;
    grid-template-areas: "l1 l2 l3 b" "s1 s2 s3 b";
}
.events-page-wrapper #etn_event_month {
    grid-area: s1;
}
.events-page-wrapper #etn_event_date_from {
    grid-area: s2;
}
.events-page-wrapper #etn_event_date_to {
    grid-area: s3;
}


	</style>
	<script>
	function edit_old(event_id, mode)
	{
		if(mode)
		{
			var edit = document.getElementById('edit_' + event_id);
			if(edit)
				edit.style.display = 'block';
			var details = document.getElementById('value_' + event_id);
			if(details)
				details.style.display = 'none';
		}
		else
		{
			var edit = document.getElementById('edit_' + event_id);
			if(edit)
				edit.style.display = 'none';
			var details = document.getElementById('details_' + event_id);
			if(details)
				details.style.display = 'block';
		}
	}
	</script>
<script>
function edit(event_id, mode) {
    // Create class selectors for edit and details based on event_id
    var editClass = 'edit_' + event_id;
    var detailsClass = 'value_' + event_id;

    // Select all elements with the edit class
    var editElements = document.querySelectorAll('.' + editClass);

    // Select all elements with the details class
    var detailsElements = document.querySelectorAll('.' + detailsClass);

    if (mode) {
        // Show all edit elements and hide all details elements
        editElements.forEach(function(editElement) {
            editElement.style.display = 'block';
        });
        detailsElements.forEach(function(detailsElement) {
            detailsElement.style.display = 'none';
        });
    } else {
        // Hide all edit elements and show all details elements
        editElements.forEach(function(editElement) {
            editElement.style.display = 'none';
        });
        detailsElements.forEach(function(detailsElement) {
            detailsElement.style.display = 'block';
        });
    }
}
</script>
<div class="events-page-wrapper">
	<div class="etn_search_shortcode etn_search_wrapper">
			<h3 style="display: inline-block;">Filter by:</h3><a class="clear_all" href="." style="">Clear All</a>
				<form method="GET" class="etn_event_inline_form filter_form" action="<?=get_url_without_pagination()?>">
				<div class="etn-event-search-wrapper">
					<div class="input-group filter-input-group">
						<label for="etn_event_month" id="etn_event_month_label">Month</label>
						<select name="date_range" id="etn_event_month" class="etn_event_select2 etn_event_select etn_event_month">
							<option value="">Select event month</option>
<?php
		$all_user_events = get_user_lessons($current_user_id, $fields = 'ids');
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
				ksort( $dates );
				$selected_date = !empty($_REQUEST['date_range']) ? $_REQUEST['date_range'] : 0;
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
						<label for="etn_event_location" id="etn_event_date_from_label">Start Date</label>
						<select name="date_from" id="etn_event_date_from" fdprocessedid="h8glb">
							<option value="">Select a date</option>
<?php
				for($i = 1; $i < 32; $i++)
				{
					$selected = $date_from == $i ? ' selected' : '';
?>
							<option value="<?=$i?>"<?=$selected?>><?=$i?></option>
<?php
				}
?>
						</select>
						<label for="etn_event_location" id="etn_event_date_to_label">End Date</label>
						<select name="date_to" id="etn_event_date_to" fdprocessedid="t4aqku">
							<option value="">Select a date</option>
<?php
				for($i = 1; $i < 32; $i++)
				{
					$selected = $date_to == $i ? ' selected' : '';
?>
							<option value="<?=$i?>"<?=$selected?>><?=$i?></option>
<?php
				}
?>
						</select>
						<div class="search-button-wrapper">
							<button type="submit" class="etn-btn etn-btn-primary">Filter Now</button>
						</div>
					</div>
	            </div>
			</form>
		</div>
	</div>
	<script>
	// Function to calculate the number of days in a given month and year
	function getDaysInMonth(month, year) {
	    return new Date(year, month, 0).getDate();
	}
	
	// Function to populate the "From" and "To" date selects
	function populateDateSelects() {
		//alert('populateDateSelects()');
	    const dateRangeSelect = document.getElementById("etn_event_month");
	    const fromDateSelect = document.getElementById("etn_event_date_from");
	    const toDateSelect = document.getElementById("etn_event_date_to");
	
	    // Get the selected value from the date range select
	    const selectedValue = dateRangeSelect.value;
	
	    if (!selectedValue) {
	        // If no value is selected, clear the "From" and "To" selects
	        clearSelect(fromDateSelect);
	        clearSelect(toDateSelect);
	        return;
	    }
	
	    // Extract year and month from the selected value
	    const year = parseInt(selectedValue.substring(0, 4), 10);
	    const month = parseInt(selectedValue.substring(4), 10);
	
	    // Get the number of days in the selected month and year
	    const daysInMonth = getDaysInMonth(month, year);
	
		var selected_from = '<?php if(!empty($_REQUEST['date_from'])) echo $_REQUEST['date_from']; ?>';
		var selected_to = '<?php if(!empty($_REQUEST['date_to'])) echo $_REQUEST['date_to']; ?>';
	
	    populateSelect(fromDateSelect, daysInMonth, selected_from);
	    populateSelect(toDateSelect, daysInMonth, selected_to);
	}
	
	// Helper function to clear a select element
	function clearSelect(selectElement) {
	    selectElement.innerHTML = "<option value=''>Select a date</option>";
	}
	
	// Helper function to populate a select element with days, adding leading zeros to values
	function populateSelect(selectElement, daysInMonth, selected_day) {
	    clearSelect(selectElement); // Clear existing options
	    for (let day = 1; day <= daysInMonth; day++) {
	        const option = document.createElement("option");
	        option.value = day.toString().padStart(2, '0'); // Add leading zero to the value
	        
			if(selected_day == option.value)
				option.selected = true;
			
			option.textContent = day; // Keep the display text as a plain number
	        selectElement.appendChild(option);
	    }
	}
	
	document.getElementById("etn_event_month").addEventListener("change", populateDateSelects);
	//populateDateSelects();
	</script>
	<table class="order-events-table booked-lessons-table">
		<tbody id="orders-body">

<?php
	$start_dates = [];
	$j = 1;
	
	foreach($booked_event_posts as $event)
	{
		$product_id = $event->ID;
		$product_name = get_the_title($product_id);
		//$permalink = get_permalink($product_id);
		$permalink = '/orders/' . $product_id;
		
		$start_date = get_post_meta($event->ID, 'etn_start_date', true);
		$start_time = get_post_meta($event->ID, 'etn_start_time', true);
		$timestamp = strtotime($start_date . ' ' . $start_time);
		$formatted_date = date('D M, d Y h:i A', $timestamp);
		$formatted_start_date = date('D - M d, Y', $timestamp);
		
		$book_data = get_post_meta($event->ID, 'book_data');
		$players_count = 0;
		$description_shown = FALSE;
		$booked_1_to_1 = FALSE;
		
		foreach($book_data as $book_data_entry)
		{
			if(!empty($book_data_entry['players']) && !empty($book_data_entry['group_select']) && $book_data_entry['group_select'] == 1)//if 1 to 1 booked
			{
				foreach($book_data_entry['players'] as $player)
				{
					if(empty($player['charged']))
					{
						$booked_1_to_1 = TRUE;
						break;
					}
				}
			}
		}
		
		foreach($book_data as $book_data_item)
		{
			$user_id = 0;
			
			if(!empty($book_data_item['user_id']))
				$user_id = $book_data_item['user_id'];
		    
			$customer = get_userdata($user_id);
			if(!$customer)
				continue;
			
			if(!empty($book_data_item['players']))
			{
				//if($event->ID == 6750)
					//vd($book_data_item['players'], '$book_data_item[\'players\']');
				foreach($book_data_item['players'] as $player)
				{
					if(!is_array($player))
						continue;
					if(!empty($player['charged']))
						continue;
					
					$players_count++;
				}
			}
			
				//$players_count += count($book_data_item['players']);
			
		}
		
		//get_event_attendees($event_id, $is_lesson);
		
		//$players_count += 1;
		$rowspan = ' rowspan="' . ($players_count + 1) . '"';
		
		$etn_total_avaiilable_tickets = get_post_meta($event->ID, 'etn_total_avaiilable_tickets', true);
		
		$avaiilable_tickets = 0;
		$booked_seats = get_booked_seats($event->ID);
		
		if(!$booked_1_to_1)
		{
			$booked_charged_orders = get_booked_orders($event->ID, TRUE);
			$orders_count = has_orders_for_product($event->ID, $booked_charged_orders);
			
			if(!$orders_count)
			{
				if($etn_total_avaiilable_tickets) 
				{
					$avaiilable_tickets = $etn_total_avaiilable_tickets - $booked_seats;
					if($avaiilable_tickets < 0)
						$avaiilable_tickets = 0;
				}
			}
		}
		
		//$price = '<span class="events_page_price lesson_price">' . wrap_prices_with_span(get_post_meta($event->ID, 'lesson_price', true)) . '</span>';
		
		$avaiilable = $booked_seats . '/' . $etn_total_avaiilable_tickets;
		
		if($avaiilable_tickets <= 0)
			$color_class = ' row-red';
		elseif($avaiilable_tickets < $etn_total_avaiilable_tickets)
			$color_class = ' row-yellow';
		else
			$color_class = ' row-green';

		//vd($booked_seats . ' ' . $etn_total_avaiilable_tickets . ' ' . $color_class, $product_name);
		
		if(!isset($start_dates[$start_date]))
		{
			$start_dates[$start_date] = $start_date;
?>
		<tr class="event_date_header">
			<td class="order-events-table-td-1" style="border-radius: 16px 0 0 16px;text-align: left;"><?=$formatted_start_date?></td>
			<td class="order-events-table-td-2" style="text-align: left;">Cllient</td>
			<td class="order-events-table-td-3">Amount</td>
			<td class="order-events-table-td-4" style="text-align: left;">Action</td>
			<td style="border-radius: 0 16px 16px 0;" class="order-events-table-td-6">Payment Status</td>
		</tr>
<?php
		}
		
		if($players_count)
		{
			$tr_style = 'border-top:3px solid #fff;';
			$td_style = 'border-radius: 0 16px 0 0;';
		}
		else
		{
			$tr_style = 'border-top:3px solid #fff;border-bottom:3px solid #fff;';
			$td_style = 'border-radius: 0 16px 16px 0;';
		}
		
		//$tr_style = '';
?>
		<tr style="<?=$tr_style?>" id="player_<?=$j?>" class="order_child order_child_<?=$event_id?> order_<?=$event_id?><?=$color_class?>">
			<td<?=$rowspan?> style="border-radius: 16px 0 0 16px;" class="order-events-table-td-1">
				<a title="Event ID <?=$event->ID?>" href="<?=$permalink?>"><strong><?=$product_name?></strong></a><br>
				<span><?=$formatted_date?></span><br>
				<a class="add_player" title="Event ID <?=$event->ID?>" href="<?=$permalink?>/?add_player=1">Add Player</a>
			</td>
<?php
		if(!$description_shown)
		{
			$description_shown = TRUE;
?>
			<td class="order-events-table-td-2 event-description" valign="top">
<?php
			//$event_description = !empty($book_data_item['event_description']) && $book_data_item['event_description'] != '-1' ? $book_data_item['event_description'] : '';
			//$player_age = !empty($book_data_item['player_age']) && $book_data_item['player_age'] != '-1' ? $book_data_item['player_age'] : '';
			//$player_level = !empty($book_data_item['player_level']) && $book_data_item['player_level'] != '-1' ? $book_data_item['player_level'] : '';
			$player_level = $player_age = $event_description = '';
			
			$player_age_meta = get_post_meta($event->ID, 'player_age', TRUE);
			if($player_age_meta)
				$player_age = $player_age_meta;
			
			$player_level_meta = get_post_meta($event->ID, 'player_level', TRUE);
			if($player_level_meta)
				$player_level = $player_level_meta;
			
			$event_description_meta = get_post_meta($event->ID, 'event_description', TRUE);
			if($event_description_meta)
				$event_description = $event_description_meta;

?>
				<div class="event_property events_page_event_level details_<?=$event->ID?>">Level:<br><strong class="value_<?=$event->ID?>"><?=$player_level?></strong></div>
				<input class="edit_element edit_<?=$event->ID?>" type="text" id="player_level_<?=$event->ID?>" value="<?=$player_level?>">
			</td>
			<td class="order-events-table-td-3 event-description">
				<div class="event_property events_page_event_age_group details_<?=$event->ID?>">Age&nbsp;group:<br><strong class="value_<?=$event->ID?>"><?=$player_age?></strong></div>
				<input class="edit_element edit_<?=$event->ID?>" type="text" id="player_age_<?=$event->ID?>" value="<?=$player_age?>">
			</td>
			<td class="order-events-table-td-4 event-description">
				<div class="event_property events_page_event_goals details_<?=$event->ID?>">Goals:<br><strong class="value_<?=$event->ID?>"><?=$event_description?></strong></div>
				<textarea class="edit_element edit_<?=$event->ID?>" id="event_description_<?=$event->ID?>"><?=$event_description?></textarea>
			</td>
			<td style="<?=$td_style?>" class="order-events-table-td-5 event-description">
				<div id="details_<?=$event->ID?>">
					<span class="events_page_grid_element_actions" onclick="edit(<?=$event->ID?>, 1)"></span>
				</div>
				<div id="edit_<?=$event->ID?>">
					<button onclick="save_event_description_ajax(<?=$event->ID?>, <?=$user_id?>, 0)" class="table_action edit_element button-add event-button-add button-action edit_<?=$event->ID?>">Save</button>
					<button onclick="edit(<?=$event->ID?>, 0)" class="table_action edit_element button-action delete-added-user edit_<?=$event->ID?>">Cancel</button>
				</div>
			</td>
		</tr>
<?php
		}
		//vd($book_data, '$book_data');
		if($book_data)
		{
			$count_book_data = count($book_data);
			//vd($count_book_data, '$count_book_data');
			$product_id = $event_id = $event->ID;
			$product_name = get_the_title($product_id);
			//$permalink = get_permalink($product_id);
			$permalink = '/orders/' . $product_id;
			
			$current_player = 1;
			
			//if($event_id == 6167)
				//vd($current_player, '$current_player = 1');
				
			//vd(function_exists('wc_get_customer_saved_methods_list'), 'function_exists(\'wc_get_customer_saved_methods_list\')');
			
			foreach($book_data as $book_data_item)
			{
				if(!$book_data_item)
					continue;
				//vd($book_data_item, '$book_data_item');
				$customer_id = $book_data_item['user_id'];
				$customer_name = $customer_link = '';
				
				$charge_method = 'create_payment_link';
				$button_value = 'Send Link';
				
				if($customer_id)
				{
				    $customer = get_userdata($customer_id);
				    if($customer)
					{
						$customer_link = '/edit-profile/' . $customer->ID . '/';
						$customer_name = $customer->display_name;
						
						$saved_methods = wc_get_customer_saved_methods_list($customer->ID);
						//$saved_methods = 0;
						if($saved_methods)
						{
							$charge_method = 'charge_added_player';
							$button_value = 'Charge';
						}
					}
					else
					{
						continue;
					}
				}
				
				$payment_status = '';
				$display_status = isset($display_statuses[$payment_status]) ? $display_statuses[$payment_status] : '&nbsp;';
				
				//vd($book_data_item, '$book_data_item');
				
				$visible_players = 0;
				
				if(!empty($book_data_item['players']))
				{
					$i = 1;
					
					foreach($book_data_item['players'] as $player)
					{
						if(!is_array($player))
							continue;
						
						//vd($current_player, '$current_player begin');
						
						//vd($player, '$player');
						$amount = $status = '';
						
						if(!empty($player['charged']))
						{
							$status = wph_get_order_status($player['charged']);
							
							if($order = wc_get_order( $player['charged'] ))
							{
								$amount = $order->get_subtotal();
							}
							
							continue;
						}
						else
							$visible_players++;
						
						$td_style = $current_player < $players_count ? '' : ' style="border-radius: 0 0 16px 0;"';
						if($current_player < $players_count)
						{
							$tr_style = '';
							$td_style = '';
						}
						else
						{
							$tr_style = 'border-bottom:3px solid #fff;';
							$td_style = 'border-radius: 0 0 16px 0;';
						}
						//vd($players_count, '$players_count');
						
						$for = $player['player'] !== $customer_name ? ' (for ' . $player['player'] . ')' : '';
?>
		<tr style="<?=$tr_style?>" class="<?=$color_class?>">
			<td class="order-events-table-td-2" style="text-align: left;"><a href="<?=$customer_link?>"><?=$customer_name?></a><?=$for?></td>
			<td class="order-events-table-td-3" id="input_player_td_<?=$j?>"><?=$amount?><input id="amount_player_<?=$j?>" style="display:none;" type="text" value=""></td>
			<td class="order-events-table-td-4" id="actions_player_td_<?=$j?>">
				<button id="add_order_<?=$j?>" onclick="javascript:add_order_payment_booked(<?=$j?>, <?=$product_id?>, <?=$book_data_item['user_id']?>, '<?=addslashes($player['player'])?>')" class="table_action button-add event-button-add button-action">Begin Checkout</button>
				<button id="delete_player_<?=$j?>" onclick="javascript:save_event_description_ajax(<?=$event->ID?>, <?=$book_data_item['user_id']?>, '<?=addslashes($player['player'])?>')" class="table_action button-action delete-added-user">Remove</button>
				<button onclick="<?=$charge_method?>(<?=$j?>)" id="charge_player_<?=$j?>" class="button-action" style="display:none;" value="Charge"><?=$button_value?></button>
				<button onclick="delete_added_user_player(<?=$j?>)" id="delete_player_<?=$j?>" class="button-action delete-added-user" style="display:none;" value="Cancel">Cancel</button>
				<input type="hidden" id="user_player_id_<?=$j?>" value="<?=$book_data_item['user_id']?>">
				<input type="hidden" id="product_player_id_<?=$j?>" value="<?=$product_id?>">
				<input type="hidden" id="player_name_<?=$j?>" value="<?=addslashes($player['player'])?>">
				<input type="hidden" id="event_name_<?=$j?>" value="<?=addslashes($product_name)?>">
			</td>
			<td style="<?=$td_style?>" class="order-events-table-td-5"><?=$status?></td>
		</tr>
<?php
						$j++;
						$i++;
						$current_player++;
						//vd($current_player, '$current_player end');
					}
				}
				
				if(0)//empty($visible_players))
				{
?>
<style>
.order_child_<?=$event_id?> {
	display:none;
}
</style>
<?php
				}
			}
		}
		
		//if(!$book_data)
		if(0)
		{
?>
			<td colspan="5">No events found</td>
<?php
		}
?>
		</tr>
<?php
	}
?>
		</tbody>
	</table>
<?php
		//vd($total_events, '$total_events');
		//vd($max_events, '$max_events');
		//vd($total_events > $max_events, '$total_events > $max_events');
		
        if($total_events > $max_events)
		{
	        $total_pages = ceil($total_events / $max_events);
			//vd($total_pages, '$total_pages');
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
?>
<style>
.key-block {
	font-family: Arial, sans-serif;
	font-size: 14px;
	color: #333;
	/*margin: 20px;*/
}

.key-block h4 {
	margin-bottom: 10px;
	font-size: 16px;
	font-weight: bold;
}

.key-item {
	display: flex;
	align-items: center;
	margin-bottom: 5px;
}

.key-circle {
	width: 16px;
	height: 16px;
	border-radius: 50%;
	margin-right: 4px;
}

.key-circle.green {
	background-color: #CBF6B4;
}

.key-circle.yellow {
	background-color: #FFE8AA;
}

.key-circle.red {
	background-color: #FFD6C9;
}
</style>
<div class="key-block">
	<h4>Key for Lessons:</h4>
	<div class="key-item">
		<div class="key-circle green"></div>
		<span>- all spots available</span>
	</div>
	<div class="key-item">
		<div class="key-circle yellow"></div>
		<span>- limited spots available</span>
	</div>
	<div class="key-item">
		<div class="key-circle red"></div>
		<span>- no spots available</span>
	</div>
</div>
<div id="inner_frame_wrap">
	<iframe id="inner_frame" style="width:100%"></iframe>
</div>
