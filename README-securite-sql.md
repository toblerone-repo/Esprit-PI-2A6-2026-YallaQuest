# Sécurité des requêtes SQL

## Modifications

- `config.php` sélectionne la base `yalla_quest`, définie dans le schéma SQL du projet.
- La connexion utilise `utf8mb4` et désactive `PDO::ATTR_EMULATE_PREPARES`.
- Les requêtes de `Controllers/utilisateurC.php` utilisent des paramètres typés.
- Les requêtes utilisent la clé primaire `id_utilisateur`.
- La connexion renvoie toujours une clé `id`, via l'alias SQL `id_utilisateur AS id`.
- La mise à jour cible l'identifiant passé à `updateUtilisateur()`.
- Les détails des erreurs SQL sont consignés côté serveur ; le navigateur reçoit un message générique et un statut HTTP 500.

Les requêtes présentes dans le code PHP concernent la connexion et la mise à jour
d'un utilisateur. Les modules qui contiennent seulement des données d'exemple
n'exécutent pas encore de SQL.

## Vérifications effectuées

Les protections SQL, la connexion et le changement de mot de passe ont été
vérifiés sur des tables temporaires, sans modifier les données du projet.
Le fichier de test a été retiré ; il n'était pas utilisé par l'application.

## Pour les prochaines requêtes

Passer les valeurs reçues dans des paramètres PDO. Les noms de colonnes
et les directions de tri dynamiques doivent venir d'une liste autorisée.
Le contrôle du propriétaire d'une ressource reste une vérification d'accès
distincte de la protection contre les injections SQL.

## Hachage avant enregistrement

`updateUtilisateur(Utilisateur $utilisateur, int $id, string $ancienMotDePasse)`
reçoit le nouveau mot de passe en clair dans le modèle. Le contrôleur lit
le hachage actuel du compte, vérifie l'ancien mot de passe, puis calcule
`password_hash($utilisateur->getMp(), PASSWORD_DEFAULT)` avant l'UPDATE.

La vue `modifier-profil.php` transmet le nouveau mot de passe sans le hacher
et utilise l'identifiant du compte connecté dans `$_SESSION['utilisateur']['id']`.
Elle n'utilise plus d'identifiant ni de mot de passe de démonstration.
La méthode retourne un booléen pour indiquer si la modification a réussi.
La condition sur le hachage précédent empêche d'écraser un changement
concurrent de mot de passe.


## Inscription à intégrer

Le futur traitement d'inscription devra hacher le mot de passe en clair
directement dans son contrôleur avant l'INSERT :

```php
$passwordHash = password_hash($motDePasse, PASSWORD_DEFAULT);
```

Puis transmettre ce hachage au paramètre PDO de `mot_de_passe_hash`.

## XSS, CSRF et en-têtes HTTP

Les sorties HTML sont échappées avec htmlspecialchars, ENT_QUOTES,
ENT_SUBSTITUTE et UTF-8. Les identifiants et compteurs sont numériques.
Les données PHP intégrées à JavaScript utilisent JSON_HEX_*.
Les libellés interpolés dans du HTML par les scripts historiques des quêtes
sont aussi échappés côté PHP.

AuthController contient getCsrfToken() et verifyCsrfToken(). Le token est
généré avec random_bytes(32), conservé en session et comparé avec hash_equals().
Le démarrage simple de session est nécessaire au stockage du token.

Les formulaires POST contiennent csrf_token. Les points d'entrée existants
vérifient le token avant tout traitement et refusent les valeurs absentes,
modifiées ou provenant d'une autre session (HTTP 403).
Les modifications par GET sont refusées dans les pages d'objectifs.

Les quatre en-têtes de la partie 7.5 sont directement dans index.php.
Les réglages des cookies et le délai d'inactivité sont laissés à leur lot.
Les scripts JavaScript ont retrouvé leurs versions précédentes. Le raccordement
de leurs requêtes AJAX aux formulaires POST reste à leur lot.
Aucun traitement d'inscription ni nouveau fichier MVC n'est ajouté.
