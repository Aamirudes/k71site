<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Label - {{ $order->order_number }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: #f3f4f6;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .no-print-bar {
            width: 100%;
            max-width: 420px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            background: #111827;
            color: white;
            border: none;
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-secondary {
            background: #ffffff;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .label-container {
            width: 100mm;
            min-height: 150mm;
            background: #ffffff;
            border: 2px solid #000000;
            padding: 12px;
            color: #000000;
            position: relative;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .header-strip {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000000;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .carrier-name {
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .payment-pill {
            font-size: 11px;
            font-weight: 900;
            padding: 2px 8px;
            border: 2px solid #000000;
            text-transform: uppercase;
        }

        .payment-cod {
            background: #000000;
            color: #ffffff;
        }

        .barcode-section {
            text-align: center;
            border-bottom: 2px solid #000000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .barcode-graphic {
            height: 42px;
            letter-spacing: 4px;
            font-size: 28px;
            font-family: 'Courier New', Courier, monospace;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            background: repeating-linear-gradient(
                90deg,
                #000,
                #000 2px,
                #fff 2px,
                #fff 5px,
                #000 5px,
                #000 8px,
                #fff 8px,
                #fff 10px
            );
            color: transparent;
            margin: 4px 0;
            border-radius: 2px;
        }

        .awb-text {
            font-family: monospace;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .address-box {
            border-bottom: 2px solid #000000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .address-title {
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #4b5563;
            margin-bottom: 3px;
        }

        .recipient-name {
            font-size: 16px;
            font-weight: 900;
            margin-bottom: 2px;
        }

        .address-lines {
            font-size: 11px;
            line-height: 1.35;
            font-weight: 500;
        }

        .pincode-highlight {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-top: 5px;
            padding-top: 4px;
            border-top: 1px dashed #9ca3af;
        }

        .pin-number {
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 2px;
        }

        .phone-number {
            font-size: 12px;
            font-weight: 700;
        }

        .sender-box {
            border-bottom: 2px solid #000000;
            padding-bottom: 6px;
            margin-bottom: 8px;
            font-size: 10px;
            line-height: 1.3;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 8px;
        }

        .items-table th, .items-table td {
            border: 1px solid #000000;
            padding: 3px 5px;
            text-align: left;
        }

        .items-table th {
            background-color: #f3f4f6;
            font-weight: 800;
            text-transform: uppercase;
        }

        .footer-strip {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            padding-top: 4px;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }

            .no-print-bar {
                display: none !important;
            }

            .label-container {
                width: 100mm;
                height: 150mm;
                border: 2px solid #000;
                box-shadow: none;
                page-break-after: avoid;
            }

            @page {
                size: 100mm 150mm;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <a href="{{ route('vendor.orders.show', $order) }}" class="btn btn-secondary">
            ← Back to Order
        </a>
        <button onclick="window.print()" class="btn">
            🖨️ Print Shipping Label (4×6")
        </button>
    </div>

    <div class="label-container">
        <!-- Carrier & Payment Badge -->
        <div class="header-strip">
            <span class="carrier-name">{{ $order->courier_name ?? 'LOGISTICS DISPATCH' }}</span>
            @if($order->payment_method === 'cod')
                <span class="payment-pill payment-cod">COD: ₹{{ number_format($order->total_amount / 100, 2) }}</span>
            @else
                <span class="payment-pill">PREPAID - DO NOT COLLECT CASH</span>
            @endif
        </div>

        <!-- AWB Barcode -->
        <div class="barcode-section">
            <div class="barcode-graphic"></div>
            <div class="awb-text">AWB: {{ $order->tracking_number ?? $order->order_number }}</div>
            <div style="font-size: 10px; color: #4b5563; margin-top: 2px;">Order Ref: #{{ $order->order_number }}</div>
        </div>

        <!-- TO / DELIVERY ADDRESS (LARGEST FOR CARRIER SORTING) -->
        <div class="address-box">
            <div class="address-title">SHIP TO (CONSIGNEE):</div>
            <div class="recipient-name">
                {{ $order->user->name ?? 'Valued Customer' }}
            </div>
            @if($order->address_snapshot)
                <div class="address-lines">
                    {{ $order->address_snapshot['line_1'] ?? '' }}<br>
                    {{ $order->address_snapshot['city'] ?? '' }}, {{ $order->address_snapshot['state'] ?? '' }}
                </div>
                <div class="pincode-highlight">
                    <span class="pin-number">PIN: {{ $order->address_snapshot['pincode'] ?? '000000' }}</span>
                    <span class="phone-number">TEL: {{ $order->address_snapshot['phone'] ?? $order->user->phone ?? 'N/A' }}</span>
                </div>
            @else
                <div class="address-lines">Standard Customer Delivery Address</div>
            @endif
        </div>

        <!-- FROM / RETURN ADDRESS -->
        <div class="sender-box">
            <div class="address-title">SHIPPER (RETURN ADDRESS):</div>
            <strong style="font-size: 11px;">{{ $vendor->shop_name }}</strong><br>
            @if(!empty($vendor->pickup_address))
                {{ $vendor->pickup_address['line_1'] ?? '' }}, 
                {{ $vendor->pickup_address['city'] ?? '' }}, 
                {{ $vendor->pickup_address['state'] ?? '' }} - {{ $vendor->pickup_address['pincode'] ?? '' }}<br>
            @endif
            Phone: {{ $vendor->contact_phone ?? 'Seller Support' }} | GSTIN: {{ $vendor->gstin ?? 'Unregistered' }}
        </div>

        <!-- PACKAGE ITEMS CHECKLIST -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 44%;">Garment / Item</th>
                    <th style="width: 26%;">Variant / Size</th>
                    <th style="width: 18%; text-align: center;">Weight</th>
                    <th style="width: 12%; text-align: center;">Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ \Illuminate\Support\Str::limit($item->product->title ?? $item->snapshot['title'] ?? 'Product', 28) }}</td>
                        <td>{{ $item->variant->sku ?? $item->snapshot['variant_sku'] ?? 'Standard' }}</td>
                        <td style="text-align: center; font-size: 8.5px; color: #4b5563;">{{ $item->product->weight_grams ?? 250 }}g/pc</td>
                        <td style="text-align: center; font-weight: 700;">{{ $item->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @php
            $actualConsignmentGrams = $order->items->sum(function($i) {
                return ($i->product->weight_grams ?? 250) * $i->quantity;
            });
        @endphp
        <!-- FOOTER DETAILS -->
        <div class="footer-strip">
            <span>Date: {{ $order->created_at->format('d/m/Y') }}</span>
            <span>Items: {{ $order->items->sum('quantity') }} pcs</span>
            <span>Total Weight: {{ $actualConsignmentGrams >= 1000 ? number_format($actualConsignmentGrams / 1000, 2) . ' kg' : $actualConsignmentGrams . 'g' }}</span>
        </div>
    </div>

</body>
</html>
