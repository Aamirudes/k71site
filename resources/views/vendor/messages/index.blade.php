<x-layouts.vendor title="Customer Messages & Inquiries">
    <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-serif font-bold text-gray-900">Customer Messages & Inquiries</h1>
            <p class="text-sm text-gray-500 mt-1">Direct inquiries, auction discussions, and customer support threads.</p>
        </div>
        <a href="/messages" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-700 via-indigo-600 to-purple-600 text-white text-xs font-bold shadow-md hover:shadow-lg hover:scale-105 active:scale-95 transition-all">
            <span>✨ Open Realtime Direct Chat</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($conversations->isEmpty())
        <div class="rounded-2xl bg-white border border-gray-200 p-12 text-center shadow-xs">
            <div class="text-5xl mb-4">💬</div>
            <h2 class="text-base font-bold text-gray-900 mb-1">No customer messages yet</h2>
            <p class="text-xs text-gray-500 max-w-md mx-auto">
                When buyers or bidders have questions regarding your listings or auctions, their messages will appear here.
            </p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($conversations as $conversation)
                @php
                    $unread = $conversation->unreadMessagesFor('vendor', $vendor->id);
                    $latest = $conversation->latestMessage;
                @endphp
                <a href="{{ route('vendor.messages.show', $conversation->id) }}"
                   class="block rounded-2xl bg-white hover:bg-gray-50/80 border {{ $unread > 0 ? 'border-violet-300 ring-2 ring-violet-500/10' : 'border-gray-200' }} p-5 transition shadow-xs group">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-11 h-11 rounded-xl bg-violet-50 border border-violet-200 flex items-center justify-center text-violet-700 font-bold shrink-0 text-base">
                                {{ substr($conversation->user->name ?? 'Customer', 0, 1) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-sm font-bold text-gray-900 group-hover:text-violet-700 transition">
                                        {{ $conversation->user->name ?? 'Customer' }}
                                    </h2>
                                    @if($unread > 0)
                                        <span class="px-2 py-0.5 rounded-full bg-violet-600 text-white text-[10px] font-black">
                                            {{ $unread }} new
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs font-semibold text-gray-700 mt-0.5">
                                    {{ $conversation->subject ?? 'Direct Inquiry' }}
                                </p>
                                @if($conversation->product)
                                    <span class="inline-block mt-1 text-[11px] text-gray-600 bg-gray-100 px-2.5 py-0.5 rounded-md border border-gray-200">
                                        📦 {{ \Illuminate\Support\Str::limit($conversation->product->title, 45) }}
                                    </span>
                                @endif
                                @if($latest)
                                    <p class="text-xs text-gray-500 mt-2 line-clamp-1">
                                        <span class="font-medium text-gray-700">{{ $latest->sender_type === 'vendor' ? 'You: ' : 'Buyer: ' }}</span>
                                        {{ $latest->body }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            <span class="text-[11px] text-gray-400 font-mono">
                                {{ $conversation->last_message_at ? $conversation->last_message_at->diffForHumans() : '' }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $conversations->links() }}
        </div>
    @endif
</x-layouts.vendor>
