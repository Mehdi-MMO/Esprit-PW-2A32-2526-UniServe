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
}
