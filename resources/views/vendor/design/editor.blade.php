<!DOCTYPE html>
<html lang="en" class="h-full bg-stone-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Visual Storefront Editor · {{ $vendor->shop_name }}</title>
    
    <!-- Tailwind CSS (Vite or CDN fallback) -->
    @if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <!-- Google Fonts for typography previews -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,300&family=Plus+Jakarta+Sans:wght@600;700&family=Fraunces:wght@600;800&family=Outfit:wght@500;700&family=Syne:wght@700;800&family=Space+Grotesk:wght@600;700&family=Cinzel:wght@600;700&family=Bodoni+Moda:wght@600;800&family=Chakra+Petch:wght@600;700&family=Prata&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        [x-cloak] { display: none !important; }
        .preview-mobile { width: 390px !important; height: 844px !important; max-height: calc(100vh - 120px); border-radius: 40px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 12px solid #1c1917; }
        .preview-tablet { width: 768px !important; height: 1024px !important; max-height: calc(100vh - 120px); border-radius: 28px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.2); border: 10px solid #1c1917; }
        .preview-desktop { width: 100% !important; height: 100% !important; border-radius: 0; border: none; }
        /* Custom thin scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.25); }
    </style>
</head>

<body class="h-full flex flex-col overflow-hidden text-stone-900 font-sans antialiased"
      x-data="visualEditor()"
      x-init="initEditor()">

    <!-- TOP TOOLBAR -->
    <header class="h-14 sm:h-16 bg-stone-900 text-white flex items-center justify-between px-3 sm:px-6 z-40 shrink-0 border-b border-stone-800 shadow-md">
        
        <!-- Left: Back link & Store Info -->
        <div class="flex items-center gap-2 sm:gap-4">
            <a href="{{ route('vendor.design.index') }}" 
               class="p-2 -ml-1 rounded-xl text-stone-400 hover:text-white hover:bg-stone-800 transition flex items-center gap-1 text-xs font-semibold"
               title="Exit to Store Design Hub">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="hidden md:inline">Dashboard</span>
            </a>

            <div class="h-5 w-px bg-stone-800 hidden sm:block"></div>

            <div class="flex items-center gap-2">
                <span class="text-sm font-bold font-heading truncate max-w-[140px] sm:max-w-[200px]">
                    {{ $vendor->shop_name }}
                </span>
                
                <!-- Preset Switcher Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button type="button" 
                            @click="open = !open" 
                            class="px-2.5 py-1 rounded-lg bg-stone-800 hover:bg-stone-750 text-stone-200 text-xs font-bold flex items-center gap-1 border border-stone-700 transition">
                        <span class="uppercase text-[11px] text-amber-400" x-text="config.preset"></span>
                        <svg class="w-3 h-3 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open" 
                         @click.away="open = false" 
                         x-transition
                         class="absolute left-0 mt-1 w-64 bg-stone-900 border border-stone-700 rounded-2xl shadow-2xl p-2 z-50 text-xs space-y-1"
                         style="display: none;">
                        <div class="px-2.5 py-1 text-[10px] uppercase font-bold tracking-wider text-stone-400">Switch Theme Preset</div>
                        @foreach($presets as $pKey => $pMeta)
                            <button type="button" 
                                    @click="switchPreset('{{ $pKey }}'); open = false;"
                                    class="w-full text-left px-2.5 py-2 rounded-xl transition flex items-center justify-between"
                                    :class="config.preset === '{{ $pKey }}' ? 'bg-amber-500/20 text-amber-300 font-bold' : 'hover:bg-stone-800 text-stone-300'">
                                <div>
                                    <div class="font-bold">{{ $pMeta['name'] }}</div>
                                    <div class="text-[10px] text-stone-400">{{ $pMeta['badge'] }}</div>
                                </div>
                                <span x-show="config.preset === '{{ $pKey }}'">✓</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Unsaved Dirty Indicator -->
                <span x-show="isDirty" 
                      x-cloak 
                      class="hidden lg:inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400/10 text-amber-300 border border-amber-400/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                    <span>Unsaved Changes</span>
                </span>
            </div>
        </div>

        <!-- Center: Responsive Viewport Switchers -->
        <div class="flex items-center gap-1 bg-stone-800 p-1 rounded-xl border border-stone-700">
            <button type="button" 
                    @click="setViewport('desktop')" 
                    class="p-1.5 sm:px-3 sm:py-1 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition"
                    :class="viewport === 'desktop' ? 'bg-stone-700 text-white shadow-xs' : 'text-stone-400 hover:text-white'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span class="hidden md:inline">Desktop</span>
            </button>
            <button type="button" 
                    @click="setViewport('tablet')" 
                    class="p-1.5 sm:px-3 sm:py-1 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition"
                    :class="viewport === 'tablet' ? 'bg-stone-700 text-white shadow-xs' : 'text-stone-400 hover:text-white'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span class="hidden md:inline">Tablet</span>
            </button>
            <button type="button" 
                    @click="setViewport('mobile')" 
                    class="p-1.5 sm:px-3 sm:py-1 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition"
                    :class="viewport === 'mobile' ? 'bg-stone-700 text-white shadow-xs' : 'text-stone-400 hover:text-white'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span class="hidden md:inline">Mobile</span>
            </button>
        </div>

        <!-- Right: Undo/Redo, Save Draft & Publish -->
        <div class="flex items-center gap-2 sm:gap-3">
            
            <!-- Undo / Redo -->
            <div class="hidden sm:flex items-center gap-1 border-r border-stone-800 pr-2">
                <button type="button" 
                        @click="undo()" 
                        :disabled="historyIndex <= 0" 
                        class="p-1.5 rounded-lg text-stone-400 hover:text-white disabled:opacity-30 disabled:hover:text-stone-400 transition"
                        title="Undo (Ctrl+Z)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2M3 10l6 6m-6-6l6-6"/></svg>
                </button>
                <button type="button" 
                        @click="redo()" 
                        :disabled="historyIndex >= history.length - 1" 
                        class="p-1.5 rounded-lg text-stone-400 hover:text-white disabled:opacity-30 disabled:hover:text-stone-400 transition"
                        title="Redo (Ctrl+Y)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a5 5 0 00-5 5v2m15-7l-6 6m6-6l-6-6"/></svg>
                </button>
            </div>

            <!-- Save Draft Button -->
            <button type="button" 
                    @click="saveDraft()" 
                    :disabled="saving"
                    class="px-3 sm:px-4 py-2 rounded-xl text-xs font-bold bg-stone-800 hover:bg-stone-750 text-stone-200 border border-stone-700 transition flex items-center gap-1.5 shadow-sm">
                <span x-show="!saving">💾 Save Draft</span>
                <span x-show="saving" x-cloak class="flex items-center gap-1">
                    <span class="w-3 h-3 rounded-full border-2 border-white/20 border-t-white animate-spin"></span>
                    <span>Saving...</span>
                </span>
            </button>

            <!-- Publish Button (Primary) -->
            <button type="button" 
                    @click="openPublishModal()" 
                    class="px-4 sm:px-5 py-2 rounded-xl text-xs font-extrabold uppercase tracking-wider bg-emerald-500 hover:bg-emerald-400 text-stone-950 transition flex items-center gap-1.5 shadow-md shadow-emerald-950/20 hover:scale-102">
                <span>🚀 Publish</span>
            </button>
        </div>
    </header>

    <!-- MAIN 3-PANEL WORKSPACE -->
    <div class="flex-1 flex overflow-hidden relative">
        
        <!-- LEFT PANEL: STORE STRUCTURE & SECTIONS TREE (Desktop) -->
        <aside class="w-72 sm:w-80 bg-white border-r border-stone-200 flex flex-col shrink-0 z-30 transition-all duration-300 hidden md:flex">
            
            <!-- Left Tabs: Sections | Brand & Theme | Header | Footer -->
            <div class="flex border-b border-stone-200 p-1 bg-stone-50/70 text-xs font-bold text-stone-600">
                <button type="button" 
                        @click="leftTab = 'sections'" 
                        class="flex-1 py-2 text-center rounded-lg transition"
                        :class="leftTab === 'sections' ? 'bg-white text-stone-900 shadow-xs' : 'hover:text-stone-900'">
                    Sections
                </button>
                <button type="button" 
                        @click="leftTab = 'theme'" 
                        class="flex-1 py-2 text-center rounded-lg transition"
                        :class="leftTab === 'theme' ? 'bg-white text-stone-900 shadow-xs' : 'hover:text-stone-900'">
                    Theme &amp; Fonts
                </button>
                <button type="button" 
                        @click="leftTab = 'header_footer'" 
                        class="flex-1 py-2 text-center rounded-lg transition"
                        :class="leftTab === 'header_footer' ? 'bg-white text-stone-900 shadow-xs' : 'hover:text-stone-900'">
                    Header &amp; Nav
                </button>
            </div>

            <!-- TAB 1: SECTIONS TREE -->
            <div x-show="leftTab === 'sections'" class="flex-1 overflow-y-auto p-4 space-y-4">
                
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-400">Homepage Layout</span>
                    <button type="button" 
                            @click="openAddSectionModal()" 
                            class="px-2.5 py-1 text-xs font-bold rounded-lg bg-stone-900 text-white hover:bg-stone-800 transition flex items-center gap-1">
                        <span>+ Add Section</span>
                    </button>
                </div>

                <!-- Sections List with Reorder, Show/Hide & Delete -->
                <div class="space-y-2">
                    <template x-for="(sec, index) in config.sections" :key="sec.id">
                        <div class="p-3 rounded-2xl border transition-all duration-200 flex items-center justify-between gap-2 group cursor-pointer"
                             :class="selectedSectionId === sec.id ? 'border-blue-500 bg-blue-50/50 shadow-xs ring-1 ring-blue-500' : 'border-stone-200 bg-white hover:border-stone-300 hover:bg-stone-50/60'"
                             @click="selectSection(sec.id)">
                            
                            <!-- Left: Drag handle & Title -->
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <span class="text-base shrink-0" x-text="getSectionIcon(sec.type)"></span>
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-bold text-stone-800 truncate" x-text="sec.title || getSectionName(sec.type)"></div>
                                    <div class="text-[10px] text-stone-400 capitalize" x-text="sec.type.replace('_', ' ')"></div>
                                </div>
                            </div>

                            <!-- Right Actions: Up / Down / Eye / Delete -->
                            <div class="flex items-center gap-1 shrink-0">
                                <!-- Move Up -->
                                <button type="button" 
                                        @click.stop="moveSection(index, -1)" 
                                        :disabled="index === 0" 
                                        class="p-1 text-stone-400 hover:text-stone-800 disabled:opacity-20 transition"
                                        title="Move Up">
                                    ▲
                                </button>
                                <!-- Move Down -->
                                <button type="button" 
                                        @click.stop="moveSection(index, 1)" 
                                        :disabled="index === config.sections.length - 1" 
                                        class="p-1 text-stone-400 hover:text-stone-800 disabled:opacity-20 transition"
                                        title="Move Down">
                                    ▼
                                </button>
                                <!-- Toggle Visibility -->
                                <button type="button" 
                                        @click.stop="toggleSectionVisibility(index)" 
                                        class="p-1 transition text-xs"
                                        :class="sec.visible ? 'text-stone-700' : 'text-stone-300'"
                                        :title="sec.visible ? 'Hide Section' : 'Show Section'">
                                    <span x-text="sec.visible ? '👁️' : '🕶️'"></span>
                                </button>
                                <!-- Delete Section -->
                                <button type="button" 
                                        @click.stop="deleteSection(index)" 
                                        class="p-1 text-stone-400 hover:text-rose-600 transition"
                                        title="Delete Section">
                                    ✕
                                </button>
                            </div>

                        </div>
                    </template>
                </div>

                <div class="pt-4 border-t border-stone-100 flex items-center justify-between text-xs text-stone-400">
                    <span>Reorder with ▲ ▼</span>
                    <button type="button" @click="resetToDefaults()" class="text-rose-500 hover:underline">
                        Reset Preset
                    </button>
                </div>
            </div>

            <!-- TAB 2: BRAND & THEME STYLING -->
            <div x-show="leftTab === 'theme'" class="flex-1 overflow-y-auto p-4 space-y-6" style="display: none;">
                
                <!-- Heading Font -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-stone-700">Display Heading Font</label>
                    <select x-model="config.theme.font_heading" 
                            @change="markDirty(); updateLiveTheme();"
                            class="w-full text-xs font-semibold rounded-xl border-stone-200 bg-stone-50 p-2.5 focus:bg-white">
                        <option value="Playfair Display">Playfair Display (Refined Editorial Serif)</option>
                        <option value="Cormorant Garamond">Cormorant Garamond (Classic Luxury)</option>
                        <option value="Fraunces">Fraunces (Warm Characterful Serif)</option>
                        <option value="Plus Jakarta Sans">Plus Jakarta Sans (Modern Geometric)</option>
                        <option value="Inter">Inter (Ultra Clean Sans)</option>
                        <option value="Outfit">Outfit (Warm Humanist Sans)</option>
                        <option value="Space Grotesk">Space Grotesk (Distinctive Brutalist)</option>
                        <option value="Syne">Syne (High-Contrast Bold Display)</option>
                    </select>
                </div>

                <!-- Body Font -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-stone-700">Body Typography</label>
                    <select x-model="config.theme.font_body" 
                            @change="markDirty(); updateLiveTheme();"
                            class="w-full text-xs font-semibold rounded-xl border-stone-200 bg-stone-50 p-2.5 focus:bg-white">
                        <option value="Inter">Inter (High Legibility)</option>
                        <option value="Plus Jakarta Sans">Plus Jakarta Sans</option>
                        <option value="Outfit">Outfit (Warm Rounded)</option>
                        <option value="Space Grotesk">Space Grotesk (Tech Minimal)</option>
                    </select>
                </div>

                <!-- Palette Colors -->
                <div class="space-y-4 pt-2 border-t border-stone-100">
                    <div class="text-xs font-bold uppercase tracking-wider text-stone-400">Color System</div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[11px] font-bold text-stone-600 block mb-1">Primary Color</label>
                            <div class="flex items-center gap-2 border border-stone-200 rounded-xl p-1.5 bg-stone-50">
                                <input type="color" x-model="config.theme.primary_color" @input="markDirty(); updateLiveTheme();" class="w-7 h-7 rounded border-none cursor-pointer">
                                <input type="text" x-model="config.theme.primary_color" @input="markDirty(); updateLiveTheme();" class="w-full text-xs font-mono bg-transparent uppercase">
                            </div>
                        </div>

                        <div>
                            <label class="text-[11px] font-bold text-stone-600 block mb-1">Accent Highlight</label>
                            <div class="flex items-center gap-2 border border-stone-200 rounded-xl p-1.5 bg-stone-50">
                                <input type="color" x-model="config.theme.accent_color" @input="markDirty(); updateLiveTheme();" class="w-7 h-7 rounded border-none cursor-pointer">
                                <input type="text" x-model="config.theme.accent_color" @input="markDirty(); updateLiveTheme();" class="w-full text-xs font-mono bg-transparent uppercase">
                            </div>
                        </div>

                        <div>
                            <label class="text-[11px] font-bold text-stone-600 block mb-1">Background Surface</label>
                            <div class="flex items-center gap-2 border border-stone-200 rounded-xl p-1.5 bg-stone-50">
                                <input type="color" x-model="config.theme.bg_color" @input="markDirty(); updateLiveTheme();" class="w-7 h-7 rounded border-none cursor-pointer">
                                <input type="text" x-model="config.theme.bg_color" @input="markDirty(); updateLiveTheme();" class="w-full text-xs font-mono bg-transparent uppercase">
                            </div>
                        </div>

                        <div>
                            <label class="text-[11px] font-bold text-stone-600 block mb-1">Card Canvas</label>
                            <div class="flex items-center gap-2 border border-stone-200 rounded-xl p-1.5 bg-stone-50">
                                <input type="color" x-model="config.theme.surface_color" @input="markDirty(); updateLiveTheme();" class="w-7 h-7 rounded border-none cursor-pointer">
                                <input type="text" x-model="config.theme.surface_color" @input="markDirty(); updateLiveTheme();" class="w-full text-xs font-mono bg-transparent uppercase">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Corner Radius Presets -->
                <div class="space-y-2 pt-2 border-t border-stone-100">
                    <label class="text-xs font-bold text-stone-700">Card Geometry &amp; Radius</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" 
                                @click="config.theme.border_radius = 'rounded-none'; markDirty();"
                                class="p-2 border rounded-none text-xs font-bold text-center"
                                :class="config.theme.border_radius === 'rounded-none' ? 'border-stone-900 bg-stone-100' : 'border-stone-200'">
                            Sharp (0px)
                        </button>
                        <button type="button" 
                                @click="config.theme.border_radius = 'rounded-lg'; markDirty();"
                                class="p-2 border rounded-lg text-xs font-bold text-center"
                                :class="config.theme.border_radius === 'rounded-lg' ? 'border-stone-900 bg-stone-100' : 'border-stone-200'">
                            Subtle (8px)
                        </button>
                        <button type="button" 
                                @click="config.theme.border_radius = 'rounded-2xl'; markDirty();"
                                class="p-2 border rounded-2xl text-xs font-bold text-center"
                                :class="config.theme.border_radius === 'rounded-2xl' ? 'border-stone-900 bg-stone-100' : 'border-stone-200'">
                            Pill (16px)
                        </button>
                    </div>
                </div>

            </div>

            <!-- TAB 3: HEADER & FOOTER SETTINGS -->
            <div x-show="leftTab === 'header_footer'" class="flex-1 overflow-y-auto p-4 space-y-6" style="display: none;">
                
                <!-- Announcement Bar -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-stone-800">Announcement Top Bar</label>
                        <input type="checkbox" x-model="config.header.announcement_enabled" @change="markDirty()" class="rounded text-stone-900">
                    </div>
                    <div x-show="config.header.announcement_enabled" class="space-y-2">
                        <input type="text" 
                               x-model="config.header.announcement_text" 
                               @input="markDirty()" 
                               placeholder="e.g. Free shipping above ₹1,999"
                               class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 p-2.5">
                        <input type="text" 
                               x-model="config.header.announcement_link" 
                               @input="markDirty()" 
                               placeholder="Link URL (optional)"
                               class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 p-2.5">
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <label class="text-[10px] text-stone-400">Background</label>
                                <input type="color" x-model="config.header.announcement_bg" @input="markDirty()" class="w-full h-8 rounded">
                            </div>
                            <div class="flex-1">
                                <label class="text-[10px] text-stone-400">Text Color</label>
                                <input type="color" x-model="config.header.announcement_text_color" @input="markDirty()" class="w-full h-8 rounded">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Header Layout Variant -->
                <div class="space-y-2 pt-4 border-t border-stone-100">
                    <label class="text-xs font-bold text-stone-800">Header Layout Arrangement</label>
                    <select x-model="config.header.layout" @change="markDirty()" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 p-2.5">
                        <option value="split">Split (Logo Left, Nav Center, Bag Right)</option>
                        <option value="search_prominent">Search Prominent (Commerce Focus)</option>
                        <option value="minimal">Minimal Contemporary</option>
                        <option value="stacked">Centered &amp; Stacked (Bold Fashion)</option>
                    </select>
                </div>

                <!-- Sticky Header -->
                <div class="flex items-center justify-between pt-2">
                    <span class="text-xs font-semibold text-stone-700">Sticky on Scroll</span>
                    <input type="checkbox" x-model="config.header.sticky" @change="markDirty()" class="rounded text-stone-900">
                </div>

                <!-- Footer Settings -->
                <div class="space-y-3 pt-6 border-t border-stone-200">
                    <div class="text-xs font-bold uppercase tracking-wider text-stone-400">Footer Settings</div>
                    <div>
                        <label class="text-xs font-bold text-stone-700">Boutique Short Bio</label>
                        <textarea x-model="config.footer.about_text" 
                                  @input="markDirty()" 
                                  rows="3" 
                                  class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 p-2.5 mt-1"></textarea>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-stone-700">Copyright Line</label>
                        <input type="text" x-model="config.footer.copyright_text" @input="markDirty()" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 p-2.5 mt-1">
                    </div>
                </div>

            </div>

        </aside>

        <!-- CENTER: LIVE STOREFRONT CANVAS (Responsive Preview) -->
        <main class="flex-1 bg-stone-200/70 p-2 sm:p-6 flex flex-col items-center justify-center overflow-auto relative">
            
            <!-- Viewport Container Frame -->
            <div class="transition-all duration-300 flex flex-col bg-white overflow-hidden shadow-2xl relative"
                 :class="{
                     'preview-mobile': viewport === 'mobile',
                     'preview-tablet': viewport === 'tablet',
                     'preview-desktop': viewport === 'desktop'
                 }">
                
                <!-- Iframe rendering real live draft -->
                <iframe id="storefront-preview-iframe"
                        src="{{ route('vendor.design.preview') }}"
                        class="w-full h-full border-none flex-1"
                        @load="iframeLoaded()">
                </iframe>

                <!-- Loading Overlay when syncing -->
                <div x-show="reloadingFrame" 
                     x-cloak 
                     class="absolute inset-0 bg-white/70 backdrop-blur-xs flex items-center justify-center z-50">
                    <div class="flex flex-col items-center gap-2">
                        <span class="w-6 h-6 rounded-full border-2 border-stone-900 border-t-transparent animate-spin"></span>
                        <span class="text-xs font-bold text-stone-800">Updating Canvas...</span>
                    </div>
                </div>

            </div>

            <!-- Floating Preview Status Pill -->
            <div class="absolute bottom-4 left-6 hidden lg:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-stone-900/90 text-white text-[11px] font-semibold backdrop-blur-md shadow-lg pointer-events-none">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Live Interactive Preview · Click section to inspect</span>
            </div>

        </main>

        <!-- RIGHT PANEL: CONTEXTUAL INSPECTOR (Section Settings) -->
        <aside class="w-80 sm:w-96 bg-white border-l border-stone-200 flex flex-col shrink-0 z-30 transition-all duration-300 hidden lg:flex"
               x-show="selectedSection" 
               x-cloak>
            
            <div class="p-4 border-b border-stone-200 flex items-center justify-between bg-stone-50/70">
                <div class="flex items-center gap-2">
                    <span class="text-base" x-text="getSectionIcon(selectedSection?.type)"></span>
                    <div>
                        <div class="text-xs font-extrabold uppercase tracking-wider text-stone-900" x-text="selectedSection ? getSectionName(selectedSection.type) : 'Inspector'"></div>
                        <div class="text-[10px] text-stone-400">Contextual Section Controls</div>
                    </div>
                </div>
                <button type="button" @click="selectedSectionId = null" class="text-stone-400 hover:text-stone-800 p-1 text-sm font-bold">
                    ✕
                </button>
            </div>

            <!-- Inspector Form Controls -->
            <div class="flex-1 overflow-y-auto p-5 space-y-5" x-show="selectedSection">
                
                <!-- Title & Headline -->
                <template x-if="selectedSection?.title !== undefined">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-stone-700">Section Headline</label>
                        <input type="text" 
                               x-model="selectedSection.title" 
                               @input="markDirty()" 
                               class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 p-2.5 focus:bg-white font-heading font-semibold">
                    </div>
                </template>

                <!-- Subtitle -->
                <template x-if="selectedSection?.subtitle !== undefined">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-stone-700">Subtitle / Tagline</label>
                        <input type="text" 
                               x-model="selectedSection.subtitle" 
                               @input="markDirty()" 
                               class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 p-2.5 focus:bg-white">
                    </div>
                </template>

                <!-- Badge -->
                <template x-if="selectedSection?.badge !== undefined">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-stone-700">Seasonal Badge Pill</label>
                        <input type="text" 
                               x-model="selectedSection.badge" 
                               @input="markDirty()" 
                               placeholder="e.g. Atelier Edition 2026"
                               class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 p-2.5 focus:bg-white">
                    </div>
                </template>

                <!-- Description / Body -->
                <template x-if="selectedSection?.description !== undefined || selectedSection?.text !== undefined">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-stone-700">Descriptive Copy</label>
                        <textarea x-model="selectedSection.description || selectedSection.text" 
                                  @input="if(selectedSection.description !== undefined) selectedSection.description = $event.target.value; if(selectedSection.text !== undefined) selectedSection.text = $event.target.value; markDirty();" 
                                  rows="3" 
                                  class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 p-2.5 focus:bg-white"></textarea>
                    </div>
                </template>

                <!-- Pull Quote (Lookbook / Brand Story) -->
                <template x-if="selectedSection?.quote !== undefined">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-stone-700">Editorial Pull Quote</label>
                        <textarea x-model="selectedSection.quote" 
                                  @input="markDirty()" 
                                  rows="2" 
                                  class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 p-2.5 focus:bg-white font-serif italic"></textarea>
                    </div>
                </template>

                <!-- Primary CTA -->
                <template x-if="selectedSection?.cta_primary_text !== undefined">
                    <div class="space-y-3 pt-2 border-t border-stone-100">
                        <label class="text-xs font-bold uppercase tracking-wider text-stone-400">Primary Button</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" x-model="selectedSection.cta_primary_text" @input="markDirty()" placeholder="Button text" class="text-xs rounded-xl border-stone-200 bg-stone-50 p-2">
                            <input type="text" x-model="selectedSection.cta_primary_link" @input="markDirty()" placeholder="#catalog" class="text-xs rounded-xl border-stone-200 bg-stone-50 p-2">
                        </div>
                    </div>
                </template>

                <!-- Image URL & File Upload -->
                <template x-if="selectedSection?.image_url !== undefined">
                    <div class="space-y-2 pt-2 border-t border-stone-100">
                        <label class="text-xs font-bold text-stone-700">Featured Image Banner</label>
                        <div class="flex items-center gap-2">
                            <input type="text" x-model="selectedSection.image_url" @input="markDirty()" placeholder="https://..." class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 p-2">
                            <label class="px-3 py-2 bg-stone-100 hover:bg-stone-200 rounded-xl text-xs font-bold cursor-pointer shrink-0">
                                Upload
                                <input type="file" @change="uploadImage($event)" accept="image/*" class="hidden">
                            </label>
                        </div>
                    </div>
                </template>

                <!-- Product Grid Specific Controls -->
                <template x-if="selectedSection?.type === 'featured_products'">
                    <div class="space-y-4 pt-2 border-t border-stone-100">
                        <div class="text-xs font-bold uppercase tracking-wider text-stone-400">Product Grid Layout</div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-[10px] font-bold text-stone-600">Columns</label>
                                <select x-model="selectedSection.columns" @change="markDirty()" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 p-2 mt-1">
                                    <option value="2">2 Columns</option>
                                    <option value="3">3 Columns</option>
                                    <option value="4">4 Columns</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-stone-600">Aspect Ratio</label>
                                <select x-model="selectedSection.aspect_ratio" @change="markDirty()" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 p-2 mt-1">
                                    <option value="3:4">Portrait 3:4 (Fashion)</option>
                                    <option value="1:1">Square 1:1 (Minimal)</option>
                                    <option value="4:5">Editorial 4:5</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <span class="text-xs text-stone-700 font-semibold">Quick Buy Button</span>
                            <input type="checkbox" x-model="selectedSection.show_quick_buy" @change="markDirty()" class="rounded text-stone-900">
                        </div>
                    </div>
                </template>

                <!-- Kinetic Scroll Sequence Controls -->
                <template x-if="selectedSection?.type === 'scroll_sequence'">
                    <div class="space-y-3 pt-2 border-t border-stone-100">
                        <div class="text-xs font-bold uppercase tracking-wider text-stone-400">Video-to-JPG Scroll Motion</div>
                        <p class="text-[11px] text-stone-500 leading-relaxed">
                            Upload any product video inside the Store Design Hub to automatically generate JPG scroll frames for this stage.
                        </p>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-[10px] font-bold text-stone-600">Scroll Stage (vh)</label>
                                <input type="number" min="180" max="600" x-model.number="config.scroll_sequence.height_vh" @input="markDirty()" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 p-2 mt-1">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-stone-600">Damping (0.05-0.45)</label>
                                <input type="number" step="0.02" min="0.05" max="0.45" x-model.number="config.scroll_sequence.smoothing" @input="markDirty()" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 p-2 mt-1">
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-stone-600">Beat 1 Headline</label>
                            <input type="text" x-model="config.scroll_sequence.beat1_title" @input="markDirty()" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 p-2 mt-1">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-stone-600">Beat 2 Headline</label>
                            <input type="text" x-model="config.scroll_sequence.beat2_title" @input="markDirty()" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 p-2 mt-1">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-stone-600">Beat 3 Headline</label>
                            <input type="text" x-model="config.scroll_sequence.beat3_title" @input="markDirty()" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50 p-2 mt-1">
                        </div>
                        <a href="{{ route('vendor.design.index') }}#kinetic-studio"
                           class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-stone-900 hover:bg-stone-800 text-white text-xs font-bold transition">
                            <span>🎬 Open Video-to-JPG Uploader</span>
                        </a>
                    </div>
                </template>

                <!-- Alignment Control -->
                <template x-if="selectedSection?.alignment !== undefined">
                    <div class="space-y-1.5 pt-2 border-t border-stone-100">
                        <label class="text-xs font-bold text-stone-700">Text Alignment</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" @click="selectedSection.alignment = 'left'; markDirty();" class="p-1.5 border rounded-lg text-xs font-bold" :class="selectedSection.alignment === 'left' ? 'border-stone-900 bg-stone-100' : 'border-stone-200'">Left</button>
                            <button type="button" @click="selectedSection.alignment = 'center'; markDirty();" class="p-1.5 border rounded-lg text-xs font-bold" :class="selectedSection.alignment === 'center' ? 'border-stone-900 bg-stone-100' : 'border-stone-200'">Center</button>
                            <button type="button" @click="selectedSection.alignment = 'right'; markDirty();" class="p-1.5 border rounded-lg text-xs font-bold" :class="selectedSection.alignment === 'right' ? 'border-stone-900 bg-stone-100' : 'border-stone-200'">Right</button>
                        </div>
                    </div>
                </template>

            </div>
        </aside>

    </div>

    <!-- MOBILE BOTTOM BAR (Phones only) -->
    <div class="md:hidden h-14 bg-white border-t border-stone-200 flex items-center justify-around px-2 z-40 shrink-0">
        <button type="button" 
                @click="mobileDrawer = 'sections'" 
                class="flex flex-col items-center gap-0.5 text-stone-600 hover:text-stone-900">
            <span class="text-base">📑</span>
            <span class="text-[10px] font-bold">Sections</span>
        </button>
        <button type="button" 
                @click="mobileDrawer = 'theme'" 
                class="flex flex-col items-center gap-0.5 text-stone-600 hover:text-stone-900">
            <span class="text-base">🎨</span>
            <span class="text-[10px] font-bold">Styling</span>
        </button>
        <button type="button" 
                @click="saveDraft()" 
                class="flex flex-col items-center gap-0.5 text-stone-600 hover:text-stone-900">
            <span class="text-base">💾</span>
            <span class="text-[10px] font-bold">Save</span>
        </button>
        <button type="button" 
                @click="openPublishModal()" 
                class="flex flex-col items-center gap-0.5 text-emerald-600 font-bold">
            <span class="text-base">🚀</span>
            <span class="text-[10px] font-bold">Publish</span>
        </button>
    </div>

    <!-- ADD SECTION MODAL -->
    <div x-show="addSectionModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-stone-200 max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                <div>
                    <h3 class="text-lg font-bold font-heading text-stone-900">Add Storefront Section</h3>
                    <p class="text-xs text-stone-500">Choose a component to insert into your boutique</p>
                </div>
                <button type="button" @click="addSectionModalOpen = false" class="text-stone-400 hover:text-stone-800 p-1 text-base">✕</button>
            </div>

            <div class="flex-1 overflow-y-auto py-4 space-y-3">
                @foreach($availableSections as $sKey => $sDef)
                    <div class="p-3.5 rounded-2xl border border-stone-200 hover:border-stone-900 hover:bg-stone-50 transition cursor-pointer flex items-center justify-between gap-3 group"
                         @click="insertSection('{{ $sKey }}')">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl p-2 rounded-xl bg-stone-100 group-hover:bg-white shrink-0">
                                {{ $sDef['icon'] }}
                            </span>
                            <div>
                                <h4 class="text-xs font-bold text-stone-900 group-hover:text-stone-950">{{ $sDef['name'] }}</h4>
                                <p class="text-[11px] text-stone-500 leading-tight mt-0.5">{{ $sDef['description'] }}</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-stone-400 group-hover:text-stone-900">+ Add</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- CONFIRM PUBLISH MODAL -->
    <div x-show="publishModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-stone-200 space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl mx-auto mb-2">
                🚀
            </div>
            <div class="text-center">
                <h3 class="text-lg font-bold font-heading text-stone-900">Publish Changes to Live Store?</h3>
                <p class="text-xs text-stone-500 mt-1 leading-relaxed">
                    This will update your public boutique at <strong class="text-stone-800">{{ $vendor->storefront_url }}</strong> with your current design draft. All customer purchases will immediately use this layout.
                </p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                <button type="button" @click="publishModalOpen = false" class="px-4 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100 rounded-xl">
                    Keep Editing
                </button>
                <button type="button" 
                        @click="executePublish()" 
                        :disabled="publishing"
                        class="px-5 py-2.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl shadow-md flex items-center gap-1.5">
                    <span x-show="!publishing">Confirm &amp; Publish Live</span>
                    <span x-show="publishing" x-cloak class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full border-2 border-white/20 border-t-white animate-spin"></span>
                        <span>Publishing...</span>
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- PUBLISHED SUCCESS BANNER / TOAST -->
    <div id="editor-toast" 
         class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none bg-stone-900 text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 text-xs font-semibold">
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        <span id="editor-toast-text">Success</span>
    </div>

    <!-- Visual Editor Alpine Component Script -->
    <script>
        function visualEditor() {
            return {
                config: @json($draftConfig),
                availableSections: @json($availableSections),
                presets: @json($presets),
                
                viewport: 'desktop', // desktop | tablet | mobile
                leftTab: 'sections', // sections | theme | header_footer
                selectedSectionId: null,
                
                history: [],
                historyIndex: -1,
                isDirty: false,
                saving: false,
                publishing: false,
                reloadingFrame: false,
                
                addSectionModalOpen: false,
                publishModalOpen: false,
                mobileDrawer: null,

                get selectedSection() {
                    if (!this.selectedSectionId) return null;
                    return this.config.sections?.find(s => s.id === this.selectedSectionId) || null;
                },

                initEditor() {
                    this.pushHistory();

                    // Listen for message from iframe (click section in canvas)
                    window.addEventListener('message', (e) => {
                        if (e.data && e.data.action === 'section_clicked') {
                            this.selectSection(e.data.sectionId);
                        }
                    });

                    // Keyboard shortcuts: Ctrl+Z (Undo), Ctrl+Y (Redo), Ctrl+S (Save)
                    window.addEventListener('keydown', (e) => {
                        if ((e.ctrlKey || e.metaKey) && e.key === 'z') {
                            e.preventDefault();
                            this.undo();
                        } else if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.shiftKey && e.key === 'Z'))) {
                            e.preventDefault();
                            this.redo();
                        } else if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                            e.preventDefault();
                            this.saveDraft();
                        }
                    });

                    // Warn on tab close if dirty
                    window.addEventListener('beforeunload', (e) => {
                        if (this.isDirty) {
                            e.preventDefault();
                            e.returnValue = '';
                        }
                    });
                },

                setViewport(vp) {
                    this.viewport = vp;
                },

                selectSection(id) {
                    this.selectedSectionId = id;
                    // Send message to iframe to highlight
                    const iframe = document.getElementById('storefront-preview-iframe');
                    if (iframe && iframe.contentWindow) {
                        iframe.contentWindow.postMessage({
                            action: 'highlight_section',
                            sectionId: id
                        }, '*');
                    }
                },

                getSectionName(type) {
                    return this.availableSections[type]?.name || type.replace('_', ' ');
                },

                getSectionIcon(type) {
                    return this.availableSections[type]?.icon || '📄';
                },

                moveSection(index, direction) {
                    const newIndex = index + direction;
                    if (newIndex < 0 || newIndex >= this.config.sections.length) return;
                    
                    const item = this.config.sections.splice(index, 1)[0];
                    this.config.sections.splice(newIndex, 0, item);
                    
                    this.markDirty();
                    this.reloadPreview();
                },

                toggleSectionVisibility(index) {
                    this.config.sections[index].visible = !this.config.sections[index].visible;
                    this.markDirty();
                    this.reloadPreview();
                },

                deleteSection(index) {
                    if (this.config.sections.length <= 1) {
                        alert('Storefront must have at least one section.');
                        return;
                    }
                    if (confirm('Delete this section?')) {
                        const removed = this.config.sections.splice(index, 1)[0];
                        if (this.selectedSectionId === removed.id) {
                            this.selectedSectionId = null;
                        }
                        this.markDirty();
                        this.reloadPreview();
                    }
                },

                openAddSectionModal() {
                    this.addSectionModalOpen = true;
                },

                insertSection(type) {
                    const def = this.availableSections[type];
                    if (!def) return;

                    const newSection = {
                        id: 'sec_' + type + '_' + Date.now(),
                        type: type,
                        visible: true,
                        ...(def.default_settings || {})
                    };

                    this.config.sections.push(newSection);
                    this.addSectionModalOpen = false;
                    this.selectedSectionId = newSection.id;
                    this.markDirty();
                    this.reloadPreview();
                },

                switchPreset(key) {
                    if (confirm(`Switch to the ${key.toUpperCase()} preset? This will apply the new layout structure to your draft.`)) {
                        fetch('{{ route('vendor.design.preset') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ preset: key })
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.status === 'success') {
                                this.config = data.config;
                                this.selectedSectionId = null;
                                this.markDirty();
                                this.reloadPreview();
                                this.showToast('Switched to ' + key.toUpperCase() + ' preset');
                            }
                        });
                    }
                },

                resetToDefaults() {
                    if (confirm('Reset draft to original preset defaults?')) {
                        fetch('{{ route('vendor.design.reset') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.status === 'success') {
                                this.config = data.config;
                                this.selectedSectionId = null;
                                this.markDirty();
                                this.reloadPreview();
                                this.showToast('Reset to preset defaults');
                            }
                        });
                    }
                },

                uploadImage(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append('image', file);

                    fetch('{{ route('vendor.design.upload') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.status === 'success' && this.selectedSection) {
                            this.selectedSection.image_url = data.url;
                            this.markDirty();
                            this.reloadPreview();
                            this.showToast('Image uploaded');
                        } else {
                            alert(data.message || 'Image upload failed');
                        }
                    });
                },

                updateLiveTheme() {
                    const iframe = document.getElementById('storefront-preview-iframe');
                    if (iframe && iframe.contentWindow) {
                        iframe.contentWindow.postMessage({
                            action: 'update_theme',
                            theme: this.config.theme
                        }, '*');
                    }
                },

                reloadPreview() {
                    this.reloadingFrame = true;
                    // First save draft silently so preview endpoint gets latest state
                    fetch('{{ route('vendor.design.save') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ config: this.config })
                    })
                    .then(() => {
                        const iframe = document.getElementById('storefront-preview-iframe');
                        if (iframe) {
                            iframe.src = '{{ route('vendor.design.preview') }}?t=' + Date.now();
                        }
                    })
                    .catch(() => {
                        this.reloadingFrame = false;
                    });
                },

                iframeLoaded() {
                    this.reloadingFrame = false;
                    if (this.selectedSectionId) {
                        this.selectSection(this.selectedSectionId);
                    }
                },

                markDirty() {
                    this.isDirty = true;
                    this.pushHistory();
                    this.debounceAutoSave();
                },

                pushHistory() {
                    const snap = JSON.stringify(this.config);
                    if (this.history[this.historyIndex] !== snap) {
                        this.history = this.history.slice(0, this.historyIndex + 1);
                        this.history.push(snap);
                        if (this.history.length > 30) this.history.shift();
                        this.historyIndex = this.history.length - 1;
                    }
                },

                undo() {
                    if (this.historyIndex > 0) {
                        this.historyIndex--;
                        this.config = JSON.parse(this.history[this.historyIndex]);
                        this.isDirty = true;
                        this.reloadPreview();
                    }
                },

                redo() {
                    if (this.historyIndex < this.history.length - 1) {
                        this.historyIndex++;
                        this.config = JSON.parse(this.history[this.historyIndex]);
                        this.isDirty = true;
                        this.reloadPreview();
                    }
                },

                debounceTimer: null,
                debounceAutoSave() {
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(() => {
                        this.saveDraft(true);
                    }, 2000);
                },

                saveDraft(isAuto = false) {
                    if (this.saving) return;
                    this.saving = true;

                    fetch('{{ route('vendor.design.save') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ config: this.config })
                    })
                    .then(r => r.json())
                    .then(data => {
                        this.saving = false;
                        if (data.status === 'success') {
                            this.isDirty = false;
                            if (!isAuto) {
                                this.showToast('Draft saved successfully at ' + data.last_saved_at);
                                this.reloadPreview();
                            }
                        }
                    })
                    .catch(err => {
                        this.saving = false;
                        console.error(err);
                    });
                },

                openPublishModal() {
                    this.publishModalOpen = true;
                },

                executePublish() {
                    this.publishing = true;
                    fetch('{{ route('vendor.design.publish') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        this.publishing = false;
                        this.publishModalOpen = false;
                        if (data.status === 'success') {
                            this.isDirty = false;
                            this.showToast('🚀 Store Published and Live!');
                            setTimeout(() => {
                                window.open(data.public_url, '_blank');
                            }, 800);
                        } else {
                            alert(data.message || 'Publishing failed');
                        }
                    })
                    .catch(err => {
                        this.publishing = false;
                        alert('Publishing error. Please try again.');
                    });
                },

                showToast(msg) {
                    const toast = document.getElementById('editor-toast');
                    const text = document.getElementById('editor-toast-text');
                    if (toast && text) {
                        text.textContent = msg;
                        toast.classList.remove('translate-y-20', 'opacity-0');
                        toast.classList.add('translate-y-0', 'opacity-100');
                        setTimeout(() => {
                            toast.classList.remove('translate-y-0', 'opacity-100');
                            toast.classList.add('translate-y-20', 'opacity-0');
                        }, 3000);
                    }
                }
            };
        }
    </script>
</body>
</html>

