<?php
$page_title = 'Register';
require_once 'includes/header.php';
require_once 'classes/User.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $data = [
            'username' => sanitizeInput($_POST['username']),
            'email' => sanitizeInput($_POST['email']),
            'password' => $_POST['password'],
            'confirm_password' => $_POST['confirm_password'],
            'first_name' => sanitizeInput($_POST['first_name']),
            'last_name' => sanitizeInput($_POST['last_name']),
            'phone' => sanitizeInput($_POST['phone']),
            'address' => sanitizeInput($_POST['address']),
            'city' => sanitizeInput($_POST['city']),
            'province' => sanitizeInput($_POST['province']),
            'postal_code' => sanitizeInput($_POST['postal_code'])
        ];
        
        // Validation
        if (empty($data['username']) || empty($data['email']) || empty($data['password']) ||
            empty($data['first_name']) || empty($data['last_name'])) {
            $error = 'Please fill in all required fields.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($data['password']) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($data['password'] !== $data['confirm_password']) {
            $error = 'Passwords do not match.';
        } elseif (strlen($data['username']) < 3) {
            $error = 'Username must be at least 3 characters long.';
        } else {
            $user = new User($db);
            $result = $user->register($data);
            
            if ($result['success']) {
                $success = 'Registration successful! You can now log in.';
                // Clear form data
                $data = [];
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus fa-3x text-warning mb-3"></i>
                        <h2 class="fw-bold">Create Account</h2>
                        <p class="text-muted">Join <?php echo SITE_NAME; ?> today</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            <div class="mt-2">
                                <a href="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" 
                                   class="btn btn-success btn-sm">Login Now</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($data['first_name'] ?? ''); ?>" 
                                       placeholder="Enter your first name" required>
                                <div class="invalid-feedback">Please enter your first name.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($data['last_name'] ?? ''); ?>" 
                                       placeholder="Enter your last name" required>
                                <div class="invalid-feedback">Please enter your last name.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($data['username'] ?? ''); ?>" 
                                       placeholder="Choose a username" minlength="3" required>
                                <div class="invalid-feedback">Username must be at least 3 characters long.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>" 
                                       placeholder="Enter your email address" required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Create a password" minlength="6" required>
                                    <div class="invalid-feedback">Password must be at least 6 characters long.</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           placeholder="Confirm your password" required>
                                    <div class="invalid-feedback">Please confirm your password.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($data['phone'] ?? ''); ?>" 
                                       placeholder="+63 912 345 6789">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2" 
                                      placeholder="Enter your complete address"><?php echo htmlspecialchars($data['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($data['city'] ?? ''); ?>" 
                                       placeholder="Enter your city">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="province" class="form-label">Province</label>
                                <select class="form-control" id="province" name="province">
                                    <option value="">Select Province</option>
                                    <option value="Metro Manila" <?php echo ($data['province'] ?? '') === 'Metro Manila' ? 'selected' : ''; ?>>Metro Manila</option>
                                    <option value="Cebu" <?php echo ($data['province'] ?? '') === 'Cebu' ? 'selected' : ''; ?>>Cebu</option>
                                    <option value="Davao" <?php echo ($data['province'] ?? '') === 'Davao' ? 'selected' : ''; ?>>Davao</option>
                                    <option value="Palawan" <?php echo ($data['province'] ?? '') === 'Palawan' ? 'selected' : ''; ?>>Palawan</option>
                                    <option value="Cavite" <?php echo ($data['province'] ?? '') === 'Cavite' ? 'selected' : ''; ?>>Cavite</option>
                                    <!-- Add more provinces as needed -->
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="postal_code" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                   value="<?php echo htmlspecialchars($data['postal_code'] ?? ''); ?>" 
                                   placeholder="Enter postal code">
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and 
                                <a href="privacy.php" target="_blank">Privacy Policy</a> *
                            </label>
                            <div class="invalid-feedback">You must agree to the terms and conditions.</div>
                        </div>

                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                        <button type="submit" class="btn btn-warning w-100 mb-3 py-2">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </button>
                    </form>

                    <div class="text-center">
                        <p class="mb-0">
                            Already have an account? 
                            <a href="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" 
                               class="text-warning text-decoration-none fw-bold">Sign in here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
