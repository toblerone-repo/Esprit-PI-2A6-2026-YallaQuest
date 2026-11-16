// Yalla Quest · Back-Office · Contrôleur des guildes (administration).
// Créer, modifier et supprimer une guilde dans le modèle (Model/GuildeModel.js). La vue (js/admin-guildes.js)
// les appelle puis se redessine. INTERFACE SEULEMENT : à remplacer plus tard par le contrôleur PHP (POST des formulaires).
const GuildeController = {
  // le créateur en devient le premier membre ; renvoie l'id de la nouvelle guilde
  creer(data, id_createur) {
    const id = Math.max(0, ...GUILDES.map((x) => x.id)) + 1;
    GUILDES.push({ id, ...data, id_createur, date_creation: TODAY });
    MEMBRES.push({ id_guilde: id, id_utilisateur: id_createur, date_adhesion: TODAY });
    return id;
  },

  modifier(g, data) {
    Object.assign(g, data);
  },

  // supprimer la guilde libère ses membres, qui peuvent en rejoindre une autre
  supprimer(g) {
    GUILDES.splice(GUILDES.indexOf(g), 1);
    for (let i = MEMBRES.length - 1; i >= 0; i--) if (MEMBRES[i].id_guilde === g.id) MEMBRES.splice(i, 1);
  },
};
