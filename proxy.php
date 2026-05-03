<?php
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
                if (!h || h === '#' || h.startsWith('javascript:')) return;
                if (h.startsWith('#')) {
                    e.preventDefault(); e.stopPropagation();
                    const t = document.getElementById(h.substring(1)) || document.getElementsByName(h.substring(1))[0];
                    if (t) t.scrollIntoView({ behavior: 'smooth' });
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
        })();
    </script>";

    $content = str_replace('<head>', '<head>' . $baseTag, $content);
    $content = str_contains($content, '</body>') ? str_replace('</body>', $interceptScript . '</body>', $content) : $content . $interceptScript;
}

echo $content;
