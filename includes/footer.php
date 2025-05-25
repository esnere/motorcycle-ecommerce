</main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="text-warning">
                        <i class="fas fa-motorcycle me-2"></i><?php echo SITE_NAME; ?>
                    </h5>
                    <p class="mb-3">Your trusted source for classic motorcycle parts in the Philippines. Quality parts for vintage bikes.</p>
                    <div class="social-links">
                        <a href="https://github.com/espencer15" class="text-light me-3 fs-4"><i class="fab fa-github"></i></a>
                        <a href="https://www.facebook.com/espencer.970238" class="text-light me-3 fs-4"><i class="fab fa-facebook"></i></a>
                        <a href="https://www.instagram.com/espenwho/" class="text-light me-3 fs-4"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.youtube.com/@ezpencer15" class="text-light fs-4"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-md-2 mb-4">
                    <h6 class="text-warning">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-light text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="shop.php" class="text-light text-decoration-none">Shop</a></li>
                        <li class="mb-2"><a href="about.php" class="text-light text-decoration-none">About</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-light text-decoration-none">Contact</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li class="mb-2"><a href="dashboard.php" class="text-light text-decoration-none">Dashboard</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h6 class="text-warning">Categories</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="shop.php?category=1" class="text-light text-decoration-none">Engine Parts</a></li>
                        <li class="mb-2"><a href="shop.php?category=2" class="text-light text-decoration-none">Electrical</a></li>
                        <li class="mb-2"><a href="shop.php?category=3" class="text-light text-decoration-none">Suspension</a></li>
                        <li class="mb-2"><a href="shop.php?category=4" class="text-light text-decoration-none">Brakes</a></li>
                        <li class="mb-2"><a href="shop.php?category=5" class="text-light text-decoration-none">Body Parts</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h6 class="text-warning">Contact Info</h6>
                    <div class="contact-info">
                        <p class="mb-2">
                            <i class="fas fa-map-marker-alt me-2 text-warning"></i>
                            Ledisma Vill, Brgy Sicsicsan<br>
                            <span class="ms-4">P.P.C., Palawan, Philippines 5300</span>
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-phone me-2 text-warning"></i>
                            +63 912 345 6789
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-envelope me-2 text-warning"></i>
                            <?php echo ADMIN_EMAIL; ?>
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-clock me-2 text-warning"></i>
                            Mon-Sat: 8AM-6PM
                        </p>
                    </div>
                </div>
            </div>
            <hr class="my-4 border-secondary">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="privacy-policy.php" class="text-light text-decoration-none me-3">Privacy Policy</a>
                    <a href="terms-of-service.php" class="text-light text-decoration-none me-3">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
    
    <!-- Global CSRF Token -->
    <script>
        window.csrfToken = '<?php echo $csrf_token; ?>';
    </script>
</body>
</html>
