<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Purchase Order {{ $purchaseOrder->po_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white p-8 text-gray-900 max-w-4xl mx-auto" onload="window.print()">
    <div class="flex justify-between items-start border-b pb-6 mb-6">
        <div>
            <h1 class="text-2xl font-bold uppercase tracking-wide">Purchase Order</h1>
            <p class="font-mono text-lg text-blue-700 font-semibold">{{ $purchaseOrder->po_number }}</p>
            <p class="text-xs text-gray-500 mt-1">Status: {{ $purchaseOrder->status_label }}</p>
        </div>
        <div class="text-right text-sm">
            <div class="font-bold text-base">{{ $purchaseOrder->outlet?->name ?? 'Main Outlet' }}</div>
            <div class="text-gray-600">{{ $purchaseOrder->outlet?->address }}</div>
            <div class="text-gray-500 mt-1">Tanggal PO: {{ $purchaseOrder->order_date?->format('d M Y') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6 mb-6 text-sm">
        <div class="p-4 bg-gray-50 rounded-lg border">
            <div class="text-xs font-bold uppercase text-gray-400 mb-1">Kepada Supplier:</div>
            <div class="font-bold">{{ $purchaseOrder->supplier?->name }} ({{ $purchaseOrder->supplier?->code }})</div>
            <div>{{ $purchaseOrder->supplier?->company_name }}</div>
            <div>UP: {{ $purchaseOrder->supplier?->contact_person ?? '-' }} ({{ $purchaseOrder->supplier?->phone ?? '-' }})</div>
            <div class="text-xs text-gray-600 mt-1">{{ $purchaseOrder->supplier?->address }}</div>
        </div>
        <div class="p-4 bg-gray-50 rounded-lg border">
            <div class="text-xs font-bold uppercase text-gray-400 mb-1">Informasi Pengiriman &amp; Pembayaran:</div>
            <div>Kirim ke Outlet: <strong>{{ $purchaseOrder->outlet?->name }}</strong></div>
            <div>Estimasi Tiba: {{ $purchaseOrder->expected_date?->format('d M Y') ?? '-' }}</div>
            <div>Termin Pembayaran: {{ $purchaseOrder->payment_terms > 0 ? $purchaseOrder->payment_terms . ' Hari' : 'COD' }}</div>
        </div>
    </div>

    <table class="w-full border-collapse border border-gray-300 text-sm mb-6">
        <thead class="bg-gray-100">
            <tr>
                <th class="border p-2 text-left">No</th>
                <th class="border p-2 text-left">Produk</th>
                <th class="border p-2 text-center">Qty</th>
                <th class="border p-2 text-center">Satuan</th>
                <th class="border p-2 text-right">Harga Satuan</th>
                <th class="border p-2 text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchaseOrder->items as $idx => $item)
                <tr>
                    <td class="border p-2">{{ $idx + 1 }}</td>
                    <td class="border p-2 font-medium">{{ $item->product_name }}</td>
                    <td class="border p-2 text-center">{{ $item->quantity_ordered }}</td>
                    <td class="border p-2 text-center">{{ $item->purchase_unit }}</td>
                    <td class="border p-2 text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="border p-2 text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="border p-2 text-right font-bold">Grand Total</td>
                <td class="border p-2 text-right font-bold">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
