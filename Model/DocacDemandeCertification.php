<?php

declare(strict_types=1);

class DocacDemandeCertification
{
    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    /**
     * @param array{nom_certificat: string, titre_cours?: string|null, organisation: string, date_souhaitee: string, heure_preferee?: string|null, notes?: string|null, fichier_path?: string|null} $data
     */
    public function store(int $utilisateurId, array $data): bool
    {
        if ($utilisateurId <= 0) {
            return false;
        }

        $this->model->query(
            'INSERT INTO demandes_certification
                (utilisateur_id, nom_certificat, titre_cours, organisation, date_souhaitee, heure_preferee, notes, fichier_path, statut)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, "en_attente")',
            [
                $utilisateurId,
                $data['nom_certificat'] ?? '',
                $data['titre_cours'] ?? null,
                $data['organisation'] ?? '',
                $data['date_souhaitee'] ?? '',
                $data['heure_preferee'] ?? null,
                $data['notes'] ?? null,
                $data['fichier_path'] ?? null,
            ]
        );

        return true;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAll(): array
    {
        $statement = $this->model->query(
            'SELECT d.*, CONCAT(u.prenom, " ", u.nom) AS demandeur_nom, u.email AS demandeur_email
             FROM demandes_certification d
             INNER JOIN utilisateurs u ON u.id = d.utilisateur_id
             ORDER BY d.soumise_le DESC'
        );

        return $statement->fetchAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAllByUser(int $utilisateurId): array
    {
        $statement = $this->model->query(
            'SELECT * FROM demandes_certification WHERE utilisateur_id = ? ORDER BY soumise_le DESC',
            [$utilisateurId]
        );

        return $statement->fetchAll();
    }

    public function countByStatut(string $statut): int
    {
        $statement = $this->model->query(
            'SELECT COUNT(*) AS c FROM demandes_certification WHERE statut = ?',
            [$statut]
        );

        return (int) ($statement->fetch()['c'] ?? 0);
    }

    public function updateStatut(int $id, string $statut, string $commentaire = ''): bool
    {
        $this->model->query(
            'UPDATE demandes_certification SET statut = ?, commentaire_admin = ?, traitee_le = NOW() WHERE id = ?',
            [$statut, $commentaire, $id]
        );

        return true;
    }

    public function markQuizEnvoye(int $id): bool
    {
        $statement = $this->model->query(
            'UPDATE demandes_certification SET statut = "quiz_envoye" WHERE id = ?',
            [$id]
        );

        return $statement->rowCount() > 0;
    }

    public function getById(int $id): ?array
    {
        $statement = $this->model->query('SELECT * FROM demandes_certification WHERE id = ? LIMIT 1', [$id]);
        $row = $statement->fetch();

        return $row ?: null;
    }
}
