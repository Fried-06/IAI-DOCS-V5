import re

# Read the file
with open('generate_structure_v4.py', 'r', encoding='utf-8') as f:
    content = f.read()

# I will replace the SQL part of the template to use the new db_name
# In make_subject_page
old_execute = """    $stmt->execute([
        ':level'    => '{db_level}',
        ':semester' => '{db_sem}',
        ':subject'  => '{mat_nom_escaped_php}'
    ]);"""

new_execute = """    $stmt->execute([
        ':level'    => '{db_level}',
        ':semester' => '{db_sem}',
        ':subject'  => '{mat_db_name}'
    ]);"""

content = content.replace(old_execute, new_execute)

# Update the make_subject_page signature
old_sig = "def make_subject_page(level_key, sem_key, mat_key, mat_nom, level_titre, sem_titre, beta_check_path, css_path, js_path):"
new_sig = "def make_subject_page(level_key, sem_key, mat_key, mat_nom, mat_db_name, level_titre, sem_titre, beta_check_path, css_path, js_path):"
content = content.replace(old_sig, new_sig)

# Now we need to update the generator loop
old_loop = """            for mat_key, mat_nom in sem_data["matieres"].items():
                mat_path = os.path.join(sem_path, mat_key + ".php")
                content = make_subject_page(level_key, sem_key, mat_key, mat_nom,
                                            level_data["db_name"], sem_data["db_name"],
                                            beta_check_path, css_path, js_path)"""

new_loop = """            for mat_key, mat_info in sem_data["matieres"].items():
                if isinstance(mat_info, dict):
                    mat_nom = mat_info["titre"]
                    mat_db_name = mat_info["db_name"]
                else:
                    mat_nom = mat_info
                    mat_db_name = mat_nom # fallback
                mat_path = os.path.join(sem_path, mat_key + ".php")
                content = make_subject_page(level_key, sem_key, mat_key, mat_nom, mat_db_name,
                                            level_data["db_name"], sem_data["db_name"],
                                            beta_check_path, css_path, js_path)"""

content = content.replace(old_loop, new_loop)

with open('generate_structure_v4.py', 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated generator loop and SQL parameters.")
