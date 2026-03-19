<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');

function send_custom_woocommerce_email($user_id, $product_ids) {
    if (!$user_id || empty($product_ids)) {
        return FALSE;
    }
	
    $user = get_userdata($user_id);
    if (!$user) {
        return FALSE;
    }
	
    $fake_order = wc_create_order();
	$order_id = $fake_order->get_id();
	
	$fake_order->update_meta_data('fake_order', 1);
	update_post_meta($order_id, 'lesson_book_order', $product_ids[0]);
	
    $fake_order->set_customer_id($user_id);
    $fake_order->set_billing_first_name($user->first_name ?: 'Customer');
    $fake_order->set_billing_last_name($user->last_name ?: '');
    $fake_order->set_billing_email($user->user_email);
	
    foreach ($product_ids as $product_id) {
        $product = wc_get_product($product_id);
        if ($product) {
            $fake_order->add_product($product, 1);
        }
    }
	
    $fake_order->calculate_totals();
    $fake_order->update_status('completed');
    $fake_order->save();
	
	$fake_order->delete( true );
	
    //return $email_class->trigger($fake_order->id);
    return TRUE;
}

vd(send_custom_woocommerce_email(283, [7125]));
