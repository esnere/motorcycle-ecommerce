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
            $query .= " AND MATCH(p.name, p.description, p.brand) AGAINST(:search IN NATURAL LANGUAGE MODE)";
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
            $stmt->bindParam(':search', $search);
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
            $query .= " AND MATCH(name, description, brand) AGAINST(:search IN NATURAL LANGUAGE MODE)";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($category_id) {
            $stmt->bindParam(':category_id', $category_id);
        }
        
        if ($search) {
            $stmt->bindParam(':search', $search);
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

    public function updateStock($product_id, $quantity) {
        $query = "UPDATE " . $this->table . " SET stock_quantity = stock_quantity - :quantity WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':quantity' => $quantity, ':id' => $product_id]);
    }
}
?>
