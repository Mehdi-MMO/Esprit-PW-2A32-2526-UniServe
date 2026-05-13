<?php

declare(strict_types=1);

/**
 * Quiz question generation: Ollama (DOCAC default) with Groq + static fallbacks.
 */
class DocacQuizAiService
{
    private string $projectRoot;

    public function __construct()
    {
        $this->projectRoot = dirname(__DIR__);
    }

    /**
     * @param list<array{name: string, text: string}> $pdfFiles
     * @return list<array{question: string, options: list<string>, correct: int}>
     */
    public function generateFiveQuestions(
        string $coursTitre,
        string $description,
        string $contenu,
        string $nomCertif,
        array $pdfFiles = [],
        ?array $demandeContext = null
    ): array {
        $mode = strtolower(trim((string) (getenv('DOCAC_QUIZ_AI') ?: 'auto')));

        if ($mode === 'groq') {
            $g = $this->tryGroq($coursTitre, $description, $contenu, $nomCertif, $pdfFiles, $demandeContext);

            return $this->ensureFiveQuestions(array_merge($g, $this->fallbackQuestions($coursTitre)), $coursTitre);
        }

        if ($mode === 'ollama') {
            $o = $this->tryOllama($coursTitre, $description, $contenu, $nomCertif, $pdfFiles);
            $g = $this->tryGroq($coursTitre, $description, $contenu, $nomCertif, $pdfFiles, $demandeContext);
            $merged = array_merge($o, $g, $this->fallbackQuestions($coursTitre));

            return $this->ensureFiveQuestions($merged, $coursTitre);
        }

        // auto: Ollama first, then Groq, then fallback
        $o = $this->tryOllama($coursTitre, $description, $contenu, $nomCertif, $pdfFiles);
        if (count($o) >= 5) {
            return $this->ensureFiveQuestions($o, $coursTitre);
        }

        $g = $this->tryGroq($coursTitre, $description, $contenu, $nomCertif, $pdfFiles, $demandeContext);
        $merged = array_merge($o, $g, $this->fallbackQuestions($coursTitre));

        return $this->ensureFiveQuestions($merged, $coursTitre);
    }

    /**
     * @param list<mixed> $raw
     * @return list<array{question: string, options: list<string>, correct: int}>
     */
    private function ensureFiveQuestions(array $raw, string $coursTitre): array
    {
        $normalized = $this->normalizeQuestions($raw);
        if (count($normalized) >= 5) {
            return array_slice($normalized, 0, 5);
        }

        $merged = array_merge($normalized, $this->fallbackQuestions($coursTitre));

        return array_slice($this->normalizeQuestions($merged), 0, 5);
    }

    /**
     * @param list<array{nom: string, path: string}> $fichiers Relatif à Model/uploads/cours/
     * @return list<array{name: string, text: string}>
     */
    public function collectPdfSnippets(array $fichiers): array
    {
        $pdfs = [];
        $baseDir = AppUploads::sub('cours') . '/';

        foreach ($fichiers as $f) {
            $path = $baseDir . ($f['path'] ?? '');
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (!is_file($path)) {
                continue;
            }

            $text = '';
            if ($ext === 'pdf') {
                $text = $this->extractPdfText($path);
            } elseif (in_array($ext, ['txt', 'md'], true)) {
                $text = (string) (@file_get_contents($path) ?: '');
            }

            if ($text !== '') {
                $pdfs[] = [
                    'name' => (string) ($f['nom'] ?? basename($path)),
                    'text' => substr($text, 0, 3000),
                ];
            }
            if (count($pdfs) >= 3) {
                break;
            }
        }

        return $pdfs;
    }

    private function extractPdfText(string $path): string
    {
        if (!is_readable($path)) {
            return '';
        }

        $escaped = escapeshellarg($path);
        if (PHP_OS_FAMILY !== 'Windows') {
            $out = @shell_exec("pdftotext {$escaped} - 2>/dev/null");
            if (!empty($out) && strlen(trim((string) $out)) > 30) {
                return trim(substr((string) $out, 0, 5000));
            }
        }

        $winPaths = [
            'C:\\poppler\\Library\\bin\\pdftotext.exe',
            'C:\\poppler\\bin\\pdftotext.exe',
            'C:\\Program Files\\poppler\\bin\\pdftotext.exe',
        ];
        foreach ($winPaths as $pdftotext) {
            if (is_file($pdftotext)) {
                $cmd = '"' . $pdftotext . '" ' . $escaped . ' -';
                $out = @shell_exec($cmd);
                if (!empty($out) && strlen(trim((string) $out)) > 30) {
                    return trim(substr((string) $out, 0, 5000));
                }
            }
        }

        $content = @file_get_contents($path);
        if ($content === false || $content === '') {
            return '';
        }

        $text = '';
        if (preg_match_all('/stream\s*(.*?)\s*endstream/s', $content, $streams)) {
            foreach ($streams[1] as $stream) {
                $decoded = @gzuncompress($stream);
                $data = $decoded !== false ? $decoded : $stream;

                if (preg_match_all('/BT\s*(.*?)\s*ET/s', $data, $m)) {
                    foreach ($m[1] as $block) {
                        if (preg_match_all('/\(([^)]{1,500})\)\s*Tj/s', $block, $s)) {
                            foreach ($s[1] as $str) {
                                $clean = preg_replace('/[^ -~À-ÿ]/', ' ', $str);
                                if (strlen(trim($clean)) > 2) {
                                    $text .= trim($clean) . ' ';
                                }
                            }
                        }
                    }
                }
            }
        }

        if (strlen(trim($text)) < 50) {
            preg_match_all('/[a-zA-ZÀ-ÿ][a-zA-ZÀ-ÿ0-9\s\.\,\:\;\-\+\=]{15,}/', $content, $m2);
            $text = implode(' ', array_slice($m2[0], 0, 200));
        }

        $text = preg_replace('/\s+/', ' ', $text);

        return trim(substr($text, 0, 5000));
    }

    /**
     * @param list<array{name: string, text: string}> $pdfFiles
     * @return list<array{question: string, options: list<string>, correct: int}>
     */
    private function tryOllama(
        string $coursTitre,
        string $description,
        string $contenu,
        string $nomCertif,
        array $pdfFiles
    ): array {
        $ollamaUrl = trim((string) (getenv('OLLAMA_URL') ?: 'http://127.0.0.1:11434'));
        $ollamaModel = trim((string) (getenv('OLLAMA_MODEL') ?: 'qwen2.5:14b'));

        $context = '';
        if ($description !== '') {
            $context .= "DESCRIPTION : {$description}\n\n";
        }
        if ($contenu !== '') {
            $context .= "CONTENU DU COURS :\n{$contenu}\n\n";
        }
        foreach ($pdfFiles as $pdf) {
            if (!empty($pdf['text'])) {
                $context .= '=== DOCUMENT : ' . ($pdf['name'] ?? '') . " ===\n{$pdf['text']}\n\n";
            }
        }
        $hasContent = $context !== '';

        $prompt = "Tu es un formateur expert. Génère exactement 5 questions QCM en français.\n\n";
        $prompt .= "COURS : {$coursTitre}\n";
        $prompt .= "CERTIFICAT VISÉ : {$nomCertif}\n\n";

        if ($hasContent) {
            $prompt .= "CONTENU SUR LEQUEL BASER LES QUESTIONS :\n{$context}";
            $prompt .= "RÈGLE ABSOLUE : Chaque question DOIT porter sur un élément PRÉCIS du contenu ci-dessus.\n\n";
        }

        $prompt .= "Réponds UNIQUEMENT avec ce JSON valide (sans markdown) :\n";
        $prompt .= '[{"question":"...","options":["A","B","C","D"],"correct":0},...]';

        $payload = [
            'model' => $ollamaModel,
            'prompt' => $prompt,
            'stream' => false,
            'format' => 'json',
            'options' => [
                'temperature' => 0.3,
                'num_predict' => 2000,
            ],
        ];

        $ch = curl_init(rtrim($ollamaUrl, '/') . '/api/generate');
        if ($ch === false) {
            return [];
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 280,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!is_string($response) || $httpCode !== 200) {
            return [];
        }

        $data = json_decode($response, true);
        $raw = is_array($data) ? (string) ($data['response'] ?? '') : '';
        $raw = preg_replace('/```json|```/', '', $raw);
        $raw = trim($raw);

        if (preg_match('/\[.*\]/s', $raw, $m)) {
            $raw = $m[0];
        }

        $questions = json_decode($raw, true);

        return is_array($questions) ? $questions : [];
    }

    /**
     * @param list<array{name: string, text: string}> $pdfFiles
     * @return list<array{question: string, options: list<string>, correct: int}>
     */
    private function tryGroq(
        string $coursTitre,
        string $description,
        string $contenu,
        string $nomCertif,
        array $pdfFiles,
        ?array $demandeContext = null
    ): array {
        $apiKey = trim((string) (getenv('GROQ_API_KEY') ?: ''));
        if ($apiKey === '') {
            return [];
        }

        $model = trim((string) (getenv('GROQ_MODEL') ?: 'llama-3.3-70b-versatile'));

        $context = '';
        if ($description !== '') {
            $context .= "DESCRIPTION : {$description}\n\n";
        }
        if ($contenu !== '') {
            $context .= "CONTENU :\n{$contenu}\n\n";
        }
        foreach ($pdfFiles as $pdf) {
            if (!empty($pdf['text'])) {
                $context .= 'DOC ' . ($pdf['name'] ?? '') . " : {$pdf['text']}\n\n";
            }
        }

        $demandeLine = '';
        if ($demandeContext !== null && $demandeContext !== []) {
            $demandeLine = "\nDemande certification : id=" . (int) ($demandeContext['id'] ?? 0)
                . ', statut=' . ($demandeContext['statut'] ?? '')
                . ', date souhaitée=' . ($demandeContext['date_souhaitee'] ?? '')
                . ', organisation=' . ($demandeContext['organisation'] ?? '') . ".\n";
        }

        $user = "Cours: {$coursTitre}\nCertificat: {$nomCertif}{$demandeLine}\n{$context}\n"
            . 'Génère exactement 5 questions QCM en français. Format JSON STRICT : '
            . 'un tableau [{"question":"...","options":["A","B","C","D"],"correct":0},...] '
            . 'correct = index 0-3. Pas de markdown.';

        $body = [
            'model' => $model,
            'temperature' => 0.25,
            'max_tokens' => 2000,
            'messages' => [
                ['role' => 'system', 'content' => 'Tu réponds uniquement avec un tableau JSON valide de 5 QCM.'],
                ['role' => 'user', 'content' => $user],
            ],
        ];

        [$status, $raw] = GroqClient::postChatCompletions($apiKey, $body, 90);
        if ($status < 200 || $status >= 300 || !is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $text = trim((string) ($decoded['choices'][0]['message']['content'] ?? ''));
        if ($text === '') {
            return [];
        }

        if (preg_match('/\[.*\]/s', $text, $m)) {
            $text = $m[0];
        }

        $questions = json_decode($text, true);

        return is_array($questions) ? $questions : [];
    }

    /**
     * @param list<mixed> $raw
     * @return list<array{question: string, options: list<string>, correct: int}>
     */
    private function normalizeQuestions(array $raw): array
    {
        $out = [];
        foreach ($raw as $item) {
            if (!is_array($item)) {
                continue;
            }
            $q = trim((string) ($item['question'] ?? ''));
            $opts = $item['options'] ?? [];
            if (!is_array($opts) || count($opts) < 4) {
                continue;
            }
            $options = [
                trim((string) ($opts[0] ?? '')),
                trim((string) ($opts[1] ?? '')),
                trim((string) ($opts[2] ?? '')),
                trim((string) ($opts[3] ?? '')),
            ];
            $correct = (int) ($item['correct'] ?? 0);
            $correct = max(0, min(3, $correct));
            if ($q === '') {
                continue;
            }
            $out[] = ['question' => $q, 'options' => $options, 'correct' => $correct];
        }

        return $out;
    }

    /**
     * @return list<array{question: string, options: list<string>, correct: int}>
     */
    private function fallbackQuestions(string $coursTitre): array
    {
        return [
            [
                'question' => "Quelle est la principale technologie enseignée dans « {$coursTitre} » ?",
                'options' => ['La technologie principale du domaine', 'La bureautique standard', 'La gestion RH', 'La comptabilité'],
                'correct' => 0,
            ],
            [
                'question' => 'Quel concept est fondamental pour réussir la certification de ce cours ?',
                'options' => ['Les bases théoriques et pratiques du domaine', 'La rédaction de rapports', 'La communication commerciale', 'La gestion budgétaire'],
                'correct' => 0,
            ],
            [
                'question' => 'Quelle compétence clé ce cours développe-t-il en priorité ?',
                'options' => ['Maîtrise des outils et protocoles du domaine', 'Expression artistique', 'Comptabilité analytique', 'Droit du travail'],
                'correct' => 0,
            ],
            [
                'question' => 'Comment sont évalués les acquis dans ce type de certification ?',
                'options' => ['QCM technique + cas pratiques', 'Entretien oral uniquement', 'Portfolio artistique', 'Aucune évaluation'],
                'correct' => 0,
            ],
            [
                'question' => 'Quel niveau de maîtrise est requis pour valider cette certification ?',
                'options' => ['Compréhension et application des concepts clés', 'Notions très basiques', 'Aucune connaissance', 'Niveau expert mondial uniquement'],
                'correct' => 0,
            ],
        ];
    }
}
