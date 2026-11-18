/**
 * K71 Storefront Theme Engine — GSAP 3, ScrollTrigger, CSS 3D & Commerce Interactions
 * Supports 12 distinct art-directed motion choreographies, desktop-only 3D perspective/specular depth,
 * reduced-motion compliance, full ScrollTrigger cleanup, and live K71 cart/checkout/modal workflows.
 */
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

function initK71ThemeEngine() {
    const root = document.querySelector('.k71-sf-theme');
    if (!root || root.dataset.k71MotionInit === '1') return;
    root.dataset.k71MotionInit = '1';

    const choreography = root.dataset.motionChoreography || 'quiet-editorial';
    const depth3d = root.dataset.depth3d || 'none';
    const motionIntensity = root.dataset.motionIntensity || 'normal';
    const prefersReduced = Boolean(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    const reduced = prefersReduced || motionIntensity === 'reduced';
    const isTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    const speedScale = motionIntensity === 'subtle' ? 0.65 : 1;

    // ── 1. Sticky Header Scrolled State + Top Scroll Progress Hairline + Back-to-Top ──
    const header = root.querySelector('.k71-header');
    const progressEl = document.getElementById('k71-scroll-progress');
    const backToTopBtn = document.getElementById('k71-back-to-top');
    let onHeaderScroll = null;
    if (header || progressEl || backToTopBtn) {
        let ticking = false;
        onHeaderScroll = () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    const sy = window.scrollY || 0;
                    if (header) {
                        header.dataset.scrolled = sy > 24 ? 'true' : 'false';
                    }
                    if (progressEl) {
                        const maxScroll = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
                        const ratio = Math.max(0, Math.min(1, sy / maxScroll));
                        progressEl.style.transform = `scaleX(${ratio.toFixed(4)})`;
                    }
                    if (backToTopBtn) {
                        backToTopBtn.classList.toggle('is-visible', sy > 480);
                    }
                    ticking = false;
                });
                ticking = true;
            }
        };
        window.addEventListener('scroll', onHeaderScroll, { passive: true });
        onHeaderScroll();
    }

    const onBackToTopClick = () => {
        window.scrollTo({ top: 0, behavior: reduced ? 'auto' : 'smooth' });
    };
    if (backToTopBtn) {
        backToTopBtn.addEventListener('click', onBackToTopClick);
    }

    // ── 2. GSAP Context + MatchMedia Choreography Engine ──────────────────────
    let gsapCtx = null;
    const pointerCleanups = [];

    if (!reduced) {
        root.classList.add('k71-gsap-active');

        gsapCtx = gsap.context(() => {
            const mm = gsap.matchMedia();

            // A. Hero Entrance Timeline (Masked Word-Curtain Reveal + Tailored Choreography)
            const heroRoot = root.querySelector('[data-k71-hero-stage]');
            if (heroRoot) {
                const rawHeroTitles = heroRoot.querySelectorAll('[data-k71-hero-title]');
                const heroWords = [];
                rawHeroTitles.forEach((h) => {
                    if (h.children.length === 0 && h.textContent.trim().length > 0 && h.textContent.trim().length < 90) {
                        const orig = h.textContent.trim();
                        h.setAttribute('aria-label', orig);
                        h.innerHTML = orig.split(/\s+/).map((w) => `<span class="k71-word-mask"><span class="k71-word-inner">${w.replace(/</g, '&lt;')}</span></span>`).join(' ');
                        h.querySelectorAll('.k71-word-inner').forEach((wEl) => heroWords.push(wEl));
                    }
                });
                const heroTitles = heroWords.length ? heroWords : rawHeroTitles;
                const heroMeta = heroRoot.querySelectorAll('[data-k71-hero-meta]');
                const heroMedia = heroRoot.querySelectorAll('[data-k71-hero-media]');
                const heroLines = heroRoot.querySelectorAll('[data-k71-hairline]');

                const tl = gsap.timeline({ defaults: { overwrite: 'auto' } });

                switch (choreography) {
                    case 'cinematic-curtain':
                        if (heroMedia.length) {
                            tl.fromTo(heroMedia, { scale: 1.14, opacity: 0.2 }, { scale: 1, opacity: 1, duration: 1.35 * speedScale, ease: 'power3.out' }, 0);
                        }
                        if (heroTitles.length) {
                            tl.from(heroTitles, { y: 42, opacity: 0, letterSpacing: '0.06em', duration: 0.95 * speedScale, stagger: 0.08, ease: 'expo.out' }, 0.15);
                        }
                        if (heroMeta.length) {
                            tl.from(heroMeta, { y: 18, opacity: 0, duration: 0.7 * speedScale, stagger: 0.07, ease: 'power2.out' }, 0.35);
                        }
                        break;

                    case 'swiss-grid-snap':
                        if (heroLines.length) {
                            tl.from(heroLines, { scaleX: 0, transformOrigin: 'left center', duration: 0.55 * speedScale, stagger: 0.06, ease: 'power4.out' }, 0);
                        }
                        if (heroTitles.length) {
                            tl.from(heroTitles, { y: 24, opacity: 0, duration: 0.5 * speedScale, stagger: 0.05, ease: 'power4.out' }, 0.08);
                        }
                        if (heroMedia.length) {
                            tl.fromTo(heroMedia, { clipPath: 'inset(0% 100% 0% 0%)' }, { clipPath: 'inset(0% 0% 0% 0%)', duration: 0.75 * speedScale, ease: 'power4.inOut' }, 0.12);
                        }
                        if (heroMeta.length) {
                            tl.from(heroMeta, { opacity: 0, y: 10, duration: 0.45 * speedScale, stagger: 0.05, ease: 'power3.out' }, 0.25);
                        }
                        break;

                    case 'brutalist-kinetic':
                        if (heroTitles.length) {
                            tl.from(heroTitles, { x: -28, opacity: 0, duration: 0.42 * speedScale, stagger: 0.04, ease: 'power4.out' }, 0);
                        }
                        if (heroMedia.length) {
                            tl.from(heroMedia, { scale: 0.95, opacity: 0, duration: 0.48 * speedScale, ease: 'power3.out' }, 0.08);
                        }
                        if (heroMeta.length) {
                            tl.from(heroMeta, { y: 14, opacity: 0, duration: 0.35 * speedScale, stagger: 0.04, ease: 'power4.out' }, 0.14);
                        }
                        break;

                    case 'architectural-3d':
                        if (heroMedia.length) {
                            tl.from(heroMedia, { rotateY: -8, rotateX: 5, scale: 0.94, opacity: 0, transformPerspective: 1200, duration: 1.05 * speedScale, ease: 'power3.out' }, 0);
                        }
                        if (heroTitles.length) {
                            tl.from(heroTitles, { y: 32, opacity: 0, duration: 0.85 * speedScale, stagger: 0.07, ease: 'power3.out' }, 0.12);
                        }
                        if (heroMeta.length) {
                            tl.from(heroMeta, { y: 16, opacity: 0, duration: 0.65 * speedScale, stagger: 0.06, ease: 'power2.out' }, 0.25);
                        }
                        break;

                    case 'spatial-glass-float':
                        if (heroMedia.length) {
                            tl.from(heroMedia, { y: 26, scale: 0.94, opacity: 0, duration: 0.9 * speedScale, ease: 'back.out(1.25)' }, 0);
                        }
                        if (heroTitles.length) {
                            tl.from(heroTitles, { y: 22, opacity: 0, duration: 0.75 * speedScale, stagger: 0.06, ease: 'power3.out' }, 0.1);
                        }
                        if (heroMeta.length) {
                            tl.from(heroMeta, { y: 14, opacity: 0, duration: 0.6 * speedScale, stagger: 0.06, ease: 'power2.out' }, 0.22);
                        }
                        break;

                    case 'sneaker-vault-3d':
                        if (heroMedia.length) {
                            tl.from(heroMedia, { rotateZ: -4, y: 28, scale: 0.9, opacity: 0, duration: 0.85 * speedScale, ease: 'expo.out' }, 0);
                        }
                        if (heroTitles.length) {
                            tl.from(heroTitles, { y: 34, opacity: 0, duration: 0.65 * speedScale, stagger: 0.05, ease: 'power4.out' }, 0.08);
                        }
                        if (heroMeta.length) {
                            tl.from(heroMeta, { opacity: 0, y: 12, duration: 0.5 * speedScale, stagger: 0.05, ease: 'power3.out' }, 0.2);
                        }
                        break;

                    case 'japanese-ma':
                        if (heroMedia.length) {
                            tl.fromTo(heroMedia, { opacity: 0, y: 14 }, { opacity: 1, y: 0, duration: 1.2 * speedScale, ease: 'power2.out' }, 0);
                        }
                        if (heroTitles.length) {
                            tl.from(heroTitles, { opacity: 0, y: 12, duration: 1.05 * speedScale, stagger: 0.1, ease: 'power2.out' }, 0.15);
                        }
                        if (heroMeta.length) {
                            tl.from(heroMeta, { opacity: 0, duration: 0.9 * speedScale, stagger: 0.08, ease: 'power1.out' }, 0.35);
                        }
                        break;

                    case 'fast-commerce-light':
                        tl.from([heroTitles, heroMeta, heroMedia], { opacity: 0, y: 8, duration: 0.24, stagger: 0.03, ease: 'power2.out' }, 0);
                        break;

                    default: // quiet-editorial, editorial-asymmetry, horizontal-cinema, tech-precision
                        if (heroMedia.length) {
                            tl.fromTo(heroMedia, { clipPath: 'inset(8% 0% 0% 0%)', opacity: 0.3 }, { clipPath: 'inset(0% 0% 0% 0%)', opacity: 1, duration: 1.05 * speedScale, ease: 'power3.out' }, 0);
                        }
                        if (heroTitles.length) {
                            tl.from(heroTitles, { y: 26, opacity: 0, duration: 0.85 * speedScale, stagger: 0.08, ease: 'power3.out' }, 0.1);
                        }
                        if (heroMeta.length) {
                            tl.from(heroMeta, { y: 14, opacity: 0, duration: 0.65 * speedScale, stagger: 0.06, ease: 'power2.out' }, 0.22);
                        }
                        break;
                }
            }

            // B. ScrollTrigger Section & Staggered Product Card Entrances
            const revealDuration = choreography === 'fast-commerce-light' ? 0.22 : (choreography === 'japanese-ma' ? 0.95 : 0.68) * speedScale;
            const revealY = choreography === 'fast-commerce-light' ? 10 : (choreography === 'brutalist-kinetic' ? 28 : 22);
            const revealEase = choreography === 'swiss-grid-snap' || choreography === 'brutalist-kinetic'
                ? 'power4.out'
                : (choreography === 'spatial-glass-float' ? 'back.out(1.2)' : 'power3.out');

            root.querySelectorAll('[data-k71-reveal]').forEach((sec) => {
                gsap.from(sec, {
                    y: revealY,
                    opacity: 0,
                    duration: revealDuration,
                    ease: revealEase,
                    clearProps: 'transform,opacity',
                    scrollTrigger: {
                        trigger: sec,
                        start: 'top 88%',
                        once: true,
                    },
                });
            });

            root.querySelectorAll('[data-k71-stagger-grid]').forEach((grid) => {
                const cards = grid.querySelectorAll('[data-k71-card-item]');
                if (!cards.length) return;
                gsap.from(cards, {
                    y: revealY,
                    opacity: 0,
                    scale: choreography === 'spatial-glass-float' || choreography === 'sneaker-vault-3d' ? 0.96 : 1,
                    duration: revealDuration,
                    stagger: choreography === 'fast-commerce-light' ? 0.025 : 0.055,
                    ease: revealEase,
                    clearProps: 'transform,opacity',
                    scrollTrigger: {
                        trigger: grid,
                        start: 'top 86%',
                        once: true,
                    },
                });
            });

            // C. Desktop-Only Scroll-Linked Choreography (Never traps mobile!)
            mm.add('(min-width: 1024px) and (pointer: fine)', () => {
                // 1. Scroll-scrubbed oversized marquee (brutalist-poster / runway)
                root.querySelectorAll('[data-k71-marquee]').forEach((track) => {
                    gsap.to(track, {
                        xPercent: -18,
                        ease: 'none',
                        scrollTrigger: {
                            trigger: track.parentElement || track,
                            start: 'top bottom',
                            end: 'bottom top',
                            scrub: 0.7,
                        },
                    });
                });

                // 2. Smooth Horizontal Scroll-Driven Cinema Rail (cinematic-dark-stage / horizontal-cinema)
                root.querySelectorAll('[data-k71-hscroll-wrap]').forEach((wrap) => {
                    const track = wrap.querySelector('[data-k71-hscroll-track]');
                    if (!track) return;
                    const delta = () => Math.max(0, track.scrollWidth - wrap.clientWidth);
                    if (delta() > 60) {
                        gsap.to(track, {
                            x: () => -Math.min(delta(), 520),
                            ease: 'none',
                            scrollTrigger: {
                                trigger: wrap,
                                start: 'top 80%',
                                end: 'bottom 20%',
                                scrub: 0.85,
                                invalidateOnRefresh: true,
                            },
                        });
                    }
                });

                // 3. Counter-Parallax Editorial Columns (editorial-asymmetry / layered-parallax)
                root.querySelectorAll('[data-k71-parallax]').forEach((el) => {
                    const shift = parseFloat(el.dataset.k71Parallax || '28');
                    gsap.fromTo(el, { y: shift }, {
                        y: -shift,
                        ease: 'none',
                        scrollTrigger: {
                            trigger: el,
                            start: 'top bottom',
                            end: 'bottom top',
                            scrub: 0.6,
                        },
                    });
                });

                // 4. Scrubbed Hero Depth Zoom (cinematic-dark-stage)
                root.querySelectorAll('[data-k71-hero-scrub]').forEach((img) => {
                    gsap.to(img, {
                        scale: 1.09,
                        y: 26,
                        ease: 'none',
                        scrollTrigger: {
                            trigger: img.closest('[data-k71-hero-stage]') || img,
                            start: 'top top',
                            end: 'bottom top',
                            scrub: true,
                        },
                    });
                });
            });
        }, root);

        // ── 3. Desktop-Only CSS 3D Perspective Tilt, Specular Sheen & Magnetic Buttons ──
        if (!isTouch && window.innerWidth >= 1024 && depth3d !== 'none') {
            const maxDeg = depth3d === 'specular-vault' ? 8 : (depth3d === 'floating-glass' ? 6 : 4.5);

            root.querySelectorAll('[data-k71-3d-card]').forEach((card) => {
                const onMove = (e) => {
                    const rect = card.getBoundingClientRect();
                    const px = (e.clientX - rect.left) / rect.width;
                    const py = (e.clientY - rect.top) / rect.height;
                    const rx = (0.5 - py) * maxDeg;
                    const ry = (px - 0.5) * maxDeg;
                    card.style.setProperty('--mx', `${(px * 100).toFixed(1)}%`);
                    card.style.setProperty('--my', `${(py * 100).toFixed(1)}%`);
                    card.style.transform = `perspective(1000px) rotateX(${rx.toFixed(2)}deg) rotateY(${ry.toFixed(2)}deg) translateZ(4px)`;
                };
                const onLeave = () => {
                    card.style.transform = '';
                };
                card.addEventListener('mousemove', onMove);
                card.addEventListener('mouseleave', onLeave);
                pointerCleanups.push(() => {
                    card.removeEventListener('mousemove', onMove);
                    card.removeEventListener('mouseleave', onLeave);
                });
            });

            root.querySelectorAll('[data-k71-magnetic]').forEach((btn) => {
                const onMove = (e) => {
                    const r = btn.getBoundingClientRect();
                    const dx = (e.clientX - (r.left + r.width / 2)) * 0.18;
                    const dy = (e.clientY - (r.top + r.height / 2)) * 0.18;
                    btn.style.transform = `translate3d(${dx.toFixed(1)}px, ${dy.toFixed(1)}px, 0)`;
                };
                const onLeave = () => {
                    btn.style.transform = '';
                };
                btn.addEventListener('mousemove', onMove);
                btn.addEventListener('mouseleave', onLeave);
                pointerCleanups.push(() => {
                    btn.removeEventListener('mousemove', onMove);
                    btn.removeEventListener('mouseleave', onLeave);
                });
            });
        }
    }

    // ── 3B. Desktop Ambient Cursor Spotlight Aura on Hero Stages ──────────────
    if (!reduced && !isTouch && window.innerWidth >= 1024) {
        root.querySelectorAll('[data-k71-hero-stage]').forEach((stage) => {
            let rafAura = 0;
            const onStageMove = (e) => {
                if (rafAura) return;
                rafAura = requestAnimationFrame(() => {
                    const r = stage.getBoundingClientRect();
                    stage.style.setProperty('--aura-x', `${((e.clientX - r.left) / Math.max(1, r.width) * 100).toFixed(1)}%`);
                    stage.style.setProperty('--aura-y', `${((e.clientY - r.top) / Math.max(1, r.height) * 100).toFixed(1)}%`);
                    rafAura = 0;
                });
            };
            stage.addEventListener('mousemove', onStageMove, { passive: true });
            pointerCleanups.push(() => stage.removeEventListener('mousemove', onStageMove));
        });
    }

    // ── 3C. Sticky PDP Bottom Buy Bar Observer ────────────────────────────────
    const stickyPdpBar = document.getElementById('k71-sticky-pdp-bar');
    const stickyAddBtn = document.getElementById('k71-sticky-add-btn');
    let pdpScrollHandler = null;
    if (stickyPdpBar) {
        pdpScrollHandler = () => {
            stickyPdpBar.classList.toggle('is-visible', (window.scrollY || 0) > 460);
        };
        window.addEventListener('scroll', pdpScrollHandler, { passive: true });
        pdpScrollHandler();
        if (stickyAddBtn) {
            const onStickyAdd = () => {
                const form = root.querySelector('form[data-k71-cart-form]');
                if (form) form.requestSubmit();
            };
            stickyAddBtn.addEventListener('click', onStickyAdd);
            pointerCleanups.push(() => stickyAddBtn.removeEventListener('click', onStickyAdd));
        }
        pointerCleanups.push(() => window.removeEventListener('scroll', pdpScrollHandler));
    }

    // ── 3D. Interactive Desktop PDP Image Magnify Zoom Pan ────────────────────
    if (!reduced && !isTouch && window.innerWidth >= 1024) {
        const zoomTrigger = document.getElementById('k71-pdp-main-trigger');
        const zoomImg = document.getElementById('k71-pdp-main-img');
        if (zoomTrigger && zoomImg) {
            zoomImg.style.transition = 'transform 220ms cubic-bezier(0.22, 1, 0.36, 1)';
            const onZoomMove = (e) => {
                const r = zoomTrigger.getBoundingClientRect();
                const ox = Math.max(0, Math.min(100, ((e.clientX - r.left) / Math.max(1, r.width)) * 100));
                const oy = Math.max(0, Math.min(100, ((e.clientY - r.top) / Math.max(1, r.height)) * 100));
                zoomImg.style.transformOrigin = `${ox.toFixed(1)}% ${oy.toFixed(1)}%`;
                zoomImg.style.transform = 'scale(1.65)';
            };
            const onZoomLeave = () => {
                zoomImg.style.transformOrigin = '50% 50%';
                zoomImg.style.transform = 'scale(1)';
            };
            zoomTrigger.addEventListener('mousemove', onZoomMove, { passive: true });
            zoomTrigger.addEventListener('mouseleave', onZoomLeave);
            pointerCleanups.push(() => {
                zoomTrigger.removeEventListener('mousemove', onZoomMove);
                zoomTrigger.removeEventListener('mouseleave', onZoomLeave);
            });
        }
    }

    // ── 4. Accessible Dialogs, GSAP Animated Drawers, Wishlist & Quick-View ───
    const wishKey = 'k71_wishlist_' + (root.dataset.k71Theme || 'store');
    let wishMap = {};
    try {
        const parsed = JSON.parse(localStorage.getItem(wishKey) || '{}');
        if (Array.isArray(parsed)) {
            parsed.forEach((id) => { wishMap[String(id)] = { id: String(id), title: 'Saved Piece' }; });
        } else if (parsed && typeof parsed === 'object') {
            wishMap = parsed;
        }
    } catch {
        wishMap = {};
    }
    const renderShortlistDrawer = () => {
        const ids = Object.keys(wishMap);
        document.querySelectorAll('[data-k71-wish-count]').forEach((el) => { el.textContent = String(ids.length); });
        const box = document.getElementById('k71-shortlist-drawer-items');
        if (!box) return;
        if (ids.length === 0) {
            box.innerHTML = `<p class="text-xs py-4" style="color: var(--t-muted);">Tap the heart icon on any piece to save it to your personal shortlist.</p>`;
            return;
        }
        box.innerHTML = '';
        ids.forEach((id) => {
            const item = wishMap[id] || { id, title: 'Saved Piece' };
            const row = document.createElement('div');
            row.className = 'p-3 rounded-lg flex items-center justify-between gap-3';
            row.style.border = '1px solid var(--t-border)';
            row.style.backgroundColor = 'var(--t-bg)';
            row.innerHTML = `<div class="min-w-0"><p class="text-xs font-bold truncate"></p><p class="text-[10px] opacity-65">Saved in shortlist</p></div><button type="button" data-k71-wish-remove="${id}" class="text-[11px] font-bold uppercase cursor-pointer hover:opacity-70" style="color: var(--t-accent);">Remove</button>`;
            row.querySelector('p').textContent = item.title || 'Saved Piece';
            box.appendChild(row);
        });
    };
    const syncWishUI = () => {
        root.querySelectorAll('[data-k71-wish-btn]').forEach((btn) => {
            const id = String(btn.dataset.wishId || '');
            const active = Boolean(wishMap[id]);
            btn.dataset.wished = active ? 'true' : 'false';
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        renderShortlistDrawer();
    };
    syncWishUI();

    let lastTrigger = null;
    function openModal(id, trigger) {
        const el = document.getElementById(id);
        if (!el) return;
        lastTrigger = trigger || document.activeElement;
        el.classList.remove('hidden');
        el.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        if (!reduced) {
            const panel = el.querySelector('.k71-modal-panel, .relative.z-10');
            if (panel) {
                if (id === 'k71-cart-drawer') {
                    gsap.fromTo(panel, { x: 48, opacity: 0.4 }, { x: 0, opacity: 1, duration: 0.36, ease: 'expo.out', clearProps: 'transform,opacity' });
                } else {
                    gsap.fromTo(panel, { y: 18, scale: 0.96, opacity: 0 }, { y: 0, scale: 1, opacity: 1, duration: 0.32, ease: 'power3.out', clearProps: 'transform,opacity' });
                }
            }
        }

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

    const onDocClick = (e) => {
        const wishRemove = e.target.closest('[data-k71-wish-remove]');
        if (wishRemove) {
            e.preventDefault();
            const id = String(wishRemove.dataset.k71WishRemove || '');
            delete wishMap[id];
            try { localStorage.setItem(wishKey, JSON.stringify(wishMap)); } catch {}
            syncWishUI();
            return;
        }

        const drawerTab = e.target.closest('[data-k71-drawer-tab]');
        if (drawerTab) {
            e.preventDefault();
            const mode = drawerTab.dataset.k71DrawerTab;
            document.querySelectorAll('[data-k71-drawer-tab]').forEach((t) => {
                const isActive = t.dataset.k71DrawerTab === mode;
                t.style.backgroundColor = isActive ? 'var(--t-accent)' : 'transparent';
                t.style.color = isActive ? 'var(--t-accent-contrast)' : 'var(--t-muted)';
            });
            document.getElementById('k71-cart-drawer-items')?.classList.toggle('hidden', mode !== 'bag');
            document.getElementById('k71-shortlist-drawer-items')?.classList.toggle('hidden', mode !== 'shortlist');
            return;
        }

        const shareBtn = e.target.closest('[data-k71-share-btn]');
        if (shareBtn) {
            e.preventDefault();
            const url = window.location.href;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(() => showToast('Storefront link copied to clipboard.', false)).catch(() => showToast('Link ready to share.', false));
            } else {
                showToast('Link ready to share.', false);
            }
            return;
        }

        const gridColBtn = e.target.closest('[data-k71-grid-cols]');
        if (gridColBtn) {
            e.preventDefault();
            const cols = parseInt(gridColBtn.dataset.k71GridCols || '4', 10);
            const grid = document.getElementById('k71-collection-grid');
            if (grid) {
                grid.classList.remove('grid-cols-1', 'sm:grid-cols-2', 'grid-cols-2', 'sm:grid-cols-3', 'lg:grid-cols-3', 'lg:grid-cols-4');
                if (cols === 2) grid.classList.add('grid-cols-1', 'sm:grid-cols-2');
                else if (cols === 3) grid.classList.add('grid-cols-2', 'lg:grid-cols-3');
                else grid.classList.add('grid-cols-2', 'sm:grid-cols-3', 'lg:grid-cols-4');
                document.querySelectorAll('[data-k71-grid-cols]').forEach((b) => {
                    const active = parseInt(b.dataset.k71GridCols || '4', 10) === cols;
                    b.style.backgroundColor = active ? 'var(--t-accent)' : 'transparent';
                    b.style.color = active ? 'var(--t-accent-contrast)' : 'var(--t-muted)';
                });
            }
            return;
        }

        const savedFilterBtn = e.target.closest('#k71-filter-saved-btn');
        if (savedFilterBtn) {
            e.preventDefault();
            const next = savedFilterBtn.getAttribute('aria-pressed') !== 'true';
            savedFilterBtn.setAttribute('aria-pressed', next ? 'true' : 'false');
            savedFilterBtn.style.backgroundColor = next ? 'var(--t-accent)' : 'transparent';
            savedFilterBtn.style.color = next ? 'var(--t-accent-contrast)' : 'var(--t-text)';
            const items = document.querySelectorAll('#k71-collection-grid [data-product-id]');
            items.forEach((el) => {
                const pid = String(el.dataset.productId || '');
                el.classList.toggle('hidden', next && !wishMap[pid]);
            });
            if (next && Object.keys(wishMap).length === 0) {
                showToast('No saved pieces yet — tap the heart icon on any card.', false);
            }
            return;
        }

        const wishBtn = e.target.closest('[data-k71-wish-btn]');
        if (wishBtn) {
            e.preventDefault();
            e.stopPropagation();
            const id = String(wishBtn.dataset.wishId || '');
            if (!id) return;
            const title = wishBtn.dataset.wishTitle || 'Item';
            if (wishMap[id]) {
                delete wishMap[id];
                showToast(`Removed "${title}" from shortlist.`, false);
            } else {
                wishMap[id] = { id, title };
                showToast(`Saved "${title}" to your shortlist.`, false);
                if (!reduced) {
                    gsap.fromTo(wishBtn, { scale: 0.8 }, { scale: 1.15, duration: 0.24, ease: 'back.out(2.5)', yoyo: true, repeat: 1, clearProps: 'transform' });
                }
            }
            try {
                localStorage.setItem(wishKey, JSON.stringify(wishMap));
            } catch {}
            syncWishUI();
            return;
        }

        const qtyStep = e.target.closest('[data-k71-qty-step]');
        if (qtyStep) {
            e.preventDefault();
            const qtyInput = document.getElementById('k71-qv-qty');
            if (qtyInput) {
                const delta = parseInt(qtyStep.dataset.k71QtyStep || '0', 10);
                const next = Math.max(1, Math.min(99, (parseInt(qtyInput.value || '1', 10) || 1) + delta));
                qtyInput.value = String(next);
            }
            return;
        }

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
            return;
        }

        const qvBtn = e.target.closest('[data-k71-quickview]');
        if (qvBtn) {
            e.preventDefault();
            const qImg = document.getElementById('k71-qv-img');
            const qCat = document.getElementById('k71-qv-cat');
            const qTitle = document.getElementById('k71-qv-title');
            const qPrice = document.getElementById('k71-qv-price');
            const qDesc = document.getElementById('k71-qv-desc');
            const qPid = document.getElementById('k71-qv-pid');
            const qQty = document.getElementById('k71-qv-qty');
            const qLink = document.getElementById('k71-qv-link');
            if (qImg) { qImg.src = qvBtn.dataset.qvImg || ''; qImg.alt = qvBtn.dataset.qvTitle || ''; }
            if (qCat) qCat.textContent = qvBtn.dataset.qvCat || '';
            if (qTitle) qTitle.textContent = qvBtn.dataset.qvTitle || '';
            if (qPrice) qPrice.textContent = qvBtn.dataset.qvPrice || '';
            if (qDesc) qDesc.textContent = qvBtn.dataset.qvDesc || '';
            if (qPid) qPid.value = qvBtn.dataset.qvId || '';
            if (qQty) qQty.value = '1';
            if (qLink) qLink.href = qvBtn.dataset.qvUrl || '#';
            openModal('k71-quickview-modal', qvBtn);
        }
    };

    const onDocKeydown = (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('[data-k71-modal]:not(.hidden)').forEach((m) => closeModal(m));
            return;
        }
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            openModal('k71-search-modal', document.activeElement);
            return;
        }
        if (e.key === '/' && !(e.target && ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName))) {
            const modal = document.getElementById('k71-search-modal');
            if (modal && modal.classList.contains('hidden')) {
                e.preventDefault();
                openModal('k71-search-modal', document.activeElement);
            }
        }
    };

    document.addEventListener('click', onDocClick);
    document.addEventListener('keydown', onDocKeydown);

    // ── 5. Real Backend AJAX Add-to-Cart & Toast ──────────────────────────────
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

    const onDocSubmit = async (e) => {
        const form = e.target.closest('[data-k71-cart-form]');
        if (!form) return;
        e.preventDefault();

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalHtml = submitBtn ? submitBtn.innerHTML : '';
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
                const drawerItems = document.getElementById('k71-cart-drawer-items');
                if (drawerItems && !form.dataset.k71BuyNow) {
                    const cardTitle = form.closest('article')?.querySelector('h2,h3')?.textContent?.trim()
                        || document.getElementById('k71-qv-title')?.textContent?.trim()
                        || document.querySelector('h1')?.textContent?.trim()
                        || 'Store Item';
                    const row = document.createElement('div');
                    row.className = 'p-3.5 rounded-lg flex items-center justify-between gap-3';
                    row.style.border = '1px solid var(--t-border)';
                    row.style.backgroundColor = 'var(--t-bg)';
                    row.innerHTML = `<div class="min-w-0"><p class="text-xs font-bold truncate"></p><p class="text-[11px] opacity-70">Added to bag</p></div><span class="text-xs font-mono font-bold shrink-0">✓</span>`;
                    row.querySelector('p').textContent = cardTitle;
                    drawerItems.prepend(row);
                    const qv = document.getElementById('k71-quickview-modal');
                    if (qv && !qv.classList.contains('hidden')) closeModal(qv);
                    openModal('k71-cart-drawer', submitBtn);
                }
                if (form.dataset.k71BuyNow === 'true' && form.dataset.k71CheckoutUrl) {
                    window.location.href = form.dataset.k71CheckoutUrl;
                }
            }
        } catch {
            showToast('Network error while adding to bag. Please try again.', true);
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            }
        }
    };
    document.addEventListener('submit', onDocSubmit);

    // ── 6. Optional Vendor-Controlled Promo Popup (Frequency-Capped) ──────────
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

    // ── 7. Real Release Countdown (Only When Real Deadline Configured) ────────
    const countdownIntervals = [];
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
        countdownIntervals.push(setInterval(tick, 1000));
    });

    // ── 7B. Lightweight Procedural WebGL Atmospheric Shader Stage (Desktop Only) ──
    const shaderCleanups = [];
    if (!reduced && !isTouch && window.innerWidth >= 1024) {
        root.querySelectorAll('canvas[data-k71-shader]').forEach((canvas) => {
            const gl = canvas.getContext('webgl', { alpha: true, antialias: false, powerPreference: 'low-power' });
            if (!gl) return;

            const vsSource = `attribute vec2 p; void main(){ gl_Position = vec4(p, 0.0, 1.0); }`;
            const fsSource = `
                precision mediump float;
                uniform float u_t;
                uniform vec2 u_res;
                uniform vec2 u_mouse;
                uniform vec3 u_accent;
                void main() {
                    vec2 uv = gl_FragCoord.xy / u_res;
                    vec2 m = u_mouse / u_res;
                    float wave = sin(uv.x * 3.2 + u_t * 0.45) * cos(uv.y * 2.8 - u_t * 0.35);
                    float glow = smoothstep(0.75, 0.0, length(uv - vec2(0.5 + 0.15 * sin(u_t * 0.25), 0.42)));
                    float ptr = smoothstep(0.45, 0.0, length(uv - m));
                    vec3 col = u_accent * (0.14 * glow + 0.12 * ptr + 0.04 * (wave * 0.5 + 0.5));
                    gl_FragColor = vec4(col, 0.55 * glow + 0.35 * ptr + 0.08);
                }
            `;

            const compile = (type, src) => {
                const s = gl.createShader(type);
                gl.shaderSource(s, src);
                gl.compileShader(s);
                return s;
            };
            const prog = gl.createProgram();
            gl.attachShader(prog, compile(gl.VERTEX_SHADER, vsSource));
            gl.attachShader(prog, compile(gl.FRAGMENT_SHADER, fsSource));
            gl.linkProgram(prog);
            if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) return;
            gl.useProgram(prog);

            const buf = gl.createBuffer();
            gl.bindBuffer(gl.ARRAY_BUFFER, buf);
            gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 1, -1, -1, 1, 1, 1]), gl.STATIC_DRAW);
            const loc = gl.getAttribLocation(prog, 'p');
            gl.enableVertexAttribArray(loc);
            gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0);

            const uT = gl.getUniformLocation(prog, 'u_t');
            const uRes = gl.getUniformLocation(prog, 'u_res');
            const uMouse = gl.getUniformLocation(prog, 'u_mouse');
            const uAccent = gl.getUniformLocation(prog, 'u_accent');

            // Parse CSS accent hex into normalized RGB
            const hex = (getComputedStyle(root).getPropertyValue('--t-accent') || '#c8a45d').trim().replace('#', '');
            const rgb = hex.length === 6
                ? [parseInt(hex.slice(0, 2), 16) / 255, parseInt(hex.slice(2, 4), 16) / 255, parseInt(hex.slice(4, 6), 16) / 255]
                : [0.78, 0.64, 0.36];

            let mx = 0.5, my = 0.5, visible = true, rafId = 0;
            const resize = () => {
                const rect = canvas.getBoundingClientRect();
                canvas.width = Math.max(2, Math.floor(rect.width * 0.5));
                canvas.height = Math.max(2, Math.floor(rect.height * 0.5));
                gl.viewport(0, 0, canvas.width, canvas.height);
            };
            resize();

            const onMouse = (e) => {
                const r = canvas.getBoundingClientRect();
                mx = Math.max(0, Math.min(1, (e.clientX - r.left) / Math.max(1, r.width)));
                my = Math.max(0, Math.min(1, 1 - (e.clientY - r.top) / Math.max(1, r.height)));
            };
            window.addEventListener('mousemove', onMouse, { passive: true });
            window.addEventListener('resize', resize, { passive: true });

            const io = new IntersectionObserver((entries) => {
                visible = entries[0]?.isIntersecting ?? true;
            });
            io.observe(canvas);

            const start = performance.now();
            const render = (now) => {
                if (visible) {
                    gl.uniform1f(uT, (now - start) * 0.001);
                    gl.uniform2f(uRes, canvas.width, canvas.height);
                    gl.uniform2f(uMouse, mx * canvas.width, my * canvas.height);
                    gl.uniform3f(uAccent, rgb[0], rgb[1], rgb[2]);
                    gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
                }
                rafId = requestAnimationFrame(render);
            };
            rafId = requestAnimationFrame(render);

            shaderCleanups.push(() => {
                cancelAnimationFrame(rafId);
                io.disconnect();
                window.removeEventListener('mousemove', onMouse);
                window.removeEventListener('resize', resize);
                gl.getExtension('WEBGL_lose_context')?.loseContext();
            });
        });
    }

    // ── 8. Clean Teardown on Unload (Zero Stale ScrollTriggers / Listeners) ───
    const teardown = () => {
        if (gsapCtx) gsapCtx.revert();
        ScrollTrigger.getAll().forEach((st) => st.kill());
        pointerCleanups.forEach((fn) => fn());
        shaderCleanups.forEach((fn) => fn());
        countdownIntervals.forEach((id) => clearInterval(id));
        if (onHeaderScroll) window.removeEventListener('scroll', onHeaderScroll);
        if (backToTopBtn) backToTopBtn.removeEventListener('click', onBackToTopClick);
        document.removeEventListener('click', onDocClick);
        document.removeEventListener('keydown', onDocKeydown);
        document.removeEventListener('submit', onDocSubmit);
        delete root.dataset.k71MotionInit;
    };
    window.addEventListener('pagehide', teardown, { once: true });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initK71ThemeEngine, { once: true });
} else {
    initK71ThemeEngine();
}
