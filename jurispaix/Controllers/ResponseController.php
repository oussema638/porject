<?php
// Controllers/ResponseController.php
// Handles response operations for reclamations using separate reponses table

if (!isset($_SESSION)) {
    session_start();
}

require_once __DIR__ . '/../Models/Reclamation.php';
require_once __DIR__ . '/../Models/Response.php';
require_once __DIR__ . '/../Services/MailService.php';

// Handle direct controller calls
if (basename($_SERVER['PHP_SELF']) === 'ResponseController.php') {
    ResponseController::route();
    exit;
}

class ResponseController {
    public $reclamationModel;
    public $responseModel;
    private $mailService;

    public function __construct() {
        $this->reclamationModel = new Reclamation();
        $this->responseModel = new Response();
        $this->mailService = new MailService();
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
            case 'saveResponse':
                $controller->saveResponse();
                break;
            case 'deleteResponse':
                $controller->deleteResponse();
                break;
            default:
                $controller->showInTreatment();
                break;
        }
    }

    /**
     * Display reclamations in treatment (status = 'traitement')
     * with all related responses
     */
    public function showInTreatment() {
        $reclamations = $this->reclamationModel->getByStatus('traitement');
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

        // map available to the view
        $responsesByReclamation = $responsesByReclamation;

        require __DIR__ . '/../Views/traitement_back.php';
    }

    /**
     * Save (create or update) a response for a reclamation
     */
    public function saveResponse() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /jurispaix/router.php?route=traitement');
            exit;
        }

        $reclamationId = $_POST['id'] ?? null;
        $responseId = $_POST['response_id'] ?? null;
        $reponse = $_POST['reponse'] ?? '';
        $auteur = $_POST['auteur'] ?? 'admin';

        if (!$reclamationId || empty(trim($reponse))) {
            $_SESSION['error'] = 'ID réclamation ou réponse manquant';
            header('Location: /jurispaix/router.php?route=traitement');
            exit;
        }

        try {
            if ($responseId) {
                // Update modifier + updated_at
                $this->responseModel->update($responseId, $reponse);
                $_SESSION['success'] = 'Réponse mise à jour avec succès';
            } else {
                // New response, keep original texte and auteur
                $this->responseModel->add($reclamationId, $reponse, $auteur);
                $sent = $this->mailService->sendAdminResponse((int)$reclamationId, $reponse);
                if (!$sent) {
                    error_log('Mail send failed (admin response): ' . ($this->mailService->getLastError() ?? 'unknown error'));
                }
                $_SESSION['success'] = 'Réponse ajoutée avec succès';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
        }

        header('Location: /jurispaix/router.php?route=traitement');
        exit;
    }

    /**
     * Delete a response
     */
    public function deleteResponse() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /jurispaix/router.php?route=traitement');
            exit;
        }

        $responseId = $_POST['response_id'] ?? null;

        if (!$responseId) {
            $_SESSION['error'] = 'ID de réponse manquant';
            header('Location: /jurispaix/router.php?route=traitement');
            exit;
        }

        try {
            $this->responseModel->delete($responseId);
            $_SESSION['success'] = 'Réponse supprimée avec succès';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
        }

        header('Location: /jurispaix/router.php?route=traitement');
        exit;
    }
}
?>

