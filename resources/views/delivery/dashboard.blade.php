<x-layouts.public title="Delivery Pilot Command | K71 Hyperlocal">
    <div class="py-6 sm:py-10 max-w-5xl mx-auto px-4 sm:px-6"
         x-data="{
            isOnline: {{ $partner->is_online ? 'true' : 'false' }},
            lat: {{ $partner->current_latitude ?? 27.0425 }},
            lng: {{ $partner->current_longitude ?? 74.7214 }},
            audioRingActive: false,
            audioCtx: null,
            ringInterval: null,
            newAssignmentModal: null,

            init() {
                this.startLocationTracking();
                this.startAssignmentPolling();
            },

            // Web Audio API Ringtone Alert (Works on mobile & desktop without external mp3 files!)
            playAlertRing() {
                try {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (!this.audioCtx) {
                        this.audioCtx = new AudioContext();
                    }
                    if (this.audioCtx.state === 'suspended') {
                        this.audioCtx.resume();
                    }

                    // Loud chime tones
                    const osc = this.audioCtx.createOscillator();
                    const gain = this.audioCtx.createGain();
                    osc.connect(gain);
                    gain.connect(this.audioCtx.destination);

                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(880, this.audioCtx.currentTime); // A5
                    osc.frequency.exponentialRampToValueAtTime(1760, this.audioCtx.currentTime + 0.15); // A6
                    osc.frequency.setValueAtTime(1320, this.audioCtx.currentTime + 0.25); // E6

                    gain.gain.setValueAtTime(0.8, this.audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, this.audioCtx.currentTime + 0.6);

                    osc.start();
                    osc.stop(this.audioCtx.currentTime + 0.6);
                } catch (e) {
                    console.log('Audio ring error:', e);
                }
            },

            startRinging() {
                if (this.audioRingActive) return;
                this.audioRingActive = true;
                this.playAlertRing();
                this.ringInterval = setInterval(() => {
                    if (this.audioRingActive) {
                        this.playAlertRing();
                    }
                }, 1200);
            },

            stopRinging() {
                this.audioRingActive = false;
                if (this.ringInterval) {
                    clearInterval(this.ringInterval);
                    this.ringInterval = null;
                }
            },

            startLocationTracking() {
                if (navigator.geolocation) {
                    navigator.geolocation.watchPosition((pos) => {
                        this.lat = pos.coords.latitude;
                        this.lng = pos.coords.longitude;
                        this.syncLocation();
                    }, (err) => {}, { enableHighAccuracy: true });
                }
            },

            syncLocation() {
                fetch('{{ route('delivery.location') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        latitude: this.lat,
                        longitude: this.lng,
                        is_online: this.isOnline
                    })
                }).catch(() => {});
            },

            toggleOnline() {
                this.isOnline = !this.isOnline;
                this.syncLocation();
            },

            startAssignmentPolling() {
                setInterval(() => {
                    if (!this.isOnline) return;
                    fetch('{{ route('delivery.check_assignments') }}')
                        .then(r => r.json())
                        .then(data => {
                            if (data.has_new && data.assignment) {
                                this.newAssignmentModal = data.assignment;
                                this.startRinging();
                            }
                        })
                        .catch(() => {});
                }, 4000);
            },

            acceptOrder(assignmentId) {
                this.stopRinging();
                fetch(`/delivery/assignments/${assignmentId}/accept`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(r => r.json())
                .then(d => {
                    this.newAssignmentModal = null;
                    window.location.reload();
                });
            },

            pickupOrder(assignmentId) {
                fetch(`/delivery/assignments/${assignmentId}/pickup`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(r => r.json())
                .then(d => window.location.reload());
            },

            deliverOrder(assignmentId) {
                fetch(`/delivery/assignments/${assignmentId}/deliver`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(r => r.json())
                .then(d => window.location.reload());
            }
         }">

        <!-- INCOMING ORDER POPUP MODAL WITH LOUD BELL RINGTONE -->
        <div x-show="newAssignmentModal" 
             x-cloak 
             x-transition 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
            <div class="w-full max-w-md bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border-4 border-amber-400 animate-bounce-subtle text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-400 text-white flex items-center justify-center text-3xl shadow-lg shadow-amber-400/40 mb-4 animate-pulse">
                    🔔
                </div>
                <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-black uppercase tracking-wider mb-2">
                    ⚡ New Pickup Dispatched To You!
                </span>
                <h3 class="text-xl font-black text-slate-950" x-text="newAssignmentModal?.vendor_name"></h3>
                <p class="text-xs text-slate-500 mt-1" x-text="newAssignmentModal?.pickup_address"></p>

                <div class="my-5 p-4 rounded-2xl bg-slate-50 border border-slate-200 grid grid-cols-2 gap-3 text-center">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Payout Earned</span>
                        <div class="text-lg font-black text-emerald-600" x-text="newAssignmentModal?.payout"></div>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Pickup Distance</span>
                        <div class="text-lg font-black text-slate-900" x-text="newAssignmentModal?.distance"></div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="stopRinging(); newAssignmentModal = null" 
                            class="flex-1 py-3 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100 transition cursor-pointer">
                        Pass Order
                    </button>
                    <button type="button" @click="acceptOrder(newAssignmentModal?.id)" 
                            class="flex-1 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-lg shadow-emerald-600/30 transition cursor-pointer">
                        ACCEPT &amp; NAVIGATE 🛵
                    </button>
                </div>
            </div>
        </div>

        <!-- Pilot Header Card -->
        <div class="bg-white/90 backdrop-blur-xl rounded-3xl border border-slate-200/90 shadow-sm p-6 mb-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-950 text-white font-bold flex items-center justify-center text-xl shadow-md">
                        🛵
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-lg font-black text-slate-900">{{ $partner->name }}</h1>
                            <span class="text-[10px] font-mono font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full uppercase">
                                {{ $partner->vehicle_type }} ({{ $partner->vehicle_number ?: 'K71 Pilot' }})
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                            <span>📍 {{ $partner->city }}, {{ $partner->state }}</span>
                            <span>•</span>
                            <span class="text-amber-600 font-bold">★ {{ number_format($partner->rating, 1) }}</span>
                            <span>•</span>
                            <span>{{ $partner->total_deliveries }} completed trips</span>
                        </div>
                    </div>
                </div>

                <!-- Online / Offline Switch -->
                <div class="flex items-center gap-3">
                    <button type="button" @click="toggleOnline()" 
                            :class="isOnline ? 'bg-emerald-600 text-white shadow-emerald-600/30' : 'bg-slate-200 text-slate-700'"
                            class="px-4 py-2.5 rounded-2xl font-black text-xs shadow-md transition-all flex items-center gap-2 cursor-pointer">
                        <span class="w-2.5 h-2.5 rounded-full" :class="isOnline ? 'bg-white animate-pulse' : 'bg-slate-400'"></span>
                        <span x-text="isOnline ? 'ONLINE & READY FOR PICKUP' : 'OFFLINE (PAUSED)'"></span>
                    </button>
                    <!-- Audio Test Trigger -->
                    <button type="button" @click="playAlertRing()" class="p-2.5 rounded-xl border border-slate-200 text-slate-600 hover:text-slate-900 text-xs" title="Test Ringtone">
                        🔔 Test Ring
                    </button>
                </div>
            </div>

            <!-- Stats Strip -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-6 border-t border-slate-100">
                <div class="p-3 rounded-2xl bg-slate-50/80">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Today's Earnings</span>
                    <span class="text-base font-black text-emerald-600">₹{{ number_format($totalEarningsToday, 2) }}</span>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50/80">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Active Orders</span>
                    <span class="text-base font-black text-slate-900">{{ $activeAssignments->count() }}</span>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50/80">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Payout UPI</span>
                    <span class="text-xs font-mono font-bold text-slate-700 truncate block">{{ $partner->payout_upi_id ?: 'Add UPI' }}</span>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50/80">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Speed Tier</span>
                    <span class="text-xs font-bold text-indigo-600">⚡ 10-Min Hyperlocal</span>
                </div>
            </div>
        </div>

        <!-- Active Deliveries Section -->
        <div class="space-y-4 mb-8">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span>🛵 Active Pickups &amp; Deliveries</span>
                    <span class="text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-bold">{{ $activeAssignments->count() }}</span>
                </h2>
                <span class="text-xs text-slate-400">Updates live automatically</span>
            </div>

            @forelse($activeAssignments as $assignment)
                <div class="p-5 rounded-2xl bg-white border-2 border-emerald-500/30 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 font-black flex items-center justify-center text-sm shrink-0">
                            #{{ $assignment->order ? substr($assignment->order->order_number, -4) : $assignment->id }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-slate-900">{{ $assignment->vendor ? $assignment->vendor->shop_name : 'Merchant Store' }}</h3>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $assignment->status === 'picked_up' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $assignment->status === 'picked_up' ? 'Out for Delivery' : 'Ready for Pickup' }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Pickup from: <strong class="text-slate-800">{{ $assignment->vendor ? ($assignment->vendor->pickup_address['line_1'] ?? 'Makrana Center') : 'Vendor Store' }}</strong>
                            </p>
                            <p class="text-xs text-slate-500">
                                Deliver to: <strong class="text-slate-800">{{ $assignment->order ? ($assignment->order->shipping_address['line_1'] ?? 'Customer Address') : 'Customer' }}</strong>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <span class="text-sm font-mono font-black text-emerald-600 mr-2">
                            +₹{{ number_format($assignment->payout_amount, 2) }}
                        </span>
                        
                        @if($assignment->status === 'assigned')
                            <button type="button" @click="acceptOrder({{ $assignment->id }})" 
                                    class="flex-1 sm:flex-none px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition cursor-pointer">
                                Accept Order
                            </button>
                        @elseif($assignment->status === 'accepted')
                            <button type="button" @click="pickupOrder({{ $assignment->id }})" 
                                    class="flex-1 sm:flex-none px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition cursor-pointer">
                                Confirm Pickup From Store 📦
                            </button>
                        @elseif($assignment->status === 'picked_up')
                            <button type="button" @click="deliverOrder({{ $assignment->id }})" 
                                    class="flex-1 sm:flex-none px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition cursor-pointer">
                                Mark Delivered to Customer ✓
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 rounded-2xl bg-white border border-slate-200 text-center">
                    <div class="text-3xl mb-2">🛵</div>
                    <h3 class="text-sm font-bold text-slate-800">No Active Trips Assigned</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Keep your status <strong class="text-emerald-600">ONLINE</strong>. When nearby food and Feasto merchants book a pickup, your phone will ring instantly!
                    </p>
                </div>
            @endforelse
        </div>

        <!-- Completed Deliveries Today -->
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Recently Delivered</h3>
            <div class="bg-white rounded-2xl border border-slate-200/80 divide-y divide-slate-100 overflow-hidden">
                @forelse($completedAssignments as $c)
                    <div class="p-4 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">✓</span>
                            <div>
                                <span class="font-bold text-slate-900">{{ $c->vendor ? $c->vendor->shop_name : 'Merchant' }}</span>
                                <span class="text-slate-400 text-[10px] block">Order #{{ $c->order ? $c->order->order_number : $c->order_id }} • {{ $c->delivered_at ? $c->delivered_at->format('h:i A') : 'Today' }}</span>
                            </div>
                        </div>
                        <span class="font-mono font-bold text-emerald-600">+₹{{ number_format($c->payout_amount, 2) }}</span>
                    </div>
                @empty
                    <div class="p-4 text-center text-xs text-slate-400">
                        No completed trips yet today.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</x-layouts.public>

