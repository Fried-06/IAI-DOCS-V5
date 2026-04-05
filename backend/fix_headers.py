import os
import glob
import re

def fix_markdown_headers(docs_dir):
    md_files = glob.glob(os.path.join(docs_dir, '**/*.md'), recursive=True)
    updated = 0
    
    for filepath in md_files:
        # Only process files that are in the structured tree like L2/Semestre3/Probabilites/partiel/2026.md
        parts = filepath.replace('\\', '/').split('/')
        if 'Docs' not in parts: continue
        
        idx = parts.index('Docs')
        if len(parts) - idx < 5: continue
        
        subject_name = parts[idx+3]
        
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
            
        lines = content.split('\n')
        filtered = []
        for line in lines:
            if line.startswith('# ') or line.startswith('## '):
                if "exercice" not in line.lower() and "question" not in line.lower():
                    continue
            filtered.append(line)
            
        new_content = f"# {subject_name}\n\n" + '\n'.join(filtered).strip()
        
        if content != new_content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(new_content)
            updated += 1
            
    print(f"Updated headers in {updated} markdown files.")

if __name__ == '__main__':
    base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    fix_markdown_headers(os.path.join(base_dir, 'Docs'))
