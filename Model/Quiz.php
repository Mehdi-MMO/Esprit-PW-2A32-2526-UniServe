<?php
declare(strict_types=1);

class Quiz extends Model
{
    // ── Admin side ───────────────────────────────────────────────

    /** Create a quiz for a demande (called after AI generates questions) */
    public function create(int $demandeId, string $coursTitre, array $questions): bool
    {
        $sql = "INSERT INTO quizzes (demande_id, cours_titre, questions_json)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    cours_titre     = VALUES(cours_titre),
                    questions_json  = VALUES(questions_json),
                    statut          = 'en_attente',
                    score           = NULL,
                    passe_le        = NULL";
        $stmt = self::$db->prepare($sql);
        try {
            return $stmt->execute([$demandeId, $coursTitre, json_encode($questions, JSON_UNESCAPED_UNICODE)]);
        } catch (PDOException $e) {
            error_log('Quiz::create — ' . $e->getMessage());
            return false;
        }
    }

    /** Get quiz by demande_id */
    public function getByDemandeId(int $demandeId): ?array
    {
        $stmt = self::$db->prepare("SELECT * FROM quizzes WHERE demande_id = ?");
        $stmt->execute([$demandeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        $row['questions'] = json_decode($row['questions_json'], true) ?? [];
        return $row;
    }

    /** Get quiz by its own ID */
    public function getById(int $id): ?array
    {
        $stmt = self::$db->prepare("SELECT * FROM quizzes WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        $row['questions'] = json_decode($row['questions_json'], true) ?? [];
        return $row;
    }

    /** All quizzes with joined demande info */
    public function getAllWithDemande(): array
    {
        $stmt = self::$db->prepare("
            SELECT q.*, dc.nom_certificat, dc.organisation, dc.date_souhaitee
            FROM quizzes q
            JOIN demandes_certification dc ON dc.id = q.demande_id
            ORDER BY q.cree_le DESC
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['questions'] = json_decode($r['questions_json'], true) ?? [];
        }
        return $rows;
    }

    // ── Student side ─────────────────────────────────────────────

    /** Record student's answers, compute score, update statut */
    public function submit(int $id, int $score): bool
    {
        $statut = $score >= 3 ? 'accepte' : 'refuse';
        $sql = "UPDATE quizzes SET score = ?, statut = ?, passe_le = NOW() WHERE id = ?";
        $stmt = self::$db->prepare($sql);
        try {
            return $stmt->execute([$score, $statut, $id]);
        } catch (PDOException $e) {
            error_log('Quiz::submit — ' . $e->getMessage());
            return false;
        }
    }

    /** Check if a quiz exists and is pending for a given demande */
    public function hasOpenQuiz(int $demandeId): bool
    {
        $stmt = self::$db->prepare(
            "SELECT id FROM quizzes WHERE demande_id = ? AND statut = 'en_attente'"
        );
        $stmt->execute([$demandeId]);
        return (bool) $stmt->fetchColumn();
    }
}
