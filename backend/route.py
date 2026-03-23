import os
import sys
import re
import shutil

def main():
    if len(sys.argv) < 5:
        print("Usage: python route.py <clean_md_file> <title> <level> <category>")
        sys.exit(1)
        
    input_file = sys.argv[1]
    title = sys.argv[2]
    level = sys.argv[3]
    category = sys.argv[4]
    
    if not os.path.exists(input_file):
        print(f"Error: Clean file not found: {input_file}")
        sys.exit(1)
        
    # Extract year
    year_match = re.search(r'\b(19|20)\d{2}\b', title)
    year = year_match.group(0) if year_match else "Inconnu"
    
    # Extract subject
    subject = re.sub(r'\b(19|20)\d{2}\b', '', title).strip()
    if not subject:
        subject = "Sujet_Inconnu"
        
    # Extract semestre
    semestre_match = re.search(r'(?i)\b(s1|s2|semestre\s*[12])\b', title)
    if semestre_match:
        val = semestre_match.group(0).lower()
        if '1' in val:
            semestre = 'semestre1'
        else:
            semestre = 'semestre2'
        # Remove semestre from subject name for a cleaner folder
        subject = re.sub(r'(?i)\b(s1|s2|semestre\s*[12])\b', '', subject).strip()
    else:
        semestre = 'semestre_inconnu'
        
    # Sanitize subject
    subject = re.sub(r'[^a-zA-Z0-9_\- ]', '', subject).strip()
    subject = subject.replace(' ', '_')
    if not subject:
        subject = "Sujet_Inconnu"
        
    # Type mapping
    type_map = {
        'exam': 'examens',
        'course': 'cours',
        'assignment': 'td_tp'
    }
    doc_type = type_map.get(category.lower(), category.lower())
    
    # Target directory: ../Docs/build/{level}/{semestre}/{subject}/{type}
    backend_dir = os.path.dirname(os.path.abspath(__file__))
    base_dir = os.path.dirname(backend_dir)
    target_dir = os.path.join(base_dir, 'Docs', 'build', level, semestre, subject, doc_type)
    
    os.makedirs(target_dir, exist_ok=True)
    
    # Determine unique filename to avoid overwrites
    base_filename = year
    ext = '.md'
    target_file = os.path.join(target_dir, base_filename + ext)
    
    counter = 1
    while os.path.exists(target_file):
        target_file = os.path.join(target_dir, f"{base_filename}_{counter}{ext}")
        counter += 1
        
    # Move file
    shutil.move(input_file, target_file)
    
    print(f"Success: File routed to {target_file}")
    sys.exit(0)

if __name__ == "__main__":
    main()
