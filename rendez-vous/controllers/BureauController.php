<?php
require_once "config/database.php";
require_once "models/Bureau.php";

class BureauController {
    private $db;
    private $model;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->model = new Bureau($this->db);
    }

    public function listAll() {
        $stmt = $this->model->getAll();
        require_once "views/back/bureaux/list.php";
    }

    public function createForm() {
        require_once "views/back/bureaux/create.php";
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['nom']) || empty($_POST['localisation'])) {
                die("Error: Please fill in all required fields.");
            }
            $this->model->nom          = htmlspecialchars(strip_tags($_POST['nom']));
            $this->model->localisation = htmlspecialchars(strip_tags($_POST['localisation']));
            $this->model->responsable  = htmlspecialchars(strip_tags($_POST['responsable'] ?? ''));
            if ($this->model->create()) {
                header("Location: index.php?page=back&module=offices");
                exit();
            }
        }
    }

    public function editForm() {
        if (isset($_GET['id'])) {
            $data = $this->model->getOne($_GET['id']);
            if ($data) {
                require_once "views/back/bureaux/edit.php";
            }
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->id           = $_POST['id'];
            $this->model->nom          = htmlspecialchars(strip_tags($_POST['nom']));
            $this->model->localisation = htmlspecialchars(strip_tags($_POST['localisation']));
            $this->model->responsable  = htmlspecialchars(strip_tags($_POST['responsable'] ?? ''));
            if ($this->model->update()) {
                header("Location: index.php?page=back&module=offices");
                exit();
            }
        }
    }

    public function delete() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];

            // Vérifier si ce bureau a des rendez-vous liés
            $count = $this->model->countRendezVous($id);
            if ($count > 0) {
                // Rediriger avec un message d'erreur
                header("Location: index.php?page=back&module=offices&error=has_rdv&count=" . $count);
                exit();
            }

            if ($this->model->delete($id)) {
                header("Location: index.php?page=back&module=offices&success=deleted");
                exit();
            }
        }
    }
}
?>