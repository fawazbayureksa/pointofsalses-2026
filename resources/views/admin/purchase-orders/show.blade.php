@extends('layouts.admin')

@section('title', 'Detail PO ' . $purchaseOrder->po_number)
@section('page-title', 'Purchase Order: ' . $purchaseOrder->po_number)

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('admin.purchase-orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Daftar PO
            </a>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.purchase-orders.print', $purchaseOrder) }}" target="_blank"
                    class="px-3 py-2 text-sm font-medium bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    <i class="fa-solid fa-print mr-1"></i> Cetak PO
                </a>

                @if ($purchaseOrder->status === 'draft')
                    <form method="POST" action="{{ route('admin.purchase-orders.submit-approval', $purchaseOrder) }}">
                        @csrf
                        <button type="submit" class="px-3 py-2 text-sm font-medium bg-amber-500 text-white rounded-lg hover:bg-amber-600">
                            Ajukan Approval
                        </button>
                    </form>
                @endif

                @if (in_array($purchaseOrder->status, ['draft', 'pending_approval']))
                    <form method="POST" action="{{ route('admin.purchase-orders.approve', $purchaseOrder) }}">
                        @csrf
                        <button type="submit" class="px-3 py-2 text-sm font-semibold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            <i class="fa-solid fa-check mr-1"></i> Approve PO
                        </button>
                    </form>
                @endif

                @if (in_array($purchaseOrder->status, ['approved', 'draft']))
                    <form method="POST" action="{{ route('admin.purchase-orders.mark-ordered', $purchaseOrder) }}">
                        @csrf
                        <button type="submit" class="px-3 py-2 text-sm font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fa-solid fa-paper-plane mr-1"></i> Tandai Dikirim (Ordered)
                        </button>
                    </form>
                @endif

                @if ($purchaseOrder->canReceiveGoods())
                    <a href="{{ route('admin.goods-receipts.index', ['po_id' => $purchaseOrder->id]) }}"
                        class="px-3 py-2 text-sm font-semibold bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                        <i class="fa-solid fa-dolly mr-1"></i> Terima Barang (Goods Receiving)
                    </a>
                @endif

                @if (! in_array($purchaseOrder->status, ['received', 'partially_received', 'cancelled']))
                    <form method="POST" action="{{ route('admin.purchase-orders.cancel', $purchaseOrder) }}"
                        onsubmit="return confirm('Batalkan PO ini?')">
                        @csrf
                        <button type="submit" class="px-3 py-2 text-sm font-medium bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100">
                            Batalkan PO
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-admin-card title="Informasi Purchase Order">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Nomor PO:</dt>
                        <dd class="font-mono font-bold text-blue-700">{{ $purchaseOrder->po_number }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Status:</dt>
                        <dd><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $purchaseOrder->status_badge_class }}">{{ $purchaseOrder->status_label }}</span></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Supplier:</dt>
                        <dd class="font-semibold">{{ $purchaseOrder->supplier?->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Outlet Tujuan:</dt>
                        <dd>{{ $purchaseOrder->outlet?->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Tanggal Pesan:</dt>
                        <dd>{{ $purchaseOrder->order_date?->format('d M Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Estimasi Tiba:</dt>
                        <dd>{{ $purchaseOrder->expected_date?->format('d M Y') ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Termin Pembayaran:</dt>
                        <dd>{{ $purchaseOrder->payment_terms > 0 ? $purchaseOrder->payment_terms . ' Hari' : 'COD' }}</dd>
                    </div>
                </dl>
            </x-admin-card>

            <div class="lg:col-span-2">
                <x-admin-card title="Detail Item & Progress Penerimaan (Ordered vs Received)">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Produk</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500">Satuan</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500">Qty Dipesan</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500">Qty Diterima</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500">Harga Satuan</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($purchaseOrder->items as $item)
                                    <tr>
                                        <td class="px-3 py-2 font-medium">{{ $item->product_name }}</td>
                                        <td class="px-3 py-2 text-center text-xs">{{ $item->purchase_unit }} ({{ $item->conversion_factor }}x)</td>
                                        <td class="px-3 py-2 text-center font-semibold">{{ $item->quantity_ordered }}</td>
                                        <td class="px-3 py-2 text-center">
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $item->quantity_received >= $item->quantity_ordered ? 'bg-emerald-100 text-emerald-800' : ($item->quantity_received > 0 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600') }}">
                                                {{ $item->quantity_received }} / {{ $item->quantity_ordered }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="px-3 py-2 text-right font-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 text-sm">
                                <tr>
                                    <td colspan="5" class="px-3 py-1.5 text-right text-gray-500">Subtotal:</td>
                                    <td class="px-3 py-1.5 text-right font-medium">Rp {{ number_format($purchaseOrder->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="px-3 py-1.5 text-right text-gray-500">Diskon:</td>
                                    <td class="px-3 py-1.5 text-right text-red-600">- Rp {{ number_format($purchaseOrder->discount_amount, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="px-3 py-1.5 text-right text-gray-500">Pajak ({{ $purchaseOrder->tax_rate }}%):</td>
                                    <td class="px-3 py-1.5 text-right">Rp {{ number_format($purchaseOrder->tax_amount, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="px-3 py-1.5 text-right text-gray-500">Biaya Pengiriman:</td>
                                    <td class="px-3 py-1.5 text-right">Rp {{ number_format($purchaseOrder->shipping_cost, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-t border-gray-300">
                                    <td colspan="5" class="px-3 py-2 text-right font-bold text-gray-900">Grand Total PO:</td>
                                    <td class="px-3 py-2 text-right font-bold text-blue-700">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </x-admin-card>
            </div>
        </div>
    </div>
@endsection
