// Yalla Quest · Back-Office · Modèle des guildes (administration).
// INTERFACE SEULEMENT : échantillon en mémoire (tables du MLD), perdu au rechargement.
// À remplacer plus tard par les données renvoyées par le modèle PHP.
const TODAY = new Date().toISOString().slice(0, 10);
const MAX_MEMBRES = 10; // règle des guildes : 10 membres au plus
const COMPETENCES = [
  { id: 1, nom: 'Force', img: 'force' },
  { id: 2, nom: 'Endurance', img: 'endurance' },
  { id: 3, nom: 'Mobilité', img: 'mobilite' },
  { id: 4, nom: 'Focus', img: 'focus' },
  { id: 5, nom: 'Nutrition', img: 'nutrition' },
];
const UTILISATEURS = ['yasmine.t', 'karim.bs', 'nour.h', 'sami.j', 'leila.m', 'omar.g', 'ines.b', 'mehdi.c', 'rania.a', 'hedi.b', 'amira.k', 'youssef.d', 'salma.r', 'anis.f', 'hiba.z', 'lina.s', 'walid.m', 'meriem.k'] // les trois derniers n'ont pas de guilde
  .map((pseudo, i) => ({ id: i + 1, pseudo }));
const STATUTS = { active: 'Active', suspendue: 'Suspendue', archivee: 'Archivée' };
const GUILDES = [
  { id: 1, nom: 'Les Lanternes', description: "Marcher, s'étirer et cuisiner ensemble après les cours.", id_createur: 1, id_competence: 3, statut: 'active', date_creation: '2026-02-10' },
  { id: 2, nom: 'Souffle profond', description: 'Respiration et étirements, dix minutes par jour, sans pression.', id_createur: 2, id_competence: 4, statut: 'active', date_creation: '2025-10-20' },
  { id: 3, nom: 'Médina en mouvement', description: 'Explorer la ville à pied, une ruelle à la fois.', id_createur: 5, id_competence: 2, statut: 'active', date_creation: '2026-04-02' },
  { id: 4, nom: 'Cuisine de saison', description: 'Recettes tunisiennes locales, légères et de saison.', id_createur: 6, id_competence: 5, statut: 'active', date_creation: '2026-07-14' },
  { id: 5, nom: 'Coureurs du matin', description: 'Courir 5 km avant 8 h, trois fois par semaine.', id_createur: 4, id_competence: 1, statut: 'suspendue', date_creation: '2026-09-01' },
];
// MembreGuilde (id_guilde, id_utilisateur, date_adhesion) : un utilisateur n'appartient qu'à une guilde
const MEMBRES = [
  [1, 1, '2026-02-10'], [1, 3, '2026-02-12'], [1, 7, '2026-05-15'], [1, 11, '2026-06-02'], [1, 9, '2026-09-20'],
  [2, 2, '2025-10-20'], [2, 12, '2025-11-05'], [2, 8, '2026-07-03'],
  [3, 5, '2026-04-02'], [3, 13, '2026-04-10'], [3, 10, '2026-11-12'],
  [4, 6, '2026-07-14'], [4, 14, '2026-07-20'],
  [5, 4, '2026-09-01'], [5, 15, '2026-09-03'],
].map(([id_guilde, id_utilisateur, date_adhesion]) => ({ id_guilde, id_utilisateur, date_adhesion }));

const competence = (id) => COMPETENCES.find((c) => c.id === id);
const utilisateur = (id) => UTILISATEURS.find((u) => u.id === id);
const guilde = (id) => GUILDES.find((g) => g.id === id);
const membresDe = (g) => MEMBRES.filter((m) => m.id_guilde === g);
const guildeDe = (u) => MEMBRES.find((m) => m.id_utilisateur === u)?.id_guilde ?? null;
