<?php

declare(strict_types=1);

class AuthController extends Controller
{
    private const OTP_MAX_ATTEMPTS = 5;
    private const OTP_REQUEST_COOLDOWN_SECONDS = 60;

    public function landing(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirectByUserRole((string) ($_SESSION['user']['role'] ?? ''));
            return;
        }

        $this->render('auth_landing', [
            'title' => 'Bienvenue sur UniServe',
        ], 'landing');
    }

    public function login(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirectByUserRole((string) ($_SESSION['user']['role'] ?? ''));
            return;
        }

        $error = null;
        $success = $this->popFlash('auth_login_success');

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $email = $this->normalizeEmail((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            if ($email === '' || $password === '') {
                $error = 'Veuillez renseigner votre email et votre mot de passe.';
            } elseif (($emailError = $this->validateInstitutionalEmail($email)) !== null) {
                $error = $emailError;
            }

            if ($error !== null) {
                $this->render('auth_login', ['error' => $error, 'success' => $success], 'landing');
                return;
            }

            $userModel = new User();
            $user = $userModel->findByEmail($email);

            $hash = (string) ($user['mot_de_passe_hash'] ?? '');
            $ok = !empty($user) && $hash !== '' && password_verify($password, $hash);

            if (!$ok) {
                $this->render('auth_login', [
                    'error' => 'Identifiants invalides. Veuillez réessayer.',
                    'success' => $success,
                ], 'landing');
                return;
            }

            if ((string) ($user['statut_compte'] ?? 'actif') !== 'actif') {
                $this->render('auth_login', [
                    'error' => 'Ce compte est inactif. Contactez l’administration.',
                    'success' => $success,
                ], 'landing');
                return;
            }

            $role = (string) ($user['role'] ?? '');
            if (!in_array($role, User::allowedRoles(), true)) {
                $this->render('auth_login', [
                    'error' => 'Rôle utilisateur non reconnu.',
                    'success' => $success,
                ], 'landing');
                return;
            }

            session_regenerate_id(true);

            // Update last login timestamp
            $userModel = new User();
            $userModel->updateLastLogin((int) ($user['id'] ?? 0));

            // Store only the required fields in session.
            $_SESSION['user'] = [
                'id' => (int) ($user['id'] ?? 0),
                'nom' => (string) ($user['nom'] ?? ''),
                'prenom' => (string) ($user['prenom'] ?? ''),
                'email' => (string) ($user['email'] ?? ''),
                'role' => $role,
                'matricule' => (string) ($user['matricule'] ?? ''),
            ];

            $this->redirectByUserRole($role);
            return;
        }

        $this->render('auth_login', [
            'error' => $error,
            'success' => $success,
        ], 'landing');
    }

    public function forgot(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirectByUserRole((string) ($_SESSION['user']['role'] ?? ''));
            return;
        }

        $error = null;
        $success = $this->popFlash('auth_forgot_success');
        $email = $this->normalizeEmail((string) ($_POST['email'] ?? $_GET['email'] ?? ''));

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            if (($emailError = $this->validateInstitutionalEmail($email)) !== null) {
                $error = $emailError;
            } else {
                $userModel = new User();
                $user = $userModel->findByEmail($email);
                $isActive = is_array($user) && (string) ($user['statut_compte'] ?? 'inactif') === 'actif';

                if ($isActive) {
                    $passwordReset = new PasswordReset();
                    $secondsAgo = $passwordReset->lastRequestSecondsAgo($email);
                    if ($secondsAgo !== null && $secondsAgo < self::OTP_REQUEST_COOLDOWN_SECONDS) {
                        $wait = self::OTP_REQUEST_COOLDOWN_SECONDS - $secondsAgo;
                        $error = 'Veuillez patienter ' . $wait . ' secondes avant de demander un nouveau code.';
                    } else {
                        $passwordReset->deleteExpiredFor((int) ($user['id'] ?? 0));
                        $created = $passwordReset->createForUser((int) ($user['id'] ?? 0), $email);

                        $otp = (string) ($created['otp'] ?? '');
                        $requestToken = (string) ($created['request_token'] ?? '');

                        $mailService = new MailService();
                        $subject = 'UniServe - Code de reinitialisation';
                        $htmlBody = '<p>Bonjour,</p><p>Votre code OTP est: <strong>' . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . '</strong>.</p><p>Ce code expire dans 10 minutes.</p>';
                        $textBody = "Bonjour,\nVotre code OTP est: {$otp}\nCe code expire dans 10 minutes.";
                        $sent = $mailService->send($email, $subject, $htmlBody, $textBody);

                        if (!$sent) {
                            $failedEntry = $passwordReset->findActiveByRequestToken($requestToken);
                            if ($failedEntry !== null) {
                                $passwordReset->markUsed((int) ($failedEntry['id'] ?? 0));
                            }
                            $error = "Impossible d'envoyer l'email de verification. Reessayez dans quelques instants.";
                            $this->render('auth_forgot', [
                                'title' => 'Mot de passe oublie',
                                'error' => $error,
                                'success' => $success,
                                'email' => $email,
                            ], 'landing');
                            return;
                        }

                        $_SESSION['password_reset_email'][$requestToken] = $email;
                        $this->redirect('/auth/verify/' . $requestToken);
                        return;
                    }
                } else {
                    $success = 'Si un compte actif correspond a cet email, un code OTP sera envoye.';
                }
            }
        }

        $this->render('auth_forgot', [
            'title' => 'Mot de passe oublie',
            'error' => $error,
            'success' => $success,
            'email' => $email,
        ], 'landing');
    }

    public function verify(string $requestToken): void
    {
        if ($this->isLoggedIn()) {
            $this->redirectByUserRole((string) ($_SESSION['user']['role'] ?? ''));
            return;
        }

        $passwordReset = new PasswordReset();
        $entry = $passwordReset->findActiveByRequestToken($requestToken);
        if ($entry === null) {
            $_SESSION['auth_forgot_success'] = 'Le code est invalide ou expire. Veuillez demander un nouveau code.';
            $this->redirect('/auth/forgot');
            return;
        }

        $error = null;
        $email = (string) ($entry['email'] ?? ($_SESSION['password_reset_email'][$requestToken] ?? ''));

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $otp = preg_replace('/\D+/', '', (string) ($_POST['otp'] ?? ''));
            if ($otp === null) {
                $otp = '';
            }

            if ($otp === '' || strlen($otp) !== 6) {
                $error = 'Saisissez le code OTP a 6 chiffres.';
            } elseif ((int) ($entry['attempts'] ?? 0) >= self::OTP_MAX_ATTEMPTS) {
                $passwordReset->markUsed((int) ($entry['id'] ?? 0));
                $_SESSION['auth_forgot_success'] = 'Nombre maximal de tentatives atteint. Veuillez demander un nouveau code.';
                $this->redirect('/auth/forgot');
                return;
            } elseif (!password_verify($otp, (string) ($entry['otp_hash'] ?? ''))) {
                $attempts = $passwordReset->incrementAttempts((int) ($entry['id'] ?? 0));
                if ($attempts >= self::OTP_MAX_ATTEMPTS) {
                    $passwordReset->markUsed((int) ($entry['id'] ?? 0));
                    $_SESSION['auth_forgot_success'] = 'Nombre maximal de tentatives atteint. Veuillez demander un nouveau code.';
                    $this->redirect('/auth/forgot');
                    return;
                }

                $remaining = self::OTP_MAX_ATTEMPTS - $attempts;
                $error = 'Code invalide. Tentatives restantes: ' . $remaining . '.';
            } else {
                $resetToken = $passwordReset->markVerified((int) ($entry['id'] ?? 0));
                $_SESSION['password_reset_bindings'][$resetToken] = [
                    'fingerprint' => $this->requestFingerprint(),
                    'issued_at' => time(),
                ];
                $this->redirect('/auth/reset/' . $resetToken);
                return;
            }
        }

        $this->render('auth_verify_otp', [
            'title' => 'Verifier le code OTP',
            'error' => $error,
            'requestToken' => $requestToken,
            'email' => $email,
        ], 'landing');
    }

    public function reset(string $resetToken): void
    {
        if ($this->isLoggedIn()) {
            $this->redirectByUserRole((string) ($_SESSION['user']['role'] ?? ''));
            return;
        }

        $passwordReset = new PasswordReset();
        $entry = $passwordReset->findActiveByResetToken($resetToken);
        if ($entry === null || empty($entry['verified_at'])) {
            $_SESSION['auth_forgot_success'] = 'Session de reinitialisation invalide ou expiree.';
            $this->redirect('/auth/forgot');
            return;
        }

        $binding = $_SESSION['password_reset_bindings'][$resetToken] ?? null;
        $fingerprint = $this->requestFingerprint();
        if (!is_array($binding) || (string) ($binding['fingerprint'] ?? '') !== $fingerprint) {
            $_SESSION['auth_forgot_success'] = 'Session de reinitialisation invalide. Recommencez la procedure.';
            $this->redirect('/auth/forgot');
            return;
        }

        $error = null;

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $newPassword = (string) ($_POST['new_password'] ?? '');
            $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

            if (($passwordError = $this->validateMinLength($newPassword, User::MIN_PASSWORD_LENGTH, 'Le mot de passe')) !== null) {
                $error = $passwordError;
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'Le nouveau mot de passe et sa confirmation ne correspondent pas.';
            } else {
                $userModel = new User();
                $updated = $userModel->updatePasswordById((int) ($entry['user_id'] ?? 0), $newPassword);
                if (!$updated) {
                    $error = 'Impossible de mettre a jour le mot de passe.';
                } else {
                    $passwordReset->markUsed((int) ($entry['id'] ?? 0));
                    unset($_SESSION['password_reset_bindings'][$resetToken]);
                    $_SESSION['auth_login_success'] = 'Votre mot de passe a ete reinitialise avec succes.';
                    $this->redirect('/auth/login');
                    return;
                }
            }
        }

        $this->render('auth_reset_password', [
            'title' => 'Reinitialiser le mot de passe',
            'error' => $error,
            'resetToken' => $resetToken,
        ], 'landing');
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                (string) ($params['path'] ?? '/'),
                (string) ($params['domain'] ?? ''),
                (bool) ($params['secure'] ?? false),
                (bool) ($params['httponly'] ?? true),
            );
        }

        session_destroy();
        $this->redirect('/');
    }

    private function popFlash(string $key): ?string
    {
        if (!isset($_SESSION[$key])) {
            return null;
        }

        $value = (string) $_SESSION[$key];
        unset($_SESSION[$key]);
        return $value;
    }

    private function requestFingerprint(): string
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        return hash('sha256', $ip . '|' . $ua);
    }
}
