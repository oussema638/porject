<?php
// Views/backoffice.php
// Receives data from BackofficeController::showPending, but can run standalone
if (!isset($_SESSION)) {
    session_start();
}

$definedVars = get_defined_vars();
$hasReclamationsVar = array_key_exists('reclamations', $definedVars);

if (!$hasReclamationsVar) {
    require_once __DIR__ . '/../Controllers/BackofficeController.php';
    $controller = new BackofficeController();
    $reclamations = $controller->reclamationModel->getByStatus('enattente');
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
        <h2>Réclamations en attente</h2>

        <?php if ($success): ?>
          <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div style="margin-bottom:18px;">
          <button class="btn btn-primary" onclick="window.location.href='/jurispaix/router.php?route=traitement'">⚡ Traitement des réclamations</button>
          <button class="btn btn-secondary" style="margin-left:10px;" onclick="window.location.reload()">🔄 Actualiser</button>
        </div>

        <div id="reclamations-list">
          <?php if (empty($reclamations)): ?>
            <div style="text-align:center; padding:20px; color:#475569;">Aucune réclamation en attente.</div>
          <?php else: ?>
            <?php foreach ($reclamations as $rec):
              $date = new DateTime($rec['created_at']);
              $dateStr = $date->format('d/m/Y');
            ?>
              <div class="reclamation-card" data-id="<?php echo $rec['id']; ?>">
                <h3><?php echo htmlspecialchars($rec['titre']); ?></h3>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($rec['texte']); ?></p>
                <p><strong>Catégorie:</strong> <?php echo htmlspecialchars($rec['categorie']); ?> | <strong>Priorité:</strong> <?php echo htmlspecialchars($rec['priorite']); ?></p>
                <p><strong>Date:</strong> <?php echo $dateStr; ?></p>
                <form method="POST" action="/jurispaix/Controllers/BackofficeController.php?action=accept">
                  <input type="hidden" name="id" value="<?php echo $rec['id']; ?>">
                  <button type="submit" class="btn-accepter">Accepter</button>
                </form>
                <form method="POST" action="/jurispaix/Controllers/BackofficeController.php?action=refuse">
                  <input type="hidden" name="id" value="<?php echo $rec['id']; ?>">
                  <button type="submit" class="btn-refuser">Refuser</button>
                </form>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
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
  <script src="/jurispaix/Public/js/backoffice.js"></script>
</body>
</html>

