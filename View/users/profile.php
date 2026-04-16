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
$statutCompteRaw = (string) ($user['statut_compte'] ?? '');

$departement = $departement !== '' ? $departement : '—';
$niveau = $niveau !== '' ? $niveau : '—';
$telephone = $telephone !== '' ? $telephone : '—';
$statutCompte = in_array($statutCompteRaw, ['actif', 'inactif'], true) ? $statutCompteRaw : 'actif';
?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:46px;height:46px;">
                            <span class="fw-bold"><?= htmlspecialchars(substr($fullName, 0, 1), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div>
                            <div class="fw-bold fs-3 lh-1 mb-2"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge bg-light text-muted border">
                                    <?= htmlspecialchars((string) ($user['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <span class="badge <?= $statutCompte === 'actif' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= htmlspecialchars($statutCompte, ENT_QUOTES, 'UTF-8') ?>
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
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                    <h3 class="h5 mb-0">Informations personnelles</h3>
                                </div>

                                <form method="post" action="<?= $this->url('/users/profile') ?>">
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
                                            <label class="form-label text-muted small">Rôle</label>
                                            <div>
                                                <span class="badge bg-light text-muted border">
                                                    <?= htmlspecialchars((string) ($user['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label text-muted small">Statut_compte</label>
                                            <div>
                                                <span class="badge <?= $statutCompte === 'actif' ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= htmlspecialchars($statutCompte, ENT_QUOTES, 'UTF-8') ?>
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
                        <div class="card border-0 shadow-sm">
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

