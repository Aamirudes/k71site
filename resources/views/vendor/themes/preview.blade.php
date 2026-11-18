<x-layouts.vendor title="Preview: {{ $theme->name }}">
<div class="h-[calc(100vh-4rem)] flex flex-col bg-slate-100"
     x-data="{
        viewport: 'desktop',
        page: '{{ $initialPage }}',
        get frameUrl() {
            return '{{ route('vendor.themes.preview_frame', $theme->theme_id) }}?page=' + this.page;
        },
        get frameWidthClass() {
            if (this.viewport === 'mobile') return 'w-[390px] max-w-full';
            if (this.viewport === 'tablet') return 'w-[768px] max-w-full';
            return 'w-full';
        }
     }">

    {{-- Top Isolated Preview Control Bar --}}
    <div class="bg-white border-b border-slate-200 px-4 py-3 flex flex-wrap items-center justify-between gap-3 shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('vendor.themes.index') }}" class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50">
                &larr; Catalogue
            </a>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h1 class="text-sm font-extrabold text-slate-900 truncate">{{ $theme->name }}</h1>
                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold">{{ $theme->formatted_price }}</span>
                    @if($isLive)
                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">Live</span>
                    @elseif($isEntitled)
                        <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-[10px] font-bold">Entitled</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">Preview Only</span>
                    @endif
                </div>
                <p class="text-[11px] text-slate-500 truncate">Isolated preview · Your live storefront is completely untouched.</p>
            </div>
        </div>

        {{-- Page Switcher (Home / Collection / Product Detail) --}}
        @if(!$theme->is_legacy)
            <div class="inline-flex rounded-xl bg-slate-100 p-1 text-xs font-bold">
                <button type="button" @click="page = 'home'" :class="page === 'home' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" class="px-3 py-1.5 rounded-lg transition cursor-pointer">Homepage</button>
                <button type="button" @click="page = 'collection'" :class="page === 'collection' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" class="px-3 py-1.5 rounded-lg transition cursor-pointer">Collection</button>
                <button type="button" @click="page = 'product'" :class="page === 'product' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" class="px-3 py-1.5 rounded-lg transition cursor-pointer">Product Page</button>
            </div>
        @endif

        {{-- Viewport Switcher (Desktop / Tablet / Mobile) --}}
        <div class="inline-flex rounded-xl bg-slate-100 p-1 text-xs font-bold">
            <button type="button" @click="viewport = 'desktop'" :class="viewport === 'desktop' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" class="px-3 py-1.5 rounded-lg transition cursor-pointer">Desktop</button>
            <button type="button" @click="viewport = 'tablet'" :class="viewport === 'tablet' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" class="px-3 py-1.5 rounded-lg transition cursor-pointer">Tablet</button>
            <button type="button" @click="viewport = 'mobile'" :class="viewport === 'mobile' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" class="px-3 py-1.5 rounded-lg transition cursor-pointer">Mobile</button>
        </div>

        {{-- Context Action --}}
        <div class="flex items-center gap-2">
            @if($isEntitled)
                @if($theme->is_legacy)
                    <a href="{{ route('vendor.design.index') }}" class="px-4 py-2 rounded-xl bg-zinc-900 text-white text-xs font-bold hover:bg-zinc-800">Open Classic Studio</a>
                @else
                    <a href="{{ route('vendor.themes.customize', $theme->theme_id) }}" class="px-4 py-2 rounded-xl bg-zinc-900 text-white text-xs font-bold hover:bg-zinc-800">Customize Draft</a>
                @endif
            @elseif($activeRequest && $activeRequest->isPending())
                @if($whatsappUrl)
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700">WhatsApp Admin ({{ $activeRequest->reference_code }})</a>
                @else
                    <span class="px-3 py-2 rounded-xl bg-amber-100 text-amber-900 text-xs font-bold">Pending: {{ $activeRequest->reference_code }}</span>
                @endif
            @elseif($theme->canBeRequested())
                <form action="{{ route('vendor.themes.request', $theme->theme_id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 cursor-pointer">Request Access ({{ $theme->formatted_price }})</button>
                </form>
            @endif
        </div>
    </div>

    {{-- Responsive Isolated Iframe Stage --}}
    <div class="flex-1 overflow-auto p-3 sm:p-5 flex justify-center">
        <div :class="frameWidthClass" class="h-full bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden transition-all duration-300">
            <iframe :src="frameUrl" title="Isolated theme preview of {{ $theme->name }}" class="w-full h-full border-0"></iframe>
        </div>
    </div>
</div>
</x-layouts.vendor>
