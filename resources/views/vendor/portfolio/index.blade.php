<x-layouts.vendor title="Portfolio Case Studies">
    <div class="max-w-7xl mx-auto space-y-6 pb-12">

        <div class="bg-white rounded-2xl border border-zinc-200 p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="text-[11px] font-mono uppercase tracking-wider text-zinc-500 font-bold">Proof of Work</div>
                <h1 class="text-2xl font-black text-zinc-950 tracking-tight mt-0.5">Portfolio Case Studies</h1>
                <p class="text-xs text-zinc-500 mt-1">Showcase real client outcomes on your personal website and attach case studies directly to Upwork-style project proposals.</p>
            </div>

            <a href="{{ route('vendor.portfolio.create') }}"
               class="px-5 py-2.5 rounded-xl bg-black hover:bg-zinc-800 text-white text-xs font-bold shrink-0">
                + Add Case Study
            </a>
        </div>

        @if($projects->isEmpty())
            <div class="p-12 text-center rounded-2xl bg-white border border-zinc-200 space-y-4">
                <div class="text-3xl">🎨</div>
                <h3 class="text-base font-bold text-zinc-900">No Case Studies Added Yet</h3>
                <p class="text-xs text-zinc-500 max-w-md mx-auto">Publish rich portfolio studies with cover imagery, deliverables, role details, and results to build high client trust.</p>
                <a href="{{ route('vendor.portfolio.create') }}" class="inline-block px-5 py-2.5 rounded-xl bg-black text-white text-xs font-bold">
                    Create First Case Study
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($projects as $proj)
                    @php
                        $coverUrl = $proj->cover_image_url;
                    @endphp
                    <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden flex flex-col justify-between">
                        <div>
                            <div class="h-44 bg-zinc-100 border-b border-zinc-200 overflow-hidden relative">
                                @if($coverUrl)
                                    <img src="{{ $coverUrl }}" alt="{{ $proj->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-xs font-mono text-zinc-400">NO COVER IMAGE</div>
                                @endif

                                <div class="absolute top-3 left-3 flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $proj->status === 'published' ? 'bg-black text-white' : 'bg-white text-zinc-700 border border-zinc-300' }}">
                                        {{ $proj->status }}
                                    </span>
                                    @if($proj->is_featured)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-300 text-black">Featured</span>
                                    @endif
                                </div>
                            </div>

                            <div class="p-5 space-y-2">
                                <div class="text-[11px] font-mono text-zinc-500 uppercase">{{ $proj->work_category ?: ($proj->category?->name ?? 'Case Study') }}</div>
                                <h3 class="font-bold text-base text-zinc-950 leading-snug">{{ $proj->title }}</h3>
                                @if($proj->summary)
                                    <p class="text-xs text-zinc-600 line-clamp-2">{{ $proj->summary }}</p>
                                @endif
                                @if($proj->client_name)
                                    <div class="text-[11px] text-zinc-500">Client: <strong class="text-zinc-800">{{ $proj->client_name }}</strong></div>
                                @endif
                            </div>
                        </div>

                        <div class="p-4 border-t border-zinc-100 bg-zinc-50/50 flex flex-wrap items-center justify-between gap-2 text-[11px] font-bold">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('vendor.portfolio.edit', $proj) }}" class="px-2.5 py-1.5 rounded-lg border border-zinc-300 bg-white hover:bg-zinc-100 text-zinc-800">
                                    Edit
                                </a>
                                <form action="{{ route('vendor.portfolio.toggle_status', $proj) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-zinc-300 bg-white hover:bg-zinc-100 text-zinc-800 cursor-pointer">
                                        {{ $proj->status === 'published' ? 'Unpublish' : 'Publish' }}
                                    </button>
                                </form>
                                <form action="{{ route('vendor.portfolio.toggle_featured', $proj) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-zinc-300 bg-white hover:bg-zinc-100 text-zinc-800 cursor-pointer">
                                        {{ $proj->is_featured ? 'Unpin' : 'Feature' }}
                                    </button>
                                </form>
                            </div>

                            <div class="flex items-center gap-1.5">
                                @if($proj->status === 'published')
                                    <a href="{{ route('storefront.portfolio.show', [$vendor->slug, $proj->slug]) }}" target="_blank" class="px-2.5 py-1.5 rounded-lg border border-zinc-300 bg-white hover:bg-zinc-100 text-zinc-800">
                                        View ↗
                                    </a>
                                @endif
                                <form action="{{ route('vendor.portfolio.destroy', $proj) }}" method="POST"
                                      onsubmit="return confirm('Delete this portfolio case study?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 cursor-pointer">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div>{{ $projects->links() }}</div>
        @endif

    </div>
</x-layouts.vendor>

