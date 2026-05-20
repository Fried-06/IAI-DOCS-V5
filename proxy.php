<?php
require_once 'backend/beta_check.php';
// proxy.php — Tunnel Premium avec Masquage d'URL (Spécial Sphinx Search)
error_reporting(0);
require_once 'backend/config.php';

// La triche : on récupère l'URL soit par ?url= soit par le chemin /https/...
$pathInfo = $_SERVER['PATH_INFO'] ?? '';
$url = $_GET['url'] ?? '';

if (empty($url) && !empty($pathInfo)) {
    // On transforme /https/domaine.com/... en https://domaine.com/...
    $url = preg_replace('#^/(https?)/#', '$1://', $pathInfo);
}

if (empty($url) || !str_contains($url, 'supabase.co')) {
    die("URL invalide ou manquante.");
}

// On rajoute les paramètres de recherche (?q=...) s'ils existent
if (!empty($_SERVER['QUERY_STRING']) && !str_contains($url, '?')) {
    // On retire le paramètre 'url' de la query string s'il existe
    $qs = preg_replace('/url=[^&]+&?/', '', $_SERVER['QUERY_STRING']);
    if (!empty($qs)) $url .= '?' . $qs;
}

$ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
$mimeTypes = [
    'html' => 'text/html', 'css' => 'text/css', 'js' => 'application/javascript',
    'json' => 'application/json', 'png' => 'image/png', 'jpg' => 'image/jpeg',
    'gif' => 'image/gif', 'svg' => 'image/svg+xml'
];
$contentType = $mimeTypes[$ext] ?? 'text/plain';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
$content = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode !== 200) die("Erreur Cloud ($httpCode)");

header("Content-Type: $contentType");

if ($ext === 'html') {
    $baseUrl = substr($url, 0, strrpos($url, '/') + 1);
    $bucketRoot = explode('/subjects/', $url)[0] . '/subjects/';
    
    $baseTag = "<base href=\"$baseUrl\">";
    $interceptScript = "
    <script>
        (function() {
            const bucketRoot = '$bucketRoot';
            const currentOrigin = window.location.origin;
            const viewerUrl = currentOrigin + '/viewer.php';
            const proxyUrl = currentOrigin + '/proxy.php/'; // Note le / final

            // Interception des clics
            window.addEventListener('click', function(e) {
                const a = e.target.closest('a');
                if (!a) return;
                const h = a.getAttribute('href');
                if (!h || h.startsWith('javascript:')) return;

                // Cas spécial : Retour en haut (# ou #top)
                if (h === '#' || h === '#top') {
                    e.preventDefault(); e.stopPropagation();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }

                // Ancres internes classiques
                if (h.startsWith('#')) {
                    e.preventDefault(); e.stopPropagation();
                    const t = document.getElementById(h.substring(1)) || document.getElementsByName(h.substring(1))[0];
                    if (t) {
                        t.scrollIntoView({ behavior: 'smooth' });
                    } else {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                    return;
                }
                e.preventDefault(); e.stopPropagation();
                window.parent.location.href = viewerUrl + '?url=' + encodeURIComponent(a.href);
            }, true);

            // Interception Recherche (La Triche)
            window.addEventListener('submit', function(e) {
                const f = e.target;
                if (!f) return;
                e.preventDefault(); e.stopPropagation();
                const p = new URLSearchParams(new FormData(f));
                // On redirige vers search.html avec les vrais paramètres URL
                const dest = bucketRoot + 'search.html?' + p.toString();
                window.parent.location.href = viewerUrl + '?url=' + encodeURIComponent(dest);
            }, true);

            // Force Proxy AJAX
            const originalFetch = window.fetch;
            window.fetch = function(input, init) {
                let u = (typeof input === 'string') ? input : (input.url || input.href);
                if (u && !u.startsWith(currentOrigin)) {
                    u = proxyUrl + new URL(u, '$baseUrl').href.replace('://', '/');
                }
                return originalFetch(u, init);
            };

            const originalOpen = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function(m, u) {
                if (u && !u.startsWith(currentOrigin) && !u.startsWith('/')) {
                    u = proxyUrl + new URL(u, '$baseUrl').href.replace('://', '/');
                }
                originalOpen.apply(this, arguments);
            };

            // 4. SURLIGNER POUR EXPLIQUER
            const style = document.createElement('style');
            style.textContent = `
                .ai-explain-btn {
                    position: absolute; background: #00e5c4; color: #040c18;
                    padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;
                    cursor: pointer; z-index: 10000; box-shadow: 0 4px 15px rgba(0,0,0,0.3);
                    display: none; align-items: center; gap: 5px; border: none;
                }
            `;
            document.head.appendChild(style);

            const explainBtn = document.createElement('button');
            explainBtn.className = 'ai-explain-btn';
            explainBtn.innerHTML = '✨ Expliquer avec l\\'IA';
            document.body.appendChild(explainBtn);

            document.addEventListener('mouseup', function(e) {
                const selection = window.getSelection();
                const text = selection.toString().trim();

                if (text.length > 5) {
                    const range = selection.getRangeAt(0);
                    const rect = range.getBoundingClientRect();

                    explainBtn.style.left = (rect.left + window.scrollX) + 'px';
                    explainBtn.style.top = (rect.top + window.scrollY - 40) + 'px';
                    explainBtn.style.display = 'flex';
                    explainBtn.onmousedown = function(btnEv) {
                        btnEv.preventDefault();
                        window.parent.postMessage({ type: 'explain_text', text: text }, '*');
                        explainBtn.style.display = 'none';
                        selection.removeAllRanges();
                    };
                } else {
                    explainBtn.style.display = 'none';
                }
            });
        })();
    </script>";

    // --- Injection de Thèmes Premium & Polices d'Écriture ---
    $theme = $_GET['theme'] ?? 'cosmic';
    
    // Google Fonts Import
    $fontImport = '<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&family=Plus+Jakarta+Sans:wght@300;400;600&family=Merriweather:ital,wght@0,300;0,400;0,700;1,400&family=JetBrains+Mono:wght@300;400;600&display=swap" rel="stylesheet">';
    
    $themeCss = "";
    if ($theme === 'cosmic') {
        $themeCss = "
            :root, :root[data-theme='light'], :root[data-theme='dark'] {
                --font-stack: 'Outfit', sans-serif !important;
                --font-stack--monospace: 'JetBrains Mono', monospace !important;
                --color-brand-primary: #00e5c4 !important;
                --color-brand-content: #00e5c4 !important;
                --color-background-primary: #040c18 !important;
                --color-background-secondary: rgba(13, 27, 45, 0.7) !important;
                --color-background-hover: rgba(255, 255, 255, 0.05) !important;
                --color-foreground-primary: #e2e8f0 !important;
                --color-foreground-secondary: #94a3b8 !important;
                --color-sidebar-link-text: #94a3b8 !important;
                --color-sidebar-link-text--top-level: #e2e8f0 !important;
                --color-sidebar-item-background--hover: rgba(0, 229, 196, 0.1) !important;
                --color-sidebar-item-background--active: rgba(0, 229, 196, 0.15) !important;
                --color-link: #00e5c4 !important;
                --color-link--hover: #a855f7 !important;
                --color-sidebar-background: rgba(13, 27, 45, 0.75) !important;
            }
            body {
                background-color: #040c18 !important;
                color: #e2e8f0 !important;
            }
            .sidebar-drawer {
                backdrop-filter: blur(10px) !important;
                -webkit-backdrop-filter: blur(10px) !important;
                border-right: 1px solid rgba(255, 255, 255, 0.05) !important;
            }
            .sphinx-toggle-button, .togglebutton {
                border-color: rgba(255, 255, 255, 0.1) !important;
            }
            ::-webkit-scrollbar { width: 8px; height: 8px; }
            ::-webkit-scrollbar-track { background: #040c18; }
            ::-webkit-scrollbar-thumb { background: rgba(0, 229, 196, 0.2); border-radius: 4px; }
            ::-webkit-scrollbar-thumb:hover { background: rgba(0, 229, 196, 0.5); }
        ";
    } elseif ($theme === 'sepia') {
        $themeCss = "
            :root, :root[data-theme='light'], :root[data-theme='dark'] {
                --font-stack: 'Merriweather', serif !important;
                --font-stack--monospace: 'JetBrains Mono', monospace !important;
                --color-brand-primary: #b58900 !important;
                --color-brand-content: #b58900 !important;
                --color-background-primary: #fdf6e3 !important;
                --color-background-secondary: #eee8d5 !important;
                --color-background-hover: rgba(0, 0, 0, 0.05) !important;
                --color-foreground-primary: #586e75 !important;
                --color-foreground-secondary: #93a1a1 !important;
                --color-sidebar-link-text: #586e75 !important;
                --color-sidebar-link-text--top-level: #073642 !important;
                --color-sidebar-item-background--hover: rgba(181, 137, 0, 0.1) !important;
                --color-sidebar-item-background--active: rgba(181, 137, 0, 0.15) !important;
                --color-link: #b58900 !important;
                --color-link--hover: #cb4b16 !important;
            }
            body {
                background-color: #fdf6e3 !important;
                color: #586e75 !important;
                line-height: 1.7 !important;
            }
            .sidebar-drawer {
                border-right: 1px solid rgba(0, 0, 0, 0.08) !important;
                background-color: #eee8d5 !important;
            }
            ::-webkit-scrollbar { width: 8px; height: 8px; }
            ::-webkit-scrollbar-track { background: #fdf6e3; }
            ::-webkit-scrollbar-thumb { background: rgba(181, 137, 0, 0.2); border-radius: 4px; }
            ::-webkit-scrollbar-thumb:hover { background: rgba(181, 137, 0, 0.5); }
        ";
    } elseif ($theme === 'cyberpunk') {
        $themeCss = "
            :root, :root[data-theme='light'], :root[data-theme='dark'] {
                --font-stack: 'Plus Jakarta Sans', sans-serif !important;
                --font-stack--monospace: 'JetBrains Mono', monospace !important;
                --color-brand-primary: #f43f5e !important;
                --color-brand-content: #f43f5e !important;
                --color-background-primary: #0f172a !important;
                --color-background-secondary: #1e293b !important;
                --color-background-hover: rgba(255, 255, 255, 0.05) !important;
                --color-foreground-primary: #f8fafc !important;
                --color-foreground-secondary: #94a3b8 !important;
                --color-sidebar-link-text: #94a3b8 !important;
                --color-sidebar-link-text--top-level: #f8fafc !important;
                --color-sidebar-item-background--hover: rgba(244, 63, 94, 0.1) !important;
                --color-sidebar-item-background--active: rgba(244, 63, 94, 0.15) !important;
                --color-link: #f43f5e !important;
                --color-link--hover: #38bdf8 !important;
            }
            body {
                background-color: #0f172a !important;
                color: #f8fafc !important;
            }
            .sidebar-drawer {
                border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
                background-color: #1e293b !important;
            }
            ::-webkit-scrollbar { width: 8px; height: 8px; }
            ::-webkit-scrollbar-track { background: #0f172a; }
            ::-webkit-scrollbar-thumb { background: rgba(244, 63, 94, 0.2); border-radius: 4px; }
            ::-webkit-scrollbar-thumb:hover { background: rgba(244, 63, 94, 0.5); }
        ";
    } else { // Light Premium
        $themeCss = "
            :root, :root[data-theme='light'], :root[data-theme='dark'] {
                --font-stack: 'Outfit', sans-serif !important;
                --font-stack--monospace: 'JetBrains Mono', monospace !important;
                --color-brand-primary: #7c3aed !important;
                --color-brand-content: #7c3aed !important;
                --color-background-primary: #ffffff !important;
                --color-background-secondary: #f8fafc !important;
                --color-background-hover: rgba(0, 0, 0, 0.03) !important;
                --color-foreground-primary: #0f172a !important;
                --color-foreground-secondary: #475569 !important;
                --color-sidebar-link-text: #475569 !important;
                --color-sidebar-link-text--top-level: #0f172a !important;
                --color-sidebar-item-background--hover: rgba(124, 58, 237, 0.08) !important;
                --color-sidebar-item-background--active: rgba(124, 58, 237, 0.12) !important;
                --color-link: #7c3aed !important;
                --color-link--hover: #4c1d95 !important;
            }
            body {
                background-color: #ffffff !important;
                color: #0f172a !important;
            }
            .sidebar-drawer {
                border-right: 1px solid rgba(0, 0, 0, 0.05) !important;
                background-color: #f8fafc !important;
            }
            ::-webkit-scrollbar { width: 8px; height: 8px; }
            ::-webkit-scrollbar-track { background: #ffffff; }
            ::-webkit-scrollbar-thumb { background: rgba(124, 58, 237, 0.2); border-radius: 4px; }
            ::-webkit-scrollbar-thumb:hover { background: rgba(124, 58, 237, 0.5); }
        ";
    }
    
    // Style de masquage pour la sidebar originale de Furo afin de laisser place à notre sidebar dynamique SQL
    $furoOverrideCss = "
        :root {
            --page-width: 100% !important;
            --content-width: 100% !important;
            --compact-width: 100% !important;
        }
        .sidebar-drawer, .sidebar-toggle, .mobile-header { display: none !important; }
        
        /* Forcer l'affichage de la sidebar de droite (TOC) sur ordinateur même si l'iframe est rétrécie par notre sidebar */
        @media (min-width: 48rem) {
            .toc-drawer {
                display: block !important;
            }
            .article-container {
                display: grid !important;
                grid-template-columns: 1fr var(--toc-width) !important;
                max-width: 100% !important;
                width: 100% !important;
            }
        }
        
        /* Étendre tous les conteneurs à 100% et enlever le centrage restrictif */
        .page, .main, .page-content, .content, .content-container, .document, .main-content {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
        }
        
        /* Espacement premium aéré pour le confort de lecture */
        .content-container {
            padding: 2rem 3rem !important;
        }
    ";

    $injectedHead = $fontImport . "\n<style>\n" . $themeCss . "\n" . $furoOverrideCss . "\n</style>\n";

    $content = str_replace('<head>', '<head>' . $baseTag . $injectedHead, $content);
    $content = str_contains($content, '</body>') ? str_replace('</body>', $interceptScript . '</body>', $content) : $content . $interceptScript;
}

echo $content;
