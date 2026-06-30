import os
import re

directory = r"C:\Users\MSI\Music\IAI_MENTORIA5 - Copie\iai-resources"

replacements = {
    'href="index.html"': 'href="Accueil"',
    'href="exams.php"': 'href="Examens"',
    'href="exams.html"': 'href="Examens"',
    'href="search.php"': 'href="Rechercher"',
    'href="search.html"': 'href="Rechercher"',
    'href="contribute.html"': 'href="Contribuer"',
    'href="contribute.php"': 'href="Contribuer"',
    'href="login.html"': 'href="Connexion"',
    'href="login.php"': 'href="Connexion"',
    'href="profile.html"': 'href="Profil"',
    'href="profile.php"': 'href="Profil"',
    'href="beta_gate.php"': 'href="AccesBeta"',
    'href="beta_gate.html"': 'href="AccesBeta"'
}

# Special case for "Contributeurs" vs "Contribuer" (not strictly in router, but just in case)
# Wait, router doesn't map Contributeurs. Let's keep contributors.php as is.

count = 0
for root, dirs, files in os.walk(directory):
    # Skip Docs directory
    if '\\Docs\\' in root or root.endswith('\\Docs'):
        continue
    
    for file in files:
        if file.endswith('.html') or file.endswith('.php'):
            filepath = os.path.join(root, file)
            
            try:
                with open(filepath, 'r', encoding='utf-8') as f:
                    content = f.read()
                    
                new_content = content
                for old_val, new_val in replacements.items():
                    new_content = new_content.replace(old_val, new_val)
                    
                if new_content != content:
                    with open(filepath, 'w', encoding='utf-8') as f:
                        f.write(new_content)
                    count += 1
            except Exception as e:
                print(f"Error processing {filepath}: {e}")

print(f"Successfully updated routes in {count} files.")
