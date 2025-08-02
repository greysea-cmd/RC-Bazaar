<?php
class Order {
    private $conn;
    private $table = 'orders';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (buyer_id, seller_id, book_id, quantity, unit_price, total_amount, shipping_address, payment_method, notes) 
                  VALUES (:buyer_id, :seller_id, :book_id, :quantity, :unit_price, :total_amount, :shipping_address, :payment_method, :notes)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':buyer_id', $data['buyer_id']);
        $stmt->bindParam(':seller_id', $data['seller_id']);
        $stmt->bindParam(':book_id', $data['book_id']);
        $stmt->bindParam(':quantity', $data['quantity']);
        $stmt->bindParam(':unit_price', $data['unit_price']);
        $stmt->bindParam(':total_amount', $data['total_amount']);
        $stmt->bindParam(':shipping_address', $data['shipping_address']);
        $stmt->bindParam(':payment_method', $data['payment_method']);
        $stmt->bindParam(':notes', $data['notes']);
        
        return $stmt->execute();
    }

    public function getUserOrders($user_id, $type = 'buyer') {
        $field = ($type === 'buyer') ? 'buyer_id' : 'seller_id';
        
        $query = "SELECT o.*, b.title as book_title, b.author as book_author, b.image_url,
                         buyer.username as buyer_name, seller.username as seller_name
                  FROM " . $this->table . " o
                  JOIN books b ON o.book_id = b.id
                  JOIN users buyer ON o.buyer_id = buyer.id
                  JOIN users seller ON o.seller_id = seller.id
                  WHERE o.$field = :user_id
                  ORDER BY o.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderById($id) {
        $query = "SELECT o.*, b.title as book_title, b.author as book_author, b.image_url,
                         buyer.username as buyer_name, buyer.first_name as buyer_first_name, buyer.last_name as buyer_last_name,
                         seller.username as seller_name, seller.first_name as seller_first_name, seller.last_name as seller_last_name
                  FROM " . $this->table . " o
                  JOIN books b ON o.book_id = b.id
                  JOIN users buyer ON o.buyer_id = buyer.id
                  JOIN users seller ON o.seller_id = seller.id
                  WHERE o.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateOrderStatus($id, $status) {
        $query = "UPDATE " . $this->table . " SET order_status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    public function updatePaymentStatus($id, $status) {
        $query = "UPDATE " . $this->table . " SET payment_status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    public function getAllOrders() {
        $query = "SELECT o.*, b.title as book_title, b.author as book_author,
                         buyer.username as buyer_name, seller.username as seller_name
                  FROM " . $this->table . " o
                  JOIN books b ON o.book_id = b.id
                  JOIN users buyer ON o.buyer_id = buyer.id
                  JOIN users seller ON o.seller_id = seller.id
                  ORDER BY o.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}