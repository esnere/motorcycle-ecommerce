<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Order.php';
require_once '../classes/Cart.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['order_id']) || !is_numeric($input['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);
$cart = new Cart($db);

$order_id = (int)$input['order_id'];
$order_data = $order->getOrderById($order_id);

// Check if order exists and belongs to current user
if (!$order_data || $order_data['user_id'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit();
}

// Get order details
$order_details = $order->getOrderDetails($order_id);

try {
    // Add each item to cart
    foreach ($order_details as $item) {
        $cart->addToCart($_SESSION['user_id'], $item['product_id'], $item['quantity']);
    }
    
    echo json_encode(['success' => true, 'message' => 'Items added to cart successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to add items to cart']);
}
?>
