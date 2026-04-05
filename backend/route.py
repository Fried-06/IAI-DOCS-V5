import os
import sys
import re
import shutil
import unicodedata


def remove_accents(text):
    """
    Normalize a string: remove accents, replace spaces with underscores,
    and strip any character that is not alphanumeric or underscore/dash.
    Examples:
        'Probabilités' -> 'Probabilites'
        'Semestre 3'   -> 'Semestre3'
        'L2'           -> 'L2'
    """
    # Decompose accented characters into base + combining marks
    nfkd = unicodedata.normalize('NFKD', str(text))
    # Keep only ASCII characters (drops combining marks = accents)
    ascii_only = nfkd.encode('ASCII', 'ignore').decode('ASCII')
    # Remove spaces
    no_spaces = ascii_only.replace(' ', '')
    # Keep only alphanumeric + underscore + dash
    clean = re.sub(r'[^a-zA-Z0-9_\-]', '', no_spaces)
    return clean


def ensure_index_rst(directory, title, children=None):
    """
    Create or update an index.rst in the given directory.
    Only creates the file if it doesn't exist yet.
    """
    rst_path = os.path.join(directory, 'index.rst')
    if os.path.exists(rst_path):
        return  # Don't overwrite manually curated indexes

    underline = '=' * len(title)
    if children:
        toctree_entries = '\n'.join(f'   {c}/index' for c in children)
        content = f"""{title}
{underline}

.. toctree::
   :maxdepth: 2

{toctree_entries}

"""
    else:
        content = f"""{title}
{underline}

.. toctree::
   :maxdepth: 1
   :glob:

   *

"""
    with open(rst_path, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"Created index.rst: {rst_path}")


def main():
    if len(sys.argv) < 7:
        print("Usage: python route.py <clean_md_file> <level> <semester> <subject> <type> <year>")
        sys.exit(1)

    input_file = sys.argv[1]

    # Normalize all path components to be accent-free and space-free
    level    = remove_accents(sys.argv[2])
    semestre = remove_accents(sys.argv[3])
    subject  = remove_accents(sys.argv[4])
    doc_type = sys.argv[5]  # Keep corrige/xxx slash as-is
    year     = sys.argv[6]

    if not os.path.exists(input_file):
        print(f"Error: Clean file not found: {input_file}")
        sys.exit(1)

    # ── Target directory: ../Docs/{level}/{semestre}/{subject}/{type} ──
    backend_dir = os.path.dirname(os.path.abspath(__file__))
    docs_dir    = os.path.join(os.path.dirname(backend_dir), 'Docs')
    target_dir  = os.path.join(docs_dir, level, semestre, subject, doc_type)

    os.makedirs(target_dir, exist_ok=True)

    # Move the MD file into place
    target_file = os.path.join(target_dir, str(year) + '.md')
    shutil.move(input_file, target_file)

    # ── Auto-create missing index.rst files in the hierarchy ──
    # Level index (e.g. L2/index.rst)
    level_dir = os.path.join(docs_dir, level)
    ensure_index_rst(level_dir, level)

    # Semester index (e.g. L2/Semestre3/index.rst)
    sem_dir = os.path.join(level_dir, semestre)
    ensure_index_rst(sem_dir, semestre)

    # Subject index (e.g. L2/Semestre3/Probabilites/index.rst)
    subject_dir = os.path.join(sem_dir, subject)
    ensure_index_rst(subject_dir, subject)

    # Type index (e.g. L2/Semestre3/Probabilites/partiel/index.rst)
    type_dir = target_dir
    ensure_index_rst(type_dir, doc_type)

    print(f"Success: File routed to {target_file}")
    sys.exit(0)


if __name__ == "__main__":
    main()
