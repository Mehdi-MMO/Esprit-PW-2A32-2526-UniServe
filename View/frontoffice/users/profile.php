<?php
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
$roleValue = strtolower((string) ($user['role'] ?? ''));
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header">
    <div>
        <div class="us-kicker mb-1">Compte utilisateur</div>
        <h1 class="h3 mb-1">Mon profil</h1>
        <p class="us-page-subtitle">Mettez a jour vos informations et vos preferences de securite.</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-8">
        <div class="us-section-card">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <button
                            type="button"
                            class="btn p-0 border-0 bg-transparent profile-photo-trigger"
                            data-profile-photo-trigger="1"
                            data-profile-photo-input-id="photo_profil"
                            title="Changer la photo de profil"
                            aria-label="Changer la photo de profil"
                        >
                            <?php if ($photoProfilUrl !== null): ?>
                                <span class="us-avatar">
                                    <img
                                        src="<?= htmlspecialchars($photoProfilUrl, ENT_QUOTES, 'UTF-8') ?>"
                                        alt="Photo de profil"
                                        class="us-profile-photo"
                                        width="46"
                                        height="46"
                                        data-profile-photo-preview="1"
                                    >
                                </span>
                            <?php else: ?>
                                <div class="us-avatar" data-profile-photo-preview-fallback="1">
                                    <span class="fw-bold"><?= htmlspecialchars(substr($fullName, 0, 1), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            <?php endif; ?>
                        </button>
                        <div>
                            <h2 class="fw-bold fs-3 lh-1 mb-2"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></h2>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="us-role-chip <?= htmlspecialchars($roleValue, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars(ucfirst((string) ($user['role'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <span class="us-status-chip <?= $statutCompte === 'actif' ? 'actif' : 'inactif' ?>">
                                    <?= htmlspecialchars(ucfirst($statutCompte), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

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
                                                <span class="us-role-chip <?= htmlspecialchars($roleValue, ENT_QUOTES, 'UTF-8') ?>">
                                                    <?= htmlspecialchars(ucfirst((string) ($user['role'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label text-muted small">Statut du compte</label>
                                            <div>
                                                <span class="us-status-chip <?= $statutCompte === 'actif' ? 'actif' : 'inactif' ?>">
                                                    <?= htmlspecialchars(ucfirst($statutCompte), ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end mt-4">
                                        <button class="btn btn-primary" type="submit">Enregistrer</button>
                                    </div>
                                </form>
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

<div class="modal fade us-avatar-crop-modal" id="profilePhotoCropModal" tabindex="-1" aria-labelledby="profilePhotoCropModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-2">
                <div>
                    <h5 class="modal-title mb-1" id="profilePhotoCropModalLabel">Ajuster la photo de profil</h5>
                    <p class="text-muted small mb-0">Recadrez l'image avant de l'enregistrer.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="us-crop-stage mb-3">
                    <img id="profilePhotoCropImage" class="us-crop-image" alt="Aperçu du recadrage">
                </div>
                <div class="mb-3">
                    <label for="profilePhotoCropZoom" class="form-label small text-muted mb-1">Zoom</label>
                    <input type="range" class="form-range" id="profilePhotoCropZoom" min="100" max="300" step="1" value="100">
                </div>
                <p class="small text-muted mb-0">Astuce: deplacez l'image avec la souris ou le doigt pour choisir le cadrage.</p>
            </div>
            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="profilePhotoCropReset">Reinitialiser</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary btn-sm" id="profilePhotoCropConfirm">Appliquer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.querySelector('[data-profile-photo-input="1"]');
        const triggers = document.querySelectorAll('[data-profile-photo-trigger="1"]');
        const fileNameLabel = document.getElementById('photo_profil_filename');
        let headerPhotoImg = document.querySelector('[data-profile-photo-preview="1"]');
        const headerPhotoFallback = document.querySelector('[data-profile-photo-preview-fallback="1"]');
        const cropModalEl = document.getElementById('profilePhotoCropModal');
        const cropImage = document.getElementById('profilePhotoCropImage');
        const zoomInput = document.getElementById('profilePhotoCropZoom');
        const resetButton = document.getElementById('profilePhotoCropReset');
        const confirmButton = document.getElementById('profilePhotoCropConfirm');
        const bootstrapModal = (cropModalEl && window.bootstrap && window.bootstrap.Modal)
            ? new window.bootstrap.Modal(cropModalEl)
            : null;
        const hasCropper = typeof window.Cropper !== 'undefined';

        let cropper = null;
        let pendingSourceUrl = null;
        let previousPreviewUrl = null;
        let pendingFile = null;
        let cropConfirmed = false;

        if (!input) {
            return;
        }

        triggers.forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                input.click();
            });
        });

        function clearPendingSelection(resetLabel) {
            pendingFile = null;
            cropConfirmed = false;
            input.value = '';
            if (resetLabel && fileNameLabel) {
                fileNameLabel.textContent = 'Aucun fichier sélectionné';
            }
        }

        function ensureHeaderPreviewImage() {
            if (headerPhotoImg) {
                return headerPhotoImg;
            }
            if (!headerPhotoFallback) {
                return null;
            }
            const avatarWrapper = document.createElement('span');
            avatarWrapper.className = 'us-avatar';
            const generatedImg = document.createElement('img');
            generatedImg.className = 'us-profile-photo';
            generatedImg.width = 46;
            generatedImg.height = 46;
            generatedImg.setAttribute('data-profile-photo-preview', '1');
            generatedImg.alt = 'Photo de profil';
            avatarWrapper.appendChild(generatedImg);
            headerPhotoFallback.replaceWith(avatarWrapper);
            headerPhotoImg = generatedImg;
            return headerPhotoImg;
        }

        function destroyCropper() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            if (pendingSourceUrl) {
                URL.revokeObjectURL(pendingSourceUrl);
                pendingSourceUrl = null;
            }
            if (cropImage) {
                cropImage.removeAttribute('src');
            }
            if (zoomInput) {
                zoomInput.value = '100';
            }
        }

        function setFileInput(file) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            input.files = dataTransfer.files;
        }

        function openCropModalForFile(file) {
            if (!bootstrapModal || !hasCropper || !cropImage) {
                return false;
            }

            pendingFile = file;
            cropConfirmed = false;

            destroyCropper();
            pendingSourceUrl = URL.createObjectURL(file);
            cropImage.src = pendingSourceUrl;

            cropImage.onload = function () {
                if (cropper) {
                    cropper.destroy();
                }
                cropper = new window.Cropper(cropImage, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 1,
                    background: false,
                    responsive: true,
                    guides: false,
                    center: true,
                    checkCrossOrigin: false
                });
            };

            bootstrapModal.show();
            return true;
        }

        input.addEventListener('change', function () {
            const file = input.files && input.files[0] ? input.files[0] : null;
            if (fileNameLabel) {
                fileNameLabel.textContent = file ? file.name : 'Aucun fichier sélectionné';
            }

            if (!file) {
                return;
            }

            if (!file.type || file.type.indexOf('image/') !== 0) {
                clearPendingSelection(false);
                if (fileNameLabel) {
                    fileNameLabel.textContent = 'Format non pris en charge';
                }
                return;
            }

            if (!openCropModalForFile(file)) {
                if (fileNameLabel) {
                    fileNameLabel.textContent = 'Recadrage indisponible';
                }
                clearPendingSelection(false);
            }
        });

        if (zoomInput) {
            zoomInput.addEventListener('input', function () {
                if (!cropper) {
                    return;
                }
                cropper.zoomTo(Number(zoomInput.value) / 100);
            });
        }

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                if (!cropper) {
                    return;
                }
                cropper.reset();
                if (zoomInput) {
                    zoomInput.value = '100';
                }
            });
        }

        if (confirmButton) {
            confirmButton.addEventListener('click', function () {
                if (!cropper || !pendingFile) {
                    return;
                }

                const canvas = cropper.getCroppedCanvas({
                    width: 512,
                    height: 512,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high'
                });

                if (!canvas) {
                    return;
                }

                canvas.toBlob(function (blob) {
                    if (!blob) {
                        return;
                    }

                    const croppedFile = new File([blob], 'avatar_' + Date.now() + '.webp', { type: 'image/webp' });
                    setFileInput(croppedFile);

                    if (fileNameLabel) {
                        fileNameLabel.textContent = croppedFile.name;
                    }

                    const previewImage = ensureHeaderPreviewImage();
                    if (previewImage) {
                        if (previousPreviewUrl) {
                            URL.revokeObjectURL(previousPreviewUrl);
                        }
                        previousPreviewUrl = URL.createObjectURL(croppedFile);
                        previewImage.src = previousPreviewUrl;
                    }

                    cropConfirmed = true;
                    pendingFile = null;
                    bootstrapModal.hide();
                }, 'image/webp', 0.92);
            });
        }

        if (cropModalEl) {
            cropModalEl.addEventListener('hidden.bs.modal', function () {
                if (!cropConfirmed) {
                    clearPendingSelection(true);
                }
                destroyCropper();
            });
        }
    });
</script>
