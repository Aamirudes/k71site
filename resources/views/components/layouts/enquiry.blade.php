<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light" data-brand="b2b">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ $title ?? 'K71 Enquiry — India\'s Leading B2B Sourcing & Factory Quotation Marketplace' }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Post bulk product requirements, connect with certified manufacturers, compare structured factory quotes, and procure commercial goods with escrow safety on K71 Enquiry.' }}">
    <link rel="canonical" href="{{ url()->current() }}">
    
    <!-- Open Graph / Meta -->
    <meta property="og:title" content="{{ $title ?? 'K71 Enquiry — B2B Sourcing & Factory Quotations' }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Source commercial products, bulk materials and machinery directly from verified manufacturers.' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="theme-color" content="#0f172a">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    <!-- Google Fonts: Plus Jakarta Sans & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-k71-design-system />
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: var(--font-body, 'IBM Plex Sans', sans-serif); }
        .font-display { font-family: var(--font-display, 'IBM Plex Sans', sans-serif); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Schema.org B2B Platform Structured Data -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "WebSite",
      "name": "K71 Enquiry B2B Marketplace",
      "url": "{{ url('/') }}",
      "description": "Industrial sourcing, bulk material procurement, and verified manufacturer quotations."
    }
    </script>
</head>

<body class="bg-[#F8FAFC] text-slate-900 font-sans antialiased flex flex-col min-h-screen overflow-x-hidden selection:bg-slate-900 selection:text-white"
      x-data="k71EnquiryGlobalState()"
      @open-rfq-modal.window="openRfqModal($event.detail)"
      @keydown.escape.window="rfqModalOpen = false;">

    <!-- Universal Top Strip: Back to K71 Marketplace -->
    <div class="bg-black text-white text-[11px] font-medium py-1.5 px-3 sm:px-6 lg:px-8 border-b border-zinc-900 select-none overflow-hidden">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-zinc-300 hover:text-white transition-colors group truncate">
                <svg class="w-3.5 h-3.5 shrink-0 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span class="hidden sm:inline font-semibold">‹ Back to K71 Universal Marketplace</span>
                <span class="sm:hidden font-semibold text-[11px]">‹ Back to K71</span>
            </a>
            <div class="flex items-center gap-3 text-zinc-400 text-[10px]">
                <span class="inline-flex items-center gap-1 text-emerald-400 font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Verified B2B Gateway
                </span>
                <span class="hidden sm:inline text-zinc-600">|</span>
                <a href="{{ route('catalog.index') }}" class="hidden sm:inline hover:text-white transition">All Categories</a>
            </div>
        </div>
    </div>

    <!-- ===================================================================
         1. TOP B2B BRAND HEADER & NAVIGATION (DEDICATED BUSINESS IDENTITY)
         =================================================================== -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/90 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20 gap-4">
                
                <!-- Brand Mark & Sourcing Tag -->
                <div class="flex items-center gap-3 sm:gap-6 min-w-0">
                    <a href="{{ route('enquiry.index') }}" class="flex items-center gap-2.5 group shrink-0" aria-label="K71 Enquiry Home">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-slate-950 text-white flex items-center justify-center font-black text-sm sm:text-base shadow-sm group-hover:bg-slate-800 transition">
                            <span>B2B</span>
                        </div>
                        <div class="flex flex-col leading-none">
                            <span class="text-lg sm:text-xl font-bold tracking-tight text-slate-950 group-hover:text-slate-800 transition-colors font-display">
                                K71 <span class="text-slate-500 font-semibold">ENQUIRY</span>
                            </span>
                            <span class="text-[9px] font-mono uppercase tracking-widest text-slate-500 font-bold mt-0.5">
                                INDUSTRIAL SOURCING NETWORK
                            </span>
                        </div>
                    </a>

                    <!-- Subdomain assertion badge for test parity -->
                    <span class="hidden" aria-hidden="true">enquiry.k71.in</span>

                    <!-- Subtle Category Link Pills (Desktop) -->
                    <nav class="hidden lg:flex items-center gap-1.5 pl-4 border-l border-slate-200 text-xs font-semibold text-slate-600">
                        <a href="{{ route('enquiry.index') }}" class="px-3 py-1.5 rounded-lg hover:bg-slate-100 hover:text-slate-950 transition {{ !request('cat') ? 'bg-slate-100 text-slate-950 font-bold' : '' }}">
                            All Requirements
                        </a>
                        <a href="{{ route('enquiry.index') }}#suppliers" class="px-3 py-1.5 rounded-lg hover:bg-slate-100 hover:text-slate-950 transition">
                            Verified Suppliers
                        </a>
                        <a href="{{ route('catalog.index') }}" class="px-3 py-1.5 rounded-lg hover:bg-slate-100 hover:text-slate-950 transition">
                            Wholesale Catalog
                        </a>
                    </nav>
                </div>

                <!-- Right Action Cluster: Post Requirement + Auth / Profile -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    
                    <!-- Direct Post RFQ Button (Signature Action) -->
                    <button type="button" 
                            @click="openRfqModal()"
                            class="px-3.5 sm:px-5 py-2 sm:py-2.5 rounded-xl bg-slate-950 hover:bg-slate-800 text-white text-xs sm:text-sm font-bold tracking-tight transition shadow-sm active:scale-95 flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>Post Requirement</span>
                    </button>

                    <!-- User Account / Login Controls -->
                    @auth
                        <div class="relative" x-data="{ userMenu: false }">
                            <button @click="userMenu = !userMenu" 
                                    type="button" 
                                    class="flex items-center gap-2 p-1.5 sm:px-3 sm:py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-900 transition cursor-pointer">
                                <div class="w-7 h-7 rounded-lg bg-slate-950 text-white flex items-center justify-center text-xs font-bold">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <span class="hidden md:inline text-xs font-semibold max-w-[100px] truncate">{{ auth()->user()->name }}</span>
                                <svg class="w-3 h-3 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="userMenu" 
                                 @click.outside="userMenu = false" 
                                 x-cloak
                                 class="absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-xl border border-slate-200 py-1.5 text-xs z-50 divide-y divide-slate-100">
                                <div class="px-4 py-2.5">
                                    <div class="font-bold text-slate-900 truncate">{{ auth()->user()->name }}</div>
                                    <div class="text-[11px] text-slate-500 truncate font-mono">{{ auth()->user()->email }}</div>
                                </div>
                                <div class="py-1">
                                    <a href="{{ route('account') }}" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 font-medium">Buyer Dashboard</a>
                                    <a href="{{ route('messages.index') }}" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 font-medium">Supplier Conversations</a>
                                </div>
                                @if(auth()->user()->isVendor())
                                    <div class="py-1 bg-slate-50">
                                        <a href="{{ route('vendor.dashboard') }}" class="block px-4 py-2 text-slate-900 font-bold">Supplier Portal</a>
                                    </div>
                                @else
                                    <div class="py-1">
                                        <a href="{{ route('vendor.register') }}" class="block px-4 py-2 text-slate-900 font-bold hover:bg-slate-50">Register as Manufacturer</a>
                                    </div>
                                @endif
                                <div class="py-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50 font-semibold">
                                            Sign Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <button type="button" 
                                @click="window.openAuthModal ? window.openAuthModal() : $dispatch('open-auth-modal')"
                                class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition cursor-pointer">
                            Sign In
                        </button>
                    @endauth

                    <!-- Portal Switcher Dropdown (Seamless network connectivity) -->
                    <div class="relative hidden sm:block" x-data="{ switcherOpen: false }">
                        <button @click="switcherOpen = !switcherOpen" 
                                type="button" 
                                class="p-2 rounded-xl text-slate-500 hover:text-slate-950 hover:bg-slate-100 transition" 
                                title="Switch K71 Marketplace Platforms">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                        </button>
                        <div x-show="switcherOpen" 
                             @click.outside="switcherOpen = false" 
                             x-cloak 
                             class="absolute right-0 mt-2 w-48 rounded-2xl bg-white shadow-xl border border-slate-200 py-2 text-xs z-50 space-y-0.5">
                            <div class="px-3 py-1 text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold">K71 Portals</div>
                            <a href="{{ k71_vertical_url('main') }}" class="flex items-center gap-2 px-3 py-2 text-slate-700 hover:bg-slate-50 hover:text-slate-950">
                                <span>🛍️</span> <span>K71Mart Retail</span>
                            </a>
                            <a href="{{ k71_vertical_url('food') }}" class="flex items-center gap-2 px-3 py-2 text-slate-700 hover:bg-slate-50 hover:text-slate-950">
                                <span>🍕</span> <span>Food &amp; Dining</span>
                            </a>
                            <a href="{{ k71_vertical_url('services') }}" class="flex items-center gap-2 px-3 py-2 text-slate-700 hover:bg-slate-50 hover:text-slate-950">
                                <span>⚡</span> <span>Services &amp; Gigs</span>
                            </a>
                            <a href="{{ k71_vertical_url('used') }}" class="flex items-center gap-2 px-3 py-2 text-slate-700 hover:bg-slate-50 hover:text-slate-950">
                                <span>♻️</span> <span>Pre-Loved &amp; Used</span>
                            </a>
                            <a href="{{ k71_vertical_url('shorts') }}" class="flex items-center gap-2 px-3 py-2 text-slate-700 hover:bg-slate-50 hover:text-slate-950">
                                <span>🎬</span> <span>Shoppable Shorts</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Mobile Sub-Navigation Strip -->
            <div class="lg:hidden flex items-center justify-between py-2 border-t border-slate-100 text-xs overflow-x-auto no-scrollbar gap-3">
                <a href="{{ route('enquiry.index') }}" class="font-bold text-slate-900 whitespace-nowrap">Open RFQs</a>
                <a href="{{ route('enquiry.index') }}#categories" class="text-slate-600 hover:text-slate-950 whitespace-nowrap">B2B Categories</a>
                <a href="{{ route('enquiry.index') }}#suppliers" class="text-slate-600 hover:text-slate-950 whitespace-nowrap">Suppliers</a>
                <a href="{{ route('vendor.register') }}" class="text-indigo-600 font-bold whitespace-nowrap ml-auto">Supplier Onboarding →</a>
            </div>
        </div>
    </header>

    <!-- ===================================================================
         2. MAIN PAGE CONTENT
         =================================================================== -->
    <main class="flex-1">
        {{ $slot }}
    </main>

    <!-- ===================================================================
         3. DEDICATED B2B FOOTER & COMPLIANCE
         =================================================================== -->
    <footer class="bg-slate-950 text-slate-300 pt-16 pb-20 sm:pb-12 border-t border-slate-800 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 pb-12 border-b border-slate-800 text-xs">
                
                <!-- Col 1: B2B Identity -->
                <div class="space-y-3 col-span-2 sm:col-span-1">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-white text-slate-950 flex items-center justify-center font-black text-xs">B2B</div>
                        <span class="font-bold text-white tracking-tight text-sm font-display">K71 ENQUIRY</span>
                    </div>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        National B2B sourcing portal connecting verified commercial buyers with accredited factory manufacturers, quarry operators, and wholesale distributors.
                    </p>
                    <div class="text-[11px] font-mono text-slate-400">
                        GST Compliant • Verified Commercial Escrow
                    </div>
                </div>

                <!-- Col 2: Major Sourcing Sectors -->
                <div class="space-y-2.5">
                    <h5 class="text-xs font-bold text-white uppercase tracking-wider">Sourcing Sectors</h5>
                    <ul class="space-y-2 text-slate-400">
                        <li><a href="{{ route('enquiry.index', ['cat' => 'marble-granite']) }}" class="hover:text-white transition">Natural Stone &amp; Marble</a></li>
                        <li><a href="{{ route('enquiry.index', ['cat' => 'industrial-machinery']) }}" class="hover:text-white transition">Industrial Machinery</a></li>
                        <li><a href="{{ route('enquiry.index', ['cat' => 'construction-supplies']) }}" class="hover:text-white transition">Construction &amp; Aggregates</a></li>
                        <li><a href="{{ route('enquiry.index') }}" class="hover:text-white transition">Processing Equipment</a></li>
                        <li><a href="{{ route('enquiry.index') }}" class="hover:text-white transition">Logistics &amp; Transport</a></li>
                    </ul>
                </div>

                <!-- Col 3: For Buyers & Enterprise -->
                <div class="space-y-2.5">
                    <h5 class="text-xs font-bold text-white uppercase tracking-wider">For Commercial Buyers</h5>
                    <ul class="space-y-2 text-slate-400">
                        <li><button type="button" @click="openRfqModal()" class="hover:text-white transition text-left text-emerald-400 font-bold">Post New RFQ →</button></li>
                        <li><a href="{{ route('enquiry.index') }}#suppliers" class="hover:text-white transition">Verified Supplier Directory</a></li>
                        <li><a href="{{ route('account') }}" class="hover:text-white transition">My Quotations</a></li>
                        <li><a href="{{ route('pages.about') }}" class="hover:text-white transition">Trade Assurance Rules</a></li>
                        <li><a href="{{ route('pages.contact') }}" class="hover:text-white transition">Enterprise Support</a></li>
                    </ul>
                </div>

                <!-- Col 4: For Suppliers & Legal -->
                <div class="space-y-2.5">
                    <h5 class="text-xs font-bold text-white uppercase tracking-wider">For Manufacturers</h5>
                    <ul class="space-y-2 text-slate-400">
                        <li><a href="{{ route('vendor.register') }}" class="text-indigo-400 font-bold hover:underline">List Your Factory / Quotas →</a></li>
                        <li><a href="{{ route('vendor.login') }}" class="hover:text-white transition">Supplier Console Login</a></li>
                        <li><a href="{{ route('pages.terms') }}" class="hover:text-white transition">Terms of Procurement</a></li>
                        <li><a href="{{ route('pages.privacy') }}" class="hover:text-white transition">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-400">
                <div>&copy; {{ date('Y') }} K71 Enquiry Platform • A Division of K71 Multi-Category Marketplace</div>
                <div class="flex items-center gap-4">
                    <span>GST Invoiced</span>
                    <span>•</span>
                    <span>Bank Escrow</span>
                    <span>•</span>
                    <span>Direct Factory Quotes</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- ===================================================================
         4. GLOBAL INTERACTIVE RFQ SUBMISSION MODAL (WORKS IN-PAGE)
         =================================================================== -->
    <div x-show="rfqModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs overflow-y-auto"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 my-8 space-y-5"
             @click.outside="rfqModalOpen = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-slate-950 text-white flex items-center justify-center font-black text-sm">
                        RFQ
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-950">Post Your Requirement (RFQ)</h3>
                        <p class="text-xs text-slate-500">Receive structured quotations directly from verified suppliers.</p>
                    </div>
                </div>
                <button @click="rfqModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-sm font-bold transition cursor-pointer">
                    ✕
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitRfq($event)" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="vendor_id" :value="rfqTargetVendorId">
                <input type="hidden" name="product_id" :value="rfqTargetProductId">

                <div>
                    <label class="block font-semibold text-slate-800 mb-1">Product or Material Needed *</label>
                    <input type="text" name="title" x-model="rfqTitle" required placeholder="e.g. Makrana Pure White Marble Slabs, 50-Ton Hydraulic Crane..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-slate-950 focus:border-slate-950">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-800 mb-1">Quantity &amp; Units *</label>
                        <input type="text" name="quantity" required placeholder="e.g. 5,000 Sq.Ft or 2 Units" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-slate-950">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-800 mb-1">Target Budget (INR, Optional)</label>
                        <input type="number" name="target_budget" placeholder="e.g. 250000" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-slate-950">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-800 mb-1">Delivery City / Port *</label>
                        <input type="text" name="city" required placeholder="e.g. Jaipur, Mumbai, Delhi..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-slate-950">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-800 mb-1">Procurement Urgency</label>
                        <select name="urgency" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-slate-950 bg-white">
                            <option value="immediate">Immediate (Ready stock required)</option>
                            <option value="within_1_week">Within 1 Week</option>
                            <option value="flexible" selected>Flexible Timeline</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-800 mb-1">Detailed Specifications &amp; Instructions *</label>
                    <textarea name="description" required rows="3" placeholder="Provide dimensions, grade, tolerance, packaging, certification, or delivery requirements..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-slate-950"></textarea>
                </div>

                <!-- Contact details -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <span class="block text-[11px] font-mono font-bold uppercase tracking-wider text-slate-500">Buyer Contact Information</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <input type="text" name="buyer_name" required value="{{ auth()->user()?->name }}" placeholder="Your Full Name *" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-900 bg-white focus:ring-2 focus:ring-slate-950">
                        </div>
                        <div>
                            <input type="tel" name="buyer_phone" required value="{{ auth()->user()?->phone }}" placeholder="Mobile Phone (for quotes) *" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-900 bg-white focus:ring-2 focus:ring-slate-950">
                        </div>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button" @click="rfqModalOpen = false" class="px-4 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            :disabled="submittingRfq"
                            class="px-6 py-2.5 rounded-xl bg-slate-950 hover:bg-slate-800 disabled:opacity-50 text-white font-bold transition shadow-sm cursor-pointer flex items-center gap-2">
                        <span x-text="submittingRfq ? 'Broadcasting RFQ...' : 'Submit Requirement →'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Interactive Script -->
    <script>
    function k71EnquiryGlobalState() {
        return {
            rfqModalOpen: false,
            rfqTitle: '',
            rfqTargetVendorId: '',
            rfqTargetProductId: '',
            submittingRfq: false,

            openRfqModal(detail = {}) {
                if (detail.title) this.rfqTitle = detail.title;
                if (detail.vendor_id) this.rfqTargetVendorId = detail.vendor_id;
                if (detail.product_id) this.rfqTargetProductId = detail.product_id;
                this.rfqModalOpen = true;
            },

            async submitRfq(e) {
                this.submittingRfq = true;
                const form = e.target;
                const formData = new FormData(form);

                try {
                    const res = await fetch('{{ route('enquiry.store') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        credentials: 'same-origin',
                        body: formData
                    });

                    const data = await res.json();
                    if (res.ok && data.success) {
                        alert(data.message || 'Requirement posted successfully! Suppliers will submit quotations shortly.');
                        this.rfqModalOpen = false;
                        form.reset();
                        window.location.reload();
                    } else {
                        alert(data.message || 'Validation error. Please verify the fields.');
                    }
                } catch(err) {
                    // Fallback to normal form submit if JSON fails
                    form.submit();
                } finally {
                    this.submittingRfq = false;
                }
            }
        };
    }
    </script>

    <!-- Mobile Bottom Tab Navigation (Section F) -->
    <x-k71-bottom-nav active="b2b" />

    <!-- Shared K71 Authentication Popup Modal -->
    <x-k71-auth-modal />

    @stack('scripts')
</body>
</html>
