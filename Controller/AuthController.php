<?php

declare(strict_types=1);

class AuthController extends Controller
{
    public function landing(): void
    {
        $this->render('auth/landing', [
            'title' => 'Bienvenue sur UniServe',
        ]);
    }

    public function login(): void
    {
        $this->render('auth/login');
    }
}
