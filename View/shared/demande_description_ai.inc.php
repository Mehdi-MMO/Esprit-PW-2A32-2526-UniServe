<?php
declare(strict_types=1);
$url = trim((string) ($demande_ai_description_url ?? ''));
if ($url === '') {
    return;
}
?>
<div class="d-flex flex-wrap align-items-center gap-2 mb-2">
    <button type="button" class="btn btn-outline-primary btn-sm" id="us-demande-ai-desc-btn" data-url="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>" data-exclude-demande-id="<?= (int) ($demande_ai_exclude_demande_id ?? 0) ?>">
        <i class="fa-solid fa-wand-magic-sparkles me-1" aria-hidden="true"></i>Suggestion IA (dossier + Mes demandes)
    </button>
    <span class="small text-muted" id="us-demande-ai-desc-status" aria-live="polite"></span>
</div>
<script>
(function () {
    var btn = document.getElementById('us-demande-ai-desc-btn');
    var statusEl = document.getElementById('us-demande-ai-desc-status');
    var ta = document.getElementById('description');
    var titreEl = document.getElementById('titre');
    var catEl = document.getElementById('categorie_id');
    if (!btn || !ta || !btn.dataset.url) return;

    btn.addEventListener('click', function () {
        var titre = titreEl ? titreEl.value.trim() : '';
        var notes = ta.value.trim();
        var categorieId = 0;
        if (catEl && catEl.value) {
            var n = parseInt(catEl.value, 10);
            categorieId = isNaN(n) ? 0 : n;
        }
        var excludeId = parseInt(btn.getAttribute('data-exclude-demande-id') || '0', 10) || 0;
        btn.disabled = true;
        if (statusEl) statusEl.textContent = 'Génération en cours…';
        fetch(btn.dataset.url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ titre: titre, description: notes, categorie_id: categorieId, exclude_demande_id: excludeId })
        })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
            .then(function (res) {
                btn.disabled = false;
                if (res.ok && res.body && res.body.ok && res.body.description) {
                    ta.value = String(res.body.description);
                    if (statusEl) statusEl.textContent = 'Texte proposé — relisez et modifiez avant envoi.';
                } else {
                    var err = (res.body && res.body.error) ? res.body.error : 'Erreur réseau ou serveur.';
                    if (statusEl) statusEl.textContent = err;
                }
            })
            .catch(function () {
                btn.disabled = false;
                if (statusEl) statusEl.textContent = 'Erreur réseau.';
            });
    });
})();
</script>
