<?php

declare(strict_types=1);

class RendezvousController extends Controller
{
    public function landing(): void
    {
        $this->render('rendezvous/index', [
            'title' => 'Rendez-vous',
        ]);
    }

    public function index(): void
    {
        $this->landing();
    }
}

