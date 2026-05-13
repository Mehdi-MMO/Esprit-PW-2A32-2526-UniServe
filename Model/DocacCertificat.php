<?php

declare(strict_types=1);

class DocacCertificat
{
    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAllCertificats(): array
    {
        $statement = $this->model->query('SELECT * FROM certificats ORDER BY date_obtention DESC');

        return $statement->fetchAll();
    }

    public function addCertificat(string $nom, string $date, string $org, string $path, string $titreCours): bool
    {
        $this->model->query(
            'INSERT INTO certificats (nom_certificat, date_obtention, organisation, fichier_path, titre_cours)
             VALUES (?, ?, ?, ?, ?)',
            [$nom, $date, $org, $path !== '' ? $path : null, $titreCours]
        );

        return true;
    }

    public function updateCertificat(int $id, string $nom, string $date, string $org, string $path, string $titreCours): bool
    {
        if ($path === '') {
            $statement = $this->model->query(
                'UPDATE certificats SET nom_certificat = ?, date_obtention = ?, organisation = ?, titre_cours = ? WHERE id = ?',
                [$nom, $date, $org, $titreCours, $id]
            );
        } else {
            $statement = $this->model->query(
                'UPDATE certificats SET nom_certificat = ?, date_obtention = ?, organisation = ?, fichier_path = ?, titre_cours = ? WHERE id = ?',
                [$nom, $date, $org, $path, $titreCours, $id]
            );
        }

        return $statement->rowCount() > 0;
    }

    public function deleteCertificat(int $id): bool
    {
        $statement = $this->model->query('DELETE FROM certificats WHERE id = ?', [$id]);

        return $statement->rowCount() > 0;
    }

    public function getById(int $id): ?array
    {
        $statement = $this->model->query('SELECT * FROM certificats WHERE id = ? LIMIT 1', [$id]);
        $row = $statement->fetch();

        return $row ?: null;
    }
}
