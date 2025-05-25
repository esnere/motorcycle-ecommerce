<?php
$page_title = 'Admin - Reports';
require_once '../includes/header.php';
require_once '../classes/Order.php';
require_once '../classes/Product.php';
require_once '../classes/User.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('../index.php');
}

$order_obj = new Order($db);
$product_obj = new Product($db);
$user_obj = new User($db);

// Get date range from filters
$date_from = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$date_to = $_GET['date_to'] ?? date('Y-m-d'); // Today
$report_type = $_GET['report_type'] ?? 'overview';

// Sales Analytics
$sales_query = "SELECT 
    DATE(created_at) as date,
    COUNT(*) as orders_count,
    SUM(total_amount) as total_sales,
    AVG(total_amount) as avg_order_value
    FROM orders 
    WHERE DATE(created_at) BETWEEN :date_from AND :date_to 
    AND payment_status = 'paid'
    GROUP BY DATE(created_at)
    ORDER BY date DESC";

$stmt = $db->prepare($sales_query);
$stmt->execute([':date_from' => $date_from, ':date_to' => $date_to]);
$daily_sales = $stmt->fetchAll();

// Top Products
$top_products_query = "SELECT 
    p.name, p.price, p.image,
    SUM(oi.quantity) as total_sold,
    SUM(oi.quantity * oi.price) as total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN :date_from AND :date_to
    AND o.payment_status = 'paid'
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 10";

$stmt = $db->prepare($top_products_query);
$stmt->execute([':date_from' => $date_from, ':date_to' => $date_to]);
$top_products = $stmt->fetchAll();

// Customer Analytics
$customer_stats_query = "SELECT 
    COUNT(DISTINCT u.id) as total_customers,
    COUNT(DISTINCT CASE WHEN o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN u.id END) as active_customers
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    WHERE u.is_admin = 0";

$stmt = $db->prepare($customer_stats_query);
$stmt->execute();
$customer_stats = $stmt->fetch();

// Calculate averages separately to avoid complex subqueries
$avg_stats_query = "SELECT 
    AVG(order_count) as avg_orders_per_customer,
    AVG(total_spent) as avg_spent_per_customer
    FROM (
        SELECT user_id, COUNT(*) as order_count, SUM(total_amount) as total_spent
        FROM orders 
        WHERE payment_status = 'paid'
        GROUP BY user_id
    ) as customer_orders";

$stmt = $db->prepare($avg_stats_query);
$stmt->execute();
$avg_stats = $stmt->fetch();

// Merge the results
$customer_stats['avg_orders_per_customer'] = $avg_stats['avg_orders_per_customer'] ?? 0;
$customer_stats['avg_spent_per_customer'] = $avg_stats['avg_spent_per_customer'] ?? 0;

try {
    // Revenue Summary
    $revenue_summary_query = "SELECT 
        SUM(CASE WHEN DATE(created_at) BETWEEN ? AND ? THEN total_amount ELSE 0 END) as period_revenue,
        SUM(CASE WHEN DATE(created_at) = CURDATE() THEN total_amount ELSE 0 END) as today_revenue,
        SUM(CASE WHEN YEARWEEK(created_at) = YEARWEEK(NOW()) THEN total_amount ELSE 0 END) as week_revenue,
        SUM(CASE WHEN MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) THEN total_amount ELSE 0 END) as month_revenue,
        COUNT(CASE WHEN DATE(created_at) BETWEEN ? AND ? THEN 1 END) as period_orders
        FROM orders 
        WHERE payment_status = 'paid'";

    $stmt = $db->prepare($revenue_summary_query);
    $stmt->execute([$date_from, $date_to, $date_from, $date_to]);
    $revenue_summary = $stmt->fetch();
} catch (PDOException $e) {
    // Set default values if query fails
    $revenue_summary = [
        'period_revenue' => 0,
        'today_revenue' => 0,
        'week_revenue' => 0,
        'month_revenue' => 0,
        'period_orders' => 0
    ];
}

// Low Stock Products
$low_stock_query = "SELECT name, stock_quantity, price, image 
    FROM products 
    WHERE stock_quantity <= 5 AND is_active = 1
    ORDER BY stock_quantity ASC
    LIMIT 10";
$low_stock_products = $db->query($low_stock_query)->fetchAll();

// Calculate totals for charts
$total_period_sales = array_sum(array_column($daily_sales, 'total_sales'));
$total_period_orders = array_sum(array_column($daily_sales, 'orders_count'));
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
                    <a href="orders.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-cart me-2"></i>Orders
                    </a>
                    <a href="users.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i>Users
                    </a>
                    <a href="reports.php" class="list-group-item list-group-item-action active">
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
                <h2>Reports & Analytics</h2>
                <div class="text-muted">
                    <i class="fas fa-calendar me-2"></i><?php echo date('F j, Y'); ?>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="<?php echo $date_from; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="<?php echo $date_to; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="report_type" class="form-label">Report Type</label>
                            <select class="form-control" id="report_type" name="report_type">
                                <option value="overview" <?php echo $report_type === 'overview' ? 'selected' : ''; ?>>Overview</option>
                                <option value="sales" <?php echo $report_type === 'sales' ? 'selected' : ''; ?>>Sales</option>
                                <option value="products" <?php echo $report_type === 'products' ? 'selected' : ''; ?>>Products</option>
                                <option value="customers" <?php echo $report_type === 'customers' ? 'selected' : ''; ?>>Customers</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-chart-line me-1"></i>Generate Report
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="exportReport()">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Revenue Summary Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-peso-sign fa-2x mb-2"></i>
                            <h4><?php echo formatPrice($revenue_summary['period_revenue'] ?? 0); ?></h4>
                            <small>Period Revenue</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                            <h4><?php echo $revenue_summary['period_orders'] ?? 0; ?></h4>
                            <small>Period Orders</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line fa-2x mb-2"></i>
                            <h4><?php echo formatPrice($revenue_summary['today_revenue'] ?? 0); ?></h4>
                            <small>Today's Revenue</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar-week fa-2x mb-2"></i>
                            <h4><?php echo formatPrice($revenue_summary['week_revenue'] ?? 0); ?></h4>
                            <small>This Week</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Sales Chart -->
                <div class="col-md-8 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Daily Sales Trend</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="salesChart" height="100"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Customer Stats -->
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Customer Analytics</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6>Total Customers</h6>
                                <h4 class="text-primary"><?php echo $customer_stats['total_customers'] ?? 0; ?></h4>
                            </div>
                            <div class="mb-3">
                                <h6>Active Customers (30 days)</h6>
                                <h4 class="text-success"><?php echo $customer_stats['active_customers'] ?? 0; ?></h4>
                            </div>
                            <div class="mb-3">
                                <h6>Avg Orders per Customer</h6>
                                <h4 class="text-info"><?php echo number_format($customer_stats['avg_orders_per_customer'] ?? 0, 1); ?></h4>
                            </div>
                            <div>
                                <h6>Avg Spent per Customer</h6>
                                <h4 class="text-warning"><?php echo formatPrice($customer_stats['avg_spent_per_customer'] ?? 0); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Top Products -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Top Selling Products</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($top_products)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No sales data for selected period</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Sold</th>
                                                <th>Revenue</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($top_products as $product): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?php echo $product['image'] ?: '/placeholder.svg?height=30&width=30'; ?>" 
                                                             class="rounded me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                                        <small><?php echo htmlspecialchars($product['name']); ?></small>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-primary"><?php echo $product['total_sold']; ?></span></td>
                                                <td class="fw-bold"><?php echo formatPrice($product['total_revenue']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alert -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Low Stock Alert</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($low_stock_products)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <p class="text-muted">All products are well stocked</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Stock</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($low_stock_products as $product): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?php echo $product['image'] ?: '/placeholder.svg?height=30&width=30'; ?>" 
                                                             class="rounded me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                                        <small><?php echo htmlspecialchars($product['name']); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-danger"><?php echo $product['stock_quantity']; ?></span>
                                                </td>
                                                <td>
                                                    <a href="products.php" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
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
            </div>

            <!-- Daily Sales Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daily Sales Report</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($daily_sales)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No sales data for selected period</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Orders</th>
                                        <th>Total Sales</th>
                                        <th>Avg Order Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($daily_sales as $day): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y', strtotime($day['date'])); ?></td>
                                        <td><span class="badge bg-primary"><?php echo $day['orders_count']; ?></span></td>
                                        <td class="fw-bold"><?php echo formatPrice($day['total_sales']); ?></td>
                                        <td><?php echo formatPrice($day['avg_order_value']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-dark">
                                        <th>Total</th>
                                        <th><span class="badge bg-light text-dark"><?php echo $total_period_orders; ?></span></th>
                                        <th class="fw-bold"><?php echo formatPrice($total_period_sales); ?></th>
                                        <th><?php echo $total_period_orders > 0 ? formatPrice($total_period_sales / $total_period_orders) : formatPrice(0); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Chart
const ctx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [<?php echo "'" . implode("','", array_map(function($day) { return date('M j', strtotime($day['date'])); }, array_reverse($daily_sales))) . "'"; ?>],
        datasets: [{
            label: 'Daily Sales',
            data: [<?php echo implode(',', array_column(array_reverse($daily_sales), 'total_sales')); ?>],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₱' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Sales: ₱' + context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});

function exportReport() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.open('?' + params.toString(), '_blank');
}
</script>

<?php require_once '../includes/footer.php'; ?>
