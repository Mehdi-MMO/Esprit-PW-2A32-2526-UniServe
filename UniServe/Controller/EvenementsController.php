<?php

declare(strict_types=1);

class EvenementsController extends Controller
{
    public function landing(): void
    {
        $this->render('evenements/index', [
            'title' => 'Événements',
        ]);
    }

    public function index(): void
    {
        $this->landing();
    }
}

