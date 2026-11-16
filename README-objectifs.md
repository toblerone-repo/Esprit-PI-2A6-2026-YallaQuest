# YallaQuest — Module Objectifs & Étapes de Progression (CRUD MVC)
**Projet Technologies Web 2A — ESPRIT (2026-2027)**  
*« Chaque habitude est une quête. »*

---

## 🎯 Présentation du Projet & Objectifs

Le module **Objectifs & Étapes** de **YallaQuest** est une application web modulaire développée selon le patron de conception **MVC (Modèle - Vue - Contrôleur)** en PHP 8 / MySQL, strictement conforme aux enseignements du cours de Technologies Web 2A (*Chapitre 4 PHP*, *Chapitre 5 POO*, *Chapitre 6 PHP PDO - CRUD*).

Il permet à chaque aventurier :
1. **De planifier des buts de santé et bien-être** (activités sportives, sommeil, nutrition, santé mentale, habitudes) sous forme de notes pour le futur avec une date/heure de début et de fin.
2. **De découper chaque but en étapes obligatoires (`EtapeObjectif`)** : chaque objectif contient un ensemble d'étapes jalons nécessaires à son accomplissement.
3. **De suivre sa progression en temps réel** : chaque étape validée met à jour le ratio d'avancement et la barre de progression en %.
4. **D'automatiser l'accomplissement** : dès que toutes les étapes d'un objectif sont validées, l'objectif se termine automatiquement (`statut = 'atteint'`), attribue des points d'expérience (**+50 XP**), fait progresser le niveau de l'aventurier et affiche une notification de félicitation.
5. **D'administrer les objectifs (Back-Office)** : supervision globale, filtrage dynamique par état, et création/assignation directe d'objectifs avec étapes.

---

## 📐 Diagramme de Classes UML & Modèle de Données

Le module respecte fidèlement le diagramme de classes et la relation d'agrégation/contenance :

```
┌─────────────────────────────────┐           ┌─────────────────────────────────┐
│            objectif             │           │          EtapeObjectif          │
├─────────────────────────────────┤           ├─────────────────────────────────┤
│ *IdO : int                      │ 0..n   1..1│ *IdEO : int                     │
│ *titre : string                 │───────────│ *titre : string                 │
│ *note : string                  │  contenir │ *est_validee : bool             │
│ *date_debut : datetime          │           │ *date_validation : datetime/null│
│ *date_fin : datetime            │           │ *id_objectif : int (FK -> IdO)  │
│ *statut : string                │           └─────────────────────────────────┘
└─────────────────────────────────┘
```

### Script SQL pour phpMyAdmin (`yallaquest.sql`)

```sql
CREATE DATABASE IF NOT EXISTS `yallaquest` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `yallaquest`;

-- Table des objectifs
CREATE TABLE IF NOT EXISTS `objectif` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titre` VARCHAR(150) NOT NULL,
  `note` TEXT NULL,
  `description` TEXT NULL,
  `categorie` VARCHAR(50) DEFAULT 'Activité physique',
  `date_debut` DATETIME NULL,
  `date_fin` DATETIME NULL,
  `date_limite` DATE NULL,
  `statut` ENUM('a_venir', 'en_cours', 'atteint') DEFAULT 'en_cours',
  `xp_recompense` INT DEFAULT 50,
  `date_creation` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des étapes d'un objectif (Relation 1..1 vers 0..n)
CREATE TABLE IF NOT EXISTS `etape_objectif` (
  `IdEO` INT AUTO_INCREMENT PRIMARY KEY,
  `titre` VARCHAR(255) NOT NULL,
  `est_validee` TINYINT(1) DEFAULT 0,
  `date_validation` DATETIME NULL,
  `id_objectif` INT NOT NULL,
  INDEX (`id_objectif`),
  CONSTRAINT `fk_etape_objectif` FOREIGN KEY (`id_objectif`) REFERENCES `objectif`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 🏛️ Architecture Logicielle MVC (Conforme au Cours ESPRIT)

Le code a été simplifié pour être immédiatement compréhensible et défendable en soutenance académique, en évitant toute bibliothèque externe superflue :

```text
C:\xampp\htdocs\objectif/
├── index.php                         # Point d'entrée racine (redirection Front-Office)
├── config.php                        # Connexion PDO Singleton standard ESPRIT
├── logo.jpeg                         # Logo officiel Félin Géométrique
├── README.md                         # Documentation complète du projet
│
├── Front-Office/                     # Espace Aventurier (Joueur)
│   ├── index.php                     # Redirection vers la vue
│   ├── objectifs.php                 # Fichier d'accès direct
│   ├── Model/
│   │   ├── Objectif.php              # Entité Objectif (attributs privés, constructeur, getters/setters)
│   │   ├── EtapeObjectif.php         # Entité EtapeObjectif (attributs privés, constructeur, getters/setters)
│   │   └── Utilisateur.php           # Entité Utilisateur
│   ├── Controller/
│   │   ├── ObjectifC.php             # Contrôleur CRUD principal (requêtes préparées PDO)
│   │   └── ObjectifController.php    # Alias de compatibilité
│   ├── View/
│   │   └── objectifs.php             # Vue Front-Office complète (cards, formulaire, progression XP)
│   ├── css/
│   │   └── style.css                 # Feuilles de style Front-Office
│   └── js/
│       └── objectifs.js              # Fonctions JS simples de validation et interaction
│
├── Back-Office/                      # Espace Administration (Supervision)
│   ├── index.php                     # Redirection vers la vue d'administration
│   ├── objectifs.php                 # Fichier d'accès direct
│   ├── Model/ (Objectif.php, EtapeObjectif.php)
│   ├── Controller/ (ObjectifC.php, AdminController.php)
│   ├── View/
│   │   └── objectifs.php             # Table de supervision, filtres et attribution de but
│   └── css/
│       └── admin.css                 # Feuilles de style Back-Office
│
└── shared/                           # Ressources partagées (UI & Design)
    ├── css/yq.css                    # Charte graphique (noir nuit, lilas, ivoire, sauge, or)
    ├── fonts/                        # Polices web intégrées (Playfair Display & Inter)
    └── img/piliers/                  # Visuels d'ambiance des 5 piliers bien-être
```

---

## 💻 Détail des Composants Techniques

### 1. La Connexion PDO Singleton (`config.php`)
Conforme à la diapositive 10 du **Chapitre 6 - PHP PDO - CRUD** :
```php
class config {
    private static $pdo = null;

    public static function getConnexion() {
        if (!isset(self::$pdo)) {
            self::$pdo = new PDO("mysql:host=localhost;dbname=yallaquest;charset=utf8mb4", "root", "", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        }
        return self::$pdo;
    }
}
```

### 2. Les Entités Modèles (`Model/`)
- **`Objectif.php`** : Encapsule les données d'un objectif (`$IdO`, `$titre`, `$note`, `$date_debut`, `$date_fin`, `$statut`, `$categorie`, `$xpRecompense`).
- **`EtapeObjectif.php`** : Encapsule une étape avec `$IdEO`, `$titre`, `$est_validee`, `$date_validation`, et la clé étrangère `$id_objectif`.

### 3. Le Contrôleur CRUD (`Controller/ObjectifC.php`)
Structure identique à la classe `PersonneC` du cours :
- `afficherObjectifs()` : retourne la liste de tous les objectifs classés par date.
- `recupererObjectif($id)` : sélectionne un objectif par son identifiant unique.
- `ajouterObjectif(Objectif $objectif)` : insertion sécurisée par requête préparée `INSERT INTO objectif (...)`.
- `modifierObjectif(Objectif $objectif, $id)` : mise à jour via `UPDATE objectif SET ...`.
- `supprimerObjectif($id)` : supprime l'objectif ainsi que toutes ses étapes associées.
- `recupererEtapes($idObjectif)` : renvoie les étapes d'un objectif.
- `ajouterEtape(EtapeObjectif $etape)` : ajoute une nouvelle étape.
- `validerEtape($idEO, $estValidee)` : coche ou décoche une étape. **Règle d'achèvement automatique** : si toutes les étapes d'un objectif sont validées, le statut de l'objectif passe automatiquement à `'atteint'`.
- `basculerStatut($id)` : permet d'accomplir ou reprendre un objectif manuellement.

### 4. Les Vues PHP / HTML (`View/`)
- **Génération serveur propre** : Boucle PHP `<?php foreach ($listeObjectifs as $obj): ?>` générant l'affichage HTML sans script opaque.
- **Formulaire standard** : Traitement en méthode `POST` avec redirection `header('Location: ...')`.
- **Validation JavaScript claire** (`js/objectifs.js`) : Vérification des champs requis, cohérence chronologique (`date_fin > date_debut`), ajout dynamique de champs d'étapes dans la modale.

---

## 🎨 Charte Graphique YallaQuest

L'interface respecte scrupuleusement la charte graphique de YallaQuest :
- **Noir Nuit** (`#15131F`) : Fond sombre immersif.
- **Lilas** (`#EBC1F2`) : Éléments d'accent, badges d'XP, boutons principaux.
- **Ivoire** (`#F6F4F8`) : Cartes d'objectifs à fort contraste et lisibilité.
- **Or Doux** (`#D7A64B`) : Totems et distinctions.
- **Sauge** (`#7BAE8C`) : Indicateurs de réussite et statuts accomplis.
- **Typographies** : *Playfair Display* (titres nobles) et *Inter* (corps de texte moderne).

---

## 🚀 Guide d'Exécution & Démarrage Rapide

1. Ouvrez le panneau de contrôle **XAMPP** et démarrez les modules **Apache** et **MySQL**.
2. Assurez-vous que la base de données `yallaquest` existe dans phpMyAdmin (le contrôleur injecte automatiquement les données initiales de démonstration si la table est vide).
3. Ouvrez votre navigateur web et accédez aux adresses locales :

- **Portail & Espace Aventurier (Front-Office) :**  
  👉 [http://localhost/objectif/](http://localhost/objectif/)  
  *(ou [http://localhost/objectif/Front-Office/View/objectifs.php](http://localhost/objectif/Front-Office/View/objectifs.php))*

- **Panneau d'Administration (Back-Office) :**  
  👉 [http://localhost/objectif/Back-Office/View/objectifs.php](http://localhost/objectif/Back-Office/View/objectifs.php)

*(Un bouton direct permet de basculer en 1 clic entre le Front-Office et le Back-Office sur chaque écran).*

---

## 🧪 Scénarios de Test pour la Soutenance

| Étape | Action à réaliser | Résultat attendu |
| :---: | :--- | :--- |
| **1** | Cliquer sur **« Nouveau but »** | La boîte de dialogue s'ouvre avec le formulaire. |
| **2** | Saisir un titre, des dates et ajouter 2 étapes via le champ `+ Ajouter` | Les étapes s'affichent sous forme de liste dynamique. |
| **3** | Cliquer sur **« Enregistrer l'objectif »** | L'objectif est inséré en base de données et la carte s'affiche instantanément. |
| **4** | Cocher la 1ère étape sur la carte | La barre de progression passe à 50% et la date de validation s'affiche. |
| **5** | Cocher la 2ème étape (dernière) | **L'objectif se termine automatiquement** : le titre est barré, le statut devient « Accompli », +50 XP sont crédités et la jauge de niveau augmente. |
| **6** | Cliquer sur le bouton **« Administration »** | Redirection vers le Back-Office : l'objectif et ses étapes complétées sont visibles dans le tableau de supervision. |
| **7** | Cliquer sur **« Supprimer »** | L'objectif et ses étapes sont supprimés en cascade dans la base de données. |

---

## 👥 Informations Académiques

- **Module :** Projet Technologies Web (PW)
- **Niveau :** 2ème Année Ingénieur (2A)
- **Établissement :** ESPRIT (École Supérieure Privée d'Ingénierie et de Technologies)
- **Année Universitaire :** 2026 - 2027
