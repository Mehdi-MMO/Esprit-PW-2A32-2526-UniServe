<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/PasswordReset.php';
require_once __DIR__ . '/../Model/MailService.php';
require_once __DIR__ . '/../Model/LoginRiskService.php';

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
            'landingNav' => 'home',
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
            $email = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            if ($email === '' || $password === '') {
                $error = 'Veuillez renseigner votre email et votre mot de passe.';
                $this->render('auth_login', ['title' => 'Connexion', 'error' => $error, 'success' => $success, 'landingNav' => 'login'], 'landing');
                return;
            }

            $userModel = new User();
            $user = $userModel->findByEmail($email);

            $hash = (string) ($user['mot_de_passe_hash'] ?? '');
            $ok = !empty($user) && $hash !== '' && password_verify($password, $hash);

            if (!$ok) {
                if (LoginRiskService::isStepUpEnabled()) {
                    $risk = new LoginRiskService();
                    $risk->recordFailedAttempt($email, $risk->fingerprintFromRequest());
                }

                $this->render('auth_login', [
                    'title' => 'Connexion',
                    'error' => 'Identifiants invalides. Veuillez réessayer.',
                    'success' => null,
                    'landingNav' => 'login',
                ], 'landing');
                return;
            }

            if ((string) ($user['statut_compte'] ?? 'actif') !== 'actif') {
                $this->render('auth_login', [
                    'title' => 'Connexion',
                    'error' => 'Ce compte est inactif. Contactez l’administration.',
                    'success' => null,
                    'landingNav' => 'login',
                ], 'landing');
                return;
            }

            $role = (string) ($user['role'] ?? '');
            if (!in_array($role, User::allowedRoles(), true)) {
                $this->render('auth_login', [
                    'title' => 'Connexion',
                    'error' => 'Rôle utilisateur non reconnu.',
                    'success' => null,
                    'landingNav' => 'login',
                ], 'landing');
                return;
            }

            if (LoginRiskService::isStepUpEnabled()) {
                $risk = new LoginRiskService();
                $fp = $risk->fingerprintFromRequest();
                $assessment = $risk->assessLogin($user, $email, $fp);
                $level = (string) ($assessment['risk_level'] ?? 'low');

                if (in_array($level, ['medium', 'high'], true)) {
                    $challenge = $risk->createChallenge((int) ($user['id'] ?? 0), $email);
                    $mailService = new MailService();
                    $subject = 'UniServe — Vérification de connexion';
                    $otp = (string) ($challenge['otp'] ?? '');
                    $requestToken = (string) ($challenge['request_token'] ?? '');
                    $htmlBody = '<p>Bonjour,</p><p>Un code de vérification a été demandé suite à une connexion inhabituelle.</p>'
                        . '<p>Code : <strong>' . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . '</strong></p>'
                        . '<p>Ce code expire dans 10 minutes.</p>';
                    $textBody = "Bonjour,\nCode de vérification : {$otp}\nExpire dans 10 minutes.";
                    $sent = $mailService->send($email, $subject, $htmlBody, $textBody);

                    if (!$sent) {
                        $_SESSION['auth_login_success'] = 'Connexion autorisée (envoi du code de vérification impossible — vérifiez la configuration email).';
                        $this->establishUserSession($user, $userModel, $risk, $fp);
                        $this->redirectByUserRole($role);
                        return;
                    }

                    $this->redirect('/auth/verifyLoginRisk/' . $requestToken);
                    return;
                }

                $risk->trustDevice((int) ($user['id'] ?? 0), $fp);
            }

            $this->establishUserSession($user, $userModel, null, null);

            $this->redirectByUserRole($role);
            return;
        }

        $this->render('auth_login', [
            'title' => 'Connexion',
            'error' => $error,
            'success' => $success,
            'landingNav' => 'login',
        ], 'landing');
    }

    public function verifyLoginRisk(string $requestToken): void
    {
        if ($this->isLoggedIn()) {
            $this->redirectByUserRole((string) ($_SESSION['user']['role'] ?? ''));
            return;
        }

        if (!LoginRiskService::isStepUpEnabled()) {
            $this->redirect('/auth/login');
            return;
        }

        $risk = new LoginRiskService();
        $entry = $risk->findActiveChallenge($requestToken);
        if ($entry === null) {
            $_SESSION['auth_login_success'] = 'Le lien de vérification est invalide ou expiré. Veuillez vous reconnecter.';
            $this->redirect('/auth/login');
            return;
        }

        $error = null;
        $email = (string) ($entry['email'] ?? '');

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $otp = preg_replace('/\D+/', '', (string) ($_POST['otp'] ?? ''));

            if ($otp === '' || strlen($otp) !== 6) {
                $error = 'Saisissez le code à 6 chiffres.';
            } elseif ((int) ($entry['attempts'] ?? 0) >= LoginRiskService::challengeMaxAttempts()) {
                $risk->markChallengeUsed((int) ($entry['id'] ?? 0));
                $_SESSION['auth_login_success'] = 'Nombre maximal de tentatives atteint. Veuillez vous reconnecter.';
                $this->redirect('/auth/login');
                return;
            } elseif (!password_verify($otp, (string) ($entry['otp_hash'] ?? ''))) {
                $attempts = $risk->incrementChallengeAttempts((int) ($entry['id'] ?? 0));
                if ($attempts >= LoginRiskService::challengeMaxAttempts()) {
                    $risk->markChallengeUsed((int) ($entry['id'] ?? 0));
                    $_SESSION['auth_login_success'] = 'Nombre maximal de tentatives atteint. Veuillez vous reconnecter.';
                    $this->redirect('/auth/login');
                    return;
                }

                $remaining = LoginRiskService::challengeMaxAttempts() - $attempts;
                $error = 'Code invalide. Tentatives restantes : ' . $remaining . '.';
            } else {
                $risk->markChallengeUsed((int) ($entry['id'] ?? 0));
                $fp = $risk->fingerprintFromRequest();
                $risk->trustDevice((int) ($entry['user_id'] ?? 0), $fp);

                $userModel = new User();
                $user = $userModel->findById((int) ($entry['user_id'] ?? 0));
                if ($user === null || (string) ($user['statut_compte'] ?? 'inactif') !== 'actif') {
                    $_SESSION['auth_login_success'] = 'Compte introuvable ou inactif.';
                    $this->redirect('/auth/login');
                    return;
                }

                $role = (string) ($user['role'] ?? '');
                if (!in_array($role, User::allowedRoles(), true)) {
                    $_SESSION['auth_login_success'] = 'Rôle utilisateur non reconnu.';
                    $this->redirect('/auth/login');
                    return;
                }

                $this->establishUserSession($user, $userModel, null, null);
                $this->redirectByUserRole($role);
                return;
            }
        }

        $this->render('auth_verify_login_risk', [
            'title' => 'Vérification de connexion',
            'error' => $error,
            'requestToken' => $requestToken,
            'email' => $email,
        ], 'landing');
    }

    public function forgot(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirectByUserRole((string) ($_SESSION['user']['role'] ?? ''));
            return;
        }

        $error = null;
        $flashSuccess = $this->popFlash('auth_forgot_success');
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
                        $subject = 'UniServe — Code de réinitialisation';
                        $htmlBody = '<p>Bonjour,</p><p>Votre code OTP est : <strong>'
                            . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8')
                            . '</strong>.</p><p>Ce code expire dans 10 minutes.</p>';
                        $textBody = "Bonjour,\nVotre code OTP est : {$otp}\nCe code expire dans 10 minutes.";
                        $sent = $mailService->send($email, $subject, $htmlBody, $textBody);

                        if (!$sent) {
                            $failedEntry = $passwordReset->findActiveByRequestToken($requestToken);
                            if ($failedEntry !== null) {
                                $passwordReset->markUsed((int) ($failedEntry['id'] ?? 0));
                            }
                            $error = 'Impossible d’envoyer l’email de vérification. Vérifiez SMTP_HOST / SMTP_USER / SMTP_PASS dans .env, puis réessayez.';
                            $this->render('auth_forgot', [
                                'title' => 'Mot de passe oublié',
                                'error' => $error,
                                'success' => $flashSuccess,
                                'email' => $email,
                            ], 'landing');
                            return;
                        }

                        $_SESSION['password_reset_email'][$requestToken] = $email;
                        $this->redirect('/auth/verify/' . $requestToken);
                        return;
                    }
                } else {
                    $flashSuccess = 'Si un compte actif correspond à cet email, un code OTP sera envoyé.';
                }
            }
        }

        $this->render('auth_forgot', [
            'title' => 'Mot de passe oublié',
            'error' => $error,
            'success' => $flashSuccess,
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
            $_SESSION['auth_forgot_success'] = 'Le code est invalide ou expiré. Veuillez demander un nouveau code.';
            $this->redirect('/auth/forgot');
            return;
        }

        $error = null;
        $email = (string) ($entry['email'] ?? ($_SESSION['password_reset_email'][$requestToken] ?? ''));

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $otp = preg_replace('/\D+/', '', (string) ($_POST['otp'] ?? ''));

            if ($otp === '' || strlen($otp) !== 6) {
                $error = 'Saisissez le code OTP à 6 chiffres.';
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
                $error = 'Code invalide. Tentatives restantes : ' . $remaining . '.';
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
            'title' => 'Vérifier le code OTP',
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
            $_SESSION['auth_forgot_success'] = 'Session de réinitialisation invalide ou expirée.';
            $this->redirect('/auth/forgot');
            return;
        }

        $binding = $_SESSION['password_reset_bindings'][$resetToken] ?? null;
        $fingerprint = $this->requestFingerprint();
        if (!is_array($binding) || (string) ($binding['fingerprint'] ?? '') !== $fingerprint) {
            $_SESSION['auth_forgot_success'] = 'Session de réinitialisation invalide. Recommencez la procédure.';
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
                $updated = $userModel->updateById((int) ($entry['user_id'] ?? 0), ['password' => $newPassword]);
                if (!$updated) {
                    $error = 'Impossible de mettre à jour le mot de passe.';
                } else {
                    $passwordReset->markUsed((int) ($entry['id'] ?? 0));
                    unset($_SESSION['password_reset_bindings'][$resetToken]);
                    $_SESSION['auth_login_success'] = 'Votre mot de passe a été réinitialisé avec succès.';
                    $this->redirect('/auth/login');
                    return;
                }
            }
        }

        $this->render('auth_reset_password', [
            'title' => 'Réinitialiser le mot de passe',
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

    /**
     * @param array<string, mixed> $user
     */
    private function establishUserSession(array $user, User $userModel, ?LoginRiskService $risk, ?string $fingerprint): void
    {
        session_regenerate_id(true);

        $role = (string) ($user['role'] ?? '');
        $_SESSION['user'] = [
            'id' => (int) ($user['id'] ?? 0),
            'nom' => (string) ($user['nom'] ?? ''),
            'prenom' => (string) ($user['prenom'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'role' => $role,
            'matricule' => (string) ($user['matricule'] ?? ''),
            'photo_profil' => (string) ($user['photo_profil'] ?? ''),
        ];

        $userModel->touchLastLogin((int) ($user['id'] ?? 0));

        if ($risk !== null && $fingerprint !== null && $fingerprint !== '') {
            $risk->trustDevice((int) ($user['id'] ?? 0), $fingerprint);
        }
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
