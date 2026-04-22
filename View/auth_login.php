<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-9">
            <div class="row g-3 align-items-stretch">
                <div class="col-md-6">
                    <div class="us-card h-100">
                        <div class="p-4 p-md-4">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="us-brand-mark" aria-hidden="true">U</span>
                                    <div class="lh-sm">
                                        <div class="fw-bold">Portail UniServe</div>
                                        <div class="text-muted small">Connexion sécurisée</div>
                                    </div>
                                </div>
                                <span class="us-badge">Université</span>
                            </div>

                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger py-2 small mb-3" role="alert">
                                    <?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            <?php else: ?>
                                <div class="text-muted small mb-3">
                                    Utilisez votre adresse institutionnelle pour accéder à votre espace.
                                </div>
                            <?php endif; ?>

                            <div class="us-divider mb-3"></div>

                            <form method="post" action="<?= $this->url('/auth/login') ?>">
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-semibold">Email</label>
                                    <input type="email" id="email" class="form-control" name="email" placeholder="prenom.nom@universite.tld" autocomplete="username" required>
                                </div>
                                <div class="mb-2">
                                    <label for="password" class="form-label fw-semibold">Mot de passe</label>
                                    <input type="password" id="password" class="form-control" name="password" placeholder="Votre mot de passe" autocomplete="current-password" required>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2">Se connecter</button>
                                <div class="text-muted small mt-3">
                                    Besoin d’aide ? Contactez le service scolarité.
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="us-card h-100 p-4 p-md-4">
                        <div class="mb-2 fw-bold">Accès officiel</div>
                        <div class="text-muted mb-3">
                            UniServe centralise les services universitaires (demandes, rendez-vous, documents) dans un espace clair et institutionnel.
                        </div>

                        <div class="us-divider my-3"></div>

                        <div class="d-grid gap-2">
                            <div class="d-flex align-items-start gap-2">
                                <span class="us-badge">1</span>
                                <div>
                                    <div class="fw-semibold">Connexion</div>
                                    <div class="text-muted small">Identifiants institutionnels.</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <span class="us-badge">2</span>
                                <div>
                                    <div class="fw-semibold">Accès aux modules</div>
                                    <div class="text-muted small">Selon votre rôle (étudiant, enseignant, staff).</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <span class="us-badge">3</span>
                                <div>
                                    <div class="fw-semibold">Suivi simplifié</div>
                                    <div class="text-muted small">Historique et notifications (à venir).</div>
                                </div>
                            </div>
                        </div>

                        <div class="us-divider my-3"></div>

                        <a class="btn btn-outline-secondary w-100 py-2" href="<?= $this->url('/') ?>">Retour à l’accueil</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
