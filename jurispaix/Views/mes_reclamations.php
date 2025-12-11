<?php
// Views/mes_reclamations.php
// Clean view - receives data from controller, fallback for direct load
if (!isset($_SESSION)) {
    session_start();
}

$definedVars = get_defined_vars();
$hasReclamationsVar = array_key_exists('reclamations', $definedVars);

if (!$hasReclamationsVar) {
    require_once __DIR__ . '/../Controllers/ReclamationController.php';
    $controller = new ReclamationController();
    $userId = 'user_001';
    $reclamations = $controller->reclamationModel->getAllByUser($userId);
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
// responsesByReclamation may be provided by the controller (from reponses table)
$responsesByReclamation = $responsesByReclamation ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Mes Réclamations — jurisPaix</title>
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
      <input type="text" class="dashboard-search" placeholder="Rechercher une réclamation..." id="search-input" />
      <select class="dashboard-filter">
        <option>Tous les statuts</option>
        <option>En attente</option>
        <option>En cours de traitement</option>
        <option>Refusée</option>
        <option>Résolue</option>
      </select>
      <a class="btn btn-primary" href="/jurispaix/Views/ajouter_reclamation.php">➕ Ajouter</a>
    </div>

    <div class="dashboard-table-container">
      <table class="dashboard-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Titre</th>
            <th>Catégorie</th>
            <th>Date</th>
            <th>Priorité</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($reclamations)): ?>
            <tr>
              <td colspan="7" style="text-align:center; padding:40px; color:#e5e7eb;">Aucune réclamation trouvée.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($reclamations as $rec): 
              $date = new DateTime($rec['created_at']);
              $dateStr = $date->format('d/m/Y');
              
              $statutClass = [
                'enattente' => 'status-badge status-pending',
                'traitement' => 'status-badge status-accepted',
                'refuse' => 'status-badge status-refused',
                'resolue' => 'status-badge status-resolved'
              ][$rec['statut']] ?? 'status-badge status-pending';
              
              $statutText = [
                'enattente' => 'En attente',
                'traitement' => 'Accepté',
                'refuse' => 'Refusé',
                'resolue' => 'Résolu'
              ][$rec['statut']] ?? $rec['statut'];
              
              $recResponses = $responsesByReclamation[$rec['id']] ?? [];
              $hasResponse = !empty($recResponses);
            ?>
              <tr class="table-row" data-id="<?php echo $rec['id']; ?>">
                <td><?php echo $rec['id']; ?></td>
                <td>
                  <div class="cell-title"><?php echo htmlspecialchars($rec['titre']); ?></div>
                  <?php if ($hasResponse): ?>
                    <div class="cell-response">📩 Réponse disponible</div>
                  <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($rec['categorie']); ?></td>
                <td><?php echo $dateStr; ?></td>
                <td><?php echo htmlspecialchars($rec['priorite']); ?></td>
                <td><span class="<?php echo $statutClass; ?>"><?php echo $statutText; ?></span></td>
                <td>
                  <button class="btn-view" onclick="viewReclamation(<?php echo $rec['id']; ?>)">Voir</button>
                  <button class="btn-edit-small" onclick="editReclamation(<?php echo $rec['id']; ?>)">✏️</button>
                  <button class="btn-delete-small" onclick="deleteReclamation(<?php echo $rec['id']; ?>)">🗑️</button>
                </td>
              </tr>
              <?php if ($hasResponse): ?>
                <?php foreach ($recResponses as $resp): ?>
                  <?php
                    $isAdmin      = ($resp['auteur'] ?? 'admin') === 'admin';
                    $whoLabel     = $isAdmin ? 'Admin' : 'Utilisateur';
                    $displayTexte = isset($resp['modifier']) && $resp['modifier'] !== null && $resp['modifier'] !== ''
                      ? $resp['modifier']
                      : $resp['texte'];
                    $timestamp    = !empty($resp['updated_at']) ? $resp['updated_at'] : $resp['created_at'];
                  ?>
                  <tr class="table-row response-row" data-id="<?php echo $rec['id']; ?>">
                    <td colspan="7">
                      <div class="reponse">
                        <div style="font-size:0.85rem; color:#9ca3af; margin-bottom:4px;">
                          <?php echo htmlspecialchars($whoLabel); ?>
                          •
                          <?php echo htmlspecialchars($timestamp); ?>
                          <?php if (!empty($resp['updated_at'])): ?>
                            (modifiée)
                          <?php endif; ?>
                        </div>
                        <p style="margin-top:4px;"><?php echo nl2br(htmlspecialchars($displayTexte)); ?></p>
                        <!-- Optional user reply to this response (creates another entry in reponses) -->
                        <form method="POST" action="/jurispaix/Controllers/ResponseController.php?action=saveResponse" style="margin-top:10px;">
                          <input type="hidden" name="id" value="<?php echo $rec['id']; ?>">
                          <input type="hidden" name="auteur" value="user">
                          <textarea name="reponse" placeholder="Répondre à cette réponse..." style="width:100%; min-height:70px;"></textarea>
                          <button type="submit" class="btn-primary" style="margin-top:6px; padding:6px 12px; font-size:0.85rem;">
                            Répondre
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
<script src="/jurispaix/Public/js/mes_reclamations.js"></script>
</body>
</html>

