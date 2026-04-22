<?php

declare(strict_types=1);

class FrontofficeController extends Controller
{
    public function landing(): void
    {
        $this->dashboard();
    }

    public function dashboard(): void
    {
        $this->requireLogin();
        $this->requireRole(['etudiant', 'enseignant']);

        $this->render('frontoffice/dashboard');
    }
}

