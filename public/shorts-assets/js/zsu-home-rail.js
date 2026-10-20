/*!
 * ZSU Home Rail JS — v17.0 CINEMATIC3D
 * - True 3D 360° cylindrical carousel (drag/swipe/auto-rotate)
 * - Cards click → redirect to short_url (scrollable shorts page)
 * - Boosted = subtle gold, not neon kid glow
 * - Spin wheel: vivid segments, bright white text
 */
(function () {
  'use strict';

  const esc   = s => String(s).replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const fmt   = n => n>=1e6?(n/1e6).toFixed(1)+'M':n>=1e3?(n/1e3).toFixed(1)+'K':String(n||0);
  const cfg   = () => window.ZSU_HR || {};
  const toast = (msg,type='',dur=3200) => {
    document.querySelectorAll('.zhr-toast').forEach(t=>t.remove());
    const el=document.createElement('div');
    el.className='zhr-toast'+(type?' '+type:'');
    el.textContent=msg; document.body.appendChild(el);
    setTimeout(()=>el.classList.add('out'),dur-350);
    setTimeout(()=>el.remove(),dur);
  };
  const ajax = (action,data={}) => {
    const fd=new FormData(); fd.append('action',action); fd.append('nonce',cfg().nonce||'');
    Object.entries(data).forEach(([k,v])=>fd.append(k,v));
    return fetch(cfg().ajax_url||'/wp-admin/admin-ajax.php',{method:'POST',body:fd}).then(r=>r.json());
  };


  /* ══════════════════════════════════════════════════════════
     HOME RAIL — Freestyle horizontal scroll, Elite badges,
     no vertical scroll stealing, homepage-only FAB
  ══════════════════════════════════════════════════════════ */
  class Carousel3D {
    constructor(wrap) {
      this.wrap   = wrap;
      this.shorts = [];
      this.count  = parseInt(wrap.dataset.count) || 8;
      this.liked  = new Set(JSON.parse(localStorage.getItem('zhr_liked') || '[]'));
      this.track  = null;       // the scroll container
      this._mouseDown  = false;
      this._mouseStartX = 0;
      this._mouseScrollLeft = 0;

      this._fetch(cfg().default_filter || 'boosted');
      this._bindFilters();
    }

    /* ── AJAX fetch ─────────────────────────────────────── */
    _fetch(filter) {
      this._showSkeleton();
      ajax('zsu_home_rail_fetch', { count: cfg().max_items || this.count, filter })
        .then(r => {
          if (r.success && r.data.shorts?.length) {
            this.shorts = r.data.shorts;
            this._buildRail();
          } else {
            this._showEmpty();
          }
        }).catch(() => this._showEmpty());
    }

    _showSkeleton() {
      const inner = this.wrap.querySelector('.zhr__rail-inner');
      if (!inner) return;
      inner.innerHTML = `
        <div class="zhr__skel-wrap">
          ${[0,1,2,3].map(() => '<div class="zhr__skel"><div class="zhr__skel-inner"></div></div>').join('')}
        </div>`;
    }

    _showEmpty() {
      const inner = this.wrap.querySelector('.zhr__rail-inner');
      if (inner) inner.innerHTML = '<div class="zhr__empty">No shorts yet — check back soon.</div>';
    }

    /* ── Build freestyle horizontal scroll rail ─────────── */
    _buildRail() {
      const inner = this.wrap.querySelector('.zhr__rail-inner');
      if (!inner || !this.shorts.length) { this._showEmpty(); return; }

      inner.innerHTML = `
        <div class="zhr__scroll-track">
          ${this.shorts.map((s, i) => this._cardHTML(s, i)).join('')}
        </div>`;

      this.track = inner.querySelector('.zhr__scroll-track');
      this._bindMouseDrag();
      this._bindClicks();
      this._bindLikes();
    }

    /* ── Card HTML ──────────────────────────────────────── */
    _cardHTML(s, i) {
      const isBoosted  = !!s.is_boosted;
      const isFeatured = !!s.is_featured;
      const liked      = this.liked.has(s.id);
      const shortUrl   = s.short_url || (cfg().shorts_url ? cfg().shorts_url + '/v/' + s.id : '#');

      /* Elite badge — EXACT vendor badge style (Cinzel font, crown SVG)
         but: LEFT placement, white pill bg, white border, NAVY BLUE text */
      const eliteBadge = isBoosted ? `
        <div class="zhr__reel-elite" aria-label="Elite">
          <svg class="zhr__elite-crown" viewBox="0 0 14 10" width="11" height="8" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M0 10h14V8H0v2zM14 3L11 1 7 5 3 1 0 3l1.5 4h11L14 3z" fill="#1e3a5f"/></svg>
          ELITE
        </div>` : (isFeatured ? '<span class="zhr__badge--featured">Featured</span>' : '');

      /* Views badge */
      const vThresh      = cfg().views_min_display || 0;
      const rawViews     = s.views || 0;
      const multiplier   = cfg().views_multiplier || 1;
      const floor        = cfg().views_floor || 0;
      const displayViews = Math.max(floor, rawViews * multiplier);
      const showViews    = cfg().show_views !== false && rawViews >= vThresh;
      const viewsFmt     = displayViews >= 100000 ? (displayViews / 100000).toFixed(1) + 'L'
                         : displayViews >= 1000   ? (displayViews / 1000).toFixed(1) + 'K'
                         : String(Math.round(displayViews));

      const vendorAvatar = s.vendor?.avatar || '';
      const vendorName   = s.vendor?.name   || '';
      const prodCount    = s.product_count  || 0;

      const heartFill = `<svg width="14" height="14" viewBox="0 0 24 24" fill="#ef4444" stroke="none"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>`;
      const heartEmpty = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.9)" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>`;

      return `
        <div class="zhr__scroll-card${isBoosted ? ' is-boosted' : ''}"
             data-id="${s.id}"
             data-short-url="${esc(shortUrl)}"
             role="listitem" tabindex="0"
             aria-label="${esc(s.title)}"
>

          <!-- Full thumbnail -->
          <img class="zhr__reel-thumb"
               src="${esc(s.thumb_url || '')}"
               alt="${esc(s.title)}"
               loading="lazy" decoding="async"
 style="pointer-events:none">

          <!-- Bottom scrim gradient -->
          <div class="zhr__reel-scrim"></div>

          <!-- Title bottom left -->
          ${s.title ? `<div class="zhr__reel-title">${esc(s.title)}</div>` : ''}

          <!-- ELITE badge — top right, exactly like OLX -->
          ${isBoosted ? eliteBadge : ''}

          <!-- Featured badge — bottom left if not boosted -->
          ${!isBoosted && isFeatured ? `<div class="zhr__reel-badges">${eliteBadge}</div>` : ''}


          <!-- Like button removed -->

          <!-- Vendor avatar — bottom right -->
          ${vendorAvatar ? `
          <div class="zhr__reel-vendor-logo" title="${esc(vendorName)}">
            <img src="${esc(vendorAvatar)}" alt="${esc(vendorName)}" loading="lazy">
          </div>` : ''}

          <!-- Product count — bottom centre -->
          ${cfg().show_products !== false && prodCount > 0 ? `
          <div class="zhr__reel-product-count">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round">
              <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
              <line x1="3" y1="6" x2="21" y2="6"/>
              <path d="M16 10a4 4 0 01-8 0"/>
            </svg>
            ${prodCount} Product${prodCount !== 1 ? 's' : ''}
          </div>` : ''}

        </div>`;
    }

    /* ── Mouse drag on desktop (horizontal only) ─────────── */
    _bindMouseDrag() {
      const t = this.track;
      if (!t) return;

      /* ── Desktop mouse drag ── */
      t.addEventListener('mousedown', e => {
        this._mouseDown = true;
        this._mouseStartX = e.pageX - t.offsetLeft;
        this._mouseScrollLeft = t.scrollLeft;
        t.style.cursor = 'grabbing';
      });
      document.addEventListener('mouseup', () => {
        this._mouseDown = false;
        if (t) t.style.cursor = 'grab';
      });
      document.addEventListener('mousemove', e => {
        if (!this._mouseDown) return;
        e.preventDefault();
        const x    = e.pageX - t.offsetLeft;
        const walk = (x - this._mouseStartX) * 1.4;
        t.scrollLeft = this._mouseScrollLeft - walk;
      });

      /* ── Mobile touch: pure CSS — identical to featured rail ───────
         touch-action:auto + overflow-x:auto on the track means the browser
         handles both axes natively. No JS touch handlers needed.
         Horizontal swipe scrolls the track. Vertical scrolls the page.
      ────────────────────────────────────────────────────────────── */
    }

    /* ── Card clicks → navigate ──────────────────────────── */
    _bindClicks() {
      const t = this.track;
      if (!t) return;
      t.addEventListener('click', e => {
        // Don't navigate if user just dragged
        if (this._mouseDown) return;
        const card = e.target.closest('.zhr__scroll-card');
        if (!card) return;
        if (e.target.closest('.zhr__reel-like')) return; // like button handled separately
        const url = card.dataset.shortUrl;
        if (url && url !== '#') window.location.href = url;
      });
      t.addEventListener('keydown', e => {
        const card = e.target.closest('.zhr__scroll-card');
        if (!card) return;
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          const url = card.dataset.shortUrl;
          if (url && url !== '#') window.location.href = url;
        }
      });
    }

    /* ── Like toggle ─────────────────────────────────────── */
    _bindLikes() {
      const t = this.track;
      if (!t) return;
      const heartFill  = `<svg width="14" height="14" viewBox="0 0 24 24" fill="#ef4444" stroke="none"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>`;
      const heartEmpty = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.9)" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>`;

      t.addEventListener('click', e => {
        const btn = e.target.closest('.zhr__reel-like');
        if (!btn) return;
        e.stopPropagation();
        const id    = parseInt(btn.dataset.id);
        const liked = this.liked.has(id);
        let   likes = parseInt(btn.dataset.likes) || 0;

        if (liked) {
          this.liked.delete(id);
          btn.innerHTML = heartEmpty;
          btn.classList.remove('liked');
          likes = Math.max(0, likes - 1);
        } else {
          this.liked.add(id);
          btn.innerHTML = heartFill;
          btn.classList.add('liked');
          btn.style.animation = 'none';
          requestAnimationFrame(() => { btn.style.animation = '_like .32s cubic-bezier(.16,1,.3,1)'; });
          likes++;
        }
        btn.dataset.likes = likes;
        localStorage.setItem('zhr_liked', JSON.stringify([...this.liked]));
        ajax('zsu_toggle_like', { short_id: id }).catch(() => {});
      });
    }

    /* ── Filter pills ─────────────────────────────────────── */
    _bindFilters() {
      this.wrap.querySelectorAll('.zhr__filter').forEach(btn => {
        btn.addEventListener('click', () => {
          this.wrap.querySelectorAll('.zhr__filter').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          this._fetch(btn.dataset.filter || 'trending');
        });
      });
    }
  }




  /* ══════════════════════════════════════════════════════════
     SPIN WHEEL — vivid, crisp white segment text
  ══════════════════════════════════════════════════════════ */
  const wheel = (() => {
    let overlay=null, spinning=false, canvas=null;

    // Minimal monochrome segment colors — no RGB, no glow
    const PRIZES = () => cfg().prizes || [
      {label:'10% Off',   color:'#111827', type:'coupon', value:'SPIN10'  },
      {label:'₹50 Cash',  color:'#374151', type:'cash',   value:'CASH50'  },
      {label:'Free Ship', color:'#1f2937', type:'coupon', value:'FREESHIP'},
      {label:'20% Off',   color:'#4b5563', type:'coupon', value:'SPIN20'  },
      {label:'Try Again', color:'#6b7280', type:'none',   value:''        },
      {label:'15% Off',   color:'#111827', type:'coupon', value:'SPIN15'  },
      {label:'₹100 Cash', color:'#374151', type:'cash',   value:'CASH100' },
      {label:'Lucky Dip', color:'#1f2937', type:'coupon', value:'LUCKY'   },
    ];

    function open() {
      if (!overlay) _build();
      overlay.style.display='flex'; requestAnimationFrame(()=>overlay.classList.add('visible'));
      document.body.style.overflow='hidden';
      _drawWheel(0);
      ajax('zsu_home_spin_check').then(r=>{
        if (r.data?.blocked||(!r.success&&r.data?.message==='already_spun'))
          _showBlocked(r.data.seconds_left||82800);
      }).catch(()=>{});
    }

    function close() {
      if (!overlay) return;
      overlay.classList.remove('visible');
      setTimeout(()=>{ overlay.style.display='none'; document.body.style.overflow=''; },330);
    }

    function _build() {
      overlay=document.createElement('div'); overlay.className='zhr-wheel-overlay'; overlay.style.display='none';
      overlay.addEventListener('click',e=>{if(e.target===overlay)close();});

      const modal=document.createElement('div'); modal.className='zhr-wheel-modal';
      const railPreset = cfg().active_preset || 'clean_white';
      const wheelPresetCfg = cfg().wheel_preset || 'same';
      const activePreset = (wheelPresetCfg === 'same' || !wheelPresetCfg) ? railPreset : wheelPresetCfg;
      modal.dataset.preset = activePreset;
      // Propagate CSS vars to modal for wheel theming
      const presetColors = {
        clean_white:['#2563eb','#f59e0b'], soft_gray:['#374151','#6b7280'],
        slate_blue:['#1e40af','#3b82f6'], warm_cream:['#92400e','#d97706'],
        rose_blush:['#be185d','#db2777'], sage_green:['#065f46','#059669'],
        charcoal_dark:['#f9fafb','#f59e0b'], navy_classic:['#1d4ed8','#fbbf24'],
        minimal_ink:['#18181b','#52525b'], dusty_mauve:['#7c3aed','#a78bfa'],
        terracotta:['#c2410c','#ea580c'], steel:['#0f172a','#0ea5e9'],
        // legacy
        neon_dark:['#2563eb','#f59e0b'], cyber_punk:['#374151','#6b7280'],
        marble_gold:['#92400e','#d97706'], ocean_wave:['#1e40af','#3b82f6'],
        cherry_blossom:['#be185d','#db2777'], forest_dark:['#065f46','#059669'],
        royal_purple:['#7c3aed','#a78bfa'], fire_sunset:['#c2410c','#ea580c'],
        arctic_white:['#18181b','#52525b'], holographic:['#1d4ed8','#fbbf24'],
        midnight_rose:['#be185d','#db2777'], saffron_india:['#c2410c','#ea580c'],
        sakura_pop:['#be185d','#db2777']
      };
      const pc = presetColors[activePreset] || presetColors['neon_dark'];
      modal.style.setProperty('--primary', pc[0]);
      modal.style.setProperty('--accent2', pc[1]);

      // Close
      const cl=document.createElement('button'); cl.className='zhr-wheel-close'; cl.setAttribute('aria-label','Close');
      cl.innerHTML='<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#111827" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
      cl.addEventListener('click',close); modal.appendChild(cl);

      // Header
      const top=document.createElement('div'); top.className='zhr-wheel-top';
      top.innerHTML=`<p class="zhr-wheel-eyebrow">Daily Reward</p><h2 class="zhr-wheel-title">${cfg().spin_title||'Spin & Win'}</h2><p class="zhr-wheel-subtitle">${cfg().spin_subtitle||'One free spin every 24 hours'}</p>`;
      modal.appendChild(top);
      modal.appendChild(Object.assign(document.createElement('div'),{className:'zhr-wheel-hr'}));

      // Arena
      const arena=document.createElement('div'); arena.className='zhr-wheel-arena';
      canvas=document.createElement('canvas'); canvas.className='zhr-wheel-canvas'; canvas.width=280; canvas.height=280;
      arena.appendChild(canvas);
      const ticker=document.createElement('div'); ticker.className='zhr-wheel-ticker';
      ticker.innerHTML='<svg width="14" height="20" viewBox="0 0 14 20"><polygon points="7,20 0,0 14,0" fill="currentColor"/></svg>';
      arena.appendChild(ticker);
      arena.appendChild(Object.assign(document.createElement('div'),{className:'zhr-wheel-hub'}));
      modal.appendChild(arena);

      // Body
      const body=document.createElement('div'); body.className='zhr-wheel-body';

      const btn=document.createElement('button'); btn.className='zhr-wheel-btn';
      btn.innerHTML='<span class="zhr-wheel-btn-label">Spin the Wheel</span><span class="zhr-wheel-btn-sub">Free · Resets every 24 hours</span>';
      btn.addEventListener('click',_spin); body.appendChild(btn);

      const blocked=document.createElement('div'); blocked.className='zhr-wheel-blocked'; blocked.style.display='none';
      blocked.innerHTML=`<div class="zhr-wheel-blocked-head"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><strong>Already spun today</strong></div><p>Next spin available in</p><div class="zhr-wheel-countdown"></div>`;
      body.appendChild(blocked);

      const result=document.createElement('div'); result.className='zhr-wheel-result'; result.style.display='none';
      result.innerHTML=`<div class="zhr-wheel-result-check"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg></div><p class="zhr-wheel-result-label"></p><p class="zhr-wheel-result-prize"></p><div class="zhr-wheel-coupon-wrap" style="display:none"><p class="zhr-wheel-coupon-label">Your code</p><div class="zhr-wheel-coupon"><code class="zhr-wheel-coupon-code"></code><button class="zhr-wheel-coupon-copy"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>Copy</button></div></div>`;
      body.appendChild(result);

      const conf=document.createElement('div'); conf.className='zhr-wheel-confetti'; body.appendChild(conf);
      modal.appendChild(body);

      overlay.appendChild(modal); document.body.appendChild(overlay);
      document.addEventListener('keydown',e=>{if(overlay.style.display!=='none'&&e.key==='Escape')close();});
    }

    function _showBlocked(sec) {
      const btn=overlay.querySelector('.zhr-wheel-btn');
      const bl=overlay.querySelector('.zhr-wheel-blocked');
      const cl=overlay.querySelector('.zhr-wheel-countdown');
      if(btn) btn.style.display='none'; if(bl) bl.style.display='block'; if(cl) _countdown(sec,cl);
    }

    function _countdown(sec,el) {
      const tick=()=>{
        if(sec<=0){el.textContent='Refresh to spin!';return;}
        const h=Math.floor(sec/3600),m=Math.floor((sec%3600)/60),s=sec%60;
        el.textContent=`${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
        sec--; setTimeout(tick,1000);
      }; tick();
    }

    function _drawWheel(rot) {
      if (!canvas) return;
      const prizes=PRIZES(), ctx=canvas.getContext('2d');
      const W=canvas.width,H=canvas.height,cx=W/2,cy=H/2,r=cx-6;
      const arc=(Math.PI*2)/prizes.length;
      ctx.clearRect(0,0,W,H);

      // Outer ring — subtle
      ctx.save();
      ctx.beginPath(); ctx.arc(cx,cy,r+5,0,Math.PI*2);
      ctx.strokeStyle='#e5e7eb'; ctx.lineWidth=8; ctx.stroke();
      ctx.restore();

      prizes.forEach((p,i)=>{
        const s=rot+arc*i-Math.PI/2, e=s+arc;

        // Alternating minimal segments
        const isDark = i%2===0;
        ctx.beginPath(); ctx.moveTo(cx,cy); ctx.arc(cx,cy,r,s,e); ctx.closePath();
        ctx.fillStyle = isDark ? '#111827' : '#f9fafb';
        ctx.fill();

        // Separator line
        ctx.strokeStyle='rgba(255,255,255,.15)'; ctx.lineWidth=1; ctx.stroke();

        // Label text
        ctx.save();
        ctx.translate(cx,cy); ctx.rotate(s+arc/2);
        ctx.textAlign='right'; ctx.textBaseline='middle';
        const fs=Math.max(9,r*.085);
        ctx.font=`700 ${fs}px -apple-system,BlinkMacSystemFont,'Segoe UI',system-ui,sans-serif`;
        ctx.fillStyle = isDark ? '#ffffff' : '#111827';
        ctx.shadowColor='transparent'; ctx.shadowBlur=0;
        ctx.fillText(p.label, r*.86, 0);
        ctx.restore();
      });

      // Hub — clean white circle
      ctx.beginPath(); ctx.arc(cx,cy,r*.11,0,Math.PI*2);
      ctx.fillStyle='#ffffff'; ctx.fill();
      ctx.strokeStyle='#e5e7eb'; ctx.lineWidth=2; ctx.stroke();
    }

    function _spin() {
      if(spinning) return; spinning=true;
      const prizes=PRIZES();
      const n=prizes.length;
      const prizeIdx=Math.floor(Math.random()*n);
      const btn=overlay.querySelector('.zhr-wheel-btn');
      const arena=overlay.querySelector('.zhr-wheel-arena');
      const result=overlay.querySelector('.zhr-wheel-result');
      btn.disabled=true; if(result) result.style.display='none';

      const arc=(Math.PI*2)/n;
      // Spin so that the pointer (at top = -PI/2) lands on the centre of prizeIdx segment
      // The wheel draws starting from -PI/2, so segment i centre = -PI/2 + arc*i + arc/2
      // We want that angle to equal -PI/2 after rotation => rot = -(arc*i + arc/2)
      // Add full rotations for drama
      const extraRot = Math.PI*2 * (6 + Math.floor(Math.random()*3));
      const segCentre = arc*prizeIdx + arc/2;
      const target = extraRot + segCentre; // total rotation to apply

      const t0=performance.now(), dur=5000+Math.random()*1000; let rot=0;
      let lastSegTick=-1;

      const anim=now=>{
        const p=Math.min((now-t0)/dur,1);
        // Ease-out quintic
        rot=target*(1-Math.pow(1-p,5));
        _drawWheel(rot);
        // Haptic tick every segment crossing
        const curSeg=Math.floor(((rot%(Math.PI*2))+Math.PI*2)%(Math.PI*2)/arc);
        if(curSeg!==lastSegTick){ lastSegTick=curSeg; if(navigator.vibrate) navigator.vibrate(4); }
        if(p<1){requestAnimationFrame(anim);}
        else {
          spinning=false;
          // Verify correct segment by calculating which one the pointer lands on
          const finalRot=target % (Math.PI*2);
          // pointer at top = 0 in rotated space => check which segment contains angle 0 - finalRot = -finalRot
          const pointerAngle = ((-finalRot) % (Math.PI*2) + Math.PI*2) % (Math.PI*2);
          const landedIdx = Math.floor(pointerAngle/arc) % n;
          _onDone(landedIdx, prizes[landedIdx]);
        }
      };
      requestAnimationFrame(anim);
    }

    function _onDone(idx,prize) {
      ajax('zsu_home_spin_result',{prize_index:idx}).then(r=>{
        if(!r.success&&r.data?.message==='already_spun'){
          toast('Already spun today!','err'); _showBlocked(r.data.seconds_left||82800); return;
        }
        const wonPrize = r.data?.prize || prize;
        _showResult(wonPrize);
        // Only blast congratulations for real wins, not "Try Again"
        if(wonPrize.type !== 'none') _confetti();
        if(navigator.vibrate) navigator.vibrate([40,20,80,20,120]);
      }).catch(()=>{
        _showResult(prize);
        if(prize.type !== 'none') _confetti();
      });
    }

    function _showResult(prize) {
      const result=overlay.querySelector('.zhr-wheel-result');
      const cw=result.querySelector('.zhr-wheel-coupon-wrap');
      const btn=overlay.querySelector('.zhr-wheel-btn');
      result.style.display='block'; if(btn) btn.style.display='none';
      result.querySelector('.zhr-wheel-result-label').textContent=prize.type==='none'?'Better luck next time':'You won';
      result.querySelector('.zhr-wheel-result-prize').textContent=prize.label||'';
      if(prize.type!=='none'&&prize.value){
        cw.style.display='block';
        result.querySelector('.zhr-wheel-coupon-code').textContent=prize.value;
        result.querySelector('.zhr-wheel-coupon-copy').onclick=()=>
          navigator.clipboard.writeText(prize.value).then(()=>toast('Copied!','ok')).catch(()=>toast(prize.value,'ok'));
      } else { cw.style.display='none'; }
    }

    function _confetti() {
      const c=overlay?.querySelector('.zhr-wheel-confetti'); if(!c) return; c.innerHTML='';

      // ── Full-screen BLAST overlay ────────────────────────────────────
      const blast=document.createElement('div');
      blast.id='zhr-blast-overlay';
      blast.style.cssText='position:fixed;inset:0;z-index:999999;pointer-events:none;overflow:hidden;';

      // Rain confetti from top
      const blastPal=['#FF6B6B','#FFE66D','#4ECDC4','#45B7D1','#96CEB4','#FFEAA7','#DDA0DD','#98D8C8','#F7DC6F','#BB8FCE'];
      const blastEmojis=['🎉','🎊','✨','🎈','🎁','⭐','💫','🌟','🎀','🏆'];

      // 150 confetti pieces raining from top
      for(let i=0;i<150;i++){
        const p=document.createElement('div');
        const isCircle = i%3===0;
        const r0=Math.random()*360, r1=r0+(Math.random()*400-200);
        p.style.cssText=`
          position:absolute;
          left:${Math.random()*100}%;
          top:-20px;
          width:${6+Math.random()*10}px;
          height:${6+Math.random()*10}px;
          background:${blastPal[i%blastPal.length]};
          border-radius:${isCircle?'50%':'2px'};
          --r0:${r0}deg;--r1:${r1}deg;
          animation:zhr-blast-rain ${1.5+Math.random()*2}s ${Math.random()*0.8}s cubic-bezier(.25,.46,.45,.94) forwards;
        `;
        blast.appendChild(p);
        setTimeout(()=>p.remove(),4000);
      }

      // 20 big emoji bursts from centre
      blastEmojis.forEach((em,i)=>{
        const el=document.createElement('div');
        const angle=(Math.PI*2/blastEmojis.length)*i;
        const dist=120+Math.random()*100;
        el.style.cssText=`
          position:absolute;left:50%;top:50%;
          font-size:${28+Math.random()*20}px;
          transform:translate(-50%,-50%);
          animation:zhr-blast-emoji ${0.8+Math.random()*0.4}s ${i*0.04}s cubic-bezier(.17,.67,.58,1.5) forwards;
          --bx:${Math.cos(angle)*dist}px;
          --by:${Math.sin(angle)*dist-60}px;
          opacity:0;
        `;
        el.textContent=em;
        blast.appendChild(el);
        setTimeout(()=>el.remove(),2500);
      });

      // Big 🎉 Congratulations! banner
      const banner=document.createElement('div');
      banner.style.cssText=`
        position:absolute;top:50%;left:50%;transform:translate(-50%,-50%) scale(0);
        background:linear-gradient(135deg,#1a1a2e,#16213e);
        color:#fff;border-radius:24px;padding:20px 40px;
        text-align:center;box-shadow:0 20px 80px rgba(0,0,0,.5);
        animation:zhr-blast-banner 0.6s 0.15s cubic-bezier(.34,1.56,.64,1) forwards;
        white-space:nowrap;
      `;
      banner.innerHTML=`
        <div style="font-size:clamp(2.5rem,8vw,4.5rem);line-height:1;margin-bottom:6px;">🎉</div>
        <div style="font-size:clamp(1.2rem,4vw,2rem);font-weight:900;letter-spacing:-.02em;
          background:linear-gradient(135deg,#FFD700,#FF6B6B,#4ECDC4);
          -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
          Congratulations!
        </div>
        <div style="font-size:clamp(.7rem,2vw,.95rem);opacity:.6;margin-top:4px;font-weight:500;-webkit-text-fill-color:#fff;">You're a winner 🏆</div>
      `;
      blast.appendChild(banner);

      // Ring burst from centre
      for(let r=0;r<3;r++){
        const ring=document.createElement('div');
        ring.style.cssText=`
          position:absolute;top:50%;left:50%;
          width:${60+r*40}px;height:${60+r*40}px;
          transform:translate(-50%,-50%);
          border:3px solid ${['#FFD700','#FF6B6B','#4ECDC4'][r]};
          border-radius:50%;
          animation:zhr-blast-ring ${0.7+r*0.2}s ${r*0.1}s ease-out forwards;
          opacity:0;
        `;
        blast.appendChild(ring);
        setTimeout(()=>ring.remove(),2000);
      }

      document.body.appendChild(blast);

      // Inject keyframes if not already there
      if(!document.getElementById('zhr-blast-kf')){
        const s=document.createElement('style'); s.id='zhr-blast-kf';
        s.textContent=`
          @keyframes zhr-blast-rain{
            0%{transform:rotate(var(--r0,0deg)) translateY(0);opacity:1;}
            80%{opacity:1;}
            100%{transform:rotate(var(--r1,360deg)) translateY(120vh);opacity:0;}
          }
          @keyframes zhr-blast-emoji{
            0%{opacity:0;transform:translate(-50%,-50%) scale(0);}
            60%{opacity:1;transform:translate(calc(-50% + var(--bx)),calc(-50% + var(--by))) scale(1.2);}
            100%{opacity:0;transform:translate(calc(-50% + var(--bx)),calc(-50% + var(--by) - 40px)) scale(.8);}
          }
          @keyframes zhr-blast-banner{
            0%{transform:translate(-50%,-50%) scale(0);opacity:0;}
            70%{transform:translate(-50%,-50%) scale(1.08);opacity:1;}
            100%{transform:translate(-50%,-50%) scale(1);opacity:1;}
          }
          @keyframes zhr-blast-ring{
            0%{transform:translate(-50%,-50%) scale(0);opacity:.9;}
            100%{transform:translate(-50%,-50%) scale(5);opacity:0;}
          }
          @keyframes zhr-blast-out{
            0%{opacity:1;} 100%{opacity:0;}
          }
        `;
        document.head.appendChild(s);
      }

      // Fade out and remove after 2.8s
      setTimeout(()=>{
        blast.style.animation='zhr-blast-out .4s ease forwards';
        setTimeout(()=>blast.remove(),450);
      },2800);

    }

    return {open,close};
  })();


  /* ══════════════════════════════════════════════════════════
     FAB
  ══════════════════════════════════════════════════════════ */
  function mountFab() {
    if(!cfg().spin_enabled&&cfg().spin_enabled!==undefined) return;
    if(!document.querySelector('.zhr')) return;
    if(document.querySelector('.zhr-spin-fab')) return;

    // Only show on the homepage — WordPress adds .home or .front-page to <body>
    const body = document.body;
    const isHomepage = body.classList.contains('home')
      || body.classList.contains('front-page')
      || body.classList.contains('page-template-home')
      || window.location.pathname === '/'
      || window.location.pathname === '/home/';

    if (!isHomepage) return;

    const fab = document.createElement('button');
    fab.className = 'zhr-spin-fab';
    fab.setAttribute('aria-label','Spin & Win');
    fab.innerHTML = `<img src="https://deepakclothings.in/wp-content/uploads/2026/05/01b935135728003.Y3JvcCw4MDgsNjMyLDAsMA.jpg" alt="Spin" style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;">`;
    fab.addEventListener('click', () => wheel.open());
    document.body.appendChild(fab);

    // Trigger animation class after append so CSS transition fires
    requestAnimationFrame(() => requestAnimationFrame(() => fab.classList.add('zhr-fab-visible')));
  }


  /* ══════════════════════════════════════════════════════════
     BOOT
  ══════════════════════════════════════════════════════════ */

  function boot() {
    document.querySelectorAll('.zhr').forEach(wrap=>{
      if(!wrap.dataset.rInit){ wrap.dataset.rInit='1';
        // Apply active preset as data-preset attribute so CSS vars kick in
        const preset = cfg().active_preset || wrap.dataset.preset || 'clean_white';
        if(preset) wrap.dataset.preset = preset;
        // Set CSS vars from preset map
        const presetMap = {
          // ── 5 White presets ──
          pure_white:  ['#111827','#f59e0b'],
          frost:       ['#1e40af','#3b82f6'],
          ivory:       ['#92400e','#d97706'],
          blush:       ['#be185d','#f43f5e'],
          sage:        ['#065f46','#10b981'],
          // ── 5 Black presets ──
          obsidian:    ['#f9fafb','#f59e0b'],
          midnight:    ['#818cf8','#a78bfa'],
          noir:        ['#e5e7eb','#d97706'],
          slate_dark:  ['#38bdf8','#0ea5e9'],
          carbon:      ['#86efac','#22c55e'],
          // ── legacy keys kept for back-compat ──
          clean_white: ['#2563eb','#f59e0b'], soft_gray:['#374151','#6b7280'],
          slate_blue:  ['#1e40af','#3b82f6'], warm_cream:['#92400e','#d97706'],
          rose_blush:  ['#be185d','#db2777'], sage_green:['#065f46','#059669'],
          charcoal_dark:['#f9fafb','#f59e0b'],navy_classic:['#1d4ed8','#fbbf24'],
          minimal_ink: ['#18181b','#52525b'], dusty_mauve:['#7c3aed','#a78bfa'],
          terracotta:  ['#c2410c','#ea580c'], steel:['#0f172a','#0ea5e9'],
          neon_dark:   ['#2563eb','#f59e0b'], cyber_punk:['#374151','#6b7280'],
          marble_gold: ['#92400e','#d97706'], ocean_wave:['#1e40af','#3b82f6'],
        };
        const pc = presetMap[preset] || presetMap['neon_dark'];
        wrap.style.setProperty('--primary', pc[0]);
        wrap.style.setProperty('--accent2', pc[1]);
        new Carousel3D(wrap);
      }
    });
    mountFab();

  }

  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',boot);
  else boot();
  if('MutationObserver' in window) new MutationObserver(boot).observe(document.body,{childList:true,subtree:true});
})();
