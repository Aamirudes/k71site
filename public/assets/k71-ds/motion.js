/**
 * K71 DESIGN SYSTEM — MOTION.JS
 * Declarative scroll-reveal, 3D card tilt, magnetic pull, and IntersectionObserver fallback.
 * Automatically respects prefers-reduced-motion and weak touch devices.
 */
(function() {
    'use strict';

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const isTouchDevice = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);

    // 1. Declarative Scroll Reveal with IntersectionObserver
    function initScrollReveals(root = document) {
        if (prefersReducedMotion) return;

        const revealElements = root.querySelectorAll('[data-reveal], h1:not([data-reveal]), h2:not([data-reveal]), .card:not([data-reveal])');
        if (!('IntersectionObserver' in window)) return;

        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const revealType = el.getAttribute('data-reveal') || 'up';
                    el.classList.add('k71-revealed');
                    el.style.opacity = '1';
                    el.style.transform = 'translate3d(0, 0, 0) scale(1)';
                    obs.unobserve(el);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        revealElements.forEach(el => {
            if (el.classList.contains('k71-reveal-init')) return;
            el.classList.add('k71-reveal-init');
            const type = el.getAttribute('data-reveal') || 'up';
            el.style.transition = 'opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), transform 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
            el.style.opacity = '0';
            if (type === 'up') el.style.transform = 'translate3d(0, 24px, 0)';
            else if (type === 'scale') el.style.transform = 'scale(0.96)';
            else if (type === 'fade') el.style.transform = 'none';
            observer.observe(el);
        });
    }

    // 2. 3D Card Tilt with Pointer Follow
    function initCardTilt() {
        if (prefersReducedMotion || isTouchDevice) return;

        document.addEventListener('mousemove', (e) => {
            const card = e.target.closest('[data-tilt], .k71-tilt');
            if (!card) return;

            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;

            const rotateX = ((y - centerY) / centerY) * -6; // max 6 deg
            const rotateY = ((x - centerX) / centerX) * 6;

            card.style.transform = `perspective(1000px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) scale3d(1.01, 1.01, 1.01)`;
            card.style.transition = 'transform 0.1s ease-out';
        }, { passive: true });

        document.addEventListener('mouseout', (e) => {
            const card = e.target.closest('[data-tilt], .k71-tilt');
            if (!card) return;
            card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
            card.style.transition = 'transform 0.4s ease-out';
        }, { passive: true });
    }

    // 3. MutationObserver for Dynamic / AJAX Injected Content
    function initMutationObserver() {
        if (!('MutationObserver' in window)) return;
        const observer = new MutationObserver((mutations) => {
            for (const m of mutations) {
                if (m.addedNodes.length > 0) {
                    m.addedNodes.forEach(node => {
                        if (node.nodeType === 1) {
                            initScrollReveals(node);
                        }
                    });
                }
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    // Lifecycle Init
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initScrollReveals();
            initCardTilt();
            initMutationObserver();
        });
    } else {
        initScrollReveals();
        initCardTilt();
        initMutationObserver();
    }
})();
