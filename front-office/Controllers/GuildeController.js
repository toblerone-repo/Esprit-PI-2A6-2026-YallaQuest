// Yalla Quest · Front-Office · Contrôleur des guildes.
// Les actions du membre connecté (ME_ID) sur le modèle (Model/GuildeModel.js). La vue (js/guildes.js) les appelle
// puis se redessine ; aucune fonction ici n'écrit dans la page. À remplacer plus tard par des appels au contrôleur PHP.
const GuildeController = {
  // vote ou change son vote ; renvoie null si c'est déjà ce vote, sinon 'modifié' ou 'enregistré'
  voter(pid, reponse) {
    const p = PROPOSALS.find((x) => x.id === pid);
    const v = VOTES.find((x) => x.proposal === pid && x.user === ME_ID);
    if (v && v.reponse_vote === reponse) return null;
    if (v) { Object.assign(v, { reponse_vote: reponse, date_vote: TODAY }); return 'modifié'; }
    VOTES.push({ user: ME_ID, guild: p.guild, proposal: p.id, date_vote: TODAY, reponse_vote: reponse });
    return 'enregistré';
  },

  rejoindre(gid) {
    ADHESIONS.push({ user: ME_ID, guild: gid, date_adhesion: TODAY });
  },

  // quitter retire l'adhésion et les votes du membre encore ouverts dans cette guilde
  quitter(gid) {
    ADHESIONS.splice(ADHESIONS.findIndex((a) => a.user === ME_ID && a.guild === gid), 1);
    for (let i = VOTES.length - 1; i >= 0; i--) { const v = VOTES[i]; if (v.user === ME_ID && v.guild === gid && isOpen(PROPOSALS.find((p) => p.id === v.proposal))) VOTES.splice(i, 1); }
  },

  // un membre n'appartient qu'à une guilde : changer = quitter l'ancienne puis rejoindre la nouvelle
  changerDeGuilde(depuis, vers) {
    this.quitter(depuis);
    this.rejoindre(vers);
  },

  proposer(gid, qid, dateLimite) {
    PROPOSALS.push({ id: 'p' + Date.now().toString(36), guild: gid, quest: qid, date_proposition: TODAY, date_limite_vote: dateLimite });
  },
};
