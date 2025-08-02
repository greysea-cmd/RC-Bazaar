<?php
class Dispute {
    private $conn;
    private $table = 'disputes';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (order_id, complainant_id, respondent_id, dispute_type, description) 
                  VALUES (:order_id, :complainant_id, :respondent_id, :dispute_type, :description)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $data['order_id']);
        $stmt->bindParam(':complainant_id', $data['complainant_id']);
        $stmt->bindParam(':respondent_id', $data['respondent_id']);
        $stmt->bindParam(':dispute_type', $data['dispute_type']);
        $stmt->bindParam(':description', $data['description']);
        
        return $stmt->execute();
    }

    public function getAllDisputes() {
        $query = "SELECT d.*, o.id as order_number, b.title as book_title,
                         complainant.username as complainant_name, respondent.username as respondent_name
                  FROM " . $this->table . " d
                  JOIN orders o ON d.order_id = o.id
                  JOIN books b ON o.book_id = b.id
                  JOIN users complainant ON d.complainant_id = complainant.id
                  JOIN users respondent ON d.respondent_id = respondent.id
                  ORDER BY d.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDisputeById($id) {
        $query = "SELECT d.*, o.id as order_number, b.title as book_title, b.author as book_author,
                         complainant.username as complainant_name, complainant.first_name as complainant_first_name,
                         respondent.username as respondent_name, respondent.first_name as respondent_first_name
                  FROM " . $this->table . " d
                  JOIN orders o ON d.order_id = o.id
                  JOIN books b ON o.book_id = b.id
                  JOIN users complainant ON d.complainant_id = complainant.id
                  JOIN users respondent ON d.respondent_id = respondent.id
                  WHERE d.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
