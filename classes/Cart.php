<?php
class Cart {
    private $conn;
    private $table = 'cart';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function addToCart($user_id, $product_id, $quantity = 1) {
        // Check if item already exists in cart
        $existing = $this->getCartItem($user_id, $product_id);
        
        if ($existing) {
            // Update quantity
            return $this->updateQuantity($user_id, $product_id, $existing['quantity'] + $quantity);
        } else {
            // Add new item
            $query = "INSERT INTO " . $this->table . " (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
            $stmt = $this->conn->prepare($query);
            
            try {
                return $stmt->execute([
                    ':user_id' => $user_id,
                    ':product_id' => $product_id,
                    ':quantity' => $quantity
                ]);
            } catch (PDOException $e) {
                return false;
            }
        }
    }

    public function getCartItems($user_id) {
        $query = "SELECT c.*, p.name, p.price, p.image, p.stock_quantity, p.sku 
                  FROM " . $this->table . " c 
                  JOIN products p ON c.product_id = p.id 
                  WHERE c.user_id = :user_id AND p.is_active = 1 
                  ORDER BY c.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll();
    }

    public function getCartItem($user_id, $product_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $user_id, ':product_id' => $product_id]);
        return $stmt->fetch();
    }

    public function updateQuantity($user_id, $product_id, $quantity) {
        if ($quantity <= 0) {
            return $this->removeFromCart($user_id, $product_id);
        }
        
        $query = "UPDATE " . $this->table . " SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        
        try {
            return $stmt->execute([
                ':quantity' => $quantity,
                ':user_id' => $user_id,
                ':product_id' => $product_id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function removeFromCart($user_id, $product_id) {
        $query = "DELETE FROM " . $this->table . " WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':user_id' => $user_id, ':product_id' => $product_id]);
    }

    public function clearCart($user_id) {
        $query = "DELETE FROM " . $this->table . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':user_id' => $user_id]);
    }

    public function getCartTotal($user_id) {
        $query = "SELECT SUM(c.quantity * p.price) as total 
                  FROM " . $this->table . " c 
                  JOIN products p ON c.product_id = p.id 
                  WHERE c.user_id = :user_id AND p.is_active = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getCartCount($user_id) {
        $query = "SELECT SUM(quantity) as count FROM " . $this->table . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    public function validateCartItems($user_id) {
        $items = $this->getCartItems($user_id);
        $errors = [];
        
        foreach ($items as $item) {
            if ($item['quantity'] > $item['stock_quantity']) {
                $errors[] = "Insufficient stock for {$item['name']}. Available: {$item['stock_quantity']}";
            }
        }
        
        return $errors;
    }
}
?>
