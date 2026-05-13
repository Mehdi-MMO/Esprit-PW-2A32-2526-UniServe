<?php

declare(strict_types=1);

/**
 * Legacy alias for English /events/* URLs. All behavior lives in EvenementsController.
 */
require_once __DIR__ . '/EvenementsController.php';

class EventsController extends Controller
{
    public function landing(): void
    {
        (new EvenementsController())->landing();
    }

    public function clubs(): void
    {
        (new EvenementsController())->clubs();
    }

    public function clubShow(int|string $id): void
    {
        (new EvenementsController())->clubShow($id);
    }

    public function createClubRequestForm(): void
    {
        (new EvenementsController())->createClubRequestForm();
    }

    public function createClubRequest(): void
    {
        (new EvenementsController())->createClubRequest();
    }

    public function createEventRequestForm(): void
    {
        (new EvenementsController())->createEventRequestForm();
    }

    public function createEventRequest(): void
    {
        (new EvenementsController())->createEventRequest();
    }

    public function manage(): void
    {
        (new EvenementsController())->manage();
    }

    public function createForm(): void
    {
        (new EvenementsController())->createForm();
    }

    public function create(): void
    {
        (new EvenementsController())->create();
    }

    public function editForm(int|string $id): void
    {
        (new EvenementsController())->editForm($id);
    }

    public function edit(int|string $id): void
    {
        (new EvenementsController())->edit($id);
    }

    public function delete(int|string $id): void
    {
        (new EvenementsController())->delete($id);
    }

    public function approve(int|string $id): void
    {
        (new EvenementsController())->approveEvent($id);
    }

    public function reject(int|string $id): void
    {
        (new EvenementsController())->rejectEvent($id);
    }

    public function inscriptions(int|string $id): void
    {
        (new EvenementsController())->inscriptions($id);
    }

    public function checkIn(int|string $eventId, int|string $userId): void
    {
        (new EvenementsController())->checkIn($eventId, $userId);
    }

    public function index(): void
    {
        (new EvenementsController())->index();
    }

    public function show(int|string $id): void
    {
        (new EvenementsController())->show($id);
    }

    public function register(int|string $id): void
    {
        (new EvenementsController())->register($id);
    }

    public function unregister(int|string $id): void
    {
        (new EvenementsController())->unregister($id);
    }

    public function mesInscriptions(): void
    {
        (new EvenementsController())->mesInscriptions();
    }

    public function createRequestForm(): void
    {
        (new EvenementsController())->createEventRequestForm();
    }

    public function createRequest(): void
    {
        (new EvenementsController())->createEventRequest();
    }

    public function approveEvent(int|string $id): void
    {
        (new EvenementsController())->approveEvent($id);
    }

    public function rejectEvent(int|string $id): void
    {
        (new EvenementsController())->rejectEvent($id);
    }

    public function manageClubs(): void
    {
        (new EvenementsController())->manageClubs();
    }

    public function createClubForm(): void
    {
        (new EvenementsController())->createClubForm();
    }

    public function createClub(): void
    {
        (new EvenementsController())->createClub();
    }

    public function editClubForm(int|string $id): void
    {
        (new EvenementsController())->editClubForm($id);
    }

    public function editClub(int|string $id): void
    {
        (new EvenementsController())->editClub($id);
    }

    public function deleteClub(int|string $id): void
    {
        (new EvenementsController())->deleteClub($id);
    }

    public function approveClub(int|string $id): void
    {
        (new EvenementsController())->approveClub($id);
    }

    public function rejectClub(int|string $id): void
    {
        (new EvenementsController())->rejectClub($id);
    }

    public function paymentSuccess(): void
    {
        (new EvenementsController())->paymentSuccess();
    }

    public function paymentCancel(): void
    {
        (new EvenementsController())->paymentCancel();
    }
}
