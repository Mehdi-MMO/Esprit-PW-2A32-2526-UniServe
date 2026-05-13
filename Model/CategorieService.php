<?php

declare(strict_types=1);

/**
 * Service catalog rows in categories_service (demandes de service).
 */
class CategorieService
{
    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    public function findAllActive(): array
    {
        $statement = $this->model->query(
            'SELECT id, nom, description, actif FROM categories_service WHERE actif = 1 ORDER BY nom ASC'
        );

        return $statement->fetchAll();
    }

    public function findAll(): array
    {
        $statement = $this->model->query(
            'SELECT id, nom, description, actif FROM categories_service ORDER BY nom ASC'
        );

        return $statement->fetchAll();
    }

    public function findById(int|string $id): ?array
    {
        $statement = $this->model->query(
            'SELECT id, nom, description, actif FROM categories_service WHERE id = ? LIMIT 1',
            [(int) $id]
        );

        $row = $statement->fetch();

        return $row ?: null;
    }

    public function countActive(): int
    {
        $statement = $this->model->query(
            'SELECT COUNT(*) AS n FROM categories_service WHERE actif = 1'
        );

        $row = $statement->fetch();

        return (int) ($row['n'] ?? 0);
    }

    public function countDemandesForCategorie(int $categorieId): int
    {
        if ($categorieId <= 0) {
            return 0;
        }

        $statement = $this->model->query(
            'SELECT COUNT(*) AS n FROM demandes_service WHERE categorie_id = ?',
            [$categorieId]
        );

        return (int) ($statement->fetch()['n'] ?? 0);
    }

    public function create(string $nom, string $description, int $actif): int|false
    {
        $nom = trim($nom);
        $description = trim($description);
        if ($nom === '' || $description === '') {
            return false;
        }

        $actif = $actif === 1 ? 1 : 0;
        $this->model->query(
            'INSERT INTO categories_service (nom, description, actif) VALUES (?, ?, ?)',
            [$nom, $description, $actif]
        );

        return (int) $this->model->lastInsertId();
    }

    public function update(int $id, string $nom, string $description, int $actif): bool
    {
        if ($id <= 0) {
            return false;
        }

        $nom = trim($nom);
        $description = trim($description);
        if ($nom === '' || $description === '') {
            return false;
        }

        $actif = $actif === 1 ? 1 : 0;
        $statement = $this->model->query(
            'UPDATE categories_service SET nom = ?, description = ?, actif = ? WHERE id = ?',
            [$nom, $description, $actif, $id]
        );

        return $statement->rowCount() > 0;
    }

    /**
     * If demandes still reference this row, only sets actif = 0. Otherwise deletes the row.
     *
     * @return array{ok: bool, hard_deleted: bool, message: string}
     */
    public function deleteOrDeactivate(int $id): array
    {
        if ($id <= 0) {
            return ['ok' => false, 'hard_deleted' => false, 'message' => 'Identifiant invalide.'];
        }

        if ($this->findById($id) === null) {
            return ['ok' => false, 'hard_deleted' => false, 'message' => 'Catégorie introuvable.'];
        }

        $n = $this->countDemandesForCategorie($id);
        if ($n > 0) {
            $this->model->query(
                'UPDATE categories_service SET actif = 0 WHERE id = ?',
                [$id]
            );

            return [
                'ok' => true,
                'hard_deleted' => false,
                'message' => 'Des demandes utilisent encore cette catégorie : elle a été désactivée.',
            ];
        }

        $this->model->query('DELETE FROM categories_service WHERE id = ?', [$id]);

        return ['ok' => true, 'hard_deleted' => true, 'message' => 'Catégorie supprimée.'];
    }
}
