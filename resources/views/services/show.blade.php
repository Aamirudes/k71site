<x-layouts.public 
    :title="'Booking #' . $booking->booking_number . ' — Appointment Details | K71'"
    :metaDescription="'View appointment confirmation and status for booking #' . $booking->booking_number . ' on K71 Services.'"
>
    <div class="max-w-3xl mx-auto py-6">

        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="rounded-3xl bg-white border border-zinc-200/80 p-6 sm:p-8 shadow-xs space-y-6">
            
            <!-- Header & Status Pill -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-zinc-100">
                <div>
                    <div class="text-xs uppercase tracking-wider text-zinc-500 font-semibold mb-1">Appointment Confirmation</div>
                    <h1 class="text-2xl font-bold text-zinc-950">#{{ $booking->booking_number }}</h1>
                </div>

                <div>
                    @if($booking->isPending())
                        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold uppercase tracking-wide">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Awaiting Confirmation
                        </span>
                    @elseif($booking->isConfirmed())
                        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold uppercase tracking-wide">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Confirmed &amp; Scheduled
                        </span>
                    @elseif($booking->isCompleted())
                        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-800 text-xs font-semibold uppercase tracking-wide">
                            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Service Completed
                        </span>
                    @elseif($booking->isCancelled())
                        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold uppercase tracking-wide">
                            Cancelled
                        </span>
                    @endif
                </div>
            </div>

            <!-- Scheduled Slot Card -->
            <div class="p-5 rounded-2xl bg-indigo-50/50 border border-indigo-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white border border-indigo-200 text-indigo-600 flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-[11px] text-indigo-800 uppercase font-bold tracking-wider">Scheduled Appointment</div>
                        <div class="text-lg font-bold text-zinc-950">{{ $booking->formatted_date }}</div>
                        <div class="text-xs text-zinc-600 font-medium">Slot: {{ $booking->booking_slot }}</div>
                    </div>
                </div>

                <div class="sm:text-right">
                    <div class="text-[11px] text-zinc-500 uppercase font-semibold">Service Fee</div>
                    <div class="text-xl font-bold text-zinc-950">{{ $booking->formatted_amount }}</div>
                    <div class="text-[10px] text-zinc-500">Pay at Doorstep / Post Service</div>
                </div>
            </div>

            <!-- Service Details -->
            <div class="space-y-3">
                <h3 class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Service Information</h3>
                <div class="p-4 rounded-2xl bg-zinc-50 border border-zinc-200 flex items-center gap-4">
                    @if($booking->product->images->isNotEmpty())
                        <img src="{{ asset('storage/' . $booking->product->images->first()->path) }}" alt="{{ $booking->product->title }}" class="w-16 h-16 rounded-xl object-cover shrink-0">
                    @else
                        <div class="w-16 h-16 rounded-xl bg-white border border-zinc-200 flex items-center justify-center text-zinc-400 shrink-0">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="flex-1">
                        <div class="text-xs text-indigo-600 font-semibold">{{ $booking->product->category?->name ?? 'Professional Service' }}</div>
                        <h4 class="text-base font-bold text-zinc-950">{{ $booking->product->title }}</h4>
                        <p class="text-xs text-zinc-500 line-clamp-1 mt-0.5">{{ $booking->product->description }}</p>
                    </div>
                </div>
            </div>

            <!-- Address & Customer Contact -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-zinc-50 border border-zinc-200 space-y-2">
                    <div class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Service Address</div>
                    <div class="text-sm text-zinc-800 font-medium leading-relaxed">
                        {{ $booking->service_address }}
                    </div>
                    @if($booking->notes)
                        <div class="text-xs text-zinc-500 pt-2 border-t border-zinc-200">
                            <span class="font-bold text-zinc-700">Instructions:</span> {{ $booking->notes }}
                        </div>
                    @endif
                </div>

                <div class="p-4 rounded-2xl bg-zinc-50 border border-zinc-200 space-y-2">
                    <div class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Customer Details</div>
                    <div class="text-sm text-zinc-800 font-medium">{{ $booking->customer_name }}</div>
                    <div class="text-xs text-zinc-600">Phone: {{ $booking->customer_phone }}</div>
                    @if($booking->customer_email)
                        <div class="text-xs text-zinc-600">Email: {{ $booking->customer_email }}</div>
                    @endif
                </div>
            </div>

            @if($booking->cancelled_reason)
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                    <span class="font-bold">Cancellation Reason:</span> {{ $booking->cancelled_reason }}
                </div>
            @endif

            <!-- Provider Contact Card & WhatsApp Action -->
            <div class="pt-6 border-t border-zinc-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500">Assigned Professional</div>
                        <div class="text-sm font-bold text-zinc-950">{{ $booking->vendor->shop_name }}</div>
                        <div class="text-[11px] text-zinc-500">{{ $booking->vendor->city ?? 'Local Provider' }}</div>
                    </div>
                </div>

                @php
                    $cleanPhone = preg_replace('/[^0-9]/', '', $booking->vendor->phone ?? $booking->vendor->contact_phone ?? '919876543210');
                    if (strlen($cleanPhone) === 10) { $cleanPhone = '91' . $cleanPhone; }
                    $msg = "Hi {$booking->vendor->shop_name}, I have an appointment booked (Booking #{$booking->booking_number}) for {$booking->product->title} on {$booking->formatted_date} at slot {$booking->booking_slot}.";
                    $waLink = "https://wa.me/{$cleanPhone}?text=" . urlencode($msg);
                @endphp
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer"
                       class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition shadow-xs active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        <span>Message Professional</span>
                    </a>
                </div>
            </div>

        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('services.index') }}" class="text-xs text-zinc-500 hover:text-zinc-800 transition font-medium">
                ← Return to Services Directory
            </a>
        </div>

    </div>
</x-layouts.public>
