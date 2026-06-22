import os
import glob

# Find all PHP files in pages/ and subjects/
php_files = glob.glob('pages/**/*.php', recursive=True) + glob.glob('subjects/**/*.php', recursive=True)

print(f"Found {len(php_files)} PHP files to patch")

for fpath in php_files:
    with open(fpath, 'r', encoding='utf-8', errors='replace') as f:
        content = f.read()

    # Skip if already has beta_check or session_start at top
    if 'beta_check.php' in content:
        print(f"SKIP (already has beta_check): {fpath}")
        continue

    # Determine the relative path depth from the file to iai-resources/ root
    # pages/L1/semestre1.php -> depth = 2 -> ../../backend/beta_check.php
    # subjects/L1/semestre1/algorithmique.php -> depth = 3 -> ../../../backend/beta_check.php
    parts = fpath.replace('\\', '/').split('/')
    depth = len(parts) - 1  # number of directory levels

    relative_prefix = '../' * depth
    beta_path = relative_prefix + 'backend/beta_check.php'

    php_header = f"""<?php
session_start();
require_once __DIR__ . '/{beta_path}';
?>
"""
    # Only prepend if the file doesn't already start with <?php
    if content.lstrip().startswith('<?php'):
        # Insert beta_check after session_start if present, otherwise after first <?php
        if 'session_start();' in content:
            content = content.replace(
                'session_start();',
                f"session_start();\nrequire_once __DIR__ . '/{beta_path}';",
                1
            )
        else:
            content = content.replace('<?php', f"<?php\nsession_start();\nrequire_once __DIR__ . '/{beta_path}';", 1)
    else:
        # Pure HTML or HTML starting page, prepend PHP block
        content = php_header + content

    with open(fpath, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"PATCHED: {fpath}")

print("\nDone patching all pages/ and subjects/ files")
