<?php
$page_title = "Contact Us";
require_once 'includes/header.php';

// Handle contact form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = 'Security token mismatch. Please try again.';
    } else {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $subject = trim($_POST['subject']);
        $message = trim($_POST['message']);
        
        // Validate inputs
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $error_message = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Please enter a valid email address.';
        } else {
            // In a real application, you would send an email or save to database
            // For now, we'll just show a success message
            $success_message = 'Thank you for your message! We will get back to you within 24 hours.';
            
            // Clear form data
            $name = $email = $phone = $subject = $message = '';
        }
    }
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-dark mb-3">Contact Us</h1>
                <p class="lead text-muted">Get in touch with our motorcycle parts experts</p>
            </div>

            <!-- Contact Information -->
            <div class="row mb-5">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="fas fa-map-marker-alt fa-2x text-warning"></i>
                            </div>
                            <h5 class="fw-bold">Visit Our Store</h5>
                            <p class="text-muted small mb-0">
                                Ledisma Vill, Brgy Sicsicsan<br>
                                Puerto Princesa City, Palawan<br>
                                Philippines 5300
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="fas fa-phone fa-2x text-warning"></i>
                            </div>
                            <h5 class="fw-bold">Call Us</h5>
                            <p class="text-muted small mb-2">
                                <strong>Landline:</strong><br>
                                (02) 0000-2222
                            </p>
                            <p class="text-muted small mb-0">
                                <strong>Mobile:</strong><br>
                                +63 993 050 2358
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="fas fa-envelope fa-2x text-warning"></i>
                            </div>
                            <h5 class="fw-bold">Email Us</h5>
                            <p class="text-muted small mb-2">
                                <strong>General:</strong><br>
                                Espencer@classicmotoparts.ph
                            </p>
                            <p class="text-muted small mb-0">
                                <strong>Support:</strong><br>
                                classy@classicmotoparts.ph
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Business Hours -->
            <div class="row mb-5">
                <div class="col-md-6 mx-auto">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-center mb-4">
                                <i class="fas fa-clock text-warning me-2"></i>
                                Business Hours
                            </h5>
                            <div class="row text-center">
                                <div class="col-6">
                                    <p class="mb-2"><strong>Monday - Friday</strong></p>
                                    <p class="text-muted small">8:00 AM - 6:00 PM</p>
                                </div>
                                <div class="col-6">
                                    <p class="mb-2"><strong>Saturday</strong></p>
                                    <p class="text-muted small">10:00 AM - 3:00 PM</p>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="mb-2"><strong>Sunday</strong></p>
                                <p class="text-muted small">Closed</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">
                                <i class="fas fa-paper-plane text-warning me-2"></i>
                                Send Us a Message
                            </h5>

                            <?php if ($success_message): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?php echo htmlspecialchars($success_message); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <?php if ($error_message): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <?php echo htmlspecialchars($error_message); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                        <select class="form-select" id="subject" name="subject" required>
                                            <option value="">Select a subject</option>
                                            <option value="General Inquiry" <?php echo (isset($subject) && $subject === 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                                            <option value="Parts Availability" <?php echo (isset($subject) && $subject === 'Parts Availability') ? 'selected' : ''; ?>>Parts Availability</option>
                                            <option value="Technical Support" <?php echo (isset($subject) && $subject === 'Technical Support') ? 'selected' : ''; ?>>Technical Support</option>
                                            <option value="Order Status" <?php echo (isset($subject) && $subject === 'Order Status') ? 'selected' : ''; ?>>Order Status</option>
                                            <option value="Return/Exchange" <?php echo (isset($subject) && $subject === 'Return/Exchange') ? 'selected' : ''; ?>>Return/Exchange</option>
                                            <option value="Wholesale Inquiry" <?php echo (isset($subject) && $subject === 'Wholesale Inquiry') ? 'selected' : ''; ?>>Wholesale Inquiry</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="message" name="message" rows="5" 
                                              placeholder="Please provide details about your inquiry..." required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" name="submit_contact" class="btn btn-warning btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>
                                        Send Message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="mt-5">
                <h3 class="fw-bold text-center mb-4">Frequently Asked Questions</h3>
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Do you ship nationwide?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, we ship to all provinces in the Philippines. Shipping fees vary by location and package weight. 
                                Same-day delivery is available in Metro Manila for orders placed before 2 PM.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                What is your return policy?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We accept returns within 30 days of purchase for unused items in original packaging. 
                                Custom or special-order parts are non-returnable unless defective.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Do you offer installation services?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We can recommend trusted mechanics in your area. Our technical team can also provide 
                                installation guidance and support via phone or email.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Can you source hard-to-find parts?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes! We specialize in sourcing rare and discontinued parts. Contact us with your requirements, 
                                and we'll do our best to locate the parts you need.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
