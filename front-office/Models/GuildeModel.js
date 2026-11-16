// Yalla Quest · Front-Office · Modèle des guildes.
// Les données (échantillon en mémoire, perdu au rechargement) et les règles de lecture.
// Chargé avant Controller/GuildeController.js et js/guildes.js, qui lisent ces constantes.
// Sample data shaped on the MCD: UTILISATEUR, GUILDE, QUÊTE, PROPOSITION; Créer, Adhérer, Proposer, Concerner, Voter.
// Every figure on the screens is computed from these rows.
const NOW = '2026-11-15T14:38';
const MAX_MEMBERS = 10; // règle des guildes : 10 membres max
const TODAY = NOW.slice(0, 10);
const ME_ID = 'u1';

// UTILISATEUR (id_utilisateur, pseudo, email, mot_de_passe — never sent to the page, date_inscription)
const USERS = [
  { id: 'u1',  pseudo: 'yasmine.t', email: 'yasmine.t@exemple.tn', date_inscription: '2026-01-19' },
  { id: 'u2',  pseudo: 'karim.bs',  email: 'karim.bs@exemple.tn',  date_inscription: '2025-09-02' },
  { id: 'u3',  pseudo: 'nour.h',    email: 'nour.h@exemple.tn',    date_inscription: '2025-10-11' },
  { id: 'u4',  pseudo: 'sami.j',    email: 'sami.j@exemple.tn',    date_inscription: '2026-02-03' },
  { id: 'u5',  pseudo: 'leila.m',   email: 'leila.m@exemple.tn',   date_inscription: '2026-03-14' },
  { id: 'u6',  pseudo: 'omar.g',    email: 'omar.g@exemple.tn',    date_inscription: '2026-04-21' },
  { id: 'u7',  pseudo: 'ines.b',    email: 'ines.b@exemple.tn',    date_inscription: '2026-05-08' },
  { id: 'u8',  pseudo: 'mehdi.c',   email: 'mehdi.c@exemple.tn',   date_inscription: '2026-06-30' },
  { id: 'u9',  pseudo: 'rania.a',   email: 'rania.a@exemple.tn',   date_inscription: '2026-09-12' },
  { id: 'u10', pseudo: 'hedi.b',    email: 'hedi.b@exemple.tn',    date_inscription: '2026-11-12' },
  { id: 'u11', pseudo: 'amira.k',   email: 'amira.k@exemple.tn',   date_inscription: '2026-05-30' },
  { id: 'u12', pseudo: 'youssef.d', email: 'youssef.d@exemple.tn', date_inscription: '2025-11-01' },
  { id: 'u13', pseudo: 'salma.r',   email: 'salma.r@exemple.tn',   date_inscription: '2026-04-08' },
  { id: 'u14', pseudo: 'anis.f',    email: 'anis.f@exemple.tn',    date_inscription: '2026-07-18' },
  { id: 'u15', pseudo: 'hiba.z',    email: 'hiba.z@exemple.tn',    date_inscription: '2026-09-02' },
];

// GUILDE (id_guilde, nom, description, date_creation) + Créer (createur, 1,1)
const GUILDS = [
  { id: 'g1', nom: 'Les Lanternes',        description: "Marcher, s'étirer et cuisiner ensemble après les cours.",  date_creation: '2026-02-10', createur: 'u1' },
  { id: 'g2', nom: 'Souffle profond',      description: 'Respiration et étirements, dix minutes par jour, sans pression.', date_creation: '2025-10-20', createur: 'u2' },
  { id: 'g3', nom: 'Médina en mouvement',  description: 'Explorer la ville à pied, une ruelle à la fois.',              date_creation: '2026-04-02', createur: 'u5' },
  { id: 'g4', nom: 'Cuisine de saison',    description: 'Recettes tunisiennes locales, légères et de saison.',          date_creation: '2026-07-14', createur: 'u6' },
  { id: 'g5', nom: 'Coureurs du matin',    description: 'Courir 5 km avant 8 h, trois fois par semaine.',               date_creation: '2026-09-01', createur: 'u4' },
];

// Adhérer (utilisateur, guilde, date_adhesion)
// UTILISATEUR 0,1 — a user belongs to one guild at most; GUILDE 1,n — its founder joins on creation
const ADHESIONS = [
  ['u1', 'g1', '2026-02-10'], ['u3', 'g1', '2026-02-12'], ['u7', 'g1', '2026-05-15'], ['u11', 'g1', '2026-06-02'], ['u9', 'g1', '2026-09-20'],
  ['u2', 'g2', '2025-10-20'], ['u12', 'g2', '2025-11-05'], ['u8', 'g2', '2026-07-03'],
  ['u5', 'g3', '2026-04-02'], ['u13', 'g3', '2026-04-10'], ['u10', 'g3', '2026-11-12'],
  ['u6', 'g4', '2026-07-14'], ['u14', 'g4', '2026-07-20'],
  ['u4', 'g5', '2026-09-01'], ['u15', 'g5', '2026-09-03'],
].map(([user, guild, date_adhesion]) => ({ user, guild, date_adhesion }));

// QUÊTE (id_quete, titre, description, date_debut, date_fin, type, statut)
// type = one of the five Yalla Quest skills: Force, Endurance, Mobilité, Focus, Nutrition
const QUESTS = [
  { id: 'q1', titre: 'Courir 5 km avant midi. Sans écouteurs.', description: 'Juste votre souffle et vos pas. Remarquez trois choses sur votre trajet habituel.', date_debut: '2026-11-23', date_fin: '2026-11-30', type: 'Endurance', statut: 'À venir' },
  { id: 'q2', titre: 'Préparer une salade méchouia',          description: 'Poivrons et tomates grillés, sans friture. Une photo de l\'assiette suffit.', date_debut: '2026-11-18', date_fin: '2026-11-25', type: 'Nutrition', statut: 'À venir' },
  { id: 'q3', titre: 'Marcher 30 minutes dans la médina',     description: 'Un parcours différent chaque jour, à son rythme.',                   date_debut: '2026-11-21', date_fin: '2026-12-05', type: 'Endurance', statut: 'À venir' },
  { id: 'q4', titre: '30 jours de gainage',                   description: 'Renforcement adapté à son niveau, de 20 secondes à 2 minutes.',      date_debut: '2026-12-01', date_fin: '2026-12-30', type: 'Force', statut: 'À venir' },
  { id: 'q5', titre: "Séance d'étirements guidée",            description: 'Quinze minutes, trois fois par semaine, le dos et les hanches.',     date_debut: '2026-11-02', date_fin: '2026-11-30', type: 'Mobilité', statut: 'En cours' },
  { id: 'q6', titre: 'Cuisiner un plat de saison',            description: 'Un légume du marché, une recette de famille, sans friture.',        date_debut: '2026-10-05', date_fin: '2026-10-26', type: 'Nutrition', statut: 'Terminée' },
  { id: 'q7', titre: 'Courir un semi-marathon en groupe',     description: 'Le semi de Carthage, allure libre, on part et on arrive ensemble.', date_debut: '2026-12-06', date_fin: '2026-12-06', type: 'Endurance', statut: 'À venir' },
  { id: 'q8', titre: '10 minutes de respiration chaque soir', description: 'Respiration guidée avant de dormir, téléphone dans une autre pièce.', date_debut: '2026-10-01', date_fin: '2026-12-17', type: 'Focus', statut: 'En cours' },
];

// PROPOSITION (id_p, date_proposition, date_limite_vote) + Proposer (guilde, 1,1) + Concerner (quête, 1,1)
const PROPOSALS = [
  { id: 'p1',  guild: 'g1', quest: 'q2', date_proposition: '2026-11-12', date_limite_vote: '2026-11-16' },
  { id: 'p2',  guild: 'g1', quest: 'q1', date_proposition: '2026-11-14', date_limite_vote: '2026-11-19' },
  { id: 'p3',  guild: 'g1', quest: 'q4', date_proposition: '2026-11-10', date_limite_vote: '2026-11-25' },
  { id: 'p4',  guild: 'g1', quest: 'q8', date_proposition: '2026-09-20', date_limite_vote: '2026-09-27' },
  { id: 'p5',  guild: 'g1', quest: 'q6', date_proposition: '2026-09-25', date_limite_vote: '2026-10-01' },
  { id: 'p6',  guild: 'g2', quest: 'q5', date_proposition: '2026-10-25', date_limite_vote: '2026-10-31' },
  { id: 'p7',  guild: 'g2', quest: 'q1', date_proposition: '2026-11-13', date_limite_vote: '2026-11-18' },
  { id: 'p8',  guild: 'g3', quest: 'q3', date_proposition: '2026-11-11', date_limite_vote: '2026-11-17' },
  { id: 'p9',  guild: 'g4', quest: 'q6', date_proposition: '2026-09-28', date_limite_vote: '2026-10-03' },
  { id: 'p10', guild: 'g5', quest: 'q7', date_proposition: '2026-11-09', date_limite_vote: '2026-11-20' },
];

// Voter (utilisateur, guilde, proposition, date_vote, reponse_vote)
const VOTES = [
  ['u3', 'g1', 'p1', '2026-11-12', 'pour'], ['u11', 'g1', 'p1', '2026-11-13', 'pour'], ['u7', 'g1', 'p1', '2026-11-13', 'contre'],
  ['u9', 'g1', 'p2', '2026-11-14', 'pour'],
  ['u1', 'g1', 'p3', '2026-11-10', 'pour'], ['u3', 'g1', 'p3', '2026-11-11', 'contre'], ['u11', 'g1', 'p3', '2026-11-11', 'contre'], ['u7', 'g1', 'p3', '2026-11-12', 'pour'],
  ['u1', 'g1', 'p4', '2026-09-21', 'pour'], ['u3', 'g1', 'p4', '2026-09-21', 'pour'], ['u11', 'g1', 'p4', '2026-09-22', 'pour'], ['u7', 'g1', 'p4', '2026-09-24', 'contre'],
  ['u1', 'g1', 'p5', '2026-09-26', 'contre'], ['u3', 'g1', 'p5', '2026-09-27', 'pour'], ['u9', 'g1', 'p5', '2026-09-28', 'contre'], ['u7', 'g1', 'p5', '2026-09-30', 'contre'],
  ['u2', 'g2', 'p6', '2026-10-25', 'pour'], ['u12', 'g2', 'p6', '2026-10-26', 'pour'], ['u8', 'g2', 'p6', '2026-10-27', 'pour'],
  ['u2', 'g2', 'p7', '2026-11-13', 'pour'], ['u8', 'g2', 'p7', '2026-11-14', 'contre'],
  ['u5', 'g3', 'p8', '2026-11-11', 'pour'], ['u13', 'g3', 'p8', '2026-11-12', 'pour'],
  ['u6', 'g4', 'p9', '2026-09-28', 'pour'], ['u14', 'g4', 'p9', '2026-09-30', 'pour'],
  ['u4', 'g5', 'p10', '2026-11-09', 'pour'], ['u15', 'g5', 'p10', '2026-11-14', 'contre'],
].map(([user, guild, proposal, date_vote, reponse_vote]) => ({ user, guild, proposal, date_vote, reponse_vote }));

// ---- rules (sample assumptions, to confirm): a vote closes at 23:59 on date_limite_vote;
// a proposition is adopted when "pour" outnumbers "contre" at closing; a member may change their vote until then.
const isOpen = (p) => p.date_limite_vote >= TODAY;
const membersOf = (g) => ADHESIONS.filter((a) => a.guild === g);
const isMember = (u, g) => ADHESIONS.some((a) => a.user === u && a.guild === g);
const guildOf = (u) => ADHESIONS.find((a) => a.user === u)?.guild || null; // 0 or 1 guild per user
const votesOf = (p) => VOTES.filter((v) => v.proposal === p);
const outcome = (p) => { const v = votesOf(p.id); const pour = v.filter((x) => x.reponse_vote === 'pour').length; return pour > v.length - pour ? 'Adoptée' : 'Rejetée'; };

// ---- checks: the sample data obeys the MCD's cardinalities and the rules above ----
GUILDS.forEach((g) => {
  console.assert(membersOf(g.id).length >= 1, 'GUILDE 1,n Adhérer', g.id);
  console.assert(isMember(g.createur, g.id), 'founder is a member', g.id);
  console.assert(membersOf(g.id).length <= MAX_MEMBERS, '10 members max', g.id);
});
QUESTS.forEach((q) => console.assert(PROPOSALS.some((p) => p.quest === q.id), 'QUÊTE 1,n Concerner', q.id));
VOTES.forEach((v) => {
  const p = PROPOSALS.find((x) => x.id === v.proposal), a = ADHESIONS.find((x) => x.user === v.user && x.guild === v.guild);
  console.assert(p.guild === v.guild, 'vote cast in the proposing guild', v);
  console.assert(a && a.date_adhesion <= v.date_vote, 'only members vote', v);
  console.assert(v.date_vote >= p.date_proposition && v.date_vote <= p.date_limite_vote, 'vote inside the voting window', v);
});
console.assert(new Set(VOTES.map((v) => v.user + v.proposal)).size === VOTES.length, 'one vote per member per proposition');
console.assert(new Set(ADHESIONS.map((a) => a.user)).size === ADHESIONS.length, 'UTILISATEUR 0,1 Adhérer: one guild per user');
ADHESIONS.forEach((a) => console.assert(USERS.find((u) => u.id === a.user).date_inscription <= a.date_adhesion, 'joined after signing up', a));
