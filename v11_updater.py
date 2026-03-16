import os
import re

base_dir = r"C:\Users\MSI\Music\IAI_MENTORIA\iai-resources"
index_path = os.path.join(base_dir, "index.html")
css_path = os.path.join(base_dir, "css", "style.css")
js_path = os.path.join(base_dir, "js", "main.js")

# ═══════════════════════════════════════════════════════
# 1. TIMELINE HTML — inject before </main>
# ═══════════════════════════════════════════════════════
timeline_html = """
        <!-- ═══ Vision Timeline Section ═══ -->
        <section class="section timeline-section" id="vision">
            <div class="container">
                <h2 class="section-title" style="font-family:'Bebas Neue',sans-serif; font-size: clamp(2.5rem,5vw,4rem); letter-spacing:0.04em;">Notre Vision</h2>
                <p style="text-align:center; color:var(--muted); font-family:'JetBrains Mono',monospace; font-size:0.8rem; letter-spacing:0.08em; margin-bottom:3rem;">DE L'IDÉE À LA RÉALITÉ</p>

                <div class="timeline">
                    <!-- The animated vertical line -->
                    <div class="timeline-line">
                        <div class="timeline-line-fill" id="timeline-fill"></div>
                    </div>

                    <!-- Step 1 — Left -->
                    <div class="timeline-item tl-left tl-reveal">
                        <div class="timeline-node"></div>
                        <div class="timeline-card">
                            <div class="timeline-img-wrap">
                                <img src="images/image1.png" alt="Problème" loading="lazy">
                            </div>
                            <span class="timeline-step">ÉTAPE 01</span>
                            <h3>Des ressources dispersées</h3>
                            <p>Les cours, TD et examens sont souvent éparpillés entre différents groupes et plateformes, ce qui rend la révision difficile.</p>
                        </div>
                    </div>

                    <!-- Step 2 — Right -->
                    <div class="timeline-item tl-right tl-reveal">
                        <div class="timeline-node"></div>
                        <div class="timeline-card">
                            <div class="timeline-img-wrap">
                                <img src="images/image2.png" alt="Centralisation" loading="lazy">
                            </div>
                            <span class="timeline-step">ÉTAPE 02</span>
                            <h3>Centraliser les ressources</h3>
                            <p>IAI Docs rassemble cours, TD, examens et explications pédagogiques en un seul endroit pour faciliter la révision.</p>
                        </div>
                    </div>

                    <!-- Step 3 — Left -->
                    <div class="timeline-item tl-left tl-reveal">
                        <div class="timeline-node"></div>
                        <div class="timeline-card">
                            <div class="timeline-img-wrap">
                                <img src="images/image3.png" alt="Communauté" loading="lazy">
                            </div>
                            <span class="timeline-step">ÉTAPE 03</span>
                            <h3>Une communauté étudiante</h3>
                            <p>Les étudiants contribuent ensemble en partageant leurs notes, leurs corrections et leurs explications.</p>
                        </div>
                    </div>

                    <!-- Step 4 — Right -->
                    <div class="timeline-item tl-right tl-reveal">
                        <div class="timeline-node"></div>
                        <div class="timeline-card">
                            <div class="timeline-img-wrap">
                                <img src="images/image4.png" alt="Intelligence" loading="lazy">
                            </div>
                            <span class="timeline-step">ÉTAPE 04</span>
                            <h3>Un hub d'apprentissage intelligent</h3>
                            <p>Grâce à l'intelligence artificielle, les étudiants pourront obtenir des explications, des exemples et une aide personnalisée directement à partir de leurs cours.</p>
                        </div>
                    </div>

                    <!-- Step 5 — Left -->
                    <div class="timeline-item tl-left tl-reveal">
                        <div class="timeline-node"></div>
                        <div class="timeline-card">
                            <div class="timeline-img-wrap">
                                <img src="images/image5.png" alt="Expansion" loading="lazy">
                            </div>
                            <span class="timeline-step">ÉTAPE 05</span>
                            <h3>Une plateforme pour tous les IAI</h3>
                            <p>L'objectif est d'étendre la plateforme pour regrouper les ressources de toutes les représentations IAI.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
"""

with open(index_path, "r", encoding="utf-8") as f:
    content = f.read()

# Insert timeline before </main>
content = content.replace("    </main>", timeline_html + "\n    </main>")

with open(index_path, "w", encoding="utf-8") as f:
    f.write(content)

# ═══════════════════════════════════════════════════════
# 2. CSS — Timeline + Modern Navbar
# ═══════════════════════════════════════════════════════
new_css = """

/* ═══════════════════════════════════════════════════════
   PHASE 11: TIMELINE SECTION
   ═══════════════════════════════════════════════════════ */

.timeline-section {
  padding: 6rem 0;
  position: relative;
  overflow: hidden;
  background:
    radial-gradient(ellipse 60% 40% at 50% 0%, rgba(0,229,196,0.04) 0%, transparent 60%),
    var(--bg);
}

/* ── Timeline Container ── */
.timeline {
  position: relative;
  max-width: 900px;
  margin: 0 auto;
  padding: 2rem 0;
}

/* ── Center Vertical Line ── */
.timeline-line {
  position: absolute;
  left: 50%;
  top: 0;
  bottom: 0;
  width: 2px;
  background: var(--border2);
  transform: translateX(-50%);
}
.timeline-line-fill {
  width: 100%;
  height: 0%;
  background: linear-gradient(180deg, var(--cyan), var(--purple));
  transition: height 0.05s linear;
  box-shadow: 0 0 12px rgba(0,229,196,0.4);
}

/* ── Timeline Items ── */
.timeline-item {
  position: relative;
  width: 50%;
  padding: 1.5rem 2.5rem;
}
.timeline-item.tl-left {
  left: 0;
  text-align: right;
  padding-right: 3.5rem;
}
.timeline-item.tl-right {
  left: 50%;
  text-align: left;
  padding-left: 3.5rem;
}

/* ── Glowing Node ── */
.timeline-node {
  position: absolute;
  top: 2.2rem;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: var(--cyan);
  border: 3px solid var(--bg);
  box-shadow: 0 0 12px rgba(0,229,196,0.6), 0 0 24px rgba(0,229,196,0.3);
  z-index: 2;
}
.tl-left .timeline-node {
  right: -8px;
}
.tl-right .timeline-node {
  left: -8px;
}
/* Alternate node colors */
.timeline-item:nth-child(2) .timeline-node { background: var(--amber); box-shadow: 0 0 12px rgba(255,183,3,0.6); }
.timeline-item:nth-child(3) .timeline-node { background: var(--pink); box-shadow: 0 0 12px rgba(255,77,141,0.6); }
.timeline-item:nth-child(4) .timeline-node { background: var(--purple); box-shadow: 0 0 12px rgba(168,85,247,0.6); }
.timeline-item:nth-child(5) .timeline-node { background: var(--green); box-shadow: 0 0 12px rgba(34,217,122,0.6); }

/* ── Timeline Card ── */
.timeline-card {
  background: var(--bg2);
  border: 1px solid var(--border2);
  border-radius: 8px;
  padding: 1.8rem;
  transition: border-color 0.3s, box-shadow 0.3s;
}
.timeline-card:hover {
  border-color: var(--cyan);
  box-shadow: 0 8px 30px rgba(0,229,196,0.1);
}
.timeline-item:nth-child(2) .timeline-card:hover { border-color: var(--amber); box-shadow: 0 8px 30px rgba(255,183,3,0.1); }
.timeline-item:nth-child(3) .timeline-card:hover { border-color: var(--pink); box-shadow: 0 8px 30px rgba(255,77,141,0.1); }
.timeline-item:nth-child(4) .timeline-card:hover { border-color: var(--purple); box-shadow: 0 8px 30px rgba(168,85,247,0.1); }
.timeline-item:nth-child(5) .timeline-card:hover { border-color: var(--green); box-shadow: 0 8px 30px rgba(34,217,122,0.1); }

/* ── Image inside card ── */
.timeline-img-wrap {
  width: 100%;
  height: 180px;
  border-radius: 6px;
  overflow: hidden;
  margin-bottom: 1.2rem;
  box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}
.timeline-img-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}
.timeline-card:hover .timeline-img-wrap img {
  transform: scale(1.05);
}

/* ── Card Typography ── */
.timeline-step {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.6rem;
  letter-spacing: 0.2em;
  color: var(--cyan);
  display: inline-block;
  margin-bottom: 0.5rem;
  border: 1px solid var(--cyan);
  padding: 0.2rem 0.6rem;
}
.timeline-item:nth-child(2) .timeline-step { color: var(--amber); border-color: var(--amber); }
.timeline-item:nth-child(3) .timeline-step { color: var(--pink); border-color: var(--pink); }
.timeline-item:nth-child(4) .timeline-step { color: var(--purple); border-color: var(--purple); }
.timeline-item:nth-child(5) .timeline-step { color: var(--green); border-color: var(--green); }

.timeline-card h3 {
  font-family: 'Bebas Neue', sans-serif;
  font-size: 1.6rem;
  letter-spacing: 0.03em;
  color: #fff;
  margin-bottom: 0.5rem;
}
.timeline-card p {
  font-size: 0.88rem;
  color: var(--muted);
  line-height: 1.7;
}

/* ── Scroll Reveal for timeline cards ── */
.tl-reveal {
  opacity: 0;
  transition: opacity 0.7s ease, transform 0.7s ease;
}
.tl-reveal.tl-left {
  transform: translateX(-40px);
}
.tl-reveal.tl-right {
  transform: translateX(40px);
}
.tl-reveal.tl-visible {
  opacity: 1;
  transform: translateX(0);
}

/* ── Responsive: Mobile ── */
@media (max-width: 768px) {
  .timeline-line { left: 20px; }
  .timeline-item { width: 100%; left: 0 !important; text-align: left !important; padding-left: 50px !important; padding-right: 1rem !important; }
  .timeline-node { left: 12px !important; right: auto !important; }
  .tl-reveal.tl-left, .tl-reveal.tl-right { transform: translateX(30px); }
}


/* ═══════════════════════════════════════════════════════
   PHASE 11: MODERN FLOATING NAVBAR (Glassmorphism)
   ═══════════════════════════════════════════════════════ */

.navbar {
  position: fixed !important;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  padding: 0.6rem 0 !important;
  background: transparent !important;
  border-bottom: none !important;
  transition: transform 0.35s cubic-bezier(0.4,0,0.2,1),
              background 0.35s ease,
              box-shadow 0.35s ease,
              backdrop-filter 0.35s ease;
}

/* When scrolled: glassmorphism */
.navbar.nav-scrolled {
  background: rgba(4, 12, 24, 0.75) !important;
  -webkit-backdrop-filter: blur(16px) saturate(180%);
  backdrop-filter: blur(16px) saturate(180%);
  box-shadow: 0 1px 0 rgba(21,37,64,0.5), 0 8px 30px rgba(0,0,0,0.25) !important;
  border-bottom: 1px solid rgba(21,37,64,0.4) !important;
}

/* Hidden state: slide up */
.navbar.nav-hidden {
  transform: translateY(-100%);
}

/* Compensate for fixed navbar on body */
body {
  padding-top: 0 !important; /* Hero is full-height anyway */
}

/* ── Nav links styling ── */
.nav-item {
  font-family: 'JetBrains Mono', monospace !important;
  font-size: 0.75rem !important;
  letter-spacing: 0.08em !important;
  text-transform: uppercase !important;
  color: var(--muted) !important;
  transition: color 0.2s ease !important;
  text-decoration: none !important;
}
.nav-item:hover {
  color: #fff !important;
}

/* ── Mobile hamburger drawer ── */
@media (max-width: 768px) {
  .nav-links {
    position: fixed;
    top: 0;
    right: -100%;
    width: 280px;
    height: 100vh;
    background: rgba(4,12,24,0.95);
    backdrop-filter: blur(20px);
    flex-direction: column;
    padding: 5rem 2rem 2rem;
    transition: right 0.35s cubic-bezier(0.4,0,0.2,1);
    z-index: 999;
    border-left: 1px solid var(--border2);
  }
  .nav-links.nav-open {
    right: 0;
  }
  .nav-menu {
    flex-direction: column;
    gap: 1.5rem !important;
  }
  .nav-actions {
    flex-direction: column;
    gap: 1rem;
    margin-top: 2rem;
  }
  /* Overlay */
  .nav-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 998;
  }
  .nav-overlay.active {
    display: block;
  }
}
"""

with open(css_path, "a", encoding="utf-8") as f:
    f.write(new_css)

# ═══════════════════════════════════════════════════════
# 3. JAVASCRIPT — Timeline Fill + Navbar Scroll Behavior
# ═══════════════════════════════════════════════════════
new_js = """

// ═══════════════════════════════════════════════════════
// PHASE 11: TIMELINE SCROLL FILL + CARD REVEAL
// ═══════════════════════════════════════════════════════

document.addEventListener("DOMContentLoaded", () => {
    const timelineFill = document.getElementById("timeline-fill");
    const timelineSection = document.querySelector(".timeline");
    const tlItems = document.querySelectorAll(".tl-reveal");

    // --- Timeline line fill on scroll ---
    if (timelineFill && timelineSection) {
        window.addEventListener("scroll", () => {
            const rect = timelineSection.getBoundingClientRect();
            const sectionTop = rect.top;
            const sectionHeight = rect.height;
            const windowH = window.innerHeight;

            // Calculate fill percentage
            if (sectionTop < windowH && rect.bottom > 0) {
                const scrolled = windowH - sectionTop;
                const pct = Math.min(Math.max((scrolled / sectionHeight) * 100, 0), 100);
                timelineFill.style.height = pct + "%";
            }
        }, { passive: true });
    }

    // --- Timeline card reveal with IntersectionObserver ---
    if (tlItems.length > 0) {
        const tlObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("tl-visible");
                    tlObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });

        tlItems.forEach(item => tlObserver.observe(item));
    }
});


// ═══════════════════════════════════════════════════════
// PHASE 11: MODERN NAVBAR SCROLL BEHAVIOR
// ═══════════════════════════════════════════════════════

(function() {
    const navbar = document.querySelector(".navbar");
    if (!navbar) return;

    let lastScrollY = 0;
    let ticking = false;
    const scrollThreshold = 80;  // px before glassmorphism activates
    const hideThreshold = 300;   // px before hide-on-scroll-down activates

    function updateNavbar() {
        const currentY = window.scrollY;

        // Glassmorphism when scrolled past threshold
        if (currentY > scrollThreshold) {
            navbar.classList.add("nav-scrolled");
        } else {
            navbar.classList.remove("nav-scrolled");
        }

        // Hide on scroll down, show on scroll up
        if (currentY > hideThreshold && currentY > lastScrollY) {
            navbar.classList.add("nav-hidden");
        } else {
            navbar.classList.remove("nav-hidden");
        }

        lastScrollY = currentY;
        ticking = false;
    }

    window.addEventListener("scroll", () => {
        if (!ticking) {
            requestAnimationFrame(updateNavbar);
            ticking = true;
        }
    }, { passive: true });

    // --- Mobile hamburger ---
    const menuBtn = document.querySelector(".mobile-menu-btn");
    const navLinks = document.querySelector(".nav-links");

    if (menuBtn && navLinks) {
        // Create overlay element
        let overlay = document.querySelector(".nav-overlay");
        if (!overlay) {
            overlay = document.createElement("div");
            overlay.className = "nav-overlay";
            document.body.appendChild(overlay);
        }

        menuBtn.addEventListener("click", () => {
            navLinks.classList.toggle("nav-open");
            overlay.classList.toggle("active");
        });

        overlay.addEventListener("click", () => {
            navLinks.classList.remove("nav-open");
            overlay.classList.remove("active");
        });
    }
})();
"""

with open(js_path, "a", encoding="utf-8") as f:
    f.write(new_js)

print("Phase 11 injected: Timeline + Modern Navbar.")
