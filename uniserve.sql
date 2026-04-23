USE uniserve;

-- Drop tables in reverse FK order
DROP TABLE IF EXISTS inscriptions_evenement;
DROP TABLE IF EXISTS evenements;
DROP TABLE IF EXISTS clubs;
DROP TABLE IF EXISTS demandes_document;
DROP TABLE IF EXISTS types_document;
DROP TABLE IF EXISTS rendez_vous;
DROP TABLE IF EXISTS bureaux;
DROP TABLE IF EXISTS pieces_jointes;
DROP TABLE IF EXISTS demandes_service;
DROP TABLE IF EXISTS categories_service;
DROP TABLE IF EXISTS utilisateurs;

-- ==========================================
-- MODULE 1 : UTILISATEURS
-- ==========================================
CREATE TABLE utilisateurs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  mot_de_passe_hash VARCHAR(255) NOT NULL,
  role ENUM('etudiant','enseignant','staff','admin') NOT NULL DEFAULT 'etudiant',
  matricule VARCHAR(50) UNIQUE,
  departement VARCHAR(120),
  niveau VARCHAR(50),
  telephone VARCHAR(30),
  photo_profil VARCHAR(255) NULL,
  statut_compte ENUM('actif','inactif') NOT NULL DEFAULT 'actif',
  cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modifie_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe_hash, role) VALUES
('Admin',    'UniServe', 'admin@uniserve.net',    '$2y$10$zU9XLJ01b.7.bZ9vf19ojOOU8Cd8gK.MZxMWl1AsVW5Pq39MwXKqa', 'admin'),
('Dupont',   'Jean',     'etudiant@uniserve.net', '$2y$10$pNfl6q7OdMey/uTT0GVcZu8RGZW4PM1KCtcTpFam/3VbXbZf3KfFK', 'etudiant'),
('Martin',   'Sophie',   'staff@uniserve.net',    '$2y$10$oDapi1WzNbmdzmFimOqlXuZC6aV1kKFQdmVH2970HVNnra2oyJZKS', 'staff'),
('Leclerc',  'Paul',     'prof@uniserve.net',     '$2y$10$QY6VY/DTZJsHVAZ6IW2pbOpv6oTxnn4qX3N/v3ZJQHwAffuNeTqhm', 'enseignant');

-- ==========================================
-- MODULE 2 : DEMANDES DE SERVICE
-- ==========================================
CREATE TABLE categories_service (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(120) NOT NULL UNIQUE,
  description TEXT,
  actif BOOLEAN NOT NULL DEFAULT TRUE
);

INSERT INTO categories_service (nom, description) VALUES
('Bulletin de notes',         'Demande de relevé de notes officiel'),
('Attestation de scolarité',  'Certificat de présence en cours'),
('Réclamation administrative','Réclamation auprès de l administration'),
('Aide financière',           'Demande de bourse ou aide sociale');

CREATE TABLE demandes_service (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  etudiant_id BIGINT NOT NULL,
  categorie_id BIGINT NOT NULL,
  titre VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  statut ENUM('en_attente','en_cours','traite','rejete') NOT NULL DEFAULT 'en_attente',
  assigne_a BIGINT NULL DEFAULT NULL,
  soumise_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  cloturee_le TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id),
  FOREIGN KEY (categorie_id) REFERENCES categories_service(id),
  FOREIGN KEY (assigne_a) REFERENCES utilisateurs(id)
);

CREATE TABLE pieces_jointes (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  demande_service_id BIGINT NOT NULL,
  nom_fichier VARCHAR(255) NOT NULL,
  chemin_fichier VARCHAR(500) NOT NULL,
  type_mime VARCHAR(100),
  televersee_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (demande_service_id) REFERENCES demandes_service(id)
);

-- ==========================================
-- MODULE 3 : RENDEZ-VOUS
-- ==========================================
CREATE TABLE bureaux (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(120) NOT NULL,
  localisation VARCHAR(255),
  type_service VARCHAR(120) NOT NULL,
  actif BOOLEAN NOT NULL DEFAULT TRUE
);

INSERT INTO bureaux (nom, localisation, type_service) VALUES
('Cellule d ecoute',  'Bâtiment A - Bureau 12',       'soutien_psychologique'),
('Service financier', 'Bâtiment B - Rez-de-chaussée', 'aide_financiere'),
('Scolarité',         'Bâtiment C - Bureau 3',         'administratif');

CREATE TABLE rendez_vous (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  etudiant_id BIGINT NOT NULL,
  bureau_id BIGINT NOT NULL,
  motif VARCHAR(255),
  date_debut DATETIME NOT NULL,
  date_fin DATETIME NOT NULL,
  statut ENUM('reserve','confirme','annule','termine') NOT NULL DEFAULT 'reserve',
  reserve_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  annule_le TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id),
  FOREIGN KEY (bureau_id) REFERENCES bureaux(id)
);

-- ==========================================
-- MODULE 4 : DOCUMENTS ACADÉMIQUES
-- ==========================================
CREATE TABLE types_document (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(120) NOT NULL UNIQUE,
  description TEXT,
  actif BOOLEAN NOT NULL DEFAULT TRUE
);

INSERT INTO types_document (nom, description) VALUES
('Relevé de notes',      'Relevé officiel des notes par semestre'),
('Liste des cours suivis','Détail des matières étudiées'),
('Attestation de réussite','Attestation de validation de l année'),
('Diplôme',              'Demande de copie de diplôme');

CREATE TABLE demandes_document (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  etudiant_id BIGINT NOT NULL,
  type_document_id BIGINT NOT NULL,
  statut ENUM('en_attente','en_validation','valide','rejete','livre') NOT NULL DEFAULT 'en_attente',
  valide_par BIGINT NULL DEFAULT NULL,
  note_validation TEXT,
  demandee_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  validee_le TIMESTAMP NULL DEFAULT NULL,
  livree_le TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id),
  FOREIGN KEY (type_document_id) REFERENCES types_document(id),
  FOREIGN KEY (valide_par) REFERENCES utilisateurs(id)
);

-- ==========================================
-- MODULE 5 : ÉVÉNEMENTS
-- ==========================================
CREATE TABLE clubs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  cree_par BIGINT NOT NULL,
  nom VARCHAR(120) NOT NULL UNIQUE,
  description TEXT,
  email_contact VARCHAR(255),
  actif BOOLEAN NOT NULL DEFAULT TRUE,
  statut_validation ENUM('en_attente','approuve','rejete') NOT NULL DEFAULT 'en_attente',
  FOREIGN KEY (cree_par) REFERENCES utilisateurs(id)
);

INSERT INTO clubs (cree_par, nom, description, email_contact, actif, statut_validation) VALUES
(1, 'Club Informatique', 'Club dédié aux projets tech et développement', 'info@club-info.tn', 1, 'approuve'),
(1, 'Club Culturel',     'Activités culturelles et artistiques',         'info@club-culture.tn', 1, 'approuve'),
(1, 'Club Sportif',      'Organisation des activités sportives du campus','info@club-sport.tn', 1, 'approuve');

CREATE TABLE evenements (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  club_id BIGINT NULL DEFAULT NULL,
  cree_par BIGINT NOT NULL,
  titre VARCHAR(150) NOT NULL,
  description TEXT,
  lieu VARCHAR(255),
  date_debut DATETIME NOT NULL,
  date_fin DATETIME NOT NULL,
  capacite INT,
  statut ENUM('planifie','ouvert','complet','termine','annule') NOT NULL DEFAULT 'planifie',
  valide_par BIGINT NULL DEFAULT NULL,
  valide_le DATETIME NULL DEFAULT NULL,
  cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (club_id) REFERENCES clubs(id),
  FOREIGN KEY (cree_par) REFERENCES utilisateurs(id),
  FOREIGN KEY (valide_par) REFERENCES utilisateurs(id)
);

CREATE TABLE inscriptions_evenement (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  evenement_id BIGINT NOT NULL,
  utilisateur_id BIGINT NOT NULL,
  statut ENUM('inscrit','present','absent') NOT NULL DEFAULT 'inscrit',
  inscrit_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  presence_le TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY unique_inscription (evenement_id, utilisateur_id),
  FOREIGN KEY (evenement_id) REFERENCES evenements(id),
  FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);
