<x-layouts.admin title="Homepage & SEO Studio">
    <div class="space-y-6" x-data="{ activeTab: 'seo' }">
            
            <!-- Header Bar -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-brand-surface shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-brand-sage mb-1">
                        <span class="px-2 py-0.5 rounded-full bg-brand-sage/10 text-brand-sage">Master Controller</span>
                        <span>•</span>
                        <span>Full Admin Rights</span>
                    </div>
                    <h1 class="text-3xl font-serif font-bold text-brand-text">Homepage & SEO Command Center</h1>
                    <p class="text-sm text-gray-500 mt-1">Configure search visibility, brand storytelling, hero aesthetics, and storefront merchandising.</p>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-medium border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-xs">
                        <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Preview Storefront
                    </a>
                    <button type="submit" form="homepage-studio-form" class="inline-flex items-center justify-center px-6 py-2.5 rounded-xl text-sm font-medium bg-brand-text text-white hover:bg-brand-sage transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Publish All Changes
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="p-4 bg-emerald-50 text-emerald-800 rounded-2xl border border-emerald-200 flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-md font-semibold">Live Now</span>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="p-4 bg-red-50 text-red-700 rounded-2xl border border-red-200">
                    <p class="font-bold text-sm mb-1">Please fix the following validation errors:</p>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Navigation Tabs -->
            <div class="bg-white rounded-2xl p-2 border border-brand-surface shadow-xs flex flex-wrap gap-1.5">
                <button type="button" @click="activeTab = 'seo'" :class="activeTab === 'seo' ? 'bg-brand-sage text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-all">
                    <span>🔍</span> Search Engine Optimization (SEO)
                </button>
                <button type="button" @click="activeTab = 'hero'" :class="activeTab === 'hero' ? 'bg-brand-sage text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-all">
                    <span>👑</span> Hero Section & Atmosphere
                </button>
                <button type="button" @click="activeTab = 'announcement'" :class="activeTab === 'announcement' ? 'bg-brand-sage text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-all">
                    <span>📢</span> Announcement Marquee
                </button>
                <button type="button" @click="activeTab = 'sections'" :class="activeTab === 'sections' ? 'bg-brand-sage text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-all">
                    <span>📑</span> Section Controls & Visibility
                </button>
                <button type="button" @click="activeTab = 'editorial'" :class="activeTab === 'editorial' ? 'bg-brand-sage text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-all">
                    <span>✍️</span> SEO Ranking Article
                </button>
            </div>

            <!-- Configuration Form -->
            <form id="homepage-studio-form" action="{{ route('admin.homepage.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- TAB 1: SEO & Metadata -->
                <div x-show="activeTab === 'seo'" class="space-y-6">
                    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-brand-surface shadow-sm space-y-6">
                        <div class="border-b border-gray-100 pb-4">
                            <h2 class="text-xl font-serif font-bold text-brand-text">Core Search Engine Optimization (Meta & Indexing)</h2>
                            <p class="text-xs text-gray-500 mt-1">Configure exactly how Google, Bing, and social crawlers perceive and rank your marketplace home page.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Meta Title -->
                            <div class="space-y-1.5 md:col-span-2">
                                <div class="flex justify-between items-center">
                                    <label class="block text-sm font-semibold text-gray-800">Homepage Meta Title (Title Tag)</label>
                                    <span class="text-xs text-gray-400">Recommended: 50–60 characters</span>
                                </div>
                                <input type="text" name="seo_meta_title" value="{{ old('seo_meta_title', $seo['seo_meta_title'] ?? 'The Curated Luxury Marketplace for Modern Designers & Artisans') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage focus:border-brand-sage text-sm shadow-xs" placeholder="e.g. ShopDost | Handcrafted Luxury Ethnic & Designer Fashion">
                                <p class="text-xs text-gray-500">Appears in Google search results and browser tab titles.</p>
                            </div>

                            <!-- Meta Description -->
                            <div class="space-y-1.5 md:col-span-2">
                                <div class="flex justify-between items-center">
                                    <label class="block text-sm font-semibold text-gray-800">Meta Description</label>
                                    <span class="text-xs text-gray-400">Recommended: 140–160 characters</span>
                                </div>
                                <textarea name="seo_meta_description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage focus:border-brand-sage text-sm shadow-xs" placeholder="Discover bespoke designer fashion, handcrafted ethnic wear, and luxury silhouettes directly from verified independent ateliers.">{{ old('seo_meta_description', $seo['seo_meta_description'] ?? 'Discover exclusive designer collections, handcrafted ethnic couture, and slow fashion directly from independent boutiques and master ateliers across India.') }}</textarea>
                                <p class="text-xs text-gray-500">The snippet shown below your search result in Google.</p>
                            </div>

                            <!-- Meta Keywords -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-800">Target Meta Keywords</label>
                                <input type="text" name="seo_meta_keywords" value="{{ old('seo_meta_keywords', $seo['seo_meta_keywords'] ?? 'luxury fashion, designer kurtis, handcrafted sarees, artisan atelier, multi-vendor fashion marketplace') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage focus:border-brand-sage text-sm shadow-xs" placeholder="keyword1, keyword2, keyword3">
                                <p class="text-xs text-gray-500">Comma-separated target terms.</p>
                            </div>

                            <!-- Canonical URL -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-800">Canonical URL</label>
                                <input type="url" name="seo_canonical_url" value="{{ old('seo_canonical_url', $seo['seo_canonical_url'] ?? url('/')) }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage focus:border-brand-sage text-sm shadow-xs" placeholder="https://yourdomain.com/">
                                <p class="text-xs text-gray-500">Prevents duplicate content penalties if accessing via aliases or params.</p>
                            </div>

                            <!-- Robots Directive -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-800">Robots Directive (Indexation)</label>
                                <select name="seo_robots" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage focus:border-brand-sage text-sm shadow-xs bg-white">
                                    <option value="index, follow" {{ ($seo['seo_robots'] ?? 'index, follow') === 'index, follow' ? 'selected' : '' }}>index, follow (Standard - Fully Index & Follow Links)</option>
                                    <option value="noindex, follow" {{ ($seo['seo_robots'] ?? '') === 'noindex, follow' ? 'selected' : '' }}>noindex, follow (Do not show on Google, but follow links)</option>
                                    <option value="index, nofollow" {{ ($seo['seo_robots'] ?? '') === 'index, nofollow' ? 'selected' : '' }}>index, nofollow (Index page only)</option>
                                    <option value="noindex, nofollow" {{ ($seo['seo_robots'] ?? '') === 'noindex, nofollow' ? 'selected' : '' }}>noindex, nofollow (Strictly Hidden)</option>
                                </select>
                            </div>

                            <!-- Schema.org JSON-LD Toggle -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-800">Schema.org JSON-LD Structured Data</label>
                                <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors">
                                    <input type="checkbox" name="seo_schema_enabled" value="1" {{ !empty($seo['seo_schema_enabled']) || !isset($seo['seo_schema_enabled']) ? 'checked' : '' }} class="w-4 h-4 text-brand-sage rounded focus:ring-brand-sage border-gray-300">
                                    <div>
                                        <p class="text-xs font-semibold text-gray-800">Inject Rich JSON-LD Microdata</p>
                                        <p class="text-[11px] text-gray-500">Generates Google WebSite, Organization, SearchAction, and Marketplace schemas.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- OpenGraph & Social Media Previews -->
                        <div class="border-t border-gray-100 pt-6 mt-6">
                            <h3 class="text-md font-serif font-bold text-brand-text mb-4">OpenGraph & Social Sharing (WhatsApp, Twitter, Facebook)</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-semibold text-gray-800">OG Sharing Title</label>
                                    <input type="text" name="seo_og_title" value="{{ old('seo_og_title', $seo['seo_og_title'] ?? 'ShopDost Marketplace | Modern Designer Couture & Artisan Fashion') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage focus:border-brand-sage text-sm shadow-xs">
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-sm font-semibold text-gray-800">OG Social Share Image (1200x630 px)</label>
                                    <input type="file" name="seo_og_image" accept="image/*" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-sage/10 file:text-brand-sage hover:file:bg-brand-sage/20">
                                    @if(!empty($seo['seo_og_image']))
                                        <div class="mt-2 flex items-center gap-3 p-2 bg-gray-50 rounded-xl border border-gray-200">
                                            <img src="{{ asset('storage/' . $seo['seo_og_image']) }}" alt="Current OG" class="w-16 h-10 object-cover rounded-md border border-gray-200">
                                            <span class="text-xs text-gray-600">Active custom social share card</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="space-y-1.5 md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-800">OG Social Description</label>
                                    <textarea name="seo_og_description" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage focus:border-brand-sage text-sm shadow-xs">{{ old('seo_og_description', $seo['seo_og_description'] ?? 'Discover handcrafted masterpieces, designer ensembles, and slow fashion created by independent Indian artisans.') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: Hero Visuals & Typography -->
                <div x-show="activeTab === 'hero'" class="space-y-6">
                    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-brand-surface shadow-sm space-y-6">
                        <div class="border-b border-gray-100 pb-4">
                            <h2 class="text-xl font-serif font-bold text-brand-text">Hero Presentation & Brand Atmosphere</h2>
                            <p class="text-xs text-gray-500 mt-1">This is the visual centerpiece that first greets visitors on desktop and mobile.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Hero Badge -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-800">Top Capsule Badge</label>
                                <input type="text" name="hero_badge" value="{{ old('hero_badge', $homepage['hero_badge'] ?? '✨ Redefining the Modern Marketplace') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage focus:border-brand-sage text-sm shadow-xs">
                                <p class="text-xs text-gray-500">Floats above the main title.</p>
                            </div>

                            <!-- Hero Image Upload -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-800">Hero Card / Background Artwork</label>
                                <input type="file" name="hero_image" accept="image/*" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-sage/10 file:text-brand-sage hover:file:bg-brand-sage/20">
                                @if(!empty($homepage['hero_image']))
                                    <div class="mt-2 flex items-center gap-3 p-2 bg-gray-50 rounded-xl border border-gray-200">
                                        <img src="{{ asset('storage/' . $homepage['hero_image']) }}" alt="Current Hero" class="w-16 h-12 object-cover rounded-md border border-gray-200">
                                        <span class="text-xs text-gray-600">Active custom hero image</span>
                                    </div>
                                @else
                                    <p class="text-[11px] text-gray-400">Currently using high-end atelier gradient with subtle luxury mesh.</p>
                                @endif
                            </div>

                            <!-- Headline Line 1 -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-800">Headline Line 1 (Bold Modern)</label>
                                <input type="text" name="hero_title_line1" value="{{ old('hero_title_line1', $homepage['hero_title_line1'] ?? 'Curated Fashion') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage focus:border-brand-sage text-sm shadow-xs font-bold">
                            </div>

                            <!-- Headline Line 2 -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-800">Headline Line 2 (Editorial Serif / Script)</label>
                                <input type="text" name="hero_title_line2" value="{{ old('hero_title_line2', $homepage['hero_title_line2'] ?? 'for the Modern Soul.') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage focus:border-brand-sage text-sm shadow-xs italic font-serif">
                            </div>

                            <!-- Hero Description -->
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-800">Hero Subtitle Story</label>
                                <textarea name="hero_description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage focus:border-brand-sage text-sm shadow-xs">{{ old('hero_description', $homepage['hero_description'] ?? 'Discover exclusive collections from independent designers. A seamless space where premium quality meets aesthetic brilliance.') }}</textarea>
                            </div>

                            <!-- Primary CTA -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-800">Primary CTA Button Text</label>
                                <input type="text" name="hero_cta_primary_text" value="{{ old('hero_cta_primary_text', $homepage['hero_cta_primary_text'] ?? 'Explore Collection') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage focus:border-brand-sage text-sm shadow-xs">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-800">Primary CTA Target Link</label>
                                <input type="text" name="hero_cta_primary_link" value="{{ old('hero_cta_primary_link', $homepage['hero_cta_primary_link'] ?? '#featured-products') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage focus:border-brand-sage text-sm shadow-xs" placeholder="/products or #featured-products">
                            </div>

                            <!-- Secondary CTA -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-800">Secondary CTA Button Text</label>
                                <input type="text" name="hero_cta_secondary_text" value="{{ old('hero_cta_secondary_text', $homepage['hero_cta_secondary_text'] ?? 'Become a Seller') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage focus:border-brand-sage text-sm shadow-xs">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-800">Secondary CTA Target Link</label>
                                <input type="text" name="hero_cta_secondary_link" value="{{ old('hero_cta_secondary_link', $homepage['hero_cta_secondary_link'] ?? '/vendor/register') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage focus:border-brand-sage text-sm shadow-xs" placeholder="/vendor/register">
                            </div>
                        </div>

                        <!-- Credentials Stats Bar -->
                        <div class="border-t border-gray-100 pt-6 mt-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-md font-serif font-bold text-brand-text">Trust Stats Bar (Credentials)</h3>
                                    <p class="text-xs text-gray-500">Displayed beneath the hero card to establish instant marketplace authority.</p>
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="hero_stats_enabled" value="1" {{ !empty($homepage['hero_stats_enabled']) || !isset($homepage['hero_stats_enabled']) ? 'checked' : '' }} class="w-4 h-4 text-brand-sage rounded focus:ring-brand-sage border-gray-300">
                                    <span class="text-xs font-semibold text-gray-700">Display Stats Bar</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 space-y-2">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Metric 1</span>
                                    <input type="text" name="hero_stat_1_val" value="{{ old('hero_stat_1_val', $homepage['hero_stat_1_val'] ?? '500+') }}" class="w-full px-3 py-1.5 rounded-lg border border-gray-300 text-sm font-bold" placeholder="500+">
                                    <input type="text" name="hero_stat_1_lbl" value="{{ old('hero_stat_1_lbl', $homepage['hero_stat_1_lbl'] ?? 'Artisan Boutiques') }}" class="w-full px-3 py-1.5 rounded-lg border border-gray-300 text-xs" placeholder="Artisan Boutiques">
                                </div>

                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 space-y-2">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Metric 2</span>
                                    <input type="text" name="hero_stat_2_val" value="{{ old('hero_stat_2_val', $homepage['hero_stat_2_val'] ?? '100%') }}" class="w-full px-3 py-1.5 rounded-lg border border-gray-300 text-sm font-bold" placeholder="100%">
                                    <input type="text" name="hero_stat_2_lbl" value="{{ old('hero_stat_2_lbl', $homepage['hero_stat_2_lbl'] ?? 'Pure Verified Fabrics') }}" class="w-full px-3 py-1.5 rounded-lg border border-gray-300 text-xs" placeholder="Pure Verified Fabrics">
                                </div>

                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 space-y-2">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Metric 3</span>
                                    <input type="text" name="hero_stat_3_val" value="{{ old('hero_stat_3_val', $homepage['hero_stat_3_val'] ?? '4.9★') }}" class="w-full px-3 py-1.5 rounded-lg border border-gray-300 text-sm font-bold" placeholder="4.9★">
                                    <input type="text" name="hero_stat_3_lbl" value="{{ old('hero_stat_3_lbl', $homepage['hero_stat_3_lbl'] ?? 'Atelier Rating') }}" class="w-full px-3 py-1.5 rounded-lg border border-gray-300 text-xs" placeholder="Atelier Rating">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: Announcement Marquee -->
                <div x-show="activeTab === 'announcement'" class="space-y-6">
                    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-brand-surface shadow-sm space-y-6">
                        <div class="border-b border-gray-100 pb-4 flex justify-between items-center">
                            <div>
                                <h2 class="text-xl font-serif font-bold text-brand-text">Header Announcement Marquee</h2>
                                <p class="text-xs text-gray-500 mt-1">Slim luxury ribbon pinned to the top of the homepage for urgent promotions, sales, or seasonal alerts.</p>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="announcement_enabled" value="1" {{ !empty($homepage['announcement_enabled']) || !isset($homepage['announcement_enabled']) ? 'checked' : '' }} class="w-4 h-4 text-brand-sage rounded focus:ring-brand-sage border-gray-300">
                                <span class="text-sm font-semibold text-gray-700">Enable Marquee</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-800">Badge Label</label>
                                <input type="text" name="announcement_badge" value="{{ old('announcement_badge', $homepage['announcement_badge'] ?? 'CURATED EDIT') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage text-sm shadow-xs uppercase">
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-800">Icon / Emoji</label>
                                <input type="text" name="announcement_emoji" value="{{ old('announcement_emoji', $homepage['announcement_emoji'] ?? '✦') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage text-sm shadow-xs">
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-800">Destination Link (Optional)</label>
                                <input type="text" name="announcement_link" value="{{ old('announcement_link', $homepage['announcement_link'] ?? '#new-arrivals') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage text-sm shadow-xs">
                            </div>

                            <div class="space-y-1.5 md:col-span-3">
                                <label class="block text-sm font-semibold text-gray-800">Marquee Message</label>
                                <input type="text" name="announcement_text" value="{{ old('announcement_text', $homepage['announcement_text'] ?? 'Complimentary pan-India white-glove shipping on all bespoke designer orders over ₹1,999') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage text-sm shadow-xs">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: Section Controls & Visibility -->
                <div x-show="activeTab === 'sections'" class="space-y-6">
                    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-brand-surface shadow-sm space-y-6">
                        <div class="border-b border-gray-100 pb-4">
                            <h2 class="text-xl font-serif font-bold text-brand-text">Homepage Layout & Merchandising Rails</h2>
                            <p class="text-xs text-gray-500 mt-1">Control which sections are visible and customize their editorial titles and subtitles.</p>
                        </div>

                        <div class="space-y-6">
                            <!-- Categories Section -->
                            <div class="p-5 bg-gray-50 rounded-2xl border border-gray-200 space-y-4">
                                <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
                                    <div class="flex-1 space-y-2 w-full">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" name="show_categories" value="1" id="sc_cat" {{ !empty($homepage['show_categories']) || !isset($homepage['show_categories']) ? 'checked' : '' }} class="w-4 h-4 text-brand-sage rounded focus:ring-brand-sage border-gray-300">
                                            <label for="sc_cat" class="text-sm font-bold text-gray-800 cursor-pointer">1. Curated Categories Rail</label>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pl-7">
                                            <input type="text" name="cat_section_title" value="{{ old('cat_section_title', $homepage['cat_section_title'] ?? 'Explore By Craft') }}" class="px-3 py-1.5 rounded-lg border border-gray-300 text-xs" placeholder="Section Title">
                                            <input type="text" name="cat_section_subtitle" value="{{ old('cat_section_subtitle', $homepage['cat_section_subtitle'] ?? 'Handpicked silhouettes across distinctive designer genres') }}" class="px-3 py-1.5 rounded-lg border border-gray-300 text-xs" placeholder="Section Subtitle">
                                        </div>
                                    </div>
                                </div>

                                <!-- Category Cover Images Manager -->
                                @if(isset($categories) && $categories->isNotEmpty())
                                    <div class="border-t border-gray-200 pt-4 mt-2">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-3">
                                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Category Card Cover Images</h4>
                                            <span class="text-[11px] text-gray-500">Auto-fetches from real products by default, or upload custom photos below.</span>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                            @foreach($categories as $cat)
                                                <div class="bg-white p-3.5 rounded-xl border border-gray-200 shadow-2xs space-y-2">
                                                    <div class="flex items-center justify-between">
                                                        <span class="font-bold text-xs text-gray-800">{{ $cat->name }}</span>
                                                        @if($cat->image)
                                                            <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">Custom Upload</span>
                                                        @else
                                                            <span class="text-[10px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200">Auto-Product</span>
                                                        @endif
                                                    </div>
                                                    
                                                    @if($cat->image)
                                                        <div class="relative w-full h-24 rounded-lg overflow-hidden border border-gray-100 bg-gray-50">
                                                            <img src="{{ asset('storage/' . $cat->image) }}" alt="{{ $cat->name }}" class="w-full h-full object-cover">
                                                        </div>
                                                        <label class="flex items-center gap-1.5 text-[11px] text-red-600 cursor-pointer pt-1">
                                                            <input type="checkbox" name="remove_category_image[{{ $cat->id }}]" value="1" class="w-3.5 h-3.5 rounded text-red-600 focus:ring-red-500 border-gray-300">
                                                            <span>Reset to auto-fetched product photo</span>
                                                        </label>
                                                    @endif

                                                    <div>
                                                        <label class="block text-[10px] font-medium text-gray-500 mb-1">
                                                            {{ $cat->image ? 'Replace Cover Image' : 'Upload Custom Cover' }}
                                                        </label>
                                                        <input type="file" name="category_images[{{ $cat->id }}]" accept="image/*" class="w-full text-[11px] text-gray-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-brand-sage/10 file:text-brand-sage hover:file:bg-brand-sage/20">
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Boutiques / Ateliers Section -->
                            <div class="p-5 bg-gray-50 rounded-2xl border border-gray-200 flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
                                <div class="flex-1 space-y-2 w-full">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" name="show_boutiques" value="1" id="sc_bout" {{ !empty($homepage['show_boutiques']) || !isset($homepage['show_boutiques']) ? 'checked' : '' }} class="w-4 h-4 text-brand-sage rounded focus:ring-brand-sage border-gray-300">
                                        <label for="sc_bout" class="text-sm font-bold text-gray-800 cursor-pointer">2. Featured Boutiques & Ateliers</label>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pl-7">
                                        <input type="text" name="boutiques_section_title" value="{{ old('boutiques_section_title', $homepage['boutiques_section_title'] ?? 'Master Boutiques') }}" class="px-3 py-1.5 rounded-lg border border-gray-300 text-xs" placeholder="Section Title">
                                        <input type="text" name="boutiques_section_subtitle" value="{{ old('boutiques_section_subtitle', $homepage['boutiques_section_subtitle'] ?? 'Meet the passionate artisans and slow-fashion ateliers behind the craft') }}" class="px-3 py-1.5 rounded-lg border border-gray-300 text-xs" placeholder="Section Subtitle">
                                    </div>
                                </div>
                            </div>

                            <!-- Featured Spotlight Products -->
                            <div class="p-5 bg-gray-50 rounded-2xl border border-gray-200 flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
                                <div class="flex-1 space-y-2 w-full">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" name="show_featured_products" value="1" id="sc_feat" {{ !empty($homepage['show_featured_products']) || !isset($homepage['show_featured_products']) ? 'checked' : '' }} class="w-4 h-4 text-brand-sage rounded focus:ring-brand-sage border-gray-300">
                                        <label for="sc_feat" class="text-sm font-bold text-gray-800 cursor-pointer">3. Curated Spotlight Picks (Featured)</label>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pl-7">
                                        <input type="text" name="spotlight_section_title" value="{{ old('spotlight_section_title', $homepage['spotlight_section_title'] ?? 'The Atelier Spotlight') }}" class="px-3 py-1.5 rounded-lg border border-gray-300 text-xs" placeholder="Section Title">
                                        <input type="text" name="spotlight_section_subtitle" value="{{ old('spotlight_section_subtitle', $homepage['spotlight_section_subtitle'] ?? 'Hand-selected by our fashion editors for supreme craftsmanship') }}" class="px-3 py-1.5 rounded-lg border border-gray-300 text-xs" placeholder="Section Subtitle">
                                    </div>
                                </div>
                            </div>

                            <!-- New Arrivals Section -->
                            <div class="p-5 bg-gray-50 rounded-2xl border border-gray-200 flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
                                <div class="flex-1 space-y-2 w-full">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" name="show_new_arrivals" value="1" id="sc_arr" {{ !empty($homepage['show_new_arrivals']) || !isset($homepage['show_new_arrivals']) ? 'checked' : '' }} class="w-4 h-4 text-brand-sage rounded focus:ring-brand-sage border-gray-300">
                                        <label for="sc_arr" class="text-sm font-bold text-gray-800 cursor-pointer">4. Just Landed (New Arrivals)</label>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pl-7">
                                        <input type="text" name="arrivals_section_title" value="{{ old('arrivals_section_title', $homepage['arrivals_section_title'] ?? 'Fresh Off The Loom') }}" class="px-3 py-1.5 rounded-lg border border-gray-300 text-xs" placeholder="Section Title">
                                        <input type="text" name="arrivals_section_subtitle" value="{{ old('arrivals_section_subtitle', $homepage['arrivals_section_subtitle'] ?? 'The latest couture and everyday modern statements added this week') }}" class="px-3 py-1.5 rounded-lg border border-gray-300 text-xs" placeholder="Section Subtitle">
                                    </div>
                                </div>
                            </div>

                            <!-- Trust Pillars & Values -->
                            <div class="p-5 bg-gray-50 rounded-2xl border border-gray-200 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="show_value_pillars" value="1" id="sc_pil" {{ !empty($homepage['show_value_pillars']) || !isset($homepage['show_value_pillars']) ? 'checked' : '' }} class="w-4 h-4 text-brand-sage rounded focus:ring-brand-sage border-gray-300">
                                    <div>
                                        <label for="sc_pil" class="text-sm font-bold text-gray-800 cursor-pointer">5. Marketplace Trust & Assurance Pillars</label>
                                        <p class="text-xs text-gray-500">Showcases 100% Verified Quality, Direct Atelier Pricing, Safe Escrow, and Pan-India Delivery guarantees.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- SEO Article Section -->
                            <div class="p-5 bg-gray-50 rounded-2xl border border-gray-200 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="show_seo_editorial" value="1" id="sc_seo" {{ !empty($homepage['show_seo_editorial']) || !isset($homepage['show_seo_editorial']) ? 'checked' : '' }} class="w-4 h-4 text-brand-sage rounded focus:ring-brand-sage border-gray-300">
                                    <div>
                                        <label for="sc_seo" class="text-sm font-bold text-gray-800 cursor-pointer">6. High-Impact SEO Ranking Editorial (Bottom Section)</label>
                                        <p class="text-xs text-gray-500">Essential for Google keyword dominance. Features expandable "Read More / Show Less" to keep the visual design pristine.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 5: Rich SEO Ranking Article -->
                <div x-show="activeTab === 'editorial'" class="space-y-6">
                    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-brand-surface shadow-sm space-y-6">
                        <div class="border-b border-gray-100 pb-4">
                            <h2 class="text-xl font-serif font-bold text-brand-text">Long-Form SEO Content Engine</h2>
                            <p class="text-xs text-gray-500 mt-1">Google strongly favors homepages with authoritative, topical keyword depth. This text is styled cleanly at the bottom of the page with an interactive accordion to preserve aesthetic luxury.</p>
                        </div>

                        <div class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-800">SEO Section Heading</label>
                                <input type="text" name="seo_footer_title" value="{{ old('seo_footer_title', $seo['seo_footer_title'] ?? 'The Premier Destination for Curated Fashion & Independent Boutiques') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage text-sm shadow-xs font-serif font-bold">
                            </div>

                            <div class="space-y-1.5">
                                <div class="flex justify-between items-center">
                                    <label class="block text-sm font-semibold text-gray-800">SEO Editorial Narrative (Markdown or HTML supported)</label>
                                    <span class="text-xs text-brand-sage font-medium">Rankings Booster</span>
                                </div>
                                <textarea name="seo_footer_content" rows="12" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-brand-sage text-sm font-mono leading-relaxed shadow-xs" placeholder="Write rich keyword copy about your designers, handloom fabrics, regional heritage, and fashion philosophy...">{{ old('seo_footer_content', $seo['seo_footer_content'] ?? "Welcome to the vanguard of modern apparel where discerning customers connect directly with India's most talented fashion creators, boutique ateliers, and independent artisans. Our curated marketplace redefines high fashion by eliminating intermediaries and celebrating authentic, slow-crafted perfection.

From bespoke handloom sarees woven in Chanderi, Banaras, and Kanchipuram to contemporary Indo-western ensembles, minimalist streetwear, and luxury daily essentials, every creation hosted on our platform undergoes stringent authenticity checks. Whether you are curating a festive bridal trousseau, elevating your corporate wardrobe with tailored linens, or discovering bohemian fusion pieces, our marketplace guarantees certified pure fabrics, transparent pricing, and direct-to-artisan prosperity.

Shop with complete confidence knowing every order is backed by comprehensive quality inspections, buyer protection escrow, express pan-India insured transit, and hassle-free returns. Join thousands of connoisseurs who celebrate individuality and sustainable elegance.") }}</textarea>
                                <p class="text-xs text-gray-500">Tip: Include high-intent search phrases such as "designer kurtis", "handcrafted sarees", "slow fashion marketplace", and "independent fashion boutiques".</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Floating Save Bar -->
                <div class="sticky bottom-6 z-30 bg-white/95 backdrop-blur-md p-4 rounded-2xl border border-brand-surface shadow-lg flex justify-between items-center mt-8">
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Changes take effect immediately on the live marketplace.</span>
                    </div>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-brand-text text-white hover:bg-brand-sage transition-all shadow-sm">
                        Publish Changes Live
                    </button>
                </div>
            </form>
        </div>
</x-layouts.admin>
