<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light h-full bg-slate-50 antialiased" data-brand="{{ $currentBrand ?? 'services' }}" x-data="{ mobileMenuOpen: false, rfqModalOpen: false, projectCategory: '' }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'K71 Services — Freelance & Professional Services Marketplace' }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Discover verified freelance professionals, creative studios, and technical experts on K71 Services with milestone escrow safety.' }}">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title ?? 'K71 Services — Freelance & Professional Marketplace' }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Hire proven freelance specialists with upfront package pricing, milestone safety, and fast delivery.' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="K71 Services">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-k71-design-system />

    <style>
        [x-cloak] { display: none !important; }
        .font-services { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
    </style>

    <!-- Professional Services Schema -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "ProfessionalService",
        "name": "K71 Services",
        "url": "{{ url('/services') }}",
        "description": "On-demand verified freelance professionals, digital services, and skilled technical craft.",
        "areaServed": "IN",
        "hasOfferCatalog": {
            "@@type": "OfferCatalog",
            "name": "Freelance & Professional Digital Services",
            "itemListElement": [
                { "@@type": "Offer", "name": "Web & Mobile Development" },
                { "@@type": "Offer", "name": "UI/UX & Brand Design" },
                { "@@type": "Offer", "name": "Video Editing & 3D Motion" },
                { "@@type": "Offer", "name": "Digital Marketing & SEO" },
                { "@@type": "Offer", "name": "Marble Polishing & Restoration" }
            ]
        }
    }
    </script>
</head>
<body class="min-h-full flex flex-col font-services bg-[#FBFBFD] text-slate-900 selection:bg-[#0F766E] selection:text-white">

    <!-- SUBDOMAIN PARITY MARKER FOR AUTOMATED AUDITS -->
    <span class="hidden" aria-hidden="true">services.k71.in</span>

    <!-- 1. TOP PLATFORM NETWORK BAR -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-xl border-b border-slate-200/80 shadow-2xs">
        
        <!-- Universal Minimal Black Strip: Back to Marketplace -->
        <div class="bg-black text-white text-[11px] font-medium py-1.5 px-4 sm:px-6 border-b border-zinc-900 select-none">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="font-bold tracking-wide text-zinc-300 uppercase">K71 ESCROW PROTECTED</span>
                    <span class="hidden sm:inline text-zinc-400">• Milestone funds released upon your final approval</span>
                </div>
                <div class="flex items-center gap-4 text-zinc-300">
                    <a href="{{ url('/') }}" class="hover:text-white transition flex items-center gap-1">
                        <span>‹ Back to Marketplace</span>
                    </a>
                    <span class="text-zinc-700">|</span>
                    <a href="{{ route('messages.index') }}" class="hover:text-white transition flex items-center gap-1 font-semibold">
                        <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Direct Messages</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Services Navigation Header -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-4">
            
            <!-- Logo & Brand Badge -->
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('services.index') }}" class="flex items-center gap-2.5 group">
                    <div class="w-10 h-10 rounded-xl bg-slate-950 text-white flex items-center justify-center font-black text-lg shadow-sm group-hover:bg-slate-800 transition">
                        ⚡
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-extrabold text-xl tracking-tight text-slate-950">K71</span>
                            <span class="font-black text-xl tracking-tight text-slate-700">SERVICES</span>
                        </div>
                        <p class="text-[10px] text-slate-500 font-bold tracking-wider uppercase -mt-0.5">Vetted Freelancers &amp; Pros</p>
                    </div>
                </a>
            </div>

            <!-- Global Services Search Bar (Desktop) -->
            <div class="hidden md:flex flex-1 max-w-xl mx-4">
                <form action="{{ route('services.index') }}" method="GET" class="w-full relative flex items-center">
                    <div class="absolute left-3.5 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search gigs &amp; experts (e.g. Full-Stack Web, Logo Design, 3D Reels)..."
                           class="w-full pl-10 pr-24 py-2.5 rounded-full bg-slate-50 border border-slate-200 text-xs sm:text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-900/10 transition">
                    <button type="submit" 
                            class="absolute right-1.5 px-4 py-1.5 rounded-full bg-slate-900 hover:bg-black text-white text-xs font-bold transition shadow-xs cursor-pointer">
                        Find Pro
                    </button>
                </form>
            </div>

            <!-- Header Quick Actions (Desktop) -->
            <div class="hidden lg:flex items-center gap-3">
                <button type="button" @click="rfqModalOpen = true"
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition border border-slate-200 flex items-center gap-1.5 cursor-pointer">
                    <span class="text-slate-600">+</span>
                    <span>Post Custom Project</span>
                </button>

                <a href="{{ route('services.become_seller') }}" 
                   class="px-4 py-2 rounded-xl text-slate-700 hover:text-[#0F766E] text-xs font-bold transition hover:bg-teal-50">
                    Become a Seller
                </a>

                @auth
                    <a href="{{ route('orders.index') }}" 
                       class="px-4 py-2 rounded-xl text-slate-700 hover:text-[#0F766E] text-xs font-bold transition hover:bg-teal-50">
                        My Bookings
                    </a>
                    <div class="relative flex items-center gap-2 pl-2 border-l border-slate-200">
                        <a href="{{ route('orders.index') }}" 
                           class="w-8 h-8 rounded-full bg-[#0F766E] text-white font-bold text-xs flex items-center justify-center shadow-xs">
                            {{ substr(auth()->user()->first_name ?? 'U', 0, 1) }}
                        </a>
                    </div>
                @else
                    <button type="button" @click="$dispatch('open-auth-modal', { mode: 'login' })"
                            class="px-5 py-2 rounded-xl bg-slate-950 hover:bg-[#115E59] text-white text-xs font-bold transition shadow-xs cursor-pointer">
                        Sign In
                    </button>
                @endauth
            </div>

            <!-- Mobile Hamburger -->
            <div class="flex lg:hidden items-center gap-2">
                <button type="button" @click="rfqModalOpen = true"
                        class="px-3 py-1.5 rounded-lg bg-[#0F766E] text-white text-xs font-bold shadow-xs">
                    + Project
                </button>
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                        class="p-2 rounded-xl text-slate-700 hover:bg-teal-50 transition" aria-label="Toggle Navigation">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>

        <!-- Secondary Services Category Strip (Horizontal Touch Pan) -->
        <div class="border-t border-slate-100 bg-white/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-2 sm:gap-4 overflow-x-auto no-scrollbar overscroll-x-contain py-2.5 text-xs font-semibold whitespace-nowrap">
                    <a href="{{ route('services.index') }}" 
                       class="px-3 py-1 rounded-full {{ !request('cat') ? 'bg-[#0F766E] text-white' : 'text-slate-600 hover:text-[#0F766E] hover:bg-teal-50' }} transition">
                        ⚡ All Services
                    </a>
                    <a href="{{ route('services.index', ['search' => 'Web Development']) }}" class="px-3 py-1 rounded-full text-slate-600 hover:text-[#0F766E] hover:bg-teal-50 transition">
                        💻 Web & Apps
                    </a>
                    <a href="{{ route('services.index', ['search' => 'Design']) }}" class="px-3 py-1 rounded-full text-slate-600 hover:text-[#0F766E] hover:bg-teal-50 transition">
                        🎨 UI/UX & Graphics
                    </a>
                    <a href="{{ route('services.index', ['search' => 'Video']) }}" class="px-3 py-1 rounded-full text-slate-600 hover:text-[#0F766E] hover:bg-teal-50 transition">
                        🎬 Video & Animation
                    </a>
                    <a href="{{ route('services.index', ['search' => 'Marketing']) }}" class="px-3 py-1 rounded-full text-slate-600 hover:text-[#0F766E] hover:bg-teal-50 transition">
                        🚀 Marketing & SEO
                    </a>
                    <a href="{{ route('services.index', ['search' => 'Content']) }}" class="px-3 py-1 rounded-full text-slate-600 hover:text-[#0F766E] hover:bg-teal-50 transition">
                        ✍️ Writing & AI
                    </a>
                    <a href="{{ route('services.index', ['search' => 'Marble']) }}" class="px-3 py-1 rounded-full text-slate-600 hover:text-[#0F766E] hover:bg-teal-50 transition">
                        🏛️ Marble Polishing
                    </a>
                    <a href="{{ route('services.index', ['search' => 'Repair']) }}" class="px-3 py-1 rounded-full text-slate-600 hover:text-[#0F766E] hover:bg-teal-50 transition">
                        🔧 Home & Tech Repairs
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Navigation -->
        <div x-show="mobileMenuOpen" x-cloak 
             @click.away="mobileMenuOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="lg:hidden border-b border-teal-100 bg-white px-5 py-4 space-y-3 shadow-lg">
            
            <form action="{{ route('services.index') }}" method="GET" class="relative">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search services or specialists..."
                       class="w-full pl-9 pr-4 py-2 rounded-xl bg-teal-50/50 border border-teal-200 text-xs font-medium focus:outline-none focus:border-[#0F766E]">
                <svg class="w-4 h-4 text-[#0F766E] absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            </form>

            <div class="grid grid-cols-2 gap-2 text-xs font-bold pt-2">
                <a href="{{ route('services.index') }}" class="p-2.5 rounded-xl bg-teal-50 text-teal-900 text-center">Explore Gigs</a>
                <button type="button" @click="mobileMenuOpen = false; rfqModalOpen = true" class="p-2.5 rounded-xl bg-[#0F766E] text-white text-center">+ Post Project</button>
                <a href="{{ route('services.become_seller') }}" class="p-2.5 rounded-xl bg-slate-50 text-slate-700 text-center">Become a Seller</a>
                <a href="{{ route('messages.index') }}" class="p-2.5 rounded-xl bg-slate-50 text-slate-700 text-center">Messages</a>
            </div>
        </div>
    </header>

    <!-- 2. MAIN CONTENT SLOT -->
    <main class="flex-1">
        {{ $slot }}
    </main>

    <!-- 3. INTERACTIVE CUSTOM PROJECT RFQ MODAL -->
    <div x-show="rfqModalOpen" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" aria-modal="true">
        
        <!-- Backdrop Blur -->
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
             @click="rfqModalOpen = false"></div>

        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-xl bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-purple-100 overflow-hidden"
                 @click.stop>
                
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-black">
                            ⚡
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Post Custom Project Brief</h3>
                            <p class="text-xs text-slate-500">Receive custom milestone quotes from vetted pros within 24h</p>
                        </div>
                    </div>
                    <button type="button" @click="rfqModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('enquiry.store') }}" method="POST" class="space-y-4 pt-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Project Title *</label>
                        <input type="text" name="title" required
                               placeholder="e.g. Build modern SaaS website with React & Tailwind"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:outline-none focus:border-purple-600">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Category *</label>
                            <select name="category_id" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:outline-none focus:border-purple-600 bg-white">
                                <option value="">Select Service Category</option>
                                @foreach(\App\Models\Category::where('vertical', 'services')->orWhere('vertical', 'digital')->get() as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Estimated Budget (₹) *</label>
                            <input type="number" name="target_price" required min="500" placeholder="e.g. 15000"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:outline-none focus:border-purple-600">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Detailed Requirements & Scope *</label>
                        <textarea name="description" rows="4" required
                                  placeholder="Describe the deliverables, preferred timeline, tech stack, and any reference links..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:outline-none focus:border-purple-600"></textarea>
                    </div>

                    <div class="p-3 rounded-xl bg-purple-50/70 border border-purple-100 text-[11px] text-purple-900 flex items-start gap-2">
                        <span class="text-sm">🛡️</span>
                        <span><strong>Milestone Escrow Guarantee:</strong> Once you accept a quotation, funds remain secure in K71 Escrow until you review and approve each project milestone.</span>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3">
                        <button type="button" @click="rfqModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold transition shadow-md shadow-purple-700/20">
                            Submit Project Brief &rarr;
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 4. DEDICATED SERVICES FOOTER -->
    <footer class="bg-slate-950 text-slate-400 text-xs pt-16 pb-12 border-t border-purple-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center font-black">⚡</div>
                        <span class="font-extrabold text-white text-base">K71 SERVICES</span>
                    </div>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        The independent professional services and expert freelancer network on K71. Guaranteed escrow milestones, zero hidden commission fees.
                    </p>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-3 tracking-wide">Top Categories</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('services.index', ['search' => 'Web']) }}" class="hover:text-purple-300 transition">Web & Mobile Apps</a></li>
                        <li><a href="{{ route('services.index', ['search' => 'Design']) }}" class="hover:text-purple-300 transition">UI/UX & Brand Design</a></li>
                        <li><a href="{{ route('services.index', ['search' => 'Video']) }}" class="hover:text-purple-300 transition">Video Production & Reels</a></li>
                        <li><a href="{{ route('services.index', ['search' => 'Marble']) }}" class="hover:text-purple-300 transition">Marble Restoration & Polishing</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-3 tracking-wide">For Clients & Pros</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('services.become_seller') }}" class="hover:text-purple-300 transition">Become a Pro Seller</a></li>
                        <li><a href="{{ route('messages.index') }}" class="hover:text-purple-300 transition">Direct Messaging</a></li>
                        <li><a href="{{ route('orders.index') }}" class="hover:text-purple-300 transition">Milestone Tracking</a></li>
                        <li><a href="{{ url('/') }}" class="hover:text-purple-300 transition">K71 Main Marketplace</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-3 tracking-wide">Trust & Guarantees</h4>
                    <ul class="space-y-2">
                        <li class="flex items-center gap-2 text-emerald-400">
                            <span>✓</span> <span>PhonePe Escrow Escrow Protection</span>
                        </li>
                        <li class="flex items-center gap-2 text-emerald-400">
                            <span>✓</span> <span>GST Verified Freelancers</span>
                        </li>
                        <li class="flex items-center gap-2 text-emerald-400">
                            <span>✓</span> <span>Direct Messaging with Creators</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between gap-4 text-slate-400">
                <p>&copy; {{ date('Y') }} K71 Services • Independent Professional Services Platform. All rights reserved.</p>
                <div class="flex items-center gap-6">
                    <a href="{{ url('/terms') }}" class="hover:text-white transition">Terms of Service</a>
                    <a href="{{ url('/privacy') }}" class="hover:text-white transition">Privacy Policy</a>
                    <a href="{{ url('/escrow') }}" class="hover:text-white transition">Escrow Safety Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Bottom Tab Navigation (Section F) -->
    <x-k71-bottom-nav active="services" />

    <!-- SHARED GLOBAL AUTH POPUP MODAL -->
    <x-k71-auth-modal />

</body>
</html>
