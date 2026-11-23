# Inscription + Bloquer / débloquer un compte

Deux tâches : **Inscription** (workshop, Partie 4) et **Bloquer / débloquer un compte** (« ajouter une colonne statut et refuser la connexion si le compte est bloqué »).
Le code ajouté est repéré par `[INSCRIPTION]` ou `[BLOCAGE]` dans les commentaires.
La partie Sécurité (CSRF, en-têtes HTTP, configuration de la session) n'est pas traitée ici : un autre membre s'en charge.

## 1. Base de données

La colonne `statut ENUM('actif','bloque') NOT NULL DEFAULT 'actif'` existe déjà : rien à ajouter pour le blocage.
Le workshop demande un nom d'utilisateur unique ; le formulaire avait déjà un champ « Pseudo », mais aucune colonne pour l'enregistrer :

```sql
ALTER TABLE utilisateur
  ADD COLUMN pseudo VARCHAR(50) NULL AFTER prenom,
  ADD CONSTRAINT uq_utilisateur_pseudo UNIQUE (pseudo);
```

`NULL` est voulu : les comptes déjà créés et l'ajout d'utilisateur par l'admin (sans pseudo) continuent de fonctionner (MySQL accepte plusieurs `NULL` dans une colonne `UNIQUE`).

## 2. Inscription

`Views/front-office/pages/join_now.php` → `UtilisateurC::register($_POST)` → `login.php?registered=1`.

- Contrôleur `Controllers/utilisateurC.php` : `isAuthenticated()`, `existsByEmailOrUsername()` (privée), `insertUser()` (privée), `register()`.
- Validation PHP : tous les champs obligatoires, pseudo `^[a-zA-Z0-9_\-]{3,50}$`, e-mail `FILTER_VALIDATE_EMAIL`, mot de passe d'au moins 8 caractères, confirmation identique, e-mail ou pseudo déjà pris.
- Mot de passe : `password_hash($mdp, PASSWORD_DEFAULT)` (le hash commence par `$2y$`).
- Le rôle est forcé à `'user'` et le statut à `'actif'` dans la requête `INSERT` : un `role=admin` envoyé dans le formulaire est ignoré.
- `Views/js/validation.js` fait le même contrôle côté navigateur, puis laisse le formulaire partir vers PHP.

## 3. Bloquer / débloquer

- Contrôleur : `bloquerUtilisateur($id)`, `debloquerUtilisateur($id)` → `changerStatut()` (privée). Ces méthodes renvoient un tableau d'erreurs (vide = réussite). Elles vérifient que l'utilisateur connecté est admin (`hasRole('admin')`), que le compte existe et qu'un admin ne se bloque pas lui-même, puis font l'`UPDATE` avec une requête préparée.
- Action : `Views/back-office/pages/changerStatut.php` (POST `id` + `action` = `bloquer` | `debloquer`). Elle renvoie une erreur 403 si l'utilisateur n'est pas admin, puis revient à `listuser.php` avec un message dans `$_SESSION['message_statut']`.
- Connexion : `login()` lit maintenant `role` et `statut` (et `id_utilisateur` au lieu de `id`, qui n'existe pas). `login.php` refuse un compte bloqué (« Ton compte est bloqué… ») sans créer de session. Ce contrôle a lieu après la vérification du mot de passe, pour ne pas révéler quels e-mails existent.

## 4. À coller dans `listuser.php` (page de Darine)

**a)** En haut, juste après `$utilisateurC = new UtilisateurC();` :

```php
// [BLOCAGE] Message laissé par changerStatut.php (affiché une seule fois)
$messageStatut = $_SESSION['message_statut'] ?? null;
unset($_SESSION['message_statut']);
```

**b)** Juste au-dessus du tableau des aventuriers :

```php
<?php if ($messageStatut): ?>
    <!-- [BLOCAGE] Résultat du blocage / déblocage -->
    <p class="<?= $messageStatut['type'] === 'success' ? 'sub' : 'search-error' ?>" role="status"><?= htmlspecialchars($messageStatut['texte']) ?></p>
<?php endif; ?>
```

**c)** Dans la cellule Actions, bloc `<?php else: ?>` (lignes des autres comptes) :

```php
<!-- [BLOCAGE] Bloquer / débloquer -->
<form method="post" action="changerStatut.php">
    <input type="hidden" name="id" value="<?= (int) $u->getId() ?>">
    <?php if ($u->getStatut() === 'bloque'): ?>
        <button type="submit" name="action" value="debloquer" class="btn sm">Débloquer</button>
    <?php else: ?>
        <button type="submit" name="action" value="bloquer" class="btn danger sm">Bloquer</button>
    <?php endif; ?>
</form>
```

La colonne « Statut » (Actif / Bloqué) est déjà affichée par `listuser.php`.

## 5. Notes d'intégration

- **Modèle `Utilisateur`** : seuls l'attribut `$pseudo` et `getPseudo()` / `setPseudo()` ont été ajoutés. `insertUser()` utilise `getNom()`, `getPrenom()`, `getEmail()`, `getMp()` et `getPseudo()`. Si le modèle de Darine (qui utilise `getPassword()`) est retenu, remplacer `getMp()` par `getPassword()` dans `insertUser()`, ajouter `pseudo` au modèle, et créer l'objet avec `new Utilisateur()` puis des setters dans `register()`.
- **Session** : `login.php` remplit `$_SESSION['utilisateur']` (avec `id_utilisateur`, `role` et `statut`). `isAuthenticated()` et `hasRole()` lisent cette clé. Si l'équipe passe à `$_SESSION['user_id']` / `$_SESSION['role']` (workshop), il suffit de modifier ces deux méthodes et le test `id_utilisateur` dans `changerStatut()`.
- **`config.php`** : base `yalla_quest` + `charset=utf8mb4` (même correction que dans la version de Darine).
- **Partie Sécurité** : la personne qui ajoute le jeton CSRF devra aussi l'ajouter au formulaire d'inscription et au formulaire Bloquer / débloquer de `listuser.php`.
