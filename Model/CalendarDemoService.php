<?php

declare(strict_types=1);

class CalendarDemoService
{
    private const TABLE_NAME = 'calendar_demo_items';
    private const SAMPLE_STUDENT_EMAIL = 'etudiant.uniserve@gmail.com';

    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    public function getSummary(): array
    {
        $this->ensureTableExists();

        $statement = $this->model->query(
            'SELECT COUNT(*) AS item_count,
                    COUNT(DISTINCT user_id) AS user_count,
                    MIN(start_at) AS first_item,
                    MAX(start_at) AS last_item
             FROM ' . self::TABLE_NAME
        );

        $row = $statement->fetch() ?: [];

        return [
            'item_count' => (int) ($row['item_count'] ?? 0),
            'user_count' => (int) ($row['user_count'] ?? 0),
            'first_item' => (string) ($row['first_item'] ?? ''),
            'last_item' => (string) ($row['last_item'] ?? ''),
            'target_user' => $this->resolveSampleStudentLabel(),
        ];
    }

    public function seedDemoAgenda(?int $createdBy = null): array
    {
        $this->ensureTableExists();

        $student = $this->resolveSampleStudent();
        if ($student === null) {
            return [
                'ok' => false,
                'message' => "Aucun compte étudiant de démonstration n'a été trouvé.",
                'created' => 0,
                'cleared' => 0,
            ];
        }

        $creatorId = $createdBy ?? $this->resolveCreatorId();
        if ($creatorId <= 0) {
            return [
                'ok' => false,
                'message' => 'Impossible de trouver un compte staff ou admin pour attribuer les données démo.',
                'created' => 0,
                'cleared' => 0,
            ];
        }

        $cleared = $this->clearDemoAgenda((int) $student['id']);

        $now = new DateTimeImmutable('now');

        $demoSlots = [
            [
                'type' => 'rendezvous',
                'title' => 'Entretien dossier de bourse',
                'start' => $now->modify('+1 day')->setTime(9, 0),
                'end' => $now->modify('+1 day')->setTime(9, 30),
                'location' => 'Scolarité - Bâtiment C - Bureau 3',
                'status' => 'confirme',
                'owner_label' => 'Rendez-vous',
                'color' => '#2f7df4',
            ],
            [
                'type' => 'rendezvous',
                'title' => 'Suivi administratif',
                'start' => $now->modify('+2 days')->setTime(14, 0),
                'end' => $now->modify('+2 days')->setTime(14, 45),
                'location' => 'Service financier - Bâtiment B - Rez-de-chaussée',
                'status' => 'reserve',
                'owner_label' => 'Rendez-vous',
                'color' => '#2f7df4',
            ],
            [
                'type' => 'events_registered',
                'title' => 'Atelier CV et entretien',
                'start' => $now->modify('+1 day')->setTime(16, 0),
                'end' => $now->modify('+1 day')->setTime(17, 30),
                'location' => 'Amphi A',
                'status' => 'ouvert',
                'owner_label' => 'Club Career Hub',
                'color' => '#f1a535',
            ],
            [
                'type' => 'events_registered',
                'title' => 'Conférence orientation',
                'start' => $now->modify('+3 days')->setTime(11, 0),
                'end' => $now->modify('+3 days')->setTime(12, 0),
                'location' => 'Salle polyvalente',
                'status' => 'planifie',
                'owner_label' => 'Club Culturel',
                'color' => '#7056d8',
            ],
            [
                'type' => 'events_public',
                'title' => 'Forum des associations',
                'start' => $now->modify('+4 days')->setTime(9, 30),
                'end' => $now->modify('+4 days')->setTime(11, 30),
                'location' => 'Cour centrale',
                'status' => 'ouvert',
                'owner_label' => 'Club Informatique',
                'color' => '#1fa971',
            ],
            [
                'type' => 'events_public',
                'title' => 'Soirée campus',
                'start' => $now->modify('+5 days')->setTime(18, 0),
                'end' => $now->modify('+5 days')->setTime(20, 0),
                'location' => 'Espaces extérieurs',
                'status' => 'complet',
                'owner_label' => 'Club Sportif',
                'color' => '#8d6df2',
            ],
        ];

        $this->model->query('START TRANSACTION');

        try {
            $created = 0;
            foreach ($demoSlots as $slot) {
                $this->model->query(
                    'INSERT INTO ' . self::TABLE_NAME . '
                        (user_id, source_type, title, start_at, end_at, location, status, owner_label, color, url, is_readonly, sort_order, created_by)
                     VALUES
                        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [
                        (int) $student['id'],
                        $slot['type'],
                        $slot['title'],
                        $slot['start']->format('Y-m-d H:i:s'),
                        $slot['end']->format('Y-m-d H:i:s'),
                        $slot['location'],
                        $slot['status'],
                        $slot['owner_label'],
                        $slot['color'],
                        $slot['type'] === 'rendezvous' ? '/rendezvous' : '/evenements',
                        1,
                        $created,
                        $creatorId,
                    ]
                );
                $created++;
            }

            $this->model->query('COMMIT');

            return [
                'ok' => true,
                'message' => 'L agenda de démonstration a été rempli pour ' . ($student['prenom'] ?? 'cet étudiant') . '.',
                'created' => $created,
                'cleared' => $cleared,
            ];
        } catch (Throwable $throwable) {
            $this->model->query('ROLLBACK');
            throw $throwable;
        }
    }

    public function clearDemoAgenda(?int $userId = null): int
    {
        $this->ensureTableExists();

        $params = [];
        $where = '';
        if ($userId !== null && $userId > 0) {
            $where = ' WHERE user_id = ?';
            $params[] = $userId;
        }

        $statement = $this->model->query('DELETE FROM ' . self::TABLE_NAME . $where, $params);
        return $statement->rowCount();
    }

    private function ensureTableExists(): void
    {
        $this->model->query(
            'CREATE TABLE IF NOT EXISTS ' . self::TABLE_NAME . ' (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT NOT NULL,
                source_type ENUM(\'rendezvous\', \'events_registered\', \'events_public\', \'certifications\') NOT NULL,
                title VARCHAR(150) NOT NULL,
                start_at DATETIME NOT NULL,
                end_at DATETIME NOT NULL,
                location VARCHAR(255) NULL DEFAULT NULL,
                status VARCHAR(50) NOT NULL DEFAULT \'\',
                owner_label VARCHAR(120) NULL DEFAULT NULL,
                color VARCHAR(20) NOT NULL DEFAULT \'#2f7df4\',
                url VARCHAR(255) NULL DEFAULT NULL,
                is_readonly TINYINT(1) NOT NULL DEFAULT 1,
                sort_order INT NOT NULL DEFAULT 0,
                created_by BIGINT NULL DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES utilisateurs(id) ON DELETE SET NULL,
                INDEX idx_demo_user_time (user_id, start_at),
                INDEX idx_demo_source (source_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $this->upgradeDemoSourceEnumIfNeeded();
    }

    private function upgradeDemoSourceEnumIfNeeded(): void
    {
        try {
            $statement = $this->model->query(
                'SELECT COLUMN_TYPE
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = ?
                   AND COLUMN_NAME = ?',
                [self::TABLE_NAME, 'source_type']
            );
            $row = $statement->fetch();
            $columnType = strtolower((string) ($row['COLUMN_TYPE'] ?? ''));
            if ($columnType === '' || str_contains($columnType, 'certifications')) {
                return;
            }

            $this->model->execSql(
                'ALTER TABLE ' . self::TABLE_NAME . ' MODIFY COLUMN source_type ENUM(\'rendezvous\',\'events_registered\',\'events_public\',\'certifications\') NOT NULL'
            );
        } catch (\Throwable) {
            // ignore: missing privileges, non-MySQL, or concurrent DDL
        }
    }

    private function resolveSampleStudent(): ?array
    {
        $statement = $this->model->query(
            'SELECT id, prenom, nom, email
             FROM utilisateurs
             WHERE email = ?
                OR role = ?
             ORDER BY CASE WHEN email = ? THEN 0 ELSE 1 END, id ASC
             LIMIT 1',
            [self::SAMPLE_STUDENT_EMAIL, 'etudiant', self::SAMPLE_STUDENT_EMAIL]
        );

        $row = $statement->fetch();
        return $row ?: null;
    }

    private function resolveSampleStudentLabel(): string
    {
        $student = $this->resolveSampleStudent();
        if ($student === null) {
            return 'Compte étudiant introuvable';
        }

        return trim((string) ($student['prenom'] ?? '') . ' ' . (string) ($student['nom'] ?? ''));
    }

    private function resolveCreatorId(): int
    {
        $statement = $this->model->query(
            'SELECT id
             FROM utilisateurs
             WHERE role IN (?, ?)
             ORDER BY FIELD(role, ?, ?), id ASC
             LIMIT 1',
            ['staff', 'admin', 'staff', 'admin']
        );

        $row = $statement->fetch();
        return (int) ($row['id'] ?? 0);
    }

}