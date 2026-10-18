@extends('layouts.admin')

@section('title', 'Suppliers Directory')
@section('page-title', 'Supplier Directory')

@section('content')
    <div class="space-y-6" x-data="{
        createOpen: false,
        editOpen: false,
        editing: {}
    }">
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <i class="fa-solid fa-circle-exclamation text-red-400 mt-0.5"></i>
                    <div class="ml-3 text-sm text-red-700">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-center">
                <i class="fa-solid fa-circle-check text-green-500 mr-3"></i>
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        <x-admin-card title="Master Suppliers (Global per Tenant)">
            <div class="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <form method="GET" action="{{ route('admin.suppliers.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari kode, nama perusahaan, kontak..."
                        class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 w-64">
                    <select name="status" class="px-3 py-2 text-sm border border-gray-300 rounded-lg">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <x-admin-button type="submit" variant="primary" icon="fa-solid fa-magnifying-glass">
                        Filter
                    </x-admin-button>
                </form>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.supplier-products.index') }}"
                        class="px-3 py-2 text-sm font-medium text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100">
                        <i class="fa-solid fa-boxes-packing mr-1"></i> Supplier Product Catalog
                    </a>
                    <x-admin-button variant="primary" icon="fa-solid fa-plus" @click="createOpen = true">
                        Tambah Supplier
                    </x-admin-button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kode</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama &amp; Perusahaan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Contact Person</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">NPWP / Legal</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Produk</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Termin</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($suppliers as $supplier)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-xs font-bold text-blue-700">
                                    <a href="{{ route('admin.suppliers.show', $supplier) }}" class="hover:underline">
                                        {{ $supplier->code }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-900">{{ $supplier->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $supplier->company_name ?? $supplier->city ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-800">{{ $supplier->contact_person ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $supplier->phone ?? $supplier->email ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    <div>NPWP: {{ $supplier->npwp ?: '-' }}</div>
                                    @if ($supplier->bank_name)
                                        <div class="text-gray-400">{{ $supplier->bank_name }} - {{ $supplier->bank_account_number }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 text-xs font-semibold bg-blue-50 text-blue-700 rounded-full">
                                        {{ $supplier->supplier_products_count }} SKU
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-xs text-gray-700">
                                    {{ $supplier->default_payment_terms > 0 ? $supplier->default_payment_terms . ' Hari' : 'COD' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($supplier->is_active)
                                        <span class="px-2 py-1 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full">Active</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <a href="{{ route('admin.suppliers.show', $supplier) }}"
                                        class="text-blue-600 hover:text-blue-900" title="Detail & Produk">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <button type="button"
                                        @click="editing = @js($supplier); editOpen = true"
                                        class="text-emerald-600 hover:text-emerald-900" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.suppliers.destroy', $supplier) }}" class="inline"
                                        onsubmit="return confirm('Hapus supplier {{ $supplier->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                    Belum ada data supplier. Klik <strong>Tambah Supplier</strong> untuk membuat master pemasok pertama.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($suppliers->hasPages())
                <div class="mt-4">{{ $suppliers->links() }}</div>
            @endif
        </x-admin-card>

        {{-- Create Supplier Modal --}}
        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.away="createOpen = false" class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6">
                <div class="flex items-center justify-between border-b pb-3 mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Tambah Supplier Baru</h3>
                    <button @click="createOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form method="POST" action="{{ route('admin.suppliers.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Kode Supplier (Otomatis jika kosong)</label>
                            <input type="text" name="code" placeholder="Otomatis: SUP-0001" class="w-full px-3 py-2 border rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Supplier *</label>
                            <input type="text" name="name" required placeholder="PT Sumber Makmur" class="w-full px-3 py-2 border rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Perusahaan Legal</label>
                            <input type="text" name="company_name" placeholder="PT Sumber Makmur Distribusi" class="w-full px-3 py-2 border rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Contact Person</label>
                            <input type="text" name="contact_person" placeholder="Budi Santoso" class="w-full px-3 py-2 border rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Telepon / WhatsApp</label>
                            <input type="text" name="phone" placeholder="08123456789" class="w-full px-3 py-2 border rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" placeholder="sales@supplier.co.id" class="w-full px-3 py-2 border rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">NPWP</label>
                            <input type="text" name="npwp" placeholder="01.234.567.8-901.000" class="w-full px-3 py-2 border rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Termin Pembayaran Default (Hari)</label>
                            <input type="number" name="default_payment_terms" value="14" min="0" class="w-full px-3 py-2 border rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Bank</label>
                            <input type="text" name="bank_name" placeholder="BCA / Mandiri" class="w-full px-3 py-2 border rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">No. Rekening</label>
                            <input type="text" name="bank_account_number" placeholder="1234567890" class="w-full px-3 py-2 border rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Kota</label>
                            <input type="text" name="city" placeholder="Jakarta" class="w-full px-3 py-2 border rounded-lg text-sm">
                        </div>
                        <div class="flex items-center pt-5">
                            <input type="hidden" name="is_active" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-blue-600 rounded">
                                <span class="ml-2 text-sm text-gray-700">Supplier Aktif</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat Lengkap</label>
                        <textarea name="address" rows="2" class="w-full px-3 py-2 border rounded-lg text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Supplier</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 border rounded-lg text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button type="button" @click="createOpen = false" class="px-4 py-2 text-sm bg-gray-100 rounded-lg">Batal</button>
                        <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">Simpan Supplier</x-admin-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit Supplier Modal --}}
        <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.away="editOpen = false" class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6">
                <div class="flex items-center justify-between border-b pb-3 mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Edit Supplier (<span x-text="editing.code"></span>)</h3>
                    <button @click="editOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form method="POST" :action="'{{ url('/admin/suppliers') }}/' + editing.id" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Supplier *</label>
                            <input type="text" name="name" x-model="editing.name" required class="w-full px-3 py-2 border rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Perusahaan Legal</label>
                            <input type="text" name="company_name" x-model="editing.company_name" class="w-full px-3 py-2 border rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Contact Person</label>
                            <input type="text" name="contact_person" x-model="editing.contact_person" class="w-full px-3 py-2 border rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Telepon</label>
                            <input type="text" name="phone" x-model="editing.phone" class="w-full px-3 py-2 border rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" x-model="editing.email" class="w-full px-3 py-2 border rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">NPWP</label>
                            <input type="text" name="npwp" x-model="editing.npwp" class="w-full px-3 py-2 border rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Termin Pembayaran Default (Hari)</label>
                            <input type="number" name="default_payment_terms" x-model="editing.default_payment_terms" min="0" class="w-full px-3 py-2 border rounded-lg text-sm">
                        </div>
                        <div class="flex items-center pt-5">
                            <input type="hidden" name="is_active" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" :checked="editing.is_active" class="w-4 h-4 text-blue-600 rounded">
                                <span class="ml-2 text-sm text-gray-700">Supplier Aktif</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat</label>
                        <textarea name="address" x-model="editing.address" rows="2" class="w-full px-3 py-2 border rounded-lg text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan</label>
                        <textarea name="notes" x-model="editing.notes" rows="2" class="w-full px-3 py-2 border rounded-lg text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button type="button" @click="editOpen = false" class="px-4 py-2 text-sm bg-gray-100 rounded-lg">Batal</button>
                        <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">Perbarui Supplier</x-admin-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
