<?php

declare(strict_types=1);

class RendezvousController extends Controller
{
    private function viewPath(): string
    {
        $role = (string) ($_SESSION['user']['role'] ?? '');
        if (in_array($role, ['staff', 'admin'], true)) {
            return 'backoffice/rendezvous/index';
        }

        return 'frontoffice/rendezvous/index';
    }

    public function landing(): void
    {
        $this->render($this->viewPath(), [
            'title' => 'Rendez-vous',
        ]);
    }

    public function index(): void
    {
        $this->landing();
    }
}

