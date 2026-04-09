<?php
require_once "config/database.php";
require_once "models/RendezVous.php";

class RendezVousController {
    private $db;
    private $model;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->model = new RendezVous($this->db);
    }

    public function listAll() {
        $stmt = $this->model->getAll();
        require_once "views/back/list.php";
    }

    public function showFront() {
        $stmt = $this->model->getAll();
        require_once "views/front/index.php";
    }

    public function createForm() {
        $errors = [];
        require_once "views/back/create.php";
    }

    public function store() {
        $errors = $this->validate($_POST);
        if (empty($errors)) {
            $this->model->titre = $_POST['titre'];
            $this->model->date_rdv = $_POST['date_rdv'];
            $this->model->heure_rdv = $_POST['heure_rdv'];
            $this->model->description = $_POST['description'];
            $this->model->create();
            header("Location: index.php?page=back");
            exit();
        }
        require_once "views/back/create.php";
    }

    public function editForm() {
        $errors = [];
        $this->model->id = $_GET['id'];
        $this->model->getOne();
        require_once "views/back/edit.php";
    }

    public function update() {
        $errors = $this->validate($_POST);
        if (empty($errors)) {
            $this->model->id = $_POST['id'];
            $this->model->titre = $_POST['titre'];
            $this->model->date_rdv = $_POST['date_rdv'];
            $this->model->heure_rdv = $_POST['heure_rdv'];
            $this->model->description = $_POST['description'];
            $this->model->update();
            header("Location: index.php?page=back");
            exit();
        }
        $this->model->id = $_POST['id'];
        require_once "views/back/edit.php";
    }

    public function delete() {
        $this->model->id = $_GET['id'];
        $this->model->delete();
        header("Location: index.php?page=back");
        exit();
    }

    private function validate($data) {
        $errors = [];
        if (empty(trim($data['titre']))) {
            $errors['titre'] = "Le titre est obligatoire.";
        } elseif (strlen($data['titre']) < 3) {
            $errors['titre'] = "Le titre doit contenir au moins 3 caractères.";
        }
        if (empty($data['date_rdv'])) {
            $errors['date_rdv'] = "La date est obligatoire.";
        }
        if (empty($data['heure_rdv'])) {
            $errors['heure_rdv'] = "L'heure est obligatoire.";
        }
        return $errors;
    }
}
?>