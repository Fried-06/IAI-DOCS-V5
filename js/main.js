/**
 * IAI Resources - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {

    // ═══════════════════════════════════════════════════════
    // AUTH STATE CHECK — Dynamic navbar based on session
    // ═══════════════════════════════════════════════════════
    (function checkAuthState() {
        fetch('/backend/session_check.php')
            .then(res => res.json())
            .then(data => {
                // Find all nav-actions containers on the page
                const navActions = document.querySelectorAll('.nav-actions');
                navActions.forEach(container => {
                    // Find existing buttons
                    const loginBtn = container.querySelector('.auth-login-btn');
                    const registerBtn = container.querySelector('.auth-register-btn');
                    const profileBtn = container.querySelector('#btn-profil');

                    if (data.logged_in) {
                        // Hide login/register buttons
                        if (loginBtn) loginBtn.style.display = 'none';
                        if (registerBtn) registerBtn.style.display = 'none';
                        if (profileBtn) profileBtn.style.display = 'none';

                        // Check if avatar already injected
                        if (container.querySelector('.nav-avatar-group')) return;

                        // Create avatar + logout group
                        const avatarGroup = document.createElement('div');
                        avatarGroup.className = 'nav-avatar-group';
                        avatarGroup.style.cssText = 'display:flex;align-items:center;gap:0.75rem;';

                        // Avatar link (goes to profile)
                        const avatarLink = document.createElement('a');
                        avatarLink.href = '/profile.php';
                        avatarLink.className = 'nav-avatar-link';
                        avatarLink.title = data.username;
                        avatarLink.style.cssText = 'display:flex;align-items:center;gap:0.5rem;text-decoration:none;color:inherit;';

                        const avatarCircle = document.createElement('div');
                        avatarCircle.className = 'nav-avatar-circle';
                        avatarCircle.textContent = data.initials;
                        avatarCircle.style.cssText = 'width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--cyan,#00e5c4),var(--purple,#a855f7));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.8rem;letter-spacing:0.02em;box-shadow:0 0 12px rgba(0,229,196,0.3);';

                        const userName = document.createElement('span');
                        userName.textContent = data.username;
                        userName.className = 'nav-username';
                        userName.style.cssText = 'font-size:0.85rem;font-weight:600;color:var(--text,#c8ddf2);';

                        avatarLink.appendChild(avatarCircle);
                        avatarLink.appendChild(userName);

                        // Logout button
                        const logoutBtn = document.createElement('a');
                        logoutBtn.href = '/backend/logout.php';
                        logoutBtn.className = 'btn btn-outline nav-logout-btn';
                        logoutBtn.style.cssText = 'padding:0.4rem 0.8rem;font-size:0.8rem;border:1px solid rgba(255,100,100,0.3);color:#ff6b6b;border-radius:6px;text-decoration:none;transition:all 0.3s;';
                        logoutBtn.textContent = 'Déconnexion';
                        logoutBtn.addEventListener('mouseenter', () => {
                            logoutBtn.style.background = 'rgba(255,100,100,0.15)';
                            logoutBtn.style.borderColor = '#ff6b6b';
                        });
                        logoutBtn.addEventListener('mouseleave', () => {
                            logoutBtn.style.background = 'transparent';
                            logoutBtn.style.borderColor = 'rgba(255,100,100,0.3)';
                        });

                        avatarGroup.appendChild(avatarLink);
                        avatarGroup.appendChild(logoutBtn);
                        container.appendChild(avatarGroup);
                    } else {
                        // Not logged in — buttons already visible by default
                        if (loginBtn) loginBtn.style.display = '';
                        if (registerBtn) registerBtn.style.display = '';
                    }
                });
            })
            .catch(() => {
                // If fetch fails (e.g. no PHP server), keep default buttons visible
            });
    })();

    // 1. Fake Online Users Simulator
    const onlineIndicator = document.getElementById('online-count');
    
    if (onlineIndicator) {
        // Init with a random number between 8 and 25
        let nbUsers = Math.floor(Math.random() * 18) + 8;
        onlineIndicator.textContent = `${nbUsers} utilisateurs en ligne`;

        // Fluctuate slowly every few seconds
        setInterval(() => {
            // Small chance to increase or decrease by 1 or 2
            const change = Math.floor(Math.random() * 3) - 1; // -1, 0, or 1
            nbUsers += change;
            
            // Keep bounds realistic
            if (nbUsers < 3) nbUsers = 3;
            if (nbUsers > 45) nbUsers = 45;
            
            onlineIndicator.textContent = `${nbUsers} utilisateurs en ligne`;
        }, 5000); // every 5 seconds
    }

    // 2. Mobile Menu Toggle
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileBtn && navLinks) {
        mobileBtn.addEventListener('click', () => {
            // Very simple toggle logic, in a real app this would use a proper off-canvas menu
            if (navLinks.style.display === 'flex') {
                navLinks.style.display = 'none';
            } else {
                navLinks.style.display = 'flex';
                navLinks.style.flexDirection = 'column';
                navLinks.style.position = 'absolute';
                navLinks.style.top = '4.5rem';
                navLinks.style.left = '0';
                navLinks.style.right = '0';
                navLinks.style.backgroundColor = 'var(--bg-card)';
                navLinks.style.padding = '1rem';
                navLinks.style.boxShadow = 'var(--shadow-md)';
                navLinks.style.zIndex = '99';
            }
        });
    }

    // Window resize handler to fix nav links display mode
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768 && navLinks) {
            navLinks.style.display = 'flex';
            navLinks.style.flexDirection = 'row';
            navLinks.style.position = 'static';
            navLinks.style.padding = '0';
            navLinks.style.boxShadow = 'none';
        } else if (navLinks) {
            navLinks.style.display = 'none';
        }
    });
});

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


// --- PHASE 7: SCROLL REVEAL (IntersectionObserver) ---
document.addEventListener("DOMContentLoaded", () => {
    // Add page-fade-in to body on load (HTML script handles this usually, but guaranteeing it here too)
    // document.body.classList.add("page-fade-in");

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
                entry.target.classList.remove("scroll-reveal-hidden");
                sr12Observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08 });

    document.querySelectorAll(".scroll-reveal").forEach(el => {
        el.classList.add("scroll-reveal-hidden");
        sr12Observer.observe(el);
    });
});
