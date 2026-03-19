<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

if (!is_user_logged_in()) {
    wp_send_json_error('User not logged in.');
}

// Verify nonce (optional)
// if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'your_nonce_action')) {
//     wp_send_json_error('Invalid nonce.');
// }

if (!isset($_FILES['image'])) {
    wp_send_json_error('Image file not found.');
}

if (!isset($_REQUEST['user_id'])) {
    wp_send_json_error('No user_id');
}

$user_id = $_REQUEST['user_id'];

$upload_dir = wp_upload_dir();
$user_upload_dir = trailingslashit($upload_dir['basedir']) . 'ultimatemember/' . $user_id;
if (!file_exists($user_upload_dir)) {
    wp_mkdir_p($user_upload_dir);
}

$file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$file_name = 'profile_photo.' . $file_extension;
/*
$counter = 1;
while (file_exists($user_upload_dir . '/' . $file_name)) {
    $file_name = 'profile_photo_' . $counter . '.' . $file_extension;
    $counter++;
}
*/
$target_file = trailingslashit($user_upload_dir) . $file_name;

//$file_name = 'profile_photo.png';
/*
$counter = 1;
while (file_exists($user_upload_dir . '/' . $file_name)) {
    $file_name = 'profile_photo_' . $counter . '.png';
    $counter++;
}
*/
$target_file = trailingslashit($user_upload_dir) . $file_name;


$log_filename = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/the7childtheme/wph-upload-image-ajax.txt';

wph_log_data_global($log_filename,
	array(
		'$user_upload_dir' => $user_upload_dir,
		'$file_name' => $file_name,
	), TRUE, FALSE
);
// Move the uploaded image file to the target directory
if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
	update_user_meta($user_id, 'profile_photo', $file_name);
	
    //do_action('custom_image_uploaded', $target_file);
    wp_send_json_success('Image uploaded successfully.');
} else {
    // Error uploading image
    wp_send_json_error('Error uploading image.');
}
