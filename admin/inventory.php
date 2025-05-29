<?php
$page_title = 'Inventory Management';
require_once '../config/config.php';
require_once '../classes/Product.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$product_obj = new Product($db);
$error = '';
$success = '';

// Handle stock updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        if (isset($_POST['update_stock'])) {
            $product_id = (int)$_POST['product_id'];
            $new_stock = (int)$_POST['new_stock'];
            $reason = sanitizeInput($_POST['reason']);
            
            $result = $product_obj->setStock($product_id, $new_stock, $reason);
            if ($result) {
                logAdminAction($_SESSION['user_id'], 'UPDATE', 'products', $product_id, 
                              ['stock' => $new_stock, 'reason' => $reason]);
                $success = 'Stock updated successfully.';
            } else {
                $error = 'Failed to update stock.';
            }
        }
    }
}

// Get filter parameters
$category_filter = $_GET['category'] ?? '';
$stock_filter = $_GET['stock_status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query conditions
$conditions = [];
$params = [];

if ($category_filter) {
    $conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

if ($stock_filter) {
    switch ($stock_filter) {
        case 'low':
            $conditions[] = "p.stock_quantity <= p.low_stock_threshold";
            break;
        case 'out':
            $conditions[] = "p.stock_quantity = 0";
            break;
        case 'in':
            $conditions[] = "p.stock_quantity > 0";
            break;
    }
}

if ($search) {
    $conditions[] = "(p.name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Get products with inventory info
$query = "SELECT p.*, c.name as category_name,
          CASE 
              WHEN p.stock_quantity = 0 THEN 'Out of Stock'
              WHEN p.stock_quantity <= p.low_stock_threshold THEN 'Low Stock'
              ELSE 'In Stock'
          END as stock_status
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          $where_clause
          ORDER BY p.stock_quantity ASC, p.name ASC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$categories = $product_obj->getCategories();

// Get stock statistics
$stats_query = "SELECT 
    COUNT(*) as total_products,
    SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
    SUM(CASE WHEN stock_quantity <= low_stock_threshold AND stock_quantity > 0 THEN 1 ELSE 0 END) as low_stock,
    SUM(CASE WHEN stock_quantity > low_stock_threshold THEN 1 ELSE 0 END) as in_stock,
    SUM(stock_quantity * price) as total_inventory_value
    FROM products";
$stats = $db->query($stats_query)->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-cogs me-2"></i>Admin Panel
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.php">View Site</a>
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Admin Menu</h6>
                    </div>
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
                        <a href="inventory.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-warehouse me-2"></i>Inventory
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
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-warehouse me-2"></i>Inventory Management</h2>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check me-2"></i><?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <!-- Inventory Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6>Total Products</h6>
                                        <h3><?php echo number_format($stats['total_products']); ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-box fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6>In Stock</h6>
                                        <h3><?php echo number_format($stats['in_stock']); ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6>Low Stock</h6>
                                        <h3><?php echo number_format($stats['low_stock']); ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6>Out of Stock</h6>
                                        <h3><?php echo number_format($stats['out_of_stock']); ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-times-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Stock Status</label>
                                <select name="stock_status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="in" <?php echo $stock_filter == 'in' ? 'selected' : ''; ?>>In Stock</option>
                                    <option value="low" <?php echo $stock_filter == 'low' ? 'selected' : ''; ?>>Low Stock</option>
                                    <option value="out" <?php echo $stock_filter == 'out' ? 'selected' : ''; ?>>Out of Stock</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Product name or SKU..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Inventory Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Product Inventory</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th>Category</th>
                                        <th>Current Stock</th>
                                        <th>Low Stock Alert</th>
                                        <th>Status</th>
                                        <th>Value</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo htmlspecialchars($product['image_url'] ?: '/placeholder.svg?height=40&width=40'); ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                     class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($product['name']); ?></div>
                                                    <small class="text-muted"><?php echo formatPrice($product['price']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['sku']); ?></td>
                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <td>
                                            <span class="fw-bold"><?php echo number_format($product['stock_quantity']); ?></span>
                                        </td>
                                        <td><?php echo number_format($product['low_stock_threshold']); ?></td>
                                        <td>
                                            <?php
                                            $status_class = '';
                                            switch ($product['stock_status']) {
                                                case 'In Stock':
                                                    $status_class = 'bg-success';
                                                    break;
                                                case 'Low Stock':
                                                    $status_class = 'bg-warning';
                                                    break;
                                                case 'Out of Stock':
                                                    $status_class = 'bg-danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>">
                                                <?php echo $product['stock_status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatPrice($product['stock_quantity'] * $product['price']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="openStockModal(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['stock_quantity']; ?>)">
                                                <i class="fas fa-edit me-1"></i>Update
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Update Modal -->
    <div class="modal fade" id="stockModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Stock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="product_id" id="modal_product_id">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <input type="text" id="modal_product_name" class="form-control" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Current Stock</label>
                            <input type="text" id="modal_current_stock" class="form-control" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_stock" class="form-label">New Stock Quantity *</label>
                            <input type="number" name="new_stock" id="new_stock" class="form-control" min="0" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Change</label>
                            <select name="reason" id="reason" class="form-select" required>
                                <option value="">Select reason...</option>
                                <option value="stock_received">Stock Received</option>
                                <option value="stock_sold">Stock Sold</option>
                                <option value="stock_damaged">Stock Damaged</option>
                                <option value="stock_returned">Stock Returned</option>
                                <option value="inventory_adjustment">Inventory Adjustment</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_stock" class="btn btn-primary">Update Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function openStockModal(productId, productName, currentStock) {
        document.getElementById('modal_product_id').value = productId;
        document.getElementById('modal_product_name').value = productName;
        document.getElementById('modal_current_stock').value = currentStock;
        document.getElementById('new_stock').value = currentStock;
        
        const modal = new bootstrap.Modal(document.getElementById('stockModal'));
        modal.show();
    }
    </script>
</body>
</html>
