import os
import re

base_dir = r"C:\Users\MSI\Music\IAI_MENTORIA\iai-resources"
index_path = os.path.join(base_dir, "index.html")
css_path = os.path.join(base_dir, "css", "style.css")
js_path = os.path.join(base_dir, "js", "main.js")

# ═══════════════════════════════════════════════════════
# 1. REPLACE HERO SECTION IN INDEX.HTML
# ═══════════════════════════════════════════════════════
with open(index_path, "r", encoding="utf-8") as f:
    html = f.read()

# ── Replace the old hero section ──
old_hero = '''        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-bg-shape shape-1"></div>
            <div class="hero-bg-shape shape-2"></div>
            <div class="container hero-content">
                <h1>Le WIkipédia de l\'IAI-TOGO</h1>
                <p>Accédez aux anciens examens, devoirs et cours structurés des années académiques précédentes de manière centralisée.</p>
                <div class="hero-actions">
                    <a href="pages/L1/index.html" class="btn btn-primary">Parcourir les Cours</a>
                    <a href="search.html" class="btn btn-accent">Explorer les Examens</a>
                </div>
            </div>
        </section>'''

new_hero = '''        <!-- ═══ HERO SECTION — Modern AI Startup ═══ -->
        <section class="hero-v12" id="hero">
            <!-- Animated grid background -->
            <div class="hero-grid"></div>
            <!-- Radial glow orbs -->
            <div class="hero-glow hero-glow-1"></div>
            <div class="hero-glow hero-glow-2"></div>
            <div class="hero-glow hero-glow-3"></div>
            <!-- Mouse spotlight -->
            <div class="hero-spotlight" id="hero-spotlight"></div>

            <div class="container hero-v12-content">
                <span class="hero-badge anim-fade-up" style="animation-delay:0.1s">
                    <span class="hero-badge-dot"></span>
                    Plateforme Académique Open Source
                </span>
                <h1 class="hero-title anim-fade-up" style="animation-delay:0.25s">
                    Le Wikipédia de l\'<br><span class="gradient-text">IAI-TOGO</span>
                </h1>
                <p class="hero-subtitle anim-fade-up" style="animation-delay:0.45s">
                    Accédez aux anciens examens, devoirs et cours structurés des années académiques précédentes de manière centralisée.
                </p>
                <div class="hero-cta anim-fade-up" style="animation-delay:0.6s">
                    <a href="pages/L1/index.html" class="btn-glass btn-glass-primary hero-float">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>
                        Parcourir les Cours
                    </a>
                    <a href="search.html" class="btn-glass btn-glass-outline hero-float" style="animation-delay:0.15s">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Explorer les Examens
                    </a>
                </div>
            </div>
        </section>

        <!-- ═══ STATISTICS ROW ═══ -->
        <section class="stats-section scroll-reveal">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number" data-target="500">0</span><span class="stat-suffix">+</span>
                        <span class="stat-label">Examens</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" data-target="120">0</span><span class="stat-suffix">+</span>
                        <span class="stat-label">Cours</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" data-target="300">0</span><span class="stat-suffix">+</span>
                        <span class="stat-label">Étudiants</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" data-target="75">0</span><span class="stat-suffix">+</span>
                        <span class="stat-label">Matières</span>
                    </div>
                </div>
            </div>
        </section>'''

html = html.replace(old_hero, new_hero)

# ── Add "Why use IAI Docs" + "Contribute CTA" sections before the footer ──
why_and_cta = '''
        <!-- ═══ WHY USE IAI DOCS ═══ -->
        <section class="section why-section scroll-reveal" id="why">
            <div class="container">
                <span class="section-badge">Pourquoi nous choisir</span>
                <h2 class="section-title-v12">Pourquoi utiliser <span class="gradient-text">IAI Docs</span> ?</h2>
                <div class="why-grid">
                    <div class="why-card scroll-reveal">
                        <div class="why-icon" style="--accent:var(--cyan)">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                        </div>
                        <h3>Centralisé</h3>
                        <p>Tous les cours, TD, examens et corrections rassemblés en un seul endroit accessible à tout moment.</p>
                    </div>
                    <div class="why-card scroll-reveal">
                        <div class="why-icon" style="--accent:var(--purple)">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <h3>Communautaire</h3>
                        <p>Construit par les étudiants, pour les étudiants. Chacun contribue avec ses notes et corrections.</p>
                    </div>
                    <div class="why-card scroll-reveal">
                        <div class="why-icon" style="--accent:var(--green)">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        </div>
                        <h3>Structuré</h3>
                        <p>Organisation par niveau, semestre et matière. Navigation intuitive pour trouver rapidement ce dont vous avez besoin.</p>
                    </div>
                    <div class="why-card scroll-reveal">
                        <div class="why-icon" style="--accent:var(--amber)">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                        </div>
                        <h3>Évolutif</h3>
                        <p>Nouvelles ressources ajoutées chaque année. La plateforme grandit avec chaque nouvelle promotion.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══ CONTRIBUTE CTA ═══ -->
        <section class="section cta-section scroll-reveal" id="contribute-cta">
            <div class="container">
                <div class="cta-box">
                    <div class="cta-glow"></div>
                    <span class="section-badge" style="margin-bottom:1rem">Open Source</span>
                    <h2 class="section-title-v12" style="margin-bottom:1rem">Rejoignez la <span class="gradient-text">communauté</span></h2>
                    <p style="color:var(--muted);max-width:500px;margin:0 auto 2rem;font-size:1.05rem;line-height:1.7">
                        Partagez vos cours, corrections et examens pour aider les promotions futures. Chaque contribution compte.
                    </p>
                    <a href="contribute.html" class="btn-glass btn-glass-primary" style="display:inline-flex">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Contribuer maintenant
                    </a>
                </div>
            </div>
        </section>
'''

# Insert before <!-- Footer -->
html = html.replace('    <!-- Footer -->', why_and_cta + '\n    <!-- Footer -->')

# ── Add scroll-reveal class to existing sections ──
html = html.replace('<section class="section">', '<section class="section scroll-reveal">', 1)
html = html.replace('<section class="section bg-light">', '<section class="section bg-light scroll-reveal">', 1)

# ── Update Google Fonts: add Sora + Space Grotesk ──
old_fonts = '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">'
new_fonts = '<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">'
html = html.replace(old_fonts, new_fonts)

with open(index_path, "w", encoding="utf-8") as f:
    f.write(html)

print("HTML updated.")

# ═══════════════════════════════════════════════════════
# 2. CSS — Complete Phase 12 Overhaul
# ═══════════════════════════════════════════════════════
phase12_css = """

/* ═══════════════════════════════════════════════════════
   PHASE 12: MODERN AI STARTUP HOMEPAGE
   ═══════════════════════════════════════════════════════ */

/* ── Global Typography Overhaul ── */
body {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-rendering: optimizeLegibility;
  font-feature-settings: 'kern' 1;
}

h1, h2, h3, h4, .hero-title, .section-title, .section-title-v12 {
  font-family: 'Sora', 'Space Grotesk', sans-serif !important;
  font-weight: 700;
  letter-spacing: -0.02em;
}

body, p, span, li, a, div {
  font-family: 'Inter', sans-serif !important;
}

code, pre, .timeline-step, .hero-badge, .stat-label {
  font-family: 'JetBrains Mono', monospace !important;
}

/* ── Gradient Text Utility ── */
.gradient-text {
  background: linear-gradient(135deg, #3b9eff 0%, #00e5c4 40%, #a855f7 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* ── Section Badge ── */
.section-badge {
  display: inline-block;
  font-family: 'JetBrains Mono', monospace !important;
  font-size: 0.7rem;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: var(--cyan);
  border: 1px solid rgba(0,229,196,0.3);
  padding: 0.3rem 1rem;
  border-radius: 100px;
  margin-bottom: 1.5rem;
  background: rgba(0,229,196,0.05);
}

.section-title-v12 {
  font-size: clamp(2rem, 4vw, 3rem);
  color: #fff;
  margin-bottom: 1rem;
  text-align: center;
}


/* ═══════════════════════════════════════════
   HERO V12
   ═══════════════════════════════════════════ */

.hero-v12 {
  position: relative;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background: var(--bg);
  padding-top: 100px;
}

/* ── Animated Grid ── */
.hero-grid {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(59,158,255,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(59,158,255,0.04) 1px, transparent 1px);
  background-size: 60px 60px;
  mask-image: radial-gradient(ellipse 70% 50% at 50% 50%, black 40%, transparent 100%);
  -webkit-mask-image: radial-gradient(ellipse 70% 50% at 50% 50%, black 40%, transparent 100%);
  animation: gridPulse 8s ease-in-out infinite alternate;
}
@keyframes gridPulse {
  0% { opacity: 0.4; }
  100% { opacity: 0.8; }
}

/* ── Radial Glow Orbs ── */
.hero-glow {
  position: absolute;
  border-radius: 50%;
  filter: blur(120px);
  pointer-events: none;
  will-change: transform;
}
.hero-glow-1 {
  width: 600px; height: 600px;
  background: radial-gradient(circle, rgba(59,158,255,0.15), transparent 70%);
  top: -10%; left: 30%;
  animation: glowFloat1 12s ease-in-out infinite alternate;
}
.hero-glow-2 {
  width: 500px; height: 500px;
  background: radial-gradient(circle, rgba(0,229,196,0.12), transparent 70%);
  bottom: -15%; right: 20%;
  animation: glowFloat2 10s ease-in-out infinite alternate;
}
.hero-glow-3 {
  width: 400px; height: 400px;
  background: radial-gradient(circle, rgba(168,85,247,0.1), transparent 70%);
  top: 30%; right: 10%;
  animation: glowFloat3 14s ease-in-out infinite alternate;
}
@keyframes glowFloat1 { 0%{transform:translate(0,0)} 100%{transform:translate(30px,-20px)} }
@keyframes glowFloat2 { 0%{transform:translate(0,0)} 100%{transform:translate(-20px,15px)} }
@keyframes glowFloat3 { 0%{transform:translate(0,0)} 100%{transform:translate(-15px,-25px)} }

/* ── Mouse Spotlight ── */
.hero-spotlight {
  position: absolute;
  width: 400px;
  height: 400px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(0,229,196,0.06), transparent 70%);
  pointer-events: none;
  transform: translate(-50%, -50%);
  transition: left 0.15s ease-out, top 0.15s ease-out;
  will-change: left, top;
  display: none;
}

/* ── Hero Content ── */
.hero-v12-content {
  position: relative;
  z-index: 2;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.hero-badge {
  font-size: 0.65rem;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--cyan);
  border: 1px solid rgba(0,229,196,0.25);
  padding: 0.4rem 1.2rem;
  border-radius: 100px;
  background: rgba(0,229,196,0.06);
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 2rem;
}
.hero-badge-dot {
  width: 6px; height: 6px;
  border-radius: 50%;
  background: var(--cyan);
  animation: dotPulse 2s ease-in-out infinite;
}
@keyframes dotPulse {
  0%,100% { opacity: 1; box-shadow: 0 0 0 0 rgba(0,229,196,0.4); }
  50% { opacity: 0.6; box-shadow: 0 0 0 6px rgba(0,229,196,0); }
}

.hero-title {
  font-family: 'Sora', sans-serif !important;
  font-size: clamp(3rem, 7vw, 5.5rem) !important;
  font-weight: 800;
  line-height: 1.05;
  color: #fff;
  letter-spacing: -0.03em;
  margin-bottom: 1.5rem;
}

.hero-subtitle {
  font-size: clamp(1rem, 2vw, 1.15rem);
  color: var(--muted);
  max-width: 600px;
  line-height: 1.8;
  margin-bottom: 2.5rem;
}

/* ── Glass CTA Buttons ── */
.hero-cta {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  justify-content: center;
}

.btn-glass {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.85rem 1.8rem;
  border-radius: 100px;
  font-family: 'Inter', sans-serif !important;
  font-size: 0.85rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s cubic-bezier(0.25,0.8,0.25,1);
  cursor: pointer;
  border: 1px solid transparent;
  position: relative;
  overflow: hidden;
}
.btn-glass-primary {
  background: rgba(59,158,255,0.15);
  -webkit-backdrop-filter: blur(12px);
  backdrop-filter: blur(12px);
  border-color: rgba(59,158,255,0.3);
  color: #fff;
}
.btn-glass-primary:hover {
  background: rgba(59,158,255,0.25);
  border-color: rgba(59,158,255,0.5);
  box-shadow: 0 0 30px rgba(59,158,255,0.2);
  transform: translateY(-2px);
}
.btn-glass-outline {
  background: rgba(255,255,255,0.04);
  -webkit-backdrop-filter: blur(12px);
  backdrop-filter: blur(12px);
  border-color: rgba(255,255,255,0.1);
  color: var(--text);
}
.btn-glass-outline:hover {
  background: rgba(255,255,255,0.08);
  border-color: rgba(255,255,255,0.2);
  box-shadow: 0 0 20px rgba(255,255,255,0.05);
  transform: translateY(-2px);
}

/* ── Floating animation for CTA ── */
.hero-float {
  animation: heroFloat 4s ease-in-out infinite;
}
.hero-float:nth-child(2) { animation-delay: 0.5s; }
@keyframes heroFloat {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-3px); }
}
.hero-float:hover {
  animation-play-state: paused;
}


/* ── Hero Entry Animations ── */
.anim-fade-up {
  opacity: 0;
  transform: translateY(25px);
  animation: animFadeUp 0.8s ease-out forwards;
}
@keyframes animFadeUp {
  to { opacity: 1; transform: translateY(0); }
}

/* ═══════════════════════════════════════════
   STATISTICS ROW
   ═══════════════════════════════════════════ */

.stats-section {
  padding: 3.5rem 0;
  border-top: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
  background: var(--bg2);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
  text-align: center;
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.stat-number {
  font-family: 'Sora', sans-serif !important;
  font-size: clamp(2.2rem, 4vw, 3rem);
  font-weight: 800;
  color: #fff;
  line-height: 1;
}
.stat-suffix {
  font-family: 'Sora', sans-serif !important;
  font-size: clamp(1.5rem, 3vw, 2rem);
  font-weight: 700;
  color: var(--cyan);
}
.stat-label {
  font-size: 0.7rem;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: var(--muted);
  margin-top: 0.4rem;
}

/* ═══════════════════════════════════════════
   WHY USE IAI DOCS
   ═══════════════════════════════════════════ */

.why-section {
  padding: 6rem 0;
  text-align: center;
}

.why-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
  margin-top: 3rem;
}

.why-card {
  background: var(--bg2);
  border: 1px solid var(--border2);
  border-radius: 12px;
  padding: 2.2rem 1.8rem;
  text-align: left;
  transition: all 0.35s cubic-bezier(0.25,0.8,0.25,1);
}
.why-card:hover {
  transform: translateY(-4px) scale(1.02);
  border-color: var(--cyan);
  box-shadow: 0 12px 40px rgba(0,229,196,0.08);
}
.why-card:nth-child(2):hover { border-color: var(--purple); box-shadow: 0 12px 40px rgba(168,85,247,0.08); }
.why-card:nth-child(3):hover { border-color: var(--green); box-shadow: 0 12px 40px rgba(34,217,122,0.08); }
.why-card:nth-child(4):hover { border-color: var(--amber); box-shadow: 0 12px 40px rgba(255,183,3,0.08); }

.why-icon {
  width: 48px; height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.2rem;
  background: rgba(0,229,196,0.08);
  color: var(--accent, var(--cyan));
  border: 1px solid rgba(0,229,196,0.15);
}
.why-card:nth-child(2) .why-icon { background: rgba(168,85,247,0.08); border-color: rgba(168,85,247,0.15); color: var(--purple); }
.why-card:nth-child(3) .why-icon { background: rgba(34,217,122,0.08); border-color: rgba(34,217,122,0.15); color: var(--green); }
.why-card:nth-child(4) .why-icon { background: rgba(255,183,3,0.08); border-color: rgba(255,183,3,0.15); color: var(--amber); }

.why-card h3 {
  font-family: 'Sora', sans-serif !important;
  font-size: 1.15rem;
  color: #fff;
  margin-bottom: 0.6rem;
}
.why-card p {
  font-size: 0.88rem;
  color: var(--muted);
  line-height: 1.7;
}

/* ═══════════════════════════════════════════
   CONTRIBUTE CTA
   ═══════════════════════════════════════════ */

.cta-section {
  padding: 5rem 0;
}

.cta-box {
  position: relative;
  text-align: center;
  background: var(--bg2);
  border: 1px solid var(--border2);
  border-radius: 16px;
  padding: 4rem 2rem;
  overflow: hidden;
}
.cta-glow {
  position: absolute;
  top: -50%; left: 50%; transform: translateX(-50%);
  width: 500px; height: 300px;
  background: radial-gradient(circle, rgba(59,158,255,0.08), transparent 70%);
  pointer-events: none;
}

/* ═══════════════════════════════════════════
   SCROLL REVEAL (Phase 12 global)
   ═══════════════════════════════════════════ */

.scroll-reveal {
  opacity: 0;
  transform: translateY(30px);
  transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.scroll-reveal.sr-visible {
  opacity: 1;
  transform: translateY(0);
}

/* ═══════════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════════ */

@media (max-width: 900px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .why-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 600px) {
  .hero-title { font-size: 2.8rem !important; }
  .stats-grid { grid-template-columns: 1fr 1fr; gap: 1.5rem; }
  .why-grid { grid-template-columns: 1fr; }
  .hero-cta { flex-direction: column; align-items: center; }
  .hero-cta .btn-glass { width: 100%; max-width: 300px; justify-content: center; }
}
"""

with open(css_path, "a", encoding="utf-8") as f:
    f.write(phase12_css)

print("CSS appended.")

# ═══════════════════════════════════════════════════════
# 3. JAVASCRIPT — Spotlight, Counter, Scroll Reveal v12
# ═══════════════════════════════════════════════════════
phase12_js = """

// ═══════════════════════════════════════════════════════
// PHASE 12: MODERN STARTUP HOMEPAGE JS
// ═══════════════════════════════════════════════════════

document.addEventListener("DOMContentLoaded", () => {

    // ── 1. MOUSE SPOTLIGHT ──
    const hero = document.querySelector(".hero-v12");
    const spotlight = document.getElementById("hero-spotlight");
    if (hero && spotlight) {
        spotlight.style.display = "block";
        hero.addEventListener("mousemove", (e) => {
            const rect = hero.getBoundingClientRect();
            spotlight.style.left = (e.clientX - rect.left) + "px";
            spotlight.style.top = (e.clientY - rect.top) + "px";
        });
        hero.addEventListener("mouseleave", () => { spotlight.style.display = "none"; });
        hero.addEventListener("mouseenter", () => { spotlight.style.display = "block"; });
    }

    // ── 2. ANIMATED COUNTER ──
    function animateCounter(el) {
        const target = parseInt(el.getAttribute("data-target"), 10);
        const duration = 2000;
        const start = performance.now();
        function update(now) {
            const pct = Math.min((now - start) / duration, 1);
            // EaseOutQuart for smooth deceleration
            const eased = 1 - Math.pow(1 - pct, 4);
            el.textContent = Math.floor(eased * target);
            if (pct < 1) requestAnimationFrame(update);
            else el.textContent = target;
        }
        requestAnimationFrame(update);
    }

    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.querySelectorAll(".stat-number").forEach(animateCounter);
                statsObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });

    const statsSection = document.querySelector(".stats-section");
    if (statsSection) statsObserver.observe(statsSection);

    // ── 3. SCROLL REVEAL (Phase 12) ──
    const sr12Observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("sr-visible");
                sr12Observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08 });

    document.querySelectorAll(".scroll-reveal").forEach(el => sr12Observer.observe(el));
});
"""

with open(js_path, "a", encoding="utf-8") as f:
    f.write(phase12_js)

print("JS appended.")
print("Phase 12 complete! Reload index.html.")
