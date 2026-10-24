<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $seoShort ? $seoShort->title . ' — K71 Shorts' : 'Shorts — Watch & Shop Short Videos on K71' }}</title>
    <meta name="description" content="{{ $seoShort ? ($seoShort->description ?: 'Watch ' . $seoShort->title . ' on K71 Shorts.') : 'Discover trending fashion, food, lifestyle, and products through short videos on K71.' }}">

    <!-- OpenGraph & Twitter Cards -->
    <meta property="og:title" content="{{ $seoShort ? $seoShort->title : 'K71 Shorts — Short Video Commerce' }}">
    <meta property="og:description" content="{{ $seoShort ? ($seoShort->description ?: 'Watch on K71 Shorts') : 'Shop directly from trending videos on K71' }}">
    <meta property="og:image" content="{{ $seoShort ? ($seoShort->thumb_url ?: asset('images/logo.png')) : asset('images/logo.png') }}">
    <meta property="og:type" content="video.other">
    <meta name="twitter:card" content="summary_large_image">

    @if($seoShort)
    <!-- VideoObject Schema.org JSON-LD for Googlebot -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "VideoObject",
        "name": "{{ addslashes($seoShort->title) }}",
        "description": "{{ addslashes($seoShort->description ?: $seoShort->title) }}",
        "thumbnailUrl": ["{{ $seoShort->thumb_url ?: asset('images/product-placeholder.svg') }}"],
        "uploadDate": "{{ $seoShort->created_at?->toIso8601String() ?? now()->toIso8601String() }}",
        "contentUrl": "{{ $seoShort->video_url }}",
        "embedUrl": "{{ route('shorts.show', $seoShort->id) }}"
    }
    </script>
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- K71 Core Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Dedicated Shorts CSS (isolated per Decision D12) -->
    <link rel="stylesheet" href="{{ asset('shorts-assets/css/zsu-frontend.css') }}">

    <style id="zsu-inline-css">
        :root {
            --zsu-primary: {{ $zsuConfig['primary_color'] ?? '#7c3aed' }};
            --zsu-accent: {{ $zsuConfig['accent_color'] ?? '#f59e0b' }};
            --zsu-grad-start: #0f0c29;
            --zsu-grad-end: #302b63;
            --zsu-font: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            --zsu-radius: 20px;
            --zsu-radius-sm: 12px;
            --zsu-radius-lg: 28px;
            --zsu-neon: 0 0 20px rgba(124, 58, 237, 0.35), 0 0 40px rgba(124, 58, 237, 0.15);
            --zsu-glass-bg: rgba(15, 12, 41, 0.7);
            --zsu-heading-color: #ffffff;
        }

        .zsu-body {
            background: #080711;
            color: #f8fafc;
            min-height: 100vh;
            padding-bottom: 70px;
            font-family: var(--zsu-font);
        }

        /* Responsive container & header styling */
        .zsu-filter-strip {
            display: flex;
            align-items: center;
            gap: 10px;
            overflow-x: auto;
            padding: 12px 16px;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }
        .zsu-filter-strip::-webkit-scrollbar { display: none; }

        .zsu-filter-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #cbd5e1;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .zsu-filter-pill:hover, .zsu-filter-pill.active {
            background: linear-gradient(135deg, #e11d48, #be123c);
            border-color: #f43f5e;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(225, 29, 72, 0.35);
        }
    </style>
</head>
<body class="zsu-body zsu-neon-on zsu-glass-on zsu-particles-on">

    <!-- Top Navigation -->
    <x-k71-nav />

    <!-- Particle Background Canvas -->
    <canvas id="zsuParticleCanvas" aria-hidden="true"></canvas>

    <!-- Categories & Filter Strip -->
    <div class="sticky top-16 z-30 bg-[#080711]/90 backdrop-blur-md border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4">
            <div class="zsu-filter-strip" id="zsuFilterStrip">
                <button class="zsu-filter-pill active" data-filter="latest">
                    <span>⚡</span> Latest
                </button>
                <button class="zsu-filter-pill" data-filter="trending">
                    <span>🔥</span> Trending
                </button>
                <button class="zsu-filter-pill" data-filter="popular">
                    <span>👑</span> Popular
                </button>
                <button class="zsu-filter-pill" data-filter="boosted">
                    <span>🚀</span> Featured
                </button>
                <div class="w-px h-5 bg-white/20 mx-1"></div>
                @foreach($categories as $cat)
                <button class="zsu-filter-pill" data-cat="{{ $cat['slug'] }}">
                    {{ $cat['name'] }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <main class="zsu-main max-w-7xl mx-auto px-4 pt-4" id="zsuMain" role="main">

        <!-- ── FEATURED HERO RAIL ──────────────────────────────────── -->
        <section class="zsu-featured-section mb-8" id="zsuFeaturedSection" aria-label="Featured Shorts">
            <div class="zsu-section-header-row flex items-center justify-between mb-4">
                <h2 class="zsu-section-heading text-lg font-bold flex items-center gap-2">
                    <span class="zsu-heading-icon text-amber-400">⭐</span>
                    Featured Creator Picks
                </h2>
                <a href="#" class="zsu-see-all text-xs font-semibold text-rose-400 hover:text-rose-300 transition" data-filter="boosted">See All →</a>
            </div>
            <div class="zsu-featured-rail" id="zsuFeaturedRail" role="list" aria-label="Featured shorts">
                @foreach($featuredShorts as $s)
                <div class="zsu-short-card{{ $s->is_featured ? ' featured' : '' }}{{ $s->is_boosted ? ' boosted' : '' }}" data-id="{{ $s->id }}" role="listitem" tabindex="0">
                    <div class="zsu-card-thumb-wrap">
                        @if($s->is_boosted)
                        <div class="zsu-elite-ribbon"><svg viewBox="0 0 14 10" width="11" height="8" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M0 10h14V8H0v2zM14 3L11 1 7 5 3 1 0 3l1.5 4h11L14 3z" fill="#1e3a5f"/></svg> ELITE</div>
                        @endif
                        <img class="zsu-card-thumb" src="{{ $s->thumb_url ?: asset('images/product-placeholder.svg') }}" alt="{{ $s->title }}" loading="lazy" decoding="async">
                        <div class="zsu-card-overlay"></div>
                        <div class="zsu-card-badges">
                            @if($s->is_featured && !$s->is_boosted)
                            <span class="zsu-card-badge zsu-badge-featured">⭐ Featured</span>
                            @endif
                            @if($s->trending_score > 100)
                            <span class="zsu-card-badge zsu-badge-trending">🔥</span>
                            @endif
                        </div>
                        @if($s->duration)
                        <div class="zsu-card-duration">{{ gmdate('i:s', $s->duration) }}</div>
                        @endif
                        <div class="zsu-card-play">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="white" style="filter:drop-shadow(0 1px 4px rgba(0,0,0,.4));"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        </div>
                        <div class="zsu-card-info">
                            @if($s->vendor)
                            <div class="zsu-card-vendor">
                                <img class="zsu-card-vendor-avatar" src="{{ $s->vendor->logo_url ?: asset('images/default-avatar.png') }}" alt="{{ $s->vendor->shop_name }}" loading="lazy">
                                <span class="zsu-card-vendor-name">{{ $s->vendor->shop_name }}</span>
                            </div>
                            @endif
                            <div class="zsu-card-title">{{ $s->title }}</div>
                        </div>
                        <div class="zsu-card-shine"></div>
                        <button class="zsu-card-quick-like" data-id="{{ $s->id }}" aria-label="Like" title="Like">🤍</button>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <!-- ── MAIN SHORTS GRID ─────────────────────────────────────── -->
        <section class="zsu-grid-section" id="zsuGridSection" aria-label="All Shorts">
            <div class="zsu-section-header-row flex items-center justify-between mb-4">
                <h2 class="zsu-section-heading text-lg font-bold flex items-center gap-2" id="zsuGridHeading">
                    <span class="zsu-heading-icon text-rose-500">🎬</span>
                    Shorts Feed
                </h2>
                <div class="zsu-grid-result-count text-xs text-slate-400" id="zsuGridCount">{{ $initialShorts->count() }} shorts</div>
            </div>

                @if($initialShorts->isEmpty())
                    <div style="grid-column: 1 / -1;" class="py-16 px-4 text-center max-w-lg mx-auto">
                        <div class="w-16 h-16 rounded-3xl bg-white/10 text-rose-500 text-3xl flex items-center justify-center mx-auto mb-4 border border-white/10">
                            🎬
                        </div>
                        <span class="text-[11px] font-black uppercase tracking-wider text-rose-400 bg-rose-500/20 px-3 py-1 rounded-full border border-rose-500/30 inline-block mb-2">
                            Live Video Commerce Ready
                        </span>
                        <h3 class="text-xl font-black text-white mb-2">Short Video Shopping Feed</h3>
                        <p class="text-xs text-slate-400 mb-6 leading-relaxed">
                            All test demo videos have been cleared. Verified sellers can now upload short shoppable reels and video reviews directly linked to their products.
                        </p>
                        <div class="flex items-center justify-center gap-3">
                            <a href="{{ route('vendor.login') }}" class="px-6 py-2.5 rounded-full bg-gradient-to-r from-rose-600 to-pink-600 text-white text-xs font-black shadow-lg shadow-rose-600/30 hover:brightness-110 transition">
                                Upload Shoppable Reel &rarr;
                            </a>
                            <a href="{{ route('catalog.index') }}" class="px-5 py-2.5 rounded-full bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition">
                                Browse Products
                            </a>
                        </div>
                    </div>
                @else
                    @foreach($initialShorts as $s)
                    <div class="zsu-short-card{{ $s->is_featured ? ' featured' : '' }}{{ $s->is_boosted ? ' boosted' : '' }}" data-id="{{ $s->id }}" role="listitem" tabindex="0">
                        <div class="zsu-card-thumb-wrap">
                            <img class="zsu-card-thumb" src="{{ $s->thumb_url ?: asset('images/product-placeholder.svg') }}" alt="{{ $s->title }}" loading="lazy" decoding="async">
                            <div class="zsu-card-overlay"></div>
                            <div class="zsu-card-badges">
                                @if($s->is_featured && !$s->is_boosted)
                                <span class="zsu-card-badge zsu-badge-featured">⭐ Featured</span>
                                @endif
                                @if($s->trending_score > 100)
                                <span class="zsu-card-badge zsu-badge-trending">🔥</span>
                                @endif
                            </div>
                            @if($s->duration)
                            <div class="zsu-card-duration">{{ gmdate('i:s', $s->duration) }}</div>
                            @endif
                            <div class="zsu-card-play">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="white" style="filter:drop-shadow(0 1px 4px rgba(0,0,0,.4));"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                            </div>
                            <div class="zsu-card-info">
                                @if($s->vendor)
                                <div class="zsu-card-vendor">
                                    <img class="zsu-card-vendor-avatar" src="{{ $s->vendor->logo_url ?: asset('images/default-avatar.png') }}" alt="{{ $s->vendor->shop_name }}" loading="lazy">
                                    <span class="zsu-card-vendor-name">{{ $s->vendor->shop_name }}</span>
                                </div>
                                @endif
                                <div class="zsu-card-title">{{ $s->title }}</div>
                                @if($s->products->count())
                                <div class="mt-1 flex items-center gap-1.5 text-[11px] font-semibold text-rose-300">
                                    <span>🛍️</span> Shop {{ $s->products->count() }} {{ Str::plural('item', $s->products->count()) }}
                                </div>
                                @endif
                            </div>
                            <div class="zsu-card-shine"></div>
                            <button class="zsu-card-quick-like" data-id="{{ $s->id }}" aria-label="Like" title="Like">🤍</button>
                        </div>
                    </div>
                    @endforeach
                @endif

            <!-- Infinite scroll sentinel -->
            <div class="zsu-load-more-sentinel py-8 text-center" id="zsuSentinel" aria-hidden="true">
                <div class="zsu-load-spinner inline-flex items-center gap-3 text-sm text-slate-400" id="zsuLoadSpinner" style="display:none;">
                    <div class="zsu-spinner-ring"></div>
                    <span>Loading more shorts…</span>
                </div>
                <button class="zsu-load-more-btn px-6 py-2.5 rounded-full bg-white/10 text-white font-medium hover:bg-white/20 transition" id="zsuLoadMoreBtn" style="display:none;">
                    Load More
                </button>
            </div>

            <!-- Empty state -->
            <div class="zsu-grid-empty text-center py-16" id="zsuGridEmpty" style="display:none;" role="alert">
                <div class="text-5xl mb-3">🎬</div>
                <h3 class="text-lg font-bold text-white mb-1">No Shorts Found</h3>
                <p class="text-sm text-slate-400 mb-4">Try selecting a different category or filter.</p>
                <button class="px-5 py-2 rounded-full bg-rose-600 text-white font-semibold text-sm hover:bg-rose-500 transition" id="zsuEmptyReset">Show All Shorts</button>
            </div>
        </section>

    </main>

    <!-- ════════════════════════════════════════════════════════════ -->
    <!--  LIGHTBOX / FULLSCREEN REEL PLAYER                           -->
    <!-- ════════════════════════════════════════════════════════════ -->
    <div class="zsu-lightbox" id="zsuLightbox" role="dialog" aria-modal="true" aria-label="Video player" style="display:none;">

        <!-- Close button -->
        <button class="zsu-lb-close" id="zsuLbClose" aria-label="Close">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>

        <!-- Navigation arrows -->
        <button class="zsu-lb-nav zsu-lb-prev" id="zsuLbPrev" aria-label="Previous short">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
        </button>
        <button class="zsu-lb-nav zsu-lb-next" id="zsuLbNext" aria-label="Next short">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
        </button>

        <!-- Video stack (vertical snap scroll) -->
        <div class="zsu-lb-stack" id="zsuLbStack" role="feed" aria-label="Video feed">
            <!-- Cards injected by JS -->
        </div>

        <!-- Side panel -->
        <aside class="zsu-lb-sidebar" id="zsuLbSidebar" aria-label="Video details">

            <!-- Vendor info -->
            <div class="zsu-lb-vendor" id="zsuLbVendor">
                <img class="zsu-lb-avatar" id="zsuLbAvatar" src="" alt="" loading="lazy">
                <div class="zsu-lb-vendor-info">
                    <span class="zsu-lb-vendor-name" id="zsuLbVendorName"></span>
                    <span class="zsu-lb-vendor-store" id="zsuLbVendorStore"></span>
                </div>
                <button class="zsu-follow-btn" id="zsuLbFollowBtn" data-vendor-id="" aria-pressed="false">
                    <span class="zsu-follow-label">Follow</span>
                </button>
            </div>

            <!-- Title + description -->
            <div class="zsu-lb-text">
                <h3 class="zsu-lb-title" id="zsuLbTitle"></h3>
                <p class="zsu-lb-desc" id="zsuLbDesc"></p>
            </div>

            <!-- Category chips -->
            <div class="zsu-lb-cats" id="zsuLbCats"></div>

            <!-- Stats row -->
            <div class="zsu-lb-stats" id="zsuLbStats">
                <div class="zsu-lb-stat">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span id="zsuLbViews">0</span>
                </div>
                <div class="zsu-lb-stat">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    <span id="zsuLbLikes">0</span>
                </div>
                <div class="zsu-lb-stat">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                    <span id="zsuLbShares">0</span>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="zsu-lb-actions" id="zsuLbActions">
                <button class="zsu-lb-action-btn zsu-lb-like-btn" id="zsuLbLikeBtn" data-short-id="" aria-pressed="false" title="Like">
                    <span class="zsu-action-icon">❤️</span>
                    <span class="zsu-action-label" id="zsuLbLikeBtnCount">Like</span>
                </button>

                <button class="zsu-lb-action-btn zsu-lb-share-btn" id="zsuLbShareBtn" title="Share">
                    <span class="zsu-action-icon">🔗</span>
                    <span class="zsu-action-label">Share</span>
                </button>

                <button class="zsu-lb-action-btn zsu-lb-comment-btn" id="zsuLbCommentBtn" title="Comments">
                    <span class="zsu-action-icon">💬</span>
                    <span class="zsu-action-label" id="zsuLbCommentCount">0</span>
                </button>

                <button class="zsu-lb-action-btn zsu-lb-mute-btn" id="zsuLbMuteBtn" title="Mute/Unmute">
                    <span class="zsu-action-icon" id="zsuMuteIcon">🔇</span>
                    <span class="zsu-action-label">Mute</span>
                </button>

                <button class="zsu-lb-action-btn zsu-lb-fullscreen-btn" id="zsuLbFullscreenBtn" title="Fullscreen">
                    <span class="zsu-action-icon">⛶</span>
                    <span class="zsu-action-label">Expand</span>
                </button>
            </div>

            <!-- Comments panel -->
            <div class="zsu-comments-panel" id="zsuCommentsPanel" style="display:none;">
                <div class="zsu-comments-header">
                    <span>💬 Comments</span>
                    <button class="zsu-comments-close" id="zsuCommentsClose">✕</button>
                </div>
                <div class="zsu-comments-list" id="zsuCommentsList"></div>
                @auth
                <div class="zsu-comment-input-row">
                    <img src="{{ asset('images/default-avatar.png') }}" class="zsu-comment-avatar" alt="" loading="lazy">
                    <input type="text" class="zsu-comment-input" id="zsuCommentInput" placeholder="Add a comment…" maxlength="500">
                    <button class="zsu-comment-submit" id="zsuCommentSubmit" aria-label="Post">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
                @endauth
            </div>

        </aside>

        <!-- Progress bar -->
        <div class="zsu-lb-progress" id="zsuLbProgress" aria-hidden="true">
            <div class="zsu-lb-progress-bar" id="zsuLbProgressBar"></div>
        </div>

        <!-- Shoppable bottom sheet -->
        <div id="zsuShopSheet" style="display:none;">
            <div id="zsuShopSheetInner">
                <div id="zsuShopSheetHandle"></div>
                <div id="zsuShopSheetHeader">
                    <span>🛍️ Shop Tagged Products</span>
                    <button id="zsuShopSheetClose" aria-label="Close">✕</button>
                </div>
                <div id="zsuShopSheetList"></div>
            </div>
        </div>
        <div id="zsuShopSheetOverlay" style="display:none;"></div>

    </div>

    <!-- ════════════════════════════════════════════════════════════ -->
    <!--  SHARE SHEET                                                 -->
    <!-- ════════════════════════════════════════════════════════════ -->
    <div class="zsu-share-sheet" id="zsuShareSheet" role="dialog" aria-modal="true" aria-label="Share" style="display:none;">
        <div class="zsu-share-sheet-inner">
            <div class="zsu-share-sheet-header">
                <span class="zsu-share-sheet-title">Share this Short</span>
                <button class="zsu-share-sheet-close" id="zsuShareSheetClose" aria-label="Close">✕</button>
            </div>
            <div class="zsu-share-preview" id="zsuSharePreview">
                <img class="zsu-share-thumb" id="zsuShareThumb" src="" alt="" loading="lazy">
                <span class="zsu-share-video-title" id="zsuShareVideoTitle"></span>
            </div>
            <div class="zsu-share-platforms" id="zsuSharePlatforms">
                <button class="zsu-share-btn" data-platform="whatsapp">
                    <span class="zsu-share-platform-icon">💬</span>
                    <span class="zsu-share-platform-label">WhatsApp</span>
                </button>
                <button class="zsu-share-btn" data-platform="facebook">
                    <span class="zsu-share-platform-icon">📘</span>
                    <span class="zsu-share-platform-label">Facebook</span>
                </button>
                <button class="zsu-share-btn" data-platform="twitter">
                    <span class="zsu-share-platform-icon">🐦</span>
                    <span class="zsu-share-platform-label">Twitter</span>
                </button>
                <button class="zsu-share-btn" data-platform="copy">
                    <span class="zsu-share-platform-icon">📋</span>
                    <span class="zsu-share-platform-label">Copy Link</span>
                </button>
            </div>
            <div class="zsu-copy-link-row">
                <input type="text" class="zsu-copy-link-input" id="zsuCopyLinkInput" readonly aria-label="Share URL">
                <button class="zsu-copy-link-btn" id="zsuCopyLinkBtn">Copy</button>
            </div>
        </div>
    </div>
    <div class="zsu-overlay" id="zsuShareOverlay" style="display:none;"></div>

    <!-- ════════════════════════════════════════════════════════════ -->
    <!--  PRODUCT QUICK-VIEW MODAL                                   -->
    <!-- ════════════════════════════════════════════════════════════ -->
    <div class="zsu-product-modal" id="zsuProductModal" role="dialog" aria-modal="true" aria-label="Product details" style="display:none;">
        <div class="zsu-product-modal-inner">
            <button class="zsu-product-modal-close" id="zsuProductModalClose" aria-label="Close">✕</button>
            <div class="zsu-product-modal-body" id="zsuProductModalBody"></div>
        </div>
    </div>
    <div class="zsu-overlay" id="zsuProductOverlay" style="display:none;"></div>

    <!-- Toast Container & Confetti Canvas -->
    <div class="zsu-toast-container" id="zsuToastContainer" aria-live="polite" aria-atomic="true"></div>
    <canvas id="zsuConfettiCanvas" aria-hidden="true"></canvas>

    <!-- ════════════════════════════════════════════════════════════ -->
    <!--  SPIN-TO-WIN WHEEL MODAL & FAB                               -->
    <!-- ════════════════════════════════════════════════════════════ -->
    <div class="zsu-wheel-overlay" id="zsuWheelOverlay" style="display:none;" role="dialog" aria-modal="true" aria-label="Spin to Win">
        <div class="zsu-wheel-modal">
            <button class="zsu-wheel-close" id="zsuWheelClose" aria-label="Close">✕</button>
            <div class="zsu-wheel-header">
                <h2 class="zsu-wheel-title">
                    <span class="zsu-wheel-title-icon">🎡</span>
                    Spin & Win!
                </h2>
                <p class="zsu-wheel-subtitle">Spin once a day for exclusive discount coupons!</p>
            </div>
            <div class="zsu-wheel-arena">
                <div class="zsu-wheel-pointer" aria-hidden="true">▼</div>
                <div class="zsu-wheel-glow-ring" aria-hidden="true"></div>
                <canvas id="zsuWheelCanvas" width="340" height="340" aria-hidden="true"></canvas>
                <div class="zsu-wheel-center-hub" aria-hidden="true">
                    <span class="zsu-wheel-hub-icon">⚡</span>
                </div>
            </div>
            <button class="zsu-wheel-spin-btn" id="zsuWheelSpinBtn">
                <span class="zsu-wheel-spin-label">SPIN NOW!</span>
                <span class="zsu-wheel-spin-sub">1 spin remaining today</span>
            </button>
            <div class="zsu-wheel-result" id="zsuWheelResult" style="display:none;">
                <div class="zsu-wheel-result-emoji" id="zsuWheelResultEmoji">🎉</div>
                <div class="zsu-wheel-result-text" id="zsuWheelResultText"></div>
                <div class="zsu-wheel-result-code" id="zsuWheelResultCode" style="display:none;">
                    <span class="zsu-coupon-label">Your coupon code:</span>
                    <div class="zsu-coupon-box" id="zsuCouponBox">
                        <span id="zsuCouponCode"></span>
                        <button class="zsu-coupon-copy" id="zsuCouponCopy" aria-label="Copy code">📋</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Trigger Bubble for Spin Wheel -->
    <button class="zsu-wheel-fab" id="zsuWheelFab" aria-label="Spin to Win" title="Spin to Win!" style="position:fixed;bottom:85px;right:20px;z-index:90;width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#e11d48,#be123c);border:2px solid #fda4af;box-shadow:0 8px 25px rgba(225,29,72,0.5);display:flex;align-items:center;justify-content:center;font-size:24px;cursor:pointer;animation:zsu-pulse 2s infinite;">
        🎡
    </button>

    <!-- K71 Bottom Navigation, Mini-Cart & Mini-Wishlist -->
    <x-k71-bottom-nav active="shorts" />
    <x-k71-mini-cart />
    <x-k71-mini-wishlist />

    <!-- Localize ZSU configuration & load frontend script -->
    <script>
        window.ZSU = @json($zsuConfig);
    </script>
    <script src="{{ asset('shorts-assets/js/zsu-frontend.js') }}" defer></script>
</body>
</html>
