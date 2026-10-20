<x-layouts.admin title="Shorts Moderation & Discovery">
    <div class="space-y-6" x-data="shortsModeration()">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between border-b border-slate-800 pb-5">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 text-xs font-mono font-semibold px-2.5 py-0.5 rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                        Video Commerce Moderation
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-white tracking-tight flex items-center gap-2">
                    🎬 Shorts Moderation Hub
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-slate-400">
                    Review and curate video reels uploaded by boutique vendors across K71. Manage approvals and top-of-feed pinning.
                </p>
            </div>
            <div class="mt-4 sm:mt-0 flex items-center gap-3">
                <a href="{{ route('shorts.index') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold rounded-xl transition shadow-xs">
                    <span>⚡ View Live Feed</span>
                </a>
            </div>
        </div>

        <!-- Filter Status Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
            <a href="{{ route('admin.shorts.index', ['status' => 'all']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $currentStatus === 'all' ? 'bg-amber-500 text-slate-950 font-black' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800' }}">
                <span>All Shorts</span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $currentStatus === 'all' ? 'bg-slate-950/20' : 'bg-slate-900' }}">{{ $counts['all'] }}</span>
            </a>
            <a href="{{ route('admin.shorts.index', ['status' => 'published']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $currentStatus === 'published' ? 'bg-emerald-500 text-slate-950 font-black' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800' }}">
                <span>Published</span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $currentStatus === 'published' ? 'bg-slate-950/20' : 'bg-slate-900' }}">{{ $counts['published'] }}</span>
            </a>
            <a href="{{ route('admin.shorts.index', ['status' => 'pending']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $currentStatus === 'pending' ? 'bg-amber-400 text-slate-950 font-black' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800' }}">
                <span>Pending Review</span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $currentStatus === 'pending' ? 'bg-slate-950/20' : 'bg-slate-900' }}">{{ $counts['pending'] }}</span>
            </a>
            <a href="{{ route('admin.shorts.index', ['status' => 'rejected']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $currentStatus === 'rejected' ? 'bg-rose-500 text-white font-black' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-800' }}">
                <span>Rejected</span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $currentStatus === 'rejected' ? 'bg-black/30' : 'bg-slate-900' }}">{{ $counts['rejected'] }}</span>
            </a>
        </div>

        <!-- Table of Shorts -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
            @if($shorts->isEmpty())
            <div class="p-12 text-center text-slate-500 text-sm">
                No shorts found under this status.
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/60 text-slate-400 uppercase font-mono tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="p-4">Reel / Preview</th>
                            <th class="p-4">Title & Store</th>
                            <th class="p-4">Engagement</th>
                            <th class="p-4">Status & Boost</th>
                            <th class="p-4 text-right">Moderation Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($shorts as $short)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="p-4">
                                <a href="{{ $short->video_url }}" target="_blank" class="relative block w-14 aspect-[9/16] rounded-xl overflow-hidden bg-slate-950 ring-1 ring-slate-700 group">
                                    <img src="{{ $short->thumb_url ?: \App\Models\Short::placeholderThumb($short->id, $short->title) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-300" alt="{{ $short->title }}">
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition">
                                        <span class="text-white text-xs">▶</span>
                                    </div>
                                </a>
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-white text-sm max-w-xs line-clamp-1">{{ $short->title }}</div>
                                <div class="text-slate-400 mt-0.5 flex items-center gap-2">
                                    <span class="text-amber-400 font-semibold">{{ $short->vendor?->shop_name ?? 'Unknown Vendor' }}</span>
                                    <span>•</span>
                                    <span>{{ $short->category?->name ?? 'General' }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 font-mono mt-1">
                                    {{ $short->created_at?->format('M j, Y H:i') }}
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="font-mono font-bold text-slate-200">
                                    <span>{{ number_format($short->views) }}</span> <span class="text-slate-500 font-normal">views</span>
                                </div>
                                <div class="text-slate-400 mt-0.5 text-[11px]">
                                    ❤️ {{ number_format($short->likes) }} · 🔗 {{ number_format($short->shares) }} · 💬 {{ $short->comments_count }}
                                </div>
                                <div class="text-[10px] font-mono text-emerald-400 mt-0.5">
                                    Score: {{ number_format($short->trending_score, 1) }}
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex flex-col gap-1.5 items-start">
                                    @if($short->status === 'published')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                        Published
                                    </span>
                                    @elseif($short->status === 'pending')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                        Pending Review
                                    </span>
                                    @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                        Rejected
                                    </span>
                                    @endif

                                    @if($short->is_manual_boost)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/20 text-purple-400 border border-purple-500/30">
                                        📌 Pinned to Top
                                    </span>
                                    @elseif($short->is_boosted)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                        🚀 {{ ucfirst($short->boost_type ?? 'Boosted') }}
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Manual Pin Button -->
                                    <button type="button" @click="togglePin({{ $short->id }})" class="px-2.5 py-1.5 rounded-xl border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition" title="Toggle Pin to Top">
                                        {{ $short->is_manual_boost ? 'Unpin' : '📌 Pin' }}
                                    </button>

                                    @if($short->status !== 'published')
                                    <button type="button" @click="approveShort({{ $short->id }})" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition">
                                        Approve
                                    </button>
                                    @endif

                                    @if($short->status !== 'rejected')
                                    <button type="button" @click="rejectShort({{ $short->id }})" class="px-3 py-1.5 rounded-xl bg-rose-600/20 hover:bg-rose-600 border border-rose-500/30 text-rose-300 hover:text-white text-xs font-bold transition">
                                        Reject
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-800">
                {{ $shorts->links() }}
            </div>
            @endif
        </div>
    </div>

    <script>
        function shortsModeration() {
            return {
                approveShort(id) {
                    if (!confirm('Approve and publish this short immediately?')) return;
                    fetch(`/admin/shorts/${id}/approve`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) location.reload();
                    });
                },

                rejectShort(id) {
                    const reason = prompt('Reason for rejection:', 'Content violates marketplace guidelines.');
                    if (!reason) return;

                    fetch(`/admin/shorts/${id}/reject`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ reason: reason }),
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) location.reload();
                    });
                },

                togglePin(id) {
                    fetch(`/admin/shorts/${id}/manual-boost`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) location.reload();
                    });
                }
            };
        }
    </script>
</x-layouts.admin>
