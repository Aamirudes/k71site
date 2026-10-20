<x-layouts.vendor title="Edit Coupon">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-serif font-bold text-gray-900">Edit Coupon</h1>
            <p class="text-sm text-gray-500 mt-1">Modify coupon code: {{ $coupon->code }}</p>
        </div>
        <div>
            <a href="{{ route('vendor.coupons.index') }}" class="text-sm font-medium text-brand-sage hover:text-brand-sage/80 transition-colors">&larr; Back to Coupons</a>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-8 p-4 bg-red-50 border border-red-200 rounded-xl">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('vendor.coupons.update', $coupon) }}" method="POST" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
        @csrf
        @method('PUT')
        
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                
                <div class="sm:col-span-4">
                    <label for="code" class="block text-sm font-medium leading-6 text-gray-900">Coupon Code *</label>
                    <div class="mt-2">
                        <input type="text" name="code" id="code" value="{{ old('code', $coupon->code) }}" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-sage sm:text-sm sm:leading-6">
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="type" class="block text-sm font-medium leading-6 text-gray-900">Discount Type *</label>
                    <div class="mt-2">
                        <select id="type" name="type" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-sage sm:max-w-xs sm:text-sm sm:leading-6">
                            <option value="percentage" {{ old('type', $coupon->type) == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                            <option value="flat" {{ old('type', $coupon->type) == 'flat' ? 'selected' : '' }}>Flat Amount (₹)</option>
                        </select>
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="value" class="block text-sm font-medium leading-6 text-gray-900">Discount Value *</label>
                    <div class="mt-2">
                        @php
                            $displayValue = $coupon->type === 'flat' ? $coupon->value / 100 : $coupon->value;
                        @endphp
                        <input type="number" name="value" id="value" value="{{ old('value', $displayValue) }}" min="1" step="any" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-sage sm:text-sm sm:leading-6">
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="min_cart_value" class="block text-sm font-medium leading-6 text-gray-900">Minimum Cart Value (₹)</label>
                    <div class="mt-2">
                        <input type="number" name="min_cart_value" id="min_cart_value" value="{{ old('min_cart_value', $coupon->min_cart_value ? $coupon->min_cart_value / 100 : '') }}" min="1" step="any" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-sage sm:text-sm sm:leading-6">
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="max_discount" class="block text-sm font-medium leading-6 text-gray-900">Maximum Discount (₹)</label>
                    <div class="mt-2">
                        <input type="number" name="max_discount" id="max_discount" value="{{ old('max_discount', $coupon->max_discount ? $coupon->max_discount / 100 : '') }}" min="1" step="any" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-sage sm:text-sm sm:leading-6">
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="starts_at" class="block text-sm font-medium leading-6 text-gray-900">Start Date & Time *</label>
                    <div class="mt-2">
                        <input type="datetime-local" name="starts_at" id="starts_at" value="{{ old('starts_at', \Carbon\Carbon::parse($coupon->starts_at)->format('Y-m-d\TH:i')) }}" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-sage sm:text-sm sm:leading-6">
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="expires_at" class="block text-sm font-medium leading-6 text-gray-900">End Date & Time *</label>
                    <div class="mt-2">
                        <input type="datetime-local" name="expires_at" id="expires_at" value="{{ old('expires_at', \Carbon\Carbon::parse($coupon->expires_at)->format('Y-m-d\TH:i')) }}" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-sage sm:text-sm sm:leading-6">
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="total_limit" class="block text-sm font-medium leading-6 text-gray-900">Total Usage Limit</label>
                    <div class="mt-2">
                        <input type="number" name="total_limit" id="total_limit" value="{{ old('total_limit', $coupon->total_limit) }}" min="1" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-sage sm:text-sm sm:leading-6">
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="per_customer_limit" class="block text-sm font-medium leading-6 text-gray-900">Per-Customer Limit *</label>
                    <div class="mt-2">
                        <input type="number" name="per_customer_limit" id="per_customer_limit" value="{{ old('per_customer_limit', $coupon->per_customer_limit) }}" min="1" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-sage sm:text-sm sm:leading-6">
                    </div>
                </div>

                <div class="sm:col-span-6">
                    <fieldset>
                        <legend class="text-sm font-semibold leading-6 text-gray-900">Conditions & Status</legend>
                        <div class="mt-4 space-y-4">
                            <div class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input id="first_order_only" name="first_order_only" type="checkbox" value="1" {{ old('first_order_only', $coupon->first_order_only) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-brand-sage focus:ring-brand-sage">
                                </div>
                                <div class="ml-3 text-sm leading-6">
                                    <label for="first_order_only" class="font-medium text-gray-900">First Order Only</label>
                                </div>
                            </div>
                            <div class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-brand-sage focus:ring-brand-sage">
                                </div>
                                <div class="ml-3 text-sm leading-6">
                                    <label for="is_active" class="font-medium text-gray-900">Active</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>

            </div>
        </div>
        
        <div class="flex items-center justify-between border-t border-gray-900/10 px-4 py-4 sm:px-8 bg-gray-50">
            <div>
                <p class="text-sm text-gray-500">Used {{ $coupon->usage_count }} times.</p>
            </div>
            <div class="flex gap-x-6">
                <a href="{{ route('vendor.coupons.index') }}" class="text-sm font-semibold leading-6 text-gray-900 pt-2">Cancel</a>
                <button type="submit" class="rounded-md bg-brand-sage px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-sage/90">Save Changes</button>
            </div>
        </div>
    </form>
</x-layouts.vendor>
