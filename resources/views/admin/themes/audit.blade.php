<x-layouts.admin title="Theme Marketplace Audit History">
<div class="p-6 max-w-7xl mx-auto space-y-6">
    @include('admin.themes._tabs')

    <div class="rounded-2xl bg-white border border-zinc-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-zinc-50 border-b border-zinc-200 text-[10px] font-mono uppercase text-zinc-500">
                    <tr>
                        <th class="py-3 px-4">Timestamp</th>
                        <th class="py-3 px-3">Actor</th>
                        <th class="py-3 px-3">Action</th>
                        <th class="py-3 px-3">Target</th>
                        <th class="py-3 px-4">Before / After State &amp; Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($logs as $log)
                        <tr class="align-top">
                            <td class="py-3 px-4 whitespace-nowrap font-mono text-[11px] text-zinc-500">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="font-bold text-zinc-900">{{ $log->actor?->name ?? 'System' }}</span>
                                <span class="ml-1 px-1.5 py-0.5 rounded bg-zinc-100 text-[10px] uppercase font-mono">{{ $log->actor_role }}</span>
                            </td>
                            <td class="py-3 px-3 font-mono font-bold text-indigo-700">{{ $log->action }}</td>
                            <td class="py-3 px-3 text-[11px] text-zinc-700">
                                @if($log->theme_id) <div>Theme: <strong class="font-mono">{{ $log->theme_id }}</strong></div> @endif
                                @if($log->vendor) <div>Vendor: <strong>{{ $log->vendor->shop_name }}</strong></div> @endif
                            </td>
                            <td class="py-3 px-4 text-[11px] text-zinc-600 space-y-1 max-w-md">
                                @if($log->notes) <p class="font-medium text-zinc-800">{{ $log->notes }}</p> @endif
                                @if($log->before_state) <div class="font-mono text-[10px] text-zinc-500 break-all">Before: {{ json_encode($log->before_state) }}</div> @endif
                                @if($log->after_state) <div class="font-mono text-[10px] text-emerald-700 break-all">After: {{ json_encode($log->after_state) }}</div> @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-zinc-500">No theme audit events recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $logs->links() }}</div>
</div>
</x-layouts.admin>
