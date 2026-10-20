/* ═══════════════════════════════════════════════════════════════════
   ZSU ADMIN JS  v11  — Real file at assets/js/zsu-admin.js
   Enqueued with wp_enqueue_script('zsu-admin', ZSU_ASSETS_URL.'js/zsu-admin.js')
   ═══════════════════════════════════════════════════════════════════ */

/* global ZSU_ADMIN, Chart, wp */
(function ($) {
  'use strict';

  /* ── Guard: ZSU_ADMIN must exist ── */
  if (typeof ZSU_ADMIN === 'undefined') return;

  const { ajax_url, nonce } = ZSU_ADMIN;

  // ── Toast utility ─────────────────────────────────────────────
  function toast(msg, type = 'success', duration = 3200) {
    const $t = $('#zsuToast');
    $t.text(msg).attr('class', `zsu-toast ${type}`).show();
    clearTimeout(window._zsuToastTimer);
    window._zsuToastTimer = setTimeout(() => $t.fadeOut(300), duration);
  }

  // ── AJAX helper ───────────────────────────────────────────────
  function zsuAjax(action, data = {}, method = 'POST') {
    return $.ajax({
      url: ajax_url,
      type: method,
      data: { action, nonce, ...data },
    });
  }

  // ── Build badge HTML ──────────────────────────────────────────
  function badge(text, cls) {
    return `<span class="zsu-badge zsu-badge-${cls}">${text}</span>`;
  }

  // ── Format number ─────────────────────────────────────────────
  function fnum(n) {
    n = parseInt(n, 10) || 0;
    if (n >= 1e6) return (n / 1e6).toFixed(1) + 'M';
    if (n >= 1e3) return (n / 1e3).toFixed(1) + 'K';
    return n.toString();
  }

  // ═══════════════════════════════════════════════════════════════
  //  DASHBOARD
  // ═══════════════════════════════════════════════════════════════
  let dashChart = null;

  function initDashboard() {
    if (!$('#zsu-dashboard').length) return;
    loadDashChart(30);
    loadTopShorts(30);
    loadPendingPreview();

    // Range tabs
    $(document).on('click', '#zsuChartRangeTabs .zsu-tab', function () {
      $('#zsuChartRangeTabs .zsu-tab').removeClass('active');
      $(this).addClass('active');
      const days = $(this).data('days');
      loadDashChart(days);
      loadTopShorts(days);
    });

    // Export CSV
    $(document).on('click', '#zsuExportCsvBtn', function () {
      const days = parseInt($('#zsuChartRangeTabs .zsu-tab.active').data('days') || 30, 10);
      exportCsv(days);
    });
  }

  function loadDashChart(days) {
    const $loader = $('#zsuDashChartLoader');
    $loader.show();
    zsuAjax('zsu_admin_get_stats', { days }).done((res) => {
      $loader.hide();
      if (!res.success) return;
      const chart  = res.chart || [];
      const labels = chart.map((r) => r.day);
      const views  = chart.map((r) => parseInt(r.views, 10));
      const likes  = chart.map((r) => parseInt(r.likes, 10));
      const shares = chart.map((r) => parseInt(r.shares, 10));
      const clicks = chart.map((r) => parseInt(r.clicks, 10));

      const ctx = document.getElementById('zsuDashChart');
      if (!ctx) return;

      if (dashChart) dashChart.destroy();

      dashChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: '👁 Views',
              data: views,
              borderColor: '#7c3aed',
              backgroundColor: 'rgba(124,58,237,0.08)',
              tension: 0.4,
              fill: true,
              pointRadius: 3,
              pointHoverRadius: 6,
            },
            {
              label: '❤️ Likes',
              data: likes,
              borderColor: '#f43f5e',
              backgroundColor: 'rgba(244,63,94,0.06)',
              tension: 0.4,
              fill: true,
              pointRadius: 3,
              pointHoverRadius: 6,
            },
            {
              label: '🔗 Shares',
              data: shares,
              borderColor: '#10b981',
              backgroundColor: 'rgba(16,185,129,0.06)',
              tension: 0.4,
              fill: true,
              pointRadius: 3,
              pointHoverRadius: 6,
            },
            {
              label: '🛒 Clicks',
              data: clicks,
              borderColor: '#f59e0b',
              backgroundColor: 'rgba(245,158,11,0.06)',
              tension: 0.4,
              fill: true,
              pointRadius: 3,
              pointHoverRadius: 6,
            },
          ],
        },
        options: {
          responsive: true,
          interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: {
              labels: { color: '#94a3b8', font: { size: 12 }, boxWidth: 14 },
            },
            tooltip: {
              backgroundColor: '#1a1d2e',
              titleColor: '#fff',
              bodyColor: '#94a3b8',
              borderColor: '#252840',
              borderWidth: 1,
            },
          },
          scales: {
            x: {
              grid: { color: 'rgba(255,255,255,0.04)' },
              ticks: { color: '#64748b', maxRotation: 30 },
            },
            y: {
              grid: { color: 'rgba(255,255,255,0.04)' },
              ticks: { color: '#64748b', callback: (v) => fnum(v) },
            },
          },
        },
      });

      // Populate top shorts table after chart loads
      renderTopShortsTable(res.top_shorts || []);
    });
  }

  function loadTopShorts(days) {
    zsuAjax('zsu_admin_get_stats', { days }).done((res) => {
      if (res.success) renderTopShortsTable(res.top_shorts || []);
    });
  }

  function renderTopShortsTable(shorts) {
    if (!shorts.length) {
      $('#zsuTopShortsWrap').html('<p style="padding:20px;color:#64748b;">No data yet.</p>');
      return;
    }

    let html = `<table class="zsu-table">
      <thead><tr>
        <th>#</th>
        <th>Short</th>
        <th>👁 Views</th>
        <th>❤️ Likes</th>
        <th>🔗 Shares</th>
        <th>Actions</th>
      </tr></thead><tbody>`;

    shorts.forEach((s, i) => {
      const editUrl = `${ajaxurl.replace('admin-ajax.php', '')}post.php?post=${s.ID}&action=edit`;
      html += `<tr>
        <td style="color:#64748b;font-weight:600;">${i + 1}</td>
        <td><a href="${editUrl}" style="color:#e2e8f0;font-weight:600;text-decoration:none;">${escHtml(s.post_title)}</a></td>
        <td>${fnum(s.views)}</td>
        <td>${fnum(s.likes)}</td>
        <td>${fnum(s.shares)}</td>
        <td><a href="${editUrl}" class="zsu-btn zsu-btn-sm zsu-btn-ghost">✏️ Edit</a></td>
      </tr>`;
    });

    html += '</tbody></table>';
    $('#zsuTopShortsWrap').html(html);
  }

  function loadPendingPreview() {
    if (!$('#zsuPendingPreview').length) return;
    zsuAjax('zsu_admin_get_pending').done((res) => {
      if (!res.success || !res.pending.length) {
        $('#zsuPendingPreview').html('<p style="color:#64748b;padding:10px 0;">Queue is empty.</p>');
        return;
      }
      let html = '<div style="display:flex;gap:12px;flex-wrap:wrap;">';
      res.pending.slice(0, 4).forEach((p) => {
        html += `<div style="width:140px;">
          <img src="${escAttr(p.thumb)}" style="width:100%;aspect-ratio:9/16;object-fit:cover;border-radius:8px;background:#252840;" loading="lazy">
          <div style="font-size:11px;color:#e2e8f0;margin-top:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escHtml(p.title)}</div>
          <div style="display:flex;gap:5px;margin-top:6px;">
            <button class="zsu-btn zsu-btn-sm zsu-btn-success" onclick="zsuApprove(${p.id})">✅</button>
            <button class="zsu-btn zsu-btn-sm zsu-btn-danger"  onclick="zsuReject(${p.id})">❌</button>
          </div>
        </div>`;
      });
      html += '</div>';
      if (res.total > 4) {
        html += `<p style="font-size:12px;color:#64748b;margin-top:10px;">+ ${res.total - 4} more waiting</p>`;
      }
      $('#zsuPendingPreview').html(html);
    });
  }

  function exportCsv(days) {
    zsuAjax('zsu_admin_export_analytics', { days }).done((res) => {
      if (!res.success || !res.csv) { toast('No data to export', 'error'); return; }
      const blob = new Blob([res.csv], { type: 'text/csv;charset=utf-8;' });
      const url  = URL.createObjectURL(blob);
      const a    = document.createElement('a');
      a.href = url; a.download = res.filename || 'zsu-analytics.csv';
      a.click(); URL.revokeObjectURL(url);
      toast('CSV exported!', 'success');
    });
  }

  // ═══════════════════════════════════════════════════════════════
  //  ALL SHORTS MANAGER
  // ═══════════════════════════════════════════════════════════════
  let currentPage = 1;
  let selectedIds = new Set();

  function initShortsManager() {
    if (!$('#zsu-shorts-manager').length) return;
    loadShortsGrid(1);

    // Search (debounce)
    let sTimer;
    $(document).on('input', '#zsuShortsSearch', function () {
      clearTimeout(sTimer);
      sTimer = setTimeout(() => { currentPage = 1; loadShortsGrid(1); }, 400);
    });

    // Filters
    $(document).on('change', '#zsuStatusFilter, #zsuCatFilter, #zsuSortFilter', () => {
      currentPage = 1;
      loadShortsGrid(1);
    });

    // Refresh
    $(document).on('click', '#zsuShortsRefresh', () => loadShortsGrid(currentPage));

    // Pagination
    $(document).on('click', '.zsu-page-btn:not(.disabled)', function () {
      if ($(this).hasClass('active')) return;
      currentPage = parseInt($(this).data('page'), 10);
      loadShortsGrid(currentPage);
    });

    // Bulk actions
    $(document).on('change', '.zsu-admin-short-select', function () {
      const id = parseInt($(this).val(), 10);
      if (this.checked) { selectedIds.add(id); }
      else { selectedIds.delete(id); }
      updateBulkBar();
    });

    $(document).on('click', '#zsuBulkApprove', bulkApprove);
    $(document).on('click', '#zsuBulkDelete',  bulkDelete);
    $(document).on('click', '#zsuBulkClear',   () => { selectedIds.clear(); updateBulkBar(); });

    // Card actions
    $(document).on('click', '.zsu-approve-btn', function () {
      const id = $(this).data('id');
      zsuApprove(id, () => loadShortsGrid(currentPage));
    });
    $(document).on('click', '.zsu-reject-btn', function () {
      const id = $(this).data('id');
      zsuReject(id, () => loadShortsGrid(currentPage));
    });
    $(document).on('click', '.zsu-delete-short-btn', function () {
      const id = $(this).data('id');
      if (!confirm('Delete this short permanently?')) return;
      zsuDeleteShort(id, () => loadShortsGrid(currentPage));
    });
  }

  function loadShortsGrid(page) {
    const search  = $('#zsuShortsSearch').val() || '';
    const status  = $('#zsuStatusFilter').val() || '';
    const cat     = $('#zsuCatFilter').val() || '';
    const sort    = $('#zsuSortFilter').val() || 'date-desc';
    const $grid   = $('#zsuShortsAdminGrid');
    const $pager  = $('#zsuShortsPagination');

    $grid.html('<div class="zsu-spinner-center"><div class="zsu-spinner"></div></div>');

    // Build WP_Query args — we piggyback on get_shorts with extras
    const [orderby, order] = sort.split('-');

    $.ajax({
      url: ajax_url,
      type: 'POST',
      data: {
        action: 'zsu_get_shorts',
        nonce,
        page,
        per_page: 24,
        search,
        filter: orderby === 'trending' ? 'trending' : (orderby === 'views' ? 'popular' : 'latest'),
        cat,
        status_filter: status,
      },
    }).done((res) => {
      if (!res.success) { $grid.html('<p style="padding:20px;color:#ef4444;">Failed to load.</p>'); return; }

      const shorts = res.shorts || [];
      if (!shorts.length) {
        $grid.html('<div class="zsu-empty-state"><div class="zsu-empty-icon">🎬</div><div class="zsu-empty-title">No shorts found</div><div class="zsu-empty-desc">Try adjusting your filters.</div></div>');
        $pager.empty();
        return;
      }

      let html = '';
      shorts.forEach((s) => {
        const isSelected = selectedIds.has(s.id);
        const statusClass = s.status === 'publish' ? 'publish' : (s.status === 'pending' ? 'pending' : 'draft');
        const statusLabel = s.status === 'publish' ? '✅' : (s.status === 'pending' ? '⏳' : '📝');
        const editUrl = `${ajax_url.replace('admin-ajax.php', '')}post.php?post=${s.id}&action=edit`;

        html += `<div class="zsu-admin-short-card" data-id="${s.id}">
          <div class="zsu-admin-short-thumb-wrap">
            <img src="${escAttr(s.thumb_url)}" class="zsu-admin-short-thumb" loading="lazy" alt="${escAttr(s.title)}">
            <input type="checkbox" class="zsu-admin-short-select" value="${s.id}" ${isSelected ? 'checked' : ''}>
            <div class="zsu-admin-short-status">${badge(statusLabel, statusClass)}</div>
            ${s.is_featured ? '<div class="zsu-admin-short-featured">⭐ Featured</div>' : ''}
            ${s.is_boosted  ? '<div class="zsu-admin-short-boosted">🚀 Boosted</div>'  : ''}
            <div class="zsu-admin-short-overlay">
              ${s.status === 'pending' ? `<button class="zsu-btn zsu-btn-sm zsu-btn-success zsu-approve-btn" data-id="${s.id}">✅</button>` : ''}
              <a href="${editUrl}" class="zsu-btn zsu-btn-sm zsu-btn-ghost">✏️</a>
              <button class="zsu-btn zsu-btn-sm zsu-btn-danger zsu-delete-short-btn" data-id="${s.id}">🗑</button>
            </div>
          </div>
          <div class="zsu-admin-short-body">
            <div class="zsu-admin-short-title" title="${escAttr(s.title)}">${escHtml(s.title)}</div>
            <div class="zsu-admin-short-stats">
              <span class="zsu-admin-short-stat">👁 ${s.views}</span>
              <span class="zsu-admin-short-stat">❤️ ${s.likes}</span>
              <span class="zsu-admin-short-stat">🔗 ${s.shares}</span>
            </div>
          </div>
        </div>`;
      });

      $grid.html(html);
      renderPagination($pager, page, parseInt(res.pages, 10));
    });
  }

  function updateBulkBar() {
    const count = selectedIds.size;
    if (count > 0) {
      $('#zsuBulkBar').show();
      $('#zsuBulkCount').text(`${count} selected`);
    } else {
      $('#zsuBulkBar').hide();
    }
  }

  function bulkApprove() {
    if (!selectedIds.size) return;
    const ids = [...selectedIds];
    let done = 0;
    ids.forEach((id) => {
      zsuAjax('zsu_admin_approve_short', { post_id: id }).done(() => {
        done++;
        if (done === ids.length) {
          selectedIds.clear();
          updateBulkBar();
          loadShortsGrid(currentPage);
          toast(`${ids.length} shorts approved!`, 'success');
        }
      });
    });
  }

  function bulkDelete() {
    if (!selectedIds.size) return;
    if (!confirm(`Delete ${selectedIds.size} shorts permanently?`)) return;
    const ids = [...selectedIds];
    let done = 0;
    ids.forEach((id) => {
      zsuAjax('zsu_admin_delete_short', { post_id: id }).done(() => {
        done++;
        if (done === ids.length) {
          selectedIds.clear();
          updateBulkBar();
          loadShortsGrid(currentPage);
          toast(`${ids.length} shorts deleted.`, 'warning');
        }
      });
    });
  }

  // ═══════════════════════════════════════════════════════════════
  //  PENDING PAGE
  // ═══════════════════════════════════════════════════════════════
  function initPendingPage() {
    if (!$('#zsu-pending-page').length) return;
    loadPendingFull();
  }

  function loadPendingFull() {
    zsuAjax('zsu_admin_get_pending').done((res) => {
      const $grid = $('#zsuPendingGrid');
      const $empty = $('#zsuPendingEmpty');

      if (!res.success || !res.pending.length) {
        $grid.hide();
        $empty.show();
        return;
      }

      $empty.hide();
      let html = '';
      res.pending.forEach((p) => {
        html += `<div class="zsu-pending-card" id="zsuPending${p.id}">
          <div class="zsu-pending-thumb-wrap">
            <img src="${escAttr(p.thumb)}" class="zsu-pending-thumb" loading="lazy">
            <div style="position:absolute;top:8px;left:8px;">${badge('⏳ Pending', 'pending')}</div>
          </div>
          <div class="zsu-pending-body">
            <div class="zsu-pending-title">${escHtml(p.title)}</div>
            <div class="zsu-pending-meta">
              🏪 Vendor #${p.vendor_id} &nbsp;·&nbsp; 📅 ${escHtml(p.date)}
            </div>
            <div class="zsu-pending-actions">
              <button class="zsu-btn zsu-btn-success zsu-approve-btn" data-id="${p.id}" style="flex:1;">✅ Approve</button>
              <button class="zsu-btn zsu-btn-danger  zsu-reject-btn"  data-id="${p.id}" style="flex:1;">❌ Reject</button>
              <a href="${ajax_url.replace('admin-ajax.php','')}post.php?post=${p.id}&action=edit" class="zsu-btn zsu-btn-ghost" style="flex:0 0 auto;">✏️</a>
            </div>
          </div>
        </div>`;
      });

      $grid.html(html).show();
    });

    // Wire up approve/reject on pending page
    $(document).on('click', '#zsu-pending-page .zsu-approve-btn', function () {
      const id = $(this).data('id');
      zsuApprove(id, () => {
        $(`#zsuPending${id}`).remove();
        if (!$('.zsu-pending-card').length) {
          $('#zsuPendingGrid').hide();
          $('#zsuPendingEmpty').show();
        }
      });
    });

    $(document).on('click', '#zsu-pending-page .zsu-reject-btn', function () {
      const id = $(this).data('id');
      zsuReject(id, () => {
        $(`#zsuPending${id}`).remove();
        if (!$('.zsu-pending-card').length) {
          $('#zsuPendingGrid').hide();
          $('#zsuPendingEmpty').show();
        }
      });
    });
  }

  // ═══════════════════════════════════════════════════════════════
  //  BOOSTS PAGE
  // ═══════════════════════════════════════════════════════════════
  function initBoostsPage() {
    if (!$('#zsu-boosts-manager').length) return;
    loadBoostsTable();

    $(document).on('click', '#zsuBoostsRefresh', loadBoostsTable);

    $(document).on('click', '.zsu-boost-action-btn', function () {
      const boostId = $(this).data('id');
      const action  = $(this).data('action');
      zsuAjax('zsu_admin_boost_action', { boost_id: boostId, boost_action: action }).done((res) => {
        if (res.success) { toast(`Boost ${action}d successfully!`, 'success'); loadBoostsTable(); }
        else { toast('Action failed', 'error'); }
      });
    });
  }

  function loadBoostsTable() {
    const statusF = $('#zsuBoostStatusFilter').val() || '';
    const typeF   = $('#zsuBoostTypeFilter').val() || '';

    zsuAjax('zsu_admin_get_boosts').done((res) => {
      if (!res.success) { $('#zsuBoostsTableWrap').html('<p style="padding:20px;color:#ef4444;">Failed to load.</p>'); return; }

      let boosts = res.boosts || [];
      if (statusF) boosts = boosts.filter((b) => b.status === statusF);
      if (typeF)   boosts = boosts.filter((b) => b.boost_type === typeF);

      if (!boosts.length) {
        $('#zsuBoostsTableWrap').html('<div class="zsu-empty-state"><div class="zsu-empty-icon">🚀</div><div class="zsu-empty-title">No boosts yet</div><div class="zsu-empty-desc">Vendors can purchase boosts from their studio.</div></div>');
        return;
      }

      let html = `<table class="zsu-table">
        <thead><tr>
          <th>ID</th><th>Short</th><th>Vendor</th>
          <th>Type</th><th>Coins</th><th>Status</th>
          <th>Starts</th><th>Ends</th><th>Impressions</th><th>Actions</th>
        </tr></thead><tbody>`;

      boosts.forEach((b) => {
        const statusBadge = badge(b.status, b.status);
        const typeBadge   = badge('🚀 ' + b.boost_type, b.boost_type);
        const actions = [];
        if (b.status === 'pending')  actions.push(`<button class="zsu-btn zsu-btn-sm zsu-btn-success zsu-boost-action-btn" data-id="${b.id}" data-action="approve">✅ Approve</button>`);
        if (b.status === 'active')   actions.push(`<button class="zsu-btn zsu-btn-sm zsu-btn-warning  zsu-boost-action-btn" data-id="${b.id}" data-action="expire">⏹ Stop</button>`);
        if (b.status !== 'cancelled') actions.push(`<button class="zsu-btn zsu-btn-sm zsu-btn-danger  zsu-boost-action-btn" data-id="${b.id}" data-action="cancel">✕</button>`);

        html += `<tr>
          <td style="color:#64748b;">#${b.id}</td>
          <td><strong style="color:#e2e8f0;">${escHtml(b.post_title || 'ID:' + b.short_id)}</strong></td>
          <td>#${b.vendor_id}</td>
          <td>${typeBadge}</td>
          <td><span style="color:#f59e0b;font-weight:700;">🪙 ${fnum(b.coins_spent)}</span></td>
          <td>${statusBadge}</td>
          <td style="color:#64748b;font-size:12px;">${b.starts_at ? b.starts_at.split(' ')[0] : '—'}</td>
          <td style="color:#64748b;font-size:12px;">${b.ends_at ? b.ends_at.split(' ')[0] : '—'}</td>
          <td>${fnum(b.impressions || 0)}</td>
          <td><div style="display:flex;gap:4px;">${actions.join('')}</div></td>
        </tr>`;
      });

      html += '</tbody></table>';
      $('#zsuBoostsTableWrap').html(html);
    });
  }

  // ═══════════════════════════════════════════════════════════════
  //  COINS PAGE
  // ═══════════════════════════════════════════════════════════════
  function initCoinsPage() {
    if (!$('#zsu-coins-page').length) return;
    loadCoinsLog();
  }

  function loadCoinsLog() {
    zsuAjax('zsu_admin_get_coins_log').done((res) => {
      if (!res.success) return;
      const log = res.log || [];

      if (!log.length) {
        $('#zsuCoinsLogWrap').html('<p style="padding:20px;color:#64748b;">No coin transactions yet.</p>');
        return;
      }

      let html = `<table class="zsu-table">
        <thead><tr>
          <th>ID</th><th>User</th><th>Delta</th><th>Balance</th>
          <th>Reason</th><th>Ref</th><th>Date</th>
        </tr></thead><tbody>`;

      log.forEach((row) => {
        const delta = parseInt(row.delta, 10);
        const cls   = delta > 0 ? 'positive' : 'negative';
        const sign  = delta > 0 ? '+' : '';

        html += `<tr>
          <td style="color:#64748b;">#${row.id}</td>
          <td><span style="color:#e2e8f0;">${escHtml(row.display_name || 'User #' + row.user_id)}</span></td>
          <td><span class="zsu-coin-${cls}">${sign}${fnum(Math.abs(delta))} 🪙</span></td>
          <td style="color:#e2e8f0;">${fnum(row.balance)} 🪙</td>
          <td style="font-size:12px;color:#94a3b8;">${escHtml(row.reason || '—')}</td>
          <td style="font-size:12px;color:#64748b;">${row.ref_id > 0 ? '#' + row.ref_id : '—'}</td>
          <td style="font-size:12px;color:#64748b;">${(row.created_at || '').split(' ')[0]}</td>
        </tr>`;
      });

      html += '</tbody></table>';
      $('#zsuCoinsLogWrap').html(html);
    });
  }

  // ═══════════════════════════════════════════════════════════════
  //  SHARED ACTIONS (used across pages)
  // ═══════════════════════════════════════════════════════════════
  window.zsuApprove = function (id, callback) {
    zsuAjax('zsu_admin_approve_short', { post_id: id }).done((res) => {
      if (res.success) { toast('✅ Short approved!', 'success'); if (callback) callback(); }
      else { toast('Failed to approve', 'error'); }
    });
  };

  window.zsuReject = function (id, callback) {
    if (!confirm('Reject and notify vendor?')) return;
    zsuAjax('zsu_admin_reject_short', { post_id: id }).done((res) => {
      if (res.success) { toast('❌ Short rejected.', 'warning'); if (callback) callback(); }
      else { toast('Failed to reject', 'error'); }
    });
  };

  function zsuDeleteShort(id, callback) {
    zsuAjax('zsu_admin_delete_short', { post_id: id }).done((res) => {
      if (res.success) { toast('🗑 Short deleted.', 'warning'); if (callback) callback(); }
      else { toast('Failed to delete', 'error'); }
    });
  }

  // ═══════════════════════════════════════════════════════════════
  //  PAGINATION HELPER
  // ═══════════════════════════════════════════════════════════════
  function renderPagination($container, current, total) {
    if (total <= 1) { $container.empty(); return; }

    let html = '';
    const prev = current - 1;
    const next = current + 1;

    html += `<button class="zsu-page-btn${prev < 1 ? ' disabled' : ''}" data-page="${prev}">‹</button>`;

    // Show at most 7 page buttons
    let start = Math.max(1, current - 3);
    let end   = Math.min(total, current + 3);

    if (current <= 4) { start = 1; end = Math.min(7, total); }
    if (current >= total - 3) { start = Math.max(1, total - 6); end = total; }

    if (start > 1) {
      html += `<button class="zsu-page-btn" data-page="1">1</button>`;
      if (start > 2) html += `<span style="color:#64748b;padding:0 6px;">…</span>`;
    }

    for (let i = start; i <= end; i++) {
      html += `<button class="zsu-page-btn${i === current ? ' active' : ''}" data-page="${i}">${i}</button>`;
    }

    if (end < total) {
      if (end < total - 1) html += `<span style="color:#64748b;padding:0 6px;">…</span>`;
      html += `<button class="zsu-page-btn" data-page="${total}">${total}</button>`;
    }

    html += `<button class="zsu-page-btn${next > total ? ' disabled' : ''}" data-page="${next}">›</button>`;

    $container.html(html);
  }

  // ═══════════════════════════════════════════════════════════════
  //  SECURITY HELPERS
  // ═══════════════════════════════════════════════════════════════
  function escHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function escAttr(str) { return escHtml(str); }

  // ═══════════════════════════════════════════════════════════════
  //  INIT
  // ═══════════════════════════════════════════════════════════════
  // (init moved to end of file)
// ═══════════════════════════════════════════════════════════════
  //  ANALYTICS PAGE
  // ═══════════════════════════════════════════════════════════════
  let analyticsLineChart    = null;
  let analyticsDeviceChart  = null;
  let analyticsBrowserChart = null;
  let analyticsEngageChart  = null;

  const DONUT_COLORS = ['#7c3aed','#f59e0b','#10b981','#06b6d4','#f43f5e','#8b5cf6','#ec4899'];

  function initAnalyticsPage() {
    if (!$('#zsu-analytics-page').length) return;
    loadAnalytics(30);

    $(document).on('click', '#zsuAnalyticsRefresh', () => {
      loadAnalytics(parseInt($('#zsuAnalyticsDays').val(), 10));
    });
    $(document).on('change', '#zsuAnalyticsDays', () => {
      loadAnalytics(parseInt($('#zsuAnalyticsDays').val(), 10));
    });
    $(document).on('click', '#zsuAnalyticsExport', () => {
      exportCsv(parseInt($('#zsuAnalyticsDays').val(), 10));
    });
  }

  function loadAnalytics(days) {
    // Show spinners
    $('#zsuAnalyticsCards').html('<div class="zsu-spinner-center" style="grid-column:1/-1;"><div class="zsu-spinner"></div></div>');
    $('#zsuAnalyticsTopTable').html('<div class="zsu-spinner-center"><div class="zsu-spinner"></div></div>');
    $('#zsuTopVendorsWrap').html('<div class="zsu-spinner-center"><div class="zsu-spinner"></div></div>');

    zsuAjax('zsu_admin_get_stats', { days }).done((res) => {
      if (!res.success) return;
      renderAnalyticsSummaryCards(res, days);
      renderAnalyticsLineChart(res.chart || [], days);
      renderDonutChart('Device', res.device_split  || [], '#zsuDeviceChart',  '#zsuDeviceLegend');
      renderDonutChart('Browser',res.browser_split || [], '#zsuBrowserChart', '#zsuBrowserLegend');
      renderTopVendors(res.top_vendors || []);
      renderTopShortsTableFull(res.top_shorts || []);
      renderEngagementChart(res.top_shorts || []);
    });
  }

  function renderAnalyticsSummaryCards(res, days) {
    const ev = res.events || {};
    const cards = [
      ['👁', 'Total Views',    ev.views    || 0, '#7c3aed'],
      ['❤️', 'Total Likes',   ev.likes    || 0, '#f43f5e'],
      ['🔗', 'Total Shares',  ev.shares   || 0, '#10b981'],
      ['🛒', 'Product Clicks',ev.clicks   || 0, '#f59e0b'],
    ];
    let html = '';
    cards.forEach(([icon, label, val, color]) => {
      html += `<div class="zsu-stat-card" style="--card-color:${color}">
        <div class="zsu-stat-icon">${icon}</div>
        <div class="zsu-stat-body">
          <div class="zsu-stat-value">${fnum(val)}</div>
          <div class="zsu-stat-label">${label} (${days}d)</div>
        </div>
        <div class="zsu-stat-glow"></div>
      </div>`;
    });
    $('#zsuAnalyticsCards').html(html);
  }

  function renderAnalyticsLineChart(data, days) {
    const labels = data.map((r) => r.day);
    const views  = data.map((r) => parseInt(r.views,  10));
    const likes  = data.map((r) => parseInt(r.likes,  10));
    const shares = data.map((r) => parseInt(r.shares, 10));
    const clicks = data.map((r) => parseInt(r.clicks, 10));

    const ctx = document.getElementById('zsuAnalyticsLineChart');
    if (!ctx) return;
    if (analyticsLineChart) analyticsLineChart.destroy();

    analyticsLineChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [
          { label:'👁 Views',    data:views,  borderColor:'#7c3aed', backgroundColor:'rgba(124,58,237,0.07)',  tension:0.4, fill:true, pointRadius:2, pointHoverRadius:5 },
          { label:'❤️ Likes',   data:likes,  borderColor:'#f43f5e', backgroundColor:'rgba(244,63,94,0.05)',   tension:0.4, fill:true, pointRadius:2, pointHoverRadius:5 },
          { label:'🔗 Shares',  data:shares, borderColor:'#10b981', backgroundColor:'rgba(16,185,129,0.05)',  tension:0.4, fill:true, pointRadius:2, pointHoverRadius:5 },
          { label:'🛒 Clicks',  data:clicks, borderColor:'#f59e0b', backgroundColor:'rgba(245,158,11,0.05)',  tension:0.4, fill:true, pointRadius:2, pointHoverRadius:5 },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode:'index', intersect:false },
        plugins: {
          legend: { labels:{ color:'#94a3b8', font:{size:11}, boxWidth:12 } },
          tooltip: { backgroundColor:'#1a1d2e', titleColor:'#fff', bodyColor:'#94a3b8', borderColor:'#252840', borderWidth:1 },
        },
        scales: {
          x: { grid:{ color:'rgba(255,255,255,0.03)' }, ticks:{ color:'#475569', maxRotation:40, font:{size:10} } },
          y: { grid:{ color:'rgba(255,255,255,0.03)' }, ticks:{ color:'#475569', callback:(v)=>fnum(v) } },
        },
      },
    });
  }

  function renderDonutChart(titleKey, data, canvasId, legendId) {
    const ctx = document.querySelector(canvasId);
    if (!ctx) return;

    let chartRef = titleKey === 'Device' ? analyticsDeviceChart : analyticsBrowserChart;
    if (chartRef) chartRef.destroy();

    if (!data.length) {
      $(canvasId).closest('.zsu-section').find('.zsu-donut-legend').html('<p style="color:#475569;font-size:12px;text-align:center;">No data</p>');
      return;
    }

    const labels = data.map((r) => (r.device || r.browser || '?').toUpperCase());
    const values = data.map((r) => parseInt(r.cnt, 10));
    const total  = values.reduce((a, b) => a + b, 0);

    const chart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{
          data: values,
          backgroundColor: DONUT_COLORS.slice(0, labels.length),
          borderColor: '#1a1d2e',
          borderWidth: 2,
          hoverOffset: 6,
        }],
      },
      options: {
        cutout: '68%',
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor:'#1a1d2e',
            titleColor:'#fff',
            bodyColor:'#94a3b8',
            callbacks: { label: (ctx) => ` ${ctx.label}: ${fnum(ctx.parsed)} (${Math.round((ctx.parsed/total)*100)}%)` },
          },
        },
      },
    });

    if (titleKey === 'Device') analyticsDeviceChart = chart;
    else analyticsBrowserChart = chart;

    // Build legend
    let legendHtml = '';
    labels.forEach((l, i) => {
      const pct = total ? Math.round((values[i]/total)*100) : 0;
      legendHtml += `<div class="zsu-donut-legend-item">
        <div class="zsu-donut-legend-dot" style="background:${DONUT_COLORS[i % DONUT_COLORS.length]};"></div>
        ${escHtml(l)} ${pct}%
      </div>`;
    });
    $(legendId).html(legendHtml);
  }

  function renderTopVendors(vendors) {
    if (!vendors.length) {
      $('#zsuTopVendorsWrap').html('<p style="color:#475569;font-size:12px;padding:10px 0;">No vendor data yet.</p>');
      return;
    }
    let html = '';
    vendors.forEach((v, i) => {
      html += `<div class="zsu-vendor-rank-row">
        <div class="zsu-vendor-rank-num">${i + 1}</div>
        <div style="flex:1;">
          <div style="font-size:13px;font-weight:600;color:#e2e8f0;">Vendor #${escHtml(v.vendor_id)}</div>
          <div style="font-size:11px;color:#64748b;">${fnum(v.short_count)} shorts</div>
        </div>
      </div>`;
    });
    $('#zsuTopVendorsWrap').html(html);
  }

  function renderTopShortsTableFull(shorts) {
    if (!shorts.length) {
      $('#zsuAnalyticsTopTable').html('<p style="padding:20px;color:#64748b;">No data yet.</p>');
      return;
    }
    let html = `<table class="zsu-table">
      <thead><tr>
        <th>#</th><th>Short</th>
        <th>👁 Views</th><th>❤️ Likes</th>
        <th>🔗 Shares</th><th>🛒 Clicks</th>
        <th>Score</th>
      </tr></thead><tbody>`;

    shorts.forEach((s, i) => {
      const editUrl = `${ajax_url.replace('admin-ajax.php','')}post.php?post=${s.ID}&action=edit`;
      html += `<tr>
        <td style="color:#64748b;font-weight:700;">${i+1}</td>
        <td><a href="${editUrl}" style="color:#e2e8f0;font-weight:600;text-decoration:none;">${escHtml(s.post_title)}</a></td>
        <td>${fnum(s.views)}</td><td>${fnum(s.likes)}</td>
        <td>${fnum(s.shares)}</td><td>${fnum(s.clicks || 0)}</td>
        <td><span style="color:#8b5cf6;font-weight:700;">${parseFloat(s.score || 0).toFixed(0)}</span></td>
      </tr>`;
    });

    html += '</tbody></table>';
    $('#zsuAnalyticsTopTable').html(html);
  }

  function renderEngagementChart(shorts) {
    const ctx = document.getElementById('zsuEngagementChart');
    if (!ctx || !shorts.length) return;
    if (analyticsEngageChart) analyticsEngageChart.destroy();

    const labels = shorts.slice(0, 10).map((s) => s.post_title ? s.post_title.substring(0, 18) + (s.post_title.length > 18 ? '…' : '') : 'ID:' + s.ID);
    const views  = shorts.slice(0, 10).map((s) => parseInt(s.views,  10));
    const likes  = shorts.slice(0, 10).map((s) => parseInt(s.likes,  10));
    const shares = shorts.slice(0, 10).map((s) => parseInt(s.shares, 10));

    analyticsEngageChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [
          { label:'👁 Views',  data:views,  backgroundColor:'rgba(124,58,237,0.7)',  borderRadius:4 },
          { label:'❤️ Likes', data:likes,  backgroundColor:'rgba(244,63,94,0.7)',   borderRadius:4 },
          { label:'🔗 Shares',data:shares, backgroundColor:'rgba(16,185,129,0.7)',  borderRadius:4 },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { labels:{ color:'#94a3b8', font:{size:11}, boxWidth:12 } },
          tooltip: { backgroundColor:'#1a1d2e', titleColor:'#fff', bodyColor:'#94a3b8', borderColor:'#252840', borderWidth:1 },
        },
        scales: {
          x: { grid:{ color:'rgba(255,255,255,0.03)' }, ticks:{ color:'#475569', maxRotation:40, font:{size:10} } },
          y: { grid:{ color:'rgba(255,255,255,0.03)' }, ticks:{ color:'#475569', callback:(v)=>fnum(v) } },
          stacked: false,
        },
      },
    });
  }

  // ═══════════════════════════════════════════════════════════════
  //  PRESETS PAGE
  // ═══════════════════════════════════════════════════════════════
  function initPresetsPage() {
    if (!$('#zsu-presets-page').length) return;

    // Init WP color pickers
    if ($.fn.wpColorPicker) {
      $('.zsu-color-picker-input').wpColorPicker();
    }

    // Wheel preset selector
    $(document).on('click', '.zsu-wheel-preset-btn', function () {
      const wpreset = $(this).data('wheel-preset');
      zsuAjax('zsu_admin_save_option', { key: 'zsu_wheel_preset', value: wpreset }).done((res) => {
        if (res.success) {
          // Toggle active class on wheel preset buttons
          $('.zsu-wheel-preset-btn').removeClass('zwp-active');
          $(`.zsu-wheel-preset-btn[data-wheel-preset="${wpreset}"]`).addClass('zwp-active');
          toast('Wheel theme updated!', 'success');
        }
      });
    });

    // Apply preset
    $(document).on('click', '.zsu-apply-preset-btn:not([disabled])', function () {
      const preset = $(this).data('preset');
      const $btn   = $(this);
      $btn.text('Applying…').prop('disabled', true);

      zsuAjax('zsu_admin_apply_preset', { preset }).done((res) => {
        if (res.success) {
          toast('Preset applied!', 'success');
          // Reset all cards (new class names: zpc-active, zpc-active-badge)
          $('.zsu-preset-card').removeClass('zpc-active');
          $('.zsu-apply-preset-btn')
            .text('Apply')
            .prop('disabled', false)
            .removeClass('zsu-btn-ghost')
            .addClass('zsu-btn-primary');
          $('.zpc-active-badge').remove();
          // Activate selected card
          const $card = $(`.zsu-preset-card[data-preset="${preset}"]`);
          $card.addClass('zpc-active');
          $card.find('.zsu-apply-preset-btn')
            .text('✓ Applied')
            .prop('disabled', true)
            .removeClass('zsu-btn-primary')
            .addClass('zsu-btn-ghost');
          $card.find('.zpc-preview').append('<div class="zpc-active-badge">✓ Active</div>');
        } else {
          toast('Failed to apply preset', 'error');
          $btn.text('Apply').prop('disabled', false);
        }
      });
    });

    // Save color overrides
    $(document).on('click', '#zsuSaveColorOverrides', () => {
      const data = {};
      ['zsu_primary_color','zsu_accent_color','zsu_gradient_start','zsu_gradient_end'].forEach((k) => {
        data[k] = $(`#${k}`).val() || $(`#${k}`).closest('.wp-color-picker-container').find('.wp-color-picker').val();
      });
      zsuAjax('zsu_admin_save_settings', data).done((res) => {
        if (res.success) toast('🎨 Color overrides saved!', 'success');
        else toast('Save failed', 'error');
      });
    });
  }

  // ═══════════════════════════════════════════════════════════════
  //  SETTINGS PAGE
  // ═══════════════════════════════════════════════════════════════
  function initSettingsPage() {
    if (!$('#zsu-settings-page').length) return;

    // Init WP color pickers
    if ($.fn.wpColorPicker) {
      $('.zsu-color-picker-input').wpColorPicker();
    }

    // Tab switching
    $(document).on('click', '.zsu-settings-tab-btn', function () {
      const tab = $(this).data('tab');
      $('.zsu-settings-tab-btn').removeClass('active');
      $(this).addClass('active');
      $('.zsu-settings-panel').removeClass('active');
      $(`#zsuTab-${tab}`).addClass('active');
      // Re-init color pickers in the newly visible panel (they fail on hidden elements)
      if ($.fn.wpColorPicker) {
        $(`#zsuTab-${tab} .zsu-color-picker-input`).each(function () {
          if (!$(this).closest('.wp-picker-container').length) {
            $(this).wpColorPicker();
          }
        });
      }
    });

    // Toggle label update
    $(document).on('change', '.zsu-settings-toggle-input', function () {
      const label = $(this).closest('.zsu-toggle-wrap').find('.zsu-toggle-label');
      label.text(this.checked ? 'Enabled' : 'Disabled');
    });

    // Save all settings
    $(document).on('click', '#zsuSaveSettings', () => {
      const data = {};

      // Text/number inputs
      $('#zsu-settings-page .zsu-settings-input').each(function () {
        if (this.name) data[this.name] = $(this).val();
      });

      // Select inputs
      $('#zsu-settings-page .zsu-settings-select').each(function () {
        if (this.name) data[this.name] = $(this).val();
      });

      // Toggle checkboxes
      $('#zsu-settings-page .zsu-settings-toggle-input').each(function () {
        if (this.name) data[this.name] = this.checked ? '1' : '0';
      });

      // Color pickers
      $('#zsu-settings-page .zsu-color-picker-input').each(function () {
        const $wrap = $(this).closest('.wp-picker-container');
        const val   = $wrap.length ? $wrap.find('.wp-color-picker').val() : $(this).val();
        if (this.id) data[this.id] = val;
      });

      const $btn = $('#zsuSaveSettings');
      $btn.text('⏳ Saving…').prop('disabled', true);

      zsuAjax('zsu_admin_save_settings', data).done((res) => {
        $btn.text('💾 Save All Settings').prop('disabled', false);
        if (res.success) {
          toast(`✅ ${res.message || 'Settings saved!'}`, 'success');
          $('#zsuSettingsSavedMsg').show().delay(3000).fadeOut();
        } else {
          toast('Failed to save settings', 'error');
        }
      });
    });

    // Danger zone buttons
    $(document).on('click', '#zsuRecreateTables', () => {
      if (!confirm('Recreate database tables? Existing data is preserved.')) return;
      zsuAjax('zsu_admin_save_settings', { _recreate_tables: 1 }).always(() => toast('DB tables checked/recreated.', 'info'));
    });

    $(document).on('click', '#zsuRegenerateAssets', () => {
      zsuAjax('zsu_admin_save_settings', { _regenerate_assets: 1 }).always(() => toast('⚡ Assets regenerated.', 'info'));
    });

    $(document).on('click', '#zsuFlushRewrites', () => {
      zsuAjax('zsu_admin_save_settings', { _flush_rewrites: 1 }).always(() => toast('🔀 Rewrite rules flushed.', 'info'));
    });
  }

  // ═══════════════════════════════════════════════════════════════
  //  INIT — runs after ALL functions are defined
  // ═══════════════════════════════════════════════════════════════
  $(document).ready(function () {
    initDashboard();
    initShortsManager();
    initPendingPage();
    initBoostsPage();
    initCoinsPage();
    initAnalyticsPage();
    initPresetsPage();
    initSettingsPage();
  });

}(jQuery));

/* ══════════════════════════════════════════════════
   PRIZE EDITOR — Spin Wheel admin panel
══════════════════════════════════════════════════ */
(function(){
  'use strict';

  function serializePrizes(){
    const rows = document.querySelectorAll('#zsuPrizeList .zsu-prize-row');
    const data = [];
    rows.forEach(row=>{
      const label = row.querySelector('[name*="[label]"]')?.value?.trim();
      const color = row.querySelector('[name*="[color]"]')?.value?.trim()||'#374151';
      const type  = row.querySelector('[name*="[type]"]')?.value?.trim()||'none';
      const value = row.querySelector('[name*="[value]"]')?.value?.trim()||'';
      if(label) data.push({label,color,type,value});
    });
    const hidden = document.getElementById('zsuPrizesJSON');
    if(hidden) hidden.value = JSON.stringify(data);
  }

  function addPrizeRow(label='New Prize', color='#6366f1', type='coupon', value='CODE'){
    const list = document.getElementById('zsuPrizeList');
    if(!list || list.children.length>=10) return;
    const idx = list.children.length;
    const style = 'width:100%;padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:#fff;font-size:.78rem;box-sizing:border-box;';
    const row = document.createElement('div');
    row.className='zsu-prize-row';
    row.dataset.idx=idx;
    row.style.cssText='display:grid;grid-template-columns:24px 1fr 1fr 90px 1fr 36px;gap:6px;align-items:center;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:8px 10px;';
    row.innerHTML=`
      <span style="cursor:move;color:rgba(255,255,255,.3);font-size:14px;user-select:none;">⠿</span>
      <input type="text" name="prizes[${idx}][label]" value="${label}" placeholder="Label" style="${style}">
      <input type="color" name="prizes[${idx}][color]" value="${color}" style="height:34px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:transparent;cursor:pointer;width:100%;">
      <select name="prizes[${idx}][type]" style="${style}">
        <option value="coupon"${type==='coupon'?' selected':''}>Coupon</option>
        <option value="none"${type==='none'?' selected':''}>No Prize</option>
      </select>
      <input type="text" name="prizes[${idx}][value]" value="${value}" placeholder="COUPONCODE" style="${style};text-transform:uppercase;font-family:monospace;">
      <button type="button" class="zsu-prize-remove" style="width:32px;height:32px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:transparent;color:rgba(255,255,255,.4);cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;">×</button>
    `;
    list.appendChild(row);
    bindRowEvents(row);
    serializePrizes();
  }

  function bindRowEvents(row){
    row.querySelectorAll('input,select').forEach(el=>{
      el.addEventListener('input', serializePrizes);
      el.addEventListener('change', serializePrizes);
    });
    const rm = row.querySelector('.zsu-prize-remove');
    if(rm) rm.addEventListener('click',()=>{ row.remove(); serializePrizes(); });
  }

  document.addEventListener('DOMContentLoaded',()=>{
    // Bind existing rows
    document.querySelectorAll('#zsuPrizeList .zsu-prize-row').forEach(bindRowEvents);

    // Add button
    const addBtn = document.getElementById('zsuAddPrize');
    if(addBtn) addBtn.addEventListener('click',()=>addPrizeRow());

    // Serialize before save
    const saveBtn = document.getElementById('zsuSaveSettings');
    if(saveBtn) saveBtn.addEventListener('click', serializePrizes, true);

    // Initial serialize
    serializePrizes();

    // Settings tabs — ensure homerail/spinwheel tabs work
    document.querySelectorAll('.zsu-settings-tab-btn').forEach(btn=>{
      btn.addEventListener('click',()=>{
        const tab = btn.dataset.tab;
        document.querySelectorAll('.zsu-settings-tab-btn').forEach(b=>b.classList.toggle('active',b===btn));
        document.querySelectorAll('.zsu-settings-panel').forEach(p=>p.classList.toggle('active',p.id==='zsuTab-'+tab));
      });
    });
  });
})();
