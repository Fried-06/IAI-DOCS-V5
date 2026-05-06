<?php
// viewer.php — Lecteur de documents Supabase avec Illusion d'URL
$url = $_GET['url'] ?? '';

if (empty($url)) {
    die("Lien du document manquant.");
}

// LA TRICHE : On transforme :// en / pour que le proxy le lise comme un chemin
$maskedUrl = str_replace('://', '/', $url);
$proxyUrl = "proxy.php/" . $maskedUrl;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualiseur - IAI DOCS</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    
    <!-- Bibliothèques de rendu Riche -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
    <style>
        :root { --primary: #00e5c4; --bg: #040c18; --card-bg: rgba(13, 27, 45, 0.7); --border: rgba(255, 255, 255, 0.1); }
        body, html { margin: 0; padding: 0; height: 100%; background: var(--bg); color: #fff; font-family: 'Outfit', sans-serif; overflow: hidden; }
        .toolbar { height: 60px; background: var(--card-bg); backdrop-filter: blur(10px); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 25px; position: relative; z-index: 100; }
        .logo { font-weight: 600; font-size: 1.2rem; text-decoration: none; color: #fff; }
        .logo span { color: var(--primary); }
        .btn { background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border); color: #fff; padding: 8px 16px; border-radius: 8px; cursor: pointer; text-decoration: none; font-size: 0.9rem; transition: all 0.3s; }
        .btn:hover { background: var(--primary); color: var(--bg); border-color: var(--primary); }
        .viewer-container { position: absolute; top: 60px; bottom: 0; left: 0; right: 0; }
        #doc-frame { width: 100%; height: 100%; border: none; background: #fff; }

        /* --- Interface Tuteur IA --- */
        .ai-chat-trigger {
            position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px;
            background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;
            cursor: pointer; box-shadow: 0 10px 30px rgba(0, 229, 196, 0.4); z-index: 1000; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .ai-chat-trigger:hover { transform: scale(1.1) rotate(5deg); }
        .ai-chat-trigger svg { width: 30px; height: 30px; fill: var(--bg); }

        .chat-drawer {
            position: fixed; top: 0; right: -400px; width: 400px; height: 100%;
            background: rgba(4, 12, 24, 0.95); backdrop-filter: blur(20px);
            border-left: 1px solid var(--border); z-index: 1001; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex; flex-direction: column; box-shadow: -10px 0 50px rgba(0,0,0,0.5);
        }
        .chat-drawer.open { right: 0; }
        .chat-drawer.expanded { width: 800px; max-width: 95%; }

        .chat-header { padding: 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 15px; }
        .chat-header-actions { display: flex; align-items: center; gap: 15px; }
        .chat-header h3 { margin: 0; font-size: 1.1rem; color: var(--primary); display: flex; align-items: center; gap: 10px; flex: 1; }
        
        .chat-messages { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 15px; }
        .message { max-width: 85%; padding: 12px 16px; border-radius: 15px; font-size: 0.95rem; line-height: 1.5; overflow-wrap: break-word; }
        .message.ai { background: rgba(255,255,255,0.05); align-self: flex-start; border-bottom-left-radius: 2px; border: 1px solid var(--border); }
        .message.user { background: var(--primary); color: var(--bg); align-self: flex-end; border-bottom-right-radius: 2px; font-weight: 500; }
        
        /* Styles Markdown & Tableaux */
        .message table { border-collapse: collapse; margin: 10px 0; width: 100%; font-size: 0.85rem; }
        .message th, .message td { border: 1px solid var(--border); padding: 8px; text-align: left; }
        .message th { background: rgba(255,255,255,0.1); color: var(--primary); }
        .message code { background: rgba(0,0,0,0.3); padding: 2px 5px; border-radius: 4px; font-family: monospace; }
        .message pre { background: rgba(0,0,0,0.3); padding: 10px; border-radius: 8px; overflow-x: auto; }
        .message blockquote { border-left: 3px solid var(--primary); margin: 10px 0; padding-left: 10px; font-style: italic; opacity: 0.8; }

        .chat-input-area { padding: 20px; border-top: 1px solid var(--border); display: flex; gap: 10px; }
        .chat-input { flex: 1; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 10px; padding: 12px; color: #fff; font-family: inherit; outline: none; transition: border-color 0.3s; }
        .chat-input:focus { border-color: var(--primary); }
        
        .web-toggle { background: transparent; border: 1px solid var(--border); width: 45px; height: 45px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; opacity: 0.6; }
        .web-toggle.active { background: rgba(66, 133, 244, 0.2); border-color: #4285F4; opacity: 1; box-shadow: 0 0 10px rgba(66, 133, 244, 0.4); }
        .web-toggle svg { width: 20px; height: 20px; stroke: #fff; }
        .web-toggle.active svg { stroke: #4285F4; }

        .send-btn { background: var(--primary); border: none; width: 45px; height: 45px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: transform 0.2s; }
        .send-btn:hover { transform: scale(1.05); }
        .send-btn svg { width: 20px; height: 20px; fill: var(--bg); }

        .close-chat { cursor: pointer; color: #fff; opacity: 0.5; transition: opacity 0.3s; }
        .close-chat:hover { opacity: 1; }

        /* Animation de chargement IA */
        .typing-indicator { display: flex; gap: 5px; padding: 10px; display: none; }
        .dot { width: 6px; height: 6px; background: var(--primary); border-radius: 50%; animation: bounce 1.4s infinite ease-in-out; }
        .dot:nth-child(2) { animation-delay: 0.2s; }
        .dot:nth-child(3) { animation-delay: 0.4s; }
        @keyframes bounce { 0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1); } }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="index.php" class="logo">IAI<span>-DOCS</span></a>
        <div class="nav-actions">
            <button class="btn" onclick="document.getElementById('doc-frame').requestFullscreen()">⛶ Plein écran</button>
            <a href="exams.php" class="btn">Quitter</a>
        </div>
    </div>

    <div class="viewer-container">
        <iframe id="doc-frame" src="<?= $proxyUrl ?>"></iframe>
    </div>

    <!-- Interface Tuteur IA -->
    <div class="ai-chat-trigger" id="ai-trigger" title="Demander au Tuteur IA">
        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.61.38 3.12 1.05 4.47l-1.01 3.65c-.14.52.34 1.01.86.86l3.65-1.01C7.88 20.62 9.39 21 12 21c5.52 0 10-4.48 10-10S17.52 2 12 2zm0 16c-1.46 0-2.84-.36-4.05-1l-.43-.22-2.18.6 1.05-3.81L6 13.05C5.36 11.84 5 10.46 5 9c0-3.86 3.14-7 7-7s7 3.14 7 7-3.14 7-7 7z"/></svg>
    </div>

    <div class="chat-drawer" id="chat-drawer">
        <div class="chat-header">
            <h3>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                Tuteur IA
            </h3>
            <div class="chat-header-actions">
                <div class="close-chat" id="expand-chat" title="Agrandir / Réduire">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                </div>
                <div class="close-chat" id="close-chat" title="Fermer">✕</div>
            </div>
        </div>
        <div class="chat-messages" id="chat-messages">
            <div class="message ai">
                Bonjour ! Je suis votre tuteur IAI. Posez-moi vos questions sur ce document, ou activez la recherche web 🌐 pour des questions d'actualité.
            </div>
        </div>
        <div class="typing-indicator" id="typing-indicator">
            <div class="dot"></div><div class="dot"></div><div class="dot"></div>
        </div>
        <div class="chat-input-area">
            <button class="web-toggle" id="web-toggle" title="Activer la recherche Google">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
            </button>
            <input type="text" class="chat-input" id="chat-input" placeholder="Posez votre question...">
            <button class="send-btn" id="send-btn">
                <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
            </button>
        </div>
    </div>

    <script>
        const drawer = document.getElementById('chat-drawer');
        const trigger = document.getElementById('ai-trigger');
        const closeBtn = document.getElementById('close-chat');
        const expandBtn = document.getElementById('expand-chat');
        const webToggle = document.getElementById('web-toggle');
        const chatInput = document.getElementById('chat-input');
        const sendBtn = document.getElementById('send-btn');
        const messagesContainer = document.getElementById('chat-messages');
        const typingIndicator = document.getElementById('typing-indicator');
        const iframe = document.getElementById('doc-frame');

        let isWebSearchEnabled = false;

        // Activer / Désactiver la recherche Web
        webToggle.onclick = () => {
            isWebSearchEnabled = !isWebSearchEnabled;
            webToggle.classList.toggle('active');
        };

        // Ouvrir / Fermer le chat
        trigger.onclick = () => drawer.classList.add('open');
        closeBtn.onclick = () => drawer.classList.remove('open');
        
        // Agrandir / Réduire le chat
        expandBtn.onclick = () => drawer.classList.toggle('expanded');

        // Fonction pour envoyer un message
        async function sendMessage() {
            const text = chatInput.value.trim();
            if (!text) return;

            // Ajouter le message utilisateur
            appendMessage('user', text);
            chatInput.value = '';
            
            // Extraire le contexte du document
            let docContext = "";
            try {
                docContext = iframe.contentDocument.body.innerText;
            } catch(e) { console.error("Erreur lecture contexte:", e); }

            // Afficher le chargement
            typingIndicator.style.display = 'flex';
            messagesContainer.scrollTop = messagesContainer.scrollHeight;

            try {
                const response = await fetch('backend/ai_handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        message: text,
                        context: docContext,
                        webSearch: isWebSearchEnabled
                    })
                });
                const data = await response.json();
                appendMessage('ai', data.reply);
            } catch (err) {
                appendMessage('ai', "Désolé, j'ai rencontré une erreur de connexion.");
            } finally {
                typingIndicator.style.display = 'none';
            }
        }

        function appendMessage(role, text) {
            const msgDiv = document.createElement('div');
            msgDiv.className = `message ${role}`;
            
            if (role === 'ai') {
                // 1. Rendu Markdown
                let html = marked.parse(text);
                
                // 2. Rendu LaTeX (Support des formules $...$ et $$...$$)
                html = html.replace(/\$\$(.*?)\$\$/gs, (match, p1) => {
                    try { return katex.renderToString(p1, { displayMode: true }); } catch(e) { return match; }
                });
                html = html.replace(/\$(.*?)\$/g, (match, p1) => {
                    try { return katex.renderToString(p1, { displayMode: false }); } catch(e) { return match; }
                });
                
                msgDiv.innerHTML = html;
                
                // 3. Rendu Mermaid (Graphes et schémas)
                setTimeout(() => {
                    mermaid.init(undefined, msgDiv.querySelectorAll('.language-mermaid'));
                }, 100);
            } else {
                msgDiv.innerText = text;
            }
            
            messagesContainer.appendChild(msgDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        sendBtn.onclick = sendMessage;
        chatInput.onkeypress = (e) => { if(e.key === 'Enter') sendMessage(); };

        // Écouter les messages venant de l'iframe (Texte surligné)
        window.addEventListener('message', (e) => {
            if (e.data.type === 'explain_text') {
                drawer.classList.add('open');
                chatInput.value = "Peux-tu m'expliquer ce passage : '" + e.data.text + "' ?";
                sendMessage();
            }
        });
    </script>
</body>
</html>
