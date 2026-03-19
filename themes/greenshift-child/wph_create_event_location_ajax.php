<?php
/*
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
*/
//echo 'wph-save-post-ajax.php';
//return;

ob_start();

require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
$success = FALSE;
$message = '';

$log_filename = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph_create_event_location_ajax.txt';

function get_unique_term_name($term_name, $taxonomy) {
    $suffix = 0;
    $unique_name = $term_name;
    
    while (term_exists($unique_name, $taxonomy)) {
        $suffix++;
        $unique_name = $term_name . '_' . $suffix;
    }
    
    return $unique_name;
}

if(1
		&& isset( $_REQUEST['etn_event_location'] )
		&& isset( $_REQUEST['street_address_line_1'] )
		&& isset( $_REQUEST['street_address_line_2'] )
		&& isset( $_REQUEST['city'] )
		&& isset( $_REQUEST['state'] )
		&& isset( $_REQUEST['zip'] )
)
{
    $term_args = array(
        'taxonomy' => 'etn_location',
        'name' => $_REQUEST['etn_event_location'],
    );
	
	//$term = get_term_by( 'name', $term_args['name'], $term_args['taxonomy'] );
	$unique_term_name = get_unique_term_name($term_args['name'], $term_args['taxonomy']);
	
	//if($term === FALSE)
	if(1)
	{
	    $term = wp_insert_term( $unique_term_name, $term_args['taxonomy'] );
		
	    if( ! is_wp_error( $term ) )
		{
	        $term_id = $term['term_id'];
	        $meta_key = 'address';
	        //$meta_value = $_REQUEST['address'];
			
		    $address_components = array(
		         $_REQUEST['street_address_line_1'],
		         $_REQUEST['street_address_line_2'],
		         $_REQUEST['city'],
		         $_REQUEST['state'],
		         $_REQUEST['zip'],
		    );
			
		    $address_components = array_filter( $address_components );
		    $meta_value = implode( ', ', $address_components );
			
	        add_term_meta( $term_id, $meta_key, $meta_value );
			
			$user_id = get_current_user_id();
	        add_term_meta( $term_id, 'user_id', $user_id );
			
	        $_REQUEST['etn_location'] = array( strval($term_id) );
			$_REQUEST['etn_event_location_type'] = 'existing_location';
			
	        $success = TRUE;
		}
		else
		{
	        $message = 'Failed to create location';
	    }
    }
	else
	{
        $message = 'Location "' . $term_args['name'] . '" already exists.<br>Please choose another Location Name.';
    }
}

$CONTENT = ob_get_contents();
$response = array('success' => $success, 'message' => $message, 'bodyData' => $_REQUEST, 'CONTENT' => $CONTENT);
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