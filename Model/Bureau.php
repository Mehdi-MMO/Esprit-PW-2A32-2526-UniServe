<?php

declare(strict_types=1);

/**
 * Offices where rendez-vous can be booked (table bureaux).
 */
class Bureau
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
            'SELECT id, nom, localisation, type_service, actif FROM bureaux WHERE actif = 1 ORDER BY nom ASC'
        );

        return $statement->fetchAll();
    }

    public function findById(int|string $id): ?array
    {
        $statement = $this->model->query(
            'SELECT id, nom, localisation, type_service, actif FROM bureaux WHERE id = ? LIMIT 1',
            [(int) $id]
        );

        $row = $statement->fetch();

        return $row ?: null;
    }
}
