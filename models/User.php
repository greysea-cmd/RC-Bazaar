<?php
// models/User.php
class User {
    private $conn;
    private $table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (username, email, password, first_name, last_name, phone, address, city, state, zipcode, user_type) 
                  VALUES (:username, :email, :password, :first_name, :last_name, :phone, :address, :city, :state, :zipcode, :user_type)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', password_hash($data['password'], HASH_ALGO));
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':city', $data['city']);
        $stmt->bindParam(':state', $data['state']);
        $stmt->bindParam(':zipcode', $data['zipcode']);
        $stmt->bindParam(':user_type', $data['user_type']);
        
        return $stmt->execute();
    }

    public function login($email, $password) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email AND status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function getUserById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateDisputeStatus($id, $status, $admin_id = null, $admin_notes = null, $resolution = null) {
        $query = "UPDATE " . $this->table . " SET status = :status, admin_id = :admin_id, admin_notes = :admin_notes, resolution = :resolution WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':admin_id', $admin_id);
        $stmt->bindParam(':admin_notes', $admin_notes);
        $stmt->bindParam(':resolution', $resolution);
        return $stmt->execute();
    }

    public function getUserDisputes($user_id) {
        $query = "SELECT d.*, o.id as order_number, b.title as book_title,
                         complainant.username as complainant_name, respondent.username as respondent_name
                  FROM " . $this->table . " d
                  JOIN orders o ON d.order_id = o.id
                  JOIN books b ON o.book_id = b.id
                  JOIN users complainant ON d.complainant_id = complainant.id
                  JOIN users respondent ON d.respondent_id = respondent.id
                  WHERE d.complainant_id = :user_id OR d.respondent_id = :user_id
                  ORDER BY d.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateProfile($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET first_name = :first_name, last_name = :last_name, phone = :phone, 
                      address = :address, city = :city, state = :state, zipcode = :zipcode 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':city', $data['city']);
        $stmt->bindParam(':state', $data['state']);
        $stmt->bindParam(':zipcode', $data['zipcode']);
        
        return $stmt->execute();
    }

    public function getAllUsers($limit = null, $offset = null) {
        $query = "SELECT id, username, email, first_name, last_name, user_type, status, rating, created_at 
                  FROM " . $this->table . " ORDER BY created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit";
            if ($offset) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            if ($offset) {
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUserStatus($id, $status) {
        $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }
}