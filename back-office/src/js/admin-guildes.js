// Yalla Quest · Back-Office · Vue de l'administration des guildes (CRUD).
// Données : Model/GuildeModel.js ; créer / modifier / supprimer : Controller/GuildeController.js.
// INTERFACE SEULEMENT : les données ci-dessous sont un échantillon en mémoire, perdu au rechargement.
// Chaque formulaire est un vrai <form method="post"> dont les champs portent les noms des colonnes du MLD
// (id, nom, description, id_competence, id_createur, statut). Pour brancher le backend : retirer le
// preventDefault() de onSubmit, pointer action="" vers le contrôleur PHP, et remplacer les tableaux par
// les données renvoyées par le modèle.
(() => {
  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => [...el.querySelectorAll(s)];
  const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
  const BASE = document.body.dataset.base || 'front-office/'; // ressources communes (front-office/img/)
  const d = (iso) => new Date(iso + 'T12:00:00').toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' });
  const plural = (k, one, many) => `${k} ${k > 1 ? many : one}`;

  const image = (c) => `${BASE}img/piliers/${competence(c)?.img || 'focus'}.jpg`;
  const initiales = (u) => utilisateur(u).pseudo.split('.').map((w) => w[0]).join('').toUpperCase();

  const state = { q: '', statut: 'all' };
  const canvas = $('#canvas');
  const dlg = $('#dialog');
  let trigger = null; // le bouton qui a ouvert la fenêtre : il reprend le focus à la fermeture

  /* ---------- page ---------- */
  canvas.innerHTML = `
    <div class="title-row">
      <div><p class="kicker">Administration · guildes</p><h1>Guildes</h1>
        <p class="about">Créer, consulter, modifier et supprimer les guildes.</p>
        <p class="sub" id="a-summary"></p></div>
      <div class="actions"><button class="btn primary" type="button" data-a="create">Nouvelle guilde</button></div>
    </div>
    <div class="filters admin-filters" role="search">
      <div class="search"><label class="sr" for="a-q">Rechercher une guilde</label>
        <input class="input" id="a-q" type="search" placeholder="Rechercher par nom, description ou créateur" autocomplete="off"></div>
      <label class="sr" for="a-statut">Filtrer par statut</label>
      <select class="input" id="a-statut"><option value="all">Tous les statuts</option>${Object.entries(STATUTS).map(([k, v]) => `<option value="${k}">${v}</option>`).join('')}</select>
      <span class="stamp" id="a-count" aria-live="polite"></span>
    </div>
    <div class="sheet"><div class="table-wrap"><table class="respond admin-table">
      <thead><tr><th>Guilde</th><th class="c-comp">Compétence</th><th>Créée par</th><th class="n">Membres</th><th>Statut</th><th class="n"><span class="sr">Actions</span></th></tr></thead>
      <tbody id="a-rows"></tbody></table></div></div>
    <footer class="pagefoot"><img src="${BASE}img/logo.png" alt="" width="22" height="22">Yalla Quest · « Chaque habitude est une quête. »</footer>`;

  const pill = (s) => `<span class="pill st-${esc(s)}">${esc(STATUTS[s] || s)}</span>`;

  function refresh() {
    const q = state.q.trim().toLowerCase();
    const list = GUILDES
      .filter((g) => state.statut === 'all' || g.statut === state.statut)
      .filter((g) => !q || [g.nom, g.description, utilisateur(g.id_createur)?.pseudo].some((t) => (t || '').toLowerCase().includes(q)))
      .sort((a, b) => a.nom.localeCompare(b.nom, 'fr'));
    $('#a-rows').innerHTML = list.map((g) => {
      const n = membresDe(g.id).length;
      return `<tr data-id="${g.id}">
        <td class="id"><span class="gcell"><img src="${image(g.id_competence)}" alt=""><span><strong>${esc(g.nom)}</strong><span class="desc">${esc(g.description)}</span></span></span></td>
        <td class="hide-sm c-comp">${esc(competence(g.id_competence)?.nom || '—')}</td>
        <td class="hide-sm" style="white-space:nowrap">${esc(utilisateur(g.id_createur)?.pseudo || '—')}<span class="desc">le ${d(g.date_creation)}</span></td>
        <td class="n second">${n} / ${MAX_MEMBRES}<span class="sm-only"> membres · ${esc(STATUTS[g.statut])}</span></td>
        <td class="hide-sm">${pill(g.statut)}</td>
        <td class="end"><span class="row-actions">
          <button class="btn" type="button" data-a="view" data-id="${g.id}" aria-label="Voir ${esc(g.nom)}">Voir</button>
          <button class="btn" type="button" data-a="edit" data-id="${g.id}" aria-label="Modifier ${esc(g.nom)}">Modifier</button>
          <button class="btn danger" type="button" data-a="delete" data-id="${g.id}" aria-label="Supprimer ${esc(g.nom)}">Supprimer</button>
        </span></td></tr>`;
    }).join('') || `<tr><td colspan="6"><div class="empty">${GUILDES.length
      ? `Aucune guilde ne correspond${state.q.trim() ? ` à « ${esc(state.q.trim())} »` : ''}${state.statut !== 'all' ? ` avec le statut ${esc(STATUTS[state.statut].toLowerCase())}` : ''}.`
      : 'Aucune guilde pour le moment. Créez la première avec « Nouvelle guilde ».'}</div></td></tr>`;

    const total = MEMBRES.length, susp = GUILDES.filter((g) => g.statut === 'suspendue').length, pleines = GUILDES.filter((g) => membresDe(g.id).length >= MAX_MEMBRES).length;
    $('#a-summary').innerHTML = `<strong>${plural(GUILDES.length, 'guilde', 'guildes')}</strong> · ${plural(total, 'membre', 'membres')} au total${susp ? ` · ${plural(susp, 'suspendue', 'suspendues')}` : ''}${pleines ? ` · ${plural(pleines, 'complète', 'complètes')}` : ''}.`;
    $('#a-count').textContent = list.length === GUILDES.length ? '' : `${list.length} sur ${GUILDES.length}`;
  }

  /* ---------- fenêtres ---------- */
  let pending = false; // le focus reste à rendre au déclencheur
  function open(html) {
    dlg.innerHTML = html;
    $$('[data-cancel]', dlg).forEach((b) => b.addEventListener('click', closeDlg));
    dlg.showModal();
    pending = true;
  }
  // rend le focus au bouton qui a ouvert la fenêtre ; si sa ligne a été redessinée, on retrouve le bouton équivalent
  function restoreFocus() {
    let t = trigger;
    if (t && !t.isConnected && t.dataset.a) t = $(`[data-a="${t.dataset.a}"][data-id="${t.dataset.id}"]`, canvas);
    (t && t.isConnected ? t : $('[data-a="create"]', canvas))?.focus();
    trigger = null; pending = false;
  }
  function closeDlg() { dlg.close(); restoreFocus(); }
  // fermeture par Échap
  dlg.addEventListener('close', () => { if (!dlg.open && pending) restoreFocus(); });

  const options = (list, sel, label, extra = () => '') => list.map((x) => `<option value="${x.id}" ${x.id === sel ? 'selected' : ''} ${extra(x)}>${esc(label(x))}</option>`).join('');

  // Créer (g absent) ou modifier (g présent)
  function formDialog(g) {
    const edit = !!g;
    const createurs = UTILISATEURS.map((u) => ({ ...u, busy: guildeDe(u.id) }));
    const libres = createurs.filter((u) => !u.busy).length;
    open(`<form method="post" action="" data-form="${edit ? 'update' : 'create'}">
      <h2 id="dialog-title">${edit ? 'Modifier la guilde' : 'Nouvelle guilde'}</h2>
      ${edit ? `<input type="hidden" name="id" value="${g.id}">` : '<p class="lead">Le créateur en devient le premier membre. Neuf autres pourront la rejoindre.</p>'}
      <div class="field"><label for="f-nom">Nom</label><input class="input" id="f-nom" name="nom" required maxlength="40" value="${esc(g?.nom)}" autocomplete="off"></div>
      <div class="field"><label for="f-desc">Description</label><textarea class="input" id="f-desc" name="description" required maxlength="160" aria-describedby="f-desc-h">${esc(g?.description)}</textarea><span class="hint" id="f-desc-h">160 caractères au plus.</span></div>
      <div class="form-2">
        <div class="field"><label for="f-comp">Compétence</label><select class="input" id="f-comp" name="id_competence" required>${edit ? '' : '<option value="" selected disabled>Choisir</option>'}${options(COMPETENCES, g?.id_competence, (c) => c.nom)}</select></div>
        <div class="field"><label for="f-statut">Statut</label><select class="input" id="f-statut" name="statut" required>${Object.entries(STATUTS).map(([k, v]) => `<option value="${k}" ${k === (g?.statut || 'active') ? 'selected' : ''}>${v}</option>`).join('')}</select></div>
      </div>
      ${edit
        ? `<p class="hint">Créée par <strong>${esc(utilisateur(g.id_createur)?.pseudo)}</strong> le ${d(g.date_creation)} · ${membresDe(g.id).length} / ${MAX_MEMBRES} membres.</p>`
        : `<div class="field"><label for="f-createur">Créateur</label><select class="input" id="f-createur" name="id_createur" required aria-describedby="f-createur-h"><option value="" selected disabled>Choisir un utilisateur</option>${options(createurs, null, (u) => u.busy ? `${u.pseudo} (membre de ${guilde(u.busy).nom})` : u.pseudo, (u) => (u.busy ? 'disabled' : ''))}</select><span class="hint" id="f-createur-h">${libres ? "Un utilisateur n'appartient qu'à une guilde : ceux qui en ont déjà une sont grisés." : "Tous les utilisateurs ont déjà une guilde : aucun ne peut en créer une autre pour le moment."}</span></div>`}
      <div class="foot"><button class="btn" type="button" data-cancel>Annuler</button><button class="btn primary" type="submit">${edit ? 'Enregistrer' : 'Créer la guilde'}</button></div>
    </form>`);
    const form = $('form', dlg), nom = $('#f-nom', dlg);
    // nom unique (comparaison sans casse ni espaces autour)
    const checkNom = () => {
      const v = nom.value.trim().toLowerCase();
      nom.setCustomValidity(v && GUILDES.some((x) => x.nom.trim().toLowerCase() === v && x.id !== g?.id) ? 'Une guilde porte déjà ce nom.' : '');
    };
    nom.addEventListener('input', checkNom);
    form.addEventListener('submit', (e) => {
      e.preventDefault(); // à retirer quand le contrôleur PHP traitera le POST
      checkNom();
      if (!form.reportValidity()) return;
      const fd = new FormData(form);
      const data = { nom: fd.get('nom').trim(), description: fd.get('description').trim(), id_competence: +fd.get('id_competence'), statut: fd.get('statut') };
      if (!data.nom || !data.description) { (!data.nom ? nom : $('#f-desc', dlg)).setCustomValidity('Ce champ ne peut pas être vide.'); form.reportValidity(); return; }
      if (edit) {
        GuildeController.modifier(g, data);
        trigger = $(`[data-a="edit"][data-id="${g.id}"]`) || trigger;
        refresh(); closeDlg(); toast('Modifications enregistrées');
      } else {
        const id = GuildeController.creer(data, +fd.get('id_createur'));
        refresh(); closeDlg(); toast(`Guilde « ${data.nom} » créée`);
        $(`tr[data-id="${id}"]`)?.classList.add('flash');
      }
    });
    $('#f-desc', dlg).addEventListener('input', (e) => e.target.setCustomValidity(''));
    nom.focus();
  }

  function viewDialog(g) {
    const mem = membresDe(g.id).sort((a, b) => a.date_adhesion.localeCompare(b.date_adhesion));
    open(`<form>
      <div class="view-head"><img src="${image(g.id_competence)}" alt="" width="56" height="56"><div><h2 id="dialog-title">${esc(g.nom)}</h2>${pill(g.statut)}</div></div>
      <p class="lead">${esc(g.description)}</p>
      <dl class="facts">
        <dt>Compétence</dt><dd>${esc(competence(g.id_competence)?.nom || '—')}</dd>
        <dt>Créateur</dt><dd>${esc(utilisateur(g.id_createur)?.pseudo || '—')}</dd>
        <dt>Création</dt><dd>${d(g.date_creation)}</dd>
        <dt>Membres</dt><dd>${mem.length} / ${MAX_MEMBRES}</dd>
      </dl>
      <div class="rows">${mem.map((m) => `<div class="row"><span class="avatar sm" aria-hidden="true">${initiales(m.id_utilisateur)}</span><span class="who"><strong>${esc(utilisateur(m.id_utilisateur).pseudo)}</strong><span>${m.id_utilisateur === g.id_createur ? 'A créé la guilde · ' : ''}membre depuis le ${d(m.date_adhesion)}</span></span></div>`).join('') || '<div class="row"><span class="who"><span>Aucun membre.</span></span></div>'}</div>
      <div class="foot"><button class="btn" type="button" data-cancel>Fermer</button><button class="btn primary" type="button" data-a="edit" data-id="${g.id}">Modifier</button></div>
    </form>`);
  }

  function deleteDialog(g) {
    const n = membresDe(g.id).length;
    open(`<form method="post" action="" data-form="delete"><input type="hidden" name="id" value="${g.id}">
      <h2 id="dialog-title">Supprimer « ${esc(g.nom)} » ?</h2>
      <p class="lead">${n ? `Ses ${plural(n, 'membre sera retiré', 'membres seront retirés')} de la guilde et pourront en rejoindre une autre. ` : ''}Cette action est définitive.</p>
      <div class="foot"><button class="btn" type="button" data-cancel>Annuler</button><button class="btn primary destructive" type="submit">Supprimer la guilde</button></div>
    </form>`);
    $('form', dlg).addEventListener('submit', (e) => {
      e.preventDefault(); // à retirer quand le contrôleur PHP traitera le POST
      GuildeController.supprimer(g);
      trigger = null;
      refresh(); closeDlg(); toast(`Guilde « ${g.nom} » supprimée`);
    });
    $('[data-cancel]', dlg).focus(); // le choix sûr reçoit le focus
  }

  /* ---------- toast ---------- */
  let tId;
  function toast(msg) { const t = $('#toast'); t.textContent = msg; t.classList.add('show'); clearTimeout(tId); tId = setTimeout(() => t.classList.remove('show'), 2400); }

  /* ---------- événements ---------- */
  document.addEventListener('click', (e) => {
    const b = e.target.closest('[data-a]');
    if (!b) return;
    const g = b.dataset.id ? guilde(+b.dataset.id) : null;
    if (b.closest('#dialog')) { if (b.dataset.a === 'edit' && g) { dlg.close(); trigger = $(`[data-a="edit"][data-id="${g.id}"]`); formDialog(g); } return; }
    trigger = b;
    if (b.dataset.a === 'create') formDialog(null);
    else if (b.dataset.a === 'view' && g) viewDialog(g);
    else if (b.dataset.a === 'edit' && g) formDialog(g);
    else if (b.dataset.a === 'delete' && g) deleteDialog(g);
  });
  $('#a-q').addEventListener('input', (e) => { state.q = e.target.value; refresh(); });
  $('#a-statut').addEventListener('change', (e) => { state.statut = e.target.value; refresh(); });

  // menu plein écran sur téléphone (même comportement que le front)
  const sb = $('#sidebar'), tg = $('.menu-toggle');
  const menu = (o) => { sb.classList.toggle('open', o); tg?.setAttribute('aria-expanded', o); };
  tg?.addEventListener('click', () => menu(true));
  $('.close-menu')?.addEventListener('click', () => menu(false));

  refresh();
})();
