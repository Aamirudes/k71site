/**
 * K71 Storefront Theme Motion & Interaction Engine
 * Lightweight, accessible, reduced-motion compliant, zero scroll hijacking.
 */
(function () {
  'use strict';

  const root = document.querySelector('.k71-sf-theme');
  if (!root) return;

  const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const motionIntensity = root.dataset.motionIntensity || 'normal';
  const reduced = prefersReducedMotion || motionIntensity === 'reduced';
  const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
  const staggerMs = parseInt(root.dataset.motionStagger || '45', 10);

  if (!reduced) {
    root.classList.add('k71-js-ready');
  }

  // 1. Viewport Reveal via IntersectionObserver (non-blocking)
  const revealEls = Array.from(root.querySelectorAll('[data-k71-reveal]'));
  if (reduced || !('IntersectionObserver' in window)) {
    revealEls.forEach((el) => el.classList.add('is-visible'));
  } else {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const delay = Math.min(parseInt(entry.target.dataset.k71Delay || '0', 10), 280);
            setTimeout(() => entry.target.classList.add('is-visible'), delay);
            io.unobserve(entry.target);
          }
        });
      },
      { rootMargin: '0px 0px -40px 0px', threshold: 0.04 }
    );

    revealEls.forEach((el, idx) => {
      if (!el.dataset.k71Delay) {
        el.dataset.k71Delay = String((idx % 4) * (motionIntensity === 'subtle' ? Math.round(staggerMs * 0.5) : staggerMs));
      }
      io.observe(el);
    });
  }

  // 2. Sticky Header Scrolled State
  const header = root.querySelector('.k71-header');
  if (header) {
    let ticking = false;
    const onScroll = () => {
      if (!ticking) {
        window.requestAnimationFrame(() => {
          header.dataset.scrolled = window.scrollY > 24 ? 'true' : 'false';
          ticking = false;
        });
        ticking = true;
      }
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // 3. Subtle Desktop-Only Parallax (never hijacks scroll)
  if (!reduced && !isTouchDevice && window.innerWidth >= 1024 && root.dataset.enableParallax === 'true') {
    const parallaxEls = Array.from(root.querySelectorAll('[data-k71-parallax]'));
    if (parallaxEls.length) {
      let raf = false;
      const updateParallax = () => {
        const vh = window.innerHeight;
        parallaxEls.forEach((el) => {
          const rect = el.getBoundingClientRect();
          if (rect.bottom >= 0 && rect.top <= vh) {
            const ratio = (rect.top - vh / 2) / vh;
            const shift = Math.max(-24, Math.min(24, ratio * -26));
            el.style.transform = 'translate3d(0,' + shift.toFixed(1) + 'px,0)';
          }
        });
        raf = false;
      };
      window.addEventListener('scroll', () => {
        if (!raf) {
          raf = true;
          window.requestAnimationFrame(updateParallax);
        }
      }, { passive: true });
    }
  }

  // 4. Desktop-Only Pointer Tilt (Obsidian Glass, Future Interface, Liquid Motion, Orbital 3D, Signature 71)
  if (!reduced && !isTouchDevice && window.innerWidth >= 1024 && root.dataset.enableTilt === 'true') {
    root.querySelectorAll('[data-k71-tilt]').forEach((card) => {
      card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width - 0.5;
        const y = (e.clientY - rect.top) / rect.height - 0.5;
        card.style.transform = `perspective(900px) rotateX(${(-y * 5).toFixed(2)}deg) rotateY(${(x * 5).toFixed(2)}deg) translateY(-2px)`;
      });
      card.addEventListener('mouseleave', () => {
        card.style.transform = '';
      });
    });
  }

  // 5. Accessible Dialogs & Overlays (Escape to close + focus return)
  let lastTrigger = null;
  function openModal(id, trigger) {
    const el = document.getElementById(id);
    if (!el) return;
    lastTrigger = trigger || document.activeElement;
    el.classList.remove('hidden');
    el.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    const focusable = el.querySelector('input, button, a, select, textarea, [tabindex]:not([tabindex="-1"])');
    if (focusable) focusable.focus();
  }

  function closeModal(el) {
    if (!el) return;
    el.classList.add('hidden');
    el.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    if (lastTrigger && typeof lastTrigger.focus === 'function') {
      lastTrigger.focus();
    }
  }

  document.addEventListener('click', (e) => {
    const openBtn = e.target.closest('[data-k71-open]');
    if (openBtn) {
      e.preventDefault();
      openModal(openBtn.dataset.k71Open, openBtn);
      return;
    }

    const closeBtn = e.target.closest('[data-k71-close]');
    if (closeBtn) {
      e.preventDefault();
      const targetId = closeBtn.dataset.k71Close;
      const modal = targetId ? document.getElementById(targetId) : closeBtn.closest('[data-k71-modal]');
      closeModal(modal);
      return;
    }

    const lightboxBtn = e.target.closest('[data-k71-lightbox-src]');
    if (lightboxBtn) {
      e.preventDefault();
      const img = document.getElementById('k71-lightbox-img');
      const caption = document.getElementById('k71-lightbox-caption');
      if (img) {
        img.src = lightboxBtn.dataset.k71LightboxSrc;
        img.alt = lightboxBtn.dataset.k71LightboxAlt || 'Product image';
      }
      if (caption) caption.textContent = lightboxBtn.dataset.k71LightboxAlt || '';
      openModal('k71-lightbox-modal', lightboxBtn);
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      document.querySelectorAll('[data-k71-modal]:not(.hidden)').forEach((m) => closeModal(m));
    }
  });

  // 6. Real Cart Add via K71 Backend Endpoint (never fakes success before server confirms)
  function showToast(message, isError) {
    const toast = document.getElementById('k71-theme-toast');
    const text = document.getElementById('k71-theme-toast-text');
    if (!toast || !text) return;
    text.textContent = message;
    toast.dataset.state = isError ? 'error' : 'success';
    toast.classList.remove('opacity-0', 'translate-y-4', 'pointer-events-none');
    clearTimeout(toast._t);
    toast._t = setTimeout(() => {
      toast.classList.add('opacity-0', 'translate-y-4', 'pointer-events-none');
    }, 3600);
  }

  document.addEventListener('submit', async (e) => {
    const form = e.target.closest('[data-k71-cart-form]');
    if (!form) return;
    e.preventDefault();

    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.textContent : '';
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Adding...';
    }

    try {
      const formData = new FormData(form);
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content || formData.get('_token') || '';
      const res = await fetch(form.action, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrf,
        },
        body: formData,
        credentials: 'same-origin',
      });

      const data = await res.json().catch(() => ({}));
      if (!res.ok || data.success === false) {
        showToast(data.message || 'Unable to add item to bag. Please check availability.', true);
      } else {
        showToast(data.message || 'Added to your bag.', false);
        if (typeof data.cart_count !== 'undefined') {
          document.querySelectorAll('[data-k71-cart-count]').forEach((badge) => {
            badge.textContent = String(data.cart_count);
            badge.classList.remove('hidden');
          });
        }
        if (form.dataset.k71BuyNow === 'true' && form.dataset.k71CheckoutUrl) {
          window.location.href = form.dataset.k71CheckoutUrl;
        }
      }
    } catch (err) {
      showToast('Network error while adding to bag. Please try again.', true);
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      }
    }
  });

  // 7. Optional Vendor-Controlled Promotional Popup (frequency-capped via sessionStorage, never intrusive)
  const promoModal = document.getElementById('k71-promo-modal');
  if (promoModal && promoModal.dataset.enabled === 'true') {
    const storageKey = 'k71_promo_seen_' + (promoModal.dataset.vendorId || 'store');
    if (!sessionStorage.getItem(storageKey)) {
      const delaySec = Math.max(4, parseInt(promoModal.dataset.delay || '8', 10));
      setTimeout(() => {
        if (!document.querySelector('[data-k71-modal]:not(.hidden)')) {
          sessionStorage.setItem(storageKey, '1');
          openModal('k71-promo-modal', null);
        }
      }, delaySec * 1000);
    }
  }

  // 8. Real Release Countdown (ONLY when a real configured deadline exists)
  root.querySelectorAll('[data-k71-countdown]').forEach((el) => {
    const target = Date.parse(el.dataset.k71Countdown || '');
    if (isNaN(target)) return;
    const tick = () => {
      const diff = target - Date.now();
      if (diff <= 0) {
        el.textContent = 'Available Now';
        return;
      }
      const d = Math.floor(diff / 86400000);
      const h = Math.floor((diff % 86400000) / 3600000);
      const m = Math.floor((diff % 3600000) / 60000);
      const s = Math.floor((diff % 60000) / 1000);
      el.textContent = `${d}d ${String(h).padStart(2, '0')}h ${String(m).padStart(2, '0')}m ${String(s).padStart(2, '0')}s`;
    };
    tick();
    setInterval(tick, 1000);
  });
})();
