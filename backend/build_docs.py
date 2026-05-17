import os
import sys
import shutil
import subprocess
import urllib.request
import json
import re
import unicodedata

def get_supabase_credentials():
    backend_dir = os.path.dirname(os.path.abspath(__file__))
    config_path = os.path.join(backend_dir, 'config.php')
    url, key = None, None
    if os.path.exists(config_path):
        with open(config_path, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
            url_match = re.search(r"define\s*\(\s*['\"]SUPABASE_STORAGE_URL['\"]\s*,\s*['\"](.*?)['\"]\s*\)", content)
            key_match = re.search(r"define\s*\(\s*['\"]SUPABASE_KEY['\"]\s*,\s*['\"](.*?)['\"]\s*\)", content)
            if url_match:
                url = url_match.group(1)
            if key_match:
                key = key_match.group(1)
    return url, key

def ensure_extensions(conf_path):
    required_exts = ['myst_parser', 'sphinx.ext.mathjax', 'sphinx_togglebutton']
    with open(conf_path, 'r', encoding='utf-8') as f:
        content = f.read()
        
    missing = []
    for ext in required_exts:
        if repr(ext) not in content and f'"{ext}"' not in content and f"'{ext}'" not in content:
            missing.append(ext)
            
    if missing:
        append_str = "\n# Auto-appended for automation\n"
        append_str += f"if 'extensions' not in locals():\n    extensions = []\n"
        append_str += f"extensions.extend({repr(missing)})\n"
        
        with open(conf_path, 'a', encoding='utf-8') as f:
            f.write(append_str)
        print(f"Appended missing extensions: {missing}")

def slugify(text):
    # Normalize accents
    text = unicodedata.normalize('NFD', text).encode('ascii', 'ignore').decode('utf-8')
    text = text.replace(' ', '')
    text = re.sub(r'[^a-zA-Z0-9_\-]', '', text)
    return text

def compile_single_document(doc_id, supabase_url, supabase_key, backend_dir, docs_root):
    # Fetch details via REST
    url = f"{supabase_url}/rest/v1/documents?select=id,title,filename,pdf_url,subject_id(name,semester_id(name,level_id(name))),type_id(name),year_id(year)&id=eq.{doc_id}"
    headers = {
        'apikey': supabase_key,
        'Authorization': f'Bearer {supabase_key}',
        'Content-Type': 'application/json'
    }
    
    req = urllib.request.Request(url, headers=headers)
    try:
        with urllib.request.urlopen(req) as response:
            docs = json.loads(response.read().decode('utf-8'))
            if not docs:
                print(f"Error: Document with ID {doc_id} not found.")
                return False
            doc = docs[0]
    except Exception as e:
        print(f"Error fetching metadata for doc {doc_id}: {e}")
        return False

    # Extract meta fields
    try:
        level_name = doc['subject_id']['semester_id']['level_id']['name']
        semester_name = doc['subject_id']['semester_id']['name']
        subject_name = doc['subject_id']['name']
        type_name = doc['type_id']['name']
        year = doc['year_id']['year']
    except Exception as e:
        print(f"Metadata format error for doc {doc_id}: {e}")
        return False

    level_slug = slugify(level_name.replace('Licence ', 'L').strip())
    semester_slug = slugify(semester_name.strip())
    subject_slug = slugify(subject_name.strip())
    type_slug = type_name
    if type_slug.startswith('corrige_'):
        type_slug = 'corrige/' + type_slug

    relative_path = f"{level_slug}/{semester_slug}/{subject_slug}/{type_slug}/{year}"
    
    # Check if the source markdown file exists physically in Docs/
    src_file_docs = os.path.join(docs_root, f"{relative_path}.md")
    if not os.path.exists(src_file_docs):
        print(f"Error: Source file {src_file_docs} does not exist.")
        return False

    # Setup temp build dir
    temp_dir = os.path.join(backend_dir, f"temp_build_{doc_id}")
    if os.path.exists(temp_dir):
        shutil.rmtree(temp_dir)
    os.makedirs(temp_dir)

    try:
        # Create nested source dir inside sandbox
        src_dir_rel = os.path.dirname(relative_path)
        src_dir_abs = os.path.join(temp_dir, src_dir_rel)
        os.makedirs(src_dir_abs, exist_ok=True)

        # Copy markdown and conf.py
        dest_file_temp = os.path.join(temp_dir, f"{relative_path}.md")
        shutil.copy2(src_file_docs, dest_file_temp)
        shutil.copy2(os.path.join(docs_root, 'conf.py'), os.path.join(temp_dir, 'conf.py'))

        # Write index.rst for the single-document project
        index_content = f"""
Temp Build
==========

.. toctree::
   :maxdepth: 2
   
   {relative_path}
"""
        with open(os.path.join(temp_dir, 'index.rst'), 'w', encoding='utf-8') as f:
            f.write(index_content)

        # Run sphinx-build in the sandbox
        print(f"Running sphinx-build for document {doc_id}...")
        subprocess.run(
            [sys.executable, '-m', 'sphinx', '-b', 'html', '.', '_build/html'],
            cwd=temp_dir,
            check=True,
            stdout=subprocess.DEVNULL,
            stderr=subprocess.DEVNULL
        )

        compiled_html_path = os.path.join(temp_dir, '_build', 'html', f"{relative_path}.html")
        if not os.path.exists(compiled_html_path):
            print(f"Error: Compiled HTML not found at {compiled_html_path}")
            return False

        # Read compiled HTML
        with open(compiled_html_path, 'rb') as f:
            file_data = f.read()

        # Upload compiled HTML to Supabase Storage
        dest_storage_path = f"{relative_path}.html"
        storage_url = f"{supabase_url}/storage/v1/object/subjects/{dest_storage_path}"
        
        req_upload = urllib.request.Request(
            storage_url,
            data=file_data,
            headers={
                'apikey': supabase_key,
                'Authorization': f'Bearer {supabase_key}',
                'Content-Type': 'text/html',
                'x-upsert': 'true'
            },
            method='POST'
        )

        print(f"Uploading {dest_storage_path} to Supabase bucket 'subjects'...")
        try:
            with urllib.request.urlopen(req_upload) as response:
                print(f"Document {doc_id} successfully compiled and uploaded!")
        except Exception as upload_err:
            # Fallback to PUT if POST fails due to upsert logic
            req_upload = urllib.request.Request(
                storage_url,
                data=file_data,
                headers={
                    'apikey': supabase_key,
                    'Authorization': f'Bearer {supabase_key}',
                    'Content-Type': 'text/html',
                    'x-upsert': 'true'
                },
                method='PUT'
            )
            with urllib.request.urlopen(req_upload) as response:
                print(f"Document {doc_id} successfully compiled and uploaded (via PUT)!")

        return True

    except Exception as e:
        print(f"Exception during compile for doc {doc_id}: {e}")
        return False
    finally:
        if os.path.exists(temp_dir):
            shutil.rmtree(temp_dir)

def main():
    backend_dir = os.path.dirname(os.path.abspath(__file__))
    docs_root = os.path.join(os.path.dirname(backend_dir), 'Docs')
    
    if not os.path.exists(docs_root):
        print(f"Error: Docs directory not found at {docs_root}")
        sys.exit(1)
        
    conf_path = os.path.join(docs_root, 'conf.py')
    if os.path.exists(conf_path):
        ensure_extensions(conf_path)

    # Parse arguments
    args = [arg for arg in sys.argv[1:] if arg.isdigit()]
    if args:
        # Fast single compilation mode
        supabase_url, supabase_key = get_supabase_credentials()
        if not supabase_url or not supabase_key:
            print("Error: Could not retrieve Supabase credentials from config.php")
            sys.exit(1)

        success_count = 0
        for doc_id in args:
            print(f"\n--- Starting fast compilation for Document ID: {doc_id} ---")
            if compile_single_document(int(doc_id), supabase_url, supabase_key, backend_dir, docs_root):
                success_count += 1
            else:
                print(f"Compilation failed for Document ID: {doc_id}")

        if success_count == len(args):
            print("\nSphinx build successful.")
            sys.exit(0)
        else:
            print(f"\nSphinx build completed with errors: {success_count}/{len(args)} succeeded.")
            sys.exit(1)
    else:
        # Fallback to full compile (legacy mode)
        print("No document IDs provided. Falling back to full Sphinx build...")
        out_dir = os.path.join(docs_root, '_build', 'html')
        try:
            subprocess.run(
                [sys.executable, '-m', 'sphinx', '-j', 'auto', '-b', 'html', '.', '_build/html'],
                cwd=docs_root,
                check=True
            )
            print(f"Sphinx build successful. Output in {out_dir}")
            sys.exit(0)
        except Exception as e:
            print(f"Sphinx full build failed: {e}")
            sys.exit(1)

if __name__ == "__main__":
    main()
