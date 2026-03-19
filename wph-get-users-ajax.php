<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

//echo 'wph-get-users-ajax.php';
//return;

ob_start();

require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
$success = TRUE;
$post_id = 0;
$message = '';

$states = array(
    'AL' => 'Alabama',
    'AK' => 'Alaska',
    'AZ' => 'Arizona',
    'AR' => 'Arkansas',
    'CA' => 'California',
    'CO' => 'Colorado',
    'CT' => 'Connecticut',
    'DE' => 'Delaware',
    'FL' => 'Florida',
    'GA' => 'Georgia',
    'HI' => 'Hawaii',
    'ID' => 'Idaho',
    'IL' => 'Illinois',
    'IN' => 'Indiana',
    'IA' => 'Iowa',
    'KS' => 'Kansas',
    'KY' => 'Kentucky',
    'LA' => 'Louisiana',
    'ME' => 'Maine',
    'MD' => 'Maryland',
    'MA' => 'Massachusetts',
    'MI' => 'Michigan',
    'MN' => 'Minnesota',
    'MS' => 'Mississippi',
    'MO' => 'Missouri',
    'MT' => 'Montana',
    'NE' => 'Nebraska',
    'NV' => 'Nevada',
    'NH' => 'New Hampshire',
    'NJ' => 'New Jersey',
    'NM' => 'New Mexico',
    'NY' => 'New York',
    'NC' => 'North Carolina',
    'ND' => 'North Dakota',
    'OH' => 'Ohio',
    'OK' => 'Oklahoma',
    'OR' => 'Oregon',
    'PA' => 'Pennsylvania',
    'RI' => 'Rhode Island',
    'SC' => 'South Carolina',
    'SD' => 'South Dakota',
    'TN' => 'Tennessee',
    'TX' => 'Texas',
    'UT' => 'Utah',
    'VT' => 'Vermont',
    'VA' => 'Virginia',
    'WA' => 'Washington',
    'WV' => 'West Virginia',
    'WI' => 'Wisconsin',
    'WY' => 'Wyoming',
	
	'AB' => 'Alberta',
	'BC' => 'British Columbia',
	'MB' => 'Manitoba',
	'NB' => 'New Brunswick',
	'NL' => 'Newfoundland and Labrador',
	'NS' => 'Nova Scotia',
	'ON' => 'Ontario',
	'PE' => 'Prince Edward Island',
	'QC' => 'Quebec',
	'SK' => 'Saskatchewan',
);

$log_filename = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph-get-users-ajax.txt';

if(1)
{
    //check_ajax_referer('get-users-nonce', 'security');
	$role = !empty($_REQUEST['role']) ? $_REQUEST['role'] : '';
	$order_id = !empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '';
	$vendor_id = !empty($_REQUEST['vendor_id']) ? $_REQUEST['vendor_id'] : 0;
	
    $search = isset($_REQUEST['search']) ? sanitize_text_field($_REQUEST['search']) : '';
	
    $args = array(
        'role' => $role,
        'search' => '*' . esc_attr($search) . '*',
        'search_columns' => array('user_login', 'user_nicename', 'user_email', 'display_name'),
    );
	
    $get_users = get_users($args);
	
    // Prepare the response
    $users = array();
    foreach ($get_users as $user) {
		$children = get_user_children($user->ID);
		
		$user_data = array(
            'ID' => $user->ID,
            'display_name' => $user->display_name,
            'children' => $children,
			'orders_count' => 0,
			'booked_a_lesson' => 0,
			'placed_orders' => 0,
			'card_on_file' => 0,
        );
		
		if($vendor_id)
		{
			$user_data['placed_orders'] = $user_data['orders_count'] = has_orders($vendor_id, $user->ID);
			$user_data['booked_a_lesson'] = booked_a_lesson($vendor_id, $user->ID);
			
			if(!$user_data['orders_count'])
				if($user_data['booked_a_lesson'])
					$user_data['orders_count'] = 1;
		}
		
		$saved_methods = wc_get_customer_saved_methods_list($user->ID);
		if($saved_methods)
			$user_data['card_on_file'] = count($saved_methods);
		
		if(!$saved_methods)
			$user_data['orders_count'] = 0;
		
		$city = get_user_meta($user->ID, 'city', TRUE);
		$state = get_user_meta($user->ID, 'state', TRUE);
		
		if(isset($states[$state]))
			$state = $states[$state];
		
		$user_data['city'] = $city;
		$user_data['state'] = $state;
		
        $users[] = $user_data;
    }
}
else
{
	$success = FALSE;
}
/*
wph_log_data_global($log_filename,
	array(
		'AFTER ALL' => TRUE,
	), TRUE, FALSE
);
*/
$CONTENT = ob_get_contents();
$response = array('success' => $success, 'item_id' => $item_id, 'added_trs' => $_REQUEST['added_trs'], 'users' => $users, 'message' => $message, 'CONTENT' => $CONTENT);
ob_end_clean();
/*
wph_log_data_global($log_filename,
	array(
		'AFTER ob_get_contents' => TRUE,
	), TRUE, FALSE
);
*/
echo json_encode($response);
?>