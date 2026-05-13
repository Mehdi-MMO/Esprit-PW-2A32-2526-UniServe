<?php

declare(strict_types=1);

/**
 * Compact cross-module context for Groq consumers (brief, chatbot).
 * No PII beyond what the student already sees in the UI (titres, statuts).
 */
class UserAiSnapshot
{
    private const MAX_SERVICE_ROWS = 8;
    private const MAX_DOC_ROWS = 8;
    private const MAX_CERT_ROWS = 6;
    private const MAX_STAFF_DEMANDE_QUEUE = 10;
    private const MAX_TITRE_LEN = 80;

    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    /**
     * @return array<string, mixed>
     */
    public static function build(int $userId, string $role): array
    {
        return (new self())->buildInternal($userId, $role);
    }

    /**
     * Stable digest for cache invalidation (brief).
     */
    public static function digest(array $snapshot): string
    {
        $json = json_encode(self::normalizeForDigest($snapshot), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return substr(hash('sha256', is_string($json) ? $json : ''), 0, 32);
    }

    /**
     * French lines for Groq user prompt / fallback (compact).
     *
     * @param array<string, mixed> $snapshot
     */
    public static function toFrenchBriefLines(array $snapshot): string
    {
        $lines = [];
        $svc = $snapshot['demandes_service'] ?? [];
        if (is_array($svc) && $svc !== []) {
            $lines[] = 'Demandes de service ouvertes (' . count($svc) . ') :';
            foreach ($svc as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $lines[] = '- #' . (int) ($row['id'] ?? 0) . ' « ' . self::clip((string) ($row['titre'] ?? '')) . ' » (' . (string) ($row['statut'] ?? '') . ', ' . (string) ($row['categorie_nom'] ?? '') . ')';
            }
        }
        $staffSvc = $snapshot['demandes_service_staff'] ?? null;
        if (is_array($staffSvc)) {
            $ea = (int) ($staffSvc['en_attente'] ?? 0);
            $ec = (int) ($staffSvc['en_cours'] ?? 0);
            $q = $staffSvc['queue'] ?? [];
            if ($ea + $ec > 0 || (is_array($q) && $q !== [])) {
                $lines[] = 'File admin — demandes de service : ' . $ea . ' en attente, ' . $ec . ' en cours (extrait file).';
                if (is_array($q)) {
                    foreach ($q as $row) {
                        if (!is_array($row)) {
                            continue;
                        }
                        $lines[] = '- #' . (int) ($row['id'] ?? 0) . ' « ' . self::clip((string) ($row['titre'] ?? '')) . ' » (' . (string) ($row['statut'] ?? '') . ', ' . (string) ($row['categorie_nom'] ?? '') . ')';
                    }
                }
            }
        }
        $docs = $snapshot['demandes_document'] ?? [];
        if (is_array($docs) && $docs !== []) {
            $lines[] = 'Demandes de documents en cours (' . count($docs) . ') :';
            foreach ($docs as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $lines[] = '- #' . (int) ($row['id'] ?? 0) . ' ' . self::clip((string) ($row['type_nom'] ?? 'Document')) . ' (' . (string) ($row['statut'] ?? '') . ')';
            }
        }
        $certs = $snapshot['certifications'] ?? [];
        if (is_array($certs) && $certs !== []) {
            $lines[] = 'Certifications (suivi) :';
            foreach ($certs as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $quiz = !empty($row['quiz_pending']) ? ' — quiz à compléter' : '';
                $lines[] = '- « ' . self::clip((string) ($row['nom_certificat'] ?? '')) . ' » ' . (string) ($row['statut'] ?? '') . (isset($row['date_souhaitee']) ? ', date souhaitée ' . (string) $row['date_souhaitee'] : '') . $quiz;
            }
        }
        $n = (int) ($snapshot['notifications_unread'] ?? 0);
        if ($n > 0) {
            $lines[] = 'Notifications non lues : ' . $n . '.';
        }

        return implode("\n", $lines);
    }

    /**
     * Single system-style block for the chatbot (truncated).
     */
    public static function toChatbotContext(array $snapshot, int $maxChars = 1200): string
    {
        $base = self::toFrenchBriefLines($snapshot);
        if ($base === '') {
            return 'Aucune demande ou certification en attente détectée dans les modules portail (vue synthétique).';
        }
        if (mb_strlen($base) <= $maxChars) {
            return 'Contexte portail (interne, ne pas citer comme source externe) :' . "\n" . $base;
        }

        return 'Contexte portail (interne, tronqué) :' . "\n" . mb_substr($base, 0, $maxChars) . '…';
    }

    /**
     * @return array<string, mixed>
     */
    private function buildInternal(int $userId, string $role): array
    {
        if ($userId <= 0) {
            return ['role' => $role, 'generated_at' => date('c')];
        }

        $out = [
            'role' => $role,
            'generated_at' => date('c'),
            'demandes_service' => [],
            'demandes_document' => [],
            'certifications' => [],
            'notifications_unread' => 0,
        ];

        try {
            $demSvc = new DemandeDeService();
            foreach ($demSvc->findAllForStudent($userId) as $row) {
                $st = (string) ($row['statut'] ?? '');
                if (in_array($st, ['traite', 'rejete'], true)) {
                    continue;
                }
                $out['demandes_service'][] = [
                    'id' => (int) ($row['id'] ?? 0),
                    'titre' => self::clip((string) ($row['titre'] ?? '')),
                    'statut' => $st,
                    'categorie_nom' => self::clip((string) ($row['categorie_nom'] ?? '')),
                    'soumise_le' => (string) ($row['soumise_le'] ?? ''),
                ];
                if (count($out['demandes_service']) >= self::MAX_SERVICE_ROWS) {
                    break;
                }
            }
        } catch (\Throwable) {
            // table missing in minimal installs
        }

        try {
            $demDoc = new DemandeDocument();
            foreach ($demDoc->findForStudent($userId) as $row) {
                $st = (string) ($row['statut'] ?? '');
                if (in_array($st, ['livre', 'rejete'], true)) {
                    continue;
                }
                $out['demandes_document'][] = [
                    'id' => (int) ($row['id'] ?? 0),
                    'statut' => $st,
                    'type_nom' => self::clip((string) ($row['type_nom'] ?? '')),
                    'demandee_le' => (string) ($row['demandee_le'] ?? ''),
                ];
                if (count($out['demandes_document']) >= self::MAX_DOC_ROWS) {
                    break;
                }
            }
        } catch (\Throwable) {
        }

        if ($this->hasTable('demandes_certification')) {
            try {
                $demCert = new DocacDemandeCertification();
                foreach ($demCert->getAllByUser($userId) as $row) {
                    $st = (string) ($row['statut'] ?? '');
                    if (in_array($st, ['accepte', 'refuse'], true)) {
                        continue;
                    }
                    $line = [
                        'id' => (int) ($row['id'] ?? 0),
                        'nom_certificat' => self::clip((string) ($row['nom_certificat'] ?? '')),
                        'statut' => $st,
                        'date_souhaitee' => (string) ($row['date_souhaitee'] ?? ''),
                    ];
                    if ($st === 'quiz_envoye' && $this->hasTable('quizzes')) {
                        $line['quiz_pending'] = $this->isQuizPending((int) ($row['id'] ?? 0));
                    }
                    $out['certifications'][] = $line;
                    if (count($out['certifications']) >= self::MAX_CERT_ROWS) {
                        break;
                    }
                }
            } catch (\Throwable) {
            }
        }

        try {
            $out['notifications_unread'] = (new NotificationModel())->countUnread($userId);
        } catch (\Throwable) {
        }

        if (in_array($role, ['staff', 'admin'], true)) {
            try {
                $demAdmin = new DemandeDeService();
                $counts = $demAdmin->countOpenDemandesByStatut();
                $queueRows = $demAdmin->findOpenQueueForStaffBrief(self::MAX_STAFF_DEMANDE_QUEUE);
                $queue = [];
                foreach ($queueRows as $r) {
                    if (!is_array($r)) {
                        continue;
                    }
                    $queue[] = [
                        'id' => (int) ($r['id'] ?? 0),
                        'titre' => self::clip((string) ($r['titre'] ?? '')),
                        'statut' => (string) ($r['statut'] ?? ''),
                        'categorie_nom' => self::clip((string) ($r['categorie_nom'] ?? '')),
                    ];
                }
                $out['demandes_service_staff'] = [
                    'en_attente' => (int) ($counts['en_attente'] ?? 0),
                    'en_cours' => (int) ($counts['en_cours'] ?? 0),
                    'queue' => $queue,
                ];
            } catch (\Throwable) {
                $out['demandes_service_staff'] = [
                    'en_attente' => 0,
                    'en_cours' => 0,
                    'queue' => [],
                ];
            }
        }

        return $out;
    }

    private function isQuizPending(int $demandeId): bool
    {
        if ($demandeId <= 0) {
            return false;
        }
        $statement = $this->model->query(
            'SELECT passe_le FROM quizzes WHERE demande_id = ? LIMIT 1',
            [$demandeId]
        );
        $row = $statement->fetch();
        if ($row === false) {
            return true;
        }

        return trim((string) ($row['passe_le'] ?? '')) === '';
    }

    private function hasTable(string $table): bool
    {
        try {
            $statement = $this->model->query(
                'SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
                [$table]
            );
            $row = $statement->fetch();

            return (int) ($row['cnt'] ?? 0) > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    private static function clip(string $s): string
    {
        $s = trim($s);
        if (mb_strlen($s) <= self::MAX_TITRE_LEN) {
            return $s;
        }

        return mb_substr($s, 0, self::MAX_TITRE_LEN - 1) . '…';
    }

    /**
     * @param array<string, mixed> $snapshot
     * @return array<string, mixed>
     */
    private static function normalizeForDigest(array $snapshot): array
    {
        $copy = $snapshot;
        unset($copy['generated_at']);
        ksort($copy);

        return $copy;
    }
}
