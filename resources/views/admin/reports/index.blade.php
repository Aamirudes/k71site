<x-layouts.admin title="Trust & Moderation Queue">
    <div class="space-y-6">
        <!-- Header Banner -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                        Trust & Safety
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                        Community Reports
                    </span>
                </div>
                <h2 class="text-2xl font-serif font-bold text-slate-900 mt-2">Trust &amp; Moderation Queue</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Review flagged items, abusive content, counterfeit claims, and enforce marketplace safety policies.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3.5 py-2 rounded-2xl bg-rose-900 text-xs font-mono font-bold text-white shadow-xs">
                    {{ $counts['pending'] ?? 0 }} Pending Review
                </span>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 text-xs sm:text-sm flex items-center gap-3 shadow-xs">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filter Tabs -->
        <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
            @foreach(['all' => 'All Reports', 'pending' => 'Pending Moderation', 'resolved' => 'Resolved'] as $key => $label)
                <a href="{{ route('admin.reports.index', ['status' => $key]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $status === $key ? 'bg-slate-900 text-white shadow-xs' : 'bg-white hover:bg-slate-100 text-slate-600 border border-slate-200' }}">
                    <span>{{ $label }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === $key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                        {{ $counts[$key] ?? 0 }}
                    </span>
                </a>
            @endforeach
        </div>

        <!-- Reports Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            @if($reports->isEmpty())
                <div class="p-12 text-center">
                    <div class="text-5xl mb-3">🛡️</div>
                    <h3 class="text-base font-bold text-slate-900">No reports found</h3>
                    <p class="text-xs text-slate-500 mt-1">The moderation queue is currently clear.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/75 border-b border-slate-200/80 text-[11px] font-mono uppercase text-slate-500">
                            <tr>
                                <th class="py-3.5 px-4 font-semibold">ID / Date</th>
                                <th class="py-3.5 px-4 font-semibold">Reported Target</th>
                                <th class="py-3.5 px-4 font-semibold">Reason</th>
                                <th class="py-3.5 px-4 font-semibold">Reporter</th>
                                <th class="py-3.5 px-4 font-semibold">Status</th>
                                <th class="py-3.5 px-4 font-semibold text-right">Moderation Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            @foreach($reports as $report)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-4 px-4 font-mono text-slate-500">
                                        #{{ $report->id }}<br>
                                        <span class="text-[10px] text-slate-400">{{ $report->created_at->format('d M Y, h:i A') }}</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="font-bold text-slate-900">
                                            {{ class_basename($report->reportable_type) }} #{{ $report->reportable_id }}
                                        </div>
                                        @if($report->reportable instanceof \App\Models\Product)
                                            <a href="{{ route('product.show', $report->reportable->slug) }}" target="_blank" class="text-violet-600 hover:underline text-[11px]">
                                                {{ \Illuminate\Support\Str::limit($report->reportable->title, 35) }} ↗
                                            </a>
                                        @elseif($report->reportable instanceof \App\Models\Vendor)
                                            <span class="text-slate-600 text-[11px]">{{ $report->reportable->shop_name }}</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200 font-bold text-[10px] uppercase">
                                            {{ str_replace('_', ' ', $report->reason) }}
                                        </span>
                                        @if($report->details)
                                            <p class="text-[11px] text-slate-500 mt-1 max-w-xs">{{ $report->details }}</p>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="font-semibold text-slate-900">{{ $report->user->name ?? 'User' }}</span><br>
                                        <span class="text-[10px] text-slate-400">{{ $report->user->email ?? '' }}</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        @if($report->status === 'pending')
                                            <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200 font-bold text-[10px]">
                                                Pending Review
                                            </span>
                                        @elseif($report->status === 'action_taken')
                                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold text-[10px]">
                                                Action: {{ $report->action_taken }}
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200 font-bold text-[10px]">
                                                Dismissed
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        @if($report->isPending())
                                            <div class="flex items-center justify-end gap-1.5" x-data="{ showResolve: false }">
                                                <button type="button" @click="showResolve = !showResolve" class="px-3 py-1.5 rounded-xl bg-slate-900 text-white font-bold text-[11px] hover:bg-black transition">
                                                    Action ▾
                                                </button>

                                                <!-- Resolution Modal -->
                                                <div x-show="showResolve" @click.away="showResolve = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display: none;">
                                                    <div class="bg-white rounded-2xl p-6 max-w-md w-full text-left shadow-2xl border border-slate-200">
                                                        <h4 class="text-base font-bold text-slate-900 mb-1">Moderate Report #{{ $report->id }}</h4>
                                                        <p class="text-xs text-slate-500 mb-4">Select an enforcement action for {{ class_basename($report->reportable_type) }} #{{ $report->reportable_id }}.</p>

                                                        <form action="{{ route('admin.reports.resolve', $report->id) }}" method="POST" class="space-y-3">
                                                            @csrf
                                                            <div>
                                                                <label class="block text-xs font-bold text-slate-700 mb-1">Action</label>
                                                                <select name="action" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 focus:ring-2 focus:ring-slate-900">
                                                                    <option value="dismiss">Dismiss Report (No Violation)</option>
                                                                    <option value="block_product">Deactivate Product Listing</option>
                                                                    <option value="warn_vendor">Suspend Vendor Account</option>
                                                                    <option value="delete_review">Unpublish Review</option>
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label class="block text-xs font-bold text-slate-700 mb-1">Admin Notes</label>
                                                                <textarea name="admin_notes" rows="3" placeholder="Reasoning or notes for the audit trail..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 focus:ring-2 focus:ring-slate-900"></textarea>
                                                            </div>
                                                            <div class="flex items-center justify-end gap-2 pt-2">
                                                                <button type="button" @click="showResolve = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                                                                <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold">Submit Action</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-[11px] text-slate-400 font-mono">Resolved by {{ $report->resolver->first_name ?? 'Admin' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-200">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
