<?php
// Views/traitement_back.php
// Receives data from ResponseController::showInTreatment, fallback if direct
if (!isset($_SESSION)) {
    session_start();
}

$definedVars = get_defined_vars();
$hasReclamationsVar = array_key_exists('reclamations', $definedVars);

if (!$hasReclamationsVar) {
    require_once __DIR__ . '/../Controllers/ResponseController.php';
    $controller = new ResponseController();
    $reclamations = $controller->reclamationModel->getByStatus('traitement');
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

$reclamations = $reclamations ?? [];
$success = $success ?? null;
$error = $error ?? null;
// responsesByReclamation may be set by the controller; ensure it exists for the view
$responsesByReclamation = $responsesByReclamation ?? [];
?>
<!DOCTYPE html>
  <html lang='fr'>
<head>
  <meta charset='utf-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>Backoffice - Jurispaix</title>
  <link rel='stylesheet' href='/jurispaix/Public/css/boi.css'>
</head>
<body>
  <header>
    <div class='logo'>
      <img src='/jurispaix/Public/images/logo.jpg' alt='Logo Jurispaix'>
    </div>
    <div class='header-buttons'>
      <button class='btn btn-secondary'>Profil</button>
      <button class='btn btn-primary'>Déconnexion</button>
    </div>
  </header>

  <div class='main-container'>
    <aside class='sidebar'>
      <div class='menu-item'>👥 Utilisateurs</div>
      <div class='menu-item' id='menu-evenements'>📅 Événements</div>
      <div class='submenu' id='submenu-evenements'>
        <a href='#' class='submenu-item'>➕ Ajouter un événement</a>
        <a href='#' class='submenu-item'>📋 Liste des événements</a>
      </div>
      <div class='menu-item'>📅 Rendez-vous</div>
      <div class='menu-item'>📰 Articles</div>
      <div class='menu-item'>📩 Réclamation</div>
    </aside>

    <main>
      <div class="content-card">
        <h2>Réclamations en traitement</h2>

        <?php if ($success): ?>
          <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div id="reclamations-list">
          <?php if (empty($reclamations)): ?>
            <div style="text-align:center; padding:20px; color:#475569;">Aucune réclamation en cours de traitement.</div>
          <?php else: ?>
            <?php foreach ($reclamations as $rec):
              $date = new DateTime($rec['created_at']);
              $dateStr = $date->format('d/m/Y');
              $recResponses = $responsesByReclamation[$rec['id']] ?? [];
            ?>
              <div class="reclamation-card" data-id="<?php echo $rec['id']; ?>">
                <h3><?php echo htmlspecialchars($rec['titre']); ?></h3>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($rec['texte']); ?></p>
                <p><strong>Catégorie:</strong> <?php echo htmlspecialchars($rec['categorie']); ?> | <strong>Priorité:</strong> <?php echo htmlspecialchars($rec['priorite']); ?></p>
                <p><strong>Date:</strong> <?php echo $dateStr; ?></p>
                <!-- New response form -->
                <form method="POST" action="/jurispaix/Controllers/ResponseController.php?action=saveResponse">
                  <input type="hidden" name="id" value="<?php echo $rec['id']; ?>">
                  <input type="hidden" name="auteur" value="admin">
                  <textarea name="reponse" placeholder="Écrire une nouvelle réponse..." required></textarea>
                  <button type="submit" class="btn-repondre">Ajouter une réponse</button>
                </form>
                <!-- Existing responses with edit/delete -->
                <?php foreach ($recResponses as $resp): ?>
                  <div class="reponse">
                    <div style="margin-bottom:6px; font-size:0.9rem; opacity:0.8;">
                      <?php echo htmlspecialchars($resp['created_at']); ?>
                      <?php if (!empty($resp['updated_at'])): ?>
                        (modifiée <?php echo htmlspecialchars($resp['updated_at']); ?>)
                      <?php endif; ?>
                    </div>
                    <form method="POST" action="/jurispaix/Controllers/ResponseController.php?action=saveResponse" style="margin-bottom:8px;">
                      <input type="hidden" name="id" value="<?php echo $rec['id']; ?>">
                      <input type="hidden" name="response_id" value="<?php echo $resp['id']; ?>">
                      <textarea name="reponse" required><?php echo htmlspecialchars($resp['texte']); ?></textarea>
                      <button type="submit" class="btn-repondre">Mettre à jour</button>
                    </form>
                    <form method="POST" action="/jurispaix/Controllers/ResponseController.php?action=deleteResponse" onsubmit="return confirm('Supprimer cette réponse ?');">
                      <input type="hidden" name="response_id" value="<?php echo $resp['id']; ?>">
                      <button type="submit" class="btn-refuser">Supprimer</button>
                    </form>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <button class="btn btn-secondary" style="margin-top:20px;" onclick="window.location.href='/jurispaix/router.php?route=backoffice'">⬅ Retour</button>
      </div>
    </main>
  </div>

  <script>
    const menuEvenements = document.getElementById('menu-evenements');
    const submenuEvenements = document.getElementById('submenu-evenements');
    if (submenuEvenements) {
      submenuEvenements.style.display = 'none';
      menuEvenements.addEventListener('click', () => {
        submenuEvenements.style.display = submenuEvenements.style.display === 'none' ? 'block' : 'none';
      });
    }
  </script>
</body>
</html>

