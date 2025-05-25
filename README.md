# Classic Motorcycle Parts PH - E-commerce Website

A comprehensive e-commerce platform for classic motorcycle parts and accessories, built with PHP, MySQL, and Bootstrap. This system provides a complete online shopping experience for motorcycle enthusiasts in the Philippines.

## 🏍️ Features

### Customer Features
- **User Registration & Authentication** - Secure account creation and login system
- **Product Catalog** - Browse motorcycle parts by categories and brands
- **Advanced Search** - Find parts by name, brand, model, or description
- **Shopping Cart** - Add, update, and remove items with real-time calculations
- **Secure Checkout** - Complete order processing with multiple payment options
- **Order Management** - Track order status and view order history
- **User Profile** - Manage personal information and shipping addresses
- **Responsive Design** - Optimized for desktop, tablet, and mobile devices

### Admin Features
- **Dashboard** - Comprehensive overview of sales, orders, and statistics
- **Product Management** - Full CRUD operations for products and categories
- **Order Management** - Process orders, update status, and track payments
- **User Management** - Manage customer accounts and admin privileges
- **Inventory Control** - Track stock levels and manage product availability
- **Reports & Analytics** - Sales reports and business insights

### Security Features
- **CSRF Protection** - Secure forms against cross-site request forgery
- **Password Hashing** - Secure password storage using PHP's password_hash()
- **Input Sanitization** - Protection against SQL injection and XSS attacks
- **Session Management** - Secure user session handling
- **Admin Access Control** - Role-based permissions for administrative functions

## 🛠️ Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework**: Bootstrap 5.3.2
- **Icons**: Font Awesome 6.4.0
- **Server**: Apache/Nginx

## 📋 Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- PDO PHP extension
- GD PHP extension (for image handling)

## 🚀 Installation

### 1. Clone the Repository
\`\`\`bash
git clone https://github.com/yourusername/motorcycle-ecommerce.git
cd motorcycle-ecommerce
\`\`\`

### 2. Database Setup
1. Create a new MySQL database named `motorcycle_parts_db`
2. Import the database schema:
\`\`\`bash
mysql -u your_username -p motorcycle_parts_db < database/motorcycle_parts_db.sql
\`\`\`
3. If you encounter the `is_active` column error, run the fix:
\`\`\`bash
mysql -u your_username -p motorcycle_parts_db < database/fix_users_table.sql
\`\`\`

### 3. Configuration
1. Update database credentials in `config/database.php`:
\`\`\`php
private $host = 'localhost';
private $db_name = 'motorcycle_parts_db';
private $username = 'your_username';
private $password = 'your_password';
\`\`\`

2. Configure site settings in `config/config.php`:
\`\`\`php
define('SITE_NAME', 'Classic Motorcycle Parts PH');
define('SITE_URL', 'http://your-domain.com');
define('ADMIN_EMAIL', 'admin@yourdomain.com');
\`\`\`

### 4. File Permissions
Set appropriate permissions for upload directories:
\`\`\`bash
chmod 755 assets/images/uploads/
\`\`\`

### 5. Web Server Configuration
Ensure your web server points to the project root directory and has PHP enabled.

## 👤 Default Accounts

### Admin Account
- **Username**: `admin`
- **Password**: `admin123`
- **Email**: `admin@motorcycleparts.ph`

### Customer Account
- **Username**: `customer`
- **Password**: `customer123`
- **Email**: `customer@example.com`


## 🔧 Configuration Options

### Currency Settings
\`\`\`php
define('CURRENCY', '₱');  // Philippine Peso
\`\`\`

### Pagination
\`\`\`php
define('PRODUCTS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);
\`\`\`

### File Upload
\`\`\`php
define('UPLOAD_PATH', 'assets/images/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
\`\`\`

### Shipping
- Free shipping on orders over ₱5,000
- Standard shipping rate: ₱150

## 🛡️ Security Considerations

1. **Change Default Passwords** - Update admin and demo account passwords
2. **Database Security** - Use strong database credentials
3. **File Permissions** - Set restrictive permissions on sensitive files
4. **SSL Certificate** - Use HTTPS in production
5. **Regular Updates** - Keep PHP and MySQL updated
6. **Backup Strategy** - Implement regular database backups

## 🐛 Troubleshooting

### Common Issues

#### "Column 'is_active' not found" Error
Run the database fix:
\`\`\`bash
mysql -u username -p motorcycle_parts_db < database/fix_users_table.sql
\`\`\`

#### "Class not found" Errors
Ensure all required files are properly included and paths are correct.

#### Image Upload Issues
Check file permissions on the uploads directory:
\`\`\`bash
chmod 755 assets/images/uploads/
\`\`\`

#### Session Issues
Verify PHP session configuration and ensure session directory is writable.

## 📱 Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari, Chrome Mobile)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Commit your changes (`git commit -am 'Add new feature'`)
4. Push to the branch (`git push origin feature/new-feature`)
5. Create a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

For support and questions:
- **Email**: admin@motorcycleparts.ph
- **Phone**: +63 912 345 6789
- **Address**: 123 Motorcycle Street, Manila, Philippines 1000

## 🔄 Version History

### v1.0.0 (Current)
- Initial release
- Complete e-commerce functionality
- Admin panel
- User management
- Order processing
- Payment integration ready

## 🚀 Future Enhancements

- [ ] Payment gateway integration (PayPal, GCash, PayMaya)
- [ ] Email notifications for orders
- [ ] Product reviews and ratings
- [ ] Wishlist functionality
- [ ] Advanced inventory management
- [ ] Multi-language support
- [ ] Mobile app API
- [ ] Social media integration
- [ ] Advanced analytics dashboard

## 📊 Database Schema

The system uses the following main tables:
- `users` - Customer and admin accounts
- `products` - Product catalog
- `categories` - Product categories
- `cart` - Shopping cart items
- `orders` - Order records
- `order_items` - Order line items
- `admin_logs` - Admin activity logs

For detailed schema information, see `database/motorcycle_parts_db.sql`.

