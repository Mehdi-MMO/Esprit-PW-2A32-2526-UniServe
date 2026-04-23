<?php
$filters = $filters ?? [];
$pagination = $pagination ?? [];

$error = (string) ($_GET['error'] ?? '');
$singleAdminId = $singleAdminId ?? null;

$q = (string) ($filters['q'] ?? '');
$role = (string) ($filters['role'] ?? '');
$statutCompte = (string) ($filters['statut_compte'] ?? '');

$page = (int) ($pagination['page'] ?? 1);
$pages = (int) ($pagination['pages'] ?? 1);
$total = (int) ($pagination['total'] ?? 0);
$perPage = (int) ($pagination['per_page'] ?? 10);
$stats = $stats ?? [];
$statsTotal = (int) ($stats['total'] ?? 0);
$statsActif = (int) ($stats['actif'] ?? 0);
$statsInactif = (int) ($stats['inactif'] ?? 0);
$statsAdmin = (int) ($stats['admin'] ?? 0);
$statsStaff = (int) ($stats['staff'] ?? 0);

$baseParams = [];
if ($q !== '') {
    $baseParams['q'] = $q;
}
if ($role !== '') {
    $baseParams['role'] = $role;
}
if ($statutCompte !== '') {
    $baseParams['statut_compte'] = $statutCompte;
}
if (!in_array($perPage, [10, 25], true)) {
    $perPage = 10;
}
if ($perPage !== 10) {
    $baseParams['per_page'] = $perPage;
}
$baseQuery = http_build_query($baseParams);
$utilisateursUrl = $this->url('/utilisateurs');
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header">
    <div>
        <div class="us-kicker mb-1">Administration</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Utilisateurs'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="us-page-subtitle">
            <span id="users-results-label">
                <?= $total > 0 ? 'Résultats: ' . (int) $total : 'Gestion des comptes utilisateurs.' ?>
            </span>
        </p>
    </div>

    <a href="<?= $this->url('/utilisateurs/create') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nouveau
    </a>
</div>

<div class="us-surface-muted px-3 py-2 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="small text-muted">Registre des comptes institutionnels</div>
    <div class="small"><strong><?= (int) $total ?></strong> compte(s)</div>
</div>

<div class="row g-2 mb-3">
    <div class="col-md-6 col-xl-3">
        <div class="us-stat-card">
            <div class="us-stat-label">Total comptes</div>
            <div class="us-stat-value"><?= (int) $statsTotal ?></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="us-stat-card">
            <div class="us-stat-label">Comptes actifs</div>
            <div class="us-stat-value"><?= (int) $statsActif ?></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="us-stat-card">
            <div class="us-stat-label">Comptes inactifs</div>
            <div class="us-stat-value"><?= (int) $statsInactif ?></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="us-stat-card">
            <div class="us-stat-label">Admin / Staff</div>
            <div class="us-stat-value"><?= (int) $statsAdmin ?> / <?= (int) $statsStaff ?></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ajaxUrl = '<?= $this->url('/utilisateurs/ajax') ?>';
        const form = document.getElementById('utilisateurs-filters');
        const rowsEl = document.getElementById('users-rows');
        const paginationEl = document.getElementById('users-pagination');
        const resultsLabel = document.getElementById('users-results-label');
        const pageLabel = document.getElementById('users-page-label');

        if (!form || !rowsEl || !paginationEl) {
            return;
        }

        function fetchPage(page) {
            const formData = new FormData(form);
            const params = new URLSearchParams();

            params.set('page', String(page));
            params.set('per_page', String(formData.get('per_page') || '10'));
            params.set('q', String(formData.get('q') || ''));
            params.set('role', String(formData.get('role') || ''));
            params.set('statut_compte', String(formData.get('statut_compte') || ''));

            fetch(ajaxUrl + '?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data && typeof data.rowsHtml === 'string') {
                        rowsEl.innerHTML = data.rowsHtml;
                    }
                    if (data && typeof data.paginationHtml === 'string') {
                        paginationEl.innerHTML = data.paginationHtml;
                    }
                    if (data && data.meta && resultsLabel) {
                        const total = Number(data.meta.total || 0);
                        resultsLabel.textContent = total > 0 ? ('Résultats: ' + total) : 'Gestion des comptes utilisateurs.';
                    }
                    if (data && data.meta && pageLabel) {
                        const current = Number(data.meta.page || 1);
                        const pages = Number(data.meta.pages || 1);
                        pageLabel.textContent = 'Page ' + current + ' / ' + pages;
                    }
                })
                .catch(function () {
                    // Fallback: do nothing, the user can still use classic GET filtering.
                });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            fetchPage(1);
        });

        const resetBtn = form.querySelector('[data-reset-filters="1"]');
        if (resetBtn) {
            resetBtn.addEventListener('click', function (e) {
                e.preventDefault();
                form.reset();
                const qInput = form.querySelector('#q');
                if (qInput) {
                    qInput.value = '';
                }
                fetchPage(1);
            });
        }

        paginationEl.addEventListener('click', function (e) {
            const a = e.target.closest('a.page-link');
            if (!a) {
                return;
            }

            const href = a.getAttribute('href') || '';
            if (href === '#' || a.closest('.disabled')) {
                e.preventDefault();
                return;
            }
            const match = href.match(/[?&]page=(\d+)/);
            if (!match) {
                return;
            }

            e.preventDefault();
            fetchPage(parseInt(match[1], 10));
        });
    });
</script>

<div class="us-section-card">
    <div class="card-body p-3 p-md-4">
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger py-2 small mb-3" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form id="utilisateurs-filters" method="get" action="<?= $this->url('/utilisateurs') ?>" class="row g-2 align-items-end mb-3 us-filter-shell us-users-toolbar">
            <div class="col-lg-4">
                <label class="form-label text-muted small mb-1" for="q">Recherche</label>
                <input
                    type="text"
                    class="form-control"
                    id="q"
                    name="q"
                    placeholder="Nom, prénom, email..."
                    value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="col-lg-2">
                <label class="form-label text-muted small mb-1" for="role">Rôle</label>
                <select class="form-select" id="role" name="role">
                    <option value="" <?= $role === '' ? 'selected' : '' ?>>Tous</option>
                    <option value="etudiant" <?= $role === 'etudiant' ? 'selected' : '' ?>>Étudiant</option>
                    <option value="enseignant" <?= $role === 'enseignant' ? 'selected' : '' ?>>Enseignant</option>
                    <option value="staff" <?= $role === 'staff' ? 'selected' : '' ?>>Staff</option>
                    <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>

            <div class="col-lg-2">
                <label class="form-label text-muted small mb-1" for="statut_compte">Statut</label>
                <select class="form-select" id="statut_compte" name="statut_compte">
                    <option value="" <?= $statutCompte === '' ? 'selected' : '' ?>>Tous</option>
                    <option value="actif" <?= $statutCompte === 'actif' ? 'selected' : '' ?>>Actif</option>
                    <option value="inactif" <?= $statutCompte === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                </select>
            </div>

            <div class="col-lg-2">
                <label class="form-label text-muted small mb-1" for="per_page">Par page</label>
                <select class="form-select" id="per_page" name="per_page" onchange="this.form.submit()">
                    <option value="10" <?= $perPage === 10 ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= $perPage === 25 ? 'selected' : '' ?>>25</option>
                </select>
            </div>

            <div class="col-lg-2 d-grid d-sm-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filtrer</button>
                <a href="<?= $this->url('/utilisateurs') ?>" class="btn btn-outline-secondary btn-sm w-100" data-reset-filters="1">Réinitialiser</a>
            </div>
        </form>

        <div class="table-responsive us-table-wrap">
            <table class="table table-hover align-middle mb-0 us-users-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Matricule</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="users-rows">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4 us-empty-state">Aucun utilisateur trouvé.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold us-user-name">
                                        <?= htmlspecialchars((string) ($u['prenom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                        <?= htmlspecialchars((string) ($u['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="text-muted small us-user-meta"><?= htmlspecialchars((string) ($u['departement'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td><?= htmlspecialchars((string) ($u['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php $roleValue = strtolower((string) ($u['role'] ?? '')); ?>
                                    <span class="us-role-chip <?= htmlspecialchars($roleValue, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars(ucfirst($roleValue), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars((string) ($u['matricule'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($u['telephone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php
                                    $statut = (string) ($u['statut_compte'] ?? '');
                                    $badgeClass = $statut === 'actif' ? 'actif' : 'inactif';
                                    ?>
                                    <span class="us-status-chip <?= $badgeClass ?>">
                                        <?= htmlspecialchars(ucfirst($statut), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="us-action-group">
                                        <a class="btn btn-outline-primary btn-sm" href="<?= $this->url('/utilisateurs/edit/' . (int) ($u['id'] ?? 0)) ?>">
                                            <i class="bi bi-pencil me-1"></i>Modifier
                                        </a>
                                    <?php
                                    $userId = (int) ($u['id'] ?? 0);
                                    $isSingleAdmin = $singleAdminId !== null && $userId === (int) $singleAdminId;
                                    ?>
                                    <?php if ($isSingleAdmin): ?>
                                        <button class="btn btn-outline-danger btn-sm" type="button" disabled title="Suppression bloquée (admin unique)">
                                            <i class="bi bi-trash me-1"></i>Supprimer
                                        </button>
                                    <?php else: ?>
                                        <form method="post" action="<?= $this->url('/utilisateurs/delete/' . $userId) ?>" class="d-inline">
                                            <button class="btn btn-outline-danger btn-sm" type="submit" onclick="return confirm('Supprimer cet utilisateur ?');">
                                                <i class="bi bi-trash me-1"></i>Supprimer
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3">
            <div class="text-muted small" id="users-page-label">
                Page <?= (int) $page ?> / <?= (int) $pages ?>
            </div>
            <div id="users-pagination">
                <?php if ($pages > 1): ?>
                    <nav aria-label="Pagination">
                        <ul class="pagination mb-0">
                            <?php
                            $makeLink = function (int $targetPage) use ($utilisateursUrl, $baseQuery): string {
                                return $utilisateursUrl . ($baseQuery !== '' ? '?' . $baseQuery . '&' : '?') . 'page=' . $targetPage;
                            };

                            $prev = $page - 1;
                            $next = $page + 1;
                            ?>

                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $page <= 1 ? '#' : $this->url('/utilisateurs') . ($baseQuery !== '' ? '?' . $baseQuery . '&' : '?') . 'page=' . $prev ?>"
                                   aria-disabled="<?= $page <= 1 ? 'true' : 'false' ?>">Précédent</a>
                            </li>

                            <?php
                            $start = max(1, $page - 2);
                            $end = min($pages, $page + 2);
                            for ($p = $start; $p <= $end; $p++): ?>
                                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $this->url('/utilisateurs') . ($baseQuery !== '' ? '?' . $baseQuery . '&' : '?') . 'page=' . $p ?>"><?= (int) $p ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $page >= $pages ? '#' : $this->url('/utilisateurs') . ($baseQuery !== '' ? '?' . $baseQuery . '&' : '?') . 'page=' . $next ?>"
                                   aria-disabled="<?= $page >= $pages ? 'true' : 'false' ?>">Suivant</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
