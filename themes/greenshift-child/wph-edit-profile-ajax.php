<?php
ob_start();
/*
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
*/
require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');

add_filter( 'retrieve_password_message', 'custom_retrieve_password_message', 10, 4 );
function custom_retrieve_password_message( $message, $key, $user_login, $user_data ) {
    // Customize the message content here
    $message = "Hello, \n\n";
    $message .= "You were invited to Rez Schedule as a Coach. Please click the following link:\n\n";
    $message .= network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . "\n\n";
    $message .= "Thanks!";
    
    return $message;
}

$log_filename = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph-edit-profile-ajax.txt';

$success = TRUE;

$theme_uri = get_stylesheet_directory_uri();
$GLOBALS['success'] = TRUE;
$GLOBALS['error_message'] = '';
$GLOBALS['user_meta'] = '';
$user_id = 0;
$children_html = '';

//check_ajax_referer('create-account', 'security');

function wph_create_user($email, $first_name, $last_name, $password, $role = 'subscriber')
{
	$existing_user = get_user_by('email', $email);
	vd($existing_user, '$existing_user');
	if($existing_user)
	{
		$GLOBALS['error_message'] = 'User ' . $email . ' already exists';
		$GLOBALS['success'] = FALSE;
	    return FALSE;
		//return $existing_user->ID;
	}
	
	$base_username = sanitize_user(strtolower($first_name . '_' . $last_name));
	$username = $base_username;
	$username_exists = username_exists($username);
	$suffix = 1;
	
	while($username_exists)
	{
	    $username = $base_username . $suffix;
	    $username_exists = username_exists($username);
	    $suffix++;
	}
	
	$user_id = wp_insert_user(array(
	    'user_login' => $username,
	    'user_pass'  => $password,
	    'user_email' => $email,
		'role' => $role,
	));
	
	if (is_wp_error($user_id)) {
		$GLOBALS['error_message'] = $user_id->get_error_message();
		$GLOBALS['success'] = FALSE;
	    return FALSE;
	}
	
	update_user_meta($user_id, 'first_name', $first_name);
	update_user_meta($user_id, 'last_name', $last_name);
	$GLOBALS['error_message'] = 'Account has been created';
    return $user_id;
}

wph_log_data_global($log_filename,
	array(
		'$_REQUEST' => $_REQUEST,
	), TRUE, TRUE
);


if(isset($_REQUEST['user_id']))
{
	$is_new_user = empty($_REQUEST['user_id']);
	$user_id = $_REQUEST['user_id'];
	$email = !empty($_REQUEST['email']) ? $_REQUEST['email'] : '';
	$first_name = !empty($_REQUEST['first_name']) ? $_REQUEST['first_name'] : '';
	$last_name = !empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : '';
	$password = !empty($_REQUEST['password']) ? $_REQUEST['password'] : '';
	$role = !empty($_REQUEST['role']) ? $_REQUEST['role'] : '';
	/*
	vd($user_id, '$user_id');
	vd($email, '$email');
	vd($first_name, '$first_name');
	vd($last_name, '$last_name');
	vd($password, '$password');
	*/
	if(!$user_id && $email && $first_name && $last_name && $password)
		$user_id = wph_create_user($email, $first_name, $last_name, $password, $role);
	
	//var_dump($user_id, '$user_id');
	
	if($user_id)
	{
		if(!empty($_REQUEST['first_name']))
		{
			update_user_meta($user_id, 'first_name', $_REQUEST['first_name']);
			if($is_new_user)
				update_user_meta($user_id, 'billing_first_name', $_REQUEST['first_name']);
		}
		
		if(!empty($_REQUEST['last_name']))
		{
			update_user_meta($user_id, 'last_name', $_REQUEST['last_name']);
			if($is_new_user)
				update_user_meta($user_id, 'billing_last_name', $_REQUEST['last_name']);
		}
		
		if(isset($_REQUEST['phone']))
		{
			update_user_meta($user_id, 'phone', $_REQUEST['phone']);
			if($is_new_user)
				update_user_meta($user_id, 'billing_phone', $_REQUEST['phone']);
		}
		
		if(isset($_REQUEST['email']))
		{
			update_user_meta($user_id, 'email', $_REQUEST['email']);
			if($is_new_user)
				update_user_meta($user_id, 'billing_email', $_REQUEST['email']);
		}
		
		if(isset($_REQUEST['street_address_line_1']))
		{
			update_user_meta($user_id, 'street_address_line_1', $_REQUEST['street_address_line_1']);
			if($is_new_user)
				update_user_meta($user_id, 'billing_address_1', $_REQUEST['street_address_line_1']);
		}
		
		if(isset($_REQUEST['street_address_line_2']))
		{
			update_user_meta($user_id, 'street_address_line_2', $_REQUEST['street_address_line_2']);
			if($is_new_user)
				update_user_meta($user_id, 'billing_address_2', $_REQUEST['street_address_line_2']);
		}
		
		if(isset($_REQUEST['city']))
		{
			update_user_meta($user_id, 'city', $_REQUEST['city']);
			if($is_new_user)
				update_user_meta($user_id, 'billing_city', $_REQUEST['city']);
		}
		
		if(isset($_REQUEST['state']))
		{
			update_user_meta($user_id, 'state', $_REQUEST['state']);
			if($is_new_user)
				update_user_meta($user_id, 'billing_state', $_REQUEST['state']);
		}
		
		if(isset($_REQUEST['zip']))
		{
			update_user_meta($user_id, 'zip', $_REQUEST['zip']);
			if($is_new_user)
				update_user_meta($user_id, 'billing_postcode', $_REQUEST['zip']);
		}
		
		if($is_new_user)
			update_user_meta($user_id, 'billing_country', 'US');
		if($is_new_user)
			update_user_meta($user_id, 'shipping_method', '');
		if($is_new_user)
			update_user_meta($user_id, 'paying_customer', 1);
		
		if(isset($_REQUEST['contact_first_name']))
			update_user_meta($user_id, 'contact_first_name', $_REQUEST['contact_first_name']);
		if(isset($_REQUEST['contact_last_name']))
			update_user_meta($user_id, 'contact_last_name', $_REQUEST['contact_last_name']);
		if(isset($_REQUEST['contact_phone']))
			update_user_meta($user_id, 'contact_phone', $_REQUEST['contact_phone']);
			
		if(isset($_REQUEST['facility_name']))
		{
			update_user_meta($user_id, 'facility_name', $_REQUEST['facility_name']);
			$facility_name_slug = generate_facility_name_slug($_REQUEST['facility_name'], $user_id);
		}
		if(isset($_REQUEST['short_bio']))
			update_user_meta($user_id, 'short_bio', $_REQUEST['short_bio']);
		
		if(isset($_REQUEST['bank_account_name']))
			update_user_meta($user_id, 'bank_account_name', $_REQUEST['bank_account_name']);
		if(isset($_REQUEST['routing_number']))
			update_user_meta($user_id, 'routing_number', $_REQUEST['routing_number']);
		if(isset($_REQUEST['bank_account_number']))
			update_user_meta($user_id, 'bank_account_number', $_REQUEST['bank_account_number']);
		if(isset($_REQUEST['billing_address']))
			update_user_meta($user_id, 'billing_address', $_REQUEST['billing_address']);
		if(isset($_REQUEST['terms_agree_parent']))
			update_user_meta($user_id, 'terms_agree_parent', $_REQUEST['terms_agree_parent']);
		if(isset($_REQUEST['terms_agree_coach']))
			update_user_meta($user_id, 'terms_agree_coach', $_REQUEST['terms_agree_coach']);
		if(isset($_REQUEST['i_am_a_player']))
			update_user_meta($user_id, 'i_am_a_player', $_REQUEST['i_am_a_player']);
		if(isset($_REQUEST['gender']))
			update_user_meta($user_id, 'gender', $_REQUEST['gender']);
		if(isset($_REQUEST['sports']))
			update_user_meta($user_id, 'sports', $_REQUEST['sports']);
		
		//vd($_REQUEST['sports'], '$sports');
		
		if(!empty($_REQUEST['parent_facility']))
		{
			update_user_meta($user_id, 'parent_facility', $_REQUEST['parent_facility']);
			$user = get_user_by( 'ID', $user_id );
			//if(retrieve_password( $user->user_login ) === TRUE)
			if(simulate_password_reset_request($user->user_email) === TRUE)
				$GLOBALS['error_message'] .= '<br>Invite has been sent';
		}
		
		if(isset($_REQUEST['date_of_birth_user']))
		{
			update_user_meta($user_id, 'date_of_birth_user', $_REQUEST['date_of_birth_user']);
			$metas = convert_date_string_to_user_meta($_REQUEST['date_of_birth_user']);
			if($metas)
			{
				foreach($metas as $key => $name)
					update_user_meta($user_id, $key, $name);
			}
		}
		
		if(isset($_REQUEST['children']))
		{
			foreach($_REQUEST['children'] as &$child)
			{
				$post_data = array(
				);
				
				$post_data['ID'] = $child['id'];
				$post_data['post_status'] = 'publish';
				$post_data['post_type'] = 'child';
				
				if(!empty($child['delete_child']))
				{
					wp_delete_post($child['delete_child']);
				}
				elseif(!empty($child['first_name']) && !empty($child['last_name']))
				{
					$post_data['post_title'] = sanitize_text_field(trim($child['first_name'] . ' ' . $child['last_name']));
					
					$post_content = '';
					
					if(isset($child['date_of_birth']))
						$post_content .= $child['date_of_birth'] . "\r\n";
					if(isset($child['sports']))
						$post_content .= $child['sports'] . "\r\n";
					if(isset($child['current_team']))
						$post_content .= $child['current_team'] . "\r\n";
					if(isset($child['level']))
						$post_content .= $child['level'] . "\r\n";
					if(isset($child['gender']))
						$post_content .= $child['gender'] . "\r\n";
					
					$post_data['post_content'] = wp_kses_post($post_content);
					
					if($child['id'])
					{
						$update_post = wp_update_post($post_data);
						//if(!$update_post)
							//$success = FALSE;
					}
					else
					{
						$child['id'] = wp_insert_post($post_data);
						//if(!$child['id'])
							//$success = FALSE;
					}
					
					if($child['id'])
					{
						update_post_meta($child['id'], 'first_name', $child['first_name']);
						update_post_meta($child['id'], 'last_name', $child['last_name']);
						update_post_meta($child['id'], 'date_of_birth', $child['date_of_birth']);
						update_post_meta($child['id'], 'sports', $child['sports']);
						update_post_meta($child['id'], 'current_team', $child['current_team']);
						update_post_meta($child['id'], 'level', $child['level']);
						update_post_meta($child['id'], 'gender', $child['gender']);
					}
				}
			}
			
			$update = update_user_meta($user_id, 'children', $_REQUEST['children']);
			ob_start();
			include('edit-profile-parent-children.php');
			$children_html = ob_get_contents();
			vd($update, '$update');
		}
		
		vd($_REQUEST['children'], '$_REQUEST[\'children\']');
		
		if($password && !empty($_REQUEST['new_password']))
		{
			$current_user = get_user_by('ID', $user_id);
			
			if(wp_check_password($password, $current_user->user_pass, $current_user->ID))
			{
			    $wp_set_password = wp_set_password($_REQUEST['new_password'], $user_id);
				$GLOBALS['error_message'] .= 'Password has been reset';
			}
			else
			{
			    $GLOBALS['error_message'] .=  'Invalid password.';
			}
		}
		
		if(!empty($_REQUEST['delete_parent_facility']))
		{
			if(delete_user_meta($user_id, 'parent_facility', $_REQUEST['delete_parent_facility']))
				$GLOBALS['error_message'] = '<br>The user has been removed';
			
			if(wp_delete_user($user_id))
				$GLOBALS['error_message'] = 'The user has been deleted';
		}
	}
}
/*
wph_log_data_global($log_filename,
	array(
		'$services' => $_REQUEST['services'],
		'$servise_names' => $servise_names,
		//'$_REQUEST' => $_REQUEST,
	), TRUE, FALSE
);
*/
/*
if(isset($_REQUEST['email']))
{
	$GLOBALS['success'] = TRUE;
	
	wph_log_data_global($log_filename,
		array(
			'$_REQUEST' => $_REQUEST,
		), TRUE, FALSE
	);
   //$GLOBALS['success'] = FALSE;
}
$user_email
*/
$CONTENT = ob_get_contents();
$response = array('success' => $success, 'error_message' => $GLOBALS['error_message'], 'user_id' => $user_id, 'children_html' => $children_html, 'CONTENT' => $CONTENT);
/*
wph_log_data_global($log_filename,
	array(
		'$response' => $response,
	), TRUE, FALSE
);
*/
ob_end_clean();

echo json_encode($response);
?>