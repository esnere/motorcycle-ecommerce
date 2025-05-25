<?php
$page_title = 'Admin Dashboard';
require_once '../includes/header.php';
require_once '../classes/Order.php';
require_once '../classes/Product.php';
require_once '../classes/User.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('../index.php');
}

$order = new Order($db);
$product = new Product($db);
$user = new User($db);

// Get statistics
$order_stats = $order->getOrderStats();
$total_products = $product->getProductCount();
$total_users = $user->getUserCount();
$recent_orders = $order->getAllOrders(10);

// Calculate revenue for current month
$current_month_revenue = 0;
$current_month = date('Y-m');
foreach ($recent_orders as $order_item) {
    if (date('Y-m', strtotime($order_item['created_at'])) === $current_month && $order_item['payment_status'] === 'paid') {
        $current_month_revenue += $order_item['total_amount'];
    }
}
?>

<div class="container-fluid py-4">
<div class="pt-5"> 
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2">
            <div class="admin-sidebar">
                <div class="list-group list-group-flush">
                    <a href="index.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a href="products.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-box me-2"></i>Products
                    </a>
                    <a href="categories.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tags me-2"></i>Categories
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-cart me-2"></i>Orders
                    </a>
                    <a href="users.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i>Users
                    </a>
                    <a href="reports.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-bar me-2"></i>Reports
                    </a>
                    <a href="settings.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-cog me-2"></i>Settings
                    </a>
                    <hr>
                    <a href="../index.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-home me-2"></i>Back to Site
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Admin Dashboard</h2>
                <div class="text-muted">
                    <i class="fas fa-calendar me-2"></i><?php echo date('F j, Y'); ?>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-shopping-cart fa-2x mb-3"></i>
                            <h4 class="display-6"><?php echo $order_stats['total_orders']; ?></h4>
                            <p class="mb-0">Total Orders</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-peso-sign fa-2x mb-3"></i>
                            <h4 class="display-6"><?php echo formatPrice($order_stats['total_revenue'] ?? 0); ?></h4>
                            <p class="mb-0">Total Revenue</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <i class="fas fa-box fa-2x mb-3"></i>
                            <h4 class="display-6"><?php echo $total_products; ?></h4>
                            <p class="mb-0">Products</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-2x mb-3"></i>
                            <h4 class="display-6"><?php echo $total_users; ?></h4>
                            <p class="mb-0">Users</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Status Overview -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Order Status Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-3">
                                    <div class="mb-2">
                                        <span class="badge bg-warning text-dark fs-6">
                                            <?php echo $order_stats['pending_orders']; ?>
                                        </span>
                                    </div>
                                    <small>Pending</small>
                                </div>
                                <div class="col-3">
                                    <div class="mb-2">
                                        <span class="badge bg-info fs-6">
                                            <?php echo $order_stats['processing_orders']; ?>
                                        </span>
                                    </div>
                                    <small>Processing</small>
                                </div>
                                <div class="col-3">
                                    <div class="mb-2">
                                        <span class="badge bg-primary fs-6">
                                            <?php echo $order_stats['shipped_orders']; ?>
                                        </span>
                                    </div>
                                    <small>Shipped</small>
                                </div>
                                <div class="col-3">
                                    <div class="mb-2">
                                        <span class="badge bg-success fs-6">
                                            <?php echo $order_stats['delivered_orders']; ?>
                                        </span>
                                    </div>
                                    <small>Delivered</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">This Month</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <h6>Revenue</h6>
                                    <h4 class="text-success"><?php echo formatPrice($current_month_revenue); ?></h4>
                                </div>
                                <div class="col-6">
                                    <h6>Avg Order Value</h6>
                                    <h4 class="text-info"><?php echo formatPrice($order_stats['avg_order_value'] ?? 0); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Orders</h5>
                    <a href="orders.php" class="btn btn-primary btn-sm">View All Orders</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_orders)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h6>No orders yet</h6>
                            <p class="text-muted">Orders will appear here once customers start purchasing.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order_item): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($order_item['order_number']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($order_item['first_name'] . ' ' . $order_item['last_name']); ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($order_item['email']); ?></small>
                                        </td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($order_item['created_at'])); ?></td>
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
                                        <td>
                                            <?php
                                            $payment_classes = [
                                                'pending' => 'bg-warning text-dark',
                                                'paid' => 'bg-success',
                                                'failed' => 'bg-danger'
                                            ];
                                            $payment_class = $payment_classes[$order_item['payment_status']] ?? 'bg-secondary';
                                            ?>
                                            <span class="badge <?php echo $payment_class; ?>">
                                                <?php echo ucfirst($order_item['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td class="fw-bold"><?php echo formatPrice($order_item['total_amount']); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="order-details.php?id=<?php echo $order_item['id']; ?>" 
                                                   class="btn btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit-order.php?id=<?php echo $order_item['id']; ?>" 
                                                   class="btn btn-outline-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
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
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
