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

    /**
     * @return list<array<string, mixed>>
     */
    public function findAllOrdered(): array
    {
        $statement = $this->model->query(
            'SELECT id, nom, localisation, type_service, actif FROM bureaux ORDER BY actif DESC, nom ASC'
        );

        return $statement->fetchAll();
    }

    /**
     * @param array{nom: string, localisation: string, type_service: string, actif: int} $data
     */
    public function create(array $data): int
    {
        $this->model->query(
            'INSERT INTO bureaux (nom, localisation, type_service, actif) VALUES (?, ?, ?, ?)',
            [
                $data['nom'],
                $data['localisation'],
                $data['type_service'],
                $data['actif'],
            ]
        );

        return (int) $this->model->lastInsertId();
    }

    /**
     * @param array{nom: string, localisation: string, type_service: string, actif: int} $data
     */
    public function update(int $id, array $data): void
    {
        $this->model->query(
            'UPDATE bureaux SET nom = ?, localisation = ?, type_service = ?, actif = ? WHERE id = ?',
            [
                $data['nom'],
                $data['localisation'],
                $data['type_service'],
                $data['actif'],
                $id,
            ]
        );
    }
}
