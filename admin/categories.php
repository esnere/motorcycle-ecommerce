<?php
$page_title = 'Admin - Categories';
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
                    'image' => sanitizeInput($_POST['image'])
                ];
                
                $query = "INSERT INTO categories (name, description, image) VALUES (:name, :description, :image)";
                $stmt = $db->prepare($query);
                $result = $stmt->execute($data);
                
                if ($result) {
                    logAdminAction($_SESSION['user_id'], 'CREATE', 'categories', $db->lastInsertId(), null, $data);
                    $message = 'Category created successfully!';
                } else {
                    $error = 'Failed to create category.';
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $old_data = $product->getCategoryById($id);
                
                $data = [
                    'name' => sanitizeInput($_POST['name']),
                    'description' => sanitizeInput($_POST['description']),
                    'image' => sanitizeInput($_POST['image'])
                ];
                
                $query = "UPDATE categories SET name = :name, description = :description, image = :image WHERE id = :id";
                $stmt = $db->prepare($query);
                $result = $stmt->execute(array_merge($data, ['id' => $id]));
                
                if ($result) {
                    logAdminAction($_SESSION['user_id'], 'UPDATE', 'categories', $id, $old_data, $data);
                    $message = 'Category updated successfully!';
                } else {
                    $error = 'Failed to update category.';
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $old_data = $product->getCategoryById($id);
                
                // Check if category has products
                $product_count = $product->getProductCount($id);
                if ($product_count > 0) {
                    $error = "Cannot delete category. It contains $product_count products.";
                } else {
                    $query = "UPDATE categories SET is_active = 0 WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $result = $stmt->execute(['id' => $id]);
                    
                    if ($result) {
                        logAdminAction($_SESSION['user_id'], 'DELETE', 'categories', $id, $old_data, null);
                        $message = 'Category deleted successfully!';
                    } else {
                        $error = 'Failed to delete category.';
                    }
                }
                break;
        }
    }
}

// Get categories
$categories = $product->getCategories();

// Get category statistics
$category_stats = [];
foreach ($categories as $category) {
    $category_stats[$category['id']] = $product->getProductCount($category['id']);
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
                    <a href="products.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-box me-2"></i>Products
                    </a>
                    <a href="categories.php" class="list-group-item list-group-item-action active">
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
                <h2>Category Management</h2>
                <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#categoryModal">
                    <i class="fas fa-plus me-2"></i>Add Category
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

            <!-- Categories Grid -->
            <div class="row g-4">
                <?php foreach ($categories as $category): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <?php if ($category['image']): ?>
                            <img src="<?php echo htmlspecialchars($category['image']); ?>" 
                                 class="card-img-top" alt="Category" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 200px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h5>
                            <p class="card-text flex-grow-1">
                                <?php echo htmlspecialchars($category['description']); ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-primary">
                                    <?php echo $category_stats[$category['id']]; ?> products
                                </span>
                                <div class="btn-group btn-group-sm">
                                    <a href="../shop.php?category=<?php echo $category['id']; ?>" 
                                       class="btn btn-outline-info" title="View" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)" 
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>', <?php echo $category_stats[$category['id']]; ?>)" 
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalTitle">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoryForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="id" id="categoryId">
                    
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="categoryName" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="categoryDescription" name="description" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="categoryImage" class="form-label">Image URL</label>
                        <input type="url" class="form-control" id="categoryImage" name="image" 
                               placeholder="https://example.com/image.jpg">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="categorySubmitBtn">
                        <i class="fas fa-save me-2"></i>Save Category
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
                <p>Are you sure you want to delete this category?</p>
                <p><strong id="deleteCategoryName"></strong></p>
                <p id="deleteWarning" class="text-danger" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This category contains products and cannot be deleted.
                </p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" style="display: inline;" id="deleteForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="id" id="deleteCategoryId">
                    <button type="submit" name="action" value="delete" class="btn btn-danger" id="deleteBtn">
                        <i class="fas fa-trash me-2"></i>Delete Category
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editCategory(category) {
    document.getElementById('categoryModalTitle').textContent = 'Edit Category';
    document.getElementById('categorySubmitBtn').innerHTML = '<i class="fas fa-save me-2"></i>Update Category';
    document.getElementById('categoryForm').action = '?action=edit';
    
    // Fill form with category data
    document.getElementById('categoryId').value = category.id;
    document.getElementById('categoryName').value = category.name;
    document.getElementById('categoryDescription').value = category.description || '';
    document.getElementById('categoryImage').value = category.image || '';
    
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

function deleteCategory(id, name, productCount) {
    document.getElementById('deleteCategoryId').value = id;
    document.getElementById('deleteCategoryName').textContent = name;
    
    const deleteWarning = document.getElementById('deleteWarning');
    const deleteBtn = document.getElementById('deleteBtn');
    
    if (productCount > 0) {
        deleteWarning.style.display = 'block';
        deleteBtn.disabled = true;
        deleteBtn.textContent = 'Cannot Delete';
    } else {
        deleteWarning.style.display = 'none';
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = '<i class="fas fa-trash me-2"></i>Delete Category';
    }
    
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Reset form when modal is closed
document.getElementById('categoryModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('categoryModalTitle').textContent = 'Add Category';
    document.getElementById('categorySubmitBtn').innerHTML = '<i class="fas fa-save me-2"></i>Save Category';
    document.getElementById('categoryForm').action = '?action=add';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
});
</script>

<?php require_once '../includes/footer.php'; ?>
