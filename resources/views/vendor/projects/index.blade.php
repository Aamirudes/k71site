<x-layouts.vendor title="Open Briefs & Proposals">
    <div class="max-w-7xl mx-auto space-y-8 pb-12">

        <div class="bg-white rounded-2xl border border-zinc-200 p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="text-[11px] font-mono uppercase tracking-wider text-zinc-500 font-bold">Upwork Project Marketplace</div>
                <h1 class="text-2xl font-black text-zinc-950 tracking-tight mt-0.5">Open Project Briefs &amp; My Proposals</h1>
                <p class="text-xs text-zinc-500 mt-1">Discover custom buyer project briefs on K71 Services, pitch with portfolio samples, and track proposal status.</p>
            </div>

            <a href="{{ route('services.projects.index') }}" target="_blank"
               class="px-4 py-2.5 rounded-xl border border-zinc-300 bg-white hover:bg-zinc-50 text-xs font-bold text-zinc-800 shrink-0">
                Browse Public Project Board ↗
            </a>
        </div>

        <!-- MY SUBMITTED PROPOSALS -->
        <div class="bg-white rounded-2xl border border-zinc-200 p-6 space-y-4">
            <h2 class="text-base font-black text-zinc-950">My Submitted Proposals</h2>

            @if($myProposals->isEmpty())
                <div class="p-8 rounded-xl border border-dashed border-zinc-300 text-center text-xs text-zinc-500">
                    You have not submitted any proposals yet. Open any brief below to submit a tailored pitch.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-zinc-200 text-[10px] font-mono uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th class="py-3 px-3">Project Brief</th>
                                <th class="py-3 px-3">Bid Amount</th>
                                <th class="py-3 px-3">Timeline</th>
                                <th class="py-3 px-3">Status</th>
                                <th class="py-3 px-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach($myProposals as $prop)
                                <tr>
                                    <td class="py-3.5 px-3">
                                        @if($prop->project)
                                            <a href="{{ route('services.projects.show', $prop->project) }}" class="font-bold text-zinc-950 hover:underline block">
                                                {{ $prop->project->title }}
                                            </a>
                                            <span class="text-[11px] text-zinc-400">Client: {{ $prop->project->buyer?->name ?? 'Client' }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-3 font-mono font-bold text-black">{{ $prop->formatted_bid }}</td>
                                    <td class="py-3.5 px-3 text-zinc-600">{{ $prop->delivery_days }} days</td>
                                    <td class="py-3.5 px-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ match($prop->status) {
                                            'hired' => 'bg-emerald-100 text-emerald-800',
                                            'shortlisted' => 'bg-blue-100 text-blue-800',
                                            'rejected', 'withdrawn' => 'bg-zinc-100 text-zinc-500',
                                            default => 'bg-amber-100 text-amber-800',
                                        } }}">
                                            {{ $prop->status }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($prop->project)
                                                <a href="{{ route('services.projects.show', $prop->project) }}" class="px-2.5 py-1 rounded-lg border border-zinc-300 text-[11px] font-bold text-zinc-700 hover:bg-zinc-50">
                                                    Open Brief
                                                </a>
                                            @endif
                                            @if(in_array($prop->status, ['pending', 'shortlisted'], true))
                                                <form action="{{ route('services.proposals.withdraw', $prop) }}" method="POST"
                                                      onsubmit="return confirm('Withdraw this proposal?')">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 text-[11px] font-bold cursor-pointer">
                                                        Withdraw
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div>{{ $myProposals->links() }}</div>
            @endif
        </div>

        <!-- OPEN MARKETPLACE PROJECT BRIEFS -->
        <div class="bg-white rounded-2xl border border-zinc-200 p-6 space-y-4">
            <h2 class="text-base font-black text-zinc-950">Latest Open Buyer Briefs</h2>

            @if($openBriefs->isEmpty())
                <div class="p-8 rounded-xl border border-dashed border-zinc-300 text-center text-xs text-zinc-500">
                    No open project briefs available right now.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($openBriefs as $brief)
                        <div class="rounded-xl border border-zinc-200 p-4 flex flex-col justify-between gap-3 hover:border-black transition">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-[10px] font-mono text-zinc-500 uppercase">
                                    <span>{{ $brief->category?->name ?? ucfirst($brief->work_mode) }}</span>
                                    <span>{{ $brief->proposals_count }} proposals</span>
                                </div>
                                <h3 class="font-bold text-sm text-zinc-950 leading-snug">{{ $brief->title }}</h3>
                                <p class="text-xs text-zinc-600 line-clamp-2">{{ $brief->description }}</p>
                            </div>

                            <div class="pt-3 border-t border-zinc-100 flex items-center justify-between text-xs">
                                <span class="font-mono font-bold text-black">
                                    {{ $brief->budget_min ? $brief->formatted_budget : 'Flexible Budget' }}
                                </span>
                                <a href="{{ route('services.projects.show', $brief) }}" class="px-3 py-1.5 rounded-lg bg-black hover:bg-zinc-800 text-white font-bold text-[11px]">
                                    View &amp; Pitch →
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-layouts.vendor>
