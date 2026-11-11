<x-layouts.public 
    title="Become a Verified Vendor — Register Your Business on K71"
    metaDescription="Join India's unified multi-sector marketplace. Register your boutique, factory, cloud kitchen, or pro service on K71 with zero setup fees."
    :fullWidth="true"
>
    <!-- Grail Obsidian Onboarding Backdrop -->
    <section class="grail-hero-band text-[#EFE9F2] border-b border-white/10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-14 text-center">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/8 text-[#ECD2F3] text-xs font-bold uppercase tracking-wider mb-4 border border-white/15">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Merchant Onboarding Portal</span>
            </div>
            <h1 class="grail-display text-[clamp(2.2rem,5vw,3.5rem)] font-bold text-white tracking-tight">
                Start Selling on K71
            </h1>
            <p class="mt-3 text-xs sm:text-sm text-white/70 max-w-lg mx-auto">
                Launch your dedicated storefront, reach millions of buyers, and integrate directly with our logistics &amp; escrow settlement engines.
            </p>
        </div>
    </section>

    <!-- Grail Stone Sheet -->
    <div class="grail-sheet -mt-4 sm:-mt-5 rounded-t-[28px] sm:rounded-t-[36px] py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto space-y-8 p-6 sm:p-10 rounded-[28px] bg-white border border-black/8 shadow-[0_18px_50px_rgba(26,16,24,0.08)]" x-data="vendorOnboarding()">
            
            <form class="space-y-8" action="{{ route('vendor.register') }}" method="POST">
                @csrf
                
                <div class="space-y-6">
                    <!-- SECTION 1: MANDATORY CATEGORY SELECTION -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-black text-[#1A1018]">
                                1. Select Your Store Category &amp; Trade Sector <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] font-bold text-[#623562] bg-[#F8EFFA] px-2.5 py-0.5 rounded-full">Required</span>
                        </div>
                        <p class="text-xs text-[#7A6B78] mb-4">
                            Choose the primary vertical you are selling in. Your vendor portal, catalog attributes, and commission structure will be customized specifically for your trade.
                        </p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3" x-data="{ selectedType: '{{ old('vendor_type', '') }}' }">
                            
                            <!-- 1. Clothing & Fashion -->
                            <label :class="selectedType === 'clothing' ? 'ring-2 ring-[#1A1018] border-[#1A1018] bg-[#F8EFFA]/65' : 'border-black/10 bg-[#FAFAF8] hover:border-black/25'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="clothing" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">👗</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-[#1A1018]">Clothing &amp; Fashion</div>
                                        <span x-show="selectedType === 'clothing'" class="text-[#623562] text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-[#7A6B78] mt-0.5 leading-snug">Apparel, footwear, jewelry &amp; accessories</div>
                                </div>
                            </label>

                            <!-- 1b. Men's Fashion & Apparel -->
                            <label :class="selectedType === 'mens' ? 'ring-2 ring-[#1A1018] border-[#1A1018] bg-[#F8EFFA]/65' : 'border-black/10 bg-[#FAFAF8] hover:border-black/25'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="mens" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">👔</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-[#1A1018]">Men's Fashion &amp; Apparel</div>
                                        <span x-show="selectedType === 'mens'" class="text-[#623562] text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-[#7A6B78] mt-0.5 leading-snug">Shirts, ethnic kurtas, trousers, suits &amp; footwear</div>
                                </div>
                            </label>

                            <!-- 2. Food & Cloud Kitchen -->
                            <label :class="selectedType === 'food' ? 'ring-2 ring-[#1A1018] border-[#1A1018] bg-[#F8EFFA]/65' : 'border-black/10 bg-[#FAFAF8] hover:border-black/25'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="food" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">🍔</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-[#1A1018]">Food &amp; Cloud Kitchen</div>
                                        <span x-show="selectedType === 'food'" class="text-[#623562] text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-[#7A6B78] mt-0.5 leading-snug">Dishes, bakery, veg/non-veg &amp; 30m dispatch</div>
                                </div>
                            </label>

                            <!-- 2b. Feasto Hyperlocal Quick-Commerce -->
                            <label :class="selectedType === 'feasto' ? 'ring-2 ring-[#0C831F] border-[#0C831F] bg-emerald-50/70' : 'border-black/10 bg-[#FAFAF8] hover:border-black/25'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="feasto" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">⚡</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-[#1A1018] flex items-center gap-1.5">
                                            <span>Feasto Hyperlocal Store</span>
                                            <span class="px-1.5 py-0.2 rounded-md bg-[#0C831F] text-white text-[9px] font-extrabold uppercase">10 Mins</span>
                                        </div>
                                        <span x-show="selectedType === 'feasto'" class="text-[#0C831F] text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-[#7A6B78] mt-0.5 leading-snug">Fast moving groceries, snacks &amp; drinks. Direct K71 store with full vendor dashboard &amp; 10-min delivery fleet dispatch.</div>
                                </div>
                            </label>

                            <!-- 3. Heavy Machinery / B2B -->
                            <label :class="selectedType === 'machinery' ? 'ring-2 ring-[#1A1018] border-[#1A1018] bg-[#F8EFFA]/65' : 'border-black/10 bg-[#FAFAF8] hover:border-black/25'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="machinery" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">🛠️</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-[#1A1018]">Machinery &amp; B2B RFQ</div>
                                        <span x-show="selectedType === 'machinery'" class="text-[#623562] text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-[#7A6B78] mt-0.5 leading-snug">Marble saws, CNC, industrial raw materials</div>
                                </div>
                            </label>

                            <!-- 6. Electronics & IT -->
                            <label :class="selectedType === 'electronics' ? 'ring-2 ring-[#1A1018] border-[#1A1018] bg-[#F8EFFA]/65' : 'border-black/10 bg-[#FAFAF8] hover:border-black/25'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="electronics" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">📱</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-[#1A1018]">Electronics &amp; IT</div>
                                        <span x-show="selectedType === 'electronics'" class="text-[#623562] text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-[#7A6B78] mt-0.5 leading-snug">Laptops, audio, smart gadgets &amp; POS</div>
                                </div>
                            </label>

                            <!-- 7. Automotive -->
                            <label :class="selectedType === 'automotive' ? 'ring-2 ring-[#1A1018] border-[#1A1018] bg-[#F8EFFA]/65' : 'border-black/10 bg-[#FAFAF8] hover:border-black/25'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="automotive" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">🚗</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-[#1A1018]">Automotive &amp; Spares</div>
                                        <span x-show="selectedType === 'automotive'" class="text-[#623562] text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-[#7A6B78] mt-0.5 leading-snug">Cars, bikes, fleets &amp; OEM spare parts</div>
                                </div>
                            </label>

                            <!-- 9. Real Estate -->
                            <label :class="selectedType === 'real_estate' ? 'ring-2 ring-[#1A1018] border-[#1A1018] bg-[#F8EFFA]/65' : 'border-black/10 bg-[#FAFAF8] hover:border-black/25'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="real_estate" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">🏡</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-[#1A1018]">Real Estate &amp; Assets</div>
                                        <span x-show="selectedType === 'real_estate'" class="text-[#623562] text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-[#7A6B78] mt-0.5 leading-snug">Villas, commercial floors &amp; verified leads</div>
                                </div>
                            </label>

                            <!-- 10. Home & Living -->
                            <label :class="selectedType === 'home_living' ? 'ring-2 ring-[#1A1018] border-[#1A1018] bg-[#F8EFFA]/65' : 'border-black/10 bg-[#FAFAF8] hover:border-black/25'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="home_living" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">🏠</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-[#1A1018]">Home &amp; Living</div>
                                        <span x-show="selectedType === 'home_living'" class="text-[#623562] text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-[#7A6B78] mt-0.5 leading-snug">Solid wood furniture, decor &amp; renovation</div>
                                </div>
                            </label>

                            <!-- 11. Beauty & Wellness -->
                            <label :class="selectedType === 'beauty' ? 'ring-2 ring-[#1A1018] border-[#1A1018] bg-[#F8EFFA]/65' : 'border-black/10 bg-[#FAFAF8] hover:border-black/25'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="beauty" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">💄</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-[#1A1018]">Beauty &amp; Personal Care</div>
                                        <span x-show="selectedType === 'beauty'" class="text-[#623562] text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-[#7A6B78] mt-0.5 leading-snug">Dermatology skincare, makeup &amp; perfumes</div>
                                </div>
                            </label>

                            <!-- 12. Groceries & Essentials -->
                            <label :class="selectedType === 'groceries' ? 'ring-2 ring-[#1A1018] border-[#1A1018] bg-[#F8EFFA]/65' : 'border-black/10 bg-[#FAFAF8] hover:border-black/25'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="groceries" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">🛒</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-[#1A1018]">Groceries &amp; FMCG</div>
                                        <span x-show="selectedType === 'groceries'" class="text-[#623562] text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-[#7A6B78] mt-0.5 leading-snug">Produce, organic staples, pantry &amp; FMCG</div>
                                </div>
                            </label>

                            <!-- 13. Toys & Sports -->
                            <label :class="selectedType === 'toys_sports' ? 'ring-2 ring-[#1A1018] border-[#1A1018] bg-[#F8EFFA]/65' : 'border-black/10 bg-[#FAFAF8] hover:border-black/25'"
                                   class="relative flex items-start p-3.5 rounded-2xl border cursor-pointer transition-all shadow-xs group">
                                <input type="radio" name="vendor_type" value="toys_sports" x-model="selectedType" class="sr-only" required>
                                <div class="text-2xl mr-3 shrink-0 group-hover:scale-110 transition-transform">🧸</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-black text-[#1A1018]">Toys, Books &amp; Sports</div>
                                        <span x-show="selectedType === 'toys_sports'" class="text-[#623562] text-xs font-black">✓</span>
                                    </div>
                                    <div class="text-[11px] text-[#7A6B78] mt-0.5 leading-snug">STEM robotics, academic books &amp; gym gear</div>
                                </div>
                            </label>

                        </div>
                        @error('vendor_type')<p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- SECTION 2: STORE DETAILS -->
                    <div class="pt-6 border-t border-black/8 space-y-4">
                        <h3 class="text-sm font-black uppercase tracking-wider text-[#1A1018]">2. Store Information</h3>
                        
                        <div>
                            <label for="shop_name" class="block text-xs font-bold uppercase tracking-wider text-[#7A6B78] mb-1">
                                Store / Brand Name <span class="text-rose-500">*</span>
                            </label>
                            <input id="shop_name" name="shop_name" type="text" required x-model="shopName" @input.debounce.500ms="updateSlugFromName" 
                                   class="grail-input-light w-full px-4 py-3 rounded-2xl text-sm font-medium" 
                                   placeholder="e.g. Acme Crafted Goods" value="{{ old('shop_name') }}">
                            @error('shop_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        
                        <div>
                            <label for="slug" class="block text-xs font-bold uppercase tracking-wider text-[#7A6B78] mb-1">
                                Store URL / Slug <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex rounded-2xl overflow-hidden border border-black/15 shadow-xs">
                                <span class="inline-flex items-center px-4 bg-[#F4F1EC] text-[#7A6B78] text-xs font-mono">
                                    @if(config('marketplace.use_subdomains'))
                                        https://
                                    @else
                                        {{ url(config('marketplace.storefront_path_prefix')) }}/
                                    @endif
                                </span>
                                <input id="slug" name="slug" type="text" required x-model="slug" @input.debounce.500ms="manualSlugUpdate" 
                                       class="flex-1 px-4 py-3 bg-[#FAFAF8] border-none text-sm font-mono font-bold text-[#1A1018] focus:outline-none focus:ring-0" 
                                       placeholder="my-store">
                                @if(config('marketplace.use_subdomains'))
                                <span class="inline-flex items-center px-4 bg-[#F4F1EC] text-[#7A6B78] text-xs font-mono">
                                    .{{ config('marketplace.domain') }}
                                </span>
                                @endif
                            </div>
                            <div class="mt-1.5 text-xs flex items-center">
                                <span x-show="isChecking" class="text-[#7A6B78]">Checking availability...</span>
                                <span x-show="!isChecking && slugMessage" :class="isAvailable ? 'text-emerald-600 font-bold' : 'text-rose-600 font-bold'" x-text="slugMessage" style="display: none;"></span>
                            </div>
                            @error('slug')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <!-- Pickup Address -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#7A6B78] mb-1">
                                Physical Workshop / Dispatch Address (For Couriers)
                            </label>
                            <input name="address" type="text" 
                                   class="grail-input-light w-full px-4 py-3 rounded-2xl text-sm font-medium mb-3" 
                                   placeholder="Shop / Unit No, Industrial Estate, Street" value="{{ old('address') }}">
                            
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-[#7A6B78] mb-1">City</label>
                                    <input name="city" type="text" class="grail-input-light w-full px-3.5 py-2.5 rounded-xl text-xs" placeholder="e.g. Bengaluru" value="{{ old('city') }}">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-[#7A6B78] mb-1">State</label>
                                    <input name="state" type="text" class="grail-input-light w-full px-3.5 py-2.5 rounded-xl text-xs" placeholder="e.g. Karnataka" value="{{ old('state') }}">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-[#7A6B78] mb-1">6-Digit Pincode</label>
                                    <input name="pincode" type="text" maxlength="6" pattern="[0-9]{6}" class="grail-input-light w-full px-3.5 py-2.5 rounded-xl text-xs font-mono" placeholder="e.g. 560078" value="{{ old('pincode') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: PERSONAL DETAILS (FOR GUESTS) -->
                    @guest
                    <div class="pt-6 border-t border-black/8 space-y-4">
                        <h3 class="text-sm font-black uppercase tracking-wider text-[#1A1018]">3. Merchant Account Credentials</h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="first_name" class="block text-xs font-bold uppercase tracking-wider text-[#7A6B78] mb-1">First Name <span class="text-rose-500">*</span></label>
                                <input id="first_name" name="first_name" type="text" required class="grail-input-light w-full px-4 py-3 rounded-2xl text-sm" value="{{ old('first_name') }}">
                                @error('first_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="last_name" class="block text-xs font-bold uppercase tracking-wider text-[#7A6B78] mb-1">Last Name <span class="text-rose-500">*</span></label>
                                <input id="last_name" name="last_name" type="text" required class="grail-input-light w-full px-4 py-3 rounded-2xl text-sm" value="{{ old('last_name') }}">
                                @error('last_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="username" class="block text-xs font-bold uppercase tracking-wider text-[#7A6B78] mb-1">Username <span class="text-rose-500">*</span></label>
                                <input id="username" name="username" type="text" required x-model="username" @input.debounce.500ms="checkUsername" class="grail-input-light w-full px-4 py-3 rounded-2xl text-sm" value="{{ old('username') }}">
                                <div class="mt-1 text-xs flex items-center h-4">
                                    <span x-show="isCheckingUsername" class="text-[#7A6B78]">Checking...</span>
                                    <span x-show="!isCheckingUsername && usernameMessage" :class="isUsernameAvailable ? 'text-emerald-600 font-bold' : 'text-rose-600 font-bold'" x-text="usernameMessage" style="display: none;"></span>
                                </div>
                                @error('username')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-[#7A6B78] mb-1">Email Address <span class="text-rose-500">*</span></label>
                                <input id="email" name="email" type="email" autocomplete="email" required class="grail-input-light w-full px-4 py-3 rounded-2xl text-sm" value="{{ old('email') }}">
                                @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-[#7A6B78] mb-1">Phone Number <span class="text-rose-500">*</span></label>
                            <input id="phone" name="phone" type="tel" required placeholder="10-digit mobile number" class="grail-input-light w-full px-4 py-3 rounded-2xl text-sm" value="{{ old('phone') }}">
                            @error('phone')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div x-data="{ showPass: false }">
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-[#7A6B78] mb-1">Password <span class="text-rose-500">*</span></label>
                            <input id="password" name="password" :type="showPass ? 'text' : 'password'" required class="grail-input-light w-full px-4 py-3 rounded-2xl text-sm" placeholder="••••••••">
                            @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    @endguest
                    
                    @auth
                        @if(!auth()->user()->phone)
                        <div class="pt-6 border-t border-black/8 space-y-4">
                            <h3 class="text-sm font-black uppercase tracking-wider text-[#1A1018]">3. Contact Verification</h3>
                            <div>
                                <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-[#7A6B78] mb-1">Phone Number <span class="text-rose-500">*</span></label>
                                <input id="phone" name="phone" type="tel" required placeholder="10-digit mobile number" class="grail-input-light w-full px-4 py-3 rounded-2xl text-sm" value="{{ old('phone') }}">
                                @error('phone')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        @endif
                    @endauth

                    <!-- Submit Button -->
                    <div class="pt-6 border-t border-black/8">
                        <button type="submit" :disabled="(!isAvailable && slug.length > 0) || (!isUsernameAvailable && username.length > 0)" 
                                class="grail-btn-obsidian w-full py-4 px-6 rounded-2xl text-sm uppercase tracking-wider shadow-xl active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer flex items-center justify-center gap-2">
                            <span>Register &amp; Launch Storefront</span>
                            <span>&rarr;</span>
                        </button>
                        <p class="mt-3 text-center text-xs text-[#7A6B78]">
                            By registering, you agree to the <a href="{{ route('pages.terms') }}" class="text-[#623562] underline">K71 Merchant Terms</a> and Escrow Settlement Policy.
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
                            .replace(/^-+|-+$/g, '')
                            .slice(0, 30)
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
