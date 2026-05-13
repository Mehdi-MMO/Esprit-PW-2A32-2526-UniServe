<?php

declare(strict_types=1);

class DocacQuiz
{
    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    /**
     * @param list<array{question: string, options: list<string>, correct: int}> $questions
     */
    public function create(int $demandeId, string $coursTitre, array $questions): bool
    {
        $json = json_encode($questions, JSON_UNESCAPED_UNICODE);
        if (!is_string($json)) {
            return false;
        }

        $this->model->query(
            'INSERT INTO quizzes (demande_id, cours_titre, questions_json, statut)
             VALUES (?, ?, ?, "en_attente")
             ON DUPLICATE KEY UPDATE
                cours_titre = VALUES(cours_titre),
                questions_json = VALUES(questions_json),
                statut = "en_attente",
                score = NULL,
                passe_le = NULL',
            [$demandeId, $coursTitre, $json]
        );

        return true;
    }

    public function getByDemandeId(int $demandeId): ?array
    {
        $statement = $this->model->query('SELECT * FROM quizzes WHERE demande_id = ? LIMIT 1', [$demandeId]);
        $row = $statement->fetch();
        if ($row === false) {
            return null;
        }

        $row['questions'] = json_decode((string) ($row['questions_json'] ?? ''), true) ?: [];

        return $row;
    }

    public function getById(int $id): ?array
    {
        $statement = $this->model->query('SELECT * FROM quizzes WHERE id = ? LIMIT 1', [$id]);
        $row = $statement->fetch();
        if ($row === false) {
            return null;
        }

        $row['questions'] = json_decode((string) ($row['questions_json'] ?? ''), true) ?: [];

        return $row;
    }

    public function submit(int $id, int $score): bool
    {
        $statut = $score >= 3 ? 'accepte' : 'refuse';
        $statement = $this->model->query(
            'UPDATE quizzes SET score = ?, statut = ?, passe_le = NOW() WHERE id = ?',
            [$score, $statut, $id]
        );

        return $statement->rowCount() > 0;
    }

    public function hasOpenQuiz(int $demandeId): bool
    {
        $statement = $this->model->query(
            'SELECT id FROM quizzes WHERE demande_id = ? AND statut = "en_attente" LIMIT 1',
            [$demandeId]
        );

        return (bool) $statement->fetch();
    }
}
