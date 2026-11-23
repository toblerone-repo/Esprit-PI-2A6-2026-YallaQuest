CREATE DATABASE yalla_quest
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE yalla_quest;

CREATE TABLE images (
    id_image VARCHAR(64) PRIMARY KEY,
    chemin VARCHAR(2048) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE guilde (
    id_guilde INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT NULL,
    date_creation DATE NOT NULL,
    id_utilisateur_createur INT UNSIGNED NOT NULL
) ENGINE=InnoDB;

CREATE TABLE utilisateur (
    id_utilisateur INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    mail VARCHAR(254) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    statut ENUM('actif', 'bloque') NOT NULL DEFAULT 'actif',
    mot_de_passe_hash VARCHAR(255) NOT NULL,
    total_xp INT UNSIGNED NOT NULL DEFAULT 0,
    niveau INT UNSIGNED NOT NULL DEFAULT 1,
    derniere_prolongation_at DATETIME DEFAULT NULL,
    id_totem INT UNSIGNED NULL,
    date_obtention_totem DATE NULL,
    img_profil VARCHAR(100) DEFAULT NULL,
    id_guilde INT UNSIGNED DEFAULT NULL,
    CONSTRAINT uq_utilisateur_mail UNIQUE (mail),
    CONSTRAINT fk_utilisateur_img_profil
        FOREIGN KEY (img_profil) REFERENCES images (id_image),
    CONSTRAINT fk_utilisateur_guilde
        FOREIGN KEY (id_guilde) REFERENCES guilde (id_guilde)
) ENGINE=InnoDB;

ALTER TABLE guilde
    ADD CONSTRAINT fk_guilde_createur
        FOREIGN KEY (id_utilisateur_createur) REFERENCES utilisateur (id_utilisateur);

CREATE TABLE competence (
    id_competence INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT NULL,
    visuel VARCHAR(255) NULL,
    pourcentage DECIMAL(5,2) NULL,
    id_utilisateur_maj INT UNSIGNED NOT NULL,
    CONSTRAINT fk_competence_utilisateur_maj
        FOREIGN KEY (id_utilisateur_maj) REFERENCES utilisateur (id_utilisateur)
) ENGINE=InnoDB;

CREATE TABLE totem (
    id_totem INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    visuel VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    seuil INT UNSIGNED NOT NULL,
    id_competence INT UNSIGNED NOT NULL,
    id_utilisateur_maj INT UNSIGNED NOT NULL,
    CONSTRAINT uq_totem_competence UNIQUE (id_competence),
    CONSTRAINT fk_totem_competence
        FOREIGN KEY (id_competence) REFERENCES competence (id_competence),
    CONSTRAINT fk_totem_utilisateur_maj
        FOREIGN KEY (id_utilisateur_maj) REFERENCES utilisateur (id_utilisateur)
) ENGINE=InnoDB;

CREATE TABLE exp_competence (
    id_utilisateur INT UNSIGNED NOT NULL,
    id_competence INT UNSIGNED NOT NULL,
    xp_c INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id_utilisateur, id_competence),
    CONSTRAINT fk_exp_competence_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id_utilisateur),
    CONSTRAINT fk_exp_competence_competence
        FOREIGN KEY (id_competence) REFERENCES competence (id_competence)
) ENGINE=InnoDB;

ALTER TABLE utilisateur
    ADD CONSTRAINT fk_utilisateur_totem
        FOREIGN KEY (id_totem) REFERENCES totem (id_totem);

CREATE TABLE categorie (
    id_categorie INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT NULL
) ENGINE=InnoDB;

CREATE TABLE quete (
    id_quete INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    difficulte VARCHAR(30) NOT NULL,
    xp INT UNSIGNED NOT NULL,
    id_categorie INT UNSIGNED NOT NULL,
    id_competence INT UNSIGNED NOT NULL,
    CONSTRAINT fk_quete_categorie
        FOREIGN KEY (id_categorie) REFERENCES categorie (id_categorie),
    CONSTRAINT fk_quete_competence
        FOREIGN KEY (id_competence) REFERENCES competence (id_competence)
) ENGINE=InnoDB;

CREATE TABLE programmation (
    id_programmation INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT UNSIGNED NOT NULL,
    id_quete INT UNSIGNED NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut VARCHAR(30) NOT NULL DEFAULT 'programmee',
    CONSTRAINT uq_programmation_utilisateur_quete_debut
        UNIQUE (id_utilisateur, id_quete, date_debut),
    KEY ix_programmation_utilisateur_fin (id_utilisateur, date_fin),
    CONSTRAINT ck_programmation_dates CHECK (date_fin >= date_debut),
    CONSTRAINT fk_programmation_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id_utilisateur),
    CONSTRAINT fk_programmation_quete
        FOREIGN KEY (id_quete) REFERENCES quete (id_quete)
) ENGINE=InnoDB;

CREATE TABLE type_act (
    id_type_act INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    unite_mesure VARCHAR(50) NULL
) ENGINE=InnoDB;

CREATE TABLE activite_quete (
    id_activite INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    quantite DECIMAL(10,2) NOT NULL,
    description TEXT NULL,
    id_quete INT UNSIGNED NOT NULL,
    id_type_act INT UNSIGNED NOT NULL,
    CONSTRAINT fk_activite_quete
        FOREIGN KEY (id_quete) REFERENCES quete (id_quete),
    CONSTRAINT fk_activite_type_act
        FOREIGN KEY (id_type_act) REFERENCES type_act (id_type_act)
) ENGINE=InnoDB;

CREATE TABLE realiser (
    id_utilisateur INT UNSIGNED NOT NULL,
    id_activite INT UNSIGNED NOT NULL,
    ressenti TEXT NULL,
    date_validation DATE NULL,
    note_preuve TEXT NULL,
    PRIMARY KEY (id_utilisateur, id_activite),
    CONSTRAINT fk_realiser_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id_utilisateur),
    CONSTRAINT fk_realiser_activite
        FOREIGN KEY (id_activite) REFERENCES activite_quete (id_activite)
) ENGINE=InnoDB;

CREATE TABLE objectif (
    id_objectif INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    note TEXT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut VARCHAR(30) NOT NULL,
    id_utilisateur INT UNSIGNED NOT NULL,
    id_competence INT UNSIGNED NOT NULL,
    CONSTRAINT fk_objectif_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id_utilisateur),
    CONSTRAINT fk_objectif_competence
        FOREIGN KEY (id_competence) REFERENCES competence (id_competence)
) ENGINE=InnoDB;

CREATE TABLE etape_objectif (
    id_etape_objectif INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    est_validee BOOLEAN NOT NULL DEFAULT FALSE,
    date_validation DATE NULL,
    id_objectif INT UNSIGNED NOT NULL,
    CONSTRAINT fk_etape_objectif_objectif
        FOREIGN KEY (id_objectif) REFERENCES objectif (id_objectif)
) ENGINE=InnoDB;

CREATE TABLE adherer (
    id_utilisateur INT UNSIGNED NOT NULL,
    id_guilde INT UNSIGNED NOT NULL,
    date_adhesion DATE NOT NULL,
    PRIMARY KEY (id_utilisateur, id_guilde),
    CONSTRAINT fk_adherer_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id_utilisateur),
    CONSTRAINT fk_adherer_guilde
        FOREIGN KEY (id_guilde) REFERENCES guilde (id_guilde)
) ENGINE=InnoDB;

CREATE TABLE proposition (
    id_proposition INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    date_proposition DATE NOT NULL,
    date_limite_vote DATE NOT NULL,
    id_guilde INT UNSIGNED NOT NULL,
    id_quete INT UNSIGNED NOT NULL,
    CONSTRAINT fk_proposition_guilde
        FOREIGN KEY (id_guilde) REFERENCES guilde (id_guilde),
    CONSTRAINT fk_proposition_quete
        FOREIGN KEY (id_quete) REFERENCES quete (id_quete)
) ENGINE=InnoDB;

CREATE TABLE voter (
    id_utilisateur INT UNSIGNED NOT NULL,
    id_proposition INT UNSIGNED NOT NULL,
    date_vote DATE NOT NULL,
    reponse_vote VARCHAR(30) NOT NULL,
    PRIMARY KEY (id_utilisateur, id_proposition),
    CONSTRAINT fk_voter_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id_utilisateur),
    CONSTRAINT fk_voter_proposition
        FOREIGN KEY (id_proposition) REFERENCES proposition (id_proposition)
) ENGINE=InnoDB;

CREATE TABLE publication (
    id_publication INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    titre VARCHAR(150) NOT NULL,
    contenu TEXT NOT NULL,
    date_publication DATE NOT NULL,
    statut VARCHAR(30) NOT NULL,
    sources TEXT NULL,
    id_utilisateur INT UNSIGNED NOT NULL,
    CONSTRAINT fk_publication_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id_utilisateur)
) ENGINE=InnoDB;

CREATE TABLE signalement (
    id_signalement INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    motif VARCHAR(150) NOT NULL,
    commentaire TEXT NULL,
    date_signalement DATE NOT NULL,
    statut VARCHAR(30) NOT NULL,
    id_utilisateur INT UNSIGNED NOT NULL,
    id_publication INT UNSIGNED NOT NULL,
    CONSTRAINT fk_signalement_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id_utilisateur),
    CONSTRAINT fk_signalement_publication
        FOREIGN KEY (id_publication) REFERENCES publication (id_publication)
) ENGINE=InnoDB;

-- Eya : pseudo utilisé par l’inscription, compatible avec les anciens comptes.
ALTER TABLE utilisateur
  ADD COLUMN pseudo VARCHAR(50) NULL AFTER prenom,
  ADD CONSTRAINT uq_utilisateur_pseudo UNIQUE (pseudo);
