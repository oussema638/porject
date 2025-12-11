<?php
// Models/Response.php
// Handles all database operations for responses linked to reclamations

require_once __DIR__ . '/Database.php';

class Response {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->ensureTable();
    }

    /**
     * Ensure the reponses table exists with proper foreign key to reclamations.
     * Uses 'texte' as original text and 'modifier' for edited text.
     */
    private function ensureTable() {
        // Base table creation (id, reclamation_id, texte, auteur, created_at, updated_at, modifier)
        $sql = "
            CREATE TABLE IF NOT EXISTS reponses (
                id INT AUTO_INCREMENT PRIMARY KEY,
                reclamation_id INT NOT NULL,
                texte TEXT NOT NULL,
                auteur VARCHAR(20) NOT NULL DEFAULT 'admin',
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                modifier TEXT NULL,
                CONSTRAINT fk_reponses_reclamation
                    FOREIGN KEY (reclamation_id) REFERENCES reclamations(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        $this->pdo->exec($sql);

        // Ensure updated_at exists for legacy databases where it may be missing
        try {
            $this->pdo->query("SELECT updated_at FROM reponses LIMIT 1");
        } catch (PDOException $e) {
            try {
                $this->pdo->exec("ALTER TABLE reponses ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL");
            } catch (PDOException $e2) {
                // ignore if already present
            }
        }

        // Ensure auteur column exists
        try {
            $this->pdo->query("SELECT auteur FROM reponses LIMIT 1");
        } catch (PDOException $e) {
            try {
                $this->pdo->exec("ALTER TABLE reponses ADD COLUMN auteur VARCHAR(20) NOT NULL DEFAULT 'admin'");
            } catch (PDOException $e2) {
                // ignore
            }
        }

        // Ensure modifier column exists
        try {
            $this->pdo->query("SELECT modifier FROM reponses LIMIT 1");
        } catch (PDOException $e) {
            try {
                $this->pdo->exec("ALTER TABLE reponses ADD COLUMN modifier TEXT NULL");
            } catch (PDOException $e2) {
                // ignore
            }
        }
    }

    /**
     * Add a new response for a reclamation
     */
    public function add($reclamationId, $texte, $auteur = 'admin') {
        $stmt = $this->pdo->prepare("
            INSERT INTO reponses (reclamation_id, texte, auteur, created_at) 
            VALUES (:reclamation_id, :texte, :auteur, NOW())
        ");
        return $stmt->execute([
            ':reclamation_id' => $reclamationId,
            ':texte' => $texte,
            ':auteur' => $auteur
        ]);
    }

    /**
     * Update an existing response: store new text in 'modifier' and update updated_at
     */
    public function update($id, $texte) {
        $stmt = $this->pdo->prepare("
            UPDATE reponses 
            SET modifier = :texte, updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            ':texte' => $texte,
            ':id' => $id
        ]);
    }

    /**
     * Delete a response by ID
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM reponses WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get a single response by ID
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM reponses WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get all responses for one reclamation
     */
    public function getAllByReclamation($reclamationId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM reponses 
            WHERE reclamation_id = :reclamation_id
            ORDER BY created_at ASC
        ");
        $stmt->execute([':reclamation_id' => $reclamationId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all responses for several reclamations at once
     * Returns a flat list; caller can group by reclamation_id.
     */
    public function getAllByReclamationIds(array $ids) {
        if (empty($ids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
            SELECT * FROM reponses 
            WHERE reclamation_id IN ($placeholders)
            ORDER BY created_at ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($ids);
        return $stmt->fetchAll();
    }
}
?>


