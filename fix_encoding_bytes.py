import os
import glob

# Current workspace directory
base_dir = r"c:\Users\MSI\Music\IAI_MENTORIA5 - Copie\iai-resources"

# Define byte replacements
byte_map = {
    # 'é' replacements
    bytes.fromhex('c3 83 c2 a9'): 'é'.encode('utf-8'),      # Ã©
    bytes.fromhex('c3 a0 c2 83 c2 a9'): 'é'.encode('utf-8'), # àƒ©
    bytes.fromhex('c3 a0 c2 a2 c2 a9'): 'é'.encode('utf-8'), # Another variation
    
    # 'è' replacements
    bytes.fromhex('c3 83 c2 a8'): 'è'.encode('utf-8'),      # Ã¨
    bytes.fromhex('c3 a0 c2 83 c2 a8'): 'è'.encode('utf-8'), # àƒè
    
    # 'à' replacements
    bytes.fromhex('c3 a0 c2 83 c2 a0'): 'à'.encode('utf-8'), # àƒÂ 
    bytes.fromhex('c3 83 20'): 'à '.encode('utf-8'),         # Ã  (often happens with space)
    bytes.fromhex('c3 a0 c2 a2 c2 a0'): 'à'.encode('utf-8'),
    
    # 'â' replacements
    bytes.fromhex('c3 83 c2 a2'): 'â'.encode('utf-8'),      # Ã¢
    bytes.fromhex('c3 a0 c2 83 c2 a2'): 'â'.encode('utf-8'), # àƒ¢
    
    # 'ê' replacements
    bytes.fromhex('c3 83 c2 aa'): 'ê'.encode('utf-8'),      # Ãª
    bytes.fromhex('c3 a0 c2 83 c2 aa'): 'ê'.encode('utf-8'), # àƒª
    
    # French double encoding often leads to these sequences
    bytes.fromhex('c3 a0 c2 a2 c2 a0'): 'à'.encode('utf-8'),
    bytes.fromhex('c3 a0 c2 a2 c2 a2'): 'â'.encode('utf-8'),
    bytes.fromhex('c3 a0 c2 a2 c2 a9'): 'é'.encode('utf-8'),
    bytes.fromhex('c3 a0 c2 a2 c2 a8'): 'è'.encode('utf-8'),

    # Emoji replacements (Rank icons)
    'ðŸ †'.encode('utf-8', 'ignore'): '&#127942;'.encode('utf-8'),
    'ðŸ¥ˆ'.encode('utf-8', 'ignore'): '&#129352;'.encode('utf-8'),
    'ðŸ¥‰'.encode('utf-8', 'ignore'): '&#129353;'.encode('utf-8'),
    
    # Special characters
    'â€”'.encode('utf-8', 'ignore'): '—'.encode('utf-8'),
    'â¢'.encode('utf-8', 'ignore'): '•'.encode('utf-8'),
    'â€™'.encode('utf-8', 'ignore'): "'".encode('utf-8'),
}

# Add more variations observed in grep
mojibake_common = {
    'ràƒ©servàƒ©s': 'réservés',
    'Acadàƒ©mique': 'Académique',
    'Wikipàƒ©dia': 'Wikipédia',
    'Accàƒ©dez': 'Accédez',
    'structuràƒ©s': 'structurés',
    'pràƒ©càƒ©dentes': 'précédentes',
    'centralisàƒ©e': 'centralisée',
    'Sàƒ©curitàƒ©': 'Sécurité',
    'ràƒ©union': 'réunion',
    'annàƒ©e': 'année',
    'associàƒ©s': 'associés',
    'Implàƒ©mentation': 'Implémentation',
    'Surveillàƒ©': 'Surveillé',
    'dispersàƒ©es': 'dispersées',
    'àƒ©parpillàƒ©s': 'éparpillés',
    'diffàƒ©rents': 'différents',
    'ràƒ©vision': 'révision',
    'communautàƒ©': 'communauté',
    'àƒ©tudiante': 'étudiante',
    'àƒ©tudiants': 'étudiants',
    'GràƒÂ¢ce': 'Grâce',
    'àƒÂ ': 'à',
    'personnalisàƒ©e': 'personnalisée',
    'àƒ©tendre': 'étendre',
    'repràƒ©sentations': 'représentations',
    'Centralisàƒ©': 'Centralisé',
    'rassemblàƒ©s': 'rassemblés',
    'Structuràƒ©': 'Structuré',
    'ajoutàƒ©e': 'ajoutée',
    'dàƒ©diàƒ©e': 'dédiée',
    'ingàƒ©nieurs': 'ingénieurs',
    'supàƒ©rieurs': 'supérieurs',
    'rÃ©servÃ©s': 'réservés',
    'validÃ©(s)': 'validé(s)',
    'Ã©tudiants': 'étudiants',
    'Ã©': 'é'
}

for k, v in mojibake_common.items():
    byte_map[k.encode('utf-8', 'ignore')] = v.encode('utf-8')

# Files to process
files = glob.glob(os.path.join(base_dir, "**", "*.html"), recursive=True)
files += glob.glob(os.path.join(base_dir, "**", "*.php"), recursive=True)

for file_path in files:
    if "node_modules" in file_path or "_build" in file_path:
        continue
    try:
        with open(file_path, "rb") as f:
            data = f.read()
        
        new_data = data
        for k, v in byte_map.items():
            new_data = new_data.replace(k, v)
        
        if new_data != data:
            with open(file_path, "wb") as f:
                f.write(new_data)
            print(f"Fixed bytes in: {file_path}")
    except Exception as e:
        print(f"Error processing {file_path}: {e}")

print("Byte-level fix complete.")
