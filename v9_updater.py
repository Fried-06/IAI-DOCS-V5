import os
import re
import glob

base_dir = r"C:\Users\MSI\Music\IAI_MENTORIA\iai-resources"
css_path = os.path.join(base_dir, "css", "style.css")
js_path = os.path.join(base_dir, "js", "main.js")
index_path = os.path.join(base_dir, "index.html")
login_path = os.path.join(base_dir, "login.html")

# ═══════════════════════════════════════════════════════
# 1. COLOR-CODED CSS PER LEVEL
# ═══════════════════════════════════════════════════════
color_css = """

/* ── PHASE 9: COLOR-CODED LEVELS ── */
/* L1 = Blue (default cyan/blue), L2 = Red/Crimson, GLSI = Purple, ASR = Green */

/* --- L1 Blue (already default) --- */
.level-l1:hover { border-color: var(--blue) !important; box-shadow: 0 10px 30px rgba(59,158,255,0.2) !important; }
.level-l1 .level-icon { color: var(--blue); }
.level-l1 .level-title { color: var(--blue); }

/* --- L2 Red/Crimson --- */
.level-l2:hover { border-color: var(--pink) !important; box-shadow: 0 10px 30px rgba(255,77,141,0.2) !important; }
.level-l2 .level-icon { color: var(--pink); }
.level-l2 .level-title { color: var(--pink); }

/* --- GLSI Purple --- */
.level-glsi:hover { border-color: var(--purple) !important; box-shadow: 0 10px 30px rgba(168,85,247,0.2) !important; }
.level-glsi .level-icon { color: var(--purple); }
.level-glsi .level-title { color: var(--purple); }

/* --- ASR Green --- */
.level-asr:hover { border-color: var(--green) !important; box-shadow: 0 10px 30px rgba(34,217,122,0.2) !important; }
.level-asr .level-icon { color: var(--green); }
.level-asr .level-title { color: var(--green); }

/* Card image container */
.level-card-img {
  width: 100%;
  height: 120px;
  border-radius: 6px 6px 0 0;
  overflow: hidden;
  margin-bottom: 1rem;
  background: var(--bg3);
  border-bottom: 1px solid var(--border2);
}
.level-card-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}
.level-card:hover .level-card-img img {
  transform: scale(1.05);
}

/* ── Smooth Nav Transitions ── */
html {
  scroll-behavior: smooth;
}
.nav-item {
  transition: color 0.25s ease, border-bottom-color 0.25s ease !important;
  border-bottom: 2px solid transparent;
  padding-bottom: 2px;
}
.nav-item:hover {
  color: var(--cyan) !important;
  border-bottom-color: var(--cyan);
}

/* ── Fullscreen Preloader ── */
#preloader video {
  width: 100vw !important;
  height: 100vh !important;
  max-width: 100vw !important;
  max-height: 100vh !important;
  object-fit: cover !important;
}

/* ── Level pages: color-coded semester cards ── */
.semester-l1 .level-card:hover { border-color: var(--blue) !important; box-shadow: 0 8px 25px rgba(59,158,255,0.15) !important; }
.semester-l2 .level-card:hover { border-color: var(--pink) !important; box-shadow: 0 8px 25px rgba(255,77,141,0.15) !important; }
.semester-glsi .level-card:hover { border-color: var(--purple) !important; box-shadow: 0 8px 25px rgba(168,85,247,0.15) !important; }
.semester-asr .level-card:hover { border-color: var(--green) !important; box-shadow: 0 8px 25px rgba(34,217,122,0.15) !important; }

/* Subject cards per level */
.subject-l1 .resource-card:hover { border-color: var(--blue) !important; box-shadow: 0 8px 20px rgba(59,158,255,0.15) !important; }
.subject-l2 .resource-card:hover { border-color: var(--pink) !important; box-shadow: 0 8px 20px rgba(255,77,141,0.15) !important; }
.subject-glsi .resource-card:hover { border-color: var(--purple) !important; box-shadow: 0 8px 20px rgba(168,85,247,0.15) !important; }
.subject-asr .resource-card:hover { border-color: var(--green) !important; box-shadow: 0 8px 20px rgba(34,217,122,0.15) !important; }

/* Subject page sidebars per level */
.sidebar-l1 .year-btn.active { background: var(--blue) !important; }
.sidebar-l1 .year-btn:hover { color: var(--blue) !important; border-left-color: var(--blue) !important; }
.sidebar-l2 .year-btn.active { background: var(--pink) !important; }
.sidebar-l2 .year-btn:hover { color: var(--pink) !important; border-left-color: var(--pink) !important; }
.sidebar-glsi .year-btn.active { background: var(--purple) !important; }
.sidebar-glsi .year-btn:hover { color: var(--purple) !important; border-left-color: var(--purple) !important; }
.sidebar-asr .year-btn.active { background: var(--green) !important; }
.sidebar-asr .year-btn:hover { color: var(--green) !important; border-left-color: var(--green) !important; }
"""

with open(css_path, "a", encoding="utf-8") as f:
    f.write(color_css)

# ═══════════════════════════════════════════════════════
# 2. UPDATE INDEX.HTML - ADD COLOR CLASSES & IMAGE FIELDS
# ═══════════════════════════════════════════════════════
with open(index_path, "r", encoding="utf-8") as f:
    idx = f.read()

# Add color classes to L1 card
idx = idx.replace(
    '<a href="pages/L1/index.html" class="level-card">',
    '<a href="pages/L1/index.html" class="level-card level-l1">'
)
# Add color classes to L2 card
idx = idx.replace(
    '<a href="pages/L2/index.html" class="level-card">',
    '<a href="pages/L2/index.html" class="level-card level-l2">'
)
# Add color classes to GLSI card
idx = idx.replace(
    '<a href="pages/L3/GLSI/index.html" class="level-card">',
    '<a href="pages/L3/GLSI/index.html" class="level-card level-glsi">'
)
# Add color classes to ASR card
idx = idx.replace(
    '<a href="pages/L3/ASR/index.html" class="level-card">',
    '<a href="pages/L3/ASR/index.html" class="level-card level-asr">'
)

# Add image fields inside each level card (right after the opening tag)
# L1
idx = idx.replace(
    '<a href="pages/L1/index.html" class="level-card level-l1">\n                        <div class="level-icon">',
    '<a href="pages/L1/index.html" class="level-card level-l1">\n                        <div class="level-card-img"><img src="assets/l1_cover.png" alt="L1"></div>\n                        <div class="level-icon">'
)
# L2
idx = idx.replace(
    '<a href="pages/L2/index.html" class="level-card level-l2">\n                        <div class="level-icon">',
    '<a href="pages/L2/index.html" class="level-card level-l2">\n                        <div class="level-card-img"><img src="assets/l2_cover.png" alt="L2"></div>\n                        <div class="level-icon">'
)
# GLSI
idx = idx.replace(
    '<a href="pages/L3/GLSI/index.html" class="level-card level-glsi">\n                        <div class="level-icon">',
    '<a href="pages/L3/GLSI/index.html" class="level-card level-glsi">\n                        <div class="level-card-img"><img src="assets/glsi_cover.png" alt="GLSI"></div>\n                        <div class="level-icon">'
)
# ASR
idx = idx.replace(
    '<a href="pages/L3/ASR/index.html" class="level-card level-asr">\n                        <div class="level-icon">',
    '<a href="pages/L3/ASR/index.html" class="level-card level-asr">\n                        <div class="level-card-img"><img src="assets/asr_cover.png" alt="ASR"></div>\n                        <div class="level-icon">'
)

# Also try with \r\n line endings
idx = idx.replace(
    '<a href="pages/L1/index.html" class="level-card level-l1">\r\n                        <div class="level-icon">',
    '<a href="pages/L1/index.html" class="level-card level-l1">\r\n                        <div class="level-card-img"><img src="assets/l1_cover.png" alt="L1"></div>\r\n                        <div class="level-icon">'
)
idx = idx.replace(
    '<a href="pages/L2/index.html" class="level-card level-l2">\r\n                        <div class="level-icon">',
    '<a href="pages/L2/index.html" class="level-card level-l2">\r\n                        <div class="level-card-img"><img src="assets/l2_cover.png" alt="L2"></div>\r\n                        <div class="level-icon">'
)
idx = idx.replace(
    '<a href="pages/L3/GLSI/index.html" class="level-card level-glsi">\r\n                        <div class="level-icon">',
    '<a href="pages/L3/GLSI/index.html" class="level-card level-glsi">\r\n                        <div class="level-card-img"><img src="assets/glsi_cover.png" alt="GLSI"></div>\r\n                        <div class="level-icon">'
)
idx = idx.replace(
    '<a href="pages/L3/ASR/index.html" class="level-card level-asr">\r\n                        <div class="level-icon">',
    '<a href="pages/L3/ASR/index.html" class="level-card level-asr">\r\n                        <div class="level-card-img"><img src="assets/asr_cover.png" alt="ASR"></div>\r\n                        <div class="level-icon">'
)

# Fix preloader video to be fullscreen
idx = idx.replace(
    'style="max-width: 80%; max-height: 80%;"',
    'style="width: 100vw; height: 100vh; object-fit: cover;"'
)

with open(index_path, "w", encoding="utf-8") as f:
    f.write(idx)

# ═══════════════════════════════════════════════════════
# 3. UPDATE LOGIN.HTML - ADD LOGO + COLOR SWIPE
# ═══════════════════════════════════════════════════════
with open(login_path, "r", encoding="utf-8") as f:
    login = f.read()

# Add logo above sign-in form title
if 'IAI_DOCS2.png' not in login:
    login = login.replace(
        '<h2>Bon retour !</h2>',
        '<img src="assets/IAI_DOCS2.png" alt="Logo IAI" style="height: 80px; margin-bottom: 1rem; filter: drop-shadow(0 0 8px rgba(0,229,196,0.4));">\n                <h2>Bon retour !</h2>'
    )
    login = login.replace(
        '<h2>Créer un compte</h2>',
        '<img src="assets/IAI_DOCS2.png" alt="Logo IAI" style="height: 80px; margin-bottom: 1rem; filter: drop-shadow(0 0 8px rgba(168,85,247,0.4));">\n                <h2>Créer un compte</h2>'
    )

# Add color-swipe CSS/JS for the login overlay
# The overlay-right (Sign Up call) will be cyan, and on swipe, overlay-left (Sign In call) will be purple
color_swipe_css = """
        /* Color swipe for login */
        .auth-container .overlay {
            transition: transform 0.6s ease-in-out, background 0.6s ease-in-out;
            background: linear-gradient(135deg, #0b1930, #040c18);
        }
        .overlay-right::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at 50% 50%, rgba(0,229,196,0.08), transparent 70%);
            pointer-events: none;
        }
        .overlay-left::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at 50% 50%, rgba(168,85,247,0.08), transparent 70%);
            pointer-events: none;
        }
        .auth-container .overlay-right .btn-overlay {
            border-color: var(--cyan); color: var(--cyan);
        }
        .auth-container .overlay-right .btn-overlay:hover {
            background: var(--cyan); color: #000;
        }
        .auth-container.sign-up-mode .overlay-left .btn-overlay {
            border-color: var(--purple); color: var(--purple);
        }
        .auth-container.sign-up-mode .overlay-left .btn-overlay:hover {
            background: var(--purple); color: #fff;
        }
        .auth-container.sign-up-mode .sign-up-panel .btn {
            background: var(--purple); color: #fff;
        }
        .auth-container.sign-up-mode .sign-up-panel .btn:hover {
            background: #c084fc;
            box-shadow: 0 0 20px rgba(168,85,247,0.4);
        }
        .auth-container.sign-up-mode .sign-up-panel .input-field:focus {
            border-color: var(--purple);
            box-shadow: 0 0 10px rgba(168,85,247,0.2);
        }
"""
# Inject before </style>
login = login.replace('    </style>', color_swipe_css + '    </style>')

with open(login_path, "w", encoding="utf-8") as f:
    f.write(login)

# ═══════════════════════════════════════════════════════
# 4. SMOOTH NAV JS (page transitions)
# ═══════════════════════════════════════════════════════
nav_js = """

// --- PHASE 9: SMOOTH NAV TRANSITIONS ---
document.querySelectorAll('a.nav-item').forEach(link => {
    link.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href && !href.startsWith('#') && !href.startsWith('http')) {
            e.preventDefault();
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.25s ease-out';
            setTimeout(() => { window.location.href = href; }, 250);
        }
    });
});
"""

with open(js_path, "a", encoding="utf-8") as f:
    f.write(nav_js)

# ═══════════════════════════════════════════════════════
# 5. ADD COLOR CLASSES TO SUBJECT PAGES
# ═══════════════════════════════════════════════════════
html_files = glob.glob(os.path.join(base_dir, "**", "*.html"), recursive=True)

for fpath in html_files:
    rel = os.path.relpath(fpath, base_dir).replace('\\', '/')
    
    # Determine level from path
    lv_class = ''
    if '/L1/' in rel or '\\L1\\' in os.path.relpath(fpath, base_dir):
        lv_class = 'l1'
    elif 'GLSI' in rel:
        lv_class = 'glsi'
    elif 'ASR' in rel:
        lv_class = 'asr'
    elif '/L2/' in rel or '\\L2\\' in os.path.relpath(fpath, base_dir):
        lv_class = 'l2'
    
    if lv_class:
        with open(fpath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Add body-level class for styling scope
        if f'class="page-fade-in level-{lv_class}"' not in content:
            content = content.replace('class="page-fade-in"', f'class="page-fade-in level-{lv_class}"')
        
        with open(fpath, 'w', encoding='utf-8') as f:
            f.write(content)

print("Phase 9 complete.")
