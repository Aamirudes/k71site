<x-layouts.public title="Website In Draft Mode" :hideMainHeader="false" :hideFooter="false">
    <div class="min-h-[70vh] flex items-center justify-center px-4 py-16">
        <div class="max-w-lg w-full rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-8 sm:p-10 text-center shadow-sm space-y-6">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 flex items-center justify-center text-2xl">
                🔒
            </div>

            <div class="space-y-2">
                <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-widest bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300">
                    Unpublished Draft · 404
                </span>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-zinc-950 dark:text-white">
                    This portfolio website is currently private or still in draft mode
                </h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    @isset($vendor)
                        <strong>{{ $vendor->shop_name }}</strong> has not published their public personal website yet. If you are the owner, sign in and publish it from your K71 Provider Dashboard.
                    @else
                        This provider website is not publicly available right now.
                    @endisset
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                <a href="{{ route('services.index') }}"
                   class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-xs font-bold uppercase tracking-wider hover:opacity-90 transition">
                    Browse K71 Services
                </a>
                @auth
                    @if(isset($vendor) && auth()->user()->vendor?->id === $vendor->id)
                        <a href="{{ route('vendor.website.index') }}"
                           class="w-full sm:w-auto px-6 py-3 rounded-2xl border border-zinc-300 dark:border-zinc-700 text-zinc-800 dark:text-zinc-200 text-xs font-bold uppercase tracking-wider hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                            Open Provider Dashboard →
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</x-layouts.public>

