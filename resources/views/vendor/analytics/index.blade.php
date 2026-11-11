<x-layouts.vendor title="Website Analytics">
    <div class="max-w-7xl mx-auto space-y-6 pb-12">

        <div class="bg-white rounded-2xl border border-zinc-200 p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="text-[11px] font-mono uppercase tracking-wider text-zinc-500 font-bold">Real Traffic Telemetry</div>
                <h1 class="text-2xl font-black text-zinc-950 tracking-tight mt-0.5">Personal Website &amp; Conversion Analytics</h1>
                <p class="text-xs text-zinc-500 mt-1">Measured strictly from real visits, portfolio opens, enquiry submissions, and completed orders.</p>
            </div>

            <div class="flex items-center gap-2 text-xs font-bold">
                @foreach([7 => '7 Days', 14 => '14 Days', 30 => '30 Days'] as $dVal => $dLabel)
                    <a href="{{ route('vendor.analytics.index', ['days' => $dVal]) }}"
                       class="px-3.5 py-2 rounded-xl border {{ $days === $dVal ? 'bg-black text-white border-black' : 'bg-white text-zinc-700 border-zinc-300' }}">
                        {{ $dLabel }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-zinc-200 p-5">
                <div class="text-[10px] font-mono uppercase font-bold text-zinc-500">Website Views ({{ $days }}d)</div>
                <div class="text-3xl font-black font-mono text-black mt-1">{{ $websiteViews }}</div>
                <div class="text-xs text-zinc-500 mt-1">Unique visitors: {{ $uniqueVisitors }}</div>
            </div>

            <div class="bg-white rounded-2xl border border-zinc-200 p-5">
                <div class="text-[10px] font-mono uppercase font-bold text-zinc-500">Portfolio Case Views</div>
                <div class="text-3xl font-black font-mono text-black mt-1">{{ $portfolioViews }}</div>
                <div class="text-xs text-zinc-500 mt-1">Deep case-study reads</div>
            </div>

            <div class="bg-white rounded-2xl border border-zinc-200 p-5">
                <div class="text-[10px] font-mono uppercase font-bold text-zinc-500">Website Enquiries ({{ $days }}d)</div>
                <div class="text-3xl font-black font-mono text-black mt-1">{{ $enquirySubmits }}</div>
                <div class="text-xs text-zinc-500 mt-1">All-time enquiries: {{ $directEnquiriesCount }}</div>
            </div>

            <div class="bg-white rounded-2xl border border-zinc-200 p-5">
                <div class="text-[10px] font-mono uppercase font-bold text-zinc-500">Completed Projects</div>
                <div class="text-3xl font-black font-mono text-black mt-1">{{ $serviceOrdersCompleted + $contractsCompleted }}</div>
                <div class="text-xs text-zinc-500 mt-1">{{ $serviceOrdersCompleted }} orders · {{ $contractsCompleted }} contracts</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Top Portfolio Projects -->
            <div class="bg-white rounded-2xl border border-zinc-200 p-6 space-y-4">
                <h2 class="text-sm font-black uppercase tracking-wider text-zinc-950">Top Viewed Portfolio Studies</h2>
                @if($topPortfolio->isEmpty())
                    <p class="text-xs text-zinc-500 py-4">No portfolio views recorded in this period yet.</p>
                @else
                    <div class="space-y-3">
                        @foreach($topPortfolio as $row)
                            <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-zinc-50 border border-zinc-200 text-xs">
                                <span class="font-bold text-zinc-900 truncate">{{ $row['project']->title }}</span>
                                <span class="font-mono font-bold text-black shrink-0">{{ $row['views'] }} views</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Daily Event Breakdown -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-zinc-200 p-6 space-y-4">
                <h2 class="text-sm font-black uppercase tracking-wider text-zinc-950">Daily Activity Breakdown</h2>
                <div class="overflow-x-auto max-h-96 overflow-y-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-zinc-200 text-[10px] font-mono uppercase text-zinc-500">
                            <tr>
                                <th class="py-2.5 px-3">Date</th>
                                <th class="py-2.5 px-3">Website Views</th>
                                <th class="py-2.5 px-3">Portfolio Opens</th>
                                <th class="py-2.5 px-3">Enquiries</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach($dailyRows as $d)
                                <tr>
                                    <td class="py-2 px-3 font-mono font-semibold text-zinc-700">{{ $d['date'] }}</td>
                                    <td class="py-2 px-3 font-mono font-bold text-black">{{ $d['views'] }}</td>
                                    <td class="py-2 px-3 font-mono text-zinc-700">{{ $d['portfolio'] }}</td>
                                    <td class="py-2 px-3 font-mono text-zinc-700">{{ $d['enquiries'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-layouts.vendor>

