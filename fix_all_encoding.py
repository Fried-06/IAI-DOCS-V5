import os
import glob

# Current workspace directory
base_dir = r"c:\Users\MSI\Music\IAI_MENTORIA5 - Copie\iai-resources"

# Define the mojibake mapping
# These are common multiple-encoding errors (UTF-8 bytes interpreted as other encodings and saved as UTF-8)
mojibake_map = {
    "àƒ©": "é",
    "àƒ¨": "è",
    "àƒ ": "à",
    "àƒ¢": "â",
    "àƒª": "ê",
    "àƒ®": "î",
    "àƒ´": "ô",
    "àƒ»": "û",
    "àƒ§": "ç",
    "àƒ¯": "ï",
    "àƒ«": "ë",
    "àƒ‰": "É",
    "àƒˆ": "È",
    "àƒ€": "À",
    "àƒÂ¢": "â",
    "àƒÂ": "à",
    "àƒÂª": "ê",
    "Ã©": "é",
    "Ã¨": "è",
    "Ã": "à",
    "Ã¢": "â",
    "Ãª": "ê",
    "Ã®": "î",
    "Ã´": "ô",
    "Ã»": "û",
    "Ã§": "ç",
    "Ã¯": "ï",
    "Ã«": "ë",
    "Ã‰": "É",
    "Ãˆ": "È",
    "Ã€": "À",
    "â€™": "'",
    "â€œ": '"',
    "â€ ": '"',
    "â€“": "-",
    "â€”": "-",
    "Â°": "°",
    "Â": ""
}

# Special case for specific corrupted sequences found in grep
special_map = {
    "rÃ©servÃ©s": "réservés",
    "dÃ©couverte": "découverte",
    "DÃ©couvrez": "Découvrez",
    " validÃ©(s)": " validé(s)",
    "Acadàƒ©mique": "Académique",
    "Wikipàƒ©dia": "Wikipédia",
    "Accàƒ©dez": "Accédez",
    "structuràƒ©s": "structurés",
    "pràƒ©càƒ©dentes": "précédentes",
    "centralisàƒ©e": "centralisée",
    "Sàƒ©curitàƒ©": "Sécurité",
    "ràƒ©union": "réunion",
    "ràƒ©servàƒ©s": "réservés",
    "annàƒ©e": "année",
    "associàƒ©s": "associés",
    "Implàƒ©mentation": "Implémentation",
    "Surveillàƒ©": "Surveillé",
    "dispersàƒ©es": "dispersées",
    "àƒ©parpillàƒ©s": "éparpillés",
    "diffàƒ©rents": "différents",
    "ràƒ©vision": "révision",
    "communautàƒ©": "communauté",
    "àƒ©tudiante": "étudiante",
    "àƒ©tudiants": "étudiants",
    "GràƒÂ¢ce": "Grâce",
    "àƒÂ ": "à",
    "personnalisàƒ©e": "personnalisée",
    "àƒ©tendre": "étendre",
    "repràƒ©sentations": "représentations",
    "Centralisàƒ©": "Centralisé",
    "rassemblàƒ©s": "rassemblés",
    "àƒÂ  tout moment": "à tout moment",
    "Structuràƒ©": "Structuré",
    "ajoutàƒ©e": "ajoutée",
    "dàƒ©diàƒ©e": "dédiée",
    "ingàƒ©nieurs": "ingénieurs"
}

# Collect all HTML and PHP files
files = glob.glob(os.path.join(base_dir, "**", "*.html"), recursive=True)
files += glob.glob(os.path.join(base_dir, "**", "*.php"), recursive=True)

print(f"Total files found: {len(files)}")

for file_path in files:
    try:
        # Read the file
        with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
            content = f.read()
        
        original_content = content
        
        # Apply special map first
        for k, v in special_map.items():
            content = content.replace(k, v)
            
        # Apply general mojibake map
        for k, v in mojibake_map.items():
            content = content.replace(k, v)
            
        # If content changed, write back
        if content != original_content:
            with open(file_path, "w", encoding="utf-8") as f:
                f.write(content)
            print(f"Fixed encoding in: {file_path}")
            
    except Exception as e:
        print(f"Error processing {file_path}: {e}")

print("Encoding fix complete.")
