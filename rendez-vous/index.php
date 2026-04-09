<?php
require_once "controllers/RendezVousController.php";

$controller = new RendezVousController();

$page = $_GET['page'] ?? 'front';
$action = $_GET['action'] ?? '';

if ($page === 'front') {
    $controller->showFront();
} elseif ($page === 'back') {
    if ($action === 'create') {
        $controller->createForm();
    } elseif ($action === 'store') {
        $controller->store();
    } elseif ($action === 'edit') {
        $controller->editForm();
    } elseif ($action === 'update') {
        $controller->update();
    } elseif ($action === 'delete') {
        $controller->delete();
    } else {
        $controller->listAll();
    }
}
?>