<?php
require_once "controllers/RendezVousController.php";
require_once "controllers/BureauController.php";

$page   = $_GET['page']   ?? 'front';
$module = $_GET['module'] ?? 'appointments';
$action = $_GET['action'] ?? '';

if ($page === 'back') {
    if ($module === 'offices') {
        $controller = new BureauController();
        if ($action === 'create')       $controller->createForm();
        elseif ($action === 'store')    $controller->store();
        elseif ($action === 'edit')     $controller->editForm();
        elseif ($action === 'update')   $controller->update();
        elseif ($action === 'delete')   $controller->delete();
        else                            $controller->listAll();
    } else {
        $controller = new RendezVousController();
        if ($action === 'updateStatus') $controller->updateStatus();
        else                            $controller->listAll();
    }
} else {
    // C'est ici que l'erreur se produisait
    $controller = new RendezVousController();
    if ($action === 'book')              $controller->bookForm();
    elseif ($action === 'store_booking') $controller->storeBooking();
    else                                 $controller->showFront(); // Appelle la méthode ajoutée ci-dessus
}
?>