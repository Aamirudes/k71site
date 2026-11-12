@php
    $seq = $vendorScrollSequence ?? \App\Services\ScrollSequenceService::resolveVendorSequence($vendor, $config ?? []);
    $seqCfg = is_array($config['scroll_sequence'] ?? null) ? $config['scroll_sequence'] : [];
    $seqEnabled = array_key_exists('enabled', $seqCfg) ? (bool) $seqCfg['enabled'] : true;
    $seqFrames = is_array($seq['frames'] ?? null) ? array_values($seq['frames']) : [];
    $seqVideoUrl = trim((string) ($seq['video_url'] ?? ($seqCfg['video_url'] ?? '')));

    $brandCfg = $config['brand'] ?? [];
    $storeTitle = trim((string) ($brandCfg['store_name'] ?? $vendor->shop_name)) ?: 'K71 Studio';
    $storeTag = trim((string) ($vendor->tagline ?? '')) ?: 'Signature pieces unveiled frame by frame.';
    $storeDesc = trim((string) ($vendor->description ?? '')) ?: 'Explore crafted releases directly from our flagship studio before stepping into the live collection below.';

    $defaultHeightVh = count($seqFrames) >= 90 ? 340 : 290;
    $seqHeightVh = max(180, min(600, (int) (($seqCfg['height_vh'] ?? 0) ?: $defaultHeightVh)));
    $seqSmoothing = max(0.04, min(0.45, (float) (($seqCfg['smoothing'] ?? 0) ?: 0.14)));

    $seqBeats = [
        [
            'kicker' => trim((string) ($seqCfg['beat1_kicker'] ?? '')) ?: 'Chapter I • Studio Craft',
            'title' => trim((string) ($seqCfg['beat1_title'] ?? '')) ?: strtoupper($storeTitle),
            'desc' => trim((string) ($seqCfg['beat1_desc'] ?? '')) ?: $storeTag,
        ],
        [
            'kicker' => trim((string) ($seqCfg['beat2_kicker'] ?? '')) ?: 'Chapter II • Frame by Frame',
            'title' => trim((string) ($seqCfg['beat2_title'] ?? '')) ?: 'Crafted in Pure Motion.',
            'desc' => trim((string) ($seqCfg['beat2_desc'] ?? '')) ?: \Illuminate\Support\Str::limit($storeDesc, 150),
        ],
        [
            'kicker' => trim((string) ($seqCfg['beat3_kicker'] ?? '')) ?: 'Chapter III • Flagship Drop',
            'title' => trim((string) ($seqCfg['beat3_title'] ?? '')) ?: 'Discover the Collection.',
            'desc' => trim((string) ($seqCfg['beat3_desc'] ?? '')) ?: ('Scroll directly into verified pieces from ' . $storeTitle . '.'),
        ],
    ];

    $seqSkipHref = $seqSkipHref ?? '#kinetic-products';
    $seqCtaHref = $seqCtaHref ?? '#kinetic-products';
    $seqCtaLabel = trim((string) ($seqCfg['cta_text'] ?? ($seqCtaLabel ?? ''))) ?: 'Explore Collection';
    $seqSecondaryHref = $seqSecondaryHref ?? $vendor->storefrontUrl([], 'kinetic-products');
    $seqSecondaryLabel = $seqSecondaryLabel ?? 'Browse All';
    $seqUseH1 = $seqUseH1 ?? true;

    $seqBadgeLabel = !empty($seqFrames)
        ? 'Cinematic Scrub'
        : ($seqVideoUrl !== '' ? 'Video Scrub' : '3D Studio Preview');
@endphp

@once
<style>
    .kinetic-seq-stage {
        position: relative;
        width: 100%;
        background-color: #000000;
        color: #EFE9F2;
    }
    .kinetic-seq-sticky {
        position: -webkit-sticky;
        position: sticky;
        top: 0;
        height: 100vh;
        height: 100dvh;
        width: 100%;
        overflow: hidden;
        background-color: #000000;
    }
    .kinetic-seq-canvas {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        display: block;
    }
    .kinetic-seq-beat {
        opacity: var(--beat-o, 0);
        transform: translate3d(0, var(--beat-y, 24px), 0);
        pointer-events: var(--beat-pe, none);
        will-change: opacity, transform;
    }
    .kinetic-seq-progress-fill {
        transform-origin: 0 50%;
        transform: scaleX(var(--seq-p, 0));
        will-change: transform;
    }
    .kinetic-display {
        font-family: 'Cormorant Garamond', var(--sf-font-heading, Georgia, serif) !important;
        font-weight: 300 !important;
        text-transform: uppercase;
        letter-spacing: -0.012em !important;
        line-height: 0.92 !important;
    }
    .kinetic-cta-pill {
        background: #ECD2F3;
        color: #1A1018;
        font-weight: 600;
        border-radius: 9999px;
        transition: background 0.18s ease, transform 0.22s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.22s ease;
    }
    .kinetic-cta-pill:hover,
    .kinetic-cta-pill:focus-visible {
        background: #F6E3FB;
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 8px 22px rgba(236, 210, 243, 0.30);
    }
    @media (prefers-reduced-motion: reduce) {
        .kinetic-seq-stage {
            height: auto !important;
            min-height: 540px;
        }
        .kinetic-seq-sticky {
            position: relative !important;
            top: 0 !important;
            height: 540px !important;
        }
        .kinetic-seq-beat[data-seq-beat="0"] {
            opacity: 1 !important;
            transform: none !important;
            pointer-events: auto !important;
        }
    }
</style>
@endonce

@if($seqEnabled)
@php
    $seqConfigPayload = [
        'frames' => $seqFrames,
        'video_url' => $seqVideoUrl,
        'source' => $seq['source'] ?? 'fallback',
        'smoothing' => $seqSmoothing,
        'count' => count($seqFrames),
    ];
@endphp

<section id="k71-scroll-stage"
         class="kinetic-seq-stage border-b border-white/10"
         style="height: {{ $seqHeightVh }}vh;"
         data-section-id="{{ $sectionId ?? 'sec_scroll_sequence' }}"
         data-seq-stage>
    <script type="application/json" id="k71-seq-config">{!! json_encode($seqConfigPayload, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES) !!}</script>

    <div class="kinetic-seq-sticky">
        <canvas id="k71-seq-canvas"
                class="kinetic-seq-canvas"
                aria-label="{{ $storeTitle }} interactive scroll showcase"></canvas>

        {{-- Atmospheric Vignettes & High-Contrast Scrims --}}
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/88 via-black/24 to-black/72"></div>
        <div class="pointer-events-none hidden lg:block absolute inset-0 bg-gradient-to-r from-black/80 via-black/30 to-transparent"></div>
        <div class="pointer-events-none absolute top-0 left-1/3 w-[48vw] h-[280px] rounded-full bg-[radial-gradient(ellipse_at_center,rgba(236,210,243,0.14)_0%,rgba(201,162,74,0.06)_50%,transparent_75%)] blur-3xl"></div>

        <div class="relative z-10 h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 sm:pt-28 pb-5 sm:pb-8 flex flex-col justify-between pointer-events-none">

            {{-- Top Sequence Telemetry Strip --}}
            <div class="flex items-center justify-between gap-3">
                <div class="inline-flex items-center gap-2 sm:gap-2.5 px-3 sm:px-3.5 py-1.5 rounded-full bg-black/60 border border-white/15 backdrop-blur-xl text-[10px] sm:text-xs font-medium tracking-[0.14em] uppercase text-[#EFE9F2]/90">
                    <span class="w-2 h-2 rounded-full bg-[#ECD2F3] animate-pulse shrink-0"></span>
                    <span class="whitespace-nowrap" data-seq-badge>{{ $seqBadgeLabel }}</span>
                    <span class="text-white/25">&bull;</span>
                    <span class="font-mono text-[#C9A24A] tabular-nums whitespace-nowrap" data-seq-counter>
                        {{ !empty($seqFrames) ? sprintf('01 / %02d', count($seqFrames)) : ($seqVideoUrl !== '' ? '01 / 72' : '000°') }}
                    </span>
                </div>

                <a href="{{ $seqSkipHref }}"
                   class="pointer-events-auto inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-black/60 hover:bg-black/90 border border-white/15 text-[11px] font-medium text-[#EFE9F2]/80 hover:text-white backdrop-blur-xl transition cursor-pointer">
                    <span>Skip</span>
                    <span aria-hidden="true">&#8595;</span>
                </a>
            </div>

            {{-- Synchronized Story Beats --}}
            <div class="relative flex-1 flex items-end pb-5 sm:pb-10">
                <div class="relative w-full max-w-2xl min-h-[250px] sm:min-h-[280px]">
                    @foreach($seqBeats as $bIdx => $beat)
                        <div class="kinetic-seq-beat absolute inset-x-0 bottom-0 space-y-3 sm:space-y-4"
                             data-seq-beat="{{ $bIdx }}"
                             style="{{ $bIdx === 0 ? '--beat-o: 1; --beat-y: 0px; --beat-pe: auto;' : '--beat-o: 0; --beat-y: 26px; --beat-pe: none;' }}">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#ECD2F3]/14 border border-[#ECD2F3]/28 text-[#FFCDFF] text-[10px] sm:text-xs font-semibold uppercase tracking-[0.18em] backdrop-blur-md">
                                <span>{{ $beat['kicker'] }}</span>
                            </div>

                            @if($bIdx === 0 && $seqUseH1)
                                <h1 class="kinetic-display text-[clamp(2rem,4.5vw,4.1rem)] text-white drop-shadow-[0_2px_20px_rgba(0,0,0,0.88)]">
                                    {{ $beat['title'] }}
                                </h1>
                            @else
                                <h2 class="kinetic-display text-[clamp(2rem,4.5vw,4.1rem)] text-white drop-shadow-[0_2px_20px_rgba(0,0,0,0.88)]">
                                    {{ $beat['title'] }}
                                </h2>
                            @endif

                            <p class="text-xs sm:text-base text-[#EFE9F2]/85 max-w-xl leading-relaxed font-normal drop-shadow-[0_1px_12px_rgba(0,0,0,0.9)]">
                                {{ $beat['desc'] }}
                            </p>

                            @if($bIdx === 2)
                                <div class="flex flex-wrap items-center gap-3 pt-1 pointer-events-auto">
                                    <a href="{{ $seqCtaHref }}"
                                       class="kinetic-cta-pill inline-flex items-center gap-2.5 px-6 py-3 text-xs sm:text-sm tracking-wide cursor-pointer">
                                        <span>{{ $seqCtaLabel }}</span>
                                        <span aria-hidden="true">&rarr;</span>
                                    </a>
                                    <a href="{{ $seqSecondaryHref }}"
                                       class="inline-flex items-center gap-2 px-5 py-3 rounded-full bg-white/10 hover:bg-white/18 text-[#EFE9F2] font-semibold text-xs sm:text-sm border border-white/18 backdrop-blur-md transition cursor-pointer">
                                        <span>{{ $seqSecondaryLabel }}</span>
                                        <span aria-hidden="true">&#8599;</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Bottom Interactive Chapter Selector & Linear Progress Track --}}
            <div class="pointer-events-auto space-y-3 pt-2 border-t border-white/10">
                <div class="grid grid-cols-3 gap-2 sm:gap-4 text-left">
                    @foreach($seqBeats as $bIdx => $beat)
                        <button type="button"
                                data-seq-jump="{{ $bIdx }}"
                                class="group flex items-center justify-between gap-2 py-1 text-left transition cursor-pointer">
                            <div class="min-w-0">
                                <span class="block text-[9px] sm:text-[10px] font-mono uppercase tracking-[0.18em] text-[#C9A24A]">0{{ $bIdx + 1 }}</span>
                                <span class="block text-[11px] sm:text-xs font-medium text-[#EFE9F2]/70 group-hover:text-white truncate transition-colors"
                                      data-seq-tab-label="{{ $bIdx }}">{{ $beat['kicker'] }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>

                <div class="w-full h-[2px] rounded-full bg-white/15 overflow-hidden">
                    <div class="kinetic-seq-progress-fill w-full h-full bg-gradient-to-r from-[#C9A24A] via-[#ECD2F3] to-[#FFCDFF]"
                         data-seq-bar
                         style="--seq-p: 0;"></div>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
    (function () {
        const stageEl = document.getElementById('k71-scroll-stage');
        const canvas = document.getElementById('k71-seq-canvas');
        const configNode = document.getElementById('k71-seq-config');
        if (!stageEl || !canvas || !configNode) return;

        let seqConfig = { frames: [], video_url: '', source: 'fallback', smoothing: 0.14 };
        try {
            seqConfig = JSON.parse(configNode.textContent || '{}');
        } catch (e) {}

        const frameUrls = Array.isArray(seqConfig.frames) ? seqConfig.frames : [];
        const videoUrl = typeof seqConfig.video_url === 'string' ? seqConfig.video_url.trim() : '';
        const hasRealFrames = frameUrls.length > 0;
        const hasVideoStream = !hasRealFrames && videoUrl.length > 0;
        const totalFrames = hasRealFrames ? frameUrls.length : (hasVideoStream ? 72 : 120);
        const smoothingBase = Math.min(0.45, Math.max(0.04, Number(seqConfig.smoothing) || 0.14));
        const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const ctx = canvas.getContext('2d', { alpha: false });
        if (!ctx) return;

        const stickyEl = stageEl.querySelector('.kinetic-seq-sticky');
        const barEl = stageEl.querySelector('[data-seq-bar]');
        const counterEl = stageEl.querySelector('[data-seq-counter]');
        const beatEls = Array.from(stageEl.querySelectorAll('[data-seq-beat]'));
        const tabLabels = Array.from(stageEl.querySelectorAll('[data-seq-tab-label]'));
        const jumpButtons = Array.from(stageEl.querySelectorAll('[data-seq-jump]'));

        const clamp01 = (v) => Math.max(0, Math.min(1, v));
        const loadedFrames = hasRealFrames ? new Array(totalFrames).fill(null) : [];
        let loadedCount = 0;
        let prioritizeWindow = () => {};

        let cw = 0;
        let ch = 0;
        let dpr = 1;
        let currentProgress = 0;
        let targetProgress = 0;
        let rafId = null;
        let lastTs = 0;
        let lastDrawKey = '';

        // Optional video-scrubbing engine when video_url exists without JPG frames
        let videoEl = null;
        let videoReady = false;
        let videoSeeking = false;
        let desiredVideoTime = -1;

        if (hasVideoStream) {
            videoEl = document.createElement('video');
            videoEl.muted = true;
            videoEl.playsInline = true;
            videoEl.preload = 'auto';
            videoEl.crossOrigin = 'anonymous';
            videoEl.src = videoUrl;
            videoEl.addEventListener('loadeddata', () => {
                videoReady = true;
                lastDrawKey = '';
                drawFrameAt(currentProgress);
            });
            videoEl.addEventListener('seeked', () => {
                videoSeeking = false;
                videoReady = true;
                lastDrawKey = '';
                drawFrameAt(currentProgress);
                if (desiredVideoTime >= 0 && Math.abs(desiredVideoTime - videoEl.currentTime) > 0.04) {
                    const nextT = desiredVideoTime;
                    desiredVideoTime = -1;
                    try {
                        videoSeeking = true;
                        videoEl.currentTime = nextT;
                    } catch (err) {
                        videoSeeking = false;
                    }
                }
            });
        }

        const resizeCanvas = () => {
            const box = stickyEl ? stickyEl.getBoundingClientRect() : canvas.getBoundingClientRect();
            const w = Math.max(300, Math.round(box.width || window.innerWidth));
            const h = Math.max(260, Math.round(box.height || window.innerHeight));
            dpr = Math.min(window.devicePixelRatio || 1, 2);
            cw = w;
            ch = h;
            const targetW = Math.round(w * dpr);
            const targetH = Math.round(h * dpr);
            if (canvas.width !== targetW || canvas.height !== targetH) {
                canvas.width = targetW;
                canvas.height = targetH;
            }
            lastDrawKey = '';
            drawFrameAt(currentProgress);
        };

        const drawCoverSource = (src, sw, sh, alpha) => {
            if (!src || !sw || !sh) return;
            const scale = Math.max(cw / sw, ch / sh);
            const dw = sw * scale;
            const dh = sh * scale;
            const dx = (cw - dw) * 0.5;
            const dy = (ch - dh) * 0.5;
            if (alpha < 1) ctx.globalAlpha = alpha;
            ctx.drawImage(src, dx, dy, dw, dh);
            if (alpha < 1) ctx.globalAlpha = 1;
        };

        const drawCoverImage = (img, alpha) => {
            if (!img || !img.naturalWidth || !img.naturalHeight) return;
            drawCoverSource(img, img.naturalWidth, img.naturalHeight, alpha);
        };

        const findNearestLoaded = (idx) => {
            if (loadedFrames[idx]) return { idx, img: loadedFrames[idx] };
            for (let offset = 1; offset < totalFrames; offset++) {
                const fwd = idx + offset;
                if (fwd < totalFrames && loadedFrames[fwd]) {
                    return { idx: fwd, img: loadedFrames[fwd] };
                }
                const bwd = idx - offset;
                if (bwd >= 0 && loadedFrames[bwd]) {
                    return { idx: bwd, img: loadedFrames[bwd] };
                }
            }
            return null;
        };

        const studioDust = Array.from({ length: 34 }, (_, i) => ({
            x: ((i * 73) % 100) / 100,
            y: ((i * 41) % 100) / 100,
            z: 0.4 + ((i * 19) % 100) / 100,
            r: 0.9 + (i % 3) * 0.65,
            tint: i % 3 === 0 ? 'rgba(201,162,74,' : 'rgba(236,210,243,',
        }));

        const drawProceduralStudio = (p) => {
            ctx.fillStyle = '#020203';
            ctx.fillRect(0, 0, cw, ch);

            const isDesktop = cw >= 960;
            const focalX = isDesktop ? cw * 0.68 : cw * 0.54;
            const focalY = isDesktop ? ch * 0.49 : ch * 0.42;
            const radius = Math.min(cw, ch) * (isDesktop ? 0.30 : 0.28);

            const keyX = focalX + Math.cos(p * Math.PI * 1.5 - 0.8) * radius * 0.95;
            const keyY = focalY - Math.sin(p * Math.PI) * radius * 0.65;
            const aura = ctx.createRadialGradient(keyX, keyY, radius * 0.08, focalX, focalY, radius * 2.35);
            aura.addColorStop(0, 'rgba(236, 210, 243, 0.24)');
            aura.addColorStop(0.38, 'rgba(201, 162, 74, 0.12)');
            aura.addColorStop(0.75, 'rgba(34, 24, 38, 0.18)');
            aura.addColorStop(1, 'rgba(0, 0, 0, 0)');
            ctx.fillStyle = aura;
            ctx.fillRect(0, 0, cw, ch);

            ctx.save();
            ctx.translate(focalX, focalY);
            ctx.rotate(p * 0.22);
            ctx.strokeStyle = 'rgba(239, 233, 242, 0.06)';
            ctx.lineWidth = 1;
            [1.18, 1.48, 1.82].forEach((m, idx) => {
                ctx.beginPath();
                ctx.ellipse(0, 0, radius * m, radius * m * (0.42 + Math.sin(p * Math.PI) * 0.14), 0.28 - idx * 0.08, 0, Math.PI * 2);
                ctx.stroke();
            });
            ctx.restore();

            const yaw = -1.05 + p * 2.25;
            const pitch = 0.22 - Math.sin(p * Math.PI) * 0.14;
            const scalePulse = 0.96 + Math.sin(p * Math.PI) * 0.16;
            const fov = radius * 3.4;

            const cosY = Math.cos(yaw), sinY = Math.sin(yaw);
            const cosX = Math.cos(pitch), sinX = Math.sin(pitch);

            const project = (vx, vy, vz) => {
                const x1 = vx * cosY - vz * sinY;
                const z1 = vx * sinY + vz * cosY;
                const y2 = vy * cosX - z1 * sinX;
                const z2 = vy * sinX + z1 * cosX;
                const persp = fov / (fov + z2 + radius * 1.15);
                return {
                    x: focalX + x1 * persp * scalePulse,
                    y: focalY + y2 * persp * scalePulse,
                    z: z2,
                };
            };

            const h = radius * 0.96;
            const wTop = radius * 0.56;
            const wMid = radius * 0.78;
            const wBot = radius * 0.48;
            const dMid = radius * 0.24;
            const dCap = radius * 0.16;

            const slabRing = (rx, rz, y, cutX = 0.74, cutZ = 0.74) => [
                project(-rx * cutX, y, -rz),
                project(rx * cutX, y, -rz),
                project(rx, y, -rz * cutZ),
                project(rx, y, rz * cutZ),
                project(rx * cutX, y, rz),
                project(-rx * cutX, y, rz),
                project(-rx, y, rz * cutZ),
                project(-rx, y, -rz * cutZ),
            ];

            const rTop = slabRing(wTop, dCap, -h * 0.78);
            const rMid = slabRing(wMid, dMid, -h * 0.04);
            const rBot = slabRing(wBot, dCap * 1.1, h * 0.76);
            const sides = 8;
            const faces = [];

            const pushBand = (upper, lower, shine) => {
                for (let i = 0; i < sides; i++) {
                    const j = (i + 1) % sides;
                    const pts = [upper[i], upper[j], lower[j], lower[i]];
                    const cross = (pts[1].x - pts[0].x) * (pts[2].y - pts[0].y) - (pts[1].y - pts[0].y) * (pts[2].x - pts[0].x);
                    const avgZ = (pts[0].z + pts[1].z + pts[2].z + pts[3].z) * 0.25;
                    const dxCenter = (pts[0].x + pts[2].x) * 0.5 - keyX;
                    const lightDot = clamp01(0.68 - Math.abs(dxCenter) / (radius * 2.2));
                    faces.push({ pts, z: avgZ, front: cross > 0, isMain: (i === 0 || i === 4), light: lightDot * shine });
                }
            };

            pushBand(rTop, rMid, 1.0);
            pushBand(rMid, rBot, 0.84);
            const topCenterZ = rTop.reduce((acc, v) => acc + v.z, 0) / sides;
            faces.push({ pts: rTop, z: topCenterZ, front: true, isCap: true, light: 0.94 });
            faces.sort((a, b) => b.z - a.z);

            faces.forEach((f) => {
                if (!f.front && !f.isCap) return;
                ctx.beginPath();
                ctx.moveTo(f.pts[0].x, f.pts[0].y);
                for (let k = 1; k < f.pts.length; k++) {
                    ctx.lineTo(f.pts[k].x, f.pts[k].y);
                }
                ctx.closePath();

                if (f.isCap) {
                    const capGrad = ctx.createLinearGradient(focalX - wTop, focalY - h, focalX + wTop, focalY);
                    capGrad.addColorStop(0, 'rgba(236, 210, 243, 0.36)');
                    capGrad.addColorStop(0.5, 'rgba(34, 24, 38, 0.90)');
                    capGrad.addColorStop(1, 'rgba(201, 162, 74, 0.32)');
                    ctx.fillStyle = capGrad;
                } else if (f.isMain) {
                    const gx = f.pts[0].x, gy = f.pts[0].y;
                    const gx2 = f.pts[2].x, gy2 = f.pts[2].y;
                    const faceGrad = ctx.createLinearGradient(gx, gy, gx2, gy2);
                    const l = f.light;
                    faceGrad.addColorStop(0, `rgba(${Math.round(28 + l * 64)}, ${Math.round(22 + l * 46)}, ${Math.round(36 + l * 74)}, 0.94)`);
                    faceGrad.addColorStop(0.48, `rgba(${Math.round(46 + l * 58)}, ${Math.round(36 + l * 42)}, ${Math.round(54 + l * 62)}, 0.94)`);
                    faceGrad.addColorStop(1, `rgba(${Math.round(22 + l * 38)}, ${Math.round(18 + l * 28)}, ${Math.round(26 + l * 40)}, 0.94)`);
                    ctx.fillStyle = faceGrad;
                } else {
                    const l = f.light;
                    ctx.fillStyle = `rgba(${Math.round(20 + l * 42)}, ${Math.round(16 + l * 28)}, ${Math.round(24 + l * 44)}, 0.92)`;
                }
                ctx.fill();
                ctx.strokeStyle = f.light > 0.56 ? 'rgba(236, 210, 243, 0.42)' : 'rgba(201, 162, 74, 0.24)';
                ctx.lineWidth = 1.1;
                ctx.stroke();
            });

            studioDust.forEach((d, idx) => {
                const drift = (p * (0.4 + d.z * 0.7) + d.x) % 1;
                const px = cw * drift;
                const py = ch * ((d.y + Math.sin(p * Math.PI * 2 + idx) * 0.04 + 1) % 1);
                const alpha = (0.14 + 0.28 * Math.abs(Math.sin(p * Math.PI + idx))).toFixed(3);
                ctx.fillStyle = d.tint + alpha + ')';
                ctx.beginPath();
                ctx.arc(px, py, d.r, 0, Math.PI * 2);
                ctx.fill();
            });
        };

        const drawFrameAt = (p) => {
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

            if (hasVideoStream && videoEl) {
                const dur = Number(videoEl.duration);
                if (Number.isFinite(dur) && dur > 0) {
                    const targetT = clamp01(p) * dur;
                    if (!videoSeeking && Math.abs(targetT - videoEl.currentTime) > 0.03) {
                        videoSeeking = true;
                        try {
                            videoEl.currentTime = targetT;
                        } catch (e) {
                            videoSeeking = false;
                        }
                    } else if (videoSeeking) {
                        desiredVideoTime = targetT;
                    }
                    if (videoReady && videoEl.videoWidth > 0 && videoEl.videoHeight > 0) {
                        const key = `vid_${Math.round(p * 240)}_${cw}x${ch}`;
                        if (key === lastDrawKey) return;
                        lastDrawKey = key;
                        ctx.fillStyle = '#000000';
                        ctx.fillRect(0, 0, cw, ch);
                        drawCoverSource(videoEl, videoEl.videoWidth, videoEl.videoHeight, 1);
                        return;
                    }
                }
            }

            if (!hasRealFrames) {
                const key = `proc_${Math.round(p * 1000)}_${cw}x${ch}`;
                if (key === lastDrawKey) return;
                lastDrawKey = key;
                drawProceduralStudio(p);
                return;
            }

            const exact = p * Math.max(0, totalFrames - 1);
            const lo = Math.floor(exact);
            const hi = Math.min(totalFrames - 1, lo + 1);
            const frac = exact - lo;
            const nearestIdx = Math.min(totalFrames - 1, Math.max(0, Math.round(exact)));
            const key = `real_${lo}_${hi}_${Math.round(frac * 20)}_${loadedCount}_${cw}x${ch}`;
            if (key === lastDrawKey) return;
            lastDrawKey = key;

            ctx.fillStyle = '#000000';
            ctx.fillRect(0, 0, cw, ch);

            const imgLo = loadedFrames[lo];
            const imgHi = loadedFrames[hi];

            if (!loadedFrames[nearestIdx]) {
                prioritizeWindow(nearestIdx);
            }

            if (imgLo && imgHi && lo !== hi && frac > 0.03 && frac < 0.97) {
                drawCoverImage(imgLo, 1);
                drawCoverImage(imgHi, frac);
            } else {
                const best = loadedFrames[nearestIdx]
                    ? { idx: nearestIdx, img: loadedFrames[nearestIdx] }
                    : findNearestLoaded(nearestIdx);
                if (best) {
                    drawCoverImage(best.img, 1);
                } else {
                    drawProceduralStudio(p);
                }
            }
        };

        const updateOverlayUI = (p) => {
            if (barEl) {
                barEl.style.setProperty('--seq-p', p.toFixed(4));
            }

            if (counterEl) {
                if (hasRealFrames || hasVideoStream) {
                    const frameNum = Math.min(totalFrames, Math.max(1, Math.round(p * (totalFrames - 1)) + 1));
                    counterEl.textContent = `${String(frameNum).padStart(2, '0')} / ${String(totalFrames).padStart(2, '0')}`;
                } else {
                    const deg = Math.round(p * 360);
                    counterEl.textContent = `${String(deg).padStart(3, '0')}°`;
                }
            }

            const computeBeatOpacity = (idx, val) => {
                if (idx === 0) {
                    if (val <= 0.24) return 1;
                    if (val >= 0.36) return 0;
                    return 1 - (val - 0.24) / 0.12;
                }
                if (idx === 1) {
                    if (val <= 0.28 || val >= 0.72) return 0;
                    if (val < 0.38) return (val - 0.28) / 0.10;
                    if (val > 0.62) return 1 - (val - 0.62) / 0.10;
                    return 1;
                }
                if (val <= 0.64) return 0;
                if (val >= 0.74) return 1;
                return (val - 0.64) / 0.10;
            };

            let strongestIdx = 0;
            let strongestVal = -1;
            beatEls.forEach((el, idx) => {
                const op = clamp01(computeBeatOpacity(idx, p));
                if (op > strongestVal) {
                    strongestVal = op;
                    strongestIdx = idx;
                }
                const yOffset = (1 - op) * (idx === 0 ? -16 : 22);
                el.style.setProperty('--beat-o', op.toFixed(4));
                el.style.setProperty('--beat-y', `${yOffset.toFixed(2)}px`);
                el.style.setProperty('--beat-pe', op > 0.45 ? 'auto' : 'none');
            });

            tabLabels.forEach((lbl, idx) => {
                lbl.style.color = idx === strongestIdx ? '#FFFFFF' : 'rgba(239, 233, 242, 0.55)';
            });
        };

        const computeTargetProgress = () => {
            const rect = stageEl.getBoundingClientRect();
            const stickyTop = stickyEl ? (parseFloat(getComputedStyle(stickyEl).top) || 0) : 0;
            const stickyH = (stickyEl ? stickyEl.offsetHeight : (window.innerHeight - stickyTop)) || 600;
            const scrollSpan = Math.max(1, rect.height - stickyH);
            const elapsed = stickyTop - rect.top;
            return clamp01(elapsed / scrollSpan);
        };

        const tick = (ts) => {
            const dt = lastTs ? Math.min(40, Math.max(4, ts - lastTs)) : 16.67;
            lastTs = ts;

            targetProgress = computeTargetProgress();
            const alpha = reducedMotion ? 1 : (1 - Math.pow(1 - smoothingBase, dt / 16.67));
            const diff = targetProgress - currentProgress;

            if (Math.abs(diff) < 0.00035 || reducedMotion) {
                currentProgress = targetProgress;
                rafId = null;
            } else {
                currentProgress += diff * alpha;
                rafId = window.requestAnimationFrame(tick);
            }

            drawFrameAt(currentProgress);
            updateOverlayUI(currentProgress);
        };

        const kickLoop = () => {
            targetProgress = computeTargetProgress();
            if (!rafId) {
                lastTs = 0;
                rafId = window.requestAnimationFrame(tick);
            }
        };

        let streamingBegun = false;
        if (hasRealFrames) {
            const queue = [];
            const urgentQueue = [];
            const queuedSet = new Set();
            const startedSet = new Set();

            const pushIdx = (i) => {
                if (i >= 0 && i < totalFrames && !queuedSet.has(i)) {
                    queuedSet.add(i);
                    queue.push(i);
                }
            };
            pushIdx(0);
            pushIdx(totalFrames - 1);
            for (let k = 1; k < 16; k++) pushIdx(Math.round((k * (totalFrames - 1)) / 16));
            for (let stride = 8; stride >= 1; stride = Math.floor(stride / 2)) {
                for (let i = 0; i < totalFrames; i += stride) pushIdx(i);
                if (stride === 1) break;
            }

            const fetchFrame = (idx, onDone) => {
                const img = new Image();
                img.decoding = 'async';
                img.onload = () => {
                    const markReady = () => {
                        loadedFrames[idx] = img;
                        loadedCount++;
                        lastDrawKey = '';
                        drawFrameAt(currentProgress);
                        if (onDone) onDone();
                    };
                    if (typeof img.decode === 'function') {
                        img.decode().then(markReady).catch(markReady);
                    } else {
                        markReady();
                    }
                };
                img.onerror = () => {
                    if (onDone) onDone();
                };
                img.src = frameUrls[idx];
            };

            let cursor = 0;
            const nextFrameIndex = () => {
                while (urgentQueue.length > 0) {
                    const ui = urgentQueue.shift();
                    if (!startedSet.has(ui) && !loadedFrames[ui]) {
                        startedSet.add(ui);
                        return ui;
                    }
                }
                while (cursor < queue.length) {
                    const qi = queue[cursor++];
                    if (!startedSet.has(qi) && !loadedFrames[qi]) {
                        startedSet.add(qi);
                        return qi;
                    }
                }
                return -1;
            };

            const loadNext = () => {
                const idx = nextFrameIndex();
                if (idx === -1) return;
                fetchFrame(idx, loadNext);
            };

            let workersStarted = 0;
            const totalConcurrency = Math.min(6, queue.length);
            const beginStreaming = () => {
                if (streamingBegun) return;
                streamingBegun = true;
                while (workersStarted < totalConcurrency) {
                    workersStarted++;
                    loadNext();
                }
            };

            prioritizeWindow = (centerIdx) => {
                const priority = [centerIdx];
                for (let offset = 1; offset <= 4; offset++) {
                    priority.push(centerIdx + offset, centerIdx - offset);
                }
                urgentQueue.length = 0;
                for (const candidate of priority) {
                    if (candidate >= 0 && candidate < totalFrames && !loadedFrames[candidate] && !startedSet.has(candidate)) {
                        urgentQueue.push(candidate);
                    }
                }
                if (!streamingBegun) {
                    beginStreaming();
                }
            };

            if (!startedSet.has(0)) {
                startedSet.add(0);
                fetchFrame(0, null);
            }

            if (document.readyState === 'complete') {
                setTimeout(beginStreaming, 20);
            } else {
                window.addEventListener('load', () => {
                    setTimeout(beginStreaming, 20);
                }, { once: true });
            }
        }

        jumpButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const beatIdx = Number(btn.getAttribute('data-seq-jump') || 0);
                const targetStops = [0.08, 0.50, 0.90];
                const pStop = targetStops[beatIdx] ?? 0;
                const rect = stageEl.getBoundingClientRect();
                const stageTopDoc = rect.top + window.scrollY;
                const stickyTop = stickyEl ? (parseFloat(getComputedStyle(stickyEl).top) || 0) : 0;
                const stickyH = (stickyEl ? stickyEl.offsetHeight : (window.innerHeight - stickyTop)) || 600;
                const scrollSpan = Math.max(1, rect.height - stickyH);
                const destY = Math.max(0, stageTopDoc - stickyTop + scrollSpan * pStop);
                window.scrollTo({ top: destY, behavior: reducedMotion ? 'auto' : 'smooth' });
            });
        });

        if ('ResizeObserver' in window && stickyEl) {
            new ResizeObserver(resizeCanvas).observe(stickyEl);
        } else {
            window.addEventListener('resize', resizeCanvas, { passive: true });
        }

        window.addEventListener('scroll', kickLoop, { passive: true });
        resizeCanvas();
        targetProgress = computeTargetProgress();
        currentProgress = targetProgress;
        drawFrameAt(currentProgress);
        updateOverlayUI(currentProgress);

        window.__k71ScrollSeq = {
            getState: () => ({
                mode: hasRealFrames ? 'frames' : (hasVideoStream ? 'video' : 'procedural'),
                totalFrames,
                loadedCount,
                progress: Number(currentProgress.toFixed(4)),
                targetProgress: Number(targetProgress.toFixed(4)),
                frameIndex: hasRealFrames ? Math.round(currentProgress * Math.max(0, totalFrames - 1)) : 0,
            }),
        };
    })();
</script>
@else
<section class="bg-[#000000] text-[#EFE9F2] pt-28 pb-12 border-b border-white/10" data-section-id="{{ $sectionId ?? 'sec_scroll_sequence' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row sm:items-end justify-between gap-6">
        <div class="space-y-2 max-w-2xl">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#ECD2F3]/14 border border-[#ECD2F3]/28 text-[#FFCDFF] text-[10px] font-semibold uppercase tracking-[0.18em]">
                Kinetic Obsidian Storefront
            </span>
            <h1 class="kinetic-display text-4xl sm:text-6xl text-white">{{ $storeTitle }}</h1>
            <p class="text-sm text-[#EFE9F2]/75">{{ $storeTag }}</p>
        </div>
        <a href="{{ $seqCtaHref }}" class="kinetic-cta-pill inline-flex items-center gap-2 px-6 py-3 text-xs sm:text-sm shrink-0">
            <span>{{ $seqCtaLabel }}</span>
            <span>&rarr;</span>
        </a>
    </div>
</section>
@endif
