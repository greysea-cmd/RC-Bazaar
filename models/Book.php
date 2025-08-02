<?php
class Book {
    private $conn;
    private $table = 'books';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (seller_id, title, author, isbn, category_id, condition_type, description, price, quantity, image_url) 
                  VALUES (:seller_id, :title, :author, :isbn, :category_id, :condition_type, :description, :price, :quantity, :image_url)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':seller_id', $data['seller_id']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':author', $data['author']);
        $stmt->bindParam(':isbn', $data['isbn']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':condition_type', $data['condition_type']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':quantity', $data['quantity']);
        $stmt->bindParam(':image_url', $data['image_url']);
        
        return $stmt->execute();
    }

    public function getApprovedBooks($search = null, $category = null, $limit = null, $offset = null) {
        $query = "SELECT b.*, c.name as category_name, u.username as seller_name, u.rating as seller_rating 
                  FROM " . $this->table . " b 
                  LEFT JOIN categories c ON b.category_id = c.id 
                  LEFT JOIN users u ON b.seller_id = u.id 
                  WHERE b.status = 'approved' AND b.quantity > 0";
        
        if ($search) {
            $query .= " AND (b.title LIKE :search OR b.author LIKE :search OR b.isbn LIKE :search)";
        }
        
        if ($category) {
            $query .= " AND b.category_id = :category";
        }
        
        $query .= " ORDER BY b.created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit";
            if ($offset) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($search) {
            $search_param = "%$search%";
            $stmt->bindParam(':search', $search_param);
        }
        
        if ($category) {
            $stmt->bindParam(':category', $category);
        }
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            if ($offset) {
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBookById($id) {
        $query = "SELECT b.*, c.name as category_name, u.username as seller_name, u.rating as seller_rating, 
                         u.first_name, u.last_name, u.city, u.state 
                  FROM " . $this->table . " b 
                  LEFT JOIN categories c ON b.category_id = c.id 
                  LEFT JOIN users u ON b.seller_id = u.id 
                  WHERE b.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getSellerBooks($seller_id) {
        $query = "SELECT b.*, c.name as category_name 
                  FROM " . $this->table . " b 
                  LEFT JOIN categories c ON b.category_id = c.id 
                  WHERE b.seller_id = :seller_id 
                  ORDER BY b.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':seller_id', $seller_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingBooks() {
        $query = "SELECT b.*, c.name as category_name, u.username as seller_name 
                  FROM " . $this->table . " b 
                  LEFT JOIN categories c ON b.category_id = c.id 
                  LEFT JOIN users u ON b.seller_id = u.id 
                  WHERE b.status = 'pending' 
                  ORDER BY b.created_at ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateBookStatus($id, $status, $admin_notes = null) {
        $query = "UPDATE " . $this->table . " SET status = :status, admin_notes = :admin_notes WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':admin_notes', $admin_notes);
        return $stmt->execute();
    }

    public function updateQuantity($id, $quantity) {
        $query = "UPDATE " . $this->table . " SET quantity = :quantity WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':quantity', $quantity);
        return $stmt->execute();
    }
}