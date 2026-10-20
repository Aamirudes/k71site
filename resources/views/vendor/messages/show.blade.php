<x-layouts.vendor title="Conversation with {{ $conversation->user->name ?? 'Customer' }}">
    <!-- Back and Subject Header -->
    <div class="mb-6">
        <a href="{{ route('vendor.messages.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-gray-500 hover:text-gray-900 transition mb-3">
            <span>←</span>
            <span>Back to All Inquiries</span>
        </a>

        <div class="rounded-2xl bg-white border border-gray-200 p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-violet-50 border border-violet-200 flex items-center justify-center text-violet-700 font-bold shrink-0 text-lg">
                    {{ substr($conversation->user->name ?? 'Customer', 0, 1) }}
                </div>
                <div>
                    <h1 class="text-base font-bold text-gray-900">
                        {{ $conversation->user->name ?? 'Customer' }}
                    </h1>
                    <p class="text-xs text-gray-600 mt-0.5 font-medium">{{ $conversation->subject }}</p>
                </div>
            </div>

            @if($conversation->product)
                <div class="shrink-0">
                    <a href="{{ route('catalog.product', $conversation->product->slug) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gray-50 hover:bg-gray-100 border border-gray-200 text-xs text-gray-700 transition">
                        <span>📦</span>
                        <span class="font-medium truncate max-w-[200px]">{{ $conversation->product->title }}</span>
                        <span class="text-[10px] text-violet-600 font-bold">View →</span>
                    </a>
                </div>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Chat Box Container -->
    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden shadow-xs flex flex-col h-[550px]">
        <!-- Scrollable Messages Area -->
        <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-4 bg-gray-50/50">
            @foreach($conversation->messages as $msg)
                @if($msg->isSenderVendor())
                    <!-- Vendor Message (Right) -->
                    <div class="flex justify-end">
                        <div class="max-w-[80%] sm:max-w-[70%]">
                            <div class="rounded-2xl rounded-tr-xs bg-brand-sage text-white p-3.5 text-xs sm:text-sm leading-relaxed shadow-xs">
                                {{ $msg->body }}
                            </div>
                            <div class="text-[10px] text-gray-400 text-right mt-1 font-mono">
                                You ({{ $vendor->shop_name }}) • {{ $msg->created_at->format('h:i A') }}
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Customer Message (Left) -->
                    <div class="flex justify-start items-start gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-violet-100 text-violet-700 font-bold text-[10px] flex items-center justify-center shrink-0 mt-1">
                            {{ substr($conversation->user->name ?? 'C', 0, 1) }}
                        </div>
                        <div class="max-w-[80%] sm:max-w-[70%]">
                            <div class="rounded-2xl rounded-tl-xs bg-white text-gray-900 p-3.5 text-xs sm:text-sm leading-relaxed border border-gray-200 shadow-xs">
                                {{ $msg->body }}
                            </div>
                            <div class="text-[10px] text-gray-400 mt-1 font-mono">
                                {{ $conversation->user->name ?? 'Customer' }} • {{ $msg->created_at->format('h:i A') }}
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Vendor Reply Box -->
        <div class="p-4 bg-white border-t border-gray-200">
            <form action="{{ route('vendor.messages.reply', $conversation->id) }}" method="POST" class="flex items-center gap-2">
                @csrf
                <input type="text" name="body" required placeholder="Type your reply to {{ $conversation->user->name ?? 'customer' }}..."
                       class="flex-1 px-4 py-3 rounded-xl bg-gray-50 border border-gray-300 text-gray-900 text-xs sm:text-sm focus:ring-2 focus:ring-brand-sage focus:outline-none placeholder:text-gray-400">
                <button type="submit" 
                        class="px-5 py-3 rounded-xl bg-brand-sage hover:bg-brand-sage/90 text-white text-xs font-bold transition shadow-sm shrink-0 flex items-center gap-1.5 active:scale-95">
                    <span>Send Reply</span>
                    <span>↗</span>
                </button>
            </form>
        </div>
    </div>
</x-layouts.vendor>
