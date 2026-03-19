<?php
$current_user_id = get_current_user_id();
if(!$current_user_id)
	return;

$user_roles = get_current_user_role();

if(!in_array('administrator', $user_roles) && !in_array('um_custom_role_1', $user_roles) && !in_array('seller', $user_roles))
{
	return;
}

include('system-message.php');

if($_SERVER['REMOTE_ADDR'] == '188.163.75.165' || $_SERVER['REMOTE_ADDR'] == '92.60.179.128')
{
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
}

    $atts = shortcode_atts(array(
		'users_count' => 20,
		'show_title' => FALSE,
    ), $atts);
	
	$posts = [];
	//if($all_user_events)
	if(1)
	{
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		$offset = ($paged - 1) * $atts['users_count'];
		
		$args = array(
		    'meta_key'     => 'parent_facility',
		    'meta_value'   => $current_user_id,
		    'number'       => -1,   // Limit the number of results
		    'fields'       => 'all'     // You can use 'all', 'ids', or other fields as needed
		);
		
		$users = get_users( $args );
		
		$users = get_users($args);
		$total_users = $users ? count($users) : 0;
		
		$args['number'] = $atts['users_count'];
		$args['offset'] = $offset;
		$users = get_users($args);
		
		//vd($users, '$users');
	}
	
	//$posts_count = $posts ? count($posts) : 0;
	$users_count = $users ? count($users) : 0;
	
	//vd($orders_count, '$orders_count');
	//vd(get_current_page_url(), 'get_current_page_url()');
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

<div class="dashboard-widget attendees events events-table-wrapper attendees-table-wrapper">
<?php
	if($atts['show_title'])
	{
?>
	<div class="widget-title"> Users</div>
<?php
	}
?>
	<style>
	.event_datetime {
		color:#6a7682;
	}
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
	</style>
	<script>
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
		var links = a.parentNode.querySelectorAll('a');
		for(var i = 0; i < links.length; i++)
		{
			links[i].style.display = 'none';
		}
	}
	
	var added_trs = 0;
	
	function add_user()
	{
		added_trs++;
		console.log('added_trs');
		console.log(added_trs);
		/*
		var arrow = document.getElementById('arrow_' + item_id);
		if(arrow.classList.contains('arrow-down'))
			expand_oevent(item_id);
		*/
	    const newRow = document.createElement('tr');
	    newRow.id = 'added_user_' + added_trs;
	    newRow.className = 'order_child';
	    newRow.style.display = 'table-row';
	    newRow.innerHTML = `
	        <!-- td class="order-events-table-td-1">&nbsp;</td -->
	        <td id="user_` + added_trs + `" class="order-events-table-td-3">
				<input id="user_name_first` + added_trs + `" type="text">
			</td>
	        <td id="user_` + added_trs + `" class="order-events-table-td-4">
				<input id="user_name_last` + added_trs + `" type="text">
			</td>
	        <td id="user_` + added_trs + `" class="order-events-table-td-5">
				<input id="user_email` + added_trs + `" type="text">
			</td>
	        <td class="order-events-table-td-6">
	            <button onclick="charge_added_user(` + added_trs + `)" id="charge_` + added_trs + `" class="button-action" value="Charge">Invite</button>
	            <button onclick="delete_added_user(` + added_trs + `)" id="delete_` + added_trs + `" class="button-action delete-added-user" value="Delete">Cancel</button>
				<input type="hidden" id="user_id_` + added_trs + `" value="">
	        </td>
	    `;
		
		var selector = '.user_parent';
	    const rows = document.querySelectorAll(selector);
		
	    if (rows.length > 0) {
		    const firstOrderRow = rows[0];
	    	firstOrderRow.insertAdjacentElement('beforebegin', newRow);
	    } else {
	        document.getElementById('orders-body').appendChild(newRow);
	    }
	}
	
	function splitString(str) {
	    // Trim any extra whitespace and split the string by spaces
	    const words = str.trim().split(/\s+/);
	
	    // Check if there are two or more words
	    if (words.length >= 2) {
	        const first_name = words[0]; // The first word
	        const last_name = words.slice(1).join(' '); // The rest of the string
	        return { first_name, last_name };
	    } else {
	        return false;
	    }
	}	
		
	function charge_added_user(added_user)
	{
		var user_name_first = document.getElementById('user_name_first' + added_user);
		if(user_name_first)
		{
			var user_name_first_value = user_name_first.value;
			if(!user_name_first_value)
			{
				show_system_message(
									'Error',
									'Please insert First Name',
									'error',
									'',
									'',
									'Close',
									''
				);
				
				return;
			}
		}
		
		var user_name_last = document.getElementById('user_name_last' + added_user);
		if(user_name_last)
		{
			var user_name_last_value = user_name_last.value;
			if(!user_name_last_value)
			{
				show_system_message(
									'Error',
									'Please insert Last Name',
									'error',
									'',
									'',
									'Close',
									''
				);
				
				return;
			}
		}
		
		var user_email = document.getElementById('user_email' + added_user);
		if(user_email)
		{
			var user_email_value = user_email.value;
			if(!user_email_value)
			{
				show_system_message(
									'Error',
									'Please insert the User Email',
									'error',
									'',
									'',
									'Close',
									''
				);
				
				return;
			}
		}
		
		//console.dir('split_name');
		//console.dir(split_name);
		//return;
		
		//var button_id = 'charge_' + added_user;
		waiting(1, 0);//show the "wait please" popup
		
		add_user_ajax(0, user_name_first_value, user_name_last_value, user_email_value, '<?=$current_user_id?>', 0);
		//start_checkout(user_id, product_id, charge_amount_value, '');
	}
	
	//remove a dynamically added row
	function delete_added_user(added_user)
	{
		var added_user_tr = document.getElementById('added_user_' + added_user);
		if(added_user_tr)
		{
			added_user_tr.remove();
		}
	}
	
	function remove_facility(user_id)
	{
		waiting(1, 0);//show the "wait please" popup
		add_user_ajax(user_id, '', '', '', 0, '<?=$current_user_id?>');
	}
	
	function resend_invite(user_id)
	{
		waiting(1, 0);//show the "wait please" popup
		add_user_ajax(user_id, '', '', '', '<?=$current_user_id?>', 0);
	}
	
	var add_user_loading = 0;
	
	function add_user_ajax(user_id, first_name, last_name, email, parent_facility, delete_parent_facility)
	{
		var post_data = {
				user_id: user_id,
				first_name: first_name,
				last_name: last_name,
				email: email,
				role: 'um_coach',
				password: 'bsdfrSz18COZn12020',
				parent_facility: parent_facility,
				delete_parent_facility: delete_parent_facility,
			};
		
		jQuery.ajax({
			type: 'POST',
			dataType: 'html',
			url: '<?=get_stylesheet_directory_uri()?>/wph-edit-profile-ajax.php',
			data: post_data,
			beforeSend : function () {
				add_user_loading = 1;
			},
			success: function (data)
			{
				add_user_loading = 0;
				if(data)
				{
					var response = JSON.parse(data);
					console.dir('response');
					console.dir(response);
					
					waiting(0, 0);//hide the "wait please" popup				
					
					if(response.success)
					{
						//var message = 'User ID ' + response.user_id + ' has been added';
						var message = '';
						if(response.error_message)
							message += response.error_message;
							//message += '<br>' + response.error_message;
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
						if(response.error_message)
							show_system_message(
												'Error', 
												response.error_message, 
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
				add_user_loading = 0;
				console.log(jqXHR);
			},
		});
		
		return false;
	}
	</script>
	<div class="etn_search_shortcode etn_search_wrapper">
		<a href="/users/" style="color:#17a0b2">Coaches</a>
		<span style="color:#17a0b2;margin: 0 8px;">|</span>
		<a href="/clients/" style="color:#8593a3">Clients</a>
	</div>
	<div class="attendees-box">
	<div class="order-events-actions" style="float: right;"><button onclick="javascript:add_user()" class="button-add">Add User</button></div>
	<table class="events-table attendees-table"><!--  -->
		<thead>
		<tr>
			<!--th class="attendees-table-th-1" scope="col"><input id="attendees_toggle_all" type="checkbox"></th -->
			<!--th class="attendees-table-th-2" scope="col">User ID</th -->
			<th class="attendees-table-th-3" scope="col">First Name</th>
			<th class="attendees-table-th-3" scope="col">Last Name</th>
			<th class="attendees-table-th-4" scope="col">Email</th>
			<th class="attendees-table-th-5" scope="col">Actions</th>
		</tr>
		</thead>
		<tbody id="orders-body">
<?php
	foreach($users as $user)
	{
		$first_name = get_user_meta($user->ID, 'first_name', TRUE);
		$last_name = get_user_meta($user->ID, 'last_name', TRUE);
		
		$_um_last_login = get_user_meta($user->ID, '_um_last_login', TRUE);
		vd($_um_last_login, '$_um_last_login');
		if(!$first_name || !$last_name)
		{
			$sb = strpos($user->display_name, '_') !== 0 ? '_' : ' ';
			$names = explode($sb, $user->display_name);
			//vd($names, '$names');
			if($names && isset($names[0]) && isset($names[1]))
			{
				$first_name = $names[0];
				$last_name = $names[1];
			}
		}
		//$tooltip = isset($tooltips[$display_status]) ? $tooltips[$display_status] : '';
?>
		<tr class="user_parent user_<?=$user->ID?>" id="user_parent_<?=$user->ID?>">
			<!--td class="order-events-table-td-1"><input id="cb-select-<?=$user->ID?>" class="attendees-table-checkbox" type="checkbox" name="post[]" value="<?=$user->ID?>"></td -->
			<!--td class="order-events-table-td-2"><a href="/edit-profile/<?=$user->ID?>/"><?=$user->ID?></a></td -->
			<td class="order-events-table-td-3" title="User ID <?=$user->ID?>"><a href="/edit-profile/<?=$user->ID?>/"><?=$first_name?></a></td>
			<td class="order-events-table-td-4" title="User ID <?=$user->ID?>"><a href="/edit-profile/<?=$user->ID?>/"><?=$last_name?></a></td>
			<td class="order-events-table-td-5" title="User ID <?=$user->ID?>"><?=$user->user_email?></td>
			<td class="order-events-table-td-6"><?php if(!$_um_last_login){ ?><button onclick="resend_invite(<?=$user->ID?>)" class="button-action" value="Resend Invite">Resend Invite</button><?php } ?><button class="button-action delete-added-user" onclick="remove_facility(<?=$user->ID?>);" value="Delete">Delete</button></td>
		</tr>
<?php
	}
?>
		</tbody>
	</table>
</div>
</div>
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
<?php
	//if($atts['show_filters'])
	{
		/*
	    $args = [
			'status' => ['wc-completed', 'wc-processing', 'wc-on-hold'],
	        'limit'  => -1,
			//'status' => array('completed', 'processing', 'on-hold'),
	        'meta_query' => [
	            [
	                'key'     => '_dokan_vendor_id',
	                'value'   => $current_user_id,
	                'compare' => '=',
	            ],
	        ],
	    ];
		
		$orders = wc_get_orders($args);
		$total_users = $orders ? count($orders) : 0;
		*/
        $total_pages = ceil($total_users / $atts['users_count']);
		//vd($total_users, '$total_users');
		//vd($total_pages, '$total_pages');
		
        if ($total_users > $atts['users_count']) {
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
	}
?>