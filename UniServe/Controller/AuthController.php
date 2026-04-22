<?php

declare(strict_types=1);

class AuthController extends Controller
{
    public function landing(): void
    {
        // Si déjà connecté, rediriger selon le rôle
        if ($this->isLoggedIn()) {
            $this->redirectByRole();
        }

        $this->render('auth/landing', [
            'title' => 'Bienvenue sur UniServe',
        ]);
    }

    public function login(): void
    {
        // Si déjà connecté, rediriger
        if ($this->isLoggedIn()) {
            $this->redirectByRole();
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if ($email === '' || $password === '') {
                $error = 'Please enter your email and password.';
            } else {
                $db   = Database::connect();
                $stmt = $db->prepare(
                    "SELECT * FROM utilisateurs WHERE email = ? AND statut_compte = 'actif' LIMIT 1"
                );
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                // Vérification du mot de passe (stocké en clair dans la démo)
                // Remplacez par password_verify() si les mots de passe sont hashés
                if ($user && $user['mot_de_passe_hash'] === $password) {
                    $_SESSION['user'] = [
                        'id'     => $user['id'],
                        'nom'    => $user['nom'],
                        'prenom' => $user['prenom'],
                        'email'  => $user['email'],
                        'role'   => $user['role'],
                    ];
                    $this->redirectByRole();
                } else {
                    $error = 'Incorrect email or password.';
                }
            }
        }

        $this->render('auth/login', [
            'error' => $error,
        ]);
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect($this->url('/'));
    }

    private function redirectByRole(): void
    {
        $role = (string)($_SESSION['user']['role'] ?? '');

        if (in_array($role, ['staff', 'admin'], true)) {
            $this->redirect($this->url('/dashboard/index'));
        } else {
            $this->redirect($this->url('/rendezvous/index'));
        }
    }
}
