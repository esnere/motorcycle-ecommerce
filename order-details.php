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

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);
$user = new User($db);

$order_id = (int)$_GET['id'];
$order_data = $order->getOrderById($order_id);

// Check if order exists and belongs to current user
if (!$order_data || $order_data['user_id'] != $_SESSION['user_id']) {
    header('Location: orders.php');
    exit();
}

$order_details = $order->getOrderDetails($order_id);
$user_data = $user->getUserById($_SESSION['user_id']);

$page_title = "Order Details - #" . $order_id;
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Order Details</h2>
                    <p class="text-muted mb-0">Order #<?php echo $order_id; ?> placed on <?php echo date('F j, Y', strtotime($order_data['created_at'])); ?></p>
                </div>
                <div>
                    <a href="orders.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Orders
                    </a>
                    <button onclick="window.print()" class="btn btn-outline-primary">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>

            <div class="row">
                <!-- Order Status -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <h6>Order Status</h6>
                                    <span class="badge badge-<?php 
                                        echo $order_data['status'] == 'delivered' ? 'success' : 
                                            ($order_data['status'] == 'shipped' ? 'info' : 
                                            ($order_data['status'] == 'processing' ? 'warning' : 'secondary')); 
                                    ?> p-2">
                                        <?php echo ucfirst($order_data['status']); ?>
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <h6>Payment Status</h6>
                                    <span class="badge badge-<?php echo $order_data['payment_status'] == 'paid' ? 'success' : 'warning'; ?> p-2">
                                        <?php echo ucfirst($order_data['payment_status']); ?>
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <h6>Payment Method</h6>
                                    <p class="mb-0"><?php echo ucfirst($order_data['payment_method']); ?></p>
                                </div>
                                <div class="col-md-3">
                                    <h6>Total Amount</h6>
                                    <h4 class="text-primary mb-0">₱<?php echo number_format($order_data['total_amount'], 2); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Order Items</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($order_details as $item): ?>
                                <div class="row align-items-center mb-3 pb-3 border-bottom">
                                    <div class="col-md-2">
                                        <img src="<?php echo $item['image_url'] ?: '/placeholder.svg?height=80&width=80'; ?>" 
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                             class="img-fluid rounded">
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                        <p class="text-muted mb-0"><?php echo htmlspecialchars($item['description']); ?></p>
                                        <small class="text-muted">SKU: <?php echo htmlspecialchars($item['sku']); ?></small>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <span class="badge badge-secondary">Qty: <?php echo $item['quantity']; ?></span>
                                    </div>
                                    <div class="col-md-2 text-right">
                                        <p class="mb-0">₱<?php echo number_format($item['price'], 2); ?></p>
                                        <small class="text-muted">each</small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- Order Summary -->
                            <div class="row mt-4">
                                <div class="col-md-6 offset-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Subtotal:</td>
                                            <td class="text-right">₱<?php echo number_format($order_data['total_amount'] - 100, 2); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Shipping:</td>
                                            <td class="text-right">₱100.00</td>
                                        </tr>
                                        <tr class="font-weight-bold">
                                            <td>Total:</td>
                                            <td class="text-right">₱<?php echo number_format($order_data['total_amount'], 2); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping & Billing Info -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-truck"></i> Shipping Address</h6>
                        </div>
                        <div class="card-body">
                            <address class="mb-0">
                                <strong><?php echo htmlspecialchars($order_data['shipping_name']); ?></strong><br>
                                <?php echo htmlspecialchars($order_data['shipping_address']); ?><br>
                                <?php echo htmlspecialchars($order_data['shipping_city']); ?>, 
                                <?php echo htmlspecialchars($order_data['shipping_province']); ?> 
                                <?php echo htmlspecialchars($order_data['shipping_postal_code']); ?><br>
                                <?php if ($order_data['shipping_phone']): ?>
                                    <abbr title="Phone">P:</abbr> <?php echo htmlspecialchars($order_data['shipping_phone']); ?>
                                <?php endif; ?>
                            </address>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-credit-card"></i> Billing Information</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>Payment Method:</strong> <?php echo ucfirst($order_data['payment_method']); ?></p>
                            <p><strong>Payment Status:</strong> 
                                <span class="badge badge-<?php echo $order_data['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($order_data['payment_status']); ?>
                                </span>
                            </p>
                            <?php if ($order_data['notes']): ?>
                                <p><strong>Order Notes:</strong><br>
                                <?php echo nl2br(htmlspecialchars($order_data['notes'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .card-header, nav, footer {
        display: none !important;
    }
    .container {
        max-width: 100% !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
