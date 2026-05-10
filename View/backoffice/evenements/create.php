<?php
// Include helpers for rendering UI components
require_once __DIR__ . '/../../shared/helpers.php';

function e(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

function dt(string $v): string
{
    $trimmed = trim($v);
    if ($trimmed === '') {
        return '';
    }

    return str_replace(' ', 'T', substr($trimmed, 0, 16));
}

$old = $old ?? [];
$clubs = $clubs ?? [];
$error = (string) ($error ?? '');
$validationRules = $validationRules ?? [];
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header mb-4">
    <div>
        <div class="us-kicker mb-1">Gestion des événements</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Créer un événement'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Planifier un nouvel événement universitaire.</p>
    </div>
    <a href="<?= $this->url('/evenements/manage') ?>" class="btn btn-outline-secondary btn-sm">Retour</a>
</div>

<div class="us-section-card">
    <div class="card-body p-3 p-md-4">
        <?php if ($error !== ''): ?>
            <?php echo renderErrorAlert($error); ?>
        <?php endif; ?>

        <form method="post" action="<?= $this->url('/evenements/create') ?>" id="event-form">
            <!-- Event Title and Club Section -->
            <?php echo renderFormSection('Informations générales'); ?>
            
            <div class="row g-3">
                <div class="col-md-8">
                    <?php 
                    echo renderFormField(
                        'titre',
                        'Titre de l\'événement',
                        'text',
                        $old['titre'] ?? '',
                        [
                            'required' => true,
                            'placeholder' => 'Ex: Conférence sur l\'IA',
                        ],
                        ''
                    );
                    ?>
                </div>

                <div class="col-md-4">
                    <?php 
                    $clubOptions = ['0' => 'Général'];
                    foreach ($clubs as $club) {
                        $clubId = (int) ($club['id'] ?? 0);
                        $clubOptions[$clubId] = (string) ($club['nom'] ?? '');
                    }
                    $selectedClub = (int) ($old['club_id'] ?? 0);
                    echo renderSelectField(
                        'club_id',
                        'Club',
                        $clubOptions,
                        $selectedClub > 0 ? $selectedClub : '0',
                        false,
                        ''
                    );
                    ?>
                </div>
            </div>

            <!-- Location and Details Section -->
            <?php echo renderFormSection('Lieu et description'); ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <?php 
                    echo renderFormField(
                        'lieu',
                        'Lieu de l\'événement',
                        'text',
                        $old['lieu'] ?? '',
                        [
                            'required' => true,
                            'placeholder' => 'Ex: Salle 101, Amphi A...',
                        ],
                        ''
                    );
                    ?>
                </div>

                <div class="col-md-6">
                    <?php 
                    echo renderFormField(
                        'capacite',
                        'Capacité (nombre de places)',
                        'number',
                        $old['capacite'] ?? '',
                        [
                            'required' => false,
                            'placeholder' => 'Ex: 50',
                            'min' => 1,
                            'max' => 500,
                        ],
                        ''
                    );
                    ?>
                </div>

                <div class="col-12">
                    <?php 
                    echo renderFormField(
                        'description',
                        'Description de l\'événement',
                        'textarea',
                        $old['description'] ?? '',
                        [
                            'required' => false,
                            'placeholder' => 'Décrivez le contenu et les détails de l\'événement...',
                            'rows' => 4,
                        ],
                        ''
                    );
                    ?>
                </div>
            </div>

            <!-- Date and Time Section -->
            <?php echo renderFormSection('Date et heure'); ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <?php 
                    echo renderFormField(
                        'date_debut',
                        'Date et heure de début',
                        'datetime-local',
                        dt((string) ($old['date_debut'] ?? '')),
                        [
                            'required' => true,
                        ],
                        ''
                    );
                    ?>
                </div>

                <div class="col-md-6">
                    <?php 
                    echo renderFormField(
                        'date_fin',
                        'Date et heure de fin',
                        'datetime-local',
                        dt((string) ($old['date_fin'] ?? '')),
                        [
                            'required' => true,
                        ],
                        ''
                    );
                    ?>
                </div>
            </div>

            <!-- Status Section -->
            <?php echo renderFormSection('Statut'); ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <?php 
                    $statusOptions = [
                        'planifie' => 'Planifié',
                        'ouvert' => 'Ouvert aux inscriptions',
                        'complet' => 'Complet',
                        'termine' => 'Terminé',
                        'annule' => 'Annulé',
                    ];
                    $currentStatus = (string) ($old['statut'] ?? 'planifie');
                    echo renderSelectField(
                        'statut',
                        'Statut de l\'événement',
                        $statusOptions,
                        $currentStatus,
                        true,
                        ''
                    );
                    ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="<?= $this->url('/evenements/manage') ?>" class="btn btn-secondary">Annuler</a>
                <button class="btn btn-primary" type="submit">Créer l'événement</button>
            </div>
        </form>
    </div>
</div>

<!-- Load validation script -->
<script src="<?= $this->url('/shared/js/validation.js') ?>"></script>
