<?php
$bureaux = $bureaux ?? [];
$old = $old ?? [
    'bureau_id'  => '',
    'motif'      => '',
    'date_debut' => '',
    'date_fin'   => '',
];
$error = $error ?? null;

$selectedBureau = $old['bureau_id'] ?? '';
$bureauxList    = array_map(fn($b) => [
    'id'          => (int) ($b['id'] ?? 0),
    'nom'         => (string) ($b['nom'] ?? ''),
    'localisation'=> (string) ($b['localisation'] ?? ''),
], $bureaux);
$bureauxJson = json_encode($bureauxList, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
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
            <div class="book-step-lbl active">Bureau &amp; Motif</div>
        </div>
        <div class="book-step-line"></div>
        <div class="book-step">
            <div class="book-step-num idle">2</div>
            <div class="book-step-lbl idle">Créneau</div>
        </div>
        <div class="book-step-line"></div>
        <div class="book-step">
            <div class="book-step-num idle">3</div>
            <div class="book-step-lbl idle">Confirmation</div>
        </div>
    </div>

    <!-- Alerte erreur générale -->
    <?php if ($error !== null && $error !== ''): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
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
                    <div class="book-summary-icon"><i class="bi bi-building-fill"></i></div>
                    <span class="book-summary-val" id="sum-bureau">—</span>
                </div>
                <div class="book-summary-row">
                    <div class="book-summary-icon"><i class="bi bi-chat-text-fill"></i></div>
                    <span class="book-summary-val" id="sum-motif">—</span>
                </div>
                <div class="book-summary-row">
                    <div class="book-summary-icon"><i class="bi bi-calendar-event-fill"></i></div>
                    <span class="book-summary-val" id="sum-debut">—</span>
                </div>
                <div class="book-summary-row">
                    <div class="book-summary-icon"><i class="bi bi-calendar-check-fill"></i></div>
                    <span class="book-summary-val" id="sum-fin">—</span>
                </div>
            </div>

            <form action="<?= $this->url('/rendezvous/create') ?>" method="POST"
                  onsubmit="return validateBookForm()" id="book-form">

                <!-- ─── Section 1 : Bureau & Motif ─── -->
                <div class="book-section">
                    <div class="book-section-num">1</div>
                    <div class="book-section-title">Bureau &amp; Motif</div>
                </div>

                <!-- Bureau -->
                <div class="mb-4">
                    <label class="book-label">
                        <i class="bi bi-building-fill"></i>Bureau <span class="text-danger ms-1">*</span>
                    </label>
                    <select name="bureau_id" id="bureau_id"
                            class="form-select"
                            onchange="updateSummary()" required>
                        <option value="">— Sélectionnez un bureau —</option>
                        <?php foreach ($bureauxList as $b): ?>
                            <option value="<?= $b['id'] ?>"
                                <?= ((string)$selectedBureau === (string)$b['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['nom'], ENT_QUOTES, 'UTF-8') ?>
                                <?php if ($b['localisation']): ?> — <?= htmlspecialchars($b['localisation'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($selectedBureau)): ?>
                    <div class="book-hint text-success">
                        <i class="bi bi-check-circle-fill"></i>Bureau pré-sélectionné.
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Motif -->
                <div class="mb-4">
                    <label class="book-label">
                        <i class="bi bi-chat-text-fill"></i>Motif
                    </label>
                    <input type="text" name="motif" id="motif"
                           class="form-control"
                           value="<?= htmlspecialchars((string)($old['motif'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Ex : Demande de bourse, Inscription, Tutorat…"
                           oninput="updateSummary(); triggerAiSuggest();"
                           maxlength="255"
                           autocomplete="off">
                    <!-- Indicateur suggestion IA -->
                    <div id="ai-indicator" style="display:none;margin-top:7px;align-items:center;gap:8px;padding:8px 12px;border-radius:10px;background:linear-gradient(135deg,rgba(11,42,90,0.05),rgba(62,207,178,0.06));border:1px solid rgba(11,42,90,0.10);">
                        <div id="ai-spinner" style="display:none;">
                            <div style="width:16px;height:16px;border:2px solid rgba(11,42,90,0.15);border-top-color:var(--brand);border-radius:50%;animation:ai-spin .7s linear infinite;flex-shrink:0;"></div>
                        </div>
                        <i id="ai-icon" class="bi bi-stars" style="color:var(--brand);font-size:.9rem;display:none;"></i>
                        <span id="ai-text" style="font-size:.78rem;font-weight:600;color:var(--brand);"></span>
                    </div>
                </div>

                <!-- ─── Section 2 : Créneau ─── -->
                <div class="book-section">
                    <div class="book-section-num">2</div>
                    <div class="book-section-title">Créneau</div>
                </div>

                <div class="row g-3 mb-4">
                    <!-- Début -->
                    <div class="col-sm-6">
                        <label class="book-label">
                            <i class="bi bi-calendar-event-fill"></i>Début <span class="text-danger ms-1">*</span>
                        </label>
                        <input type="datetime-local" name="date_debut" id="date_debut"
                               class="form-control"
                               value="<?= htmlspecialchars((string)($old['date_debut'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                               oninput="updateSummary()"
                               required>
                    </div>

                    <!-- Fin -->
                    <div class="col-sm-6">
                        <label class="book-label">
                            <i class="bi bi-clock-fill"></i>Fin <span class="text-danger ms-1">*</span>
                        </label>
                        <input type="datetime-local" name="date_fin" id="date_fin"
                               class="form-control"
                               value="<?= htmlspecialchars((string)($old['date_fin'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                               oninput="updateSummary()"
                               required>
                        <div class="book-hint">
                            <i class="bi bi-info-circle"></i>La fin doit être après le début.
                        </div>
                    </div>
                </div>

                <div class="book-divider"></div>

                <!-- Actions -->
                <div class="book-actions">
                    <button type="submit" class="book-submit">
                        <i class="bi bi-send-fill"></i>Soumettre la demande
                    </button>
                    <a href="<?= $this->url('/rendezvous') ?>" class="btn btn-outline-secondary px-4">Annuler</a>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
/* ── Récapitulatif dynamique ── */
function fmtDatetime(val) {
    if (!val) return '—';
    try {
        return new Date(val).toLocaleString('fr-FR', {day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});
    } catch(e) { return val; }
}

function updateSummary() {
    var sel    = document.getElementById('bureau_id');
    var bureau = sel && sel.value ? sel.options[sel.selectedIndex].text.trim() : '';
    var motif  = document.getElementById('motif').value.trim();
    var debut  = document.getElementById('date_debut').value;
    var fin    = document.getElementById('date_fin').value;

    var hasData = (sel && sel.value) || motif || debut || fin;
    document.getElementById('book-summary').classList.toggle('show', !!hasData);

    document.getElementById('sum-bureau').innerText = bureau || '—';
    document.getElementById('sum-motif').innerText  = motif  || '—';
    document.getElementById('sum-debut').innerText  = fmtDatetime(debut);
    document.getElementById('sum-fin').innerText    = fmtDatetime(fin);
}

document.addEventListener('DOMContentLoaded', updateSummary);

/* ── Validation ── */
function validateBookForm() {
    var valid  = true;
    var sel    = document.getElementById('bureau_id');
    var debut  = document.getElementById('date_debut');
    var fin    = document.getElementById('date_fin');

    [sel, debut, fin].forEach(function(el) { if(el) el.classList.remove('is-invalid'); });

    if (!sel || !sel.value) {
        if(sel) sel.classList.add('is-invalid');
        valid = false;
    }
    if (!debut || !debut.value) { if(debut) debut.classList.add('is-invalid'); valid = false; }
    if (!fin   || !fin.value)   { if(fin)   fin.classList.add('is-invalid');   valid = false; }
    if (debut && fin && debut.value && fin.value && fin.value <= debut.value) {
        fin.classList.add('is-invalid');
        alert('La date de fin doit être après la date de début.');
        valid = false;
    }
    return valid;
}

/* ── Suggestion intelligente de bureau (locale) ── */
const BUREAUX_DATA = <?= $bureauxJson ?>;

const KEYWORDS = {
    scolar:  ['bourse','inscription','scolarité','scolarite','frais','diplome','diplôme','attestation','certificat','relevé','releve','notes','résultats','resultats','réinscription','reinscription'],
    finance: ['paiement','facture','remboursement','comptabilité','comptabilite','finance','financement','virement','reçu','recu'],
    stage:   ['stage','internship','entreprise','convention','alternance','apprentissage','insertion','emploi'],
    academ:  ['cours','programme','emploi du temps','horaire','matiere','matière','module','exam','examen','note','semestre','licence','master'],
    biblio:  ['livre','bibliothèque','bibliotheque','emprunt','ouvrage','revue'],
    sport:   ['sport','activité physique','association','club','tournoi'],
    info:    ['informatique','ordinateur','réseau','internet','wifi','logiciel','technique','bug','impression','imprimante'],
    sante:   ['santé','sante','médecin','médical','infirmerie','psychologue','handicap','urgence'],
};

let aiDebounceTimer = null;

function suggestBureau(motif) {
    const txt = motif.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
    let bestId=0, bestScore=0, bestNom='';
    BUREAUX_DATA.forEach(function(b) {
        const nomNorm = b.nom.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
        let score = 0;
        if (txt.includes(nomNorm)) score += 10;
        nomNorm.split(/\s+/).forEach(function(word){ if(word.length>2&&txt.includes(word)) score+=4; });
        Object.entries(KEYWORDS).forEach(function([theme,kws]){
            const bureauMatchTheme = nomNorm.includes(theme)||kws.some(function(k){ return nomNorm.includes(k.normalize('NFD').replace(/[\u0300-\u036f]/g,'')); });
            if(bureauMatchTheme){ kws.forEach(function(kw){ if(txt.includes(kw.normalize('NFD').replace(/[\u0300-\u036f]/g,''))) score+=3; }); }
        });
        const locNorm = b.localisation.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
        if(txt.includes(locNorm)) score+=2;
        if(score>bestScore){ bestScore=score; bestId=b.id; bestNom=b.nom; }
    });
    return bestScore>=3 ? {id:bestId,nom:bestNom} : null;
}

function triggerAiSuggest() {
    var motif = document.getElementById('motif').value.trim();
    clearTimeout(aiDebounceTimer);
    if (motif.length < 3) { hideAiIndicator(); return; }
    var sel = document.getElementById('bureau_id');
    if (sel.dataset.userSelected === '1') return;
    aiDebounceTimer = setTimeout(function() {
        var suggestion = suggestBureau(motif);
        if (suggestion) { selectBureauById(suggestion.id); showAiSuccess('Bureau suggéré : ' + suggestion.nom); }
        else { hideAiIndicator(); }
    }, 500);
}

function selectBureauById(id) {
    var sel = document.getElementById('bureau_id');
    sel.dataset.aiChanging = '1';
    for (var i=0;i<sel.options.length;i++){ if(sel.options[i].value==id){ sel.selectedIndex=i; break; } }
    sel.dataset.aiChanging = '0';
    updateSummary();
}

function showAiSuccess(msg) {
    var box = document.getElementById('ai-indicator');
    box.style.display = 'flex';
    box.style.background  = 'linear-gradient(135deg,rgba(34,197,94,0.07),rgba(62,207,178,0.07))';
    box.style.borderColor = 'rgba(34,197,94,0.25)';
    document.getElementById('ai-spinner').style.display='none';
    var icon = document.getElementById('ai-icon');
    icon.style.display='inline-block'; icon.style.color='#166534';
    document.getElementById('ai-text').style.color='#166534';
    document.getElementById('ai-text').innerText=msg;
}

function hideAiIndicator() {
    var box = document.getElementById('ai-indicator');
    box.style.display='none'; box.style.background=''; box.style.borderColor='';
}

document.addEventListener('DOMContentLoaded', function() {
    updateSummary();
    var sel = document.getElementById('bureau_id');
    sel.dataset.userSelected = '0';
    sel.dataset.aiChanging   = '0';
    sel.addEventListener('change', function() {
        if (this.dataset.aiChanging !== '1') { this.dataset.userSelected='1'; hideAiIndicator(); }
    });
});
</script>
