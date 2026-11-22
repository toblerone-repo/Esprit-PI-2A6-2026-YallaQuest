# Intégration des six branches

Cette branche réunit les versions publiées ci-dessous. Les branches sources ne sont pas modifiées. Les historiques sont conservés par des commits de fusion.

| Branche | Commit intégré |
| --- | --- |
| Houssem | `fd9a9597835cd2c7b970ba4de22127ad39889f41` |
| Darine | `5b1715ab32923fdee68cd19b87872b8abba8ae31` |
| Eya | `552d5e842551669a8c5d22b3373a0be9362b4687` |
| Amine | `5eff88f947c09d6f0b899e98fb8f905b7001f8f9` |
| Mohamed | `9bd6a0ee90b8d175fb5ffff11fc24af6603fd35c` |
| Dorra | `ba12e8c963d0b991a86320bd3612611db0d5e284` |

## Contributions conservées

| Contribution | Travail conservé |
| --- | --- |
| Houssem | Squelette MVC, requêtes préparées, hachage/vérification du mot de passe, CSRF, XSS, en-têtes HTTP et recherche nom/e-mail/rôle/ID. |
| Darine | Modèle avec getters/setters et fromArray, liste/comptage/recherche par ID, règles de protection du compte admin, compétences/totems, images et mises en page communes. |
| Eya | Journal, activités, types d’activité, inscription avec pseudo/hachage et blocage/déblocage récemment publiés. |
| Amine | Connexion, déconnexion, espace utilisateur, sessions sécurisées et délai de 30 minutes, suppression avec messages et gestion des clés étrangères, validations REGEX, pages et scripts publiés. |
| Mohamed | Objectifs front/back, formulaire join_now, scripts et helpers HTML de ces interfaces, README. |
| Dorra | Profil réel, choix d’avatar, modification facultative du mot de passe, styles et images du profil. |

## Adaptations nécessaires à la réunion

- Le modèle central conserve le constructeur vide et fromArray de Darine. Les constructeurs positionnels de Houssem/Dorra et celui d’Amine avec DateTimeImmutable restent acceptés. Les couples getPassword/getMp et getTotalXp/getXp accèdent aux mêmes données. Les setters de dates acceptent les chaînes et DateTimeImmutable.
- Les méthodes de liste et recherche continuent à renvoyer des objets. Dorra conserve ses tableaux de profil avec getProfilUtilisateur, qui ajoute le chemin de l’image par jointure.
- updateUtilisateur conserve le nouveau mot de passe en clair et le hache avant UPDATE. updateProfilUtilisateur reçoit le hash déjà calculé par le formulaire de Dorra et vérifie de nouveau l’ancien mot de passe s’il change : aucun double hachage.
- La connexion conserve les clés user_id/username/role/last_activity d’Amine et remplit aussi utilisateur[id] pour les autres pages. Le rôle et l’existence du compte sont revérifiés avant les pages protégées.
- Les deux listes listuser.php et liste_utilisateurs.php restent accessibles : recherche dans la première, suppression et messages dans la seconde.
- Les styles de recherche, de suppression, l’ancien profil.css et le nouveau profilUtilisateur.css sont conservés. Les noms currentPage relient chaque page à sa feuille de style commune.
- Les validations REGEX d’Amine sont conservées sur le formulaire de Dorra, avec son choix d’avatar et le changement de mot de passe facultatif. Le format général d’e-mail évite de rejeter les domaines valides des autres formulaires.

## Sécurité appliquée aux contributions

- Les 24 pages ont un contrôle CSRF avant les POST/PUT/PATCH/DELETE. Les formulaires POST contiennent le token de session. Les requêtes AJAX qui changent des données passent par POST avec ce token.
- La déconnexion passe par un formulaire POST ; GET ne détruit pas la session.
- Les sorties HTML dynamiques sont échappées avec htmlspecialchars, ENT_QUOTES/ENT_SUBSTITUTE et UTF-8. Les données JSON utilisent JSON_HEX_TAG/AMP/APOS/QUOT. Les messages JavaScript utilisent textContent ou un helper d’échappement pour leurs constructions HTML.
- Les en-têtes de Houssem restent dans index.php. La CSP accepte les ressources utilisées par les templates et des nonces pour les blocs script ; les gestionnaires onclick/onsubmit existants sont conservés via script-src-attr.
- Les erreurs SQL restent dans les logs. Les e-mails déjà utilisés et les suppressions liées à des clés étrangères donnent un message contrôlé.

## État des cartes du tableau

| Tâche | État du code publié |
| --- | --- |
| Liste utilisateurs | Intégrée. |
| Recherche nom/e-mail et filtre rôle | Intégrée, avec recherche par ID combinable. |
| Suppression utilisateur | Intégrée ; propre compte et comptes liés protégés. |
| Validation REGEX | Intégrée dans les scripts existants. |
| Profil utilisateur / modification de son profil | Intégrés avec avatar et mot de passe facultatif. |
| Admin ne peut pas se supprimer ou se rétrograder | Règles serveur conservées ; la rétrogradation est un helper prêt pour le futur traitement de rôle. |
| Ajout d’un compte par admin | Traitement non publié dans les six branches. |
| Édition d’un autre compte par admin | Aucun écran/traitement complet publié ; la modification personnelle existe. |
| Changement de rôle | Traitement non publié ; garde de protection disponible. |
| Blocage/déblocage | Intégré avec les méthodes et changerStatut.php d’Eya, boutons POST/CSRF dans les deux listes, refus de blocage de soi-même et révocation des sessions des comptes bloqués. |

L’inscription publique fonctionne maintenant avec le code récemment publié par Eya. Les traitements admin encore absents ci-dessus ne sont pas inventés, conformément à la demande d’intégrer uniquement le travail publié.

## Attribution et migration SQL

Des commentaires d’auteur sont ajoutés aux sections et méthodes : Houssem, Darine, Amine, Eya, Mohamed et Dorra. Les raccordements communs sont identifiés comme Intégration.

La colonne pseudo et sa contrainte unique sont ajoutées une seule fois à la fin de yalla_quest_.sql avec l’ALTER demandé. Pour une base existante, appliquer uniquement migration-pseudo.sql une fois ; ne pas réimporter tout le schéma sur des données existantes.

## Vérifications

- 82 scénarios HTTP réussis : accès, connexion/déconnexion, sessions, CSRF sur chaque page, XSS, recherche SQL, profil et suppression.
- 9 vérifications des constructeurs/getters, règles admin et hachage réussies.
- E-mail déjà utilisé : refus HTTP 409 avec message JSON, sans fuite de détails SQL.
- Connexion AJAX avec token CSRF en en-tête : vérifiée.
- 12 vérifications supplémentaires de l’inscription et du blocage d’Eya réussies : validation, unicité, hachage, rôle user imposé, statut actif, protection CSRF/roles, blocage de soi-même refusé et blocage/déblocage effectifs.
- Nouvelle régression des 24 pages : accès, CSRF et XSS réussis.
- 23 interfaces et 50 ressources locales vérifiées après la mise à jour d’Eya : aucun fichier manquant.
- Syntaxes PHP et JavaScript vérifiées. Toutes les méthodes originales des trois classes communes et tous les chemins des six branches sont contrôlés.
- Tests effectués sur une base et des sessions temporaires séparées. Aucun jeu d’essai n’est ajouté au projet.

Pour l’exécution, utiliser la base yalla_quest et le schéma yalla_quest_.sql fourni, puis configurer config.php. L’absence de comptes en base produit une liste vide, pas des utilisateurs fictifs.
