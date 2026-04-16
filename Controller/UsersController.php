<?php

declare(strict_types=1);

class UsersController extends Controller
{
    public function profile(): void
    {
        $this->requireLogin();
        $this->requireRole(['etudiant', 'enseignant']);

        $currentUser = $this->currentUser();
        $userId = (int) ($currentUser['id'] ?? 0);

        if ($userId <= 0) {
            $this->redirect('/auth/login');
            return;
        }

        $userModel = new User();
        $user = $userModel->findById($userId);

        if ($user === null) {
            $_SESSION = [];
            $this->redirect('/auth/login');
            return;
        }

        $error = null;
        $success = null;

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $formAction = (string) ($_POST['form_action'] ?? '');

            if ($formAction === 'profile_update') {
                $nom = trim((string) ($_POST['nom'] ?? ''));
                $prenom = trim((string) ($_POST['prenom'] ?? ''));
                $email = trim((string) ($_POST['email'] ?? ''));
                $matricule = (string) ($_POST['matricule'] ?? '');
                $departement = (string) ($_POST['departement'] ?? '');
                $niveau = (string) ($_POST['niveau'] ?? '');
                $telephone = (string) ($_POST['telephone'] ?? '');

                if ($nom === '' || $prenom === '' || $email === '') {
                    $error = 'Nom, prénom et email sont obligatoires.';
                } elseif ($userModel->emailExists($email, $userId)) {
                    $error = 'Cet email est déjà utilisé par un autre compte.';
                } else {
                    $ok = $userModel->updateById($userId, [
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'email' => $email,
                        'matricule' => $matricule,
                        'departement' => $departement,
                        'niveau' => $niveau,
                        'telephone' => $telephone,
                    ]);

                    if ($ok) {
                        $user = $userModel->findById($userId);
                        $_SESSION['user'] = [
                            'id' => (int) ($user['id'] ?? $userId),
                            'nom' => (string) ($user['nom'] ?? $nom),
                            'prenom' => (string) ($user['prenom'] ?? $prenom),
                            'email' => (string) ($user['email'] ?? $email),
                            'role' => (string) ($user['role'] ?? ($_SESSION['user']['role'] ?? '')),
                            'matricule' => (string) ($user['matricule'] ?? $matricule),
                        ];

                        $success = 'Votre profil a été mis à jour.';
                    } else {
                        $error = 'Aucun changement n’a été effectué (ou mise à jour impossible).';
                    }
                }
            } elseif ($formAction === 'password_update') {
                $currentPassword = (string) ($_POST['current_password'] ?? '');
                $newPassword = (string) ($_POST['new_password'] ?? '');
                $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

                if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
                    $error = 'Veuillez remplir tous les champs du changement de mot de passe.';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'Le nouveau mot de passe et sa confirmation ne correspondent pas.';
                } elseif (!$userModel->verifyPasswordById($userId, $currentPassword)) {
                    $error = 'Mot de passe actuel incorrect.';
                } else {
                    $ok = $userModel->updateById($userId, [
                        'password' => $newPassword,
                    ]);

                    if ($ok) {
                        $success = 'Mot de passe mis à jour avec succès.';
                    } else {
                        $error = 'Impossible de mettre à jour le mot de passe.';
                    }
                }
            } else {
                $error = 'Action invalide.';
            }
        }

        $this->render('users/profile', [
            'user' => $user,
            'error' => $error,
            'success' => $success,
        ]);
    }
}

