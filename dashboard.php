<?php
$page_title = 'Dashboard';
require_once 'includes/header.php';
require_once 'classes/Order.php';
require_once 'classes/Cart.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$order = new Order($db);
$cart = new Cart($db);

// Get user statistics
$recent_orders = $order->getUserOrders($_SESSION['user_id'], 5);
$cart_count = $cart->getCartCount($_SESSION['user_id']);
$cart_total = $cart->getCartTotal($_SESSION['user_id']);

// Calculate order statistics
$total_orders = count($order->getUserOrders($_SESSION['user_id']));
$total_spent = 0;
foreach ($order->getUserOrders($_SESSION['user_id']) as $user_order) {
    if ($user_order['payment_status'] === 'paid') {
        $total_spent += $user_order['total_amount'];
    }
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h2>
                    <p class="text-muted mb-0">Manage your account and track your orders</p>
                </div>
                <div>
                    <a href="profile.php" class="btn btn-outline-warning">
                        <i class="fas fa-user-edit me-2"></i>Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card dashboard-card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-shopping-cart fa-2x mb-3"></i>
                    <h4 class="display-6"><?php echo $cart_count; ?></h4>
                    <p class="mb-0">Items in Cart</p>
                    <?php if ($cart_count > 0): ?>
                        <small><?php echo formatPrice($cart_total); ?></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-box fa-2x mb-3"></i>
                    <h4 class="display-6"><?php echo $total_orders; ?></h4>
                    <p class="mb-0">Total Orders</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card bg-warning text-dark">
                <div class="card-body text-center">
                    <i class="fas fa-peso-sign fa-2x mb-3"></i>
                    <h4 class="display-6"><?php echo formatPrice($total_spent); ?></h4>
                    <p class="mb-0">Total Spent</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-star fa-2x mb-3"></i>
                    <h4 class="display-6">Gold</h4>
                    <p class="mb-0">Member Status</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Orders -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Orders</h5>
                    <a href="orders.php" class="btn btn-outline-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_orders)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <h6>No orders yet</h6>
                            <p class="text-muted mb-3">You haven't placed any orders yet.</p>
                            <a href="shop.php" class="btn btn-warning">Start Shopping</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order_item): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($order_item['order_number']); ?></strong>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($order_item['created_at'])); ?></td>
                                        <td>
                                            <?php
                                            $status_classes = [
                                                'pending' => 'bg-warning text-dark',
                                                'processing' => 'bg-info',
                                                'shipped' => 'bg-primary',
                                                'delivered' => 'bg-success',
                                                'cancelled' => 'bg-danger'
                                            ];
                                            $status_class = $status_classes[$order_item['status']] ?? 'bg-secondary';
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>">
                                                <?php echo ucfirst($order_item['status']); ?>
                                            </span>
                                        </td>
                                        <td class="fw-bold"><?php echo formatPrice($order_item['total_amount']); ?></td>
                                        <td>
                                            <a href="order-details.php?id=<?php echo $order_item['id']; ?>" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="shop.php" class="btn btn-warning">
                            <i class="fas fa-shopping-bag me-2"></i>Browse Products
                        </a>
                        <?php if ($cart_count > 0): ?>
                            <a href="cart.php" class="btn btn-outline-warning">
                                <i class="fas fa-shopping-cart me-2"></i>View Cart (<?php echo $cart_count; ?>)
                            </a>
                            <a href="checkout.php" class="btn btn-success">
                                <i class="fas fa-credit-card me-2"></i>Checkout
                            </a>
                        <?php endif; ?>
                        <a href="orders.php" class="btn btn-outline-primary">
                            <i class="fas fa-box me-2"></i>My Orders
                        </a>
                        <a href="profile.php" class="btn btn-outline-secondary">
                            <i class="fas fa-user-edit me-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Account Info -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Email</small>
                        <div><?php echo htmlspecialchars($_SESSION['email']); ?></div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Username</small>
                        <div><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Member Since</small>
                        <div>
                            <?php
                            require_once 'classes/User.php';
                            $user_obj = new User($db);
                            $user_data = $user_obj->getUserById($_SESSION['user_id']);
                            echo date('F Y', strtotime($user_data['created_at']));
                            ?>
                        </div>
                    </div>
                    <?php if (isAdmin()): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-crown me-2"></i>
                            <strong>Administrator Account</strong>
                            <br><small>You have admin privileges</small>
                            <div class="mt-2">
                                <a href="admin/" class="btn btn-info btn-sm">
                                    <i class="fas fa-cogs me-1"></i>Admin Panel
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
