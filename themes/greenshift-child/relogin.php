<?php
ob_start();

require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
$success = TRUE;
$message = '';

$logged_user_id = get_current_user_id();
$simulate_checkout_vendor = get_user_meta($logged_user_id, 'simulate_checkout_vendor', TRUE);

if($simulate_checkout_vendor)
{
	delete_user_meta($logged_user_id, 'simulate_checkout_vendor');
	wp_set_current_user($simulate_checkout_vendor);
	wp_set_auth_cookie($simulate_checkout_vendor);
	$message = 'Re-log in executed';
}

$response = array('success' => $success, 'message' => $message, 'CONTENT' => $CONTENT);
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
