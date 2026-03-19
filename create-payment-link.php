<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/stripe/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');

vd(Stripe_Secret_Key, 'Stripe_Secret_Key');
return;

\Stripe\Stripe::setApiKey(Stripe_Secret_Key);

try {
    // Dynamic amount (e.g., from a form or database)
    $amount = 4000; // $40 in cents

    // Create the product dynamically
    $product = \Stripe\Product::create([
        'name' => 'Tennis Lesson',
    ]);

    // Create a price for the product
    $price = \Stripe\Price::create([
        'unit_amount' => $amount,
        'currency' => 'usd',
        'product' => $product->id, // Link the price to the product
    ]);

    // Create a Payment Link for the newly created price
    $paymentLink = \Stripe\PaymentLink::create([
        'line_items' => [[
            'price' => $price->id, // Use the dynamically created price ID
            'quantity' => 1,
        ]],
    ]);

    // Output the Payment Link
    echo "Send this link to the user: " . $paymentLink->url;

} catch (\Stripe\Exception\ApiErrorException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>