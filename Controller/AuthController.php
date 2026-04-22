<?php

declare(strict_types=1);

class AuthController extends Controller
{
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

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $email = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            if ($email === '' || $password === '') {
                $error = 'Veuillez renseigner votre email et votre mot de passe.';
                $this->render('auth_login', ['error' => $error], 'landing');
                return;
            }

            $userModel = new User();
            $user = $userModel->findByEmail($email);

            $hash = (string) ($user['mot_de_passe_hash'] ?? '');
            $ok = !empty($user) && $hash !== '' && password_verify($password, $hash);

            if (!$ok) {
                $this->render('auth_login', [
                    'error' => 'Identifiants invalides. Veuillez réessayer.',
                ], 'landing');
                return;
            }

            if ((string) ($user['statut_compte'] ?? 'actif') !== 'actif') {
                $this->render('auth_login', [
                    'error' => 'Ce compte est inactif. Contactez l’administration.',
                ], 'landing');
                return;
            }

            $role = (string) ($user['role'] ?? '');
            if (!in_array($role, User::allowedRoles(), true)) {
                $this->render('auth_login', [
                    'error' => 'Rôle utilisateur non reconnu.',
                ], 'landing');
                return;
            }

            session_regenerate_id(true);

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
}
