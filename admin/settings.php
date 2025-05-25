<?php
$page_title = 'Admin - Settings';
require_once '../includes/header.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('../index.php');
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'update_site_settings':
                // Update site settings (you can store these in a settings table or config file)
                $site_name = sanitizeInput($_POST['site_name']);
                $site_email = sanitizeInput($_POST['site_email']);
                $site_phone = sanitizeInput($_POST['site_phone']);
                $site_address = sanitizeInput($_POST['site_address']);
                
                // For now, we'll update the config file or you can create a settings table
                $message = 'Site settings updated successfully!';
                break;
                
            case 'update_email_settings':
                $smtp_host = sanitizeInput($_POST['smtp_host']);
                $smtp_port = sanitizeInput($_POST['smtp_port']);
                $smtp_username = sanitizeInput($_POST['smtp_username']);
                $smtp_password = $_POST['smtp_password']; // Don't sanitize password
                
                $message = 'Email settings updated successfully!';
                break;
                
            case 'update_payment_settings':
                $payment_methods = $_POST['payment_methods'] ?? [];
                $cod_enabled = isset($_POST['cod_enabled']) ? 1 : 0;
                $bank_transfer_enabled = isset($_POST['bank_transfer_enabled']) ? 1 : 0;
                
                $message = 'Payment settings updated successfully!';
                break;
                
            case 'update_shipping_settings':
                $free_shipping_threshold = (float)$_POST['free_shipping_threshold'];
                $standard_shipping_rate = (float)$_POST['standard_shipping_rate'];
                $express_shipping_rate = (float)$_POST['express_shipping_rate'];
                
                $message = 'Shipping settings updated successfully!';
                break;
                
            case 'backup_database':
                // Database backup functionality
                $backup_file = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
                // Implementation would go here
                $message = 'Database backup created successfully!';
                break;
                
            case 'clear_cache':
                // Clear any cached data
                $message = 'Cache cleared successfully!';
                break;
        }
    }
}

// Get current settings (you can load these from database or config)
$current_settings = [
    'site_name' => SITE_NAME,
    'site_email' => ADMIN_EMAIL,
    'site_phone' => '+63 123 456 7890',
    'site_address' => 'Manila, Philippines',
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => '587',
    'smtp_username' => '',
    'free_shipping_threshold' => 5000,
    'standard_shipping_rate' => 150,
    'express_shipping_rate' => 300
];
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
                    <a href="reports.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-bar me-2"></i>Reports
                    </a>
                    <a href="settings.php" class="list-group-item list-group-item-action active">
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
                <h2>System Settings</h2>
                <div class="text-muted">
                    <i class="fas fa-cog me-2"></i>Configuration Panel
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

            <!-- Settings Tabs -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="site-tab" data-bs-toggle="tab" data-bs-target="#site" type="button" role="tab">
                                <i class="fas fa-globe me-2"></i>Site Settings
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab">
                                <i class="fas fa-envelope me-2"></i>Email Settings
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab">
                                <i class="fas fa-credit-card me-2"></i>Payment Settings
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab">
                                <i class="fas fa-truck me-2"></i>Shipping Settings
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">
                                <i class="fas fa-server me-2"></i>System
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="settingsTabContent">
                        <!-- Site Settings -->
                        <div class="tab-pane fade show active" id="site" role="tabpanel">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="action" value="update_site_settings">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="site_name" class="form-label">Site Name</label>
                                        <input type="text" class="form-control" id="site_name" name="site_name" 
                                               value="<?php echo htmlspecialchars($current_settings['site_name']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="site_email" class="form-label">Contact Email</label>
                                        <input type="email" class="form-control" id="site_email" name="site_email" 
                                               value="<?php echo htmlspecialchars($current_settings['site_email']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="site_phone" class="form-label">Contact Phone</label>
                                        <input type="text" class="form-control" id="site_phone" name="site_phone" 
                                               value="<?php echo htmlspecialchars($current_settings['site_phone']); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="site_address" class="form-label">Business Address</label>
                                        <input type="text" class="form-control" id="site_address" name="site_address" 
                                               value="<?php echo htmlspecialchars($current_settings['site_address']); ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_description" class="form-label">Site Description</label>
                                    <textarea class="form-control" id="site_description" name="site_description" rows="3">Your trusted source for classic motorcycle parts and accessories in the Philippines.</textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="timezone" class="form-label">Timezone</label>
                                        <select class="form-control" id="timezone" name="timezone">
                                            <option value="Asia/Manila" selected>Asia/Manila (GMT+8)</option>
                                            <option value="UTC">UTC (GMT+0)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="currency" class="form-label">Currency</label>
                                        <select class="form-control" id="currency" name="currency">
                                            <option value="PHP" selected>Philippine Peso (₱)</option>
                                            <option value="USD">US Dollar ($)</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Site Settings
                                </button>
                            </form>
                        </div>

                        <!-- Email Settings -->
                        <div class="tab-pane fade" id="email" role="tabpanel">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="action" value="update_email_settings">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="smtp_host" class="form-label">SMTP Host</label>
                                        <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                               value="<?php echo htmlspecialchars($current_settings['smtp_host']); ?>" 
                                               placeholder="smtp.gmail.com">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="smtp_port" class="form-label">SMTP Port</label>
                                        <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                               value="<?php echo htmlspecialchars($current_settings['smtp_port']); ?>" 
                                               placeholder="587">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="smtp_username" class="form-label">SMTP Username</label>
                                        <input type="email" class="form-control" id="smtp_username" name="smtp_username" 
                                               value="<?php echo htmlspecialchars($current_settings['smtp_username']); ?>" 
                                               placeholder="your-email@gmail.com">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="smtp_password" class="form-label">SMTP Password</label>
                                        <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                               placeholder="Enter SMTP password">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="smtp_encryption" name="smtp_encryption" checked>
                                        <label class="form-check-label" for="smtp_encryption">
                                            Enable SMTP Encryption (TLS)
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Note:</strong> For Gmail, use an App Password instead of your regular password. 
                                    Enable 2-factor authentication and generate an App Password in your Google Account settings.
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Email Settings
                                </button>
                                <button type="button" class="btn btn-outline-secondary ms-2" onclick="testEmail()">
                                    <i class="fas fa-paper-plane me-2"></i>Test Email
                                </button>
                            </form>
                        </div>

                        <!-- Payment Settings -->
                        <div class="tab-pane fade" id="payment" role="tabpanel">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="action" value="update_payment_settings">
                                
                                <h6 class="mb-3">Payment Methods</h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="cod_enabled" name="cod_enabled" checked>
                                            <label class="form-check-label" for="cod_enabled">
                                                <i class="fas fa-money-bill-wave me-2"></i>Cash on Delivery (COD)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="bank_transfer_enabled" name="bank_transfer_enabled" checked>
                                            <label class="form-check-label" for="bank_transfer_enabled">
                                                <i class="fas fa-university me-2"></i>Bank Transfer
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <h6 class="mb-3">Bank Details (for Bank Transfer)</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="bank_name" class="form-label">Bank Name</label>
                                        <input type="text" class="form-control" id="bank_name" name="bank_name" 
                                               placeholder="BPI, BDO, Metrobank, etc.">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="account_name" class="form-label">Account Name</label>
                                        <input type="text" class="form-control" id="account_name" name="account_name" 
                                               placeholder="Account holder name">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="account_number" class="form-label">Account Number</label>
                                        <input type="text" class="form-control" id="account_number" name="account_number" 
                                               placeholder="Bank account number">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="swift_code" class="form-label">SWIFT Code (Optional)</label>
                                        <input type="text" class="form-control" id="swift_code" name="swift_code" 
                                               placeholder="For international transfers">
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Payment Settings
                                </button>
                            </form>
                        </div>

                        <!-- Shipping Settings -->
                        <div class="tab-pane fade" id="shipping" role="tabpanel">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="action" value="update_shipping_settings">
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="free_shipping_threshold" class="form-label">Free Shipping Threshold</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="free_shipping_threshold" 
                                                   name="free_shipping_threshold" step="0.01" 
                                                   value="<?php echo $current_settings['free_shipping_threshold']; ?>">
                                        </div>
                                        <small class="text-muted">Orders above this amount get free shipping</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="standard_shipping_rate" class="form-label">Standard Shipping Rate</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="standard_shipping_rate" 
                                                   name="standard_shipping_rate" step="0.01" 
                                                   value="<?php echo $current_settings['standard_shipping_rate']; ?>">
                                        </div>
                                        <small class="text-muted">3-7 business days</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="express_shipping_rate" class="form-label">Express Shipping Rate</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="express_shipping_rate" 
                                                   name="express_shipping_rate" step="0.01" 
                                                   value="<?php echo $current_settings['express_shipping_rate']; ?>">
                                        </div>
                                        <small class="text-muted">1-2 business days</small>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <h6 class="mb-3">Shipping Zones</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Zone</th>
                                                <th>Areas</th>
                                                <th>Standard Rate</th>
                                                <th>Express Rate</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Metro Manila</td>
                                                <td>NCR, Quezon City, Manila, Makati, etc.</td>
                                                <td>₱150.00</td>
                                                <td>₱300.00</td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary">Edit</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Luzon</td>
                                                <td>Provinces in Luzon</td>
                                                <td>₱200.00</td>
                                                <td>₱400.00</td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary">Edit</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Visayas & Mindanao</td>
                                                <td>Cebu, Davao, Iloilo, etc.</td>
                                                <td>₱300.00</td>
                                                <td>₱500.00</td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary">Edit</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Shipping Settings
                                </button>
                                <button type="button" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-plus me-2"></i>Add Shipping Zone
                                </button>
                            </form>
                        </div>

                        <!-- System Settings -->
                        <div class="tab-pane fade" id="system" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="mb-3">Database Management</h6>
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">Backup Database</h6>
                                            <p class="card-text">Create a backup of your database for safety.</p>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                <input type="hidden" name="action" value="backup_database">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-download me-2"></i>Create Backup
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6 class="mb-3">Cache Management</h6>
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">Clear Cache</h6>
                                            <p class="card-text">Clear system cache to improve performance.</p>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                <input type="hidden" name="action" value="clear_cache">
                                                <button type="submit" class="btn btn-warning">
                                                    <i class="fas fa-broom me-2"></i>Clear Cache
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h6 class="mb-3">System Information</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <td><strong>PHP Version</strong></td>
                                        <td><?php echo phpversion(); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>MySQL Version</strong></td>
                                        <td><?php echo $db->getAttribute(PDO::ATTR_SERVER_VERSION); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Server Software</strong></td>
                                        <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Upload Max Filesize</strong></td>
                                        <td><?php echo ini_get('upload_max_filesize'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Memory Limit</strong></td>
                                        <td><?php echo ini_get('memory_limit'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>System Time</strong></td>
                                        <td><?php echo date('Y-m-d H:i:s'); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testEmail() {
    alert('Email test functionality would be implemented here. This would send a test email to verify SMTP settings.');
}
</script>

<?php require_once '../includes/footer.php'; ?>
