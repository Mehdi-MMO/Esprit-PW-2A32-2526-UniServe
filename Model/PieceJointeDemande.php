<?php

declare(strict_types=1);

require_once __DIR__ . '/AppUploads.php';

/**
 * Attachments for demandes_service (table pieces_jointes).
 */
class PieceJointeDemande
{
    public const UPLOAD_SUBDIR = 'demandes_service';

    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    public function uploadBaseDir(): string
    {
        return AppUploads::sub(self::UPLOAD_SUBDIR);
    }

    public function findByDemandeId(int $demandeId): array
    {
        if ($demandeId <= 0) {
            return [];
        }

        $statement = $this->model->query(
            'SELECT id, demande_service_id, nom_fichier, chemin_fichier, type_mime, televersee_le
             FROM pieces_jointes WHERE demande_service_id = ? ORDER BY id ASC',
            [$demandeId]
        );

        return $statement->fetchAll();
    }

    public function countForDemande(int $demandeId): int
    {
        if ($demandeId <= 0) {
            return 0;
        }

        $statement = $this->model->query(
            'SELECT COUNT(*) AS c FROM pieces_jointes WHERE demande_service_id = ?',
            [$demandeId]
        );

        $row = $statement->fetch();

        return (int) ($row['c'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $statement = $this->model->query(
            'SELECT id, demande_service_id, nom_fichier, chemin_fichier, type_mime, televersee_le
             FROM pieces_jointes WHERE id = ? LIMIT 1',
            [$id]
        );

        $row = $statement->fetch();

        return $row ?: null;
    }

    /**
     * @return int|false new row id
     */
    public function create(int $demandeId, string $nomFichier, string $cheminFichier, ?string $mime): int|false
    {
        if ($demandeId <= 0 || $nomFichier === '' || $cheminFichier === '') {
            return false;
        }

        $this->model->query(
            'INSERT INTO pieces_jointes (demande_service_id, nom_fichier, chemin_fichier, type_mime)
             VALUES (?, ?, ?, ?)',
            [$demandeId, $nomFichier, $cheminFichier, $mime]
        );

        return (int) $this->model->lastInsertId();
    }

    public function deleteById(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $statement = $this->model->query('DELETE FROM pieces_jointes WHERE id = ?', [$id]);

        return $statement->rowCount() > 0;
    }

    public function deleteAllForDemande(int $demandeId): void
    {
        if ($demandeId <= 0) {
            return;
        }

        $this->model->query('DELETE FROM pieces_jointes WHERE demande_service_id = ?', [$demandeId]);
    }

    /**
     * Remove DB rows and stored files for a demande (before deleting the demande row).
     */
    public function deleteAllForDemandeWithFiles(int $demandeId): void
    {
        foreach ($this->findByDemandeId($demandeId) as $row) {
            $this->unlinkStored((string) ($row['chemin_fichier'] ?? ''));
        }

        $this->deleteAllForDemande($demandeId);
    }

    public function unlinkStored(string $relative): void
    {
        if ($relative === '' || strpbrk($relative, '/\\') !== false) {
            return;
        }

        $path = $this->uploadBaseDir() . '/' . $relative;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
