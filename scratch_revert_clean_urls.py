import os
import re

files_to_fix = [
    'index.html',
    'login.html',
    'contribute.html',
    'about.html',
    'contact.html',
    'exams.php',
    'search.php',
    'profile.php',
    'beta_gate.php',
    'contributors.php'
]

replacements = {
    r'href="/Accueil"': 'href="index.html"',
    r'href="Accueil"': 'href="index.html"',
    r'href="/Examens"': 'href="exams.php"',
    r'href="Examens"': 'href="exams.php"',
    r'href="/Rechercher"': 'href="search.php"',
    r'href="Rechercher"': 'href="search.php"',
    r'href="/Contribuer"': 'href="contribute.html"',
    r'href="Contribuer"': 'href="contribute.html"',
    r'href="/Connexion"': 'href="login.html"',
    r'href="Connexion"': 'href="login.html"',
    r'href="/Profil"': 'href="profile.php"',
    r'href="Profil"': 'href="profile.php"',
    r'href="/AccesBeta"': 'href="beta_gate.php"',
    r'href="AccesBeta"': 'href="beta_gate.php"',
    
    # Fix assets that might have been made absolute
    r'href="/css/': 'href="css/',
    r'src="/js/': 'src="js/',
    r'src="/assets/': 'src="assets/'
}

for filename in files_to_fix:
    if not os.path.exists(filename):
        continue
    with open(filename, 'r', encoding='utf-8') as f:
        content = f.read()
        
    for old, new in replacements.items():
        content = re.sub(old, new, content)
        
    with open(filename, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"Updated {filename}")
