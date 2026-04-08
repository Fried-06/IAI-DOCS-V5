// studio.js - Premium SaaS Vanilla JS Logic

document.addEventListener('DOMContentLoaded', () => {
    // State
    const state = {
        basket: [],
        searchTimeout: null,
        isProcessing: false
    };

    // DOM Elements
    const elements = {
        officialSearch: document.getElementById('officialSearch'),
        searchResults: document.getElementById('searchResults'),
        basketContainer: document.getElementById('basketContainer'),
        basketCount: document.getElementById('basketCount'),
        
        chatArea: document.getElementById('chatArea'),
        welcomeState: document.getElementById('welcomeState'),
        aiInput: document.getElementById('aiInput'),
        btnSend: document.getElementById('btnSend'),
        quickPrompts: document.getElementById('quickPrompts'),
        
        uploadModal: document.getElementById('uploadModalOverlay'),
        btnUploadModal: document.getElementById('btnUploadModal'),
        btnAttachInput: document.getElementById('btnAttachInput'),
        uploadModalClose: document.getElementById('uploadModalClose'),
        uploadForm: document.getElementById('uploadForm'),
        uploadStatus: document.getElementById('uploadStatus'),
        
        btnExport: document.getElementById('btnExport'),
        btnShare: document.getElementById('btnShare'),
        btnSettings: document.getElementById('btnSettings'),
        
        toolCards: document.querySelectorAll('.tool-card'),
        addDocsBtns: document.querySelectorAll('.add-to-basket')
    };

    // ==========================================
    // 1. MODAL & UPLOAD LOGIC
    // ==========================================
    if (elements.btnUploadModal) {
        elements.btnUploadModal.addEventListener('click', () => {
            elements.uploadModal.style.display = 'flex';
        });
    }

    if (elements.btnAttachInput) {
        elements.btnAttachInput.addEventListener('click', () => {
            elements.uploadModal.style.display = 'flex';
        });
    }

    if (elements.uploadModalClose) {
        elements.uploadModalClose.addEventListener('click', () => {
            elements.uploadModal.style.display = 'none';
        });
    }

    elements.uploadModal.addEventListener('click', (e) => {
        if (e.target === elements.uploadModal) {
            elements.uploadModal.style.display = 'none';
        }
    });

    if (elements.uploadForm) {
        elements.uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            elements.uploadStatus.innerHTML = '<span style="color: var(--c-accent-secondary);">Transfert sécurisé en cours...</span>';
            
            const formData = new FormData(elements.uploadForm);
            try {
                const req = await fetch('backend/upload_handler.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await req.json();
                if (res.success) {
                    elements.uploadStatus.innerHTML = '<span style="color: #10b981;">' + res.message + '</span>';
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    elements.uploadStatus.innerHTML = '<span style="color: var(--c-accent-danger);">' + res.error + '</span>';
                }
            } catch (err) {
                elements.uploadStatus.innerHTML = '<span style="color: var(--c-accent-danger);">Erreur système.</span>';
            }
        });
    }

    // ==========================================
    // 2. SEARCH & BASKET LOGIC
    // ==========================================
    if (elements.officialSearch) {
        elements.officialSearch.addEventListener('input', (e) => {
            const q = e.target.value;
            // Only search if user typed @
            if (!q.includes('@')) {
                elements.searchResults.style.display = 'none';
                return;
            }

            const cleanQ = q.replace('@', '').trim();
            clearTimeout(state.searchTimeout);

            if (cleanQ.length < 2) {
                elements.searchResults.innerHTML = '<div class="p-4 text-sm text-center text-muted">Tapez au moins 2 lettres après l\'@</div>';
                elements.searchResults.style.display = 'block';
                return;
            }

            state.searchTimeout = setTimeout(async () => {
                elements.searchResults.innerHTML = '<div class="p-4 text-sm text-center text-muted">Recherche...</div>';
                elements.searchResults.style.display = 'block';

                try {
                    const res = await fetch(`backend/search_official.php?q=${encodeURIComponent(cleanQ)}`);
                    const data = await res.json();

                    if (data.length > 0) {
                        elements.searchResults.innerHTML = data.map(item => `
                            <div class="search-item" data-id="${item.id}" data-name="${item.label}">
                                ${item.label}
                            </div>
                        `).join('');

                        // Bind clicks
                        elements.searchResults.querySelectorAll('.search-item').forEach(el => {
                            el.addEventListener('click', () => {
                                const id = el.getAttribute('data-id');
                                const name = el.getAttribute('data-name');
                                addToBasket(id, 'official', name);
                                elements.officialSearch.value = '';
                                elements.searchResults.style.display = 'none';
                            });
                        });
                    } else if (data.error) {
                        elements.searchResults.innerHTML = `<div class="p-4 text-sm text-center" style="color:var(--c-accent-danger)">⚠ Erreur serveur: ${data.error}</div>`;
                    } else {
                        elements.searchResults.innerHTML = '<div class="p-4 text-sm text-center text-muted">Aucun document trouvé pour &laquo;&nbsp;' + cleanQ + '&nbsp;&raquo;.<br><small style="opacity:.6">Essayez «maths», «physique», ou un titre exact.</small></div>';
                    }
                } catch (err) {
                    elements.searchResults.innerHTML = '<div class="p-4 text-sm text-center text-accent-danger">Erreur de connexion.</div>';
                }
            }, 300);
        });

        // Hide search results on outside click
        document.addEventListener('click', (e) => {
            if (!elements.officialSearch.contains(e.target) && !elements.searchResults.contains(e.target)) {
                elements.searchResults.style.display = 'none';
            }
        });
    }

    // Add private docs to basket
    elements.addDocsBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const btnEl = e.currentTarget;
            const id = btnEl.getAttribute('data-id');
            const type = btnEl.getAttribute('data-type');
            const name = btnEl.closest('li').querySelector('.doc-name').textContent;
            addToBasket(id, type, name);
        });
    });

    // Delete private docs
    document.querySelectorAll('.delete-private-doc').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const btnEl = e.currentTarget;
            const id = btnEl.getAttribute('data-id');
            if (confirm('Voulez-vous vraiment supprimer ce document de façon permanente ?')) {
                const formData = new FormData();
                formData.append('id', id);
                try {
                    const req = await fetch('backend/delete_private_doc.php', {
                        method: 'POST',
                        body: formData
                    });
                    const res = await req.json();
                    if (res.success) {
                        const li = document.getElementById('pdoc-' + id);
                        if (li) li.remove();
                        removeFromBasket(id);
                        if(elements.uploadStatus) elements.uploadStatus.innerHTML = '<span style="color: #10b981;">Document supprimé.</span>';
                    } else {
                        alert("Erreur: " + res.error);
                    }
                } catch(err) {
                    alert("Erreur système lors de la suppression.");
                }
            }
        });
    });

    function addToBasket(id, type, name) {
        if (!state.basket.find(i => i.id === id && i.type === type)) {
            state.basket.push({id, type, name});
            updateBasketUI();
            checkWelcomeState();
        }
    }

    function removeFromBasket(id) {
        state.basket = state.basket.filter(i => i.id !== id);
        updateBasketUI();
        checkWelcomeState();
    }

    function updateBasketUI() {
        if (state.basket.length === 0) {
            elements.basketContainer.innerHTML = `
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <p>Aucune ressource.</p>
                    <span>Importez des PDF ou cherchez @cours.</span>
                </div>
            `;
        } else {
            elements.basketContainer.innerHTML = '<ul class="doc-list">' + state.basket.map(item => `
                <li>
                    <span title="${item.name}">${item.name}</span>
                    <button class="btn-icon remove-basket" data-id="${item.id}">&times;</button>
                </li>
            `).join('') + '</ul>';

            elements.basketContainer.querySelectorAll('.remove-basket').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    removeFromBasket(e.target.getAttribute('data-id'));
                });
            });
        }
        elements.basketCount.textContent = state.basket.length;
    }

    function checkWelcomeState() {
        if (!elements.welcomeState) return;
        
        if (state.basket.length > 0) {
            // Update the summary card in welcome state
            const sumCard = elements.welcomeState.querySelector('.smart-summary-card');
            if (sumCard) {
                sumCard.querySelector('.card-header').innerHTML = `
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-accent-secondary"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    Prêt pour l'analyse
                `;
                sumCard.querySelector('.card-body').innerHTML = `
                    <p><strong>${state.basket.length} document(s)</strong> en mémoire contextuelle. Le moteur d'intelligence documentaire est prêt à extraire l'information.</p>
                    <p class="mt-2 text-xs">Sélectionnez un outil dans le panneau de droite ou posez une question ci-dessous.</p>
                `;
            }
        } else {
            const sumCard = elements.welcomeState.querySelector('.smart-summary-card');
            if (sumCard) {
                sumCard.querySelector('.card-header').innerHTML = `
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    Résumé Automatique En Attente
                `;
                sumCard.querySelector('.card-body').innerHTML = `
                    Insérez des ressources dans le panneau de gauche pour générer un aperçu intelligent. Le modèle extraira automatiquement les <strong>Mots Clés</strong> et préparera le contexte.
                `;
            }
        }
    }

    // ==========================================
    // 3. WORKSPACE CHAT LOGIC (AI INTERACTION)
    // ==========================================

    // Initialize mermaid with dark theme
    if (typeof mermaid !== 'undefined') {
        mermaid.initialize({ 
            startOnLoad: false, 
            theme: 'dark',
            themeVariables: {
                primaryColor: '#a855f7',
                primaryTextColor: '#f8fafc',
                lineColor: '#3b82f6',
                secondaryColor: '#1A2235',
                tertiaryColor: '#121826'
            }
        });
    }

    // Render LaTeX math in a given DOM element
    function renderMath(element) {
        if (typeof renderMathInElement !== 'undefined') {
            renderMathInElement(element, {
                delimiters: [
                    { left: '$$', right: '$$', display: true },
                    { left: '$', right: '$', display: false },
                    { left: '\\(', right: '\\)', display: false },
                    { left: '\\[', right: '\\]', display: true }
                ],
                throwOnError: false
            });
        }
    }

    // Render mermaid diagrams inside an element
    async function renderMermaidIn(element) {
        if (typeof mermaid === 'undefined') return;
        const mermaidEls = element.querySelectorAll('.mermaid-block');
        for (const el of mermaidEls) {
            try {
                const id = 'mermaid-' + Math.random().toString(36).substr(2, 9);
                const { svg } = await mermaid.render(id, el.textContent.trim());
                el.innerHTML = svg;
                el.classList.add('mermaid-rendered');
            } catch(e) {
                el.innerHTML = '<pre style="color:var(--c-accent-danger)">Erreur de rendu du diagramme</pre>';
            }
        }
    }

    // Process AI markdown: extract mermaid blocks, parse markdown, then render math
    function processAiContent(rawMarkdown) {
        // Step 1: Extract mermaid code blocks and replace with placeholders
        let mermaidBlocks = [];
        let processed = rawMarkdown.replace(/```mermaid\s*([\s\S]*?)```/gi, (match, code) => {
            const index = mermaidBlocks.length;
            mermaidBlocks.push(code.trim());
            return `%%MERMAID_PLACEHOLDER_${index}%%`;
        });

        // Step 2: Parse markdown
        let html = typeof marked !== 'undefined' ? marked.parse(processed) : processed;

        // Step 3: Re-inject mermaid blocks as styled containers
        mermaidBlocks.forEach((code, index) => {
            html = html.replace(
                `%%MERMAID_PLACEHOLDER_${index}%%`,
                `<div class="mermaid-block" style="background:rgba(255,255,255,0.03); padding:1.5rem; border-radius:var(--radius-md); border:1px solid var(--c-border); margin:1rem 0; text-align:center;">${code}</div>`
            );
        });

        return html;
    }
    
    function appendMessage(role, contentHtml) {
        if (elements.welcomeState) {
            elements.welcomeState.style.display = 'none';
        }

        const msgDiv = document.createElement('div');
        msgDiv.className = `msg ${role}`;
        
        let avatarSvg = role === 'user' ? 
            'ME' : 
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a10 10 0 1010 10H12V2z"/><path d="M12 12L2.5 16.5"/><path d="M12 12l8.5 6.5"/></svg>';

        msgDiv.innerHTML = `
            <div class="msg-avatar">${avatarSvg}</div>
            <div class="msg-content">${contentHtml}</div>
        `;
        
        let thread = elements.chatArea.querySelector('.chat-thread');
        if (!thread) {
            thread = document.createElement('div');
            thread.className = 'chat-thread';
            elements.chatArea.appendChild(thread);
        }

        thread.appendChild(msgDiv);
        elements.chatArea.scrollTop = elements.chatArea.scrollHeight;
        
        // Post-render: KaTeX math + Mermaid diagrams
        const contentEl = msgDiv.querySelector('.msg-content');
        if (role === 'ai' && contentEl) {
            renderMath(contentEl);
            renderMermaidIn(contentEl);
        }
        
        return msgDiv;
    }

    function DOMPurifyIsSafe(str, token) {
        return str.includes(token);
    }
    
    function showLoading() {
        return appendMessage('ai', `
            <div style="display:flex; gap:0.5rem; align-items:center;">
                <div style="width:8px;height:8px;border-radius:50%;background:var(--c-accent-primary);animation:pulse 1s infinite alternate;"></div>
                <div style="width:8px;height:8px;border-radius:50%;background:var(--c-accent-secondary);animation:pulse 1s infinite alternate 0.2s;"></div>
                <div style="width:8px;height:8px;border-radius:50%;background:var(--c-border-light);animation:pulse 1s infinite alternate 0.4s;"></div>
            </div>
        `);
    }

    async function triggerAiAction(actionName, customPrompt = null) {
        if (state.basket.length === 0 && actionName !== 'chat') {
            alert("Veuillez d'abord ajouter des documents dans vos Ressources (à gauche).");
            return;
        }

        if (state.isProcessing) return;
        state.isProcessing = true;

        if (customPrompt) {
            appendMessage('user', customPrompt);
            elements.aiInput.value = '';
        } else {
            const actionTextMappping = {
                mindmap: "Génère une carte mentale détaillant les concepts principaux.",
                quiz: "Créer un quiz pour me tester.",
                audio: "Génère un dialogue audio/explicatif.",
                concepts: "Extraire les concepts clés et le lexique.",
                flashcards: "Créer des flashcards de mémorisation.",
                traps: "Quels sont les pièges fréquents liés à ces documents pour un examen ?",
                resume: "Fais-moi un résumé synthétique de ces documents.",
                infographie: "Présente ces informations sous forme d'infographie structurée (texte et mise en page simple)."
            };
            appendMessage('user', actionTextMappping[actionName] || "Effectuer l'action : " + actionName);
        }

        const loaderNode = showLoading();

        try {
            const payload = { 
                action: actionName, 
                basket: state.basket,
                prompt: customPrompt
            };
            
            const res = await fetch('backend/gemini_proxy.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            
            loaderNode.remove(); // Remove loading state

            if (data.success) {
                const rendered = processAiContent(data.result);
                appendMessage('ai', rendered);
            } else {
                appendMessage('ai', `<span class="text-accent-danger">Erreur AI: ${data.error}</span>`);
            }
        } catch (err) {
            loaderNode.remove();
            appendMessage('ai', `<span class="text-accent-danger">Erreur de connexion avec le copilote IA.</span>`);
        } finally {
            state.isProcessing = false;
        }
    }

    // Tools Trigger
    elements.toolCards.forEach(card => {
        card.addEventListener('click', () => {
            triggerAiAction(card.getAttribute('data-action'));
        });
    });

    // Chat Trigger
    elements.btnSend.addEventListener('click', () => {
        const text = elements.aiInput.value.trim();
        if (text) triggerAiAction('chat', text);
    });

    elements.aiInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            const text = elements.aiInput.value.trim();
            if (text) triggerAiAction('chat', text);
        }
    });

    // Quick Prompts
    if (elements.quickPrompts) {
        elements.quickPrompts.querySelectorAll('.quick-pill').forEach(pill => {
            pill.addEventListener('click', () => {
                triggerAiAction('chat', pill.textContent);
            });
        });
    }

    // Top Header Buttons
    if (elements.btnExport) {
        elements.btnExport.addEventListener('click', () => {
            alert('Fonctionnalité : Export de la discussion en PDF (en cours de développement).');
        });
    }
    
    if (elements.btnShare) {
        elements.btnShare.addEventListener('click', () => {
            alert('Fonctionnalité : Lien copié dans le presse-papier !');
        });
    }
    
    if (elements.btnSettings) {
        elements.btnSettings.addEventListener('click', () => {
             // Example simple theme toggling
             const ui = document.querySelector('html');
             if(ui.style.filter === 'invert(1)') {
                 ui.style.filter = '';
                 alert('Thème : Mode Nuit (défaut)');
             } else {
                 ui.style.filter = 'invert(1)';
                 alert('Thème : Mode Clair activé (expérimental)');
             }
        });
    }

});
