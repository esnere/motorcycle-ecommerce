<?php
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../classes/Cart.php';
require_once '../classes/Product.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please log in to manage your cart']);
    exit;
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$cart = new Cart($db);
$product = new Product($db);
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if ($product_id <= 0 || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
            exit;
        }
        
        // Check if product exists and has stock
        $product_data = $product->getProductById($product_id);
        if (!$product_data) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }
        
        if ($product_data['stock_quantity'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock available']);
            exit;
        }
        
        $result = $cart->addToCart($_SESSION['user_id'], $product_id, $quantity);
        
        if ($result) {
            $cart_count = $cart->getCartCount($_SESSION['user_id']);
            echo json_encode([
                'success' => true, 
                'message' => 'Product added to cart',
                'cart_count' => $cart_count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add product to cart']);
        }
        break;
        
    case 'update':
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product']);
            exit;
        }
        
        // Check stock availability
        $product_data = $product->getProductById($product_id);
        if (!$product_data) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }
        
        if ($quantity > $product_data['stock_quantity']) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock available']);
            exit;
        }
        
        $result = $cart->updateQuantity($_SESSION['user_id'], $product_id, $quantity);
        
        if ($result) {
            $cart_count = $cart->getCartCount($_SESSION['user_id']);
            $cart_total = $cart->getCartTotal($_SESSION['user_id']);
            echo json_encode([
                'success' => true,
                'message' => 'Cart updated',
                'cart_count' => $cart_count,
                'cart_total' => $cart_total
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
        }
        break;
        
    case 'remove':
        $product_id = (int)($_POST['product_id'] ?? 0);
        
        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product']);
            exit;
        }
        
        $result = $cart->removeFromCart($_SESSION['user_id'], $product_id);
        
        if ($result) {
            $cart_count = $cart->getCartCount($_SESSION['user_id']);
            $cart_total = $cart->getCartTotal($_SESSION['user_id']);
            echo json_encode([
                'success' => true,
                'message' => 'Item removed from cart',
                'cart_count' => $cart_count,
                'cart_total' => $cart_total
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
        }
        break;
        
    case 'clear':
        $result = $cart->clearCart($_SESSION['user_id']);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Cart cleared',
                'cart_count' => 0,
                'cart_total' => 0
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to clear cart']);
        }
        break;
        
    case 'get':
        $cart_items = $cart->getCartItems($_SESSION['user_id']);
        $cart_count = $cart->getCartCount($_SESSION['user_id']);
        $cart_total = $cart->getCartTotal($_SESSION['user_id']);
        
        echo json_encode([
            'success' => true,
            'items' => $cart_items,
            'count' => $cart_count,
            'total' => $cart_total
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
