# YallaQuest — Module Objectifs & Étapes de Progression (CRUD MVC)
**Projet Technologies Web 2A — ESPRIT (2026-2027)**  
*« Chaque habitude est une quête. »*

---

## 🎯 Présentation du Projet & Fonctionnalités

Le module **Objectifs & Étapes** de **YallaQuest** est une application web modulaire développée selon le patron de conception **MVC (Modèle - Vue - Contrôleur)** en PHP 8 / MySQL, conforme aux exigences du cours de Technologies Web 2A (*Chapitre 4 PHP*, *Chapitre 5 POO*, *Chapitre 6 PHP PDO - CRUD*).

### Points clés :
1. **Planification des buts** : Titre, catégorie/pilier de santé, note pense-bête pour le futur, et période (date & heure de début / fin).
2. **Découpage en étapes obligatoires (`EtapeObjectif`)** : Chaque but contient 1 à n étapes jalons.
3. **Calcul de progression dynamique** : Indicateur de complétion en % et compteur d'étapes validées en temps réel.
4. **Auto-complétion et gain d'XP** : Dès que la dernière étape d'un objectif est validée, l'objectif passe au statut `'atteint'`, attribue **+50 XP** à l'aventurier et fait progresser son niveau.
5. **Administration (Back-Office)** : Supervision globale sous forme de tableau interactif, filtrage en direct par statut/recherche et assignation d'objectifs avec étapes.

---

## 📁 Architecture des Dossiers

Le projet est structuré selon une arborescence claire et optimisée :

```text
C:\xampp\htdocs\objectif/
├── README.md                                 # Documentation du projet
│
├── Controllers/                              # Contrôleurs MVC (dossier dédié)
│
├── Models/                                   # Modèles et entités métiers (dossier dédié)
│
└── Views/                                    # Vues et ressources
    ├── css/                                  # Feuilles de style optimisées
    │   ├── front-objectifs-styles.css        # Styles complets de l'espace Aventurier (Front-Office)
    │   └── back-objectifs-styles.css         # Styles complets de l'administration (Back-Office)
    │
    ├── img/                                  # Images et visuels
    │   ├── logo.jpeg
    │   └── piliers/                          # Visuels d'ambiance (endurance, focus, force, mobilite, nutrition)
    │
    ├── js/                                   # Scripts JavaScript modulaires
    │   ├── front-objectifs.js                # Fonctions Front-Office (modal, étapes dynamiques, toggle AJAX, filtres)
    │   └── back-objectifs.js                 # Fonctions Back-Office (filtres tableau en temps réel, étapes admin, validation)
    │
    ├── lib/                                  # Polices locales
    │   └── fonts/                            # Polices web (Playfair Display, Inter, Barlow Condensed)
    │
    ├── front-office/                         # Espace Aventurier
    │   ├── includes/
    │   │   └── scripts.php                   # Inclusions des scripts JavaScript Front-Office
    │   └── pages/
    │       └── front-objectifs.php           # Vue Front-Office (profil aventurier, cartes d'objectifs, modale d'ajout)
    │
    └── back-office/                          # Espace Administration
        ├── partials/
        │   └── scripts.php                   # Inclusions des scripts JavaScript Back-Office
        └── pages/
            └── back-objectifs.php            # Vue Back-Office (tableau de supervision CRUD et assignation)
```

---

## 🗄️ Schéma de la Base de Données

Le module s'appuie sur la base de données `yallaquest` et deux tables reliées par clé étrangère en cascade :

```sql
CREATE DATABASE IF NOT EXISTS `yallaquest` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `yallaquest`;

-- 1. Table des objectifs
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

-- 2. Table des étapes d'objectifs (composition 1 à n)
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

## 🚀 Guide d'Accès Local dans le Navigateur

Une fois **Apache** et **MySQL** démarrés dans le panneau de contrôle **XAMPP** :

- **Espace Aventurier (Front-Office) :**  
  👉 [http://localhost/objectif/Views/front-office/pages/front-objectifs.php](http://localhost/objectif/Views/front-office/pages/front-objectifs.php)

- **Espace Administration (Back-Office) :**  
  👉 [http://localhost/objectif/Views/back-office/pages/back-objectifs.php](http://localhost/objectif/Views/back-office/pages/back-objectifs.php)

---

## 👥 Informations Académiques

- **Module :** Projet Technologies Web (PW)
- **Niveau :** 2ème Année Ingénieur (2A)
- **Établissement :** ESPRIT (École Supérieure Privée d'Ingénierie et de Technologies)
- **Année Universitaire :** 2026 - 2027
