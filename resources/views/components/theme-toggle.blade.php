@props([
    'variant' => 'icon', // 'icon', 'pill', 'switch'
    'grailTheme' => false,
])

<div x-data="{
        theme: 'dark',
        init() {
            try {
                const stored = localStorage.getItem('k71_theme');
                this.theme = stored === 'light' ? 'light' : 'dark';
            } catch (e) {
                this.theme = 'dark';
            }
            window.addEventListener('k71-theme-changed', (e) => {
                if (e.detail && e.detail.theme) {
                    this.theme = e.detail.theme;
                }
            });
        },
        toggle() {
            const next = this.theme === 'light' ? 'dark' : 'light';
            this.theme = next;
            const doc = document.documentElement;
            if (next === 'light') {
                doc.classList.add('k71-light-mode');
                doc.classList.remove('k71-dark-mode');
                doc.setAttribute('data-theme', 'light');
            } else {
                doc.classList.add('k71-dark-mode');
                doc.classList.remove('k71-light-mode');
                doc.setAttribute('data-theme', 'dark');
            }
            try {
                localStorage.setItem('k71_theme', next);
                document.cookie = 'k71_theme=' + next + ';path=/;max-age=31536000;SameSite=Lax';
            } catch (e) {}
            window.dispatchEvent(new CustomEvent('k71-theme-changed', { detail: { theme: next } }));
        },
        setTheme(t) {
            if (this.theme === t) return;
            this.theme = t;
            const doc = document.documentElement;
            if (t === 'light') {
                doc.classList.add('k71-light-mode');
                doc.classList.remove('k71-dark-mode');
                doc.setAttribute('data-theme', 'light');
            } else {
                doc.classList.add('k71-dark-mode');
                doc.classList.remove('k71-light-mode');
                doc.setAttribute('data-theme', 'dark');
            }
            try {
                localStorage.setItem('k71_theme', t);
                document.cookie = 'k71_theme=' + t + ';path=/;max-age=31536000;SameSite=Lax';
            } catch (e) {}
            window.dispatchEvent(new CustomEvent('k71-theme-changed', { detail: { theme: t } }));
        }
    }" 
    class="inline-flex items-center">

    @if($variant === 'switch')
        <!-- Switch variant (for Hamburger Drawer or Settings menus) -->
        <div class="flex items-center justify-between w-full p-2.5 rounded-2xl bg-white/5 border border-white/10 dark-switch-container">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl flex items-center justify-center text-base"
                      :class="theme === 'light' ? 'bg-amber-100 text-amber-600' : 'bg-white/10 text-[#ECD2F3]'">
                    <span x-show="theme === 'light'">â˜€ï¸ </span>
                    <span x-show="theme !== 'light'">ðŸŒ™</span>
                </span>
                <div class="leading-tight text-left">
                    <span class="text-xs font-bold block" :class="theme === 'light' ? 'text-slate-900' : 'text-white'">Display Theme</span>
                    <span class="text-[10px] block opacity-70" :class="theme === 'light' ? 'text-slate-500' : 'text-[#EFE9F2]'" x-text="theme === 'light' ? 'Premium Light Glare' : 'Obsidian Luxury Dark'"></span>
                </div>
            </div>
            
            <div class="flex items-center p-1 rounded-full bg-black/40 border border-white/10 text-xs">
                <button type="button" 
                        @click="setTheme('light')" 
                        :class="theme === 'light' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-white/60 hover:text-white'"
                        class="px-2.5 py-1 rounded-full text-[11px] transition-all flex items-center gap-1 cursor-pointer">
                    <span>â˜€ï¸ </span>
                    <span>Light</span>
                </button>
                <button type="button" 
                        @click="setTheme('dark')" 
                        :class="theme === 'dark' ? 'bg-[#1A1018] text-[#ECD2F3] border border-[#ECD2F3]/40 shadow-sm font-bold' : 'text-white/60 hover:text-white'"
                        class="px-2.5 py-1 rounded-full text-[11px] transition-all flex items-center gap-1 cursor-pointer">
                    <span>ðŸŒ™</span>
                    <span>Dark</span>
                </button>
            </div>
        </div>

    @elseif($variant === 'pill')
        <!-- Pill variant with text and icon -->
        <button type="button"
                @click="toggle()"
                class="k71-theme-toggle-btn group relative inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-all duration-300 cursor-pointer focus:outline-none"
                :class="theme === 'light' 
                    ? 'bg-slate-100/90 hover:bg-slate-200 text-slate-900 border border-slate-300/80 shadow-[0_2px_8px_rgba(0,0,0,0.05),0_0_12px_rgba(236,210,243,0.4)]' 
                    : 'bg-white/10 hover:bg-white/15 text-[#EFE9F2] border border-white/15 shadow-[0_2px_12px_rgba(0,0,0,0.4),0_0_12px_rgba(251,191,36,0.15)]'"
                :title="theme === 'light' ? 'Switch to Obsidian Dark Mode' : 'Switch to Premium Light Glare'"
                aria-label="Toggle dark/light mode">
            
            <!-- Sun Icon (visible when Dark Mode is active, clicking switches to Light) -->
            <span x-show="theme !== 'light'" class="flex items-center gap-1.5 text-amber-300">
                <svg class="w-4 h-4 transition-transform group-hover:rotate-45 duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="4" stroke-width="2" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41" />
                </svg>
                <span class="text-[11px] font-bold text-amber-200 hidden xl:inline">Light</span>
            </span>

            <!-- Moon Icon (visible when Light Mode is active, clicking switches to Dark) -->
            <span x-show="theme === 'light'" class="flex items-center gap-1.5 text-[#623562]">
                <svg class="w-4 h-4 transition-transform group-hover:-rotate-12 duration-300 text-[#623562]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
                <span class="text-[11px] font-bold text-[#1A1018] hidden xl:inline">Dark</span>
            </span>
        </button>

    @else
        <!-- Icon-only Jewel button (Default for compact header navbar) -->
        <button type="button"
                @click="toggle()"
                class="k71-theme-toggle-btn group relative p-2 sm:p-2.5 rounded-xl sm:rounded-full transition-all duration-300 cursor-pointer focus:outline-none overflow-hidden"
                :class="theme === 'light' 
                    ? 'bg-slate-100 hover:bg-slate-200 text-[#1A1018] border border-slate-300/80 shadow-[0_2px_8px_rgba(0,0,0,0.04),0_0_14px_rgba(236,210,243,0.5)]' 
                    : 'bg-white/10 hover:bg-white/15 text-[#EFE9F2] border border-white/15 shadow-[0_2px_10px_rgba(0,0,0,0.35),0_0_12px_rgba(251,191,36,0.18)]'"
                :title="theme === 'light' ? 'Switch to Obsidian Dark Mode' : 'Switch to Premium Light Glare'"
                :aria-label="theme === 'light' ? 'Switch to Dark Mode' : 'Switch to Light Mode'">
            
            <!-- Atmospheric hover glare halo inside button -->
            <span class="absolute inset-0 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"
                  :class="theme === 'light' ? 'bg-[radial-gradient(circle_at_center,rgba(236,210,243,0.6),transparent_70%)]' : 'bg-[radial-gradient(circle_at_center,rgba(251,191,36,0.25),transparent_70%)]'"></span>

            <!-- Sun Icon (in Dark Mode, shows Sun to switch to Light) -->
            <svg x-show="theme !== 'light'" 
                 class="w-4 h-4 sm:w-[18px] sm:h-[18px] text-amber-300 transition-transform duration-500 group-hover:rotate-90 group-hover:scale-110 relative z-10" 
                 fill="none" 
                 stroke="currentColor" 
                 viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="4" stroke-width="2" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41" />
            </svg>

            <!-- Moon & Stars Icon (in Light Mode, shows Moon to switch to Dark) -->
            <svg x-show="theme === 'light'" 
                 class="w-4 h-4 sm:w-[18px] sm:h-[18px] text-[#623562] transition-transform duration-500 group-hover:-rotate-12 group-hover:scale-110 relative z-10" 
                 fill="none" 
                 stroke="currentColor" 
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
            </svg>
        </button>
    @endif
</div>

