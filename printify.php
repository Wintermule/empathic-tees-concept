<?php
// PRINTIFY INTEGRATION - Ancient cURL API calls
require_once 'printify-config.php';

// Ancient Printify API wrapper
class PrintifyAPI {
    private $api_token;
    private $shop_id;
    private $base_url = 'https://api.printify.com/v1/';
    
    public function __construct($api_token, $shop_id) {
        $this->api_token = $api_token;
        $this->shop_id = $shop_id;
    }
    
    // Ancient cURL method
    private function make_request($endpoint, $method = 'GET', $data = null) {
        $url = $this->base_url . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->api_token,
            'Content-Type: application/json'
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpcode >= 400) {
            error_log("Printify API Error: HTTP $httpcode - $response");
            return false;
        }
        
        return json_decode($response, true);
    }
    
    // Create product with custom message
    public function create_custom_tee($message, $design_color = '#ffffff') {
        // Basic t-shirt product template (Printify product ID for basic tee)
        $product_data = [
            'title' => 'Empathic Tee: ' . substr($message, 0, 50),
            'description' => 'Empathic message: ' . $message,
            'blueprint_id' => 145, // Basic t-shirt blueprint
            'print_provider_id' => 1, // GOOTEN
            'variants' => [
                [
                    'id' => 17887, // Size S
                    'price' => 2500, // $25.00 in cents
                    'is_enabled' => true
                ],
                [
                    'id' => 17888, // Size M
                    'price' => 2500,
                    'is_enabled' => true
                ],
                [
                    'id' => 17889, // Size L
                    'price' => 2500,
                    'is_enabled' => true
                ],
                [
                    'id' => 17890, // Size XL
                    'price' => 2500,
                    'is_enabled' => true
                ]
            ],
            'print_areas' => [
                [
                    'variant_ids' => [17887, 17888, 17889, 17890],
                    'placeholders' => [
                        [
                            'position' => 'front',
                            'images' => [
                                [
                                    'id' => 'text_design',
                                    'name' => $message,
                                    'type' => 'text',
                                    'text' => $message,
                                    'font_family' => 'Arial',
                                    'font_size' => 24,
                                    'font_style' => 'normal',
                                    'font_weight' => 'bold',
                                    'color' => '#333333',
                                    'x' => 0.5,
                                    'y' => 0.5,
                                    'width' => 0.8,
                                    'height' => 0.3,
                                    'angle' => 0
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        return $this->make_request("shops/{$this->shop_id}/products.json", 'POST', $product_data);
    }
    
    // Create order
    public function create_order($order_data) {
        $printify_order = [
            'external_id' => $order_data['id'],
            'line_items' => [
                [
                    'product_id' => $order_data['product_id'], // Will be set after product creation
                    'variant_id' => $this->get_variant_id($order_data['size']),
                    'quantity' => 1
                ]
            ],
            'shipping_address' => [
                'first_name' => $order_data['first_name'] ?? 'Customer',
                'last_name' => $order_data['last_name'] ?? '',
                'email' => $order_data['email'],
                'phone' => $order_data['phone'] ?? '',
                'country' => 'US',
                'region' => $order_data['state'] ?? 'CA',
                'address1' => $order_data['address'] ?? '',
                'city' => $order_data['city'] ?? '',
                'zip' => $order_data['zip'] ?? ''
            ]
        ];
        
        return $this->make_request("shops/{$this->shop_id}/orders.json", 'POST', $printify_order);
    }
    
    // Ancient size mapping
    private function get_variant_id($size) {
        $size_map = [
            'S' => 17887,
            'M' => 17888,
            'L' => 17889,
            'XL' => 17890,
            'XXL' => 17891
        ];
        
        return $size_map[strtoupper($size)] ?? 17888; // Default to M
    }
}

// Ancient order processing function
function process_printify_order($order_id) {
    global $printify_api_token, $printify_shop_id;
    
    // Load order from JSON
    $orders = json_decode(file_get_contents('orders.json'), true);
    $order = null;
    
    // Linear search for order (ancient algo)
    foreach ($orders as $o) {
        if ($o['id'] === $order_id) {
            $order = $o;
            break;
        }
    }
    
    if (!$order) {
        error_log("Order not found: $order_id");
        return false;
    }
    
    $printify = new PrintifyAPI($printify_api_token, $printify_shop_id);
    
    // Create custom product
    $product = $printify->create_custom_tee($order['message']);
    
    if (!$product) {
        error_log("Failed to create Printify product for order: $order_id");
        return false;
    }
    
    // Add product ID to order
    $order['product_id'] = $product['id'];
    
    // Create Printify order
    $printify_order = $printify->create_order($order);
    
    if ($printify_order) {
        // Log success
        file_put_contents('fulfillment.log', 
            date('Y-m-d H:i:s') . " - Order {$order_id} sent to Printify: {$printify_order['id']}\n",
            FILE_APPEND | LOCK_EX
        );
        
        // Update order status
        foreach ($orders as &$o) {
            if ($o['id'] === $order_id) {
                $o['printify_order_id'] = $printify_order['id'];
                $o['status'] = 'sent_to_print';
                break;
            }
        }
        
        file_put_contents('orders.json', json_encode($orders, JSON_PRETTY_PRINT));
        
        return true;
    }
    
    return false;
}

// Process order if called directly
if (isset($_POST['order_id'])) {
    header('Content-Type: application/json');
    $result = process_printify_order($_POST['order_id']);
    echo json_encode(['success' => $result]);
}
?>