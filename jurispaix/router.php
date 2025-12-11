<?php
// router.php
// Main router for the application

session_start();

require_once __DIR__ . '/Controllers/ReclamationController.php';
require_once __DIR__ . '/Controllers/BackofficeController.php';
require_once __DIR__ . '/Controllers/ResponseController.php';

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);
$basePath = '/jurispaix';
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}
$path = trim($path, '/');
$route = $_GET['route'] ?? $path;

// Allow direct controller access for form submissions
if (strpos($path, 'Controllers/') === 0) {
    $file = __DIR__ . '/' . $path;
    if (is_file($file)) {
        require $file;
        exit;
    }
}

// Serve static assets when running through the router (e.g. PHP built-in server)
$staticFile = __DIR__ . '/' . $path;
if ($path && is_file($staticFile) && preg_match('/\.(css|js|png|jpe?g|gif|svg|webp)$/i', $path)) {
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp'
    ];
    $extension = strtolower(pathinfo($staticFile, PATHINFO_EXTENSION));
    if (isset($mimeTypes[$extension])) {
        header('Content-Type: ' . $mimeTypes[$extension]);
    }
    readfile($staticFile);
    exit;
}

switch ($route) {
    case '':
    case 'index.php':
    case 'ajouter':
    case 'ajouter_reclamation.php':
    case 'Views/ajouter_reclamation.php':
        $controller = new ReclamationController();
        $controller->showAddForm($_GET['edit'] ?? null);
        break;

    case 'mes_reclamations':
    case 'mes_reclamations.php':
    case 'Views/mes_reclamations.php':
        $controller = new ReclamationController();
        $controller->listUserReclamations();
        break;

    case 'backoffice':
    case 'backoffice.php':
    case 'Views/backoffice.php':
        $controller = new BackofficeController();
        $controller->showPending();
        break;

    case 'traitement':
    case 'traitement_back.php':
    case 'Views/traitement_back.php':
        $controller = new ResponseController();
        $controller->showInTreatment();
        break;

    default:
        $file = __DIR__ . '/' . $path;
        if ($path && is_file($file)) {
            require $file;
        } else {
            header('HTTP/1.0 404 Not Found');
            echo 'Page not found';
        }
        break;
}
?>

