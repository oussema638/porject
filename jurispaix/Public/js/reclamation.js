// Public/js/reclamation.js
// JavaScript for ajouter_reclamation.php

// This file can be used for any client-side validation or enhancements
// The form submission is handled by the server-side controller

document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('form-ajout');
  if (form) {
    // Add any client-side validation here if needed
    form.addEventListener('submit', function(e) {
      // Server-side handles validation, but we can add client-side checks here
      const titre = document.getElementById('rec-titre').value.trim();
      const texte = document.getElementById('rec-texte').value.trim();
      const categorie = document.getElementById('rec-categorie').value;
      
      if (!titre || !texte || !categorie) {
        e.preventDefault();
        alert('Veuillez remplir tous les champs requis');
        return false;
      }
    });
  }
});

