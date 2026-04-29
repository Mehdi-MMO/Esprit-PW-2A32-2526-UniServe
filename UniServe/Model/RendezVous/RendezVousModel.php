<?php
class RendezVousModel {
    private $conn;
    private $table = "rendez_vous";

    public $id, $nom_etudiant, $id_bureau, $objet, $date_rdv, $heure_rdv, $statut;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT r.*, b.nom AS bureau_nom
                  FROM " . $this->table . " r
                  LEFT JOIN bureau b ON r.bureau_id = b.id
                  ORDER BY r.date_rdv DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . "
                  SET nom_etudiant=:nom, bureau_id=:b_id, objet=:obj,
                      date_rdv=:d, heure_rdv=:h, statut='pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nom",   $this->nom_etudiant);
        $stmt->bindParam(":b_id",  $this->id_bureau);
        $stmt->bindParam(":obj",   $this->objet);
        $stmt->bindParam(":d",     $this->date_rdv);
        $stmt->bindParam(":h",     $this->heure_rdv);
        return $stmt->execute();
    }

    public function updateStatus($id, $status) {
        // Version SANS modifie_le (pour base sans cette colonne)
        $query = "UPDATE " . $this->table . " SET statut = :s WHERE id = :id";
        $stmt  = $this->conn->prepare($query);
        return $stmt->execute([':s' => $status, ':id' => $id]);
    }

    public function getOne($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>