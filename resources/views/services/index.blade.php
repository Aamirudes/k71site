<x-layouts.public 
    title="Top-Rated Home & Professional Services — K71 Services"
    metaDescription="Book verified home cleaning, car repair, plumbing, painting, electricians, and appliance repair on K71 Services. Guaranteed work, background-checked pros, and instant time-slot booking."
>
    <div x-data="k71ServicesApp()" class="min-h-screen text-zinc-900 dark:text-zinc-100 transition-colors duration-300 pb-16">

        <!-- TOP BAR: LOCATION & TABS (Image 2 Style) -->
        <div class="mb-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-3.5 bg-white dark:bg-zinc-900 rounded-3xl border border-purple-100 dark:border-zinc-800 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400">Services.K71 • Verified Service Network</div>
                    <div class="flex items-center gap-1 font-black text-xs sm:text-sm text-zinc-950 dark:text-white cursor-pointer">
                        <span>{{ $city ?: 'All-India Verified Service Hub' }}</span>
                        <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            <!-- Tab Switcher (Services Hub vs My Bookings) -->
            <div class="flex items-center p-1 rounded-2xl bg-zinc-100 dark:bg-zinc-800 border border-purple-100 dark:border-zinc-700 text-xs font-bold w-full sm:w-auto">
                <button type="button" @click="activeTab = 'services'" 
                        :class="activeTab === 'services' ? 'bg-purple-600 text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-300 hover:text-purple-600'"
                        class="flex-1 sm:flex-none px-4 py-2 rounded-xl transition cursor-pointer">
                    Services Hub
                </button>
                <button type="button" @click="activeTab = 'bookings'" 
                        :class="activeTab === 'bookings' ? 'bg-purple-600 text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-300 hover:text-purple-600'"
                        class="flex-1 sm:flex-none px-4 py-2 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                    <span>My Bookings</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                </button>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: SERVICES HUB (Image 2 Main Feed) -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'services'" x-transition:enter="transition ease-out duration-200">
            <!-- SPECIAL OFFERS BANNER (Exact Image 2 Screen 11) -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-purple-700 via-purple-600 to-indigo-700 p-6 sm:p-8 text-white shadow-xl mb-6">
                <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="max-w-md text-center md:text-left">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-[11px] font-black tracking-wider uppercase mb-3">
                            <span>✨ Today's Special Offers</span>
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-black tracking-tight leading-tight mb-2">
                            30% OFF ON <br>
                            <span class="text-purple-200">DEEP HOME CLEANING</span>
                        </h2>
                        <p class="text-xs sm:text-sm text-purple-100 mb-5 leading-relaxed">
                            Certified 5-star sanitization and deep cleaning technicians equipped with professional-grade steam equipment.
                        </p>
                        <a href="{{ route('services.index', ['search' => 'Cleaning']) }}" 
                           class="inline-block px-6 py-2.5 rounded-2xl bg-white text-purple-800 hover:bg-purple-50 font-black text-xs uppercase tracking-wider shadow-lg transition active:scale-95">
                            Book Cleaning Today
                        </a>
                    </div>

                    <div class="relative shrink-0 flex items-center justify-center">
                        <div class="w-44 h-44 sm:w-52 sm:h-52 rounded-full bg-white/10 backdrop-blur-md border border-white/20 p-2 shadow-2xl flex items-center justify-center">
                            <img src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=400&q=80" 
                                 alt="House Cleaning Pro" class="w-full h-full object-cover rounded-full shadow-lg">
                        </div>
                        <div class="absolute -bottom-2 -left-2 px-3 py-1 rounded-full bg-white text-purple-800 text-xs font-black shadow-lg">
                            ⭐ 4.9 Verified Pro
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEARCH BAR -->
            <div class="mb-6">
                <form action="{{ route('services.index') }}" method="GET" class="relative">
                    <div class="absolute left-4 top-3.5 text-zinc-400">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" 
                           placeholder="Search cleaning, car repair, painting, laundry, electrician, plumber..." 
                           class="w-full pl-12 pr-28 py-3.5 rounded-2xl bg-white dark:bg-zinc-900 border border-purple-100 dark:border-zinc-800 text-sm font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none transition shadow-xs">
                    <button type="submit" class="absolute right-2 top-2 px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition shadow-xs">
                        Search Pros
                    </button>
                </form>
            </div>

            <!-- SERVICES HORIZONTAL CATEGORY ICONS (Image 2 Screen 11) -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-black uppercase tracking-wider text-zinc-900 dark:text-white">Services Categories</h3>
                    <span class="text-xs text-purple-600 dark:text-purple-400 font-bold cursor-pointer">View All</span>
                </div>

                <div class="flex items-center gap-4 overflow-x-auto no-scrollbar pb-2">
                    @php
                        $serviceIcons = [
                            ['name' => 'All', 'icon' => '🛠️', 'query' => ''],
                            ['name' => 'Cleaning', 'icon' => '🧹', 'query' => 'cleaning'],
                            ['name' => 'Repairing', 'icon' => '🔧', 'query' => 'repair'],
                            ['name' => 'Painting', 'icon' => '🎨', 'query' => 'painting'],
                            ['name' => 'Laundry', 'icon' => '🧺', 'query' => 'laundry'],
                            ['name' => 'Appliance', 'icon' => '📺', 'query' => 'appliance'],
                            ['name' => 'Plumbing', 'icon' => '🚰', 'query' => 'plumbing'],
                            ['name' => 'Shifting', 'icon' => '📦', 'query' => 'shifting'],
                            ['name' => 'Electrician', 'icon' => '⚡', 'query' => 'electrician'],
                            ['name' => 'Salon & Spa', 'icon' => '✂️', 'query' => 'salon'],
                        ];
                    @endphp
                    @foreach($serviceIcons as $item)
                        <a href="{{ route('services.index', ['search' => $item['query']]) }}" 
                           class="group flex flex-col items-center shrink-0 cursor-pointer text-center">
                            <div class="w-16 h-16 rounded-2xl bg-white dark:bg-zinc-900 border border-purple-100 dark:border-zinc-800 group-hover:border-purple-500 shadow-xs flex items-center justify-center text-2xl group-hover:scale-105 transition-all duration-200">
                                {{ $item['icon'] }}
                            </div>
                            <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300 group-hover:text-purple-600 dark:group-hover:text-purple-400 mt-2 transition">
                                {{ $item['name'] }}
                            </span>
                        </a>
                    @endforeach
                    @foreach($servicesCategories as $cat)
                        <a href="{{ route('services.index', ['cat' => $cat->slug]) }}" 
                           class="group flex flex-col items-center shrink-0 cursor-pointer text-center">
                            <div class="w-16 h-16 rounded-2xl bg-white dark:bg-zinc-900 border border-purple-100 dark:border-zinc-800 group-hover:border-purple-500 shadow-xs flex items-center justify-center text-2xl group-hover:scale-105 transition-all duration-200">
                                🔧
                            </div>
                            <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300 group-hover:text-purple-600 dark:group-hover:text-purple-400 mt-2 transition">
                                {{ $cat->name }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- MOST POPULAR SERVICES SECTION (Image 2 Screen 11 & Screen 16-18) -->
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-sm font-black uppercase tracking-wider text-zinc-900 dark:text-white">Most Popular Services ⭐</h3>
                <span class="text-xs text-zinc-400">Guaranteed 30-Day Warranty</span>
            </div>

            @if($services->isEmpty())
                <div class="text-center py-12 px-6 bg-white dark:bg-zinc-900 rounded-[2.5rem] border border-purple-100 dark:border-zinc-800 max-w-lg mx-auto my-8 shadow-xs">
                    <div class="w-14 h-14 rounded-2xl bg-purple-100 dark:bg-purple-900/40 text-purple-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                        🛠️
                    </div>
                    <span class="text-[11px] font-black uppercase tracking-wider text-purple-600 bg-purple-50 dark:bg-purple-950/40 px-3 py-1 rounded-full border border-purple-200/60 inline-block mb-2">
                        Services Marketplace Ready
                    </span>
                    <h3 class="text-lg font-black text-zinc-900 dark:text-white mb-1">List Your Professional Services on K71</h3>
                    <p class="text-xs text-zinc-500 mb-6 leading-relaxed">
                        All test data has been cleared. Are you a certified electrician, plumber, cleaner, or home specialist? Register as a verified service partner to receive direct customer bookings.
                    </p>
                    <div class="flex items-center justify-center gap-3">
                        <a href="{{ route('vendor.register') }}" class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-black shadow-md transition">
                            Register as Pro &rarr;
                        </a>
                        <a href="{{ route('catalog.index') }}" class="px-5 py-2.5 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 text-xs font-bold transition">
                            Explore Categories
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 mb-10">
                    @foreach($services as $svc)
                        @php
                            $rateRupees = $svc->base_price / 100;
                            $firstImg = $svc->images->first()?->path;
                            $hourlyRate = $svc->getCustomField('hourly_rate', $rateRupees > 0 ? '₹' . number_format($rateRupees, 2) : '₹499.00');
                            $duration = $svc->getCustomField('duration', '1-2 Hours');
                            $providerName = $svc->vendor?->shop_name ?? 'Urban Pro';
                            $providerCity = $svc->vendor?->city ?? 'Local';
                            $imgUrl = $firstImg ? asset('storage/' . $firstImg) : 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=400&q=80';
                        @endphp
                        <div class="group rounded-3xl bg-white dark:bg-zinc-900 border border-purple-100/80 dark:border-zinc-800 hover:border-purple-400 overflow-hidden flex flex-col justify-between transition-all duration-300 hover:shadow-lg relative">
                            
                            <!-- Image Container -->
                            <div class="relative w-full aspect-4/3 bg-zinc-100 dark:bg-zinc-800 overflow-hidden cursor-pointer"
                                 @click="openBooking({
                                     id: '{{ $svc->id }}',
                                     title: '{{ addslashes($svc->title) }}',
                                     rate: '{{ $hourlyRate }}',
                                     price: {{ $rateRupees }},
                                     provider: '{{ addslashes($providerName) }}',
                                     image: '{{ $imgUrl }}',
                                     duration: '{{ $duration }}',
                                     desc: '{{ addslashes($svc->description ?? 'Experienced certified technician with background check and complete toolkit.') }}'
                                 })">
                                <img src="{{ $imgUrl }}" alt="{{ $svc->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                                     onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=400&q=80';">

                                <!-- Rating Badge Top-Right -->
                                <div class="absolute top-3 right-3 px-2 py-0.5 rounded-lg bg-black/60 backdrop-blur-md text-white text-[10px] font-black flex items-center gap-1">
                                    <span class="text-amber-400">★</span> 4.8 (4.8k)
                                </div>

                                <!-- Verified Pro Pill Top-Left -->
                                <div class="absolute top-3 left-3">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-purple-600 text-white shadow-xs">
                                        Verified Pro
                                    </span>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="p-4 flex-1 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between text-[11px] text-zinc-400 dark:text-zinc-500 mb-1">
                                        <span class="truncate">{{ $providerName }}</span>
                                        <span>📍 {{ $providerCity }}</span>
                                    </div>

                                    <h4 class="text-sm font-bold text-zinc-950 dark:text-white group-hover:text-purple-600 line-clamp-1 leading-snug cursor-pointer"
                                        @click="openBooking({
                                            id: '{{ $svc->id }}',
                                            title: '{{ addslashes($svc->title) }}',
                                            rate: '{{ $hourlyRate }}',
                                            price: {{ $rateRupees }},
                                            provider: '{{ addslashes($providerName) }}',
                                            image: '{{ $imgUrl }}',
                                            duration: '{{ $duration }}',
                                            desc: '{{ addslashes($svc->description ?? 'Experienced certified technician with background check and complete toolkit.') }}'
                                        })">
                                        {{ $svc->title }}
                                    </h4>
                                </div>

                                <div class="pt-3 mt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                                    <div>
                                        <span class="text-base font-black text-purple-600 dark:text-purple-400">
                                            {{ $hourlyRate }}
                                        </span>
                                    </div>

                                    <!-- Book Now Pill Button -->
                                    <button type="button" 
                                            @click="openBooking({
                                                id: '{{ $svc->id }}',
                                                title: '{{ addslashes($svc->title) }}',
                                                rate: '{{ $hourlyRate }}',
                                                price: {{ $rateRupees }},
                                                provider: '{{ addslashes($providerName) }}',
                                                image: '{{ $imgUrl }}',
                                                duration: '{{ $duration }}',
                                                desc: '{{ addslashes($svc->description ?? 'Experienced certified technician with background check and complete toolkit.') }}'
                                            })"
                                            class="px-4 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-black uppercase tracking-wider transition active:scale-95 shadow-xs cursor-pointer">
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
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: MY BOOKINGS (Image 2 Screen 14-15) -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'bookings'" x-cloak class="max-w-2xl mx-auto py-8 text-center">
            @auth
                <div class="p-8 sm:p-12 rounded-3xl bg-white dark:bg-zinc-900 border border-purple-100 dark:border-zinc-800 shadow-xs">
                    <div class="w-16 h-16 rounded-3xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 flex items-center justify-center text-3xl mx-auto mb-4">
                        📅
                    </div>
                    <h3 class="text-lg font-black text-zinc-950 dark:text-white mb-1">Your Service Appointments</h3>
                    <p class="text-xs text-zinc-500 max-w-sm mx-auto mb-6">
                        You have no scheduled service visits at this moment. Choose a verified specialist from the hub to book your doorstep technician.
                    </p>
                    <button type="button" @click="activeTab = 'services'" class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-black uppercase tracking-wider transition shadow-sm">
                        Explore Services Hub &rarr;
                    </button>
                </div>
            @else
                <div class="p-8 sm:p-12 rounded-3xl bg-white dark:bg-zinc-900 border border-purple-100 dark:border-zinc-800 shadow-xs">
                    <div class="w-16 h-16 rounded-3xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 flex items-center justify-center text-3xl mx-auto mb-4">
                        🔒
                    </div>
                    <h3 class="text-lg font-black text-zinc-950 dark:text-white mb-1">Sign In to View Appointments</h3>
                    <p class="text-xs text-zinc-500 max-w-sm mx-auto mb-6">
                        Log into your K71 account to view your confirmed time slots, provider phone numbers, and service history.
                    </p>
                    <a href="{{ route('login') }}" class="inline-block px-6 py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-black uppercase tracking-wider shadow-md transition">
                        Sign In / Register &rarr;
                    </a>
                </div>
            @endauth
        </div>

        <!-- ======================================================== -->
        <!-- INTERACTIVE TIME-SLOT BOOKING MODAL (Image 2 Screen 12-13) -->
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
                 class="bg-white dark:bg-zinc-950 rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border border-purple-100 dark:border-zinc-800 relative text-zinc-950 dark:text-white"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <!-- Close Button -->
                <button type="button" @click="bookingModal = false" class="absolute top-4 right-4 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-xl font-bold p-1">
                    ✕
                </button>

                <div x-show="!bookingSuccess">
                    <div class="mb-5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400">Appointment Booking</span>
                        <h3 class="text-xl font-black text-zinc-950 dark:text-white mt-0.5" x-text="activeService ? activeService.title : ''"></h3>
                        <p class="text-xs text-zinc-500 mt-1" x-text="'Provider: ' + (activeService ? activeService.provider : '')"></p>
                    </div>

                    <!-- Work Gallery Preview (Photos & Videos - Image 2 Screen 12) -->
                    <div class="mb-5">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-zinc-400 mb-2">Verified Work Gallery</label>
                        <div class="grid grid-cols-4 gap-2">
                            <div class="aspect-square rounded-xl bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                                <img src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=150&q=80" alt="Work 1" class="w-full h-full object-cover">
                            </div>
                            <div class="aspect-square rounded-xl bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                                <img src="https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=150&q=80" alt="Work 2" class="w-full h-full object-cover">
                            </div>
                            <div class="aspect-square rounded-xl bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                                <img src="https://images.unsplash.com/photo-1505798577917-a65157d3320a?auto=format&fit=crop&w=150&q=80" alt="Work 3" class="w-full h-full object-cover">
                            </div>
                            <div class="aspect-square rounded-xl bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 flex items-center justify-center font-bold text-xs">
                                +18 More
                            </div>
                        </div>
                    </div>

                    <!-- Select Date (Calendar Chips) -->
                    <div class="mb-4">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-zinc-400 mb-2">Select Preferred Date</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" @click="selectedDate = 'Today, Oct 20'" 
                                    :class="selectedDate === 'Today, Oct 20' ? 'border-purple-600 bg-purple-50 text-purple-900 dark:bg-purple-950/40 dark:text-white font-bold' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2.5 rounded-xl border text-xs text-center transition">
                                Today
                            </button>
                            <button type="button" @click="selectedDate = 'Tomorrow, Oct 21'" 
                                    :class="selectedDate === 'Tomorrow, Oct 21' ? 'border-purple-600 bg-purple-50 text-purple-900 dark:bg-purple-950/40 dark:text-white font-bold' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2.5 rounded-xl border text-xs text-center transition">
                                Tomorrow
                            </button>
                            <button type="button" @click="selectedDate = 'Wed, Oct 22'" 
                                    :class="selectedDate === 'Wed, Oct 22' ? 'border-purple-600 bg-purple-50 text-purple-900 dark:bg-purple-950/40 dark:text-white font-bold' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2.5 rounded-xl border text-xs text-center transition">
                                Wed, Oct 22
                            </button>
                        </div>
                    </div>

                    <!-- Select Time Slot -->
                    <div class="mb-6">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-zinc-400 mb-2">Select Time Window</label>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <button type="button" @click="selectedSlot = '09:00 AM - 11:00 AM'" 
                                    :class="selectedSlot === '09:00 AM - 11:00 AM' ? 'border-purple-600 bg-purple-50 text-purple-900 dark:bg-purple-950/40 dark:text-white font-bold' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2.5 px-3 rounded-xl border text-center transition">
                                🌅 09:00 - 11:00 AM
                            </button>
                            <button type="button" @click="selectedSlot = '11:00 AM - 01:00 PM'" 
                                    :class="selectedSlot === '11:00 AM - 01:00 PM' ? 'border-purple-600 bg-purple-50 text-purple-900 dark:bg-purple-950/40 dark:text-white font-bold' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2.5 px-3 rounded-xl border text-center transition">
                                ☀️ 11:00 AM - 01:00 PM
                            </button>
                            <button type="button" @click="selectedSlot = '02:00 PM - 04:00 PM'" 
                                    :class="selectedSlot === '02:00 PM - 04:00 PM' ? 'border-purple-600 bg-purple-50 text-purple-900 dark:bg-purple-950/40 dark:text-white font-bold' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2.5 px-3 rounded-xl border text-center transition">
                                🌤️ 02:00 - 04:00 PM
                            </button>
                            <button type="button" @click="selectedSlot = '05:00 PM - 07:00 PM'" 
                                    :class="selectedSlot === '05:00 PM - 07:00 PM' ? 'border-purple-600 bg-purple-50 text-purple-900 dark:bg-purple-950/40 dark:text-white font-bold' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2.5 px-3 rounded-xl border text-center transition">
                                🌙 05:00 - 07:00 PM
                            </button>
                        </div>
                    </div>

                    <!-- Bottom Bar -->
                    <div class="flex items-center justify-between pt-3 border-t border-zinc-100 dark:border-zinc-800">
                        <div>
                            <span class="text-xs text-zinc-400 block">Total Est.</span>
                            <span class="text-base font-black text-purple-600 dark:text-purple-400" x-text="activeService ? activeService.rate : ''"></span>
                        </div>

                        <button type="button" @click="confirmBooking()" 
                                class="px-6 py-3 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase tracking-wider transition shadow-lg shadow-purple-600/20 active:scale-95 cursor-pointer">
                            Confirm Booking
                        </button>
                    </div>
                </div>

                <!-- Success State -->
                <div x-show="bookingSuccess" class="text-center py-8">
                    <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4 text-3xl">
                        ✓
                    </div>
                    <h3 class="text-lg font-black text-zinc-950 dark:text-white mb-1">Appointment Confirmed!</h3>
                    <p class="text-xs text-zinc-500 mb-4">Your appointment with <span x-text="activeService ? activeService.provider : ''"></span> has been booked.</p>
                    <span class="text-[11px] text-purple-600 font-bold">Redirecting to your Bookings tab...</span>
                </div>

            </div>
        </div>

    </div>

    <script>
    function k71ServicesApp() {
        return {
            activeTab: 'services', // 'services', 'bookings'
            bookingModal: false,
            activeService: null,
            selectedDate: 'Tomorrow, Oct 25',
            selectedSlot: '10:00 AM - 12:00 PM',
            bookingSuccess: false,
            bookingsTab: 'upcoming', // 'upcoming', 'completed'

            openBooking(svc) {
                this.activeService = svc;
                this.bookingSuccess = false;
                this.bookingModal = true;
            },
            confirmBooking() {
                if (!this.activeService) return;
                const targetId = this.activeService.id;
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                fetch('{{ route('cart.add') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        product_id: targetId,
                        quantity: 1
                    })
                }).then(r => r.json()).then(data => {
                    if (data.success !== false) {
                        window.location.href = '{{ route('cart.index') }}';
                    } else {
                        window.location.href = '/product/' + targetId;
                    }
                }).catch(e => {
                    window.location.href = '/product/' + targetId;
                });
            }
        };
    }
    </script>
</x-layouts.public>
