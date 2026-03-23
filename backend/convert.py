import os
import sys
import subprocess
try:
    import pymupdf as fitz  # type: ignore # Modern way
except ImportError:
    import fitz  # type: ignore # Legacy way

def convert_docx_to_md(input_path, output_path):
    print(f"Converting DOCX parsing: {input_path}")
    try:
        subprocess.run(
            ['pandoc', '-f', 'docx', '-t', 'markdown', input_path, '-o', output_path],
            check=True
        )
        print("DOCX converted successfully.")
        return True
    except subprocess.CalledProcessError as e:
        print(f"Pandoc error: {e}")
        return False
    except Exception as e:
        print(f"Error converting DOCX: {e}")
        return False

def convert_pdf_to_md(input_path, output_path):
    print(f"Converting PDF parsing: {input_path}")
    try:
        doc = fitz.open(input_path)
        text_content = []
        for page in doc:
            text_content.append(page.get_text("text"))
        
        with open(output_path, 'w', encoding='utf-8') as f:
            f.write("\n\n---\n\n".join(text_content))
            
        print("PDF converted successfully.")
        return True
    except Exception as e:
        print(f"Error converting PDF: {e}")
        return False

def main():
    if len(sys.argv) < 2:
        print("Usage: python convert.py <input_file>")
        sys.exit(1)
        
    input_file = sys.argv[1]
    
    if not os.path.exists(input_file):
        print(f"File not found: {input_file}")
        sys.exit(1)
        
    base_name = os.path.basename(input_file)
    name_without_ext, ext = os.path.splitext(base_name)
    ext = ext.lower()
    
    # Ensure processed directory exists
    backend_dir = os.path.dirname(os.path.abspath(__file__))
    processed_dir = os.path.join(os.path.dirname(backend_dir), 'processed')
    os.makedirs(processed_dir, exist_ok=True)
    
    output_file = os.path.join(processed_dir, name_without_ext + '.md')
    
    success = False
    if ext == '.docx':
        success = convert_docx_to_md(input_file, output_file)
    elif ext == '.pdf':
        success = convert_pdf_to_md(input_file, output_file)
    else:
        print(f"Unsupported extension: {ext}")
        sys.exit(1)
        
    if success:
        print(f"Success: output saved to {output_file}")
        sys.exit(0)
    else:
        sys.exit(1)

if __name__ == "__main__":
    main()
