<?php
// 1. Enable error reporting so you can see the actual error message
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php';
$config = require 'config.php';

use GuzzleHttp\Client;

$products = [];
$errorMessage = "";

$client = new Client(['base_uri' => 'https://api.stripe.com/v1/']);

try {
    // Fetch products and expand the default_price to get the amount and ID
    $response = $client->request('GET', 'products', [
        'headers' => [
            'Authorization' => 'Bearer ' . $config['secret_key'],
        ],
        'query' => [
            'active' => 'true', // Changed from true to 'true' (string)
            'expand' => ['data.default_price']
        ]
    ]);

    $data = json_decode($response->getBody(), true);
    $products = $data['data'];
    
} catch (\Exception $e) {
    $errorMessage = "Stripe API Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DROP 01 | Streetwear Collective</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        :root { --black: #000000; --white: #ffffff; --gray: #f4f4f4; }
        body { font-family: 'Inter', sans-serif; background-color: var(--white); margin: 0; padding: 0; text-transform: uppercase; }
        
        header { padding: 40px 5%; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--black); }
        header h1 { font-weight: 900; letter-spacing: -2px; margin: 0; font-size: 2rem; }

        .container { padding: 50px 5%; }
        
        /* Modern Grid */
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 40px; }

        .product-card { position: relative; transition: all 0.3s ease; }
        .image-container { background-color: var(--gray); aspect-ratio: 3/4; overflow: hidden; position: relative; }
        .image-container img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
        .product-card:hover img { transform: scale(1.05); }

        .info { margin-top: 15px; display: flex; justify-content: space-between; align-items: flex-start; }
        .info h3 { font-size: 0.9rem; font-weight: 700; margin: 0; max-width: 70%; }
        .price { font-size: 0.9rem; font-weight: 400; }
        
        .description { font-size: 0.75rem; color: #666; margin: 10px 0; text-transform: none; line-height: 1.4; }

        button { 
            width: 100%; background: var(--black); color: var(--white); border: none; 
            padding: 15px; font-weight: 700; cursor: pointer; margin-top: 15px;
            transition: opacity 0.2s; letter-spacing: 1px; font-family: 'Inter', sans-serif;
        }
        button:hover { opacity: 0.8; }

        .error-box { background: #ffeded; color: #d00; padding: 20px; border: 1px solid #d00; margin-bottom: 20px; text-transform: none; }
    </style>
</head>
<body>

<header>
    <h1>STREETWEAR COLLECTION</h1>
    <nav>SHOP / INFO / CART</nav>
</header>

<div class="container">
    
    <?php if ($errorMessage): ?>
        <div class="error-box"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <div class="image-container">
                    <?php if (!empty($product['images'])): ?>
                        <img src="<?php echo htmlspecialchars($product['images'][0]); ?>" alt="Product">
                    <?php else: ?>
                        <div style="display:flex; height:100%; align-items:center; justify-content:center; color:#ccc;">NO IMAGE</div>
                    <?php endif; ?>
                </div>
                
                <div class="info">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <div class="price">
                        <?php 
                        // Check if default_price exists to avoid errors
                        if (isset($product['default_price']['unit_amount'])) {
                            echo '$' . number_format($product['default_price']['unit_amount'] / 100, 2);
                        } else {
                            echo 'PRICE PENDING';
                        }
                        ?>
                    </div>
                </div>
                
                <p class="description"><?php echo htmlspecialchars($product['description'] ?? ''); ?></p>
                
                <form action="checkout.php" method="POST">
                    <input type="hidden" name="price_id" value="<?php echo $product['default_price']['id'] ?? ''; ?>">
                    <button type="submit">BUY NOW</button>
                </form>
            </div>
        <?php endforeach; ?>

        <?php if (empty($products) && !$errorMessage): ?>
            <p>No active products found in your Stripe Dashboard.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>