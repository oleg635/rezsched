<?php
/*
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
*/
function split_string_approx_equal($string) {
    $words = explode(' ', $string);
    $total_words = count($words);

    if ($total_words == 2) {
        return [$words[0], $words[1]];
    }

    if ($total_words == 3)
        return [$words[0], $words[1] . ' ' . $words[2]];

    if ($total_words == 4)
        return [$words[0] . ' ' . $words[1], $words[2] . ' ' . $words[3]];

    $midpoint = ceil(strlen($string) / 2);
    
    $part1 = '';
    $part2 = '';

    $current_length = 0;

    // Loop through words and split based on length closest to the midpoint
    foreach ($words as $word) {
        if (($current_length + strlen($word)) < $midpoint) {
            $part1 .= $word . ' ';
            $current_length += strlen($word) + 1; // +1 for the space
        } else {
            $part2 .= $word . ' ';
        }
    }

    // Trim any trailing spaces
    $part1 = trim($part1);
    $part2 = trim($part2);

    return [$part1, $part2];
}

//echo 'wph-save-post-ajax.php';
//return;

ob_start();

require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
$success = FALSE;
$waiver_id = $user_id = 0;
$book_data = [];
$already_booked = $this_user_booked = FALSE;
$message = '';

$log_filename = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph_save_waiver_ajax.php.txt';

if(!empty($_REQUEST['waiver_id']) && !empty($_REQUEST['user_id']))
{
    check_ajax_referer('save-waiver-nonce', 'security');
	
	$meta_key = 'waiver_' . $_REQUEST['waiver_id'];
	
	if(!empty($_REQUEST['confirmed']))
	{
		$time = time();
		update_user_meta($_REQUEST['user_id'], $meta_key, $time);
		
		if(!empty($_REQUEST['event_id']))
		{
			$signed_waivers_meta_key = 'signed_waivers';
			$waiver = get_post($_REQUEST['waiver_id']);
			if($waiver)
			{
				$waiver_data = array(
				    'waiver_id' => $waiver->ID,
				    'waiver_title' => $waiver->post_title,
				    'waiver_content' => $waiver->post_content,
				    'time_signed' => $time,
				    'event_id' => $_REQUEST['event_id'],
				);
				
				$event = get_post($_REQUEST['event_id']);
				if($event)
				{
					$waiver_data['event_title'] = $event->post_title;
					$waiver_data['event_content'] = $event->post_content;
				}
				
				add_user_meta($_REQUEST['user_id'], $signed_waivers_meta_key, $waiver_data);
			}
		}
		
		if(isset($_REQUEST['contact_phone']))
		{
			update_user_meta($_REQUEST['user_id'], 'contact_phone', $_REQUEST['contact_phone']);
		}
		
		if(isset($_REQUEST['contact_name']))
		{
			list($contact_first_name, $contact_last_name) = split_string_approx_equal($_REQUEST['contact_name']);
			update_user_meta($_REQUEST['user_id'], 'contact_first_name', $contact_first_name);
			update_user_meta($_REQUEST['user_id'], 'contact_last_name', $contact_last_name);
		}
		
		$success = TRUE;
		$message = 'You have successfully signed the waiver.';
	}
	else
	{
		$sign = get_user_meta($_REQUEST['user_id'], $meta_key, TRUE);
		if($sign)
		{
			delete_user_meta($_REQUEST['user_id'], $meta_key);
			$success = TRUE;
			$message = 'You have successfully unsigned the waiver.';
		}
		else
		{
			$success = FALSE;
			$message = 'Please accept both checkboxes to save the waiver.';
		}
	}
	/*
	wph_log_data_global($log_filename,
		array(
			'$_REQUEST' => $_REQUEST,
		), TRUE, FALSE
	);
	*/
/*
	wph_log_data_global($log_filename,
		array(
			'AFTER check_ajax_referer' => TRUE,
		), TRUE, FALSE
	);
*/
}
/*
wph_log_data_global($log_filename,
	array(
		'AFTER ALL' => TRUE,
	), TRUE, FALSE
);
*/
$CONTENT = ob_get_contents();
$response = array('success' => $success, 'message' => $message, 'waiver_id' => $waiver_id, 'user_id' => $user_id, 'book_data' => $book_data, 'CONTENT' => $CONTENT);
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