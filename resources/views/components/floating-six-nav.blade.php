@php
    $shouldHide = request()->routeIs('messages.*')
        || request()->is('messages*')
        || request()->routeIs('checkout.*')
        || request()->is('checkout*')
        || request()->routeIs('orders.*')
        || request()->is('orders*')
        || request()->routeIs('account.orders*')
        || request()->routeIs('admin.*')
        || request()->is('admin*')
        || request()->routeIs('vendor.*')
        || request()->is('vendor*')
        || request()->is('seller*')
        || request()->routeIs('delivery.*')
        || request()->is('delivery*');

    if ($shouldHide) {
        return;
    }

    $launcherItems = \App\Http\Controllers\Admin\FloatingLauncherController::getLauncherItems();
    $activeLauncherItems = collect($launcherItems)->filter(fn($i) => !empty($i['is_active']))->values()->all();
    $triggerImage = \App\Models\Setting::get('floating_launcher_trigger_image', null);
    $triggerText = \App\Models\Setting::get('floating_launcher_trigger_text', 'NAV');
    $closeText = \App\Models\Setting::get('floating_launcher_close_text', '✕');
@endphp

<style>
    .k71-nav-draggable-dock {
        position: fixed !important;
        z-index: 9999 !important;
        user-select: none;
        -webkit-user-select: none;
        touch-action: none;
        cursor: grab;
    }
    .k71-nav-draggable-dock.is-dragging {
        cursor: grabbing;
        transition: none !important;
    }
</style>

<div class="k71-nav-draggable-dock" 
     :class="{ 'is-dragging': isDragging }"
     x-data="floatingSixNav()"
     x-init="init()"
     :style="dockStyle"
     @pointerdown="onPointerDown($event)"
     @click.outside="close()"
     @keydown.escape.window="close()">

    <!-- Radial Arc Satellites Container -->
    <div x-show="open" 
         x-cloak 
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 scale-75"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-75"
         class="absolute inset-0 pointer-events-none">
        
        <template x-for="(item, index) in navItems" :key="item.id">
            <div class="absolute pointer-events-auto transition-all duration-300 ease-out"
                 :style="(() => {
                    const radius = 95;
                    const total = Math.max(navItems.length - 1, 1);
                    let angleDeg;
                    if (isLeftAligned) {
                        angleDeg = -80 + (index * (160 / total));
                    } else {
                        angleDeg = 100 + (index * (160 / total));
                    }
                    const rad = (angleDeg * Math.PI) / 180;
                    const x = Math.round(Math.cos(rad) * radius);
                    const y = Math.round(Math.sin(rad) * radius);
                    return 'transform: translate3d(' + x + 'px, ' + y + 'px, 0);';
                 })()">
                <a :href="item.url" 
                   class="group flex flex-col items-center justify-center w-12 h-12 rounded-2xl bg-slate-950/95 backdrop-blur-2xl border border-white/20 shadow-2xl hover:scale-110 active:scale-95 transition-all cursor-pointer relative overflow-hidden text-center"
                   :title="item.label">
                    
                    <!-- Subtle Top Gradient Sheen -->
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r" :class="item.color"></div>

                    <!-- Custom Image Icon if Uploaded -->
                    <template x-if="item.image_url">
                        <img :src="item.image_url" :alt="item.name" class="w-5 h-5 object-contain group-hover:scale-110 transition-transform">
                    </template>

                    <!-- Clean Vector SVG Glyphs fallback -->
                    <template x-if="!item.image_url && item.id === 'mart'">
                        <svg class="w-5 h-5 text-amber-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </template>
                    <template x-if="!item.image_url && item.id === 'feasto'">
                        <svg class="w-5 h-5 text-emerald-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </template>
                    <template x-if="!item.image_url && item.id === 'services'">
                        <svg class="w-5 h-5 text-indigo-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </template>
                    <template x-if="!item.image_url && item.id === 'used'">
                        <svg class="w-5 h-5 text-cyan-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </template>
                    <template x-if="!item.image_url && item.id === 'shorts'">
                        <svg class="w-5 h-5 text-rose-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                    <template x-if="!item.image_url && item.id === 'fleet'">
                        <svg class="w-5 h-5 text-yellow-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                    <template x-if="!item.image_url && !['mart', 'feasto', 'services', 'used', 'shorts', 'fleet'].includes(item.id)">
                        <svg class="w-5 h-5 text-amber-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </template>

                    <span class="text-[8px] font-black tracking-tight text-white/90 leading-none mt-1" x-text="item.name"></span>
                    
                    <!-- Crisp Hover Tooltip -->
                    <span class="absolute -top-7 whitespace-nowrap bg-black text-white text-[9px] font-bold px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none shadow-xl border border-white/10"
                          x-text="item.label"></span>
                </a>
            </div>
        </template>
    </div>

    <!-- Center Trigger Button (Obsidian Glass Floating Action Button) -->
    <button type="button"
            @click="handleButtonClick($event)"
            aria-label="Toggle 6-in-1 Navigation"
            class="w-13 h-13 sm:w-14 sm:h-14 rounded-full bg-slate-950/95 text-white shadow-[0_10px_35px_rgba(0,0,0,0.55)] border border-white/20 backdrop-blur-2xl flex items-center justify-center cursor-pointer hover:scale-105 active:scale-95 transition-all duration-200 relative group overflow-hidden focus:outline-none">
        
        <!-- Subtle Specular Edge Ring -->
        <div class="absolute inset-0 rounded-full border border-white/15 pointer-events-none"></div>

        <!-- Normal State: Custom Trigger Image or Multi-Portal Glyph -->
        <div x-show="!open" class="flex flex-col items-center justify-center transition-all w-full h-full p-1.5">
            @if(!empty($triggerImage))
                <img src="{{ $triggerImage }}" alt="{{ $triggerText }}" class="w-6 h-6 sm:w-7 sm:h-7 object-contain rounded-full shadow-xs">
                @if(!empty($triggerText))
                    <span class="text-[7px] font-black tracking-wider uppercase text-white/90 leading-none mt-0.5 truncate max-w-[44px]">{{ $triggerText }}</span>
                @endif
            @else
                <svg class="w-5 h-5 text-white group-hover:text-amber-300 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="2"></rect>
                    <rect x="14" y="3" width="7" height="7" rx="2"></rect>
                    <rect x="14" y="14" width="7" height="7" rx="2"></rect>
                    <rect x="3" y="14" width="7" height="7" rx="2"></rect>
                </svg>
                <span class="text-[7.5px] font-black tracking-wider uppercase text-white/90 leading-none mt-1">{{ $triggerText }}</span>
            @endif
        </div>

        <!-- Active Open State: Retract Close Symbol -->
        <div x-show="open" class="flex items-center justify-center transition-all">
            <span class="text-xl sm:text-2xl font-black text-amber-300 leading-none hover:rotate-90 transition-transform">{{ $closeText }}</span>
        </div>

    </button>

</div>

<script>
    function floatingSixNav() {
        return {
            open: false,
            isDragging: false,
            hasDragged: false,
            startX: 0,
            startY: 0,
            initialX: null,
            initialY: null,
            posX: null,
            posY: null,
            navItems: @json($activeLauncherItems),

            get dockStyle() {
                if (this.posX === null || this.posY === null) {
                    return 'right: 16px; bottom: 85px;';
                }
                return `left: ${this.posX}px; top: ${this.posY}px; right: auto; bottom: auto;`;
            },

            get isLeftAligned() {
                if (this.posX === null) return false;
                return this.posX < (window.innerWidth / 2);
            },

            init() {
                try {
                    const saved = JSON.parse(localStorage.getItem('k71_6in1_nav_pos'));
                    if (saved && typeof saved.x === 'number' && typeof saved.y === 'number') {
                        this.posX = Math.max(10, Math.min(window.innerWidth - 65, saved.x));
                        this.posY = Math.max(10, Math.min(window.innerHeight - 65, saved.y));
                    }
                } catch(e) {}

                if (this.posX === null || this.posY === null) {
                    if (window.innerWidth <= 640) {
                        this.posX = Math.max(10, window.innerWidth - 68);
                        this.posY = Math.max(10, window.innerHeight - 150);
                    } else {
                        this.posX = Math.max(10, window.innerWidth - 75);
                        this.posY = Math.max(10, Math.round((window.innerHeight / 2) - 30));
                    }
                }

                window.addEventListener('pointermove', (e) => this.onPointerMove(e));
                window.addEventListener('pointerup', (e) => this.onPointerUp(e));
                window.addEventListener('pointercancel', (e) => this.onPointerUp(e));

                window.addEventListener('resize', () => {
                    if (this.posX !== null && this.posY !== null) {
                        this.posX = Math.max(10, Math.min(window.innerWidth - 65, this.posX));
                        this.posY = Math.max(10, Math.min(window.innerHeight - 65, this.posY));
                    }
                });
            },

            onPointerDown(e) {
                if (e.target.closest('a')) return;
                this.isDragging = false;
                this.hasDragged = false;
                this.startX = e.clientX;
                this.startY = e.clientY;
                this.initialX = this.posX;
                this.initialY = this.posY;
            },

            onPointerMove(e) {
                if (this.initialX === null || this.initialY === null || this.startX === 0) return;
                const dx = e.clientX - this.startX;
                const dy = e.clientY - this.startY;
                const dist = Math.hypot(dx, dy);

                if (dist > 6) {
                    this.isDragging = true;
                    this.hasDragged = true;
                    this.open = false;
                    const newX = this.initialX + dx;
                    const newY = this.initialY + dy;
                    this.posX = Math.max(10, Math.min(window.innerWidth - 65, newX));
                    this.posY = Math.max(10, Math.min(window.innerHeight - 65, newY));
                }
            },

            onPointerUp(e) {
                if (this.hasDragged) {
                    try {
                        localStorage.setItem('k71_6in1_nav_pos', JSON.stringify({ x: this.posX, y: this.posY }));
                    } catch(e) {}
                }
                this.isDragging = false;
                this.startX = 0;
                this.startY = 0;
                this.initialX = null;
                this.initialY = null;
            },

            handleButtonClick(e) {
                if (this.hasDragged) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.hasDragged = false;
                    return;
                }
                this.open = !this.open;
            },

            close() {
                this.open = false;
            }
        };
    }
</script>
