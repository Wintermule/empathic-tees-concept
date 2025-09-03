<?php
// PRINTIFY CONFIGURATION - FORGEMASTER FILLS THESE IN
// Get your keys from: https://printify.com/app/account/api

// PRINTIFY API TOKEN
$printify_api_token = 'YOUR_PRINTIFY_API_TOKEN_HERE';

// SHOP ID (from your Printify store)
$printify_shop_id = 'YOUR_SHOP_ID_HERE';

// Ancient validation
if (strpos($printify_api_token, 'YOUR_') !== false) {
    die('⚠️  FORGEMASTER: Please set your Printify API credentials in printify-config.php');
}

// PRINTIFY PRODUCT SETTINGS
$default_blueprint_id = 145; // Basic t-shirt
$default_print_provider = 1; // GOOTEN (reliable, fast)

// DESIGN SETTINGS
$text_settings = [
    'font_family' => 'Arial',
    'font_size' => 24,
    'font_weight' => 'bold',
    'text_color' => '#333333',
    'position_x' => 0.5, // Center
    'position_y' => 0.5, // Middle
    'width' => 0.8,      // 80% of shirt width
    'height' => 0.3      // 30% of shirt height
];
?>