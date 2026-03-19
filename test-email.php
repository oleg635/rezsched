<?php
require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');

$res = wp_mail('webworks.ok@gmail.com', 'Test Email', 'This is a test email from WordPress.');
if ($res) {
    echo 'Email sent successfully!';
} else {
    echo 'Email failed to send.';
}

vd($res, '$res');
?>