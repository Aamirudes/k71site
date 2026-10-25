<x-layouts.vendor title="Service Bookings & Appointments">
    <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-serif font-bold text-gray-900">Service Bookings & Appointments</h1>
            <p class="text-sm text-gray-500 mt-1">Manage doorstep service appointments, confirm booking slots, and track completion.</p>
        </div>
        <div>
            <a href="{{ route('vendor.products.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-sm">
                <span>+ Add New Service</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-semibold flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Status Tabs -->
    <div class="mb-6 flex items-center gap-2 overflow-x-auto no-scrollbar border-b border-gray-200 pb-3">
        @foreach(['all' => 'All Bookings', 'pending' => 'Pending Confirmation', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $tabKey => $tabLabel)
            <a href="{{ route('vendor.bookings.index', $tabKey === 'all' ? [] : ['status' => $tabKey]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ ($status === $tabKey || (empty($status) && $tabKey === 'all')) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-200' }}">
                <span>{{ $tabLabel }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($status === $tabKey || (empty($status) && $tabKey === 'all')) ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">
                    {{ $counts[$tabKey] ?? 0 }}
                </span>
            </a>
        @endforeach
    </div>

    @if($bookings->isEmpty())
        <div class="p-12 text-center rounded-2xl bg-white border border-gray-200">
            <div class="text-4xl mb-3">📅</div>
            <h3 class="text-base font-bold text-gray-900 mb-1">No bookings in this view</h3>
            <p class="text-xs text-gray-500">When customers book service appointments on your listings, they will appear here instantly.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($bookings as $booking)
                @php
                    $cleanPhone = preg_replace('/[^0-9]/', '', $booking->customer_phone);
                    if (strlen($cleanPhone) === 10) { $cleanPhone = '91' . $cleanPhone; }
                    $waLink = "https://wa.me/{$cleanPhone}?text=" . urlencode("Hi {$booking->customer_name}, regarding your appointment #{$booking->booking_number} for {$booking->product->title} on {$booking->formatted_date}.");
                @endphp
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs transition hover:border-gray-300">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-5">
                        
                        <!-- Left: Service & Appointment Info -->
                        <div class="flex items-start gap-4">
                            @if($booking->product)
                                <img src="{{ $booking->product->primary_image_url }}" alt="{{ $booking->product->title }}"
                                     class="w-16 h-16 rounded-xl object-cover border border-gray-200 shrink-0"
                                     onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                            @else
                                <div class="w-16 h-16 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center text-2xl shrink-0">
                                    🛠️
                                </div>
                            @endif

                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-mono font-bold text-gray-900">#{{ $booking->booking_number }}</span>
                                    <span class="text-xs text-gray-400">• {{ $booking->created_at->diffForHumans() }}</span>
                                    
                                    @if($booking->isPending())
                                        <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold uppercase tracking-wider">
                                            Awaiting Confirmation
                                        </span>
                                    @elseif($booking->isConfirmed())
                                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold uppercase tracking-wider">
                                            Confirmed & Scheduled
                                        </span>
                                    @elseif($booking->isCompleted())
                                        <span class="px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 text-[10px] font-bold uppercase tracking-wider">
                                            Completed
                                        </span>
                                    @elseif($booking->isCancelled())
                                        <span class="px-2.5 py-0.5 rounded-full bg-red-50 text-red-700 border border-red-200 text-[10px] font-bold uppercase tracking-wider">
                                            Cancelled
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-base font-bold text-gray-900 leading-snug">
                                    {{ $booking->product->title }}
                                </h3>

                                <div class="p-2.5 rounded-xl bg-indigo-50/60 border border-indigo-100 text-xs space-y-1 mt-2">
                                    <div class="font-bold text-indigo-900 flex items-center gap-1.5">
                                        <span>📅 Appointment:</span>
                                        <span>{{ $booking->formatted_date }}</span>
                                        <span class="px-2 py-0.5 rounded bg-white text-indigo-800 border border-indigo-200 font-semibold">{{ $booking->booking_slot }}</span>
                                    </div>
                                    <div class="text-indigo-950">
                                        <span class="font-semibold text-gray-600">Location:</span> {{ $booking->service_address }}
                                    </div>
                                    @if($booking->notes)
                                        <div class="text-gray-700 italic">
                                            <span class="font-semibold not-italic">Notes:</span> "{{ $booking->notes }}"
                                        </div>
                                    @endif
                                    @if($booking->cancelled_reason)
                                        <div class="text-red-700">
                                            <span class="font-semibold">Cancelled Reason:</span> {{ $booking->cancelled_reason }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right: Customer Contact & Actions -->
                        <div class="lg:w-80 shrink-0 space-y-3 pt-3 lg:pt-0 lg:border-l lg:border-gray-100 lg:pl-5">
                            
                            <!-- Customer Contact Card -->
                            <div class="text-xs space-y-1">
                                <div class="font-bold text-gray-900">{{ $booking->customer_name }}</div>
                                <div class="text-gray-600 flex items-center gap-1">
                                    <span>📞</span>
                                    <a href="tel:{{ $booking->customer_phone }}" class="text-indigo-600 font-semibold hover:underline">
                                        {{ $booking->customer_phone }}
                                    </a>
                                </div>
                                @if($booking->customer_email)
                                    <div class="text-gray-500 text-[11px] truncate">✉️ {{ $booking->customer_email }}</div>
                                @endif
                                <div class="pt-1 flex items-center justify-between text-gray-900 font-bold">
                                    <span class="text-[11px] text-gray-500 uppercase font-semibold">Service Fee:</span>
                                    <span class="text-indigo-600 font-black text-sm">{{ $booking->formatted_amount }}</span>
                                </div>
                            </div>

                            <!-- WhatsApp Contact Button -->
                            <div>
                                <a href="{{ $waLink }}" target="_blank"
                                   class="w-full py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs">
                                    <span>💬</span>
                                    <span>WhatsApp Customer</span>
                                </a>
                            </div>

                            <!-- Lifecycle Actions -->
                            @if($booking->isPending())
                                <div class="grid grid-cols-2 gap-2 pt-1">
                                    <form action="{{ route('vendor.bookings.status', $booking->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="confirmed">
                                        <button type="submit" class="w-full py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-xs">
                                            ✓ Confirm Slot
                                        </button>
                                    </form>

                                    <form action="{{ route('vendor.bookings.status', $booking->id) }}" method="POST" onsubmit="return confirm('Cancel this appointment?');">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="cancelled">
                                        <input type="hidden" name="cancelled_reason" value="Unavailable at requested slot">
                                        <button type="submit" class="w-full py-2 px-3 rounded-xl bg-white hover:bg-red-50 text-red-600 border border-red-200 text-xs font-bold transition">
                                            ✕ Decline
                                        </button>
                                    </form>
                                </div>
                            @elseif($booking->isConfirmed())
                                <div class="grid grid-cols-2 gap-2 pt-1">
                                    <form action="{{ route('vendor.bookings.status', $booking->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="w-full py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs">
                                            ✓ Mark Completed
                                        </button>
                                    </form>

                                    <form action="{{ route('vendor.bookings.status', $booking->id) }}" method="POST" onsubmit="return confirm('Cancel this confirmed appointment?');">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="cancelled">
                                        <input type="hidden" name="cancelled_reason" value="Cancelled by service provider">
                                        <button type="submit" class="w-full py-2 px-3 rounded-xl bg-white hover:bg-red-50 text-red-600 border border-red-200 text-xs font-bold transition">
                                            ✕ Cancel
                                        </button>
                                    </form>
                                </div>
                            @endif

                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $bookings->links() }}
        </div>
    @endif
</x-layouts.vendor>
