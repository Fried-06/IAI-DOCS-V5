<?php
// router.php - Routeur pour le serveur PHP intégré (php -S)
// Remplace le fichier .htaccess qui n'est pas lu par "php -S"

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// 1. Si le fichier statique existe (css, js, images...), on laisse PHP le servir
if ($path !== '/' && file_exists(__DIR__ . $path) && is_file(__DIR__ . $path)) {
    return false;
}

// 1.5. Support for PATH_INFO (e.g. /proxy.php/https/...)
$script_name = $path;
while ($script_name && $script_name !== '/') {
    if (file_exists(__DIR__ . $script_name) && is_file(__DIR__ . $script_name) && str_ends_with($script_name, '.php')) {
        $_SERVER['SCRIPT_NAME'] = $script_name;
        $_SERVER['PHP_SELF'] = $path;
        $_SERVER['PATH_INFO'] = substr($path, strlen($script_name));
        $_SERVER['SCRIPT_FILENAME'] = __DIR__ . $script_name;
        require __DIR__ . $script_name;
        return true;
    }
    $script_name = dirname($script_name);
    if ($script_name === '\\' || $script_name === '.') $script_name = '/';
}

// 2. Règles de réécriture (comme .htaccess)
$rules = [
    '#^/?$#' => '/index.php',
    '#^/Accueil/?$#' => '/index.php',
    '#^/Examens/?$#' => '/exams.php',
    '#^/Rechercher/?$#' => '/search.php',
    '#^/Contribuer/?$#' => '/contribute.html',
    '#^/Profil/?$#' => '/profile.php',
    '#^/Connexion/?$#' => '/login.html',
    '#^/AccesBeta/?$#' => '/beta_gate.php',
    
    '#^/viewer/([0-9]+)/?$#' => '/viewer.php',
    
    '#^/L1/?$#' => '/pages/L1/index.php',
    '#^/L2/?$#' => '/pages/L2/index.php',
    '#^/L3/GLSI/?$#' => '/pages/L3/GLSI/index.php',
    '#^/L3/ASR/?$#' => '/pages/L3/ASR/index.php',
    
    '#^/L1/(semestre[0-9]+)/?$#' => '/pages/L1/$1.php',
    '#^/L2/(semestre[0-9]+)/?$#' => '/pages/L2/$1.php',
    '#^/L3/GLSI/(semestre[0-9]+)/?$#' => '/pages/L3/GLSI/$1.php',
    '#^/L3/ASR/(semestre[0-9]+)/?$#' => '/pages/L3/ASR/$1.php',
    
    '#^/Matiere/L1/(semestre[0-9]+)/([a-z0-9_-]+)/?$#' => '/subjects/L1/$1/$2.php',
    '#^/Matiere/L2/(semestre[0-9]+)/([a-z0-9_-]+)/?$#' => '/subjects/L2/$1/$2.php',
    '#^/Matiere/L3/GLSI/(semestre[0-9]+)/([a-z0-9_-]+)/?$#' => '/subjects/L3/GLSI/$1/$2.php',
    '#^/Matiere/L3/ASR/(semestre[0-9]+)/([a-z0-9_-]+)/?$#' => '/subjects/L3/ASR/$1/$2.php',
];

foreach ($rules as $pattern => $replacement) {
    if (preg_match($pattern, $path, $matches)) {
        if (strpos($pattern, 'viewer') !== false) {
            $_GET['id'] = $matches[1];
        }
        
        $target = preg_replace($pattern, $replacement, $path);
        
        // Simuler le comportement d'Apache pour les scripts
        $_SERVER['SCRIPT_NAME'] = $target;
        $_SERVER['PHP_SELF'] = $target;
        $_SERVER['SCRIPT_FILENAME'] = __DIR__ . $target;
        
        require __DIR__ . $target;
        return true;
    }
}

// 3. Page 404
http_response_code(404);
if (file_exists(__DIR__ . '/404.html')) {
    require __DIR__ . '/404.html';
} else {
    echo "<h1>404 Not Found</h1>";
}
return true;
