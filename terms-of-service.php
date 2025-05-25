<?php
require_once 'config/config.php';
require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h1 class="h3 mb-0"><i class="fas fa-file-contract me-2"></i>Terms of Service</h1>
                </div>
                <div class="card-body">
                    <div class="terms-content">
                        <section class="mb-5">
                            <h2 class="h4 text-primary mb-3">1. Acceptance of Terms</h2>
                            <p>By accessing and using MotorcycleParts.com ("the Website"), you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 text-primary mb-3">2. Description of Service</h2>
                            <p>MotorcycleParts.com provides an online platform for purchasing motorcycle parts, accessories, and related products. We reserve the right to modify, suspend, or discontinue any aspect of our service at any time.</p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 text-primary mb-3">3. User Accounts</h2>
                            <ul>
                                <li>You must provide accurate and complete information when creating an account</li>
                                <li>You are responsible for maintaining the confidentiality of your account credentials</li>
                                <li>You must notify us immediately of any unauthorized use of your account</li>
                                <li>We reserve the right to suspend or terminate accounts that violate these terms</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 text-primary mb-3">4. Product Information and Pricing</h2>
                            <ul>
                                <li>We strive to provide accurate product descriptions and pricing</li>
                                <li>Prices are subject to change without notice</li>
                                <li>We reserve the right to correct any errors in product information or pricing</li>
                                <li>Product availability is subject to change</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 text-primary mb-3">5. Orders and Payment</h2>
                            <ul>
                                <li>All orders are subject to acceptance and availability</li>
                                <li>We reserve the right to refuse or cancel any order</li>
                                <li>Payment must be received before order processing</li>
                                <li>You agree to pay all charges incurred by your account</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 text-primary mb-3">6. Shipping and Delivery</h2>
                            <ul>
                                <li>Shipping costs and delivery times are estimates</li>
                                <li>Risk of loss passes to you upon delivery to the carrier</li>
                                <li>We are not responsible for delays caused by shipping carriers</li>
                                <li>International shipping may be subject to customs duties and taxes</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 text-primary mb-3">7. Returns and Refunds</h2>
                            <ul>
                                <li>Returns must be initiated within 30 days of purchase</li>
                                <li>Items must be in original condition and packaging</li>
                                <li>Custom or special-order items may not be returnable</li>
                                <li>Return shipping costs are the responsibility of the customer unless the item is defective</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 text-primary mb-3">8. Warranties and Disclaimers</h2>
                            <ul>
                                <li>Products are covered by manufacturer warranties where applicable</li>
                                <li>We disclaim all warranties beyond those provided by manufacturers</li>
                                <li>We are not liable for any damages resulting from product use</li>
                                <li>Installation of parts should be performed by qualified professionals</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 text-primary mb-3">9. Limitation of Liability</h2>
                            <p>In no event shall MotorcycleParts.com be liable for any indirect, incidental, special, or consequential damages arising out of or in connection with your use of our service or products, even if we have been advised of the possibility of such damages.</p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 text-primary mb-3">10. Intellectual Property</h2>
                            <ul>
                                <li>All content on this website is protected by copyright and trademark laws</li>
                                <li>You may not reproduce, distribute, or modify any content without permission</li>
                                <li>Product names and logos are trademarks of their respective owners</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 text-primary mb-3">11. Privacy</h2>
                            <p>Your privacy is important to us. Please review our Privacy Policy, which also governs your use of the service, to understand our practices.</p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 text-primary mb-3">12. Modifications to Terms</h2>
                            <p>We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting. Your continued use of the service constitutes acceptance of any modifications.</p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 text-primary mb-3">13. Governing Law</h2>
                            <p>These terms shall be governed by and construed in accordance with the laws of [Your State/Country], without regard to its conflict of law provisions.</p>
                        </section>

                        <section class="mb-5">
                            <h2 class="h4 text-primary mb-3">14. Contact Information</h2>
                            <p>If you have any questions about these Terms of Service, please contact us at:</p>
                            <div class="contact-info bg-light p-3 rounded">
                                <p class="mb-1"><strong>Email:</strong> classy@classicmotoparts.ph</p>
                                <p class="mb-1"><strong>Phone:</strong> +63 993 050 2358</p>
                                <p class="mb-0"><strong>Address:</strong> Ledisma Vill, Brgy Sicsicsan, Puerto Princesa City, Palawan, Philippines, 5300<br>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.terms-content h2 {
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 0.5rem;
}

.terms-content ul {
    padding-left: 1.5rem;
}

.terms-content li {
    margin-bottom: 0.5rem;
}

.contact-info {
    border-left: 4px solid #007bff;
}
</style>

<?php require_once 'includes/footer.php'; ?>
