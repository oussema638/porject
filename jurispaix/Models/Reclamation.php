<?php
// Models/Reclamation.php
// All database operations for reclamations using PDO

require_once __DIR__ . '/Database.php';

class Reclamation {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Add a new reclamation
     */
    public function add($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO reclamations (titre, texte, categorie, priorite, user_id, statut, created_at) 
            VALUES (:titre, :texte, :categorie, :priorite, :user_id, 'enattente', NOW())
        ");
        
        return $stmt->execute([
            ':titre' => $data['titre'],
            ':texte' => $data['texte'],
            ':categorie' => $data['categorie'],
            ':priorite' => $data['priorite'] ?? 'Normale',
            ':user_id' => $data['user_id']
        ]);
    }

    /**
     * Update an existing reclamation
     */
    public function update($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE reclamations 
            SET titre=:titre, texte=:texte, categorie=:categorie, priorite=:priorite 
            WHERE id=:id
        ");
        
        return $stmt->execute([
            ':titre' => $data['titre'],
            ':texte' => $data['texte'],
            ':categorie' => $data['categorie'],
            ':priorite' => $data['priorite'] ?? 'Normale',
            ':id' => $id
        ]);
    }

    /**
     * Delete a reclamation
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM reclamations WHERE id=:id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get a single reclamation by ID
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM reclamations WHERE id=:id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get all reclamations for a specific user
     */
    public function getAllByUser($userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM reclamations 
            WHERE user_id=:user_id 
            ORDER BY created_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all reclamations (for backoffice)
     */
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM reclamations ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * Get reclamations by status
     */
    public function getByStatus($status) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM reclamations 
            WHERE statut=:statut 
            ORDER BY created_at DESC
        ");
        $stmt->execute([':statut' => $status]);
        return $stmt->fetchAll();
    }

    /**
     * Accept a reclamation (set status to 'traitement')
     */
    public function accept($id) {
        $stmt = $this->pdo->prepare("
            UPDATE reclamations 
            SET statut='traitement' 
            WHERE id=:id
        ");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Refuse a reclamation (set status to 'refuse')
     */
    public function refuse($id) {
        $stmt = $this->pdo->prepare("
            UPDATE reclamations 
            SET statut='refuse' 
            WHERE id=:id
        ");
        return $stmt->execute([':id' => $id]);
    }

}
?>

