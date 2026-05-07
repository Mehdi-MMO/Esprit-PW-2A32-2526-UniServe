<?php

declare(strict_types=1);

class BackofficeController extends Controller
{
    public function landing(): void
    {
        $this->dashboard();
    }

    public function dashboard(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $this->render('backoffice/dashboard', [
            'title' => 'Tableau de bord',
        ]);
    }
}

