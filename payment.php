<?php
// Ancient Stripe Integration - minimal dependencies
require_once 'stripe-config.php'; // You'll add your keys here

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Ancient error handler
function payment_error($message) {
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    payment_error('Only POST requests allowed');
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['order_id']) || !isset($data['amount'])) {
    payment_error('Missing required data');
}

$order_id = $data['order_id'];
$amount = intval($data['amount'] * 100); // Convert to cents for Stripe

// Ancient Stripe API call using cURL (no SDK needed)
function stripe_create_payment_intent($amount, $order_id) {
    global $stripe_secret_key;
    
    $url = 'https://api.stripe.com/v1/payment_intents';
    
    $data = http_build_query([
        'amount' => $amount,
        'currency' => 'usd',
        'metadata' => ['order_id' => $order_id],
        'description' => "Empathic Tee - Order #{$order_id}"
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $stripe_secret_key,
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpcode !== 200) {
        return false;
    }
    
    return json_decode($response, true);
}

// Create payment intent
$payment_intent = stripe_create_payment_intent($amount, $order_id);

if (!$payment_intent || !isset($payment_intent['client_secret'])) {
    payment_error('Failed to create payment');
}

// Log payment creation
file_put_contents('payments.log', 
    date('Y-m-d H:i:s') . " - Payment intent created for order {$order_id}: {$payment_intent['id']}\n", 
    FILE_APPEND | LOCK_EX
);

echo json_encode([
    'success' => true,
    'client_secret' => $payment_intent['client_secret'],
    'payment_intent_id' => $payment_intent['id']
]);
?>