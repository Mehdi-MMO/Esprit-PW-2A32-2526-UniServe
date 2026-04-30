<?php

declare(strict_types=1);

class Club
{
    public const VALIDATION_STATUSES = ['en_attente', 'approuve', 'rejete'];

    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    private function normalizeNullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        return $trimmed === '' ? null : $trimmed;
    }

    public function getAll(): array
    {
        $statement = $this->model->query(
            'SELECT c.id, c.cree_par, c.nom, c.description, c.email_contact, c.actif, c.statut_validation,
                    CONCAT(u.prenom, " ", u.nom) AS owner_nom
             FROM clubs c
             INNER JOIN utilisateurs u ON u.id = c.cree_par
             WHERE c.actif = 1 AND c.statut_validation = "approuve"
             ORDER BY nom ASC'
        );

        return $statement->fetchAll();
    }

    public function getAllAdmin(): array
    {
        $statement = $this->model->query(
            'SELECT c.id, c.nom, c.description, c.email_contact, c.actif
             FROM clubs c
             ORDER BY c.id DESC'
        );

        return $statement->fetchAll();
    }

    public function getPendingForAdmin(): array
    {
        $statement = $this->model->query(
            'SELECT c.id, c.nom, c.description, c.email_contact, c.actif
             FROM clubs c
             WHERE c.actif = 0
             ORDER BY c.id DESC'
        );

        return $statement->fetchAll();
    }

    public function findById(int|string $id): ?array
    {
        $statement = $this->model->query(
            'SELECT c.id, c.nom, c.description, c.email_contact, c.actif
             FROM clubs c
             WHERE c.id = ?
             LIMIT 1',
            [(int) $id]
        );

        $club = $statement->fetch();
        return $club ?: null;
    }

    public function findByOwner(int|string $ownerId): array
    {
        $statement = $this->model->query(
            'SELECT c.id, c.nom, c.description, c.email_contact, c.actif
             FROM clubs c
             WHERE c.cree_par = ?
             ORDER BY c.id DESC',
            [(int) $ownerId]
        );

        return $statement->fetchAll();
    }

    public function isOwner(int|string $clubId, int|string $userId): bool
    {
        $statement = $this->model->query(
            'SELECT id FROM clubs WHERE id = ? AND cree_par = ? LIMIT 1',
            [(int) $clubId, (int) $userId]
        );

        return (bool) $statement->fetch();
    }

    public function getEventsForClub(int|string $clubId): array
    {
        $statement = $this->model->query(
            'SELECT
                e.id,
                e.club_id,
                e.cree_par,
                e.titre,
                e.description,
                e.lieu,
                e.date_debut,
                e.date_fin,
                e.capacite,
                e.statut,
                e.cree_le,
                (SELECT COUNT(*) FROM inscriptions_evenement ie WHERE ie.evenement_id = e.id) AS inscriptions_count
             FROM evenements e
             WHERE e.club_id = ?
               AND e.statut <> "planifie"
             ORDER BY e.date_debut DESC',
            [(int) $clubId]
        );

        return $statement->fetchAll();
    }

    public function create(array $data): int|false
    {
        $ownerId = (int) ($data['cree_par'] ?? 0);
        $nom = trim((string) ($data['nom'] ?? ''));
        $description = $this->normalizeNullableString($data['description'] ?? null);
        $emailContact = $this->normalizeNullableString($data['email_contact'] ?? null);
        $actif = !empty($data['actif']) ? 1 : 0;
        $statutValidation = (string) ($data['statut_validation'] ?? ($actif === 1 ? 'approuve' : 'en_attente'));

        if ($ownerId <= 0 || $nom === '') {
            return false;
        }

        if (!in_array($statutValidation, self::VALIDATION_STATUSES, true)) {
            $statutValidation = 'en_attente';
        }

        $this->model->query(
            'INSERT INTO clubs (cree_par, nom, description, email_contact, actif, statut_validation)
             VALUES (?, ?, ?, ?, ?, ?)',
            [$ownerId, $nom, $description, $emailContact, $actif, $statutValidation]
        );

        return (int) $this->model->lastInsertId();
    }

    public function createWithOwner(array $data, int $ownerId): int|false
    {
        $data['cree_par'] = $ownerId;
        $data['actif'] = 0;
        $data['statut_validation'] = 'en_attente';
        return $this->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        $clubId = (int) $id;
        if ($clubId <= 0) {
            return false;
        }

        $nom = trim((string) ($data['nom'] ?? ''));
        $description = $this->normalizeNullableString($data['description'] ?? null);
        $emailContact = $this->normalizeNullableString($data['email_contact'] ?? null);
        $actif = array_key_exists('actif', $data) ? (!empty($data['actif']) ? 1 : 0) : null;
        $statutValidation = (string) ($data['statut_validation'] ?? '');

        if ($nom === '') {
            return false;
        }

        $sets = ['nom = ?', 'description = ?', 'email_contact = ?'];
        $params = [$nom, $description, $emailContact];

        if ($actif !== null) {
            $sets[] = 'actif = ?';
            $params[] = $actif;
        }

        if ($statutValidation !== '' && in_array($statutValidation, self::VALIDATION_STATUSES, true)) {
            $sets[] = 'statut_validation = ?';
            $params[] = $statutValidation;
        }

        $params[] = $clubId;
        $statement = $this->model->query(
            'UPDATE clubs SET ' . implode(', ', $sets) . ' WHERE id = ?',
            $params
        );

        return $statement->rowCount() > 0;
    }

    public function delete(int|string $id): bool
    {
        $statement = $this->model->query(
            'DELETE FROM clubs WHERE id = ?',
            [(int) $id]
        );

        return $statement->rowCount() > 0;
    }

    public function setActiveStatus(int|string $id, bool $active): bool
    {
        $statement = $this->model->query(
            'UPDATE clubs SET actif = ? WHERE id = ?',
            [$active ? 1 : 0, (int) $id]
        );

        return $statement->rowCount() > 0;
    }

    public function approve(int|string $clubId, int|string $adminId): bool
    {
        $statement = $this->model->query(
            'UPDATE clubs
             SET actif = 1, statut_validation = "approuve"
             WHERE id = ?',
            [(int) $clubId]
        );

        return $statement->rowCount() > 0;
    }

    public function reject(int|string $clubId, int|string $adminId): bool
    {
        $statement = $this->model->query(
            'UPDATE clubs
             SET actif = 0, statut_validation = "rejete"
             WHERE id = ?',
            [(int) $clubId]
        );

        return $statement->rowCount() > 0;
    }
}

