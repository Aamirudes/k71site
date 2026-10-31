<div x-data="{
        open: false,
        isDragging: false,
        dragStartX: 0,
        dragStartY: 0,
        posX: window.innerWidth - 76,
        posY: window.innerHeight - 180,
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
            this.posY = Math.max(60, Math.min(window.innerHeight - 100, newY));
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

        navItems: [
            { name: 'K71Mart', icon: '🛍️', label: 'Fashion & Mart', url: '{{ k71_vertical_url('main') }}', bg: 'from-amber-600 to-orange-600', angle: 0 },
            { name: 'Feasto', icon: '⚡', label: '10-Min Fast Needs', url: '{{ route('feasto.index') }}', bg: 'from-emerald-600 to-teal-600', angle: 36 },
            { name: 'Services', icon: '💼', label: 'Freelance & Gigs', url: '{{ k71_vertical_url('services') }}', bg: 'from-indigo-600 to-violet-600', angle: 72 },
            { name: 'Used', icon: '♻️', label: 'Pre-Loved OLX', url: '{{ k71_vertical_url('used') }}', bg: 'from-teal-600 to-cyan-700', angle: 108 },
            { name: 'Shorts', icon: '▶', label: 'Video Commerce', url: '{{ k71_vertical_url('shorts') }}', bg: 'from-zinc-900 to-black', angle: 144 },
            { name: 'Fleet', icon: '🛵', label: 'Earn on K71', url: '{{ route('delivery.onboarding') }}', bg: 'from-rose-600 to-pink-600', angle: 180 }
        ]
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
        
        <template x-for="(item, index) in navItems" :key="index">
            @php
                // Pre-calculated trigonometric offsets for a semi-circle arc (radius ~105px)
                // Left or Right side adapted based on position
            @endphp
            <div class="absolute pointer-events-auto transition-all duration-300 ease-out"
                 :style="(() => {
                    const radius = 100;
                    // If near right edge of screen, radiate leftwards; otherwise radiate rightwards
                    const isRightSide = posX > (window.innerWidth / 2);
                    const baseAngle = isRightSide ? 90 + (index * 36) : -90 + (index * 36);
                    const rad = (baseAngle * Math.PI) / 180;
                    const x = Math.round(Math.cos(rad) * radius);
                    const y = Math.round(Math.sin(rad) * radius);
                    return 'transform: translate3d(' + x + 'px, ' + y + 'px, 0);';
                 })()">
                <a :href="item.url" 
                   class="group flex flex-col items-center justify-center w-12 h-12 rounded-2xl bg-white/95 dark:bg-zinc-900/95 backdrop-blur-xl border border-slate-200 dark:border-zinc-700 shadow-xl hover:scale-115 transition-all cursor-pointer relative"
                   :title="item.label">
                    <span class="text-lg leading-none" x-text="item.icon"></span>
                    <span class="text-[8px] font-black tracking-tight text-slate-800 dark:text-white leading-none mt-0.5" x-text="item.name"></span>
                    <!-- Hover Tooltip -->
                    <span class="absolute -top-7 whitespace-nowrap bg-black text-white text-[9px] font-bold px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none shadow-md"
                          x-text="item.label"></span>
                </a>
            </div>
        </template>
    </div>

    <!-- Center Trigger Button -->
    <div @mousedown="startDrag($event)" 
         @touchstart="startDrag($event)"
         @click="toggle()"
         class="w-14 h-14 rounded-full bg-slate-950 text-white shadow-2xl border-2 border-white/40 flex items-center justify-center cursor-grab active:cursor-grabbing hover:scale-105 transition-all duration-200 relative group overflow-hidden">
        
        <!-- Ambient Radial Glow -->
        <div class="absolute inset-0 bg-gradient-to-tr from-indigo-500/30 via-emerald-500/20 to-amber-500/30 animate-pulse pointer-events-none"></div>

        <!-- Normal State: 6-in-1 Hex / Multiverse Logo Icon -->
        <div x-show="!open" class="flex flex-col items-center justify-center transition-all">
            <svg class="w-6 h-6 text-amber-300 animate-spin-slow" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="3" stroke-width="2" fill="currentColor"/>
                <circle cx="12" cy="4" r="2" stroke-width="2"/>
                <circle cx="19" cy="8" r="2" stroke-width="2"/>
                <circle cx="19" cy="16" r="2" stroke-width="2"/>
                <circle cx="12" cy="20" r="2" stroke-width="2"/>
                <circle cx="5" cy="16" r="2" stroke-width="2"/>
                <circle cx="5" cy="8" r="2" stroke-width="2"/>
            </svg>
            <span class="text-[7px] font-black uppercase tracking-widest text-white leading-none mt-0.5">6-in-1</span>
        </div>

        <!-- Active Open State: Retract Button '*' -->
        <div x-show="open" class="flex items-center justify-center transition-all">
            <span class="text-2xl font-black text-amber-300 leading-none hover:rotate-90 transition-transform">✱</span>
        </div>

    </div>

</div>

<style>
@keyframes spinSlow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow {
    animation: spinSlow 20s linear infinite;
}
</style>

