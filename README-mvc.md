# Squelette MVC du workshop

Cette base correspond à la partie 3 du workshop Sessions et Sécurité PHP,
adaptée aux dossiers existants `Controllers/`, `Models/` et `Views/`.

## Fichiers

- `config.php` : connexion PDO existante, partagée via `config::getConnexion()`.
- `Controllers/AuthController.php` : contrôleur avec sa propriété PDO et son constructeur.
- `Models/UserModel.php` : modèle existant `Utilisateur`, avec attributs, getters et setters, sans SQL.
- `index.php` : démarrage de session, chargement du contrôleur et squelette du routage.
- `Views/layout.php` : enveloppe HTML commune du front-office.
- `Views/layout_admin.php` : enveloppe HTML commune du back-office.

Les layouts reprennent les feuilles de style du projet et attendent
`$title` et `$content`. Les composants de navigation existants pourront être
raccordés lors de l'intégration des vues.

## Vérification du point d'entrée

Avec Apache et MySQL démarrés, ouvrir :

http://localhost/projet_v2/index.php?action=test

Le résultat attendu est une réponse HTTP 404 avec `404 - Page not found`,
comme dans la partie 3.3. Aucune action applicative n'est encore définie.

Le constructeur utilise la connexion de `config.php`, actuellement réglée
sur la base `yalla_quest`. Cette base doit être accessible pour que le
point d'entrée atteigne le routage. Le squelette ne crée aucune table.

## Utiliser un layout dans une vue

Exemple pour une future vue dans `Views/front-office/pages/`, chargée
depuis `index.php` :

```php
<?php ob_start(); ?>
<main>
    <h1>Ma page</h1>
</main>
<?php
$content = ob_get_clean();
$title = 'Ma page | YallaQuest';
require __DIR__ . '/../../layout.php';
```

Pour le back-office, utiliser `../../layout_admin.php`.

Les layouts contiennent déjà les balises `html`, `head` et `body`.
La vue qui les utilise fournit uniquement son contenu. Les chemins CSS des
layouts supposent un affichage depuis `index.php`, à la racine du projet.

## Intégration des prochains lots

Ajouter les méthodes d'authentification et de sécurité dans `AuthController`,
puis leurs actions dans le `switch` d'`index.php`. Les vues reçoivent les
variables préparées avant leur affichage.

L'authentification réutilisera la classe `Utilisateur` définie dans
`Models/UserModel.php` et les champs existants du projet.

Les pages et contrôleurs existants ne sont pas encore migrés vers ce
point d'entrée. Ce squelette n'ajoute ni inscription, ni connexion, ni
contrôle d'accès, ni protection CSRF, ni configuration de cookies,
ni expiration, ni en-têtes de sécurité.
