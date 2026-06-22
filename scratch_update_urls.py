import os
import glob

replacements = {
    'href="index.html"': 'href="/Accueil"',
    'href="exams.php"': 'href="/Examens"',
    'href="search.php"': 'href="/Rechercher"',
    'href="contribute.html"': 'href="/Contribuer"',
    'href="login.html"': 'href="/Connexion"',
    'href="profile.php"': 'href="/Profil"'
}

directory = r"C:\Users\MSI\Music\IAI_MENTORIA5 - Copie\iai-resources"

files_to_check = glob.glob(os.path.join(directory, "*.html")) + glob.glob(os.path.join(directory, "*.php"))

for file_path in files_to_check:
    with open(file_path, "r", encoding="utf-8") as f:
        content = f.read()
    
    new_content = content
    for old_val, new_val in replacements.items():
        new_content = new_content.replace(old_val, new_val)
        
    if new_content != content:
        with open(file_path, "w", encoding="utf-8") as f:
            f.write(new_content)
        print(f"Updated {os.path.basename(file_path)}")

print("Done.")
