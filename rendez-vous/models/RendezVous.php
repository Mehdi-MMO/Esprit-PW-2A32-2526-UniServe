<?php
class RendezVous {
    private $conn;
    private $table = "rendez_vous";

    public $id, $nom_etudiant, $id_bureau, $objet, $date_rdv, $heure_rdv, $statut;

    public function __construct($db) { $this->conn = $db; }

    public function create() {
        // CORRECTION : On s'assure que bureau_id est utilisé (clé étrangère SQL)
        $query = "INSERT INTO " . $this->table . " 
                  SET nom_etudiant=:nom, bureau_id=:b_id, objet=:obj, date_rdv=:d, heure_rdv=:h, statut='pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nom", $this->nom_etudiant);
        $stmt->bindParam(":b_id", $this->id_bureau);
        $stmt->bindParam(":obj", $this->objet);
        $stmt->bindParam(":d", $this->date_rdv);
        $stmt->bindParam(":h", $this->heure_rdv);
        return $stmt->execute();
    }
}