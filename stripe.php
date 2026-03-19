<?php
/*
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
*/
require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/stripe/vendor/autoload.php');

\Stripe\Stripe::setApiKey(Stripe_Secret_Key);

$payment_intent = 'pi_3QnOFJ026bVGkMvK19vfSeWX';

try {
    $test_intent = \Stripe\PaymentIntent::retrieve($payment_intent);
    var_dump(print_r($test_intent, true));
} catch (\Stripe\Exception\ApiErrorException $e) {
    var_dump("Stripe API Error: " . $e->getMessage());
} catch (Exception $e) {
    var_dump("General Error: " . $e->getMessage());
}

?>