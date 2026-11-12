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
    $rawTriggerImage = \App\Models\Setting::get('floating_launcher_trigger_image', null);
    $triggerImage = (!empty($rawTriggerImage) && !preg_match('/(k71-logo|\/images\/logo\.)/i', (string) $rawTriggerImage))
        ? $rawTriggerImage
        : null;
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
    .k71-circle-img-holder {
        border-radius: 100px !important;
        border: none !important;
        outline: none !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.35) !important;
        overflow: hidden !important;
    }
</style>

<div class="k71-nav-draggable-dock" 
     :class="{ 'is-dragging': isDragging }"
     x-data="floatingSixNav()"
     x-init="init()"
     x-show="!modalHidden"
     x-cloak
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
                   class="k71-circle-img-holder flex items-center justify-center w-12 h-12 bg-black hover:scale-110 active:scale-95 transition-transform cursor-pointer relative"
                   style="border-radius: 100px; border: none; outline: none;">
                    
                    <!-- Image Only (No text, no color bar, no outline) -->
                    <template x-if="item.image_url">
                        <img :src="item.image_url" :alt="item.name" class="w-full h-full object-cover" style="border-radius: 100px;">
                    </template>

                    <!-- Clean Monochrome SVG Glyph Fallback if no image uploaded -->
                    <template x-if="!item.image_url && item.id === 'mart'">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </template>
                    <template x-if="!item.image_url && item.id === 'feasto'">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </template>
                    <template x-if="!item.image_url && item.id === 'services'">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </template>
                    <template x-if="!item.image_url && item.id === 'used'">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </template>
                    <template x-if="!item.image_url && item.id === 'shorts'">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                    <template x-if="!item.image_url && item.id === 'enquiry'">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </template>
                    <template x-if="!item.image_url && !['mart', 'feasto', 'services', 'used', 'shorts', 'enquiry'].includes(item.id)">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </template>
                </a>
            </div>
        </template>
    </div>

    <!-- Center Trigger Button (Image Only, 100px border-radius circle, No Text, No Color, No Outline) -->
    <button type="button"
            @click="handleButtonClick($event)"
            aria-label="Toggle 6-in-1 Navigation"
            class="k71-circle-img-holder w-13 h-13 sm:w-14 sm:h-14 bg-black text-white flex items-center justify-center cursor-pointer hover:scale-105 active:scale-95 transition-transform duration-200 relative group overflow-hidden focus:outline-none"
            style="border-radius: 100px; border: none; outline: none;">
        
        <!-- Normal State: Obsidian Black + Orchid Pink K71 Trigger Mark -->
        <div x-show="!open" class="flex items-center justify-center w-full h-full bg-[#0D0D0D] ring-1 ring-[#ECD2F3]/50" style="border-radius: 100px;">
            @if(!empty($triggerImage))
                <img src="{{ $triggerImage }}" alt="K71 Nav" class="w-full h-full object-cover" style="border-radius: 100px;">
            @else
                <svg class="w-full h-full" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="32" cy="32" r="31" fill="#0D0D0D" stroke="#ECD2F3" stroke-opacity="0.7" stroke-width="2"/>
                    <path d="M13 21 H18.2 V43 H13 Z M24.2 21 H29.5 L21 32 H18.2 Z M18.2 30.6 H21.6 L30 43 H24.2 Z" fill="#FBF6FD"/>
                    <path d="M31 21 H44 V25.2 L36.2 43 H31.2 L38.6 25.2 H31 Z" fill="#FFCDFF"/>
                    <path d="M45.5 23.4 L49 21 H52.8 V43 H48.6 V25.8 L45.5 26.8 Z" fill="#ECD2F3"/>
                </svg>
            @endif
        </div>

        <!-- Active Open State: Retract Close Symbol -->
        <div x-show="open" class="flex items-center justify-center w-full h-full bg-[#0D0D0D] text-[#ECD2F3] ring-1 ring-[#ECD2F3]/50" style="border-radius: 100px;">
            <span class="text-xl sm:text-2xl font-black text-[#ECD2F3] leading-none">{{ $closeText }}</span>
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
            modalHidden: false,
            navItems: @json($activeLauncherItems),

            get dockStyle() {
                if (this.posX === null || this.posY === null) {
                    return 'right: 12px; top: 50%; transform: translateY(-50%);';
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

                // Default Position: Middle Center Right
                if (this.posX === null || this.posY === null) {
                    this.posX = Math.max(10, window.innerWidth - (window.innerWidth <= 640 ? 65 : 75));
                    this.posY = Math.max(10, Math.round((window.innerHeight / 2) - 28));
                }

                // Modal / Lightbox listener: Completely hide 6-in-1 nav when any video player modal is open
                window.addEventListener('shorts-modal-state', (e) => {
                    this.modalHidden = !!(e.detail && e.detail.open);
                    if (this.modalHidden) this.open = false;
                });

                const checkModal = () => {
                    const active = document.body.classList.contains('shorts-modal-active') ||
                                   document.querySelector('[data-shorts-player-open="true"]') !== null;
                    if (this.modalHidden !== active) {
                        this.modalHidden = active;
                        if (this.modalHidden) this.open = false;
                    }
                };

                const observer = new MutationObserver(checkModal);
                observer.observe(document.body, { attributes: true, attributeFilter: ['class', 'data-shorts-player-open'] });

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
