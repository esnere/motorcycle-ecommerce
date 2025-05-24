<?php
$page_title = 'Shopping Cart';
require_once 'includes/header.php';
require_once 'classes/Cart.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$cart = new Cart($db);
$cart_items = $cart->getCartItems($_SESSION['user_id']);
$cart_total = $cart->getCartTotal($_SESSION['user_id']);
$cart_count = $cart->getCartCount($_SESSION['user_id']);

// Validate cart items (check stock)
$validation_errors = $cart->validateCartItems($_SESSION['user_id']);
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
                <?php if ($cart_count > 0): ?>
                    <span class="badge bg-warning text-dark"><?php echo $cart_count; ?> items</span>
                <?php endif; ?>
            </h2>

            <?php if (!empty($validation_errors)): ?>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Stock Issues:</h6>
                    <ul class="mb-0">
                        <?php foreach ($validation_errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (empty($cart_items)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                    <h4>Your cart is empty</h4>
                    <p class="text-muted mb-4">Looks like you haven't added any items to your cart yet.</p>
                    <a href="shop.php" class="btn btn-warning btn-lg">
                        <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Cart Items</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php foreach ($cart_items as $item): ?>
                                <div class="cart-item p-4" data-product-id="<?php echo $item['product_id']; ?>">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <img src="<?php echo $item['image'] ?: '/placeholder.svg?height=80&width=80'; ?>" 
                                                 class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                 style="height: 80px; width: 80px; object-fit: cover;">
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="mb-1">
                                                <a href="product.php?id=<?php echo $item['product_id']; ?>" 
                                                   class="text-decoration-none text-dark">
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">SKU: <?php echo htmlspecialchars($item['sku']); ?></small>
                                            <br>
                                            <small class="text-muted">
                                                Stock: <?php echo $item['stock_quantity']; ?> available
                                            </small>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="fw-bold text-warning">
                                                <?php echo formatPrice($item['price']); ?>
                                            </span>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="quantity-controls d-flex align-items-center">
                                                <button type="button" class="btn btn-outline-secondary btn-sm quantity-minus">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" class="form-control form-control-sm quantity-input text-center mx-2" 
                                                       value="<?php echo $item['quantity']; ?>" 
                                                       min="1" max="<?php echo $item['stock_quantity']; ?>"
                                                       data-product-id="<?php echo $item['product_id']; ?>"
                                                       style="width: 70px;">
                                                <button type="button" class="btn btn-outline-secondary btn-sm quantity-plus">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <span class="fw-bold">
                                                <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                            </span>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    onclick="removeFromCart(<?php echo $item['product_id']; ?>)"
                                                    title="Remove item">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mt-3">
                            <a href="shop.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                            </a>
                            <button type="button" class="btn btn-outline-danger ms-2" onclick="clearCart()">
                                <i class="fas fa-trash me-2"></i>Clear Cart
                            </button>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span class="cart-total"><?php echo formatPrice($cart_total); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Shipping:</span>
                                    <span class="text-success">
                                        <?php if ($cart_total >= 5000): ?>
                                            FREE
                                        <?php else: ?>
                                            <?php echo formatPrice(150); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>Total:</strong>
                                    <strong class="text-warning">
                                        <?php 
                                        $shipping = $cart_total >= 5000 ? 0 : 150;
                                        echo formatPrice($cart_total + $shipping); 
                                        ?>
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

                                <a href="checkout.php" class="btn btn-warning w-100 btn-lg">
                                    <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                                </a>

                                <div class="mt-3 text-center">
                                    <small class="text-muted">
                                        <i class="fas fa-shield-alt me-1"></i>
                                        Secure checkout with SSL encryption
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Promo Code -->
                        <div class="card mt-3">
                            <div class="card-body">
                                <h6>Have a promo code?</h6>
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Enter promo code">
                                    <button class="btn btn-outline-secondary" type="button">Apply</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function clearCart() {
    if (!confirm('Are you sure you want to clear your entire cart?')) {
        return;
    }

    fetch('api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=clear&csrf_token=${window.csrfToken}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showNotification(data.message || 'Error clearing cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error clearing cart', 'error');
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
