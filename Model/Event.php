<?php

declare(strict_types=1);

class Event
{
    public const ALLOWED_STATUSES = ['planifie', 'ouvert', 'complet', 'termine', 'annule'];

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

    private function normalizeNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $intValue = (int) $value;
        return $intValue > 0 ? $intValue : null;
    }

    public static function allowedStatuses(): array
    {
        return self::ALLOWED_STATUSES;
    }

    public function getAllUpcoming(): array
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
                c.nom AS club_nom,
                e.cree_par AS club_owner_id,
                CONCAT(cu.prenom, " ", cu.nom) AS owner_nom,
                CONCAT(u.prenom, " ", u.nom) AS createur_nom,
                (SELECT COUNT(*) FROM inscriptions_evenement ie WHERE ie.evenement_id = e.id) AS inscriptions_count
             FROM evenements e
             LEFT JOIN clubs c ON c.id = e.club_id
             LEFT JOIN utilisateurs cu ON cu.id = e.cree_par
             INNER JOIN utilisateurs u ON u.id = e.cree_par
             WHERE e.date_fin >= NOW() AND e.statut = "ouvert" AND (c.id IS NULL OR c.statut_validation = "approuve")
             ORDER BY e.date_debut ASC'
        );

        return $statement->fetchAll();
    }

    public function getAllAdmin(): array
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
                c.nom AS club_nom,
                e.cree_par AS club_owner_id,
                CONCAT(cu.prenom, " ", cu.nom) AS owner_nom,
                CONCAT(u.prenom, " ", u.nom) AS createur_nom,
                (SELECT COUNT(*) FROM inscriptions_evenement ie WHERE ie.evenement_id = e.id) AS inscriptions_count
             FROM evenements e
             LEFT JOIN clubs c ON c.id = e.club_id
             LEFT JOIN utilisateurs cu ON cu.id = e.cree_par
             INNER JOIN utilisateurs u ON u.id = e.cree_par
             ORDER BY e.date_debut DESC'
        );

        return $statement->fetchAll();
    }

    public function getPendingForAdmin(): array
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
                c.nom AS club_nom,
                e.cree_par AS club_owner_id,
                CONCAT(cu.prenom, " ", cu.nom) AS owner_nom,
                CONCAT(u.prenom, " ", u.nom) AS createur_nom,
                (SELECT COUNT(*) FROM inscriptions_evenement ie WHERE ie.evenement_id = e.id) AS inscriptions_count
             FROM evenements e
             LEFT JOIN clubs c ON c.id = e.club_id
             LEFT JOIN utilisateurs cu ON cu.id = e.cree_par
             INNER JOIN utilisateurs u ON u.id = e.cree_par
             WHERE e.statut = "planifie"
             ORDER BY e.cree_le DESC'
        );

        return $statement->fetchAll();
    }

    public function findById(int|string $id): ?array
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
                c.nom AS club_nom,
                e.cree_par AS club_owner_id,
                c.statut_validation AS club_statut_validation,
                CONCAT(cu.prenom, " ", cu.nom) AS owner_nom,
                CONCAT(u.prenom, " ", u.nom) AS createur_nom,
                (SELECT COUNT(*) FROM inscriptions_evenement ie WHERE ie.evenement_id = e.id) AS inscriptions_count
             FROM evenements e
             LEFT JOIN clubs c ON c.id = e.club_id
             LEFT JOIN utilisateurs cu ON cu.id = e.cree_par
             INNER JOIN utilisateurs u ON u.id = e.cree_par
             WHERE e.id = ?
             LIMIT 1',
            [(int) $id]
        );

        $event = $statement->fetch();
        return $event ?: null;
    }

    public function findByOwner(int|string $ownerId): array
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
                c.nom AS club_nom,
                e.cree_par AS club_owner_id,
                CONCAT(cu.prenom, " ", cu.nom) AS owner_nom,
                CONCAT(u.prenom, " ", u.nom) AS createur_nom,
                (SELECT COUNT(*) FROM inscriptions_evenement ie WHERE ie.evenement_id = e.id) AS inscriptions_count
             FROM evenements e
             INNER JOIN clubs c ON c.id = e.club_id
             LEFT JOIN utilisateurs cu ON cu.id = e.cree_par
             INNER JOIN utilisateurs u ON u.id = e.cree_par
             WHERE e.cree_par = ?
             ORDER BY e.date_debut DESC',
            [(int) $ownerId]
        );

        return $statement->fetchAll();
    }

    public function create(array $data): int|false
    {
        $clubId = $this->normalizeNullableInt($data['club_id'] ?? null);
        $creePar = (int) ($data['cree_par'] ?? 0);
        $titre = trim((string) ($data['titre'] ?? ''));
        $description = $this->normalizeNullableString($data['description'] ?? null);
        $lieu = $this->normalizeNullableString($data['lieu'] ?? null);
        $dateDebut = trim((string) ($data['date_debut'] ?? ''));
        $dateFin = trim((string) ($data['date_fin'] ?? ''));
        $capacite = $this->normalizeNullableInt($data['capacite'] ?? null);
        $statut = (string) ($data['statut'] ?? 'planifie');

        if ($creePar <= 0 || $titre === '' || $dateDebut === '' || $dateFin === '') {
            return false;
        }

        if (!in_array($statut, self::ALLOWED_STATUSES, true)) {
            $statut = 'planifie';
        }

        $this->model->query(
            'INSERT INTO evenements
                (club_id, cree_par, titre, description, lieu, date_debut, date_fin, capacite, statut)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$clubId, $creePar, $titre, $description, $lieu, $dateDebut, $dateFin, $capacite, $statut]
        );

        return (int) $this->model->lastInsertId();
    }

    public function createForClubOwner(array $data, int|string $ownerId): int|false
    {
        $clubId = (int) ($data['club_id'] ?? 0);
        if ($clubId <= 0) {
            return false;
        }

        $statement = $this->model->query(
            'SELECT id FROM clubs WHERE id = ? AND cree_par = ? LIMIT 1',
            [$clubId, (int) $ownerId]
        );

        if (!$statement->fetch()) {
            return false;
        }

        $data['cree_par'] = (int) $ownerId;
        $data['statut'] = 'planifie';
        return $this->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        $eventId = (int) $id;
        if ($eventId <= 0) {
            return false;
        }

        $allowed = [
            'club_id' => 'club_id',
            'titre' => 'titre',
            'description' => 'description',
            'lieu' => 'lieu',
            'date_debut' => 'date_debut',
            'date_fin' => 'date_fin',
            'capacite' => 'capacite',
            'statut' => 'statut',
        ];

        $sets = [];
        $params = [];

        foreach ($allowed as $inputKey => $column) {
            if (!array_key_exists($inputKey, $data)) {
                continue;
            }

            $value = $data[$inputKey];

            if ($inputKey === 'club_id' || $inputKey === 'capacite') {
                $value = $this->normalizeNullableInt($value);
            } elseif ($inputKey === 'description' || $inputKey === 'lieu') {
                $value = $this->normalizeNullableString(is_string($value) ? $value : null);
            } elseif ($inputKey === 'titre' || $inputKey === 'date_debut' || $inputKey === 'date_fin') {
                $value = trim((string) $value);
                if ($value === '') {
                    continue;
                }
            } elseif ($inputKey === 'statut') {
                $value = (string) $value;
                if (!in_array($value, self::ALLOWED_STATUSES, true)) {
                    continue;
                }
            }

            $sets[] = "{$column} = ?";
            $params[] = $value;
        }

        if ($sets === []) {
            return false;
        }

        $params[] = $eventId;
        $statement = $this->model->query(
            'UPDATE evenements SET ' . implode(', ', $sets) . ' WHERE id = ?',
            $params
        );

        return $statement->rowCount() > 0;
    }

    public function delete(int|string $id): bool
    {
        $statement = $this->model->query(
            'DELETE FROM evenements WHERE id = ?',
            [(int) $id]
        );

        return $statement->rowCount() > 0;
    }

    public function countInscriptions(int|string $eventId): int
    {
        $statement = $this->model->query(
            'SELECT COUNT(*) AS cnt
             FROM inscriptions_evenement
             WHERE evenement_id = ?',
            [(int) $eventId]
        );

        $row = $statement->fetch();
        return (int) ($row['cnt'] ?? 0);
    }

    public function isUserRegistered(int|string $eventId, int|string $userId): bool
    {
        $statement = $this->model->query(
            'SELECT id
             FROM inscriptions_evenement
             WHERE evenement_id = ? AND utilisateur_id = ?
             LIMIT 1',
            [(int) $eventId, (int) $userId]
        );

        return (bool) $statement->fetch();
    }

    public function register(int|string $eventId, int|string $userId): bool
    {
        if ($this->isUserRegistered($eventId, $userId)) {
            return false;
        }

        $this->model->query(
            'INSERT INTO inscriptions_evenement (evenement_id, utilisateur_id, statut)
             VALUES (?, ?, "inscrit")',
            [(int) $eventId, (int) $userId]
        );

        return true;
    }

    public function unregister(int|string $eventId, int|string $userId): bool
    {
        $statement = $this->model->query(
            'DELETE FROM inscriptions_evenement
             WHERE evenement_id = ? AND utilisateur_id = ?',
            [(int) $eventId, (int) $userId]
        );

        return $statement->rowCount() > 0;
    }

    public function getInscriptions(int|string $eventId): array
    {
        $statement = $this->model->query(
            'SELECT
                ie.id,
                ie.evenement_id,
                ie.utilisateur_id,
                ie.statut,
                ie.inscrit_le,
                ie.presence_le,
                u.nom,
                u.prenom,
                u.email,
                u.role,
                u.matricule
             FROM inscriptions_evenement ie
             INNER JOIN utilisateurs u ON u.id = ie.utilisateur_id
             WHERE ie.evenement_id = ?
             ORDER BY ie.inscrit_le ASC',
            [(int) $eventId]
        );

        return $statement->fetchAll();
    }

    public function getUserInscriptions(int|string $userId): array
    {
        $statement = $this->model->query(
            'SELECT
                ie.id AS inscription_id,
                ie.statut AS inscription_statut,
                ie.inscrit_le,
                ie.presence_le,
                e.id,
                e.titre,
                e.lieu,
                e.date_debut,
                e.date_fin,
                e.capacite,
                e.statut AS evenement_statut,
                c.nom AS club_nom
             FROM inscriptions_evenement ie
             INNER JOIN evenements e ON e.id = ie.evenement_id
             LEFT JOIN clubs c ON c.id = e.club_id
             WHERE ie.utilisateur_id = ?
             ORDER BY e.date_debut DESC',
            [(int) $userId]
        );

        return $statement->fetchAll();
    }

    public function checkIn(int|string $eventId, int|string $userId): bool
    {
        $statement = $this->model->query(
            'UPDATE inscriptions_evenement
             SET statut = "present", presence_le = NOW()
             WHERE evenement_id = ? AND utilisateur_id = ?',
            [(int) $eventId, (int) $userId]
        );

        return $statement->rowCount() > 0;
    }

    public function setStatus(int|string $eventId, string $status): bool
    {
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            return false;
        }

        $statement = $this->model->query(
            'UPDATE evenements SET statut = ? WHERE id = ?',
            [$status, (int) $eventId]
        );

        return $statement->rowCount() > 0;
    }

    public function canUserManageEvent(int|string $eventId, int|string $userId): bool
    {
        $statement = $this->model->query(
            'SELECT e.id
             FROM evenements e
             LEFT JOIN clubs c ON c.id = e.club_id
             WHERE e.id = ?
               AND (
                 e.cree_par = ?
                 OR (c.id IS NOT NULL AND c.cree_par = ?)
               )
             LIMIT 1',
            [(int) $eventId, (int) $userId, (int) $userId]
        );

        return (bool) $statement->fetch();
    }

    public function approve(int|string $eventId, int|string $adminId): bool
    {
        $statement = $this->model->query(
            'UPDATE evenements
             SET statut = "ouvert"
             WHERE id = ?',
            [(int) $eventId]
        );

        return $statement->rowCount() > 0;
    }

    public function reject(int|string $eventId, int|string $adminId): bool
    {
        $statement = $this->model->query(
            'UPDATE evenements
             SET statut = "annule"
             WHERE id = ?',
            [(int) $eventId]
        );

        return $statement->rowCount() > 0;
    }
}

