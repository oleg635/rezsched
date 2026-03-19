<?php
if($_SERVER['REMOTE_ADDR'] == '188.163.75.165')
{
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/stripe/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');

//https://buy.stripe.com/test_14k3f2gZ83mRaAgeUU

\Stripe\Stripe::setApiKey(Stripe_Secret_Key);

$vendor_account_id = 'acct_1KB9f6HJAwGc0vfN'; // Replace with the vendor's connected account ID
$product_price = 40; // $40 for the product
$admin_fee = 3; // $3 fee for the admin
$total_amount = $product_price + $admin_fee; // Total charge = $43

$site_url = get_site_url();
$site_url = trim($site_url, '/');


$user_id = 147;
$product_id = 6914;

//$order_id = create_order_for_payment_link($user_id, $product_id, 40.00, 'John Doe');
$order_id = create_order_for_payment_link($user_id, $product_id, 40.00);
$checkout = create_stripe_checkout_session($order_id, $product_id, $site_url);

if (is_wp_error($checkout)) {
    echo $checkout->get_error_message();
} else {
    echo 'Redirect the user to: ';
?>
	<a target="_blank" href="<?=$checkout?>">checkout</a>
<?php
}
?>