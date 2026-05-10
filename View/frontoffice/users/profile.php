<?php
$profile_identity_locked = !empty($profile_identity_locked ?? false);

$fullName = trim(
    (string) ($user['prenom'] ?? '') . ' ' .
    (string) ($user['nom'] ?? '')
);
if ($fullName === '') {
    $fullName = 'Utilisateur';
}

$departement = (string) ($user['departement'] ?? '');
$niveau = (string) ($user['niveau'] ?? '');
$telephone = (string) ($user['telephone'] ?? '');
$photoProfil = trim((string) ($user['photo_profil'] ?? ''));
$statutCompteRaw = (string) ($user['statut_compte'] ?? '');

$departement = $departement !== '' ? $departement : '—';
$niveau = $niveau !== '' ? $niveau : '—';
$telephone = $telephone !== '' ? $telephone : '—';
$statutCompte = in_array($statutCompteRaw, ['actif', 'inactif'], true) ? $statutCompteRaw : 'actif';
$photoProfilUrl = null;
if ($photoProfil !== '' && str_starts_with($photoProfil, 'View/shared/assets/profile-pics/')) {
    $photoProfilUrl = $this->url('/' . $photoProfil);
}
?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-8">
        <div class="us-section-card">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <?php if ($profile_identity_locked): ?>
                            <div class="rounded-circle border overflow-hidden bg-light d-inline-flex align-items-center justify-content-center" style="width:46px;height:46px;" aria-hidden="true">
                                <?php if ($photoProfilUrl !== null): ?>
                                    <img
                                        src="<?= htmlspecialchars($photoProfilUrl, ENT_QUOTES, 'UTF-8') ?>"
                                        alt=""
                                        class="rounded-circle"
                                        style="width:46px;height:46px;object-fit:cover;"
                                    >
                                <?php else: ?>
                                    <span class="fw-bold text-primary"><?= htmlspecialchars(substr($fullName, 0, 1), ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                        <button
                            type="button"
                            class="btn p-0 border-0 bg-transparent profile-photo-trigger"
                            data-profile-photo-trigger="1"
                            data-profile-photo-input-id="photo_profil"
                            title="Changer la photo de profil"
                            aria-label="Changer la photo de profil"
                        >
                            <?php if ($photoProfilUrl !== null): ?>
                                <img
                                    src="<?= htmlspecialchars($photoProfilUrl, ENT_QUOTES, 'UTF-8') ?>"
                                    alt="Photo de profil"
                                    class="rounded-circle border"
                                    style="width:46px;height:46px;object-fit:cover;"
                                    data-profile-photo-preview="1"
                                >
                            <?php else: ?>
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:46px;height:46px;" data-profile-photo-preview-fallback="1">
                                    <span class="fw-bold"><?= htmlspecialchars(substr($fullName, 0, 1), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            <?php endif; ?>
                        </button>
                        <?php endif; ?>
                        <div>
                            <h1 class="fw-bold fs-3 lh-1 mb-2"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></h1>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge bg-light text-muted border">
                                    <?= htmlspecialchars(ucfirst((string) ($user['role'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <span class="badge <?= $statutCompte === 'actif' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= htmlspecialchars(ucfirst($statutCompte), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <h2 class="h4 mb-3">Mon profil</h2>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success py-2 small mb-3" role="alert">
                        <?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small mb-3" role="alert">
                        <?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <div class="col-12">
                        <div class="us-section-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                    <h3 class="h5 mb-0">Informations personnelles</h3>
                                </div>

                                <?php if ($profile_identity_locked): ?>
                                    <div class="alert alert-info py-2 small mb-3" role="status">
                                        Compte administrateur unique : ces informations ne peuvent pas être modifiées ici. Seul le mot de passe peut être changé (bloc ci-dessous ou depuis Utilisateurs).
                                    </div>
                                    <dl class="row mb-0 small">
                                        <dt class="col-sm-4 col-md-3 text-muted">Nom</dt>
                                        <dd class="col-sm-8 col-md-9"><?= htmlspecialchars((string) ($user['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
                                        <dt class="col-sm-4 col-md-3 text-muted">Prénom</dt>
                                        <dd class="col-sm-8 col-md-9"><?= htmlspecialchars((string) ($user['prenom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
                                        <dt class="col-sm-4 col-md-3 text-muted">Email</dt>
                                        <dd class="col-sm-8 col-md-9"><?= htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
                                        <dt class="col-sm-4 col-md-3 text-muted">Matricule</dt>
                                        <dd class="col-sm-8 col-md-9"><?= htmlspecialchars((string) (($user['matricule'] ?? '') !== '' ? $user['matricule'] : '—'), ENT_QUOTES, 'UTF-8') ?></dd>
                                        <dt class="col-sm-4 col-md-3 text-muted">Département</dt>
                                        <dd class="col-sm-8 col-md-9"><?= htmlspecialchars((string) (($user['departement'] ?? '') !== '' ? $user['departement'] : '—'), ENT_QUOTES, 'UTF-8') ?></dd>
                                        <dt class="col-sm-4 col-md-3 text-muted">Niveau</dt>
                                        <dd class="col-sm-8 col-md-9"><?= htmlspecialchars((string) (($user['niveau'] ?? '') !== '' ? $user['niveau'] : '—'), ENT_QUOTES, 'UTF-8') ?></dd>
                                        <dt class="col-sm-4 col-md-3 text-muted">Téléphone</dt>
                                        <dd class="col-sm-8 col-md-9"><?= htmlspecialchars((string) (($user['telephone'] ?? '') !== '' ? $user['telephone'] : '—'), ENT_QUOTES, 'UTF-8') ?></dd>
                                        <dt class="col-sm-4 col-md-3 text-muted">Rôle</dt>
                                        <dd class="col-sm-8 col-md-9">
                                            <span class="badge bg-light text-muted border">
                                                <?= htmlspecialchars(ucfirst((string) ($user['role'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </dd>
                                        <dt class="col-sm-4 col-md-3 text-muted">Statut</dt>
                                        <dd class="col-sm-8 col-md-9">
                                            <span class="badge <?= $statutCompte === 'actif' ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= htmlspecialchars(ucfirst($statutCompte), ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </dd>
                                    </dl>
                                <?php else: ?>
                                <form method="post" action="<?= $this->url('/users/profile') ?>" enctype="multipart/form-data">
                                    <input type="hidden" name="form_action" value="profile_update">

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small" for="nom">Nom</label>
                                            <input class="form-control" id="nom" name="nom" value="<?= htmlspecialchars((string) ($user['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label text-muted small" for="prenom">Prénom</label>
                                            <input class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars((string) ($user['prenom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label text-muted small" for="email">Email</label>
                                            <input class="form-control" id="email" type="email" name="email" value="<?= htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label text-muted small" for="matricule">Matricule</label>
                                            <input class="form-control" id="matricule" name="matricule" value="<?= htmlspecialchars((string) ($user['matricule'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label text-muted small" for="departement">Département</label>
                                            <input class="form-control" id="departement" name="departement" value="<?= htmlspecialchars((string) ($user['departement'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label text-muted small" for="niveau">Niveau</label>
                                            <input class="form-control" id="niveau" name="niveau" value="<?= htmlspecialchars((string) ($user['niveau'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label text-muted small" for="telephone">Téléphone</label>
                                            <input class="form-control" id="telephone" name="telephone" value="<?= htmlspecialchars((string) ($user['telephone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label text-muted small" for="photo_profil">Photo de profil</label>
                                            <input
                                                class="form-control visually-hidden"
                                                id="photo_profil"
                                                name="photo_profil"
                                                type="file"
                                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                                data-profile-photo-input="1"
                                            >
                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary btn-sm"
                                                data-profile-photo-trigger="1"
                                                data-profile-photo-input-id="photo_profil"
                                            >
                                                Choisir une photo
                                            </button>
                                            <span class="ms-2 text-muted small" id="photo_profil_filename">Aucun fichier sélectionné</span>
                                            <div class="form-text">JPG, PNG ou WEBP (2 Mo max).</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label text-muted small">Rôle</label>
                                            <div>
                                                <span class="badge bg-light text-muted border">
                                                    <?= htmlspecialchars(ucfirst((string) ($user['role'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label text-muted small">Statut du compte</label>
                                            <div>
                                                <span class="badge <?= $statutCompte === 'actif' ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= htmlspecialchars(ucfirst($statutCompte), ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end mt-4">
                                        <button class="btn btn-primary" type="submit">Enregistrer</button>
                                    </div>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="us-section-card">
                            <div class="card-body p-4">
                                <h3 class="h5 mb-3">Changer le mot de passe</h3>

                                <form method="post" action="<?= $this->url('/users/profile') ?>">
                                    <input type="hidden" name="form_action" value="password_update">

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small" for="current_password">Mot de passe actuel</label>
                                            <input class="form-control" id="current_password" name="current_password" type="password" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small" for="new_password">Nouveau mot de passe</label>
                                            <input class="form-control" id="new_password" name="new_password" type="password" required>
                                            <div class="form-text">Minimum 8 caractères.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small" for="confirm_password">Confirmer le nouveau mot de passe</label>
                                            <input class="form-control" id="confirm_password" name="confirm_password" type="password" required>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end mt-4">
                                        <button class="btn btn-outline-primary" type="submit">Mettre à jour</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.querySelector('[data-profile-photo-input="1"]');
        const triggers = document.querySelectorAll('[data-profile-photo-trigger="1"]');
        const fileNameLabel = document.getElementById('photo_profil_filename');
        let headerPhotoImg = document.querySelector('[data-profile-photo-preview="1"]');
        const headerPhotoFallback = document.querySelector('[data-profile-photo-preview-fallback="1"]');

        if (!input) {
            return;
        }

        triggers.forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                input.click();
            });
        });

        input.addEventListener('change', function () {
            const file = input.files && input.files[0] ? input.files[0] : null;
            if (fileNameLabel) {
                fileNameLabel.textContent = file ? file.name : 'Aucun fichier sélectionné';
            }

            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                if (!event.target || typeof event.target.result !== 'string') {
                    return;
                }

                if (!headerPhotoImg && headerPhotoFallback) {
                    const generatedImg = document.createElement('img');
                    generatedImg.className = 'rounded-circle border';
                    generatedImg.style.width = '46px';
                    generatedImg.style.height = '46px';
                    generatedImg.style.objectFit = 'cover';
                    generatedImg.setAttribute('data-profile-photo-preview', '1');
                    generatedImg.alt = 'Photo de profil';
                    headerPhotoFallback.replaceWith(generatedImg);
                    headerPhotoImg = generatedImg;
                }

                if (!headerPhotoImg) {
                    return;
                }

                headerPhotoImg.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });
    });
</script>
