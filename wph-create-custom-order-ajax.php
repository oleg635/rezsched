<?php
//if($_SERVER['REMOTE_ADDR'] == '92.60.179.128')
if(0)
{
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
}


require_once($_SERVER['DOCUMENT_ROOT'] . '/stripe/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
\Stripe\Stripe::setApiKey(Stripe_Secret_Key);

date_default_timezone_set('America/New_York');

ob_start();

$GLOBALS['success'] = $success = TRUE;
$post_id = 0;
$GLOBALS['message'] = $message = '';
$GLOBALS['order_id'] = $GLOBALS['order_id'] = $GLOBALS['attendee_id'] = 0;

$button_id = isset($_REQUEST['button_id']) ? $_REQUEST['button_id'] : '';

$log_filename = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph-create-custom-order-ajax.txt';

function refund_order($order_id, $amount = 0, $reason = 'Customer request') {

	if($_SERVER['REMOTE_ADDR'] !== '92.60.179.128')
	{
		$GLOBALS['success'] = false;
		$GLOBALS['message'] = 'The Refund function is<br>under construction';
		return TRUE;
	}
	
	$order = wc_get_order($order_id);
	$GLOBALS['order_id'] = $order_id;
	if(!$amount)
	{
		if($_SERVER['REMOTE_ADDR'] == '188.163.72.156' || $_SERVER['REMOTE_ADDR'] == '92.60.179.128')
		{
			$amount = floatval($order->get_total());
		}
		else
		{
			$amount = floatval($order->get_subtotal());
		}
	}
	
	if(1)
	{
		$refund_data = [
		    'order_id'      => $order_id,          // WooCommerce order ID
		    'refund_amount' => $amount,     // Refund amount
		    'refund_reason' => $reason,     // Reason for refund
			'item_qtys'       => array(),
			'item_totals'     => $line_items,
			'item_tax_totals' => array(),
			'restock_items'   => array(),
		    'method'        => 'dokan-stripe-connect',
		];
		
		// Create refund object
		$refund_obj = new \WeDevs\DokanPro\Refund\Refund( $refund_data );
		
		$approve = $refund_obj->approve();
		wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/refund_order.txt',
			array(
				'$approve' => $approve,
			), TRUE, FALSE
		);
	}
	else
	{
		try {
        $arr = [
            'amount'         => $amount,
            'reason'         => $reason,
            'order_id'       => $order_id,
            'line_items'     => [],
            'refund_payment' => false,
            'restock_items'  => 1,
        ];

        /*
         * First, Create the refund object for order or suborder depending on condition.
         * If it is sub order, Only a refund record will be created.
         * No request will be sent to the payment processor.
         */
        $wc_refund = wc_create_refund( $arr );
			
		    if (is_wp_error($wc_refund)) {
			
				wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/refund_order.txt',
					array(
						'$wc_refund' => $wc_refund,
					), TRUE, FALSE
				);
				
				$GLOBALS['success'] = FALSE;
				$GLOBALS['message'] = 'wc_create_refund() failed...';
				
				$error_code = array_key_first( $wc_refund->errors );
				$GLOBALS['message'] .= '<br>' . $wc_refund->errors[$error_code][0];
				
		        return FALSE;
		    }
			
		    $total_refunded = $order->get_total_refunded();
		    //$order_total = $order->get_total();
		    $order_total = $order->get_subtotal();
		    $remaining_amount = $order_total - $total_refunded;
			
		    if ($remaining_amount <= 0)
		        $order->update_status('refunded', $reason);
			else
		        $order->update_status('partial-refund', $reason);
			
			//$GLOBALS['message'] = 'Refunded amount: $' . $amount;
			$GLOBALS['message'] = 'The order has been refunded';
			wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/refund_order.txt',
				array(
					'$total_refunded' => $total_refunded,
					'$order_total' => $order_total,
					'$remaining_amount' => $remaining_amount,
					'$wc_refund' => $wc_refund,
				), TRUE, FALSE
			);
			
		    return TRUE;
		} catch (\Stripe\Exception\ApiErrorException $e) {
			$GLOBALS['success'] = FALSE;
			$GLOBALS['message'] = 'Stripe API error: ' . $e->getMessage();
	        return FALSE;
		}
	}
	
	
	$GLOBALS['success'] = false;
	$GLOBALS['message'] = 'Done';
	return TRUE;
	
		//$amount = floatval($order->get_total());
	/*
	$transaction_id = $order->get_transaction_id();
	if(!$transaction_id)
	{
		if ($charge_id) {
		    $order->set_transaction_id($charge_id);
		    $order->save();
			
		    echo "Transaction ID updated to: " . $charge_id;
		}
	}
	*/
	
	
	//$get_payment_method = $order->get_payment_method();
	
	//$payment_gateway = wc_get_payment_gateway_by_order($order);
	
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/refund_order.txt',
		array(
			'$order_id' => $order_id,
			'$amount' => $amount,
	        'transaction_id' => $transaction_id,
	        'get_payment_method' => $get_payment_method,
			'$payment_gateway' => $payment_gateway,
			'$order' => $order,
			'$_REQUEST' => $_REQUEST,
		), TRUE, FALSE
	);
	



/*
$payment_gateways = WC()->payment_gateways->payment_gateways();
$payment_gateway = $payment_gateways['dokan-stripe-connect']; // Ensure this matches the slug of the payment gateway

if ( ! $payment_gateway ) {
				$GLOBALS['success'] = FALSE;
				$GLOBALS['message'] = 'Dokan Stripe Connect Gateway not found.';
		        return false;
}


try {
    // Try processing the refund using the payment gateway's refund process
    $refund_result = $payment_gateway->process_refund( $order->get_id(), $amount, $reason );
    
    if ( $refund_result ) {
				$GLOBALS['success'] = TRUE;
				$GLOBALS['message'] = 'Refund successful for Order ID: ' . $order->get_id();
        return true;
    } else {
				$GLOBALS['success'] = FALSE;
				$GLOBALS['message'] = 'Refund failed for Order ID: ' . $order->get_id();
        return false;
    }

} catch (Exception $e) {
				$GLOBALS['success'] = FALSE;
				$GLOBALS['message'] = 'Error processing refund: ' . $e->getMessage();
    return false;
}
*/

	$vendor_id = $order->get_meta('_dokan_vendor_id');
	$charge_id_meta_key = '_dokan_stripe_charge_id_' . $vendor_id;
	$charge_id = $order->get_meta($charge_id_meta_key);
	
	if($charge_id)
	{
		try {
			$stripe = new \Stripe\StripeClient(Stripe_Secret_Key);
		    $refund = $stripe->refunds->create([
		        'charge' => $charge_id,
		        'amount' => $amount,
		        'reason' => $reason,
		    ]);
			
			wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/refund_order.txt',
				array(
					'$vendor_id' => $vendor_id,
					'$charge_id_meta_key' => $charge_id_meta_key,
					'$charge_id' => $charge_id,
					'$stripe' => $stripe,
					'$refund' => $refund,
				), TRUE, FALSE
			);
			
			
		    if ($refund) {
			    $total_refunded = $order->get_total_refunded();
			    //$order_total = $order->get_total();
			    $order_total = $order->get_subtotal();
			    $remaining_amount = $order_total - $total_refunded;
				
			    if ($remaining_amount <= 0)
			        $order->update_status('refunded', $reason);
				else
			        $order->update_status('partial-refund', $reason);
				
				//$GLOBALS['message'] = 'Refunded amount: $' . $amount;
				$GLOBALS['message'] = 'The order has been refunded';
		        return true;
		    } else {
				$GLOBALS['success'] = FALSE;
				$GLOBALS['message'] = 'Refund failed, handle accordingly';
		        return false;
		    }
		
		} catch (Exception $e) {
			$GLOBALS['success'] = FALSE;
			$GLOBALS['message'] = 'Stripe Refund Error: ' . $e->getMessage();
		    return false;
		}
	}
	else
	{
		try {
		    $wc_refund = wc_create_refund([
		        'order_id' => $order_id,
		        'amount' => $amount,
		        'reason' => $reason,
		        'refund_payment' => true,
		        'line_items' => array(),
		    ]);
			
		    if (is_wp_error($wc_refund)) {
				$GLOBALS['success'] = FALSE;
				$GLOBALS['message'] = 'wc_create_refund() failed...';
				
				$error_code = array_key_first( $wc_refund->errors );
				$GLOBALS['message'] .= '<br>' . $wc_refund->errors[$error_code][0];
				
		        return FALSE;
		    }
			
		    $total_refunded = $order->get_total_refunded();
		    //$order_total = $order->get_total();
		    $order_total = $order->get_subtotal();
		    $remaining_amount = $order_total - $total_refunded;
			
		    if ($remaining_amount <= 0)
		        $order->update_status('refunded', $reason);
			else
		        $order->update_status('partial-refund', $reason);
			
			//$GLOBALS['message'] = 'Refunded amount: $' . $amount;
			$GLOBALS['message'] = 'The order has been refunded';
			
		    return TRUE;
		} catch (\Stripe\Exception\ApiErrorException $e) {
			$GLOBALS['success'] = FALSE;
			$GLOBALS['message'] = 'Stripe API error: ' . $e->getMessage();
	        return FALSE;
		}
	}
	
	$GLOBALS['message'] = 'refund_order() ';
	return FALSE;
}

function calculate_dokan_stripe_fee($order_amount, $vendor_id) {
    // Ensure the Dokan Stripe module is available
    if ( ! class_exists( 'WeDevs\DokanPro\Modules\Stripe\Helper' ) ) {
        throw new Exception('Dokan Stripe module is not active.');
    }

	
    // Get an instance of the Stripe Helper
    $stripe_helper = WeDevs\DokanPro\Modules\Stripe\Helper::instance();
	return 0;
 //   $gateway_fee = $stripe_helper->calculate_gateway_fee($order_amount, $vendor_id);

    return $gateway_fee;
}

function create_custom_order_old($user_id, $product_id, $amount = 0.50, $attendee_name = '') {
    if (!class_exists('WC_Payment_Tokens') || !class_exists('WC_Order')) {
        $GLOBALS['success'] = FALSE;
        $GLOBALS['message'] = 'Required WooCommerce classes are not available.';
        return FALSE;
    }
	
    $payment_tokens = WC_Payment_Tokens::get_tokens(['user_id' => $user_id]);
    if (empty($payment_tokens)) {
        $GLOBALS['success'] = FALSE;
        $GLOBALS['message'] = 'No saved payment methods for this user.';
        return FALSE;
    }
	
    $payment_token = reset($payment_tokens);
	$order = wc_create_order([
        'customer_id' => $user_id,
        'status' => 'pending',
    ]);
	
    $order->set_payment_method($payment_token->get_gateway_id());
    $order->set_payment_method_title($payment_token->get_gateway_id());
    
    $product = wc_get_product($product_id);
    if (!$product) {
        $GLOBALS['success'] = FALSE;
        $GLOBALS['message'] = 'Invalid product ID.';
        return FALSE;
    }
	
    $product->set_price($amount);
    $order->add_product($product, 1);
	
	if($_SERVER['REMOTE_ADDR'] == '188.163.72.156' || $_SERVER['REMOTE_ADDR'] == '92.60.179.128')
	{
    	$imported_total_fee = $commission_fee = 0;
		//$imported_total_fee = $commission_fee = 0.01;
	}
	else
	{
	    if($amount < 300)
		{
			$imported_total_fee = $commission_fee = 3;
		}
		else
		{
		    $percentage = 0.01;
	    	//$imported_total_fee = $commission_fee = $amount * $percentage;
	    	$imported_total_fee = $commission_fee = 0;
		}
	}
	
	//vd($imported_total_fee, '$imported_total_fee');
/*
$country_code = $order->get_shipping_country();

$calculate_tax_for = array(
    'country' => $country_code, 
    'state' => '', 
    'postcode' => '', 
    'city' => ''
);
*/
// Get a new instance of the WC_Order_Item_Fee Object
	
	if($imported_total_fee)
	{
		$item_fee = new WC_Order_Item_Fee();
		
		$item_fee->set_name( "REZ Fee" ); // Generic fee name
		$item_fee->set_amount( $imported_total_fee ); // Fee amount
		$item_fee->set_tax_class( '' ); // default for ''
		$item_fee->set_tax_status( 'none' ); // or 'none'
		$item_fee->set_total( $imported_total_fee ); // Fee amount
		
		// Calculating Fee taxes
		//$item_fee->calculate_taxes( $calculate_tax_for );
		
		// Add Fee item to the order
		$order->add_item( $item_fee );
	}
	
    // Calculate the order totals
    $order->calculate_totals();
    $GLOBALS['order_id'] = $order_id = $order->get_id();
	
    $order->set_payment_method($payment_token->get_gateway_id());
    $order->set_payment_method_title($payment_token->get_gateway_id());
    $order->save();
	
	//if($_SERVER['REMOTE_ADDR'] == '188.163.72.156' || $_SERVER['REMOTE_ADDR'] == '92.60.179.128')
	if(1)
	{
		/*
		wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_token.txt',
			array(
				'$payment_token' => $payment_token,
				'$payment_intent' => $payment_intent,
			), TRUE, FALSE
		);
		*/
		/*
		wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_token.txt',
			array(
				'$payment_token' => $payment_token,
			), TRUE, FALSE
		);
		*/
		$gateway_id = $payment_token->get_gateway_id();
		/*
		wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/gateway_id.txt',
			array(
				'$gateway_id' => $gateway_id,
			), TRUE, FALSE
		);
		*/
		$payment_method_id = $payment_token->get_token();
		/*
		wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_method_id.txt',
			array(
				'$payment_method_id' => $payment_method_id,
			), TRUE, FALSE
		);
		*/
		$payment_method = \Stripe\PaymentMethod::retrieve($payment_method_id);
		
		//echo $payment_method->customer;
		/*
		wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_method.txt',
			array(
				'$payment_method' => $payment_method,
			), TRUE, FALSE
		);
		*/
		if(!empty($payment_method->customer))
			$customer_id = $payment_method->customer;
		else
		    $customer_id = get_user_meta($user_id, 'dokan_stripe_customer_id', true);
		
		//$customer_id = $payment_token->get_meta('dokan_stripe_customer_id');
	    //$customer_id = get_user_meta($user_id, 'wp__stripe_customer_id', true);
	    //$customer_id = get_user_meta($user_id, 'dokan_stripe_customer_id', true);
	}
	else
	{
	    $customer_id = get_user_meta($user_id, 'dokan_stripe_customer_id', true);
	}
	
    if (!$customer_id) {
        $GLOBALS['message'] = 'No Stripe customer ID found for this user.';
        return FALSE;
    }
	
    try {
        $payment_intent = \Stripe\PaymentIntent::create([
            'amount' => ($amount + $commission_fee) * 100, // Amount in cents
            'currency' => 'usd', // Replace with your currency
            'customer' => $customer_id, // Use customer ID
            'payment_method' => $payment_token->get_token(),
            'off_session' => true,
            'confirm' => true,
            'description' => 'Charge for order ' . $order->get_id(),
            'metadata' => ['order_id' => $order->get_id()],
        ]);
		
        if ($payment_intent->status === 'requires_action' && $payment_intent->next_action->type === 'use_stripe_sdk') {
            throw new Exception('Payment requires additional actions.');
        } elseif ($payment_intent->status !== 'succeeded') {
            throw new Exception('Payment failed or not completed.');
        }
		/*
		wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
			array(
				'AFTER $payment_intent->create' => TRUE,
				'$payment_intent' => $payment_intent,
			), TRUE, FALSE
		);
		*/
        $latest_charge_id = $payment_intent->latest_charge;
		/*
		wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
			array(
				'AFTER $payment_intent->latest_charge' => TRUE,
				'$latest_charge_id' => $latest_charge_id,
			), TRUE, FALSE
		);
		*/
        $event_post = get_post($product_id);
        if (!$event_post) {
            $GLOBALS['success'] = FALSE;
            $GLOBALS['message'] = 'Invalid event ID.';
            return FALSE;
        }
		/*
		wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
			array(
				'AFTER get_post($product_id)' => TRUE,
				'$event_post' => $event_post,
			), TRUE, FALSE
		);
		*/
        $vendor_id = $event_post->post_author;
		
        if ($latest_charge_id) {
            $charge = \Stripe\Charge::retrieve($latest_charge_id);
			
            $order->update_meta_data('_stripe_intent_id', $payment_intent->id);
            $order->update_meta_data('_stripe_currency', $payment_intent->currency);
            $order->update_meta_data('_stripe_customer_id', $payment_intent->customer);
            $order->update_meta_data('_stripe_source_id', $payment_token->get_token());
            $order->update_meta_data('_stripe_upe_payment_type', 'card');
            $order->update_meta_data('_stripe_charge_captured', $charge->captured ? 'yes' : 'no');
            $order->update_meta_data('_stripe_fee', $charge->balance_transaction->fee / 100);
            $order->update_meta_data('_stripe_net', $charge->balance_transaction->net / 100);
			
            $order->payment_complete($latest_charge_id);
            $order->save();
        } else {
            throw new Exception('No latest charge found for the payment intent.');
        }
    } catch (\Stripe\Exception\ApiErrorException $e) {
        $GLOBALS['message'] = 'Exception: ' . $e->getMessage();
        return FALSE;
    }
	/*
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
		array(
			'AFTER retrieve' => TRUE,
			'$charge' => $charge,
		), TRUE, FALSE
	);
	*/
    $res = $order->update_meta_data('_dokan_vendor_id', $vendor_id);
	/*
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
		array(
			'AFTER $order->update_meta_data()' => TRUE,
			'$res' => $res,
		), TRUE, FALSE
	);
	*/
	/*
    do_action('woocommerce_checkout_update_order_meta', $order_id);
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
		array(
			'AFTER woocommerce_checkout_update_order_meta' => TRUE,
		), TRUE, FALSE
	);
	*/
    do_action('woocommerce_order_status_pending', $order_id);
	/*
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
		array(
			'AFTER woocommerce_order_status_pending' => TRUE,
		), TRUE, FALSE
	);
	*/
    do_action('woocommerce_payment_complete', $order_id);
	/*
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
		array(
			'AFTER woocommerce_payment_complete' => TRUE,
		), TRUE, FALSE
	);
	*/
	
    $res = $order->set_status('completed');
	/*
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
		array(
			'AFTER $order->set_status(completed)' => TRUE,
			'$res' => $res,
		), TRUE, FALSE
	);
	*/
    //$order->set_payment_method('stripe');
    //$order->set_payment_method_title('stripe');
    $order->set_payment_method('dokan-stripe-connect');
	/*
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
		array(
			'AFTER $order->set_payment_method()' => TRUE,
			'$res' => $res,
		), TRUE, FALSE
	);
	*/
    $order->set_payment_method_title('dokan-stripe-connect');
	/*
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
		array(
			'AFTER $order->set_payment_method_title()' => TRUE,
			'$res' => $res,
		), TRUE, FALSE
	);
	*/
    $res = $order->save();
	/*
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
		array(
			'AFTER $order->save()' => TRUE,
			'$res' => $res,
		), TRUE, FALSE
	);
	*/
    do_action('dokan_order_created', $order_id, $vendor_id);
	
    do_action('dokan_checkout_update_order_meta', $order->get_id(), [
        'vendor_id' => $vendor_id,
        'commission' => $commission_fee,
    ]);
	
    $order_items = $order->get_items();
    foreach ($order_items as $item_id => $item) {
        wc_update_order_item_meta($item_id, '_product_id', $product_id);
    }
	
    $user_info = get_userdata($user_id);
	if(!$attendee_name)
		$attendee_name = $user_info->display_name;
	
    $attendee_post = [
        'post_title'   => $attendee_name,
        'post_type'    => 'etn-attendee',
        'post_status'  => 'publish',
        'post_author'  => $user_id,
    ];
	
    $attendee_id = wp_insert_post($attendee_post);
	
    if (is_wp_error($attendee_id)) {
        $GLOBALS['success'] = FALSE;
        $GLOBALS['message'] = 'wp_insert_post failed.';
        return FALSE;
        return $attendee_id;
    }
	
    update_post_meta($attendee_id, 'etn_attendee_order_id', $order_id);
    update_post_meta($attendee_id, 'etn_event_id', $product_id);
    update_post_meta($attendee_id, 'etn_status', 'success');
    update_post_meta($attendee_id, 'etn_ticket_price', $amount);
	
    $GLOBALS['attendee_id'] = $attendee_id;
    return $order_id;
}

function simulate_checkout($user_id, $product_id, $amount)
{
    // Load required classes
    if (!class_exists('WC_Order')) {
        $GLOBALS['success'] = FALSE;
        $GLOBALS['message'] = '!class_exists';
        return FALSE;
    }
    $currency = 'usd';
    if (!class_exists('WC_Payment_Tokens') || !class_exists('WC_Order')) {
        $GLOBALS['success'] = FALSE;
        $GLOBALS['message'] = 'Required WooCommerce classes are not available.';
        return FALSE;
    }
	
    $payment_tokens = WC_Payment_Tokens::get_tokens(['user_id' => $user_id]);
    if (empty($payment_tokens)) {
        $GLOBALS['success'] = FALSE;
        $GLOBALS['message'] = 'No saved payment methods for this user.';
        return FALSE;
    }
	
    $payment_token = reset($payment_tokens);

    // Create a new order
    $order = wc_create_order();
	
	$customer_id = get_user_meta($user_id, 'dokan_stripe_customer_id', true);
    
    // Set customer details
    $order->set_customer_id($user_id);
    
	$order->add_product(wc_get_product($product_id), 1);
    
    // Calculate totals
    $order->calculate_totals();

    $vendor_id = get_post_field('post_author', $product_id);
	$dokan_connected_vendor_id = get_user_meta($vendor_id, 'dokan_connected_vendor_id', TRUE);
	$commission_fee = 3;
	
    // Create Stripe Payment Intent if needed
    $payment_method = 'pm_card_visa'; // Example payment method
    $payment_intent = \Stripe\PaymentIntent::create([
        'amount' => $amount * 100, // Amount in cents
        'currency' => $currency,
        'customer' => $customer_id,
        'payment_method' => $payment_token->get_token(),
        'off_session' => true,
        'confirm' => true,
        'description' => 'simulate checkout ' . $order->get_id(),
        'metadata' => ['order_id' => $order->get_id()],
        'application_fee_amount' => $commission_fee * 100,
        'transfer_data' => [
            'destination' => $dokan_connected_vendor_id, // Vendor's connected account
        ],
    ]);
	
    // Check if the payment was successful
    if ($payment_intent->status === 'succeeded') {
        // Update order meta
        $order->update_meta_data('_stripe_charge_id', $payment_intent->id);
        $order->payment_complete($payment_intent->id);
        $order->save();
        
        return $order; // Return the order object
    } else {
        // Handle payment failure
        $order->update_status('failed', 'Payment failed.');
        return false; // Indicate failure
    }
}

function simulate_full_checkout($user_id, $product_ids, $amount, $customer_email, $payment_method_id) {
    // Step 1: Set up the cart
    WC()->session->set_customer_id($user_id); // Set the current user ID
    WC()->session->set('customer', array(
        'email' => $customer_email,
    ));

    // Empty the cart first
    WC()->cart->empty_cart();
    
    // Add products to the cart
    foreach ($product_ids as $product_id) {
        WC()->cart->add_to_cart($product_id, 1); // Add 1 of each product
    }

    // Step 2: Create an order
    $order = wc_create_order();
    $order->set_customer_id($user_id);
    $order->set_billing_email($customer_email);

    // Add cart items to the order
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $product_id = $cart_item['product_id'];
        $quantity = $cart_item['quantity'];
        $order->add_product(wc_get_product($product_id), $quantity);
    }

    // Calculate totals
    $order->calculate_totals();
    
    // Step 3: Create Stripe Payment Intent
    try {
        $payment_intent = \Stripe\PaymentIntent::create([
            'amount' => $amount * 100, // Amount in cents
            'currency' => 'usd',
            'payment_method' => $payment_method_id,
            'confirm' => true,
            'description' => 'Charge for order ' . $order->get_id(),
            'metadata' => ['order_id' => $order->get_id()],
        ]);

        // Step 4: Handle the response
        if ($payment_intent->status === 'succeeded') {
            // Successful payment
            $order->payment_complete($payment_intent->id);
            $order->update_meta_data('_stripe_charge_id', $payment_intent->id);
            $order->save();

            return $order; // Return the order object
        } else {
            // Payment not successful
            $order->update_status('failed', 'Payment failed.');
            return false; // Indicate failure
        }
    } catch (Exception $e) {
        // Handle exceptions
        $order->update_status('failed', 'Payment exception: ' . $e->getMessage());
        return false; // Indicate failure
    }
}

function create_custom_order($user_id, $product_id, $amount = 0.50, $attendee_name = '')
{
	$product_ids = [$product_id];
	
	return simulate_full_checkout($user_id, $product_ids, $amount, $customer_email, $payment_method_id);
	
	$event_post = get_post($product_id);
	if (!$event_post) {
	    $GLOBALS['success'] = FALSE;
	    $GLOBALS['message'] = 'Invalid event ID.';
	    return FALSE;
	}
	
    if (!class_exists('WC_Payment_Tokens') || !class_exists('WC_Order')) {
        $GLOBALS['success'] = FALSE;
        $GLOBALS['message'] = 'Required WooCommerce classes are not available.';
        return FALSE;
    }
	
    $payment_tokens = WC_Payment_Tokens::get_tokens(['user_id' => $user_id]);
    if (empty($payment_tokens)) {
        $GLOBALS['success'] = FALSE;
        $GLOBALS['message'] = 'No saved payment methods for this user.';
        return FALSE;
    }
	
    $payment_token = reset($payment_tokens);
	$order = wc_create_order([
        'customer_id' => $user_id,
        'status' => 'pending',
    ]);
	
    $order->set_payment_method($payment_token->get_gateway_id());
    $order->set_payment_method_title($payment_token->get_gateway_id());
    
    $product = wc_get_product($product_id);
    if (!$product) {
        $GLOBALS['success'] = FALSE;
        $GLOBALS['message'] = 'Invalid product ID.';
        return FALSE;
    }
	
    $product->set_price($amount);
    $order->add_product($product, 1);
	
	//if($_SERVER['REMOTE_ADDR'] == '188.163.72.156' || $_SERVER['REMOTE_ADDR'] == '92.60.179.128')
	if(0)
	{
    	$imported_total_fee = $commission_fee = 0;
		//$imported_total_fee = $commission_fee = 0.01;
	}
	else
	{
	    if($amount < 300)
		{
			$imported_total_fee = $commission_fee = 3;
		}
		else
		{
		    $percentage = 0.01;
	    	$imported_total_fee = $commission_fee = $amount * $percentage;
	    	//$imported_total_fee = $commission_fee = 0;
		}
	}
	
	//vd($imported_total_fee, '$imported_total_fee');
/*
$country_code = $order->get_shipping_country();

$calculate_tax_for = array(
    'country' => $country_code, 
    'state' => '', 
    'postcode' => '', 
    'city' => ''
);
*/
// Get a new instance of the WC_Order_Item_Fee Object
	
	if($imported_total_fee)
	{
		$item_fee = new WC_Order_Item_Fee();
		
		$item_fee->set_name( "REZ Fee" ); // Generic fee name
		$item_fee->set_amount( $imported_total_fee ); // Fee amount
		$item_fee->set_tax_class( '' ); // default for ''
		$item_fee->set_tax_status( 'none' ); // or 'none'
		$item_fee->set_total( $imported_total_fee ); // Fee amount
		
		// Calculating Fee taxes
		//$item_fee->calculate_taxes( $calculate_tax_for );
		
		// Add Fee item to the order
		$order->add_item( $item_fee );
	}
	
    // Calculate the order totals
    $order->calculate_totals();
    $GLOBALS['order_id'] = $order_id = $order->get_id();
	
    $order->set_payment_method($payment_token->get_gateway_id());
    $order->set_payment_method_title($payment_token->get_gateway_id());
    $order->save();
	
	//if($_SERVER['REMOTE_ADDR'] == '188.163.72.156' || $_SERVER['REMOTE_ADDR'] == '92.60.179.128')
	if(1)
	{
		/*
		wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_token.txt',
			array(
				'$payment_token' => $payment_token,
				'$payment_intent' => $payment_intent,
			), TRUE, FALSE
		);
		*/
		/*
		wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_token.txt',
			array(
				'$payment_token' => $payment_token,
			), TRUE, FALSE
		);
		*/
		$gateway_id = $payment_token->get_gateway_id();
		/*
		wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/gateway_id.txt',
			array(
				'$gateway_id' => $gateway_id,
			), TRUE, FALSE
		);
		*/
		$payment_method_id = $payment_token->get_token();
		/*
		wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_method_id.txt',
			array(
				'$payment_method_id' => $payment_method_id,
			), TRUE, FALSE
		);
		*/
		$payment_method = \Stripe\PaymentMethod::retrieve($payment_method_id);
		
		//echo $payment_method->customer;
		/*
		wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_method.txt',
			array(
				'$payment_method' => $payment_method,
			), TRUE, FALSE
		);
		*/
		if(!empty($payment_method->customer))
			$customer_id = $payment_method->customer;
		else
		    $customer_id = get_user_meta($user_id, 'dokan_stripe_customer_id', true);
		
		//$customer_id = $payment_token->get_meta('dokan_stripe_customer_id');
	    //$customer_id = get_user_meta($user_id, 'wp__stripe_customer_id', true);
	    //$customer_id = get_user_meta($user_id, 'dokan_stripe_customer_id', true);
	}
	else
	{
	    $customer_id = get_user_meta($user_id, 'dokan_stripe_customer_id', true);
	}
	
    if (!$customer_id) {
        $GLOBALS['message'] = 'No Stripe customer ID found for this user.';
        return FALSE;
    }
	
    $vendor_id = get_post_field('post_author', $product_id);
	$dokan_connected_vendor_id = get_user_meta($vendor_id, 'dokan_connected_vendor_id', TRUE);
	
    if (!$dokan_connected_vendor_id) {
        $GLOBALS['message'] = 'No Connected Vendor ID found for this user.';
        return FALSE;
    }
	
	$dokan_connected_vendor_id_2 = 'acct_1KB9f6HJAwGc0vfN';
	
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/create_custom_order.txt',
		array(
			'$vendor_id' => $vendor_id,
			'$dokan_connected_vendor_id' => $dokan_connected_vendor_id,
			'$dokan_connected_vendor_id_2' => $dokan_connected_vendor_id_2,
		), TRUE, FALSE
	);
	
    try {
        $payment_intent = \Stripe\PaymentIntent::create([
            'amount' => ($amount + $commission_fee) * 100, // Amount in cents
            'currency' => 'usd', // Replace with your currency
            'customer' => $customer_id, // Use customer ID
            'payment_method' => $payment_token->get_token(),
            'off_session' => true,
            'confirm' => true,
            'description' => 'Charge for order ' . $order->get_id(),
	        'metadata' => [
	            'order_id' => $order->get_id(),
	            'vendor_id' => $dokan_connected_vendor_id, // Add vendor ID for tracking
	        ],
			
	        'application_fee_amount' => $commission_fee * 100, // Amount in cents
	        'transfer_data' => [
	            'destination' => $dokan_connected_vendor_id, // Vendor's connected account
	        ],
        ]);
		
        if ($payment_intent->status === 'requires_action' && $payment_intent->next_action->type === 'use_stripe_sdk') {
            throw new Exception('Payment requires additional actions.');
        } elseif ($payment_intent->status !== 'succeeded') {
            throw new Exception('Payment failed or not completed.');
        }
		/*
		wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
			array(
				'AFTER $payment_intent->create' => TRUE,
				'$payment_intent' => $payment_intent,
			), TRUE, FALSE
		);
		*/
        $latest_charge_id = $payment_intent->latest_charge;
		/*
		wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
			array(
				'AFTER $payment_intent->latest_charge' => TRUE,
				'$latest_charge_id' => $latest_charge_id,
			), TRUE, FALSE
		);
		*/
		/*
		wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
			array(
				'AFTER get_post($product_id)' => TRUE,
				'$event_post' => $event_post,
			), TRUE, FALSE
		);
		*/
        $vendor_id = $event_post->post_author;
		
        if ($latest_charge_id) {
            $charge = \Stripe\Charge::retrieve($latest_charge_id);
			
            $order->update_meta_data('_stripe_intent_id', $payment_intent->id);
            $order->update_meta_data('_stripe_currency', $payment_intent->currency);
            $order->update_meta_data('_stripe_customer_id', $payment_intent->customer);
            $order->update_meta_data('_stripe_source_id', $payment_token->get_token());
            $order->update_meta_data('_stripe_upe_payment_type', 'card');
            $order->update_meta_data('_stripe_charge_captured', $charge->captured ? 'yes' : 'no');
            $order->update_meta_data('_stripe_fee', $charge->balance_transaction->fee / 100);
            $order->update_meta_data('_stripe_net', $charge->balance_transaction->net / 100);
			
            $order->payment_complete($latest_charge_id);
            $order->save();
        } else {
            throw new Exception('No latest charge found for the payment intent.');
        }
    } catch (\Stripe\Exception\ApiErrorException $e) {
        $GLOBALS['message'] = 'Exception: ' . $e->getMessage();
        return FALSE;
    }
	/*
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
		array(
			'AFTER retrieve' => TRUE,
			'$charge' => $charge,
		), TRUE, FALSE
	);
	*/
    $res = $order->update_meta_data('_dokan_vendor_id', $vendor_id);
	/*
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
		array(
			'AFTER $order->update_meta_data()' => TRUE,
			'$res' => $res,
		), TRUE, FALSE
	);
	*/
	/*
    do_action('woocommerce_checkout_update_order_meta', $order_id);
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
		array(
			'AFTER woocommerce_checkout_update_order_meta' => TRUE,
		), TRUE, FALSE
	);
	*/
    do_action('woocommerce_order_status_pending', $order_id);
	/*
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
		array(
			'AFTER woocommerce_order_status_pending' => TRUE,
		), TRUE, FALSE
	);
	*/
    do_action('woocommerce_payment_complete', $order_id);
	/*
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
		array(
			'AFTER woocommerce_payment_complete' => TRUE,
		), TRUE, FALSE
	);
	*/
	
    $res = $order->set_status('completed');
	/*
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
		array(
			'AFTER $order->set_status(completed)' => TRUE,
			'$res' => $res,
		), TRUE, FALSE
	);
	*/
    $order->set_payment_method('stripe');
	/*
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
		array(
			'AFTER $order->set_payment_method()' => TRUE,
			'$res' => $res,
		), TRUE, FALSE
	);
	*/
    $order->set_payment_method_title('stripe');
	/*
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
		array(
			'AFTER $order->set_payment_method_title()' => TRUE,
			'$res' => $res,
		), TRUE, FALSE
	);
	*/
    $res = $order->save();
	/*
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt',
		array(
			'AFTER $order->save()' => TRUE,
			'$res' => $res,
		), TRUE, FALSE
	);
	*/
    do_action('dokan_order_created', $order_id, $vendor_id);
	
    do_action('dokan_checkout_update_order_meta', $order->get_id(), [
        'vendor_id' => $vendor_id,
        'commission' => $commission_fee,
    ]);
	
    $order_items = $order->get_items();
    foreach ($order_items as $item_id => $item) {
        wc_update_order_item_meta($item_id, '_product_id', $product_id);
    }
	
    $user_info = get_userdata($user_id);
	if(!$attendee_name)
		$attendee_name = $user_info->display_name;
	
    $attendee_post = [
        'post_title'   => $attendee_name,
        'post_type'    => 'etn-attendee',
        'post_status'  => 'publish',
        'post_author'  => $user_id,
    ];
	
    $attendee_id = wp_insert_post($attendee_post);
	
    if (is_wp_error($attendee_id)) {
        $GLOBALS['success'] = FALSE;
        $GLOBALS['message'] = 'wp_insert_post failed.';
        return FALSE;
        return $attendee_id;
    }
	
    update_post_meta($attendee_id, 'etn_attendee_order_id', $order_id);
    update_post_meta($attendee_id, 'etn_event_id', $product_id);
    update_post_meta($attendee_id, 'etn_status', 'success');
    update_post_meta($attendee_id, 'etn_ticket_price', $amount);
	
    $GLOBALS['attendee_id'] = $attendee_id;
    return $order_id;
}

function create_custom_order_works_wrong($user_id, $product_id, $amount = 0.50, $attendee_name = '')
{
    if (!class_exists('WC_Payment_Tokens') || !class_exists('WC_Order')) {
        $GLOBALS['success'] = FALSE;
        $GLOBALS['message'] = 'Required WooCommerce classes are not available.';
        return FALSE;
    }
	
    $payment_tokens = WC_Payment_Tokens::get_tokens(['user_id' => $user_id]);
    if (empty($payment_tokens)) {
        $GLOBALS['success'] = FALSE;
        $GLOBALS['message'] = 'No saved payment methods for this user.';
        return FALSE;
    }
	
    $payment_token = reset($payment_tokens);
	
    $order = wc_create_order([
        'customer_id' => $user_id,
        'status' => 'pending',
    ]);
	
	
	
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/create_custom_order.txt',
		array(
			'AFTER wc_create_order()' => TRUE,
		), TRUE, TRUE
	);
	
    $payment_tokens = WC_Payment_Tokens::get_tokens(['user_id' => $user_id]);
    $payment_token = reset($payment_tokens);
    $payment_method_id = $payment_token->get_token();

    $order->set_payment_method('dokan-stripe-connect');
    $order->set_payment_method_title('Dokan Stripe Connect');

    // Add product and calculate totals
    $product = wc_get_product($product_id);
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/create_custom_order.txt',
		array(
			'AFTER wc_get_product()' => TRUE,
			'$product_id' => $product_id,
			'$product' => $product,
		), TRUE, FALSE
	);
	
    if (!$product) {
        $GLOBALS['success'] = FALSE;
        $GLOBALS['message'] = 'Invalid product ID.';
        return FALSE;
    }
	
    $product->set_price($amount);
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/create_custom_order.txt',
		array(
			'AFTER $product->set_price()' => TRUE,
		), TRUE, FALSE
	);
    $order->add_product($product, 1);
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/create_custom_order.txt',
		array(
			'AFTER $order->add_product()' => TRUE,
		), TRUE, FALSE
	);
    $order->calculate_totals();
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/create_custom_order.txt',
		array(
			'AFTER $order->calculate_totals()' => TRUE,
		), TRUE, FALSE
	);
    $order->save();
	
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/create_custom_order.txt',
		array(
			'BEFORE \Stripe\PaymentMethod::retrieve()' => TRUE,
		), TRUE, FALSE
	);
	
    $payment_method = \Stripe\PaymentMethod::retrieve($payment_method_id);
	
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/create_custom_order.txt',
		array(
			'AFTER \Stripe\PaymentMethod::retrieve()' => TRUE,
		), TRUE, FALSE
	);
	
    $customer_id = !empty($payment_method->customer) ? $payment_method->customer : get_user_meta($user_id, 'dokan_stripe_customer_id', true);
	
    if (!$customer_id) {
        $GLOBALS['message'] = 'No Stripe customer ID found for this user.';
        return FALSE;
    }
	
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/create_custom_order.txt',
		array(
			'BEFORE \Stripe\PaymentIntent::create()' => TRUE,
		), TRUE, FALSE
	);
	
    $vendor_id = get_post_field('post_author', $product_id);
	$dokan_connected_vendor_id = get_user_meta($vendor_id, 'dokan_connected_vendor_id', TRUE);
	
	$dokan_connected_vendor_id_2 = 'acct_1KB9f6HJAwGc0vfN';
	
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/create_custom_order.txt',
		array(
			'$vendor_id' => $vendor_id,
			'$dokan_connected_vendor_id' => $dokan_connected_vendor_id,
			'$dokan_connected_vendor_id_2' => $dokan_connected_vendor_id_2,
		), TRUE, FALSE
	);
	
    try {
        $payment_intent = \Stripe\PaymentIntent::create([
	        'amount' => $amount * 100, // Amount in cents
	        'currency' => 'usd',
	        'customer' => $customer_id,
	        'payment_method' => $payment_token->get_token(),
	        'off_session' => true,
	        'confirm' => true,
	        'description' => 'Charge for order ' . $order_id,
	        'metadata' => ['order_id' => $order_id],
	        'application_fee_amount' => 100, // Amount in cents (e.g., $1)
	        'transfer_data' => [
	            'destination' => $dokan_connected_vendor_id, // Vendor's connected account
	        ],
        ]);
		
        if ($payment_intent->status !== 'succeeded') {
            throw new Exception('Payment failed or not completed.');
        }
		
        $latest_charge_id = $payment_intent->latest_charge;
        if ($latest_charge_id) {
            $charge = \Stripe\Charge::retrieve($latest_charge_id);
            $order->update_meta_data('_stripe_charge_id', $latest_charge_id);
            $order->payment_complete($latest_charge_id);
            $order->save();
        } else {
            throw new Exception('No latest charge found for the payment intent.');
        }
    } catch (\Stripe\Exception\ApiErrorException $e) {
        $GLOBALS['message'] = 'Exception: ' . $e->getMessage();
        return FALSE;
    }
	
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/create_custom_order.txt',
		array(
			'AFTER \Stripe\PaymentIntent::create()' => TRUE,
		), TRUE, FALSE
	);
	
    $vendor_id = get_post_field('post_author', $product_id);
    $order->update_meta_data('_dokan_vendor_id', $vendor_id);
	
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/create_custom_order.txt',
		array(
			'BEFORE dokan_process_commissions()' => TRUE,
		), TRUE, FALSE
	);
    
    dokan_process_commissions($order->get_id());

	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/create_custom_order.txt',
		array(
			'AFTER dokan_process_commissions()' => TRUE,
		), TRUE, FALSE
	);
	
    // Set the order status to completed and trigger Dokan actions
    $order->set_status('completed');
    $order->save();

    do_action('dokan_order_status_completed', $order->get_id());
    do_action('dokan_checkout_create_order', $order->get_id(), $vendor_id);

    // Optional: Create attendee post
    $user_info = get_userdata($user_id);
    if (!$attendee_name) {
        $attendee_name = $user_info->display_name;
    }

    $attendee_post = [
        'post_title'   => $attendee_name,
        'post_type'    => 'etn-attendee',
        'post_status'  => 'publish',
        'post_author'  => $user_id,
    ];

    $attendee_id = wp_insert_post($attendee_post);

    if (is_wp_error($attendee_id)) {
        $GLOBALS['success'] = FALSE;
        $GLOBALS['message'] = 'wp_insert_post failed.';
        return FALSE;
    }

    update_post_meta($attendee_id, 'etn_attendee_order_id', $order->get_id());
    update_post_meta($attendee_id, 'etn_event_id', $product_id);
    update_post_meta($attendee_id, 'etn_status', 'success');
    update_post_meta($attendee_id, 'etn_ticket_price', $amount);

    return $order->get_id();
}

function create_custom_order_($user_id, $product_id, $amount = 0.50, $attendee_name = '') {
    // Create WooCommerce order with customer
    $order = wc_create_order([
        'customer_id' => $user_id,
        'status' => 'pending',
    ]);

    // Retrieve payment token
    $payment_tokens = WC_Payment_Tokens::get_tokens(['user_id' => $user_id]);
    $payment_token = reset($payment_tokens);
    $gateway_id = $payment_token->get_gateway_id();
    $payment_method_id = $payment_token->get_token();

    // Set payment method to Dokan Stripe Connect
    $order->set_payment_method('dokan-stripe-connect');
    $order->set_payment_method_title('Dokan Stripe Connect');

    // Add product to the order
    $product = wc_get_product($product_id);
    $product->set_price($amount);
    $order->add_product($product, 1);
    $order->calculate_totals();

    // Save the order
    $order_id = $order->get_id();
    $order->save();

    // Retrieve Stripe PaymentMethod and Customer ID
    $payment_method = \Stripe\PaymentMethod::retrieve($payment_method_id);
    $customer_id = !empty($payment_method->customer) ? $payment_method->customer : get_user_meta($user_id, 'dokan_stripe_customer_id', true);

    if (!$customer_id) {
        $GLOBALS['message'] = 'No Stripe customer ID found for this user.';
        return FALSE;
    }

    // Charge the customer via Stripe
    try {
        $payment_intent = \Stripe\PaymentIntent::create([
            'amount' => $amount * 100, // Amount in cents
            'currency' => 'usd',
            'customer' => $customer_id,
            'payment_method' => $payment_token->get_token(),
            'off_session' => true,
            'confirm' => true,
            'description' => 'Charge for order ' . $order_id,
            'metadata' => ['order_id' => $order_id],
        ]);

        if ($payment_intent->status !== 'succeeded') {
            throw new Exception('Payment failed or not completed.');
        }

        // Get the latest charge ID and update order metadata
        $latest_charge_id = $payment_intent->latest_charge;
        if ($latest_charge_id) {
            $charge = \Stripe\Charge::retrieve($latest_charge_id);
            $order->update_meta_data('_stripe_charge_id', $latest_charge_id);
            $order->payment_complete($latest_charge_id);
            $order->save();
        } else {
            throw new Exception('No latest charge found for the payment intent.');
        }
    } catch (\Stripe\Exception\ApiErrorException $e) {
        $GLOBALS['message'] = 'Exception: ' . $e->getMessage();
        return FALSE;
    }

    // Get vendor ID from product and save it to the order
    $event_post = get_post($product_id);
    $vendor_id = $event_post->post_author;
    $order->update_meta_data('_dokan_vendor_id', $vendor_id);

    // Trigger Dokan-specific actions to calculate commission and process the order
    do_action('dokan_order_created', $order_id, $vendor_id);
    do_action('dokan_checkout_update_order_meta', $order_id, [
        'vendor_id' => $vendor_id,
    ]);

    // Set the order to 'completed'
    $order->set_status('completed');
    $order->save();

    // Save attendee information (optional)
    $user_info = get_userdata($user_id);
    if (!$attendee_name) {
        $attendee_name = $user_info->display_name;
    }

    $attendee_post = [
        'post_title'   => $attendee_name,
        'post_type'    => 'etn-attendee',
        'post_status'  => 'publish',
        'post_author'  => $user_id,
    ];

    $attendee_id = wp_insert_post($attendee_post);

    if (is_wp_error($attendee_id)) {
        $GLOBALS['success'] = FALSE;
        $GLOBALS['message'] = 'wp_insert_post failed.';
        return FALSE;
    }

    update_post_meta($attendee_id, 'etn_attendee_order_id', $order_id);
    update_post_meta($attendee_id, 'etn_event_id', $product_id);
    update_post_meta($attendee_id, 'etn_status', 'success');
    update_post_meta($attendee_id, 'etn_ticket_price', $amount);

    return $order_id;
}

/*
function create_custom_order($user_id, $product_id, $amount = 0.50, $attendee_name = '')
{
	$order = wc_create_order([
        'customer_id' => $user_id,
        'status' => 'pending',
    ]);
	
    $payment_tokens = WC_Payment_Tokens::get_tokens(['user_id' => $user_id]);
    $payment_token = reset($payment_tokens);
	
    $order->set_payment_method($payment_token->get_gateway_id());
    $order->set_payment_method_title($payment_token->get_gateway_id());
    
    $product = wc_get_product($product_id);
    $product->set_price($amount);
    $order->add_product($product, 1);
    $order->calculate_totals();
    $order_id = $order->get_id();
	
    $order->set_payment_method($payment_token->get_gateway_id());
    $order->set_payment_method_title($payment_token->get_gateway_id());
    $order->save();
	
	$gateway_id = $payment_token->get_gateway_id();
	$payment_method_id = $payment_token->get_token();
	$payment_method = \Stripe\PaymentMethod::retrieve($payment_method_id);
	if(!empty($payment_method->customer))
		$customer_id = $payment_method->customer;
	else
	    $customer_id = get_user_meta($user_id, 'dokan_stripe_customer_id', true);
	
    if (!$customer_id) {
        $GLOBALS['message'] = 'No Stripe customer ID found for this user.';
        return FALSE;
    }
	
	$commission_fee = 0;
	
    try {
        $payment_intent = \Stripe\PaymentIntent::create([
            'amount' => ($amount + $commission_fee) * 100, // Amount in cents
            'currency' => 'usd',
            'customer' => $customer_id,
            'payment_method' => $payment_token->get_token(),
            'off_session' => true,
            'confirm' => true,
            'description' => 'Charge for order ' . $order->get_id(),
            'metadata' => ['order_id' => $order->get_id()],
        ]);
		
        if ($payment_intent->status === 'requires_action' && $payment_intent->next_action->type === 'use_stripe_sdk') {
            throw new Exception('Payment requires additional actions.');
        } elseif ($payment_intent->status !== 'succeeded') {
            throw new Exception('Payment failed or not completed.');
        }
		
        $latest_charge_id = $payment_intent->latest_charge;
		
        $event_post = get_post($product_id);
        $vendor_id = $event_post->post_author;
		
        if ($latest_charge_id) {
            $charge = \Stripe\Charge::retrieve($latest_charge_id);
			
            $order->update_meta_data('_stripe_intent_id', $payment_intent->id);
            $order->update_meta_data('_stripe_currency', $payment_intent->currency);
            $order->update_meta_data('_stripe_customer_id', $payment_intent->customer);
            $order->update_meta_data('_stripe_source_id', $payment_token->get_token());
            $order->update_meta_data('_stripe_upe_payment_type', 'card');
            $order->update_meta_data('_stripe_charge_captured', $charge->captured ? 'yes' : 'no');
            $order->update_meta_data('_stripe_fee', $charge->balance_transaction->fee / 100);
            $order->update_meta_data('_stripe_net', $charge->balance_transaction->net / 100);
			
            $order->payment_complete($latest_charge_id);
            $order->save();
        } else {
            throw new Exception('No latest charge found for the payment intent.');
        }
    } catch (\Stripe\Exception\ApiErrorException $e) {
        $GLOBALS['message'] = 'Exception: ' . $e->getMessage();
        return FALSE;
    }
	
    $order->update_meta_data('_dokan_vendor_id', $vendor_id);
    do_action('woocommerce_order_status_pending', $order_id);
    do_action('woocommerce_payment_complete', $order_id);
    $order->set_status('completed');
    $order->set_payment_method('dokan-stripe-connect');
    $order->set_payment_method_title('dokan-stripe-connect');
    $res = $order->save();
    do_action('dokan_order_created', $order_id, $vendor_id);
    do_action('dokan_checkout_update_order_meta', $order->get_id(), [
        'vendor_id' => $vendor_id,
        'commission' => $commission_fee,
    ]);
	
    $order_items = $order->get_items();
    foreach ($order_items as $item_id => $item) {
        wc_update_order_item_meta($item_id, '_product_id', $product_id);
    }
	
    $user_info = get_userdata($user_id);
	if(!$attendee_name)
		$attendee_name = $user_info->display_name;
	
    $attendee_post = [
        'post_title'   => $attendee_name,
        'post_type'    => 'etn-attendee',
        'post_status'  => 'publish',
        'post_author'  => $user_id,
    ];
	
    $attendee_id = wp_insert_post($attendee_post);
	
    if (is_wp_error($attendee_id)) {
        $GLOBALS['success'] = FALSE;
        $GLOBALS['message'] = 'wp_insert_post failed.';
        return FALSE;
        return $attendee_id;
    }
	
    update_post_meta($attendee_id, 'etn_attendee_order_id', $order_id);
    update_post_meta($attendee_id, 'etn_event_id', $product_id);
    update_post_meta($attendee_id, 'etn_status', 'success');
    update_post_meta($attendee_id, 'etn_ticket_price', $amount);
	
    $GLOBALS['attendee_id'] = $attendee_id;
    return $order_id;
}
*/
/*
	$log_filename = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/payment_intent.txt';
	wph_log_data_global($log_filename,
			array(
			'$vendor_id' => $vendor_id,
			'$payment_intent' => $payment_intent,
			'$payment_token' => $payment_token,
			'$order' => $order,
		), TRUE, FALSE
	);
*/
//$_REQUEST['order_id'] = 1646;

if(!empty($_REQUEST['refund']))
{
	$amount = !empty($_REQUEST['amount']) ? $_REQUEST['amount'] : 0;
	$success = refund_order($_REQUEST['refund'], $amount);
}
elseif(!empty($_REQUEST['user_id']) && !empty($_REQUEST['product_id']) && !empty($_REQUEST['amount']))
{
    check_ajax_referer('create-custom-order-nonce', 'security');
	
	$attendee_name = !empty($_REQUEST['player_name']) ? $_REQUEST['player_name'] : '';
	
	try {
		$order_id = create_custom_order($_REQUEST['user_id'], $_REQUEST['product_id'], $_REQUEST['amount'], $attendee_name);
	} catch (Exception $e) {
	    // Log the error message for debugging purposes
	    error_log('Error creating custom order: ' . $e->getMessage());
		$GLOBALS['message'] = $e->getMessage();
	    // Optionally, display a user-friendly message or handle the error gracefully
	    //echo 'An error occurred while processing your order. Please try again or contact support.';
	}
	finally {
		if($order_id && !empty($_REQUEST['player_name']))
		{
			global $wpdb;
			
			$product_id = $_REQUEST['product_id'];
			$user_id = $_REQUEST['user_id'];
			$player_name = $_REQUEST['player_name'];
			$amount = $_REQUEST['amount'];
			
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
			                    $player['charged'] = $order_id;
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
	$GLOBALS['message'] = $message = 'Missing data';
	$success = FALSE;
}
/*
wph_log_data_global($log_filename,
	array(
		'AFTER ALL' => TRUE,
		'$success' => $success,
	), TRUE, FALSE
);
*/
$CONTENT = ob_get_contents();

$added_player = !empty($_REQUEST['added_player']) ? $_REQUEST['added_player'] : 0;

$response = array('success' => $success, 'order_id' => $GLOBALS['order_id'], 'attendee_id' => $GLOBALS['attendee_id'], 'message' => $GLOBALS['message'], 'CONTENT' => $CONTENT, 'button_id' => $button_id, 'added_player' => $added_player);//
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