<?php

declare(strict_types=1);

class UsersController extends Controller
{
    public function landing(): void
    {
        $this->requireLogin();
        $this->redirect('/home');
    }

    public function profile(): void
    {
        $this->requireLogin();
        $this->redirect('/home');
    }
}

