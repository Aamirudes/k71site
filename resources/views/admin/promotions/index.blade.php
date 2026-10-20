<x-layouts.admin title="Marketplace Promotions & Flash Campaigns">
    <div class="space-y-6">
        <!-- Header Banner -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                        Growth & Marketing
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-violet-50 text-violet-700 border border-violet-200">
                        Flash Sales
                    </span>
                </div>
                <h2 class="text-2xl font-serif font-bold text-slate-900 mt-2">Promotions &amp; Flash Campaigns</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Configure marketplace-wide discount events, seasonal sales, promotional badges, and flash offer timers.</p>
            </div>
            <div>
                <button type="button" @click="showCreateModal = true" class="px-5 py-2.5 rounded-2xl bg-slate-900 hover:bg-black text-white text-xs font-bold transition shadow-xs flex items-center gap-2">
                    <span>+ New Promotion</span>
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 text-xs sm:text-sm flex items-center gap-3 shadow-xs">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Promotions Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden" x-data="{ showCreateModal: false }">
            @if($promotions->isEmpty())
                <div class="p-12 text-center">
                    <div class="text-5xl mb-3">⚡</div>
                    <h3 class="text-base font-bold text-slate-900">No active promotions</h3>
                    <p class="text-xs text-slate-500 mt-1">Create your first marketplace flash sale or seasonal campaign above.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/75 border-b border-slate-200/80 text-[11px] font-mono uppercase text-slate-500">
                            <tr>
                                <th class="py-3.5 px-4 font-semibold">Campaign Name</th>
                                <th class="py-3.5 px-4 font-semibold">Badge</th>
                                <th class="py-3.5 px-4 font-semibold">Discount</th>
                                <th class="py-3.5 px-4 font-semibold">Duration Window</th>
                                <th class="py-3.5 px-4 font-semibold">Status</th>
                                <th class="py-3.5 px-4 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            @foreach($promotions as $promo)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-4 px-4">
                                        <div class="font-bold text-slate-900">{{ $promo->title }}</div>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $promo->slug }}</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        @if($promo->badge)
                                            <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200 font-bold text-[10px] uppercase font-mono">
                                                {{ $promo->badge }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-[10px]">—</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 font-mono font-bold text-slate-900">
                                        @if($promo->discount_type === 'percentage')
                                            {{ $promo->discount_value }}% OFF
                                        @else
                                            ₹{{ number_format($promo->discount_value / 100, 2) }} OFF
                                        @endif
                                        @if($promo->min_spend)
                                            <span class="block text-[10px] text-slate-400 font-normal font-sans">Min spend: ₹{{ number_format($promo->min_spend / 100) }}</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-slate-500 font-mono text-[11px]">
                                        {{ $promo->starts_at->format('d M') }} → {{ $promo->ends_at->format('d M Y') }}
                                        <span class="block text-[10px] text-slate-400 font-sans">
                                            {{ $promo->isLive() ? 'Live Now' : ($promo->starts_at->isFuture() ? 'Upcoming' : 'Ended') }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        @if($promo->is_active && $promo->isLive())
                                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold text-[10px]">
                                                ● Active & Live
                                            </span>
                                        @elseif($promo->is_active)
                                            <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200 font-bold text-[10px]">
                                                Scheduled
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200 font-bold text-[10px]">
                                                Disabled
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        <form action="{{ route('admin.promotions.toggle', $promo->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-[11px] font-semibold transition">
                                                {{ $promo->is_active ? 'Disable' : 'Enable' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-200">
                    {{ $promotions->links() }}
                </div>
            @endif

            <!-- Create Promotion Modal -->
            <div x-show="showCreateModal" @click.away="showCreateModal = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display: none;">
                <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full text-left shadow-2xl border border-slate-200">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                        <h3 class="text-lg font-bold text-slate-900">Launch New Promotion Campaign</h3>
                        <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-xl">×</button>
                    </div>

                    <form action="{{ route('admin.promotions.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Campaign Title *</label>
                            <input type="text" name="title" required placeholder="e.g. Diwali Super Savings 2026" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 focus:ring-2 focus:ring-slate-900">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Badge Tag</label>
                                <input type="text" name="badge" placeholder="e.g. FLASH50" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 focus:ring-2 focus:ring-slate-900 font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Discount Type</label>
                                <select name="discount_type" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 focus:ring-2 focus:ring-slate-900">
                                    <option value="percentage">Percentage (%)</option>
                                    <option value="flat">Flat Amount (₹)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Discount Value *</label>
                                <input type="number" step="1" min="1" name="discount_value" required placeholder="e.g. 20 (for 20%)" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 focus:ring-2 focus:ring-slate-900 font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Min Spend (₹)</label>
                                <input type="number" step="1" min="0" name="min_spend" placeholder="e.g. 1000" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 focus:ring-2 focus:ring-slate-900 font-mono">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Start Time *</label>
                                <input type="datetime-local" name="starts_at" required value="{{ now()->format('Y-m-d\TH:i') }}" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 focus:ring-2 focus:ring-slate-900">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">End Time *</label>
                                <input type="datetime-local" name="ends_at" required value="{{ now()->addDays(7)->format('Y-m-d\TH:i') }}" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 focus:ring-2 focus:ring-slate-900">
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold shadow-md">Create Campaign</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
