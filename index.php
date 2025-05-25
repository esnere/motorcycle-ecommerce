<?php
$page_title = 'Home';
require_once 'includes/header.php';
require_once 'classes/Product.php';

$product = new Product($db);
$featured_products = $product->getFeaturedProducts(6);
$categories = $product->getCategories();
?>

<!-- Homepage Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center min-vh-100 justify-content-center">
            <div class="col-lg-6 text-center">
                <h1 class="display-4 fw-bold text-white mb-4">
                    Classic Motorcycle Parts
                    <span class="text-warning">Philippines</span>
                </h1>
                <p class="lead text-white mb-4">
                    Find authentic parts for your vintage motorcycle. From Honda CB to Yamaha XS, 
                    we have the parts you need to keep your classic bike running smoothly.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="shop.php" class="btn btn-warning btn-lg">
                        <i class="fas fa-shopping-bag me-2"></i>Shop Now
                    </a>
                    <a href="about.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-info-circle me-2"></i>Learn More
                    </a>
                </div>
                <div class="mt-4">
                    <small class="text-light">
                        <i class="fas fa-shipping-fast me-2"></i>Free shipping on orders over ₱50
                    </small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Shop by Category</h2>
            <p class="text-muted">Find parts for every component of your classic motorcycle</p>
        </div>
        <div class="row g-4">
            <?php foreach ($categories as $category): ?>
            <div class="col-md-6 col-lg-3">
                <a href="shop.php?category=<?php echo $category['id']; ?>" class="text-decoration-none">
                    <div class="category-card text-center p-4 h-100">
                        <div class="category-icon mb-3">
                            <?php
                            $icons = [
                                'Engine Parts' => 'fas fa-cog',
                                'Electrical' => 'fas fa-bolt',
                                'Suspension' => 'fas fa-arrows-alt-v',
                                'Brakes' => 'fas fa-stop-circle',
                                'Body Parts' => 'fas fa-car',
                                'Exhaust' => 'fas fa-wind',
                                'Transmission' => 'fas fa-cogs',
                                'Fuel System' => 'fas fa-gas-pump'
                            ];
                            $icon = $icons[$category['name']] ?? 'fas fa-wrench';
                            ?>
                            <i class="<?php echo $icon; ?> fa-3x text-warning"></i>
                        </div>
                        <h5 class="fw-bold"><?php echo htmlspecialchars($category['name']); ?></h5>
                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($category['description']); ?></p>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Featured Products</h2>
            <p class="text-muted">Popular parts for classic motorcycles</p>
        </div>
        
        <?php if (empty($featured_products)): ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h4>No featured products available</h4>
                <p class="text-muted">Check back soon for our latest featured items!</p>
                <a href="shop.php" class="btn btn-warning">Browse All Products</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($featured_products as $product): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card product-card h-100">
                        <div class="product-image">
                            <!-- <img src="<?php echo $product['image'] ?: '/placeholder.svg?height=250&width=300'; ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>"> -->
                            <div class="product-overlay">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-warning">
                                    <i class="fas fa-eye me-2"></i>View Details
                                </a>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($product['brand']); ?>
                                <?php if ($product['category_name']): ?>
                                    | <?php echo htmlspecialchars($product['category_name']); ?>
                                <?php endif; ?>
                            </p>
                            <p class="card-text flex-grow-1">
                                <?php echo substr(htmlspecialchars($product['description']), 0, 100) . '...'; ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <span class="h5 text-warning mb-0"><?php echo formatPrice($product['price']); ?></span>
                                <?php if ($product['stock_quantity'] > 0): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>In Stock
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times me-1"></i>Out of Stock
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-5">
            <a href="shop.php" class="btn btn-outline-dark btn-lg">
                <i class="fas fa-th me-2"></i>View All Products
            </a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6 col-lg-3 text-center">
                <div class="feature-icon mb-3">
                    <i class="fas fa-shipping-fast fa-3x text-warning"></i>
                </div>
                <h5 class="fw-bold">Fast Shipping</h5>
                <p class="text-muted mb-0">Quick delivery across the Philippines with tracking</p>
            </div>
            <div class="col-md-6 col-lg-3 text-center">
                <div class="feature-icon mb-3">
                    <i class="fas fa-shield-alt fa-3x text-warning"></i>
                </div>
                <h5 class="fw-bold">Quality Guaranteed</h5>
                <p class="text-muted mb-0">Authentic parts with manufacturer warranty</p>
            </div>
            <div class="col-md-6 col-lg-3 text-center">
                <div class="feature-icon mb-3">
                    <i class="fas fa-tools fa-3x text-warning"></i>
                </div>
                <h5 class="fw-bold">Expert Support</h5>
                <p class="text-muted mb-0">Technical assistance from motorcycle specialists</p>
            </div>
            <div class="col-md-6 col-lg-3 text-center">
                <div class="feature-icon mb-3">
                    <i class="fas fa-undo fa-3x text-warning"></i>
                </div>
                <h5 class="fw-bold">Easy Returns</h5>
                <p class="text-muted mb-0">30-day hassle-free return policy</p>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-5 bg-dark text-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h3 class="fw-bold mb-3">Stay Updated</h3>
                <p class="mb-0">Get the latest news about new arrivals, special offers, and motorcycle maintenance tips.</p>
            </div>
            <div class="col-lg-6">
                <form class="d-flex gap-2 mt-3 mt-lg-0">
                    <input type="email" class="form-control" placeholder="Enter your email address" required>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-paper-plane me-2"></i>Subscribe
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
