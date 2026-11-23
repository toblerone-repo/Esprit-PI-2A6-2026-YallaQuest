# YallaQuest

*Chaque habitude est une quête.*

**Projet Technologies Web 2A · ESPRIT · Année universitaire 2026–2027**

YallaQuest est une application web de suivi des habitudes. Elle propose de transformer une habitude en quête, de la découper en activités et de suivre sa progression grâce à l’XP, aux niveaux et aux totems. Le front-office rassemble les parcours des membres. Le back-office rassemble la gestion des comptes et les interfaces d’administration des modules.

## Fonctionnalités et répartition du travail

| Module | Responsable | Fonctionnalités et contributions |
| --- | --- | --- |
| 1 · Quêtes et programmation | Dorra | Catalogue et programmation des quêtes, interfaces d’ajout et de prolongation. Profil utilisateur, choix d’un avatar et modification des informations avec changement de mot de passe facultatif. Conception et création de la base de données et rédaction du README principal. |
| 2 · Journal et activités | Eya | Journal de bord, suivi des activités d’une quête et interfaces de gestion des activités et de leurs types. Inscription avec pseudo et blocage ou déblocage des comptes. |
| 3 · Compétences et totems | Darine | Affichage de l’XP, de la progression par compétence et des totems. Interfaces de gestion des compétences et des totems, visuels et gabarits communs. Modèle utilisateur, conversion des lignes SQL en objets, liste et comptage des comptes, recherche par ID et protection du compte administrateur. |
| 4 · Guildes | Amine | Interfaces des guildes, des propositions et des votes, effets visuels et indicateur de chargement. Routeur, espace utilisateur, connexion, déconnexion, gestion des sessions, compteur d’inactivité, validation JavaScript partagée et suppression des comptes. |
| 5 · Objectifs et étapes | Mohamed | Interfaces de planification des objectifs, de suivi des étapes et de supervision côté administration. Gabarit d’inscription, ajout de comptes par un administrateur et changement de rôle, et authentification. |
| 6 · Communauté | Houssem | Interfaces de publication de conseils et de signalement, avec vues de modération. Squelette MVC, recherche de comptes par nom, e-mail, rôle ou ID, requêtes préparées, hachage et vérification des mots de passe, protection CSRF et XSS, et en-têtes HTTP. |

### Parcours à découvrir

| Module | Front-office | Back-office |
| --- | --- | --- |
| Quêtes et programmation | [Quêtes](Views/front-office/pages/quetes-programmation.php) présente le catalogue, les difficultés, l’XP et la programmation des quêtes. [Mon profil](Views/front-office/pages/page-profil.php) affiche les informations du compte et donne accès à la modification du profil. | [Liste des quêtes](Views/back-office/pages/quetes-listes.php) |
| Journal et activités | [Mon journal](Views/front-office/pages/journal.php) présente les activités d’une quête, leur quantité, leur unité et leur état d’avancement. | [Activités](Views/back-office/pages/activites.php) et [types d’activité](Views/back-office/pages/types-activite.php) |
| Compétences et totems | [Mes compétences](Views/front-office/pages/competences.php) illustre la répartition de l’XP entre la force, l’endurance, la mobilité, le focus et la nutrition, ainsi que les seuils de déblocage des totems. | [Compétences](Views/back-office/pages/competences.php) et [totems](Views/back-office/pages/totems.php) |
| Guildes | [Guildes](Views/front-office/pages/guildes.php) présente les guildes et les propositions de quêtes. Les liens `#guildes`, `#g1` à `#g5` et `#quetes` ouvrent les différentes vues de la démonstration. | [Guildes et propositions](Views/back-office/pages/guilde_admin.php) |
| Objectifs et étapes | [Mes objectifs](Views/front-office/pages/front-objectifs.php) présente les interfaces de création d’un objectif, de découpage en étapes et de suivi de la progression. | [Supervision et attribution des objectifs](Views/back-office/pages/back-objectifs.php) |
| Communauté | [Communauté](Views/front-office/pages/communaute.php) présente la consultation et le signalement de publications. | [Publications](Views/back-office/pages/publications.php) et [signalements](Views/back-office/pages/signalements.php) |

L’inscription, la connexion, la modification du profil et les actions d’administration sur les comptes utilisent MySQL. Plusieurs écrans de modules illustrent encore leurs parcours avec des données d’exemple ou des formulaires sans enregistrement en base. Les votes des guildes et le traitement des signalements font partie de ces démonstrations.

Par exemple, le journal décompose la quête « Préparer une salade saine » en activités telles que laver une laitue ou râper 100 g de carottes. Dans l’écran des compétences, Le Bélier illustre le totem de force avec un seuil de 300 XP.

## Technologies et organisation

Le projet utilise PHP 8 avec PDO, MySQL ou MariaDB, HTML, CSS et JavaScript. Il suit une organisation MVC.

| Emplacement | Rôle |
| --- | --- |
| [Models/](Models/) | Modèles PHP, dont celui de l’utilisateur. |
| [Controllers/](Controllers/) | Authentification et gestion des comptes. |
| [Views/front-office/](Views/front-office/) | Interfaces de l’espace membre. |
| [Views/back-office/](Views/back-office/) | Interfaces d’administration. |
| [index.php](index.php) et [session.php](session.php) | Routage de l’application et gestion des sessions. |
| [config.php](config.php) et [yalla_quest.sql](yalla_quest.sql) | Connexion PDO et export SQL complet de la structure de la base. |

Le modèle [`Utilisateur`](Models/utilisateur.php) convertit les lignes SQL en objets avec `fromArray()` pour les listes d’administration. Le profil utilise un tableau associatif obtenu avec `getProfilUtilisateur()`, qui récupère aussi le chemin de l’avatar par jointure avec `images`. Les vues partagent leurs en-têtes, pieds de page et menus dans les dossiers `includes/` et `partials/`. La variable `$currentPage` sélectionne les styles et le lien actif.

## Installation et configuration

1. Installez XAMPP avec PHP 8 et l’extension `pdo_mysql`. Démarrez **Apache** et **MySQL** dans le panneau XAMPP.
2. Clonez ou copiez ce dépôt dans `C:\xampp\htdocs\Esprit-PI-2A6-2026-YallaQuest`. Avec WampServer, utilisez le dossier `www` de votre installation.
3. Dans [phpMyAdmin](http://localhost/phpmyadmin/), créez une base vide nommée `yalla_quest` et sélectionnez-la. Dans l’onglet **Importation**, importez [`yalla_quest.sql`](yalla_quest.sql), qui contient le schéma complet.
4. Dans [`config.php`](config.php), adaptez `$servername`, `$username` et `$password` à votre installation locale. Gardez `$dbname` à `yalla_quest` pour utiliser la base créée à l’étape précédente.

## Exécution

- **Accueil.** Ouvrez [YallaQuest](http://localhost/Esprit-PI-2A6-2026-YallaQuest/index.php). Si votre dossier porte un autre nom, utilisez ce nom dans l’adresse.
- **Espace membre.** [Créez un compte](http://localhost/Esprit-PI-2A6-2026-YallaQuest/index.php?action=register), puis [connectez-vous](http://localhost/Esprit-PI-2A6-2026-YallaQuest/index.php?action=login). Dans le menu, ouvrez **Profils**, puis **Modifier mes informations** pour vérifier le chargement du profil et l’enregistrement des modifications.
- **Espace d’administration.** Sur une installation locale neuve, créez un compte depuis le formulaire d’inscription. Dans phpMyAdmin, ouvrez la table `utilisateur`, repérez ce compte par son adresse e-mail et passez son champ `role` de `user` à `admin`. Déconnectez-vous, puis reconnectez-vous avec ce compte pour accéder au [back-office](http://localhost/Esprit-PI-2A6-2026-YallaQuest/index.php?action=admin).

## Comptes, sessions et sécurité

- L’inscription publique crée un compte `user` et `actif`. L’e-mail et le pseudo sont uniques. Les comptes bloqués sont refusés à la connexion.
- La liste des aventuriers permet de chercher ou filtrer les comptes. Le back-office propose aussi l’ajout, la suppression, le changement de rôle et le blocage ou déblocage. Des contrôles empêchent notamment un administrateur de supprimer son propre compte, de le bloquer ou de se retirer le rôle `admin`.
- Le changement de mot de passe dans le profil demande l’ancien mot de passe, un nouveau mot de passe et sa confirmation. Lorsque les trois champs sont vides, le hachage actuel est conservé.
- La connexion renouvelle l’identifiant de session et stocke l’identifiant du compte dans `$_SESSION['user_id']`. Le délai d’inactivité est réglé en secondes avec `$sessionLifetime` dans [`session.php`](session.php). Le compteur du front-office utilise la même durée et le bouton **Rester connecté** relance le délai par une requête au serveur.
- Les contrôleurs des comptes utilisent des requêtes PDO préparées. Les mots de passe sont hachés avec `password_hash()` et contrôlés avec `password_verify()`. Les formulaires sensibles, dont la déconnexion, envoient un jeton CSRF. Les vues échappent les données affichées et le routeur applique les en-têtes de sécurité HTTP.

## Effets visuels des guildes

L’[indicateur de chargement](Views/front-office/includes/spinner.php) disparaît après le chargement de la page. La vue des guildes utilise [guilde.css](Views/css/guilde.css) et les [scripts d’effets](Views/js/effects/) pour les transitions, les animations de défilement et l’emblème. Les réglages sont regroupés dans `TUNE` dans [`main.js`](Views/js/effects/main.js). Les bibliothèques GSAP, Lenis, SplitType et Three.js sont fournies dans [`Views/lib/`](Views/lib/). Le mode de mouvement réduit désactive les effets les plus animés.

## Vérifier l’installation

1. Inscrivez un compte de test, connectez-vous et ouvrez **Profils**. Vérifiez que les informations du compte s’affichent. Le formulaire propose des avatars lorsque la table `images` contient des entrées.
2. Modifiez le nom en laissant les trois champs de mot de passe vides. Le profil doit afficher les changements après l’enregistrement. Vérifiez qu’un ancien mot de passe incorrect empêche le changement de mot de passe.
3. Connectez-vous avec un compte `admin` et vérifiez la recherche des aventuriers. Utilisez un compte de test distinct pour vérifier le blocage, le refus de connexion et le déblocage.
4. Laissez une session membre inactive pendant le délai configuré pour vérifier le compteur et le retour à la connexion.
