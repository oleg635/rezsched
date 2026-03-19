<?php
ob_start();

require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
$success = TRUE;
$message = '';

if(isset($_POST['post_id']) && isset($_POST['meta_key']) && isset($_POST['meta_value']))
{
	$attendee_id = $_POST['post_id'];
	$res = update_post_meta($attendee_id, $_POST['meta_key'], $_POST['meta_value']);
	
	if($_POST['meta_key'] == 'etn_status_custom')
	{
		$etn_event_id = get_post_meta($attendee_id, 'etn_event_id', true);
		if($etn_event_id)
		{
			$etn_total_avaiilable_tickets = get_post_meta($etn_event_id, 'etn_total_avaiilable_tickets', true);
			if($etn_total_avaiilable_tickets)
			{
				$event_attendees = get_event_attendees($etn_event_id, $is_lesson = FALSE);
				$count_attendees = count($event_attendees); 
				//$etn_total_sold_tickets = $etn_total_avaiilable_tickets - $count_attendees;
				//$res = update_post_meta($etn_event_id, 'etn_total_sold_tickets', $etn_total_sold_tickets);
				$res = update_sold_tickets_abs($etn_event_id, $count_attendees);
			}
		}
	}
	
	$success = $res;
	if($success)
		$message = 'All is ok';
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
