import os
import glob
import re

base_dir = r"C:\Users\MSI\Music\IAI_MENTORIA\iai-resources"

# 1. Update style.css
css_path = os.path.join(base_dir, "css", "style.css")
with open(css_path, "a", encoding="utf-8") as f:
    f.write("""
/* --- Dark Mode Variables --- */
body.dark {
  --text-main: #f9fafb;
  --text-muted: #9ca3af;
  --bg-body: #111827;
  --bg-card: #1f2937;
  --border: #374151;
  --primary: #60a5fa;
  --primary-light: #93c5fd;
  --primary-dark: #3b82f6;
  --secondary: #374151;
}

body.dark .navbar, body.dark .footer {
  background-color: #1f2937;
  color: #f9fafb;
  border-bottom: 1px solid #374151;
}

body.dark .footer-text, body.dark .footer-links a {
  color: #d1d5db;
}

body.dark .input-field, body.dark .search-input, body.dark .form-control {
  background-color: #374151;
  border-color: #4b5563;
  color: #f9fafb;
}

body.dark .btn-outline {
  border-color: #60a5fa;
  color: #60a5fa;
}

body.dark .btn-outline:hover {
  background-color: #60a5fa;
  color: #111827;
}

/* Theme Toggle Button */
.theme-toggle {
  background: none;
  border: none;
  cursor: pointer;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem;
  transition: color var(--transition-fast);
}

.theme-toggle:hover {
  color: var(--accent);
}

.theme-toggle svg {
  width: 24px;
  height: 24px;
}
""")

# 2. Update main.js
js_path = os.path.join(base_dir, "js", "main.js")
with open(js_path, "a", encoding="utf-8") as f:
    f.write("""
    // 3. Dark Mode Toggle
    const themeBtn = document.getElementById('theme-toggle');
    const body = document.body;
    
    // Check local storage
    if (localStorage.getItem('theme') === 'dark') {
        body.classList.add('dark');
        updateThemeIcon(true);
    }

    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            body.classList.toggle('dark');
            const isDark = body.classList.contains('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateThemeIcon(isDark);
        });
    }

    function updateThemeIcon(isDark) {
        if (!themeBtn) return;
        if (isDark) {
            themeBtn.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>';
        } else {
            themeBtn.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>';
        }
    }
""")

# 3. Process all HTML files
html_files = glob.glob(os.path.join(base_dir, "**", "*.html"), recursive=True)

for file in html_files:
    # Skip backend or other folders if necessary, but here we process all
    # Calculate relative depth
    rel_path = os.path.relpath(base_dir, os.path.dirname(file)).replace('\\', '/')
    if rel_path == '.':
        rel_path = ''
    else:
        rel_path += '/'
        
    # Read HTML
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Replace navbar block
    nav_pattern = re.compile(r'<nav class="navbar">.*?</nav>', re.DOTALL)
    
    new_nav = f"""<nav class="navbar">
        <div class="container nav-container">
            <a href="{rel_path}index.html" class="logo">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 3L1 9L4 10.63V17L12 21L20 17V10.63L23 9M12 5.16L19.89 9L12 12.84L4.11 9M4 12V16.32L12 20.32L20 16.32V12L12 16L4 12Z"/>
                </svg>
                Ressources IAI
            </a>
            <div class="nav-links">
                <ul class="nav-menu">
                    <li><a href="{rel_path}index.html" class="nav-item">Accueil</a></li>
                    <li><a href="{rel_path}exams.html" class="nav-item">Examens</a></li>
                    <li><a href="{rel_path}search.html" class="nav-item">Rechercher</a></li>
                    <li><a href="{rel_path}contribute.html" class="nav-item">Contribuer</a></li>
                </ul>
                <div class="nav-actions">
                    <button class="theme-toggle" id="theme-toggle" title="Basculer le thème">
                        <!-- Moon icon default -->
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    </button>
                    <a href="{rel_path}profile.html" class="btn btn-outline" style="padding: 0.5rem 1rem;">Profil</a>
                </div>
            </div>
            <button class="mobile-menu-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
        </div>
    </nav>"""
    
    if nav_pattern.search(content):
        content = nav_pattern.sub(new_nav, content)
    
    # Replace broken CSS references
    # Some pages might have: href="../../../css/style.css" or href="../css/style.css" etc.
    css_pattern = re.compile(r'<link rel="stylesheet" href="(\.\./)*css/style\.css">')
    content = css_pattern.sub(f'<link rel="stylesheet" href="{rel_path}css/style.css">', content)
    
    # Ensure script main.js exists at the end of body
    if '<script src="' not in content and 'main.js"' not in content:
        content = content.replace('</body>', f'<script src="{rel_path}js/main.js"></script>\n</body>')
    else:
        # Replace script path just to be sure
        script_pattern = re.compile(r'<script src="(\.\./)*js/main\.js"></script>')
        content = script_pattern.sub(f'<script src="{rel_path}js/main.js"></script>', content)
        
    with open(file, 'w', encoding='utf-8') as f:
        f.write(content)

print(f"Processed {len(html_files)} HTML files.")
