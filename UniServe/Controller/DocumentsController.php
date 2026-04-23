<?php

declare(strict_types=1);

class DocumentsController extends Controller
{
    public function landing(): void
    {
        $this->render('documents/index', [
            'title' => 'Documents académiques',
        ]);
    }

    public function index(): void
    {
        $this->landing();
    }
}

