<?php
class Bureau {
    private $conn;
    private $table = "bureau";

    public $id;
    public $nom;
    public $localisation;
    public $responsable;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getOne($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " SET nom=:nom, localisation=:loc, responsable=:resp";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":loc", $this->localisation);
        $stmt->bindParam(":resp", $this->responsable);
        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table . " SET nom=:nom, localisation=:loc, responsable=:resp WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":loc", $this->localisation);
        $stmt->bindParam(":resp", $this->responsable);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    public function countRendezVous($id) {
        $query = "SELECT COUNT(*) FROM rendez_vous WHERE bureau_id = ? AND statut != 'cancelled'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }

    public function deleteCancelledRendezVous($id) {
        $query = "DELETE FROM rendez_vous WHERE bureau_id = ? AND statut = 'cancelled'";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function delete($id) {
        $this->deleteCancelledRendezVous($id);
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
}
?>