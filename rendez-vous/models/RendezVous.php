<?php
class RendezVous {
    private $conn;
    private $table = "rendez_vous";

    public $id;
    public $titre;
    public $date_rdv;
    public $heure_rdv;
    public $description;

    public function __construct($db) {
        $this->conn = $db;
    }

    // READ ALL
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY date_rdv, heure_rdv";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // READ ONE
    public function getOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->titre = $row['titre'];
            $this->date_rdv = $row['date_rdv'];
            $this->heure_rdv = $row['heure_rdv'];
            $this->description = $row['description'];
        }
    }

    // CREATE
    public function create() {
        $query = "INSERT INTO " . $this->table . " (titre, date_rdv, heure_rdv, description)
                  VALUES (:titre, :date_rdv, :heure_rdv, :description)";
        $stmt = $this->conn->prepare($query);

        $this->titre = htmlspecialchars(strip_tags($this->titre));
        $this->date_rdv = htmlspecialchars(strip_tags($this->date_rdv));
        $this->heure_rdv = htmlspecialchars(strip_tags($this->heure_rdv));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":date_rdv", $this->date_rdv);
        $stmt->bindParam(":heure_rdv", $this->heure_rdv);
        $stmt->bindParam(":description", $this->description);

        return $stmt->execute();
    }

    // UPDATE
    public function update() {
        $query = "UPDATE " . $this->table . "
                  SET titre=:titre, date_rdv=:date_rdv, heure_rdv=:heure_rdv, description=:description
                  WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->titre = htmlspecialchars(strip_tags($this->titre));
        $this->date_rdv = htmlspecialchars(strip_tags($this->date_rdv));
        $this->heure_rdv = htmlspecialchars(strip_tags($this->heure_rdv));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":date_rdv", $this->date_rdv);
        $stmt->bindParam(":heure_rdv", $this->heure_rdv);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // DELETE
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }
}
?>