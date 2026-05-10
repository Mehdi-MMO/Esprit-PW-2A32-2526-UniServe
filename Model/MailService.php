<?php

declare(strict_types=1);

class MailService
{
    public function send(string $to, string $subject, string $htmlBody, string $textBody): bool
    {
        $host = trim((string) (getenv('SMTP_HOST') ?: ''));
        $user = trim((string) (getenv('SMTP_USER') ?: ''));
        $pass = trim((string) (getenv('SMTP_PASS') ?: ''));
        $from = trim((string) (getenv('SMTP_FROM') ?: $user));
        $fromName = trim((string) (getenv('SMTP_FROM_NAME') ?: 'UniServe'));
        $secure = strtolower(trim((string) (getenv('SMTP_SECURE') ?: 'tls')));
        $port = (int) (getenv('SMTP_PORT') ?: 587);

        if ($host === '' || $user === '' || $pass === '' || $from === '' || $port <= 0) {
            return false;
        }

        $phpMailerFile = __DIR__ . '/lib/PHPMailer/PHPMailer.php';
        $smtpFile = __DIR__ . '/lib/PHPMailer/SMTP.php';
        $exceptionFile = __DIR__ . '/lib/PHPMailer/Exception.php';
        if (!is_file($phpMailerFile) || !is_file($smtpFile) || !is_file($exceptionFile)) {
            return false;
        }

        require_once $exceptionFile;
        require_once $smtpFile;
        require_once $phpMailerFile;

        if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            return false;
        }

        try {
            $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
            $mailer->isSMTP();
            $mailer->Host = $host;
            $mailer->Port = $port;
            $mailer->SMTPAuth = true;
            $mailer->Username = $user;
            $mailer->Password = $pass;
            $mailer->CharSet = 'UTF-8';
            $mailer->Timeout = 20;

            if ($secure === 'ssl') {
                $mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mailer->setFrom($from, $fromName);
            $mailer->addAddress($to);
            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $htmlBody;
            $mailer->AltBody = $textBody;

            return $mailer->send();
        } catch (Throwable) {
            return false;
        }
    }
}
