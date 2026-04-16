<?php
require_once "config/database.php";
require_once "models/RendezVous.php";
require_once "models/Bureau.php";

class RendezVousController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Affiche la page d'accueil (Front-office)
    public function showFront() {
        $bureauModel = new Bureau($this->db);
        $stmtBureaux = $bureauModel->getAll();

        // Récupération des rendez-vous récents pour le front
        $query = "SELECT r.*, b.nom AS bureau_nom FROM rendez_vous r 
                  LEFT JOIN bureau b ON r.bureau_id = b.id 
                  ORDER BY r.date_rdv DESC LIMIT 6";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        // CORRECTION : Utilise votre fichier views/front/index.php
        require_once "views/front/index.php"; 
    }

    // Affiche le formulaire de réservation
    public function bookForm() {
        $bureauModel = new Bureau($this->db);
        $stmtBureaux = $bureauModel->getAll();
        require_once "views/front/book.php";
    }

    // Enregistre la réservation
    public function storeBooking() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new RendezVous($this->db);
            $model->nom_etudiant = htmlspecialchars(strip_tags($_POST['nom_etudiant']));
            $model->id_bureau    = $_POST['id_bureau'];
            $model->objet         = htmlspecialchars(strip_tags($_POST['objet']));
            $model->date_rdv      = $_POST['date_rdv'];
            $model->heure_rdv     = $_POST['heure_rdv'];

            if ($model->create()) {
                header("Location: index.php?success=1");
                exit();
            }
        }
    }

    // Liste pour l'administration (Back-office)
    public function listAll() {
        // CORRECTION : "AS student_name" pour que list.php affiche le nom
        $query = "SELECT r.*, r.nom_etudiant AS student_name, b.nom as bureau_nom 
                  FROM rendez_vous r 
                  LEFT JOIN bureau b ON r.bureau_id = b.id 
                  ORDER BY r.date_rdv DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        require_once "views/back/rendez-vous/list.php";
    }

    public function updateStatus() {
        if (isset($_GET['id']) && isset($_GET['status'])) {
            $query = "UPDATE rendez_vous SET statut = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$_GET['status'], $_GET['id']]);
        }
        header("Location: index.php?page=back&module=appointments");
        exit();
    }
}