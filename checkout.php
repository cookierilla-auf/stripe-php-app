<?php
require_once 'vendor/autoload.php';
$config = require 'config.php';

use GuzzleHttp\Client;

// Check if the price_id was actually sent from the products page
if (isset($_POST['price_id'])) {
    $price_id = $_POST['price_id'];
} else {
    // If someone tries to access checkout.php directly without clicking a button
    header("Location: products.php");
    exit;
}

$client = new Client(['base_uri' => 'https://api.stripe.com/v1/']);

try {
    $response = $client->request('POST', 'checkout/sessions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $config['secret_key'],
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ],
        'form_params' => [
            'line_items' => [
                [
                    'price' => $price_id,
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment', // Or 'subscription' if your products are recurring
            'success_url' => 'http://localhost/stripe-php-app/success.php',
            'cancel_url' => 'http://localhost/stripe-php-app/cancel.php',
        ],
    ]);

    $session = json_decode($response->getBody(), true);
    
    // This is the magic line that redirects the user to Stripe
    header("Location: " . $session['url']);
    exit;

} catch (\Exception $e) {
    echo "Checkout Error: " . $e->getMessage();
}