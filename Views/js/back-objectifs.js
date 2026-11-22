/**
 * YallaQuest - Back-Office Objectifs (Administration)
 * Fichier unique regroupant toute la logique JavaScript du Back-Office
 * (Filtrage en direct, ajout dynamique d'étapes admin, validation, modals)
 */

// 1. Filtrage en direct du tableau des objectifs par recherche texte et statut
function filtrerTableau() {
    var searchInput = document.getElementById('questSearch');
    var filterSelect = document.getElementById('questStatutFilter');
    var search = searchInput ? searchInput.value.toLowerCase().trim() : '';
    var filter = filterSelect ? filterSelect.value : 'all';
    var rows = document.querySelectorAll('#adminTable tbody tr');

    rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        var statut = row.getAttribute('data-statut');
        var matchSearch = (search === '' || text.indexOf(search) !== -1);
        var matchFilter = (filter === 'all' || statut === filter);
        row.style.display = (matchSearch && matchFilter) ? '' : 'none';
    });
}

// 2. Ajout dynamique d'étapes dans la modale d'assignation Admin
function ajouterEtapeAdmin() {
    var txt = document.getElementById('adminEtapeTxt');
    var liste = document.getElementById('adminListeEtapes');
    if (!txt || !txt.value.trim() || !liste) return;

    var val = txt.value.trim();
    var div = document.createElement('div');
    div.style = "display:flex;justify-content:space-between;align-items:center;background:rgba(255,255,255,0.05);padding:6px 12px;border-radius:6px;";
    div.innerHTML = '<input type="hidden" name="etapes[]" value="' + escapeHtml(val) + '">' +
                    '<span>🔹 ' + escapeHtml(val) + '</span>' +
                    '<button type="button" class="btn danger sm" onclick="this.parentElement.remove()" style="padding:2px 8px;font-size:12px;">✕</button>';
    liste.appendChild(div);
    txt.value = '';
    txt.focus();
}

// 3. Validation du formulaire d'assignation Admin avant envoi
function validerAdminObjectif() {
    var titre = document.getElementById('adminTitre').value.trim();
    var debut = document.getElementById('adminDebut').value;
    var fin = document.getElementById('adminFin').value;
    var pendingTxt = document.getElementById('adminEtapeTxt');
    
    // Si une étape a été saisie sans cliquer sur "+ Ajouter"
    if (pendingTxt && pendingTxt.value.trim() !== '') {
        ajouterEtapeAdmin();
    }
    
    var etapes = document.querySelectorAll('#adminListeEtapes input[name="etapes[]"]');

    if (!titre) {
        alert("Le titre est requis.");
        return false;
    }
    if (!debut || !fin) {
        alert("Veuillez renseigner les dates de début et de fin.");
        return false;
    }
    if (new Date(fin) <= new Date(debut)) {
        alert("La date de fin doit être postérieure au début.");
        return false;
    }
    if (etapes.length === 0) {
        alert("Ajoutez au moins une étape pour cet objectif.");
        if (pendingTxt) pendingTxt.focus();
        return false;
    }
    return true;
}

// 4. Helper d'échappement HTML
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    var str = text.toString();
    var out = '';
    for (var i = 0; i < str.length; i++) {
        var c = str.charAt(i);
        out += map[c] !== undefined ? map[c] : c;
    }
    return out;
}

// Initialisation au chargement du DOM
document.addEventListener('DOMContentLoaded', function() {
    var txt = document.getElementById('adminEtapeTxt');
    if (txt) {
        txt.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                ajouterEtapeAdmin();
            }
        });
    }
});
