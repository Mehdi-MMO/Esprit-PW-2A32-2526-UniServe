<?php
/**
 * @var list<array<string, mixed>> $notifications
 * @var list<array<string, mixed>> $unread
 * @var int $unread_count
 */

$notifications = is_array($notifications ?? null) ? $notifications : [];
$unreadCount = (int) ($unread_count ?? 0);

$filter = strtolower((string) ($_GET['filter'] ?? 'all'));
if (!in_array($filter, ['all', 'unread'], true)) {
    $filter = 'all';
}

$visibleNotifications = $filter === 'unread'
    ? array_values(array_filter($notifications, static function (array $n): bool {
        return (int) ($n['lu'] ?? 0) !== 1;
    }))
    : $notifications;

$totalCount = count($notifications);
$visibleCount = count($visibleNotifications);

$inferType = static function (string $lien): array {
    $link = strtolower($lien);
    if (str_contains($link, '/demandes') || str_contains($link, '/services')) {
        return ['key' => 'demandes', 'icon' => 'fa-solid fa-envelope-open-text', 'label' => 'Demande'];
    }
    if (str_contains($link, '/rendezvous')) {
        return ['key' => 'rendezvous', 'icon' => 'fa-solid fa-calendar-check', 'label' => 'Rendez-vous'];
    }
    if (str_contains($link, '/evenements') || str_contains($link, '/events')) {
        return ['key' => 'evenements', 'icon' => 'fa-solid fa-calendar-day', 'label' => 'Événement'];
    }
    if (str_contains($link, '/certifications')) {
        return ['key' => 'certifications', 'icon' => 'fa-solid fa-graduation-cap', 'label' => 'Certification'];
    }
    if (str_contains($link, '/documents')) {
        return ['key' => 'documents', 'icon' => 'fa-solid fa-folder-open', 'label' => 'Document'];
    }
    return ['key' => 'default', 'icon' => 'fa-regular fa-bell', 'label' => 'Notification'];
};

$formatTimeAgo = static function (string $createdAt): string {
    if ($createdAt === '') {
        return '';
    }
    $timestamp = strtotime($createdAt);
    if ($timestamp === false) {
        return '';
    }
    $seconds = max(0, time() - $timestamp);
    if ($seconds < 45) {
        return "à l’instant";
    }
    $minutes = (int) floor($seconds / 60);
    if ($minutes < 60) {
        return 'il y a ' . $minutes . ' min';
    }
    $hours = (int) floor($minutes / 60);
    if ($hours < 24) {
        return 'il y a ' . $hours . ' h';
    }
    $days = (int) floor($hours / 24);
    if ($days === 1) {
        return 'hier';
    }
    if ($days < 7) {
        return 'il y a ' . $days . ' j';
    }
    return date('d/m/Y H:i', $timestamp);
};

$groupKey = static function (string $createdAt): string {
    $timestamp = strtotime($createdAt);
    if ($timestamp === false) {
        return 'older';
    }
    $created = (new DateTimeImmutable('@' . $timestamp))->setTime(0, 0, 0);
    $today = (new DateTimeImmutable('today'))->setTime(0, 0, 0);
    $diffDays = (int) $created->diff($today)->format('%r%a');
    if ($diffDays <= 0) {
        return 'today';
    }
    if ($diffDays === 1) {
        return 'yesterday';
    }
    if ($diffDays <= 6) {
        return 'this_week';
    }
    return 'older';
};

$groupLabels = [
    'today' => "Aujourd'hui",
    'yesterday' => 'Hier',
    'this_week' => 'Cette semaine',
    'older' => 'Plus tôt',
];

$groups = ['today' => [], 'yesterday' => [], 'this_week' => [], 'older' => []];
foreach ($visibleNotifications as $notif) {
    $groups[$groupKey((string) ($notif['cree_le'] ?? ''))][] = $notif;
}

$filterAllUrl = $this->url('/notifications');
$filterUnreadUrl = $this->url('/notifications') . '?filter=unread';
?>

<section class="us-notifs-page">
    <header class="us-fo-header mb-3">
        <div class="us-fo-header-text">
            <div class="us-kicker mb-1">Centre de notifications</div>
            <h1 class="us-fo-title">Notifications</h1>
            <p class="us-fo-subtitle mb-0">
                <?php if ($unreadCount > 0): ?>
                    Vous avez <strong><?= (int) $unreadCount ?></strong> notification<?= $unreadCount > 1 ? 's' : '' ?> non lue<?= $unreadCount > 1 ? 's' : '' ?>.
                <?php else: ?>
                    Tout est à jour. Bon travail&nbsp;!
                <?php endif; ?>
            </p>
        </div>
        <div class="us-fo-header-actions">
            <?php if ($unreadCount > 0): ?>
                <form method="post" action="<?= $this->url('/notifications/markAllRead') ?>" class="m-0">
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-check-double me-1" aria-hidden="true"></i> Tout marquer lu
                    </button>
                </form>
            <?php endif; ?>
            <a class="btn btn-primary btn-sm" href="<?= $this->url('/frontoffice/dashboard') ?>">
                <i class="fa-solid fa-house me-1" aria-hidden="true"></i> Retour au tableau de bord
            </a>
        </div>
    </header>

    <div class="us-notifs-toolbar mb-3">
        <div class="us-notifs-filter" role="tablist" aria-label="Filtrer les notifications">
            <a class="us-notifs-filter-chip <?= $filter === 'all' ? 'is-active' : '' ?>"
               href="<?= htmlspecialchars($filterAllUrl, ENT_QUOTES, 'UTF-8') ?>"
               role="tab"
               aria-selected="<?= $filter === 'all' ? 'true' : 'false' ?>">
                Toutes
                <span class="us-notifs-filter-count"><?= (int) $totalCount ?></span>
            </a>
            <a class="us-notifs-filter-chip <?= $filter === 'unread' ? 'is-active' : '' ?>"
               href="<?= htmlspecialchars($filterUnreadUrl, ENT_QUOTES, 'UTF-8') ?>"
               role="tab"
               aria-selected="<?= $filter === 'unread' ? 'true' : 'false' ?>">
                Non lues
                <span class="us-notifs-filter-count"><?= (int) $unreadCount ?></span>
            </a>
        </div>
        <div class="us-notifs-toolbar-info text-muted small">
            <?php if ($visibleCount > 0): ?>
                <?= (int) $visibleCount ?> affichée<?= $visibleCount > 1 ? 's' : '' ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($visibleCount === 0): ?>
        <div class="us-notifs-empty us-notifs-empty--page">
            <div class="us-notifs-empty-icon">
                <i class="fa-regular fa-bell-slash" aria-hidden="true"></i>
            </div>
            <div class="us-notifs-empty-title">
                <?= $filter === 'unread' ? 'Aucune notification non lue' : 'Aucune notification pour le moment' ?>
            </div>
            <div class="us-notifs-empty-sub">
                <?= $filter === 'unread'
                    ? 'Tout est à jour. Vous pouvez souffler.'
                    : 'Les nouvelles alertes apparaîtront ici dès qu’il y aura de l’activité.' ?>
            </div>
            <?php if ($filter === 'unread' && $totalCount > 0): ?>
                <a class="btn btn-sm btn-outline-secondary mt-3" href="<?= htmlspecialchars($filterAllUrl, ENT_QUOTES, 'UTF-8') ?>">
                    Voir toutes les notifications
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="us-notifs-page-list">
            <?php foreach ($groups as $key => $items): ?>
                <?php if (empty($items)) { continue; } ?>
                <section class="us-notif-group">
                    <h2 class="us-notif-group-title"><?= htmlspecialchars($groupLabels[$key] ?? $key, ENT_QUOTES, 'UTF-8') ?></h2>
                    <ul class="us-notif-group-list list-unstyled mb-0">
                        <?php foreach ($items as $n): ?>
                            <?php
                            $nid = (int) ($n['id'] ?? 0);
                            $lu = (int) ($n['lu'] ?? 0) === 1;
                            $msg = (string) ($n['message'] ?? '');
                            $lien = isset($n['lien']) ? trim((string) $n['lien']) : '';
                            $cree = (string) ($n['cree_le'] ?? '');
                            $type = $inferType($lien);
                            $timeAgo = $formatTimeAgo($cree);
                            ?>
                            <li class="us-notif-row<?= $lu ? '' : ' is-unread' ?>">
                                <span class="us-notifs-icon us-notifs-icon--<?= htmlspecialchars($type['key'], ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true">
                                    <i class="<?= htmlspecialchars($type['icon'], ENT_QUOTES, 'UTF-8') ?>"></i>
                                </span>
                                <div class="us-notif-row-body">
                                    <div class="us-notif-row-top">
                                        <span class="us-notif-row-type"><?= htmlspecialchars($type['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php if ($timeAgo !== ''): ?>
                                            <span class="us-notif-row-time" title="<?= htmlspecialchars($cree, ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($timeAgo, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="us-notif-row-msg mb-2"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></p>
                                    <div class="us-notif-row-actions">
                                        <?php if ($lien !== ''): ?>
                                            <a href="<?= htmlspecialchars($this->url($lien), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-primary">
                                                <i class="fa-solid fa-arrow-up-right-from-square me-1" aria-hidden="true"></i> Ouvrir
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!$lu): ?>
                                            <form method="post" action="<?= $this->url('/notifications/markRead/' . $nid) ?>" class="m-0">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fa-solid fa-check me-1" aria-hidden="true"></i> Marquer lu
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="us-notif-row-status">
                                                <i class="fa-solid fa-circle-check me-1" aria-hidden="true"></i> Lue
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (!$lu): ?>
                                    <span class="us-notif-row-dot" aria-label="Non lue" title="Non lue"></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
