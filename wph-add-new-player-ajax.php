<?php
ob_start();

require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
$success = TRUE;
$post_id = 0;
$message = '';

$user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
$first_name = isset($_REQUEST['first_name'][0]) ? $_REQUEST['first_name'][0] : '';
$last_name = isset($_REQUEST['last_name'][0]) ? $_REQUEST['last_name'][0] : '';
	
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph-send-link-ajax.php',
		array(
			'$user_id' => $user_id,
			'$payment_link' => $payment_link,
			'$event_name' => $event_name,
			'$_REQUEST' => $_REQUEST,
		), TRUE, TRUE
	);

if($user_id > 0 && !empty($first_name) && !empty($last_name))
{
    check_ajax_referer('add-new-player-nonce', 'security');
	
	$player = add_new_player();
	
} else {
	$message = 'Required parameters are missing.';
	$success = FALSE;
}

$CONTENT = ob_get_contents();
$response = array('success' => $success, 'user_id' => $user_id, 'player' => $player, 'event_name' => $event_name, 'message' => $message, 'CONTENT' => $CONTENT);
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