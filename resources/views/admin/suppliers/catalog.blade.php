@extends('layouts.admin')

@section('title', 'Supplier Products Mapping')
@section('page-title', 'Supplier Product Mapping & Price Catalog')

@section('content')
    <div class="space-y-6">
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-sm text-green-800">
                <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
            </div>
        @endif

        <x-admin-card title="Hubungkan Produk dengan Supplier (Multi-Supplier Catalog)">
            <form method="POST" action="{{ route('admin.supplier-products.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Supplier *</label>
                        <select name="supplier_id" required class="w-full px-3 py-2 text-sm border rounded-lg">
                            <option value="">-- Pilih Supplier --</option>
                            @foreach ($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->code }} - {{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Produk *</label>
                        <select name="product_id" required class="w-full px-3 py-2 text-sm border rounded-lg">
                            <option value="">-- Pilih Produk --</option>
                            @foreach ($products as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->name }} (Harga Jual: Rp {{ number_format($prod->price, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Harga Beli Supplier (Rp) *</label>
                        <input type="number" name="purchase_price" required min="0" step="0.01" placeholder="10000" class="w-full px-3 py-2 text-sm border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">SKU dari Supplier</label>
                        <input type="text" name="supplier_sku" placeholder="SUP-SKU-01" class="w-full px-3 py-2 text-sm border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Satuan Pembelian (pcs/karton)</label>
                        <input type="text" name="purchase_unit" value="pcs" class="w-full px-3 py-2 text-sm border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Konversi ke Stok (1 satuan beli = X pcs)</label>
                        <input type="number" name="conversion_factor" value="1" min="0.001" step="any" class="w-full px-3 py-2 text-sm border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Minimum Order Qty (MOQ)</label>
                        <input type="number" name="min_order_qty" value="1" min="0.001" step="any" class="w-full px-3 py-2 text-sm border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Lead Time Pengiriman (Hari)</label>
                        <input type="number" name="lead_time_days" value="2" min="0" class="w-full px-3 py-2 text-sm border rounded-lg">
                    </div>
                </div>
                <div class="flex items-center justify-between pt-2">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_primary" value="1" class="w-4 h-4 text-blue-600 rounded">
                        <span class="ml-2 text-sm font-medium text-gray-700">Jadikan sebagai Supplier Utama (Primary Supplier) untuk produk ini</span>
                    </label>
                    <x-admin-button type="submit" variant="primary" icon="fa-solid fa-link">
                        Simpan Mapping Produk
                    </x-admin-button>
                </div>
            </form>
        </x-admin-card>

        <x-admin-card title="Daftar Katalog Produk Supplier">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Produk</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Supplier</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Harga Beli</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Satuan Beli</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">MOQ</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Lead Time</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Prioritas</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($mappings as $map)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $map->product?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs text-blue-600">{{ $map->supplier?->code }}</span>
                                    {{ $map->supplier?->name }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($map->purchase_price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center text-xs">{{ $map->purchase_unit }} ({{ $map->conversion_factor }}x)</td>
                                <td class="px-4 py-3 text-center">{{ $map->min_order_qty }}</td>
                                <td class="px-4 py-3 text-center">{{ $map->lead_time_days }} hari</td>
                                <td class="px-4 py-3 text-center">
                                    @if ($map->is_primary)
                                        <span class="px-2 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full">Utama</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded-full">Alternatif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.supplier-products.destroy', $map) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-gray-400">Belum ada mapping produk supplier.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin-card>
    </div>
@endsection
