<?php
$page_title = 'Admin - Order Details';
require_once '../includes/header.php';
require_once '../classes/Order.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('../index.php');
}

$order_id = (int)($_GET['id'] ?? 0);
if (!$order_id) {
    redirect('orders.php');
}

$order_obj = new Order($db);
$order = $order_obj->getOrderById($order_id);
$order_items = $order_obj->getOrderItems($order_id);

if (!$order) {
    redirect('orders.php');
}

$is_print = isset($_GET['print']);
?>

<?php if ($is_print): ?>
<!DOCTYPE html>
<html>
<head>
    <title>Order #<?php echo htmlspecialchars($order['order_number']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .order-info { margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f5f5f5; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
    </style>
</head>
<body>
    <div class="header">
    <div class="pt-5"> 
        <h1><?php echo SITE_NAME; ?></h1>
        <h2>Order Invoice</h2>
    </div>
<?php else: ?>

<div class="container-fluid py-4">
<div class="pt-5"> 
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2">
            <div class="admin-sidebar">
                <div class="list-group list-group-flush">
                    <a href="index.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a href="products.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-box me-2"></i>Products
                    </a>
                    <a href="categories.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tags me-2"></i>Categories
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action active">
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
                <div>
                    <h2>Order Details</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="orders.php">Orders</a></li>
                            <li class="breadcrumb-item active"><?php echo htmlspecialchars($order['order_number']); ?></li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="orders.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Back to Orders
                    </a>
                    <a href="?id=<?php echo $order_id; ?>&print=1" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-print me-2"></i>Print Invoice
                    </a>
                </div>
            </div>
<?php endif; ?>

            <!-- Order Header -->
            <div class="<?php echo $is_print ? 'order-info' : 'card mb-4'; ?>">
                <?php if (!$is_print): ?><div class="card-body"><?php endif; ?>
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Order #<?php echo htmlspecialchars($order['order_number']); ?></h4>
                            <p class="mb-1"><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                            <p class="mb-1"><strong>Payment Method:</strong> <?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></p>
                            <p class="mb-0">
                                <strong>Status:</strong> 
                                <?php if (!$is_print): ?>
                                    <?php
                                    $status_classes = [
                                        'pending' => 'bg-warning text-dark',
                                        'processing' => 'bg-info',
                                        'shipped' => 'bg-primary',
                                        'delivered' => 'bg-success',
                                        'cancelled' => 'bg-danger'
                                    ];
                                    $status_class = $status_classes[$order['status']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                <?php else: ?>
                                    <?php echo ucfirst($order['status']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5>Customer Information</h5>
                            <p class="mb-1"><strong><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong></p>
                            <p class="mb-1"><?php echo htmlspecialchars($order['email']); ?></p>
                            <?php if (!$is_print): ?>
                                <p class="mb-0">
                                    <strong>Payment Status:</strong> 
                                    <?php
                                    $payment_classes = [
                                        'pending' => 'bg-warning text-dark',
                                        'paid' => 'bg-success',
                                        'failed' => 'bg-danger'
                                    ];
                                    $payment_class = $payment_classes[$order['payment_status']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo $payment_class; ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </p>
                            <?php else: ?>
                                <p class="mb-0"><strong>Payment Status:</strong> <?php echo ucfirst($order['payment_status']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php if (!$is_print): ?></div><?php endif; ?>
            </div>

            <!-- Addresses -->
            <div class="<?php echo $is_print ? 'order-info' : 'row mb-4'; ?>">
                <?php if ($is_print): ?>
                    <div style="display: flex; justify-content: space-between;">
                        <div style="width: 48%;">
                            <h5>Shipping Address</h5>
                            <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                        </div>
                        <div style="width: 48%;">
                            <h5>Billing Address</h5>
                            <p><?php echo nl2br(htmlspecialchars($order['billing_address'])); ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-shipping-fast me-2"></i>Shipping Address</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Billing Address</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['billing_address'])); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Order Items -->
            <div class="<?php echo $is_print ? '' : 'card mb-4'; ?>">
                <?php if (!$is_print): ?>
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Order Items</h5>
                    </div>
                    <div class="card-body">
                <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table <?php echo $is_print ? '' : 'table-hover'; ?>">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $subtotal = 0;
                                foreach ($order_items as $item): 
                                    $item_total = $item['price'] * $item['quantity'];
                                    $subtotal += $item_total;
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!$is_print && $item['image']): ?>
                                                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                                     class="me-3 rounded" alt="Product" 
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['sku']); ?></td>
                                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                                    <td class="text-end"><?php echo formatPrice($item['price']); ?></td>
                                    <td class="text-end"><?php echo formatPrice($item_total); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                    <td class="text-end"><strong><?php echo formatPrice($subtotal); ?></strong></td>
                                </tr>
                                <?php 
                                $shipping = $order['total_amount'] - $subtotal;
                                if ($shipping > 0): 
                                ?>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Shipping:</strong></td>
                                    <td class="text-end"><strong><?php echo formatPrice($shipping); ?></strong></td>
                                </tr>
                                <?php endif; ?>
                                <tr class="<?php echo $is_print ? 'total-row' : 'table-warning'; ?>">
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end"><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php if (!$is_print): ?></div><?php endif; ?>
            </div>

            <?php if ($order['notes'] && !$is_print): ?>
            <!-- Order Notes -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Order Notes</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                </div>
            </div>
            <?php endif; ?>

<?php if ($is_print): ?>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
<?php else: ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
<?php endif; ?>
