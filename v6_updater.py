import os
import glob
import re
from PIL import Image

base_dir = r"C:\Users\MSI\Music\IAI_MENTORIA\iai-resources"

# 1. Image processing to remove checkered background
img_path = os.path.join(base_dir, "assets", "logo_iai.png")
if os.path.exists(img_path):
    print("Processing image to remove checkers...")
    img = Image.open(img_path).convert("RGBA")
    w, h = img.size
    pixels = img.load()

    # Find checker colors from corners
    corners = [(0,0), (w-1,0), (0,h-1), (w-1,h-1)]
    bg_targets = []
    
    # We sample a block from top-left to find the checker colors
    for x in range(min(w, 20)):
        for y in range(min(h, 20)):
            c = pixels[x, y][:3]
            # Add to bg_targets if it's a light color (often checkers are white/gray)
            if c[0] > 170 and c[1] > 170 and c[2] > 170:
                is_new = True
                for t in bg_targets:
                    if abs(c[0]-t[0]) < 10 and abs(c[1]-t[1]) < 10 and abs(c[2]-t[2]) < 10:
                        is_new = False
                        break
                if len(bg_targets) < 5 and is_new:
                    bg_targets.append(c)

    print("Target background colors found:", bg_targets)
    
    # Flood fill
    stack = corners.copy()
    visited = set(stack)

    while stack:
        x, y = stack.pop()
        
        is_bg = False
        for t in bg_targets:
            if abs(pixels[x, y][0]-t[0]) < 20 and abs(pixels[x, y][1]-t[1]) < 20 and abs(pixels[x, y][2]-t[2]) < 20:
                is_bg = True
                break
                
        if is_bg:
            pixels[x, y] = (0, 0, 0, 0)
            # Add neighbors
            for dx, dy in [(1,0), (-1,0), (0,1), (0,-1)]:
                nx, ny = x+dx, y+dy
                if 0 <= nx < w and 0 <= ny < h and (nx, ny) not in visited:
                    visited.add((nx, ny))
                    stack.append((nx, ny))

    # Clean up antialiased edges inside transparent area
    for y in range(h):
        for x in range(w):
            if pixels[x,y][3] < 120 and pixels[x,y][3] > 0:
                 pixels[x,y] = (0,0,0,0)

    clean_path = os.path.join(base_dir, "assets", "logo_iai_clean.png")
    img.save(clean_path)
    print("Clean image saved to:", clean_path)

# 2. Update HTML
html_files = glob.glob(os.path.join(base_dir, "**", "*.html"), recursive=True)

for file in html_files:
    rel_path = os.path.relpath(base_dir, os.path.dirname(file)).replace('\\', '/')
    if rel_path == '.':
        rel_path = ''
    else:
        rel_path += '/'
        
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # We replace the logo block
    logo_pattern = re.compile(r'<a href="[^"]*index\.html" class="logo">.*?</a>', re.DOTALL)
    
    # Increase height to 60px (or 80px) and remove text
    new_logo = f'''<a href="{rel_path}index.html" class="logo" style="padding: 0; display: flex; align-items: center;">
                <img src="{rel_path}assets/logo_iai_clean.png" alt="Logo IAI" style="height: 60px; width: auto; object-fit: contain;">
            </a>'''
            
    if logo_pattern.search(content):
        content = logo_pattern.sub(new_logo, content)
        
    with open(file, 'w', encoding='utf-8') as f:
        f.write(content)

print(f"Processed HTML files globally.")

# 3. Update CSS for Dark Mode
css_path = os.path.join(base_dir, "css", "style.css")
with open(css_path, "a", encoding="utf-8") as f:
    f.write('''
/* --- Enhanced Logo Visibility in Dark Mode --- */
body.dark .navbar .logo img {
  filter: drop-shadow(0px 0px 4px rgba(255, 255, 255, 0.7)) brightness(1.2);
}
''')
