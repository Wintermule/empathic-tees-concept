<?php
// Ancient PHP - no frameworks, pure simplicity
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Ancient error handling
function ancient_error($message) {
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

// Ancient logging
function ancient_log($message) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents('orders.log', "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ancient_error('Only POST requests allowed');
}

// Ancient input validation
$input = file_get_contents('php://input');
if (empty($input)) {
    ancient_error('No input data');
}

$order = json_decode($input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    ancient_error('Invalid JSON');
}

// Ancient validation - check required fields
$required_fields = ['product', 'message', 'size', 'email', 'price'];
foreach ($required_fields as $field) {
    if (empty($order[$field])) {
        ancient_error("Missing required field: $field");
    }
}

// Ancient email validation
if (!filter_var($order['email'], FILTER_VALIDATE_EMAIL)) {
    ancient_error('Invalid email format');
}

// Ancient size validation
$valid_sizes = ['S', 'M', 'L', 'XL', 'XXL'];
if (!in_array(strtoupper($order['size']), $valid_sizes)) {
    ancient_error('Invalid size');
}

// Ancient price validation
if (!is_numeric($order['price']) || $order['price'] < 1 || $order['price'] > 100) {
    ancient_error('Invalid price');
}

// Ancient order ID generation
$order['id'] = time() . '_' . substr(md5($order['email']), 0, 8);
$order['timestamp'] = date('Y-m-d H:i:s');
$order['status'] = 'pending';

// Ancient file-based storage - append to orders.json
$orders_file = 'orders.json';
$orders = [];

// Read existing orders if file exists
if (file_exists($orders_file)) {
    $existing_data = file_get_contents($orders_file);
    if (!empty($existing_data)) {
        $orders = json_decode($existing_data, true);
        if (!is_array($orders)) {
            $orders = [];
        }
    }
}

// Add new order
$orders[] = $order;

// Ancient atomic file write
$temp_file = $orders_file . '.tmp';
if (file_put_contents($temp_file, json_encode($orders, JSON_PRETTY_PRINT), LOCK_EX) === false) {
    ancient_error('Failed to save order');
}

if (!rename($temp_file, $orders_file)) {
    unlink($temp_file);
    ancient_error('Failed to finalize order');
}

// Ancient logging
ancient_log("Order created: {$order['id']} - {$order['product']} - {$order['email']}");

// Send confirmation email (ancient style)
$to = $order['email'];
$subject = "Order Confirmation #{$order['id']} - Empathic Tees";
$headers = "From: orders@empathictees.com\r\n";
$headers .= "Reply-To: support@empathictees.com\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$message_body = "
EMPATHIC TEES - ORDER CONFIRMATION

Order ID: {$order['id']}
Product: {$order['product']}
Message: \"{$order['message']}\"
Size: {$order['size']}
Price: \${$order['price']}

Your empathic message will spread gentleness into the world.

NO GODS • NO MASTERS • EMPATHIC REBELS

Processing will begin immediately.
You'll receive shipping updates soon.

Thank you for supporting software for humans, not capital.

---
This order helps fund the Gentree Humanity Fund.
";

// Ancient mail function
@mail($to, $subject, $message_body, $headers);

// Success response
echo json_encode([
    'success' => true,
    'order_id' => $order['id'],
    'message' => 'Order received and processing'
]);

ancient_log("Order confirmed: {$order['id']}");
?>