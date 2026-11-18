<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PREVIEW — {{ $theme->name }} · {{ $vendor->shop_name }}</title>
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="m-0 p-0 bg-zinc-950 text-zinc-100 h-screen overflow-hidden antialiased"
      x-data="{
          viewport: 'desktop',
          page: '{{ $initialPage }}',
          fullscreen: false,
          base: '{{ $signedFrameBase }}',
          get frameUrl() {
              const sep = this.base.includes('?') ? '&' : '?';
              return this.base + sep + 'page=' + this.page;
          },
          get frameContainerClass() {
              if (this.fullscreen || this.viewport === 'desktop') return 'w-full h-full rounded-none border-0';
              if (this.viewport === 'tablet') return 'w-[768px] max-w-full h-full rounded-2xl border border-zinc-700 shadow-2xl';
              return 'w-[390px] max-w-full h-full rounded-[2rem] border-4 border-zinc-800 shadow-2xl';
          }
      }">

    <div class="h-screen flex flex-col">

        {{-- Sleek Top Preview Bar (Hidden cleanly in Full-Screen Mode) --}}
        <header x-show="!fullscreen" x-transition
                class="bg-zinc-950/95 border-b border-zinc-800 px-3 sm:px-5 py-2.5 flex flex-wrap items-center justify-between gap-2.5 shrink-0 z-50">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="px-2.5 py-1 rounded-md bg-amber-400 text-zinc-950 text-[10px] font-black uppercase tracking-wider shrink-0">
                    PREVIEW — NOT LIVE
                </span>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-xs sm:text-sm font-extrabold text-white truncate">{{ $theme->name }}</span>
                        <span class="hidden sm:inline text-[11px] text-zinc-400 truncate">for {{ $vendor->shop_name }}</span>
                    </div>
                </div>
            </div>

            {{-- Page Switcher --}}
            @if(!$theme->is_legacy)
                <div class="inline-flex rounded-lg bg-zinc-900 border border-zinc-800 p-0.5 text-[11px] font-bold">
                    <button type="button" @click="page = 'home'" :class="page === 'home' ? 'bg-white text-zinc-950' : 'text-zinc-400 hover:text-white'" class="px-2.5 py-1.5 rounded-md transition cursor-pointer">Home</button>
                    <button type="button" @click="page = 'collection'" :class="page === 'collection' ? 'bg-white text-zinc-950' : 'text-zinc-400 hover:text-white'" class="px-2.5 py-1.5 rounded-md transition cursor-pointer">Collection</button>
                    <button type="button" @click="page = 'product'" :class="page === 'product' ? 'bg-white text-zinc-950' : 'text-zinc-400 hover:text-white'" class="px-2.5 py-1.5 rounded-md transition cursor-pointer">Product</button>
                </div>
            @endif

            {{-- Responsive Viewport Controls + Full-Screen --}}
            <div class="inline-flex items-center rounded-lg bg-zinc-900 border border-zinc-800 p-0.5 text-[11px] font-bold">
                <button type="button" @click="viewport = 'desktop'" :class="viewport === 'desktop' ? 'bg-white text-zinc-950' : 'text-zinc-400 hover:text-white'" class="px-2.5 py-1.5 rounded-md transition cursor-pointer" title="Desktop viewport">Desktop</button>
                <button type="button" @click="viewport = 'tablet'" :class="viewport === 'tablet' ? 'bg-white text-zinc-950' : 'text-zinc-400 hover:text-white'" class="px-2.5 py-1.5 rounded-md transition cursor-pointer" title="Tablet 768px viewport">Tablet</button>
                <button type="button" @click="viewport = 'mobile'" :class="viewport === 'mobile' ? 'bg-white text-zinc-950' : 'text-zinc-400 hover:text-white'" class="px-2.5 py-1.5 rounded-md transition cursor-pointer" title="Mobile 390px viewport">Mobile</button>
                <button type="button" @click="fullscreen = true; viewport = 'desktop'" class="px-2.5 py-1.5 rounded-md text-zinc-300 hover:text-white transition cursor-pointer border-l border-zinc-800 ml-0.5" title="Full-screen preview">Full-Screen</button>
            </div>

            {{-- Return / Action Controls --}}
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ $returnUrl }}" class="px-3 py-1.5 rounded-lg border border-zinc-700 text-[11px] font-bold text-zinc-200 hover:bg-zinc-800 transition">
                    {{ !empty($isAdminPreview) ? 'Back to Admin' : 'Marketplace' }}
                </a>
                @if(empty($isAdminPreview))
                    @if($isEntitled)
                        <a href="{{ $theme->is_legacy ? route('vendor.design.index') : route('vendor.themes.customize', $theme->theme_id) }}"
                           class="px-3.5 py-1.5 rounded-lg bg-white text-zinc-950 text-[11px] font-extrabold hover:bg-zinc-200 transition">
                            Customize Draft
                        </a>
                    @elseif($activeRequest && $activeRequest->isPending())
                        @if($whatsappUrl)
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="px-3.5 py-1.5 rounded-lg bg-emerald-500 text-zinc-950 text-[11px] font-extrabold hover:bg-emerald-400 transition">
                                WhatsApp ({{ $activeRequest->reference_code }})
                            </a>
                        @else
                            <span class="px-3 py-1.5 rounded-lg bg-amber-400/15 text-amber-300 border border-amber-400/30 text-[11px] font-bold">Pending: {{ $activeRequest->reference_code }}</span>
                        @endif
                    @elseif($theme->canBeRequested())
                        <form action="{{ route('vendor.themes.request', $theme->theme_id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-emerald-500 text-zinc-950 text-[11px] font-extrabold hover:bg-emerald-400 transition cursor-pointer">
                                Request Access · {{ $theme->formatted_price }}
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </header>

        {{-- Discreet Full-Screen Exit Pill --}}
        <div x-show="fullscreen" x-cloak class="fixed bottom-4 left-4 z-50 flex items-center gap-2 px-3.5 py-2 rounded-full bg-zinc-950/90 border border-zinc-700 shadow-2xl text-xs">
            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            <span class="font-bold text-amber-300">PREVIEW — NOT LIVE</span>
            <button type="button" @click="fullscreen = false" class="ml-1 px-2.5 py-1 rounded-full bg-white text-zinc-950 font-extrabold text-[11px] cursor-pointer hover:bg-zinc-200">
                Exit Full-Screen
            </button>
        </div>

        {{-- Isolated Responsive Preview Canvas --}}
        <main class="flex-1 overflow-auto flex justify-center transition-all duration-300" :class="(!fullscreen && viewport !== 'desktop') ? 'p-4 sm:p-6 bg-zinc-900' : 'p-0 bg-white'">
            <div :class="frameContainerClass" class="bg-white overflow-hidden transition-all duration-300">
                <iframe :src="frameUrl"
                        title="Isolated theme preview: {{ $theme->name }}"
                        class="w-full h-full border-0 bg-white"></iframe>
            </div>
        </main>
    </div>
</body>
</html>
