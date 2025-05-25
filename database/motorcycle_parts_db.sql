-- Create database
CREATE DATABASE IF NOT EXISTS motorcycle_parts_db;
USE motorcycle_parts_db;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    province VARCHAR(50),
    postal_code VARCHAR(10),
    is_admin BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
);

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    brand VARCHAR(100),
    model VARCHAR(100),
    year_from INT,
    year_to INT,
    stock_quantity INT DEFAULT 0,
    sku VARCHAR(50) UNIQUE,
    image VARCHAR(255),
    gallery TEXT, -- JSON array of image URLs
    weight DECIMAL(8,2),
    dimensions VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_category (category_id),
    INDEX idx_featured (featured),
    INDEX idx_active (is_active),
    INDEX idx_sku (sku),
    FULLTEXT idx_search (name, description, brand)
);

-- Cart table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id),
    INDEX idx_user (user_id)
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    shipping_address TEXT,
    billing_address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_order_number (order_number)
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_order (order_id)
);

-- Admin logs table
CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(100),
    table_name VARCHAR(50),
    record_id INT,
    old_values TEXT,
    new_values TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id),
    INDEX idx_admin (admin_id),
    INDEX idx_created (created_at)
);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Engine Parts', 'Engine components and accessories for classic motorcycles'),
('Electrical', 'Electrical components, wiring, and ignition systems'),
('Suspension', 'Suspension parts, shocks, and fork components'),
('Brakes', 'Brake components, pads, and hydraulic systems'),
('Body Parts', 'Body panels, fairings, and accessories'),
('Exhaust', 'Exhaust systems, pipes, and mufflers'),
('Transmission', 'Clutch, gearbox, and transmission components'),
('Fuel System', 'Carburetors, fuel tanks, and fuel delivery systems');

-- Insert sample products
INSERT INTO products (name, description, price, category_id, brand, stock_quantity, sku, featured) VALUES
('Honda CB750 Piston Kit', 'Complete piston kit for Honda CB750 classic motorcycles. Includes pistons, rings, pins, and clips. High-quality aftermarket replacement.', 2500.00, 1, 'Honda', 10, 'HON-CB750-PST-001', TRUE),
('Yamaha XS650 Points Set', 'Ignition points set for Yamaha XS650. Original specification replacement for reliable ignition timing.', 450.00, 2, 'Yamaha', 25, 'YAM-XS650-PTS-001', TRUE),
('Kawasaki Z1 Shock Absorber', 'Rear shock absorber for Kawasaki Z1. Progressive spring rate for improved handling and comfort.', 3200.00, 3, 'Kawasaki', 8, 'KAW-Z1-SHK-001', FALSE),
('Suzuki GT750 Brake Pads', 'Front brake pads for Suzuki GT750. Semi-metallic compound for excellent stopping power.', 800.00, 4, 'Suzuki', 15, 'SUZ-GT750-BRK-001', TRUE),
('Honda CB350 Gas Tank', 'Fuel tank for Honda CB350. Steel construction with original mounting points. Requires painting.', 8500.00, 5, 'Honda', 3, 'HON-CB350-TNK-001', FALSE),
('Universal Chrome Exhaust Pipe', 'Chrome exhaust pipe suitable for various classic bikes. 38mm diameter with universal mounting.', 1200.00, 6, 'Universal', 20, 'UNI-CHR-EXH-001', TRUE),
('Honda CB750 Clutch Kit', 'Complete clutch kit for Honda CB750. Includes friction plates, steel plates, and springs.', 1800.00, 7, 'Honda', 12, 'HON-CB750-CLT-001', FALSE),
('Yamaha XS650 Carburetor', 'Rebuilt carburetor for Yamaha XS650. Mikuni VM34 with new jets and gaskets.', 4500.00, 8, 'Yamaha', 6, 'YAM-XS650-CRB-001', TRUE)
('Kawasaki Z900 Clutch Cable', 'Durable clutch cable for Kawasaki Z900. Smooth operation and direct fit.', 650.00, 7, 'Kawasaki', 18, 'KAW-Z900-CLT-001', FALSE),
('Suzuki GS750 Ignition Coil', 'High-performance ignition coil for Suzuki GS750. Improves spark strength and reliability.', 1300.00, 2, 'Suzuki', 10, 'SUZ-GS750-IGN-001', TRUE),
('Yamaha RD350 Rear Fender', 'Metal rear fender for Yamaha RD350. Original shape, ready for paint.', 2200.00, 5, 'Yamaha', 5, 'YAM-RD350-FND-001', FALSE),
('Honda CB550 Front Fork Kit', 'Rebuild kit for Honda CB550 front forks. Includes seals, bushings, and springs.', 1700.00, 3, 'Honda', 7, 'HON-CB550-FRK-001', TRUE),
('Universal LED Tail Light', 'Compact LED tail light with integrated brake and turn signals. Fits most classic bikes.', 950.00, 2, 'Universal', 30, 'UNI-LED-TL-001', TRUE),
('Yamaha XS750 Gear Shifter', 'Replacement gear shifter lever for Yamaha XS750. OEM-style finish.', 780.00, 7, 'Yamaha', 12, 'YAM-XS750-GSH-001', FALSE),
('Honda CL350 Muffler Set', 'Chrome muffler set for Honda CL350. Classic scrambler style.', 3100.00, 6, 'Honda', 6, 'HON-CL350-MUF-001', TRUE),
('Suzuki GT550 Fuel Petcock', 'OEM-style petcock for Suzuki GT550. Two-position valve with reserve function.', 500.00, 8, 'Suzuki', 20, 'SUZ-GT550-PET-001', FALSE),
('Kawasaki H1 Front Brake Lever', 'Front brake lever for Kawasaki H1. Aluminum finish with pivot hardware.', 350.00, 4, 'Kawasaki', 25, 'KAW-H1-BRK-001', TRUE),
('Universal Motorcycle Mirror Set', 'Round chrome mirrors with 10mm thread. Suitable for most motorcycle handlebars.', 600.00, 5, 'Universal', 50, 'UNI-CHR-MIR-001', TRUE);


-- Insert admin user (password: admin123)
INSERT INTO users (username, email, password, first_name, last_name, is_admin, is_active) VALUES
('admin', 'admin@motorcycleparts.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', TRUE, TRUE);

-- Insert sample customer (password: customer123)
INSERT INTO users (username, email, password, first_name, last_name, phone, address, city, province, postal_code, is_active) VALUES
('customer', 'customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan', 'Dela Cruz', '+63 912 345 6789', '123 Rizal Street', 'Manila', 'Metro Manila', '1000', TRUE);
