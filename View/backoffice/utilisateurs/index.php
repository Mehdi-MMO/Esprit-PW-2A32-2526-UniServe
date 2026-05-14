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

<div class="back-page-header">
    <div class="back-page-header-left">
        <div class="back-page-icon"><i class="bi bi-people-fill"></i></div>
        <div>
            <div class="back-page-title"><?= htmlspecialchars((string) ($title ?? 'Utilisateurs'), ENT_QUOTES, 'UTF-8') ?></div>
            <div class="back-page-sub" id="users-results-label">
                <?= $total > 0 ? 'Résultats: ' . (int) $total : 'Gestion des comptes utilisateurs.' ?>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $this->url('/utilisateurs/create') ?>" class="btn btn-primary btn-sm" style="border-radius:10px; font-weight:600;">
            <i class="bi bi-plus-lg me-1"></i>Nouveau
        </a>
    </div>
</div>

<div class="back-stats-row mb-4">
    <span class="back-stat-pill" style="background:#f3f4f6;color:#374151;border-color:#e5e7eb;">
        <i class="bi bi-people-fill"></i><?= (int) $statsTotal ?> Total
    </span>
    <span class="back-stat-pill" style="background:#dcfce7;color:#166534;border-color:#86efac;">
        <i class="bi bi-check-circle-fill"></i><?= (int) $statsActif ?> Actifs
    </span>
    <span class="back-stat-pill" style="background:#fee2e2;color:#991b1b;border-color:#fca5a5;">
        <i class="bi bi-x-circle-fill"></i><?= (int) $statsInactif ?> Inactifs
    </span>
    <span class="back-stat-pill" style="background:#e0e7ff;color:#4338ca;border-color:#c7d2fe;">
        <i class="bi bi-shield-lock-fill"></i><?= (int) $statsAdmin ?> Admins
    </span>
    <span class="back-stat-pill" style="background:#e0f2fe;color:#0369a1;border-color:#bae6fd;">
        <i class="bi bi-person-workspace"></i><?= (int) $statsStaff ?> Staffs
    </span>
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

        <form id="utilisateurs-filters" method="get" action="<?= $this->url('/utilisateurs') ?>" class="mb-4 d-flex flex-wrap gap-2 align-items-center">
            <input type="text" class="form-control" id="q" name="q" placeholder="Nom, prénom, email..." value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" style="max-width:250px; border-radius:10px; font-size:.9rem; font-weight:600;">
            
            <select class="form-select" id="role" name="role" style="max-width:150px; border-radius:10px; font-size:.9rem; font-weight:600;">
                <option value="" <?= $role === '' ? 'selected' : '' ?>>Tous rôles</option>
                <option value="etudiant" <?= $role === 'etudiant' ? 'selected' : '' ?>>Étudiant</option>
                <option value="enseignant" <?= $role === 'enseignant' ? 'selected' : '' ?>>Enseignant</option>
                <option value="staff" <?= $role === 'staff' ? 'selected' : '' ?>>Staff</option>
                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            
            <select class="form-select" id="statut_compte" name="statut_compte" style="max-width:150px; border-radius:10px; font-size:.9rem; font-weight:600;">
                <option value="" <?= $statutCompte === '' ? 'selected' : '' ?>>Tous statuts</option>
                <option value="actif" <?= $statutCompte === 'actif' ? 'selected' : '' ?>>Actif</option>
                <option value="inactif" <?= $statutCompte === 'inactif' ? 'selected' : '' ?>>Inactif</option>
            </select>
            
            <select class="form-select" id="per_page" name="per_page" onchange="this.form.submit()" style="max-width:100px; border-radius:10px; font-size:.9rem; font-weight:600;">
                <option value="10" <?= $perPage === 10 ? 'selected' : '' ?>>10 / pg</option>
                <option value="25" <?= $perPage === 25 ? 'selected' : '' ?>>25 / pg</option>
            </select>
            
            <button type="submit" class="btn btn-primary" style="border-radius:10px; font-weight:600;"><i class="bi bi-search me-1"></i> Filtrer</button>
            <?php if ($q !== '' || $role !== '' || $statutCompte !== ''): ?>
                <button type="button" data-reset-filters="1" class="btn btn-outline-secondary" style="border-radius:8px;">Réinitialiser</button>
            <?php endif; ?>
        </form>

        <div class="back-table-wrap">
            <table class="back-table">
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
                                    <div class="back-student-cell">
                                        <div class="back-avatar"><?= mb_substr($u['prenom'] ?? 'U', 0, 1) ?><?= mb_substr($u['nom'] ?? '', 0, 1) ?></div>
                                        <div>
                                            <div class="back-student-name">
                                                <?= htmlspecialchars((string) ($u['prenom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                <?= htmlspecialchars((string) ($u['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <div class="text-muted small" style="font-size:0.75rem;"><?= htmlspecialchars((string) ($u['departement'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars((string) ($u['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php 
                                        $roleLabel = htmlspecialchars(ucfirst((string) ($u['role'] ?? '')), ENT_QUOTES, 'UTF-8');
                                        $rBg = '#f3f4f6'; $rColor = '#4b5563'; $rBorder = '#e5e7eb';
                                        if ($roleLabel === 'Admin') { $rBg = '#fee2e2'; $rColor = '#991b1b'; $rBorder = '#fca5a5'; }
                                        if ($roleLabel === 'Staff') { $rBg = '#e0f2fe'; $rColor = '#0369a1'; $rBorder = '#bae6fd'; }
                                    ?>
                                    <span class="badge" style="background:<?= $rBg ?>;color:<?= $rColor ?>;border:1px solid <?= $rBorder ?>;border-radius:6px;padding:5px 8px;">
                                        <?= $roleLabel ?>
                                    </span>
                                </td>
                                <td class="fw-semibold text-secondary"><?= htmlspecialchars((string) ($u['matricule'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($u['telephone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php
                                    $statut = (string) ($u['statut_compte'] ?? '');
                                    $sBg = $statut === 'actif' ? '#dcfce7' : '#fee2e2';
                                    $sColor = $statut === 'actif' ? '#166534' : '#991b1b';
                                    $sBorder = $statut === 'actif' ? '#86efac' : '#fca5a5';
                                    ?>
                                    <span class="badge" style="background:<?= $sBg ?>;color:<?= $sColor ?>;border:1px solid <?= $sBorder ?>;border-radius:6px;padding:5px 8px;">
                                        <?= htmlspecialchars(ucfirst($statut), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex flex-column gap-1 justify-content-end align-items-end">
                                        <a class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" href="<?= $this->url('/utilisateurs/edit/' . (int) ($u['id'] ?? 0)) ?>" style="border-radius:6px; width:32px; height:32px;" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    <?php
                                    $userId = (int) ($u['id'] ?? 0);
                                    $isSingleAdmin = $singleAdminId !== null && $userId === (int) $singleAdminId;
                                    ?>
                                    <?php if ($isSingleAdmin): ?>
                                        <button class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center" type="button" disabled title="Suppression bloquée (admin unique)" style="border-radius:6px; width:32px; height:32px;">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    <?php else: ?>
                                        <form method="post" action="<?= $this->url('/utilisateurs/delete/' . $userId) ?>" class="m-0 d-inline">
                                            <button class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center" type="submit" onclick="return confirm('Supprimer cet utilisateur ?');" style="border-radius:6px; width:32px; height:32px;" title="Supprimer">
                                                <i class="bi bi-trash3-fill"></i>
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
