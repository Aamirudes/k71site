@php
    $launcherItems = \App\Http\Controllers\Admin\FloatingLauncherController::getLauncherItems();
    $activeLauncherItems = collect($launcherItems)->filter(fn($i) => !empty($i['is_active']))->values()->all();
@endphp

<div x-data="{
        open: false,
        isDragging: false,
        dragStartX: 0,
        dragStartY: 0,
        posX: window.innerWidth - 76,
        posY: window.innerHeight - 170,
        hasMoved: false,

        startDrag(e) {
            this.isDragging = true;
            this.hasMoved = false;
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            this.dragStartX = clientX - this.posX;
            this.dragStartY = clientY - this.posY;
            document.addEventListener('mousemove', this.onDrag.bind(this));
            document.addEventListener('mouseup', this.stopDrag.bind(this));
            document.addEventListener('touchmove', this.onDrag.bind(this));
            document.addEventListener('touchend', this.stopDrag.bind(this));
        },

        onDrag(e) {
            if (!this.isDragging) return;
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            const newX = clientX - this.dragStartX;
            const newY = clientY - this.dragStartY;
            if (Math.abs(newX - this.posX) > 5 || Math.abs(newY - this.posY) > 5) {
                this.hasMoved = true;
            }
            this.posX = Math.max(16, Math.min(window.innerWidth - 76, newX));
            this.posY = Math.max(60, Math.min(window.innerHeight - 90, newY));
        },

        stopDrag() {
            this.isDragging = false;
            document.removeEventListener('mousemove', this.onDrag);
            document.removeEventListener('mouseup', this.stopDrag);
            document.removeEventListener('touchmove', this.onDrag);
            document.removeEventListener('touchend', this.stopDrag);
        },

        toggle() {
            if (this.hasMoved) return;
            this.open = !this.open;
        },

        navItems: {{ Js::from($activeLauncherItems) }}
     }"
     :style="'transform: translate3d(' + posX + 'px, ' + posY + 'px, 0);'"
     class="fixed top-0 left-0 z-50 select-none touch-none transition-transform duration-75"
     style="will-change: transform;">

    <!-- Radial Semi-Circle 6 Nav Items Container -->
    <div x-show="open" 
         x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-50"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-50"
         class="absolute inset-0 pointer-events-none">
        
        <template x-for="(item, index) in navItems" :key="item.id">
            <div class="absolute pointer-events-auto transition-all duration-300 ease-out"
                 :style="(() => {
                    const radius = 95;
                    const isRightSide = posX > (window.innerWidth / 2);
                    const baseAngle = isRightSide ? 95 + (index * 34) : -85 + (index * 34);
                    const rad = (baseAngle * Math.PI) / 180;
                    const x = Math.round(Math.cos(rad) * radius);
                    const y = Math.round(Math.sin(rad) * radius);
                    return 'transform: translate3d(' + x + 'px, ' + y + 'px, 0);';
                 })()">
                <a :href="item.url" 
                   class="group flex flex-col items-center justify-center w-12 h-12 rounded-2xl bg-slate-900/90 dark:bg-black/90 backdrop-blur-2xl border border-white/20 shadow-2xl hover:scale-110 active:scale-95 transition-all cursor-pointer relative overflow-hidden"
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

    <!-- Center Trigger Button (Apple Dynamic Island Pro Obsidian Glass) -->
    <div @mousedown="startDrag($event)" 
         @touchstart="startDrag($event)"
         @click="toggle()"
         class="w-14 h-14 rounded-full bg-black/90 dark:bg-black/95 text-white shadow-[0_10px_35px_rgba(0,0,0,0.55)] border border-white/25 backdrop-blur-2xl flex items-center justify-center cursor-grab active:cursor-grabbing hover:scale-105 active:scale-95 transition-all duration-200 relative group overflow-hidden">
        
        <!-- Subtle Specular Edge Ring -->
        <div class="absolute inset-0 rounded-full border border-white/20 pointer-events-none"></div>

        <!-- Normal State: Sleek Multi-Portal Glyph -->
        <div x-show="!open" class="flex flex-col items-center justify-center transition-all">
            <svg class="w-5 h-5 text-white group-hover:text-amber-300 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7" rx="2"></rect>
                <rect x="14" y="3" width="7" height="7" rx="2"></rect>
                <rect x="14" y="14" width="7" height="7" rx="2"></rect>
                <rect x="3" y="14" width="7" height="7" rx="2"></rect>
            </svg>
            <span class="text-[7.5px] font-black tracking-wider uppercase text-white/80 leading-none mt-1">NAV</span>
        </div>

        <!-- Active Open State: Retract Button '✱' -->
        <div x-show="open" class="flex items-center justify-center transition-all">
            <span class="text-2xl font-black text-amber-300 leading-none hover:rotate-90 transition-transform">✱</span>
        </div>

    </div>

</div>
