<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= htmlspecialchars($title) ?></h1>
    <?php if (in_array($_SESSION['user']['role'], ['etudiant', 'enseignant'])): ?>
    <a href="<?= $this->url('/demandes/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Nouvelle Demande</a>
    <?php endif; ?>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Titre</th>
                        <th>Service</th>
                        <?php if (in_array($_SESSION['user']['role'], ['staff', 'admin'])): ?>
                        <th>Utilisateur</th>
                        <?php endif; ?>
                        <th>Date</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($demandes)): ?>
                    <tr>
                        <td colspan="<?= in_array($_SESSION['user']['role'], ['staff', 'admin']) ? 6 : 5 ?>" class="text-center py-4 text-muted">Aucune demande trouvée.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($demandes as $demande): ?>
                        <tr>
                            <td class="fw-medium"><?= htmlspecialchars($demande['titre']) ?></td>
                            <td><?= htmlspecialchars($demande['service_nom']) ?></td>
                            <?php if (in_array($_SESSION['user']['role'], ['staff', 'admin'])): ?>
                            <td><?= htmlspecialchars($demande['user_prenom'] . ' ' . $demande['user_nom']) ?></td>
                            <?php endif; ?>
                            <td><?= date('d/m/Y', strtotime($demande['date_creation'])) ?></td>
                            <td>
                                <?php
                                $statusClasses = [
                                    'en_attente' => 'bg-warning text-dark',
                                    'en_cours' => 'bg-info text-dark',
                                    'traite' => 'bg-success',
                                    'rejete' => 'bg-danger'
                                ];
                                $statusLabels = [
                                    'en_attente' => 'En attente',
                                    'en_cours' => 'En cours',
                                    'traite' => 'Traité',
                                    'rejete' => 'Rejeté'
                                ];
                                $class = $statusClasses[$demande['statut']] ?? 'bg-secondary';
                                $label = $statusLabels[$demande['statut']] ?? $demande['statut'];
                                ?>
                                <span class="badge <?= $class ?>"><?= $label ?></span>
                            </td>
                            <td class="text-end">
                                <?php if (in_array($_SESSION['user']['role'], ['staff', 'admin'])): ?>
                                    <form action="<?= $this->url('/demandes/updateStatut/' . $demande['id']) ?>" method="POST" class="d-inline">
                                        <select name="statut" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                            <option value="en_attente" <?= $demande['statut'] === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                            <option value="en_cours" <?= $demande['statut'] === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                                            <option value="traite" <?= $demande['statut'] === 'traite' ? 'selected' : '' ?>>Traité</option>
                                            <option value="rejete" <?= $demande['statut'] === 'rejete' ? 'selected' : '' ?>>Rejeté</option>
                                        </select>
                                    </form>
                                <?php endif; ?>

                                <?php if (in_array($_SESSION['user']['role'], ['etudiant', 'enseignant']) && $demande['statut'] === 'en_attente'): ?>
                                    <a href="<?= $this->url('/demandes/edit/' . $demande['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <?php endif; ?>

                                <?php if (in_array($_SESSION['user']['role'], ['staff', 'admin']) || $demande['utilisateur_id'] == $_SESSION['user']['id']): ?>
                                <form action="<?= $this->url('/demandes/delete/' . $demande['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ?');">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
