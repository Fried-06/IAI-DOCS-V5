import os
import glob

files = glob.glob('**/*.html', recursive=True) + glob.glob('**/*.php', recursive=True)

for file in files:
    try:
        with open(file, 'r', encoding='utf-8') as f:
            content = f.read()
            
        if 'src="js/main.js"' in content or 'src="../js/main.js"' in content or 'src="/js/main.js?v=2"' in content or 'src="/js/main.js"' in content:
            # We already have various formats of main.js inclusion. Let's make sure it's correct.
            # Replace all variations with just js/main.js?v=3 (or /js/main.js?v=3 if it started with /)
            import re
            # Replace src="/js/main.js..."
            new_content = re.sub(r'src="/js/main\.js(\?v=\d+)?"', 'src="/js/main.js?v=3"', content)
            # Replace src="js/main.js..."
            new_content = re.sub(r'src="js/main\.js(\?v=\d+)?"', 'src="js/main.js?v=3"', new_content)
            # Replace src="../js/main.js..."
            new_content = re.sub(r'src="\.\./js/main\.js(\?v=\d+)?"', 'src="../js/main.js?v=3"', new_content)
            
            if new_content != content:
                with open(file, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                print(f"Updated {file}")
    except Exception as e:
        print(f"Failed to update {file}: {e}")
