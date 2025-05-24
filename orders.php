<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Order.php';
require_once 'classes/User.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);
$user = new User($db);

$user_data = $user->getUserById($_SESSION['user_id']);
$user_orders = $order->getUserOrders($_SESSION['user_id']);

$page_title = "My Orders";
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-shopping-bag"></i> My Orders</h2>
                <a href="shop.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Continue Shopping
                </a>
            </div>

            <?php if (empty($user_orders)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                        <h4>No Orders Yet</h4>
                        <p class="text-muted">You haven't placed any orders yet. Start shopping to see your orders here!</p>
                        <a href="shop.php" class="btn btn-primary">
                            <i class="fas fa-shopping-cart"></i> Start Shopping
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($user_orders as $order_item): ?>
                        <div class="col-md-12 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <strong>Order #<?php echo $order_item['id']; ?></strong>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">
                                                <?php echo date('M d, Y', strtotime($order_item['created_at'])); ?>
                                            </small>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="badge badge-<?php 
                                                echo $order_item['status'] == 'delivered' ? 'success' : 
                                                    ($order_item['status'] == 'shipped' ? 'info' : 
                                                    ($order_item['status'] == 'processing' ? 'warning' : 'secondary')); 
                                            ?>">
                                                <?php echo ucfirst($order_item['status']); ?>
                                            </span>
                                        </div>
                                        <div class="col-md-3 text-right">
                                            <strong>₱<?php echo number_format($order_item['total_amount'], 2); ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php 
                                    $order_details = $order->getOrderDetails($order_item['id']);
                                    ?>
                                    <div class="row">
                                        <?php foreach ($order_details as $detail): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="d-flex">
                                                    <img src="<?php echo $detail['image_url'] ?: '/placeholder.svg?height=60&width=60'; ?>" 
                                                         alt="<?php echo htmlspecialchars($detail['product_name']); ?>" 
                                                         class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($detail['product_name']); ?></h6>
                                                        <small class="text-muted">
                                                            Qty: <?php echo $detail['quantity']; ?> × 
                                                            ₱<?php echo number_format($detail['price'], 2); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <h6>Shipping Address:</h6>
                                            <p class="mb-0">
                                                <?php echo htmlspecialchars($order_item['shipping_address']); ?><br>
                                                <?php echo htmlspecialchars($order_item['shipping_city']); ?>, 
                                                <?php echo htmlspecialchars($order_item['shipping_province']); ?> 
                                                <?php echo htmlspecialchars($order_item['shipping_postal_code']); ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Payment Status:</h6>
                                            <span class="badge badge-<?php echo $order_item['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($order_item['payment_status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php if ($order_item['status'] == 'pending'): ?>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i> Order is being processed
                                                </small>
                                            <?php elseif ($order_item['status'] == 'processing'): ?>
                                                <small class="text-muted">
                                                    <i class="fas fa-cog"></i> Order is being prepared
                                                </small>
                                            <?php elseif ($order_item['status'] == 'shipped'): ?>
                                                <small class="text-muted">
                                                    <i class="fas fa-truck"></i> Order has been shipped
                                                </small>
                                            <?php elseif ($order_item['status'] == 'delivered'): ?>
                                                <small class="text-success">
                                                    <i class="fas fa-check-circle"></i> Order delivered
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="order-details.php?id=<?php echo $order_item['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View Details
                                            </a>
                                            <?php if ($order_item['status'] == 'delivered'): ?>
                                                <button class="btn btn-sm btn-outline-secondary ml-2" 
                                                        onclick="reorderItems(<?php echo $order_item['id']; ?>)">
                                                    <i class="fas fa-redo"></i> Reorder
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function reorderItems(orderId) {
    if (confirm('Add all items from this order to your cart?')) {
        fetch('api/reorder.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: orderId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Items added to cart successfully!');
                window.location.href = 'cart.php';
            } else {
                alert('Error adding items to cart: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding items to cart.');
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>
