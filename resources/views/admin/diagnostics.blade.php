<x-layouts.admin title="Platform System Diagnostics">
    <div class="space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-mono uppercase tracking-wider text-zinc-400 font-bold mb-1">
                    <span class="w-2 h-2 rounded-full {{ $report['healthy'] ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    <span>System Diagnostics &amp; Reconciliation</span>
                </div>
                <h2 class="text-xl font-bold text-zinc-950 tracking-tight">Platform Consistency Monitor</h2>
                <p class="text-xs text-zinc-500 mt-1">
                    Read-only inspection of database connectivity, storage permissions, subscription expiry drift, enquiry counters, short boosts, and scheduled jobs.
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 rounded-lg text-xs font-semibold bg-zinc-100 hover:bg-zinc-200 text-zinc-800 transition">
                    ← Command Center
                </a>
                <form action="{{ route('admin.diagnostics.reconcile') }}" method="POST" onsubmit="return confirm('Run safe deterministic reconciliation (sync mismatched enquiry quote counts and expire overdue short boosts)?');">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg text-xs font-semibold bg-zinc-950 hover:bg-zinc-800 text-white transition cursor-pointer">
                        Run Safe Reconciliation
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 text-xs font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($report['checks'] as $checkKey => $count)
                <div class="bg-white rounded-2xl p-5 border {{ $count > 0 ? 'border-amber-300 bg-amber-50/30' : 'border-zinc-200/80' }} shadow-xs">
                    <div class="text-[11px] font-mono uppercase tracking-wider text-zinc-500 font-bold">
                        {{ ucwords(str_replace('_', ' ', $checkKey)) }}
                    </div>
                    <div class="text-3xl font-extrabold mt-2 font-mono {{ $count > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                        {{ $count }}
                    </div>
                    <div class="text-[11px] text-zinc-500 mt-1">
                        {{ $count === 0 ? 'Consistent' : 'Action or cron sweep recommended' }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-4">
            <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider font-mono">Runtime &amp; Storage Health</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div class="p-3 rounded-xl bg-zinc-50 border border-zinc-200/70">
                    <div class="text-zinc-400 font-bold uppercase text-[10px]">Database</div>
                    <div class="font-mono font-bold mt-1 {{ $report['environment']['db_healthy'] ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ $report['environment']['db_healthy'] ? 'Connected (' . $report['environment']['db_connection'] . ')' : 'Error' }}
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-zinc-50 border border-zinc-200/70">
                    <div class="text-zinc-400 font-bold uppercase text-[10px]">Public Storage</div>
                    <div class="font-mono font-bold mt-1 {{ $report['environment']['storage_writable'] ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ $report['environment']['storage_writable'] ? 'Writable' : 'Read-Only' }}
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-zinc-50 border border-zinc-200/70">
                    <div class="text-zinc-400 font-bold uppercase text-[10px]">PHP / Laravel</div>
                    <div class="font-mono font-bold mt-1 text-zinc-900">
                        {{ $report['environment']['php_version'] }} / {{ $report['environment']['laravel_version'] }}
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-zinc-50 border border-zinc-200/70">
                    <div class="text-zinc-400 font-bold uppercase text-[10px]">Queue / Cache</div>
                    <div class="font-mono font-bold mt-1 text-zinc-900">
                        {{ $report['environment']['queue_driver'] }} / {{ $report['environment']['cache_driver'] }}
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-4">
            <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider font-mono">Recent Scheduled Job Runs</h3>
            @if($report['recent_job_runs']->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="border-b border-zinc-200 text-zinc-500 text-left">
                                <th class="py-2 pr-4">Job</th>
                                <th class="py-2 pr-4">Expired / Failed</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4">Processed</th>
                                <th class="py-2 pr-4">Summary</th>
                                <th class="py-2">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach($report['recent_job_runs'] as $run)
                                <tr>
                                    <td class="py-2.5 pr-4 font-mono font-bold text-zinc-900">{{ $run->job_name }}</td>
                                    <td class="py-2.5 pr-4 font-mono text-zinc-600">{{ $run->expired_count ?? 0 }} / {{ $run->failed_count ?? 0 }}</td>
                                    <td class="py-2.5 pr-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $run->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                            {{ strtoupper($run->status) }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 pr-4 font-mono">{{ $run->processed_count }}</td>
                                    <td class="py-2.5 pr-4 text-zinc-600">{{ $run->summary }}</td>
                                    <td class="py-2.5 font-mono text-zinc-400">{{ optional($run->created_at)->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-xs text-zinc-500">No scheduled job runs logged yet.</p>
            @endif
        </div>
    </div>
</x-layouts.admin>
