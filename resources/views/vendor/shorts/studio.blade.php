<x-layouts.vendor title="Creator Shorts Studio">
    <!-- Dedicated Shorts Studio CSS -->
    <link rel="stylesheet" href="{{ asset('shorts-assets/css/zsu-studio.css') }}">

    <div class="space-y-6" x-data="vendorStudio()">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between border-b border-gray-200 pb-5">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-600 border border-rose-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                        Shoppable Video Commerce
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-gray-900 tracking-tight flex items-center gap-2">
                    🎬 Creator Shorts Studio
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-gray-500">
                    Upload engaging 9:16 reels, tag store products, and boost to millions of shoppers across K71.
                </p>
            </div>
            <div class="mt-4 sm:mt-0 flex items-center gap-3">
                <!-- Coin Balance Pill -->
                <div class="flex items-center gap-2 bg-amber-50 border border-amber-200/80 px-3.5 py-1.5 rounded-xl shadow-xs">
                    <span class="text-xl">🪙</span>
                    <div>
                        <div class="text-[10px] font-bold text-amber-700 uppercase tracking-wider">Creator Coins</div>
                        <div class="text-sm font-black text-amber-950" x-text="coinsBalance">
                            {{ $stats['coins_balance'] }}
                        </div>
                    </div>
                </div>

                <a href="{{ route('shorts.index') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition shadow-xs">
                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Live Shorts Feed
                </a>
            </div>
        </div>

        <!-- Metrics Overview Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Published Shorts</div>
                <div class="text-2xl font-black text-gray-900 mt-1">{{ $stats['total_shorts'] }}</div>
                <div class="text-[11px] text-emerald-600 font-semibold mt-0.5">Active across K71</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Video Views</div>
                <div class="text-2xl font-black text-gray-900 mt-1">{{ number_format($stats['total_views']) }}</div>
                <div class="text-[11px] text-gray-400 font-medium mt-0.5">Impressions generated</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Shopper Likes</div>
                <div class="text-2xl font-black text-gray-900 mt-1">{{ number_format($stats['total_likes']) }}</div>
                <div class="text-[11px] text-rose-500 font-semibold mt-0.5">❤️ Positive engagement</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Reel Shares</div>
                <div class="text-2xl font-black text-gray-900 mt-1">{{ number_format($stats['total_shares']) }}</div>
                <div class="text-[11px] text-blue-600 font-semibold mt-0.5">WhatsApp & Social</div>
            </div>
        </div>

        <!-- Tab Navigation Strip -->
        <div class="border-b border-gray-200">
            <nav class="flex space-x-6">
                <button @click="activeTab = 'upload'" :class="activeTab === 'upload' ? 'border-rose-500 text-rose-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-3 px-1 border-b-2 font-bold text-sm flex items-center gap-2 transition">
                    <span>📤</span> Upload New Short
                </button>
                <button @click="activeTab = 'my-shorts'; loadMyShorts()" :class="activeTab === 'my-shorts' ? 'border-rose-500 text-rose-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-3 px-1 border-b-2 font-bold text-sm flex items-center gap-2 transition">
                    <span>🎬</span> My Shorts ({{ $stats['total_shorts'] }})
                </button>
                <button @click="activeTab = 'boost'; loadBoosts()" :class="activeTab === 'boost' ? 'border-rose-500 text-rose-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-3 px-1 border-b-2 font-bold text-sm flex items-center gap-2 transition">
                    <span>🚀</span> Boost Campaigns
                </button>
                <button @click="activeTab = 'coins'; loadCoins()" :class="activeTab === 'coins' ? 'border-rose-500 text-rose-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-3 px-1 border-b-2 font-bold text-sm flex items-center gap-2 transition">
                    <span>🪙</span> Coin Ledger
                </button>
            </nav>
        </div>

        <!-- TAB 1: UPLOAD WIZARD -->
        <div x-show="activeTab === 'upload'" class="bg-white rounded-3xl border border-gray-200/80 p-6 md:p-8 shadow-xs">
            <div class="max-w-3xl mx-auto space-y-6">
                <!-- Dropzone / Video Upload -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Video File (MP4, WebM, MOV — 9:16 Ratio)</label>
                    
                    <div x-show="!videoUrl" 
                         @dragover.prevent="dragOver = true" 
                         @dragleave.prevent="dragOver = false"
                         @drop.prevent="handleDrop($event)"
                         :class="dragOver ? 'border-rose-500 bg-rose-50/50' : 'border-gray-300 hover:border-gray-400 bg-gray-50/50'"
                         class="border-2 border-dashed rounded-2xl p-8 text-center transition cursor-pointer relative">
                        <input type="file" accept="video/mp4,video/webm,video/quicktime" @change="handleFileSelect($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="text-4xl mb-2">📹</div>
                        <h4 class="text-sm font-bold text-gray-900">Drag & drop your video reel here</h4>
                        <p class="text-xs text-gray-500 mt-1">MP4, WebM up to 100MB. Recommended ratio: 9:16 vertical.</p>
                        <button type="button" class="mt-4 px-4 py-2 bg-rose-600 text-white rounded-xl text-xs font-bold shadow-xs hover:bg-rose-500 transition">
                            Browse Local File
                        </button>
                    </div>

                    <!-- Upload Progress -->
                    <div x-show="uploading" class="mt-3">
                        <div class="flex items-center justify-between text-xs font-bold text-gray-600 mb-1">
                            <span>Uploading video…</span>
                            <span x-text="uploadProgress + '%'">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-rose-600 h-2 rounded-full transition-all duration-200" :style="'width: ' + uploadProgress + '%'"></div>
                        </div>
                    </div>

                    <!-- Live Video Preview & Thumbnail Canvas -->
                    <div x-show="videoUrl" class="mt-4 flex flex-col md:flex-row gap-6 items-start">
                        <div class="relative w-48 aspect-[9/16] bg-black rounded-2xl overflow-hidden shadow-md shrink-0">
                            <video :src="videoUrl" id="videoPreview" class="w-full h-full object-cover" controls playsinline></video>
                        </div>
                        <div class="flex-1 space-y-3">
                            <div class="flex items-center gap-2 text-xs font-bold text-emerald-600">
                                <span>✓</span> Video uploaded successfully
                            </div>
                            <button type="button" @click="captureFrame()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold rounded-xl transition">
                                📸 Capture Current Frame as Thumbnail
                            </button>
                            <div x-show="thumbDataUri" class="flex items-center gap-3 pt-2">
                                <img :src="thumbDataUri" class="w-16 aspect-[9/16] object-cover rounded-lg border border-gray-200 shadow-xs" alt="Captured Frame">
                                <span class="text-xs text-gray-500 font-medium">Selected frame thumbnail preview</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Title & Description -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Short Title *</label>
                        <input type="text" x-model="form.title" placeholder="e.g. Pure Cotton Summer Anarkali Kurti 🌸" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Category</label>
                        <select x-model="form.category_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm font-medium">
                            <option value="">Select Category</option>
                            @foreach($categories as $c)
                            <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Aspect Ratio</label>
                        <select x-model="form.ratio" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm font-medium">
                            <option value="9:16">9:16 — Vertical Mobile (Recommended)</option>
                            <option value="1:1">1:1 — Square</option>
                            <option value="4:5">4:5 — Portrait</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Description & Hashtags</label>
                        <textarea x-model="form.description" rows="3" placeholder="Tell shoppers about this look, sizing, fabric care, and styling tips… #summerfashion #kurti" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm font-medium"></textarea>
                    </div>
                </div>

                <!-- Shoppable Tagged Products -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Tagged Store Products</label>
                        <button type="button" @click="showProductSearch = true" class="text-xs font-bold text-rose-600 hover:text-rose-700 flex items-center gap-1">
                            + Tag Products from Store
                        </button>
                    </div>

                    <div x-show="selectedProducts.length === 0" class="border border-gray-200 rounded-2xl p-4 text-center bg-gray-50/50">
                        <p class="text-xs text-gray-500">No products tagged yet. Tag items so viewers can buy directly while watching.</p>
                    </div>

                    <div x-show="selectedProducts.length > 0" class="flex flex-wrap gap-2">
                        <template x-for="(p, index) in selectedProducts" :key="p.id">
                            <div class="inline-flex items-center gap-2 bg-gray-100 border border-gray-200 px-3 py-1.5 rounded-xl text-xs font-semibold">
                                <img :src="p.image" class="w-6 h-6 object-cover rounded-md" :alt="p.title">
                                <span x-text="p.title" class="max-w-[150px] truncate"></span>
                                <span class="text-emerald-700 font-bold" x-text="p.price"></span>
                                <button type="button" @click="removeProduct(index)" class="text-gray-400 hover:text-rose-600 ml-1">✕</button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t border-gray-200 flex justify-end">
                    <button type="button" @click="saveShort()" :disabled="saving || !form.title || !videoUrl" class="px-6 py-3 bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white font-bold rounded-2xl text-sm shadow-md transition disabled:opacity-50 flex items-center gap-2">
                        <span x-show="saving">Saving…</span>
                        <span x-show="!saving">Publish Short Now 🚀</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- TAB 2: MY SHORTS -->
        <div x-show="activeTab === 'my-shorts'" class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs">
            <div x-show="loadingShorts" class="py-12 text-center text-sm text-gray-400">Loading your shorts…</div>
            <div x-show="!loadingShorts && myShorts.length === 0" class="py-12 text-center">
                <div class="text-4xl mb-2">🎬</div>
                <h4 class="text-sm font-bold text-gray-900">No Shorts Published Yet</h4>
                <p class="text-xs text-gray-500 mt-1">Start by uploading your first short video reel.</p>
                <button type="button" @click="activeTab = 'upload'" class="mt-4 px-4 py-2 bg-rose-600 text-white rounded-xl text-xs font-bold">Upload a Short</button>
            </div>

            <div x-show="!loadingShorts && myShorts.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                <template x-for="short in myShorts" :key="short.id">
                    <div class="bg-gray-50 rounded-2xl overflow-hidden border border-gray-200 group flex flex-col justify-between">
                        <div class="relative aspect-[9/16] bg-black overflow-hidden">
                            <img :src="short.thumb_url" :alt="short.title" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                            
                            <!-- Boosted Badge -->
                            <div x-show="short.is_boosted" class="absolute top-2 left-2 px-2 py-0.5 rounded-full bg-rose-600 text-white text-[9px] font-bold">
                                🚀 BOOSTED
                            </div>

                            <div class="absolute bottom-2 left-2 right-2 text-white">
                                <div class="text-[10px] font-semibold text-white/80" x-text="short.views + ' views • ' + short.likes + ' likes'"></div>
                                <h4 class="text-xs font-bold line-clamp-2 mt-0.5" x-text="short.title"></h4>
                            </div>
                        </div>

                        <div class="p-2.5 flex items-center justify-between gap-1 border-t border-gray-200 bg-white">
                            <button type="button" @click="openBoostModal(short)" class="flex-1 py-1 px-2 rounded-lg bg-rose-50 text-rose-700 text-[11px] font-bold hover:bg-rose-100 transition text-center">
                                Boost Reel
                            </button>
                            <button type="button" @click="deleteShort(short.id)" class="p-1 rounded-lg text-gray-400 hover:text-red-600 transition" title="Delete">
                                🗑️
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- TAB 3: BOOST CAMPAIGNS -->
        <div x-show="activeTab === 'boost'" class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs space-y-6">
            <div>
                <h3 class="text-base font-bold text-gray-900">Boost Tiers & Exposure Packages</h3>
                <p class="text-xs text-gray-500">Spend Creator Coins to place your video reels on the top featured rails and explore feeds.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="border-2 border-rose-100 rounded-2xl p-5 bg-rose-50/30 flex flex-col justify-between">
                    <div>
                        <div class="text-xs font-bold text-rose-600 uppercase tracking-wider">Standard Boost</div>
                        <div class="text-2xl font-black text-gray-900 mt-1">199 <span class="text-xs font-bold text-gray-500">Coins</span></div>
                        <p class="text-xs text-gray-600 mt-2">Active for 24 Hours. Placed on top category rails and home explore carousel.</p>
                    </div>
                </div>
                <div class="border-2 border-amber-200 rounded-2xl p-5 bg-amber-50/30 flex flex-col justify-between">
                    <div>
                        <div class="text-xs font-bold text-amber-700 uppercase tracking-wider">Prime Boost (Recommended)</div>
                        <div class="text-2xl font-black text-gray-900 mt-1">499 <span class="text-xs font-bold text-gray-500">Coins</span></div>
                        <p class="text-xs text-gray-600 mt-2">Active for 3 Days (72h). 2.5x impression multiplier and priority rank.</p>
                    </div>
                </div>
                <div class="border-2 border-purple-200 rounded-2xl p-5 bg-purple-50/30 flex flex-col justify-between">
                    <div>
                        <div class="text-xs font-bold text-purple-700 uppercase tracking-wider">Elite Boost</div>
                        <div class="text-2xl font-black text-gray-900 mt-1">999 <span class="text-xs font-bold text-gray-500">Coins</span></div>
                        <p class="text-xs text-gray-600 mt-2">Active for 7 Full Days. Permanent top placement, glowing creator badge, and feed hero.</p>
                    </div>
                </div>
            </div>

            <div>
                <h4 class="text-sm font-bold text-gray-900 mb-3">Your Active & Past Boosts</h4>
                <div x-show="boostsList.length === 0" class="border border-gray-200 rounded-2xl p-6 text-center text-xs text-gray-500">
                    No boost campaigns run yet. Select any short from "My Shorts" to launch a campaign.
                </div>
                <div x-show="boostsList.length > 0" class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-500 uppercase font-bold border-b border-gray-200">
                            <tr>
                                <th class="p-3">Short</th>
                                <th class="p-3">Tier</th>
                                <th class="p-3">Coins</th>
                                <th class="p-3">Impressions</th>
                                <th class="p-3">Clicks</th>
                                <th class="p-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="b in boostsList" :key="b.id">
                                <tr>
                                    <td class="p-3 font-semibold text-gray-800" x-text="b.short_title"></td>
                                    <td class="p-3 font-bold" x-text="b.boost_type"></td>
                                    <td class="p-3" x-text="b.coins_spent"></td>
                                    <td class="p-3 font-bold text-rose-600" x-text="b.impressions"></td>
                                    <td class="p-3 font-bold text-emerald-600" x-text="b.clicks"></td>
                                    <td class="p-3">
                                        <span :class="b.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'" class="px-2 py-0.5 rounded-full text-[10px] font-bold" x-text="b.status"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 4: COIN LEDGER -->
        <div x-show="activeTab === 'coins'" class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Creator Coins Ledger</h3>
                    <p class="text-xs text-gray-500">Track all coin credits, boost expenditures, and rewards.</p>
                </div>
            </div>

            <div x-show="coinsHistoryList.length === 0" class="border border-gray-200 rounded-2xl p-6 text-center text-xs text-gray-500">
                No coin transactions recorded yet.
            </div>

            <div x-show="coinsHistoryList.length > 0" class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 text-gray-500 uppercase font-bold border-b border-gray-200">
                        <tr>
                            <th class="p-3">Date</th>
                            <th class="p-3">Reason</th>
                            <th class="p-3">Amount</th>
                            <th class="p-3">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="item in coinsHistoryList" :key="item.id">
                            <tr>
                                <td class="p-3 text-gray-500" x-text="item.created_at"></td>
                                <td class="p-3 font-semibold text-gray-800" x-text="item.reason"></td>
                                <td class="p-3 font-bold" :class="item.delta.startsWith('+') ? 'text-emerald-600' : 'text-rose-600'" x-text="item.delta"></td>
                                <td class="p-3 font-black text-gray-900" x-text="item.balance"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PRODUCT SEARCH MODAL -->
        <div x-show="showProductSearch" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div @click.away="showProductSearch = false" class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-gray-900">Tag Products from Your Store</h3>
                    <button @click="showProductSearch = false" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
                </div>
                <input type="text" x-model="productSearchTerm" @input.debounce.300ms="searchProducts()" placeholder="Search product name or SKU…" class="w-full px-4 py-2 rounded-xl border border-gray-300 text-sm">
                
                <div class="max-h-64 overflow-y-auto divide-y divide-gray-100">
                    <template x-for="p in searchResults" :key="p.id">
                        <div class="py-2.5 flex items-center justify-between hover:bg-gray-50 px-2 rounded-xl">
                            <div class="flex items-center gap-3">
                                <img :src="p.image" class="w-10 h-10 object-cover rounded-lg border border-gray-200" :alt="p.title">
                                <div>
                                    <div class="text-xs font-bold text-gray-900 truncate max-w-[220px]" x-text="p.title"></div>
                                    <div class="text-xs text-emerald-700 font-bold" x-text="p.price"></div>
                                </div>
                            </div>
                            <button type="button" @click="tagProduct(p)" class="px-3 py-1 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg text-xs font-bold transition">
                                Tag
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- BOOST MODAL -->
        <div x-show="showBoostModal" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div @click.away="showBoostModal = false" class="bg-white rounded-3xl p-6 w-full max-w-md shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-gray-900">Boost Short</h3>
                    <button @click="showBoostModal = false" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
                </div>

                <p class="text-xs text-gray-500">Select boost exposure package for <span class="font-bold text-gray-800" x-text="targetShort?.title"></span></p>

                <div class="space-y-2">
                    <label class="flex items-center justify-between p-3.5 border rounded-2xl cursor-pointer hover:bg-gray-50 transition" :class="boostTier === 'standard' ? 'border-rose-500 bg-rose-50/20' : 'border-gray-200'">
                        <div class="flex items-center gap-3">
                            <input type="radio" value="standard" x-model="boostTier" class="text-rose-600">
                            <div>
                                <div class="text-xs font-bold text-gray-900">Standard Boost (24 Hours)</div>
                                <div class="text-[11px] text-gray-500">Top category rails</div>
                            </div>
                        </div>
                        <span class="text-xs font-black text-rose-600">199 Coins</span>
                    </label>

                    <label class="flex items-center justify-between p-3.5 border rounded-2xl cursor-pointer hover:bg-gray-50 transition" :class="boostTier === 'prime' ? 'border-rose-500 bg-rose-50/20' : 'border-gray-200'">
                        <div class="flex items-center gap-3">
                            <input type="radio" value="prime" x-model="boostTier" class="text-rose-600">
                            <div>
                                <div class="text-xs font-bold text-gray-900">Prime Boost (3 Days)</div>
                                <div class="text-[11px] text-gray-500">2.5x Priority discovery</div>
                            </div>
                        </div>
                        <span class="text-xs font-black text-amber-700">499 Coins</span>
                    </label>

                    <label class="flex items-center justify-between p-3.5 border rounded-2xl cursor-pointer hover:bg-gray-50 transition" :class="boostTier === 'elite' ? 'border-rose-500 bg-rose-50/20' : 'border-gray-200'">
                        <div class="flex items-center gap-3">
                            <input type="radio" value="elite" x-model="boostTier" class="text-rose-600">
                            <div>
                                <div class="text-xs font-bold text-gray-900">Elite Boost (7 Days)</div>
                                <div class="text-[11px] text-gray-500">Hero placements & gold badge</div>
                            </div>
                        </div>
                        <span class="text-xs font-black text-purple-700">999 Coins</span>
                    </label>
                </div>

                <div class="pt-3 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" @click="showBoostModal = false" class="px-4 py-2 border border-gray-300 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="button" @click="submitBoost()" :disabled="boosting" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold shadow-xs">
                        <span x-show="boosting">Activating…</span>
                        <span x-show="!boosting">Confirm & Activate Boost</span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Alpine Controller Component -->
    <script>
        function vendorStudio() {
            return {
                activeTab: 'upload',
                dragOver: false,
                uploading: false,
                uploadProgress: 0,
                saving: false,
                videoUrl: '',
                videoPath: '',
                thumbDataUri: '',
                coinsBalance: {{ $stats['coins_balance'] }},
                form: {
                    title: '',
                    description: '',
                    category_id: '',
                    ratio: '9:16',
                },
                selectedProducts: [],
                showProductSearch: false,
                productSearchTerm: '',
                searchResults: [],
                myShorts: [],
                loadingShorts: false,
                boostsList: [],
                coinsHistoryList: [],
                showBoostModal: false,
                targetShort: null,
                boostTier: 'standard',
                boosting: false,

                handleDrop(e) {
                    this.dragOver = false;
                    const file = e.dataTransfer.files[0];
                    if (file) this.uploadVideo(file);
                },

                handleFileSelect(e) {
                    const file = e.target.files[0];
                    if (file) this.uploadVideo(file);
                },

                uploadVideo(file) {
                    if (!file) return;
                    this.uploading = true;
                    this.uploadProgress = 15;
                    const formData = new FormData();
                    formData.append('video', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '{{ route("vendor.shorts.upload") }}', true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                    xhr.upload.onprogress = (e) => {
                        if (e.lengthComputable) {
                            this.uploadProgress = Math.max(15, Math.round((e.loaded / e.total) * 95));
                        }
                    };

                    xhr.onload = () => {
                        this.uploading = false;
                        if (xhr.status === 200) {
                            try {
                                const res = JSON.parse(xhr.responseText);
                                if (res.success) {
                                    this.videoUrl = res.url;
                                    this.videoPath = res.video_path;
                                    this.uploadProgress = 100;
                                    this.$nextTick(() => {
                                        const vEl = document.getElementById('videoPreview');
                                        if (vEl) {
                                            vEl.onloadeddata = () => {
                                                setTimeout(() => this.captureFrame(), 400);
                                            };
                                        }
                                    });
                                    return;
                                } else {
                                    alert(res.message || 'Failed to upload video.');
                                    return;
                                }
                            } catch (e) {}
                        }

                        let errMsg = 'Failed to upload video. Please ensure file is valid MP4 or WebM under 100MB.';
                        try {
                            const errRes = JSON.parse(xhr.responseText);
                            if (errRes.message) errMsg = errRes.message;
                            else if (errRes.error) errMsg = errRes.error;
                        } catch (e) {}
                        alert(errMsg);
                    };

                    xhr.onerror = () => {
                        this.uploading = false;
                        alert('Upload failed due to network error.');
                    };

                    xhr.send(formData);
                },

                captureFrame() {
                    const video = document.getElementById('videoPreview');
                    if (!video) return;

                    const canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth || 720;
                    canvas.height = video.videoHeight || 1280;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    this.thumbDataUri = canvas.toDataURL('image/jpeg', 0.85);
                },

                searchProducts() {
                    fetch('{{ route("vendor.shorts.ajax") }}?action=zsu_vendor_search_products&term=' + encodeURIComponent(this.productSearchTerm))
                        .then(r => r.json())
                        .then(d => {
                            if (d.success) this.searchResults = d.products;
                        });
                },

                tagProduct(p) {
                    if (!this.selectedProducts.find(x => x.id === p.id)) {
                        this.selectedProducts.push(p);
                    }
                    this.showProductSearch = false;
                },

                removeProduct(idx) {
                    this.selectedProducts.splice(idx, 1);
                },

                saveShort() {
                    this.saving = true;
                    const payload = {
                        action: 'zsu_save_short',
                        _token: '{{ csrf_token() }}',
                        title: this.form.title,
                        description: this.form.description,
                        category_id: this.form.category_id,
                        ratio: this.form.ratio,
                        video_url: this.videoUrl,
                        video_path: this.videoPath,
                        thumb_datauri: this.thumbDataUri,
                        product_ids: this.selectedProducts.map(p => p.id),
                    };

                    fetch('{{ route("vendor.shorts.ajax") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify(payload),
                    })
                    .then(r => r.json())
                    .then(res => {
                        this.saving = false;
                        if (res.success) {
                            alert('Short published successfully!');
                            this.form.title = '';
                            this.form.description = '';
                            this.videoUrl = '';
                            this.thumbDataUri = '';
                            this.selectedProducts = [];
                            this.activeTab = 'my-shorts';
                            this.loadMyShorts();
                        } else {
                            alert(res.message || 'Error saving short.');
                        }
                    })
                    .catch(() => {
                        this.saving = false;
                        alert('Network error while saving short.');
                    });
                },

                loadMyShorts() {
                    this.loadingShorts = true;
                    fetch('{{ route("vendor.shorts.ajax") }}?action=zsu_vendor_get_shorts')
                        .then(r => r.json())
                        .then(d => {
                            this.loadingShorts = false;
                            if (d.success) this.myShorts = d.shorts;
                        });
                },

                deleteShort(id) {
                    if (!confirm('Are you sure you want to delete this short?')) return;
                    fetch('{{ route("vendor.shorts.ajax") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ action: 'zsu_vendor_delete_short', short_id: id }),
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) this.loadMyShorts();
                    });
                },

                openBoostModal(short) {
                    this.targetShort = short;
                    this.showBoostModal = true;
                },

                submitBoost() {
                    this.boosting = true;
                    fetch('{{ route("vendor.shorts.ajax") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({
                            action: 'zsu_request_boost',
                            short_id: this.targetShort.id,
                            tier: this.boostTier,
                        }),
                    })
                    .then(r => r.json())
                    .then(d => {
                        this.boosting = false;
                        this.showBoostModal = false;
                        if (d.success) {
                            alert(d.message);
                            this.loadMyShorts();
                            this.loadBoosts();
                            this.fetchCoins();
                        } else {
                            alert(d.message);
                        }
                    });
                },

                loadBoosts() {
                    fetch('{{ route("vendor.shorts.ajax") }}?action=zsu_vendor_get_boosts')
                        .then(r => r.json())
                        .then(d => {
                            if (d.success) this.boostsList = d.boosts;
                        });
                },

                loadCoins() {
                    fetch('{{ route("vendor.shorts.ajax") }}?action=zsu_coins_history')
                        .then(r => r.json())
                        .then(d => {
                            if (d.success) this.coinsHistoryList = d.history;
                        });
                },

                fetchCoins() {
                    fetch('{{ route("vendor.shorts.ajax") }}?action=zsu_get_coins_balance')
                        .then(r => r.json())
                        .then(d => {
                            if (d.success) this.coinsBalance = d.balance;
                        });
                }
            };
        }
    </script>
</x-layouts.vendor>
