<?php

declare(strict_types=1);

class DemandesController extends Controller
{
    private function viewPath(): string
    {
        $role = (string) ($_SESSION['user']['role'] ?? '');
        if (in_array($role, ['staff', 'admin'], true)) {
            return 'backoffice/demandes/index';
        }

        return 'frontoffice/demandes/index';
    }

    public function landing(): void
    {
        $this->render($this->viewPath(), [
            'title' => 'Demandes de service',
        ]);
    }

    public function index(): void
    {
        $this->landing();
    }
}

