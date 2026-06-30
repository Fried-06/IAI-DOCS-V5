<?php
// backend/config.php
// ─────────────────────────────────────────────────────────────
// Ce fichier NE CONTIENT PAS de secrets.
// Toutes les valeurs sensibles sont lues depuis les variables
// d'environnement injectées par le serveur (Render, Docker, etc.)
//
// Pour configurer sur Render :
//   Dashboard → Service → Environment → Add Environment Variable
//
//   Variables requises :
//     DB_HOST          → ex: aws-0-eu-west-1.pooler.supabase.com
//     DB_PORT          → 5432
//     DB_NAME          → postgres
//     DB_USER          → postgres.egdltwovnapbjgfmjmoi
//     DB_PASS          → (ton mot de passe Supabase)
//     SUPABASE_URL     → https://egdltwovnapbjgfmjmoi.supabase.co
//     SUPABASE_KEY     → (ta clé service_role Supabase)
//     GEMINI_API_KEY   → (ta clé Google Gemini)
//
// En développement local, copier .env.example vers .env et
// renseigner les valeurs. Apache/PHP lira les env vars via php-fpm
// ou via la commande : set DB_PASS=... avant de lancer le serveur.
// ─────────────────────────────────────────────────────────────

function env(string $key, string $default = ''): string {
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
    return $default;
}

// ── Base de données PostgreSQL (Supabase) ──────────────────
define('DB_HOST',    env('DB_HOST',    'localhost'));
define('DB_PORT',    env('DB_PORT',    '5432'));
define('DB_NAME',    env('DB_NAME',    'postgres'));
define('DB_USER',    env('DB_USER',    ''));
define('DB_PASS',    env('DB_PASS',    ''));
define('DB_CHARSET', 'utf8mb4');

// ── Supabase Storage / Auth ────────────────────────────────
define('SUPABASE_STORAGE_URL', env('SUPABASE_URL', ''));
define('SUPABASE_KEY',         env('SUPABASE_KEY', ''));

// ── Google Gemini API ──────────────────────────────────────
define('GEMINI_API_KEY', env('GEMINI_API_KEY', ''));
