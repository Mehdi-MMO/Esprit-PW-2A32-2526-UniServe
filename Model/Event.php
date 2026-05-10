<?php

declare(strict_types=1);

class Event
{
    public const ALLOWED_STATUSES = ['planifie', 'ouvert', 'complet', 'termine', 'annule'];

    private Model $model;
    private ?bool $hasPrixTicketColumn = null;
    private ?bool $hasEventTicketsTable = null;

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

    public function supportsTicketPricing(): bool
    {
        return $this->hasPrixTicketColumn() || $this->hasEventTicketsTable();
    }

    private function hasPrixTicketColumn(): bool
    {
        if ($this->hasPrixTicketColumn !== null) {
            return $this->hasPrixTicketColumn;
        }

        try {
            $statement = $this->model->query('SHOW COLUMNS FROM evenements LIKE "prix_ticket"');
            $this->hasPrixTicketColumn = (bool) $statement->fetch();
        } catch (\Throwable $e) {
            $this->hasPrixTicketColumn = false;
        }

        return $this->hasPrixTicketColumn;
    }

    private function prixTicketSelectExpr(string $alias = 'e'): string
    {
        $hasColumn = $this->hasPrixTicketColumn();
        $hasTable = $this->hasEventTicketsTable();

        if ($hasTable && $hasColumn) {
            return 'COALESCE((SELECT et.prix_ticket FROM evenement_tickets et WHERE et.evenement_id = ' . $alias . '.id LIMIT 1), ' . $alias . '.prix_ticket, 0.00) AS prix_ticket,';
        }

        if ($hasTable) {
            return 'COALESCE((SELECT et.prix_ticket FROM evenement_tickets et WHERE et.evenement_id = ' . $alias . '.id LIMIT 1), 0.00) AS prix_ticket,';
        }

        if ($hasColumn) {
            return $alias . '.prix_ticket,';
        }

        return '0.00 AS prix_ticket,';
    }

    private function hasEventTicketsTable(): bool
    {
        if ($this->hasEventTicketsTable !== null) {
            return $this->hasEventTicketsTable;
        }

        try {
            $statement = $this->model->query('SHOW TABLES LIKE "evenement_tickets"');
            $exists = (bool) $statement->fetch();
            if (!$exists) {
                $this->model->query(
                    'CREATE TABLE IF NOT EXISTS evenement_tickets (
                        id BIGINT AUTO_INCREMENT PRIMARY KEY,
                        evenement_id BIGINT NOT NULL UNIQUE,
                        prix_ticket DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                        devise VARCHAR(10) NOT NULL DEFAULT "USD",
                        modifie_par BIGINT NULL DEFAULT NULL,
                        modifie_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (evenement_id) REFERENCES evenements(id) ON DELETE CASCADE,
                        FOREIGN KEY (modifie_par) REFERENCES utilisateurs(id)
                    )'
                );
                $statement = $this->model->query('SHOW TABLES LIKE "evenement_tickets"');
                $exists = (bool) $statement->fetch();
            }
            $this->hasEventTicketsTable = $exists;
        } catch (\Throwable $e) {
            $this->hasEventTicketsTable = false;
        }

        return $this->hasEventTicketsTable;
    }

    private function upsertTicketPriceForEvent(int $eventId, float $price, ?int $updatedBy = null): void
    {
        if (!$this->hasEventTicketsTable() || $eventId <= 0) {
            return;
        }

        $this->model->query(
            'INSERT INTO evenement_tickets (evenement_id, prix_ticket, devise, modifie_par)
             VALUES (?, ?, "USD", ?)
             ON DUPLICATE KEY UPDATE prix_ticket = VALUES(prix_ticket), modifie_par = VALUES(modifie_par)',
            [$eventId, max(0.0, round($price, 2)), $updatedBy]
        );
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
                ' . $this->prixTicketSelectExpr('e') . '
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
                ' . $this->prixTicketSelectExpr('e') . '
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
                ' . $this->prixTicketSelectExpr('e') . '
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
                ' . $this->prixTicketSelectExpr('e') . '
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
                ' . $this->prixTicketSelectExpr('e') . '
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
        $prixTicket = isset($data['prix_ticket']) ? max(0.0, round((float) $data['prix_ticket'], 2)) : 0.0;
        $statut = (string) ($data['statut'] ?? 'planifie');

        if ($creePar <= 0 || $titre === '' || $dateDebut === '' || $dateFin === '') {
            return false;
        }

        if (!in_array($statut, self::ALLOWED_STATUSES, true)) {
            $statut = 'planifie';
        }

        if ($this->hasPrixTicketColumn()) {
            $this->model->query(
                'INSERT INTO evenements
                    (club_id, cree_par, titre, description, lieu, date_debut, date_fin, capacite, prix_ticket, statut)
                 VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$clubId, $creePar, $titre, $description, $lieu, $dateDebut, $dateFin, $capacite, $prixTicket, $statut]
            );
            $newEventId = (int) $this->model->lastInsertId();
            if ($this->hasEventTicketsTable()) {
                $this->upsertTicketPriceForEvent($newEventId, $prixTicket, $creePar);
            }
            return $newEventId;
        }

        $this->model->query(
            'INSERT INTO evenements
                (club_id, cree_par, titre, description, lieu, date_debut, date_fin, capacite, statut)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$clubId, $creePar, $titre, $description, $lieu, $dateDebut, $dateFin, $capacite, $statut]
        );

        $newEventId = (int) $this->model->lastInsertId();
        $this->upsertTicketPriceForEvent($newEventId, $prixTicket, $creePar);
        return $newEventId;
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
            'prix_ticket' => 'prix_ticket',
            'statut' => 'statut',
        ];

        $priceUpdatedInTicketTable = false;
        if ($this->hasEventTicketsTable() && array_key_exists('prix_ticket', $data)) {
            $this->upsertTicketPriceForEvent($eventId, (float) $data['prix_ticket']);
            $priceUpdatedInTicketTable = true;
        }

        if (!$this->hasPrixTicketColumn()) {
            unset($allowed['prix_ticket']);
        }

        $sets = [];
        $params = [];

        foreach ($allowed as $inputKey => $column) {
            if (!array_key_exists($inputKey, $data)) {
                continue;
            }

            $value = $data[$inputKey];

            if ($inputKey === 'club_id' || $inputKey === 'capacite') {
                $value = $this->normalizeNullableInt($value);
            } elseif ($inputKey === 'prix_ticket') {
                $value = max(0.0, round((float) $value, 2));
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
            return $priceUpdatedInTicketTable;
        }

        $params[] = $eventId;
        $statement = $this->model->query(
            'UPDATE evenements SET ' . implode(', ', $sets) . ' WHERE id = ?',
            $params
        );

        return $statement->rowCount() > 0 || $priceUpdatedInTicketTable;
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
                ' . $this->prixTicketSelectExpr('e') . '
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
               AND (e.cree_par = ? OR e.cree_par = ?)
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

