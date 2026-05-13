<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/Model/Database.php';
Database::ensureEnvLoaded();
require_once __DIR__ . '/Controller/Controller.php';
require_once __DIR__ . '/Model/Model.php';
require_once __DIR__ . '/Model/AppUploads.php';
require_once __DIR__ . '/Model/CategorieService.php';
require_once __DIR__ . '/Model/DemandeDeService.php';
require_once __DIR__ . '/Model/Bureau.php';
require_once __DIR__ . '/Model/RendezVous.php';
require_once __DIR__ . '/Model/DashboardService.php';
require_once __DIR__ . '/Model/CalendarService.php';
require_once __DIR__ . '/Model/GroqClient.php';
require_once __DIR__ . '/Model/GroqLoginRiskService.php';
require_once __DIR__ . '/Model/LoginRiskService.php';
require_once __DIR__ . '/Model/NotificationModel.php';
require_once __DIR__ . '/Model/DemandeTextModeration.php';
require_once __DIR__ . '/Model/DocacDemandeCertification.php';
require_once __DIR__ . '/Model/UserAiSnapshot.php';
require_once __DIR__ . '/Model/CalendarBriefService.php';
require_once __DIR__ . '/Model/CalendarBriefCache.php';
require_once __DIR__ . '/Model/CalendarDemoService.php';
require_once __DIR__ . '/Model/User.php';
require_once __DIR__ . '/Model/TypeDocument.php';
require_once __DIR__ . '/Model/DemandeDocument.php';
require_once __DIR__ . '/Controller/App.php';

new App();
