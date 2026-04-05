/* ══════════════════════════════════════════════════════════
   FOOTER REVEAL — Interaction Controller
   IAI-DOCS · 2026
   ══════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  // ── 1. INTERSECTION OBSERVER — Entrance animations ──
  const brandPanel = document.getElementById('footer-brand-reveal');

  if (brandPanel) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            brandPanel.classList.add('is-visible');
          } else {
            // Remove class when fully out of view for re-trigger on re-entry
            brandPanel.classList.remove('is-visible');
          }
        });
      },
      {
        threshold: 0.15,
        rootMargin: '0px 0px -50px 0px',
      }
    );

    observer.observe(brandPanel);
  }

  // ── 2. BACK TO TOP ──
  const backToTopBtn = document.getElementById('back-to-top');

  if (backToTopBtn) {
    backToTopBtn.addEventListener('click', (e) => {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // ── 3. MAGNETIC LETTER PROXIMITY EFFECT ──
  const wordmark = document.getElementById('footer-wordmark');

  if (wordmark) {
    const letters = wordmark.querySelectorAll('.wordmark-letter');
    let rafId = null;
    let mouseX = 0;
    let mouseY = 0;
    let isActive = false;

    // Only enable on devices with fine pointer (no touch)
    const hasFinePointer = window.matchMedia('(pointer: fine)').matches;

    if (hasFinePointer) {
      wordmark.addEventListener(
        'mousemove',
        (e) => {
          mouseX = e.clientX;
          mouseY = e.clientY;

          if (!isActive) {
            isActive = true;
            updateLetterPositions();
          }
        },
        { passive: true }
      );

      wordmark.addEventListener(
        'mouseleave',
        () => {
          isActive = false;
          if (rafId) {
            cancelAnimationFrame(rafId);
            rafId = null;
          }

          // Reset all letters smoothly
          letters.forEach((letter) => {
            letter.style.setProperty('--dx', '0');
            letter.style.setProperty('--dy', '0');
          });
        },
        { passive: true }
      );
    }

    function updateLetterPositions() {
      if (!isActive) return;

      letters.forEach((letter) => {
        const rect = letter.getBoundingClientRect();
        const letterCenterX = rect.left + rect.width / 2;
        const letterCenterY = rect.top + rect.height / 2;

        const distX = mouseX - letterCenterX;
        const distY = mouseY - letterCenterY;
        const distance = Math.sqrt(distX * distX + distY * distY);

        // Influence radius — letters within this range are affected
        const radius = 200;
        const maxDisplacement = 6;

        if (distance < radius) {
          // Inverse relationship: closer = more displacement, pushed away
          const force = (1 - distance / radius) * maxDisplacement;
          const angle = Math.atan2(distY, distX);

          // Push away from cursor
          const dx = -Math.cos(angle) * force;
          const dy = -Math.sin(angle) * force;

          letter.style.setProperty('--dx', dx.toFixed(2));
          letter.style.setProperty('--dy', dy.toFixed(2));
        } else {
          letter.style.setProperty('--dx', '0');
          letter.style.setProperty('--dy', '0');
        }
      });

      rafId = requestAnimationFrame(updateLetterPositions);
    }
  }
})();
