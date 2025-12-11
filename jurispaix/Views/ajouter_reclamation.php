<?php
// Views/ajouter_reclamation.php
// Clean view - receives data from controller, fallback when direct access
if (!isset($_SESSION)) {
    session_start();
}

$editId = $_GET['edit'] ?? null;
$definedVars = get_defined_vars();
$hasReclamationVar = array_key_exists('reclamation', $definedVars);

if (!$hasReclamationVar) {
    require_once __DIR__ . '/../Controllers/ReclamationController.php';
    $controller = new ReclamationController();
    $reclamation = null;
    if ($editId) {
        $reclamation = $controller->reclamationModel->getById($editId);
        if (!$reclamation) {
            header('Location: mes_reclamations.php');
            exit;
        }
    }
}

$hasSuccessVar = array_key_exists('success', $definedVars);
$hasErrorVar = array_key_exists('error', $definedVars);

if (!$hasSuccessVar && isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
}
if (!$hasErrorVar && isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
}
unset($_SESSION['success'], $_SESSION['error']);

if (!isset($success)) {
    $success = null;
}
if (!isset($error)) {
    $error = null;
}
if (!isset($reclamation)) {
    $reclamation = null;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title><?php echo $reclamation ? 'Modifier' : 'Ajouter'; ?> Réclamation — jurisPaix</title>
<link rel="stylesheet" href="/jurispaix/Public/css/style.css">
<link rel="stylesheet" href="/jurispaix/Public/css/dashboard.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="dashboard-main">
  <div class="dashboard-container">
    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="dashboard-toolbar">
      <h2 style="color: #fff; margin: 0;"><?php echo $reclamation ? 'Modifier la réclamation' : 'Ajouter une nouvelle réclamation'; ?></h2>
      <a class="btn-primary" href="mes_reclamations.php">← Retour</a>
    </div>

    <form id="form-ajout" method="POST" action="/jurispaix/Controllers/ReclamationController.php?action=save" class="dashboard-form">
      <input type="hidden" name="userId" value="user_001">
      <?php if ($reclamation): ?>
        <input type="hidden" name="id" id="rec-id" value="<?php echo htmlspecialchars($reclamation['id']); ?>">
      <?php endif; ?>

      <div class="form-group">
        <label for="rec-titre">Titre</label>
        <input id="rec-titre" name="titre" type="text" placeholder="Ex: Problème facture / service ..." 
               value="<?php echo $reclamation ? htmlspecialchars($reclamation['titre']) : ''; ?>" required />
      </div>

      <div class="form-group">
        <label for="rec-texte">Détails</label>
        <textarea id="rec-texte" name="texte" rows="6" placeholder="Décrivez précisément votre réclamation..." required><?php echo $reclamation ? htmlspecialchars($reclamation['texte']) : ''; ?></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="rec-categorie">Catégorie</label>
          <select id="rec-categorie" name="categorie" required>
            <option value="">-- Choisir --</option>
            <option value="Service" <?php echo ($reclamation && $reclamation['categorie'] === 'Service') ? 'selected' : ''; ?>>Service</option>
            <option value="Facturation" <?php echo ($reclamation && $reclamation['categorie'] === 'Facturation') ? 'selected' : ''; ?>>Facturation</option>
            <option value="Comportement" <?php echo ($reclamation && $reclamation['categorie'] === 'Comportement') ? 'selected' : ''; ?>>Comportement</option>
            <option value="Technique" <?php echo ($reclamation && $reclamation['categorie'] === 'Technique') ? 'selected' : ''; ?>>Technique</option>
            <option value="Autre" <?php echo ($reclamation && $reclamation['categorie'] === 'Autre') ? 'selected' : ''; ?>>Autre</option>
          </select>
        </div>
        <div class="form-group">
          <label for="rec-priorite">Priorité</label>
          <select id="rec-priorite" name="priorite">
            <option value="Normale" <?php echo (!$reclamation || $reclamation['priorite'] === 'Normale') ? 'selected' : ''; ?>>Normale</option>
            <option value="Haute" <?php echo ($reclamation && $reclamation['priorite'] === 'Haute') ? 'selected' : ''; ?>>Haute</option>
            <option value="Urgente" <?php echo ($reclamation && $reclamation['priorite'] === 'Urgente') ? 'selected' : ''; ?>>Urgente</option>
          </select>
        </div>
      </div>

      <div class="form-actions">
        <button class="btn-primary" type="submit" id="submit-btn"><?php echo $reclamation ? 'Modifier' : 'Envoyer la réclamation'; ?></button>
        <a class="btn-primary" href="mes_reclamations.php" style="background: #6b7280;">Voir mes réclamations</a>
      </div>

      <div class="notice" style="margin-top: 20px; padding: 15px; background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6; border-radius: 6px;">
        <strong style="color: #7dd3fc;">Info :</strong> <span style="color: #e5e7eb;">Votre réclamation sera envoyée à l'administrateur pour traitement.</span>
      </div>
    </form>
  </div>
</main>
<script src="/jurispaix/Public/js/reclamation.js"></script>
</body>
</html>

