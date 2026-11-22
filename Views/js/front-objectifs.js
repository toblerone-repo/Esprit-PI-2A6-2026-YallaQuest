/**
 * YallaQuest - Front-Office Objectifs
 * Fichier unique regroupant toute la logique JavaScript du Front-Office
 * (Modal, étapes dynamiques, validation de formulaire, filtrage, toggle AJAX, toasts)
 */

// 1. Toast de notification YallaQuest
function showToast(msg) {
    var t = document.getElementById('toast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'toast';
        t.className = 'toast';
        t.setAttribute('role', 'status');
        t.setAttribute('aria-live', 'polite');
        document.body.appendChild(t);
    }
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(window._toastTimer);
    window._toastTimer = setTimeout(function () {
        t.classList.remove('show');
    }, 2600);
}

// 2. Gestion de la modale d'ajout
function ouvrirModal() {
    var modal = document.getElementById('dlgBut');
    if (modal) {
        if (typeof modal.showModal === 'function') {
            modal.showModal();
        } else {
            modal.style.display = 'block';
        }
        var premierChamp = modal.querySelector('input:not([type=hidden]), textarea');
        if (premierChamp) premierChamp.focus();
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

// 3. Gestion dynamique des étapes dans le formulaire d'ajout
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
    div.style = "display:flex;justify-content:space-between;align-items:center;background:rgba(255,255,255,0.05);padding:6px 12px;border-radius:6px;margin-bottom:6px;";
    div.innerHTML = 
        '<input type="hidden" name="etapes[]" value="' + escapeHtml(texte) + '">' +
        '<span class="etape-item-text">🔹 ' + escapeHtml(texte) + '</span>' +
        '<button type="button" class="btn danger sm" onclick="this.parentElement.remove();" style="padding:2px 8px;font-size:12px;">✕</button>';

    listeEtapes.appendChild(div);
    inputEtape.value = '';
    inputEtape.focus();
}

// 4. Validation du formulaire avant envoi
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

// 5. Validation interactive des étapes (Cocher / Décocher)
function toggleEtape(idEO, checkbox) {
    var val = checkbox.checked ? 1 : 0;
    fetch('front-objectifs.php?toggle_etape=' + idEO + '&val=' + val)
        .then(function() {
            window.location.reload();
        })
        .catch(function() {
            window.location.href = 'front-objectifs.php?toggle_etape=' + idEO + '&val=' + val;
        });
}

// 6. Filtrage des cartes par statut (Tous, À venir, En cours, Atteints)
function filtrerObjectifs(statut, btn) {
    var cards = document.querySelectorAll('.obj-card');
    var buttons = document.querySelectorAll('.tabs button');

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
    if (text === null || text === undefined) return '';
    var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Initialisation au chargement du DOM
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

    // Afficher un toast si paramètre succès dans l'URL
    var params = new URLSearchParams(window.location.search);
    if (params.get('succes') === '1') {
        showToast('Objectif enregistré avec succès !');
    }
});
