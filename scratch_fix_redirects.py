import os
import re

# List of root-level PHP files that need beta protection (but don't have it yet)
files_needing_beta = ['exams.php', 'search.php', 'contribute.html', 'about.html', 'contact.html']

for fname in files_needing_beta:
    if not os.path.exists(fname):
        continue
    with open(fname, 'r', encoding='utf-8', errors='replace') as f:
        content = f.read()
    if 'beta_check.php' not in content:
        if fname.endswith('.php'):
            # Insert after session_start
            content = content.replace(
                'session_start();',
                'session_start();\nrequire_once __DIR__ . \'/backend/beta_check.php\';',
                1
            )
        print(f'Updated {fname}')
        with open(fname, 'w', encoding='utf-8') as f:
            f.write(content)
    else:
        print(f'{fname} already has beta_check')

# Fix beta_gate.php: redirect to index.php instead of Accueil
with open('beta_gate.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

# Fix the redirect after successful beta authorization
content = content.replace("header('Location: Accueil');", "header('Location: index.php');")
content = content.replace('header("Location: Accueil");', 'header("Location: index.php");')

with open('beta_gate.php', 'w', encoding='utf-8') as f:
    f.write(content)
print('beta_gate.php redirects fixed to index.php')

# Fix auth.php: use relative path from backend/ to root
with open('backend/auth.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()
content = content.replace("header('Location: ../Accueil');", "header('Location: ../index.php');")
content = content.replace('header("Location: ../Accueil");', 'header("Location: ../index.php");')
content = content.replace("header('Location: ../Connexion');", "header('Location: ../login.php');")
content = content.replace('header("Location: ../Connexion");', 'header("Location: ../login.php");')
content = content.replace("window.location='/Connexion';", "window.location='../login.html';")
content = content.replace("window.location='../Connexion';", "window.location='../login.html';")
with open('backend/auth.php', 'w', encoding='utf-8') as f:
    f.write(content)
print('backend/auth.php fixed')

# Fix logout.php: use relative path from backend/
with open('backend/logout.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()
content = content.replace('header("Location: ../AccesBeta");', 'header("Location: ../beta_gate.php");')
content = content.replace("header('Location: ../AccesBeta');", "header('Location: ../beta_gate.php');")
with open('backend/logout.php', 'w', encoding='utf-8') as f:
    f.write(content)
print('backend/logout.php fixed')

# Fix beta_check.php: use relative path back to beta_gate.php
with open('backend/beta_check.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()
# Replace the redirect logic entirely
old_redirect = """        $redirectUrl = 'AccesBeta';
        if (str_contains($_SERVER['REQUEST_URI'], '/backend/')) {
            $redirectUrl = '../AccesBeta';
        }
        header('Location: ' . $redirectUrl);"""
new_redirect = """        // Redirect to beta gate - detect if called from backend/ or root
        $scriptDir = dirname($_SERVER['SCRIPT_FILENAME']);
        $rootDir = realpath(__DIR__ . '/..');
        if (realpath($scriptDir) === realpath(__DIR__)) {
            // Called from backend/ itself
            $redirectUrl = '../beta_gate.php';
        } else {
            // Called from root or pages/subjects
            $redirectUrl = '/beta_gate.php';
            // Build a relative path based on script location
            $relDepth = substr_count(str_replace($rootDir, '', realpath($scriptDir)), DIRECTORY_SEPARATOR);
            $redirectUrl = str_repeat('../', $relDepth) . 'beta_gate.php';
            if ($relDepth === 0) $redirectUrl = 'beta_gate.php';
        }
        header('Location: ' . $redirectUrl);"""
content = content.replace(old_redirect, new_redirect)
with open('backend/beta_check.php', 'w', encoding='utf-8') as f:
    f.write(content)
print('backend/beta_check.php redirect fixed')

print('\nAll done!')
