<x-layouts.public title="My Listings & Services | K71 Customer Command" :fullWidth="true">
    <!-- Grail Obsidian Hero -->
    <section class="grail-hero-band text-[#EFE9F2] border-b border-white/10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-7 pb-10 sm:pt-10 sm:pb-12">
            <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between gap-4">
                <div>
                    <span class="grail-eyebrow block mb-2">Personal Listings &amp; Services</span>
                    <h1 class="grail-display text-[clamp(1.85rem,4vw,2.85rem)] font-bold text-white tracking-tight">My Listings &amp; Services</h1>
                    <p class="text-xs sm:text-sm text-white/65 mt-1 font-medium">
                        Manage your used classified ads (OLX style) and freelance service gigs (Upwork/Fiverr style).
                    </p>
                </div>
                
                <div class="flex items-center gap-2.5 flex-wrap">
                    <a href="{{ route('used.index') }}#post-ad" class="px-4 py-2.5 rounded-full bg-[#ECD2F3] hover:bg-white text-[#1A1018] font-bold text-xs shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                        <span>♻️ Sell Used Item</span>
                    </a>
                    <a href="{{ route('services.become_seller') }}" class="px-4 py-2.5 rounded-full bg-[#161616] hover:bg-white/15 text-white border border-white/20 font-bold text-xs shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                        <span>💼 Offer a Service</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Grail Stone Sheet -->
    <div class="grail-sheet -mt-4 sm:-mt-5 rounded-t-[28px] sm:rounded-t-[36px] min-h-screen">
        <div class="py-8 max-w-6xl mx-auto px-4 sm:px-6" x-data="{ activeTab: '{{ $activeTab }}' }">

        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between">
                <span>✓ {{ session('success') }}</span>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-700 font-bold cursor-pointer">✕</button>
            </div>
        @endif

        <!-- Tab Selector -->
        <div class="flex items-center gap-2 mb-6 border-b border-black/10 pb-3.5">
            <button type="button" @click="activeTab = 'used'"
                    :class="activeTab === 'used' ? 'bg-[#1A1018] text-[#ECD2F3] shadow-xs font-bold' : 'bg-white text-[#7A6B78] border border-black/8 hover:text-[#1A1018]'"
                    class="px-4 py-2 rounded-full text-xs transition cursor-pointer flex items-center gap-2">
                <span>♻️ Used Classifieds</span>
                <span class="px-2 py-0.2 rounded-full text-[10px] font-mono {{ $usedAds->count() > 0 ? 'bg-[#ECD2F3] text-[#1A1018]' : 'bg-black/10 text-[#7A6B78]' }}">
                    {{ $usedAds->count() }}
                </span>
            </button>
            <button type="button" @click="activeTab = 'services'"
                    :class="activeTab === 'services' ? 'bg-[#1A1018] text-[#ECD2F3] shadow-xs font-bold' : 'bg-white text-[#7A6B78] border border-black/8 hover:text-[#1A1018]'"
                    class="px-4 py-2 rounded-full text-xs transition cursor-pointer flex items-center gap-2">
                <span>💼 Freelance Gigs</span>
                <span class="px-2 py-0.2 rounded-full text-[10px] font-mono {{ $services->count() > 0 ? 'bg-[#ECD2F3] text-[#1A1018]' : 'bg-black/10 text-[#7A6B78]' }}">
                    {{ $services->count() }}
                </span>
            </button>
        </div>

        <!-- TAB 1: USED CLASSIFIED ADS (OLX STYLE) -->
        <div x-show="activeTab === 'used'" class="space-y-4">
            @forelse($usedAds as $ad)
                <div class="p-4 sm:p-5 rounded-[24px] bg-white border border-black/8 shadow-[0_8px_24px_rgba(26,16,24,0.04)] hover:border-black/20 transition flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-20 h-20 rounded-2xl bg-[#F4F1EC] overflow-hidden shrink-0 border border-black/8">
                            @if($ad->primary_image_url)
                                <img src="{{ $ad->primary_image_url }}" alt="{{ $ad->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-2xl">📦</div>
                            @endif
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $ad->is_sold ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $ad->is_sold ? 'SOLD' : 'ACTIVE' }}
                                </span>
                                <span class="text-[10px] text-[#7A6B78] font-mono">{{ $ad->category?->name ?? 'Classified' }}</span>
                            </div>
                            <h3 class="text-sm font-bold text-[#1A1018] mt-1">
                                <a href="{{ route('used.show', $ad->slug) }}" class="hover:text-[#623562]">{{ $ad->title }}</a>
                            </h3>
                            <div class="flex items-center gap-3 text-xs text-[#7A6B78] mt-1 font-medium">
                                <span class="font-mono font-black text-[#1A1018] text-sm tabular-nums">₹{{ number_format($ad->base_price / 100, 0) }}</span>
                                <span>•</span>
                                <span>📍 {{ $ad->custom_fields['location'] ?? 'Rajasthan' }}</span>
                                <span>•</span>
                                <span>Condition: {{ $ad->custom_fields['condition'] ?? 'Good' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end flex-wrap">
                        <!-- Toggle Sold -->
                        <form action="{{ route('used.toggle-sold', $ad) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-full border border-black/15 hover:bg-[#FAF8F5] text-[#1A1018] text-xs font-bold transition cursor-pointer">
                                {{ $ad->is_sold ? 'Mark Available' : 'Mark Sold' }}
                            </button>
                        </form>
                        <!-- Edit Ad -->
                        <a href="{{ route('used.edit', $ad) }}" class="px-4 py-1.5 rounded-full bg-[#1A1018] hover:bg-black text-[#ECD2F3] text-xs font-bold transition cursor-pointer">
                            Edit Ad ✏️
                        </a>
                        <!-- Delete Ad -->
                        <form action="{{ route('used.delete', $ad) }}" method="POST" onsubmit="return confirm('Delete this classified ad?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-xl text-rose-500 hover:bg-rose-50 transition cursor-pointer" title="Delete Ad">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-12 rounded-[24px] bg-white border border-black/8 text-center shadow-xs">
                    <div class="text-3xl mb-2">♻️</div>
                    <h3 class="grail-display text-lg font-bold text-[#1A1018]">No Used Ads Posted Yet</h3>
                    <p class="text-xs text-[#7A6B78] mt-1 max-w-sm mx-auto mb-5">
                        Sell pre-owned phones, bikes, furniture, cars, and gadgets directly to local verified buyers without any middleman fees.
                    </p>
                    <a href="{{ route('used.index') }}#post-ad" class="grail-btn-obsidian inline-flex items-center gap-1.5 text-xs">
                        Post Your First Classified Ad →
                    </a>
                </div>
            @endforelse
        </div>

        <!-- TAB 2: FREELANCE SERVICES & GIGS (UPWORK / FIVERR STYLE) -->
        <div x-show="activeTab === 'services'" class="space-y-6">
            
            @if($freelanceProfile)
                <!-- Freelancer Profile Header Card -->
                <div class="p-5 rounded-[24px] bg-white border border-black/8 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-[#1A1018] text-[#ECD2F3] grail-display font-bold flex items-center justify-center text-xl shadow-md shrink-0">
                            {{ strtoupper(substr($freelanceProfile->shop_name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-black text-[#1A1018]">{{ $freelanceProfile->shop_name }}</h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-[#1A1018] text-[#ECD2F3] text-[10px] font-bold">
                                    ★ {{ number_format($freelanceProfile->rating ?? 5.0, 1) }} Top Rated Pro
                                </span>
                            </div>
                            <p class="text-xs text-[#7A6B78] mt-0.5">{{ $freelanceProfile->tagline }}</p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('services.provider', $freelanceProfile->slug) }}" target="_blank" class="px-4 py-2 rounded-full border border-black/15 hover:bg-[#FAF8F5] text-[#1A1018] text-xs font-bold transition inline-flex items-center gap-1.5">
                            <span>View Public Pro Profile</span>
                            <span>↗</span>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Services Gigs Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($services as $gig)
                    <div class="grail-card p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#623562] bg-[#F8EFFA] px-2.5 py-0.5 rounded-full">
                                    {{ $gig->category?->name ?? 'Freelance Gig' }}
                                </span>
                                <span class="text-xs font-mono font-black text-[#1A1018] tabular-nums">
                                    From ₹{{ number_format($gig->base_price / 100, 0) }}
                                </span>
                            </div>
                            <h4 class="text-sm font-bold text-[#1A1018] line-clamp-2 leading-snug">
                                <a href="{{ route('services.show', $gig->slug) }}" class="hover:text-[#623562]">{{ $gig->title }}</a>
                            </h4>
                            <p class="text-xs text-[#7A6B78] mt-2 line-clamp-3 leading-relaxed">
                                {{ $gig->short_description ?: Str::limit($gig->description, 100) }}
                            </p>
                        </div>

                        <!-- 3-Tier Packages Indicator -->
                        <div class="mt-4 pt-3 border-t border-black/6">
                            <div class="flex items-center justify-between text-[11px] text-[#7A6B78] mb-3">
                                <span>3-Tier Gig Packages:</span>
                                <span class="font-bold text-[#1A1018]">{{ $gig->gigPackages->count() > 0 ? $gig->gigPackages->count() . ' Packages Active' : 'Basic Tier' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <a href="{{ route('services.gig.edit', $gig) }}" class="flex-1 text-center py-2 rounded-full bg-[#1A1018] hover:bg-black text-[#ECD2F3] text-xs font-bold transition cursor-pointer">
                                    Edit Gig ✏️
                                </a>
                                <form action="{{ route('services.gig.destroy', $gig) }}" method="POST" onsubmit="return confirm('Remove this service gig?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-xl text-rose-500 hover:bg-rose-50 transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full p-12 rounded-[24px] bg-white border border-black/8 text-center shadow-xs">
                        <div class="text-3xl mb-2">💼</div>
                        <h3 class="grail-display text-lg font-bold text-[#1A1018]">No Services Gigs Created Yet</h3>
                        <p class="text-xs text-[#7A6B78] mt-1 max-w-sm mx-auto mb-5">
                            Offer web development, UI/UX, AI modeling, 3D architectural rendering, video editing, or marble installation services to verified buyers worldwide.
                        </p>
                        <a href="{{ route('services.become_seller') }}" class="grail-btn-obsidian inline-flex items-center gap-1.5 text-xs">
                            Create Your First Freelance Gig →
                        </a>
                    </div>
                @endforelse
            </div>

        </div>

        </div>
    </div>
</x-layouts.public>

