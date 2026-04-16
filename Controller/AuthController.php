<?php

declare(strict_types=1);

class AuthController extends Controller
{
    public function landing(): void
    {
        $this->render('auth/landing', [
            'title' => 'Bienvenue sur UniServe',
        ]);
    }

    public function login(): void
    {
        $error = null;

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $email = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            if ($email === '' || $password === '') {
                $error = 'Veuillez renseigner votre email et votre mot de passe.';
                $this->render('auth/login', ['error' => $error]);
                return;
            }

            $userModel = new User();
            $user = $userModel->findByEmail($email);

            $hash = (string) ($user['mot_de_passe_hash'] ?? '');
            $ok = !empty($user) && $hash !== '' && password_verify($password, $hash);

            if (!$ok) {
                $this->render('auth/login', [
                    'error' => 'Identifiants invalides. Veuillez réessayer.',
                ]);
                return;
            }

            // Store only the required fields in session.
            $_SESSION['user'] = [
                'id' => (int) ($user['id'] ?? 0),
                'nom' => (string) ($user['nom'] ?? ''),
                'prenom' => (string) ($user['prenom'] ?? ''),
                'email' => (string) ($user['email'] ?? ''),
                'role' => (string) ($user['role'] ?? ''),
                'matricule' => (string) ($user['matricule'] ?? ''),
            ];

            $role = (string) ($_SESSION['user']['role'] ?? '');

            if (in_array($role, ['etudiant', 'enseignant'], true)) {
                $this->redirect('/frontoffice/dashboard');
                return;
            }

            $this->redirect('/backoffice/dashboard');
            return;
        }

        $this->render('auth/login', [
            'error' => $error,
        ]);
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
