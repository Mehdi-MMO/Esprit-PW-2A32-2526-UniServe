CREATE DATABASE IF NOT EXISTS uniserve;
USE uniserve;

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

INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe_hash, role, matricule, departement, niveau, telephone, statut_compte) VALUES
('Admin', 'UniServe', 'admin.uniserve@gmail.com', '$2y$10$zU9XLJ01b.7.bZ9vf19ojOOU8Cd8gK.MZxMWl1AsVW5Pq39MwXKqa', 'admin', 'ADM-001', 'Administration', 'N/A', '00000000', 'actif'),
('Dupont', 'Jean', 'etudiant.uniserve@gmail.com', '$2y$10$pNfl6q7OdMey/uTT0GVcZu8RGZW4PM1KCtcTpFam/3VbXbZf3KfFK', 'etudiant', 'ETU-2026-001', 'Informatique', 'Licence 3', '22222222', 'actif'),
('Leclerc', 'Paul', 'prof.uniserve@gmail.com', '$2y$10$QY6VY/DTZJsHVAZ6IW2pbOpv6oTxnn4qX3N/v3ZJQHwAffuNeTqhm', 'enseignant', 'ENS-001', 'Sciences', 'Master', '33333333', 'actif'),
('Martin', 'Sophie', 'staff.uniserve@gmail.com', '$2y$10$oDapi1WzNbmdzmFimOqlXuZC6aV1kKFQdmVH2970HVNnra2oyJZKS', 'staff', 'STF-001', 'Services', 'N/A', '44444444', 'actif');
