<?php
declare(strict_types=1);
/** @var string $clubsEventsSubnavActive 'clubs' | 'events' */
$clubsEventsSubnavActive = $clubsEventsSubnavActive ?? 'clubs';
?>
<div class="us-module-switch card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center justify-content-lg-between gap-3 gap-lg-4">
            <div class="us-module-switch__heading flex-shrink-0">
                <div class="us-module-switch__eyebrow text-uppercase">Module administration</div>
                <div class="us-module-switch__title">Clubs &amp; événements</div>
                <p class="us-module-switch__hint text-muted small mb-0 d-none d-md-block">
                    Choisissez la vue à administrer : gestion des associations ou des événements.
                </p>
            </div>
            <nav class="us-segment-nav align-self-stretch align-self-lg-center" aria-label="Basculer entre clubs et événements">
                <a href="<?= $this->url('/evenements/manageClubs') ?>"
                   class="us-segment-nav__link <?= $clubsEventsSubnavActive === 'clubs' ? 'is-active' : '' ?>"
                    <?= $clubsEventsSubnavActive === 'clubs' ? 'aria-current="page"' : '' ?>>
                    <i class="bi bi-collection-fill us-segment-nav__icon" aria-hidden="true"></i>
                    <span>Clubs</span>
                </a>
                <a href="<?= $this->url('/evenements/manage') ?>"
                   class="us-segment-nav__link <?= $clubsEventsSubnavActive === 'events' ? 'is-active' : '' ?>"
                    <?= $clubsEventsSubnavActive === 'events' ? 'aria-current="page"' : '' ?>>
                    <i class="bi bi-calendar-event-fill us-segment-nav__icon" aria-hidden="true"></i>
                    <span>Événements</span>
                </a>
            </nav>
        </div>
    </div>
</div>
