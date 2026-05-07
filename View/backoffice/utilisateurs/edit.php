<?php
function e(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

$singleAdminId = $singleAdminId ?? null;
$isSingleAdminEditing = $isSingleAdminEditing ?? false;
$isCompactAdminEdit = (bool) $isSingleAdminEditing;
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header">
    <div>
        <div class="us-kicker mb-1">Gestion des comptes</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Modifier un utilisateur'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="us-page-subtitle mb-0">
            <?= $isCompactAdminEdit ? 'Modifier les informations essentielles du compte administrateur.' : 'Mettre à jour le profil, le rôle ou le statut sans casser l’accès existant.' ?>
        </p>
    </div>

    <a href="<?= $this->url('/utilisateurs') ?>" class="btn btn-outline-secondary btn-sm">Retour</a>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="us-section-card">
            <div class="card-body p-3 p-md-4">
                <div class="us-surface-muted px-3 py-2 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="small text-muted">Champs marqués * : obligatoires</div>
                    <div class="small text-muted">Laisser le mot de passe vide pour conserver l’actuel</div>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small" role="alert">
                        <?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= $this->url('/utilisateurs/edit/' . (int) ($user['id'] ?? 0)) ?>" data-validate-account-form="1">
                    <h2 class="h6 mb-3"><?= $isCompactAdminEdit ? 'Compte administrateur' : 'Compte utilisateur' ?></h2>
                    <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small" for="nom">Nom *</label>
                    <input class="form-control" id="nom" name="nom" value="<?= e((string) ($user['nom'] ?? '')) ?>" data-required-label="Le nom" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="prenom">Prénom *</label>
                    <input class="form-control" id="prenom" name="prenom" value="<?= e((string) ($user['prenom'] ?? '')) ?>" data-required-label="Le prénom" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="email">Email *</label>
                    <input class="form-control" id="email" type="email" name="email" value="<?= e((string) ($user['email'] ?? '')) ?>" data-required-label="Email" data-validate-email="institutional" required>
                    <div class="form-text">Adresse @gmail.com uniquement.</div>
                </div>

                <?php if (!$isCompactAdminEdit): ?>
                    <div class="col-md-6">
                        <label class="form-label text-muted small" for="role">Rôle *</label>
                        <select class="form-select" id="role" name="role" data-required-label="Le rôle" required>
                            <?php $role = (string) ($user['role'] ?? 'etudiant'); ?>
                            <?php
                            $singleAdminIdExists = $singleAdminId !== null;
                            $isSingleAdminEditingLocal = (bool) ($isSingleAdminEditing ?? false);
                            ?>
                            <option value="etudiant" <?= $role === 'etudiant' ? 'selected' : '' ?> <?= ($singleAdminIdExists && $isSingleAdminEditingLocal) ? 'disabled' : '' ?>>Étudiant</option>
                            <option value="enseignant" <?= $role === 'enseignant' ? 'selected' : '' ?> <?= ($singleAdminIdExists && $isSingleAdminEditingLocal) ? 'disabled' : '' ?>>Enseignant</option>
                            <option value="staff" <?= $role === 'staff' ? 'selected' : '' ?> <?= ($singleAdminIdExists && $isSingleAdminEditingLocal) ? 'disabled' : '' ?>>Staff</option>
                            <option value="admin"
                                <?= $role === 'admin' ? 'selected' : '' ?>
                                <?= ($singleAdminIdExists && !$isSingleAdminEditingLocal) ? 'disabled' : '' ?>
                            >Admin</option>
                        </select>
                        <?php if ($singleAdminIdExists && !$isSingleAdminEditingLocal): ?>
                            <div class="form-text">Le rôle admin est déjà attribué.</div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php $singleAdminIdExists = $singleAdminId !== null; ?>
                    <div class="col-12">
                        <div class="alert alert-info py-2 small mb-0">Le rôle et le statut sont verrouillés pour le compte administrateur unique.</div>
                    </div>
                <?php endif; ?>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="new_password">Nouveau mot de passe</label>
                    <input class="form-control" id="new_password" name="new_password" type="password" placeholder="Laisser vide pour conserver le mot de passe actuel" data-password-label="Le nouveau mot de passe" data-validate-password-min="<?= (int) User::MIN_PASSWORD_LENGTH ?>">
                    <div class="form-text">Optionnel</div>
                </div>

                <?php if (!$isCompactAdminEdit): ?>
                    <div class="col-md-6">
                        <label class="form-label text-muted small" for="statut_compte">Statut du compte *</label>
                        <select class="form-select" id="statut_compte" name="statut_compte" data-required-label="Le statut du compte" required>
                            <?php $statut = (string) ($user['statut_compte'] ?? 'actif'); ?>
                            <option value="actif" <?= $statut === 'actif' ? 'selected' : '' ?>>Actif</option>
                            <option value="inactif" <?= $statut === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                        </select>
                    </div>
                <?php endif; ?>
                    </div>

                    <?php if (!$isCompactAdminEdit): ?>
                        <div class="us-divider my-4"></div>

                        <h2 class="h6 mb-3">Informations complémentaires</h2>
                        <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label text-muted small" for="matricule">Matricule</label>
                        <input class="form-control" id="matricule" name="matricule" value="<?= e((string) ($user['matricule'] ?? '')) ?>">
                        <div class="form-text">Optionnel</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small" for="telephone">Téléphone</label>
                        <input class="form-control" id="telephone" name="telephone" value="<?= e((string) ($user['telephone'] ?? '')) ?>">
                        <div class="form-text">Optionnel</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small" for="departement">Département</label>
                        <input class="form-control" id="departement" name="departement" value="<?= e((string) ($user['departement'] ?? '')) ?>">
                        <div class="form-text">Optionnel</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small" for="niveau">Niveau</label>
                        <input class="form-control" id="niveau" name="niveau" value="<?= e((string) ($user['niveau'] ?? '')) ?>">
                        <div class="form-text">Optionnel</div>
                    </div>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-end align-items-center flex-wrap gap-2 mt-4 pt-2 border-top">
                        <a href="<?= $this->url('/utilisateurs') ?>" class="btn btn-outline-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card us-side-note h-100">
            <div class="card-body p-3 p-md-4">
                <div class="us-kicker mb-1">Aide rapide</div>
                <h2 class="h6 mb-3"><?= $isCompactAdminEdit ? 'Admin unique' : 'Conseils de modification' ?></h2>
                <div class="us-note-list">
                    <div class="us-note-item">
                        <i class="bi bi-key"></i>
                        <div>Laisse le mot de passe vide pour ne pas le changer.</div>
                    </div>
                    <?php if (!$isCompactAdminEdit): ?>
                        <div class="us-note-item">
                            <i class="bi bi-envelope-check"></i>
                            <div>Conserve une adresse @gmail.com valide pour eviter les erreurs de validation.</div>
                        </div>
                        <div class="us-note-item">
                            <i class="bi bi-person-badge"></i>
                            <div>Le matricule et le département aident à retrouver rapidement le compte.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
