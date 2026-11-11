<x-layouts.vendor title="Verified Reviews Hub">
    <div class="max-w-6xl mx-auto space-y-6 pb-12">

        <div class="bg-white rounded-2xl border border-zinc-200 p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="text-[11px] font-mono uppercase tracking-wider text-zinc-500 font-bold">Client Reputation</div>
                <h1 class="text-2xl font-black text-zinc-950 tracking-tight mt-0.5">Verified Client Reviews &amp; Official Replies</h1>
                <p class="text-xs text-zinc-500 mt-1">Only verified completed service orders and completed project contracts generate reviews shown on your website.</p>
            </div>

            <div class="text-left sm:text-right px-4 py-3 rounded-xl bg-zinc-50 border border-zinc-200">
                <div class="text-xs text-zinc-500 font-bold">Overall Rating</div>
                <div class="text-2xl font-black font-mono text-black">{{ $avgRating ? $avgRating . ' ★' : 'New' }}</div>
                <div class="text-[11px] text-zinc-500">{{ $totalCount }} verified review{{ $totalCount === 1 ? '' : 's' }}</div>
            </div>
        </div>

        @if($totalCount === 0)
            <div class="p-12 text-center rounded-2xl bg-white border border-zinc-200 space-y-2">
                <h3 class="text-base font-bold text-zinc-900">No Verified Reviews Yet</h3>
                <p class="text-xs text-zinc-500">Reviews submitted by clients after completed service orders or contracts automatically appear here and on your website.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($serviceReviews as $rev)
                    <div class="bg-white rounded-2xl border border-zinc-200 p-5 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="font-bold text-sm text-zinc-950">{{ $rev->user?->name ?? 'Verified Client' }}</span>
                                <span class="text-xs text-zinc-400 mx-1.5">•</span>
                                <span class="text-xs text-zinc-600">{{ $rev->product?->title ?? 'Service Order' }}</span>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900">
                                ★ {{ (int) $rev->rating }}/5 · Service Order
                            </span>
                        </div>

                        <p class="text-xs sm:text-sm text-zinc-700 leading-relaxed">{{ $rev->body ?? $rev->comment }}</p>

                        @if($rev->vendor_reply)
                            <div class="p-3.5 rounded-xl bg-zinc-50 border border-zinc-200 text-xs">
                                <div class="font-bold text-zinc-900 mb-1">Your Official Reply:</div>
                                <p class="text-zinc-700">{{ $rev->vendor_reply }}</p>
                            </div>
                        @else
                            <form action="{{ route('vendor.reviews.reply', $rev) }}" method="POST" class="flex flex-col sm:flex-row gap-2 pt-2">
                                @csrf
                                <input type="text" name="vendor_reply" required placeholder="Write an official thank-you or response (displayed on your website)..."
                                       class="flex-1 px-3.5 py-2 rounded-xl border border-zinc-300 text-xs">
                                <button type="submit" class="px-4 py-2 rounded-xl bg-black hover:bg-zinc-800 text-white text-xs font-bold cursor-pointer">
                                    Post Official Reply
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach

                @foreach($contractReviews as $con)
                    <div class="bg-white rounded-2xl border border-zinc-200 p-5 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="font-bold text-sm text-zinc-950">{{ $con->buyer?->name ?? 'Project Client' }}</span>
                                <span class="text-xs text-zinc-400 mx-1.5">•</span>
                                <span class="text-xs text-zinc-600">{{ $con->project?->title ?? 'Completed Contract' }}</span>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900">
                                ★ {{ (int) $con->buyer_rating }}/5 · Project Contract
                            </span>
                        </div>

                        <p class="text-xs sm:text-sm text-zinc-700 leading-relaxed">{{ $con->buyer_review }}</p>

                        @if($con->vendor_reply)
                            <div class="p-3.5 rounded-xl bg-zinc-50 border border-zinc-200 text-xs">
                                <div class="font-bold text-zinc-900 mb-1">Your Official Reply:</div>
                                <p class="text-zinc-700">{{ $con->vendor_reply }}</p>
                            </div>
                        @else
                            <form action="{{ route('vendor.reviews.contract_reply', $con) }}" method="POST" class="flex flex-col sm:flex-row gap-2 pt-2">
                                @csrf
                                <input type="text" name="vendor_reply" required placeholder="Write an official reply shown on your personal website..."
                                       class="flex-1 px-3.5 py-2 rounded-xl border border-zinc-300 text-xs">
                                <button type="submit" class="px-4 py-2 rounded-xl bg-black hover:bg-zinc-800 text-white text-xs font-bold cursor-pointer">
                                    Post Official Reply
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.vendor>

