<?php
// ============================================================
//  TEST OLLAMA LOCAL
//  Place dans UniServe/ → http://localhost/UniServe/test_gemini.php
// ============================================================

require_once __DIR__ . '/config/anthropic.php';

$url   = defined('OLLAMA_URL')   ? OLLAMA_URL   : 'http://localhost:11434';
$model = defined('OLLAMA_MODEL') ? OLLAMA_MODEL : 'qwen2.5:14b';

echo "<h2>🔍 Diagnostic Ollama (IA Locale)</h2>";
echo "<pre style='background:#1a1a1a;color:#0f0;padding:20px;font-size:14px;'>";

echo "1. Configuration :\n";
echo "   URL   : {$url}\n";
echo "   Modèle: {$model}\n\n";

echo "2. Test connexion Ollama...\n";

// Test if Ollama is running
$ch = curl_init($url . '/api/tags');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 5,
    CURLOPT_CONNECTTIMEOUT => 3,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

if ($err || $code !== 200) {
    echo "   ❌ Ollama ne répond pas !\n";
    echo "   → Ouvre l'application Ollama sur ton PC\n";
    echo "   → Ou tape dans CMD : ollama serve\n\n";
} else {
    $data   = json_decode($resp, true);
    $models = array_column($data['models'] ?? [], 'name');
    echo "   ✅ Ollama tourne !\n";
    echo "   Modèles installés : " . implode(', ', $models) . "\n\n";

    // Check if our model is available
    $found = false;
    foreach ($models as $m) {
        if (strpos($m, 'qwen2.5:14b') !== false) { $found = true; break; }
    }
    echo "   Modèle qwen2.5:14b : " . ($found ? "✅ Disponible" : "❌ Non trouvé") . "\n\n";
}

echo "3. Test génération de questions...\n";
echo "   (peut prendre 30-60 secondes la première fois)\n\n";

$payload = [
    'model'  => $model,
    'prompt' => 'Génère 2 questions QCM sur Python. Réponds UNIQUEMENT avec ce JSON sans markdown: [{"question":"...","options":["A","B","C","D"],"correct":0}]',
    'stream' => false,
    'format' => 'json',
    'options' => ['temperature' => 0.3, 'num_predict' => 500],
];

$ch = curl_init($url . '/api/generate');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 120,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Status : {$httpCode}\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    $raw  = $data['response'] ?? '';
    $raw  = preg_replace('/```json|```/', '', $raw);
    if (preg_match('/\[.*\]/s', $raw, $m)) $raw = $m[0];
    $questions = json_decode(trim($raw), true);
    if (is_array($questions) && count($questions) > 0) {
        echo "   ✅ SUCCÈS ! " . count($questions) . " questions générées :\n\n";
        foreach ($questions as $i => $q) {
            echo "   Q" . ($i+1) . ": " . ($q['question'] ?? '?') . "\n";
            foreach (($q['options'] ?? []) as $j => $opt) {
                $mark = ($j == ($q['correct'] ?? -1)) ? " ✅" : "";
                echo "      " . ['A','B','C','D'][$j] . ". {$opt}{$mark}\n";
            }
            echo "\n";
        }
    } else {
        echo "   ⚠️  Réponse reçue mais JSON invalide :\n";
        echo "   " . substr($raw, 0, 300) . "\n";
    }
} else {
    echo "   ❌ Erreur {$httpCode}\n";
    echo "   " . substr($response, 0, 200) . "\n";
}

echo "=== FIN DU TEST ===\n";
echo "</pre>";
echo "<p style='color:orange'><strong>⚠️ Supprime ce fichier après le test !</strong></p>";