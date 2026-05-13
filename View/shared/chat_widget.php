<?php $usAiWelcome = 'Bonjour — posez votre question sur le portail.'; ?>
<div
    class="us-ai-chat"
    data-ai-chat
    data-ai-user-id="<?= (int) ($_SESSION['user']['id'] ?? 0) ?>"
    data-ai-welcome="<?= htmlspecialchars($usAiWelcome, ENT_QUOTES, 'UTF-8') ?>"
    data-ai-endpoint="<?= htmlspecialchars($this->url('/chatbot/ask'), ENT_QUOTES, 'UTF-8') ?>"
>
    <button type="button" class="btn btn-primary us-ai-chat-toggle" data-ai-chat-toggle>
        Assistant IA
    </button>

    <div class="us-ai-chat-panel d-none" data-ai-chat-panel>
        <div class="us-ai-chat-header">
            <div class="d-flex align-items-center gap-2">
                <?= us_brand_logo_html($this, 'us-brand-logo--chat', true) ?>
                <strong>Assistant UniServe</strong>
            </div>
            <div class="us-ai-chat-header-actions">
                <button type="button" class="btn btn-outline-secondary btn-sm us-ai-chat-reset" data-ai-chat-reset title="Effacer la conversation locale">
                    Nouvelle conversation
                </button>
                <button type="button" class="btn-close" aria-label="Fermer" data-ai-chat-close></button>
            </div>
        </div>

        <div class="us-ai-chat-body" data-ai-chat-messages>
            <div class="us-ai-chat-message bot" data-ai-chat-welcome>
                <?= htmlspecialchars($usAiWelcome, ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>

        <form class="us-ai-chat-form" data-ai-chat-form>
            <input
                type="text"
                class="form-control form-control-sm"
                placeholder="Votre question…"
                data-ai-chat-input
                required
            >
            <button type="submit" class="btn btn-primary btn-sm" data-ai-chat-send>Envoyer</button>
        </form>
    </div>
</div>
