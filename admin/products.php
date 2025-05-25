<?php
$page_title = 'Admin - Products';
require_once '../includes/header.php';
require_once '../classes/Product.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('../index.php');
}

$product = new Product($db);

// Handle actions
$action = $_GET['action'] ?? '';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        switch ($action) {
            case 'add':
                $data = [
                    'name' => sanitizeInput($_POST['name']),
                    'description' => sanitizeInput($_POST['description']),
                    'price' => (float)$_POST['price'],
                    'category_id' => (int)$_POST['category_id'],
                    'brand' => sanitizeInput($_POST['brand']),
                    'model' => sanitizeInput($_POST['model']),
                    'year_from' => $_POST['year_from'] ? (int)$_POST['year_from'] : null,
                    'year_to' => $_POST['year_to'] ? (int)$_POST['year_to'] : null,
                    'stock_quantity' => (int)$_POST['stock_quantity'],
                    'sku' => sanitizeInput($_POST['sku']),
                    'weight' => $_POST['weight'] ? (float)$_POST['weight'] : null,
                    'dimensions' => sanitizeInput($_POST['dimensions']),
                    'featured' => isset($_POST['featured']) ? 1 : 0,
                    'image' => sanitizeInput($_POST['image'])
                ];
                
                $result = $product->createProduct($data);
                if ($result) {
                    logAdminAction($_SESSION['user_id'], 'CREATE', 'products', $result, null, $data);
                    $message = 'Product created successfully!';
                } else {
                    $error = 'Failed to create product.';
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $old_data = $product->getProductById($id);
                
                $data = [
                    'name' => sanitizeInput($_POST['name']),
                    'description' => sanitizeInput($_POST['description']),
                    'price' => (float)$_POST['price'],
                    'category_id' => (int)$_POST['category_id'],
                    'brand' => sanitizeInput($_POST['brand']),
                    'model' => sanitizeInput($_POST['model']),
                    'year_from' => $_POST['year_from'] ? (int)$_POST['year_from'] : null,
                    'year_to' => $_POST['year_to'] ? (int)$_POST['year_to'] : null,
                    'stock_quantity' => (int)$_POST['stock_quantity'],
                    'sku' => sanitizeInput($_POST['sku']),
                    'weight' => $_POST['weight'] ? (float)$_POST['weight'] : null,
                    'dimensions' => sanitizeInput($_POST['dimensions']),
                    'featured' => isset($_POST['featured']) ? 1 : 0,
                    'image' => sanitizeInput($_POST['image'])
                ];
                
                $result = $product->updateProduct($id, $data);
                if ($result) {
                    logAdminAction($_SESSION['user_id'], 'UPDATE', 'products', $id, $old_data, $data);
                    $message = 'Product updated successfully!';
                } else {
                    $error = 'Failed to update product.';
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $old_data = $product->getProductById($id);
                
                $result = $product->deleteProduct($id);
                if ($result) {
                    logAdminAction($_SESSION['user_id'], 'DELETE', 'products', $id, $old_data, null);
                    $message = 'Product deleted successfully!';
                } else {
                    $error = 'Failed to delete product.';
                }
                break;
        }
    }
}

// Get filters
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = ADMIN_ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Get products
$products = $product->getAllProducts($limit, $offset, $category_filter ?: null, $search ?: null);
$total_products = $product->getProductCount($category_filter ?: null, $search ?: null);
$total_pages = ceil($total_products / $limit);

// Get categories for filter
$categories = $product->getCategories();

// Get product for editing
$edit_product = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $edit_product = $product->getProductById((int)$_GET['id']);
}
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
                    <a href="products.php" class="list-group-item list-group-item-action active">
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
            <div class="pt-5"> 
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Product Management</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
                    <i class="fas fa-plus me-2"></i>Add Product
                </button>
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

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search Products</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search by name, brand, or SKU">
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-control" id="category" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="products.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <span class="text-muted">
                                Total: <?php echo $total_products; ?> products
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Products Table -->
            <div class="card">
                <div class="card-body">
                    <?php if (empty($products)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <h5>No products found</h5>
                            <p class="text-muted">No products match your current filters.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $prod): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo $prod['image'] ?: '/placeholder.svg?height=50&width=50'; ?>" 
                                                 class="rounded" alt="Product" style="width: 50px; height: 50px; object-fit: cover;">
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($prod['name']); ?></strong>
                                                <?php if ($prod['featured']): ?>
                                                    <span class="badge bg-warning text-dark ms-1">Featured</span>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($prod['brand']); ?>
                                                <?php if ($prod['model']): ?>
                                                    - <?php echo htmlspecialchars($prod['model']); ?>
                                                <?php endif; ?>
                                            </small>
                                            <br>
                                            <small class="text-muted">SKU: <?php echo htmlspecialchars($prod['sku']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($prod['category_name']); ?></td>
                                        <td class="fw-bold"><?php echo formatPrice($prod['price']); ?></td>
                                        <td>
                                            <?php if ($prod['stock_quantity'] > 0): ?>
                                                <span class="badge bg-success"><?php echo $prod['stock_quantity']; ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($prod['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="../product.php?id=<?php echo $prod['id']; ?>" 
                                                   class="btn btn-outline-info" title="View" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-primary" 
                                                        onclick="editProduct(<?php echo htmlspecialchars(json_encode($prod)); ?>)" 
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="deleteProduct(<?php echo $prod['id']; ?>, '<?php echo htmlspecialchars($prod['name']); ?>')" 
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Products pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>">
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

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalTitle">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="productForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="id" id="productId">
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="productName" class="form-label">Product Name *</label>
                            <input type="text" class="form-control" id="productName" name="name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="productSku" class="form-label">SKU *</label>
                            <input type="text" class="form-control" id="productSku" name="sku" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="productDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="productDescription" name="description" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="productPrice" class="form-label">Price *</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="productPrice" name="price" 
                                       step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="productCategory" class="form-label">Category *</label>
                            <select class="form-control" id="productCategory" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="productStock" class="form-label">Stock Quantity *</label>
                            <input type="number" class="form-control" id="productStock" name="stock_quantity" 
                                   min="0" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="productBrand" class="form-label">Brand</label>
                            <input type="text" class="form-control" id="productBrand" name="brand">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="productModel" class="form-label">Model</label>
                            <input type="text" class="form-control" id="productModel" name="model">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="productWeight" class="form-label">Weight (kg)</label>
                            <input type="number" class="form-control" id="productWeight" name="weight" 
                                   step="0.01" min="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="productYearFrom" class="form-label">Year From</label>
                            <input type="number" class="form-control" id="productYearFrom" name="year_from" 
                                   min="1900" max="2030">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="productYearTo" class="form-label">Year To</label>
                            <input type="number" class="form-control" id="productYearTo" name="year_to" 
                                   min="1900" max="2030">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="productDimensions" class="form-label">Dimensions</label>
                            <input type="text" class="form-control" id="productDimensions" name="dimensions" 
                                   placeholder="L x W x H">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="productImage" class="form-label">Image URL</label>
                        <input type="url" class="form-control" id="productImage" name="image" 
                               placeholder="https://example.com/image.jpg">
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="productFeatured" name="featured">
                            <label class="form-check-label" for="productFeatured">
                                Featured Product
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="productSubmitBtn">
                        <i class="fas fa-save me-2"></i>Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this product?</p>
                <p><strong id="deleteProductName"></strong></p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="id" id="deleteProductId">
                    <button type="submit" name="action" value="delete" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Delete Product
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editProduct(product) {
    document.getElementById('productModalTitle').textContent = 'Edit Product';
    document.getElementById('productSubmitBtn').innerHTML = '<i class="fas fa-save me-2"></i>Update Product';
    document.getElementById('productForm').action = '?action=edit';
    
    // Fill form with product data
    document.getElementById('productId').value = product.id;
    document.getElementById('productName').value = product.name;
    document.getElementById('productSku').value = product.sku;
    document.getElementById('productDescription').value = product.description || '';
    document.getElementById('productPrice').value = product.price;
    document.getElementById('productCategory').value = product.category_id;
    document.getElementById('productStock').value = product.stock_quantity;
    document.getElementById('productBrand').value = product.brand || '';
    document.getElementById('productModel').value = product.model || '';
    document.getElementById('productWeight').value = product.weight || '';
    document.getElementById('productYearFrom').value = product.year_from || '';
    document.getElementById('productYearTo').value = product.year_to || '';
    document.getElementById('productDimensions').value = product.dimensions || '';
    document.getElementById('productImage').value = product.image || '';
    document.getElementById('productFeatured').checked = product.featured == 1;
    
    new bootstrap.Modal(document.getElementById('productModal')).show();
}

function deleteProduct(id, name) {
    document.getElementById('deleteProductId').value = id;
    document.getElementById('deleteProductName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Reset form when modal is closed
document.getElementById('productModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('productModalTitle').textContent = 'Add Product';
    document.getElementById('productSubmitBtn').innerHTML = '<i class="fas fa-save me-2"></i>Save Product';
    document.getElementById('productForm').action = '?action=add';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
});
</script>

<?php require_once '../includes/footer.php'; ?>
