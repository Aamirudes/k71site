<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $seoShort ? $seoShort->title . ' — K71 Vintage Video Commerce' : 'Flipkart Video Cinema — Watch, Win & Shop on K71 Shorts' }}</title>
    <meta name="description" content="{{ $seoShort ? ($seoShort->description ?: 'Watch ' . $seoShort->title . ' on K71 Shorts.') : 'Retro cinema meets modern video commerce. Watch trending shorts, earn coins, and shop instant products.' }}">

    <!-- OpenGraph & Twitter Cards -->
    <meta property="og:title" content="{{ $seoShort ? $seoShort->title : 'K71 Shorts — Flipkart Style Video Commerce' }}">
    <meta property="og:description" content="{{ $seoShort ? ($seoShort->description ?: 'Watch on K71 Shorts') : 'Shop directly from trending videos with interactive rewards.' }}">
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
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- K71 Core Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Dedicated Shorts CSS -->
    <link rel="stylesheet" href="{{ asset('shorts-assets/css/zsu-frontend.css') }}">

    <style id="zsu-inline-css">
        :root {
            --zsu-primary: #f59e0b;
            --zsu-accent: #fbbf24;
            --zsu-film-noir: #090a0f;
            --zsu-film-reel: #161824;
            --zsu-amber-glow: 0 0 25px rgba(245, 158, 11, 0.45), 0 0 50px rgba(245, 158, 11, 0.2);
            --zsu-font: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            --zsu-vintage-title: 'Cinzel', serif;
        }

        body.zsu-body {
            background: #090a0f radial-gradient(circle at 50% 10%, #1c1527 0%, #090a0f 60%);
            color: #fef3c7;
            min-height: 100vh;
            padding-bottom: 85px;
            font-family: var(--zsu-font);
        }

        /* Retro Cinema Film Strip Header & Marquee */
        .vintage-marquee {
            background: linear-gradient(90deg, #1e130c, #451a03, #78350f, #451a03, #1e130c);
            border: 1px solid rgba(245, 158, 11, 0.35);
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.8), inset 0 0 15px rgba(245, 158, 11, 0.2);
        }

        .film-strip-border {
            background-image: repeating-linear-gradient(
                90deg,
                #000 0px,
                #000 12px,
                transparent 12px,
                transparent 18px
            );
            height: 6px;
            opacity: 0.7;
        }

        @keyframes vinylSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-vinyl {
            animation: vinylSpin 3.5s linear infinite;
        }

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
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(245, 158, 11, 0.2);
            color: #fef3c7;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .zsu-filter-pill:hover, .zsu-filter-pill.active {
            background: linear-gradient(135deg, #d97706, #b45309);
            border-color: #fbbf24;
            color: #ffffff;
            box-shadow: 0 4px 18px rgba(245, 158, 11, 0.4);
            transform: translateY(-1px);
        }

        .retro-card {
            border: 1px solid rgba(245, 158, 11, 0.25);
            background: #11121d;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .retro-card:hover {
            border-color: #f59e0b;
            box-shadow: 0 15px 35px rgba(245, 158, 11, 0.25);
            transform: translateY(-4px) scale(1.02);
        }
    </style>
</head>
<body class="zsu-body zsu-neon-on zsu-glass-on zsu-particles-on">

    <!-- Top Navigation -->
    <x-k71-nav />

    <!-- Particle Background Canvas -->
    <canvas id="zsuParticleCanvas" aria-hidden="true"></canvas>

    <!-- ════════════════════════════════════════════════════════════ -->
    <!-- FLIPKART RETRO CINEMA VINTAGE MARQUEE & COIN REWARDS BAR     -->
    <!-- ════════════════════════════════════════════════════════════ -->
    <div class="max-w-7xl mx-auto px-4 pt-4">
        <div class="vintage-marquee rounded-3xl p-4 sm:p-5 text-white flex flex-col md:flex-row items-center justify-between gap-4 mb-4">
            <div class="flex items-center gap-3.5">
                <!-- Glowing Vintage Film Camera Icon -->
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-yellow-400 text-zinc-950 flex items-center justify-center font-black text-2xl shrink-0 shadow-lg animate-pulse">
                    🎬
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider bg-black/40 text-amber-300 px-2.5 py-0.5 rounded-md border border-amber-500/30">
                            FLIPKART VIDEO COMMERCE • RETRO CINEMA
                        </span>
                        <span class="text-[11px] text-amber-200 hidden sm:inline">shorts.{{ config('k71.root_domain', 'k71.in') }}</span>
                    </div>
                    <h1 class="text-base sm:text-lg font-black tracking-wide text-amber-100 mt-0.5 uppercase">
                        Watch Trending Reels &bull; Win SuperCoins &bull; Instant Tap-To-Buy
                    </h1>
                </div>
            </div>

            <!-- Flipkart SuperCoins Reward Tracker & Lucky Wheel Trigger -->
            <div class="flex items-center gap-3 shrink-0 w-full md:w-auto justify-between md:justify-end">
                <div class="flex items-center gap-2 bg-black/40 backdrop-blur-md px-3.5 py-2 rounded-2xl border border-amber-400/30">
                    <span class="text-lg">🪙</span>
                    <div>
                        <div class="text-[9px] uppercase font-black text-amber-400 tracking-wider">SuperCoins Earned</div>
                        <div class="text-xs font-black text-white">450 Coins Active</div>
                    </div>
                </div>

                <button type="button" onclick="document.getElementById('zsuWheelFab').click()" 
                        class="px-4 py-2 rounded-2xl bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-zinc-950 font-black text-xs uppercase tracking-wider shadow-lg transition active:scale-95 flex items-center gap-1.5 cursor-pointer">
                    <span>🎡 Daily Spin</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Categories & Filter Strip (Retro Cinema Theme) -->
    <div class="sticky top-16 z-30 bg-[#090a0f]/95 backdrop-blur-md border-b border-amber-500/20">
        <div class="max-w-7xl mx-auto px-4">
            <div class="zsu-filter-strip" id="zsuFilterStrip">
                <button class="zsu-filter-pill active" data-filter="latest">
                    <span>⚡</span> Latest Releases
                </button>
                <button class="zsu-filter-pill" data-filter="trending">
                    <span>🔥</span> Trending Now
                </button>
                <button class="zsu-filter-pill" data-filter="popular">
                    <span>👑</span> Most Watched
                </button>
                <button class="zsu-filter-pill" data-filter="boosted">
                    <span>🚀</span> Featured Reels
                </button>
                <div class="w-px h-5 bg-amber-500/30 mx-1"></div>
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
        <section class="zsu-featured-section mb-10" id="zsuFeaturedSection" aria-label="Featured Shorts">
            <div class="zsu-section-header-row flex items-center justify-between mb-4">
                <h2 class="zsu-section-heading text-lg font-black uppercase tracking-wider flex items-center gap-2 text-amber-200">
                    <span class="zsu-heading-icon text-amber-400">⭐</span>
                    Featured Cinema &amp; Creator Premieres
                </h2>
                <a href="#" class="zsu-see-all text-xs font-black uppercase text-amber-400 hover:text-amber-300 transition" data-filter="boosted">See All →</a>
            </div>

            <!-- Film strip perforation top line -->
            <div class="film-strip-border mb-3 rounded-full"></div>

            <div class="zsu-featured-rail" id="zsuFeaturedRail" role="list" aria-label="Featured shorts">
                @foreach($featuredShorts as $s)
                <div class="zsu-short-card retro-card{{ $s->is_featured ? ' featured' : '' }}{{ $s->is_boosted ? ' boosted' : '' }}" data-id="{{ $s->id }}" role="listitem" tabindex="0">
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
                        <div class="zsu-card-duration font-mono">{{ gmdate('i:s', $s->duration) }}</div>
                        @endif
                        <div class="zsu-card-play">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="#f59e0b" style="filter:drop-shadow(0 1px 4px rgba(0,0,0,.6));"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        </div>
                        <div class="zsu-card-info">
                            @if($s->vendor)
                            <div class="zsu-card-vendor">
                                <img class="zsu-card-vendor-avatar" src="{{ $s->vendor->logo ? asset('storage/'.$s->vendor->logo) : asset('images/default-avatar.png') }}" alt="{{ $s->vendor->shop_name }}" loading="lazy">
                                <span class="zsu-card-vendor-name font-bold">{{ $s->vendor->shop_name }}</span>
                            </div>
                            @endif
                            <div class="zsu-card-title font-black">{{ $s->title }}</div>
                        </div>
                        <div class="zsu-card-shine"></div>
                        <button class="zsu-card-quick-like" data-id="{{ $s->id }}" aria-label="Like" title="Like">🤍</button>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Film strip perforation bottom line -->
            <div class="film-strip-border mt-3 rounded-full"></div>
        </section>

        <!-- ── MAIN SHORTS GRID ─────────────────────────────────────── -->
        <section class="zsu-grid-section" id="zsuGridSection" aria-label="All Shorts">
            <div class="zsu-section-header-row flex items-center justify-between mb-4">
                <h2 class="zsu-section-heading text-lg font-black uppercase tracking-wider flex items-center gap-2 text-amber-200" id="zsuGridHeading">
                    <span class="zsu-heading-icon text-amber-400">🎞️</span>
                    Shoppable Cinema Reel Feed
                </h2>
                <div class="zsu-grid-result-count text-xs text-amber-300/80 font-bold" id="zsuGridCount">{{ $initialShorts->count() }} reels available</div>
            </div>

            <!-- Shorts grid -->
            <div class="zsu-grid" id="zsuGrid" style="--cols:2;" role="list" aria-label="Shorts grid">
                @foreach($initialShorts as $s)
                <div class="zsu-short-card retro-card{{ $s->is_featured ? ' featured' : '' }}{{ $s->is_boosted ? ' boosted' : '' }}" data-id="{{ $s->id }}" role="listitem" tabindex="0">
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
                        <div class="zsu-card-duration font-mono">{{ gmdate('i:s', $s->duration) }}</div>
                        @endif
                        <div class="zsu-card-play">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="#f59e0b" style="filter:drop-shadow(0 1px 4px rgba(0,0,0,.6));"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        </div>
                        <div class="zsu-card-info">
                            @if($s->vendor)
                            <div class="zsu-card-vendor">
                                <img class="zsu-card-vendor-avatar" src="{{ $s->vendor->logo ? asset('storage/'.$s->vendor->logo) : asset('images/default-avatar.png') }}" alt="{{ $s->vendor->shop_name }}" loading="lazy">
                                <span class="zsu-card-vendor-name font-bold">{{ $s->vendor->shop_name }}</span>
                            </div>
                            @endif
                            <div class="zsu-card-title font-black">{{ $s->title }}</div>
                            @if($s->products->count())
                            <div class="mt-1 flex items-center gap-1.5 text-[11px] font-bold text-amber-300">
                                <span>🛍️</span> Buy Tagged {{ $s->products->count() }} {{ Str::plural('Item', $s->products->count()) }}
                            </div>
                            @endif
                        </div>
                        <div class="zsu-card-shine"></div>
                        <button class="zsu-card-quick-like" data-id="{{ $s->id }}" aria-label="Like" title="Like">🤍</button>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Infinite scroll sentinel -->
            <div class="zsu-load-more-sentinel py-8 text-center" id="zsuSentinel" aria-hidden="true">
                <div class="zsu-load-spinner inline-flex items-center gap-3 text-sm text-amber-200" id="zsuLoadSpinner" style="display:none;">
                    <div class="zsu-spinner-ring"></div>
                    <span>Loading more reels…</span>
                </div>
                <button class="zsu-load-more-btn px-6 py-2.5 rounded-full bg-amber-500/20 border border-amber-500/40 text-amber-200 font-bold hover:bg-amber-500/30 transition" id="zsuLoadMoreBtn" style="display:none;">
                    Load More Reels
                </button>
            </div>

            <!-- Empty state -->
            <div class="zsu-grid-empty text-center py-16" id="zsuGridEmpty" style="display:none;" role="alert">
                <div class="text-5xl mb-3">🎬</div>
                <h3 class="text-lg font-black text-amber-200 mb-1">No Shorts Found</h3>
                <p class="text-sm text-slate-400 mb-4">Try selecting a different category or filter.</p>
                <button class="px-5 py-2 rounded-full bg-amber-500 text-zinc-950 font-black text-sm hover:bg-amber-400 transition" id="zsuEmptyReset">Show All Shorts</button>
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
                    Flipkart Video Spin &amp; Win!
                </h2>
                <p class="zsu-wheel-subtitle">Spin once a day for exclusive video discount coupons!</p>
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
    <button class="zsu-wheel-fab" id="zsuWheelFab" aria-label="Spin to Win" title="Spin to Win!" style="position:fixed;bottom:85px;right:20px;z-index:90;width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#f59e0b,#d97706);border:2px solid #fde68a;box-shadow:0 8px 25px rgba(245,158,11,0.5);display:flex;align-items:center;justify-content:center;font-size:24px;cursor:pointer;animation:zsu-pulse 2s infinite;">
        🎡
    </button>

    <!-- K71 Bottom Navigation & Mini-Cart -->
    <x-k71-bottom-nav active="shorts" />
    <x-k71-mini-cart />

    <!-- Localize ZSU configuration & load frontend script -->
    <script>
        window.ZSU = @json($zsuConfig);
    </script>
    <script src="{{ asset('shorts-assets/js/zsu-frontend.js') }}" defer></script>
</body>
</html>
