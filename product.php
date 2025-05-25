<?php
$page_title = 'Product Details';
require_once 'includes/header.php';
require_once 'classes/Product.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('shop.php');
}

$product_obj = new Product($db);
$product = $product_obj->getProductById($_GET['id']);

if (!$product) {
    redirect('shop.php');
}

$page_title = $product['name'];

// Get related products
$related_products = $product_obj->getAllProducts(4, 0, $product['category_id']);
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="shop.php">Shop</a></li>
            <li class="breadcrumb-item">
                <a href="shop.php?category=<?php echo $product['category_id']; ?>">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </a>
            </li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="product-image-container">
                <!-- <img src="<?php echo $product['image'] ?: '/placeholder.svg?height=500&width=500'; ?>" 
                     class="img-fluid rounded shadow product-main-image" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     style="width: 100%; height: 400px; object-fit: cover;"> -->
                
                <!-- Image Gallery (if gallery images exist) -->
                <?php if (!empty($product['gallery'])): ?>
                    <?php $gallery = json_decode($product['gallery'], true); ?>
                    <?php if (is_array($gallery) && count($gallery) > 0): ?>
                    <div class="row mt-3">
                        <?php foreach ($gallery as $index => $image): ?>
                        <div class="col-3">
                            <img src="<?php echo htmlspecialchars($image); ?>" 
                                 class="img-fluid rounded product-thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                 data-image="<?php echo htmlspecialchars($image); ?>"
                                 alt="Product image <?php echo $index + 1; ?>"
                                 style="height: 80px; object-fit: cover; cursor: pointer;">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="product-details">
                <h1 class="h2 mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="product-meta mb-3">
                    <span class="badge bg-secondary me-2">
                        <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($product['category_name']); ?>
                    </span>
                    <span class="text-muted">
                        <i class="fas fa-barcode me-1"></i>SKU: <?php echo htmlspecialchars($product['sku']); ?>
                    </span>
                </div>

                <div class="product-price mb-4">
                    <span class="h3 text-warning fw-bold"><?php echo formatPrice($product['price']); ?></span>
                </div>

                <div class="product-info mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-bold text-muted">Brand:</td>
                                    <td><?php echo htmlspecialchars($product['brand']); ?></td>
                                </tr>
                                <?php if ($product['model']): ?>
                                <tr>
                                    <td class="fw-bold text-muted">Model:</td>
                                    <td><?php echo htmlspecialchars($product['model']); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($product['year_from'] && $product['year_to']): ?>
                                <tr>
                                    <td class="fw-bold text-muted">Year:</td>
                                    <td><?php echo $product['year_from']; ?> - <?php echo $product['year_to']; ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-bold text-muted">Stock:</td>
                                    <td>
                                        <?php if ($product['stock_quantity'] > 0): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i><?php echo $product['stock_quantity']; ?> available
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>Out of stock
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if ($product['weight']): ?>
                                <tr>
                                    <td class="fw-bold text-muted">Weight:</td>
                                    <td><?php echo $product['weight']; ?> kg</td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($product['dimensions']): ?>
                                <tr>
                                    <td class="fw-bold text-muted">Dimensions:</td>
                                    <td><?php echo htmlspecialchars($product['dimensions']); ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>

                <?php if ($product['stock_quantity'] > 0): ?>
                <div class="add-to-cart mb-4">
                    <form id="addToCartForm" class="needs-validation" novalidate>
                        <div class="row g-3 align-items-end">
                            <div class="col-auto">
                                <label for="quantity" class="form-label fw-bold">Quantity</label>
                                <div class="quantity-controls">
                                    <button type="button" class="quantity-minus btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" class="form-control quantity-input text-center" 
                                           id="quantity" name="quantity" value="1" min="1" 
                                           max="<?php echo $product['stock_quantity']; ?>" 
                                           style="width: 80px;" required>
                                    <button type="button" class="quantity-plus btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Please enter a valid quantity.
                                </div>
                            </div>
                            <div class="col-auto">
                                <?php if (isLoggedIn()): ?>
                                    <button type="submit" class="btn btn-warning btn-lg" id="addToCartBtn">
                                        <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                    </button>
                                <?php else: ?>
                                    <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                                       class="btn btn-warning btn-lg">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login to Purchase
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    </form>
                </div>
                <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This product is currently out of stock. Please check back later or contact us for availability.
                </div>
                <?php endif; ?>

                <div class="product-actions mb-4">
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary" onclick="window.history.back()">
                            <i class="fas fa-arrow-left me-2"></i>Back to Shop
                        </button>
                        <button class="btn btn-outline-info" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Print
                        </button>
                        <button class="btn btn-outline-success" onclick="shareProduct()">
                            <i class="fas fa-share me-2"></i>Share
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Description -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Product Description</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
    <div class="mt-5">
        <h3 class="mb-4"><i class="fas fa-boxes me-2"></i>Related Products</h3>
        <div class="row g-4">
            <?php foreach ($related_products as $related): ?>
                <?php if ($related['id'] == $product['id']) continue; ?>
            <div class="col-md-6 col-lg-3">
                <div class="card product-card h-100">
                    <div class="product-image">
                        <img src="<?php echo $related['image'] ?: '/placeholder.svg?height=200&width=250'; ?>" 
                             class="card-img-top" alt="<?php echo htmlspecialchars($related['name']); ?>"
                             style="height: 200px; object-fit: cover;">
                        <div class="product-overlay">
                            <a href="product.php?id=<?php echo $related['id']; ?>" class="btn btn-warning">
                                <i class="fas fa-eye me-2"></i>View Details
                            </a>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title"><?php echo htmlspecialchars($related['name']); ?></h6>
                        <p class="text-muted small"><?php echo htmlspecialchars($related['brand']); ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <span class="h6 text-warning mb-0"><?php echo formatPrice($related['price']); ?></span>
                            <?php if ($related['stock_quantity'] > 0): ?>
                                <span class="badge bg-success">In Stock</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add to cart form submission
    const addToCartForm = document.getElementById('addToCartForm');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = document.getElementById('addToCartBtn');
            
            // Show loading state
            submitFormWithLoading(this, submitBtn);
            
            fetch('api/cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartCount(data.cart_count);
                    showNotification('Product added to cart successfully!', 'success');
                } else {
                    showNotification(data.message || 'Error adding product to cart', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error adding product to cart', 'error');
            })
            .finally(() => {
                // Reset button
                submitBtn.innerHTML = '<i class="fas fa-cart-plus me-2"></i>Add to Cart';
                submitBtn.disabled = false;
            });
        });
    }

    // Setup quantity controls
    setupQuantityControls();
});

function shareProduct() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo htmlspecialchars($product['name']); ?>',
            text: '<?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...',
            url: window.location.href
        });
    } else {
        // Fallback: copy URL to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            showNotification('Product URL copied to clipboard!', 'success');
        });
    }
}

function submitFormWithLoading(form, button) {
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
    button.disabled = true;
}

function updateCartCount(count) {
    const cartBadge = document.querySelector('.navbar .badge');
    if (cartBadge) {
        cartBadge.textContent = count;
    }
}

function showNotification(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

function setupQuantityControls() {
    const quantityInput = document.querySelector('.quantity-input');
    const quantityMinus = document.querySelector('.quantity-minus');
    const quantityPlus = document.querySelector('.quantity-plus');

    quantityMinus.addEventListener('click', function() {
        let currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    });

    quantityPlus.addEventListener('click', function() {
        let currentValue = parseInt(quantityInput.value);
        let maxValue = parseInt(quantityInput.max);
        if (currentValue < maxValue) {
            quantityInput.value = currentValue + 1;
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
