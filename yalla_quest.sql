-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2026 at 01:17 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `yalla_quest`
--

-- --------------------------------------------------------

--
-- Table structure for table `activite_quete`
--

CREATE TABLE `activite_quete` (
  `id_activite` int(10) UNSIGNED NOT NULL,
  `nom` varchar(150) NOT NULL,
  `quantite` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `id_quete` int(10) UNSIGNED NOT NULL,
  `id_type_act` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `adherer`
--

CREATE TABLE `adherer` (
  `id_utilisateur` int(10) UNSIGNED NOT NULL,
  `id_guilde` int(10) UNSIGNED NOT NULL,
  `date_adhesion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categorie`
--

CREATE TABLE `categorie` (
  `id_categorie` int(10) UNSIGNED NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `competence`
--

CREATE TABLE `competence` (
  `id_competence` int(10) UNSIGNED NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `visuel` varchar(255) DEFAULT NULL,
  `pourcentage` decimal(5,2) DEFAULT NULL,
  `id_utilisateur_maj` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `etape_objectif`
--

CREATE TABLE `etape_objectif` (
  `id_etape_objectif` int(10) UNSIGNED NOT NULL,
  `titre` varchar(150) NOT NULL,
  `est_validee` tinyint(1) NOT NULL DEFAULT 0,
  `date_validation` date DEFAULT NULL,
  `id_objectif` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exp_competence`
--

CREATE TABLE `exp_competence` (
  `id_utilisateur` int(10) UNSIGNED NOT NULL,
  `id_competence` int(10) UNSIGNED NOT NULL,
  `xp_c` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guilde`
--

CREATE TABLE `guilde` (
  `id_guilde` int(10) UNSIGNED NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `date_creation` date NOT NULL,
  `id_utilisateur_createur` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `id_image` varchar(64) NOT NULL,
  `chemin` varchar(2048) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `objectif`
--

CREATE TABLE `objectif` (
  `id_objectif` int(10) UNSIGNED NOT NULL,
  `titre` varchar(150) NOT NULL,
  `note` text NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `statut` varchar(30) NOT NULL,
  `id_utilisateur` int(10) UNSIGNED NOT NULL,
  `id_competence` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `programmation`
--

CREATE TABLE `programmation` (
  `id_programmation` int(10) UNSIGNED NOT NULL,
  `id_utilisateur` int(10) UNSIGNED NOT NULL,
  `id_quete` int(10) UNSIGNED NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `statut` varchar(30) NOT NULL DEFAULT 'programmee'
) ;

-- --------------------------------------------------------

--
-- Table structure for table `proposition`
--

CREATE TABLE `proposition` (
  `id_proposition` int(10) UNSIGNED NOT NULL,
  `date_proposition` date NOT NULL,
  `date_limite_vote` date NOT NULL,
  `id_guilde` int(10) UNSIGNED NOT NULL,
  `id_quete` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `publication`
--

CREATE TABLE `publication` (
  `id_publication` int(10) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `titre` varchar(150) NOT NULL,
  `contenu` text NOT NULL,
  `date_publication` date NOT NULL,
  `statut` varchar(30) NOT NULL,
  `sources` text DEFAULT NULL,
  `id_utilisateur` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quete`
--

CREATE TABLE `quete` (
  `id_quete` int(10) UNSIGNED NOT NULL,
  `titre` varchar(150) NOT NULL,
  `difficulte` varchar(30) NOT NULL,
  `xp` int(10) UNSIGNED NOT NULL,
  `id_categorie` int(10) UNSIGNED NOT NULL,
  `id_competence` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `realiser`
--

CREATE TABLE `realiser` (
  `id_utilisateur` int(10) UNSIGNED NOT NULL,
  `id_activite` int(10) UNSIGNED NOT NULL,
  `ressenti` text DEFAULT NULL,
  `date_validation` date DEFAULT NULL,
  `note_preuve` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `signalement`
--

CREATE TABLE `signalement` (
  `id_signalement` int(10) UNSIGNED NOT NULL,
  `motif` varchar(150) NOT NULL,
  `commentaire` text DEFAULT NULL,
  `date_signalement` date NOT NULL,
  `statut` varchar(30) NOT NULL,
  `id_utilisateur` int(10) UNSIGNED NOT NULL,
  `id_publication` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `totem`
--

CREATE TABLE `totem` (
  `id_totem` int(10) UNSIGNED NOT NULL,
  `nom` varchar(100) NOT NULL,
  `visuel` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `seuil` int(10) UNSIGNED NOT NULL,
  `id_competence` int(10) UNSIGNED NOT NULL,
  `id_utilisateur_maj` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `type_act`
--

CREATE TABLE `type_act` (
  `id_type_act` int(10) UNSIGNED NOT NULL,
  `nom` varchar(100) NOT NULL,
  `unite_mesure` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id_utilisateur` int(10) UNSIGNED NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `pseudo` varchar(50) DEFAULT NULL,
  `mail` varchar(254) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `statut` enum('actif','bloque') NOT NULL DEFAULT 'actif',
  `mot_de_passe_hash` varchar(255) NOT NULL,
  `total_xp` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `niveau` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `derniere_prolongation_at` datetime DEFAULT NULL,
  `id_totem` int(10) UNSIGNED DEFAULT NULL,
  `date_obtention_totem` date DEFAULT NULL,
  `img_profil` varchar(100) DEFAULT NULL,
  `id_guilde` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `voter`
--

CREATE TABLE `voter` (
  `id_utilisateur` int(10) UNSIGNED NOT NULL,
  `id_proposition` int(10) UNSIGNED NOT NULL,
  `date_vote` date NOT NULL,
  `reponse_vote` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activite_quete`
--
ALTER TABLE `activite_quete`
  ADD PRIMARY KEY (`id_activite`),
  ADD KEY `fk_activite_quete` (`id_quete`),
  ADD KEY `fk_activite_type_act` (`id_type_act`);

--
-- Indexes for table `adherer`
--
ALTER TABLE `adherer`
  ADD PRIMARY KEY (`id_utilisateur`,`id_guilde`),
  ADD KEY `fk_adherer_guilde` (`id_guilde`);

--
-- Indexes for table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id_categorie`);

--
-- Indexes for table `competence`
--
ALTER TABLE `competence`
  ADD PRIMARY KEY (`id_competence`),
  ADD KEY `fk_competence_utilisateur_maj` (`id_utilisateur_maj`);

--
-- Indexes for table `etape_objectif`
--
ALTER TABLE `etape_objectif`
  ADD PRIMARY KEY (`id_etape_objectif`),
  ADD KEY `fk_etape_objectif_objectif` (`id_objectif`);

--
-- Indexes for table `exp_competence`
--
ALTER TABLE `exp_competence`
  ADD PRIMARY KEY (`id_utilisateur`,`id_competence`),
  ADD KEY `fk_exp_competence_competence` (`id_competence`);

--
-- Indexes for table `guilde`
--
ALTER TABLE `guilde`
  ADD PRIMARY KEY (`id_guilde`),
  ADD KEY `fk_guilde_createur` (`id_utilisateur_createur`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id_image`);

--
-- Indexes for table `objectif`
--
ALTER TABLE `objectif`
  ADD PRIMARY KEY (`id_objectif`),
  ADD KEY `fk_objectif_utilisateur` (`id_utilisateur`),
  ADD KEY `fk_objectif_competence` (`id_competence`);

--
-- Indexes for table `programmation`
--
ALTER TABLE `programmation`
  ADD PRIMARY KEY (`id_programmation`),
  ADD UNIQUE KEY `uq_programmation_utilisateur_quete_debut` (`id_utilisateur`,`id_quete`,`date_debut`),
  ADD KEY `ix_programmation_utilisateur_fin` (`id_utilisateur`,`date_fin`),
  ADD KEY `fk_programmation_quete` (`id_quete`);

--
-- Indexes for table `proposition`
--
ALTER TABLE `proposition`
  ADD PRIMARY KEY (`id_proposition`),
  ADD KEY `fk_proposition_guilde` (`id_guilde`),
  ADD KEY `fk_proposition_quete` (`id_quete`);

--
-- Indexes for table `publication`
--
ALTER TABLE `publication`
  ADD PRIMARY KEY (`id_publication`),
  ADD KEY `fk_publication_utilisateur` (`id_utilisateur`);

--
-- Indexes for table `quete`
--
ALTER TABLE `quete`
  ADD PRIMARY KEY (`id_quete`),
  ADD KEY `fk_quete_categorie` (`id_categorie`),
  ADD KEY `fk_quete_competence` (`id_competence`);

--
-- Indexes for table `realiser`
--
ALTER TABLE `realiser`
  ADD PRIMARY KEY (`id_utilisateur`,`id_activite`),
  ADD KEY `fk_realiser_activite` (`id_activite`);

--
-- Indexes for table `signalement`
--
ALTER TABLE `signalement`
  ADD PRIMARY KEY (`id_signalement`),
  ADD KEY `fk_signalement_utilisateur` (`id_utilisateur`),
  ADD KEY `fk_signalement_publication` (`id_publication`);

--
-- Indexes for table `totem`
--
ALTER TABLE `totem`
  ADD PRIMARY KEY (`id_totem`),
  ADD UNIQUE KEY `uq_totem_competence` (`id_competence`),
  ADD KEY `fk_totem_utilisateur_maj` (`id_utilisateur_maj`);

--
-- Indexes for table `type_act`
--
ALTER TABLE `type_act`
  ADD PRIMARY KEY (`id_type_act`);

--
-- Indexes for table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `uq_utilisateur_mail` (`mail`),
  ADD UNIQUE KEY `uq_utilisateur_pseudo` (`pseudo`),
  ADD KEY `fk_utilisateur_img_profil` (`img_profil`),
  ADD KEY `fk_utilisateur_guilde` (`id_guilde`),
  ADD KEY `fk_utilisateur_totem` (`id_totem`);

--
-- Indexes for table `voter`
--
ALTER TABLE `voter`
  ADD PRIMARY KEY (`id_utilisateur`,`id_proposition`),
  ADD KEY `fk_voter_proposition` (`id_proposition`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activite_quete`
--
ALTER TABLE `activite_quete`
  MODIFY `id_activite` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id_categorie` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `competence`
--
ALTER TABLE `competence`
  MODIFY `id_competence` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `etape_objectif`
--
ALTER TABLE `etape_objectif`
  MODIFY `id_etape_objectif` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guilde`
--
ALTER TABLE `guilde`
  MODIFY `id_guilde` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `objectif`
--
ALTER TABLE `objectif`
  MODIFY `id_objectif` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `programmation`
--
ALTER TABLE `programmation`
  MODIFY `id_programmation` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proposition`
--
ALTER TABLE `proposition`
  MODIFY `id_proposition` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `publication`
--
ALTER TABLE `publication`
  MODIFY `id_publication` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quete`
--
ALTER TABLE `quete`
  MODIFY `id_quete` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `signalement`
--
ALTER TABLE `signalement`
  MODIFY `id_signalement` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `totem`
--
ALTER TABLE `totem`
  MODIFY `id_totem` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `type_act`
--
ALTER TABLE `type_act`
  MODIFY `id_type_act` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id_utilisateur` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activite_quete`
--
ALTER TABLE `activite_quete`
  ADD CONSTRAINT `fk_activite_quete` FOREIGN KEY (`id_quete`) REFERENCES `quete` (`id_quete`),
  ADD CONSTRAINT `fk_activite_type_act` FOREIGN KEY (`id_type_act`) REFERENCES `type_act` (`id_type_act`);

--
-- Constraints for table `adherer`
--
ALTER TABLE `adherer`
  ADD CONSTRAINT `fk_adherer_guilde` FOREIGN KEY (`id_guilde`) REFERENCES `guilde` (`id_guilde`),
  ADD CONSTRAINT `fk_adherer_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Constraints for table `competence`
--
ALTER TABLE `competence`
  ADD CONSTRAINT `fk_competence_utilisateur_maj` FOREIGN KEY (`id_utilisateur_maj`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Constraints for table `etape_objectif`
--
ALTER TABLE `etape_objectif`
  ADD CONSTRAINT `fk_etape_objectif_objectif` FOREIGN KEY (`id_objectif`) REFERENCES `objectif` (`id_objectif`);

--
-- Constraints for table `exp_competence`
--
ALTER TABLE `exp_competence`
  ADD CONSTRAINT `fk_exp_competence_competence` FOREIGN KEY (`id_competence`) REFERENCES `competence` (`id_competence`),
  ADD CONSTRAINT `fk_exp_competence_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Constraints for table `guilde`
--
ALTER TABLE `guilde`
  ADD CONSTRAINT `fk_guilde_createur` FOREIGN KEY (`id_utilisateur_createur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Constraints for table `objectif`
--
ALTER TABLE `objectif`
  ADD CONSTRAINT `fk_objectif_competence` FOREIGN KEY (`id_competence`) REFERENCES `competence` (`id_competence`),
  ADD CONSTRAINT `fk_objectif_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Constraints for table `programmation`
--
ALTER TABLE `programmation`
  ADD CONSTRAINT `fk_programmation_quete` FOREIGN KEY (`id_quete`) REFERENCES `quete` (`id_quete`),
  ADD CONSTRAINT `fk_programmation_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Constraints for table `proposition`
--
ALTER TABLE `proposition`
  ADD CONSTRAINT `fk_proposition_guilde` FOREIGN KEY (`id_guilde`) REFERENCES `guilde` (`id_guilde`),
  ADD CONSTRAINT `fk_proposition_quete` FOREIGN KEY (`id_quete`) REFERENCES `quete` (`id_quete`);

--
-- Constraints for table `publication`
--
ALTER TABLE `publication`
  ADD CONSTRAINT `fk_publication_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Constraints for table `quete`
--
ALTER TABLE `quete`
  ADD CONSTRAINT `fk_quete_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`id_categorie`),
  ADD CONSTRAINT `fk_quete_competence` FOREIGN KEY (`id_competence`) REFERENCES `competence` (`id_competence`);

--
-- Constraints for table `realiser`
--
ALTER TABLE `realiser`
  ADD CONSTRAINT `fk_realiser_activite` FOREIGN KEY (`id_activite`) REFERENCES `activite_quete` (`id_activite`),
  ADD CONSTRAINT `fk_realiser_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Constraints for table `signalement`
--
ALTER TABLE `signalement`
  ADD CONSTRAINT `fk_signalement_publication` FOREIGN KEY (`id_publication`) REFERENCES `publication` (`id_publication`),
  ADD CONSTRAINT `fk_signalement_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Constraints for table `totem`
--
ALTER TABLE `totem`
  ADD CONSTRAINT `fk_totem_competence` FOREIGN KEY (`id_competence`) REFERENCES `competence` (`id_competence`),
  ADD CONSTRAINT `fk_totem_utilisateur_maj` FOREIGN KEY (`id_utilisateur_maj`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Constraints for table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD CONSTRAINT `fk_utilisateur_guilde` FOREIGN KEY (`id_guilde`) REFERENCES `guilde` (`id_guilde`),
  ADD CONSTRAINT `fk_utilisateur_img_profil` FOREIGN KEY (`img_profil`) REFERENCES `images` (`id_image`),
  ADD CONSTRAINT `fk_utilisateur_totem` FOREIGN KEY (`id_totem`) REFERENCES `totem` (`id_totem`);

--
-- Constraints for table `voter`
--
ALTER TABLE `voter`
  ADD CONSTRAINT `fk_voter_proposition` FOREIGN KEY (`id_proposition`) REFERENCES `proposition` (`id_proposition`),
  ADD CONSTRAINT `fk_voter_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
