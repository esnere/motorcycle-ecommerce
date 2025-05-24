<?php
$page_title = 'Shop';
require_once 'includes/header.php';
require_once 'classes/Product.php';

$product = new Product($db);

// Get filters
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : null;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'newest';

// Pagination
$limit = PRODUCTS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Get products and total count
$products = $product->getAllProducts($limit, $offset, $category_id, $search);
$total_products = $product->getProductCount($category_id, $search);
$total_pages = ceil($total_products / $limit);

// Get categories for filter
$categories = $product->getCategories();
$current_category = $category_id ? $product->getCategoryById($category_id) : null;

// Build query string for pagination
$query_params = [];
if ($category_id) $query_params['category'] = $category_id;
if ($search) $query_params['search'] = $search;
if ($sort !== 'newest') $query_params['sort'] = $sort;
$query_string = http_build_query($query_params);
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
                </div>
                <div class="card-body">
                    <!-- Search -->
                    <form method="GET" class="mb-4" id="searchForm">
                        <?php if ($category_id): ?>
                            <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                        <?php endif; ?>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search products..." 
                                   value="<?php echo htmlspecialchars($search ?? ''); ?>">
                            <button class="btn btn-outline-warning" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Categories -->
                    <h6 class="fw-bold mb-3">Categories</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="shop.php<?php echo $search ? '?search=' . urlencode($search) : ''; ?>" 
                               class="text-decoration-none d-flex justify-content-between align-items-center <?php echo !$category_id ? 'fw-bold text-warning' : 'text-dark'; ?>">
                                <span>All Categories</span>
                                <span class="badge bg-secondary"><?php echo $total_products; ?></span>
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                        <?php $cat_count = $product->getProductCount($cat['id'], $search); ?>
                        <li class="mb-2">
                            <a href="shop.php?category=<?php echo $cat['id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                               class="text-decoration-none d-flex justify-content-between align-items-center <?php echo $category_id == $cat['id'] ? 'fw-bold text-warning' : 'text-dark'; ?>">
                                <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                <span class="badge bg-secondary"><?php echo $cat_count; ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Clear Filters -->
                    <?php if ($category_id || $search): ?>
                    <div class="mt-4">
                        <a href="shop.php" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Products -->
        <div class="col-lg-9">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-2">
                        <?php if ($current_category): ?>
                            <?php echo htmlspecialchars($current_category['name']); ?>
                        <?php elseif ($search): ?>
                            Search Results
                        <?php else: ?>
                            All Products
                        <?php endif; ?>
                    </h2>
                    <p class="text-muted mb-0">
                        <?php if ($search): ?>
                            <?php echo $total_products; ?> results for "<?php echo htmlspecialchars($search); ?>"
                        <?php else: ?>
                            <?php echo $total_products; ?> products found
                        <?php endif; ?>
                    </p>
                </div>
                
                <!-- Sort Options -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-sort me-2"></i>Sort by
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="?<?php echo $query_string; ?>&sort=newest">Newest First</a></li>
                        <li><a class="dropdown-item" href="?<?php echo $query_string; ?>&sort=price_low">Price: Low to High</a></li>
                        <li><a class="dropdown-item" href="?<?php echo $query_string; ?>&sort=price_high">Price: High to Low</a></li>
                        <li><a class="dropdown-item" href="?<?php echo $query_string; ?>&sort=name">Name A-Z</a></li>
                    </ul>
                </div>
            </div>

            <!-- Products Grid -->
            <?php if (empty($products)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4>No products found</h4>
                    <p class="text-muted">
                        <?php if ($search): ?>
                            Try adjusting your search terms or browse our categories.
                        <?php else: ?>
                            No products available in this category at the moment.
                        <?php endif; ?>
                    </p>
                    <div class="mt-3">
                        <a href="shop.php" class="btn btn-warning me-2">View All Products</a>
                        <a href="contact.php" class="btn btn-outline-secondary">Contact Us</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($products as $prod): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card product-card h-100">
                            <div class="product-image">
                                <img src="<?php echo $prod['image'] ?: '/placeholder.svg?height=250&width=300'; ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                                <div class="product-overlay">
                                    <a href="product.php?id=<?php echo $prod['id']; ?>" class="btn btn-warning">
                                        <i class="fas fa-eye me-2"></i>View Details
                                    </a>
                                </div>
                                <?php if ($prod['featured']): ?>
                                    <span class="position-absolute top-0 start-0 badge bg-warning text-dark m-2">
                                        <i class="fas fa-star me-1"></i>Featured
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($prod['name']); ?></h5>
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($prod['brand']); ?>
                                    <?php if ($prod['category_name']): ?>
                                        | <?php echo htmlspecialchars($prod['category_name']); ?>
                                    <?php endif; ?>
                                </p>
                                <p class="card-text flex-grow-1">
                                    <?php echo substr(htmlspecialchars($prod['description']), 0, 100) . '...'; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <span class="h5 text-warning mb-0"><?php echo formatPrice($prod['price']); ?></span>
                                    <?php if ($prod['stock_quantity'] > 0): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>In Stock (<?php echo $prod['stock_quantity']; ?>)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times me-1"></i>Out of Stock
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($prod['stock_quantity'] > 0 && isLoggedIn()): ?>
                                    <button class="btn btn-outline-warning btn-sm mt-2" 
                                            onclick="addToCart(<?php echo $prod['id']; ?>)">
                                        <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Products pagination" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo $query_string; ?>">
                                    <i class="fas fa-chevron-left me-1"></i>Previous
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        
                        if ($start > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1&<?php echo $query_string; ?>">1</a>
                            </li>
                            <?php if ($start > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo $query_string; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($end < $total_pages): ?>
                            <?php if ($end < $total_pages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $total_pages; ?>&<?php echo $query_string; ?>"><?php echo $total_pages; ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo $query_string; ?>">
                                    Next<i class="fas fa-chevron-right ms-1"></i>
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

<?php require_once 'includes/footer.php'; ?>
