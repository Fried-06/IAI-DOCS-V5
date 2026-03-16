import os
import glob
import re
import shutil

base_dir = r"C:\Users\MSI\Music\IAI_MENTORIA\iai-resources"
source_assets = r"C:\Users\MSI\Music\logo_and_loading"
dest_assets = os.path.join(base_dir, "assets")

# 1. Copy Assets
if not os.path.exists(dest_assets):
    os.makedirs(dest_assets)

for item in os.listdir(source_assets):
    s = os.path.join(source_assets, item)
    d = os.path.join(dest_assets, item)
    if os.path.isfile(s):
        shutil.copy2(s, d)

# 2. Process all HTML files to replace SVG logo with PNG logo
html_files = glob.glob(os.path.join(base_dir, "**", "*.html"), recursive=True)

for file in html_files:
    rel_path = os.path.relpath(base_dir, os.path.dirname(file)).replace('\\', '/')
    if rel_path == '.':
        rel_path = ''
    else:
        rel_path += '/'
        
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # We replace the logo block inside the nav
    # Old logo pattern: <a href="..." class="logo"> ... <svg ...> ... </svg> ... Ressources IAI ... </a>
    logo_pattern = re.compile(r'<a href="[^"]*index\.html" class="logo">.*?</a>', re.DOTALL)
    
    new_logo = f"""<a href="{rel_path}index.html" class="logo">
                <img src="{rel_path}assets/logo_iai.png" alt="Logo IAI" style="height: 40px; width: auto; object-fit: contain;">
                Ressources IAI
            </a>"""
            
    if logo_pattern.search(content):
        content = logo_pattern.sub(new_logo, content)
        
    with open(file, 'w', encoding='utf-8') as f:
        f.write(content)

# 3. Inject Preloader into index.html
index_path = os.path.join(base_dir, "index.html")
with open(index_path, "r", encoding="utf-8") as f:
    index_content = f.read()

preloader_html = """
    <!-- Preloader -->
    <div id="preloader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #000; z-index: 9999; display: flex; justify-content: center; align-items: center; transition: opacity 0.5s ease-out;">
        <video id="loading-video" src="assets/video_logo.mp4" muted playsinline style="max-width: 80%; max-height: 80%;"></video>
    </div>
    <script>
        if (sessionStorage.getItem('visited')) {
            document.getElementById('preloader').style.display = 'none';
        } else {
            const preloader = document.getElementById('preloader');
            const video = document.getElementById('loading-video');
            
            // Allow clicking to dismiss if auto-play is blocked
            preloader.addEventListener('click', dismissPreloader);

            // Attempt to play
            video.play().catch(() => {
                // If blocked by browser policy, dismiss after a short delay
                setTimeout(dismissPreloader, 3000);
            });

            video.addEventListener('ended', dismissPreloader);

            function dismissPreloader() {
                preloader.style.opacity = '0';
                setTimeout(() => {
                    preloader.style.display = 'none';
                    sessionStorage.setItem('visited', 'true');
                }, 500);
            }
            
            // Fallback timeout just in case it hangs
            setTimeout(dismissPreloader, 8000);
        }
    </script>
"""

# Inject right after <body> tag
if '<div id="preloader"' not in index_content:
    body_pattern = re.compile(r'<body[^>]*>')
    match = body_pattern.search(index_content)
    if match:
        idx = match.end()
        index_content = index_content[:idx] + "\n" + preloader_html + index_content[idx:]
        with open(index_path, "w", encoding="utf-8") as f:
            f.write(index_content)

print("V5 Updates applied: Logo replaced globally, Video Preloader injected into index.html.")
