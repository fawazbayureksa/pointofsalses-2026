@extends('layouts.admin')

@section('title', 'Supplier Detail - ' . $supplier->name)
@section('page-title', 'Supplier Detail: ' . $supplier->name . ' (' . $supplier->code . ')')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.suppliers.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Daftar Supplier
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.purchase-orders.index', ['supplier_id' => $supplier->id]) }}"
                    class="px-3 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fa-solid fa-file-signature mr-1"></i> Buat / Lihat PO
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-admin-card title="Informasi Supplier">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Kode:</dt>
                        <dd class="font-mono font-bold text-blue-700">{{ $supplier->code }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Perusahaan:</dt>
                        <dd class="font-semibold">{{ $supplier->company_name ?: $supplier->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Contact Person:</dt>
                        <dd>{{ $supplier->contact_person ?: '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Telepon / Email:</dt>
                        <dd>{{ $supplier->phone ?: '-' }} {{ $supplier->email ? '/ ' . $supplier->email : '' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">NPWP:</dt>
                        <dd>{{ $supplier->npwp ?: '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Termin Default:</dt>
                        <dd>{{ $supplier->default_payment_terms > 0 ? $supplier->default_payment_terms . ' Hari' : 'COD' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Total Hutang Aktif:</dt>
                        <dd class="font-bold text-red-600">Rp {{ number_format($supplier->totalPayableBalance(), 0, ',', '.') }}</dd>
                    </div>
                </dl>
            </x-admin-card>

            <div class="lg:col-span-2">
                <x-admin-card title="Mapping Produk & Harga Beli Supplier">
                    <form method="POST" action="{{ route('admin.supplier-products.store') }}" class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        @csrf
                        <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-2 items-end">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Produk</label>
                                <select name="product_id" required class="w-full px-2 py-1.5 text-sm border rounded">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku ?: 'No SKU' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Harga Beli</label>
                                <input type="number" name="purchase_price" required min="0" step="0.01" placeholder="10000" class="w-full px-2 py-1.5 text-sm border rounded">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Satuan &amp; Isi</label>
                                <div class="flex gap-1">
                                    <input type="text" name="purchase_unit" value="pcs" placeholder="box" class="w-1/2 px-2 py-1.5 text-sm border rounded">
                                    <input type="number" name="conversion_factor" value="1" min="0.001" step="any" title="Konversi ke unit stok" class="w-1/2 px-2 py-1.5 text-sm border rounded">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">MOQ / Lead(hr)</label>
                                <div class="flex gap-1">
                                    <input type="number" name="min_order_qty" value="1" min="1" class="w-1/2 px-2 py-1.5 text-sm border rounded">
                                    <input type="number" name="lead_time_days" value="2" min="0" class="w-1/2 px-2 py-1.5 text-sm border rounded">
                                </div>
                            </div>
                            <div>
                                <label class="inline-flex items-center text-xs mb-1">
                                    <input type="checkbox" name="is_primary" value="1" class="mr-1"> Utama
                                </label>
                                <button type="submit" class="w-full px-3 py-1.5 text-xs font-semibold bg-blue-600 text-white rounded hover:bg-blue-700">
                                    + Simpan
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Produk</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500">Harga Beli</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500">Satuan (Konversi)</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500">MOQ / Lead Time</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500">Tipe</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($supplier->supplierProducts as $sp)
                                    <tr>
                                        <td class="px-3 py-2 font-medium">{{ $sp->product?->name ?? '-' }}</td>
                                        <td class="px-3 py-2 text-right font-semibold">Rp {{ number_format($sp->purchase_price, 0, ',', '.') }}</td>
                                        <td class="px-3 py-2 text-center text-xs">{{ $sp->purchase_unit }} ({{ $sp->conversion_factor }}x)</td>
                                        <td class="px-3 py-2 text-center text-xs">{{ $sp->min_order_qty }} / {{ $sp->lead_time_days }} hr</td>
                                        <td class="px-3 py-2 text-center">
                                            @if ($sp->is_primary)
                                                <span class="px-2 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full">Utama</span>
                                            @else
                                                <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded-full">Alternatif</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <form method="POST" action="{{ route('admin.supplier-products.destroy', $sp) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3 py-4 text-center text-gray-400">Belum ada produk yang dihubungkan ke supplier ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-admin-card>
            </div>
        </div>
    </div>
@endsection
