<x-layouts.admin title="System Audit Logs & Security Trail">
    <div class="space-y-6">
        <!-- Header Banner -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                        Compliance & Audit
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Immutable Ledger
                    </span>
                </div>
                <h2 class="text-2xl font-serif font-bold text-slate-900 mt-2">System Audit Logs</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Full audit trail of mutations across products, orders, settings, and vendor actions.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3.5 py-2 rounded-2xl bg-slate-900 text-xs font-mono font-bold text-white shadow-xs">
                    {{ $logs->total() }} Log Entries
                </span>
            </div>
        </div>

        <!-- Audit Logs Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            @if($logs->isEmpty())
                <div class="p-12 text-center">
                    <div class="text-5xl mb-3">📜</div>
                    <h3 class="text-base font-bold text-slate-900">No audit logs recorded yet</h3>
                    <p class="text-xs text-slate-500 mt-1">Audit log records will be captured automatically during operations.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/75 border-b border-slate-200/80 text-[11px] font-mono uppercase text-slate-500">
                            <tr>
                                <th class="py-3.5 px-4 font-semibold">Timestamp</th>
                                <th class="py-3.5 px-4 font-semibold">User</th>
                                <th class="py-3.5 px-4 font-semibold">Action</th>
                                <th class="py-3.5 px-4 font-semibold">Target Model</th>
                                <th class="py-3.5 px-4 font-semibold">IP Address</th>
                                <th class="py-3.5 px-4 font-semibold text-right">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            @foreach($logs as $log)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-4 px-4 font-mono text-slate-500 text-[11px]">
                                        {{ $log->created_at->format('d M Y, h:i:s A') }}
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="font-bold text-slate-900">{{ $log->user->name ?? 'System' }}</span>
                                        @if($log->user)
                                            <span class="block text-[10px] text-slate-400">{{ $log->user->email }}</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        @php
                                            $color = match($log->action) {
                                                'created' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'updated' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                'deleted' => 'bg-rose-50 text-rose-700 border-rose-200',
                                                default => 'bg-slate-50 text-slate-700 border-slate-200',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full border text-[10px] uppercase font-bold font-mono {{ $color }}">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 font-mono text-[11px] text-slate-800">
                                        {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                    </td>
                                    <td class="py-4 px-4 font-mono text-[11px] text-slate-500">
                                        {{ $log->ip_address ?? '—' }}
                                    </td>
                                    <td class="py-4 px-4 text-right" x-data="{ open: false }">
                                        @if($log->new_values || $log->old_values)
                                            <button type="button" @click="open = !open" class="text-violet-600 hover:text-violet-800 font-bold text-[11px]">
                                                Payload ▾
                                            </button>
                                            <div x-show="open" @click.away="open = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display: none;">
                                                <div class="bg-white rounded-2xl p-6 max-w-lg w-full text-left shadow-2xl border border-slate-200 max-h-[80vh] overflow-y-auto">
                                                    <h4 class="text-sm font-bold text-slate-900 mb-2">Audit Payload: {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</h4>
                                                    @if($log->old_values)
                                                        <div class="mb-3">
                                                            <div class="text-[10px] font-bold text-slate-400 uppercase">Old Values</div>
                                                            <pre class="bg-slate-50 p-2 rounded-lg text-[10px] text-slate-700 overflow-x-auto">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                                        </div>
                                                    @endif
                                                    @if($log->new_values)
                                                        <div>
                                                            <div class="text-[10px] font-bold text-slate-400 uppercase">New Values</div>
                                                            <pre class="bg-slate-50 p-2 rounded-lg text-[10px] text-slate-700 overflow-x-auto">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                                        </div>
                                                    @endif
                                                    <div class="mt-4 text-right">
                                                        <button type="button" @click="open = false" class="px-4 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-slate-400 text-[10px]">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-200">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
