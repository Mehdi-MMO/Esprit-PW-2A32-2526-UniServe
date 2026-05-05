<style>
/* ── Book form ── */
.book-wrap { max-width:600px;margin:0 auto; }

.book-header {
    display:flex;align-items:center;gap:14px;margin-bottom:28px;
}
.book-header-icon {
    width:52px;height:52px;border-radius:15px;flex-shrink:0;
    background:linear-gradient(135deg,#0b2a5a,#1565c0);
    display:grid;place-items:center;color:#fff;font-size:1.3rem;
    box-shadow:0 5px 16px rgba(11,42,90,.24);
}
.book-header-title { font-size:1.2rem;font-weight:800;color:var(--brand);margin-bottom:2px; }
.book-header-sub   { font-size:.8rem;color:var(--text-muted); }

/* Steps indicator */
.book-steps {
    display:flex;align-items:center;gap:0;margin-bottom:28px;
}
.book-step {
    display:flex;align-items:center;gap:8px;flex:1;
}
.book-step-num {
    width:28px;height:28px;border-radius:50%;flex-shrink:0;
    display:grid;place-items:center;font-size:.72rem;font-weight:800;
    border:2px solid;transition:all .2s;
}
.book-step-num.done  { background:var(--brand);color:#fff;border-color:var(--brand); }
.book-step-num.active{ background:#fff;color:var(--brand);border-color:var(--brand); }
.book-step-num.idle  { background:#fff;color:var(--text-muted);border-color:var(--border); }
.book-step-lbl { font-size:.72rem;font-weight:700; }
.book-step-lbl.active { color:var(--brand); }
.book-step-lbl.idle   { color:var(--text-muted); }
.book-step-line { flex:1;height:2px;background:var(--border);margin:0 6px;border-radius:2px; }
.book-step-line.done  { background:var(--brand); }

/* Card */
.book-card {
    background:#fff;border-radius:18px;
    border:1px solid var(--border);
    box-shadow:0 2px 12px rgba(11,42,90,.07);
    overflow:hidden;
}
.book-card-bar { height:4px;background:linear-gradient(90deg,var(--brand),var(--mint)); }
.book-card-body { padding:28px; }

/* Section headers */
.book-section {
    display:flex;align-items:center;gap:10px;
    padding:14px 0 10px;border-bottom:1px solid var(--border);
    margin-bottom:18px;
}
.book-section-num {
    width:24px;height:24px;border-radius:7px;flex-shrink:0;
    background:var(--brand);color:#fff;
    display:grid;place-items:center;font-size:.68rem;font-weight:800;
}
.book-section-title { font-size:.78rem;font-weight:800;color:var(--brand);text-transform:uppercase;letter-spacing:.06em; }

/* Labels */
.book-label {
    display:flex;align-items:center;gap:6px;
    font-size:.73rem;font-weight:700;
    color:var(--brand);text-transform:uppercase;letter-spacing:.05em;
    margin-bottom:7px;
}
.book-label i { opacity:.75;font-size:.8rem; }

/* Hint text */
.book-hint {
    display:flex;align-items:center;gap:5px;
    font-size:.72rem;color:var(--text-muted);margin-top:5px;
}
.book-hint i { font-size:.7rem; }

/* Summary card */
.book-summary {
    background:linear-gradient(135deg,rgba(11,42,90,.04),rgba(62,207,178,.04));
    border:1px solid rgba(11,42,90,.10);
    border-radius:12px;padding:14px 16px;margin-bottom:24px;
    display:none;
}
.book-summary.show { display:block; }
.book-summary-title { font-size:.72rem;font-weight:800;color:var(--brand);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px; }
.book-summary-row   { display:flex;align-items:center;gap:8px;font-size:.8rem;color:var(--text-muted);margin-bottom:6px; }
.book-summary-row:last-child { margin-bottom:0; }
.book-summary-icon  { width:22px;height:22px;border-radius:6px;background:rgba(11,42,90,.07);color:var(--brand);display:grid;place-items:center;font-size:.65rem;flex-shrink:0; }
.book-summary-val   { font-weight:600;color:var(--text); }

/* Divider */
.book-divider { height:1px;background:var(--border);margin:22px 0; }

/* Actions */
.book-actions { display:flex;align-items:center;gap:12px; }
.book-submit {
    display:inline-flex;align-items:center;gap:8px;
    background:linear-gradient(135deg,#0b2a5a,#1565c0);
    color:#fff;border:none;padding:12px 28px;border-radius:999px;
    font-size:.9rem;font-weight:700;cursor:pointer;
    box-shadow:0 4px 14px rgba(11,42,90,.22);
    transition:all .18s;
}
.book-submit:hover { transform:translateY(-2px);box-shadow:0 8px 20px rgba(11,42,90,.28); }
</style>

<?php
$selectedBureau = $_POST['id_bureau'] ?? $_GET['bureau_id'] ?? '';
$hasErrors      = !empty(array_filter($errors ?? []));

// Collecter les bureaux pour le JSON (on clone le résultat avant de l'itérer dans le select)
$bureauxList = [];
$stmtBureaux->execute(); // réexécuter pour avoir les données fraîches
while ($b = $stmtBureaux->fetch(PDO::FETCH_ASSOC)) {
    $bureauxList[] = ['id' => $b['id'], 'nom' => $b['nom'], 'localisation' => $b['localisation']];
}
$bureauxJson = json_encode($bureauxList, JSON_UNESCAPED_UNICODE);
?>

<div class="book-wrap">

    <!-- En-tête -->
    <div class="book-header">
        <div class="book-header-icon"><i class="bi bi-calendar-plus"></i></div>
        <div>
            <div class="book-header-title">Nouveau Rendez-vous</div>
            <div class="book-header-sub">Remplissez le formulaire pour soumettre votre demande.</div>
        </div>
    </div>

    <!-- Indicateur d'étapes -->
    <div class="book-steps">
        <div class="book-step">
            <div class="book-step-num active">1</div>
            <div class="book-step-lbl active">Informations</div>
        </div>
        <div class="book-step-line"></div>
        <div class="book-step">
            <div class="book-step-num idle">2</div>
            <div class="book-step-lbl idle">Date &amp; Heure</div>
        </div>
        <div class="book-step-line"></div>
        <div class="book-step">
            <div class="book-step-num idle">3</div>
            <div class="book-step-lbl idle">Confirmation</div>
        </div>
    </div>

    <!-- Alerte erreur générale -->
    <?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($errors['general']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="book-card">
        <div class="book-card-bar"></div>
        <div class="book-card-body">

            <!-- Récapitulatif dynamique -->
            <div class="book-summary" id="book-summary">
                <div class="book-summary-title"><i class="bi bi-eye me-1"></i>Aperçu de votre demande</div>
                <div class="book-summary-row">
                    <div class="book-summary-icon"><i class="bi bi-person-fill"></i></div>
                    <span class="book-summary-val" id="sum-nom">—</span>
                </div>
                <div class="book-summary-row">
                    <div class="book-summary-icon"><i class="bi bi-building-fill"></i></div>
                    <span class="book-summary-val" id="sum-bureau">—</span>
                </div>
                <div class="book-summary-row">
                    <div class="book-summary-icon"><i class="bi bi-chat-text-fill"></i></div>
                    <span class="book-summary-val" id="sum-objet">—</span>
                </div>
                <div class="book-summary-row">
                    <div class="book-summary-icon"><i class="bi bi-calendar-event-fill"></i></div>
                    <span class="book-summary-val" id="sum-datetime">—</span>
                </div>
            </div>

            <form action="index.php?action=store_booking" method="POST"
                  onsubmit="return validateBookForm()" id="book-form">

                <!-- ─── Section 1 : Identité ─── -->
                <div class="book-section">
                    <div class="book-section-num">1</div>
                    <div class="book-section-title">Identité &amp; Bureau</div>
                </div>

                <!-- Nom étudiant -->
                <div class="mb-4">
                    <label class="book-label">
                        <i class="bi bi-person-fill"></i>Nom de l'étudiant <span class="text-danger ms-1">*</span>
                    </label>
                    <input type="text" name="nom_etudiant" id="nom_etudiant"
                           class="form-control <?= !empty($errors['nom_etudiant']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($_POST['nom_etudiant'] ?? '') ?>"
                           placeholder="Ex : Ali Ben Salah"
                           oninput="updateSummary()">
                    <div class="invalid-feedback" id="err_nom">
                        <?= htmlspecialchars($errors['nom_etudiant'] ?? '') ?>
                    </div>
                </div>

                <!-- Bureau -->
                <div class="mb-4">
                    <label class="book-label">
                        <i class="bi bi-building-fill"></i>Bureau <span class="text-danger ms-1">*</span>
                    </label>
                    <select name="id_bureau" id="id_bureau"
                            class="form-select <?= !empty($errors['id_bureau']) ? 'is-invalid' : '' ?>"
                            onchange="updateSummary()">
                        <option value="">— Sélectionnez un bureau —</option>
                        <?php foreach ($bureauxList as $b): ?>
                            <option value="<?= $b['id'] ?>"
                                <?= ($selectedBureau == $b['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['nom']) ?> — <?= htmlspecialchars($b['localisation']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback" id="err_bureau">
                        <?= htmlspecialchars($errors['id_bureau'] ?? '') ?>
                    </div>
                    <?php if (!empty($selectedBureau)): ?>
                    <div class="book-hint text-success">
                        <i class="bi bi-check-circle-fill"></i>Bureau pré-sélectionné depuis la page d'accueil.
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Sujet -->
                <div class="mb-4">
                    <label class="book-label">
                        <i class="bi bi-chat-text-fill"></i>Sujet <span class="text-danger ms-1">*</span>
                    </label>
                    <input type="text" name="objet" id="objet"
                           class="form-control <?= !empty($errors['objet']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($_POST['objet'] ?? '') ?>"
                           placeholder="Ex : Demande de bourse, Inscription, Tutorat…"
                           oninput="updateSummary(); triggerAiSuggest();"
                           autocomplete="off">
                    <div class="invalid-feedback" id="err_objet">
                        <?= htmlspecialchars($errors['objet'] ?? '') ?>
                    </div>
                    <!-- Indicateur suggestion IA -->
                    <div id="ai-indicator" style="display:none;margin-top:7px;display:none;align-items:center;gap:8px;padding:8px 12px;border-radius:10px;background:linear-gradient(135deg,rgba(11,42,90,0.05),rgba(62,207,178,0.06));border:1px solid rgba(11,42,90,0.10);">
                        <div id="ai-spinner" style="display:none;">
                            <div style="width:16px;height:16px;border:2px solid rgba(11,42,90,0.15);border-top-color:#0b2a5a;border-radius:50%;animation:ai-spin .7s linear infinite;flex-shrink:0;"></div>
                        </div>
                        <i id="ai-icon" class="bi bi-stars" style="color:#0b2a5a;font-size:.9rem;display:none;"></i>
                        <span id="ai-text" style="font-size:.78rem;font-weight:600;color:#0b2a5a;"></span>
                    </div>
                    <style>@keyframes ai-spin{to{transform:rotate(360deg);}}</style>
                </div>

                <!-- ─── Section 2 : Date & Heure ─── -->
                <div class="book-section">
                    <div class="book-section-num">2</div>
                    <div class="book-section-title">Date &amp; Heure</div>
                </div>

                <div class="row g-3 mb-4">
                    <!-- Date -->
                    <div class="col-sm-7">
                        <label class="book-label">
                            <i class="bi bi-calendar-event-fill"></i>Date <span class="text-danger ms-1">*</span>
                        </label>
                        <input type="text" name="date_rdv" id="date_rdv"
                               class="form-control <?= !empty($errors['date_rdv']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($_POST['date_rdv'] ?? '') ?>"
                               placeholder="AAAA-MM-JJ"
                               oninput="updateSummary()">
                        <div class="book-hint">
                            <i class="bi bi-info-circle"></i>Format : AAAA-MM-JJ &nbsp;(ex : 2026-06-15)
                        </div>
                        <div class="invalid-feedback" id="err_date">
                            <?= htmlspecialchars($errors['date_rdv'] ?? '') ?>
                        </div>
                    </div>

                    <!-- Heure -->
                    <div class="col-sm-5">
                        <label class="book-label">
                            <i class="bi bi-clock-fill"></i>Heure <span class="text-danger ms-1">*</span>
                        </label>
                        <input type="text" name="heure_rdv" id="heure_rdv"
                               class="form-control <?= !empty($errors['heure_rdv']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($_POST['heure_rdv'] ?? '') ?>"
                               placeholder="HH:MM"
                               oninput="updateSummary()">
                        <div class="book-hint">
                            <i class="bi bi-info-circle"></i>Format : HH:MM &nbsp;(ex : 09:30)
                        </div>
                        <div class="invalid-feedback" id="err_heure">
                            <?= htmlspecialchars($errors['heure_rdv'] ?? '') ?>
                        </div>
                    </div>
                </div>

                <div class="book-divider"></div>

                <!-- Actions -->
                <div class="book-actions">
                    <button type="submit" class="book-submit">
                        <i class="bi bi-send-fill"></i>Soumettre la demande
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary px-4">Annuler</a>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
/* ── Récapitulatif dynamique ── */
function updateSummary() {
    var nom    = document.getElementById('nom_etudiant').value.trim();
    var sel    = document.getElementById('id_bureau');
    var bureau = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : '';
    var objet  = document.getElementById('objet').value.trim();
    var date   = document.getElementById('date_rdv').value.trim();
    var heure  = document.getElementById('heure_rdv').value.trim();

    var hasData = nom || sel.value || objet || date || heure;
    var summary = document.getElementById('book-summary');
    summary.classList.toggle('show', !!hasData);

    document.getElementById('sum-nom').innerText    = nom    || '—';
    document.getElementById('sum-bureau').innerText = sel.value ? bureau : '—';
    document.getElementById('sum-objet').innerText  = objet  || '—';
    var dt = '';
    if (date && heure)      dt = date + ' à ' + heure;
    else if (date)          dt = date;
    else if (heure)         dt = heure;
    document.getElementById('sum-datetime').innerText = dt || '—';
}

/* Initialiser si valeurs pré-remplies */
document.addEventListener('DOMContentLoaded', updateSummary);

/* ── Validation identique à l'original ── */
function validateBookForm() {
    var valid = true;
    ['nom_etudiant','id_bureau','objet','date_rdv','heure_rdv'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.classList.remove('is-invalid');
    });

    var nom = document.getElementById('nom_etudiant').value.trim();
    if (nom === '') { setError('nom_etudiant','err_nom',"Le nom de l'étudiant est obligatoire."); valid=false; }
    else if (nom.length < 2) { setError('nom_etudiant','err_nom',"Le nom doit contenir au moins 2 caractères."); valid=false; }

    var bureau = document.getElementById('id_bureau').value;
    if (bureau === '') { setError('id_bureau','err_bureau',"Veuillez sélectionner un bureau."); valid=false; }

    var objet = document.getElementById('objet').value.trim();
    if (objet === '') { setError('objet','err_objet',"Le sujet est obligatoire."); valid=false; }

    var date = document.getElementById('date_rdv').value.trim();
    var dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (date === '') { setError('date_rdv','err_date',"La date est obligatoire."); valid=false; }
    else if (!dateRegex.test(date)) { setError('date_rdv','err_date',"Format requis : AAAA-MM-JJ"); valid=false; }
    else {
        var parts = date.split('-');
        var d = new Date(parts[0], parts[1]-1, parts[2]);
        if (d.getFullYear()!=parts[0]||(d.getMonth()+1)!=parts[1]||d.getDate()!=parts[2]) {
            setError('date_rdv','err_date',"La date saisie n'est pas valide."); valid=false;
        }
    }

    var heure = document.getElementById('heure_rdv').value.trim();
    var heureRegex = /^\d{2}:\d{2}$/;
    if (heure === '') { setError('heure_rdv','err_heure',"L'heure est obligatoire."); valid=false; }
    else if (!heureRegex.test(heure)) { setError('heure_rdv','err_heure',"Format requis : HH:MM"); valid=false; }
    else {
        var h=parseInt(heure.split(':')[0]), m=parseInt(heure.split(':')[1]);
        if (h<0||h>23||m<0||m>59) { setError('heure_rdv','err_heure',"Heure invalide (00:00 – 23:59)."); valid=false; }
    }
    return valid;
}

/* ── Suggestion intelligente de bureau (locale, sans API) ── */
const BUREAUX_DATA = <?= $bureauxJson ?>;

let aiDebounceTimer = null;

// Mots-clés associés à des types de bureaux
const KEYWORDS = {
    scolar:   ['bourse','inscription','scolarité','scolarite','frais','diplome','diplôme','attestation','certificat','relevé','releve','notes','résultats','resultats','réinscription','reinscription','equivalence','équivalence','admission'],
    finance:  ['paiement','facture','remboursement','comptabilité','comptabilite','finance','financement','virement','reçu','recu','quittance'],
    stage:    ['stage','internship','entreprise','convention','alternance','apprentissage','insertion','emploi','cv','lettre','recommandation'],
    academ:   ['cours','programme','emploi du temps','horaire','matiere','matière','module','exam','examen','note','ue','semestre','licence','master','doctorat','professeur','enseignant'],
    registr:  ['dossier','document','enregistrement','registre','inscription administrative','archive'],
    biblio:   ['livre','bibliothèque','bibliotheque','emprunt','ouvrage','revue','ressource'],
    sport:    ['sport','activité physique','activite physique','association','club','tournament','tournoi'],
    info:     ['informatique','ordinateur','réseau','reseau','internet','wifi','logiciel','materiel','matériel','technique','bug','panne','impression','imprimante'],
    rh:       ['ressources humaines','rh','personnel','contrat','poste','recrutement','carrière','carriere'],
    sante:    ['santé','sante','médecin','medecin','médical','medical','infirmerie','psychologue','handicap','accessibilité','accessibilite','urgence'],
};

function suggestBureau(objet) {
    const txt = objet.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

    let bestId    = 0;
    let bestScore = 0;
    let bestNom   = '';

    BUREAUX_DATA.forEach(b => {
        const nomNorm = b.nom.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        let score = 0;

        // 1. Correspondance directe avec le nom du bureau
        if (txt.includes(nomNorm)) score += 10;
        nomNorm.split(/\s+/).forEach(word => { if (word.length > 2 && txt.includes(word)) score += 4; });

        // 2. Correspondance via mots-clés thématiques
        Object.entries(KEYWORDS).forEach(([theme, kws]) => {
            // Vérifier si ce bureau correspond au thème
            const bureauMatchTheme = nomNorm.includes(theme) ||
                kws.some(k => nomNorm.includes(k.normalize('NFD').replace(/[\u0300-\u036f]/g,'')));

            if (bureauMatchTheme) {
                kws.forEach(kw => {
                    const kwNorm = kw.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                    if (txt.includes(kwNorm)) score += 3;
                });
            }
        });

        // 3. Correspondance partielle sujet → localisation
        const locNorm = b.localisation.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
        if (txt.includes(locNorm)) score += 2;

        if (score > bestScore) { bestScore = score; bestId = b.id; bestNom = b.nom; }
    });

    return bestScore >= 3 ? { id: bestId, nom: bestNom } : null;
}

function triggerAiSuggest() {
    const objet = document.getElementById('objet').value.trim();
    clearTimeout(aiDebounceTimer);

    if (objet.length < 3) { hideAiIndicator(); return; }

    const sel = document.getElementById('id_bureau');
    if (sel.dataset.userSelected === '1') return;

    aiDebounceTimer = setTimeout(() => {
        const suggestion = suggestBureau(objet);
        if (suggestion) {
            selectBureauById(suggestion.id);
            showAiSuccess('Bureau suggéré : ' + suggestion.nom);
        } else {
            hideAiIndicator();
        }
    }, 500);
}

function selectBureauById(id) {
    const sel = document.getElementById('id_bureau');
    sel.dataset.aiChanging = '1';
    for (let i = 0; i < sel.options.length; i++) {
        if (sel.options[i].value == id) { sel.selectedIndex = i; break; }
    }
    sel.dataset.aiChanging = '0';
    updateSummary();
}

function showAiLoading() {
    const box = document.getElementById('ai-indicator');
    box.style.display = 'flex';
    document.getElementById('ai-spinner').style.display = 'block';
    document.getElementById('ai-icon').style.display    = 'none';
    document.getElementById('ai-text').innerText        = 'Analyse…';
}

function showAiSuccess(msg) {
    const box = document.getElementById('ai-indicator');
    box.style.display = 'flex';
    box.style.background   = 'linear-gradient(135deg,rgba(34,197,94,0.07),rgba(62,207,178,0.07))';
    box.style.borderColor  = 'rgba(34,197,94,0.25)';
    document.getElementById('ai-spinner').style.display = 'none';
    const icon = document.getElementById('ai-icon');
    icon.style.display = 'inline-block';
    icon.style.color   = '#166534';
    document.getElementById('ai-text').style.color   = '#166534';
    document.getElementById('ai-text').innerText     = msg;
}

function hideAiIndicator() {
    const box = document.getElementById('ai-indicator');
    box.style.display    = 'none';
    box.style.background = '';
    box.style.borderColor = '';
}

// Marquer si l'utilisateur change le bureau manuellement
document.addEventListener('DOMContentLoaded', function() {
    updateSummary();
    const sel = document.getElementById('id_bureau');
    sel.dataset.userSelected = '0';
    sel.dataset.aiChanging   = '0';
    sel.addEventListener('change', function() {
        if (this.dataset.aiChanging !== '1') {
            this.dataset.userSelected = '1';
            hideAiIndicator();
        }
    });
});
</script>