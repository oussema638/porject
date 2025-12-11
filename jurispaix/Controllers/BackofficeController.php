<?php
// Controllers/BackofficeController.php
// Handles admin operations for reclamations

if (!isset($_SESSION)) {
    session_start();
}

require_once __DIR__ . '/../Models/Reclamation.php';

// Handle direct controller calls
if (basename($_SERVER['PHP_SELF']) === 'BackofficeController.php') {
    BackofficeController::route();
    exit;
}

class BackofficeController {
    public $reclamationModel;

    public function __construct() {
        $this->reclamationModel = new Reclamation();
    }
    
    /**
     * Retrieve flash messages and clear them from the session
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
            case 'accept':
                $controller->accept();
                break;
            case 'refuse':
                $controller->refuse();
                break;
            default:
                $controller->showPending();
                break;
        }
    }

    /**
     * Display pending reclamations (status = 'enattente')
     */
    public function showPending() {
        $reclamations = $this->reclamationModel->getByStatus('enattente');
        $flash = $this->getFlashMessages();
        $success = $flash['success'];
        $error = $flash['error'];
        require __DIR__ . '/../Views/backoffice.php';
    }

    /**
     * Accept a reclamation
     */
    public function accept() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /jurispaix/router.php?route=backoffice');
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID manquant';
            header('Location: /jurispaix/router.php?route=backoffice');
            exit;
        }

        try {
            $this->reclamationModel->accept($id);
            $_SESSION['success'] = 'Réclamation acceptée et mise en traitement';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
        }

        header('Location: /jurispaix/router.php?route=backoffice');
        exit;
    }

    /**
     * Refuse a reclamation
     */
    public function refuse() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /jurispaix/router.php?route=backoffice');
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID manquant';
            header('Location: /jurispaix/router.php?route=backoffice');
            exit;
        }

        try {
            $this->reclamationModel->refuse($id);
            $_SESSION['success'] = 'Réclamation refusée';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
        }

        header('Location: /jurispaix/router.php?route=backoffice');
        exit;
    }
}
?>

