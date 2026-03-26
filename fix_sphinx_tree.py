import os

docs_root = r"c:\Users\MSI\Music\IAI_MENTORIA5 - Copie\iai-resources\Docs"

def fix_subject_index(subject_dir):
    index_path = os.path.join(subject_dir, "index.rst")
    if not os.path.exists(index_path):
        return

    with open(index_path, 'r', encoding='utf-8') as f:
        lines = f.readlines()

    new_lines = []
    in_corrige = False
    for line in lines:
        if "**Corrige**" in line or "**Corrigé**" in line:
            in_corrige = True
            new_lines.append(line)
            continue
        
        if in_corrige and "<corrige/" in line:
            # Check if it's a partiel or devoir based on the card logic we want
            # This is a bit tricky, but since we restructured, let's just make it a toctree or direct links
            # Actually, let's just update the links to point to the nested ones if they exist
            if "202" in line: # matches year links
                # For now, let's just point to corrige_partiel as a default or try to be smart
                # But better: add a toctree
                pass
        
        new_lines.append(line)

    # Actually, a better approach is to create index.rst in subfolders and use toctree
    
    corrige_dir = os.path.join(subject_dir, "corrige")
    if os.path.exists(corrige_dir):
        # Create index.rst in corrige/
        c_index = os.path.join(corrige_dir, "index.rst")
        with open(c_index, 'w', encoding='utf-8') as f:
            f.write("Corrigés\n========\n\n.. toctree::\n   :maxdepth: 2\n\n")
            for sub in ["corrige_partiel", "corrige_devoir", "corrige_exercice"]:
                if os.path.exists(os.path.join(corrige_dir, sub)):
                    # Create index.rst in subfolder
                    s_index = os.path.join(corrige_dir, sub, "index.rst")
                    with open(s_index, 'w', encoding='utf-8') as sf:
                        title = sub.replace('_', ' ').capitalize()
                        sf.write(f"{title}\n{'='*len(title)}\n\n.. toctree::\n   :maxdepth: 1\n   :glob:\n\n   *\n")
                    f.write(f"   {sub}/index\n")
        
        # Now update the subject index to include the corrige/index
        # Find where the old Corrige links are and replace with pointing to index
        
        final_lines = []
        skip = False
        for line in new_lines:
            if "**Corrige**" in line or "**Corrigé**" in line:
                final_lines.append(line)
                final_lines.append("\n- `Accéder aux corrigés <corrige/index.html>`_\n")
                skip = True
                continue
            if skip and ("<corrige/" in line or line.strip() == "-"):
                continue
            if skip and line.strip() == "":
                # we hit the end of the section usually
                # but let's be safe and just wait for the next section
                pass
            if line.startswith("**") and skip:
                skip = False
            
            if not skip:
                final_lines.append(line)
        
        with open(index_path, 'w', encoding='utf-8') as f:
            f.writelines(final_lines)

for root, dirs, files in os.walk(docs_root):
    if "index.rst" in files and root != docs_root:
        # Check if it's a subject dir (Level/Semester/Subject)
        parts = os.path.relpath(root, docs_root).split(os.sep)
        if len(parts) == 3:
            print(f"Fixing {root}...")
            fix_subject_index(root)
