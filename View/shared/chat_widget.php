<div class="us-ai-chat" data-ai-chat data-ai-endpoint="<?= htmlspecialchars($this->url('/chatbot/ask'), ENT_QUOTES, 'UTF-8') ?>">
    <button type="button" class="btn btn-primary us-ai-chat-toggle" data-ai-chat-toggle>
        Assistant IA
    </button>

    <div class="us-ai-chat-panel d-none" data-ai-chat-panel>
        <div class="us-ai-chat-header">
            <strong>Assistant UniServe</strong>
            <button type="button" class="btn-close" aria-label="Fermer" data-ai-chat-close></button>
        </div>

        <div class="us-ai-chat-body" data-ai-chat-messages>
            <div class="us-ai-chat-message bot">
                Bonjour, je peux vous aider pour UniServe.
            </div>
        </div>

        <form class="us-ai-chat-form" data-ai-chat-form>
            <input
                type="text"
                class="form-control form-control-sm"
                placeholder="Posez votre question..."
                data-ai-chat-input
                required
            >
            <button type="submit" class="btn btn-primary btn-sm" data-ai-chat-send>Envoyer</button>
        </form>
    </div>
</div>
