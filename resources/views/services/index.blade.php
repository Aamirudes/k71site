<x-layouts.public 
    title="Fiverr & Upwork Style Verified Professional Services — K71 Services"
    metaDescription="Book verified top-rated pros for home deep cleaning, car detailing, plumbing, electrical repairs, painting, and digital gigs on K71 Services. 100% milestone escrow protection and guaranteed work quality."
>
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #4f14cc 0%, #6d28d9 50%, #7c3aed 100%);
        }
        .purple-glow:hover {
            box-shadow: 0 10px 25px -5px rgba(109, 40, 217, 0.35);
        }
        .glass-upwork {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(109, 40, 217, 0.12);
        }
        .dark .glass-upwork {
            background: rgba(24, 24, 27, 0.95);
            border: 1px solid rgba(109, 40, 217, 0.22);
        }
    </style>

    <div x-data="{
        activeTab: 'services', // 'services', 'bookings'
        bookingModal: false,
        activeService: null,
        selectedDate: 'Tomorrow, Oct 21',
        selectedSlot: '10:00 AM - 12:00 PM',
        bookingSuccess: false,
        bookingsTab: 'upcoming', // 'upcoming', 'completed'

        openBooking(svc) {
            this.activeService = svc;
            this.bookingSuccess = false;
            this.bookingModal = true;
        },
        confirmBooking() {
            this.bookingSuccess = true;
            setTimeout(() => {
                this.bookingModal = false;
                this.activeTab = 'bookings';
                this.bookingsTab = 'upcoming';
            }, 1200);
        }
    }" class="min-h-screen text-zinc-900 dark:text-zinc-100 transition-colors duration-300 pb-20 font-sans">

        <!-- ======================================================== -->
        <!-- 1. UPWORK/FIVERR CLEAN PROFESSIONAL HEADER               -->
        <!-- ======================================================== -->
        <div class="mb-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-4 rounded-3xl purple-gradient text-white shadow-xl border border-purple-400/30">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-white text-[#5e17eb] flex items-center justify-center font-black text-2xl shrink-0 shadow-md">
                    ⚡
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider bg-black/25 text-purple-200 px-2 py-0.5 rounded-md">
                            services.{{ config('k71.root_domain', 'k71.in') }}
                        </span>
                        <span class="text-[11px] text-purple-100 font-semibold hidden sm:inline">100% Escrow Protected Freelance &amp; Home Pros</span>
                    </div>
                    <div class="flex items-center gap-1.5 font-black text-sm text-white mt-0.5 cursor-pointer hover:text-purple-200 transition">
                        <span>📍 Metro Hub:</span>
                        <span class="underline decoration-dotted font-extrabold">{{ request('city', 'New York, NY / Local Metropolitan Hub') }}</span>
                        <svg class="w-3.5 h-3.5 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            <!-- Tab Switcher (Services Hub vs My Bookings) -->
            <div class="flex items-center p-1 rounded-2xl bg-black/25 backdrop-blur-md border border-white/15 text-xs font-bold w-full sm:w-auto">
                <button type="button" @click="activeTab = 'services'" 
                        :class="activeTab === 'services' ? 'bg-white text-[#5e17eb] shadow-md' : 'text-purple-100 hover:text-white'"
                        class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl font-black uppercase tracking-wider transition cursor-pointer">
                    Services Hub
                </button>
                <button type="button" @click="activeTab = 'bookings'" 
                        :class="activeTab === 'bookings' ? 'bg-white text-[#5e17eb] shadow-md' : 'text-purple-100 hover:text-white'"
                        class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl font-black uppercase tracking-wider transition cursor-pointer flex items-center justify-center gap-1.5">
                    <span>My Bookings</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                </button>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 1: SERVICES HUB (UPWORK / FIVERR PRO CATALOG)        -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'services'" x-transition:enter="transition ease-out duration-200">
            <!-- 2. UPWORK / FIVERR HERO: VIBRANT PURPLE & WHITE -->
            <div class="relative overflow-hidden rounded-3xl purple-gradient p-6 sm:p-10 text-white shadow-2xl mb-8 border border-purple-300/30">
                <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="max-w-xl text-center md:text-left">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/20 backdrop-blur-md text-[11px] font-black tracking-wider uppercase mb-3 border border-white/25">
                            <span class="text-amber-300">👑</span>
                            <span>FIVERR PRO &amp; UPWORK TALENT POOL</span>
                        </div>
                        <h2 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight uppercase drop-shadow-md">
                            Hire Top-Rated Pros. <br>
                            <span class="text-purple-200">Get Things Done Flawlessly.</span>
                        </h2>
                        <p class="text-xs sm:text-sm text-purple-100 font-medium mt-3 mb-6 max-w-lg leading-relaxed">
                            Certified 5-star plumbers, deep home cleaners, AC service mechanics, licensed electricians, and expert digital talent. Pay securely via milestone escrow.
                        </p>

                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                            <a href="#servicesList" class="px-7 py-3 rounded-2xl bg-white text-[#5e17eb] hover:bg-purple-50 font-black text-xs uppercase tracking-wider shadow-xl transition active:scale-95 flex items-center gap-2">
                                <span>Browse Verified Gigs 🛠️</span>
                            </a>
                            <div class="flex items-center gap-2 text-xs font-bold text-white bg-black/25 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-white/15">
                                <span class="text-emerald-400">🛡️</span> 100% Milestone Escrow Guarantee
                            </div>
                        </div>
                    </div>

                    <!-- Verified Pro Badge & Talent Display -->
                    <div class="relative shrink-0 flex items-center justify-center">
                        <div class="w-48 h-48 sm:w-60 sm:h-60 rounded-full bg-white/15 backdrop-blur-xl border-4 border-white/30 p-2 shadow-2xl flex items-center justify-center">
                            <img src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=500&q=80" 
                                 alt="Verified Pro Specialist" class="w-full h-full object-cover rounded-full shadow-2xl">
                        </div>
                        <div class="absolute -bottom-3 -left-3 px-3.5 py-2 rounded-xl bg-white text-zinc-950 text-xs font-black shadow-xl border border-purple-100 flex items-center gap-1.5">
                            <span class="text-emerald-600">✓</span> 99% Job Success Score
                        </div>
                        <div class="absolute -top-3 -right-3 px-3 py-1.5 rounded-xl bg-amber-400 text-zinc-950 text-[11px] font-black uppercase tracking-wider shadow-xl flex items-center gap-1">
                            <span>⭐ TOP RATED PLUS</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. SEARCH BAR WITH SERVICE QUERY SUGGESTIONS -->
            <div class="mb-8">
                <form action="{{ route('services.index') }}" method="GET" class="relative">
                    <div class="absolute left-4 top-4 text-[#7c3aed]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" 
                           placeholder="Search for deep house cleaning, AC repair, bathroom plumbing, wall painting, electrician, car wash..." 
                           class="w-full pl-12 pr-36 py-4 rounded-3xl bg-white dark:bg-zinc-900 border-2 border-purple-200 dark:border-zinc-800 text-sm font-semibold focus:ring-4 focus:ring-purple-500/20 focus:border-[#7c3aed] focus:outline-none transition shadow-sm">
                    <button type="submit" class="absolute right-2.5 top-2.5 px-6 py-2.5 rounded-2xl purple-gradient hover:opacity-95 text-white text-xs font-black uppercase tracking-wider transition shadow-md cursor-pointer">
                        Find Experts
                    </button>
                </form>
            </div>

            <!-- 4. SERVICES CATEGORY PILLS (FIVERR STYLE HORIZONTAL RAIL) -->
            <div class="mb-10">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base sm:text-lg font-black uppercase tracking-wider text-zinc-900 dark:text-white flex items-center gap-2">
                            <span>Explore Pro Categories</span>
                            <span class="text-purple-600">✨</span>
                        </h3>
                        <p class="text-xs text-zinc-400">Instantly book certified professionals in your area</p>
                    </div>
                    <a href="{{ route('services.index') }}" class="text-xs font-black text-[#5e17eb] hover:underline cursor-pointer">View All</a>
                </div>

                <div class="flex items-center gap-4 overflow-x-auto no-scrollbar py-2">
                    @php
                        $serviceIcons = [
                            ['name' => 'All Gigs', 'icon' => '🛠️', 'query' => ''],
                            ['name' => 'Deep Cleaning', 'icon' => '🧹', 'query' => 'cleaning'],
                            ['name' => 'Appliance Repair', 'icon' => '🔧', 'query' => 'repair'],
                            ['name' => 'Painting & Polish', 'icon' => '🎨', 'query' => 'painting'],
                            ['name' => 'Laundry & Press', 'icon' => '🧺', 'query' => 'laundry'],
                            ['name' => 'Plumbing Works', 'icon' => '🚰', 'query' => 'plumbing'],
                            ['name' => 'Electrician Care', 'icon' => '⚡', 'query' => 'electrician'],
                            ['name' => 'Packers & Movers', 'icon' => '📦', 'query' => 'shifting'],
                            ['name' => 'Salon & Grooming', 'icon' => '✂️', 'query' => 'salon'],
                        ];
                    @endphp
                    @foreach($serviceIcons as $item)
                        <a href="{{ route('services.index', ['search' => $item['query']]) }}" 
                           class="group flex flex-col items-center shrink-0 cursor-pointer text-center min-w-[80px]">
                            <div class="w-18 h-18 rounded-2xl glass-upwork group-hover:border-[#7c3aed] group-hover:scale-105 shadow-sm group-hover:shadow-md flex items-center justify-center text-3xl transition-all duration-300">
                                {{ $item['icon'] }}
                            </div>
                            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 group-hover:text-[#5e17eb] mt-2 transition">
                                {{ $item['name'] }}
                            </span>
                        </a>
                    @endforeach
                    @foreach($servicesCategories as $cat)
                        <a href="{{ route('services.index', ['cat' => $cat->slug]) }}" 
                           class="group flex flex-col items-center shrink-0 cursor-pointer text-center min-w-[80px]">
                            <div class="w-18 h-18 rounded-2xl glass-upwork group-hover:border-[#7c3aed] group-hover:scale-105 shadow-sm group-hover:shadow-md flex items-center justify-center text-2xl transition-all duration-300">
                                💼
                            </div>
                            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 group-hover:text-[#5e17eb] mt-2 transition">
                                {{ $cat->name }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- 5. MOST POPULAR SERVICES GRID (FIVERR GIG CARDS) -->
            <div id="servicesList" class="mb-6 flex items-center justify-between">
                <div>
                    <h3 class="text-base sm:text-lg font-black uppercase tracking-wider text-zinc-900 dark:text-white flex items-center gap-2">
                        <span>Top-Rated Verified Pros</span>
                        <span class="px-2.5 py-0.5 rounded-md bg-purple-100 text-[#5e17eb] text-[10px] font-black uppercase">VETTED TALENT</span>
                    </h3>
                    <p class="text-xs text-zinc-400">Guaranteed on-time arrival with 30-day free rework warranty</p>
                </div>
            </div>

            @if($services->isEmpty())
                <div class="text-center py-16 px-4 glass-upwork rounded-3xl max-w-md mx-auto my-8">
                    <div class="w-16 h-16 rounded-2xl bg-purple-100 dark:bg-purple-950/40 text-[#5e17eb] flex items-center justify-center mx-auto mb-3 text-3xl">
                        🔧
                    </div>
                    <h3 class="text-base font-black text-zinc-900 dark:text-white mb-1">No services found</h3>
                    <p class="text-xs text-zinc-500 mb-4">Try clearing filters or search for cleaning, plumber, or electrician.</p>
                    <a href="{{ route('services.index') }}" class="px-6 py-2.5 rounded-2xl purple-gradient text-white text-xs font-black uppercase tracking-wider shadow-md">
                        Browse All Services
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-12">
                    @foreach($services as $svc)
                        @php
                            $rateRupees = $svc->base_price / 100;
                            $firstImg = $svc->images->first()?->path;
                            $hourlyRate = $svc->getCustomField('hourly_rate', $rateRupees > 0 ? '₹' . number_format($rateRupees, 2) : '₹499.00');
                            $duration = $svc->getCustomField('duration', '1-2 Hours');
                            $providerName = $svc->vendor?->shop_name ?? 'Urban Pro Expert';
                            $providerCity = $svc->vendor?->city ?? 'Metro';
                            $imgUrl = $firstImg ? asset('storage/' . $firstImg) : 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=500&q=80';
                        @endphp
                        <div class="group rounded-3xl glass-upwork overflow-hidden flex flex-col justify-between transition-all duration-300 hover:shadow-2xl hover:-translate-y-1.5 relative">
                            
                            <!-- Image Container with Pro Badge & Star Rating -->
                            <div class="relative w-full aspect-4/3 bg-zinc-100 dark:bg-zinc-800 overflow-hidden cursor-pointer"
                                 @click="openBooking({
                                     id: '{{ $svc->id }}',
                                     title: '{{ addslashes($svc->title) }}',
                                     rate: '{{ $hourlyRate }}',
                                     price: {{ $rateRupees }},
                                     provider: '{{ addslashes($providerName) }}',
                                     image: '{{ $imgUrl }}',
                                     duration: '{{ $duration }}',
                                     desc: '{{ addslashes($svc->description ?? 'Experienced certified technician equipped with professional tools and guaranteed 30-day warranty.') }}'
                                 })">
                                <img src="{{ $imgUrl }}" alt="{{ $svc->title }}"
                                     class="w-full h-full object-cover group-hover:scale-108 transition duration-700 ease-out"
                                     onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=500&q=80';">

                                <!-- Pro Badges -->
                                <div class="absolute top-3 left-3">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-[#5e17eb] text-white shadow-md flex items-center gap-1">
                                        <span>👑</span> TOP RATED PLUS
                                    </span>
                                </div>

                                <div class="absolute top-3 right-3 px-2 py-0.5 rounded-lg bg-black/70 backdrop-blur-md text-white text-[10px] font-black flex items-center gap-1">
                                    <span class="text-amber-400">★</span> 4.9 (520+)
                                </div>

                                <!-- ETA Badge -->
                                <div class="absolute bottom-3 left-3 px-2.5 py-1 rounded-lg bg-black/70 backdrop-blur-md text-white text-[10px] font-bold flex items-center gap-1">
                                    <span>⚡ Slot: {{ $duration }}</span>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between">
                                <div>
                                    <!-- Talent Avatar & Info -->
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="relative w-6 h-6 rounded-full bg-purple-100 text-[#5e17eb] font-black text-xs flex items-center justify-center shrink-0">
                                            {{ substr($providerName, 0, 1) }}
                                            <span class="absolute -bottom-0.5 -right-0.5 w-2 h-2 rounded-full bg-emerald-500 border border-white"></span>
                                        </div>
                                        <span class="text-xs font-bold text-zinc-600 dark:text-zinc-400 truncate">{{ $providerName }}</span>
                                        <span class="text-[10px] text-zinc-400 ml-auto shrink-0">📍 {{ $providerCity }}</span>
                                    </div>

                                    <h4 class="text-sm sm:text-base font-black text-zinc-950 dark:text-white group-hover:text-[#5e17eb] line-clamp-2 leading-snug cursor-pointer transition-colors"
                                        @click="openBooking({
                                            id: '{{ $svc->id }}',
                                            title: '{{ addslashes($svc->title) }}',
                                            rate: '{{ $hourlyRate }}',
                                            price: {{ $rateRupees }},
                                            provider: '{{ addslashes($providerName) }}',
                                            image: '{{ $imgUrl }}',
                                            duration: '{{ $duration }}',
                                            desc: '{{ addslashes($svc->description ?? 'Experienced certified technician equipped with professional tools and guaranteed 30-day warranty.') }}'
                                        })">
                                        {{ $svc->title }}
                                    </h4>
                                </div>

                                <div class="pt-4 mt-3 border-t border-purple-100 dark:border-zinc-800 flex items-center justify-between">
                                    <div>
                                        <span class="text-[10px] text-zinc-400 uppercase font-black block">Starting At</span>
                                        <span class="text-base sm:text-lg font-black text-[#5e17eb] dark:text-purple-400">
                                            {{ $hourlyRate }}
                                        </span>
                                    </div>

                                    <!-- Book Now Button -->
                                    <button type="button" 
                                            @click="openBooking({
                                                id: '{{ $svc->id }}',
                                                title: '{{ addslashes($svc->title) }}',
                                                rate: '{{ $hourlyRate }}',
                                                price: {{ $rateRupees }},
                                                provider: '{{ addslashes($providerName) }}',
                                                image: '{{ $imgUrl }}',
                                                duration: '{{ $duration }}',
                                                desc: '{{ addslashes($svc->description ?? 'Experienced certified technician equipped with professional tools and guaranteed 30-day warranty.') }}'
                                            })"
                                            class="px-4 py-2 rounded-2xl purple-gradient hover:opacity-95 text-white text-xs font-black uppercase tracking-wider transition active:scale-95 shadow-md shadow-purple-500/25 cursor-pointer">
                                        Book Now
                                    </button>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $services->links() }}
                </div>
            @endif

            <!-- Upwork 3-Pillar Milestone Escrow Guarantee -->
            <div class="mt-12 p-6 sm:p-8 rounded-3xl bg-purple-50/70 dark:bg-zinc-900/70 border border-purple-200/80 dark:border-zinc-800">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-[#5e17eb] text-white flex items-center justify-center font-black text-xl shrink-0 shadow-md">
                            🛡️
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-zinc-950 dark:text-white uppercase tracking-wide">Milestone Escrow Protection</h4>
                            <p class="text-xs text-zinc-500 mt-1 leading-relaxed">Your payment stays safe in K71 Escrow. Funds are released only after you inspect and sign off on completed work.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-[#5e17eb] text-white flex items-center justify-center font-black text-xl shrink-0 shadow-md">
                            ⚡
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-zinc-950 dark:text-white uppercase tracking-wide">Instant Time-Slot Booking</h4>
                            <p class="text-xs text-zinc-500 mt-1 leading-relaxed">Choose precise morning, midday, or evening appointment windows with verified real-time pro availability.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-[#5e17eb] text-white flex items-center justify-center font-black text-xl shrink-0 shadow-md">
                            🏅
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-zinc-950 dark:text-white uppercase tracking-wide">30-Day Service Guarantee</h4>
                            <p class="text-xs text-zinc-500 mt-1 leading-relaxed">Enjoy complete peace of mind. Any issue arising within 30 days is re-inspected and resolved at zero additional charge.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 2: MY BOOKINGS TRACKER (UPWORK / FIVERR ORDERS)      -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'bookings'" x-cloak class="max-w-2xl mx-auto py-6">
            <!-- Segmented Control: Upcoming vs Completed -->
            <div class="flex p-1 rounded-2xl bg-zinc-100 dark:bg-zinc-800 border border-purple-100 dark:border-zinc-700 mb-6">
                <button type="button" @click="bookingsTab = 'upcoming'" 
                        :class="bookingsTab === 'upcoming' ? 'purple-gradient text-white shadow-md' : 'text-zinc-600 dark:text-zinc-400'"
                        class="flex-1 py-3 text-xs font-black uppercase tracking-wider rounded-xl transition cursor-pointer">
                    Upcoming Appointments (2)
                </button>
                <button type="button" @click="bookingsTab = 'completed'" 
                        :class="bookingsTab === 'completed' ? 'purple-gradient text-white shadow-md' : 'text-zinc-600 dark:text-zinc-400'"
                        class="flex-1 py-3 text-xs font-black uppercase tracking-wider rounded-xl transition cursor-pointer">
                    Completed History (8)
                </button>
            </div>

            <!-- Upcoming Bookings List -->
            <div x-show="bookingsTab === 'upcoming'" class="space-y-4">
                <div class="p-5 rounded-3xl glass-upwork shadow-sm border border-purple-200/80">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-purple-100 text-[#5e17eb] text-2xl flex items-center justify-center">
                                🧹
                            </div>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md">CONFIRMED SLOT</span>
                                <h4 class="text-sm font-black text-zinc-950 dark:text-white mt-1">Deep Home Sanitization &amp; Floor Buffing</h4>
                            </div>
                        </div>
                        <span class="text-base font-black text-[#5e17eb]">₹1,899</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 p-3 rounded-2xl bg-purple-50/50 dark:bg-zinc-900 text-xs text-zinc-600 dark:text-zinc-400 mb-3">
                        <div>📅 <strong>Tomorrow, Oct 21</strong></div>
                        <div>⏱️ <strong>10:00 AM - 12:00 PM</strong></div>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-purple-100 dark:border-zinc-800 text-xs">
                        <span class="text-zinc-400">Escrow Ref: <strong>#ESC-99214</strong></span>
                        <button type="button" class="px-3.5 py-1.5 rounded-xl bg-purple-100 text-[#5e17eb] font-bold hover:bg-purple-200 transition">
                            Contact Technician
                        </button>
                    </div>
                </div>
            </div>

            <!-- Completed Bookings List -->
            <div x-show="bookingsTab === 'completed'" class="space-y-4">
                <div class="p-5 rounded-3xl glass-upwork shadow-sm border border-zinc-200 dark:border-zinc-800 opacity-90">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-black uppercase tracking-wider bg-zinc-200 text-zinc-700 px-2 py-0.5 rounded-md">COMPLETED</span>
                            <h4 class="text-sm font-bold text-zinc-950 dark:text-white">AC Gas Refill &amp; Coil Power Wash</h4>
                        </div>
                        <span class="text-sm font-black text-zinc-700 dark:text-zinc-300">₹899</span>
                    </div>
                    <div class="text-xs text-zinc-400">Completed on Oct 14 • 5★ Rated Pro</div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- APPOINTMENT BOOKING DRAWER / MODAL                       -->
        <!-- ======================================================== -->
        <div x-show="bookingModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-md flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.away="bookingModal = false" 
                 class="bg-white dark:bg-zinc-950 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-purple-200 dark:border-zinc-800 relative text-zinc-950 dark:text-white"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <!-- Close Button -->
                <button type="button" @click="bookingModal = false" class="absolute top-4 right-4 text-zinc-400 hover:text-zinc-600 text-xl font-bold p-1 cursor-pointer">
                    ✕
                </button>

                <div x-show="!bookingSuccess">
                    <div class="mb-5">
                        <span class="text-[11px] font-black uppercase tracking-wider text-[#5e17eb] dark:text-purple-400">Milestone Booking</span>
                        <h3 class="text-xl sm:text-2xl font-black text-zinc-950 dark:text-white mt-0.5" x-text="activeService ? activeService.title : ''"></h3>
                        <p class="text-xs text-zinc-500 mt-1" x-text="'Verified Pro: ' + (activeService ? activeService.provider : '')"></p>
                    </div>

                    <!-- Work Gallery Preview -->
                    <div class="mb-5">
                        <label class="block text-[11px] font-black uppercase tracking-wider text-zinc-400 mb-2">Verified Work Gallery</label>
                        <div class="grid grid-cols-4 gap-2">
                            <div class="aspect-square rounded-2xl bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                                <img src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=150&q=80" alt="Work 1" class="w-full h-full object-cover">
                            </div>
                            <div class="aspect-square rounded-2xl bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                                <img src="https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=150&q=80" alt="Work 2" class="w-full h-full object-cover">
                            </div>
                            <div class="aspect-square rounded-2xl bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                                <img src="https://images.unsplash.com/photo-1505798577917-a65157d3320a?auto=format&fit=crop&w=150&q=80" alt="Work 3" class="w-full h-full object-cover">
                            </div>
                            <div class="aspect-square rounded-2xl bg-purple-100 text-[#5e17eb] flex items-center justify-center font-black text-xs">
                                +18 More
                            </div>
                        </div>
                    </div>

                    <!-- Select Preferred Date -->
                    <div class="mb-4">
                        <label class="block text-[11px] font-black uppercase tracking-wider text-zinc-400 mb-2">Select Preferred Date</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" @click="selectedDate = 'Today, Oct 20'" 
                                    :class="selectedDate === 'Today, Oct 20' ? 'border-[#5e17eb] bg-purple-50 text-purple-900 dark:bg-purple-950/40 dark:text-white font-bold ring-2 ring-purple-400' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2.5 rounded-2xl border text-xs text-center transition cursor-pointer">
                                Today
                            </button>
                            <button type="button" @click="selectedDate = 'Tomorrow, Oct 21'" 
                                    :class="selectedDate === 'Tomorrow, Oct 21' ? 'border-[#5e17eb] bg-purple-50 text-purple-900 dark:bg-purple-950/40 dark:text-white font-bold ring-2 ring-purple-400' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2.5 rounded-2xl border text-xs text-center transition cursor-pointer">
                                Tomorrow
                            </button>
                            <button type="button" @click="selectedDate = 'Wed, Oct 22'" 
                                    :class="selectedDate === 'Wed, Oct 22' ? 'border-[#5e17eb] bg-purple-50 text-purple-900 dark:bg-purple-950/40 dark:text-white font-bold ring-2 ring-purple-400' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2.5 rounded-2xl border text-xs text-center transition cursor-pointer">
                                Wed, Oct 22
                            </button>
                        </div>
                    </div>

                    <!-- Select Time Window -->
                    <div class="mb-6">
                        <label class="block text-[11px] font-black uppercase tracking-wider text-zinc-400 mb-2">Select Time Window</label>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <button type="button" @click="selectedSlot = '09:00 AM - 11:00 AM'" 
                                    :class="selectedSlot === '09:00 AM - 11:00 AM' ? 'border-[#5e17eb] bg-purple-50 text-purple-900 dark:bg-purple-950/40 dark:text-white font-bold ring-2 ring-purple-400' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2.5 px-3 rounded-2xl border text-center transition cursor-pointer">
                                🌅 09:00 - 11:00 AM
                            </button>
                            <button type="button" @click="selectedSlot = '11:00 AM - 01:00 PM'" 
                                    :class="selectedSlot === '11:00 AM - 01:00 PM' ? 'border-[#5e17eb] bg-purple-50 text-purple-900 dark:bg-purple-950/40 dark:text-white font-bold ring-2 ring-purple-400' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2.5 px-3 rounded-2xl border text-center transition cursor-pointer">
                                ☀️ 11:00 AM - 01:00 PM
                            </button>
                            <button type="button" @click="selectedSlot = '02:00 PM - 04:00 PM'" 
                                    :class="selectedSlot === '02:00 PM - 04:00 PM' ? 'border-[#5e17eb] bg-purple-50 text-purple-900 dark:bg-purple-950/40 dark:text-white font-bold ring-2 ring-purple-400' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2.5 px-3 rounded-2xl border text-center transition cursor-pointer">
                                🌤️ 02:00 - 04:00 PM
                            </button>
                            <button type="button" @click="selectedSlot = '05:00 PM - 07:00 PM'" 
                                    :class="selectedSlot === '05:00 PM - 07:00 PM' ? 'border-[#5e17eb] bg-purple-50 text-purple-900 dark:bg-purple-950/40 dark:text-white font-bold ring-2 ring-purple-400' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2.5 px-3 rounded-2xl border text-center transition cursor-pointer">
                                🌙 05:00 - 07:00 PM
                            </button>
                        </div>
                    </div>

                    <!-- Bottom Bar -->
                    <div class="flex items-center justify-between pt-4 border-t border-zinc-100 dark:border-zinc-800">
                        <div>
                            <span class="text-xs text-zinc-400 block font-bold">Estimated Rate</span>
                            <span class="text-base sm:text-lg font-black text-[#5e17eb] dark:text-purple-400" x-text="activeService ? activeService.rate : ''"></span>
                        </div>

                        <button type="button" @click="confirmBooking()" 
                                class="px-7 py-3.5 rounded-2xl purple-gradient hover:opacity-95 text-white font-black text-xs uppercase tracking-wider transition shadow-lg shadow-purple-600/30 active:scale-95 cursor-pointer">
                            Confirm Appointment
                        </button>
                    </div>
                </div>

                <!-- Success Confirmation State -->
                <div x-show="bookingSuccess" class="text-center py-8">
                    <div class="w-18 h-18 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4 text-3xl font-black shadow-lg">
                        ✓
                    </div>
                    <h3 class="text-xl font-black text-zinc-950 dark:text-white mb-1">Appointment Confirmed!</h3>
                    <p class="text-xs text-zinc-500 mb-4">Your appointment with <span x-text="activeService ? activeService.provider : ''" class="font-bold"></span> has been scheduled and protected via Escrow.</p>
                    <span class="text-xs text-[#5e17eb] font-black">Redirecting to your Bookings tab...</span>
                </div>

            </div>
        </div>

    </div>
</x-layouts.public>
