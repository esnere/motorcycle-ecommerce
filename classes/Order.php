<?php
require_once 'Product.php';
require_once 'Cart.php';

class Order {
    private $conn;
    private $table = 'orders';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createOrder($data) {
        try {
            $this->conn->beginTransaction();
            
            // Generate order number
            $order_number = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Insert order
            $query = "INSERT INTO " . $this->table . " 
                      (user_id, order_number, total_amount, payment_method, shipping_address, billing_address, notes) 
                      VALUES (:user_id, :order_number, :total_amount, :payment_method, :shipping_address, :billing_address, :notes)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':user_id' => $data['user_id'],
                ':order_number' => $order_number,
                ':total_amount' => $data['total_amount'],
                ':payment_method' => $data['payment_method'],
                ':shipping_address' => $data['shipping_address'],
                ':billing_address' => $data['billing_address'],
                ':notes' => $data['notes'] ?? null
            ]);
            
            $order_id = $this->conn->lastInsertId();
            
            // Insert order items and update stock
            $cart = new Cart($this->conn);
            $cart_items = $cart->getCartItems($data['user_id']);
            
            $product = new Product($this->conn);
            
            foreach ($cart_items as $item) {
                $this->addOrderItem($order_id, $item['product_id'], $item['quantity'], $item['price']);
                $product->updateStock($item['product_id'], -$item['quantity']);
            }
            
            // Clear cart
            $cart->clearCart($data['user_id']);
            
            $this->conn->commit();
            return ['success' => true, 'order_id' => $order_id, 'order_number' => $order_number];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Failed to create order'];
        }
    }

    private function addOrderItem($order_id, $product_id, $quantity, $price) {
        $query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                  VALUES (:order_id, :product_id, :quantity, :price)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':order_id' => $order_id,
            ':product_id' => $product_id,
            ':quantity' => $quantity,
            ':price' => $price
        ]);
    }

    public function getUserOrders($user_id, $limit = null, $offset = 0) {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id ORDER BY created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getOrderById($id, $user_id = null) {
        $query = "SELECT o.*, u.first_name, u.last_name, u.email 
                  FROM " . $this->table . " o 
                  JOIN users u ON o.user_id = u.id 
                  WHERE o.id = :id";
        
        if ($user_id) {
            $query .= " AND o.user_id = :user_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($user_id) {
            $stmt->bindParam(':user_id', $user_id);
        }
        
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getOrderByNumber($order_number, $user_id = null) {
        $query = "SELECT o.*, u.first_name, u.last_name, u.email 
              FROM " . $this->table . " o 
              JOIN users u ON o.user_id = u.id 
              WHERE o.order_number = :order_number";
    
        if ($user_id) {
            $query .= " AND o.user_id = :user_id";
        }
    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_number', $order_number);
    
        if ($user_id) {
            $stmt->bindParam(':user_id', $user_id);
        }
    
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getOrderItems($order_id) {
        $query = "SELECT oi.*, p.name, p.image, p.sku 
                  FROM order_items oi 
                  JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetchAll();
    }

    public function updateOrderStatus($order_id, $status) {
        $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':status' => $status, ':id' => $order_id]);
    }

    public function updatePaymentStatus($order_id, $payment_status) {
        $query = "UPDATE " . $this->table . " SET payment_status = :payment_status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':payment_status' => $payment_status, ':id' => $order_id]);
    }

    // Admin functions
    public function getAllOrders($limit = null, $offset = 0, $status = null) {
        $query = "SELECT o.*, u.first_name, u.last_name, u.email 
                  FROM " . $this->table . " o 
                  JOIN users u ON o.user_id = u.id";
        
        if ($status) {
            $query .= " WHERE o.status = :status";
        }
        
        $query .= " ORDER BY o.created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($status) {
            $stmt->bindParam(':status', $status);
        }
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getOrderCount($status = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        
        if ($status) {
            $query .= " WHERE status = :status";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($status) {
            $stmt->bindParam(':status', $status);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }

    public function getOrderStats() {
        $query = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_revenue,
                    AVG(total_amount) as avg_order_value,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
                    COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing_orders,
                    COUNT(CASE WHEN status = 'shipped' THEN 1 END) as shipped_orders,
                    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered_orders
                  FROM " . $this->table;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getOrderDetails($orderId) {
        $query = "SELECT * FROM order_details WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query); 
        $stmt->bindParam(':order_id', $orderId); 
        $stmt->execute(); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC); 
    }
}
?>
