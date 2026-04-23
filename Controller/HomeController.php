<?php

declare(strict_types=1);

class HomeController extends Controller
{
    public function landing(): void
    {
        $this->requireLogin();
        $this->render('home/placeholder');
    }
}
