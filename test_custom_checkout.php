<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');


function custom_redirect_after_checkout($order_id) {
    $redirect_url = site_url();
    
    if (!is_admin()) {
        wp_safe_redirect('/order-history/');
        exit;
    }
}

add_action('woocommerce_thankyou', 'custom_redirect_after_checkout');
