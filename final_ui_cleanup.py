import os

# Current workspace directory
base_dir = r"c:\Users\MSI\Music\IAI_MENTORIA5 - Copie\iai-resources"

replacements = {
    "profile.php": {
        "âš¡": "&#9889;",
        "âš": "&#9888;"
    },
    "contributors.php": {
        "à°Ÿ‘¥": "&#128101;",
        "ðŸ †": "&#127942;",
        "ðŸ¥ˆ": "&#129352;",
        "ðŸ¥‰": "&#129353;"
    },
    "exams.php": {
        "â€”": "-"
    }
}

for file_name, mapping in replacements.items():
    file_path = os.path.join(base_dir, file_name)
    if not os.path.exists(file_path):
        continue
    try:
        with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
            content = f.read()
        
        original_content = content
        for k, v in mapping.items():
            content = content.replace(k, v)
        
        if content != original_content:
            with open(file_path, "w", encoding="utf-8") as f:
                f.write(content)
            print(f"Fixed final icons in: {file_name}")
    except Exception as e:
        print(f"Error processing {file_name}: {e}")

print("Final cleanup complete.")
