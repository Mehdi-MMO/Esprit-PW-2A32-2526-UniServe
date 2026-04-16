<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/Model/Model.php';
require_once __DIR__ . '/Model/User.php';
require_once __DIR__ . '/core/App.php';

new App();
