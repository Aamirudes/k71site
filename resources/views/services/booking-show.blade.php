<x-layouts.public 
    :title="'Booking #' . $booking->booking_number . ' — Appointment Details | K71'"
    :metaDescription="'View appointment confirmation and status for booking #' . $booking->booking_number . ' on K71 Services.'"
    :fullWidth="true"
>
    <div class="w-full selection:bg-[#ECD2F3] selection:text-[#1A1018]">

        <!-- OBSIDIAN BOOKING HEADER -->
        <section class="grail-hero-band py-6 sm:py-8 px-4 sm:px-6 lg:px-8 border-b border-white/10">
            <div class="max-w-3xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="grail-eyebrow block mb-1">Appointment Confirmation</span>
                    <h1 class="text-2xl sm:text-4xl font-light text-white" style="font-family: var(--font-grail-display);">#{{ $booking->booking_number }}</h1>
                </div>

                <div>
                    @if($booking->isPending())
                        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/15 border border-amber-400/30 text-amber-200 text-xs font-semibold uppercase tracking-wide">
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                            Awaiting Confirmation
                        </span>
                    @elseif($booking->isConfirmed())
                        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 text-xs font-semibold uppercase tracking-wide">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            Confirmed &amp; Scheduled
                        </span>
                    @elseif($booking->isCompleted())
                        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#ECD2F3] text-[#1A1018] text-xs font-bold uppercase tracking-wide">
                            <svg class="w-3.5 h-3.5 text-[#1A1018]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Service Completed
                        </span>
                    @elseif($booking->isCancelled())
                        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-rose-500/15 border border-rose-400/30 text-rose-200 text-xs font-semibold uppercase tracking-wide">
                            Cancelled
                        </span>
                    @endif
                </div>
            </div>
        </section>

        <!-- CURVED EDITORIAL SHEET -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 sm:py-10 pb-20 px-4 sm:px-6 lg:px-8 shadow-[0_-18px_48px_rgba(0,0,0,0.45)]">
            <div class="max-w-3xl mx-auto">

                @if(session('success'))
                    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <div class="grail-card rounded-3xl bg-white border border-[#E6E2E8] p-6 sm:p-8 shadow-xs space-y-6">

                    <!-- Scheduled Slot Card -->
                    <div class="p-5 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-[#1A1018] text-[#ECD2F3] flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="grail-eyebrow !text-[#623562]">Scheduled Appointment</div>
                                <div class="text-lg font-bold text-[#1A1018]">{{ $booking->formatted_date }}</div>
                                <div class="text-xs text-[#1A1018]/70 font-medium">Slot: {{ $booking->booking_slot }}</div>
                            </div>
                        </div>

                        <div class="sm:text-right">
                            <div class="text-[11px] text-[#1A1018]/55 uppercase font-semibold">Service Fee</div>
                            <div class="text-xl font-bold text-[#1A1018]">{{ $booking->formatted_amount }}</div>
                            <div class="text-[10px] text-[#1A1018]/55">Pay at Doorstep / Post Service</div>
                        </div>
                    </div>

                    <!-- Service Details -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold text-[#1A1018]/55 uppercase tracking-[0.14em]">Service Information</h3>
                        <div class="p-4 rounded-2xl bg-[#FAFAF8] border border-[#E6E2E8] flex items-center gap-4">
                            @if($booking->product && $booking->product->images->isNotEmpty())
                                <img src="{{ format_image_url($booking->product->images->first()->path) }}" alt="{{ $booking->product->title }}" class="w-16 h-16 rounded-xl object-cover shrink-0">
                            @else
                                <div class="w-16 h-16 rounded-xl bg-white border border-[#E6E2E8] flex items-center justify-center text-[#1A1018]/40 shrink-0">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="flex-1">
                                <div class="grail-eyebrow !text-[#623562]">{{ $booking->product?->category?->name ?? 'Professional Service' }}</div>
                                <h4 class="text-base font-bold text-[#1A1018]">{{ $booking->product?->title ?? 'Service Appointment' }}</h4>
                                <p class="text-xs text-[#1A1018]/60 line-clamp-1 mt-0.5">{{ $booking->product?->description ?? '' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Address & Customer Contact -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl bg-[#FAFAF8] border border-[#E6E2E8] space-y-2">
                            <div class="text-xs font-bold text-[#1A1018]/55 uppercase tracking-[0.12em]">Service Address</div>
                            <div class="text-sm text-[#1A1018] font-medium leading-relaxed">
                                @if($canViewSensitiveContact ?? true)
                                    {{ $booking->service_address }}
                                @else
                                    {{ \Illuminate\Support\Str::limit($booking->service_address, 14, '•••• (Hidden for privacy)') }}
                                @endif
                            </div>
                            @if($booking->notes)
                                <div class="text-xs text-[#1A1018]/65 pt-2 border-t border-[#E6E2E8]">
                                    <span class="font-bold text-[#1A1018]">Instructions:</span> {{ $booking->notes }}
                                </div>
                            @endif
                        </div>

                        <div class="p-4 rounded-2xl bg-[#FAFAF8] border border-[#E6E2E8] space-y-2">
                            <div class="text-xs font-bold text-[#1A1018]/55 uppercase tracking-[0.12em]">Customer Details</div>
                            <div class="text-sm text-[#1A1018] font-medium">{{ $booking->customer_name }}</div>
                            @if($canViewSensitiveContact ?? true)
                                <div class="text-xs text-[#1A1018]/70">Phone: {{ $booking->customer_phone }}</div>
                                @if($booking->customer_email)
                                    <div class="text-xs text-[#1A1018]/70">Email: {{ $booking->customer_email }}</div>
                                @endif
                            @else
                                <div class="text-xs text-[#1A1018]/60">Contact details hidden — sign in with the booking account to view full contact info.</div>
                            @endif
                        </div>
                    </div>

                    @if($booking->cancelled_reason)
                        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                            <span class="font-bold">Cancellation Reason:</span> {{ $booking->cancelled_reason }}
                        </div>
                    @endif

                    <!-- Provider Contact Card & WhatsApp Action -->
                    <div class="pt-6 border-t border-[#E6E2E8] flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#F8EFFA] border border-[#ECD2F3] text-[#623562] flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-[#1A1018]/55">Assigned Professional</div>
                                <div class="text-sm font-bold text-[#1A1018]">{{ $booking->vendor?->shop_name ?? 'Service Provider' }}</div>
                                <div class="text-[11px] text-[#1A1018]/55">{{ $booking->vendor?->city ?? 'Local Provider' }}</div>
                            </div>
                        </div>

                        @php
                            $cleanPhone = preg_replace('/[^0-9]/', '', $booking->vendor?->phone ?? $booking->vendor?->contact_phone ?? '919876543210');
                            if (strlen($cleanPhone) === 10) { $cleanPhone = '91' . $cleanPhone; }
                            $vendorName = $booking->vendor?->shop_name ?? 'Provider';
                            $serviceTitle = $booking->product?->title ?? 'Service';
                            $msg = "Hi {$vendorName}, I have an appointment booked (Booking #{$booking->booking_number}) for {$serviceTitle} on {$booking->formatted_date} at slot {$booking->booking_slot}.";
                            $waLink = "https://wa.me/{$cleanPhone}?text=" . urlencode($msg);
                        @endphp
                        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                            @if(($canViewSensitiveContact ?? false) && in_array($booking->status, ['pending', 'confirmed'], true))
                                <form action="{{ route('bookings.cancel', $booking->booking_number) }}" method="POST" onsubmit="return confirm('Cancel this appointment booking?');">
                                    @csrf
                                    <button type="submit"
                                            class="px-4 py-2.5 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold transition active:scale-95">
                                        Cancel Appointment
                                    </button>
                                </form>
                            @endif
                            <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer"
                               class="w-full sm:w-auto px-5 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition shadow-xs active:scale-95 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                <span>Message Professional</span>
                            </a>
                        </div>
                    </div>

                </div>

                <div class="mt-8 text-center">
                    <a href="{{ route('services.index') }}" class="text-xs text-[#623562] hover:text-[#1A1018] transition font-bold">
                        ← Return to Services Directory
                    </a>
                </div>

            </div>
        </div>

    </div>
</x-layouts.public>
