<style>
.frm-wrap { max-width:580px;margin:0 auto; }
.frm-header { display:flex;align-items:center;gap:14px;margin-bottom:24px; }
.frm-header-icon {
    width:46px;height:46px;border-radius:13px;flex-shrink:0;
    background:linear-gradient(135deg,#9f7a2f,#c89a3c);
    display:grid;place-items:center;color:#fff;font-size:1.1rem;
    box-shadow:0 4px 14px rgba(159,122,47,.28);
}
.frm-card {
    background:#fff;border-radius:16px;border:1px solid var(--border);
    box-shadow:0 1px 8px rgba(11,42,90,.06);overflow:hidden;
}
.frm-card-bar { height:4px;background:linear-gradient(90deg,var(--accent),var(--mint)); }
.frm-preview {
    background:linear-gradient(135deg,rgba(159,122,47,.06),rgba(159,122,47,.02));
    border-bottom:1px solid var(--border);padding:16px 22px;
    display:flex;align-items:center;gap:14px;
}
.frm-preview-icon {
    width:44px;height:44px;border-radius:12px;flex-shrink:0;
    background:rgba(159,122,47,.10);border:1px solid rgba(159,122,47,.22);
    display:grid;place-items:center;font-size:1.1rem;color:var(--accent);
}
.frm-preview-id   { font-size:.68rem;font-weight:700;color:var(--text-muted);margin-bottom:2px; }
.frm-preview-nom  { font-weight:800;color:var(--brand);font-size:.9rem; }
.frm-preview-loc  { font-size:.76rem;color:var(--text-muted); }
.frm-body { padding:24px; }
.frm-label {
    display:block;font-size:.72rem;font-weight:700;
    text-transform:uppercase;letter-spacing:.06em;
    color:var(--brand);margin-bottom:6px;
}
.frm-label i { margin-right:5px;opacity:.8; }
.frm-divider { height:1px;background:var(--border);margin:20px 0; }
.frm-actions { display:flex;align-items:center;gap:10px; }
</style>

<div class="frm-wrap">

    <!-- En-tête -->
    <div class="frm-header">
        <a href="<?= $this->url('/rendezvous?tab=bureaux') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="frm-header-icon"><i class="bi bi-pencil-square"></i></div>
        <div>
            <div style="font-size:1.1rem;font-weight:800;color:var(--brand);">Modifier le Bureau</div>
            <div style="font-size:.78rem;color:var(--text-muted);">
                Bureau #<?= htmlspecialchars((string)($bureau['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                — les champs <span class="text-danger">*</span> sont obligatoires.
            </div>
        </div>
    </div>

    <div class="frm-card">
        <div class="frm-card-bar"></div>

        <!-- Aperçu live -->
        <div class="frm-preview">
            <div class="frm-preview-icon">
                <i class="bi bi-building-fill"></i>
            </div>
            <div>
                <div class="frm-preview-id">Bureau #<?= htmlspecialchars((string)($bureau['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                <div class="frm-preview-nom" id="prev-nom">
                    <?= htmlspecialchars((string)($bureau['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="frm-preview-loc" id="prev-loc">
                    <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars((string)($bureau['localisation'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
        </div>

        <div class="frm-body">
            <form action="<?= $this->url('/bureaux/update/' . htmlspecialchars((string)($bureau['id'] ?? ''), ENT_QUOTES, 'UTF-8')) ?>"
                  method="POST" onsubmit="return validateFrm()">
">

                <div class="mb-3">
                    <label class="frm-label"><i class="bi bi-building"></i>Nom du bureau <span class="text-danger">*</span></label>
                    <input type="text" name="nom" id="nom" class="form-control"
                           value="<?= htmlspecialchars((string)($bureau['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                           oninput="livePreview()">
                    <div class="invalid-feedback" id="err_nom"></div>
                </div>

                <div class="mb-3">
                    <label class="frm-label"><i class="bi bi-geo-alt"></i>Localisation <span class="text-danger">*</span></label>
                    <input type="text" name="localisation" id="localisation" class="form-control"
                           value="<?= htmlspecialchars((string)($bureau['localisation'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                           oninput="livePreview()">
                    <div class="invalid-feedback" id="err_localisation"></div>
                </div>

                <div class="mb-3">
                    <label class="frm-label">
                        <i class="bi bi-person"></i>Responsable
                        <span style="font-size:.68rem;font-weight:600;color:var(--text-muted);text-transform:none;letter-spacing:0;">(optionnel)</span>
                    </label>
                    <input type="text" name="responsable" class="form-control"
                           value="<?= htmlspecialchars((string)($bureau['responsable'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="frm-divider"></div>

                <div class="frm-actions">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-floppy-fill me-2"></i>Mettre à jour
                    </button>
                    <a href="<?= $this->url('/rendezvous?tab=bureaux') ?>"
                       class="btn btn-outline-secondary px-4">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function livePreview() {
    var n = document.getElementById('nom').value.trim();
    var l = document.getElementById('localisation').value.trim();
    document.getElementById('prev-nom').innerText = n || 'Nom du bureau';
    document.getElementById('prev-loc').innerHTML = '<i class="bi bi-geo-alt me-1"></i>' + (l || 'Localisation');
}

function validateFrm() {
    var ok = true;
    var nom = document.getElementById('nom');
    var loc = document.getElementById('localisation');
    var eN  = document.getElementById('err_nom');
    var eL  = document.getElementById('err_localisation');

    if (nom.value.trim() === '') {
        nom.classList.add('is-invalid'); eN.innerText = 'Le nom est obligatoire.'; ok = false;
    } else if (nom.value.trim().length < 2) {
        nom.classList.add('is-invalid'); eN.innerText = 'Minimum 2 caractères.'; ok = false;
    } else { nom.classList.remove('is-invalid'); eN.innerText = ''; }

    if (loc.value.trim() === '') {
        loc.classList.add('is-invalid'); eL.innerText = 'La localisation est obligatoire.'; ok = false;
    } else { loc.classList.remove('is-invalid'); eL.innerText = ''; }

    return ok;
}
</script>
