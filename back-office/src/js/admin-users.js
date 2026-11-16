/**
 * Vue - gestion des aventuriers, Back-Office Yalla Quest (MVC).
 * Données et actions via AdminModel / AdminController (inchangés). Plus de Bootstrap ni de confirm() :
 * fenêtres <dialog>, confirmation et toast viennent de shared/js/ui.js (objet YQ).
 */
const userFilters = { q: '', role: 'all', statut: 'all' };

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.toString().replace(/[&<>"']/g, (m) => map[m]);
}
function formatDay(iso) {
    if (!iso) return '—';
    const d = new Date(String(iso).slice(0, 10) + 'T12:00:00');
    return isNaN(d.getTime()) ? escapeHtml(iso) : d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' });
}
const initials = (u) => ((u.prenom || '')[0] || '') + ((u.nom || '')[0] || '') || (u.pseudo || '?').slice(0, 2);
const plural = (k, one, many) => k + ' ' + (k > 1 ? many : one);

function renderUsersTable() {
    const tableBody = document.getElementById('usersTableBody');
    if (!tableBody) return;
    const users = AdminModel.getUsers();
    const q = userFilters.q.trim().toLowerCase();
    const list = users.filter((u) =>
        (userFilters.role === 'all' || u.role === userFilters.role) &&
        (userFilters.statut === 'all' || u.statut === userFilters.statut) &&
        (!q || [u.pseudo, u.prenom, u.nom, u.email].some((t) => (t || '').toLowerCase().includes(q))));

    tableBody.innerHTML = list.map((u) => {
        const nomComplet = ((u.prenom || '') + ' ' + (u.nom || '')).trim();
        const admin = u.role === 'admin', actif = u.statut === 'actif';
        return `<tr data-id="${u.id}">
            <td class="id"><span class="gcell"><span class="avatar" aria-hidden="true">${escapeHtml(initials(u).toUpperCase())}</span>
                <span><strong>${escapeHtml(u.pseudo)}</strong><span class="desc">${escapeHtml(nomComplet) || 'Profil non renseigné'}</span></span></span></td>
            <td class="hide-sm hide-md">${escapeHtml(u.email)}</td>
            <td class="second">Niveau ${u.niveau || 1}<span class="desc">${u.expU || 0} XP</span></td>
            <td class="c-side"><button class="pill role-${admin ? 'admin' : 'joueur'}" type="button" data-action="role" data-id="${u.id}"
                aria-label="Rôle : ${admin ? 'administrateur' : 'joueur'}. Passer en ${admin ? 'joueur' : 'administrateur'}" title="Cliquer pour passer en ${admin ? 'joueur' : 'administrateur'}">${admin ? 'Admin' : 'Joueur'}</button></td>
            <td class="c-side"><button class="pill st-${actif ? 'actif' : 'suspendu'}" type="button" data-action="statut" data-id="${u.id}"
                aria-label="Statut : ${actif ? 'actif' : 'suspendu'}. ${actif ? 'Suspendre' : 'Réactiver'} le compte" title="Cliquer pour ${actif ? 'suspendre' : 'réactiver'}">${actif ? 'Actif' : 'Suspendu'}</button></td>
            <td class="hide-sm hide-md" style="white-space:nowrap">${formatDay(u.dateInscription)}</td>
            <td class="end"><span class="row-actions">
                <button class="btn danger sm" type="button" data-action="delete" data-id="${u.id}" aria-label="Supprimer ${escapeHtml(u.pseudo)}">Supprimer</button>
            </span></td></tr>`;
    }).join('') || `<tr><td colspan="7"><div class="empty">${users.length ? 'Aucun aventurier ne correspond à ces filtres.' : 'Aucun aventurier enregistré.'}</div></td></tr>`;

    const admins = users.filter((u) => u.role === 'admin').length, susp = users.filter((u) => u.statut === 'suspendu').length;
    const summary = document.getElementById('usersSummary');
    if (summary) summary.innerHTML = `<strong>${plural(users.length, 'aventurier', 'aventuriers')}</strong> · ${plural(admins, 'administrateur', 'administrateurs')}${susp ? ` · ${plural(susp, 'compte suspendu', 'comptes suspendus')}` : ''}.`;
    const count = document.getElementById('userCount');
    if (count) count.textContent = list.length === users.length ? '' : `${list.length} sur ${users.length}`;
}

// après un re-rendu, le focus revient sur le bouton équivalent de la même ligne
function refocus(id, action) {
    const b = document.querySelector(`#usersTableBody [data-action="${action}"][data-id="${id}"]`);
    (b || document.querySelector('#usersTableBody [data-action]') || document.querySelector('.title-row .btn.primary'))?.focus();
}

function toggleStatus(id) {
    const u = AdminController.toggleUserStatus(id);
    renderUsersTable(); refocus(id, 'statut');
    if (u) YQ.toast(`${u.pseudo} : compte ${u.statut === 'actif' ? 'réactivé' : 'suspendu'}`);
}

function toggleRole(id) {
    const u = AdminController.toggleUserRole(id);
    renderUsersTable(); refocus(id, 'role');
    if (u) YQ.toast(`${u.pseudo} est maintenant ${u.role === 'admin' ? 'administrateur' : 'joueur'}`);
}

function deleteUser(id, trigger) {
    const u = AdminModel.getUsers().find((x) => x.id == id);
    YQ.confirm({
        title: 'Supprimer cet aventurier ?',
        body: `Le compte ${u ? '« ' + u.pseudo + ' » ' : ''}sera supprimé définitivement.`,
        confirmLabel: 'Supprimer le compte',
        trigger: trigger || null
    }).then((ok) => {
        if (!ok) return;
        AdminController.deleteUser(id);
        renderUsersTable(); refocus(id, 'delete');
        YQ.toast('Aventurier supprimé');
    });
}

function fieldError(id, msg, field) {
    const el = document.getElementById(id);
    if (el) { el.textContent = msg || ''; el.hidden = !msg; }
    if (field) field.setAttribute('aria-invalid', msg ? 'true' : 'false');
}

document.addEventListener('DOMContentLoaded', function () {
    renderUsersTable();

    document.getElementById('usersTableBody').addEventListener('click', (e) => {
        const b = e.target.closest('[data-action]');
        if (!b) return;
        if (b.dataset.action === 'role') toggleRole(b.dataset.id);
        if (b.dataset.action === 'statut') toggleStatus(b.dataset.id);
        if (b.dataset.action === 'delete') deleteUser(b.dataset.id, b);
    });
    document.getElementById('userSearch').addEventListener('input', (e) => { userFilters.q = e.target.value; renderUsersTable(); });
    document.getElementById('userRoleFilter').addEventListener('change', (e) => { userFilters.role = e.target.value; renderUsersTable(); });
    document.getElementById('userStatutFilter').addEventListener('change', (e) => { userFilters.statut = e.target.value; renderUsersTable(); });

    const form = document.getElementById('formNouvelUser');
    if (!form) return;
    const pseudoEl = document.getElementById('userPseudo'), emailEl = document.getElementById('userEmail');
    pseudoEl.addEventListener('input', () => fieldError('userPseudoErr', '', pseudoEl));
    emailEl.addEventListener('input', () => fieldError('userEmailErr', '', emailEl));

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const pseudo = pseudoEl.value.trim();
        const email = emailEl.value.trim();
        const prenom = document.getElementById('userPrenom').value.trim();
        const nom = document.getElementById('userNom').value.trim();
        const role = document.getElementById('userRole').value;
        const statut = document.getElementById('userStatut').value;

        const users = AdminModel.getUsers();
        let ok = true;
        if (!pseudo) { fieldError('userPseudoErr', 'Indique un pseudo.', pseudoEl); ok = false; }
        else if (users.some((u) => (u.pseudo || '').toLowerCase() === pseudo.toLowerCase())) { fieldError('userPseudoErr', 'Ce pseudo est déjà pris.', pseudoEl); ok = false; }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { fieldError('userEmailErr', 'Indique une adresse e-mail valide.', emailEl); ok = false; }
        else if (users.some((u) => (u.email || '').toLowerCase() === email.toLowerCase())) { fieldError('userEmailErr', 'Un compte utilise déjà cette adresse.', emailEl); ok = false; }
        if (!ok) { form.querySelector('[aria-invalid="true"]')?.focus(); return; }

        const created = AdminController.addUser(pseudo, email, prenom, nom, role, statut);
        form.reset();
        YQ.closeDialog(document.getElementById('dlgUser'));
        renderUsersTable();
        document.querySelector(`#usersTableBody tr[data-id="${created.id}"]`)?.classList.add('flash');
        YQ.toast(`Compte « ${pseudo} » créé`);
    });
});
