document.addEventListener('DOMContentLoaded', function () {
    const chatRoot = document.querySelector('[data-ai-chat]');
    if (chatRoot) {
        const toggleBtn = chatRoot.querySelector('[data-ai-chat-toggle]');
        const closeBtn = chatRoot.querySelector('[data-ai-chat-close]');
        const panel = chatRoot.querySelector('[data-ai-chat-panel]');
        const form = chatRoot.querySelector('[data-ai-chat-form]');
        const input = chatRoot.querySelector('[data-ai-chat-input]');
        const sendBtn = chatRoot.querySelector('[data-ai-chat-send]');
        const messages = chatRoot.querySelector('[data-ai-chat-messages]');
        const endpoint = chatRoot.getAttribute('data-ai-endpoint') || '';
        const history = [];

        const appendMessage = function (text, who) {
            const div = document.createElement('div');
            div.className = 'us-ai-chat-message ' + who;
            div.textContent = text;
            messages.appendChild(div);
            messages.scrollTop = messages.scrollHeight;
        };

        const setOpen = function (open) {
            panel.classList.toggle('d-none', !open);
            if (open) {
                input.focus();
            }
        };

        toggleBtn.addEventListener('click', function () {
            const hidden = panel.classList.contains('d-none');
            setOpen(hidden);
        });

        closeBtn.addEventListener('click', function () {
            setOpen(false);
        });

        const setTyping = function (on) {
            let typing = messages.querySelector('[data-ai-typing]');
            if (on) {
                if (!typing) {
                    typing = document.createElement('div');
                    typing.className = 'us-ai-chat-message bot';
                    typing.setAttribute('data-ai-typing', '1');
                    typing.textContent = '...';
                    messages.appendChild(typing);
                }
                messages.scrollTop = messages.scrollHeight;
                return;
            }
            if (typing) {
                typing.remove();
            }
        };

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const text = (input.value || '').trim();
            if (text === '') {
                return;
            }

            appendMessage(text, 'user');
            history.push({ role: 'user', content: text });
            input.value = '';
            input.disabled = true;
            sendBtn.disabled = true;
            setTyping(true);

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: text, history: history.slice(-6) })
                });

                const payload = await response.json().catch(function () { return {}; });
                if (!response.ok) {
                    appendMessage(payload.error || 'Erreur du service IA.', 'bot');
                } else {
                    const reply = payload.reply || 'Pas de reponse recue.';
                    appendMessage(reply, 'bot');
                    history.push({ role: 'assistant', content: reply });
                }
            } catch (_err) {
                appendMessage('Erreur reseau. Reessayez dans un instant.', 'bot');
            } finally {
                setTyping(false);
                input.disabled = false;
                sendBtn.disabled = false;
                input.focus();
            }
        });
    }
});
