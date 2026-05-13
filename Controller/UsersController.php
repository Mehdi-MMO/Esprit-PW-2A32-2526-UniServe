<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/User.php';
require_once __DIR__ . '/../Model/ValidationService.php';
require_once __DIR__ . '/../Model/AppUploads.php';

class UsersController extends Controller
{
    private const PROFILE_PHOTO_MAX_BYTES = 2097152;
    /** DB path prefix; files on disk under Model/uploads/profile_pics/ */
    private const PROFILE_PHOTO_SUBDIR = 'Model/uploads/profile_pics';

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

        $_SESSION['user']['nom'] = (string) ($user['nom'] ?? '');
        $_SESSION['user']['prenom'] = (string) ($user['prenom'] ?? '');
        $_SESSION['user']['email'] = (string) ($user['email'] ?? '');
        $_SESSION['user']['photo_profil'] = (string) ($user['photo_profil'] ?? '');

        $this->render('frontoffice/users/profile', [
            'title' => 'Mon profil',
            'user' => $user,
            'success' => (string) ($_GET['success'] ?? ''),
            'error' => (string) ($_GET['error'] ?? ''),
            'profile_identity_locked' => $userModel->isTheSingletonAdmin($userId),
        ]);
    }

    private function handleProfileUpdate(User $userModel, int $userId): void
    {
        $existing = $userModel->findById($userId);
        if ($existing === null) {
            $this->redirectProfileError('Compte introuvable.');
            return;
        }

        if ($userModel->isTheSingletonAdmin($userId)) {
            $this->redirectProfileError(
                'Le compte administrateur unique ne peut être modifié que par le mot de passe (ci-dessous ou dans Utilisateurs).'
            );

            return;
        }

        $nom = $this->normalizeText((string) ($_POST['nom'] ?? ''));
        $prenom = $this->normalizeText((string) ($_POST['prenom'] ?? ''));
        $email = $this->normalizeEmail((string) ($_POST['email'] ?? ''));
        $matricule = trim((string) ($_POST['matricule'] ?? ''));
        $departement = trim((string) ($_POST['departement'] ?? ''));
        $niveau = trim((string) ($_POST['niveau'] ?? ''));
        $telephone = trim((string) ($_POST['telephone'] ?? ''));

        if ($nom === '' || $prenom === '' || $email === '') {
            $this->redirectProfileError('Champs obligatoires manquants.');
            return;
        }

        if (($emailInst = $this->validateInstitutionalEmail($email)) !== null) {
            $this->redirectProfileError($emailInst);
            return;
        }

        $profileFieldError = ValidationService::validateProfileFields([
            'nom' => $nom,
            'prenom' => $prenom,
            'matricule' => $matricule !== '' ? $matricule : null,
            'departement' => $departement !== '' ? $departement : null,
            'niveau' => $niveau !== '' ? $niveau : null,
            'telephone' => $telephone !== '' ? $telephone : null,
        ]);
        if ($profileFieldError !== null) {
            $this->redirectProfileError($profileFieldError);
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

        $oldPhotoRel = trim((string) ($existing['photo_profil'] ?? ''));
        $photoPath = $this->handleProfilePhotoUpload($userId, $oldPhotoRel !== '' ? $oldPhotoRel : null);
        if ($photoPath === false) {
            $this->redirectProfileError('Photo invalide : vérifiez le format (JPG, PNG, WEBP) et la taille (2 Mo max).');
            return;
        }

        if ($photoPath !== null) {
            $data['photo_profil'] = $photoPath;
        }

        if ($photoPath === null && $this->profilePayloadMatchesStored($data, $existing)) {
            $this->redirect('/users/profile?success=' . urlencode('Aucune modification à enregistrer.'));
            return;
        }

        $ok = $userModel->updateById($userId, $data);
        $fresh = $userModel->findById($userId);

        if (!$ok && $fresh !== null && $this->profilePayloadMatchesStored($data, $fresh)) {
            $this->syncSessionUser($fresh);
            $this->redirect('/users/profile?success=' . urlencode('Profil à jour.'));
            return;
        }

        if (!$ok) {
            $this->redirectProfileError('Aucun changement effectué ou mise à jour impossible.');
            return;
        }

        if ($fresh !== null) {
            $this->syncSessionUser($fresh);
        }

        $this->redirect('/users/profile?success=' . urlencode('Profil mis à jour.'));
    }

    /**
     * Compare normalized POST payload to DB row (photo excluded — handled separately).
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $user
     */
    private function profilePayloadMatchesStored(array $data, array $user): bool
    {
        $normNull = static function (mixed $v): ?string {
            if ($v === null) {
                return null;
            }
            $t = trim((string) $v);

            return $t === '' ? null : $t;
        };

        return $data['nom'] === $this->normalizeText((string) ($user['nom'] ?? ''))
            && $data['prenom'] === $this->normalizeText((string) ($user['prenom'] ?? ''))
            && $data['email'] === $this->normalizeEmail((string) ($user['email'] ?? ''))
            && $normNull($data['matricule'] ?? null) === $normNull($user['matricule'] ?? null)
            && $normNull($data['departement'] ?? null) === $normNull($user['departement'] ?? null)
            && $normNull($data['niveau'] ?? null) === $normNull($user['niveau'] ?? null)
            && $normNull($data['telephone'] ?? null) === $normNull($user['telephone'] ?? null);
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

        if (($pwdErr = $this->validateMinLength($new, User::MIN_PASSWORD_LENGTH, 'Le nouveau mot de passe')) !== null) {
            $this->redirectProfileError($pwdErr);
            return;
        }

        if (!$userModel->verifyPasswordById($userId, $current)) {
            $this->redirectProfileError('Mot de passe actuel incorrect.');
            return;
        }

        $ok = $userModel->updateById($userId, ['password' => $new]);
        if (!$ok) {
            $this->redirectProfileError('Impossible de mettre à jour le mot de passe.');
            return;
        }

        $this->redirect('/users/profile?success=' . urlencode('Mot de passe mis à jour.'));
    }

    /**
     * @param string|null $oldRelativePath Previous DB path under PROFILE_PHOTO_SUBDIR
     * @return string|null Relative path stored in DB, null if no upload, false on validation failure
     */
    private function handleProfilePhotoUpload(int $userId, ?string $oldRelativePath): string|null|false
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
        $root = dirname(__DIR__);
        require_once __DIR__ . '/../Model/AppUploads.php';
        $baseDir = AppUploads::sub('profile_pics');

        $basename = 'user_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $destFs = $baseDir . '/' . $basename;
        $relative = self::PROFILE_PHOTO_SUBDIR . '/' . $basename;

        if (!move_uploaded_file($tmp, $destFs)) {
            return false;
        }

        $old = trim((string) ($oldRelativePath ?? ''));
        if ($old !== '' && $old !== $relative) {
            $legacyPrefix = 'View/shared/assets/profile-pics/';
            $newPrefix = self::PROFILE_PHOTO_SUBDIR . '/';
            if (
                (str_starts_with($old, $newPrefix) || str_starts_with($old, $legacyPrefix))
                && str_contains($old, 'user_' . $userId . '_')
            ) {
                $oldAbs = $root . '/' . str_replace('\\', '/', $old);
                if (is_file($oldAbs)) {
                    @unlink($oldAbs);
                }
            }
        }

        return $relative;
    }

    private function syncSessionUser(array $user): void
    {
        $_SESSION['user']['nom'] = (string) ($user['nom'] ?? '');
        $_SESSION['user']['prenom'] = (string) ($user['prenom'] ?? '');
        $_SESSION['user']['email'] = (string) ($user['email'] ?? '');
        $_SESSION['user']['matricule'] = (string) ($user['matricule'] ?? '');
        $_SESSION['user']['photo_profil'] = (string) ($user['photo_profil'] ?? '');
    }

    private function redirectProfileError(string $message): void
    {
        $this->redirect('/users/profile?error=' . urlencode($message));
    }
}
