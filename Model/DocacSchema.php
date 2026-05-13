<?php

declare(strict_types=1);

/**
 * Ensures DOCAC certification tables exist (same DDL as in db/uniserve_full.sql).
 * Avoids forcing developers through phpMyAdmin on fresh or partial databases.
 */
final class DocacSchema
{
    private static bool $installed = false;

    /**
     * Runs CREATE TABLE IF NOT EXISTS for all DOCAC tables. Idempotent; skips work after first success in-process.
     *
     * @throws RuntimeException when DDL fails (e.g. missing CREATE privilege)
     */
    public static function ensureTables(): void
    {
        if (self::$installed) {
            return;
        }

        $model = new Model();
        if ($model->execSql('SET NAMES utf8mb4') === false) {
            throw new RuntimeException('DOCAC schema: SET NAMES failed.');
        }

        foreach (self::ddlStatements() as $sql) {
            if ($model->execSql($sql) === false) {
                throw new RuntimeException('DOCAC schema: CREATE TABLE failed.');
            }
        }

        self::$installed = true;
    }

    /**
     * @return list<string>
     */
    private static function ddlStatements(): array
    {
        return [
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `cours` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `certificats` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `nom_certificat` varchar(255) NOT NULL,
  `date_obtention` date NOT NULL,
  `organisation` varchar(255) NOT NULL,
  `fichier_path` varchar(512) DEFAULT NULL,
  `titre_cours` varchar(255) NOT NULL,
  `cree_le` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `demandes_certification` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `quizzes` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        ];
    }
}
