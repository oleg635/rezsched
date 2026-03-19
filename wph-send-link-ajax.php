<?php
ob_start();

require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
$success = TRUE;
$post_id = 0;
$message = '';

function create_stripe_link_post($event_name, $user_name, $user_id, $product_id, $payment_link) {
    // Generate the post title (post_name)
    $post_title = $event_name . ' for ' . $user_name;

    // Create the post
    $post_id = wp_insert_post(array(
        'post_title'    => $post_title,
        'post_content'  => '', // Temporarily empty, will update later
        'post_status'   => 'publish',
        'post_type'     => 'stripe_link',
    ));
	
    if ($post_id) {
        // Generate final payment link
        $final_payment_link = $payment_link . '&link_id=' . $post_id;
		
        // Update post content with the final payment link
        wp_update_post(array(
            'ID'           => $post_id,
            'post_content' => $final_payment_link
        ));
		
        // Update meta fields
        update_post_meta($post_id, 'user_id', $user_id);
        update_post_meta($post_id, 'product_id', $product_id);
    }
	
    return $final_payment_link;
}

$product_id = !empty($_REQUEST['product_id']) ? $_REQUEST['product_id'] : 0;
$user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
$payment_link = isset($_REQUEST['payment_link']) ? $_REQUEST['payment_link'] : '';
$event_name = !empty($_REQUEST['event_name']) ? $_REQUEST['event_name'] : '';
$link_id = '';

if(!$event_name && $product_id)
{
	$event_names = [];
	
	if(strpos($product_id, ',') !== FALSE)
		$product_ids = explode(',', $product_id);
	else
		$product_ids = [$product_id];
	
	foreach($product_ids as $id)
	{
		$product = wc_get_product($id);
	    if($product)
		{
   		    $product_name = $product->get_name();
			if($product_name)
				$event_names[] = $product_name;
		}
	}
	
	$event_name = join(',', $event_names);
}

wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph-send-link-ajax.txt',
	array(
		'$_REQUEST' => $_REQUEST,
	), TRUE, TRUE
);

if($user_id > 0 && !empty($payment_link) && !empty($event_name))
{
    check_ajax_referer('send-link-nonce', 'security');
	
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph-send-link-ajax.txt',
		array(
			'$link_id' => $link_id,
			'$user_id' => $user_id,
			'$payment_link' => $payment_link,
			'$event_name' => $event_name,
		), TRUE, FALSE
	);
	
    // Retrieve the user's email
    $user = get_user_by('ID', $user_id);
    if ($user && isset($user->user_email)) {
		
		if(strpos($payment_link, 'stripe.com') === FALSE)
			$payment_link = create_stripe_link_post($event_name, $user->display_name, $user->ID, $product_id, $payment_link);
		
        $to = $user->user_email;
		
	    $vendor_id = get_post_field('post_author', $product_id);
		//$product = $product_id(get_post($product_id));
		
        $subject = 'Payment Link For ' . $event_name;
        $message = $payment_link;
		$reply_to = 'support@rezsched.com';
		
		if($vendor_id)
		{
			$user = get_user_by('ID', $vendor_id);
			if($user)
			{
				$reply_to = $user->user_email;
				
		        $subject = $user->display_name . ' Is Requesting Access';
		        //$message = 'By clicking on the link below you are allowing ' . $user->display_name . ' to access your profile through the Rez Sched software. <br>' . $user->display_name . ' will now be able to add you to upcoming events upon your request.' . '<br><br>' . $payment_link;
		        $message = 'By clicking on the link below, you can complete your requested registration and authorize ' . $user->display_name . ' to access your profile through the Rez Sched software. <br>This will allow ' . $user->display_name . ' to add you to upcoming events upon your request.' . '<br><br>' . $payment_link;
			}
        }
		
        // Send the email
		$headers = [
		    'Content-Type: text/html; charset=UTF-8',
		    'From: Rez Sched Notification <support@rezsched.com>',
			'Reply-To: ' . $reply_to
		];
		
        $email_sent = wp_mail($to, $subject, $message, $headers);
		
        if($email_sent)
		{
            $message = 'Email sent successfully.';
			
			$event_category = 0;
			$categories = get_the_terms($product_id, 'etn_category');
			if(!empty($categories[0]))
				$event_category = $categories[0]->term_id;
			
			if($event_category == 31)//lesson
			{
				global $wpdb;
				
				$parsed_url = parse_url($payment_link);
				parse_str($parsed_url['query'], $query_params);
				
				$player_name = isset($query_params['attendee_name']) ? $query_params['attendee_name'] : '';
				$link_id = isset($query_params['link_id']) ? $query_params['link_id'] : '';
				
				wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph-send-link-ajax.txt',
					array(
						'$link_id' => $link_id,
						'$user_id' => $user_id,
						'$player_name' => $player_name,
						'$payment_link' => $payment_link,
						'$event_name' => $event_name,
					), TRUE, FALSE
				);
				
				if($link_id)
				{
					$book_data_entries = $wpdb->get_results( 
					    $wpdb->prepare(
					        "SELECT meta_id, meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", 
					        $product_id, 
					        'book_data'
					    ), 
					    ARRAY_A
					);
					
					// Loop through each book_data entry to find the one with the matching user_id
					foreach ($book_data_entries as $book_data_entry) {
					    $meta_value = maybe_unserialize($book_data_entry['meta_value']); // Unserialize the meta_value
					    $meta_id = $book_data_entry['meta_id']; // Get the meta ID
					
					    // Check if the user_id matches
					    if ($meta_value['user_id'] == $user_id) {
					        // If players exist, update the 'charged' field for the specific player
					        if (!empty($meta_value['players'])) {
					            foreach ($meta_value['players'] as &$player) {
					                if ($player['player'] == $player_name) {
					                    $player['link_id'] = $link_id;
					                }
					            }
					        }
							
					        // Update the meta entry with the modified value using the meta_id
					        $updated_meta_value = maybe_serialize($meta_value); // Serialize the modified meta_value
					        $wpdb->update(
					            $wpdb->postmeta,
					            array('meta_value' => $updated_meta_value),
					            array('meta_id' => $meta_id)
					        );
					        
					        break; // Exit the loop after updating the correct entry
					    }
					}
				}
			}
        }
		else
		{
            $message = 'Failed to send the email.';
			$success = FALSE;
        }
    } else {
        $message = 'User not found or does not have a valid email.';
		$success = FALSE;
    }
} else {
	$message = 'Required parameters are missing.';
	$success = FALSE;
}

vd($to, '$to');

$CONTENT = ob_get_contents();
$response = array('success' => $success, 'link_id' => $link_id, 'user_id' => $user_id, 'payment_link' => $payment_link, 'event_name' => $event_name, 'message' => $message, 'CONTENT' => $CONTENT);
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