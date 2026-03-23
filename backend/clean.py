import os
import sys
import re

def clean_markdown(text, title_str, category_str):
    """
    Cleans raw markdown text and formats it with a standard header
    while preserving code blocks and LaTeX expressions.
    """
    # Try to extract year from title (4 digits)
    year_match = re.search(r'\b(19|20)\d{2}\b', title_str)
    year = year_match.group(0) if year_match else "Année inconnue"
    
    # Subject is the title without the year (basic approximation)
    subject = re.sub(r'\b(19|20)\d{2}\b', '', title_str).strip()
    if not subject:
        subject = "Sujet Inconnu"
        
    type_map = {
        'exam': 'Examen',
        'course': 'Cours',
        'assignment': 'Devoir / TP'
    }
    doc_type = type_map.get(category_str.lower(), category_str.capitalize() if category_str else 'Document')
    
    # 1. Protect Code Blocks and LaTeX Blocks
    # We will temporarily substitute them with unique tokens
    protected_blocks: dict[str, str] = {}
    
    def protect_func(match):
        token = f"###PROTECT_{len(protected_blocks)}###"
        protected_blocks[token] = match.group(0)
        return token

    # Protect ``` code blocks
    text = re.sub(r'```.*?```', protect_func, text, flags=re.DOTALL)
    # Protect $$ math $$
    text = re.sub(r'\$\$.*?\$\$', protect_func, text, flags=re.DOTALL)
    # Protect inline $ math $
    text = re.sub(r'\$.*?\$', protect_func, text)

    # 2. Fix OCR / Formatting errors on plain text
    # Standardize Exercice/Question/Correction headers
    text = re.sub(r'(?i)^(exerci[sc]e\s*\d+\s*:?)', r'### \1', text, flags=re.MULTILINE)
    text = re.sub(r'(?i)^(question\s*\d+\s*:?)', r'#### \1', text, flags=re.MULTILINE)
    text = re.sub(r'(?i)^(correction\s*:?)', r'## Correction', text, flags=re.MULTILINE)

    # Multiple newlines to double newline
    text = re.sub(r'\n{3,}', '\n\n', text)
    
    # 3. Restore Protected Blocks
    for token, original_content in protected_blocks.items():
        text = text.replace(token, original_content)
        
    # Build Top Header
    header = f"# {subject} — {doc_type} — {year}\n\n## Sujet\n\n"
    
    # Remove any existing `# ` at the very beginning to avoid duplicate top headers
    text = re.sub(r'^#\s+.*?\n', '', text, count=1).strip()
    
    return header + text

def main():
    if len(sys.argv) < 2:
        print("Usage: python clean.py <input_md_file> [title] [category]")
        sys.exit(1)
        
    input_file = sys.argv[1]
    title = sys.argv[2] if len(sys.argv) > 2 else os.path.basename(input_file).replace('.md', '')
    category = sys.argv[3] if len(sys.argv) > 3 else "Document"
    
    if not os.path.exists(input_file):
        print(f"File not found: {input_file}")
        sys.exit(1)

    with open(input_file, 'r', encoding='utf-8') as f:
        raw_text = f.read()
        
    cleaned_text = clean_markdown(raw_text, title, category)
    
    backend_dir = os.path.dirname(os.path.abspath(__file__))
    cleaned_dir = os.path.join(os.path.dirname(backend_dir), 'cleaned')
    os.makedirs(cleaned_dir, exist_ok=True)
    
    base_name = os.path.basename(input_file)
    output_file = os.path.join(cleaned_dir, base_name)
    
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write(cleaned_text)
        
    print(f"Success: Cleaned document saved to {output_file}")
    sys.exit(0)

if __name__ == "__main__":
    main()
