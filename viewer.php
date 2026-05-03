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
        <!-- L'iframe appelle maintenant le proxy via un chemin masqué -->
        <iframe id="doc-frame" src="<?= $proxyUrl ?>"></iframe>
    </div>
</body>
</html>
