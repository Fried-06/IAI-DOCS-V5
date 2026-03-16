import os
import glob
import re

base_dir = r"C:\Users\MSI\Music\IAI_MENTORIA\iai-resources"

# 1. Expand Dark Mode CSS in style.css
css_path = os.path.join(base_dir, "css", "style.css")
with open(css_path, "a", encoding="utf-8") as f:
    f.write("""
/* --- Extensive Dark Mode Overrides --- */
body.dark .hero {
  background: linear-gradient(135deg, rgba(30, 58, 138, 0.15) 0%, rgba(6, 182, 212, 0.05) 100%);
}
body.dark .level-icon {
  background-color: rgba(6, 182, 212, 0.15);
}
body.dark .footer-col h3 {
  color: var(--text-light);
}
body.dark .creator-name {
  color: var(--text-muted);
}
body.dark .hero-bg-shape {
  opacity: 0.15;
}
body.dark .exam-card, body.dark .level-card, body.dark .resource-card, body.dark .sidebar, body.dark .subject-header {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
}
body.dark th, body.dark td {
  border-color: var(--border);
}
""")

# 2. Process all HTML files to restore Login/Register and update Navbar
html_files = glob.glob(os.path.join(base_dir, "**", "*.html"), recursive=True)

for file in html_files:
    # Calculate relative depth
    rel_path = os.path.relpath(base_dir, os.path.dirname(file)).replace('\\', '/')
    if rel_path == '.':
        rel_path = ''
    else:
        rel_path += '/'
        
    # Read HTML
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # We will simply replace the nav-actions block so it contains the theme-toggle, login, register, and profil buttons.
    nav_pattern = re.compile(r'<nav class="navbar">.*?</nav>', re.DOTALL)
    
    # Let's see if we can just re-inject the whole nav exactly as v3 but with the correct buttons
    # Note: On the profile page or if logged in, we show Profil, otherwise Connexion/Inscription.
    # For a static site, we usually display both or a split. Let's add them gracefully.
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
                    <button class="theme-toggle" id="theme-toggle" title="Basculer le thème" style="margin-right: 0.5rem;">
                        <!-- Moon icon default -->
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    </button>
                    <a href="{rel_path}login.html" class="btn btn-outline" style="padding: 0.5rem 1rem; border: none;">Connexion</a>
                    <a href="{rel_path}login.html" class="btn btn-primary" style="padding: 0.5rem 1rem;">S'inscrire</a>
                    <!-- Profil is hidden by default, shown via JS if logged in -->
                    <a href="{rel_path}profile.html" class="btn btn-outline" id="btn-profil" style="display: none; padding: 0.5rem 1rem;">Profil</a>
                </div>
            </div>
            <button class="mobile-menu-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
        </div>
    </nav>"""
    
    if nav_pattern.search(content):
        content = nav_pattern.sub(new_nav, content)
        
    with open(file, 'w', encoding='utf-8') as f:
        f.write(content)

print(f"Processed {len(html_files)} HTML files for Navigation and Dark Mode expansions.")
