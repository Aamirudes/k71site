/**
 * K71 DESIGN SYSTEM — MOTION.JS (TONED DOWN & LAUNCH-POLISHED)
 * Subtle, native-app feel:
 * - Fade + 8px rise on explicit section reveal (250ms, once)
 * - Sticky header shadow on scroll
 * - Add-to-cart count bounce
 * - Strictly respects prefers-reduced-motion
 * - NO 3D tilt, NO magnetic pulls, NO custom cursors, NO hiding elements by default
 */
(function() {
    'use strict';

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // 1. Subtle Section Reveal (Fade + 8px rise, 250ms, once)
    function initSectionReveals(root = document) {
        if (prefersReducedMotion || !('IntersectionObserver' in window)) return;

        const revealElements = root.querySelectorAll('[data-reveal], .k71-reveal');
        if (revealElements.length === 0) return;

        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    el.style.opacity = '1';
                    el.style.transform = 'translate3d(0, 0, 0)';
                    obs.unobserve(el);
                }
            });
        }, {
            threshold: 0.05,
            rootMargin: '0px 0px -40px 0px'
        });

        revealElements.forEach(el => {
            if (el.dataset.revealInit) return;
            el.dataset.revealInit = 'true';
            el.style.transition = 'opacity 250ms cubic-bezier(0.16, 1, 0.3, 1), transform 250ms cubic-bezier(0.16, 1, 0.3, 1)';
            el.style.opacity = '0';
            el.style.transform = 'translate3d(0, 8px, 0)';
            observer.observe(el);
        });
    }

    // 2. Sticky Header Shadow on Scroll
    function initHeaderShadow() {
        const header = document.querySelector('header.sticky, .k71-sticky-header');
        if (!header) return;

        let ticking = false;
        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    if (window.scrollY > 8) {
                        header.classList.add('shadow-sm');
                    } else {
                        header.classList.remove('shadow-sm');
                    }
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
    }

    // 3. Add to Cart Badge Bounce Event Listener
    function initCartBounce() {
        if (prefersReducedMotion) return;
        window.addEventListener('cart-count-updated', (e) => {
            const badges = document.querySelectorAll('[data-cart-badge], .my-cart-badge, #nav-cart-count');
            badges.forEach(badge => {
                badge.style.transition = 'transform 150ms cubic-bezier(0.34, 1.56, 0.64, 1)';
                badge.style.transform = 'scale(1.25)';
                setTimeout(() => {
                    badge.style.transform = 'scale(1)';
                }, 160);
            });
        });
    }

    // Lifecycle Init
    function init() {
        initSectionReveals();
        initHeaderShadow();
        initCartBounce();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
