<?php
class User {
    private $conn;
    private $table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($data) {
        // Check if username or email already exists
        if ($this->usernameExists($data['username'])) {
            return ['success' => false, 'message' => 'Username already exists'];
        }
        
        if ($this->emailExists($data['email'])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }
        
        $query = "INSERT INTO " . $this->table . " 
                  (username, email, password, first_name, last_name, phone, address, city, province, postal_code) 
                  VALUES (:username, :email, :password, :first_name, :last_name, :phone, :address, :city, :province, :postal_code)";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash password
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        try {
            $result = $stmt->execute([
                ':username' => $data['username'],
                ':email' => $data['email'],
                ':password' => $hashed_password,
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':phone' => $data['phone'] ?? null,
                ':address' => $data['address'] ?? null,
                ':city' => $data['city'] ?? null,
                ':province' => $data['province'] ?? null,
                ':postal_code' => $data['postal_code'] ?? null
            ]);
            
            return ['success' => true, 'message' => 'Registration successful'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    public function login($username, $password) {
        // First check if is_active column exists
        $check_column = "SHOW COLUMNS FROM " . $this->table . " LIKE 'is_active'";
        $stmt = $this->conn->prepare($check_column);
        $stmt->execute();
        $has_active_column = $stmt->rowCount() > 0;
        
        // Build query based on whether is_active column exists
        if ($has_active_column) {
            $query = "SELECT id, username, email, password, first_name, last_name, is_admin 
                  FROM " . $this->table . " 
                  WHERE (username = :username OR email = :username) AND is_active = 1";
        } else {
            $query = "SELECT id, username, email, password, first_name, last_name, is_admin 
                  FROM " . $this->table . " 
                  WHERE (username = :username OR email = :username)";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':username' => $username]);
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }

    public function getUserById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function updateProfile($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET first_name = :first_name, last_name = :last_name, 
                      phone = :phone, address = :address, city = :city, 
                      province = :province, postal_code = :postal_code 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        try {
            return $stmt->execute([
                ':id' => $id,
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':phone' => $data['phone'],
                ':address' => $data['address'],
                ':city' => $data['city'],
                ':province' => $data['province'],
                ':postal_code' => $data['postal_code']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function changePassword($id, $current_password, $new_password) {
        // Verify current password
        $user = $this->getUserById($id);
        if (!password_verify($current_password, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
        
        $query = "UPDATE " . $this->table . " SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        try {
            $stmt->execute([':password' => $hashed_password, ':id' => $id]);
            return ['success' => true, 'message' => 'Password changed successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to change password'];
        }
    }

    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':email' => $email]);
        return $stmt->rowCount() > 0;
    }

    public function usernameExists($username) {
        $query = "SELECT id FROM " . $this->table . " WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':username' => $username]);
        return $stmt->rowCount() > 0;
    }

    // Admin functions
    public function getAllUsers($limit = null, $offset = 0, $search = null) {
        $query = "SELECT id, username, email, first_name, last_name, phone, city, province, is_admin, created_at 
                  FROM " . $this->table . " WHERE 1=1";
        
        if ($search) {
            $query .= " AND (username LIKE :search OR email LIKE :search OR first_name LIKE :search OR last_name LIKE :search)";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($search) {
            $search_term = "%$search%";
            $stmt->bindParam(':search', $search_term);
        }
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUserCount($search = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE 1=1";
        
        if ($search) {
            $query .= " AND (username LIKE :search OR email LIKE :search OR first_name LIKE :search OR last_name LIKE :search)";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($search) {
            $search_term = "%$search%";
            $stmt->bindParam(':search', $search_term);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }
}
?>
