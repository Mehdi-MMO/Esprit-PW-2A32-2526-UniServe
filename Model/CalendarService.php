<?php

declare(strict_types=1);

class CalendarService
{
    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    public function getCalendarFeedForUser(int $userId, string $role, string $from, string $to, array $filters = []): array
    {
        $activeFilters = $this->normalizeFilters($filters);

        $events = [];
        $registeredEventIds = [];

        if (in_array('rendezvous', $activeFilters, true)) {
            $events = array_merge($events, $this->mapRendezVousEvents($userId, $from, $to));
        }

        if (in_array('events_registered', $activeFilters, true)) {
            $registeredEvents = $this->mapRegisteredEventEvents($userId, $from, $to);
            $events = array_merge($events, $registeredEvents);
            $registeredEventIds = array_map(
                static fn (array $event): int => (int) ($event['metadata']['source_id'] ?? 0),
                $registeredEvents
            );
        }

        if (in_array('events_public', $activeFilters, true)) {
            $events = array_merge($events, $this->mapPublicEventEvents($from, $to, $registeredEventIds));
        }

        if (in_array('certifications', $activeFilters, true) && $this->hasDemandesCertificationTable()) {
            $events = array_merge($events, $this->mapCertificationDemandeEvents($userId, $from, $to));
        }

        if ($this->hasDemoAgendaTable()) {
            $events = array_merge($events, $this->mapDemoAgendaEvents($userId, $from, $to, $activeFilters));
        }

        usort($events, static function (array $a, array $b): int {
            $aStart = (string) ($a['start'] ?? '');
            $bStart = (string) ($b['start'] ?? '');
            return strcmp($aStart, $bStart);
        });

        return [
            'events' => $events,
            'meta' => [
                'filters' => $activeFilters,
                'user_role' => $role,
            ],
        ];
    }

    private function normalizeFilters(array $filters): array
    {
        $allowed = ['rendezvous', 'events_registered', 'events_public', 'certifications'];
        if ($filters === []) {
            return $allowed;
        }

        $normalized = array_values(array_filter($filters, static fn (string $filter): bool => in_array($filter, $allowed, true)));
        return $normalized !== [] ? $normalized : $allowed;
    }

    private function mapRendezVousEvents(int $userId, string $from, string $to): array
    {
        $statement = $this->model->query(
            'SELECT rv.id, rv.motif, rv.date_debut, rv.date_fin, rv.statut, b.nom AS bureau_nom, b.localisation
             FROM rendez_vous rv
             INNER JOIN bureaux b ON b.id = rv.bureau_id
             WHERE rv.etudiant_id = ?
               AND rv.date_fin >= ?
               AND rv.date_debut <= ?
               AND rv.statut <> ?
             ORDER BY rv.date_debut ASC',
            [$userId, $from, $to, 'annule']
        );

        $rows = $statement->fetchAll();
        $events = [];

        foreach ($rows as $row) {
            $status = (string) ($row['statut'] ?? 'reserve');
            $rid = (int) ($row['id'] ?? 0);
            $rdvUrl = $status === 'reserve'
                ? '/rendezvous/editForm/' . $rid
                : '/rendezvous?focus=' . $rid;
            $events[] = [
                'id' => 'rdv-' . $rid,
                'source_type' => 'rendezvous',
                'title' => $this->buildRendezVousTitle((string) ($row['motif'] ?? 'Rendez-vous')),
                'start' => (string) ($row['date_debut'] ?? ''),
                'end' => (string) ($row['date_fin'] ?? ''),
                'status' => $status,
                'location' => trim(((string) ($row['bureau_nom'] ?? '')) . ' - ' . ((string) ($row['localisation'] ?? '')), ' -'),
                'owner_label' => 'Rendez-vous',
                'url' => $rdvUrl,
                'color' => $this->statusColor('rendezvous', $status),
                'is_readonly' => true,
                'metadata' => [
                    'source_id' => (int) ($row['id'] ?? 0),
                ],
            ];
        }

        return $events;
    }

    private function mapDemoAgendaEvents(int $userId, string $from, string $to, array $activeFilters): array
    {
        $where = ' WHERE user_id = ? AND start_at <= ? AND end_at >= ?';
        $params = [$userId, $to, $from];

        $allowedSources = array_values(array_filter($activeFilters, static fn (string $filter): bool => in_array($filter, ['rendezvous', 'events_registered', 'events_public', 'certifications'], true)));
        if ($allowedSources !== []) {
            $placeholders = implode(',', array_fill(0, count($allowedSources), '?'));
            $where .= ' AND source_type IN (' . $placeholders . ')';
            $params = array_merge($params, $allowedSources);
        }

        $statement = $this->model->query(
            'SELECT id, source_type, title, start_at, end_at, location, status, owner_label, color, url, is_readonly, sort_order
             FROM calendar_demo_items' . $where . '
             ORDER BY start_at ASC, sort_order ASC, id ASC',
            $params
        );

        $rows = $statement->fetchAll();
        $events = [];

        foreach ($rows as $row) {
            $sourceType = (string) ($row['source_type'] ?? 'events_public');
            $resolvedUrl = $this->resolveDemoItemUrl(
                $userId,
                $sourceType,
                trim((string) ($row['url'] ?? '')),
                (string) ($row['title'] ?? '')
            );
            $events[] = [
                'id' => 'demo-' . (int) ($row['id'] ?? 0),
                'source_type' => $sourceType,
                'title' => (string) ($row['title'] ?? 'Agenda de démonstration'),
                'start' => (string) ($row['start_at'] ?? ''),
                'end' => (string) ($row['end_at'] ?? ''),
                'status' => (string) ($row['status'] ?? ''),
                'location' => (string) ($row['location'] ?? ''),
                'owner_label' => (string) ($row['owner_label'] ?? 'Démo'),
                'url' => $resolvedUrl,
                'color' => (string) ($row['color'] ?? '#2f7df4'),
                'is_readonly' => (bool) ((int) ($row['is_readonly'] ?? 1)),
                'metadata' => [
                    'source_id' => (int) ($row['id'] ?? 0),
                    'demo' => true,
                ],
            ];
        }

        return $events;
    }

    private function mapRegisteredEventEvents(int $userId, string $from, string $to): array
    {
        $statement = $this->model->query(
            'SELECT e.id, e.titre, e.description, e.lieu, e.date_debut, e.date_fin, e.statut, c.nom AS club_nom, ie.statut AS inscription_statut
             FROM evenements e
             INNER JOIN inscriptions_evenement ie ON ie.evenement_id = e.id
             LEFT JOIN clubs c ON c.id = e.club_id
             WHERE ie.utilisateur_id = ?
               AND e.date_fin >= ?
               AND e.date_debut <= ?
               AND e.statut <> ?
             ORDER BY e.date_debut ASC',
            [$userId, $from, $to, 'annule']
        );

        $rows = $statement->fetchAll();
        $events = [];

        foreach ($rows as $row) {
            $status = (string) ($row['statut'] ?? 'planifie');
            $events[] = [
                'id' => 'evt-reg-' . (int) ($row['id'] ?? 0),
                'source_type' => 'events_registered',
                'title' => (string) ($row['titre'] ?? 'Evenement'),
                'start' => (string) ($row['date_debut'] ?? ''),
                'end' => (string) ($row['date_fin'] ?? ''),
                'status' => $status,
                'location' => (string) ($row['lieu'] ?? ''),
                'owner_label' => $this->eventOwnerLabel((string) ($row['club_nom'] ?? '')),
                'url' => '/evenements/show/' . (int) ($row['id'] ?? 0),
                'color' => $this->statusColor('events_registered', $status),
                'is_readonly' => true,
                'metadata' => [
                    'source_id' => (int) ($row['id'] ?? 0),
                    'inscription_statut' => (string) ($row['inscription_statut'] ?? 'inscrit'),
                ],
            ];
        }

        return $events;
    }

    private function mapPublicEventEvents(string $from, string $to, array $excludeEventIds): array
    {
        $whereNotIn = '';
        $params = [$from, $to, 'annule'];

        if ($excludeEventIds !== []) {
            $safeIds = array_values(array_filter(array_map('intval', $excludeEventIds), static fn (int $id): bool => $id > 0));
            if ($safeIds !== []) {
                $placeholders = implode(',', array_fill(0, count($safeIds), '?'));
                $whereNotIn = " AND e.id NOT IN ({$placeholders})";
                $params = array_merge($params, $safeIds);
            }
        }

        $statement = $this->model->query(
            'SELECT e.id, e.titre, e.lieu, e.date_debut, e.date_fin, e.statut, c.nom AS club_nom
             FROM evenements e
             LEFT JOIN clubs c ON c.id = e.club_id
             WHERE e.date_fin >= ?
               AND e.date_debut <= ?
               AND e.statut <> ?' . $whereNotIn . '
             ORDER BY e.date_debut ASC',
            $params
        );

        $rows = $statement->fetchAll();
        $events = [];

        foreach ($rows as $row) {
            $status = (string) ($row['statut'] ?? 'planifie');
            $events[] = [
                'id' => 'evt-pub-' . (int) ($row['id'] ?? 0),
                'source_type' => 'events_public',
                'title' => (string) ($row['titre'] ?? 'Evenement public'),
                'start' => (string) ($row['date_debut'] ?? ''),
                'end' => (string) ($row['date_fin'] ?? ''),
                'status' => $status,
                'location' => (string) ($row['lieu'] ?? ''),
                'owner_label' => $this->eventOwnerLabel((string) ($row['club_nom'] ?? '')),
                'url' => '/evenements/show/' . (int) ($row['id'] ?? 0),
                'color' => $this->statusColor('events_public', $status),
                'is_readonly' => true,
                'metadata' => [
                    'source_id' => (int) ($row['id'] ?? 0),
                ],
            ];
        }

        return $events;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapCertificationDemandeEvents(int $userId, string $from, string $to): array
    {
        $statement = $this->model->query(
            'SELECT id, nom_certificat, statut, date_souhaitee, heure_preferee, organisation
             FROM demandes_certification
             WHERE utilisateur_id = ?
               AND statut IN (\'en_attente\', \'quiz_envoye\')
               AND date_souhaitee >= DATE(?)
               AND date_souhaitee <= DATE(?)
             ORDER BY date_souhaitee ASC, id ASC',
            [$userId, $from, $to]
        );

        $rows = $statement->fetchAll();
        $events = [];

        foreach ($rows as $row) {
            $cid = (int) ($row['id'] ?? 0);
            $dateDay = (string) ($row['date_souhaitee'] ?? '');
            if ($dateDay === '') {
                continue;
            }
            [$startAt, $endAt] = $this->certificationSlotBounds($dateDay, (string) ($row['heure_preferee'] ?? ''));
            $status = (string) ($row['statut'] ?? 'en_attente');
            $nom = trim((string) ($row['nom_certificat'] ?? 'Certification'));
            $title = 'Certification : ' . mb_substr($nom !== '' ? $nom : 'Demande', 0, 52);
            $org = trim((string) ($row['organisation'] ?? ''));

            $events[] = [
                'id' => 'cert-' . $cid,
                'source_type' => 'certifications',
                'title' => $title,
                'start' => $startAt,
                'end' => $endAt,
                'status' => $status,
                'location' => $org,
                'owner_label' => 'Certification',
                'url' => '/certifications#mes-demandes',
                'color' => $this->statusColor('certifications', $status),
                'is_readonly' => true,
                'metadata' => [
                    'source_id' => $cid,
                ],
            ];
        }

        return $events;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function certificationSlotBounds(string $dateYmd, string $heurePreferee): array
    {
        $time = '09:00:00';
        $hp = trim($heurePreferee);
        if ($hp !== '' && preg_match('/(\d{1,2})[\s:hH](\d{2})/u', $hp, $m) === 1) {
            $hi = min(23, max(0, (int) $m[1]));
            $mi = min(59, max(0, (int) $m[2]));
            $time = sprintf('%02d:%02d:00', $hi, $mi);
        } elseif ($hp !== '' && preg_match('/(\d{1,2}):(\d{2})/', $hp, $m) === 1) {
            $hi = min(23, max(0, (int) $m[1]));
            $mi = min(59, max(0, (int) $m[2]));
            $time = sprintf('%02d:%02d:00', $hi, $mi);
        }

        $startTs = strtotime($dateYmd . ' ' . $time);
        if ($startTs === false) {
            $startTs = strtotime($dateYmd . ' 09:00:00') ?: time();
        }
        $endTs = $startTs + 3600;

        return [date('Y-m-d H:i:s', $startTs), date('Y-m-d H:i:s', $endTs)];
    }

    private function hasDemandesCertificationTable(): bool
    {
        try {
            $statement = $this->model->query(
                'SELECT COUNT(*) AS cnt
                 FROM INFORMATION_SCHEMA.TABLES
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = ?',
                ['demandes_certification']
            );
            $row = $statement->fetch();

            return (int) ($row['cnt'] ?? 0) > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    private function buildRendezVousTitle(string $motif): string
    {
        $trimmed = trim($motif);
        if ($trimmed === '') {
            return 'Rendez-vous UniServe';
        }

        return 'Rendez-vous: ' . mb_substr($trimmed, 0, 48);
    }

    private function eventOwnerLabel(string $clubName): string
    {
        $clubName = trim($clubName);
        if ($clubName === '') {
            return 'Evenement institutionnel';
        }

        return 'Club: ' . $clubName;
    }

    private function statusColor(string $sourceType, string $status): string
    {
        $palette = [
            'rendezvous' => [
                'reserve' => '#2f7df4',
                'confirme' => '#1fa971',
                'termine' => '#7b8797',
                'default' => '#2f7df4',
            ],
            'events_registered' => [
                'planifie' => '#f1a535',
                'ouvert' => '#2f7df4',
                'complet' => '#7056d8',
                'termine' => '#7b8797',
                'default' => '#2f7df4',
            ],
            'events_public' => [
                'planifie' => '#f1a535',
                'ouvert' => '#1fa971',
                'complet' => '#7056d8',
                'termine' => '#7b8797',
                'default' => '#1fa971',
            ],
            'certifications' => [
                'en_attente' => '#e67e22',
                'quiz_envoye' => '#8e44ad',
                'default' => '#c0392b',
            ],
        ];

        $sourceMap = $palette[$sourceType] ?? ['default' => '#2f7df4'];
        return $sourceMap[$status] ?? $sourceMap['default'];
    }

    private function hasDemoAgendaTable(): bool
    {
        $statement = $this->model->query(
            'SELECT COUNT(*) AS cnt
             FROM INFORMATION_SCHEMA.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?',
            ['calendar_demo_items']
        );

        $row = $statement->fetch();
        return (int) ($row['cnt'] ?? 0) > 0;
    }

    /**
     * Resolve demo agenda URLs: deep links pass through; generic /evenements or /rendezvous
     * are upgraded via titre/motif lookup when possible.
     */
    private function resolveDemoItemUrl(int $userId, string $sourceType, string $storedUrl, string $title): string
    {
        $path = trim($storedUrl);
        if ($path !== '' && $this->isDeepCalendarAppPath($path)) {
            return $path;
        }

        $titleTrim = trim($title);

        if ($sourceType === 'rendezvous') {
            if ($titleTrim !== '') {
                $statement = $this->model->query(
                    'SELECT id, statut FROM rendez_vous
                     WHERE etudiant_id = ? AND motif = ? AND statut <> ?
                     ORDER BY date_debut DESC
                     LIMIT 1',
                    [$userId, $titleTrim, 'annule']
                );
                $match = $statement->fetch();
                $rid = (int) ($match['id'] ?? 0);
                if ($rid > 0) {
                    $st = (string) ($match['statut'] ?? 'reserve');

                    return $st === 'reserve'
                        ? '/rendezvous/editForm/' . $rid
                        : '/rendezvous?focus=' . $rid;
                }
            }

            return '/rendezvous';
        }

        if (in_array($sourceType, ['events_registered', 'events_public'], true)) {
            if ($titleTrim !== '') {
                $statement = $this->model->query(
                    'SELECT id FROM evenements WHERE titre = ? AND statut <> ? ORDER BY date_debut DESC LIMIT 1',
                    [$titleTrim, 'annule']
                );
                $match = $statement->fetch();
                $eid = (int) ($match['id'] ?? 0);
                if ($eid > 0) {
                    return '/evenements/show/' . $eid;
                }
            }

            return '/evenements';
        }

        if ($sourceType === 'certifications') {
            return '/certifications#mes-demandes';
        }

        return $path !== '' ? $path : '/';
    }

    private function isDeepCalendarAppPath(string $path): bool
    {
        if ((bool) preg_match('#^/evenements/show/\d+$#', $path)) {
            return true;
        }
        if ((bool) preg_match('#^/rendezvous/editForm/\d+$#', $path)) {
            return true;
        }
        if ((bool) preg_match('#^/rendezvous\?focus=\d+$#', $path)) {
            return true;
        }
        if ((bool) preg_match('#^/certifications(/manage)?(\#.*)?$#', $path)) {
            return true;
        }

        return false;
    }
}
