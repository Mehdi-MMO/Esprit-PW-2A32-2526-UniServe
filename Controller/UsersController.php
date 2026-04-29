<?php

declare(strict_types=1);

class UsersController extends Controller
{
    private const PROFILE_PHOTO_MAX_BYTES = 2097152; // 2 MB
    private const PROFILE_PHOTO_DIR = 'View/shared/assets/profile-pics';

    private function processProfilePhotoUpload(array $file, int $userId, ?string $oldRelativePath): array
    {
        $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($errorCode === UPLOAD_ERR_NO_FILE) {
            return [null, null];
        }

        if ($errorCode !== UPLOAD_ERR_OK) {
            return [null, 'Erreur lors du téléversement de la photo de profil.'];
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            return [null, 'Fichier de photo invalide.'];
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > self::PROFILE_PHOTO_MAX_BYTES) {
            return [null, 'La photo doit faire au maximum 2 Mo.'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = (string) $finfo->file($tmpName);
        $allowedMimeMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        if (!isset($allowedMimeMap[$mimeType])) {
            return [null, 'Format non autorisé. Utilisez JPG, PNG ou WEBP.'];
        }

        $uploadDirAbsolute = __DIR__ . '/../' . self::PROFILE_PHOTO_DIR;
        if (!is_dir($uploadDirAbsolute) && !mkdir($uploadDirAbsolute, 0775, true) && !is_dir($uploadDirAbsolute)) {
            return [null, 'Impossible de préparer le dossier d’upload.'];
        }

        $extension = $allowedMimeMap[$mimeType];
        try {
            $randomPart = bin2hex(random_bytes(8));
        } catch (Throwable) {
            return [null, 'Impossible de générer un nom de fichier sécurisé.'];
        }
        $fileName = 'user_' . $userId . '_' . $randomPart . '.' . $extension;
        $relativePath = self::PROFILE_PHOTO_DIR . '/' . $fileName;
        $absolutePath = __DIR__ . '/../' . $relativePath;

        if (!move_uploaded_file($tmpName, $absolutePath)) {
            return [null, 'Impossible d’enregistrer la photo de profil.'];
        }

        $oldPath = trim((string) $oldRelativePath);
        if (
            $oldPath !== '' &&
            $oldPath !== $relativePath &&
            str_starts_with($oldPath, self::PROFILE_PHOTO_DIR . '/')
        ) {
            $oldAbsolutePath = __DIR__ . '/../' . $oldPath;
            if (is_file($oldAbsolutePath)) {
                @unlink($oldAbsolutePath);
            }
        }

        return [$relativePath, null];
    }

    public function landing(): void
    {
        $this->profile();
    }

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
                $nom = $this->normalizeText((string) ($_POST['nom'] ?? ''));
                $prenom = $this->normalizeText((string) ($_POST['prenom'] ?? ''));
                $email = $this->normalizeEmail((string) ($_POST['email'] ?? ''));
                $matricule = (string) ($_POST['matricule'] ?? '');
                $departement = (string) ($_POST['departement'] ?? '');
                $niveau = (string) ($_POST['niveau'] ?? '');
                $telephone = (string) ($_POST['telephone'] ?? '');
                $payload = [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'matricule' => $matricule,
                    'departement' => $departement,
                    'niveau' => $niveau,
                    'telephone' => $telephone,
                ];

                if ($nom === '' || $prenom === '' || $email === '') {
                    $error = 'Nom, prénom et email sont obligatoires.';
                } elseif (($emailError = $this->validateInstitutionalEmail($email)) !== null) {
                    $error = $emailError;
                } elseif ($userModel->emailExists($email, $userId)) {
                    $error = 'Cet email est déjà utilisé par un autre compte.';
                } else {
                    if (isset($_FILES['photo_profil']) && is_array($_FILES['photo_profil'])) {
                        [$photoPath, $uploadError] = $this->processProfilePhotoUpload(
                            $_FILES['photo_profil'],
                            $userId,
                            (string) ($user['photo_profil'] ?? '')
                        );
                        if ($uploadError !== null) {
                            $error = $uploadError;
                        } elseif ($photoPath !== null) {
                            $payload['photo_profil'] = $photoPath;
                        }
                    }

                    if ($error === null) {
                        $ok = $userModel->updateById($userId, $payload);
                    } else {
                        $ok = false;
                    }

                    if ($ok) {
                        $user = $userModel->findById($userId);
                        $_SESSION['user'] = [
                            'id' => (int) ($user['id'] ?? $userId),
                            'nom' => (string) ($user['nom'] ?? $nom),
                            'prenom' => (string) ($user['prenom'] ?? $prenom),
                            'email' => (string) ($user['email'] ?? $email),
                            'role' => (string) ($user['role'] ?? ($_SESSION['user']['role'] ?? '')),
                            'matricule' => (string) ($user['matricule'] ?? $matricule),
                            'photo_profil' => (string) ($user['photo_profil'] ?? ($_SESSION['user']['photo_profil'] ?? '')),
                        ];

                        $success = 'Votre profil a été mis à jour.';
                    } elseif ($error === null) {
                        $error = 'Aucun changement n’a été effectué (ou mise à jour impossible).';
                    }
                }
            } elseif ($formAction === 'password_update') {
                $currentPassword = (string) ($_POST['current_password'] ?? '');
                $newPassword = (string) ($_POST['new_password'] ?? '');
                $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

                if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
                    $error = 'Veuillez remplir tous les champs du changement de mot de passe.';
                } elseif (($passwordError = $this->validateMinLength($newPassword, User::MIN_PASSWORD_LENGTH, 'Le nouveau mot de passe')) !== null) {
                    $error = $passwordError;
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

        $this->render('frontoffice/users/profile', [
            'user' => $user,
            'error' => $error,
            'success' => $success,
        ]);
    }
}

