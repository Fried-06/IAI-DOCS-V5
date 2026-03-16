import os
import glob
import re

base_dir = r"C:\Users\MSI\Music\IAI_MENTORIA\iai-resources"
css_path = os.path.join(base_dir, "css", "style.css")
js_path = os.path.join(base_dir, "js", "main.js")
html_files = glob.glob(os.path.join(base_dir, "**", "*.html"), recursive=True)

# 1. NEW CSS BLOCKS to append/replace
tech_doc_css = """
/* ── PHASE 7: TECH-DOC DESIGN & ANIMATIONS ── */

:root {
  /* User-provided palette overriding previous light/dark roots */
  --bg:      #040c18;
  --bg2:     #07111f;
  --bg3:     #0b1930;
  --border:  #152540;
  --border2: #1e3558;
  --cyan:    #00e5c4;
  --amber:   #ffb703;
  --pink:    #ff4d8d;
  --purple:  #a855f7;
  --green:   #22d97a;
  --blue:    #3b9eff;
  --text:    #c8ddf2;
  --muted:   #4a6a8a;
  --code-bg: #020a14;
  
  /* Overriding old variables to match the new tech-doc theme across the site */
  --bg-body: var(--bg);
  --bg-card: var(--bg2);
  --text-main: var(--text);
  --primary: var(--blue);
  --primary-light: var(--cyan);
  --primary-dark: var(--bg3);
  --secondary: var(--bg3);
  --accent: var(--cyan);
}

/* Force dark mode behavior since it's a dark design system */
body {
  font-family: 'DM Sans', sans-serif !important;
  background: var(--bg) !important;
  color: var(--text) !important;
  line-height: 1.75;
}

h1, h2, h3, .hero-title, .logo {
  font-family: 'Bebas Neue', sans-serif !important;
  letter-spacing: 0.03em;
}

span, button, .nav-item, .footer {
  font-family: 'JetBrains Mono', monospace;
}

/* 1. Card Hover Animations */
.level-card, .exam-card, .resource-card {
  background: var(--bg2) !important;
  border: 1px solid var(--border2) !important;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
}

.level-card:hover, .exam-card:hover, .resource-card:hover {
  transform: translateY(-5px) scale(1.02) !important;
  box-shadow: 0 10px 25px rgba(0, 229, 196, 0.15) !important;
  border-color: var(--cyan) !important;
}

.level-card:hover .level-icon svg, .exam-card:hover svg, .resource-card:hover svg {
  transform: scale(1.1);
  transition: transform 0.3s ease;
  color: var(--cyan);
}

/* 2. Sidebar Navigation Animation (Smooth slide-down) */
.year-panel {
  display: grid !important;
  grid-template-rows: 0fr;
  transition: grid-template-rows 0.4s ease-in-out;
  overflow: hidden;
  padding: 0 !important;
  margin: 0 !important;
  opacity: 0;
}
.year-panel.active {
  grid-template-rows: 1fr;
  opacity: 1;
  margin-top: 1rem !important; /* Space when open */
}
.year-panel > div {
  min-height: 0; /* allows the grid trick to work */
}

/* 3. Page Transition Animations */
.page-fade-in {
  animation: pageFadeIn 300ms ease-out forwards;
  opacity: 0;
}
@keyframes pageFadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* 4. Scroll Reveal Animations */
.reveal-up {
  opacity: 0;
  transform: translateY(30px);
  transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}
.reveal-up.active {
  opacity: 1;
  transform: translateY(0);
}

/* 5. Search Bar Interaction */
.search-input {
  transition: all 0.3s ease !important;
  background: var(--bg3) !important;
  border: 1px solid var(--border2) !important;
  color: var(--text) !important;
  font-family: 'JetBrains Mono', monospace;
}
.search-input:focus {
  transform: scale(1.02);
  border-color: var(--cyan) !important;
  box-shadow: 0 0 15px rgba(0, 229, 196, 0.3) !important;
  width: 105%; /* subtle expand */
}

/* 6. Button Hover Effects */
.btn {
  font-family: 'JetBrains Mono', monospace !important;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  font-size: 0.8rem !important;
  transition: all 0.3s ease !important;
  border-radius: 4px !important;
  position: relative;
  overflow: hidden;
}
.btn-primary {
  background: var(--blue) !important;
  border: 1px solid var(--blue) !important;
  color: #fff !important;
}
.btn-primary:hover {
  background: transparent !important;
  color: var(--cyan) !important;
  border-color: var(--cyan) !important;
  box-shadow: 0 0 15px rgba(0, 229, 196, 0.4) !important;
  transform: scale(1.05);
}

.btn-outline {
  border: 1px solid var(--border2) !important;
  color: var(--text) !important;
}
.btn-outline:hover {
  border-color: var(--amber) !important;
  color: var(--amber) !important;
  box-shadow: 0 0 15px rgba(255, 183, 3, 0.3) !important;
  transform: scale(1.05);
}

/* 7. Loading Skeletons */
.skeleton {
  background: linear-gradient(90deg, var(--bg2) 25%, var(--border2) 50%, var(--bg2) 75%);
  background-size: 200% 100%;
  animation: skeletonPulse 1.5s infinite linear;
  border-radius: 4px;
}
@keyframes skeletonPulse {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
.skeleton-card {
  height: 150px;
  width: 100%;
}
.skeleton-text {
  height: 20px;
  width: 80%;
  margin-bottom: 10px;
}

/* 8. Logo Micro Animation */
.logo img {
  animation: logoPulse 2s ease-in-out;
  filter: drop-shadow(0 0 8px rgba(0, 229, 196, 0.5));
}
@keyframes logoPulse {
  0% { filter: drop-shadow(0 0 2px rgba(0, 229, 196, 0.1)); transform: scale(0.98); }
  50% { filter: drop-shadow(0 0 20px rgba(0, 229, 196, 0.8)); transform: scale(1.02); }
  100% { filter: drop-shadow(0 0 8px rgba(0, 229, 196, 0.5)); transform: scale(1); }
}

/* Fixing generic areas for the dark tech-doc style */
.navbar {
  background: var(--bg) !important;
  border-bottom: 1px solid var(--border) !important;
}
.footer {
  background: var(--bg2) !important;
  border-top: 1px solid var(--border) !important;
}
select, input {
  background: var(--bg3) !important;
  color: var(--text) !important;
  border: 1px solid var(--border2) !important;
}
"""

with open(css_path, "a", encoding="utf-8") as f:
    f.write(tech_doc_css)

# 2. JS UPDATES for Scroll Reveal
js_code = """

// --- PHASE 7: SCROLL REVEAL (IntersectionObserver) ---
document.addEventListener("DOMContentLoaded", () => {
    // Add page-fade-in to body on load (HTML script handles this usually, but guaranteeing it here too)
    document.body.classList.add("page-fade-in");

    // Add .reveal-up to cards automatically if not present
    const cards = document.querySelectorAll(".level-card, .exam-card, .resource-card, .subject-header");
    cards.forEach((card, index) => {
        card.classList.add("reveal-up");
        // Staggered delay based on index for siblings
        card.style.transitionDelay = `${(index % 4) * 100}ms`;
    });

    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                observer.unobserve(entry.target); // Reveal only once
            }
        });
    }, observerOptions);

    document.querySelectorAll('.reveal-up').forEach(el => observer.observe(el));
});
"""

with open(js_path, "a", encoding="utf-8") as f:
    f.write(js_code)
    
# 3. HTML UPDATES for Fonts and Page Fade In
font_link = '<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:ital,wght@0,300;0,400;0,600;0,700;1,400&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">\n'

for file in html_files:
    with open(file, "r", encoding="utf-8") as f:
        content = f.read()
    
    # Inject Google Fonts BEFORE </head>
    if "Bebas+Neue" not in content:
        content = content.replace("</head>", font_link + "</head>")
        
    # Inject page-fade-in to body
    if '<body class="' in content:
        content = content.replace('<body class="', '<body class="page-fade-in ')
    else:
        content = content.replace('<body>', '<body class="page-fade-in">')
        
    with open(file, "w", encoding="utf-8") as f:
        f.write(content)

print(f"Phase 7 scripts injected via v7_updater.py across {len(html_files)} HTML files.")
