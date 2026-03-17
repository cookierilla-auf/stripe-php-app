<?php
require_once 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/')->load();

return[
    'publishable_key' => $_ENV['STRIPE_PUBLISHABLE_KEY'],
    'secret_key' => $_ENV['STRIPE_SECRET_KEY'],
];

