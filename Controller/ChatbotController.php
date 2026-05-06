<?php
declare(strict_types=1);

require_once __DIR__ . '/../Model/Service.php';
require_once __DIR__ . '/../Model/Service/GroqService.php';

class ChatbotController extends Controller
{
    public function ask(): void
    {
        // On n'accepte que les requêtes POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        // Lire le payload JSON
        $input = json_decode(file_get_contents('php://input'), true);
        $message = trim($input['message'] ?? '');

        if ($message === '') {
            echo json_encode([
                'reply' => "Vous n'avez rien écrit ! Comment puis-je vous aider ?",
                'suggested_service_id' => null
            ]);
            return;
        }

        // Récupérer les services actifs pour donner le contexte à l'IA
        $serviceModel = new Service();
        $allServices = $serviceModel->getAll();
        $activeServices = array_filter($allServices, fn($s) => $s->getActif() == 1);

        // Appeler l'IA via Groq
        $aiService = new GroqService();
        $response = $aiService->askChatbot($message, $activeServices);

        // Renvoyer la réponse au navigateur
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function draft(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $keywords = trim($input['keywords'] ?? '');
        $serviceId = (int)($input['service_id'] ?? 0);

        if ($keywords === '' || $serviceId <= 0) {
            echo json_encode(['text' => 'Veuillez entrer quelques mots et sélectionner un service.']);
            return;
        }

        $serviceModel = new Service();
        $service = $serviceModel->getById($serviceId);
        $serviceName = $service ? $service->getNom() : 'Demande Générale';

        $aiService = new GroqService();
        $draftText = $aiService->generateDraft($keywords, $serviceName);

        header('Content-Type: application/json');
        echo json_encode(['text' => $draftText]);
    }
}
