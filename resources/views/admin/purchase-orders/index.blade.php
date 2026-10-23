@extends('layouts.admin')

@section('title', 'Purchase Orders')
@section('page-title', 'Purchase Orders (PO)')

@section('content')
    <div class="space-y-6" x-data="{
        createPoOpen: {{ request('supplier_id') ? 'true' : 'false' }},
        selectedSupplierId: '{{ request('supplier_id', '') }}',
        poItems: [{ product_id: '', quantity_ordered: 10, unit_price: 0, discount_amount: 0, purchase_unit: 'pcs', conversion_factor: 1 }]
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

        {{-- Reorder Suggestions Banner --}}
        @if ($reorderSuggestions->isNotEmpty())
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-bold text-amber-900">
                        <i class="fa-solid fa-bell mr-1.5"></i> Automatic Reorder Suggestion ({{ $reorderSuggestions->count() }} Produk Stok Menipis)
                    </h4>
                    <span class="text-xs text-amber-700">Berdasarkan Low Stock Threshold, MOQ &amp; Lead Time Supplier</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                    @foreach ($reorderSuggestions->take(3) as $sug)
                        <div class="bg-white p-2.5 rounded-lg border border-amber-200 text-xs flex items-center justify-between">
                            <div>
                                <div class="font-bold text-gray-900">{{ $sug['product']->name }} ({{ $sug['outlet']->name }})</div>
                                <div class="text-gray-500">Stok: <span class="text-red-600 font-semibold">{{ $sug['current_stock'] }}</span> (Min: {{ $sug['low_stock_threshold'] }})</div>
                                <div class="text-indigo-700">Supplier: {{ $sug['preferred_supplier']?->name ?? 'Belum dimapping' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-emerald-700">Saran: {{ $sug['suggested_qty'] }} {{ $sug['purchase_unit'] }}</div>
                                <div class="text-gray-500">~Rp {{ number_format($sug['estimated_total_cost'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <x-admin-card title="Daftar Purchase Orders (Per Outlet)">
            <div class="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <form method="GET" action="{{ route('admin.purchase-orders.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. PO / Supplier..."
                        class="px-3 py-2 text-sm border rounded-lg w-56">
                    <select name="status" class="px-3 py-2 text-sm border rounded-lg">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="ordered" {{ request('status') === 'ordered' ? 'selected' : '' }}>Ordered</option>
                        <option value="partially_received" {{ request('status') === 'partially_received' ? 'selected' : '' }}>Partially Received</option>
                        <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <select name="outlet_id" class="px-3 py-2 text-sm border rounded-lg">
                        <option value="">Semua Outlet</option>
                        @foreach ($outlets as $outlet)
                            <option value="{{ $outlet->id }}" {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>{{ $outlet->name }}</option>
                        @endforeach
                    </select>
                    <x-admin-button type="submit" variant="primary" icon="fa-solid fa-filter">Filter</x-admin-button>
                </form>

                <x-admin-button variant="primary" icon="fa-solid fa-plus" @click="createPoOpen = true">
                    Buat Purchase Order Baru
                </x-admin-button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No. PO</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Supplier</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Outlet Tujuan</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Tgl Pesan</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total Nilai</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($purchaseOrders as $po)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono font-bold text-blue-700">
                                    <a href="{{ route('admin.purchase-orders.show', $po) }}" class="hover:underline">{{ $po->po_number }}</a>
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $po->supplier?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $po->outlet?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-center text-xs">{{ $po->order_date?->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $po->status_badge_class }}">
                                        {{ $po->status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <a href="{{ route('admin.purchase-orders.show', $po) }}" class="text-blue-600 hover:text-blue-900" title="Detail & Workflow">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.purchase-orders.print', $po) }}" target="_blank" class="text-gray-600 hover:text-gray-900" title="Cetak PO">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada Purchase Order.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($purchaseOrders->hasPages())
                <div class="mt-4">{{ $purchaseOrders->links() }}</div>
            @endif
        </x-admin-card>

        {{-- Create PO Modal --}}
        <div x-show="createPoOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.away="createPoOpen = false" class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[92vh] overflow-y-auto p-6">
                <div class="flex items-center justify-between border-b pb-3 mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Buat Purchase Order (PO)</h3>
                    <button @click="createPoOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <form method="POST" action="{{ route('admin.purchase-orders.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Supplier *</label>
                            <select name="supplier_id" x-model="selectedSupplierId" required class="w-full px-3 py-2 text-sm border rounded-lg">
                                <option value="">-- Pilih Supplier --</option>
                                @foreach ($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->code }} - {{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Outlet Penerima *</label>
                            <select name="outlet_id" required class="w-full px-3 py-2 text-sm border rounded-lg">
                                @foreach ($outlets as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal PO *</label>
                            <input type="date" name="order_date" value="{{ now()->toDateString() }}" required class="w-full px-3 py-2 text-sm border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Estimasi Tiba</label>
                            <input type="date" name="expected_date" value="{{ now()->addDays(3)->toDateString() }}" class="w-full px-3 py-2 text-sm border rounded-lg">
                        </div>
                    </div>

                    <div class="border rounded-lg p-3 bg-gray-50">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-gray-700 uppercase">Daftar Produk Dipesan</span>
                            <button type="button"
                                @click="poItems.push({ product_id: '', quantity_ordered: 1, unit_price: 0, discount_amount: 0, purchase_unit: 'pcs', conversion_factor: 1 })"
                                class="px-2.5 py-1 text-xs font-semibold bg-blue-600 text-white rounded hover:bg-blue-700">
                                + Tambah Baris Produk
                            </button>
                        </div>

                        <div class="space-y-2">
                            <template x-for="(item, idx) in poItems" :key="idx">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-end bg-white p-2 rounded border">
                                    <div class="md:col-span-4">
                                        <label class="block text-[11px] text-gray-500">Produk *</label>
                                        <select :name="'items[' + idx + '][product_id]'" x-model="item.product_id" required class="w-full px-2 py-1.5 text-sm border rounded">
                                            <option value="">-- Pilih Produk --</option>
                                            @foreach ($products as $prod)
                                                <option value="{{ $prod->id }}">{{ $prod->name }} (Cost: Rp {{ number_format($prod->cost_price ?? 0, 0, ',', '.') }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] text-gray-500">Qty Pesan *</label>
                                        <input type="number" :name="'items[' + idx + '][quantity_ordered]'" x-model="item.quantity_ordered" min="0.001" step="any" required class="w-full px-2 py-1.5 text-sm border rounded">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] text-gray-500">Harga Beli/Unit *</label>
                                        <input type="number" :name="'items[' + idx + '][unit_price]'" x-model="item.unit_price" min="0" step="0.01" required class="w-full px-2 py-1.5 text-sm border rounded">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] text-gray-500">Satuan &amp; Isi</label>
                                        <div class="flex gap-1">
                                            <input type="text" :name="'items[' + idx + '][purchase_unit]'" x-model="item.purchase_unit" class="w-1/2 px-2 py-1.5 text-xs border rounded">
                                            <input type="number" :name="'items[' + idx + '][conversion_factor]'" x-model="item.conversion_factor" min="0.001" step="any" class="w-1/2 px-2 py-1.5 text-xs border rounded">
                                        </div>
                                    </div>
                                    <div class="md:col-span-2 flex items-center justify-between">
                                        <span class="text-xs font-semibold text-gray-700" x-text="'Rp ' + (Number(item.quantity_ordered || 0) * Number(item.unit_price || 0)).toLocaleString('id-ID')"></span>
                                        <button type="button" x-show="poItems.length > 1" @click="poItems.splice(idx, 1)" class="text-red-500 hover:text-red-700">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Diskon PO (Rp)</label>
                            <input type="number" name="discount_amount" value="0" min="0" class="w-full px-3 py-2 text-sm border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Pajak PPN (%)</label>
                            <input type="number" name="tax_rate" value="{{ setting('tax_rate', 11) }}" min="0" max="100" step="0.01" class="w-full px-3 py-2 text-sm border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Biaya Pengiriman / Ongkir (Rp)</label>
                            <input type="number" name="shipping_cost" value="0" min="0" class="w-full px-3 py-2 text-sm border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Termin Pembayaran (Hari)</label>
                            <input type="number" name="payment_terms" value="14" min="0" class="w-full px-3 py-2 text-sm border rounded-lg">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan PO</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm border rounded-lg"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t">
                        <button type="button" @click="createPoOpen = false" class="px-4 py-2 text-sm bg-gray-100 rounded-lg">Batal</button>
                        <button type="submit" name="submit_action" value="draft" class="px-4 py-2 text-sm font-medium bg-gray-700 text-white rounded-lg hover:bg-gray-800">
                            Simpan sebagai Draft
                        </button>
                        <button type="submit" name="submit_action" value="submit" class="px-4 py-2 text-sm font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fa-solid fa-check mr-1"></i> Buat &amp; Proses Approval
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
