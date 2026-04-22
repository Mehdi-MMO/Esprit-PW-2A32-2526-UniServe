<?php

declare(strict_types=1);

class DemandesController extends Controller
{
    public function landing(): void
    {
        $this->render('demandes/index', [
            'title' => 'Demandes de service',
        ]);
    }

    public function index(): void
    {
        $this->landing();
    }
}

