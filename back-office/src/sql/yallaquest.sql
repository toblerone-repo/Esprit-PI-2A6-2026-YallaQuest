-- YallaQuest — schéma unique de la base `yallaquest` (tous les modules).
-- Import : phpMyAdmin > Importer, ou `mysql -u root < back-office/src/sql/yallaquest.sql`.
--
-- Sources :
--   * publications / signalements : colonnes lues et écrites par back-office/app/Models/Publication.php
--     et Signalement.php (liste des colonnes dans README-YallaQuest.md). Aucun dump n'existait :
--     les types ci-dessous sont déduits du code.
--   * objectif / etape_objectif : SQL du README de la branche `objectifs` (commit 719f558).
-- Les modules Quêtes, Journal, Compétences/Totems et Guildes n'utilisent pas encore la base
-- (données en dur dans les vues ou dans les modèles JS) : aucune table n'est créée pour eux.

CREATE DATABASE IF NOT EXISTS `yallaquest` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `yallaquest`;

-- ---------------------------------------------------------------- Communauté
CREATE TABLE IF NOT EXISTS `publications` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `author`     VARCHAR(100) NOT NULL,
  `initials`   VARCHAR(4)   NOT NULL DEFAULT '',
  `category`   VARCHAR(30)  NOT NULL,                 -- nutrition | mouvement | serenite
  `type`       VARCHAR(50)  NOT NULL,                 -- Petite victoire | Conseil | Discussion
  `title`      VARCHAR(200) NOT NULL,
  `content`    TEXT         NOT NULL,
  `more`       TEXT         NOT NULL,
  `image`      VARCHAR(30)  NULL DEFAULT NULL,        -- nom d'une image de front-office/img/publications/
  `status`     VARCHAR(20)  NOT NULL DEFAULT 'draft', -- published | draft | hidden
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `signalements` (
  `id`                  INT AUTO_INCREMENT PRIMARY KEY,
  `publication_id`      INT NULL,                     -- NULL quand la publication a été supprimée
  `publication_title`   VARCHAR(200) NOT NULL,        -- copie du récit conservée pour l'historique
  `publication_author`  VARCHAR(100) NOT NULL,
  `publication_content` TEXT         NOT NULL,
  `reason`              VARCHAR(30)  NOT NULL,        -- spam | inapproprie | dangereux | autre
  `details`             TEXT         NOT NULL,
  `status`              VARCHAR(20)  NOT NULL DEFAULT 'pending', -- pending | reviewing | resolved | dismissed
  `moderator_note`      TEXT         NOT NULL,
  `decision`            VARCHAR(20)  NULL DEFAULT NULL, -- keep | hide | delete
  `created_at`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `handled_at`          DATETIME     NULL DEFAULT NULL,
  INDEX (`publication_id`),
  CONSTRAINT `fk_signalement_publication` FOREIGN KEY (`publication_id`) REFERENCES `publications`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------- Objectifs
CREATE TABLE IF NOT EXISTS `objectif` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `titre`         VARCHAR(150) NOT NULL,
  `note`          TEXT NULL,
  `description`   TEXT NULL,
  `categorie`     VARCHAR(50) DEFAULT 'Activité physique',
  `date_debut`    DATETIME NULL,
  `date_fin`      DATETIME NULL,
  `date_limite`   DATE NULL,
  `statut`        ENUM('a_venir', 'en_cours', 'atteint') DEFAULT 'en_cours',
  `xp_recompense` INT DEFAULT 50,
  `date_creation` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `etape_objectif` (
  `IdEO`            INT AUTO_INCREMENT PRIMARY KEY,
  `titre`           VARCHAR(255) NOT NULL,
  `est_validee`     TINYINT(1) DEFAULT 0,
  `date_validation` DATETIME NULL,
  `id_objectif`     INT NOT NULL,
  INDEX (`id_objectif`),
  CONSTRAINT `fk_etape_objectif` FOREIGN KEY (`id_objectif`) REFERENCES `objectif`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
