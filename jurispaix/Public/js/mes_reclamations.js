// Public/js/mes_reclamations.js
// JavaScript for mes_reclamations.php

function deleteReclamation(id) {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cette réclamation ?')) {
    return;
  }

  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/jurispaix/Controllers/ReclamationController.php?action=delete';

  const input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'id';
  input.value = id;

  form.appendChild(input);
  document.body.appendChild(form);
  form.submit();
}

function editReclamation(id) {
  // Redirect to the add/edit page with the reclamation pre-filled
  window.location.href = `ajouter_reclamation.php?edit=${id}`;
}

function viewReclamation(id) {
  // For now, use the same add/edit page to display full details
  window.location.href = `ajouter_reclamation.php?edit=${id}`;
}

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('search-input');
  if (searchInput) {
    searchInput.addEventListener('input', function(e) {
      const searchTerm = e.target.value.toLowerCase();
      const rows = document.querySelectorAll('.table-row');
      
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
      });
    });
  }
  
  // Filter functionality
  const filterSelect = document.querySelector('.dashboard-filter');
  if (filterSelect) {
    filterSelect.addEventListener('change', function(e) {
      const filterValue = e.target.value.toLowerCase();
      const rows = document.querySelectorAll('.table-row');
      
      rows.forEach(row => {
        if (filterValue === 'tous les statuts') {
          row.style.display = '';
        } else {
          const statusBadge = row.querySelector('.status-badge');
          const statusText = statusBadge ? statusBadge.textContent.toLowerCase() : '';
          const match = statusText.includes(filterValue) || 
                       (filterValue === 'en attente' && statusText.includes('attente')) ||
                       (filterValue === 'en cours de traitement' && statusText.includes('accepté'));
          row.style.display = match ? '' : 'none';
        }
      });
    });
  }
});

