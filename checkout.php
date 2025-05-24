<?php
$page_title = 'Checkout';
require_once 'includes/header.php';
require_once 'classes/Cart.php';
require_once 'classes/Order.php';
require_once 'classes/User.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$cart = new Cart($db);
$cart_items = $cart->getCartItems($_SESSION['user_id']);
$cart_total = $cart->getCartTotal($_SESSION['user_id']);

// Redirect if cart is empty
if (empty($cart_items)) {
    redirect('cart.php');
}

// Validate cart items
$validation_errors = $cart->validateCartItems($_SESSION['user_id']);

$user_obj = new User($db);
$user_data = $user_obj->getUserById($_SESSION['user_id']);

$error = '';
$success = '';

// Process order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } elseif (!empty($validation_errors)) {
        $error = 'Please resolve cart issues before proceeding.';
    } else {
        $shipping_cost = $cart_total >= 5000 ? 0 : 150;
        $total_amount = $cart_total + $shipping_cost;
        
        $order_data = [
            'user_id' => $_SESSION['user_id'],
            'total_amount' => $total_amount,
            'payment_method' => sanitizeInput($_POST['payment_method']),
            'shipping_address' => sanitizeInput($_POST['shipping_address']),
            'billing_address' => sanitizeInput($_POST['billing_address']),
            'notes' => sanitizeInput($_POST['notes'])
        ];
        
        $order_obj = new Order($db);
        $result = $order_obj->createOrder($order_data);
        
        if ($result['success']) {
            // Log admin action if needed
            if (isAdmin()) {
                logAdminAction($_SESSION['user_id'], 'CREATE', 'orders', $result['order_id'], null, $order_data);
            }
            
            redirect('thank-you.php?order=' . $result['order_number']);
        } else {
            $error = $result['message'];
        }
    }
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-credit-card me-2"></i>Checkout</h2>
            
            <?php if (!empty($validation_errors)): ?>
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Please resolve these issues:</h6>
                    <ul class="mb-0">
                        <?php foreach ($validation_errors as $error_msg): ?>
                            <li><?php echo htmlspecialchars($error_msg); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mt-3">
                        <a href="cart.php" class="btn btn-warning">Update Cart</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <form method="POST" class="needs-validation" novalidate>
        <div class="row">
            <div class="col-lg-8">
                <!-- Shipping Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-shipping-fast me-2"></i>Shipping Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" 
                                       value="<?php echo htmlspecialchars($user_data['first_name']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" 
                                       value="<?php echo htmlspecialchars($user_data['last_name']); ?>" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" 
                                   value="<?php echo htmlspecialchars($user_data['email']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" 
                                   value="<?php echo htmlspecialchars($user_data['phone']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">Shipping Address *</label>
                            <textarea class="form-control" id="shipping_address" name="shipping_address" 
                                      rows="3" required><?php echo htmlspecialchars($user_data['address'] . "\n" . $user_data['city'] . ', ' . $user_data['province'] . ' ' . $user_data['postal_code']); ?></textarea>
                            <div class="invalid-feedback">Please provide a shipping address.</div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="same_billing" checked>
                            <label class="form-check-label" for="same_billing">
                                Billing address is the same as shipping address
                            </label>
                        </div>
                        <div id="billing_section" style="display: none;">
                            <div class="mb-3 mt-3">
                                <label for="billing_address" class="form-label">Billing Address</label>
                                <textarea class="form-control" id="billing_address" name="billing_address" 
                                          rows="3"><?php echo htmlspecialchars($user_data['address'] . "\n" . $user_data['city'] . ', ' . $user_data['province'] . ' ' . $user_data['postal_code']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="cod" value="cash_on_delivery" checked>
                                    <label class="form-check-label" for="cod">
                                        <i class="fas fa-money-bill-wave me-2"></i>Cash on Delivery
                                        <small class="text-muted d-block">Pay when you receive your order</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="bank_transfer" value="bank_transfer">
                                    <label class="form-check-label" for="bank_transfer">
                                        <i class="fas fa-university me-2"></i>Bank Transfer
                                        <small class="text-muted d-block">Transfer to our bank account</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div id="bank_details" style="display: none;" class="alert alert-info">
                            <h6>Bank Transfer Details:</h6>
                            <p class="mb-1"><strong>Bank:</strong> BPI</p>
                            <p class="mb-1"><strong>Account Name:</strong> Classic Motorcycle Parts PH</p>
                            <p class="mb-1"><strong>Account Number:</strong> 1234-5678-90</p>
                            <p class="mb-0"><small>Please include your order number in the transfer reference.</small></p>
                        </div>
                    </div>
                </div>

                <!-- Order Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Order Notes (Optional)</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" name="notes" rows="3" 
                                  placeholder="Any special instructions for your order..."></textarea>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Order Summary -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <!-- Cart Items -->
                        <div class="mb-3">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold"><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <hr>

                        <!-- Totals -->
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($cart_total); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span class="text-success">
                                <?php 
                                $shipping_cost = $cart_total >= 5000 ? 0 : 150;
                                echo $shipping_cost > 0 ? formatPrice($shipping_cost) : 'FREE';
                                ?>
                            </span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong class="text-warning fs-5">
                                <?php echo formatPrice($cart_total + $shipping_cost); ?>
                            </strong>
                        </div>

                        <?php if ($cart_total < 5000): ?>
                            <div class="alert alert-info">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    Add <?php echo formatPrice(5000 - $cart_total); ?> more for free shipping!
                                </small>
                            </div>
                        <?php endif; ?>

                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <button type="submit" name="place_order" class="btn btn-warning w-100 btn-lg" 
                                <?php echo !empty($validation_errors) ? 'disabled' : ''; ?>>
                            <i class="fas fa-check me-2"></i>Place Order
                        </button>

                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Your order is secure and protected
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle billing address toggle
    const sameBilling = document.getElementById('same_billing');
    const billingSection = document.getElementById('billing_section');
    const billingAddress = document.getElementById('billing_address');
    const shippingAddress = document.getElementById('shipping_address');

    sameBilling.addEventListener('change', function() {
        if (this.checked) {
            billingSection.style.display = 'none';
            billingAddress.value = shippingAddress.value;
            billingAddress.removeAttribute('required');
        } else {
            billingSection.style.display = 'block';
            billingAddress.setAttribute('required', 'required');
        }
    });

    // Update billing address when shipping address changes
    shippingAddress.addEventListener('input', function() {
        if (sameBilling.checked) {
            billingAddress.value = this.value;
        }
    });

    // Handle payment method selection
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const bankDetails = document.getElementById('bank_details');

    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            if (this.value === 'bank_transfer') {
                bankDetails.style.display = 'block';
            } else {
                bankDetails.style.display = 'none';
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
