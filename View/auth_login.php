<section class="us-login-page">
    <div class="us-login-shell">
        <div class="row g-0">
            <div class="col-lg-5 d-none d-lg-flex flex-column">
                <div class="us-login-brand h-100 d-flex flex-column">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="us-brand-mark" aria-hidden="true">U</span>
                        <div class="text-start">
                            <div class="fw-bold fs-5">UniServe</div>
                            <div class="small us-login-brand-tagline">Portail des services universitaires</div>
                        </div>
                    </div>
                    <h2 class="mb-3">Accès officiel</h2>
                    <p class="mb-4 small us-login-brand-lead">
                        UniServe centralise les services universitaires (demandes, rendez-vous, documents) dans un espace clair et institutionnel.
                    </p>
                    <ol class="us-login-steps mt-auto" aria-label="Étapes d’accès">
                        <li>
                            <span class="us-login-step-num" aria-hidden="true">1</span>
                            <div>
                                <span class="us-login-step-title">Connexion</span>
                                Identifiants institutionnels.
                            </div>
                        </li>
                        <li>
                            <span class="us-login-step-num" aria-hidden="true">2</span>
                            <div>
                                <span class="us-login-step-title">Accès aux modules</span>
                                Selon votre rôle (étudiant, enseignant, staff).
                            </div>
                        </li>
                        <li>
                            <span class="us-login-step-num" aria-hidden="true">3</span>
                            <div>
                                <span class="us-login-step-title">Suivi simplifié</span>
                                Historique et notifications (menu une fois connecté).
                            </div>
                        </li>
                    </ol>
                </div>
            </div>

            <div class="col-12 col-lg-7 us-login-form-col">
                <div class="d-lg-none us-login-brand py-4 mb-0 rounded-0 border-0">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="us-brand-mark" aria-hidden="true">U</span>
                        <div class="text-start">
                            <div class="fw-bold fs-5">UniServe</div>
                            <div class="small us-login-brand-tagline">Connexion sécurisée</div>
                        </div>
                    </div>
                    <p class="small mb-0 us-login-brand-lead">
                        Identifiants institutionnels — accès selon votre profil.
                    </p>
                </div>

                <div class="us-card h-100">
                    <div class="p-0 p-md-0">
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
                            <div class="lh-sm d-none d-lg-block">
                                <div class="fw-bold">Portail UniServe</div>
                                <div class="text-muted small">Connexion sécurisée</div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap us-login-form-meta">
                                <span class="us-badge">Université</span>
                                <a href="<?= $this->url('/') ?>" class="us-login-back text-decoration-none">← Retour à l’accueil</a>
                            </div>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger py-2 small mb-3" role="alert">
                                <?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php elseif (!empty($success)): ?>
                            <div class="alert alert-success py-2 small mb-3" role="alert">
                                <?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php else: ?>
                            <div class="text-muted small mb-3">
                                Utilisez votre adresse institutionnelle pour accéder à votre espace.
                            </div>
                        <?php endif; ?>

                        <div class="us-divider mb-3"></div>

                        <form id="login-form" method="post" action="<?= $this->url('/auth/login') ?>" data-us-login-form>
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input type="email" id="email" class="form-control" name="email" placeholder="prenom.nom@universite.tld" autocomplete="username" spellcheck="false" required>
                            </div>
                            <div class="mb-2">
                                <label for="password" class="form-label fw-semibold">Mot de passe</label>
                                <input type="password" id="password" class="form-control" name="password" placeholder="Votre mot de passe" autocomplete="current-password" required>
                            </div>
                            <div class="text-end mb-2">
                                <a href="<?= $this->url('/auth/forgot') ?>" class="small text-decoration-none us-landing-link-focus">Mot de passe oublié ?</a>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 mt-2">Se connecter</button>
                            <div class="text-muted small mt-3">
                                Besoin d’aide ? Contactez le service scolarité.
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
