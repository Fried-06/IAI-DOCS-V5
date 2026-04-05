import os
import glob
import re

files = glob.glob('iai-resources/*.html') + glob.glob('iai-resources/*.php')
script_tag = '<script src="js/theme.js"></script>'

for f in files:
    with open(f, 'r', encoding='utf-8', errors='ignore') as file:
        content = file.read()
        
    updated = False
    if '<head>' in content and script_tag not in content:
        # inject just before </head>
        content = content.replace('</head>', f'    {script_tag}\n</head>')
        updated = True
        
    if updated:
        with open(f, 'w', encoding='utf-8') as file:
            file.write(content)
            
print("Injected theme.js into all root html/php files.")
