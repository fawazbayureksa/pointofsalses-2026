<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt #{{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            background: #fff;
            color: #000;
        }

        .receipt {
            max-width: 320px;
            margin: 20px auto;
            padding: 16px;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .lg {
            font-size: 16px;
        }

        .xl {
            font-size: 20px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .divider-solid {
            border-top: 1px solid #000;
            margin: 8px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 2px 0;
            vertical-align: top;
        }

        td.item-name {
            width: 55%;
        }

        td.item-qty {
            width: 10%;
            text-align: center;
        }

        td.item-price {
            width: 17%;
            text-align: right;
        }

        td.item-sub {
            width: 18%;
            text-align: right;
        }

        .summary-row td:first-child {}

        .summary-row td:last-child {
            text-align: right;
        }

        .total-row td {
            font-size: 15px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 4px;
        }

        .total-row td:last-child {
            text-align: right;
        }

        .footer {
            margin-top: 16px;
            font-size: 11px;
        }

        @media print {
            body {
                margin: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="receipt">
        {{-- Header --}}
        <div class="center">
            <div class="xl bold">{{ $order->outlet->name ?? config('app.name') }}</div>
            @if ($order->outlet?->address)
                <div>{{ $order->outlet->address }}</div>
            @endif
            @if ($order->outlet?->phone)
                <div>{{ $order->outlet->phone }}</div>
            @endif
        </div>

        <div class="divider-solid"></div>

        {{-- Order info --}}
        <table>
            <tr>
                <td>Order #</td>
                <td class="right bold">{{ $order->order_number }}</td>
            </tr>
            <tr>
                <td>Date</td>
                <td class="right">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Cashier</td>
                <td class="right">{{ $order->cashier?->name ?? '-' }}</td>
            </tr>
            @if ($order->customer)
                <tr>
                    <td>Customer</td>
                    <td class="right">{{ $order->customer->name }}</td>
                </tr>
            @endif
        </table>

        <div class="divider"></div>

        {{-- Items --}}
        <table>
            <thead>
                <tr>
                    <td class="item-name bold">Item</td>
                    <td class="item-qty bold">Q</td>
                    <td class="item-price bold">Price</td>
                    <td class="item-sub bold">Sub</td>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td class="item-name">
                            {{ $item->product_name }}
                            @if ($item->discount_amount > 0)
                                <br><small>Disc: -Rp {{ number_format($item->discount_amount, 0, ',', '.') }}</small>
                            @endif
                        </td>
                        <td class="item-qty">{{ rtrim(rtrim(number_format($item->quantity, 3), '0'), '.') }}</td>
                        <td class="item-price">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="item-sub">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        {{-- Summary --}}
        <table>
            <tr class="summary-row">
                <td>Subtotal</td>
                <td class="right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if ($order->discount_amount > 0)
                <tr class="summary-row">
                    <td>Discount{{ $order->discount_type === 'percentage' ? ' (%)' : '' }}</td>
                    <td class="right">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if ($order->tax_amount > 0)
                <tr class="summary-row">
                    <td>Tax</td>
                    <td class="right">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td>TOTAL</td>
                <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        {{-- Payment --}}
        @if ($order->payment)
            <table>
                <tr class="summary-row">
                    <td>Method</td>
                    <td class="right bold">{{ strtoupper($order->payment->payment_method) }}</td>
                </tr>
                <tr class="summary-row">
                    <td>Paid</td>
                    <td class="right">Rp {{ number_format($order->payment->amount, 0, ',', '.') }}</td>
                </tr>
                @if ($order->payment->change_amount > 0)
                    <tr class="summary-row">
                        <td>Change</td>
                        <td class="right">Rp {{ number_format($order->payment->change_amount, 0, ',', '.') }}</td>
                    </tr>
                @endif
            </table>
        @endif

        @if ($order->notes)
            <div class="divider"></div>
            <div><span class="bold">Notes:</span> {{ $order->notes }}</div>
        @endif

        <div class="divider-solid"></div>

        <div class="footer center">
            <div>Thank you for your purchase!</div>
            <div>{{ config('app.name') }}</div>
        </div>
    </div>

    <div class="no-print center" style="margin: 20px auto; max-width: 320px;">
        <button onclick="window.print()"
            style="padding: 8px 24px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">
            🖨 Print Receipt
        </button>
        <a href="{{ route('admin.orders.index') }}" style="margin-left: 12px; color: #6b7280; font-size: 13px;">
            ← Back to Orders
        </a>
    </div>
</body>

</html>
