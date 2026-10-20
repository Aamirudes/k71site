<x-layouts.admin title="Settlements & Bank Reconciliation">
    <div x-data="{ 
        helpModalOpen: false,
        activeHelpTab: 'timelines',
        sidebarOpen: false,
        selectedSettlement: null,
        copiedUtr: null,
        copyToClipboard(text, id) {
            navigator.clipboard.writeText(text).then(() => {
                this.copiedUtr = id;
                setTimeout(() => { this.copiedUtr = null; }, 2000);
            });
        },
        openDetails(settlement) {
            this.selectedSettlement = settlement;
            this.sidebarOpen = true;
        }
    }" class="space-y-6">

        <!-- ========================================================================= -->
        <!-- 1. PAGE HEADER -->
        <!-- ========================================================================= -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200/80">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-500">PhonePe Business Banking</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-slate-900 tracking-tight mt-1">
                    Settlements
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Consolidated payouts of your transactions directly to your registered bank account for automated reconciliation.
                </p>
            </div>

            <!-- Quick Action Links -->
            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.orders') }}" 
                   class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 hover:text-slate-900 transition-all shadow-2xs flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span>View All Orders</span>
                </a>
                <button type="button" 
                        @click="helpModalOpen = true"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-amber-950 bg-amber-400 hover:bg-amber-300 transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Settlement Help</span>
                </button>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 2. "HAVEN'T RECEIVED SETTLEMENTS? GET HELP" BANNER -->
        <!-- ========================================================================= -->
        <div class="rounded-2xl bg-gradient-to-r from-amber-50 via-amber-100/60 to-orange-50 border border-amber-200/90 p-4 sm:p-5 shadow-2xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-400 text-amber-950 flex items-center justify-center shrink-0 shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span>Haven’t received settlements? Get Help</span>
                        <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-amber-200/80 text-amber-900 border border-amber-300">T+1 Cycle</span>
                    </h3>
                    <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                        PhonePe processes settlements on all banking weekdays by ~11:00 AM. Weekend and holiday collections are consolidated on the following business day.
                    </p>
                </div>
            </div>

            <button type="button" 
                    @click="helpModalOpen = true"
                    class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold transition-all shadow-xs shrink-0 whitespace-nowrap cursor-pointer flex items-center gap-1.5 self-end sm:self-center">
                <span>Get Help</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <!-- ========================================================================= -->
        <!-- 3. LAST SETTLEMENT SUMMARY CARD -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs relative overflow-hidden">
            <div class="absolute -right-8 -bottom-8 w-44 h-44 rounded-full bg-emerald-500/5 pointer-events-none blur-2xl"></div>
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <!-- Left Details -->
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-500">Last Settlement Summary</span>
                    </div>

                    @if($lastSettlement)
                        <div class="flex flex-wrap items-baseline gap-3">
                            <span class="text-3xl sm:text-4xl font-serif font-bold text-slate-900 tracking-tight">
                                {{ $lastSettlement['amount'] }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-300">
                                <span>✓</span> {{ $lastSettlement['status'] }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-600 flex flex-wrap items-center gap-y-1 gap-x-2">
                            <span>Successfully credited on:</span>
                            <strong class="text-slate-900 font-mono">{{ $lastSettlement['datetime'] }}</strong>
                            <span>•</span>
                            <span>Ref UTR:</span>
                            <span class="font-mono font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">{{ $lastSettlement['utr'] }}</span>
                        </p>
                    @else
                        <div class="text-2xl font-serif font-bold text-slate-400">
                            No Previous Settlements
                        </div>
                        <p class="text-xs text-slate-500">
                            Settlements will appear here once customer transactions complete and settle via PhonePe.
                        </p>
                    @endif
                </div>

                <!-- Right Key Bank Account Meta -->
                <div class="bg-slate-50 rounded-xl border border-slate-200/80 p-4 shrink-0 flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center justify-center text-slate-700 font-bold text-sm">
                        🏦
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block font-mono">Designated Bank Account</span>
                        <h4 class="text-xs font-bold text-slate-800">HDFC Bank Current Account</h4>
                        <p class="text-[11px] font-mono text-slate-500">A/C: ••••••••{{ substr(config('services.phonepe.merchant_id', '4290'), -4) }} • Mode: Auto-Settle</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 4. MONTHLY NAVIGATION BAR -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-3 sm:p-4 shadow-xs flex items-center justify-between gap-3">
            <!-- Previous Month Button -->
            <a href="?month={{ $prevMonth->format('Y-m') }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:text-slate-950 bg-slate-100 hover:bg-slate-200 transition-all flex items-center gap-1.5 shadow-2xs group"
               title="View {{ $prevMonth->format('F Y') }}">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                <span class="hidden sm:inline">Previous Month</span>
            </a>

            <!-- Current Selected Month Display -->
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <h2 class="text-base sm:text-lg font-serif font-bold text-slate-900">
                    {{ $currentMonth->format('F Y') }}
                </h2>
                @if($selectedMonthStr === now()->format('Y-m'))
                    <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">Current</span>
                @else
                    <a href="?month={{ now()->format('Y-m') }}" class="text-[11px] text-amber-700 font-bold hover:underline ml-1">
                        (Jump to Today)
                    </a>
                @endif
            </div>

            <!-- Next Month Button -->
            @if($isFuture)
                <span class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-300 bg-slate-50 cursor-not-allowed flex items-center gap-1.5" title="Future months unavailable">
                    <span class="hidden sm:inline">Next Month</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @else
                <a href="?month={{ $nextMonth->format('Y-m') }}" 
                   class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:text-slate-950 bg-slate-100 hover:bg-slate-200 transition-all flex items-center gap-1.5 shadow-2xs group"
                   title="View {{ $nextMonth->format('F Y') }}">
                    <span class="hidden sm:inline">Next Month</span>
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endif
        </div>

        <!-- ========================================================================= -->
        <!-- 5. SETTLEMENT LIST TABLE -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200/80 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">
                        Settlements for {{ $currentMonth->format('F Y') }}
                    </h3>
                    <p class="text-xs text-slate-500">
                        Showing {{ count($settlements) }} consolidated settlement {{ count($settlements) === 1 ? 'payout' : 'payouts' }}. Click on any row to open the financial breakdown sidebar.
                    </p>
                </div>
                <span class="text-xs font-mono text-slate-400">
                    Live Auto-Settle
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-mono uppercase tracking-wider text-slate-500 font-semibold">
                            <th class="py-3 px-4">Initiated At</th>
                            <th class="py-3 px-4">Last Updated At</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">UTR Number</th>
                            <th class="py-3 px-4">Mode</th>
                            <th class="py-3 px-4 text-right">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-sans">
                        @forelse($settlements as $s)
                            <tr @click="openDetails({{ json_encode($s) }})" 
                                class="hover:bg-amber-50/40 transition-colors cursor-pointer group">
                                
                                <!-- Initiated At -->
                                <td class="py-3.5 px-4 whitespace-nowrap text-slate-700 font-medium font-mono text-[11px]">
                                    {{ $s['initiated_at'] }}
                                </td>

                                <!-- Last Updated At -->
                                <td class="py-3.5 px-4 whitespace-nowrap text-slate-500 font-mono text-[11px]">
                                    {{ $s['last_updated_at'] }}
                                </td>

                                <!-- Amount -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-slate-900 group-hover:text-amber-800 transition-colors">
                                        ₹{{ $s['amount_rupees'] }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 block font-mono">
                                        {{ $s['orders_count'] }} {{ $s['orders_count'] === 1 ? 'order' : 'orders' }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($s['status'] === 'SUCCESS')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            SUCCESS
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold border border-amber-200 animate-pulse">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                            PROCESSING
                                        </span>
                                    @endif
                                </td>

                                <!-- UTR with 1-Click Copy -->
                                <td class="py-3.5 px-4 whitespace-nowrap" @click.stop>
                                    <div class="inline-flex items-center gap-1.5 bg-slate-100 border border-slate-200/80 px-2.5 py-1 rounded-lg">
                                        <span class="font-mono text-xs font-semibold text-slate-800">{{ $s['utr'] }}</span>
                                        <button type="button" 
                                                @click="copyToClipboard('{{ $s['utr'] }}', '{{ $s['id'] }}')" 
                                                class="text-slate-400 hover:text-slate-800 p-0.5 rounded transition-colors"
                                                title="Copy UTR number">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </button>
                                        <span x-show="copiedUtr === '{{ $s['id'] }}'" 
                                              x-transition 
                                              class="text-[10px] font-bold text-emerald-600 ml-0.5">
                                            Copied!
                                        </span>
                                    </div>
                                </td>

                                <!-- Mode of Settlement -->
                                <td class="py-3.5 px-4 whitespace-nowrap text-slate-600 text-xs">
                                    <span class="inline-flex items-center gap-1">
                                        <span>⚡</span>
                                        <span>{{ $s['mode'] }}</span>
                                    </span>
                                </td>

                                <!-- Action / Trigger Details -->
                                <td class="py-3.5 px-4 whitespace-nowrap text-right">
                                    <button type="button" 
                                            class="text-xs font-bold text-amber-700 hover:text-amber-900 group-hover:underline inline-flex items-center gap-1">
                                        <span>Breakdown</span>
                                        <span>→</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-16 px-4 text-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-800">No Settlements Found for {{ $currentMonth->format('F Y') }}</h4>
                                    <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-4">
                                        No transactions were scheduled or settled in this calendar month. You can jump back to current transactions anytime.
                                    </p>
                                    <a href="?month={{ now()->format('Y-m') }}" 
                                       class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-black transition-colors inline-block">
                                        View Current Month
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Table Footer: Previous / Next Month Navigation -->
            <div class="px-5 py-3.5 bg-slate-50/80 border-t border-slate-200/80 flex items-center justify-between text-xs">
                <a href="?month={{ $prevMonth->format('Y-m') }}" 
                   class="font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1">
                    <span>← Previous Month ({{ $prevMonth->format('M Y') }})</span>
                </a>
                
                @if(!$isFuture)
                    <a href="?month={{ $nextMonth->format('Y-m') }}" 
                       class="font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1">
                        <span>Next Month ({{ $nextMonth->format('M Y') }}) →</span>
                    </a>
                @else
                    <span class="text-slate-400">Next Month Unavailable</span>
                @endif
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 6. GET HELP MODAL (TIMELINES, REASONS FOR DELAY & BANK HOLIDAYS) -->
        <!-- ========================================================================= -->
        <div x-show="helpModalOpen" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            
            <!-- Backdrop -->
            <div x-show="helpModalOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                 @click="helpModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="helpModalOpen" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200">
                    
                    <!-- Modal Header -->
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-sm">
                                ℹ️
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900" id="modal-title">
                                    Settlement Schedule &amp; Help Desk
                                </h3>
                                <p class="text-xs text-slate-500">PhonePe Business settlement guidelines and bank holiday policies</p>
                            </div>
                        </div>
                        <button type="button" @click="helpModalOpen = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Nav Tabs -->
                    <div class="px-6 pt-3 border-b border-slate-100 flex gap-4 text-xs font-bold">
                        <button type="button" 
                                @click="activeHelpTab = 'timelines'"
                                :class="activeHelpTab === 'timelines' ? 'text-amber-800 border-b-2 border-amber-600 pb-2.5' : 'text-slate-500 hover:text-slate-800 pb-2.5'">
                            Settlement Cycles &amp; Delays
                        </button>
                        <button type="button" 
                                @click="activeHelpTab = 'holidays'"
                                :class="activeHelpTab === 'holidays' ? 'text-amber-800 border-b-2 border-amber-600 pb-2.5' : 'text-slate-500 hover:text-slate-800 pb-2.5'">
                            View 2026 Bank Holidays ({{ count($bankHolidays) }})
                        </button>
                    </div>

                    <!-- Modal Content -->
                    <div class="p-6 text-xs text-slate-600 space-y-4 max-h-[60vh] overflow-y-auto">
                        
                        <!-- TAB 1: TIMELINES & DELAYS -->
                        <div x-show="activeHelpTab === 'timelines'" class="space-y-4">
                            <!-- Reason for Delay -->
                            <div class="rounded-2xl bg-amber-50/70 border border-amber-200/80 p-4">
                                <h4 class="text-xs font-bold text-amber-950 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                    <span>⏱️</span>
                                    <span>Reason for Delay</span>
                                </h4>
                                <p class="leading-relaxed text-amber-900">
                                    Settlements are processed automatically on <strong>all weekdays</strong>, except for national bank holidays and weekends. PhonePe batches the previous day’s transactions at 11:59 PM and releases them to your registered bank account via NEFT/IMPS by ~11:00 AM on the next business day.
                                </p>
                            </div>

                            <!-- Weekend & Holiday Processing -->
                            <div class="rounded-2xl bg-slate-50 border border-slate-200/80 p-4">
                                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                    <span>🗓️</span>
                                    <span>Weekend / Holiday Processing Rule</span>
                                </h4>
                                <p class="leading-relaxed text-slate-700">
                                    Payments received on <strong>Friday, Saturday, and Sunday</strong> are consolidated and settled on the following <strong>Monday</strong> (or Tuesday if Monday happens to be a bank holiday).
                                </p>
                                <ul class="mt-2 space-y-1 text-slate-600 list-disc list-inside">
                                    <li>Friday orders settle on Monday by 11:00 AM</li>
                                    <li>Saturday &amp; Sunday orders settle on Monday by 11:00 AM</li>
                                    <li>2nd and 4th Saturdays of every month are non-banking days</li>
                                </ul>
                            </div>

                            <!-- View Bank Holidays Trigger Link -->
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-100/80 text-xs">
                                <span>Want to check if today or upcoming days are non-banking days?</span>
                                <button type="button" 
                                        @click="activeHelpTab = 'holidays'" 
                                        class="text-amber-800 font-bold underline hover:text-amber-950">
                                    View Bank Holidays Calendar →
                                </button>
                            </div>
                        </div>

                        <!-- TAB 2: BANK HOLIDAYS LIST -->
                        <div x-show="activeHelpTab === 'holidays'" class="space-y-3">
                            <p class="text-slate-500 text-[11px]">
                                Indian Banking Holidays (RTGS/NEFT settlement cutoffs as per RBI schedule):
                            </p>
                            
                            <div class="border border-slate-200 rounded-xl overflow-hidden">
                                <table class="w-full text-left text-[11px]">
                                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-mono font-semibold">
                                        <tr>
                                            <th class="py-2 px-3">Date</th>
                                            <th class="py-2 px-3">Day</th>
                                            <th class="py-2 px-3">Occasion</th>
                                            <th class="py-2 px-3">Type</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($bankHolidays as $h)
                                            <tr class="hover:bg-slate-50/60">
                                                <td class="py-2 px-3 font-mono font-bold text-slate-800">{{ $h['date'] }}</td>
                                                <td class="py-2 px-3 text-slate-600">{{ $h['day'] }}</td>
                                                <td class="py-2 px-3 font-medium text-slate-900">{{ $h['occasion'] }}</td>
                                                <td class="py-2 px-3 text-[10px] text-slate-500">{{ $h['type'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                        <a href="mailto:crab.hashir@gmail.com?subject=Settlement%20Query%20-%20K71.in" 
                           class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 transition-colors flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>Need more help?</span>
                        </a>

                        <button type="button" 
                                @click="helpModalOpen = false" 
                                class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold transition-all shadow-xs cursor-pointer">
                            Done
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 7. SETTLEMENT DETAILS SIDEBAR (SLIDE-OVER DRAWER) -->
        <!-- ========================================================================= -->
        <div x-show="sidebarOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-hidden" 
             style="display: none;"
             aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
            
            <!-- Backdrop -->
            <div x-show="sidebarOpen" 
                 x-transition:enter="ease-in-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in-out duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                 @click="sidebarOpen = false"></div>

            <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
                <div x-show="sidebarOpen" 
                     x-transition:enter="transform transition ease-in-out duration-300"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-300"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full"
                     class="w-screen max-w-md bg-white shadow-2xl border-l border-slate-200 flex flex-col">
                    
                    <!-- Sidebar Header -->
                    <div class="px-6 py-5 bg-slate-900 text-white flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-mono uppercase tracking-wider text-amber-400 font-bold">Settlement Breakdown</span>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/30" x-text="selectedSettlement?.status"></span>
                            </div>
                            <h3 class="text-base font-bold text-white font-mono mt-0.5" x-text="selectedSettlement?.id"></h3>
                        </div>
                        <button type="button" @click="sidebarOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Sidebar Body -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-6 text-xs text-slate-700">
                        
                        <!-- Consolidated Amount Card -->
                        <div class="rounded-2xl bg-slate-50 border border-slate-200 p-5 space-y-3">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 font-mono block">Net Credited Payout</span>
                            <div class="flex items-baseline justify-between">
                                <span class="text-3xl font-serif font-bold text-slate-900">
                                    ₹<span x-text="selectedSettlement?.amount_rupees"></span>
                                </span>
                                <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                    Bank Confirmed
                                </span>
                            </div>

                            <div class="pt-3 border-t border-slate-200/80 space-y-1.5 text-[11px]">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Bank Account:</span>
                                    <span class="font-bold text-slate-800" x-text="selectedSettlement?.bank_account"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Initiated:</span>
                                    <span class="font-mono text-slate-700" x-text="selectedSettlement?.initiated_at"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Credited (Updated):</span>
                                    <span class="font-mono text-slate-700" x-text="selectedSettlement?.last_updated_at"></span>
                                </div>
                                <div class="flex justify-between items-center pt-1">
                                    <span class="text-slate-500">Bank UTR:</span>
                                    <div class="flex items-center gap-1.5 bg-white border border-slate-200 px-2 py-0.5 rounded">
                                        <span class="font-mono font-bold text-slate-900" x-text="selectedSettlement?.utr"></span>
                                        <button type="button" 
                                                @click="copyToClipboard(selectedSettlement?.utr, 'drawer')"
                                                class="text-slate-400 hover:text-slate-900"
                                                title="Copy UTR">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Underlying Financial Breakdown -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 font-mono flex items-center gap-1.5">
                                <span>📊</span>
                                <span>Financial Reconciliation Breakdown</span>
                            </h4>
                            
                            <div class="border border-slate-200 rounded-xl overflow-hidden divide-y divide-slate-100 text-xs">
                                <div class="p-3 bg-slate-50/50 flex justify-between">
                                    <span class="font-medium text-slate-600">Gross Transaction Volume (<span x-text="selectedSettlement?.orders_count"></span> Orders)</span>
                                    <span class="font-bold text-slate-900">₹<span x-text="selectedSettlement?.gross_rupees"></span></span>
                                </div>
                                <div class="p-3 flex justify-between">
                                    <span class="text-slate-500">PhonePe Gateway MDR (1.9%)</span>
                                    <span class="text-rose-600 font-mono">-₹<span x-text="selectedSettlement?.gateway_fee_rupees"></span></span>
                                </div>
                                <div class="p-3 flex justify-between">
                                    <span class="text-slate-500">GST on Gateway Fee (18%)</span>
                                    <span class="text-rose-600 font-mono">-₹<span x-text="selectedSettlement?.gst_on_fee_rupees"></span></span>
                                </div>
                                <div class="p-3 bg-slate-50/80 flex justify-between font-bold">
                                    <span class="text-slate-800">Net Amount Settled to Bank</span>
                                    <span class="text-emerald-700">₹<span x-text="selectedSettlement?.amount_rupees"></span></span>
                                </div>
                                <div class="p-3 flex justify-between border-t-2 border-slate-200">
                                    <span class="text-slate-600">K71 Platform Commission (5%)</span>
                                    <span class="font-bold text-amber-800">₹<span x-text="selectedSettlement?.platform_profit_rupees"></span></span>
                                </div>
                                <div class="p-3 flex justify-between">
                                    <span class="text-slate-600">Boutique Vendor Payouts</span>
                                    <span class="font-bold text-indigo-800">₹<span x-text="selectedSettlement?.vendor_disbursed_rupees"></span></span>
                                </div>
                            </div>
                        </div>

                        <!-- Associated Orders Table -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 font-mono flex items-center gap-1.5">
                                <span>📦</span>
                                <span>Included Orders (<span x-text="selectedSettlement?.orders_count"></span>)</span>
                            </h4>

                            <div class="space-y-2">
                                <template x-for="ord in selectedSettlement?.orders" :key="ord.id">
                                    <div class="p-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50/80 transition-colors">
                                        <div class="flex items-center justify-between mb-1">
                                            <a :href="'/admin/orders/' + ord.id" 
                                               target="_blank"
                                               class="font-mono font-bold text-amber-800 hover:underline" 
                                               x-text="ord.order_number"></a>
                                            <span class="font-bold text-slate-900" x-text="'₹' + ord.total_amount"></span>
                                        </div>
                                        <div class="flex items-center justify-between text-[11px] text-slate-500">
                                            <span x-text="ord.shop_name"></span>
                                            <span class="text-slate-600 font-mono" x-text="'Vendor Payout: ₹' + ord.vendor_payout"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar Footer -->
                    <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center gap-2">
                        <button type="button" 
                                @click="window.print()" 
                                class="flex-1 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 text-xs font-bold transition-colors text-center shadow-2xs">
                            Print Summary
                        </button>
                        <button type="button" 
                                @click="sidebarOpen = false" 
                                class="flex-1 py-2 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold transition-colors text-center shadow-xs">
                            Close Drawer
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</x-layouts.admin>
