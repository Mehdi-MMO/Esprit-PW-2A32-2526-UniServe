<?php
// Include helpers for rendering UI components
require_once __DIR__ . '/../../shared/helpers.php';

function e(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

$club = $club ?? [];
$error = (string) ($error ?? '');
$isActif = (int) ($club['actif'] ?? 0) === 1;
$clubId = (int) ($club['id'] ?? 0);
$validationRules = $validationRules ?? [];
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header mb-4">
    <div>
        <div class="us-kicker mb-1">Gestion des clubs</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Modifier un club'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Mettre à jour les informations du club.</p>
    </div>
    <a href="<?= $this->url('/evenements/manageClubs') ?>" class="btn btn-outline-secondary btn-sm">Retour</a>
</div>

<div class="us-section-card">
    <div class="card-body p-3 p-md-4">
        <?php if ($error !== ''): ?>
            <?php echo renderErrorAlert($error); ?>
        <?php endif; ?>

        <form method="post" action="<?= $this->url('/evenements/editClub/' . $clubId) ?>" id="club-form">
            <!-- Basic Information Section -->
            <?php echo renderFormSection('Informations de base'); ?>
            
            <div class="row g-3">
                <div class="col-md-8">
                    <?php 
                    echo renderFormField(
                        'nom',
                        'Nom du club',
                        'text',
                        $club['nom'] ?? '',
                        [
                            'required' => true,
                            'placeholder' => 'Ex: Club de débat',
                        ],
                        $error === 'Le nom du club est obligatoire.' ? $error : ''
                    );
                    ?>
                </div>

                <div class="col-md-4">
                    <?php 
                    echo renderFormField(
                        'email_contact',
                        'Email de contact',
                        'email',
                        $club['email_contact'] ?? '',
                        [
                            'required' => false,
                            'placeholder' => 'contact@club.com',
                        ],
                        ''
                    );
                    ?>
                </div>
            </div>

            <!-- Description Section -->
            <?php echo renderFormSection('Description et détails'); ?>

            <div class="row g-3">
                <div class="col-12">
                    <?php 
                    echo renderFormField(
                        'description',
                        'Description du club',
                        'textarea',
                        $club['description'] ?? '',
                        [
                            'required' => false,
                            'placeholder' => 'Décrivez les objectifs et activités du club...',
                            'rows' => 5,
                        ],
                        ''
                    );
                    ?>
                </div>
            </div>

            <!-- Status Section -->
            <?php echo renderFormSection('Statut'); ?>

            <div class="row g-3">
                <div class="col-12">
                    <?php 
                    echo renderCheckboxField(
                        'actif',
                        'Ce club est actif',
                        $isActif,
                        '1'
                    );
                    ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="<?= $this->url('/evenements/manageClubs') ?>" class="btn btn-secondary">Annuler</a>
                <button class="btn btn-primary" type="submit">Enregistrer les modifications</button>
            </div>
        </form>
    </div>
</div>

<!-- Load validation script -->
<script src="<?= $this->url('/shared/js/validation.js') ?>"></script>
