/**
 * JavaScript simple et clair pour la page Objectifs (Front-Office)
 * Conforme au cours ESPRIT (DOM, Événements, Validation de formulaires)
 */

// 1. Gestion de la modale d'ajout
function ouvrirModal() {
    var modal = document.getElementById('dlgBut');
    if (modal) {
        if (typeof modal.showModal === 'function') {
            modal.showModal();
        } else {
            modal.style.display = 'block';
        }
    }
}

function fermerModal() {
    var modal = document.getElementById('dlgBut');
    if (modal) {
        if (typeof modal.close === 'function') {
            modal.close();
        } else {
            modal.style.display = 'none';
        }
    }
}

// 2. Gestion dynamique des étapes dans le formulaire d'ajout
function ajouterChampEtape() {
    var inputEtape = document.getElementById('nouvelleEtapeInput');
    var listeEtapes = document.getElementById('listeChampsEtapes');
    var erreur = document.getElementById('etapeErr');

    if (!inputEtape || !listeEtapes) return;

    var texte = inputEtape.value.trim();
    if (texte === '') {
        if (erreur) erreur.textContent = "Veuillez saisir un intitulé pour l'étape.";
        inputEtape.focus();
        return;
    }

    if (erreur) erreur.textContent = '';

    // Créer la ligne de l'étape
    var div = document.createElement('div');
    div.className = 'etape-item-row';
    div.innerHTML = `
        <input type="hidden" name="etapes[]" value="${escapeHtml(texte)}">
        <span class="etape-item-text">🔹 ${escapeHtml(texte)}</span>
        <button type="button" class="btn danger sm" onclick="this.parentElement.remove();">✕</button>
    `;

    listeEtapes.appendChild(div);
    inputEtape.value = '';
    inputEtape.focus();
}

// 3. Validation du formulaire avant envoi
function validerFormulaireObjectif() {
    var titre = document.getElementById('butTitre').value.trim();
    var dateDebut = document.getElementById('butDateDebut').value;
    var dateFin = document.getElementById('butDateFin').value;
    var etapesInputs = document.querySelectorAll('input[name="etapes[]"]');
    var pendingInput = document.getElementById('nouvelleEtapeInput');

    // Si l'utilisateur a tapé une étape sans cliquer sur "+", l'ajouter automatiquement
    if (pendingInput && pendingInput.value.trim() !== '') {
        ajouterChampEtape();
        etapesInputs = document.querySelectorAll('input[name="etapes[]"]');
    }

    var errTitre = document.getElementById('butTitreErr');
    var errDate = document.getElementById('butDateErr');
    var errEtape = document.getElementById('etapeErr');

    // Réinitialiser les messages d'erreurs
    if (errTitre) errTitre.textContent = '';
    if (errDate) errDate.textContent = '';
    if (errEtape) errEtape.textContent = '';

    var valide = true;

    if (titre === '') {
        if (errTitre) errTitre.textContent = "Le titre de l'objectif est obligatoire.";
        valide = false;
    }

    if (dateDebut === '' || dateFin === '') {
        if (errDate) errDate.textContent = "Veuillez renseigner les dates de début et de fin.";
        valide = false;
    } else if (new Date(dateFin) <= new Date(dateDebut)) {
        if (errDate) errDate.textContent = "La date de fin doit être postérieure à la date de début.";
        valide = false;
    }

    if (etapesInputs.length === 0) {
        if (errEtape) errEtape.textContent = "Veuillez ajouter au moins une étape pour cet objectif.";
        if (pendingInput) pendingInput.focus();
        valide = false;
    }

    return valide;
}

// 4. Validation interactive des étapes (Cocher / Décocher)
function toggleEtape(idEO, checkbox) {
    var val = checkbox.checked ? 1 : 0;
    
    // Appel AJAX vers la page actuelle pour mettre à jour la base de données
    fetch('index.php?page=objectifs&toggle_etape=' + idEO + '&val=' + val)
        .then(function() {
            // Recharger la page pour recalculer la progression, XP et statut
            window.location.reload();
        })
        .catch(function() {
            // En cas de problème réseau, basculer via navigation standard
            window.location.href = 'index.php?page=objectifs&toggle_etape=' + idEO + '&val=' + val;
        });
}

// 5. Filtrage des cartes par statut (Tous, À venir, En cours, Atteints)
function filtrerObjectifs(statut, btn) {
    var cards = document.querySelectorAll('.obj-card');
    var buttons = document.querySelectorAll('.tabs button');

    // Mettre à jour l'onglet actif
    buttons.forEach(function(b) { b.setAttribute('aria-selected', 'false'); });
    if (btn) btn.setAttribute('aria-selected', 'true');

    cards.forEach(function(card) {
        var cardStatut = card.getAttribute('data-statut');
        if (statut === 'all' || cardStatut === statut) {
            card.style.display = 'grid';
        } else {
            card.style.display = 'none';
        }
    });
}

// Helper pour échapper le HTML
function escapeHtml(text) {
    var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Gestion de la touche Entrée dans le champ d'étape
document.addEventListener('DOMContentLoaded', function() {
    var inputEtape = document.getElementById('nouvelleEtapeInput');
    if (inputEtape) {
        inputEtape.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                ajouterChampEtape();
            }
        });
    }
});
