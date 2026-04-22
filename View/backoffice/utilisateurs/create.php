<?php
function e(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Créer un utilisateur'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Créer un compte utilisateur.</p>
    </div>

    <a href="<?= $this->url('/utilisateurs') ?>" class="btn btn-outline-secondary btn-sm">Retour</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small" role="alert">
                <?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= $this->url('/utilisateurs/create') ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small" for="nom">Nom *</label>
                    <input class="form-control" id="nom" name="nom" value="<?= e((string) ($old['nom'] ?? '')) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="prenom">Prénom *</label>
                    <input class="form-control" id="prenom" name="prenom" value="<?= e((string) ($old['prenom'] ?? '')) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="email">Email *</label>
                    <input class="form-control" id="email" name="email" type="email" value="<?= e((string) ($old['email'] ?? '')) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="password">Mot de passe *</label>
                    <input class="form-control" id="password" name="password" type="password" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="role">Rôle *</label>
                    <select class="form-select" id="role" name="role" required>
                        <?php
                        $role = (string) ($old['role'] ?? 'etudiant');
                        $adminAllowed = !isset($singleAdminId) || $singleAdminId === null;
                        ?>
                        <option value="etudiant" <?= $role === 'etudiant' ? 'selected' : '' ?>>Étudiant</option>
                        <option value="enseignant" <?= $role === 'enseignant' ? 'selected' : '' ?>>Enseignant</option>
                        <option value="staff" <?= $role === 'staff' ? 'selected' : '' ?>>Staff</option>
                        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?> <?= $adminAllowed ? '' : 'disabled' ?>>Admin</option>
                    </select>
                    <?php if (!$adminAllowed): ?>
                        <div class="form-text">Le rôle admin est déjà attribué.</div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="statut_compte">Statut du compte *</label>
                    <select class="form-select" id="statut_compte" name="statut_compte" required>
                        <?php $statut = (string) ($old['statut_compte'] ?? 'actif'); ?>
                        <option value="actif" <?= $statut === 'actif' ? 'selected' : '' ?>>Actif</option>
                        <option value="inactif" <?= $statut === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="matricule">Matricule</label>
                    <input class="form-control" id="matricule" name="matricule" value="<?= e((string) ($old['matricule'] ?? '')) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="telephone">Téléphone</label>
                    <input class="form-control" id="telephone" name="telephone" value="<?= e((string) ($old['telephone'] ?? '')) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="departement">Département</label>
                    <input class="form-control" id="departement" name="departement" value="<?= e((string) ($old['departement'] ?? '')) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="niveau">Niveau</label>
                    <input class="form-control" id="niveau" name="niveau" value="<?= e((string) ($old['niveau'] ?? '')) ?>">
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary">Créer</button>
            </div>
        </form>
    </div>
</div>
