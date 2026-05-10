<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/Model/Database.php';
require_once __DIR__ . '/Controller/Controller.php';
require_once __DIR__ . '/Model/Model.php';
require_once __DIR__ . '/Model/User.php';
require_once __DIR__ . '/Controller/App.php';

new App();
