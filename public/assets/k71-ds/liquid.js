/**
 * K71 DESIGN SYSTEM — LIQUID.JS
 * Micro-interactions: dynamic pointer water ripples, hover-fill coords,
 * and gooey slider position tracking.
 */
(function() {
    'use strict';

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // 1. Water Ripple Effect
    function initRipples() {
        document.addEventListener('click', (e) => {
            const target = e.target.closest('.k71-btn, .btn, .k71-clickable, .k71-glass-2');
            if (!target || prefersReducedMotion) return;

            const rect = target.getBoundingClientRect();
            const circle = document.createElement('span');
            const diameter = Math.max(rect.width, rect.height);
            const radius = diameter / 2;

            circle.style.width = circle.style.height = `${diameter}px`;
            circle.style.left = `${e.clientX - rect.left - radius}px`;
            circle.style.top = `${e.clientY - rect.top - radius}px`;
            circle.classList.add('k71-ripple');

            const existingRipple = target.querySelector('.k71-ripple');
            if (existingRipple) existingRipple.remove();

            target.appendChild(circle);
            setTimeout(() => circle.remove(), 600);
        }, { passive: true });
    }

    // 2. Liquid Hover Position Tracking
    function initLiquidButtons() {
        if (prefersReducedMotion) return;
        document.addEventListener('mousemove', (e) => {
            const btn = e.target.closest('.k71-btn-liquid');
            if (!btn) return;
            const rect = btn.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            btn.style.setProperty('--liquid-x', `${x}px`);
            btn.style.setProperty('--liquid-y', `${y}px`);
        }, { passive: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initRipples();
            initLiquidButtons();
        });
    } else {
        initRipples();
        initLiquidButtons();
    }
})();
