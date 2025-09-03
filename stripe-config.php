<?php
// STRIPE CONFIGURATION - FORGEMASTER FILLS THESE IN
// Get your keys from: https://dashboard.stripe.com/apikeys

// LIVE KEYS (for production)
$stripe_secret_key = 'sk_live_YOUR_SECRET_KEY_HERE';
$stripe_publishable_key = 'pk_live_YOUR_PUBLISHABLE_KEY_HERE';

// TEST KEYS (for development - comment out for live)
// $stripe_secret_key = 'sk_test_YOUR_TEST_SECRET_KEY_HERE';
// $stripe_publishable_key = 'pk_test_YOUR_TEST_PUBLISHABLE_KEY_HERE';

// Ancient validation
if (strpos($stripe_secret_key, 'YOUR_') !== false) {
    die('⚠️  FORGEMASTER: Please set your Stripe keys in stripe-config.php');
}
?>