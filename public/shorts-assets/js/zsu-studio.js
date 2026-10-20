/* ═══════════════════════════════════════════════════════════════
   ZSU VENDOR STUDIO JS  v11
   Real file: assets/js/zsu-studio.js
   Localized via wp_localize_script('zsu-studio','ZSU_STUDIO',{…})
   ═══════════════════════════════════════════════════════════════ */

/* global ZSU_STUDIO, Chart */
(function () {
  'use strict';

  const S = ZSU_STUDIO;

  /* ── State ─────────────────────────────────────────────── */
  let state = {
    step:           1,
    uploadedUrl:    '',
    uploadedFile:   null,
    thumbDataUri:   '',
    thumbUrl:       '',
    transform:      { zoom:100, rotate:0, bright:100, contrast:100, sat:100, offsetX:0, offsetY:0, flipH:false },
    ratio:          '9:16',
    taggedProducts: [],
    currentTab:     'upload',
    selectedBoostId: null,
    selectedTier:   'mega',
    videoReady:     false,
    selectedDays:   7,
    vendorChart:    null,
  };

  /* (init moved to end of file) */

  /* ═══════════════════════════════════════════════════════
     TABS
     ═══════════════════════════════════════════════════════ */
  function initTabs() {
    qAll('.zsu-studio-tab').forEach((btn) => {
      btn.addEventListener('click', () => {
        const tab = btn.dataset.tab;
        qAll('.zsu-studio-tab').forEach((b) => b.classList.remove('active'));
        qAll('.zsu-studio-panel').forEach((p) => { p.style.display = 'none'; p.classList.remove('active'); });
        btn.classList.add('active');
        const panel = q(`#zsuTab-${tab}`);
        if (panel) { panel.style.display = 'block'; panel.classList.add('active'); }
        state.currentTab = tab;
        if (tab === 'analytics') loadAnalytics();
        if (tab === 'boost')     loadBoostTab();
        if (tab === 'my-shorts') loadMyShorts();
        if (tab === 'coins')     loadCoinsHistory();
      });
    });

    // Go Upload from My Shorts empty state
    on('#zsuGoUpload', 'click', () => {
      q('[data-tab="upload"]')?.click();
    });
  }

  /* ═══════════════════════════════════════════════════════
     UPLOAD WIZARD
     ═══════════════════════════════════════════════════════ */
  function initUploadWizard() {
    const $dropzone  = q('#zsuDropzone');
    const $fileInput = q('#zsuFileInput');
    const $browse    = q('#zsuDzBrowseBtn');

    if (!$dropzone) return;

    // ── Transform dropdown toggle ──────────────────────
    var $tToggle  = q('#zsuTransformToggle');
    var $tPanel   = q('#zsuTransformPanel');
    var $tChevron = q('#zsuTransformChevron');
    if ($tToggle && $tPanel) {
      $tToggle.addEventListener('click', function () {
        var isOpen = $tPanel.style.maxHeight !== '0px' && $tPanel.style.maxHeight !== '';
        if (isOpen) {
          $tPanel.style.maxHeight = '0px';
          $tPanel.style.paddingTop = '0';
          $tToggle.setAttribute('aria-expanded', 'false');
          if ($tChevron) $tChevron.style.transform = '';
          try { localStorage.setItem('zsu_tf_open','0'); } catch(e){}
        } else {
          $tPanel.style.maxHeight = '900px';
          $tPanel.style.paddingTop = '12px';
          $tToggle.setAttribute('aria-expanded', 'true');
          if ($tChevron) $tChevron.style.transform = 'rotate(180deg)';
          try { localStorage.setItem('zsu_tf_open','1'); } catch(e){}
        }
      });
      // Restore saved state
      try {
        if (localStorage.getItem('zsu_tf_open') === '1') {
          $tPanel.style.maxHeight = '900px';
          $tPanel.style.paddingTop = '12px';
          $tToggle.setAttribute('aria-expanded', 'true');
          if ($tChevron) $tChevron.style.transform = 'rotate(180deg)';
        }
      } catch(e) {}
    }

    // Drag-drop
    $dropzone.addEventListener('dragover', (e) => { e.preventDefault(); $dropzone.classList.add('drag-over'); });
    $dropzone.addEventListener('dragleave', () => $dropzone.classList.remove('drag-over'));
    $dropzone.addEventListener('drop', (e) => {
      e.preventDefault();
      $dropzone.classList.remove('drag-over');
      const file = e.dataTransfer.files[0];
      if (file) handleFileSelected(file);
    });
    $dropzone.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') $browse?.click(); });

    $browse.addEventListener('click', (e) => { e.stopPropagation(); $fileInput?.click(); });
    $dropzone.addEventListener('click', (e) => {
      if (e.target === $browse || $browse?.contains(e.target)) return;
      $fileInput?.click();
    });

    $fileInput?.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (file) handleFileSelected(file);
    });

    // Editor controls
    initEditorControls();

    // Step navigation
    on('#zsuStep2Next', 'click', () => {
      if (!state.thumbDataUri && !state.thumbUrl) {
        captureFrame(true); // silent fallback — auto was attempted on load, this is last resort
      }
      goToStep(3);
    });
    on('#zsuStep3Back', 'click', () => goToStep(2));
    on('#zsuStep3Next', 'click', () => {
      if (!q('#zsuShortTitle').value.trim()) {
        studioToast('Please enter a title', 'error');
        return;
      }
      buildReviewStep();
      goToStep(4);
    });
    on('#zsuStep4Back', 'click', () => goToStep(3));

    // Publish
    on('#zsuPublishBtn', 'click', publishShort);

    // Upload another
    on('#zsuUploadAnother', 'click', resetWizard);

    // Title/desc counters
    on('#zsuShortTitle', 'input', () => {
      q('#zsuTitleCount').textContent = q('#zsuShortTitle').value.length;
    });
    on('#zsuShortDesc', 'input', () => {
      q('#zsuDescCount').textContent = q('#zsuShortDesc').value.length;
    });

    // Product search
    initProductSearch();
  }

  function handleFileSelected(file) {
    // Accept any video/* type or any common video extension
    const videoExts = /\.(mp4|webm|mov|avi|mkv|m4v|3gp|3g2|flv|ts|ogv|wmv|asf|mpeg|mpg|mts|m2ts|vob|divx|f4v|rm|rmvb)$/i;
    const validTypes = file.type.startsWith('video/') || videoExts.test(file.name);
    const maxBytes    = S.max_mb * 1024 * 1024;
    const $err        = q('#zsuUploadError');

    if (!validTypes) {
      showErr($err, '❌ Invalid file type. Please upload a video file.');
      return;
    }
    if (file.size > maxBytes) {
      showErr($err, `❌ File too large. Maximum is ${S.max_mb}MB.`);
      return;
    }
    if (S.upload_count >= S.max_uploads) {
      showErr($err, `❌ Upload limit reached (${S.upload_count}/${S.max_uploads}).`);
      return;
    }
    hideEl($err);

    state.uploadedFile = file;

    // Show progress
    const $prog    = q('#zsuDzProgress');
    const $progBar = q('#zsuDzProgressBar');
    const $progTxt = q('#zsuDzProgressText');
    const $inner   = q('#zsuDropzoneInner');

    $inner.style.display = 'none';
    $prog.style.display  = 'flex';

    // XHR upload with progress
    const formData = new FormData();
    formData.append('action',  'zsu_upload_short');
    formData.append('nonce',   S.nonce);
    formData.append('video',   file);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', S.ajax_url);

    xhr.upload.addEventListener('progress', (e) => {
      if (!e.lengthComputable) return;
      const pct = Math.round((e.loaded / e.total) * 100);
      $progBar.style.setProperty('--progress', pct + '%');
      $progTxt.textContent = `Uploading… ${pct}%`;
    });

    xhr.addEventListener('load', () => {
      try {
        const res = JSON.parse(xhr.responseText);
        if (res.success) {
          state.uploadedUrl = res.video_url;
          $progTxt.textContent = '✅ Upload complete!';
          setTimeout(() => {
            loadEditorVideo(res.video_url);
            goToStep(2);
            $prog.style.display  = 'none';
            $inner.style.display = 'block';
          }, 600);
        } else {
          $prog.style.display  = 'none';
          $inner.style.display = 'block';
          showErr($err, res.message || '❌ Upload failed. Please try again.');
        }
      } catch (e) {
        $prog.style.display  = 'none';
        $inner.style.display = 'block';
        showErr($err, '❌ Unexpected error during upload.');
      }
    });

    xhr.addEventListener('error', () => {
      $prog.style.display  = 'none';
      $inner.style.display = 'block';
      showErr($err, '❌ Network error. Please try again.');
    });

    xhr.send(formData);
  }

  /* ── Editor ────────────────────────────────────────────── */
  function loadEditorVideo(url) {
    const vid = q('#zsuEditorVideo');
    if (!vid) return;

    // Reset state
    state.videoReady = false;
    state.thumbDataUri = '';
    const $capBtn = q('#zsuCaptureThumbnail');
    if ($capBtn) $capBtn.disabled = true;

    // Remove crossOrigin — CORS blocks canvas reads without proper server headers
    vid.removeAttribute('crossorigin');
    vid.src = url;
    vid.muted = true;
    vid.playsInline = true;
    vid.loop = true;
    vid.load();

    // Keep trying to play every 300ms until it works (handles autoplay policy)
    let playAttempts = 0;
    const tryPlay = () => {
      if (vid.paused && playAttempts < 20) {
        playAttempts++;
        vid.play().catch(() => {});
        setTimeout(tryPlay, 300);
      }
    };

    vid.addEventListener('loadedmetadata', function onMeta() {
      vid.removeEventListener('loadedmetadata', onMeta);
      tryPlay();
    }, { once: true });

    vid.addEventListener('canplay', function onCanPlay() {
      vid.removeEventListener('canplay', onCanPlay);
      // Enable capture once video can play
      state.videoReady = true;
      if ($capBtn) $capBtn.disabled = false;
      // Auto-capture thumbnail from current frame
      requestAnimationFrame(() => captureFrame(true));
    }, { once: true });

    // Ensure video keeps playing — re-trigger if it stops unexpectedly
    vid.addEventListener('pause', function onPause() {
      // Only auto-resume if we're on step 2 (editor view)
      if (state.step === 2 && state.uploadedUrl) {
        vid.play().catch(() => {});
      }
    });

    // Fallback
    setTimeout(() => {
      if (!state.videoReady) {
        state.videoReady = true;
        if ($capBtn) $capBtn.disabled = false;
      }
    }, 5000);
  }

  function initEditorControls() {
    const sliders = {
      '#zsuZoomSlider':    ['zoom',     '#zsuZoomVal',    (v) => v + '%'],
      '#zsuRotateSlider':  ['rotate',   '#zsuRotateVal',  (v) => v + '°'],
      '#zsuBrightSlider':  ['bright',   '#zsuBrightVal',  (v) => v + '%'],
      '#zsuContrastSlider':['contrast', '#zsuContrastVal',(v) => v + '%'],
      '#zsuSatSlider':     ['sat',      '#zsuSatVal',     (v) => v + '%'],
      '#zsuOffsetXSlider': ['offsetX',  '#zsuOffsetXVal', (v) => v + 'px'],
      '#zsuOffsetYSlider': ['offsetY',  '#zsuOffsetYVal', (v) => v + 'px'],
    };

    Object.entries(sliders).forEach(([sel, [key, valSel, fmt]]) => {
      const el = q(sel);
      if (!el) return;
      el.addEventListener('input', () => {
        state.transform[key] = parseFloat(el.value);
        const $val = q(valSel);
        if ($val) $val.textContent = fmt(el.value);
        applyTransform();
      });
    });

    // Quick actions
    on('#zsuFlipH', 'click', () => {
      state.transform.flipH = !state.transform.flipH;
      applyTransform();
    });
    on('#zsuRotate90', 'click', () => {
      const sl = q('#zsuRotateSlider');
      state.transform.rotate = (state.transform.rotate + 90) % 360;
      if (sl) sl.value = state.transform.rotate;
      q('#zsuRotateVal').textContent = state.transform.rotate + '°';
      applyTransform();
    });
    on('#zsuResetTransform', 'click', () => {
      state.transform = { zoom:100, rotate:0, bright:100, contrast:100, sat:100, offsetX:0, offsetY:0, flipH:false };
      ['#zsuZoomSlider','#zsuRotateSlider','#zsuBrightSlider','#zsuContrastSlider','#zsuSatSlider','#zsuOffsetXSlider','#zsuOffsetYSlider'].forEach((sel) => {
        const sl = q(sel);
        if (sl) {
          sl.value = sl.defaultValue;
        }
      });
      ['#zsuZoomVal','#zsuRotateVal','#zsuBrightVal','#zsuContrastVal','#zsuSatVal'].forEach((sel) => {
        const el = q(sel);
        if (el) el.textContent = el.textContent.includes('°') ? '0°' : '100%';
      });
      q('#zsuOffsetXVal').textContent = '0px';
      q('#zsuOffsetYVal').textContent = '0px';
      applyTransform();
    });

    // Ratio buttons
    qAll('.zsu-ratio-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        qAll('.zsu-ratio-btn').forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');
        state.ratio = btn.dataset.ratio;
        updateRatioPreview();
        // Sync ratio in step 3
        const $sel = q('#zsuShortRatio');
        if ($sel) $sel.value = state.ratio;
      });
    });

    // Thumbnail buttons
    on('#zsuCaptureThumbnail', 'click', captureFrame);
    on('#zsuUploadThumbnail',  'click', () => q('#zsuThumbFileInput')?.click());
    q('#zsuThumbFileInput')?.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = (ev) => setThumbnail(ev.target.result);
      reader.readAsDataURL(file);
    });
  }

  function applyTransform() {
    const vid = q('#zsuEditorVideo');
    if (!vid) return;
    const { zoom, rotate, bright, contrast, sat, offsetX, offsetY, flipH } = state.transform;
    const scaleX = (flipH ? -1 : 1) * (zoom / 100);
    vid.style.transform  = `translate(${offsetX}px, ${offsetY}px) scale(${scaleX}, ${zoom/100}) rotate(${rotate}deg)`;
    vid.style.filter     = `brightness(${bright}%) contrast(${contrast}%) saturate(${sat}%)`;

    // Build CSS transform string to save
    state.transformStr = vid.style.transform + '; filter:' + vid.style.filter;
  }

  function updateRatioPreview() {
    const screen = q('#zsuEditorPhoneScreen');
    if (!screen) return;
    const ratioMap = { '9:16':'56.25%', '1:1':'100%', '4:5':'80%', '16:9':'177.78%' };
    // We control phone width=220px fixed, so adjust height via aspect-ratio
    const [w, h] = state.ratio.split(':').map(Number);
    screen.style.aspectRatio = `${w}/${h}`;
  }

  function captureFrame(silent) {
    silent = silent || false;
    const vid = q('#zsuEditorVideo');
    if (!vid || !vid.videoWidth) {
      if (!silent) studioToast('⏳ Video still loading, please wait…', 'info');
      return;
    }

    state.videoReady = true;

    const canvas = document.createElement('canvas');
    canvas.width  = vid.videoWidth  || 720;
    canvas.height = vid.videoHeight || 1280;
    const ctx = canvas.getContext('2d');

    const { bright, contrast, sat } = state.transform;
    ctx.filter = `brightness(${bright}%) contrast(${contrast}%) saturate(${sat}%)`;
    ctx.drawImage(vid, 0, 0, canvas.width, canvas.height);

    // Detect solid-black frame — sample 16 corners/midpoints
    const isBlack = (function() {
      try {
        const w = canvas.width, h = canvas.height;
        const pts = [[w*.1,h*.1],[w*.5,h*.1],[w*.9,h*.1],[w*.1,h*.5],[w*.5,h*.5],
                     [w*.9,h*.5],[w*.1,h*.9],[w*.5,h*.9],[w*.9,h*.9],[w*.3,h*.3],
                     [w*.7,h*.3],[w*.3,h*.7],[w*.7,h*.7],[w*.2,h*.5],[w*.5,h*.2],[w*.8,h*.5]];
        let sum = 0;
        pts.forEach(([x,y]) => { const d=ctx.getImageData(Math.round(x),Math.round(y),1,1).data; sum+=(d[0]+d[1]+d[2])/3; });
        return (sum / pts.length) < 8;
      } catch(e) { return false; }
    })();

    if (isBlack && !silent) {
      // Black frame detected — re-seek to a different random point and retry once
      const vidEl = q('#zsuEditorVideo');
      if (vidEl && vidEl.duration > 0) {
        const newSeek = vidEl.duration * (0.3 + Math.random() * 0.4);
        vidEl.currentTime = newSeek;
        vidEl.addEventListener('seeked', function retry() {
          vidEl.removeEventListener('seeked', retry);
          captureFrame(false); // retry with toast
        }, { once: true });
        studioToast('🎯 Finding a better frame…', 'info');
        return;
      }
    }

    const dataUri = canvas.toDataURL('image/jpeg', 0.88);
    setThumbnail(dataUri);
    if (!silent) studioToast('📸 Thumbnail captured!', 'success');
  }

  function setThumbnail(dataUri) {
    state.thumbDataUri = dataUri;
    const $preview = q('#zsuThumbPreview');
    const $placeholder = q('#zsuThumbPlaceholder');
    if ($preview) { $preview.src = dataUri; $preview.style.display = 'block'; }
    if ($placeholder) $placeholder.style.display = 'none';
    const $hidden = q('#zsuThumbDataUri');
    if ($hidden) $hidden.value = dataUri;
  }

  /* ── Product Search (Step 3) ────────────────────────────── */
  function initProductSearch() {
    const $input = q('#zsuProductSearchInput');
    const $dd    = q('#zsuProductSearchDropdown');
    if (!$input) return;

    let timer;
    $input.addEventListener('input', () => {
      clearTimeout(timer);
      const val = $input.value.trim();
      if (val.length < 2) { hideEl($dd); return; }
      timer = setTimeout(() => {
        ajax('zsu_vendor_search_products', { q: val }).then((res) => {
          if (!res.success || !res.products.length) { hideEl($dd); return; }
          $dd.innerHTML = res.products.map((p) => `
            <div class="zsu-product-search-item" data-id="${p.id}" data-name="${esc(p.name)}" data-img="${esc(p.image||'')}">
              <img class="zsu-product-search-thumb" src="${esc(p.image||'')}" alt="${esc(p.name)}" loading="lazy">
              <div style="font-size:12px;font-weight:600;color:#fff;">${esc(p.name)}</div>
            </div>`).join('');
          $dd.style.display = 'block';

          $dd.querySelectorAll('.zsu-product-search-item').forEach((item) => {
            item.addEventListener('click', () => {
              const id   = parseInt(item.dataset.id,10);
              const name = item.dataset.name;
              const img  = item.dataset.img;
              tagProduct(id, name, img);
              $input.value = '';
              hideEl($dd);
            });
          });
        });
      }, 300);
    });

    document.addEventListener('click', (e) => {
      if (!e.target.closest('#zsuProductTagger')) hideEl($dd);
    });
  }

  function tagProduct(id, name, img) {
    if (state.taggedProducts.find((p) => p.id === id)) return;
    state.taggedProducts.push({ id, name, img });
    renderTaggedProducts();
  }

  function renderTaggedProducts() {
    const $wrap  = q('#zsuTaggedProducts');
    const $empty = q('#zsuTaggedEmpty');
    const $ids   = q('#zsuTaggedProductIds');

    if (!$wrap) return;

    if (!state.taggedProducts.length) {
      if ($empty) $empty.style.display = 'block';
      if ($ids) $ids.value = '';
      return;
    }

    if ($empty) $empty.style.display = 'none';
    if ($ids) $ids.value = state.taggedProducts.map((p) => p.id).join(',');

    $wrap.innerHTML = state.taggedProducts.map((p) => `
      <div class="zsu-tagged-chip">
        <img src="${esc(p.img)}" style="width:20px;height:20px;border-radius:4px;object-fit:cover;" loading="lazy" alt="${esc(p.name)}">
        ${esc(p.name)}
        <button class="zsu-tagged-remove" data-id="${p.id}" aria-label="Remove">✕</button>
      </div>`).join('');

    $wrap.querySelectorAll('.zsu-tagged-remove').forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = parseInt(btn.dataset.id, 10);
        state.taggedProducts = state.taggedProducts.filter((p) => p.id !== id);
        renderTaggedProducts();
      });
    });
  }

  /* ── Step navigation ────────────────────────────────────── */
  function goToStep(step) {
    state.step = step;
    qAll('.zsu-wizard-pane').forEach((p) => p.style.display = 'none');
    qAll('.zsu-wizard-step').forEach((s) => {
      const n = parseInt(s.dataset.step, 10);
      s.classList.toggle('active', n === step);
      s.classList.toggle('done',   n < step);
    });
    const $pane = q(`#zsuStep${step}`);
    if ($pane) $pane.style.display = 'block';
    // Sync review video
    if (step === 4) {
      const rv = q('#zsuReviewVideo');
      if (rv && state.uploadedUrl) { rv.src = state.uploadedUrl; rv.play().catch(()=>{}); }
    }
  }

  /* ── Build review ───────────────────────────────────────── */
  function buildReviewStep() {
    const title    = q('#zsuShortTitle')?.value.trim() || '';
    const catId    = q('#zsuShortCategory')?.value    || '';
    const catName  = q('#zsuShortCategory option:checked')?.textContent || '—';
    const ratio    = q('#zsuShortRatio')?.value        || state.ratio;
    const products = state.taggedProducts.length;
    const status   = S.auto_approve ? 'Public' : 'Pending Review';

    setText('zsuReviewTitle', title);

    const $meta = q('#zsuReviewMeta');
    if ($meta) {
      $meta.innerHTML = [
        ['Title',     title || '—'],
        ['Category',  catName],
        ['Ratio',     ratio],
        ['Products',  products + ' tagged'],
        ['Status',    status],
        ['Thumbnail', state.thumbDataUri ? '✅ Set' : '⚠️ Auto-generated'],
      ].map(([label, val]) => `
        <div class="zsu-review-meta-item">
          <div class="zsu-review-meta-label">${label}</div>
          <div class="zsu-review-meta-value">${esc(val)}</div>
        </div>`).join('');
    }
  }

  /* ── Publish ────────────────────────────────────────────── */
  function publishShort() {
    const $btn     = q('#zsuPublishBtn');
    const $label   = q('#zsuPublishBtnLabel');
    const $success = q('#zsuPublishSuccess');

    if ($btn) { $btn.disabled = true; }
    if ($label) $label.textContent = '⏳ Publishing…';

    ajax('zsu_save_short', {
      video_url:   state.uploadedUrl,
      thumb_data:  state.thumbDataUri,
      title:       q('#zsuShortTitle')?.value.trim() || '',
      excerpt:     q('#zsuShortDesc')?.value.trim()  || '',
      category:    q('#zsuShortCategory')?.value     || '',
      ratio:       q('#zsuShortRatio')?.value         || state.ratio,
      product_ids: state.taggedProducts.map((p) => p.id).join(','),
      transform:   state.transformStr || '',
    }).then((res) => {
      if ($btn) $btn.disabled = false;
      if (!res.success) {
        if ($label) $label.textContent = '🚀 Publish Short';
        studioToast(res.message || 'Publish failed', 'error');
        return;
      }

      // Show success state
      q('#zsuPublishSection')?.remove();
      q('#zsuStep4Back')?.remove();
      if ($success) {
        $success.style.display = 'block';
        const desc = S.auto_approve
          ? `Your short "${res.title}" is now live!`
          : `"${res.title}" has been submitted for review.`;
        setText('zsuSuccessDesc', desc);
        const $link = q('#zsuViewShortLink');
        if ($link) $link.href = `${S.shorts_url}v/${res.post_id}/`;
      }

      studioToast('🎉 Short published successfully!', 'success');
    });
  }

  /* ── Reset wizard ───────────────────────────────────────── */
  function resetWizard() {
    state = { ...state, step:1, uploadedUrl:'', uploadedFile:null, thumbDataUri:'', thumbUrl:'', transform:{zoom:100,rotate:0,bright:100,contrast:100,sat:100,offsetX:0,offsetY:0,flipH:false}, ratio:'9:16', taggedProducts:[] };

    q('#zsuPublishSuccess').style.display = 'none';
    goToStep(1);
    // Reset form fields
    ['#zsuShortTitle','#zsuShortDesc','#zsuShortCategory'].forEach((sel) => {
      const el = q(sel);
      if (el) el.value = '';
    });
    ['#zsuTitleCount','#zsuDescCount'].forEach((sel) => { const el = q(sel); if (el) el.textContent = '0'; });
    state.taggedProducts = [];
    renderTaggedProducts();

    const $preview = q('#zsuThumbPreview');
    if ($preview) { $preview.src = ''; $preview.style.display = 'none'; }
    q('#zsuThumbPlaceholder')?.style?.removeProperty('display');
    studioToast('Ready to upload another!', 'info');
  }

  /* ═══════════════════════════════════════════════════════
     MY SHORTS
     ═══════════════════════════════════════════════════════ */
  function initMyShorts() {
    loadMyShorts();
    on('#zsuMyRefresh', 'click', loadMyShorts);
    on('#zsuMyStatusFilter', 'change', loadMyShorts);

    // Edit modal
    on('#zsuEditClose',   'click', closeEditModal);
    on('#zsuEditCancel',  'click', closeEditModal);
    on('#zsuEditOverlay', 'click', closeEditModal);
    on('#zsuEditSave',    'click', saveEdit);
  }

  function loadMyShorts() {
    const $grid  = q('#zsuMyShortsGrid');
    const $empty = q('#zsuMyEmpty');
    if (!$grid) return;

    $grid.innerHTML = '<div class="zsu-studio-spinner-wrap"><div class="zsu-studio-spinner"></div></div>';
    $empty && ($empty.style.display = 'none');

    const status = q('#zsuMyStatusFilter')?.value || '';
    ajax('zsu_vendor_get_shorts', { status_filter: status }).then((res) => {
      if (!res.success || !res.shorts.length) {
        $grid.innerHTML = '';
        $empty && ($empty.style.display = 'block');
        return;
      }

      $grid.innerHTML = res.shorts.map((s) => buildMyShortCard(s)).join('');

      // Wire ⋮ menu button toggles
      $grid.querySelectorAll('.zsu-my-menu-btn').forEach((btn) => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          const id = btn.dataset.id;
          const dd = q(`#zsuCardMenu-${id}`);
          // Close all other open dropdowns first
          qAll('.zsu-my-card-dropdown.open').forEach((d) => { if (d !== dd) d.classList.remove('open'); });
          dd?.classList.toggle('open');
        });
      });
      // Close dropdowns when clicking outside
      document.addEventListener('click', () => {
        qAll('.zsu-my-card-dropdown.open').forEach((d) => d.classList.remove('open'));
      }, { once: false });

      // Wire card actions
      $grid.querySelectorAll('.zsu-my-action-edit').forEach((btn) => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          qAll('.zsu-my-card-dropdown.open').forEach((d) => d.classList.remove('open'));
          openEditModal(parseInt(btn.dataset.id, 10), btn.dataset);
        });
      });
      $grid.querySelectorAll('.zsu-my-action-delete').forEach((btn) => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          qAll('.zsu-my-card-dropdown.open').forEach((d) => d.classList.remove('open'));
          deleteMyShort(parseInt(btn.dataset.id, 10));
        });
      });
      $grid.querySelectorAll('.zsu-my-action-boost').forEach((btn) => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          qAll('.zsu-my-card-dropdown.open').forEach((d) => d.classList.remove('open'));
          q('[data-tab="boost"]')?.click();
          setTimeout(() => selectBoostShort(parseInt(btn.dataset.id, 10)), 200);
        });
      });
    });
  }

  function buildMyShortCard(s) {
    const statusCls = s.status === 'publish' ? 'publish' : s.status === 'pending' ? 'pending' : 'draft';
    const statusLabel = s.status === 'publish' ? '✅ Live' : s.status === 'pending' ? '⏳ Pending' : '📝 Draft';
    const pid = s.product_ids ? (Array.isArray(s.product_ids) ? s.product_ids.join(',') : s.product_ids) : '';
    return `
      <div class="zsu-my-short-card">
        <div class="zsu-my-short-thumb-wrap">
          <img class="zsu-my-short-thumb" src="${esc(s.thumb_url||s.thumb)}" alt="${esc(s.title)}" loading="lazy">
          <div class="zsu-my-short-status-badge zsu-my-status-${statusCls}">${statusLabel}</div>
          <!-- ⋮ Dropdown menu -->
          <div class="zsu-my-card-menu">
            <button class="zsu-my-menu-btn" data-id="${s.id}" aria-label="Options" title="Options">⋮</button>
            <div class="zsu-my-card-dropdown" id="zsuCardMenu-${s.id}">
              <button class="zsu-my-dd-item zsu-my-action-edit"
                      data-id="${s.id}"
                      data-title="${esc(s.title)}"
                      data-desc="${esc(s.excerpt||'')}"
                      data-products="${esc(pid)}">
                ✏️ Edit
              </button>
              ${S.boost_enabled ? `<button class="zsu-my-dd-item zsu-my-action-boost" data-id="${s.id}">🚀 Boost</button>` : ''}
              <div class="zsu-my-dd-divider"></div>
              <button class="zsu-my-dd-item zsu-my-dd-danger zsu-my-action-delete" data-id="${s.id}">🗑 Delete</button>
            </div>
          </div>
        </div>
        <div class="zsu-my-short-info">
          <div class="zsu-my-short-title">${esc(s.title)}</div>
          <div class="zsu-my-short-stats">
            <span>👁 ${esc(s.views||0)}</span>
            <span>❤️ ${esc(s.likes||0)}</span>
          </div>
          ${s.status === 'publish' && s.permalink ? `<a class="zsu-my-view-btn" href="${esc(s.permalink)}" target="_blank" rel="noopener" title="View on Frontend">🔗 View</a>` : ''}
        </div>
      </div>`;
  }

  function openEditModal(id, data) {
    q('#zsuEditShortId').value    = id;
    q('#zsuEditTitle').value      = data.title || '';
    q('#zsuEditDesc').value       = data.desc  || '';
    q('#zsuEditProducts').value   = data.products || '';
    q('#zsuEditModal').style.display   = 'block';
    q('#zsuEditOverlay').style.display = 'block';
  }

  function closeEditModal() {
    q('#zsuEditModal').style.display   = 'none';
    q('#zsuEditOverlay').style.display = 'none';
  }

  function saveEdit() {
    const id       = parseInt(q('#zsuEditShortId').value, 10);
    const title    = q('#zsuEditTitle').value.trim();
    const desc     = q('#zsuEditDesc').value.trim();
    const products = q('#zsuEditProducts').value.trim();

    if (!title) { studioToast('Title is required', 'error'); return; }

    ajax('zsu_vendor_update_short', { post_id: id, title, excerpt: desc, product_ids: products })
    .then((res) => {
      if (res.success) {
        studioToast('✅ Short updated!', 'success');
        closeEditModal();
        loadMyShorts();
      } else {
        studioToast(res.message || 'Update failed', 'error');
      }
    });
  }

  function deleteMyShort(id) {
    if (!confirm('Delete this short permanently? This cannot be undone.')) return;
    ajax('zsu_vendor_delete_short', { post_id: id }).then((res) => {
      if (res.success) {
        studioToast('🗑 Short deleted.', 'info');
        loadMyShorts();
      } else {
        studioToast(res.message || 'Delete failed', 'error');
      }
    });
  }

  /* ═══════════════════════════════════════════════════════
     ANALYTICS
     ═══════════════════════════════════════════════════════ */
  function loadAnalytics() {
    const days = parseInt(q('#zsuAnalyticsDays')?.value || 30, 10);

    q('#zsuAnalyticsCards').innerHTML = '<div class="zsu-studio-spinner-wrap" style="grid-column:1/-1;"><div class="zsu-studio-spinner"></div></div>';
    q('#zsuAnalyticsTop').innerHTML   = '<div class="zsu-studio-spinner-wrap"><div class="zsu-studio-spinner"></div></div>';

    ajax('zsu_vendor_analytics', { days }).then((res) => {
      if (!res.success) return;

      // Summary cards
      const t = res.totals || {};
      const cards = [
        ['👁','Views',   t.views   || 0, '#7c3aed'],
        ['❤️','Likes',   t.likes   || 0, '#f43f5e'],
        ['🔗','Shares',  t.shares  || 0, '#10b981'],
        ['🛒','Clicks',  t.clicks  || 0, '#f59e0b'],
        ['🎬','Shorts',  res.shorts_count || 0, '#06b6d4'],
        ['🪙','Coins',   res.coins || 0, '#f97316'],
      ];

      q('#zsuAnalyticsCards').innerHTML = cards.map(([icon,label,val,color]) => `
        <div class="zsu-analytics-card" style="--card-color:${color}">
          <div class="zsu-ac-icon">${icon}</div>
          <div class="zsu-ac-val">${fnum(val)}</div>
          <div class="zsu-ac-label">${label} (${days}d)</div>
        </div>`).join('');

      // Chart
      const chartData = res.chart || [];
      const ctx = q('#zsuVendorChart');
      if (ctx && chartData.length) {
        if (state.vendorChart) state.vendorChart.destroy();
        if (typeof Chart !== 'undefined') {
          state.vendorChart = new Chart(ctx, {
            type: 'line',
            data: {
              labels: chartData.map((r) => r.day),
              datasets: [
                { label:'👁 Views',  data: chartData.map((r) => r.views  || 0), borderColor:'#7c3aed', backgroundColor:'rgba(124,58,237,0.07)', tension:0.4, fill:true, pointRadius:2 },
                { label:'❤️ Likes', data: chartData.map((r) => r.likes  || 0), borderColor:'#f43f5e', backgroundColor:'rgba(244,63,94,0.05)',  tension:0.4, fill:true, pointRadius:2 },
                { label:'🔗 Shares',data: chartData.map((r) => r.shares || 0), borderColor:'#10b981', backgroundColor:'rgba(16,185,129,0.05)', tension:0.4, fill:true, pointRadius:2 },
              ],
            },
            options: {
              responsive: true, maintainAspectRatio: true,
              plugins: { legend: { labels: { color:'#94a3b8', font:{size:11}, boxWidth:12 } }, tooltip: { backgroundColor:'#1a1d2e', titleColor:'#fff', bodyColor:'#94a3b8' } },
              scales: {
                x: { grid:{color:'rgba(255,255,255,0.03)'}, ticks:{color:'#475569',font:{size:10}} },
                y: { grid:{color:'rgba(255,255,255,0.03)'}, ticks:{color:'#475569', callback:(v)=>fnum(v)} },
              },
            },
          });
        }
      }

      // Top shorts table
      const tops = res.top_shorts || [];
      if (!tops.length) {
        q('#zsuAnalyticsTop').innerHTML = '<p style="color:#64748b;font-size:13px;padding:10px 0;">No data yet.</p>';
      } else {
        q('#zsuAnalyticsTop').innerHTML = `
          <table class="zsu-studio-table">
            <thead><tr>
              <th>#</th><th>Short</th><th>👁 Views</th><th>❤️ Likes</th><th>🔗 Shares</th>
            </tr></thead>
            <tbody>
              ${tops.slice(0,10).map((s,i) => `
                <tr>
                  <td style="color:#64748b;font-weight:700;">${i+1}</td>
                  <td style="font-weight:600;color:#fff;">${esc(s.post_title)}</td>
                  <td>${fnum(s.views)}</td>
                  <td>${fnum(s.likes)}</td>
                  <td>${fnum(s.shares)}</td>
                </tr>`).join('')}
            </tbody>
          </table>`;
      }
    });

    // Re-load on date change
    on('#zsuAnalyticsDays', 'change', loadAnalytics);
  }

  /* ═══════════════════════════════════════════════════════
     BOOST TAB
     ═══════════════════════════════════════════════════════ */
  function initBoostTab() {
    // Only mega tier — pre-select it
    state.selectedTier = 'mega';
    updateBoostCostPreview();

    // Duration buttons
    qAll('.zsu-duration-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        qAll('.zsu-duration-btn').forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');
        state.selectedDays = parseInt(btn.dataset.days, 10);
        updateBoostCostPreview();
      });
    });

    // Submit boost
    on('#zsuBoostSubmitBtn', 'click', submitBoost);
  }

  function loadBoostTab() {
    // Load published shorts as picker
    ajax('zsu_vendor_get_shorts', { status_filter: 'publish' }).then((res) => {
      const $picker = q('#zsuBoostShortPicker');
      if (!$picker) return;
      if (!res.success || !res.shorts.length) {
        $picker.innerHTML = '<p style="color:#64748b;font-size:13px;">No published shorts available to boost.</p>';
        return;
      }
      $picker.innerHTML = res.shorts.map((s) => `
        <div class="zsu-boost-short-item${state.selectedBoostId === s.id ? ' selected' : ''}" data-id="${s.id}">
          <img class="zsu-boost-short-thumb" src="${esc(s.thumb_url||s.thumb)}" alt="${esc(s.title)}" loading="lazy">
          <div class="zsu-boost-short-title">${esc(s.title)}</div>
        </div>`).join('');

      $picker.querySelectorAll('.zsu-boost-short-item').forEach((item) => {
        item.addEventListener('click', () => selectBoostShort(parseInt(item.dataset.id, 10)));
      });
    });

    loadActiveBoosts();
  }

  function selectBoostShort(id) {
    state.selectedBoostId = id;
    qAll('.zsu-boost-short-item').forEach((item) => {
      item.classList.toggle('selected', parseInt(item.dataset.id, 10) === id);
    });
    q('#zsuBoostShortId').value = id;
    updateBoostCostPreview();
    showEl(q('#zsuBoostFormSection'));
  }

  function updateBoostCostPreview() {
    const base  = S.boost_prices[state.selectedTier] || 0;
    const total = Math.round(base * (state.selectedDays / 7));
    const after = S.coins - total;

    setText('zsuBoostBaseCost',    `🪙 ${fnum(base)} / 7 days`);
    setText('zsuBoostTotalCost',   `🪙 ${fnum(total)}`);
    setText('zsuBoostAfterBalance',`🪙 ${fnum(Math.max(0, after))} ${after < 0 ? '⚠️ Insufficient' : ''}`);
    q('#zsuBoostTier').value = state.selectedTier;
  }

  function submitBoost() {
    const shortId = parseInt(q('#zsuBoostShortId')?.value || 0, 10);
    const tier    = state.selectedTier;
    const days    = state.selectedDays;
    const cost    = Math.round((S.boost_prices[tier] || 0) * (days / 7));

    if (!shortId) { studioToast('Please select a short to boost', 'error'); return; }
    if (S.coins < cost) { studioToast(`Insufficient coins! Need ${fnum(cost)}, have ${fnum(S.coins)}`, 'error'); return; }

    const $btn = q('#zsuBoostSubmitBtn');
    if ($btn) $btn.disabled = true;

    ajax('zsu_request_boost', { short_id: shortId, boost_type: tier, days }).then((res) => {
      if ($btn) $btn.disabled = false;
      const $result = q('#zsuBoostResult');
      if (res.success) {
        S.coins -= cost;
        setText('zsuStudioCoinCount', fnum(S.coins));
        setText('zsuBoostCoinBadge',  fnum(S.coins));
        setText('zsuBoostBalance',    `🪙 ${fnum(S.coins)}`);
        setText('zsuBoostAfterBalance', `🪙 ${fnum(S.coins)}`);
        studioToast(`🚀 Boost activated for ${days} days!`, 'success');
        if ($result) { $result.className = 'zsu-boost-result success'; $result.textContent = '✅ Boost request submitted! It will be reviewed and activated shortly.'; showEl($result); }
        loadActiveBoosts();
      } else {
        studioToast(res.message || 'Boost failed', 'error');
        if ($result) { $result.className = 'zsu-boost-result error'; $result.textContent = res.message || 'Boost failed. Please try again.'; showEl($result); }
      }
    });
  }

  function loadActiveBoosts() {
    const $wrap = q('#zsuActiveBoostsWrap');
    if (!$wrap) return;
    $wrap.innerHTML = '<div class="zsu-studio-spinner-wrap"><div class="zsu-studio-spinner"></div></div>';

    ajax('zsu_vendor_get_boosts').then((res) => {
      if (!res.success || !res.boosts || !res.boosts.length) {
        $wrap.innerHTML = '<p style="color:#64748b;font-size:13px;padding:10px 0;">No boosts yet. Purchase a boost above to amplify your shorts!</p>';
        return;
      }

      const tierColors = { standard:'#3b82f6', pro:'#8b5cf6', ultra:'#f59e0b', mega:'#ef4444' };
      const statusColors = { active:'#10b981', pending:'#f59e0b', expired:'#64748b', cancelled:'#ef4444' };

      $wrap.innerHTML = `
        <div class="zsu-boost-table-scroll">
        <table class="zsu-studio-table">
          <thead><tr>
            <th>Short</th><th>Tier</th><th>Status</th><th>Started</th><th>Ends</th><th>Coins</th><th>Impressions</th>
          </tr></thead>
          <tbody>
            ${res.boosts.map((b) => {
              const tc  = tierColors[b.boost_type]  || '#64748b';
              const sc  = statusColors[b.status]    || '#64748b';
              const end = (b.ends_at || '').split(' ')[0];
              const start = (b.starts_at || '').split(' ')[0];
              return `<tr>
                <td style="font-weight:600;color:#fff;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${esc(b.short_title || 'Short #' + b.short_id)}</td>
                <td><span style="background:${tc}22;color:${tc};border:1px solid ${tc}44;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;">${esc(b.boost_type)}</span></td>
                <td><span style="background:${sc}22;color:${sc};border:1px solid ${sc}44;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;">${esc(b.status)}</span></td>
                <td style="color:#94a3b8;font-size:11px;">${start}</td>
                <td style="color:#94a3b8;font-size:11px;">${end}</td>
                <td style="color:#f59e0b;font-weight:700;">🪙 ${fnum(parseInt(b.coins_spent||0,10))}</td>
                <td style="color:#94a3b8;">${fnum(parseInt(b.impressions||0,10))}</td>
              </tr>`;
            }).join('')}
          </tbody>
        </table>
        </div>`;
    }).catch(() => {
      $wrap.innerHTML = '<p style="color:#f87171;font-size:13px;padding:10px 0;">Failed to load boosts. Please refresh.</p>';
    });
  }

  /* ═══════════════════════════════════════════════════════
     BUY COINS — WhatsApp CTA
     ═══════════════════════════════════════════════════════ */
  function initBuyCoins() {
    const rates = S.coin_rates || { per_1: 1, per_100: 90, per_500: 400, per_1000: 750 };

    let selectedCoins = 0;
    let selectedPrice = 0;

    function setSelected(coins, price) {
      selectedCoins = coins;
      selectedPrice = price;
      document.querySelectorAll('.zsu-bcb-pkg:not(.zsu-bcb-pkg--custom)').forEach(b => b.classList.remove('active'));
      const matchPkg = document.querySelector(`.zsu-bcb-pkg[data-coins="${coins}"]`);
      if (matchPkg) matchPkg.classList.add('active');

      const info = document.getElementById('zsuBcbSelectedInfo');
      if (info) info.style.display = 'flex';
      const sc = document.getElementById('zsuBcbSelectedCoins');
      const sp = document.getElementById('zsuBcbSelectedPrice');
      if (sc) sc.textContent = coins;
      if (sp) sp.textContent = `₹${parseFloat(price).toFixed(2)}`;

      const btn = document.getElementById('zsuBuyCoinsBtn');
      if (btn) btn.disabled = false;
    }

    document.querySelectorAll('.zsu-bcb-pkg:not(.zsu-bcb-pkg--custom)').forEach(btn => {
      btn.addEventListener('click', () => {
        const coins = parseInt(btn.dataset.coins, 10);
        const price = parseFloat(btn.dataset.price);
        const ci = document.getElementById('zsuCustomCoins');
        if (ci) ci.value = '';
        const cp = document.getElementById('zsuCustomPrice');
        if (cp) cp.textContent = '₹—';
        setSelected(coins, price);
      });
    });

    const customInput = document.getElementById('zsuCustomCoins');
    if (customInput) {
      customInput.addEventListener('input', () => {
        const coins = parseInt(customInput.value, 10);
        const cp = document.getElementById('zsuCustomPrice');
        if (!coins || coins < 1) { if (cp) cp.textContent = '₹—'; return; }
        const price = parseFloat((coins * rates.per_1).toFixed(2));
        if (cp) cp.textContent = `₹${price}`;
        document.querySelectorAll('.zsu-bcb-pkg:not(.zsu-bcb-pkg--custom)').forEach(b => b.classList.remove('active'));
        setSelected(coins, price);
      });
    }

    const buyBtn = document.getElementById('zsuBuyCoinsBtn');
    if (buyBtn) {
      buyBtn.addEventListener('click', () => {
        if (!selectedCoins) { studioToast('Please select a package first', 'error'); return; }
        const waNumber = (S.whatsapp_number || '').replace(/\D/g, '').trim();
        if (!waNumber) { studioToast('WhatsApp not configured. Contact admin.', 'error'); return; }
        const vendorName = S.vendor_name || 'Vendor';
        const msg = encodeURIComponent(
          `Hi! I want to buy coins on Clothes 42.\n\n` +
          `👤 Vendor: ${vendorName}\n` +
          `🪙 Coins: ${selectedCoins}\n` +
          `💰 Amount: ₹${parseFloat(selectedPrice).toFixed(2)}\n\n` +
          `Please process my coin purchase. Thank you! 🙏`
        );
        window.open(`https://wa.me/${waNumber}?text=${msg}`, '_blank');
      });
    }
  }

  function loadCoinsHistory() {
    const $wrap = q('#zsuCoinsHistoryWrap');
    if (!$wrap) return;
    $wrap.innerHTML = '<div class="zsu-studio-spinner-wrap"><div class="zsu-studio-spinner"></div></div>';

    ajax('zsu_coins_history').then((res) => {
      if (!res.success || !res.history.length) {
        $wrap.innerHTML = '<p style="color:#64748b;font-size:13px;padding:10px 0;">No transactions yet.</p>';
        return;
      }

      $wrap.innerHTML = `
        <table class="zsu-studio-table">
          <thead><tr>
            <th>Date</th><th>Amount</th><th>Balance</th><th>Reason</th>
          </tr></thead>
          <tbody>
            ${res.history.map((row) => {
              const d = parseInt(row.delta, 10);
              const cls = d > 0 ? 'color:#10b981' : 'color:#f87171';
              return `<tr>
                <td style="color:#64748b;font-size:11px;">${(row.created_at||'').split(' ')[0]}</td>
                <td style="${cls};font-weight:700;">${d > 0 ? '+' : ''}${fnum(d)} 🪙</td>
                <td style="font-weight:600;color:#fff;">${fnum(row.balance)} 🪙</td>
                <td style="color:#94a3b8;font-size:11px;">${esc(row.reason||'—')}</td>
              </tr>`;
            }).join('')}
          </tbody>
        </table>`;
    });
  }

  /* ═══════════════════════════════════════════════════════
     UTILITIES
     ═══════════════════════════════════════════════════════ */
  function ajax(action, data = {}) {
    const body = new URLSearchParams({ action, nonce: S.nonce, ...data });
    return fetch(S.ajax_url, { method:'POST', body })
      .then((r) => r.json())
      .catch(() => ({ success: false }));
  }

  function q(sel) { return document.querySelector(sel); }
  function qAll(sel) { return document.querySelectorAll(sel); }

  function on(sel, event, handler) {
    const el = typeof sel === 'string' ? q(sel) : sel;
    if (el) el.addEventListener(event, handler);
  }

  function setText(id, val) { const el = q('#' + id); if (el) el.textContent = val; }
  function showEl(el) { if (el) el.style.display = 'block'; }
  function hideEl(el) { if (el) el.style.display = 'none'; }

  function showErr($el, msg) {
    if (!$el) return;
    $el.textContent = msg;
    $el.style.display = 'block';
  }

  function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
  }

  function fnum(n) {
    n = parseInt(n,10) || 0;
    if (n >= 1e6) return (n/1e6).toFixed(1) + 'M';
    if (n >= 1e3) return (n/1e3).toFixed(1) + 'K';
    return n.toString();
  }

  function studioToast(msg, type = 'info', dur = 3200) {
    const $t = q('#zsuStudioToast');
    if (!$t) return;
    $t.textContent  = msg;
    $t.className    = `zsu-studio-toast ${type}`;
    $t.style.display = 'block';
    clearTimeout(window._zsToastTimer);
    window._zsToastTimer = setTimeout(() => {
      $t.style.transition = 'opacity 0.4s';
      $t.style.opacity    = '0';
      setTimeout(() => { $t.style.display = 'none'; $t.style.opacity = '1'; }, 420);
    }, dur);
  }


  /* ── Init — runs after ALL functions defined ─────────── */
  function _zsuStudioInit() {
    if (typeof ZSU_STUDIO === 'undefined') return; // guard: var not injected yet
    initTabs();
    initUploadWizard();
    initMyShorts();
    initBoostTab();
    initBuyCoins();
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', _zsuStudioInit);
  } else {
    _zsuStudioInit();
  }

}());