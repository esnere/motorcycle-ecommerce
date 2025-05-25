<?php
$page_title = 'Admin - Orders';
require_once '../includes/header.php';
require_once '../classes/Order.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('../index.php');
}

$order_obj = new Order($db);

// Handle actions
$action = $_GET['action'] ?? '';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        switch ($action) {
            case 'update_status':
                $order_id = (int)$_POST['order_id'];
                $status = sanitizeInput($_POST['status']);
                $old_data = $order_obj->getOrderById($order_id);
                
                $result = $order_obj->updateOrderStatus($order_id, $status);
                if ($result) {
                    logAdminAction($_SESSION['user_id'], 'UPDATE', 'orders', $order_id, $old_data, ['status' => $status]);
                    $message = 'Order status updated successfully!';
                } else {
                    $error = 'Failed to update order status.';
                }
                break;
                
            case 'update_payment':
                $order_id = (int)$_POST['order_id'];
                $payment_status = sanitizeInput($_POST['payment_status']);
                $old_data = $order_obj->getOrderById($order_id);
                
                $result = $order_obj->updatePaymentStatus($order_id, $payment_status);
                if ($result) {
                    logAdminAction($_SESSION['user_id'], 'UPDATE', 'orders', $order_id, $old_data, ['payment_status' => $payment_status]);
                    $message = 'Payment status updated successfully!';
                } else {
                    $error = 'Failed to update payment status.';
                }
                break;
        }
    }
}

// Get filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$payment_filter = $_GET['payment'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = ADMIN_ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Build query
$where_conditions = ['1=1'];
$params = [];

if ($search) {
    $where_conditions[] = "(o.order_number LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($status_filter) {
    $where_conditions[] = "o.status = :status_filter";
    $params[':status_filter'] = $status_filter;
}

if ($payment_filter) {
    $where_conditions[] = "o.payment_status = :payment_filter";
    $params[':payment_filter'] = $payment_filter;
}

if ($date_from) {
    $where_conditions[] = "DATE(o.created_at) >= :date_from";
    $params[':date_from'] = $date_from;
}

if ($date_to) {
    $where_conditions[] = "DATE(o.created_at) <= :date_to";
    $params[':date_to'] = $date_to;
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Get orders
$query = "SELECT o.*, u.first_name, u.last_name, u.email 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          $where_clause 
          ORDER BY o.created_at DESC 
          LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll();

// Get total count
$count_query = "SELECT COUNT(*) as total FROM orders o JOIN users u ON o.user_id = u.id $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_orders = $count_stmt->fetch()['total'];
$total_pages = ceil($total_orders / $limit);

// Get order statistics
$stats = $order_obj->getOrderStats();
?>

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
        <div class="pt-5"> 
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Order Management</h2>
                <div class="text-muted">
                    <i class="fas fa-shopping-cart me-2"></i><?php echo $total_orders; ?> orders found
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                            <h4><?php echo $stats['total_orders']; ?></h4>
                            <small>Total Orders</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <h4><?php echo $stats['pending_orders']; ?></h4>
                            <small>Pending Orders</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-cog fa-2x mb-2"></i>
                            <h4><?php echo $stats['processing_orders']; ?></h4>
                            <small>Processing</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-check fa-2x mb-2"></i>
                            <h4><?php echo $stats['delivered_orders']; ?></h4>
                            <small>Delivered</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search Orders</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Order #, customer name, email">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="payment" class="form-label">Payment</label>
                            <select class="form-control" id="payment" name="payment">
                                <option value="">All Payment</option>
                                <option value="pending" <?php echo $payment_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="paid" <?php echo $payment_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="failed" <?php echo $payment_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="<?php echo htmlspecialchars($date_from); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="<?php echo htmlspecialchars($date_to); ?>">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                    <div class="mt-2">
                        <a href="orders.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times me-1"></i>Clear Filters
                        </a>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="card">
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5>No orders found</h5>
                            <p class="text-muted">No orders match your current filters.</p>
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
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                            <br><small class="text-muted">#<?php echo $order['id']; ?></small>
                                        </td>
                                        <td>
                                            <div><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                        </td>
                                        <td>
                                            <div><?php echo date('M j, Y', strtotime($order['created_at'])); ?></div>
                                            <small class="text-muted"><?php echo date('g:i A', strtotime($order['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm" 
                                                    onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm" 
                                                    onchange="updatePaymentStatus(<?php echo $order['id']; ?>, this.value)">
                                                <option value="pending" <?php echo $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                <option value="failed" <?php echo $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                            </select>
                                        </td>
                                        <td class="fw-bold"><?php echo formatPrice($order['total_amount']); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="order-details.php?id=<?php echo $order['id']; ?>" 
                                                   class="btn btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-info" 
                                                        onclick="printOrder(<?php echo $order['id']; ?>)" title="Print">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary dropdown-toggle" 
                                                        data-bs-toggle="dropdown" title="More Actions">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="order-details.php?id=<?php echo $order['id']; ?>">
                                                        <i class="fas fa-eye me-2"></i>View Details</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="sendOrderEmail(<?php echo $order['id']; ?>)">
                                                        <i class="fas fa-envelope me-2"></i>Send Email</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                                        <i class="fas fa-times me-2"></i>Cancel Order</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Orders pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&payment=<?php echo $payment_filter; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&payment=<?php echo $payment_filter; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&payment=<?php echo $payment_filter; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateOrderStatus(orderId, status) {
    if (confirm('Are you sure you want to update this order status?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?action=update_status';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = 'csrf_token';
        csrfToken.value = '<?php echo $csrf_token; ?>';
        
        const orderIdInput = document.createElement('input');
        orderIdInput.type = 'hidden';
        orderIdInput.name = 'order_id';
        orderIdInput.value = orderId;
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = status;
        
        form.appendChild(csrfToken);
        form.appendChild(orderIdInput);
        form.appendChild(statusInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function updatePaymentStatus(orderId, paymentStatus) {
    if (confirm('Are you sure you want to update this payment status?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?action=update_payment';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = 'csrf_token';
        csrfToken.value = '<?php echo $csrf_token; ?>';
        
        const orderIdInput = document.createElement('input');
        orderIdInput.type = 'hidden';
        orderIdInput.name = 'order_id';
        orderIdInput.value = orderId;
        
        const paymentInput = document.createElement('input');
        paymentInput.type = 'hidden';
        paymentInput.name = 'payment_status';
        paymentInput.value = paymentStatus;
        
        form.appendChild(csrfToken);
        form.appendChild(orderIdInput);
        form.appendChild(paymentInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function printOrder(orderId) {
    window.open('order-details.php?id=' + orderId + '&print=1', '_blank');
}

function sendOrderEmail(orderId) {
    if (confirm('Send order confirmation email to customer?')) {
        // Implementation for sending email
        alert('Email functionality would be implemented here');
    }
}

function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
        updateOrderStatus(orderId, 'cancelled');
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
