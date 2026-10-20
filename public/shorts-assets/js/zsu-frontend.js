/* ═══════════════════════════════════════════════════════════════════
   ZSU FRONTEND JS  v11
   Real file: assets/js/zsu-frontend.js
   Localized via wp_localize_script('zsu-frontend','ZSU',{…})
   ═══════════════════════════════════════════════════════════════════ */

/* global ZSU, jQuery */
(function () {
  'use strict';

  /* ── Guard: ZSU config must be defined ── */
  if (typeof ZSU === 'undefined' || !ZSU.ajax_url) {
    console.warn('[ZSU] Plugin config not found. Check wp_localize_script.');
    return;
  }

  /* ═══════════════════════════════════════════════════════════ */
  const ZSU_App = {

    // ── State ────────────────────────────────────────────────
    state: {
      page:          1,
      filter:        'latest',
      cat:           '',
      total:         0,
      pages:         0,
      loading:       false,
      allLoaded:     false,
      shorts:        [],        // loaded grid cards
      lbShorts:      [],        // lightbox queue
      lbIndex:       0,         // current lightbox position
      lbOpen:        false,
      isMuted:       false,
      currentShortId: 0,
      watchTimer:    null,
      watchStart:    0,
      heatTimer:     null,
      wishlist:      new Set(),
      likedIds:      new Set(),
      followedIds:   new Set(),
      searchTimer:   null,
      trayOpen:      true,
      videosPlaying: new Map(), // slide index → playing bool
    },

    // ── Init ─────────────────────────────────────────────────
    init() {
      if (this._initialized) return; // prevent double-init
      this._initialized = true;
      this.bindUI();
      this.initParticles();
      this.initHeader();
      this.initSearch();
      this.initFilterStrip();
      this.initIntersectionObserver();
      this.loadGrid(1, true);
      this.loadFeaturedRail();
      this.loadCategoryRails();
      this.loadCoinBalance();
      this.loadWishlist();
      this.loadLikedFromCookies();

      // Deep-link: open specific short via ?v=ID or rewrite /shorts/v/{id}
      const openId = parseInt(ZSU.open_id || 0, 10);
      if (openId) {
        this.fetchAndOpenLightbox(openId);
      }
    },

    // ── Bind UI events ───────────────────────────────────────
    bindUI() {
      // Grid clicks → open lightbox
      // Use native listener — on() helper can fail with complex :not() selectors
      document.addEventListener('click', (e) => {
        const card = e.target.closest('.zsu-short-card');
        if (!card || card.classList.contains('zsu-skeleton')) return;
        const id = parseInt(card.dataset.id || 0, 10);
        if (!id) return;
        e.preventDefault();
        this.openLightboxFromGrid(id);
      });

      // Quick like on card
      on(document, 'click', '.zsu-card-quick-like', (e) => {
        e.stopPropagation();
        const btn = e.currentTarget;
        const id  = parseInt(btn.dataset.id || 0, 10);
        if (id) this.toggleLike(id, btn);
      });

      // Grid view / feed view toggle
      on(document, 'click', '#zsuViewGrid', () => this.setViewMode('grid'));
      on(document, 'click', '#zsuViewFeed', () => this.setViewMode('feed'));

      // Category pills
      on(document, 'click', '.zsu-cat-pill', (e) => {
        const cat = e.currentTarget.dataset.cat || '';
        this.setCategory(cat, e.currentTarget);
      });

      // Sort select
      on(document, 'change', '#zsuSortSelect', (e) => {
        this.state.filter = e.target.value;
        this.loadGrid(1, true);
      });

      // Clear filter
      on(document, 'click', '#zsuClearFilter', () => {
        this.setCategory('', document.querySelector('.zsu-cat-pill[data-cat=""]'));
      });

      // See all links
      on(document, 'click', '.zsu-see-all', (e) => {
        e.preventDefault();
        const cat    = e.currentTarget.dataset.cat  || '';
        const filter = e.currentTarget.dataset.filter || 'latest';
        if (cat) {
          this.setCategory(cat, document.querySelector(`.zsu-cat-pill[data-cat="${cat}"]`));
        } else {
          this.state.filter = filter;
          this.loadGrid(1, true);
        }
        document.getElementById('zsuGrid')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });

      // Empty state reset
      on(document, 'click', '#zsuEmptyReset', () => {
        this.setCategory('', document.querySelector('.zsu-cat-pill[data-cat=""]'));
      });

      // ── Lightbox ──────────────────────────────────────────
      on(document, 'click', '#zsuLbClose',        () => this.closeLightbox());
      on(document, 'click', '#zsuLbPrev',         () => this.lbNavigate(-1));
      on(document, 'click', '#zsuLbNext',         () => this.lbNavigate(1));
      on(document, 'click', '#zsuLbMuteBtn',      () => this.toggleMute());
      on(document, 'click', '#zsuLbFullscreenBtn',() => this.toggleFullscreen());
      on(document, 'click', '#zsuLbLikeBtn',      () => {
        const id = parseInt(document.getElementById('zsuLbLikeBtn')?.dataset.shortId || 0, 10);
        if (id) this.toggleLike(id, document.getElementById('zsuLbLikeBtn'));
      });
      on(document, 'click', '#zsuLbShareBtn',     () => this.openShareSheet());
      on(document, 'click', '#zsuLbFollowBtn',    (e) => {
        const vid = parseInt(e.currentTarget.dataset.vendorId || 0, 10);
        if (vid) this.toggleFollow(vid, e.currentTarget);
      });
      on(document, 'click', '#zsuLbCommentBtn',   () => this.toggleComments());

      // Keyboard navigation
      on(document, 'keydown', (e) => {
        if (!this.state.lbOpen) return;
        if (e.key === 'Escape')     this.closeLightbox();
        if (e.key === 'ArrowUp')    this.lbNavigate(-1);
        if (e.key === 'ArrowDown')  this.lbNavigate(1);
        if (e.key === 'ArrowLeft')  this.lbNavigate(-1);
        if (e.key === 'ArrowRight') this.lbNavigate(1);
        if (e.key === 'm' || e.key === 'M') this.toggleMute();
        if (e.key === ' ') { e.preventDefault(); this.togglePlayPause(); }
      });

      // Click outside lightbox sidebar = close
      on(document, 'click', '#zsuLightbox', (e) => {
        if (e.target === document.getElementById('zsuLightbox')) this.closeLightbox();
      });

      // ── Shop sheet ────────────────────────────────────────
      on(document, 'click', '#zsuShopSheetClose',   () => this._closeShopSheet());
      on(document, 'click', '#zsuShopSheetOverlay', () => this._closeShopSheet());
      on(document, 'click', '.zsu-sheet-wish', (e) => {
        e.stopPropagation();
        const pid = parseInt(e.currentTarget.dataset.pid || 0, 10);
        const sid = parseInt(e.currentTarget.dataset.sid || 0, 10);
        if (pid) this.toggleWishlist(pid, sid, e.currentTarget);
      });

      // ── Share sheet ───────────────────────────────────────
      on(document, 'click', '#zsuShareSheetClose', () => this.closeShareSheet());
      on(document, 'click', '#zsuShareOverlay',    () => this.closeShareSheet());
      on(document, 'click', '.zsu-share-btn',      (e) => {
        const p = e.currentTarget.dataset.platform;
        this.executeShare(p);
      });
      on(document, 'click', '#zsuCopyLinkBtn', () => this.copyShareLink());

      // ── Product modal ─────────────────────────────────────
      on(document, 'click', '#zsuProductModalClose', () => this.closeProductModal());
      on(document, 'click', '#zsuProductOverlay',    () => this.closeProductModal());
      on(document, 'click', '#zsuCommentsClose',     () => this.closeComments());
      on(document, 'click', '#zsuCommentSubmit',     () => this.submitComment());
      on(document, 'keydown', '#zsuCommentInput',    (e) => {
        if (e.key === 'Enter') this.submitComment();
      });

      // ── Avatar dropdown ───────────────────────────────────
      on(document, 'click', '#zsuAvatarBtn', (e) => {
        e.stopPropagation();
        const dd = document.getElementById('zsuAvatarDropdown');
        dd?.classList.toggle('open');
      });
      on(document, 'click', (e) => {
        if (!e.target.closest('#zsuAvatarBtn')) {
          document.getElementById('zsuAvatarDropdown')?.classList.remove('open');
        }
        if (!e.target.closest('#zsuHeaderSearch')) {
          document.getElementById('zsuSearchDropdown')?.classList.remove('mobile-open');
        }
      });

      // CRITICAL: Clean up body lock when user navigates away (back/forward/link click)
      // Without this, zsu-no-scroll stays on <body> site-wide for logged-in users
      window.addEventListener('popstate', () => {
        if (this.state.lbOpen) {
          this.closeLightbox();
        }
        // Always clean up even if state says closed (safety net)
        document.body.classList.remove('zsu-no-scroll');
      });

      // Clean up on page unload / navigation
      window.addEventListener('pagehide', () => {
        document.body.classList.remove('zsu-no-scroll');
        document.body.style.overflow = '';
        document.body.style.position = '';
      });

      // Also clean up on any WP AJAX navigation (theme frameworks)
      document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
          document.body.classList.remove('zsu-no-scroll');
        }
      });

      // Resize/orientation: update all slide heights to stay pixel-perfect
      window.addEventListener('resize', () => {
        const h = window.innerHeight;
        const stack = document.getElementById('zsuLbStack');
        if (!stack) return;
        stack.style.height = h + 'px';
        stack.querySelectorAll('.zsu-lb-slide').forEach(slide => {
          slide.style.height    = h + 'px';
          slide.style.minHeight = h + 'px';
          slide.style.maxHeight = h + 'px';
        });
        // Re-scroll to current slide so it snaps correctly
        if (this.state.lbOpen) {
          stack.scrollTo({ top: this.state.lbIndex * h, behavior: 'instant' });
        }
      });
    },

    // ═══════════════════════════════════════════════════════
    //  GRID LOADER
    // ═══════════════════════════════════════════════════════
    loadGrid(page = 1, reset = false) {
      if (this.state.loading) return;
      if (!reset && this.state.allLoaded) return;

      this.state.loading = true;
      const $grid    = document.getElementById('zsuGrid');
      const $spinner = document.getElementById('zsuLoadSpinner');
      const $empty   = document.getElementById('zsuGridEmpty');
      const $count   = document.getElementById('zsuGridCount');

      if (reset) {
        this.state.page      = 1;
        this.state.allLoaded = false;
        this.state.shorts    = [];
        if ($grid && !$grid.querySelector('.zsu-short-card')) $grid.innerHTML = renderSkeletons(8);
        if ($empty) $empty.style.display = 'none';
        if ($count && !$count.textContent) $count.textContent = '';
      } else {
        if ($spinner) $spinner.style.display = 'flex';
      }

      ajax('zsu_get_shorts', {
        page,
        per_page: ZSU.per_page,
        filter:   this.state.filter,
        cat:      this.state.cat,
      })
      .then((res) => {
        if (reset && $grid) $grid.innerHTML = '';
        this.state.loading = false;
        if ($spinner) $spinner.style.display = 'none';

        if (!res.success || !res.shorts.length) {
          if (reset && $empty) $empty.style.display = 'block';
          this.state.allLoaded = true;
          const $lmb = document.getElementById('zsuLoadMoreBtn'); if ($lmb) $lmb.style.display = 'none';
          return;
        }

        res.shorts.forEach((s) => {
          this.state.shorts.push(s);
          const el = this.buildShortCard(s);
          $grid.appendChild(el);
        });

        this.state.page  = page;
        this.state.total = res.total;
        this.state.pages = res.pages;

        if ($count) $count.textContent = `${res.total.toLocaleString()} shorts`;

        if (page >= res.pages) {
          this.state.allLoaded = true;
          const $lmb = document.getElementById('zsuLoadMoreBtn'); if ($lmb) $lmb.style.display = 'none';
        } else {
          const $lmb2 = document.getElementById('zsuLoadMoreBtn'); if ($lmb2) $lmb2.style.display = 'inline-flex';
        }

        // Init 3D tilt on newly added cards
        if (ZSU.tilt_cards) this.initTilt($grid.querySelectorAll('.zsu-short-card:not(.zsu-tilt-init)'));

        // Animate cards in
        this.animateCardsIn($grid.querySelectorAll('.zsu-short-card:not(.zsu-anim-done)'));
      })
      .catch((err) => {
        this.state.loading = false;
        if ($spinner) $spinner.style.display = 'none';
        console.error('[ZSU] loadGrid AJAX error:', err);
        if ($grid && !$grid.children.length) {
          $grid.innerHTML = '<p style="color:#f87171;padding:20px;grid-column:1/-1">⚠ Failed to load shorts. Check console for details.</p>';
        }
      });
    },

    // ── Build a short card element ────────────────────────────
    buildShortCard(s) {
      const el = document.createElement('div');
      el.className = `zsu-short-card${s.is_featured ? ' featured' : ''}${s.is_boosted ? ' boosted' : ''}`;
      el.dataset.id = s.id;
      el.setAttribute('role', 'listitem');
      el.setAttribute('tabindex', '0');

      const isLiked    = this.state.likedIds.has(s.id);
      const thumb      = ZSU.ab_testing && s.thumb_b_url ? s.thumb_b_url : s.thumb_url;
      const durationStr = s.duration ? formatDuration(s.duration) : '';

      let badgesHtml = '';
      // Only show featured badge in non-featured context (latest grid); never show for boosted in latest grid
      if (s.is_featured && !s.is_boosted) badgesHtml += '<span class="zsu-card-badge zsu-badge-featured">⭐ Featured</span>';
      if (s.trending_score > 100) badgesHtml += '<span class="zsu-card-badge zsu-badge-trending">🔥</span>';

      const ratingHtml = s.rating_avg > 0 && ZSU.enable_ratings
        ? `<div class="zsu-card-rating"><span class="zsu-card-star">⭐</span><span class="zsu-card-rating-val">${parseFloat(s.rating_avg).toFixed(1)}</span></div>` : '';

      el.innerHTML = `
        <div class="zsu-card-thumb-wrap">
          ${s.is_boosted && s._in_featured_rail ? `<div class="zsu-elite-ribbon"><svg viewBox="0 0 14 10" width="11" height="8" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M0 10h14V8H0v2zM14 3L11 1 7 5 3 1 0 3l1.5 4h11L14 3z" fill="#1e3a5f"/></svg> ELITE</div>` : ''}
          <img class="zsu-card-thumb" src="${esc(thumb)}" alt="${esc(s.title)}" loading="lazy" decoding="async">
          <div class="zsu-card-overlay"></div>
          <div class="zsu-card-badges">${badgesHtml}</div>
          ${ratingHtml}
          ${durationStr ? `<div class="zsu-card-duration">${esc(durationStr)}</div>` : ''}
          <div class="zsu-card-play">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="white" style="filter:drop-shadow(0 1px 4px rgba(0,0,0,.4));"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          </div>
          <div class="zsu-card-info">
            ${s.vendor_name ? `
            <div class="zsu-card-vendor">
              <img class="zsu-card-vendor-avatar" src="${esc(s.vendor_avatar || '')}" alt="${esc(s.vendor_name)}" loading="lazy">
              <span class="zsu-card-vendor-name">${esc(s.vendor_name)}</span>
            </div>` : ''}
            <div class="zsu-card-title">${esc(s.title)}</div>
          </div>
          <div class="zsu-card-shine"></div>
          ${ZSU.show_like ? `<button class="zsu-card-quick-like${isLiked ? ' liked' : ''}" data-id="${s.id}" aria-label="Like" title="Like">${isLiked ? '❤️' : '🤍'}</button>` : ''}
        </div>`;

      // Enter key opens lightbox
      el.addEventListener('keydown', (e) => { if (e.key === 'Enter') el.click(); });
      return el;
    },

    // ── Animate grid cards in ─────────────────────────────────
    animateCardsIn(cards) {
      cards.forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(22px)';
        setTimeout(() => {
          card.style.transition = 'opacity 0.4s ease, transform 0.4s cubic-bezier(0.34,1.56,0.64,1)';
          card.style.opacity = '1';
          card.style.transform = 'translateY(0)';
          card.classList.add('zsu-anim-done');
        }, i * 45);
      });
    },

    // ─── Set view mode ────────────────────────────────────────
    setViewMode(mode) {
      document.body.classList.toggle('feed-view', mode === 'feed');
      document.getElementById('zsuViewGrid')?.classList.toggle('active', mode === 'grid');
      document.getElementById('zsuViewFeed')?.classList.toggle('active', mode === 'feed');
    },

    // ── Set category ──────────────────────────────────────────
    setCategory(cat, pillEl) {
      this.state.cat = cat;
      document.querySelectorAll('.zsu-cat-pill').forEach((p) => {
        p.classList.toggle('active', p === pillEl || p.dataset.cat === cat);
        p.setAttribute('aria-selected', p === pillEl || p.dataset.cat === cat ? 'true' : 'false');
      });

      const $tag   = document.getElementById('zsuActiveFilterTag');
      const $label = document.getElementById('zsuActiveFilterLabel');
      if (cat && $tag && $label) {
        $label.textContent = `📂 ${pillEl?.textContent?.trim() || cat}`;
        $tag.style.display = 'flex';
      } else if ($tag) {
        $tag.style.display = 'none';
      }

      const $heading = document.getElementById('zsuGridHeading');
      if ($heading) {
        $heading.innerHTML = cat
          ? `<span class="zsu-heading-icon">📂</span> ${esc(pillEl?.textContent?.trim() || cat)}`
          : '<span class="zsu-heading-icon">🎬</span> Latest Shorts';
      }

      this.loadGrid(1, true);
    },

    // ═══════════════════════════════════════════════════════
    //  FEATURED RAIL
    // ═══════════════════════════════════════════════════════
    loadFeaturedRail() {
      if (!ZSU.show_featured) return;
      const $rail = document.getElementById('zsuFeaturedRail');
      if (!$rail) return;

      ajax('zsu_get_featured').then((res) => {
        if (!res.success || !res.shorts.length) {
          document.getElementById('zsuFeaturedSection')?.remove();
          return;
        }
        $rail.innerHTML = '';
        res.shorts.forEach((s) => {
          s._in_featured_rail = true;  // flag so ELITE badge shows only here
          const el = this.buildShortCard(s);
          $rail.appendChild(el);
        });
        if (ZSU.tilt_cards) this.initTilt($rail.querySelectorAll('.zsu-short-card'));
      });
    },

    // ═══════════════════════════════════════════════════════
    //  CATEGORY RAILS
    // ═══════════════════════════════════════════════════════
    loadCategoryRails() {
      document.querySelectorAll('.zsu-cat-rail-section').forEach((section) => {
        const cat   = section.dataset.cat;
        const railId = `zsuRailScroll-${cat}`;
        const $rail = document.getElementById(railId);
        if (!$rail) return;

        ajax('zsu_get_cat_rail', { cat, per_page: 8 }).then((res) => {
          if (!res.success || !res.shorts.length) {
            section.style.display = 'none';
            return;
          }
          $rail.innerHTML = '';
          res.shorts.forEach((s) => {
            const el = this.buildShortCard(s);
            $rail.appendChild(el);
          });
        });
      });
    },

    // ═══════════════════════════════════════════════════════
    //  LIGHTBOX
    // ═══════════════════════════════════════════════════════
    openLightboxFromGrid(id) {
      const idx = this.state.shorts.findIndex((s) => s.id === id);
      if (idx === -1) {
        // Short not in current grid — fetch it
        this.fetchAndOpenLightbox(id);
        return;
      }
      this.state.lbShorts = [...this.state.shorts];
      this.openLightbox(idx);
    },

    fetchAndOpenLightbox(id) {
      ajax('zsu_get_short_data', { short_id: id, with_adjacent: 1 }).then((res) => {
        if (!res.success) return;
        // If server returned adjacent shorts, use them so user can scroll the chain
        if (res.shorts && res.shorts.length > 1) {
          this.state.lbShorts = res.shorts;
          this.openLightbox(typeof res.focal_index === 'number' ? res.focal_index : 0);
        } else {
          this.state.lbShorts = [res.short];
          this.openLightbox(0);
        }
      });
    },

    openLightbox(index) {
      const lb = document.getElementById('zsuLightbox');
      if (!lb) return;

      this.state.lbOpen  = true;
      this.state.lbIndex = index;
      lb.style.display   = 'flex';
      document.body.classList.add('zsu-no-scroll');

      // Build stack (also resets scroll listener)
      this.buildLbStack();

      // Update sidebar FIRST so follow btn gets vendor_id
      this.updateSidebar(this.state.lbShorts[index]);

      // Scroll to correct slide — use rAF to ensure stack is rendered first
      requestAnimationFrame(() => {
        this.scrollToSlide(index, false);
        // Play after scroll settles
        setTimeout(() => this.playSlide(index), 50);
      });

      // Update URL safely
      try { history.pushState({ zsuShortId: this.state.lbShorts[index].id }, '', `${ZSU.shorts_url}/v/${this.state.lbShorts[index].id}/`); } catch(e) {}

      // Preload adjacent videos
      this.preloadAdjacentVideos(index);
      // Note: recordView + startWatchTimer called by onSlideChange / playSlide
    },

    buildLbStack() {
      const $old = document.getElementById('zsuLbStack');
      if (!$old) return;

      // Replace with a fresh clone to remove ALL old scroll listeners
      const stack = $old.cloneNode(false);
      // Force stack height to exact pixels
      stack.style.height = window.innerHeight + 'px';
      $old.parentNode.replaceChild(stack, $old);
      // NOTE: use `stack` directly — do NOT re-query getElementById here

      this.state.lbShorts.forEach((s, i) => {
        stack.appendChild(this.buildLbSlide(s, i));
      });

      // Single scroll listener on the new node
      let _t;
      stack.addEventListener('scroll', () => {
        clearTimeout(_t);
        _t = setTimeout(() => {
          const newIdx = Math.round(stack.scrollTop / window.innerHeight);
          if (newIdx !== this.state.lbIndex && newIdx >= 0 && newIdx < this.state.lbShorts.length) {
            this.onSlideChange(newIdx);
          }
        }, 80);
      }, { passive: true });
    },

    buildLbSlide(s, index) {
      const slide = document.createElement('div');
      slide.className = 'zsu-lb-slide';
      slide.dataset.index = index;
      slide.dataset.id    = s.id;
      slide.style.height    = window.innerHeight + 'px';
      slide.style.minHeight = window.innerHeight + 'px';
      slide.style.maxHeight = window.innerHeight + 'px';
      slide.style.position  = 'relative';

      const thumb      = s.thumb_url     || '';
      const ratio      = s.ratio         || '9:16';
      const vendorName = s.vendor_name   || '';
      const vendorAvt  = s.vendor_avatar || '';
      const isLiked    = this.state.likedIds.has(s.id);

      slide.innerHTML = `
        <div class="zsu-lb-video-wrap" data-ratio="${esc(ratio)}"
             style="position:relative;width:auto;height:auto;max-width:100vw;max-height:100vh;display:flex;align-items:center;justify-content:center;background:#000;">
          <img class="zsu-lb-video-thumb" id="zsuThumb${index}"
               src="${esc(thumb)}" alt="${esc(s.title)}" loading="lazy"
               style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);max-width:100vw;max-height:100vh;width:auto;height:auto;object-fit:contain;display:block;">
          <video class="zsu-lb-video" id="zsuVid${index}"
                 playsinline webkit-playsinline
                 ${ZSU.loop ? 'loop' : ''}
                 preload="none"
                 data-src="${esc(s.video_url)}"
                 style="display:block;width:auto;height:auto;max-width:100vw;max-height:100vh;object-fit:contain;background:#000;">
          </video>
          <div class="zsu-lb-video-controls" data-index="${index}" style="position:absolute;inset:0;z-index:5;touch-action:pan-y;">
            <div class="zsu-lb-big-play" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);pointer-events:none;">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            </div>
          </div>
        </div>

        <!-- Flipkart-style bottom gradient overlay -->
        <div class="zsu-lb-overlay" style="position:absolute;bottom:0;left:0;right:0;height:75%;z-index:20;background:linear-gradient(to top,rgba(0,0,0,0.88) 0%,rgba(0,0,0,0.55) 38%,transparent 70%);padding:0 14px 90px;display:flex;flex-direction:column;justify-content:flex-end;pointer-events:none;box-sizing:border-box;">
        ${vendorName ? `
          <div class="zsu-lb-ov-vendor">
            <a class="zsu-lb-ov-vendor-link" href="${esc(s.vendor_store_url || '#')}" target="_blank" rel="noopener noreferrer" style="display:flex;align-items:center;gap:8px;text-decoration:none;flex:1;min-width:0;">
              <img class="zsu-lb-ov-avatar" src="${esc(vendorAvt)}" alt="${esc(vendorName)}"
                   onerror="this.style.display='none'">
              <div>
                <div class="zsu-lb-ov-vendor-name">${esc(vendorName)}</div>
                ${s.vendor_store ? `<div class="zsu-lb-ov-vendor-store">${esc(s.vendor_store)}</div>` : ''}
              </div>
            </a>
            ${ZSU.show_follow && s.vendor_id ? `<button class="zsu-lb-ov-follow zsu-ov-follow-btn" data-vendor-id="${esc(s.vendor_id)}">Follow</button>` : ''}
          </div>` : ''}
          <div class="zsu-lb-ov-title">${esc(s.title)}</div>
          ${s.excerpt ? `<div class="zsu-lb-ov-desc">${esc(s.excerpt)}</div>` : ''}

        </div>

        <!-- Right-rail action buttons -->
        <div class="zsu-lb-rail" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);z-index:26;display:flex;flex-direction:column;gap:16px;align-items:center;">
          ${ZSU.show_like ? `
          <button class="zsu-lb-rail-btn zsu-rail-like-btn ${isLiked?'liked':''}" data-short-id="${esc(s.id)}" aria-label="Like">
            <div class="zsu-lb-rail-icon zsu-heart-icon">
              ${isLiked
                ? '<svg width="26" height="26" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" fill="#fb7185" stroke="none"/></svg>'
                : '<svg width="26" height="26" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" fill="none" stroke="#fff" stroke-width="2"/></svg>'
              }
            </div>
          </button>` : ''}

          ${ZSU.show_share ? `
          <button class="zsu-lb-rail-btn zsu-rail-share-btn" aria-label="Share">
            <div class="zsu-lb-rail-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            </div>
            <span class="zsu-lb-rail-label">Share</span>
          </button>` : ''}

          <button class="zsu-lb-rail-btn zsu-rail-mute-btn" aria-label="Mute">
            <div class="zsu-lb-rail-icon" id="zsuRailMuteIcon${index}">
              ${this.state.isMuted
                ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/></svg>'
                : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>'
              }
            </div>
            <span class="zsu-lb-rail-label">${this.state.isMuted ? 'Unmute' : 'Mute'}</span>
          </button>
        </div>`;

      // Click video area → toggle play/pause + double-tap to like
      let _tapTimer = null;
      slide.querySelector('.zsu-lb-video-controls').addEventListener('click', (e) => {
        if (_tapTimer) {
          // Double tap detected
          clearTimeout(_tapTimer);
          _tapTimer = null;
          this.doubleTapLike(s.id, slide, e);
        } else {
          _tapTimer = setTimeout(() => {
            _tapTimer = null;
            this.togglePlayPause();
          }, 250);
        }
      });

      // Like button per slide — click shows centered burst + triggers like
      const $likeBtn = slide.querySelector('.zsu-rail-like-btn');
      if ($likeBtn) {
        $likeBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          // Always show centered heart burst on manual click too
          this._burstHeart(slide, $likeBtn);
          this.handleLike(s.id, $likeBtn);
        });
      }

      // Share button per slide
      const $shareBtn = slide.querySelector('.zsu-rail-share-btn');
      if ($shareBtn) {
        $shareBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          this.openShareSheet();
        });
      }

      // Mute button per slide
      const $muteBtn = slide.querySelector('.zsu-rail-mute-btn');
      if ($muteBtn) {
        $muteBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          this.toggleMute();
        });
      }

      // Follow button in overlay
      const $followBtn = slide.querySelector('.zsu-ov-follow-btn');
      if ($followBtn) {
        $followBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          this.handleFollow($followBtn);
        });
      }

      return slide;
    },

    scrollToSlide(index, animate = true) {
      const $stack = document.getElementById('zsuLbStack');
      if (!$stack) return;
      const h = window.innerHeight;
      $stack.scrollTo({ top: index * h, behavior: animate ? 'smooth' : 'instant' });
    },

    onSlideChange(newIdx) {
      // Pause previous
      this.pauseSlide(this.state.lbIndex);
      this.stopWatchTimer();

      this.state.lbIndex = newIdx;
      const s = this.state.lbShorts[newIdx];
      if (!s) return;

      this.updateSidebar(s);
      this.playSlide(newIdx);
      this.recordView(s.id);
      this.startWatchTimer(s.id);
      this.preloadAdjacentVideos(newIdx);

      // Update URL
      try { history.replaceState({ zsuShortId: s.id }, '', `${ZSU.shorts_url}/v/${s.id}/`); } catch(e) {}

      // Preload more grid items if near end
      if (newIdx >= this.state.lbShorts.length - 3) {
        this.loadMoreIntoLb();
      }
    },

    playSlide(index) {
      const vid   = document.getElementById(`zsuVid${index}`);
      const thumb = document.getElementById(`zsuThumb${index}`);
      if (!vid) return;

      // Lazy load src
      if (!vid.src && vid.dataset.src) {
        vid.src = vid.dataset.src;
      }

      vid.muted = this.state.isMuted;

      // ── Infinite loop: auto-advance to next reel when video ends ──
      if (!vid._zsuEndedBound) {
        vid._zsuEndedBound = true;
        vid.addEventListener('ended', () => {
          // Navigate to next; wraps around to index 0 if at end
          const nextIdx = this.state.lbIndex + 1;
          if (nextIdx < this.state.lbShorts.length) {
            this.lbNavigate(1);
          } else {
            // Wrap: go back to first reel
            this.pauseSlide(this.state.lbIndex);
            this.state.lbIndex = 0;
            const firstSlide = this.state.lbShorts[0];
            if (firstSlide) {
              this.scrollToSlide(0, true);
              this.updateSidebar(firstSlide);
              this.playSlide(0);
              this.recordView(firstSlide.id);
              this.startWatchTimer(firstSlide.id);
            }
          }
        });
      }

      const playPromise = vid.play();
      if (playPromise !== undefined) {
        playPromise.then(() => {
          if (thumb) thumb.classList.add('hidden');
          const slide = vid.closest('.zsu-lb-slide');
          if (slide) slide.classList.remove('paused');
          this.state.videosPlaying.set(index, true);
          this.startProgressTracker(vid, index);
        }).catch(() => {
          // Autoplay blocked — show big play button
          const slide = vid.closest('.zsu-lb-slide');
          if (slide) slide.classList.add('paused');
        });
      }
    },

    pauseSlide(index) {
      const vid = document.getElementById(`zsuVid${index}`);
      if (!vid) return;
      vid.pause();
      this.state.videosPlaying.set(index, false);
      const slide = vid.closest('.zsu-lb-slide');
      if (slide) slide.classList.add('paused');
      clearInterval(this._progressInterval);
    },

    togglePlayPause() {
      const vid = document.getElementById(`zsuVid${this.state.lbIndex}`);
      if (!vid) return;
      if (vid.paused) {
        this.playSlide(this.state.lbIndex);
      } else {
        this.pauseSlide(this.state.lbIndex);
      }
    },

    preloadAdjacentVideos(index) {
      if (!ZSU.preload_next) return;
      const nextIdx = index + 1;
      if (nextIdx < this.state.lbShorts.length) {
        const vid = document.getElementById(`zsuVid${nextIdx}`);
        if (vid && !vid.src && vid.dataset.src) {
          vid.preload = 'metadata';
          vid.src     = vid.dataset.src;
        }
      }
    },

    // Progress bar tracker
    startProgressTracker(vid, index) {
      clearInterval(this._progressInterval);
      const $bar = document.getElementById('zsuLbProgressBar');
      this._progressInterval = setInterval(() => {
        if (!vid || vid.paused || !vid.duration) return;
        const pct = (vid.currentTime / vid.duration) * 100;
        if ($bar) $bar.style.width = pct + '%';

        // Heatmap second mark every 2 seconds — only fire once per second mark
        if (ZSU.heatmap && Math.floor(vid.currentTime) % 2 === 0) {
          const sid = this.state.lbShorts[index]?.id;
          const mark = Math.floor(vid.currentTime);
          if (sid && mark !== this._lastHeatMark) {
            this._lastHeatMark = mark;
            ajax('zsu_update_heatmap', { short_id: sid, second_mark: mark });
          }
        }
      }, 250);
    },

    // Navigate lightbox prev/next
    lbNavigate(dir) {
      const newIdx = this.state.lbIndex + dir;
      if (newIdx < 0) return;

      // If next index is beyond loaded shorts, load more first
      if (newIdx >= this.state.lbShorts.length) {
        this.loadMoreIntoLb();
        return;
      }

      // Pause current, update state, update URL
      this.pauseSlide(this.state.lbIndex);
      this.state.lbIndex = newIdx;

      const s = this.state.lbShorts[newIdx];
      if (!s) return;

      // Scroll to slide
      this.scrollToSlide(newIdx, true);

      // Update URL immediately on button click
      try { history.replaceState({ zsuShortId: s.id }, '', `${ZSU.shorts_url}/v/${s.id}/`); } catch(e) {}

      // Update sidebar + play
      this.updateSidebar(s);
      this.playSlide(newIdx);
      this.recordView(s.id);
      this.startWatchTimer(s.id);
      this.preloadAdjacentVideos(newIdx);

      if (newIdx >= this.state.lbShorts.length - 3) {
        this.loadMoreIntoLb();
      }
    },

    // Load more shorts into lb queue
    loadMoreIntoLb() {
      if (this.state.allLoaded) return;
      ajax('zsu_get_shorts', {
        page:     this.state.page + 1,
        per_page: ZSU.per_page,
        filter:   this.state.filter,
        cat:      this.state.cat,
      }).then((res) => {
        if (!res.success) return;
        const $stack = document.getElementById('zsuLbStack');
        res.shorts.forEach((s) => {
          if (!this.state.shorts.find((x) => x.id === s.id)) {
            this.state.shorts.push(s);
            this.state.lbShorts.push(s);
            const slide = this.buildLbSlide(s, this.state.lbShorts.length - 1);
            $stack?.appendChild(slide);
          }
        });
        this.state.page = res.page;
        if (res.page >= res.pages) this.state.allLoaded = true;
      });
    },

    closeLightbox() {
      const lb = document.getElementById('zsuLightbox');
      if (!lb) return;

      this.pauseSlide(this.state.lbIndex);
      this.stopWatchTimer();
      this._clearShopChips();
      this._closeShopSheet();

      lb.style.display = 'none';
      this.state.lbOpen = false;

      // Thoroughly remove all scroll locks — belt and suspenders
      document.body.classList.remove('zsu-no-scroll');
      document.body.style.overflow = '';
      document.body.style.position = '';
      document.body.style.top = '';
      document.documentElement.style.overflow = '';

      clearInterval(this._progressInterval);

      // Reset progress bar
      const $bar = document.getElementById('zsuLbProgressBar');
      if ($bar) $bar.style.width = '0%';

      // Restore URL safely — only pushState if we actually changed it
      const currentPath = window.location.pathname;
      const shortsBase  = new URL(ZSU.shorts_url).pathname;
      if (currentPath.startsWith(shortsBase)) {
        try { history.pushState({}, '', shortsBase + '/'); } catch(e) {}
      }
    },

    // Update sidebar for a short
    updateSidebar(s) {
      if (!s) return;
      this.state.currentShortId = s.id;

      setText('zsuLbTitle', s.title);
      setText('zsuLbDesc',  s.excerpt || '');
      setText('zsuLbViews',  s.views);
      setText('zsuLbLikes',  s.likes);
      setText('zsuLbShares', s.shares);

      const $rating = document.getElementById('zsuLbRating');
      if ($rating) $rating.textContent = s.rating_avg > 0 ? `${parseFloat(s.rating_avg).toFixed(1)} (${s.rating_count})` : '—';

      // Vendor
      setAttr('zsuLbAvatar', 'src', s.vendor_avatar || '');
      setAttr('zsuLbAvatar', 'alt', s.vendor_name || '');
      setText('zsuLbVendorName',  s.vendor_name  || '');
      setText('zsuLbVendorStore', s.vendor_store || '');

      // Follow button — sync with Dokan Pro
      const $follow = document.getElementById('zsuLbFollowBtn');
      if ($follow) {
        const vid = parseInt(s.vendor_id || 0, 10);
        $follow.dataset.vendorId = vid || '';
        $follow.style.display = vid ? '' : 'none';

        if (vid) {
          // Use cached state if known, else fetch live from server (Dokan-backed)
          if (this.state.followedIds.has(vid) || this.state.followedIds.has(-vid)) {
            const isFollowing = this.state.followedIds.has(vid);
            this._applyFollowState($follow, isFollowing);
          } else {
            // Check live — covers Dokan follows made outside this session
            ajax('zsu_check_follow', { vendor_id: vid }).then((res) => {
              const isFollowing = !!(res && res.following);
              if (isFollowing) this.state.followedIds.add(vid);
              else this.state.followedIds.add(-vid); // mark as checked+not following
              this._applyFollowState($follow, isFollowing);
            });
          }
        }
      }

      // Like button
      const $like = document.getElementById('zsuLbLikeBtn');
      if ($like) {
        $like.dataset.shortId = s.id;
        const isLiked = this.state.likedIds.has(s.id);
        $like.classList.toggle('liked', isLiked);
        $like.setAttribute('aria-pressed', isLiked ? 'true' : 'false');
        $like.querySelector('.zsu-action-icon').textContent  = isLiked ? '❤️' : '🤍';
        $like.querySelector('.zsu-action-label').textContent = s.likes;
      }

      // Comment count
      setText('zsuLbCommentCount', s.comments_count || 0);

      // Categories
      const $cats = document.getElementById('zsuLbCats');
      if ($cats) {
        $cats.innerHTML = (s.categories || []).map((c) =>
          `<a href="#" class="zsu-lb-cat-chip" data-cat="${esc(c)}">${esc(c)}</a>`
        ).join('');
      }

      // Product tray
      this.loadProductTray(s.id, s.product_ids || []);

      // Mute icon sync
      this.syncMuteIcon();

      // Rating UI
      if (ZSU.enable_ratings) this.renderRatingUI(s.id);
    },

    // ═══════════════════════════════════════════════════════
    //  PRODUCT TRAY
    // ═══════════════════════════════════════════════════════
    // ═══════════════════════════════════════════════════════
    //  YOUTUBE-STYLE FLOATING PRODUCT CHIPS
    // ═══════════════════════════════════════════════════════
    loadProductTray(shortId, productIds) {
      // Clear chips immediately on slide change
      this._clearShopChips();

      if (!Array.isArray(productIds) || !productIds.length) return;

      ajax('zsu_get_products', { ids: productIds, short_id: shortId })
      .then((res) => {
        if (!res || !res.success || !res.products || !res.products.length) return;
        this._renderShopChips(res.products, shortId);
        // Cache for sheet
        this._currentProducts = res.products;
        this._currentShortIdForShop = shortId;
      }).catch(() => {});
    },

    _clearShopChips() {
      // Remove drag listeners from previous chip
      if (this._chipDragCleanup) { this._chipDragCleanup(); this._chipDragCleanup = null; }
      const $chips = document.getElementById('zsuShopChips');
      if ($chips) { $chips.innerHTML = ''; $chips.style.display = 'none'; }
      this._currentProducts = [];
    },

    _renderShopChips(products, shortId) {
      const $chips = document.getElementById('zsuShopChips');
      if (!$chips) return;

      const count = products.length;
      if (count === 0) { $chips.innerHTML = ''; $chips.style.display = 'none'; return; }

      const p = products[0];

      $chips.innerHTML = `
        <div class="zsu-pill-wrap" id="zsuShopFab">
          <button class="zsu-product-capsule" aria-label="Shop ${count} product${count>1?'s':''}">
            <span class="zsu-capsule-img-wrap">
              <img class="zsu-capsule-img" src="${esc(p.image)}" alt="" loading="lazy"
                   onerror="this.style.opacity='0'">
            </span>
            <span class="zsu-capsule-text">
              <span class="zsu-capsule-name">${esc(p.name)}</span>
              <span class="zsu-capsule-sub">View products</span>
            </span>
            <span class="zsu-capsule-badge">${count}</span>
            <span class="zsu-capsule-arrow">›</span>
          </button>
        </div>`;

      // position: top-RIGHT — reset every slide
      $chips.style.cssText = 'display:block;position:absolute;top:14px;right:14px;left:auto;bottom:auto;z-index:50;';
      // force reflow so getBoundingClientRect is accurate after cssText set
      void $chips.offsetWidth;

      const fab = document.getElementById('zsuShopFab');
      if (!fab) return;

      // ── Tap to open ──────────────────────────────────────
      let wasDragged = false;
      fab.querySelector('.zsu-product-capsule').addEventListener('click', (e) => {
        e.stopPropagation();
        if (wasDragged) { wasDragged = false; return; }
        this._openShopSheet();
      });

      // ── Drag (touch + mouse) ─────────────────────────────
      let isDragging = false, startX, startY, origLeft, origTop;

      const onDragStart = (cx, cy) => {
        isDragging = true; wasDragged = false;
        // Use offsetLeft/offsetTop — these are relative to position:absolute parent (the slide)
        origLeft = $chips.offsetLeft;
        origTop  = $chips.offsetTop;
        startX = cx; startY = cy;
        $chips.style.left   = origLeft + 'px';
        $chips.style.right  = 'auto';
        $chips.style.top    = origTop  + 'px';
        $chips.style.bottom = 'auto';
        $chips.style.transition = 'none';
        fab.classList.add('zsu-dragging');
      };
      const onDragMove = (cx, cy) => {
        if (!isDragging) return;
        wasDragged = true;
        const dx = cx - startX, dy = cy - startY;
        const parent = $chips.offsetParent || document.getElementById('zsuLightbox') || document.documentElement;
        const maxW = parent.clientWidth  - $chips.offsetWidth  - 4;
        const maxH = parent.clientHeight - $chips.offsetHeight - 4;
        $chips.style.left = Math.max(4, Math.min(maxW, origLeft + dx)) + 'px';
        $chips.style.top  = Math.max(4, Math.min(maxH, origTop  + dy)) + 'px';
      };
      const onDragEnd = () => {
        if (!isDragging) return;
        isDragging = false;
        $chips.style.transition = '';
        fab.classList.remove('zsu-dragging');
      };

      // Remove any previous drag listeners from old chips
      if (this._chipDragCleanup) this._chipDragCleanup();

      const onTouchMove = (e) => { if (!isDragging) return; onDragMove(e.touches[0].clientX, e.touches[0].clientY); };
      const onMouseMove = (e) => { if (isDragging) onDragMove(e.clientX, e.clientY); };

      // Touch
      fab.addEventListener('touchstart', (e) => {
        onDragStart(e.touches[0].clientX, e.touches[0].clientY);
      }, { passive: true });
      document.addEventListener('touchmove', onTouchMove, { passive: true });
      document.addEventListener('touchend', onDragEnd, { passive: true });
      // Mouse
      fab.addEventListener('mousedown', (e) => { e.preventDefault(); onDragStart(e.clientX, e.clientY); });
      document.addEventListener('mousemove', onMouseMove);
      document.addEventListener('mouseup', onDragEnd);

      // Store cleanup so next slide removes these listeners
      this._chipDragCleanup = () => {
        document.removeEventListener('touchmove', onTouchMove);
        document.removeEventListener('touchend', onDragEnd);
        document.removeEventListener('mousemove', onMouseMove);
        document.removeEventListener('mouseup', onDragEnd);
      };
    },

    _openShopSheet() {
      const $sheet   = document.getElementById('zsuShopSheet');
      const $overlay = document.getElementById('zsuShopSheetOverlay');
      const $list    = document.getElementById('zsuShopSheetList');
      if (!$sheet || !this._currentProducts?.length) return;

      if ($list) $list.innerHTML = (this._currentProducts || []).map((p) => this._buildSheetCard(p)).join('');
      if ($overlay) $overlay.style.display = 'block';
      $sheet.style.display = 'block';
      requestAnimationFrame(() => $sheet.classList.add('open'));
    },

    _closeShopSheet() {
      const $sheet   = document.getElementById('zsuShopSheet');
      const $overlay = document.getElementById('zsuShopSheetOverlay');
      if (!$sheet) return;
      $sheet.classList.remove('open');
      setTimeout(() => {
        $sheet.style.display   = 'none';
        $overlay.style.display = 'none';
      }, 320);
    },

    _buildSheetCard(p) {
      const oldPrice = p.on_sale ? `<del style="font-size:11px;color:rgba(255,255,255,0.45);margin-left:4px;">${wc_price(p.regular_price)}</del>` : '';
      const url      = esc(p.url || p.permalink || '#');
      return `
        <a href="${url}" target="_blank" rel="noopener noreferrer" class="zsu-sheet-card" style="text-decoration:none;">
          <img class="zsu-sheet-card-img" src="${esc(p.image)}" alt="${esc(p.name)}" loading="lazy">
          <div class="zsu-sheet-card-info">
            <div class="zsu-sheet-card-name">${esc(p.name)}</div>
            <div class="zsu-sheet-card-price">${p.price}${oldPrice}</div>
            ${p.rating > 0 ? `<div class="zsu-sheet-card-stars">${'★'.repeat(Math.round(p.rating))}${'☆'.repeat(5-Math.round(p.rating))} <span style="font-size:11px;color:rgba(255,255,255,0.5)">(${p.review_count||0})</span></div>` : ''}
          </div>
          <div class="zsu-sheet-card-arrow">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.5)" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
          </div>
        </a>`;
    },

    // ═══════════════════════════════════════════════════════
    //  PRODUCT MODAL
    // ═══════════════════════════════════════════════════════
    openProductModal(productId) {
      const $modal   = document.getElementById('zsuProductModal');
      const $overlay = document.getElementById('zsuProductOverlay');
      const $body    = document.getElementById('zsuProductModalBody');

      if (!$modal || !$body) return;

      // Find product in current tray data
      $body.innerHTML = '<div style="text-align:center;padding:40px;"><div class="zsu-spinner-ring" style="margin:auto;"></div></div>';
      $modal.style.display = 'block';
      if ($overlay) $overlay.style.display = 'block';
      document.body.classList.add('zsu-no-scroll');

      ajax('zsu_get_products', { ids: [productId], short_id: this.state.currentShortId })
      .then((res) => {
        if (!res.success || !res.products.length) { this.closeProductModal(); return; }
        const p = res.products[0];
        const inWish = this.state.wishlist.has(p.id);

        $body.innerHTML = `
          <img class="zsu-pm-img" src="${esc(p.image)}" alt="${esc(p.name)}" loading="lazy">
          <h3 class="zsu-pm-name">${esc(p.name)}</h3>
          <div class="zsu-pm-price">
            ${p.price}
            ${p.on_sale ? `<del>${wc_price(p.regular_price)}</del>` : ''}
            ${p.on_sale ? `<span class="zsu-pm-sale-badge">-${p.discount_pct}%</span>` : ''}
          </div>
          ${p.rating > 0 ? `
          <div class="zsu-pm-rating">
            <span class="zsu-pm-stars">${'⭐'.repeat(Math.round(p.rating))}</span>
            ${p.rating.toFixed(1)} (${p.review_count} reviews)
          </div>` : ''}
          <div class="zsu-pm-meta">
            ${p.sku ? `<span class="zsu-pm-meta-item">SKU: ${esc(p.sku)}</span>` : ''}
            ${p.type ? `<span class="zsu-pm-meta-item">${esc(p.type)}</span>` : ''}
            ${p.stock_qty !== null ? `<span class="zsu-pm-meta-item">${p.stock_qty} left</span>` : ''}
          </div>
          <div class="${p.in_stock ? 'zsu-pm-stock-ok' : 'zsu-pm-stock-out'}">
            ${p.in_stock ? '✅ In Stock' : '❌ Out of Stock'}
          </div>
          <div class="zsu-pm-actions">
            ${p.in_stock ? `<button class="zsu-pm-atc-btn" id="zsuPmAtcBtn" data-pid="${p.id}">🛒 Add to Cart</button>` : ''}
            <a href="${esc(p.url)}" class="zsu-pm-view-btn" target="_blank" rel="noopener">View Product →</a>
            ${ZSU.enable_wishlists ? `
            <button class="zsu-pm-atc-btn" style="background:${inWish ? '#f43f5e' : 'var(--zsu-surface)'};flex:0 0 auto;padding:0 16px;" id="zsuPmWishBtn" data-pid="${p.id}">
              ${inWish ? '❤️' : '🤍'}
            </button>` : ''}
          </div>`;

        on(document, 'click', '#zsuPmAtcBtn', (e) => this.addToCart(parseInt(e.target.dataset.pid, 10), e.target));
        on(document, 'click', '#zsuPmWishBtn', (e) => {
          const pid = parseInt(e.target.dataset.pid, 10);
          this.toggleWishlist(pid, this.state.currentShortId, e.target);
        });
      });
    },

    closeProductModal() {
      const $pm = document.getElementById('zsuProductModal');
      const $po = document.getElementById('zsuProductOverlay');
      if ($pm) $pm.style.display = 'none';
      if ($po) $po.style.display = 'none';
      document.body.classList.remove('zsu-no-scroll');
    },

    // ═══════════════════════════════════════════════════════
    //  SHARE SHEET
    // ═══════════════════════════════════════════════════════
    openShareSheet() {
      const s = this.state.lbShorts[this.state.lbIndex];
      if (!s) return;

      const shareUrl = `${ZSU.shorts_url}/v/${s.id}/`;
      setAttr('zsuShareThumb',      'src',         s.thumb_url);
      setText('zsuShareVideoTitle', s.title);
      setVal('zsuCopyLinkInput',    shareUrl);

      const $ss = document.getElementById('zsuShareSheet');
      const $so = document.getElementById('zsuShareOverlay');
      if ($ss) $ss.style.display = 'block';
      if ($so) $so.style.display = 'block';
      document.body.classList.add('zsu-no-scroll');

      this._shareUrl   = shareUrl;
      this._shareTitle = s.title;
      this._shareId    = s.id;
    },

    closeShareSheet() {
      const $ss = document.getElementById('zsuShareSheet');
      const $so = document.getElementById('zsuShareOverlay');
      if ($ss) $ss.style.display = 'none';
      if ($so) $so.style.display = 'none';
      document.body.classList.remove('zsu-no-scroll');
    },

    executeShare(platform) {
      const url   = encodeURIComponent(this._shareUrl   || window.location.href);
      const title = encodeURIComponent(this._shareTitle || document.title);
      const id    = this._shareId;

      const map = {
        whatsapp:  `https://api.whatsapp.com/send?text=${title}%20${url}`,
        facebook:  `https://www.facebook.com/sharer/sharer.php?u=${url}`,
        twitter:   `https://twitter.com/intent/tweet?text=${title}&url=${url}`,
        telegram:  `https://t.me/share/url?url=${url}&text=${title}`,
        linkedin:  `https://www.linkedin.com/shareArticle?mini=true&url=${url}&title=${title}`,
        pinterest: `https://pinterest.com/pin/create/button/?url=${url}&description=${title}`,
        email:     `mailto:?subject=${title}&body=${url}`,
        copy:      null,
      };

      if (platform === 'copy') {
        this.copyShareLink();
        return;
      }

      const shareUrl = map[platform];
      if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=450,noopener,noreferrer');
        if (id) ajax('zsu_record_share', { short_id: id, platform });
        this.closeShareSheet();
        toast(`Shared on ${platform}!`, 'success');
      }
    },

    copyShareLink() {
      const input = document.getElementById('zsuCopyLinkInput');
      const btn   = document.getElementById('zsuCopyLinkBtn');
      if (!input) return;

      navigator.clipboard?.writeText(input.value).then(() => {
        if (btn) { btn.textContent = '✅ Copied!'; btn.classList.add('copied'); }
        setTimeout(() => { if (btn) { btn.textContent = 'Copy'; btn.classList.remove('copied'); } }, 2200);
        toast('Link copied!', 'success');
        if (this._shareId) ajax('zsu_record_share', { short_id: this._shareId, platform: 'copy' });
      }).catch(() => {
        input.select();
        document.execCommand('copy');
        toast('Link copied!', 'success');
      });
    },

    // Alias used by per-slide rail buttons
    handleLike(shortId, btnEl) {
      if (!ZSU.logged_in) {
        // Still show the filled heart + burst visually even when logged out
        const slide = btnEl?.closest('.zsu-lb-slide');
        if (slide) this._burstHeart(slide, btnEl);
        // Fill the heart icon immediately
        const icon = btnEl?.querySelector('.zsu-lb-rail-icon');
        if (icon) {
          icon.classList.add('zsu-heart-pop');
          setTimeout(() => icon.classList.remove('zsu-heart-pop'), 400);
          icon.innerHTML = '<svg width="26" height="26" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" fill="#fb7185" stroke="none"/></svg>';
          btnEl.classList.add('liked');
        }
        this.showLoginPrompt();
        return;
      }
      this.toggleLike(shortId, btnEl);
    },

    // Alias for per-slide overlay follow button — delegates to toggleFollow
    handleFollow(btnEl) {
      const vid = parseInt(btnEl.dataset.vendorId || 0, 10);
      if (!vid) return;
      this.toggleFollow(vid, btnEl);
    },

    // ═══════════════════════════════════════════════════════
    //  LIKE SYSTEM
    // ═══════════════════════════════════════════════════════

    // Burst heart animation — always shown at center of slide
    _burstHeart(slide, fromBtn) {
      const size = 110;
      const heart = document.createElement('div');
      heart.className = 'zsu-dt-heart';
      heart.innerHTML = `<svg viewBox="0 0 24 24" width="${size}" height="${size}"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" fill="#fb7185" stroke="none"/></svg>`;
      // Always center in slide
      heart.style.cssText = `position:absolute;left:50%;top:50%;transform:translate(-50%,-50%) scale(0);z-index:100;pointer-events:none;animation:zsuDtHeartBurst 0.75s cubic-bezier(0.34,1.56,0.64,1) forwards;`;
      slide.appendChild(heart);
      setTimeout(() => heart.remove(), 800);
      if (ZSU.confetti_like && fromBtn) this.burstConfetti(fromBtn);
    },

    // Double-tap like: shows big centered heart burst + triggers like
    doubleTapLike(shortId, slide, event) {
      const $railLike = slide.querySelector('.zsu-rail-like-btn');
      this._burstHeart(slide, $railLike);

      // Only like if not already liked
      if (!this.state.likedIds.has(shortId)) {
        this.toggleLike(shortId, $railLike);
      }
    },

    toggleLike(shortId, btnEl) {
      ajax('zsu_record_like', { short_id: shortId }).then((res) => {
        if (!res.success) return;

        const isLiked = res.action === 'liked';

        // Update state
        if (isLiked) this.state.likedIds.add(shortId);
        else         this.state.likedIds.delete(shortId);

        // Update ALL like buttons for this short
        document.querySelectorAll(`.zsu-card-quick-like[data-id="${shortId}"]`).forEach((b) => {
          b.classList.toggle('liked', isLiked);
          b.textContent = isLiked ? '❤️' : '🤍';
        });

        const $lbBtn = document.getElementById('zsuLbLikeBtn');
        if ($lbBtn && parseInt($lbBtn.dataset.shortId, 10) === shortId) {
          $lbBtn.classList.toggle('liked', isLiked);
          $lbBtn.setAttribute('aria-pressed', isLiked ? 'true' : 'false');
          $lbBtn.querySelector('.zsu-action-icon').textContent  = isLiked ? '❤️' : '🤍';
          $lbBtn.querySelector('.zsu-action-label').textContent = res.raw;
        }

        setText('zsuLbLikes', res.likes);

        // Update grid card stats
        const $card = document.querySelector(`.zsu-short-card[data-id="${shortId}"]`);
        if ($card) {
          const $stat = $card.querySelector('.zsu-card-stat:nth-child(2)');
          if ($stat) $stat.lastChild.textContent = ' ' + res.likes;
        }

        // Confetti on like
        if (isLiked && ZSU.confetti_like) this.burstConfetti(btnEl);

        // Update per-slide overlay stats + rail button for current slide
        const currentSlide = document.querySelector(`.zsu-lb-slide[data-id="${shortId}"]`);
        if (currentSlide) {
          // overlay likes count
          const $ovLikes = currentSlide.querySelector('.zsu-ov-likes');
          if ($ovLikes) $ovLikes.textContent = res.likes;
          // rail like button icon + count
          const $railLike = currentSlide.querySelector('.zsu-rail-like-btn');
          if ($railLike) {
            $railLike.classList.toggle('liked', isLiked);
            const $railIcon = $railLike.querySelector('.zsu-lb-rail-icon');
            if ($railIcon) {
              $railIcon.classList.add('zsu-heart-pop');
              setTimeout(() => $railIcon.classList.remove('zsu-heart-pop'), 400);
              $railIcon.innerHTML = isLiked
                ? '<svg width="26" height="26" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" fill="#fb7185" stroke="none"/></svg>'
                : '<svg width="26" height="26" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" fill="none" stroke="#fff" stroke-width="2"/></svg>';
            }
            const $railCount = $railLike.querySelector('.zsu-rail-like-count');
            if ($railCount) $railCount.textContent = res.likes;
          }
        }

        // Cookie persistence
        const key = `zsu_liked_${shortId}`;
        if (isLiked) document.cookie = `${key}=1;max-age=${365*24*3600};path=/;SameSite=Lax`;
        else         document.cookie = `${key}=;max-age=0;path=/`;
      });
    },

    loadLikedFromCookies() {
      const cookies = document.cookie.split(';');
      cookies.forEach((c) => {
        const m = c.trim().match(/^zsu_liked_(\d+)=1$/);
        if (m) this.state.likedIds.add(parseInt(m[1], 10));
      });
    },

    // ═══════════════════════════════════════════════════════
    //  FOLLOW SYSTEM
    // ═══════════════════════════════════════════════════════
    _applyFollowState(btn, isFollowing) {
      if (!btn) return;
      btn.classList.toggle('following', isFollowing);
      btn.setAttribute('aria-pressed', isFollowing ? 'true' : 'false');
      btn.disabled = false; // re-enable after AJAX

      // Support both: button with inner .zsu-follow-label span, and plain text buttons
      const label = btn.querySelector('.zsu-follow-label');
      if (label) {
        label.textContent = isFollowing ? 'Following ✓' : 'Follow';
      } else {
        // Plain text button (overlay follow btn) — update textContent directly
        btn.textContent = isFollowing ? 'Following ✓' : 'Follow';
      }

      // Smooth visual feedback
      btn.style.transition = 'all 0.25s cubic-bezier(0.34,1.56,0.64,1)';
      btn.style.transform = 'scale(1.12)';
      setTimeout(() => { btn.style.transform = ''; }, 280);
    },

    toggleFollow(vendorId, btnEl) {
      if (!ZSU.logged_in) { toast('Please log in to follow vendors', 'info'); return; }
      if (!vendorId || !btnEl) return;
      if (btnEl.disabled) return; // prevent double-click

      // Optimistic UI: disable + show loading state immediately
      btnEl.disabled = true;
      const prevText = btnEl.querySelector('.zsu-follow-label')?.textContent || btnEl.textContent;
      const isCurrentlyFollowing = this.state.followedIds.has(vendorId);
      const action = isCurrentlyFollowing ? 'zsu_unfollow_vendor' : 'zsu_follow_vendor';

      // Show loading state
      const labelEl = btnEl.querySelector('.zsu-follow-label');
      if (labelEl) labelEl.textContent = '…';
      else btnEl.textContent = '…';

      ajax(action, { vendor_id: vendorId })
        .then((res) => {
          if (!res || !res.success) {
            // Revert on failure
            if (labelEl) labelEl.textContent = prevText;
            else btnEl.textContent = prevText;
            btnEl.disabled = false;
            toast('Could not update follow status', 'info');
            return;
          }
          // Clear cached state (both positive and negative markers)
          this.state.followedIds.delete(vendorId);
          this.state.followedIds.delete(-vendorId);
          // Set new confirmed state
          if (res.following) this.state.followedIds.add(vendorId);
          else               this.state.followedIds.add(-vendorId);

          // Update ALL follow buttons for this vendor on the page
          document.querySelectorAll(`[data-vendor-id="${vendorId}"]`).forEach(b => {
            this._applyFollowState(b, res.following);
          });

          toast(res.following ? '✓ Following!' : 'Unfollowed', res.following ? 'success' : 'info');
        })
        .catch(() => {
          if (labelEl) labelEl.textContent = prevText;
          else btnEl.textContent = prevText;
          btnEl.disabled = false;
        });
    },

    // ═══════════════════════════════════════════════════════
    //  WISHLIST
    // ═══════════════════════════════════════════════════════
    loadWishlist() {
      if (!ZSU.logged_in || !ZSU.enable_wishlists) return;
      ajax('zsu_get_wishlist').then((res) => {
        if (res.success) {
          (res.product_ids || []).forEach((id) => this.state.wishlist.add(id));
        }
      });
    },

    toggleWishlist(productId, shortId, btnEl) {
      if (!ZSU.logged_in) { toast('Please sign in to save products', 'info'); return; }

      ajax('zsu_wishlist_toggle', { product_id: productId, short_id: shortId }).then((res) => {
        if (!res.success) return;
        if (res.in_wishlist) {
          this.state.wishlist.add(productId);
          if (btnEl) { btnEl.textContent = '❤️'; btnEl.classList.add('wishlisted'); }
          toast('Added to wishlist!', 'success');
        } else {
          this.state.wishlist.delete(productId);
          if (btnEl) { btnEl.textContent = '🤍'; btnEl.classList.remove('wishlisted'); }
          toast('Removed from wishlist', 'info');
        }
      });
    },

    // ═══════════════════════════════════════════════════════
    //  ADD TO CART
    // ═══════════════════════════════════════════════════════
    addToCart(productId, btnEl) {
      if (!productId) return;
      const orig = btnEl?.textContent;
      if (btnEl) btnEl.textContent = '⏳ Adding…';

      // WooCommerce AJAX add to cart
      fetch(`${ZSU.home_url}/?wc-ajax=add_to_cart`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${productId}&quantity=1`,
      })
      .then((r) => r.json())
      .then((data) => {
        if (btnEl) { btnEl.textContent = '✅ Added!'; }
        setTimeout(() => { if (btnEl) btnEl.textContent = orig || '🛒 Add to Cart'; }, 2000);
        toast('Added to cart! 🛒', 'success');

        // Update WC cart fragment
        if (data.fragments) {
          Object.entries(data.fragments).forEach(([sel, html]) => {
            const el = document.querySelector(sel);
            if (el) el.outerHTML = html;
          });
        }
      })
      .catch(() => {
        if (btnEl) btnEl.textContent = orig || '🛒 Add to Cart';
        // Fallback: redirect to cart page
        window.location.href = `${ZSU.home_url}/?add-to-cart=${productId}&quantity=1`;
      });
    },

    // ═══════════════════════════════════════════════════════
    //  COMMENTS
    // ═══════════════════════════════════════════════════════
    toggleComments() {
      if (!ZSU.enable_comments) return;
      const $panel = document.getElementById('zsuCommentsPanel');
      if (!$panel) return;
      const isOpen = $panel.style.display !== 'none';
      $panel.style.display = isOpen ? 'none' : 'block';
      if (!isOpen) this.loadComments(this.state.currentShortId);
    },

    closeComments() {
      const $panel = document.getElementById('zsuCommentsPanel');
      if ($panel) $panel.style.display = 'none';
    },

    loadComments(shortId) {
      const $list = document.getElementById('zsuCommentsList');
      if (!$list) return;
      $list.innerHTML = '<div style="text-align:center;padding:20px;"><div class="zsu-spinner-ring" style="margin:auto;"></div></div>';

      ajax('zsu_get_comments', { short_id: shortId }).then((res) => {
        if (!res.success) return;
        if (!res.comments.length) {
          $list.innerHTML = '<p style="text-align:center;color:#64748b;padding:16px;font-size:13px;">No comments yet. Be first!</p>';
          return;
        }
        $list.innerHTML = res.comments.map((c) => `
          <div class="zsu-comment-item">
            <img class="zsu-comment-item-avatar" src="${esc(c.avatar)}" alt="${esc(c.display_name)}" loading="lazy">
            <div>
              <div class="zsu-comment-name">${esc(c.display_name)}</div>
              <div class="zsu-comment-text">${esc(c.content)}</div>
              <div class="zsu-comment-date">${esc(c.date)}</div>
            </div>
          </div>`).join('');
      });
    },

    submitComment() {
      const input = document.getElementById('zsuCommentInput');
      if (!input?.value.trim()) return;
      if (!ZSU.logged_in) { toast('Please sign in to comment', 'info'); return; }

      const content = input.value.trim();
      input.value = '';

      ajax('zsu_post_comment', {
        short_id: this.state.currentShortId,
        content,
      }).then((res) => {
        if (!res.success) return;
        const c    = res.comment;
        const $list = document.getElementById('zsuCommentsList');
        const empty = $list?.querySelector('p');
        if (empty) empty.remove();

        const el = document.createElement('div');
        el.className = 'zsu-comment-item';
        el.innerHTML = `
          <img class="zsu-comment-item-avatar" src="${esc(c.avatar)}" alt="${esc(c.display_name)}" loading="lazy">
          <div>
            <div class="zsu-comment-name">${esc(c.display_name)}</div>
            <div class="zsu-comment-text">${esc(c.content)}</div>
            <div class="zsu-comment-date">${esc(c.date)}</div>
          </div>`;
        $list?.prepend(el);

        // Update count
        const $btn = document.getElementById('zsuLbCommentBtn');
        if ($btn) {
          const cur = parseInt($btn.querySelector('.zsu-action-label').textContent, 10) || 0;
          $btn.querySelector('.zsu-action-label').textContent = cur + 1;
        }
      });
    },

    // ═══════════════════════════════════════════════════════
    //  RATING UI
    // ═══════════════════════════════════════════════════════
    renderRatingUI(shortId) {
      const existing = document.getElementById('zsuLbRatingUI');
      if (existing) existing.remove();

      const $stats = document.getElementById('zsuLbStats');
      if (!$stats) return;

      const ratingDiv = document.createElement('div');
      ratingDiv.id = 'zsuLbRatingUI';
      ratingDiv.className = 'zsu-star-rating';
      ratingDiv.setAttribute('aria-label', 'Rate this short');

      for (let i = 1; i <= 5; i++) {
        const star = document.createElement('button');
        star.className = 'zsu-star';
        star.textContent = '★';
        star.dataset.val = i;
        star.setAttribute('aria-label', `${i} star${i > 1 ? 's' : ''}`);

        star.addEventListener('mouseenter', () => {
          ratingDiv.querySelectorAll('.zsu-star').forEach((s, idx) => {
            s.classList.toggle('hover', idx < i);
          });
        });
        star.addEventListener('mouseleave', () => {
          ratingDiv.querySelectorAll('.zsu-star').forEach((s) => s.classList.remove('hover'));
        });
        star.addEventListener('click', () => {
          if (!ZSU.logged_in) { toast('Sign in to rate', 'info'); return; }
          ajax('zsu_rate_short', { short_id: shortId, rating: i }).then((res) => {
            if (!res.success) { toast('Already rated!', 'info'); return; }
            ratingDiv.querySelectorAll('.zsu-star').forEach((s, idx) => {
              s.classList.toggle('active', idx < i);
            });
            setText('zsuLbRating', `${res.rating_avg} (${res.rating_count})`);
            toast(`Rated ${i}⭐ — thank you!`, 'success');
          });
        });

        ratingDiv.appendChild(star);
      }

      $stats.after(ratingDiv);
    },

    // ═══════════════════════════════════════════════════════
    //  MUTE / FULLSCREEN
    // ═══════════════════════════════════════════════════════
    toggleMute() {
      this.state.isMuted = !this.state.isMuted;
      const vid = document.getElementById(`zsuVid${this.state.lbIndex}`);
      if (vid) vid.muted = this.state.isMuted;
      this.syncMuteIcon();
    },

    syncMuteIcon() {
      const isMuted = this.state.isMuted;
      // Legacy sidebar button (now hidden but kept for safety)
      const $icon  = document.getElementById('zsuMuteIcon');
      const $label = document.getElementById('zsuLbMuteBtn')?.querySelector('.zsu-action-label');
      if ($icon)  $icon.textContent  = isMuted ? '🔇' : '🔊';
      if ($label) $label.textContent = isMuted ? 'Unmute' : 'Mute';

      // Update per-slide rail mute icon for the current slide
      const idx = this.state.lbIndex;
      const $railIcon = document.getElementById(`zsuRailMuteIcon${idx}`);
      if ($railIcon) {
        $railIcon.innerHTML = isMuted
          ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/></svg>'
          : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>';
        const $railLabel = $railIcon.parentElement?.querySelector('.zsu-lb-rail-label');
        if ($railLabel) $railLabel.textContent = isMuted ? 'Unmute' : 'Mute';
      }
    },

    toggleFullscreen() {
      const s = this.state.lbShorts[this.state.lbIndex];
      if (!s) return;

      // Build overlay once, reuse on subsequent clicks
      let $ov = document.getElementById('zsuExpandOverlay');
      if (!$ov) {
        $ov = document.createElement('div');
        $ov.id = 'zsuExpandOverlay';
        $ov.style.cssText = 'display:none;position:fixed;inset:0;z-index:999999;background:rgba(0,0,0,.96);align-items:center;justify-content:center;';
        $ov.innerHTML = `
          <button id="zsuExpandClose" style="position:absolute;top:14px;right:18px;width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.18);border:2px solid rgba(255,255,255,.3);color:#fff;font-size:26px;line-height:1;cursor:pointer;z-index:2;display:flex;align-items:center;justify-content:center;">&times;</button>
          <div id="zsuExpandWrap" style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;padding:60px 16px 16px;box-sizing:border-box;">
            <video id="zsuExpandVideo" style="max-width:100%;max-height:calc(100vh - 80px);width:100%;object-fit:contain;border-radius:12px;background:#000;" playsinline webkit-playsinline controls ${ZSU.loop ? 'loop' : ''}></video>
          </div>`;
        document.body.appendChild($ov);

        const closeExpand = () => {
          const v = document.getElementById('zsuExpandVideo');
          if (v) { v.pause(); v.removeAttribute('src'); v.load(); }
          $ov.style.display = 'none';
          document.body.classList.remove('zsu-no-scroll');
          // Resume lightbox video when expand closes
          if (this.state.lbOpen) {
            this.playSlide(this.state.lbIndex);
          }
        };
        document.getElementById('zsuExpandClose')?.addEventListener('click', closeExpand);
        $ov.addEventListener('click', (e) => { if (e.target === $ov) closeExpand(); });
        document.addEventListener('keydown', (e) => {
          if (e.key === 'Escape' && $ov.style.display !== 'none') closeExpand();
        });
      }

      // Pause the lightbox video while expand is open
      this.pauseSlide(this.state.lbIndex);

      // Set correct aspect ratio on the wrap
      const ratio = s.ratio || '9:16';
      const wrap = document.getElementById('zsuExpandWrap');
      const ratioMap = { '9:16': 'calc(100vh * 9 / 16)', '1:1': '90vh', '4:5': 'calc(90vh * 4 / 5)', '4:3': 'calc(90vh * 4 / 3)', '16:9': '100%' };
      if (wrap) wrap.style.maxWidth = ratioMap[ratio] || ratioMap['9:16'];

      const vid = document.getElementById('zsuExpandVideo');
      if (!vid) return;
      vid.src = s.video_url;
      vid.muted = this.state.isMuted;
      vid.play().catch(() => {});

      $ov.style.display = 'flex';
      document.body.classList.add('zsu-no-scroll');
    },

    // ═══════════════════════════════════════════════════════
    //  ANALYTICS
    // ═══════════════════════════════════════════════════════
    recordView(shortId) {
      // Debounce: skip if same short was recorded within 3 seconds
      const now = Date.now();
      if (this._lastViewId === shortId && now - (this._lastViewTime || 0) < 3000) return;
      this._lastViewId   = shortId;
      this._lastViewTime = now;
      ajax('zsu_record_view', { short_id: shortId });
    },

    startWatchTimer(shortId) {
      this.state.watchStart = Date.now();
      this.state.watchTimer = setTimeout(() => {}, 0); // placeholder
    },

    stopWatchTimer() {
      const elapsed  = Math.round((Date.now() - this.state.watchStart) / 1000);
      const vid      = document.getElementById(`zsuVid${this.state.lbIndex}`);
      const duration = vid?.duration || 0;
      const sid      = this.state.currentShortId;

      if (sid && elapsed > 1) {
        const watchPct = duration > 0 ? Math.min(100, Math.round((elapsed / duration) * 100)) : 0;
        ajax('zsu_record_watch_pct', { short_id: sid, watch_pct: watchPct });
      }
    },

    // ═══════════════════════════════════════════════════════
    //  COIN BALANCE
    // ═══════════════════════════════════════════════════════
    loadCoinBalance() {
      if (!ZSU.logged_in) return;
      // Show server-injected balance instantly (no AJAX flicker)
      if (ZSU.initial_coins !== null && ZSU.initial_coins !== undefined) {
        setText('zsuCoinCount', ZSU.initial_coins);
      }
      // Refresh in background to catch any coins earned in other tabs
      ajax('zsu_get_coins_balance').then((res) => {
        if (res.success) {
          setText('zsuCoinCount', res.coins);
          ZSU.initial_coins = res.coins; // keep in sync
        }
      });
    },

    // Refresh coin balance after any earning event (call this after purchase, like, watch etc.)
    refreshCoinBalance() {
      if (!ZSU.logged_in) return;
      ajax('zsu_get_coins_balance').then((res) => {
        if (res.success) {
          setText('zsuCoinCount', res.coins);
          ZSU.initial_coins = res.coins;
        }
      });
    },

    // ═══════════════════════════════════════════════════════
    //  SEARCH
    // ═══════════════════════════════════════════════════════
    initSearch() {
      const $field   = document.getElementById('zsuSearchField');
      const $results = document.getElementById('zsuSearchResults');
      const $trigger = document.getElementById('zsuSearchTrigger');
      const $clear   = document.getElementById('zsuSearchClear');
      const $dropdown = document.getElementById('zsuSearchDropdown');

      if (!$field) return;

      $field.addEventListener('input', () => {
        clearTimeout(this.state.searchTimer);
        const q = $field.value.trim();
        if (q.length < 2) { $results.innerHTML = ''; $results.classList.remove('has-results'); return; }

        this.state.searchTimer = setTimeout(() => {
          ajax('zsu_search_shorts', { q }).then((res) => {
            if (!res.success || !res.shorts.length) {
              $results.innerHTML = '<div style="padding:14px 16px;color:#64748b;font-size:13px;">No results found</div>';
              $results.classList.add('has-results');
              return;
            }
            $results.innerHTML = res.shorts.slice(0, 8).map((s) => `
              <div class="zsu-search-result-item" role="option" data-id="${s.id}" tabindex="0">
                <img class="zsu-search-result-thumb" src="${esc(s.thumb_url)}" alt="${esc(s.title)}" loading="lazy">
                <div>
                  <div class="zsu-search-result-title">${esc(s.title)}</div>
                  <div class="zsu-search-result-meta">👁 ${esc(s.views)} · ❤️ ${esc(s.likes)}</div>
                </div>
              </div>`).join('');
            $results.classList.add('has-results');

            // Click search result → open lightbox
            $results.querySelectorAll('.zsu-search-result-item').forEach((item) => {
              item.addEventListener('click', () => {
                const id = parseInt(item.dataset.id, 10);
                $field.value = '';
                $results.innerHTML = '';
                $results.classList.remove('has-results');
                this.fetchAndOpenLightbox(id);
              });
            });
          });
        }, 320);
      });

      // Mobile search trigger
      $trigger?.addEventListener('click', (e) => {
        e.stopPropagation();
        $dropdown?.classList.toggle('mobile-open');
        if ($dropdown?.classList.contains('mobile-open')) $field?.focus();
      });

      // Clear button
      $clear?.addEventListener('click', () => {
        $field.value = '';
        $results.innerHTML = '';
        $results.classList.remove('has-results');
        $field.focus();
      });
    },

    // ═══════════════════════════════════════════════════════
    //  FILTER STRIP
    // ═══════════════════════════════════════════════════════
    initFilterStrip() {
      // Restore from URL query var
      if (ZSU.open_cat) {
        const pill = document.querySelector(`.zsu-cat-pill[data-cat="${ZSU.open_cat}"]`);
        if (pill) this.setCategory(ZSU.open_cat, pill);
      }
    },

    // ═══════════════════════════════════════════════════════
    //  INFINITE SCROLL
    // ═══════════════════════════════════════════════════════
    initIntersectionObserver() {
      const sentinel = document.getElementById('zsuSentinel');
      if (!sentinel) return;

      const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting && !this.state.loading && !this.state.allLoaded) {
            this.loadGrid(this.state.page + 1, false);
          }
        });
      }, { threshold: 0.1, rootMargin: '200px' });

      io.observe(sentinel);

      // Manual load-more button (fallback)
      document.getElementById('zsuLoadMoreBtn')?.addEventListener('click', () => {
        this.loadGrid(this.state.page + 1, false);
      });
    },

    // ═══════════════════════════════════════════════════════
    //  HEADER SCROLL EFFECT
    // ═══════════════════════════════════════════════════════
    initHeader() {
      const $header = document.getElementById('zsuHeader');
      if (!$header) return;
      let lastY = 0;
      window.addEventListener('scroll', () => {
        const y = window.scrollY;
        $header.classList.toggle('scrolled', y > 20);
        // Auto-hide header on scroll down, show on scroll up
        if (y > lastY + 5 && y > 80) {
          $header.style.transform = 'translateY(-100%)';
        } else if (y < lastY - 5) {
          $header.style.transform = 'translateY(0)';
        }
        lastY = y;
      }, { passive: true });
    },

    // ═══════════════════════════════════════════════════════
    //  3D TILT
    // ═══════════════════════════════════════════════════════
    initTilt(cards) {
      if (!ZSU.tilt_cards || ZSU.animation_level === 'off') return;

      cards.forEach((card) => {
        if (card.classList.contains('zsu-tilt-init')) return;
        card.classList.add('zsu-tilt-init');

        let rect;
        const shine = card.querySelector('.zsu-card-shine');

        card.addEventListener('mouseenter', () => { rect = card.getBoundingClientRect(); });
        card.addEventListener('mousemove', (e) => {
          if (!rect) return;
          const x = e.clientX - rect.left;
          const y = e.clientY - rect.top;
          const cx = rect.width  / 2;
          const cy = rect.height / 2;
          const rotX =  ((y - cy) / cy) * 8;  // max ±8deg
          const rotY = -((x - cx) / cx) * 8;

          card.style.transform = `perspective(600px) rotateX(${rotX}deg) rotateY(${rotY}deg) scale(1.03)`;

          if (shine) {
            const px = Math.round((x / rect.width)  * 100);
            const py = Math.round((y / rect.height) * 100);
            shine.style.setProperty('--mx', `${px}%`);
            shine.style.setProperty('--my', `${py}%`);
          }
        });
        card.addEventListener('mouseleave', () => {
          card.style.transform = '';
          card.style.transition = 'transform 0.5s ease';
          setTimeout(() => { card.style.transition = ''; }, 500);
        });
      });
    },

    // ═══════════════════════════════════════════════════════
    //  PARTICLE BACKGROUND
    // ═══════════════════════════════════════════════════════
    initParticles() {
      return; // Particles disabled
      const canvas = document.getElementById('zsuParticleCanvas');
      if (!canvas) return;

      const ctx   = canvas.getContext('2d');
      let w, h, particles;
      const COUNT = ZSU.animation_level === 'ultra' ? 70 : ZSU.animation_level === 'high' ? 45 : 25;

      const resize = () => {
        w = canvas.width  = window.innerWidth;
        h = canvas.height = window.innerHeight;
      };

      const hexToRgb = (hex) => {
        const r = parseInt(hex.slice(1,3),16);
        const g = parseInt(hex.slice(3,5),16);
        const b = parseInt(hex.slice(5,7),16);
        return `${r},${g},${b}`;
      };

      const primary = ZSU.primary_color || '#7c3aed';
      const accent  = ZSU.accent_color  || '#f59e0b';
      const pRgb    = hexToRgb(primary);
      const aRgb    = hexToRgb(accent);

      const spawn = () => ({
        x:    Math.random() * w,
        y:    Math.random() * h,
        r:    Math.random() * 1.8 + 0.4,
        vx:   (Math.random() - 0.5) * 0.35,
        vy:  -(Math.random() * 0.4  + 0.1),
        life: Math.random(),
        col:  Math.random() < 0.6 ? pRgb : aRgb,
      });

      resize();
      window.addEventListener('resize', resize, { passive: true });
      particles = Array.from({ length: COUNT }, spawn);

      const draw = () => {
        ctx.clearRect(0, 0, w, h);
        particles.forEach((p, i) => {
          p.x    += p.vx;
          p.y    += p.vy;
          p.life += 0.004;

          if (p.y < -10 || p.life > 1) {
            particles[i] = spawn();
            particles[i].y = h + 5;
            particles[i].life = 0;
            return;
          }

          const alpha = Math.sin(p.life * Math.PI) * 0.55;
          ctx.beginPath();
          ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
          ctx.fillStyle = `rgba(${p.col},${alpha})`;
          ctx.fill();
        });
        requestAnimationFrame(draw);
      };

      draw();
    },

    // ═══════════════════════════════════════════════════════
    //  CONFETTI BURST
    // ═══════════════════════════════════════════════════════
    burstConfetti(fromEl) {
      if (!ZSU.confetti_like || ZSU.animation_level === 'off') return;
      const canvas = document.getElementById('zsuConfettiCanvas');
      if (!canvas) return;

      canvas.width  = window.innerWidth;
      canvas.height = window.innerHeight;
      const ctx     = canvas.getContext('2d');

      let ox = window.innerWidth  / 2;
      let oy = window.innerHeight / 2;

      if (fromEl) {
        const r = fromEl.getBoundingClientRect();
        ox = r.left + r.width  / 2;
        oy = r.top  + r.height / 2;
      }

      const colors = ['#f43f5e','#f59e0b','#7c3aed','#10b981','#06b6d4','#fff','#fb923c'];
      const pieces = Array.from({ length: 60 }, () => ({
        x:    ox,
        y:    oy,
        vx:   (Math.random() - 0.5) * 14,
        vy:   (Math.random() - 0.8) * 16,
        size: Math.random() * 7 + 3,
        col:  colors[Math.floor(Math.random() * colors.length)],
        rot:  Math.random() * 360,
        rv:   (Math.random() - 0.5) * 12,
        shape: Math.random() < 0.5 ? 'rect' : 'circle',
      }));

      let frame = 0;
      const run = () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        pieces.forEach((p) => {
          p.x  += p.vx;
          p.y  += p.vy;
          p.vy += 0.55;   // gravity
          p.vx *= 0.98;
          p.rot += p.rv;

          ctx.save();
          ctx.translate(p.x, p.y);
          ctx.rotate((p.rot * Math.PI) / 180);
          ctx.globalAlpha = Math.max(0, 1 - frame / 70);
          ctx.fillStyle = p.col;

          if (p.shape === 'rect') {
            ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size * 0.5);
          } else {
            ctx.beginPath();
            ctx.arc(0, 0, p.size / 2, 0, Math.PI * 2);
            ctx.fill();
          }
          ctx.restore();
        });

        frame++;
        if (frame < 80) requestAnimationFrame(run);
        else ctx.clearRect(0, 0, canvas.width, canvas.height);
      };

      run();
    },

  }; // end ZSU_App

  /* ═══════════════════════════════════════════════════════════
     UTILITY FUNCTIONS
     ═══════════════════════════════════════════════════════════ */

  // Delegate event listener
  function on(root, event, selector, handler) {
    if (typeof selector === 'function') {
      root.addEventListener(event, selector);
      return;
    }
    root.addEventListener(event, (e) => {
      const target = e.target.closest(selector);
      if (target && root.contains(target)) {
        // Create a proxy event with currentTarget pointing to matched element
        // (e.currentTarget is read-only on native events)
        const proxyE = Object.create(e, {
          currentTarget: { value: target, writable: false, configurable: true }
        });
        handler.call(target, proxyE);
      }
    });
  }

  // AJAX helper — arrays are sent as key[]=val1&key[]=val2 so PHP sees them correctly
  function ajax(action, data = {}) {
    const body = new URLSearchParams();
    body.append('action', action);
    body.append('nonce', ZSU.nonce);
    Object.entries(data).forEach(([k, v]) => {
      if (Array.isArray(v)) {
        v.forEach(item => body.append(k + '[]', item));
      } else {
        body.append(k, v);
      }
    });
    return fetch(ZSU.ajax_url, { method: 'POST', body })
      .then((r) => r.json())
      .catch(() => ({ success: false }));
  }

  // DOM helpers
  function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
  }

  function setAttr(id, attr, val) {
    const el = document.getElementById(id);
    if (el) el.setAttribute(attr, val);
  }

  function setVal(id, val) {
    const el = document.getElementById(id);
    if (el) el.value = val;
  }

  // XSS escape
  function esc(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // Format seconds → m:ss
  function formatDuration(secs) {
    secs = parseInt(secs, 10) || 0;
    const m = Math.floor(secs / 60);
    const s = secs % 60;
    return `${m}:${s.toString().padStart(2, '0')}`;
  }

  // WooCommerce price format (simple)
  function wc_price(n) {
    return n ? `<span class="woocommerce-Price-amount">${parseFloat(n).toFixed(2)}</span>` : '';
  }

  // Skeleton card HTML
  function renderSkeletons(count) {
    return Array.from({ length: count }, () =>
      `<div class="zsu-short-card zsu-skeleton" aria-hidden="true">
         <div class="zsu-skeleton-thumb"></div>
         <div class="zsu-skeleton-line zsu-skeleton-line-lg"></div>
         <div class="zsu-skeleton-line zsu-skeleton-line-sm"></div>
       </div>`
    ).join('');
  }

  function renderTraySkeletons(count) {
    return Array.from({ length: count }, () =>
      `<div class="zsu-tray-product zsu-skeleton" style="flex:0 0 110px;" aria-hidden="true">
         <div class="zsu-skeleton-thumb" style="aspect-ratio:1;"></div>
         <div class="zsu-skeleton-line" style="margin:8px 7px 4px;width:80%;"></div>
         <div class="zsu-skeleton-line" style="margin:0 7px;width:50%;"></div>
       </div>`
    ).join('');
  }

  // Toast
  function toast(msg, type = 'info', dur = 3000) {
    const $container = document.getElementById('zsuToastContainer');
    if (!$container) return;

    const el = document.createElement('div');
    el.className = `zsu-toast-item ${type}`;
    el.textContent = msg;
    $container.appendChild(el);

    setTimeout(() => {
      el.style.transition = 'opacity 0.4s, transform 0.4s';
      el.style.opacity    = '0';
      el.style.transform  = 'translateY(10px)';
      setTimeout(() => el.remove(), 420);
    }, dur);
  }


  /* ─── Wait for DOM, then only boot if shorts elements actually exist on this page ── */
  function zsuMaybeInit() {
    // ── Guard 1: ZSU config must exist
    if (typeof ZSU === 'undefined' || !ZSU.ajax_url) return;

    // ── Guard 2: Current page must have our shorts elements
    const hasGrid        = !!document.getElementById('zsuGrid');
    const hasLightbox    = !!document.getElementById('zsuLightbox');
    const hasEmbed       = !!document.querySelector('.zsu-embedded-wrap');
    const hasVendorStore = !!document.getElementById('zsu-store-shorts-tab');
    if (!hasGrid && !hasLightbox && !hasEmbed && !hasVendorStore) return;

    // ── Guard 3: Verify we're actually on a shorts-related URL or element exists
    const shortsBase = (ZSU.shorts_url || '').replace(/\/+$/, '');
    const currentUrl = window.location.href.replace(/\/+$/, '');
    const isOnShortsPage = hasGrid
      || hasLightbox
      || hasEmbed
      || hasVendorStore
      || currentUrl === shortsBase
      || currentUrl.startsWith(shortsBase + '/')
      || currentUrl.includes('vertical=shorts')
      || window.location.pathname.includes('/shorts')
      || window.location.hostname.startsWith('shorts.');

    if (!isOnShortsPage) {
      // Clean up any leftover scroll lock and abort
      document.body.classList.remove('zsu-no-scroll');
      document.body.style.overflow = '';
      document.documentElement.style.overflow = '';
      return;
    }

    ZSU_App.init();
    window.ZSU_App = ZSU_App; // expose for vendor store tab and embeds
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', zsuMaybeInit);
  } else {
    zsuMaybeInit();
  }

}());
