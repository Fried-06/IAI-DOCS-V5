import os
import sys
import shutil
import subprocess

def ensure_extensions(conf_path):
    required_exts = ['myst_parser', 'sphinx.ext.mathjax', 'sphinx_togglebutton']
    
    with open(conf_path, 'r', encoding='utf-8') as f:
        content = f.read()
        
    missing = []
    # Simple heuristic to determine if it's there
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
    else:
        print("Required Sphinx extensions already present in conf.py.")

def main():
    backend_dir = os.path.dirname(os.path.abspath(__file__))
    docs_dir = os.path.join(os.path.dirname(backend_dir), 'Docs')
    
    if not os.path.exists(docs_dir):
        print(f"Error: Docs directory not found at {docs_dir}")
        sys.exit(1)
        
    conf_path = os.path.join(docs_dir, 'conf.py')
    if os.path.exists(conf_path):
        ensure_extensions(conf_path)
    else:
        print("Warning: conf.py not found. Sphinx build may fail.")
        
    # Ensure master document is in build/
    build_dir = os.path.join(docs_dir, 'build')
    os.makedirs(build_dir, exist_ok=True)
    
    source_index_rst = os.path.join(docs_dir, 'index.rst')
    build_index_rst = os.path.join(build_dir, 'index.rst')
    build_index_md = os.path.join(build_dir, 'index.md')
    
    if not os.path.exists(build_index_rst) and not os.path.exists(build_index_md):
        if os.path.exists(source_index_rst):
            shutil.copy(source_index_rst, build_index_rst)
            print("Copied index.rst to build/")
        else:
            print("Warning: No index.rst found in Docs/ to copy.")

    out_dir = os.path.join(docs_dir, '_build', 'html')
    
    # Run sphinx-build
    print("Running sphinx-build...")
    try:
        # Cross platform sphinx invocation
        subprocess.run(
            [sys.executable, '-m', 'sphinx', '-b', 'html', '-c', '.', 'build', '_build/html'],
            cwd=docs_dir,
            check=True
        )
        print(f"Sphinx build successful. Output in {out_dir}")
        sys.exit(0)
    except subprocess.CalledProcessError as e:
        print(f"Sphinx build failed: {e}")
        sys.exit(1)
    except Exception as e:
        print(f"Error executing Sphinx: {e}")
        sys.exit(1)

if __name__ == "__main__":
    main()
