/**
 * Vue - supervision des objectifs, Back-Office Yalla Quest (MVC).
 * Conforme au diagramme UML (objectif 0..n EtapeObjectif).
 */
const questFilters = { q: '', statut: 'all' };
const ETATS = { a_venir: 'À venir', en_cours: 'En cours', atteint: 'Atteint' };
let adminPendingEtapes = [];

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.toString().replace(/[&<>"']/g, (m) => map[m]);
}
function formatDatePretty(isoStr) {
    if (!isoStr) return 'non définie';
    const d = new Date(isoStr);
    if (isNaN(d.getTime())) return escapeHtml(isoStr);
    return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }) + ' à ' + d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}
function toLocalInput(d) {
    const p = (n) => String(n).padStart(2, '0');
    return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()) + 'T' + p(d.getHours()) + ':' + p(d.getMinutes());
}
const plural = (k, one, many) => k + ' ' + (k > 1 ? many : one);

function renderQuestsTable() {
    const tableBody = document.getElementById('adminQuestsTableBody');
    if (!tableBody) return;
    const quests = AdminModel.getQuests();
    const q = questFilters.q.trim().toLowerCase();
    const list = quests.filter((x) =>
        (questFilters.statut === 'all' || x.statut === questFilters.statut) &&
        (!q || [x.titre, x.note, x.description, x.pseudo].some((t) => (t || '').toLowerCase().includes(q))));

    tableBody.innerHTML = list.map((x) => {
        const etapes = x.etapes || [];
        const val = etapes.filter(e => e.est_validee).length;
        const tot = etapes.length;
        const etapesTooltip = etapes.map(e => (e.est_validee ? '✓ ' : '○ ') + e.titre).join('\n') || 'Aucune étape définie';
        const etapesCell = tot > 0
            ? `<span class="pill ${val === tot ? 'sage' : 'lilac'}" title="${escapeHtml(etapesTooltip)}">${val}/${tot} étape${tot > 1 ? 's' : ''}</span>`
            : `<span class="muted" style="font-size:12px;">Sans étapes</span>`;

        return `<tr data-id="${x.id}">
            <td class="id"><strong>${escapeHtml(x.titre)}</strong><span class="note-cell">${escapeHtml(x.note || x.description || 'Aucune note')}</span></td>
            <td class="second">${escapeHtml(x.pseudo || 'Aventurier')}</td>
            <td class="hide-sm hide-md"><span class="pill cat">${escapeHtml(x.categorie)}</span></td>
            <td class="period hide-sm"><span>Du <strong>${formatDatePretty(x.dateDebut)}</strong></span><span>au <strong>${formatDatePretty(x.dateFin || x.dateLimite)}</strong></span></td>
            <td>${etapesCell}</td>
            <td class="c-side"><button class="pill st-${escapeHtml(x.statut)}" type="button" data-action="statut" data-id="${x.id}"
                aria-label="État : ${ETATS[x.statut] || x.statut}. Faire évoluer l'état" title="Cliquer pour faire évoluer l'état">${ETATS[x.statut] || escapeHtml(x.statut)}</button></td>
            <td class="n hide-sm hide-md"><span class="pill xp">+${parseInt(x.xpRecompense, 10) || 50} XP</span></td>
            <td class="end"><span class="row-actions">
                <button class="btn danger sm" type="button" data-action="delete" data-id="${x.id}" aria-label="Supprimer « ${escapeHtml(x.titre)} »">Supprimer</button>
            </span></td></tr>`;
    }).join('')
    || `<tr><td colspan="8"><div class="empty">${quests.length ? 'Aucun objectif ne correspond à ces filtres.' : 'Aucun objectif enregistré dans le système.'}</div></td></tr>`;

    const count = (s) => quests.filter((x) => x.statut === s).length;
    const summary = document.getElementById('questsSummary');
    if (summary) summary.innerHTML = `<strong>${plural(quests.length, 'objectif', 'objectifs')}</strong> · ${count('a_venir')} à venir · ${count('en_cours')} en cours · ${plural(count('atteint'), 'atteint', 'atteints')}.`;
    const counter = document.getElementById('questCount');
    if (counter) counter.textContent = list.length === quests.length ? '' : `${list.length} sur ${quests.length}`;
}

function refocus(id, action) {
    const b = document.querySelector(`#adminQuestsTableBody [data-action="${action}"][data-id="${id}"]`);
    (b || document.querySelector('#adminQuestsTableBody [data-action]') || document.querySelector('.title-row .btn.primary'))?.focus();
}

function toggleQuestStatus(id) {
    const x = AdminController.toggleQuestStatus(id);
    renderQuestsTable(); refocus(id, 'statut');
    if (x) YQ.toast(`« ${x.titre} » : ${(ETATS[x.statut] || x.statut).toLowerCase()}`);
}

function deleteQuest(id, trigger) {
    const x = AdminModel.getQuests().find((o) => o.id == id);
    YQ.confirm({
        title: 'Supprimer cet objectif ?',
        body: `${x ? '« ' + x.titre + ' » sera supprimé' : 'Cet objectif sera supprimé'} définitivement du système.`,
        confirmLabel: "Supprimer l'objectif",
        trigger: trigger || null
    }).then((ok) => {
        if (!ok) return;
        AdminController.deleteQuest(id);
        renderQuestsTable(); refocus(id, 'delete');
        YQ.toast('Objectif supprimé');
    });
}

function populateUserSelect() {
    const select = document.getElementById('questUser');
    if (!select) return;
    const users = AdminModel.getUsers();
    select.innerHTML = '<option value="">Choisir un aventurier</option>';
    users.forEach((u) => {
        const opt = document.createElement('option');
        opt.value = u.id;
        const nom = ((u.prenom || '') + ' ' + (u.nom || '')).trim();
        opt.textContent = `${u.pseudo}${nom ? ' · ' + nom : ''} · niveau ${u.niveau || 1}${u.statut === 'suspendu' ? ' (suspendu)' : ''}`;
        select.appendChild(opt);
    });
}

function renderAdminPendingEtapes() {
    const container = document.getElementById('adminEtapesBuilderList');
    if (!container) return;
    if (adminPendingEtapes.length === 0) {
        container.innerHTML = `<li class="muted" style="font-size:12px;padding:4px 0;">Aucune étape ajoutée pour l'instant.</li>`;
        return;
    }
    container.innerHTML = adminPendingEtapes.map((titre, idx) => `
        <li class="etapes-builder-item">
            <span><strong class="etape-num">Étape ${idx + 1} :</strong> ${escapeHtml(titre)}</span>
            <button type="button" class="btn-remove-etape" data-admin-remove-etape="${idx}" aria-label="Supprimer" title="Supprimer">✕</button>
        </li>
    `).join('');
}

function addAdminPendingEtape() {
    const input = document.getElementById('adminEtapeInput');
    if (!input) return;
    const val = input.value.trim();
    if (!val) { input.focus(); return; }
    adminPendingEtapes.push(val);
    input.value = '';
    fieldError('adminQuestEtapesErr', '');
    renderAdminPendingEtapes();
    input.focus();
}

function fieldError(id, msg, field) {
    const el = document.getElementById(id);
    if (el) { el.textContent = msg || ''; el.hidden = !msg; }
    if (field) field.setAttribute('aria-invalid', msg ? 'true' : 'false');
}

function resetDates() {
    const s = document.getElementById('questDateDebut'), e = document.getElementById('questDateFin');
    if (s) s.value = toLocalInput(new Date());
    if (e) e.value = toLocalInput(new Date(Date.now() + 7 * 86400000));
}

document.addEventListener('DOMContentLoaded', function () {
    renderQuestsTable();
    populateUserSelect();
    resetDates();
    renderAdminPendingEtapes();

    document.getElementById('adminQuestsTableBody').addEventListener('click', (e) => {
        const b = e.target.closest('[data-action]');
        if (!b) return;
        if (b.dataset.action === 'statut') toggleQuestStatus(b.dataset.id);
        if (b.dataset.action === 'delete') deleteQuest(b.dataset.id, b);
    });
    document.getElementById('questSearch').addEventListener('input', (e) => { questFilters.q = e.target.value; renderQuestsTable(); });
    document.getElementById('questStatutFilter').addEventListener('change', (e) => { questFilters.statut = e.target.value; renderQuestsTable(); });
    document.querySelector('[data-open="#dlgQuest"]')?.addEventListener('click', () => {
        populateUserSelect();
        adminPendingEtapes = [];
        renderAdminPendingEtapes();
        fieldError('adminQuestEtapesErr', '');
    });

    // Builder d'étapes admin
    document.getElementById('adminBtnAddEtape')?.addEventListener('click', addAdminPendingEtape);
    document.getElementById('adminEtapeInput')?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            addAdminPendingEtape();
        }
    });
    document.getElementById('adminEtapesBuilderList')?.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-admin-remove-etape]');
        if (!btn) return;
        adminPendingEtapes.splice(parseInt(btn.dataset.adminRemoveEtape, 10), 1);
        renderAdminPendingEtapes();
    });

    const form = document.getElementById('formNouvelObjectifAdmin');
    if (!form) return;
    const userEl = document.getElementById('questUser'), titleEl = document.getElementById('questTitle');
    const startEl = document.getElementById('questDateDebut'), endEl = document.getElementById('questDateFin');
    userEl.addEventListener('change', () => fieldError('questUserErr', '', userEl));
    titleEl.addEventListener('input', () => fieldError('questTitleErr', '', titleEl));
    [startEl, endEl].forEach((f) => f.addEventListener('input', () => fieldError('questDateErr', '', endEl)));

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const idUser = userEl.value;
        const titre = titleEl.value.trim();
        const categorie = document.getElementById('questCategory').value;
        const note = document.getElementById('questNote').value.trim();
        const dateDebut = startEl.value, dateFin = endEl.value;
        const xpEl = document.getElementById('questXP');
        const xp = Math.min(500, Math.max(10, parseInt(xpEl.value, 10) || 50));

        // Auto-add input en cours
        const pendingInput = document.getElementById('adminEtapeInput');
        if (pendingInput && pendingInput.value.trim()) {
            adminPendingEtapes.push(pendingInput.value.trim());
            pendingInput.value = '';
        }

        let ok = true;
        if (!idUser) { fieldError('questUserErr', 'Choisis un aventurier.', userEl); ok = false; }
        if (!titre) { fieldError('questTitleErr', "Indique l'intitulé du but.", titleEl); ok = false; }
        if (!dateDebut || !dateFin) { fieldError('questDateErr', "Choisis l'heure de début et l'heure de fin.", endEl); ok = false; }
        else if (new Date(dateFin) <= new Date(dateDebut)) { fieldError('questDateErr', "L'heure de fin doit venir après l'heure de début.", endEl); ok = false; }
        if (adminPendingEtapes.length === 0) {
            fieldError('adminQuestEtapesErr', "Ajoute au moins une étape pour cet objectif.");
            document.getElementById('adminEtapeInput')?.focus();
            ok = false;
        }

        if (!ok) { form.querySelector('[aria-invalid="true"]')?.focus(); return; }

        const created = AdminController.addQuest(idUser, titre, categorie, note, dateDebut, dateFin, xp, adminPendingEtapes);
        form.reset();
        resetDates();
        adminPendingEtapes = [];
        renderAdminPendingEtapes();
        YQ.closeDialog(document.getElementById('dlgQuest'));
        renderQuestsTable();
        document.querySelector(`#adminQuestsTableBody tr[data-id="${created.id}"]`)?.classList.add('flash');
        YQ.toast(`But assigné à ${created.pseudo} avec ${created.etapes.length} étape(s)`);
    });
});
