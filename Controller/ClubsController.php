<?php

declare(strict_types=1);

/**
 * Legacy alias for English /clubs/* URLs. Club flows live in EvenementsController.
 */
require_once __DIR__ . '/EvenementsController.php';

class ClubsController extends Controller
{
    public function manage(): void
    {
        (new EvenementsController())->manageClubs();
    }

    public function createForm(): void
    {
        (new EvenementsController())->createClubForm();
    }

    public function create(): void
    {
        (new EvenementsController())->createClub();
    }

    public function editForm(int|string $id): void
    {
        (new EvenementsController())->editClubForm($id);
    }

    public function edit(int|string $id): void
    {
        (new EvenementsController())->editClub($id);
    }

    public function delete(int|string $id): void
    {
        (new EvenementsController())->deleteClub($id);
    }

    public function approve(int|string $id): void
    {
        (new EvenementsController())->approveClub($id);
    }

    public function reject(int|string $id): void
    {
        (new EvenementsController())->rejectClub($id);
    }

    public function index(): void
    {
        (new EvenementsController())->clubs();
    }

    public function show(int|string $id): void
    {
        (new EvenementsController())->clubShow($id);
    }

    public function createRequestForm(): void
    {
        (new EvenementsController())->createClubRequestForm();
    }

    public function createRequest(): void
    {
        (new EvenementsController())->createClubRequest();
    }
}
