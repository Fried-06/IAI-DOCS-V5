import os
import glob
import re

base_dir = r"C:\Users\MSI\Music\IAI_MENTORIA\iai-resources"

# 1. Update CSS Dark Mode to true black
css_path = os.path.join(base_dir, "css", "style.css")
with open(css_path, "r", encoding="utf-8") as f:
    css_content = f.read()

# Replace variables in body.dark
css_content = css_content.replace("--bg-body: #111827;", "--bg-body: #000000;")
css_content = css_content.replace("--bg-card: #1f2937;", "--bg-card: #0a0a0a;")
css_content = css_content.replace("--secondary: #374151;", "--secondary: #111111;")
css_content = css_content.replace("--border: #374151;", "--border: #222222;")
css_content = css_content.replace("background-color: #1f2937;", "background-color: #050505;")

# Ensure cards in dark mode are distinctly black
if "body.dark .exam-card, body.dark .level-card" in css_content:
    css_content = css_content.replace(
        "box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);",
        "box-shadow: 0 4px 6px -1px rgba(255, 255, 255, 0.05); border: 1px solid #222;"
    )

with open(css_path, "w", encoding="utf-8") as f:
    f.write(css_content)


# 2. Fix encoding mojibake in all HTML files
html_files = glob.glob(os.path.join(base_dir, "**", "*.html"), recursive=True)

mojibake_map = {
    "Ã©": "é",
    "Ã¨": "è",
    "Ã ": "à",
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
    "Â": "" # Often leaves a stray Â before space
}

for file_path in html_files:
    try:
        with open(file_path, "rb") as f:
            raw = f.read()
        
        try:
            # First, assume it was incorrectly encoded as UTF-8 when it was actually ISO-8859-1
            # If we decode as utf-8 and then try to encode to latin-1, it might expose the original utf-8 bytes
            text = raw.decode('utf-8')
            # Check if there is mojibake by looking for Ã
            if "Ã" in text or "â€™" in text:
                # Manual replacement is safer than full round-trip which might break valid chars
                for k, v in mojibake_map.items():
                    text = text.replace(k, v)
                
                # Write back
                with open(file_path, "w", encoding="utf-8") as f:
                    f.write(text)
        except UnicodeDecodeError:
            # If it's pure latin-1, decode it as such and rewrite to utf-8
            text = raw.decode('latin-1')
            with open(file_path, "w", encoding="utf-8") as f:
                f.write(text)
                
    except Exception as e:
        print(f"Error processing {file_path}: {e}")

print("Fixed CSS and encoding.")
