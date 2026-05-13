<?php

declare(strict_types=1);

/**
 * DOCAC `cours` table (certification prep).
 */
class DocacCours
{
    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAllCours(): array
    {
        $statement = $this->model->query('SELECT * FROM cours ORDER BY titre ASC');
        $rows = $statement->fetchAll();
        foreach ($rows as &$r) {
            $r['fichiers'] = !empty($r['fichiers_json'])
                ? (json_decode((string) $r['fichiers_json'], true) ?: [])
                : [];
        }

        return $rows;
    }

    public function getCoursParTitre(string $titre): ?array
    {
        $statement = $this->model->query('SELECT * FROM cours WHERE titre = ? LIMIT 1', [$titre]);
        $row = $statement->fetch();
        if ($row === false) {
            return null;
        }

        $row['fichiers'] = !empty($row['fichiers_json'])
            ? (json_decode((string) $row['fichiers_json'], true) ?: [])
            : [];

        return $row;
    }

    public function coursExiste(string $titre): bool
    {
        return $this->getCoursParTitre($titre) !== null;
    }

    /**
     * @param array<string, mixed> $data
     * @param list<array{nom: string, path: string}> $fichiers
     */
    public function createCours(array $data, string $imagePath, array $fichiers): bool
    {
        $this->model->query(
            'INSERT INTO cours (titre, description, formateur, contenu, image_path, fichiers_json)
             VALUES (?, ?, ?, ?, ?, ?)',
            [
                trim((string) ($data['titre'] ?? '')),
                trim((string) ($data['description'] ?? '')),
                trim((string) ($data['formateur'] ?? '')),
                trim((string) ($data['contenu'] ?? '')),
                $imagePath !== '' ? $imagePath : null,
                $fichiers !== [] ? json_encode($fichiers, JSON_UNESCAPED_UNICODE) : null,
            ]
        );

        return true;
    }

    /**
     * @param array<string, mixed> $data
     * @param list<array{nom: string, path: string}> $fichiers
     */
    public function updateCours(array $data, string $oldTitre, string $imagePath, array $fichiers): bool
    {
        if ($imagePath !== '') {
            $statement = $this->model->query(
                'UPDATE cours SET titre = ?, description = ?, formateur = ?, contenu = ?, image_path = ?, fichiers_json = ?
                 WHERE titre = ?',
                [
                    trim((string) ($data['titre'] ?? '')),
                    trim((string) ($data['description'] ?? '')),
                    trim((string) ($data['formateur'] ?? '')),
                    trim((string) ($data['contenu'] ?? '')),
                    $imagePath,
                    json_encode($fichiers, JSON_UNESCAPED_UNICODE),
                    $oldTitre,
                ]
            );
        } else {
            $statement = $this->model->query(
                'UPDATE cours SET titre = ?, description = ?, formateur = ?, contenu = ?, fichiers_json = ?
                 WHERE titre = ?',
                [
                    trim((string) ($data['titre'] ?? '')),
                    trim((string) ($data['description'] ?? '')),
                    trim((string) ($data['formateur'] ?? '')),
                    trim((string) ($data['contenu'] ?? '')),
                    json_encode($fichiers, JSON_UNESCAPED_UNICODE),
                    $oldTitre,
                ]
            );
        }

        return $statement->rowCount() > 0;
    }

    public function deleteCours(string $titre): bool
    {
        $statement = $this->model->query('DELETE FROM cours WHERE titre = ?', [$titre]);

        return $statement->rowCount() > 0;
    }
}
