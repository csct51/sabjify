<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #1c1917; font-size: 13px; }
        .invoice { padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; }
        .store-name { font-size: 20px; font-weight: bold; }
        .store-sub { color: #78716c; margin-top: 4px; font-size: 12px; }
        .invoice-title { font-size: 24px; font-weight: bold; text-align: right; }
        .invoice-meta { text-align: right; color: #57534e; font-size: 12px; margin-top: 6px; }
        .billing { display: flex; justify-content: space-between; gap: 24px; margin-bottom: 24px; }
        .block { width: 48%; }
        .block h3 { font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #a8a29e; margin-bottom: 8px; }
        .block p { line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th { background: #f5f5f4; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #57534e; }
        th, td { padding: 10px 12px; border-bottom: 1px solid #e7e5e4; }
        .right { text-align: right; }
        .totals { width: 260px; margin-left: auto; }
        .totals .row { display: flex; justify-content: space-between; padding: 6px 0; }
        .totals .grand { font-weight: bold; font-size: 15px; border-top: 2px solid #1c1917; margin-top: 6px; padding-top: 10px; }
        .footer { margin-top: 32px; text-align: center; color: #a8a29e; font-size: 11px; }
        .payment { font-size: 12px; color: #57534e; }
    </style>
</head>
<body>
    <div class="invoice">
        <div class="header">
            <div>
                <div class="store-name">{{ config('app.name') }}</div>
                <div class="store-sub">Fresh groceries delivered to your door</div>
            </div>
            <div>
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-meta">
                    <div>Invoice No: {{ $order->order_number }}</div>
                    <div>Order Date: {{ $order->created_at->format('d M Y, h:i A') }}</div>
                    <div>Payment: {{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Online Payment' }}</div>
                </div>
            </div>
        </div>

        <div class="billing">
            <div class="block">
                <h3>Billed To</h3>
                <p>
                    {{ $order->receiver_name }}<br>
                    {{ $order->receiver_phone }}<br>
                    {{ $order->address_line }}, {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}
                </p>
            </div>
            <div class="block">
                <h3>Order Status</h3>
                <p class="payment">{{ ucfirst($order->status) }} · {{ ucfirst($order->payment_status) }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Unit</th>
                    <th class="right">Price</th>
                    <th class="right">Qty</th>
                    <th class="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->unit }}</td>
                        <td class="right">{{ \Illuminate\Support\Number::currency($item->price, 'INR') }}</td>
                        <td class="right">{{ $item->quantity }}</td>
                        <td class="right">{{ \Illuminate\Support\Number::currency($item->total, 'INR') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="row"><span>Subtotal</span><span>{{ \Illuminate\Support\Number::currency($order->subtotal, 'INR') }}</span></div>
            <div class="row"><span>Delivery Fee</span><span>{{ $order->delivery_fee === 0 ? 'FREE' : \Illuminate\Support\Number::currency($order->delivery_fee, 'INR') }}</span></div>
            @if ($order->discount > 0)
                <div class="row"><span>Discount</span><span>−{{ \Illuminate\Support\Number::currency($order->discount, 'INR') }}</span></div>
            @endif
            <div class="row grand"><span>Total</span><span>{{ \Illuminate\Support\Number::currency($order->total, 'INR') }}</span></div>
        </div>

        @if ($order->notes)
            <div style="margin-top:24px; font-size:12px; color:#57534e;">
                <strong>Notes:</strong> {{ $order->notes }}
            </div>
        @endif

        <div class="footer">Thank you for shopping with {{ config('app.name') }}!</div>
    </div>
</body>
</html>
