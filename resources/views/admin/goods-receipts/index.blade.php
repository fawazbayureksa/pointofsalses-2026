@extends('layouts.admin')

@section('title', 'Goods Receiving & Supplier Returns')
@section('page-title', 'Goods Receiving (Penerimaan Barang) & Retur Supplier')

@section('content')
    @php
        $poMap = $receivablePurchaseOrders->mapWithKeys(function ($po) {
            return [
                $po->id => [
                    'id' => $po->id,
                    'po_number' => $po->po_number,
                    'supplier_name' => $po->supplier?->name,
                    'outlet_name' => $po->outlet?->name,
                    'items' => $po->items->map(fn($i) => [
                        'id' => $i->id,
                        'product_name' => $i->product_name,
                        'purchase_unit' => $i->purchase_unit,
                        'conversion_factor' => $i->conversion_factor,
                        'quantity_ordered' => (float) $i->quantity_ordered,
                        'quantity_received' => (float) $i->quantity_received,
                        'remaining' => max(0, (float) $i->quantity_ordered - (float) $i->quantity_received),
                    ])->values(),
                ],
            ];
        });
    @endphp

    <div class="space-y-6" x-data="{
        receiveOpen: {{ request('po_id') ? 'true' : 'false' }},
        returnOpen: false,
        selectedPoId: '{{ request('po_id', $receivablePurchaseOrders->first()?->id ?? '') }}',
        pos: @js($poMap)
    }">
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-sm text-green-800">
                <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
            </div>
        @endif

        <x-admin-card title="Riwayat Penerimaan Barang (Goods Receipts)">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <form method="GET" action="{{ route('admin.goods-receipts.index') }}" class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. GR / Surat Jalan / PO..."
                        class="px-3 py-2 text-sm border rounded-lg w-64">
                    <x-admin-button type="submit" variant="primary" icon="fa-solid fa-magnifying-glass">Cari</x-admin-button>
                </form>

                <div class="flex items-center gap-2">
                    <button type="button" @click="returnOpen = true"
                        class="px-3 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100">
                        <i class="fa-solid fa-rotate-left mr-1"></i> Catat Retur ke Supplier
                    </button>
                    <x-admin-button variant="primary" icon="fa-solid fa-dolly" @click="receiveOpen = true">
                        Catat Penerimaan Barang (GR)
                    </x-admin-button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No. GR</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Ref. PO</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Supplier &amp; Outlet</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Surat Jalan</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Tgl Terima</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Ringkasan Barang Masuk</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Ketepatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($goodsReceipts as $gr)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono font-bold text-emerald-700">{{ $gr->gr_number }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-blue-600">
                                    <a href="{{ route('admin.purchase-orders.show', $gr->purchase_order_id) }}" class="hover:underline">
                                        {{ $gr->purchaseOrder?->po_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $gr->supplier?->name }}</div>
                                    <div class="text-xs text-gray-500">Outlet: {{ $gr->outlet?->name }}</div>
                                </td>
                                <td class="px-4 py-3 text-xs">{{ $gr->supplier_delivery_note ?: '-' }}</td>
                                <td class="px-4 py-3 text-center text-xs">{{ $gr->received_date?->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-xs">
                                    @foreach ($gr->items as $item)
                                        <div>
                                            <strong>{{ $item->product?->name }}</strong>:
                                            <span class="text-emerald-700 font-semibold">+{{ $item->stock_added }} stok</span>
                                            @if ($item->quantity_damaged > 0)
                                                <span class="text-red-600">(Rusak: {{ $item->quantity_damaged }})</span>
                                            @endif
                                            @if ($item->batch_number)
                                                <span class="text-gray-400">[Batch: {{ $item->batch_number }}]</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($gr->is_late)
                                        <span class="px-2 py-0.5 text-xs font-semibold bg-amber-100 text-amber-800 rounded-full">Terlambat</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full">Tepat Waktu</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada dokumen penerimaan barang (Goods Receipt).</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin-card>

        {{-- Supplier Returns Table --}}
        <x-admin-card title="Riwayat Retur Barang ke Supplier (Supplier Returns)">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">No. Retur</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Supplier &amp; Outlet</th>
                            <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500">Tanggal</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Resolusi</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500">Nilai Retur</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($supplierReturns as $ret)
                            <tr>
                                <td class="px-4 py-2 font-mono font-bold text-red-700">{{ $ret->return_number }}</td>
                                <td class="px-4 py-2">{{ $ret->supplier?->name }} ({{ $ret->outlet?->name }})</td>
                                <td class="px-4 py-2 text-center text-xs">{{ $ret->return_date?->format('d M Y') }}</td>
                                <td class="px-4 py-2 text-xs">{{ $ret->resolution_label }}</td>
                                <td class="px-4 py-2 text-right font-semibold text-red-600">Rp {{ number_format($ret->total_refund_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-2 text-xs text-gray-600">{{ $ret->reason ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-4 text-center text-gray-400">Belum ada retur barang ke supplier.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin-card>

        {{-- Modal Receive Goods --}}
        <div x-show="receiveOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.away="receiveOpen = false" class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto p-6">
                <div class="flex items-center justify-between border-b pb-3 mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Catat Penerimaan Barang (Goods Receiving)</h3>
                    <button @click="receiveOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
                </div>

                @if ($receivablePurchaseOrders->isEmpty())
                    <p class="text-sm text-gray-600 py-4">Tidak ada Purchase Order berstatus Approved / Ordered / Partially Received.</p>
                @else
                    <form method="POST" action="{{ route('admin.goods-receipts.store') }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Purchase Order *</label>
                                <select name="purchase_order_id" x-model="selectedPoId" required class="w-full px-3 py-2 text-sm border rounded-lg">
                                    @foreach ($receivablePurchaseOrders as $po)
                                        <option value="{{ $po->id }}">{{ $po->po_number }} - {{ $po->supplier?->name }} ({{ $po->outlet?->name }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Diterima *</label>
                                <input type="date" name="received_date" value="{{ now()->toDateString() }}" required class="w-full px-3 py-2 text-sm border rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">No. Surat Jalan Supplier</label>
                                <input type="text" name="supplier_delivery_note" placeholder="SJ-2026-001" class="w-full px-3 py-2 text-sm border rounded-lg">
                            </div>
                        </div>

                        <template x-if="selectedPoId && pos[selectedPoId]">
                            <div class="border rounded-lg p-3 bg-gray-50 space-y-2">
                                <div class="text-xs font-bold text-gray-700 uppercase">Item Barang pada PO (Stok hanya bertambah sejumlah Qty Valid Diterima)</div>
                                <template x-for="(item, idx) in pos[selectedPoId].items" :key="item.id">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-center bg-white p-2 rounded border text-xs">
                                        <input type="hidden" :name="'items[' + idx + '][purchase_order_item_id]'" :value="item.id">
                                        <div class="md:col-span-3">
                                            <div class="font-bold text-gray-900" x-text="item.product_name"></div>
                                            <div class="text-gray-500" x-text="'Dipesan: ' + item.quantity_ordered + ' ' + item.purchase_unit + ' (Sisa: ' + item.remaining + ')'"></div>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] text-emerald-700 font-semibold">Qty Valid Diterima *</label>
                                            <input type="number" :name="'items[' + idx + '][quantity_received]'" :value="item.remaining" min="0" step="any" required class="w-full px-2 py-1 border rounded">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] text-red-600 font-semibold">Qty Rusak/Kurang</label>
                                            <input type="number" :name="'items[' + idx + '][quantity_damaged]'" value="0" min="0" step="any" class="w-full px-2 py-1 border rounded">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] text-gray-500">No. Batch / Lot</label>
                                            <input type="text" :name="'items[' + idx + '][batch_number]'" placeholder="BATCH-01" class="w-full px-2 py-1 border rounded">
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="block text-[10px] text-gray-500">Expired Date</label>
                                            <input type="date" :name="'items[' + idx + '][expiry_date]'" class="w-full px-2 py-1 border rounded">
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Penerimaan</label>
                            <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm border rounded-lg"></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-3 border-t">
                            <button type="button" @click="receiveOpen = false" class="px-4 py-2 text-sm bg-gray-100 rounded-lg">Batal</button>
                            <x-admin-button type="submit" variant="primary" icon="fa-solid fa-check">
                                Simpan Penerimaan &amp; Update Stok
                            </x-admin-button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        {{-- Modal Supplier Return --}}
        <div x-show="returnOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.away="returnOpen = false" class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6">
                <div class="flex items-center justify-between border-b pb-3 mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Catat Retur Barang ke Supplier</h3>
                    <button @click="returnOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form method="POST" action="{{ route('admin.supplier-returns.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Supplier *</label>
                            <select name="supplier_id" required class="w-full px-3 py-2 text-sm border rounded-lg">
                                @foreach ($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Outlet Asal Stok *</label>
                            <select name="outlet_id" required class="w-full px-3 py-2 text-sm border rounded-lg">
                                @foreach ($outlets as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Potong Tagihan Invoice (Opsional)</label>
                            <select name="supplier_invoice_id" class="w-full px-3 py-2 text-sm border rounded-lg">
                                <option value="">-- Tanpa Invoice --</option>
                                @foreach ($invoices as $inv)
                                    <option value="{{ $inv->id }}">{{ $inv->invoice_number }} (Sisa: Rp {{ number_format($inv->outstanding_balance, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Tipe Resolusi *</label>
                            <select name="resolution_type" required class="w-full px-3 py-2 text-sm border rounded-lg">
                                <option value="deduct_payable">Potong Hutang Invoice (Credit Note)</option>
                                <option value="replacement">Tukar Guling Barang Baru</option>
                                <option value="cash_refund">Refund Tunai / Transfer</option>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="return_date" value="{{ now()->toDateString() }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 p-3 bg-gray-50 rounded-lg border">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Produk *</label>
                            <select name="items[0][product_id]" required class="w-full px-2 py-1.5 text-sm border rounded">
                                @foreach ($products as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Qty Retur *</label>
                            <input type="number" name="items[0][quantity]" value="1" min="0.001" step="any" required class="w-full px-2 py-1.5 text-sm border rounded">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Harga Beli/Unit (Rp) *</label>
                            <input type="number" name="items[0][unit_cost]" value="10000" min="0" step="0.01" required class="w-full px-2 py-1.5 text-sm border rounded">
                            <input type="hidden" name="items[0][deduct_stock]" value="1">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Alasan Retur</label>
                        <input type="text" name="reason" placeholder="Kemasan rusak / barang mendekati expired" class="w-full px-3 py-2 text-sm border rounded-lg">
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button type="button" @click="returnOpen = false" class="px-4 py-2 text-sm bg-gray-100 rounded-lg">Batal</button>
                        <x-admin-button type="submit" variant="primary" icon="fa-solid fa-rotate-left">Simpan Retur</x-admin-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
