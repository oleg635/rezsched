<?php
$product_id = !empty($_REQUEST['product']) ? $_REQUEST['product'] : 0;
$current_user_id = get_current_user_id();

$simulate_checkout_vendor = get_user_meta($logged_user_id, 'simulate_checkout_vendor', TRUE);
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
	return;
}

/**
 * Get product IDs for current user
 */
$product_ids = get_posts([
    'post_type'      => 'product',
    'post_status'    => ['publish', 'draft', 'private'],
    'author'         => $current_user_id,
    'fields'         => 'ids',
    'posts_per_page' => -1,
]);

$categories = [];

if ( $product_ids ) {

	$terms = wp_get_object_terms(
	    $product_ids,
	    'product_cat',
	    [
	        'hide_empty' => false,
	        'exclude'    => get_terms([
	            'taxonomy' => 'product_cat',
	            'slug'     => 'uncategorized',
	            'fields'   => 'ids',
	        ]),
	    ]
	);

    if ( ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term ) {
            // only top-level categories
            if ( $term->parent === 0 ) {
                $categories[ $term->slug ] = $term;
            }
        }
    }
}
?>
<script>
var product_id = <?=$product_id?>;
</script>
<style>
/* Layout */
.rez-pos-checkout {
    max-width: 100%;
    padding: 0;
    font-family: inherit;
}

/* Client field */
.rez-pos-field {
    max-width: 420px;
    margin-bottom: 24px;
}

.rez-pos-field label {
    display: block;
    font-weight: 500;
    margin-bottom: 8px;
}

.rez-pos-field .required {
    color: #17a0b2;
}

.rez-pos-field input {
    width: 100%;
    height: 48px;
    padding: 0 14px;
    border-radius: 12px;
    border: 1px solid #cfd8e3;
    font-size: 15px;
}

/* Table wrapper */
.rez-pos-table-wrapper {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e3e8ee;
    padding: 0;
    overflow: hidden;
}

/* Table */
.rez-pos-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.rez-pos-table thead th {
    background: #e9eff4;
    padding: 14px 12px;
    text-align: left;
    font-weight: 500;
    font-size: 14px;
}

.rez-pos-table tbody td {
    padding: 14px 12px;
    border-top: 1px solid #edf1f5;
}

/* Inputs / selects inside table */
.rez-pos-table select,
.rez-pos-table input[type="text"],
.rez-pos-table input[type="number"] {
    width: 100%;
    height: 44px;
    padding: 0 14px;
    border-radius: 12px;
    border: 1px solid #cfd8e3;
    font-size: 14px;
    background: #fff;
}

.rez-pos-price {
    color: #17a0b2;
    font-weight: 500;
}

/* Quantity input */
.rez-pos-qty {
    text-align: center;
}

/* Add row button */
.rez-pos-add-row {
    width: 100%;
    margin: 16px 0;
    height: 48px;
    border-radius: 12px;
    border: 1px solid #cfd8e3;
    background: #fff;
    font-size: 15px;
    cursor: pointer;
}

/* Total */
.rez-pos-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 24px 0;
    font-size: 20px;
    font-weight: 500;
}

.rez-pos-total strong {
    color: #17a0b2;
}

/* Actions */
.rez-pos-actions {
    display: flex;
    gap: 16px;
}

.rez-pos-cancel {
    flex: 1;
    height: 48px;
    border-radius: 12px;
    border: 1px solid #17a0b2;
    background: #fff;
    color: #17a0b2;
    font-size: 15px;
    cursor: pointer;
}

.rez-pos-charge {
    flex: 1;
    height: 48px;
    border-radius: 12px;
    border: none;
    background: #17a0b2;
    color: #fff;
    font-size: 15px;
    cursor: pointer;
}
</style>
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
  top: 32px; /* Centers the tooltip vertically */
  left: -120px; /* Adjust positioning to the left */
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

div#suggestions {
    width: initial !important;
    padding: initial !important;
    width: auto;
    margin-top: 0 !important;
}

.add_new_user_input {
	display: none;
    margin-bottom: 8px;
}

.amount_input {
    margin-bottom: 8px;
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
	/*opacity: 0;*/
	width: 100%;
	height: 100%;
	border: none;
	border: 1px solid #aaa;
	transform-origin: 0 0;
}
</style>
<?php
include('system-message.php');
?>
<div class="rez-pos-checkout">
	<div class="dashboard-widget events events-page-wrapper" style="margin-bottom: 24px;">
		<a href="/products/" style="color:#17a0b2;">Product List</a>&nbsp;
		<a href="/product-checkout/" class="past_events_a" style="color:#17a0b2;">Product Checkout</a>&nbsp;
		<a href="/products-report/" class="past_events_a" style="color:#17a0b2;">Reports For POS</a>&nbsp;
	</div>
    <!-- Client -->
    <div class="rez-pos-field">
        <label for="rez-pos-client">Client <span class="required">*</span></label>
		<input id="searchInput1" type="text" class="search_facility_input" placeholder="Search user..." oninput="javascript:get_users_ajax(<?=$product_id?>, this.value, 1)" style="display: block;">
		<div id="suggestions" style=""></div>
    </div>
    <!-- Products table -->
    <div class="rez-pos-table-wrapper">
        <table class="rez-pos-table">
            <thead>
                <tr>
                    <th>Player</th>
                    <th>Category</th>
                    <th>Product Title</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <!-- Row -->
                <tr class="rez-pos-product-row">
                    <td id="user_1">
                        <select class="rez-pos-player" id="player_name_1">
                            <option value="">Select Player</option>
                        </select>
                    </td>
                    <td>
						<select class="rez-pos-category">
						    <option value="">Select Category</option>
						
						    <?php foreach ( $categories as $term ) : ?>
						        <option value="<?php echo esc_attr( $term->slug ); ?>">
						            <?php echo esc_html( $term->name ); ?>
						        </option>
						    <?php endforeach; ?>
						</select>
                    </td>
                    <td id="input_1">
                        <select class="rez-pos-product" id="product_id_1">
                            <option value="">Select Product Title</option>
                        </select>
                    </td>
                    <td id="select_1">
                        <input id="amount_1"
                            type="text"
                            class="rez-pos-price"
                            value="$0.00"
                        />
                    </td>
                    <td>
                        <input id="quantity_1"
                            type="number"
                            class="rez-pos-qty"
                            value="0"
                            min="0"
                        />
                    </td>
                    <td>
                        <input id="note_1"
                            type="text"
                            class="rez-pos-notes"
                            placeholder="Internal note"
                        />
                    </td>
                </tr>
            </tbody>
        </table>
		<input type="hidden" id="user_id_1" value="">
		<input type="hidden" id="product_id_1" value="">
        <!-- Add row -->
        <button type="button" class="rez-pos-add-row">
            + Add Another Product
        </button>
    </div>
    <!-- Total -->
    <div class="rez-pos-total">
        <span class="old-total">Total:</span>
        <strong>$00.00</strong>
    </div>
    <!-- Actions -->
    <div class="rez-pos-actions">
        <button type="button" class="rez-pos-cancel">
            Cancel
        </button>
        <button type="button" class="rez-pos-charge" id="charge_1" onclick="charge_added_player(1)">
            Charge
        </button>
    </div>
</div>
<script>
var added_trs = 1;

function add_order_payment_with_email(item_id, product_id)
{
	//if(added_trs)
		//return;
	//added_trs++;
	
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
        <td class="order-events-table-td-0" style="">&nbsp;</td>
        <td class="order-events-table-td-1" style="">&nbsp;</td>
        <td id="user_` + added_trs + `" class="order-events-table-td-2">
			<div class="search_facility">
				<!-- a class="a_search_user" href="javascript:void(0)" onclick="javascript:show_input(this)" style="display:block;">Search User</a -->
				<!-- a class="a_add_new_user" target="_blank" href="/add-user/" style="display:block;">Add New User</a -->
				<input id="searchInput` + added_trs + `" type="text" class="search_facility_input" placeholder="Search User" oninput="javascript:get_users_ajax(` + item_id + `, this.value, ` + added_trs + `)">
				<div id="suggestions" style=""></div>
			</div>	
		</td>
        <td id="input_` + added_trs + `" class="order-events-table-td-3">&nbsp;</td>
        <td id="select_` + added_trs + `" class="order-events-table-td-4" style="">&nbsp;</td>
        <td class="order-events-table-td-5">
            <button onclick="create_payment_link(` + added_trs + `)" id="charge_` + added_trs + `" class="button-action charge-added-user" value="Charge">Charge</button>
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
    	firstOrderRow.insertAdjacentElement('beforeBegin', newRow);
    } else {
        document.getElementById('orders-body').appendChild(newRow);
    }
}
</script>
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
	
	console.log('players');
	console.log(players);
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

function displayEventSuggestions(suggestions, added_trs_ajax) {
    const suggestionsContainer = document.getElementById('suggestions');
    suggestionsContainer.innerHTML = '';
    if (suggestions.length > 0) {
        suggestions.forEach(suggestion => {
			//console.log('added_trs_ajax');
			//console.log(added_trs_ajax);
            const item = document.createElement('div');
            item.className = 'suggestion-item';

            const link = document.createElement('a');
            link.href = '#';

			link.addEventListener('click', function(event) {
				event.preventDefault();
				set_event_input(this, added_trs_ajax, suggestion.ID, suggestion.price);
			});
			
			link.setAttribute('data-post_title', suggestion.post_title);
			
			var a_text =  suggestion.post_title;
			
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

function displaySuggestions(suggestions, added_trs_ajax) {
    const suggestionsContainer = document.getElementById('suggestions');
    suggestionsContainer.innerHTML = '';
    if (suggestions.length > 0) {
        suggestions.forEach(suggestion => {
			//console.log('added_trs_ajax');
			//console.log(added_trs_ajax);
            const item = document.createElement('div');
            item.className = 'suggestion-item';

            const link = document.createElement('a');
            link.href = '#';
			
			//item.setAttribute('data-charge_method', 'charge_added_user');
				
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
				charge_button.setAttribute('onclick', charge_method + '(' + added_trs_ajax + ')');
				charge_button.innerHTML = button_value;
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

function set_event_input(obj, added_trs, ID, price)
{
	console.log('obj');
	console.log(obj);
	document.querySelector('#product_id_' + added_trs).value = ID;
    const suggestionsContainer = document.getElementById('suggestions');
	suggestionsContainer.style.display = 'none';
	document.querySelector('#searchEventsInput' + added_trs).value = obj.dataset.post_title;
	document.querySelector('#amount_' + added_trs).value = price;
	
	//alert(display_name);
}

var user_children = [];

function set_user_input(added_trs, ID, display_name, children)
{
	user_children = children;
	document.querySelector('#user_' + added_trs).innerHTML = '';
    const suggestionsContainer = document.getElementById('suggestions');
	suggestionsContainer.style.display = 'none';
	
	add_player_dynamically(added_trs, display_name, children);
	/*
	var inputs_block = document.querySelector('#input_' + added_trs);
	
	inputs_block.innerHTML = `<input id="searchEventsInput` + added_trs + `" type="text" class="search_facility_input" placeholder="Search Event" oninput="javascript:get_events_ajax(this.value, ` + added_trs + `)">
				<div id="suggestions" style="display:none;"></div>`;	
	
	var select_block = document.querySelector('#select_' + added_trs);
	select_block.innerHTML = '<div class="dollar-input-wrapper"><input class="amount_input" id="amount_' + added_trs + '" type="text" placeholder="Charge amount"></div>';
	*/
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

function add_order_payment_booked(player_id, product_id, user_id, player_name)
{
	/*
	var arrow = document.getElementById('arrow_' + player_id);
	if(arrow.classList.contains('arrow-down'))
		expand_oevent(player_id);
	*/
	var amount_value = '';
	if(booked_seats !== undefined)
		if(booked_seats !== undefined)
			if(price_ranges[booked_seats] !== undefined)
			{
				amount_value = price_ranges[booked_seats];
			}
	
    var input_player_td = document.getElementById('input_player_td_' + player_id);
	if(input_player_td)
	{
		input_player_td.innerHTML = '<input id="amount_player_' + player_id + '" type="text" value="' + amount_value + '">';
	}
	
    var actions_player_td = document.getElementById('actions_player_td_' + player_id);
	if(actions_player_td)//player_name
	{
		actions_player_td.innerHTML = `<button onclick="charge_added_player(` + player_id + `)" id="charge_player_` + player_id + `" class="button-action" value="Charge">Charge</button>
            <!-- button onclick="delete_added_user_player(` + player_id + `)" id="delete_player_` + player_id + `" class="button-action delete-added-user" value="Delete">Cancel</button -->
			<input type="hidden" id="user_player_id_` + player_id + `" value="` + user_id + `">
			<input type="hidden" id="product_player_id_` + player_id + `" value="` + product_id + `">
			<input type="hidden" id="player_name_` + player_id + `" value="` + player_name + `">`;
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
		//console.log('add_player_dynamically');
		//alert(player_id);
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
		/*
        var option_add_new_player = document.createElement("option");
        option_add_new_player.id = `option_${i}`;
        option_add_new_player.value = "Add New Player";
        option_add_new_player.text = "Add New Player";
        select.appendChild(option_add_new_player);
		*/
		var select_td = document.querySelector('#user_' + added_trs);
		select_td.appendChild(select);
		
		select.addEventListener('change', function() {
			//alert(this.value);
			
			//if('Add New Player' == this.value)
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

function create_payment_link(added_user)
{
    const playerEls  = document.querySelectorAll('.player_select');
    const productEls = document.querySelectorAll('.rez-pos-product');
    const priceEls   = document.querySelectorAll('.rez-pos-price');
    const qtyEls     = document.querySelectorAll('.rez-pos-qty');
    const notesEls   = document.querySelectorAll('.rez-pos-notes');

    const userIdEl = document.querySelector('#user_id_1');
    if (!userIdEl) return null;

    const userId = userIdEl.value;

    const productIds    = [];
    const qtys          = [];
    const amounts       = [];
    const attendeeNames = [];
    const notes         = [];

    for (let i = 0; i < productEls.length; i++) {
        var productId = productEls[i].value;
        var qty       = parseInt(qtyEls[i]?.value, 10) || 0;

        if (!productId || qty <= 0) continue;

        var price  = priceEls[i].value.replace('$', '').trim();
        var player = playerEls[i]?.value || '';
        var note   = notesEls[i]?.value || '';

        productIds.push(productId);
        qtys.push(qty);
        amounts.push(`$${price}`);
        attendeeNames.push(player);
        notes.push(note);
    }

	if(typeof userId == 'undefined' || !userId || userId == '0')
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
	
	if(typeof productId == 'undefined' || !productId || productId == '0')
	{
		show_system_message(
							'Error',
							'Please specify a product',
							'error',
							'',
							'',
							'Close',
							''
		);
		
		return;
	}
	
	if(typeof price == 'undefined' || !price || price == '0')
	{
		show_system_message(
							'Error',
							'Please specify the payment Price',
							'error',
							'',
							'',
							'Close',
							''
		);
		
		return;
	}
	
	var product_ids = productIds.join(',')
	
    const params = new URLSearchParams({
        user_id: userId,
        product_id: product_ids,
        qty: qtys.join(','),
        amount: amounts.join(','),
        attendee_name: attendeeNames.join(','),
        notes: notes.join(',')
    });

	var payment_link = `<?=get_site_url()?>/wp-json/custom/v1/create-link/?${params.toString()}`;
	
	console.log('payment_link');
	console.log(payment_link);
	
	var event_name = '';
	
	send_link_ajax(product_ids, userId, payment_link, event_name);
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
						send_link_ajax(response.product_id, response.user_id, response.payment_link, response.event_name);					
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

function charge_added_user(added_user) 
{
	var user_id = document.getElementById('user_id_' + added_user).value;
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
	
	var product_id = document.getElementById('product_id_' + added_user).value;
	console.log('product_id');
	console.log(product_id);
	
	if(!product_id || product_id == '0')
	{
		show_system_message(
							'Error',
							'Please select product',
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
	
	//create_custom_order_ajax(user_id, product_id, charge_amount_value, 0, button_id, 0, '');
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
	
	submitPosCheckout();
	
	/*
	var products = collectPosProductsData();
	
	console.log('products');
	console.log(products);
	*/
	
	//start_checkout_product(user_id, product_id, charge_amount_value, player_name, first_name, last_name, save_player);
}

function collectPosProductsData() {

    const rows = document.querySelectorAll('.rez-pos-product-row');
    const result = [];
	

    rows.forEach((row, index) => {

        const playerSelect  = row.querySelector('.player_select');
        const productSelect = row.querySelector('.rez-pos-product');
        const priceInput    = row.querySelector('.rez-pos-price');
        const qtyInput      = row.querySelector('.rez-pos-qty');
        const noteInput     = row.querySelector('.rez-pos-notes');

        const playerId = playerSelect
            ? playerSelect.dataset.player_id || playerSelect.value
            : null;

        const playerName = playerSelect
            ? playerSelect.value
            : null;

        const productId = productSelect ? productSelect.value : null;

        const price = priceInput
            ? parseFloat(priceInput.value.replace('$', '')) || 0
            : 0;

        const quantity = qtyInput ? parseInt(qtyInput.value, 10) || 0 : 0;

        const note = noteInput ? noteInput.value.trim() : '';

        result.push({
            player: playerName,
            product_id: productId,
            price: price,
            quantity: quantity,
            note: note
        });
    });

    return result;
}

function submitPosCheckout(userId) {

    const products = collectPosProductsData();

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/';
    form.target = 'inner_frame'; // send to iframe

    // flag
    const flag = document.createElement('input');
    flag.type = 'hidden';
    flag.name = 'simulate_checkout_product_action';
    flag.value = '1';
    form.appendChild(flag);
	
    // user_id
	var userId = document.querySelector('#user_id_1').value;
    const user_id = document.createElement('input');
    user_id.type = 'hidden';
    user_id.name = 'user_id';
    user_id.value = userId;
    form.appendChild(user_id);

    // products array
    const dataInput = document.createElement('input');
    dataInput.type = 'hidden';
    dataInput.name = 'products';
	var products_json = JSON.stringify(products);
    dataInput.value = products_json;
    form.appendChild(dataInput);

    document.body.appendChild(form);
    //form.submit();
	
	//show_loader();
	var inner_frame = document.getElementById('inner_frame');
	inner_frame.src = '/?user=' + userId + '&products=' + products_json + '&simulate_checkout_product_action=1&vendor=<?=$current_user_id?>';
}

function start_checkout_product(user_id, product_id, charge_amount_value, player_name, first_name, last_name, save_player)
{
	alert('start_checkout_product');
	return;
	show_loader();
	var inner_frame = document.getElementById('inner_frame');
	inner_frame.src = '/?user=' + user_id + '&product_id=' + product_id + '&simulate_checkout_product=1&vendor=<?=$current_user_id?>&price=' + charge_amount_value + '&player_name=' + player_name + '&first_name[]=' + first_name + '&last_name[]=' + last_name + '&save_player=' + save_player;
}

function charge_added_player(added_player)
{
	alert('charge_added_player');
	//return;
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
	var button_id = 'charge_player_' + added_player;
	waiting(1, 0);//show the "wait please" popup
	
	var user_id = document.getElementById('user_player_id_' + added_player).value;
	var product_id = document.getElementById('product_player_id_' + added_player).value;
	//create_custom_order_ajax(user_id, product_id, charge_amount_player_value, 0, button_id, added_player, player_name);
	//start_checkout(user_id, product_id, charge_amount_player_value, player_name);
}

function refund_cancel(order_id, button_id)
{
	var button = document.getElementById(button_id);
	//alert(button);
	var amount_td = button.parentNode.parentNode.querySelector('.order-events-table-td-3');
	amount_td.innerHTML = amount_td.dataset.html;
}

function refund_order(order_id, item_id, button, attendee_id)
{
	var amount_td = button.parentNode.parentNode.querySelector('.order-events-table-td-3');
	amount_td.dataset.html = amount_td.innerHTML;
	amount_td.innerHTML = '<input id="refund_amount_' + order_id + '" style="display: block;" type="text" value="' + amount_td.dataset.amount + '"><button class="button-action submit_refund" onclick="refund_order_ajax(' + order_id + ', \'' + item_id + '\', \'' + attendee_id + '\')">Ok</button><button class="button-action submit_refund" onclick="refund_cancel(' + order_id + ', \'' + button.id + '\')">Cancel</button>';
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
		
		console.log('response');
		console.log(response);
			
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

function set_attendee_status_ajax(processing_attendee_id)
{
	var post_data = {
			post_id: processing_attendee_id,
			meta_key: 'etn_status_custom',
			meta_value: 'refund',
			security: '<?php echo wp_create_nonce('edit-post-nonce'); ?>',
		};

	show_loader();
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
	
	//show_loader();
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

var get_events_loading = 0;

function get_events_ajax(search, added_trs_ajax)
{
	if(search.length < 3)
		return;
	console.log('get_events_ajax()');

	var post_data = {
			vendor_id: <?=$current_user_id?>,
			search: search,
			added_trs: added_trs_ajax,
			security: '<?php echo wp_create_nonce('get-events-nonce'); ?>',
		};
	
	//show_loader();
	jQuery.ajax({
		type: 'POST',
		dataType: 'html',
		url: '<?=get_stylesheet_directory_uri()?>/wph-get-events-ajax.php',
		data: post_data,
		beforeSend : function () {
			get_events_loading = 1;
		},
		success: function (data)
		{
			get_events_loading = 0;
			if(data)
			{
				var response = JSON.parse(data);
				console.dir('response');
				console.dir(response);
				
				if(response.success)
				{
					if(response.events)
					{
						displayEventSuggestions(response.events, response.added_trs);
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
			get_events_loading = 0;
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
<script>
document.addEventListener('change', function (e) {

    if (!e.target.classList.contains('rez-pos-category')) return;

    const categorySelect = e.target;
    const row = categorySelect.closest('tr');
    if (!row) return;

    const category = categorySelect.value;
    const productSelect = row.querySelector('.rez-pos-product');
    if (!productSelect) return;

    const selectId = productSelect.id;

    // reset
    productSelect.innerHTML = '<option value="">Select Product Title</option>';

    if (!category) return;

    const formData = new FormData();
    formData.append('action', 'rez_get_products_by_category');
    formData.append('category', category);
    formData.append('select_id', selectId);

    fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success || !res.data.products) return;

        if (res.data.select_id !== selectId) return;

		res.data.products.forEach(product => {
		    const opt = document.createElement('option');
		    opt.value = product.id;
		    opt.textContent = product.title;
		    opt.dataset.price = product.price;
		    productSelect.appendChild(opt);
		});
		
		productSelect.addEventListener('change', function () {
		    const selected = this.options[this.selectedIndex];
		    const priceInput = row.querySelector('.rez-pos-price');
		
		    if (selected && selected.dataset.price) {
		        priceInput.value = '$' + selected.dataset.price;
		    }
			else {
		        priceInput.value = '$00.00';
		    }

			
		    const quantityInput = row.querySelector('.rez-pos-qty');
			quantityInput.value = 1;
		    recalcTotal();
		});
    });
});

document.addEventListener('input', function (e) {
    if (!e.target.classList.contains('rez-pos-qty')) return;
    recalcTotal();
});

</script>
<style>
/* Wrapper */
.rez-pos-total {
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid #e6eef5;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

/* Summary block */
.rez-pos-summary {
    display: flex;
    flex-direction: column;
    gap: 14px;
    width: 100%;
}

/* Rows */
.rez-pos-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 16px;
    color: #0f172a;
}

/* Labels */
.rez-pos-summary-row span {
    font-weight: 400;
}

/* Values */
.rez-pos-summary-row strong {
    font-weight: 500;
    color: #1aa0b2; /* teal value color */
}

/* Total row */
.rez-pos-summary-total {
    margin-top: 6px;
    padding-top: 10px;
    border-top: 1px solid #e6eef5;
}

/* Total label */
.rez-pos-summary-total span {
    font-size: 22px;
    font-weight: 600;
    color: #0f172a;
}

/* Total value */
.rez-pos-summary-total strong {
    font-size: 22px;
    font-weight: 700;
    color: #1aa0b2;
}

</style>
<script>
document.addEventListener('click', function (e) {

    if (!e.target.classList.contains('rez-pos-add-row')) return;

    const tableBody = document.querySelector('.rez-pos-table tbody');
    if (!tableBody) return;

    const firstRow = tableBody.querySelector('.rez-pos-product-row');
    if (!firstRow) return;

    const newRow = firstRow.cloneNode(true);

	// reset ONLY product select
	const productSelect = newRow.querySelector('.rez-pos-product');
	if (productSelect) {
	    productSelect.innerHTML = '<option value="">Select Product Title</option>';
	    productSelect.value = '';
	}
	
	// reset price
	const priceInput = newRow.querySelector('.rez-pos-price');
	if (priceInput) {
	    priceInput.value = '$0.00';
	}
	
	// reset quantity
	const qtyInput = newRow.querySelector('.rez-pos-qty');
	if (qtyInput) {
	    qtyInput.value = 0;
	}
	
	// reset notes
	const notesInput = newRow.querySelector('.rez-pos-notes');
	if (notesInput) {
	    notesInput.value = '';
	}

    tableBody.appendChild(newRow);
});

function parsePrice(value) {
    if (!value) return 0;
    return parseFloat(value.replace('$', '')) || 0;
}

function recalcTotal() {
    let subtotal  = 0;
    let itemCount = 0;

    document.querySelectorAll('.rez-pos-product-row').forEach(row => {
        const priceInput = row.querySelector('.rez-pos-price');
        const qtyInput   = row.querySelector('.rez-pos-qty');

        if (!priceInput || !qtyInput) return;

        const price = parsePrice(priceInput.value);
        const qty   = parseInt(qtyInput.value, 10) || 0;

        subtotal  += price * qty;
        itemCount += qty;
    });

    // ---- Fee calculation ----
    let fee = 0;

    //if (subtotal < 300)
    if (0)
	{
        fee = itemCount * 3;
    } else {
        fee = subtotal * 0.01;
    }

    const total = subtotal + fee;

    // ---- Ensure rez-pos-total exists ----
    const totalWrapper = document.querySelector('.rez-pos-total');
    if (!totalWrapper) return;

    // ---- Ensure summary block exists inside rez-pos-total ----
    let summary = totalWrapper.querySelector('.rez-pos-summary');

    if (!summary) {
        summary = document.createElement('div');
        summary.className = 'rez-pos-summary';

        summary.innerHTML = `
            <div class="rez-pos-summary-row">
                <span>Total Quantity:</span>
                <strong class="rez-pos-total-qty">0</strong>
            </div>

            <div class="rez-pos-summary-row">
                <span>Subtotal:</span>
                <strong class="rez-pos-subtotal">$0.00</strong>
            </div>

            <div class="rez-pos-summary-row">
                <span>Service Fee:</span>
                <strong class="rez-pos-fee">$0.00</strong>
            </div>

            <div class="rez-pos-summary-row rez-pos-summary-total">
                <span>Total Price:</span>
                <strong class="rez-pos-total-amount">$0.00</strong>
            </div>
        `;

        // Remove old simple total (if present)
        const oldTotal = totalWrapper.querySelector('.old-total');
        if (oldTotal) {
            totalWrapper.innerHTML = '';
        }

        totalWrapper.appendChild(summary);
    }

    // ---- Update values ----
    const qtyEl      = summary.querySelector('.rez-pos-total-qty');
    const subtotalEl = summary.querySelector('.rez-pos-subtotal');
    const feeEl      = summary.querySelector('.rez-pos-fee');
    const totalEl    = summary.querySelector('.rez-pos-total-amount');

    if (qtyEl) {
        qtyEl.textContent = itemCount;
    }

    if (subtotalEl) {
        subtotalEl.textContent = '$' + subtotal.toFixed(2);
    }

    if (feeEl) {
        feeEl.textContent = '$' + fee.toFixed(2);
    }

    if (totalEl) {
        totalEl.textContent = '$' + total.toFixed(2);
    }
}


</script>
<script>
</script>
<div id="inner_frame_wrap">
	<iframe name="inner_frame" id="inner_frame" style="width:100%l"></iframe>
</div>
