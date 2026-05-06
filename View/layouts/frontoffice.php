<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniServe - FrontOffice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $this->url('/public/css/main.css') ?>">
    <link rel="stylesheet" href="<?= $this->url('/public/css/frontoffice.css') ?>">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top us-topbar">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= $this->url('/') ?>">
                <span class="us-brand-mark" aria-hidden="true">U</span>
                <span>UniServe</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#frontNav" aria-controls="frontNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="frontNav">
                <?php $currentUri = $_SERVER['REQUEST_URI'] ?? ''; ?>
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link <?= strpos($currentUri, '/utilisateurs') !== false ? 'active' : '' ?>" href="<?= $this->url('/utilisateurs') ?>">Utilisateurs</a></li>
                    <li class="nav-item"><a class="nav-link <?= strpos($currentUri, '/demandes') !== false ? 'active' : '' ?>" href="<?= $this->url('/demandes/frontoffice') ?>">Demandes</a></li>
                    <li class="nav-item"><a class="nav-link <?= strpos($currentUri, '/rendezvous') !== false ? 'active' : '' ?>" href="<?= $this->url('/rendezvous') ?>">Rendez-vous</a></li>
                    <li class="nav-item"><a class="nav-link <?= strpos($currentUri, '/documents') !== false ? 'active' : '' ?>" href="<?= $this->url('/documents') ?>">Documents</a></li>
                    <li class="nav-item"><a class="nav-link <?= strpos($currentUri, '/evenements') !== false ? 'active' : '' ?>" href="<?= $this->url('/evenements') ?>">Evenements</a></li>
                </ul>
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="rounded-circle bg-light text-dark px-2 py-1 me-2">U</span>
                        <span class="d-none d-sm-inline">Mon compte</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Mon profil</a></li>
                        <li><a class="dropdown-item" href="<?= $this->url('/auth/logout') ?>">Deconnexion</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <main class="container mt-5 pt-4">
        <?= $content ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $this->url('/public/js/main.js') ?>"></script>

    <!-- Chatbot Widget -->
    <div id="chatbot-widget" class="position-fixed bottom-0 end-0 m-4" style="z-index: 1050; font-family: 'Inter', sans-serif; pointer-events: none;">
        <!-- Chat Window (hidden by default) -->
        <div id="chatbot-window" class="card shadow border-0 d-none" style="width: 340px; border-radius: 16px; overflow: hidden; pointer-events: auto;">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3 border-0">
                <div class="d-flex align-items-center">
                    <i class="bi bi-robot fs-4 me-2"></i>
                    <div class="lh-1">
                        <strong class="d-block fs-6">Assistant UniServe</strong>
                        <small class="opacity-75" style="font-size: 0.75rem;">Propulsé par IA</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" onclick="toggleChat()"></button>
            </div>
            <div class="card-body p-3" id="chatbot-messages" style="height: 350px; overflow-y: auto; background-color: #f8f9fa;">
                <div class="d-flex mb-3">
                    <div class="bg-white border text-dark p-3 rounded-3 shadow-sm" style="max-width: 85%; border-bottom-left-radius: 0 !important; font-size: 0.95rem;">
                        Bonjour ! 👋 Je suis l'assistant virtuel d'UniServe. Que recherchez-vous aujourd'hui ?
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-top p-2">
                <form id="chatbot-form" class="d-flex gap-2" onsubmit="sendChatMessage(event)">
                    <input type="text" id="chatbot-input" class="form-control rounded-pill border-secondary border-opacity-25 bg-light" placeholder="Posez votre question..." required autocomplete="off" style="font-size: 0.95rem;">
                    <button type="submit" class="btn btn-primary rounded-circle shadow-sm" style="width: 40px; height: 40px; flex-shrink: 0;" id="chatbot-send-btn">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Floating Button -->
        <button id="chatbot-btn" class="btn btn-primary rounded-circle shadow-lg d-flex justify-content-center align-items-center ms-auto mt-3" style="width: 60px; height: 60px; transition: transform 0.2s; pointer-events: auto;" onclick="toggleChat()">
            <i class="bi bi-chat-dots-fill fs-3"></i>
        </button>
    </div>

    <script>
    const BASE_URL = '<?= $this->url('') ?>';

    function toggleChat() {
        const window = document.getElementById('chatbot-window');
        const btnIcon = document.querySelector('#chatbot-btn i');
        if (window.classList.contains('d-none')) {
            window.classList.remove('d-none');
            btnIcon.classList.replace('bi-chat-dots-fill', 'bi-x-lg');
            document.getElementById('chatbot-input').focus();
        } else {
            window.classList.add('d-none');
            btnIcon.classList.replace('bi-x-lg', 'bi-chat-dots-fill');
        }
    }

    async function sendChatMessage(e) {
        e.preventDefault();
        const inputField = document.getElementById('chatbot-input');
        const message = inputField.value.trim();
        if (!message) return;

        inputField.value = '';
        const messagesContainer = document.getElementById('chatbot-messages');
        const sendBtn = document.getElementById('chatbot-send-btn');
        sendBtn.disabled = true;
        
        function escHtml(str) {
            const d = document.createElement('div');
            d.appendChild(document.createTextNode(str));
            return d.innerHTML;
        }
        
        messagesContainer.innerHTML += `
            <div class="d-flex mb-3 justify-content-end">
                <div class="bg-primary text-white p-3 rounded-3 shadow-sm" style="max-width: 85%; border-bottom-right-radius: 0 !important; font-size: 0.95rem;">
                    ${escHtml(message)}
                </div>
            </div>
        `;
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        const typingId = 'typing-' + Date.now();
        messagesContainer.innerHTML += `
            <div id="${typingId}" class="d-flex mb-3">
                <div class="bg-white border text-muted p-3 rounded-3 shadow-sm d-flex align-items-center gap-1" style="border-bottom-left-radius: 0 !important;">
                    <div class="spinner-grow spinner-grow-sm text-primary" style="width: 0.4rem; height: 0.4rem;" role="status"></div>
                    <div class="spinner-grow spinner-grow-sm text-primary" style="width: 0.4rem; height: 0.4rem; animation-delay: 0.1s" role="status"></div>
                    <div class="spinner-grow spinner-grow-sm text-primary" style="width: 0.4rem; height: 0.4rem; animation-delay: 0.2s" role="status"></div>
                </div>
            </div>
        `;
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        try {
            const response = await fetch(BASE_URL + '/chatbot/ask', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: message })
            });
            
            const data = await response.json();
            
            const typingEl = document.getElementById(typingId);
            if(typingEl) typingEl.remove();
            
            let aiHtml = `
                <div class="d-flex mb-3 flex-column align-items-start">
                    <div class="bg-white border text-dark p-3 rounded-3 shadow-sm mb-2" style="max-width: 85%; border-bottom-left-radius: 0 !important; font-size: 0.95rem;">
                        ${escHtml(data.reply)}
                    </div>
            `;
            
            if (data.suggested_service_id) {
                aiHtml += `
                    <a href="${BASE_URL}/demandes/create?service_id=${data.suggested_service_id}" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm ms-2" style="font-weight: 500;">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Créer la demande
                    </a>
                `;
            }
            
            aiHtml += `</div>`;
            messagesContainer.innerHTML += aiHtml;
            
        } catch (err) {
            const typingEl = document.getElementById(typingId);
            if(typingEl) typingEl.remove();
            messagesContainer.innerHTML += `
                <div class="d-flex mb-3">
                    <div class="bg-danger bg-opacity-10 text-danger border border-danger p-3 rounded-3 shadow-sm" style="max-width: 85%; border-bottom-left-radius: 0 !important; font-size: 0.95rem;">
                        Désolé, l'assistant est indisponible pour le moment.
                    </div>
                </div>
            `;
        }
        
        sendBtn.disabled = false;
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    </script>
</body>
</html>
