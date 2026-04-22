<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/Model/Model.php';
require_once __DIR__ . '/core/App.php';

$page = $_GET['page'] ?? 'front';

require_once __DIR__ . '/Model/RendezVous/BureauModel.php';
require_once __DIR__ . '/Model/RendezVous/RendezVousModel.php';
require_once __DIR__ . '/Controller/RendezvousController.php';

$controller = new RendezvousController();

if ($page === 'back') {
    // ---- BACK OFFICE ----
    $module = $_GET['module'] ?? 'appointments';
    $action = $_GET['action'] ?? '';

    if ($module === 'offices') {
        if ($action === 'create')        $controller->createBureau();
        elseif ($action === 'store')     $controller->storeBureau();
        elseif ($action === 'edit')      $controller->editBureau();
        elseif ($action === 'update')    $controller->updateBureau();
        elseif ($action === 'delete')    $controller->deleteBureau();
        else                             $controller->bureaux();
    } else {
        if ($action === 'updateStatus')  $controller->updateStatus();
        else                             $controller->list();
    }
} else {
    // ---- FRONT OFFICE ----
    $action = $_GET['action'] ?? '';

    if ($action === 'book')              $controller->bookForm();
    elseif ($action === 'store_booking') $controller->storeBooking();
    else                                 $controller->index();
}
