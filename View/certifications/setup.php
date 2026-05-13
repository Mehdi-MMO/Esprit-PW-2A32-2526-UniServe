<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="us-card p-4">
                <h1 class="h4 mb-3"><?= htmlspecialchars((string) ($title ?? 'Configuration'), ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="mb-3">Les tables de certification (DOCAC) n’ont pas pu être créées automatiquement (droits MySQL ou erreur DDL).</p>
                <ol class="mb-4">
                    <li>Vérifiez que le compte MySQL de votre <code>.env</code> (<code>DB_USER</code>) peut exécuter <code>CREATE TABLE</code> sur la base <code>DB_NAME</code>.</li>
                    <li>Sinon, importez (ou ré-importez) le fichier unique <strong><code>db/uniserve_full.sql</code></strong> dans phpMyAdmin pour créer toutes les tables.</li>
                    <li>Rechargez cette page.</li>
                </ol>
                <p class="text-muted small mb-0">Normalement l’application crée ces tables au premier accès à Certifications — cette page ne s’affiche qu’en secours.</p>
                <hr>
                <a class="btn btn-primary" href="<?= $this->url('/certifications') ?>">Réessayer</a>
                <a class="btn btn-outline-secondary ms-2" href="<?= $this->url('/frontoffice/dashboard') ?>">Tableau de bord</a>
            </div>
        </div>
    </div>
</section>
