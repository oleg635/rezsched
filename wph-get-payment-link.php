<?php
/*
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
*/
//echo 'wph-get-users-ajax.php';
//return;

ob_start();

require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
$success = TRUE;
$post_id = 0;
$payment_link = $message = '';

$log_filename = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph-get-payment-link.txt';


if(1)
{
    check_ajax_referer('get-payment-link', 'security');
	$user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
	$order_id = !empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : 0;
	$product_id = !empty($_REQUEST['product_id']) ? $_REQUEST['product_id'] : 0;
	$event_name = !empty($_REQUEST['event_name']) ? $_REQUEST['event_name'] : 0;
	
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph-get-payment-link.txt',
		array(
			'BEFORE create_payment_link_for_existing_order' => TRUE,
			'$order_id' => $order_id,
			'$product_id' => $product_id,
		), TRUE, FALSE
	);
	
	$payment_link = create_payment_link_for_existing_order($order_id, $product_id);
	
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph-get-payment-link.txt',
		array(
			'AFTER create_payment_link_for_existing_order' => TRUE,
			'$payment_link' => $payment_link,
		), TRUE, FALSE
	);
	
	if(!$payment_link)
		$success = FALSE;
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
$response = array('success' => $success, 'user_id' => $user_id, 'order_id' => $order_id, 'product_id' => $product_id, 'event_name' => $event_name, 'payment_link' => $payment_link, 'message' => $message, 'CONTENT' => $CONTENT);
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