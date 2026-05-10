<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/User.php';

class UsersController extends Controller
{
    private const PROFILE_PHOTO_MAX_BYTES = 2097152;
    private const PROFILE_PHOTO_SUBDIR = 'View/shared/assets/profile-pics';

    public function landing(): void
    {
        $this->requireLogin();

        $role = (string) ($_SESSION['user']['role'] ?? '');
        if (in_array($role, ['staff', 'admin'], true)) {
            $this->redirect('/backoffice/dashboard');
            return;
        }

        if (in_array($role, ['etudiant', 'enseignant'], true)) {
            $this->redirect('/frontoffice/dashboard');
            return;
        }

        $this->redirect('/auth/login');
    }

    public function profile(): void
    {
        $this->requireLogin();

        $userId = (int) ($_SESSION['user']['id'] ?? 0);
        if ($userId <= 0) {
            $this->redirect('/auth/login');
            return;
        }

        $userModel = new User();

        if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST') {
            $action = (string) ($_POST['form_action'] ?? '');

            if ($action === 'profile_update') {
                $this->handleProfileUpdate($userModel, $userId);
                return;
            }

            if ($action === 'password_update') {
                $this->handlePasswordUpdate($userModel, $userId);
                return;
            }
        }

        $user = $userModel->findById($userId);
        if ($user === null) {
            $this->redirect('/auth/logout');
            return;
        }

        $this->render('frontoffice/users/profile', [
            'title' => 'Mon profil',
            'user' => $user,
            'success' => (string) ($_GET['success'] ?? ''),
            'error' => (string) ($_GET['error'] ?? ''),
        ]);
    }

    private function handleProfileUpdate(User $userModel, int $userId): void
    {
        $nom = trim((string) ($_POST['nom'] ?? ''));
        $prenom = trim((string) ($_POST['prenom'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $matricule = trim((string) ($_POST['matricule'] ?? ''));
        $departement = trim((string) ($_POST['departement'] ?? ''));
        $niveau = trim((string) ($_POST['niveau'] ?? ''));
        $telephone = trim((string) ($_POST['telephone'] ?? ''));

        if ($nom === '' || $prenom === '' || $email === '') {
            $this->redirectProfileError('Champs obligatoires manquants.');
            return;
        }

        if ($userModel->emailExists($email, $userId)) {
            $this->redirectProfileError('Cette adresse email est déjà utilisée.');
            return;
        }

        $data = [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'matricule' => $matricule !== '' ? $matricule : null,
            'departement' => $departement !== '' ? $departement : null,
            'niveau' => $niveau !== '' ? $niveau : null,
            'telephone' => $telephone !== '' ? $telephone : null,
        ];

        $photoPath = $this->handleProfilePhotoUpload($userId);
        if ($photoPath === false) {
            $this->redirectProfileError('Photo invalide : vérifiez le format (JPG, PNG, WEBP) et la taille (2 Mo max).');
            return;
        }

        if ($photoPath !== null) {
            $data['photo_profil'] = $photoPath;
        }

        $userModel->updateById($userId, $data);

        $fresh = $userModel->findById($userId);
        if ($fresh !== null) {
            $this->syncSessionUser($fresh);
        }

        $this->redirect('/users/profile?success=' . urlencode('Profil mis à jour.'));
    }

    private function handlePasswordUpdate(User $userModel, int $userId): void
    {
        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if ($current === '' || $new === '' || $confirm === '') {
            $this->redirectProfileError('Renseignez tous les champs du mot de passe.');
            return;
        }

        if ($new !== $confirm) {
            $this->redirectProfileError('La confirmation ne correspond pas au nouveau mot de passe.');
            return;
        }

        if (strlen($new) < User::MIN_PASSWORD_LENGTH) {
            $this->redirectProfileError('Le nouveau mot de passe doit contenir au moins ' . User::MIN_PASSWORD_LENGTH . ' caractères.');
            return;
        }

        if (!$userModel->verifyPasswordById($userId, $current)) {
            $this->redirectProfileError('Mot de passe actuel incorrect.');
            return;
        }

        $userModel->updateById($userId, ['password' => $new]);
        $this->redirect('/users/profile?success=' . urlencode('Mot de passe mis à jour.'));
    }

    /**
     * @return string|null Relative path stored in DB, null if no upload, false on validation failure
     */
    private function handleProfilePhotoUpload(int $userId): string|null|false
    {
        if (!isset($_FILES['photo_profil'])) {
            return null;
        }

        $file = $_FILES['photo_profil'];
        $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($err === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($err !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($file['tmp_name'] ?? ''))) {
            return false;
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > self::PROFILE_PHOTO_MAX_BYTES) {
            return false;
        }

        $tmp = (string) $file['tmp_name'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: '';

        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($map[$mime])) {
            return false;
        }

        $ext = $map[$mime];
        $baseDir = dirname(__DIR__) . '/' . self::PROFILE_PHOTO_SUBDIR;
        if (!is_dir($baseDir) && !mkdir($baseDir, 0775, true) && !is_dir($baseDir)) {
            return false;
        }

        $basename = 'user_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $destFs = $baseDir . '/' . $basename;
        $relative = self::PROFILE_PHOTO_SUBDIR . '/' . $basename;

        if (!move_uploaded_file($tmp, $destFs)) {
            return false;
        }

        return $relative;
    }

    private function syncSessionUser(array $user): void
    {
        $_SESSION['user']['nom'] = (string) ($user['nom'] ?? '');
        $_SESSION['user']['prenom'] = (string) ($user['prenom'] ?? '');
        $_SESSION['user']['email'] = (string) ($user['email'] ?? '');
        $_SESSION['user']['matricule'] = (string) ($user['matricule'] ?? '');
    }

    private function redirectProfileError(string $message): void
    {
        $this->redirect('/users/profile?error=' . urlencode($message));
    }
}
