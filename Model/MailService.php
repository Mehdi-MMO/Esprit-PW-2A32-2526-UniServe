<?php

declare(strict_types=1);

class MailService
{
    public function send(string $to, string $subject, string $htmlBody, string $textBody): bool
    {
        $apiKey = trim((string) (getenv('RESEND_API_KEY') ?: ''));
        $fromEmail = trim((string) (getenv('RESEND_FROM_EMAIL') ?: ''));
        $fromName = trim((string) (getenv('RESEND_FROM_NAME') ?: 'UniServe'));

        if ($apiKey === '' || $fromEmail === '') {
            $this->logError('Missing RESEND_API_KEY or RESEND_FROM_EMAIL.');
            return false;
        }

        if (!function_exists('curl_init')) {
            $this->logError('cURL extension is not enabled.');
            return false;
        }

        $payload = [
            'from' => $this->formatFrom($fromEmail, $fromName),
            'to' => [$to],
            'subject' => $subject,
            'html' => $htmlBody,
            'text' => $textBody,
        ];

        $ch = curl_init('https://api.resend.com/emails');
        if ($ch === false) {
            $this->logError('Unable to initialize cURL.');
            return false;
        }

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $responseBody = curl_exec($ch);
        $curlError = curl_error($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseBody === false) {
            $this->logError('Resend request failed: ' . $curlError);
            return false;
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            $this->logError('Resend API error (' . $statusCode . '): ' . $responseBody);
            return false;
        }

        $decoded = json_decode((string) $responseBody, true);
        if (!is_array($decoded) || empty($decoded['id'])) {
            $this->logError('Resend response missing message id: ' . (string) $responseBody);
            return false;
        }

        return true;
    }

    private function formatFrom(string $email, string $name): string
    {
        $safeName = trim($name);
        if ($safeName === '') {
            return $email;
        }

        return sprintf('%s <%s>', $safeName, $email);
    }

    private function logError(string $message): void
    {
        try {
            $logDir = __DIR__ . '/../logs';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0777, true);
            }

            $entry = '[' . date('c') . '] ' . $message . PHP_EOL;
            @file_put_contents($logDir . '/mail.log', $entry, FILE_APPEND);
        } catch (Throwable $throwable) {
            // Do not interrupt the flow for logging failures.
        }
    }
}
