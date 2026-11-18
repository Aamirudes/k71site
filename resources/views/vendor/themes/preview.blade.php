<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DRAFT PREVIEW — {{ $theme->name }} · {{ $vendor->shop_name }}</title>
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="m-0 p-0 bg-zinc-950 text-zinc-100 h-screen w-screen overflow-hidden antialiased"
      data-frame-base="{{ $signedFrameBase }}"
      x-data="{
          viewport: 'desktop',
          page: '{{ $initialPage }}',
          dockHidden: false,
          nativeFs: false,
          base: '',
          init() {
              this.base = this.$el.dataset.frameBase || '';
              document.addEventListener('fullscreenchange', () => {
                  this.nativeFs = Boolean(document.fullscreenElement);
              });
          },
          get frameUrl() {
              if (!this.base) return 'about:blank';
              const sep = this.base.includes('?') ? '&' : '?';
              return this.base + sep + 'page=' + this.page;
          },
          toggleNativeFs() {
              if (!document.fullscreenElement && document.documentElement.requestFullscreen) {
                  document.documentElement.requestFullscreen().catch(() => {});
              } else if (document.fullscreenElement && document.exitFullscreen) {
                  document.exitFullscreen().catch(() => {});
              }
          },
          get frameContainerClass() {
              if (this.viewport === 'desktop') return 'w-full h-full rounded-none border-0';
              if (this.viewport === 'tablet') return 'w-[768px] max-w-full h-[94vh] rounded-2xl border border-zinc-700 shadow-2xl';
              return 'w-[390px] max-w-full h-[92vh] rounded-[2.25rem] border-[6px] border-zinc-800 shadow-2xl';
          }
      }">

    {{-- TRUE FULL-VIEWPORT 100vw x 100vh STOREFRONT CANVAS BY DEFAULT --}}
    <main class="w-screen h-screen flex items-center justify-center transition-all duration-300"
          :class="viewport !== 'desktop' ? 'p-4 sm:p-6 bg-zinc-900' : 'p-0 bg-white'">
        <div :class="frameContainerClass" class="bg-white overflow-hidden transition-all duration-300">
            <iframe :src="frameUrl"
                    title="Fullscreen draft preview: {{ $theme->name }}"
                    class="w-full h-full border-0 bg-white"></iframe>
        </div>
    </main>

    {{-- SECONDARY FLOATING BOTTOM PREVIEW DOCK (Does not cut off the storefront hero) --}}
    <aside x-show="!dockHidden" x-transition
           class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50 max-w-[96vw] flex flex-wrap items-center justify-center gap-2 px-3 py-2 rounded-2xl bg-zinc-950/92 backdrop-blur-xl border border-white/15 shadow-[0_18px_50px_rgba(0,0,0,0.55)] text-zinc-100">

        {{-- Draft Badge, Instant Prev/Next Studio Theme Switcher & Quick-Jump --}}
        <div class="flex items-center gap-1.5 pr-2 border-r border-white/10">
            <span class="px-2 py-0.5 rounded bg-amber-400 text-zinc-950 text-[10px] font-black uppercase tracking-wider shrink-0">
                DRAFT PREVIEW — NOT LIVE
            </span>
            @if(!empty($switcherThemes) && !empty($prevPreviewUrl) && !empty($nextPreviewUrl))
                <a href="{{ $prevPreviewUrl }}" title="Previous theme (Shortcut: [)"
                   class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-bold inline-flex items-center justify-center transition">&larr;</a>
                <select aria-label="Switch preview theme"
                        onchange="if(this.value) window.location.href=this.value;"
                        class="bg-zinc-900 text-white text-xs font-extrabold rounded-lg px-2 py-1 border border-white/15 max-w-[165px] cursor-pointer focus:outline-none">
                    @foreach($switcherThemes as $sw)
                        <option value="{{ $sw['url'] }}" {{ $sw['id'] === $theme->theme_id ? 'selected' : '' }}>
                            #{{ $sw['num'] }} {{ $sw['name'] }}
                        </option>
                    @endforeach
                </select>
                <a href="{{ $nextPreviewUrl }}" title="Next theme (Shortcut: ])"
                   class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-bold inline-flex items-center justify-center transition">&rarr;</a>
            @else
                <span class="text-xs font-extrabold text-white truncate max-w-[140px]">{{ $theme->name }}</span>
            @endif
        </div>

        {{-- Page Switcher (Home / Collection / Product) --}}
        @if(!$theme->is_legacy)
            <div class="inline-flex rounded-lg bg-white/5 border border-white/10 p-0.5 text-[11px] font-bold">
                <button type="button" @click="page = 'home'" :class="page === 'home' ? 'bg-white text-zinc-950' : 'text-zinc-300 hover:text-white'" class="px-2.5 py-1 rounded-md transition cursor-pointer">Home</button>
                <button type="button" @click="page = 'collection'" :class="page === 'collection' ? 'bg-white text-zinc-950' : 'text-zinc-300 hover:text-white'" class="px-2.5 py-1 rounded-md transition cursor-pointer">Collection</button>
                <button type="button" @click="page = 'product'" :class="page === 'product' ? 'bg-white text-zinc-950' : 'text-zinc-300 hover:text-white'" class="px-2.5 py-1 rounded-md transition cursor-pointer">Product</button>
            </div>
        @endif

        {{-- Optional Responsive Device Mode Controls (Default = Full Desktop Viewport) --}}
        <div class="inline-flex items-center rounded-lg bg-white/5 border border-white/10 p-0.5 text-[11px] font-bold">
            <button type="button" @click="viewport = 'desktop'" :class="viewport === 'desktop' ? 'bg-white text-zinc-950' : 'text-zinc-300 hover:text-white'" class="px-2.5 py-1 rounded-md transition cursor-pointer">Desktop</button>
            <button type="button" @click="viewport = 'tablet'" :class="viewport === 'tablet' ? 'bg-white text-zinc-950' : 'text-zinc-300 hover:text-white'" class="px-2.5 py-1 rounded-md transition cursor-pointer">Tablet</button>
            <button type="button" @click="viewport = 'mobile'" :class="viewport === 'mobile' ? 'bg-white text-zinc-950' : 'text-zinc-300 hover:text-white'" class="px-2.5 py-1 rounded-md transition cursor-pointer">Mobile</button>
            <button type="button" @click="toggleNativeFs()" class="hidden sm:inline-flex px-2 py-1 rounded-md text-zinc-300 hover:text-white border-l border-white/10 ml-0.5 cursor-pointer" :title="nativeFs ? 'Exit browser fullscreen' : 'Browser fullscreen'">
                <span x-text="nativeFs ? 'Exit FS' : 'FS'"></span>
            </button>
        </div>

        {{-- Contextual Commerce / Access Action + Exit Preview --}}
        <div class="flex items-center gap-1.5 pl-1 border-l border-white/10">
            @if(empty($isAdminPreview))
                @if($isEntitled)
                    <a href="{{ $theme->is_legacy ? route('vendor.design.index') : route('vendor.themes.customize', $theme->theme_id) }}"
                       class="px-3 py-1.5 rounded-lg bg-white text-zinc-950 text-[11px] font-extrabold hover:bg-zinc-200 transition">
                        Customize
                    </a>
                @elseif($activeRequest && $activeRequest->isPending())
                    @if($whatsappUrl)
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener"
                           class="px-3 py-1.5 rounded-lg bg-emerald-500 text-zinc-950 text-[11px] font-extrabold hover:bg-emerald-400 transition">
                            WhatsApp
                        </a>
                    @else
                        <span class="px-2.5 py-1 rounded-lg bg-amber-400/20 text-amber-300 text-[11px] font-bold">Pending</span>
                    @endif
                @elseif($theme->canBeRequested())
                    <form action="{{ route('vendor.themes.request', $theme->theme_id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-500 text-zinc-950 text-[11px] font-extrabold hover:bg-emerald-400 transition cursor-pointer">
                            Request · {{ $theme->formatted_price }}
                        </button>
                    </form>
                @endif
            @endif

            <a href="{{ $returnUrl }}"
               class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-[11px] font-bold transition">
                Exit Preview
            </a>
            <button type="button" @click="dockHidden = true"
                    class="px-2 py-1.5 rounded-lg text-zinc-400 hover:text-white text-[11px] font-bold cursor-pointer"
                    title="Hide preview toolbar">
                Hide
            </button>
        </div>
    </aside>

    {{-- ULTRA-MINIMAL CORNER PILL WHEN DOCK IS HIDDEN (100% PURE PIXELS) --}}
    <div x-show="dockHidden" x-cloak class="fixed bottom-4 right-4 z-50 flex items-center gap-2 px-3 py-1.5 rounded-full bg-zinc-950/85 backdrop-blur-md border border-white/15 shadow-2xl text-[11px]">
        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
        <span class="font-bold text-zinc-200">Draft Preview</span>
        <button type="button" @click="dockHidden = false" class="px-2 py-0.5 rounded-full bg-white text-zinc-950 font-extrabold cursor-pointer">Controls</button>
        <a href="{{ $returnUrl }}" class="text-zinc-300 hover:text-white font-semibold">Exit</a>
    </div>

    @if(!empty($prevPreviewUrl) && !empty($nextPreviewUrl))
        <script>
            document.addEventListener('keydown', function (e) {
                if (e.target && ['INPUT', 'SELECT', 'TEXTAREA'].includes(e.target.tagName)) return;
                if (e.key === '[') window.location.href = @json($prevPreviewUrl);
                if (e.key === ']') window.location.href = @json($nextPreviewUrl);
            });
        </script>
    @endif
</body>
</html>
