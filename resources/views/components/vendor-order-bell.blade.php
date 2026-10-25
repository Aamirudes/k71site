@php
    $curVendor = auth()->user()?->vendor;
    $isFood = $curVendor && ($curVendor->vendor_type === 'food' || $curVendor->isRestaurant());
@endphp

<div x-data="vendorOrderAlarmEngine({{ $isFood ? 'true' : 'false' }})"
     x-init="initAlarm()"
     class="relative">

    <!-- FLOATING LOUD ORDER INCOMING ALERT BANNER -->
    <div x-show="activeAlarm" 
         x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="-translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="-translate-y-full opacity-0"
         class="fixed top-4 inset-x-4 sm:inset-x-auto sm:right-6 sm:w-[460px] z-[9999] bg-slate-950/95 dark:bg-black/95 text-white rounded-3xl p-5 shadow-[0_20px_50px_rgba(0,0,0,0.5),0_0_0_1px_rgba(255,255,255,0.15)] backdrop-blur-2xl border border-rose-500/30 animate-pulse">
        
        <div class="flex items-start gap-4">
            <!-- Animated Bell Ringing Indicator -->
            <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center text-2xl shrink-0 shadow-lg shadow-rose-600/40 animate-bounce">
                🔔
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-full bg-rose-500/20 text-rose-400 text-[10px] font-black uppercase tracking-wider border border-rose-500/30">
                        🚨 INCOMING ORDER BELL
                    </span>
                    <span class="text-xs text-zinc-400 font-mono" x-text="activeOrder?.created_at || 'Just Now'"></span>
                </div>

                <h3 class="text-base font-black text-white mt-1.5 tracking-tight flex items-center gap-2">
                    <span x-text="'Order #' + (activeOrder?.order_number || '...')"></span>
                    <span class="text-emerald-400 font-mono text-sm font-bold" x-text="activeOrder?.total || ''"></span>
                </h3>

                <p class="text-xs text-zinc-300 mt-0.5 truncate">
                    Customer: <span class="font-bold text-white" x-text="activeOrder?.customer || 'Direct Buyer'"></span>
                    <span class="text-zinc-500">•</span>
                    <span x-text="(activeOrder?.items_count || 1) + ' items'"></span>
                </p>

                <!-- Action Controls -->
                <div class="mt-4 flex items-center gap-2">
                    <a :href="activeOrder?.url || '{{ route('vendor.orders.index') }}'" 
                       @click="stopRinging()"
                       class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl text-center shadow-md shadow-emerald-600/30 active:scale-95 transition">
                        View Order &rarr;
                    </a>
                    <button type="button" 
                            @click="stopRinging()"
                            class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-zinc-200 text-xs font-bold rounded-xl border border-white/10 transition">
                        Stop Bell
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- PERSISTENT FLOATING AUDIO BELL CONTROLS DOCK -->
    <div class="fixed bottom-6 right-6 z-40 hidden sm:flex items-center gap-2 bg-white/90 dark:bg-zinc-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-zinc-800 p-2 rounded-2xl shadow-lg">
        
        <!-- Bell Status Icon / Test Button -->
        <button type="button" 
                @click="testSound()" 
                title="Click to Test Incoming Order Sound Bell"
                class="flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer"
                :class="isMuted ? 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800' : 'bg-slate-900 text-white dark:bg-white dark:text-zinc-950 shadow-xs'">
            <span :class="isPlaying ? 'animate-spin' : ''" x-text="isMuted ? '🔕' : '🔔'"></span>
            <span x-text="isMuted ? 'Bell Muted' : 'Order Sound Active'"></span>
            <span x-show="pendingCount > 0" class="px-1.5 py-0.2 text-[10px] rounded-full bg-rose-600 text-white font-mono" x-text="pendingCount"></span>
        </button>

        <!-- Mute/Unmute Toggle -->
        <button type="button" 
                @click="toggleMute()" 
                :title="isMuted ? 'Unmute Bell' : 'Mute Bell'"
                class="w-8 h-8 rounded-xl flex items-center justify-center text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition cursor-pointer">
            <span x-text="isMuted ? '🔊' : '🔇'"></span>
        </button>

        <!-- Volume Slider -->
        <div class="flex items-center gap-1.5 px-2">
            <span class="text-[10px] text-zinc-400 font-bold">Vol</span>
            <input type="range" min="0.1" max="1.0" step="0.1" x-model="volume" 
                   @change="updateVolume()" 
                   class="w-16 h-1.5 bg-slate-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-slate-900 dark:accent-white">
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('vendorOrderAlarmEngine', (isFoodVendor = false) => ({
        audioCtx: null,
        isMuted: localStorage.getItem('k71_bell_muted') === 'true',
        volume: parseFloat(localStorage.getItem('k71_bell_volume') || '0.9'),
        isPlaying: false,
        activeAlarm: false,
        activeOrder: null,
        pendingCount: 0,
        lastOrderId: parseInt(localStorage.getItem('k71_last_order_id') || '0'),
        acknowledgedIds: JSON.parse(localStorage.getItem('k71_ack_orders') || '[]'),
        pollingInterval: null,
        alarmInterval: null,

        initAlarm() {
            // Check immediately, then poll every 12 seconds
            this.pollOrders();
            this.pollingInterval = setInterval(() => this.pollOrders(), 12000);

            // Unlock Web Audio on first user interaction anywhere
            const unlockAudio = () => {
                this.getAudioContext();
                document.removeEventListener('click', unlockAudio);
                document.removeEventListener('keydown', unlockAudio);
            };
            document.addEventListener('click', unlockAudio, { once: true });
            document.addEventListener('keydown', unlockAudio, { once: true });
        },

        getAudioContext() {
            if (!this.audioCtx) {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (AudioContext) {
                    this.audioCtx = new AudioContext();
                }
            }
            if (this.audioCtx && this.audioCtx.state === 'suspended') {
                this.audioCtx.resume();
            }
            return this.audioCtx;
        },

        playSingleBellChime() {
            const ctx = this.getAudioContext();
            if (!ctx) return;

            const now = ctx.currentTime;
            const masterGain = ctx.createGain();
            masterGain.gain.setValueAtTime(this.volume, now);
            masterGain.connect(ctx.destination);

            // Dual harmonic tone: 880Hz (A5) and 1318.5Hz (E6)
            const tones = [
                { freq: 880, delay: 0, dur: 0.35 },
                { freq: 1318.5, delay: 0.12, dur: 0.55 },
                { freq: 1760, delay: 0.24, dur: 0.45 }
            ];

            tones.forEach(t => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.type = 'triangle';
                osc.frequency.setValueAtTime(t.freq, now + t.delay);

                // Strike attack and exponential decay bell envelope
                gain.gain.setValueAtTime(0, now + t.delay);
                gain.gain.linearRampToValueAtTime(0.8, now + t.delay + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.001, now + t.delay + t.dur);

                osc.connect(gain);
                gain.connect(masterGain);

                osc.start(now + t.delay);
                osc.stop(now + t.delay + t.dur + 0.05);
            });
        },

        startContinuousRinging(order) {
            this.activeOrder = order;
            this.activeAlarm = true;

            if (this.isMuted) return;

            this.isPlaying = true;
            this.playSingleBellChime();

            // Clear any existing ring loop
            if (this.alarmInterval) clearInterval(this.alarmInterval);

            // Repeat chime every 2.5 seconds
            this.alarmInterval = setInterval(() => {
                if (!this.activeAlarm || this.isMuted) {
                    this.stopRinging();
                    return;
                }
                this.playSingleBellChime();
            }, 2500);
        },

        stopRinging() {
            this.isPlaying = false;
            this.activeAlarm = false;
            if (this.alarmInterval) {
                clearInterval(this.alarmInterval);
                this.alarmInterval = null;
            }
            if (this.activeOrder && this.activeOrder.id) {
                if (!this.acknowledgedIds.includes(this.activeOrder.id)) {
                    this.acknowledgedIds.push(this.activeOrder.id);
                    localStorage.setItem('k71_ack_orders', JSON.stringify(this.acknowledgedIds.slice(-50)));
                }
            }
        },

        testSound() {
            this.getAudioContext();
            this.playSingleBellChime();
        },

        toggleMute() {
            this.isMuted = !this.isMuted;
            localStorage.setItem('k71_bell_muted', this.isMuted ? 'true' : 'false');
            if (this.isMuted) {
                this.stopRinging();
            } else {
                this.testSound();
            }
        },

        updateVolume() {
            localStorage.setItem('k71_bell_volume', this.volume.toString());
            this.testSound();
        },

        pollOrders() {
            fetch('{{ route('vendor.orders.check_new') }}?last_order_id=' + this.lastOrderId, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                this.pendingCount = data.pending_count || 0;

                if (data.latest_order_id && data.latest_order_id > this.lastOrderId) {
                    this.lastOrderId = data.latest_order_id;
                    localStorage.setItem('k71_last_order_id', this.lastOrderId.toString());
                }

                if (data.has_new && data.new_orders && data.new_orders.length > 0) {
                    const unack = data.new_orders.find(o => !this.acknowledgedIds.includes(o.id));
                    if (unack) {
                        this.startContinuousRinging(unack);
                    }
                }
            })
            .catch(() => {});
        }
    }));
});
</script>
