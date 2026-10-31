/**
 * K71 DESIGN SYSTEM — LIQUID.JS (LEAN & POLISHED)
 * Subtle click feedback only, respects prefers-reduced-motion.
 */
(function() {
    'use strict';

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function initRipples() {
        if (prefersReducedMotion) return;
        document.addEventListener('click', (e) => {
            const target = e.target.closest('.k71-btn, .btn-primary, .btn-accent');
            if (!target) return;

            const rect = target.getBoundingClientRect();
            const circle = document.createElement('span');
            const diameter = Math.max(rect.width, rect.height);
            const radius = diameter / 2;

            circle.style.width = circle.style.height = `${diameter}px`;
            circle.style.left = `${e.clientX - rect.left - radius}px`;
            circle.style.top = `${e.clientY - rect.top - radius}px`;
            circle.classList.add('k71-ripple');

            const existing = target.querySelector('.k71-ripple');
            if (existing) existing.remove();

            target.appendChild(circle);
            setTimeout(() => circle.remove(), 350);
        }, { passive: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRipples);
    } else {
        initRipples();
    }
})();
