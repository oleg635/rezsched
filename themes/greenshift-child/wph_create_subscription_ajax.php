<?php
/*
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
*/

//echo 'wph_create_subscription_ajax.php';
//return;

ob_start();

require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
$success = TRUE;

$log_filename = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph_create_subscription_ajax.txt';

//function create_subscription_product($name, $regular_price, $description = '', $status = 'publish', $billing_period = 'month', $interval = 1, $length = 12, $category_id = null) {
function create_subscription_product($name, $regular_price, $description = '', $status = 'publish', $billing_period = 'month', $interval = 1, $length = 12, $category_id = null, $stock_quantity = -1) {
    // Create a new product object
    $product = new WC_Product_Subscription();

    // Set the product title
    $product->set_name($name);

    // Set the product status (e.g., 'publish', 'draft')
    $product->set_status($status);

    // Set the product's regular price (this will be the subscription price)
    $product->set_regular_price($regular_price);

    if ($stock_quantity >= 0) {
        $product->set_stock_quantity($stock_quantity);
        $product->set_manage_stock(true); // Enable stock management for the product
    }
	
    // Set the product's description (content)
    $product->set_description($description);

    // Save the product to get its ID
    $product_id = $product->save();

    // Set the subscription-specific meta fields
    update_post_meta($product_id, '_subscription_price', $regular_price); // Subscription price
    update_post_meta($product_id, '_subscription_period', $billing_period); // Billing period ('day', 'week', 'month', 'year')
    update_post_meta($product_id, '_subscription_period_interval', $interval); // Billing period interval (e.g., every 1 month)
    update_post_meta($product_id, '_subscription_length', $length); // Subscription length (e.g., 12 months)

    // Optionally, assign the product to a category
    if ($category_id) {
        wp_set_object_terms($product_id, intval($category_id), 'product_cat');
    }
	
    //echo 'Subscription Product Created with ID: ' . $product_id;
	return $product_id;
}

var_dump($_POST, '$_POST');
if(!empty($_POST['bodyData']['post_title']))
{
	wph_log_data_global($log_filename,
		array(
			'$_REQUEST[\'bodyData\'][\'etn_category\'][0]' => $_REQUEST['bodyData']['etn_category'][0],
			'$_POST' => $_POST,
		), TRUE, FALSE
	);
	
    check_ajax_referer('edit-post-nonce', 'security');
/*
	wph_log_data_global($log_filename,
		array(
			'AFTER check_ajax_referer' => TRUE,
		), TRUE, FALSE
	);
*/
	
	if(!empty($_REQUEST['bodyData']['etn_category'][0]))
		$etn_category = $_REQUEST['bodyData']['etn_category'][0];
	else
		$etn_category = 0;
	
	if(isset($_REQUEST['bodyData']['etn_ticket_variations'][0]['etn_avaiilable_tickets']))
		$stock_quantity = $_REQUEST['bodyData']['etn_ticket_variations'][0]['etn_avaiilable_tickets'];
	else
		$stock_quantity = -1;
	
	if(isset($_POST['event_price']))
		$price = $_POST['event_price'];
	else
		$price = -1;
	
	if(!empty($_REQUEST['bodyData']['etn_location']))
	{
		$etn_location = $_REQUEST['bodyData']['etn_location'];
		$term = get_term($_REQUEST['bodyData']['etn_location'][0]);
		if($term)
			$etn_event_location = $term->name;
	}
	else
	{
		$etn_location = '';
		$etn_event_location = '';
	}
	
	// Prepare the post data
	$post_data = array(
	);
	
	$do_update = 0;
	
	if(!empty($_REQUEST['bodyData']['event_id']))
	{
	    $post_id = $_REQUEST['bodyData']['event_id'];
		$post_data['ID'] = $_REQUEST['bodyData']['event_id'];
	}
	/*
	if(!empty($_REQUEST['post_status']))
		$post_data['post_status'] = $_REQUEST['post_status'];
	else
		$post_data['post_status'] = 'publish';
	*/
	if(isset($_REQUEST['bodyData']['post_title']))
		$post_data['post_title'] = sanitize_text_field($_REQUEST['bodyData']['post_title']);
	
	if(isset($_REQUEST['bodyData']['post_content']))
		$post_data['post_content'] = wp_kses_post($_REQUEST['bodyData']['post_content']);
	
	if($post_id)
	{
		$update_post = wp_update_post($post_data);
		if(!$update_post)
			$success = FALSE;
		
		if(isset($price) && $price >= 0)
		{
		    $product = wc_get_product($post_id);
			
			$product->set_regular_price($price);
			update_post_meta($post_id, '_subscription_price', $price);
			$product->save();
			
			wph_log_data_global($log_filename,
				array(
					'$post_id' => $post_id,
					'$price' => $price,
				), TRUE, FALSE
			);
		}
		
		if(isset($stock_quantity) && $stock_quantity >= 0)
		{
		    $product = wc_get_product($post_id);
			
		    if($product)
			{
		        $product->set_stock_quantity($stock_quantity);
				
		        if (!$product->managing_stock()) {
		            $product->set_manage_stock(true);
		        }
				
		        $product->save();
			}
		}
	}
	else
	{
		$post_id = create_subscription_product(
		    $_POST['bodyData']['post_title'],	// Product name
		    $_POST['event_price'],	// Regular price
		    $_POST['bodyData']['post_content'],	// Description
		    'publish',				// Status
		    'month',				// Billing period
		    1,						// Interval (every 1 month)
		    12,						// Subscription length (12 months)
		    $etn_category,			// Category ID (optional)
		    $stock_quantity
		);
		if(!$post_id)
			$success = FALSE;
	}
	
	wph_log_data_global($log_filename,
		array(
			'$_POST[\'bodyData\'][\'post_title\']' => $_POST['bodyData']['post_title'],
			'$_POST[\'event_price\']' => $_POST['event_price'],
			'$etn_category' => $etn_category,
			'$etn_location' => $etn_location,
			'$etn_event_location' => $etn_event_location,
		), TRUE, FALSE
	);
	
	//$post = get_post($_POST['post_id']);
	//if($post)
	if($post_id)
	{
		//if($bulk_posts)
		{
			//foreach($bulk_posts as $post_id)
			{
				if(isset($_REQUEST['featured_image']))
				{
					if($_REQUEST['featured_image'])
						set_post_thumbnail($post_id, $_REQUEST['featured_image']);
					else
						delete_post_thumbnail($post_id);
				}
				
				vd($_REQUEST['bodyData']['etn_tags'][0], '$_REQUEST[\'bodyData\'][\'etn_tags\'][0]');
				if(!empty($_REQUEST['bodyData']['etn_tags'][0]))
				{
					$etn_tags = wp_set_post_terms($post_id, $_REQUEST['bodyData']['etn_tags'][0], 'etn_tags');
					vd($etn_tags, '$etn_tags');
				}
				vd($_REQUEST['bodyData']['etn_category'][0], '$_REQUEST[\'bodyData\'][\'etn_category\'][0]');
				if(!empty($_REQUEST['bodyData']['etn_category'][0]))
				{
					$res = wp_set_post_terms($post_id, $_REQUEST['bodyData']['etn_category'][0], 'etn_category');
					vd($res, '$res');
				}
				vd($_REQUEST['bodyData']['etn_location'], '$_REQUEST[\'bodyData\'][\'etn_location\']');
				if(!empty($_REQUEST['bodyData']['etn_location'][0]))
				{
					wp_set_post_terms($post_id, $_REQUEST['bodyData']['etn_location'][0], 'etn_location');
				}
				
				if($etn_location)
				{
					update_post_meta($post_id, 'etn_location', $etn_location);
				}
				
				if($etn_event_location)
				{
					update_post_meta($post_id, 'etn_event_location', $etn_event_location);
				}
				
				if(isset($_REQUEST['age_group']))
					update_post_meta($post_id, 'age_group', $_REQUEST['age_group']);
				
				if(isset($_REQUEST['level']))
				{
					//echo ' post_id ' . $post_id;
					//echo ' level ' . $_REQUEST['level'];
					update_post_meta($post_id, 'level', $_REQUEST['level']);
				}
				
				if(isset($_REQUEST['bodyData']['etn_ticket_variations']))
				{
					update_post_meta($post_id, 'etn_ticket_variations', $_REQUEST['bodyData']['etn_ticket_variations']);
					if(isset($_REQUEST['bodyData']['etn_ticket_variations'][0]['etn_avaiilable_tickets']))
						update_post_meta($post_id, 'etn_total_avaiilable_tickets', $_REQUEST['bodyData']['etn_ticket_variations'][0]['etn_avaiilable_tickets']);
				}
				
				if(isset($_REQUEST['bodyData']['etn_start_date']))
					update_post_meta($post_id, 'etn_start_date', $_REQUEST['bodyData']['etn_start_date']);
				
				if(isset($_REQUEST['bodyData']['etn_end_date']))
					update_post_meta($post_id, 'etn_end_date', $_REQUEST['bodyData']['etn_end_date']);
				
				if(isset($_REQUEST['bodyData']['etn_start_time']))
					update_post_meta($post_id, 'etn_start_time', $_REQUEST['bodyData']['etn_start_time']);
				
				if(isset($_REQUEST['bodyData']['etn_end_time']))
					update_post_meta($post_id, 'etn_end_time', $_REQUEST['bodyData']['etn_end_time']);
				
				if(isset($_REQUEST['waiver']))
					update_post_meta($post_id, 'waiver', $_REQUEST['waiver']);
				
				if(isset($_REQUEST['checkbox_public_view']))
					update_post_meta($post_id, 'checkbox_public_view', $_REQUEST['checkbox_public_view']);
				
				if(isset($_REQUEST['tshirt_size']))
					update_post_meta($post_id, 'tshirt_size', $_REQUEST['tshirt_size']);
				
				if(isset($_REQUEST['jersey_size']))
					update_post_meta($post_id, 'jersey_size', $_REQUEST['jersey_size']);
				
				if(isset($_REQUEST['shorts_size']))
					update_post_meta($post_id, 'shorts_size', $_REQUEST['shorts_size']);
				
				if(isset($_REQUEST['pants_size']))
					update_post_meta($post_id, 'pants_size', $_REQUEST['pants_size']);
				
				if(isset($_REQUEST['room_area']))
					update_post_meta($post_id, 'room_area', $_REQUEST['room_area']);
				
				if(isset($_REQUEST['street_address_line_1']))
					update_post_meta($post_id, 'street_address_line_1', $_REQUEST['street_address_line_1']);
				
				if(isset($_REQUEST['street_address_line_2']))
					update_post_meta($post_id, 'street_address_line_2', $_REQUEST['street_address_line_2']);
				
				if(isset($_REQUEST['city']))
					update_post_meta($post_id, 'city', $_REQUEST['city']);
				
				if(isset($_REQUEST['state']))
					update_post_meta($post_id, 'state', $_REQUEST['state']);
				
				if(isset($_REQUEST['zip']))
					update_post_meta($post_id, 'zip', $_REQUEST['zip']);
				
				if(!empty($_REQUEST['bodyData']['etn_event_recurrence']['recurrence_weekly_day']))
				{
					$recurrence_weekly_day = $_REQUEST['bodyData']['etn_event_recurrence']['recurrence_weekly_day'];
					update_post_meta($post_id, 'recurrence_weekly_day', $recurrence_weekly_day);
				}
			}
		}
	}
	
	$success = TRUE;
}
/*
wph_log_data_global($log_filename,
	array(
		'AFTER ALL' => TRUE,
	), TRUE, FALSE
);
*/
$CONTENT = ob_get_contents();
$response = array('success' => $success, '$_REQUEST' => $_REQUEST, 'CONTENT' => $CONTENT);
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