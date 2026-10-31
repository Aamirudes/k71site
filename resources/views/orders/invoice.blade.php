<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $order->order_number }}</title>
    @vite(['resources/css/app.css'])
    <style>
        body { background-color: #fff; color: #333; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; margin: 0 !important; background-color: #fff !important; }
            .invoice-box { border: none !important; box-shadow: none !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="py-10 bg-gray-50">
    <div class="max-w-4xl mx-auto">
        <!-- Print Button -->
        <div class="flex justify-end mb-6 no-print px-4 md:px-0">
            <button onclick="window.print()" class="px-6 py-2 bg-brand-sage text-white rounded-lg shadow-sm font-medium hover:bg-brand-sage/90 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print Invoice
            </button>
        </div>

        <!-- Invoice Document -->
        <div class="invoice-box bg-white md:border md:border-gray-200 md:shadow-lg md:rounded-2xl p-8 md:p-16">
            
            <!-- Header -->
            <div class="flex justify-between items-start border-b border-gray-100 pb-8 mb-8">
                <div>
                    <h1 class="text-4xl font-serif font-bold text-gray-900 tracking-tight">{{ $order->vendor?->shop_name ?? 'K71 Direct Store' }}</h1>
                    <p class="text-gray-500 mt-2 text-sm max-w-xs">The premier multi-vendor fashion marketplace. Discover unique brands, designers, and boutiques all in one place.</p>
                </div>
                <div class="text-right">
                    <h2 class="text-3xl font-light text-gray-400 uppercase tracking-widest mb-2">Invoice</h2>
                    <p class="text-gray-900 font-bold text-lg">#{{ $order->order_number }}</p>
                    <p class="text-gray-500 text-sm mt-1">Date: {{ $order->created_at->format('M d, Y') }}</p>
                </div>
            </div>

            <!-- Addresses -->
            <div class="flex flex-col sm:flex-row justify-between gap-8 border-b border-gray-100 pb-8 mb-8">
                <div>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-3">Billed To</p>
                    <p class="text-gray-900 font-bold">{{ $order->user ? ($order->user->first_name . ' ' . $order->user->last_name) : 'Valued Customer' }}</p>
                    <address class="text-gray-600 not-italic text-sm mt-1 leading-relaxed">
                        {{ $order->user?->email ?? ($order->address_snapshot['email'] ?? '') }}<br>
                        {{ $order->address_snapshot['line_1'] ?? '' }}<br>
                        {{ $order->address_snapshot['city'] ?? '' }}, {{ $order->address_snapshot['state'] ?? '' }} {{ $order->address_snapshot['pincode'] ?? '' }}<br>
                        P: {{ $order->address_snapshot['phone'] ?? '' }}
                    </address>
                </div>
                <div class="sm:text-right">
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-3">Sold By</p>
                    <p class="text-gray-900 font-bold">{{ $order->vendor?->shop_name ?? 'K71 Direct Store' }}</p>
                    <address class="text-gray-600 not-italic text-sm mt-1 leading-relaxed">
                        GSTIN: {{ $order->vendor?->business_details['gstin'] ?? 'N/A' }}<br>
                        PAN: {{ $order->vendor?->business_details['pan'] ?? 'N/A' }}<br>
                        Contact: {{ $order->vendor?->contact_email ?? 'support@k71.com' }}
                    </address>
                </div>
            </div>

            <!-- Items Table -->
            <div class="mb-8 overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 font-bold uppercase tracking-wider">
                            <th class="p-4">Item</th>
                            <th class="p-4 text-center">HSN</th>
                            <th class="p-4 text-center">Qty</th>
                            <th class="p-4 text-right">Price</th>
                            <th class="p-4 text-right">Tax (GST)</th>
                            <th class="p-4 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @foreach($order->items as $item)
                        <tr>
                            <td class="p-4">
                                <p class="font-medium text-gray-900">{{ $item->product->title ?? $item->snapshot['title'] ?? 'Product' }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">SKU: {{ $item->variant->sku ?? $item->snapshot['variant_sku'] ?? 'N/A' }}</p>
                            </td>
                            <td class="p-4 text-center text-gray-500">{{ $item->hsn_code ?? 'N/A' }}</td>
                            <td class="p-4 text-center font-medium">{{ $item->quantity }}</td>
                            <td class="p-4 text-right text-gray-600">₹{{ number_format($item->unit_price / 100, 2) }}</td>
                            <td class="p-4 text-right text-gray-500 text-xs">₹{{ number_format($item->tax_amount / 100, 2) }} <br>({{ $item->gst_rate ?? 0 }}%)</td>
                            <td class="p-4 text-right font-medium text-gray-900">₹{{ number_format($item->total_price / 100, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="flex justify-end border-b border-gray-100 pb-8 mb-8">
                <div class="w-full max-w-sm space-y-3 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span class="font-medium text-gray-900">₹{{ number_format($order->subtotal / 100, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Shipping</span>
                        <span class="font-medium text-gray-900">₹{{ number_format($order->shipping_amount / 100, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-400">
                        <span>Tax Included</span>
                        <span>₹{{ number_format($order->tax_amount / 100, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-end border-t border-gray-200 pt-3 mt-3">
                        <span class="text-base font-bold text-gray-900">Total</span>
                        <span class="text-2xl font-bold text-gray-900 tracking-tight">₹{{ number_format($order->total_amount / 100, 2) }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Footer Notes -->
            <div class="text-xs text-gray-400 text-center">
                <p>Payment Method: <span class="font-medium uppercase">{{ $order->payment_method }}</span></p>
                <p class="mt-1">This is a computer generated invoice and does not require a physical signature.</p>
                <p class="mt-6 text-gray-300 font-serif italic text-lg">Thank you for your business.</p>
            </div>
        </div>
    </div>
</body>
</html>
