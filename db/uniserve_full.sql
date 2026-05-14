-- UniServe — base unique (schéma + données de démonstration)
-- Tables principales, sécurité connexion (OTP / appareils de confiance), notifications, DOCAC (certifications / quiz),
-- agenda démo, colonne evenements.prix_ticket (billets). Réimport : exécuter ce script sur une base vide ou sauvegardée.

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE IF NOT EXISTS `uniserve` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `uniserve`;

-- Drop in dependency order (children before parents)
DROP TABLE IF EXISTS `quizzes`;
DROP TABLE IF EXISTS `demandes_certification`;
DROP TABLE IF EXISTS `certificats`;
DROP TABLE IF EXISTS `cours`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `calendar_demo_items`;
DROP TABLE IF EXISTS `login_risk_challenges`;
DROP TABLE IF EXISTS `password_reset_otps`;
DROP TABLE IF EXISTS `trusted_devices`;
DROP TABLE IF EXISTS `login_failure_events`;
DROP TABLE IF EXISTS `jc-postal`;
DROP TABLE IF EXISTS `inscriptions_evenement`;
DROP TABLE IF EXISTS `evenements`;
DROP TABLE IF EXISTS `clubs`;
DROP TABLE IF EXISTS `demandes_document`;
DROP TABLE IF EXISTS `pieces_jointes`;
DROP TABLE IF EXISTS `demandes_service`;
DROP TABLE IF EXISTS `rendez_vous`;
DROP TABLE IF EXISTS `types_document`;
DROP TABLE IF EXISTS `categories_service`;
DROP TABLE IF EXISTS `bureaux`;
DROP TABLE IF EXISTS `utilisateurs`;

-- ---------------------------------------------------------------------------
-- utilisateurs (live dump columns: derniere_connexion, otp_login_enabled)
-- ---------------------------------------------------------------------------
CREATE TABLE `utilisateurs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe_hash` varchar(255) NOT NULL,
  `role` enum('etudiant','enseignant','staff','admin') NOT NULL DEFAULT 'etudiant',
  `matricule` varchar(50) DEFAULT NULL,
  `departement` varchar(120) DEFAULT NULL,
  `niveau` varchar(50) DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `photo_profil` varchar(255) DEFAULT NULL,
  `statut_compte` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  `derniere_connexion` datetime DEFAULT NULL,
  `otp_login_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `cree_le` timestamp NOT NULL DEFAULT current_timestamp(),
  `modifie_le` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `matricule` (`matricule`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `mot_de_passe_hash`, `role`, `matricule`, `departement`, `niveau`, `telephone`, `photo_profil`, `statut_compte`, `derniere_connexion`, `otp_login_enabled`, `cree_le`, `modifie_le`) VALUES
	(1, 'Admin', 'UniServe', 'admin.uniserve@gmail.com', '$2y$10$zU9XLJ01b.7.bZ9vf19ojOOU8Cd8gK.MZxMWl1AsVW5Pq39MwXKqa', 'admin', NULL, NULL, NULL, NULL, NULL, 'actif', '2026-05-07 15:06:40', 0, '2026-05-07 11:25:12', '2026-05-07 14:06:40'),
	(2, 'Markus', 'Dakus', 'mesfrer135@gmail.com', '$2y$10$kWfWFhQfaztFw1mIopokwO7MC53C3Q3.uOeJbNyr72QYWhj3IqMjG', 'etudiant', NULL, NULL, NULL, NULL, NULL, 'actif', '2026-05-07 15:04:57', 1, '2026-05-07 11:25:12', '2026-05-07 14:04:57'),
	(3, '3ami', 'Amen', 'staff.uniserve@gmail.com', '$2y$10$oDapi1WzNbmdzmFimOqlXuZC6aV1kKFQdmVH2970HVNnra2oyJZKS', 'staff', NULL, NULL, NULL, NULL, NULL, 'actif', NULL, 1, '2026-05-07 11:25:12', '2026-05-07 11:25:39'),
	(4, 'Wi', 'Mrs', 'prof.uniserve@gmail.com', '$2y$10$QY6VY/DTZJsHVAZ6IW2pbOpv6oTxnn4qX3N/v3ZJQHwAffuNeTqhm', 'enseignant', NULL, NULL, NULL, NULL, NULL, 'inactif', NULL, 1, '2026-05-07 11:25:12', '2026-05-07 14:07:48');

CREATE TABLE `categories_service` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `nom` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories_service` (`id`, `nom`, `description`, `actif`) VALUES
	(1, 'Bulletin de notes', 'Demande de relevé de notes officiel', 1),
	(2, 'Attestation de scolarité', 'Certificat de présence en cours', 1),
	(3, 'Réclamation administrative', 'Réclamation auprès de l administration', 1),
	(4, 'Aide financière', 'Demande de bourse ou aide sociale', 1);

CREATE TABLE `types_document` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `nom` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `types_document` (`id`, `nom`, `description`, `actif`) VALUES
	(1, 'Relevé de notes', 'Relevé officiel des notes par semestre', 1),
	(2, 'Liste des cours suivis', 'Détail des matières étudiées', 1),
	(3, 'Attestation de réussite', 'Attestation de validation de l année', 1),
	(4, 'Diplôme', 'Demande de copie de diplôme', 1);

CREATE TABLE `bureaux` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `nom` varchar(120) NOT NULL,
  `localisation` varchar(255) DEFAULT NULL,
  `type_service` varchar(120) NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `bureaux` (`id`, `nom`, `localisation`, `type_service`, `actif`) VALUES
	(1, 'Cellule d ecoute', 'Bâtiment A - Bureau 12', 'soutien_psychologique', 1),
	(2, 'Service financier', 'Bâtiment B - Rez-de-chaussée', 'aide_financiere', 1),
	(3, 'Scolarité', 'Bâtiment C - Bureau 3', 'administratif', 1);

-- clubs: Model/Club.php requires cree_par + statut_validation (not present in raw Heidi dump)
CREATE TABLE `clubs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cree_par` bigint(20) NOT NULL,
  `nom` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `email_contact` varchar(255) DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `statut_validation` enum('en_attente','approuve','rejete') NOT NULL DEFAULT 'en_attente',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`),
  KEY `cree_par` (`cree_par`),
  CONSTRAINT `clubs_ibfk_cree_par` FOREIGN KEY (`cree_par`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `clubs` (`id`, `cree_par`, `nom`, `description`, `email_contact`, `actif`, `statut_validation`) VALUES
	(1, 1, 'Club Informatique', 'Club dédié aux projets tech et développement', 'info@club-info.tn', 1, 'approuve'),
	(2, 1, 'Club Culturel', 'Activités culturelles et artistiques', 'info@club-culture.tn', 1, 'approuve'),
	(3, 1, 'Club Sportif', 'Organisation des activités sportives du campus', 'info@club-sport.tn', 1, 'approuve'),
	(4, 2, 'Club Étudiants solidaires', 'Actions solidaires et bénévolat sur le campus.', 'solidarite@uniserve.tn', 1, 'approuve');

CREATE TABLE `demandes_service` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `etudiant_id` bigint(20) NOT NULL,
  `categorie_id` bigint(20) NOT NULL,
  `titre` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `statut` enum('en_attente','en_cours','traite','rejete') NOT NULL DEFAULT 'en_attente',
  `assigne_a` bigint(20) DEFAULT NULL,
  `soumise_le` timestamp NOT NULL DEFAULT current_timestamp(),
  `cloturee_le` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `etudiant_id` (`etudiant_id`),
  KEY `categorie_id` (`categorie_id`),
  KEY `assigne_a` (`assigne_a`),
  CONSTRAINT `demandes_service_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `demandes_service_ibfk_2` FOREIGN KEY (`categorie_id`) REFERENCES `categories_service` (`id`),
  CONSTRAINT `demandes_service_ibfk_3` FOREIGN KEY (`assigne_a`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pieces_jointes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `demande_service_id` bigint(20) NOT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `chemin_fichier` varchar(500) NOT NULL,
  `type_mime` varchar(100) DEFAULT NULL,
  `televersee_le` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `demande_service_id` (`demande_service_id`),
  CONSTRAINT `pieces_jointes_ibfk_1` FOREIGN KEY (`demande_service_id`) REFERENCES `demandes_service` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `demandes_document` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `etudiant_id` bigint(20) NOT NULL,
  `type_document_id` bigint(20) NOT NULL,
  `statut` enum('en_attente','en_validation','valide','rejete','livre') NOT NULL DEFAULT 'en_attente',
  `valide_par` bigint(20) DEFAULT NULL,
  `note_validation` text DEFAULT NULL,
  `demandee_le` timestamp NOT NULL DEFAULT current_timestamp(),
  `validee_le` timestamp NULL DEFAULT NULL,
  `livree_le` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `etudiant_id` (`etudiant_id`),
  KEY `type_document_id` (`type_document_id`),
  KEY `valide_par` (`valide_par`),
  CONSTRAINT `demandes_document_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `demandes_document_ibfk_2` FOREIGN KEY (`type_document_id`) REFERENCES `types_document` (`id`),
  CONSTRAINT `demandes_document_ibfk_3` FOREIGN KEY (`valide_par`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `rendez_vous` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `etudiant_id` bigint(20) NOT NULL,
  `bureau_id` bigint(20) NOT NULL,
  `motif` varchar(255) DEFAULT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `statut` enum('reserve','confirme','annule','termine') NOT NULL DEFAULT 'reserve',
  `reserve_le` timestamp NOT NULL DEFAULT current_timestamp(),
  `annule_le` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `etudiant_id` (`etudiant_id`),
  KEY `bureau_id` (`bureau_id`),
  CONSTRAINT `rendez_vous_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `rendez_vous_ibfk_2` FOREIGN KEY (`bureau_id`) REFERENCES `bureaux` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- evenements: adds optional valide_par / valide_le from Model/schema/uniserve.sql for admin workflows
CREATE TABLE `evenements` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `club_id` bigint(20) DEFAULT NULL,
  `cree_par` bigint(20) NOT NULL,
  `titre` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `capacite` int(11) DEFAULT NULL,
  `prix_ticket` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'USD; 0 = free',
  `statut` enum('planifie','ouvert','complet','termine','annule') NOT NULL DEFAULT 'planifie',
  `valide_par` bigint(20) DEFAULT NULL,
  `valide_le` datetime DEFAULT NULL,
  `cree_le` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `club_id` (`club_id`),
  KEY `cree_par` (`cree_par`),
  KEY `valide_par` (`valide_par`),
  CONSTRAINT `evenements_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`),
  CONSTRAINT `evenements_ibfk_2` FOREIGN KEY (`cree_par`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `evenements_ibfk_3` FOREIGN KEY (`valide_par`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `evenements` (`id`, `club_id`, `cree_par`, `titre`, `description`, `lieu`, `date_debut`, `date_fin`, `capacite`, `statut`) VALUES
	(1, 1, 1, 'Forum des associations', 'Rencontre avec les clubs du campus et découverte des activités.', 'Cour centrale', '2026-06-15 09:30:00', '2026-06-15 11:30:00', 120, 'ouvert'),
	(2, 2, 1, 'Soirée culturelle', 'Spectacles, stands et rencontres artistiques.', 'Amphi A', '2026-06-20 18:00:00', '2026-06-20 21:00:00', 80, 'ouvert'),
	(3, 3, 1, 'Tournoi sportif inter-clubs', 'Matchs et animations sportives.', 'Gymnase universitaire', '2026-06-25 14:00:00', '2026-06-25 18:00:00', 60, 'ouvert'),
	(4, 1, 1, 'Atelier développement web', 'Session pratique projets et bonnes pratiques.', 'Salle info B12', '2026-07-01 10:00:00', '2026-07-01 12:00:00', 25, 'ouvert'),
	(5, 4, 2, 'Conférence orientation', 'Parcours et insertion — en attente de validation staff.', 'Salle polyvalente', '2026-06-10 11:00:00', '2026-06-10 12:00:00', 50, 'planifie'),
	(6, 4, 2, 'Collecte solidaire', 'Stand et collecte au hall principal.', 'Hall principal', '2026-07-05 10:00:00', '2026-07-05 14:00:00', 30, 'planifie');

CREATE TABLE `inscriptions_evenement` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `evenement_id` bigint(20) NOT NULL,
  `utilisateur_id` bigint(20) NOT NULL,
  `statut` enum('inscrit','present','absent') NOT NULL DEFAULT 'inscrit',
  `inscrit_le` timestamp NOT NULL DEFAULT current_timestamp(),
  `presence_le` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_inscription` (`evenement_id`,`utilisateur_id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `inscriptions_evenement_ibfk_1` FOREIGN KEY (`evenement_id`) REFERENCES `evenements` (`id`),
  CONSTRAINT `inscriptions_evenement_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `calendar_demo_items` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `source_type` enum('rendezvous','events_registered','events_public','certifications') NOT NULL,
  `title` varchar(150) NOT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT '',
  `owner_label` varchar(120) DEFAULT NULL,
  `color` varchar(20) NOT NULL DEFAULT '#2f7df4',
  `url` varchar(255) DEFAULT NULL,
  `is_readonly` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_demo_user_time` (`user_id`,`start_at`),
  KEY `idx_demo_source` (`source_type`),
  CONSTRAINT `calendar_demo_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `calendar_demo_items_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `calendar_demo_items` (`id`, `user_id`, `source_type`, `title`, `start_at`, `end_at`, `location`, `status`, `owner_label`, `color`, `url`, `is_readonly`, `sort_order`, `created_by`, `created_at`) VALUES
	(1, 2, 'rendezvous', 'Entretien dossier de bourse', '2026-05-08 09:00:00', '2026-05-08 09:30:00', 'Scolarité - Bâtiment C - Bureau 3', 'confirme', 'Rendez-vous', '#2f7df4', '/rendezvous', 1, 0, 1, '2026-05-07 12:55:13'),
	(2, 2, 'rendezvous', 'Suivi administratif', '2026-05-09 14:00:00', '2026-05-09 14:45:00', 'Service financier - Bâtiment B - Rez-de-chaussée', 'reserve', 'Rendez-vous', '#2f7df4', '/rendezvous', 1, 1, 1, '2026-05-07 12:55:13'),
	(3, 2, 'events_registered', 'Atelier CV et entretien', '2026-05-08 16:00:00', '2026-05-08 17:30:00', 'Amphi A', 'ouvert', 'Club Career Hub', '#f1a535', '/evenements', 1, 2, 1, '2026-05-07 12:55:13'),
	(4, 2, 'events_registered', 'Conférence orientation', '2026-05-10 11:00:00', '2026-05-10 12:00:00', 'Salle polyvalente', 'planifie', 'Club Culturel', '#7056d8', '/evenements', 1, 3, 1, '2026-05-07 12:55:13'),
	(5, 2, 'events_public', 'Forum des associations', '2026-05-11 09:30:00', '2026-05-11 11:30:00', 'Cour centrale', 'ouvert', 'Club Informatique', '#1fa971', '/evenements', 1, 4, 1, '2026-05-07 12:55:13'),
	(6, 2, 'events_public', 'Soirée campus', '2026-05-12 18:00:00', '2026-05-12 20:00:00', 'Espaces extérieurs', 'complet', 'Club Sportif', '#8d6df2', '/evenements', 1, 5, 1, '2026-05-07 12:55:13');

CREATE TABLE `login_failure_events` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `fingerprint_hash` char(64) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lfe_email_time` (`email`,`attempted_at`),
  KEY `idx_lfe_fingerprint_time` (`fingerprint_hash`,`attempted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `login_failure_events` (`id`, `email`, `fingerprint_hash`, `attempted_at`) VALUES
	(1, 'mesfrer135@gmail.com', '70e9b076e2579236897d0119e9120e77ed1a18119d45c505b72cf39e64b0db3f', '2026-05-07 13:21:16'),
	(2, 'mesfrer135@gmail.com', '70e9b076e2579236897d0119e9120e77ed1a18119d45c505b72cf39e64b0db3f', '2026-05-07 13:21:23'),
	(3, 'mesfrer135@gmail.com', '70e9b076e2579236897d0119e9120e77ed1a18119d45c505b72cf39e64b0db3f', '2026-05-07 13:21:31'),
	(4, 'mesfrer135@gmail.com', '70e9b076e2579236897d0119e9120e77ed1a18119d45c505b72cf39e64b0db3f', '2026-05-07 13:21:35');

CREATE TABLE `login_risk_challenges` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `request_token` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_token` (`request_token`),
  KEY `idx_lrc_user` (`user_id`),
  KEY `idx_lrc_request` (`request_token`),
  CONSTRAINT `login_risk_challenges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `login_risk_challenges` (`id`, `user_id`, `email`, `otp_hash`, `request_token`, `expires_at`, `attempts`, `used_at`, `created_at`) VALUES
	(1, 2, 'mesfrer135@gmail.com', '$2y$10$i6V.jIvfTq/3SNjfDzoLRuz5HiTAhSdA6TmS/cmgUnOzFKCY3oNiW', '6024694d5ae005bed8c9a24b486af8d6d5241b7d46252bf16c052acf67ef1b41', '2026-05-07 14:32:26', 0, '2026-05-07 14:22:51', '2026-05-07 13:22:26'),
	(2, 2, 'mesfrer135@gmail.com', '$2y$10$hhZA8I54WIXP6i0fhI64WuAfimf9WoHdnQoTWhTd8pEP.Lw8kACUO', 'c2090481a01653055a292e7e31fee60710ac642bb0a74ac12e9eca6ffef74507', '2026-05-07 14:44:24', 0, '2026-05-07 14:34:43', '2026-05-07 13:34:24'),
	(3, 1, 'admin.uniserve@gmail.com', '$2y$10$KCoiJC1r/W4QAamGWPOmDuoGOiczlQB1DJJFTjq8k2n0g1UchjlO.', '2c8aec5013f68c561bd8a80d6ef848025864e04876fd0d7b8032d56bae6d5d9d', '2026-05-07 14:54:38', 0, NULL, '2026-05-07 13:44:38'),
	(4, 1, 'admin.uniserve@gmail.com', '$2y$10$IzQnSVxA51nvn6Bh1cVGW.fdWK.YMjzIzqnUnVa1lKav1jjfWGIAC', '31a95ff60a058392c287a8a2e6105c0ad77eac159e471ebfaa19e87e1dccfc39', '2026-05-07 14:55:25', 0, NULL, '2026-05-07 13:45:25');

CREATE TABLE `password_reset_otps` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `request_token` char(64) NOT NULL,
  `reset_token` char(64) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `verified_at` datetime DEFAULT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_token` (`request_token`),
  UNIQUE KEY `reset_token` (`reset_token`),
  KEY `idx_pwr_user` (`user_id`),
  KEY `idx_pwr_request` (`request_token`),
  KEY `idx_pwr_reset` (`reset_token`),
  CONSTRAINT `password_reset_otps_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `password_reset_otps` (`id`, `user_id`, `email`, `otp_hash`, `request_token`, `reset_token`, `expires_at`, `attempts`, `verified_at`, `used_at`, `created_at`) VALUES
	(4, 2, 'mesfrer135@gmail.com', '$2y$10$iyQx0Q3xfUdU/LDOlRqM.ePzTJ6E9h8Z/mFnAPbLiL.45sH6xnC/e', '59c98bc303636c0ae1aeaa6a7bfa9e175f746508c554a39652876975397ac5fd', NULL, '2026-05-07 14:08:29', 0, NULL, NULL, '2026-05-07 12:58:29');

CREATE TABLE `trusted_devices` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `fingerprint_hash` char(64) NOT NULL,
  `first_seen` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_seen` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_trusted_device` (`user_id`,`fingerprint_hash`),
  KEY `idx_td_user` (`user_id`),
  CONSTRAINT `trusted_devices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `trusted_devices` (`id`, `user_id`, `fingerprint_hash`, `first_seen`, `last_seen`) VALUES
	(1, 2, '70e9b076e2579236897d0119e9120e77ed1a18119d45c505b72cf39e64b0db3f', '2026-05-07 13:22:51', '2026-05-07 14:04:57'),
	(3, 1, '70e9b076e2579236897d0119e9120e77ed1a18119d45c505b72cf39e64b0db3f', '2026-05-07 13:50:12', '2026-05-07 14:06:40');

-- ---------------------------------------------------------------------------
-- Notifications (Model/NotificationModel.php)
-- ---------------------------------------------------------------------------
CREATE TABLE `notifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` bigint(20) NOT NULL,
  `message` varchar(512) NOT NULL,
  `lien` varchar(512) DEFAULT NULL,
  `lu` tinyint(1) NOT NULL DEFAULT 0,
  `cree_le` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notif_user_lu` (`utilisateur_id`,`lu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- DOCAC / certifications (Model/DocacSchema.php, CertificationsController)
-- ---------------------------------------------------------------------------
CREATE TABLE `cours` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `formateur` varchar(255) DEFAULT NULL,
  `contenu` text DEFAULT NULL,
  `image_path` varchar(512) DEFAULT NULL,
  `fichiers_json` longtext DEFAULT NULL,
  `cree_le` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cours_titre` (`titre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `certificats` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `nom_certificat` varchar(255) NOT NULL,
  `date_obtention` date NOT NULL,
  `organisation` varchar(255) NOT NULL,
  `fichier_path` varchar(512) DEFAULT NULL,
  `titre_cours` varchar(255) NOT NULL,
  `cree_le` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `demandes_certification` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` bigint(20) NOT NULL,
  `nom_certificat` varchar(255) NOT NULL,
  `titre_cours` varchar(255) DEFAULT NULL,
  `organisation` varchar(255) NOT NULL,
  `date_souhaitee` date NOT NULL,
  `heure_preferee` varchar(64) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `fichier_path` varchar(512) DEFAULT NULL,
  `statut` enum('en_attente','quiz_envoye','accepte','refuse') NOT NULL DEFAULT 'en_attente',
  `commentaire_admin` text DEFAULT NULL,
  `soumise_le` timestamp NOT NULL DEFAULT current_timestamp(),
  `traitee_le` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_demcert_user` (`utilisateur_id`),
  KEY `idx_demcert_statut` (`statut`),
  CONSTRAINT `demandes_certification_ibfk_user` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quizzes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `demande_id` bigint(20) NOT NULL,
  `cours_titre` varchar(255) NOT NULL,
  `questions_json` longtext NOT NULL,
  `statut` enum('en_attente','accepte','refuse') NOT NULL DEFAULT 'en_attente',
  `score` tinyint(4) DEFAULT NULL,
  `passe_le` datetime DEFAULT NULL,
  `cree_le` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_quizzes_demande` (`demande_id`),
  CONSTRAINT `quizzes_ibfk_demande` FOREIGN KEY (`demande_id`) REFERENCES `demandes_certification` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

-- ---------------------------------------------------------------------------
-- DOCAC Seed Data
-- ---------------------------------------------------------------------------

INSERT INTO `cours` (`id`, `titre`, `description`, `formateur`, `contenu`, `image_path`, `fichiers_json`, `cree_le`) VALUES
(1, 'Développement Web Full-Stack', 'Maîtrisez HTML, CSS, JavaScript, PHP et MySQL pour créer des applications web complètes.', 'Mrs Wi', 'Ce cours couvre l\'ensemble de la stack web moderne :\n\n1. HTML5 & CSS3 : structure sémantique, Flexbox, Grid\n2. JavaScript ES6+ : DOM, Fetch API, Promises\n3. PHP 8 : OOP, MVC, PDO\n4. MySQL : modélisation, requêtes avancées, transactions\n5. Projet final : application CRUD complète\n\nPré-requis : bases de la programmation.', NULL, '[]', NOW()),
(2, 'Cybersécurité Fondamentale', 'Introduction aux concepts essentiels de la sécurité informatique et des réseaux.', 'Admin UniServe', 'Programme :\n\n1. Menaces & vulnérabilités (OWASP Top 10)\n2. Cryptographie : symétrique, asymétrique, hachage\n3. Authentification & gestion des accès\n4. Sécurité réseau : firewall, VPN, IDS/IPS\n5. Tests de pénétration : méthodologie\n6. Conformité : RGPD, ISO 27001\n\nOutils utilisés : Wireshark, Metasploit, Burp Suite.', NULL, '[]', NOW()),
(3, 'Intelligence Artificielle & Machine Learning', 'Concepts fondamentaux de l\'IA, du ML supervisé et non supervisé, et des réseaux de neurones.', 'Mrs Wi', 'Contenu détaillé :\n\n1. Introduction à l\'IA : histoire et enjeux\n2. Machine Learning supervisé : régression, classification\n3. ML non supervisé : clustering, réduction de dimension\n4. Deep Learning : CNN, RNN, Transformers\n5. Outils : Python, scikit-learn, TensorFlow, PyTorch\n6. Projet : modèle de classification d\'images\n\nNiveau : intermédiaire.', NULL, '[]', NOW()),
(4, 'Gestion de Projet Agile', 'Scrum, Kanban et méthodes agiles pour mener vos projets informatiques avec succès.', 'Admin UniServe', 'Modules :\n\n1. Manifeste Agile & valeurs\n2. Scrum : rôles, cérémonies, artefacts\n3. Kanban : flux, WIP limits\n4. User Stories & backlog grooming\n5. Estimation : Planning Poker, points\n6. Outils : Jira, Trello, GitHub Projects\n\nAtelier pratique inclus.', NULL, '[]', NOW());

INSERT INTO `certificats` (`id`, `nom_certificat`, `date_obtention`, `organisation`, `fichier_path`, `titre_cours`, `cree_le`) VALUES
(1, 'Certification Full-Stack Developer', '2026-06-15', 'UniServe', NULL, 'Développement Web Full-Stack', NOW()),
(2, 'Certified Cybersecurity Analyst', '2026-07-01', 'UniServe', NULL, 'Cybersécurité Fondamentale', NOW()),
(3, 'AI & ML Professional Certificate', '2026-08-20', 'UniServe', NULL, 'Intelligence Artificielle & Machine Learning', NOW()),
(4, 'Agile Project Manager', '2026-09-10', 'UniServe', NULL, 'Gestion de Projet Agile', NOW());

INSERT INTO `demandes_certification` (`id`, `utilisateur_id`, `nom_certificat`, `titre_cours`, `organisation`, `date_souhaitee`, `heure_preferee`, `notes`, `fichier_path`, `statut`, `commentaire_admin`, `soumise_le`, `traitee_le`) VALUES
(1, 2, 'Certification Full-Stack Developer', 'Développement Web Full-Stack', 'UniServe', '2026-06-15', '10:00', 'Je suis prêt pour la certification.', NULL, 'accepte', 'Excellent dossier, certification validée.', '2026-05-01 09:00:00', '2026-05-10 14:00:00'),
(2, 2, 'Certified Cybersecurity Analyst', 'Cybersécurité Fondamentale', 'UniServe', '2026-07-01', '14:00', 'Terminé le module complet.', NULL, 'quiz_envoye', NULL, '2026-05-05 10:30:00', NULL),
(3, 2, 'AI & ML Professional Certificate', 'Intelligence Artificielle & Machine Learning', 'UniServe', '2026-08-20', '09:00', 'Très intéressé par l\'IA appliquée.', NULL, 'en_attente', NULL, '2026-05-10 11:00:00', NULL),
(4, 2, 'Agile Project Manager', 'Gestion de Projet Agile', 'UniServe', '2026-09-10', '11:00', NULL, NULL, 'refuse', 'Dossier incomplet — merci de le compléter.', '2026-05-08 08:00:00', '2026-05-12 09:00:00');

INSERT INTO `quizzes` (`id`, `demande_id`, `cours_titre`, `questions_json`, `statut`, `score`, `passe_le`, `cree_le`) VALUES
(1, 2, 'Cybersécurité Fondamentale',
'[{"question":"Quel est le principal objectif de la cryptographie asymétrique ?","options":["Chiffrer avec une paire de clés publique\\/privée","Accélérer les connexions réseau","Compresser les fichiers","Détecter les intrusions"],"correct":0},{"question":"Parmi ces attaques, laquelle cible principalement les applications web selon l\'OWASP ?","options":["Injection SQL","Déni de service distribué","Phishing","Ransomware"],"correct":0},{"question":"Qu\'est-ce qu\'un firewall de type stateful ?","options":["Il inspecte l\'état des connexions réseau","Il bloque uniquement les ports","Il analyse le contenu des emails","Il chiffre le trafic"],"correct":0},{"question":"Quel protocole est utilisé pour sécuriser les échanges HTTP ?","options":["TLS\\/SSL","FTP","SMTP","UDP"],"correct":0},{"question":"Que signifie RGPD ?","options":["Règlement Général sur la Protection des Données","Réseau de Gestion des Protocoles Distribués","Registre Global des Politiques de Données","Rien de tout cela"],"correct":0}]',
'en_attente', NULL, NULL, NOW());
