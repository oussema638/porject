<?php
// Controllers/ReclamationController.php
// Handles user-facing reclamation operations

if (!isset($_SESSION)) {
    session_start();
}

require_once __DIR__ . '/../Models/Reclamation.php';
require_once __DIR__ . '/../Models/Response.php';
require_once __DIR__ . '/../Services/MailService.php';

// Handle direct controller calls
if (basename($_SERVER['PHP_SELF']) === 'ReclamationController.php') {
    ReclamationController::route();
    exit;
}

class ReclamationController {
    public $reclamationModel;
    public $responseModel;
    private $mailService;

    public function __construct() {
        $this->reclamationModel = new Reclamation();
        $this->responseModel = new Response();
        $this->mailService = new MailService();
    }
    
    /**
     * Retrieve one-time flash messages from the session
     */
    private function getFlashMessages() {
        $messages = [
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ];
        unset($_SESSION['success'], $_SESSION['error']);
        return $messages;
    }
    
    /**
     * Handle routing for this controller
     */
    public static function route() {
        if (!isset($_SESSION)) {
            session_start();
        }
        $controller = new self();
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'save':
                $controller->save();
                break;
            case 'delete':
                $controller->delete();
                break;
            default:
                $editId = $_GET['edit'] ?? null;
                $controller->showAddForm($editId);
                break;
        }
    }

    /**
     * Display the add reclamation form
     */
    public function showAddForm($editId = null) {
        $reclamation = null;
        if ($editId) {
            $reclamation = $this->reclamationModel->getById($editId);
            if (!$reclamation) {
                header('Location: /jurispaix/router.php?route=mes_reclamations');
                exit;
            }
        }
        $flash = $this->getFlashMessages();
        $success = $flash['success'];
        $error = $flash['error'];
        require __DIR__ . '/../Views/ajouter_reclamation.php';
    }

    /**
     * Handle form submission (add or update)
     */
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /jurispaix/router.php');
            exit;
        }

        $id = $_POST['id'] ?? null;
        $data = [
            'titre' => $_POST['titre'] ?? '',
            'texte' => $_POST['texte'] ?? '',
            'categorie' => $_POST['categorie'] ?? '',
            'priorite' => $_POST['priorite'] ?? 'Normale',
            'user_id' => $_POST['userId'] ?? 'user_001'
        ];

        // Validation
        if (empty($data['titre']) || empty($data['texte']) || empty($data['categorie'])) {
            $_SESSION['error'] = 'Tous les champs sont requis';
            $redirect = '/jurispaix/router.php' . ($id ? '?edit=' . urlencode($id) : '');
            header('Location: ' . $redirect);
            exit;
        }

        try {
            if ($id) {
                $this->reclamationModel->update($id, $data);
                $_SESSION['success'] = 'Réclamation modifiée avec succès';
            } else {
                $this->reclamationModel->add($data);
                $sent = $this->mailService->sendNewReclamation($data['titre'], $data['user_id']);
                if (!$sent) {
                    error_log('Mail send failed (new reclamation): ' . ($this->mailService->getLastError() ?? 'unknown error'));
                }
                $_SESSION['success'] = 'Réclamation ajoutée avec succès';
            }
            header('Location: /jurispaix/router.php?route=mes_reclamations');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
            $redirect = '/jurispaix/router.php' . ($id ? '?edit=' . urlencode($id) : '');
            header('Location: ' . $redirect);
            exit;
        }
    }

    /**
     * Delete a reclamation
     */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /jurispaix/router.php?route=mes_reclamations');
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID manquant';
            header('Location: /jurispaix/router.php?route=mes_reclamations');
            exit;
        }

        try {
            $this->reclamationModel->delete($id);
            $_SESSION['success'] = 'Réclamation supprimée avec succès';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
        }

        header('Location: /jurispaix/router.php?route=mes_reclamations');
        exit;
    }

    /**
     * Display user's reclamations
     */
    public function listUserReclamations($userId = 'user_001') {
        $reclamations = $this->reclamationModel->getAllByUser($userId);
        $ids = array_column($reclamations, 'id');
        $responsesByReclamation = [];
        if (!empty($ids)) {
            $responses = $this->responseModel->getAllByReclamationIds($ids);
            foreach ($responses as $resp) {
                $responsesByReclamation[$resp['reclamation_id']][] = $resp;
            }
        }
        $flash = $this->getFlashMessages();
        $success = $flash['success'];
        $error = $flash['error'];
        require __DIR__ . '/../Views/mes_reclamations.php';
    }
}
?>

