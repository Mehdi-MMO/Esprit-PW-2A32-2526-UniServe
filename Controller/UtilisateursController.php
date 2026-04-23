<?php

declare(strict_types=1);

class UtilisateursController extends Controller
{
    private function disabled(): void
    {
        $this->requireLogin();
        $this->redirect('/home');
    }

    public function landing(): void
    {
        $this->disabled();
    }

    public function index(): void
    {
        $this->disabled();
    }

    public function ajax(): void
    {
        $this->disabled();
    }

    public function create(): void
    {
        $this->disabled();
    }

    public function edit(int|string $id): void
    {
        $this->disabled();
    }

    public function delete(int|string $id): void
    {
        $this->disabled();
    }
}

