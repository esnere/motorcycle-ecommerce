<?php
$page_title = 'Order Confirmation';
require_once 'includes/header.php';
require_once 'classes/Order.php';

// Check if order parameter exists
if (!isset($_GET['order'])) {
    redirect('index.php');
}

$order_number = sanitizeInput($_GET['order']);
$order_obj = new Order($db);

// Verify that order belongs to current user (if logged in)
$order = $order_obj->getOrderByNumber($order_number);

if (!$order) {
    redirect('index.php');
}

// If user is logged in, verify order belongs to them
if (isLoggedIn() && $order['user_id'] != $_SESSION['user_id']) {
    redirect('index.php');
}

// Get order items
$order_items = $order_obj->getOrderItems($order['id']);
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Message -->
            <div class="text-center mb-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h1 class="h2 text-success mb-3">Order Confirmed!</h1>
                <p class="lead text-muted">Thank you for your order. We've received your order and will process it shortly.</p>
            </div>

            <!-- Order Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt me-2"></i>Order Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Order Number:</strong><br>
                            <span class="text-primary">#<?php echo htmlspecialchars($order['order_number']); ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Order Date:</strong><br>
                            <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Payment Method:</strong><br>
                            <?php 
                            switch($order['payment_method']) {
                                case 'cod':
                                    echo 'Cash on Delivery';
                                    break;
                                case 'bank_transfer':
                                    echo 'Bank Transfer';
                                    break;
                                case 'gcash':
                                    echo 'GCash';
                                    break;
                                default:
                                    echo ucfirst($order['payment_method']);
                            }
                            ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Order Status:</strong><br>
                            <span class="badge bg-warning">
                                <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                            </span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <strong>Shipping Address:</strong><br>
                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Contact Information:</strong><br>
                            <?php echo htmlspecialchars($order['customer_name']); ?><br>
                            <?php echo htmlspecialchars($order['customer_email']); ?><br>
                            <?php echo htmlspecialchars($order['customer_phone']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-bag me-2"></i>Order Items
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo htmlspecialchars($item['image'] ?: '/placeholder.svg?height=50&width=50'); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                 class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                                <small class="text-muted">SKU: <?php echo htmlspecialchars($item['sku']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                                    <td class="text-end"><?php echo formatPrice($item['price']); ?></td>
                                    <td class="text-end"><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td class="text-end"><strong><?php echo formatPrice($order['subtotal']); ?></strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                    <td class="text-end"><strong><?php echo formatPrice($order['shipping_fee']); ?></strong></td>
                                </tr>
                                <tr class="table-primary">
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end"><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Instructions -->
            <?php if ($order['payment_method'] == 'bank_transfer'): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-university me-2"></i>Payment Instructions
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Please transfer the total amount to one of our bank accounts:</strong></p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="border rounded p-3 mb-3">
                                <h6 class="text-primary">BPI Bank</h6>
                                <p class="mb-1"><strong>Account Name:</strong> Classic Motorcycle Parts Philippines</p>
                                <p class="mb-1"><strong>Account Number:</strong> 1234-5678-90</p>
                                <p class="mb-0"><strong>Branch:</strong> Makati City</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 mb-3">
                                <h6 class="text-primary">BDO Bank</h6>
                                <p class="mb-1"><strong>Account Name:</strong> Classic Motorcycle Parts Philippines</p>
                                <p class="mb-1"><strong>Account Number:</strong> 0987-6543-21</p>
                                <p class="mb-0"><strong>Branch:</strong> Quezon City</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> Please send a copy of your deposit slip or transfer receipt to 
                        <strong>orders@classicmotorcycleparts.ph</strong> with your order number 
                        <strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong> for faster processing.
                    </div>
                </div>
            </div>
            <?php elseif ($order['payment_method'] == 'gcash'): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-mobile-alt me-2"></i>GCash Payment Instructions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img src="/placeholder.svg?height=200&width=200" alt="GCash QR Code" class="img-fluid">
                        <p class="mt-2"><strong>Scan QR Code or send to:</strong></p>
                        <h4 class="text-success">0917-123-4567</h4>
                        <p><strong>Account Name:</strong> Classic Motorcycle Parts Philippines</p>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Please send a screenshot of your GCash payment confirmation to 
                        <strong>orders@classicmotorcycleparts.ph</strong> with your order number 
                        <strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong>.
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Next Steps -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list-ol me-2"></i>What's Next?
                    </h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li class="mb-2">
                            <strong>Order Confirmation:</strong> You will receive an email confirmation shortly.
                        </li>
                        <?php if ($order['payment_method'] != 'cod'): ?>
                        <li class="mb-2">
                            <strong>Payment:</strong> Complete your payment using the instructions above.
                        </li>
                        <li class="mb-2">
                            <strong>Payment Verification:</strong> We'll verify your payment within 24 hours.
                        </li>
                        <?php endif; ?>
                        <li class="mb-2">
                            <strong>Processing:</strong> Your order will be prepared for shipping.
                        </li>
                        <li class="mb-2">
                            <strong>Shipping:</strong> You'll receive tracking information once shipped.
                        </li>
                        <li class="mb-0">
                            <strong>Delivery:</strong> Estimated delivery time is 3-7 business days.
                        </li>
                    </ol>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center">
                <a href="shop.php" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-shopping-cart me-2"></i>Continue Shopping
                </a>
                <?php if (isLoggedIn()): ?>
                <a href="orders.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-list me-2"></i>View All Orders
                </a>
                <?php endif; ?>
            </div>

            <!-- Contact Support -->
            <div class="text-center mt-4">
                <p class="text-muted">
                    Need help? <a href="contact.php">Contact our support team</a> or call us at 
                    <strong>(02) 8123-4567</strong>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
