<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Controller/Controller.php';
require_once __DIR__ . '/Model/Model.php';
require_once __DIR__ . '/Model/User.php';
require_once __DIR__ . '/Model/PasswordReset.php';
require_once __DIR__ . '/Model/MailService.php';
require_once __DIR__ . '/Model/DashboardService.php';
require_once __DIR__ . '/Model/CalendarService.php';
require_once __DIR__ . '/Model/CalendarDemoService.php';
require_once __DIR__ . '/Controller/App.php';

new App();
