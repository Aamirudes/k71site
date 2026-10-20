<x-layouts.public title="Store Unavailable">
    <div class="min-h-[60vh] flex items-center justify-center px-4">
        <div class="text-center">
            <div class="w-24 h-24 bg-brand-surface rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-brand-sage" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h1 class="text-4xl font-serif font-bold text-brand-text mb-4">Store Unavailable</h1>
            <p class="text-lg text-brand-muted mb-8 max-w-md mx-auto">
                @if($vendor->status === 'pending')
                    This store is currently under review by our team and is not yet open to the public. Check back soon!
                @else
                    This store is currently unavailable. Please check back later.
                @endif
            </p>
            <a href="{{ route('home') }}" class="inline-flex px-8 py-3 bg-brand-text text-white rounded-full font-bold hover:bg-black transition-colors">
                Return to Marketplace
            </a>
        </div>
    </div>
</x-layouts.public>
