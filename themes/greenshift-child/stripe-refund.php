<?php
ob_start();

require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
$success = TRUE;
$message = '';

// Make sure Stripe is initialized
\Stripe\Stripe::setApiKey(Stripe_Secret_Key);

check_ajax_referer('order-item', 'security');

$order_id = isset($_REQUEST['order_id']) ? $_REQUEST['order_id'] : 0;
$item_id = isset($_REQUEST['item_id']) ? $_REQUEST['item_id'] : 0;
$attendee_id = isset($_REQUEST['attendee_id']) ? $_REQUEST['attendee_id'] : 0;
$refund_amount = isset($_REQUEST['refund_amount']) ? $_REQUEST['refund_amount'] : 0;
//$refund_amount = 1;
$refund_reason = 'requested_by_customer';

$order = wc_get_order($order_id);
$vendor_id = $order->get_meta('_dokan_vendor_id');
$charge_id_meta_key = "_dokan_stripe_charge_id_{$vendor_id}";
$charge_id = $order->get_meta($charge_id_meta_key);

vd($charge_id_meta_key, '$charge_id_meta_key');
vd($charge_id, '$charge_id');

if ($charge_id) {
    try {
        // Create a refund directly with Stripe
        $refund = \Stripe\Refund::create([
            'charge' => $charge_id,
            'amount' => $refund_amount * 100, // Refund in cents
            'reason' => $refund_reason,
        ]);
        
        $message = 'Refund successful! Refund ID: ' . $refund->id;
		
		$refund_id = wc_create_refund([
		    'amount'          => $refund_amount,
		    'reason'          => $refund_reason,
		    'order_id'        => $order_id,
		    //'refund_payment'  => true,  // Refund via the payment gateway
		]);
		
		if (is_wp_error($refund_id)) {
		    $message .= 'Refund creation failed: ' . $refund_id->get_error_message();
			$success = FALSE;
		} else {
		    // Update the order status to "refunded"
		    $order->update_status('wc-refunded', 'Refunded via Stripe API.');
			
		    // Optionally, add a note to the order
		    $order->add_order_note('Refund processed. Amount: ' . $refund_amount . ' via Stripe API.');
			
		    $message .= 'Refund successful and order status updated to refunded!';
		}
		
    } catch (\Stripe\Exception\ApiErrorException $e) {
        $message .= 'Error while refunding: ' . $e->getMessage();
		$success = FALSE;
    }
} else {
    $message .= 'No Stripe charge ID found for the order.';
	$success = FALSE;
}

$CONTENT = ob_get_contents();
$response = array('success' => $success, 'order_id' => $order_id, 'item_id' => $item_id, 'attendee_id' => $attendee_id, 'message' => $message, 'CONTENT' => $CONTENT);
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