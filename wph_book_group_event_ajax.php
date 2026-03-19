<?php
/*
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
*/
//return;

ob_start();

require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
$success = FALSE;
$event_id = $user_id = 0;
$book_data = [];
$already_booked = $this_user_booked = FALSE;
$GLOBALS['message'] = $message = '';

function wph_get_post_meta($post_id, $meta_key = '', $single = false) {
    // Database credentials from WordPress configuration
    $db_host     = DB_HOST;
    $db_user     = DB_USER;
    $db_password = DB_PASSWORD;
    $db_name     = DB_NAME;

    // Sanitize post ID
    $post_id = absint($post_id);

    // Establish a connection using mysqli
    $mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

    // Check for connection errors
    if ($mysqli->connect_error) {
        die('Connection error: ' . $mysqli->connect_error);
    }

    // Craft the SQL query
    if ('' !== $meta_key) {
        // If meta_key is provided, select specific meta
        $sql = 'SELECT meta_value FROM wp_postmeta WHERE post_id = "' . $post_id . '" AND meta_key = "' . $meta_key . '"';
    } else {
        // If no meta_key is provided, select all meta for the post
        $sql = 'SELECT meta_key, meta_value FROM wp_postmeta WHERE post_id = "' . $post_id . '"';
    }

    // Execute the query
    $result = $mysqli->query($sql);
	/*
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/result.txt',
		array(
			'$sql' => $sql,
			'$result->num_rows' => $result->num_rows,
			'$result' => $result,
		), TRUE, FALSE
	);
	*/
    if ($result->num_rows > 0) {
        if ('' !== $meta_key) {
            // If single value is requested
            if ($single) {
                $row = $result->fetch_assoc();
				$meta_value = maybe_unserialize($row['meta_value']);
				/*
				wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/sql.txt',
					array(
						'$sql' => $sql,
						'$meta_value' => $meta_value,
					), TRUE, FALSE
				);
				*/
                return $meta_value;
            }

            // If multiple values for the same meta_key
            $meta_values = [];
            while ($row = $result->fetch_assoc()) {
                $meta_values[] = maybe_unserialize($row['meta_value']);
            }
			
			$meta_value = maybe_unserialize($row['meta_value']);
			/*
			wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/sql.txt',
				array(
					'$sql' => $sql,
					'$meta_values' => $meta_values,
				), TRUE, FALSE
			);
			*/
            return $meta_values;

        } else {
            // Return all post meta in an associative array
            $meta_data = [];
            while ($row = $result->fetch_assoc()) {
                $meta_data[$row['meta_key']][] = maybe_unserialize($row['meta_value']);
            }
            return $meta_data;
        }
    } else {
        // Return empty array if no meta found
        return [];
    }

    // Close the connection
    $mysqli->close();
}


$log_filename = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph_book_group_event_ajax.txt';

if(!empty($_REQUEST['event_id']) && !empty($_REQUEST['user_id']))
{
	
	wph_log_data_global(get_stylesheet_directory() . '/wph_book_group_event_ajax.txt',
		array(
			'$_REQUEST' => $_REQUEST,
		), TRUE, FALSE
	);
	
    check_ajax_referer('book-group-event-nonce', 'security');
/*
	wph_log_data_global($log_filename,
		array(
			'AFTER check_ajax_referer' => TRUE,
		), TRUE, FALSE
	);
*/

    $event_id = $_REQUEST['event_id'];
    $user_id = $_REQUEST['user_id'];
	
	$book_data = get_post_meta($event_id, 'book_data', FALSE);
	vd($book_data, '$book_data');
	/*
	if($book_data)
	{
		$already_booked = TRUE;
		
		foreach($book_data as $book_data_entry)
		{
			if($book_data_entry['user_id'] == $user_id)
			{
				$this_user_booked = TRUE;
				
				if(!empty($_REQUEST['unbook']))
				{
					$res = delete_post_meta($event_id, 'book_data', $book_data_entry);
					if($res)
					{
						$this_user_booked = FALSE;
						$message = 'You have unbooked this event.';
						$success = TRUE;
					}
				}
				
				break;
			}
		}
	}
	*/
	//if(!$this_user_booked && empty($_REQUEST['unbook']))
	if(1)
	{
		$players = isset($_REQUEST['players']) ? $_REQUEST['players'] : array();
		
		$i = 0;
		$count = count($players);
		//foreach($players as &$player)
		for($j = 0; $j < $count; $j++)
		{
			$i++;
			
			wph_log_data_global($log_filename,
				array(
					'$players[$j]' => $players[$j],
				), TRUE, FALSE
			);
			if($players[$j]['player'] == 'Add New Player')
			{
				$player_name = '';
				if(!empty($_REQUEST['first_name']))
					$player_name = $_REQUEST['first_name'][0];
				if(!empty($_REQUEST['last_name']))
					$player_name .= ' ' . $_REQUEST['last_name'][0];
				
				$player_name = trim($player_name);
				
				if(!$player_name)
					$player_name = 'New Player';
				
				$players[$j]['player'] = $player_name;
				
				if(!empty($_REQUEST['save_player']))
				{
					wph_log_data_global($log_filename,
						array(
							'$_REQUEST[\'save_player\']' => $_REQUEST['save_player'],
						), TRUE, FALSE
					);
					if(!empty($_REQUEST['first_name'][0]) && !empty($_REQUEST['last_name'][0]))
					{
						wph_log_data_global($log_filename,
							array(
								'$_REQUEST[\'first_name\'][0]' => $_REQUEST['first_name'][0],
								'$_REQUEST[\'last_name\'][0]' => $_REQUEST['last_name'][0],
							), TRUE, FALSE
						);
						
						$post_data = array();
						$post_data['post_status'] = 'publish';
						$post_data['post_type'] = 'child';
						$post_data['post_title'] = sanitize_text_field($player_name);
						
						$post_content = '';
						$post_data['post_content'] = wp_kses_post($post_content);
						
						$child_id = wp_insert_post($post_data);
						
						wph_log_data_global($log_filename,
							array(
								'$child_id' => $child_id,
							), TRUE, FALSE
						);
						if($child_id)
						{
							update_post_meta($child_id, 'first_name', $_REQUEST['first_name'][0]);
							update_post_meta($child_id, 'last_name', $_REQUEST['last_name'][0]);
							
							$child = array(
								'id' => $child_id,
								'first_name' => $_REQUEST['first_name'][0],
								'last_name' => $_REQUEST['last_name'][0],
							);
							
							//$current_user_id = get_current_user_id();
							//$children = get_user_meta($current_user_id, 'children', TRUE);
							$children = get_user_meta($user_id, 'children', TRUE);
							
							if($children && is_array($children))
							{
								$children[] = $child;
								//$new_children = array($children[0], $children[1], $child);
							}
							else
								$children = array($child);
							
							update_user_meta($user_id, 'children', $children);
							wph_log_data_global($log_filename,
								array(
									'$children' => $children,
								), TRUE, FALSE
							);
						}
					}
				}
			}
		}
		
		$booking_user = isset($_REQUEST['booking_user']) ? $_REQUEST['booking_user'] : 0;
		$group_select = isset($_REQUEST['group_select']) ? $_REQUEST['group_select'] : -1;
		$player_age = isset($_REQUEST['player_age']) ? $_REQUEST['player_age'] : -1;
		$player_level = isset($_REQUEST['player_level']) ? $_REQUEST['player_level'] : -1;
		$event_description = isset($_REQUEST['event_description']) ? $_REQUEST['event_description'] : -1;
		$delete_player = isset($_REQUEST['delete_player']) ? $_REQUEST['delete_player'] : 0;
		
		$res = update_book_data_meta($event_id, 
										$_REQUEST['user_id'], 
										$group_select, 
										$player_age, 
										$player_level, 
										$event_description, 
										$players, 
										$delete_player, 
										$booking_user, 
				);
		if($res)
		{
			$success = TRUE;
			send_custom_woocommerce_email($user_id, [$event_id]);
		}
		
		wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/booking_user.txt',
			array(
				'$_REQUEST[\'user_id\']' => $_REQUEST['user_id'],
				'$booking_user' => $booking_user,
			), TRUE, FALSE
		);
		//$res = add_post_meta($event_id, 'book_data', $book_data, FALSE);
		
		//$res = insert_custom_post_meta($event_id, 'book_data', $book_data);
		//$res = insert_into_postmeta($event_id, 'book_data', $book_data);
		
		//if($res)
			//$success = TRUE;
	}
	else
	{
		if(!$GLOBALS['message'])
			$GLOBALS['message'] = 'You already have booked this event.';
	}
}
/*
wph_log_data_global($log_filename,
	array(
		'AFTER ALL' => TRUE,
	), TRUE, FALSE
);
*/
$CONTENT = ob_get_contents();
$response = array('success' => $success, 'message' => $GLOBALS['message'], 'event_id' => $event_id, 'user_id' => $user_id, 'book_data' => $book_data, 'CONTENT' => $CONTENT);
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