<?php
function e(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header">
    <div>
        <div class="us-kicker mb-1">Gestion des comptes</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Modifier un utilisateur'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Mettre à jour les informations et, si besoin, le mot de passe.</p>
    </div>

    <a href="<?= $this->url('/utilisateurs') ?>" class="btn btn-outline-secondary btn-sm">Retour</a>
</div>

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

        <form method="post" action="<?= $this->url('/utilisateurs/edit/' . (int) ($user['id'] ?? 0)) ?>">
            <h2 class="h6 mb-3">Compte utilisateur</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small" for="nom">Nom *</label>
                    <input class="form-control" id="nom" name="nom" value="<?= e((string) ($user['nom'] ?? '')) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="prenom">Prénom *</label>
                    <input class="form-control" id="prenom" name="prenom" value="<?= e((string) ($user['prenom'] ?? '')) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="email">Email *</label>
                    <input class="form-control" id="email" type="email" name="email" value="<?= e((string) ($user['email'] ?? '')) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="role">Rôle *</label>
                    <select class="form-select" id="role" name="role" required>
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
                    <?php elseif ($singleAdminIdExists && $isSingleAdminEditingLocal): ?>
                        <div class="form-text">Ce compte est l’admin unique.</div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="new_password">Nouveau mot de passe</label>
                    <input class="form-control" id="new_password" name="new_password" type="password" placeholder="Laisser vide pour conserver le mot de passe actuel">
                    <div class="form-text">Optionnel</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="statut_compte">Statut du compte *</label>
                    <select class="form-select" id="statut_compte" name="statut_compte" required>
                        <?php $statut = (string) ($user['statut_compte'] ?? 'actif'); ?>
                        <option value="actif" <?= $statut === 'actif' ? 'selected' : '' ?>>Actif</option>
                        <option value="inactif" <?= $statut === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                    </select>
                </div>
            </div>

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

            <div class="d-flex justify-content-end mt-4 pt-2 border-top">
                <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
