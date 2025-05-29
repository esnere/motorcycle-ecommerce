<?php
class Product {
    private $conn;
    private $table = 'products';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllProducts($limit = null, $offset = 0, $category_id = null, $search = null, $featured_only = false) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.is_active = 1";
        
        if ($category_id) {
            $query .= " AND p.category_id = :category_id";
        }
        
        if ($search) {
            $query .= " AND (p.name LIKE :search OR p.description LIKE :search2 OR p.brand LIKE :search3 OR p.sku LIKE :search4)";
        }
        
        if ($featured_only) {
            $query .= " AND p.featured = 1";
        }
        
        $query .= " ORDER BY p.created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($category_id) {
            $stmt->bindParam(':category_id', $category_id);
        }
        
        if ($search) {
            $search_term = "%$search%";
            $stmt->bindParam(':search', $search_term);
            $stmt->bindParam(':search2', $search_term);
            $stmt->bindParam(':search3', $search_term);
            $stmt->bindParam(':search4', $search_term);
        }
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getProductById($id) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.id = :id AND p.is_active = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getFeaturedProducts($limit = 6) {
        return $this->getAllProducts($limit, 0, null, null, true);
    }

    public function getProductCount($category_id = null, $search = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE is_active = 1";
        
        if ($category_id) {
            $query .= " AND category_id = :category_id";
        }
        
        if ($search) {
            $query .= " AND (name LIKE :search OR description LIKE :search2 OR brand LIKE :search3 OR sku LIKE :search4)";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($category_id) {
            $stmt->bindParam(':category_id', $category_id);
        }
        
        if ($search) {
            $search_term = "%$search%";
            $stmt->bindParam(':search', $search_term);
            $stmt->bindParam(':search2', $search_term);
            $stmt->bindParam(':search3', $search_term);
            $stmt->bindParam(':search4', $search_term);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }

    public function getCategories() {
        $query = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCategoryById($id) {
        $query = "SELECT * FROM categories WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // Admin functions
    public function createProduct($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (name, description, price, category_id, brand, model, year_from, year_to, 
                   stock_quantity, sku, image, weight, dimensions, featured) 
                  VALUES (:name, :description, :price, :category_id, :brand, :model, :year_from, :year_to, 
                          :stock_quantity, :sku, :image, :weight, :dimensions, :featured)";
        
        $stmt = $this->conn->prepare($query);
        
        try {
            $result = $stmt->execute([
                ':name' => $data['name'],
                ':description' => $data['description'],
                ':price' => $data['price'],
                ':category_id' => $data['category_id'],
                ':brand' => $data['brand'],
                ':model' => $data['model'] ?? null,
                ':year_from' => $data['year_from'] ?? null,
                ':year_to' => $data['year_to'] ?? null,
                ':stock_quantity' => $data['stock_quantity'],
                ':sku' => $data['sku'],
                ':image' => $data['image'] ?? null,
                ':weight' => $data['weight'] ?? null,
                ':dimensions' => $data['dimensions'] ?? null,
                ':featured' => $data['featured'] ?? 0
            ]);
            
            return $result ? $this->conn->lastInsertId() : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateProduct($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET name = :name, description = :description, price = :price, 
                      category_id = :category_id, brand = :brand, model = :model, 
                      year_from = :year_from, year_to = :year_to, stock_quantity = :stock_quantity, 
                      sku = :sku, image = :image, weight = :weight, dimensions = :dimensions, 
                      featured = :featured 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        try {
            return $stmt->execute([
                ':id' => $id,
                ':name' => $data['name'],
                ':description' => $data['description'],
                ':price' => $data['price'],
                ':category_id' => $data['category_id'],
                ':brand' => $data['brand'],
                ':model' => $data['model'] ?? null,
                ':year_from' => $data['year_from'] ?? null,
                ':year_to' => $data['year_to'] ?? null,
                ':stock_quantity' => $data['stock_quantity'],
                ':sku' => $data['sku'],
                ':image' => $data['image'] ?? null,
                ':weight' => $data['weight'] ?? null,
                ':dimensions' => $data['dimensions'] ?? null,
                ':featured' => $data['featured'] ?? 0
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteProduct($id) {
        $query = "UPDATE " . $this->table . " SET is_active = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    public function updateStock($product_id, $quantity_change, $reason = null) {
        try {
            // Get current stock
            $query = "SELECT stock_quantity FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $product_id]);
            $current_stock = $stmt->fetchColumn();
            
            if ($current_stock === false) {
                return false;
            }
            
            // Calculate new stock
            $new_stock = $current_stock + $quantity_change;
            
            // Prevent negative stock
            if ($new_stock < 0) {
                $new_stock = 0;
            }
            
            // Update stock
            $query = "UPDATE " . $this->table . " SET stock_quantity = :stock WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':stock' => $new_stock,
                ':id' => $product_id
            ]);
            
            // Log stock change if reason provided
            if ($result && $reason) {
                $this->logStockChange($product_id, $current_stock, $new_stock, $reason);
            }
            
            return $result;
            
        } catch (Exception $e) {
            return false;
        }
    }

    // Add method for direct stock setting (used by inventory management)
    public function setStock($product_id, $new_stock, $reason = 'manual_adjustment') {
        try {
            // Get current stock
            $query = "SELECT stock_quantity FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $product_id]);
            $current_stock = $stmt->fetchColumn();
            
            if ($current_stock === false) {
                return false;
            }
            
            // Update stock
            $query = "UPDATE " . $this->table . " SET stock_quantity = :stock WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':stock' => $new_stock,
                ':id' => $product_id
            ]);
            
            // Log stock change
            if ($result) {
                $this->logStockChange($product_id, $current_stock, $new_stock, $reason);
            }
            
            return $result;
            
        } catch (Exception $e) {
            return false;
        }
    }

    private function logStockChange($product_id, $old_stock, $new_stock, $reason) {
        try {
            // Check if stock_logs table exists, if not create it
            $this->createStockLogsTable();
            
            $query = "INSERT INTO stock_logs (product_id, old_stock, new_stock, change_amount, reason, created_at) 
                      VALUES (:product_id, :old_stock, :new_stock, :change_amount, :reason, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':product_id' => $product_id,
                ':old_stock' => $old_stock,
                ':new_stock' => $new_stock,
                ':change_amount' => $new_stock - $old_stock,
                ':reason' => $reason
            ]);
        } catch (Exception $e) {
            // Log error but don't fail the main operation
            error_log("Failed to log stock change: " . $e->getMessage());
        }
    }

    private function createStockLogsTable() {
        try {
            $query = "CREATE TABLE IF NOT EXISTS stock_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                old_stock INT NOT NULL,
                new_stock INT NOT NULL,
                change_amount INT NOT NULL,
                reason VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id)
            )";
            $this->conn->exec($query);
        } catch (Exception $e) {
            error_log("Failed to create stock_logs table: " . $e->getMessage());
        }
    }

    // Search products
    public function searchProducts($keywords, $limit = null, $offset = 0) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.is_active = 1 AND (p.name LIKE :search OR p.description LIKE :search2 OR p.brand LIKE :search3)
                  ORDER BY p.created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($query);
        
        $search_term = "%$keywords%";
        $stmt->bindParam(':search', $search_term);
        $stmt->bindParam(':search2', $search_term);
        $stmt->bindParam(':search3', $search_term);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get products by category
    public function getProductsByCategory($category_id, $limit = null, $offset = 0) {
        return $this->getAllProducts($limit, $offset, $category_id);
    }

    // Check if product exists
    public function productExists($id) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " WHERE id = :id AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    // Get low stock products
    public function getLowStockProducts($threshold = 10) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.is_active = 1 AND p.stock_quantity <= :threshold
                  ORDER BY p.stock_quantity ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':threshold' => $threshold]);
        return $stmt->fetchAll();
    }

    // Get out of stock products
    public function getOutOfStockProducts() {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.is_active = 1 AND p.stock_quantity = 0
                  ORDER BY p.name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
