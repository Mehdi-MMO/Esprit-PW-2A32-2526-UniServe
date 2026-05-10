<?php

declare(strict_types=1);

class TypeDocument
{
    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findAllActive(): array
    {
        $statement = $this->model->query(
            'SELECT id, nom, description, actif
             FROM types_document
             WHERE actif = 1
             ORDER BY nom ASC'
        );

        return $statement->fetchAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findAll(): array
    {
        $statement = $this->model->query(
            'SELECT id, nom, description, actif
             FROM types_document
             ORDER BY nom ASC'
        );

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $statement = $this->model->query(
            'SELECT id, nom, description, actif FROM types_document WHERE id = ? LIMIT 1',
            [$id]
        );

        $row = $statement->fetch();

        return $row ?: null;
    }
}
