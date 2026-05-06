<?php
// Model/Service/GroqService.php
declare(strict_types=1);

class GroqService
{
    private string $apiKey;
    private string $endpoint = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/ai.php';
        $this->apiKey = $config['groq_api_key'];
    }

    public function askChatbot(string $userMessage, array $availableServices): array
    {
        $servicesContext = "";
        foreach ($availableServices as $svc) {
            $servicesContext .= "- ID: " . $svc->getId() . " | Nom: " . $svc->getNom() . " | Description: " . $svc->getDescription() . "\n";
        }

        $systemPrompt = "Tu es l'assistant virtuel de UniServe, le portail étudiant. 
Ton rôle est d'aider les étudiants à trouver le bon service pour leurs demandes.
Voici la liste des services disponibles dans le système :\n" . $servicesContext . "

Ton objectif est de :
1. Répondre poliment et brièvement à l'étudiant en français.
2. Si sa question correspond à l'un des services disponibles, suggère-lui de faire une demande et renvoie l'ID exact de ce service.
3. Si sa question ne correspond à rien, explique-lui gentiment que tu ne peux l'aider que sur les services administratifs listés.

IMPORTANT : Tu DOIS répondre EXACTEMENT dans ce format JSON, et rien d'autre :
{
  \"reply\": \"Ta réponse texte ici\",
  \"suggested_service_id\": ID_EN_NOMBRE_OU_NULL
}";

        $payload = [
            "model" => "llama-3.3-70b-versatile", // Modèle Llama 3.3 rapide et intelligent
            "messages" => [
                ["role" => "system", "content" => $systemPrompt],
                ["role" => "user", "content" => $userMessage]
            ],
            "response_format" => ["type" => "json_object"],
            "temperature" => 0.2
        ];

        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        // Désactiver SSL verification en local
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['reply' => "Erreur de connexion à l'API Groq : $error", 'suggested_service_id' => null];
        }

        $decoded = json_decode($response, true);
        
        // Gestion des erreurs d'API
        if (isset($decoded['error'])) {
            $apiError = $decoded['error']['message'] ?? 'Erreur inconnue';
            return [
                'reply' => "⚠️ Erreur API Groq : " . $apiError,
                'suggested_service_id' => null
            ];
        }

        if (isset($decoded['choices'][0]['message']['content'])) {
            $aiText = $decoded['choices'][0]['message']['content'];
            
            $aiJson = json_decode(trim($aiText), true);
            if ($aiJson && isset($aiJson['reply'])) {
                return $aiJson;
            }
        }

        return [
            'reply' => "Je n'ai pas pu formuler une réponse correcte. Veuillez réessayer.",
            'suggested_service_id' => null
        ];
    }

    public function generateDraft(string $keywords, string $serviceName): string
    {
        $systemPrompt = "Tu es un assistant de rédaction professionnel pour un portail universitaire (UniServe).
L'étudiant souhaite faire une demande pour le service administratif suivant : '{$serviceName}'.
Voici les mots-clés ou le brouillon rapide qu'il a écrit : '{$keywords}'.

Ton objectif : Rédiger une description formelle, polie et claire (3 à 5 phrases maximum) qu'il pourra directement utiliser dans le formulaire.
Ne réponds qu'avec le texte généré, sans introduction, sans salutations (pas de 'Bonjour' au début), et sans guillemets.
Le texte doit aller droit au but.";

        $payload = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [
                ["role" => "system", "content" => $systemPrompt],
                ["role" => "user", "content" => "Rédige la description pour ma demande."]
            ],
            "temperature" => 0.4
        ];

        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) return "Erreur technique : Impossible de joindre l'IA.";

        $decoded = json_decode($response, true);
        
        if (isset($decoded['choices'][0]['message']['content'])) {
            return trim($decoded['choices'][0]['message']['content']);
        }

        return "Je n'ai pas réussi à générer le texte, veuillez réessayer.";
    }
    public function checkSpamOrGibberish(string $text): bool
    {
        $systemPrompt = "Tu es un modérateur de contenu intelligent. 
Ton rôle est de détecter si une description de demande administrative est réelle ou s'il s'agit de texte incohérent (gibberish), de spam, ou d'une suite de lettres sans aucun sens (ex: 'qsghdfshfdf').

Analyse le texte fourni.
Si le texte a un sens minimal en français ou anglais, réponds 'VALID'.
Si le texte est du charabia, des insultes ou totalement incohérent, réponds 'INVALID'.

Réponds uniquement par 'VALID' ou 'INVALID', sans ponctuation.";

        $payload = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [
                ["role" => "system", "content" => $systemPrompt],
                ["role" => "user", "content" => $text]
            ],
            "temperature" => 0.0
        ];

        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        $decoded = json_decode($response, true);
        $aiResponse = trim($decoded['choices'][0]['message']['content'] ?? '');

        return $aiResponse === 'VALID';
    }
}
