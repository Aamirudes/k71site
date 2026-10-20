<x-layouts.public 
    title="Become a Verified Vendor — Register Your Business on K71"
    metaDescription="Join India's unified multi-sector marketplace. Register your boutique, factory, cloud kitchen, or pro service on K71 with zero setup fees."
>
    <div class="min-h-[calc(100vh-16rem)] flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl w-full space-y-8 p-6 sm:p-10 rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 shadow-xl" x-data="vendorOnboarding()">
            
            <!-- Header -->
            <div class="text-center">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-violet-100 dark:bg-violet-950/60 text-violet-700 dark:text-violet-300 text-xs font-bold uppercase tracking-wider mb-3 border border-violet-200 dark:border-violet-800/60">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Merchant Onboarding Portal</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black text-zinc-950 dark:text-white tracking-tight">
                    Start Selling on K71
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 max-w-lg mx-auto">
                    Launch your dedicated storefront, reach millions of buyers, and integrate directly with our logistics &amp; escrow settlement engines.
                </p>
            </div>
            
            <form class="mt-8 space-y-8" action="{{ route('vendor.register') }}" method="POST">
                @csrf
                
                <div class="space-y-6">
                    <!-- SECTION 1: MANDATORY CATEGORY SELECTION -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-black text-zinc-950 dark:text-white">
                                1. Select Your Store Category &amp; Trade Sector <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[11px] font-bold text-violet-600 dark:text-violet-400">Required</span>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-4">
                            Choose the primary vertical you are selling in. Your vendor portal, catalog attributes, and commission structure will be customized specifically for your trade.
                        </p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3" x-data="{ selectedType: '{{ old('vendor_type', '') }}' }">
                            
                            <!-- 1. Clothing & Fashion -->
                            <label :class="selectedType === 'clothing' ? 'ring-2 ring-violet-600 border-violet-600 bg-violet-50/50 dark:bg-violet-950/40' : 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-800/50 hover:border-zinc-400 dark:hover:border-zinc-600'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="clothing" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">👗</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-zinc-900 dark:text-white">Clothing &amp; Fashion</div>
                                        <span x-show="selectedType === 'clothing'" class="text-emerald-500 text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-snug">Apparel, footwear, jewelry &amp; accessories</div>
                                </div>
                            </label>

                            <!-- 2. Food & Cloud Kitchen -->
                            <label :class="selectedType === 'food' ? 'ring-2 ring-emerald-600 border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/40' : 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-800/50 hover:border-zinc-400 dark:hover:border-zinc-600'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="food" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">🍔</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-zinc-900 dark:text-white">Food &amp; Cloud Kitchen</div>
                                        <span x-show="selectedType === 'food'" class="text-emerald-500 text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-snug">Dishes, bakery, veg/non-veg &amp; 30m dispatch</div>
                                </div>
                            </label>

                            <!-- 3. Services -->
                            <label :class="selectedType === 'services' ? 'ring-2 ring-purple-600 border-purple-600 bg-purple-50/50 dark:bg-purple-950/40' : 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-800/50 hover:border-zinc-400 dark:hover:border-zinc-600'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="services" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">🧹</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-zinc-900 dark:text-white">On-Demand Services</div>
                                        <span x-show="selectedType === 'services'" class="text-emerald-500 text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-snug">Electricians, plumbers, deep cleaning &amp; slots</div>
                                </div>
                            </label>

                            <!-- 4. Used Items -->
                            <label :class="selectedType === 'used' ? 'ring-2 ring-zinc-900 dark:ring-white border-zinc-900 dark:border-white bg-zinc-100 dark:bg-zinc-800' : 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-800/50 hover:border-zinc-400 dark:hover:border-zinc-600'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="used" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">🔄</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-zinc-900 dark:text-white">Pre-Owned &amp; Used</div>
                                        <span x-show="selectedType === 'used'" class="text-emerald-500 text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-snug">Second-hand tech, vehicles &amp; offer bidding</div>
                                </div>
                            </label>

                            <!-- 5. Heavy Machinery / B2B -->
                            <label :class="selectedType === 'machinery' ? 'ring-2 ring-amber-600 border-amber-600 bg-amber-50/50 dark:bg-amber-950/40' : 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-800/50 hover:border-zinc-400 dark:hover:border-zinc-600'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="machinery" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">🛠️</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-zinc-900 dark:text-white">Machinery &amp; B2B RFQ</div>
                                        <span x-show="selectedType === 'machinery'" class="text-emerald-500 text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-snug">Marble saws, CNC, industrial raw materials</div>
                                </div>
                            </label>

                            <!-- 6. Electronics & IT -->
                            <label :class="selectedType === 'electronics' ? 'ring-2 ring-blue-600 border-blue-600 bg-blue-50/50 dark:bg-blue-950/40' : 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-800/50 hover:border-zinc-400 dark:hover:border-zinc-600'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="electronics" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">📱</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-zinc-900 dark:text-white">Electronics &amp; IT</div>
                                        <span x-show="selectedType === 'electronics'" class="text-emerald-500 text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-snug">Laptops, audio, smart gadgets &amp; POS</div>
                                </div>
                            </label>

                            <!-- 7. Automotive -->
                            <label :class="selectedType === 'automotive' ? 'ring-2 ring-red-600 border-red-600 bg-red-50/50 dark:bg-red-950/40' : 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-800/50 hover:border-zinc-400 dark:hover:border-zinc-600'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="automotive" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">🚗</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-zinc-900 dark:text-white">Automotive &amp; Spares</div>
                                        <span x-show="selectedType === 'automotive'" class="text-emerald-500 text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-snug">Cars, bikes, fleets &amp; OEM spare parts</div>
                                </div>
                            </label>

                            <!-- 8. Digital Gigs -->
                            <label :class="selectedType === 'digital_services' ? 'ring-2 ring-violet-600 border-violet-600 bg-violet-50/50 dark:bg-violet-950/40' : 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-800/50 hover:border-zinc-400 dark:hover:border-zinc-600'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="digital_services" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">💼</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-zinc-900 dark:text-white">Digital Gigs &amp; Freelance</div>
                                        <span x-show="selectedType === 'digital_services'" class="text-emerald-500 text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-snug">Web dev, AI/ML, UI/UX, 3D &amp; copywriting</div>
                                </div>
                            </label>

                            <!-- 9. Real Estate -->
                            <label :class="selectedType === 'real_estate' ? 'ring-2 ring-emerald-700 border-emerald-700 bg-emerald-50/50 dark:bg-emerald-950/40' : 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-800/50 hover:border-zinc-400 dark:hover:border-zinc-600'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="real_estate" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">🏡</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-zinc-900 dark:text-white">Real Estate &amp; Assets</div>
                                        <span x-show="selectedType === 'real_estate'" class="text-emerald-500 text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-snug">Villas, commercial floors &amp; verified leads</div>
                                </div>
                            </label>

                            <!-- 10. Home & Living -->
                            <label :class="selectedType === 'home_living' ? 'ring-2 ring-amber-700 border-amber-700 bg-amber-50/50 dark:bg-amber-950/40' : 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-800/50 hover:border-zinc-400 dark:hover:border-zinc-600'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="home_living" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">🏠</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-zinc-900 dark:text-white">Home &amp; Living</div>
                                        <span x-show="selectedType === 'home_living'" class="text-emerald-500 text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-snug">Solid wood furniture, decor &amp; renovation</div>
                                </div>
                            </label>

                            <!-- 11. Beauty & Wellness -->
                            <label :class="selectedType === 'beauty' ? 'ring-2 ring-pink-600 border-pink-600 bg-pink-50/50 dark:bg-pink-950/40' : 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-800/50 hover:border-zinc-400 dark:hover:border-zinc-600'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="beauty" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">💄</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-zinc-900 dark:text-white">Beauty &amp; Personal Care</div>
                                        <span x-show="selectedType === 'beauty'" class="text-emerald-500 text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-snug">Dermatology skincare, makeup &amp; perfumes</div>
                                </div>
                            </label>

                            <!-- 12. Groceries & Essentials -->
                            <label :class="selectedType === 'groceries' ? 'ring-2 ring-lime-700 border-lime-700 bg-lime-50/50 dark:bg-lime-950/40' : 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-800/50 hover:border-zinc-400 dark:hover:border-zinc-600'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="groceries" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">🛒</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-zinc-900 dark:text-white">Groceries &amp; FMCG</div>
                                        <span x-show="selectedType === 'groceries'" class="text-emerald-500 text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-snug">Produce, organic staples, pantry &amp; FMCG</div>
                                </div>
                            </label>

                            <!-- 13. Toys & Sports -->
                            <label :class="selectedType === 'toys_sports' ? 'ring-2 ring-cyan-700 border-cyan-700 bg-cyan-50/50 dark:bg-cyan-950/40' : 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-800/50 hover:border-zinc-400 dark:hover:border-zinc-600'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="toys_sports" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">🧸</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-zinc-900 dark:text-white">Toys, Books &amp; Sports</div>
                                        <span x-show="selectedType === 'toys_sports'" class="text-emerald-500 text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-snug">STEM robotics, academic books &amp; gym gear</div>
                                </div>
                            </label>

                        </div>
                        @error('vendor_type')<p class="mt-2 text-xs font-bold text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <!-- SECTION 2: STORE DETAILS -->
                    <div class="pt-6 border-t border-zinc-200 dark:border-zinc-800 space-y-4">
                        <h3 class="text-sm font-black uppercase tracking-wider text-zinc-950 dark:text-white">2. Store Information</h3>
                        
                        <div>
                            <label for="shop_name" class="block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                Store / Brand Name <span class="text-red-500">*</span>
                            </label>
                            <input id="shop_name" name="shop_name" type="text" required x-model="shopName" @input.debounce.500ms="updateSlugFromName" 
                                   class="w-full px-4 py-3 rounded-2xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-sm font-medium text-zinc-950 dark:text-white focus:ring-2 focus:ring-violet-600 focus:outline-none transition" 
                                   placeholder="e.g. Acme Crafted Goods" value="{{ old('shop_name') }}">
                            @error('shop_name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        
                        <div>
                            <label for="slug" class="block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                Store URL / Slug <span class="text-red-500">*</span>
                            </label>
                            <div class="flex rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-700 shadow-xs">
                                <span class="inline-flex items-center px-4 bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 text-xs font-mono">
                                    @if(config('marketplace.use_subdomains'))
                                        https://
                                    @else
                                        {{ url(config('marketplace.storefront_path_prefix')) }}/
                                    @endif
                                </span>
                                <input id="slug" name="slug" type="text" required x-model="slug" @input.debounce.500ms="manualSlugUpdate" 
                                       class="flex-1 px-4 py-3 bg-zinc-50 dark:bg-zinc-900 border-none text-sm font-mono font-bold text-zinc-950 dark:text-white focus:outline-none focus:ring-0" 
                                       placeholder="my-store">
                                @if(config('marketplace.use_subdomains'))
                                <span class="inline-flex items-center px-4 bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 text-xs font-mono">
                                    .{{ config('marketplace.domain') }}
                                </span>
                                @endif
                            </div>
                            <div class="mt-1.5 text-xs flex items-center">
                                <span x-show="isChecking" class="text-zinc-500">Checking availability...</span>
                                <span x-show="!isChecking && slugMessage" :class="isAvailable ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-red-600 dark:text-red-400 font-bold'" x-text="slugMessage" style="display: none;"></span>
                            </div>
                            @error('slug')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <!-- Pickup Address -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">
                                Physical Workshop / Dispatch Address (For Couriers)
                            </label>
                            <input name="address" type="text" 
                                   class="w-full px-4 py-3 rounded-2xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-sm font-medium text-zinc-950 dark:text-white focus:ring-2 focus:ring-violet-600 focus:outline-none transition mb-3" 
                                   placeholder="Shop / Unit No, Industrial Estate, Street" value="{{ old('address') }}">
                            
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-zinc-500 mb-1">City</label>
                                    <input name="city" type="text" class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-xs text-zinc-950 dark:text-white" placeholder="e.g. Bengaluru" value="{{ old('city') }}">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-zinc-500 mb-1">State</label>
                                    <input name="state" type="text" class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-xs text-zinc-950 dark:text-white" placeholder="e.g. Karnataka" value="{{ old('state') }}">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-zinc-500 mb-1">6-Digit Pincode</label>
                                    <input name="pincode" type="text" maxlength="6" pattern="[0-9]{6}" class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-xs font-mono text-zinc-950 dark:text-white" placeholder="e.g. 560078" value="{{ old('pincode') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: PERSONAL DETAILS (FOR GUESTS) -->
                    @guest
                    <div class="pt-6 border-t border-zinc-200 dark:border-zinc-800 space-y-4">
                        <h3 class="text-sm font-black uppercase tracking-wider text-zinc-950 dark:text-white">3. Merchant Account Credentials</h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="first_name" class="block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">First Name <span class="text-red-500">*</span></label>
                                <input id="first_name" name="first_name" type="text" required class="w-full px-4 py-3 rounded-2xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-sm text-zinc-950 dark:text-white" value="{{ old('first_name') }}">
                                @error('first_name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="last_name" class="block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">Last Name <span class="text-red-500">*</span></label>
                                <input id="last_name" name="last_name" type="text" required class="w-full px-4 py-3 rounded-2xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-sm text-zinc-950 dark:text-white" value="{{ old('last_name') }}">
                                @error('last_name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="username" class="block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">Username <span class="text-red-500">*</span></label>
                                <input id="username" name="username" type="text" required x-model="username" @input.debounce.500ms="checkUsername" class="w-full px-4 py-3 rounded-2xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-sm text-zinc-950 dark:text-white" value="{{ old('username') }}">
                                <div class="mt-1 text-xs flex items-center h-4">
                                    <span x-show="isCheckingUsername" class="text-zinc-500">Checking...</span>
                                    <span x-show="!isCheckingUsername && usernameMessage" :class="isUsernameAvailable ? 'text-emerald-600 font-bold' : 'text-red-600 font-bold'" x-text="usernameMessage" style="display: none;"></span>
                                </div>
                                @error('username')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">Email Address <span class="text-red-500">*</span></label>
                                <input id="email" name="email" type="email" autocomplete="email" required class="w-full px-4 py-3 rounded-2xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-sm text-zinc-950 dark:text-white" value="{{ old('email') }}">
                                @error('email')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">Phone Number <span class="text-red-500">*</span></label>
                            <input id="phone" name="phone" type="tel" required placeholder="+919876543210" class="w-full px-4 py-3 rounded-2xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-sm text-zinc-950 dark:text-white" value="{{ old('phone') }}">
                            @error('phone')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div x-data="{ showPass: false }">
                                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">Password <span class="text-red-500">*</span></label>
                                <input id="password" name="password" :type="showPass ? 'text' : 'password'" required class="w-full px-4 py-3 rounded-2xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-sm text-zinc-950 dark:text-white" placeholder="••••••••">
                                @error('password')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                                <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full px-4 py-3 rounded-2xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-sm text-zinc-950 dark:text-white" placeholder="••••••••">
                            </div>
                        </div>
                    </div>
                    @endguest
                    
                    @auth
                        @if(!auth()->user()->phone)
                        <div class="pt-6 border-t border-zinc-200 dark:border-zinc-800 space-y-4">
                            <h3 class="text-sm font-black uppercase tracking-wider text-zinc-950 dark:text-white">3. Contact Verification</h3>
                            <div>
                                <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1">Phone Number <span class="text-red-500">*</span></label>
                                <input id="phone" name="phone" type="tel" required placeholder="+919876543210" class="w-full px-4 py-3 rounded-2xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-sm text-zinc-950 dark:text-white" value="{{ old('phone') }}">
                                @error('phone')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        @endif
                    @endauth

                    <!-- Submit Button -->
                    <div class="pt-6 border-t border-zinc-200 dark:border-zinc-800">
                        <button type="submit" :disabled="(!isAvailable && slug.length > 0) || (!isUsernameAvailable && username.length > 0)" 
                                class="w-full py-4 px-6 rounded-2xl bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:hover:bg-zinc-200 text-white dark:text-zinc-950 font-black text-sm uppercase tracking-wider shadow-xl transition-all duration-200 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer flex items-center justify-center gap-2">
                            <span>Register &amp; Launch Storefront</span>
                            <span>&rarr;</span>
                        </button>
                        <p class="mt-3 text-center text-xs text-zinc-500 dark:text-zinc-400">
                            By registering, you agree to the <a href="#" class="text-violet-600 dark:text-violet-400 underline">K71 Merchant Terms</a> and Escrow Settlement Policy.
                        </p>
                    </div>

                </div>
            </form>

        </div>
    </div>
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('vendorOnboarding', () => ({
                shopName: '{{ old('shop_name', '') }}',
                slug: '{{ old('slug', '') }}',
                username: '{{ old('username', '') }}',
                isChecking: false,
                isAvailable: false,
                slugMessage: '',
                slugManuallyEdited: false,
                isCheckingUsername: false,
                isUsernameAvailable: true,
                usernameMessage: '',
                
                updateSlugFromName() {
                    if (!this.slugManuallyEdited) {
                        this.slug = this.shopName.toLowerCase()
                            .replace(/[^\w\s-]/g, '')
                            .replace(/[\s_-]+/g, '-')
                            .replace(/^-+|-+$/g, '');
                        
                        if (this.slug.length >= 3) {
                            this.checkSlug();
                        }
                    }
                },
                
                manualSlugUpdate() {
                    this.slugManuallyEdited = true;
                    this.checkSlug();
                },
                
                async checkSlug() {
                    if (this.slug.length < 3) {
                        this.isAvailable = false;
                        this.slugMessage = 'Slug must be at least 3 characters.';
                        return;
                    }
                    
                    this.isChecking = true;
                    this.slugMessage = '';
                    
                    try {
                        const response = await fetch(`{{ route('api.validate.slug') }}?slug=${encodeURIComponent(this.slug)}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        this.isAvailable = data.available;
                        this.slugMessage = data.message;
                    } catch (error) {
                        this.isAvailable = false;
                        this.slugMessage = 'Error checking availability.';
                    } finally {
                        this.isChecking = false;
                    }
                },
                
                async checkUsername() {
                    if (!this.username) {
                        this.isUsernameAvailable = true;
                        this.usernameMessage = '';
                        return;
                    }
                    
                    if (this.username.length < 3) {
                        this.isUsernameAvailable = false;
                        this.usernameMessage = 'Username must be at least 3 characters.';
                        return;
                    }
                    
                    this.isCheckingUsername = true;
                    this.usernameMessage = '';
                    
                    try {
                        const response = await fetch(`{{ route('api.validate.username') }}?username=${encodeURIComponent(this.username)}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        this.isUsernameAvailable = data.available;
                        this.usernameMessage = data.message;
                    } catch (error) {
                        this.isUsernameAvailable = false;
                        this.usernameMessage = 'Error checking availability.';
                    } finally {
                        this.isCheckingUsername = false;
                    }
                },
                
                init() {
                    if (this.slug) {
                        this.checkSlug();
                        this.slugManuallyEdited = true;
                    }
                    if (this.username) {
                        this.checkUsername();
                    }
                }
            }));
        });
    </script>
</x-layouts.public>
