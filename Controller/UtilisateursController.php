<?php

declare(strict_types=1);

class UtilisateursController extends Controller
{
    public function landing(): void
    {
        $this->render('utilisateurs/index', [
            'title' => 'Utilisateurs',
        ]);
    }

    public function index(): void
    {
        $this->landing();
    }
}

