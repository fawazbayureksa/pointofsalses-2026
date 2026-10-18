@extends('layouts.admin')

@section('title', 'Supplier Invoices & Payables')
@section('page-title', 'Supplier Invoices & Accounts Payable (Hutang Pembelian)')

@section('content')
    <div class="space-y-6" x-data="{ createInvoiceOpen: false, payModalOpen: false, activeInvoice: {} }">
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

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl border p-5 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Total Sisa Hutang (Payables)</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalPayable, 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
                    <i class="fa-solid fa-file-invoice-dollar text-xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-xl border p-5 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-red-500 uppercase">Hutang Jatuh Tempo (Overdue)</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">Rp {{ number_format($overduePayable, 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                    <i class="fa-solid fa-clock text-xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-xl border p-5 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-emerald-600 uppercase">Dibayar Bulan Ini</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($paidThisMonth, 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-circle-check text-xl"></i>
                </div>
            </div>
        </div>

        <x-admin-card title="Daftar Tagihan Supplier (Supplier Invoices)">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <form method="GET" action="{{ route('admin.supplier-invoices.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Invoice / Supplier..."
                        class="px-3 py-2 text-sm border rounded-lg w-60">
                    <select name="status" class="px-3 py-2 text-sm border rounded-lg">
                        <option value="">Semua Status</option>
                        <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partially_paid" {{ request('status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                    <x-admin-button type="submit" variant="primary" icon="fa-solid fa-filter">Filter</x-admin-button>
                </form>

                <x-admin-button variant="primary" icon="fa-solid fa-plus" @click="createInvoiceOpen = true">
                    Catat Tagihan Supplier Baru
                </x-admin-button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No. Invoice</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Supplier &amp; Ref PO/GR</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Jatuh Tempo</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total Tagihan</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Sisa Hutang</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">3-Way Match</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($invoices as $inv)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-mono font-bold text-blue-700">{{ $inv->invoice_number }}</div>
                                    <div class="text-xs text-gray-500">Ref: {{ $inv->supplier_invoice_number ?: '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $inv->supplier?->name }}</div>
                                    <div class="text-xs text-gray-500">
                                        PO: {{ $inv->purchaseOrder?->po_number ?? '-' }} | GR: {{ $inv->goodsReceipt?->gr_number ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center text-xs">
                                    <div>{{ $inv->due_date?->format('d M Y') }}</div>
                                    @if ($inv->isOverdue())
                                        <span class="text-[10px] font-bold text-red-600">Lewat Jatuh Tempo!</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-bold {{ $inv->outstanding_balance > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                    Rp {{ number_format($inv->outstanding_balance, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($inv->matching_status === 'matched')
                                        <span class="px-2 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full">Matched</span>
                                    @elseif ($inv->matching_status === 'discrepancy')
                                        <span class="px-2 py-0.5 text-xs font-semibold bg-amber-100 text-amber-800 rounded-full">Discrepancy</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded-full">Manual</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $inv->status_badge_class }}">
                                        {{ $inv->effective_status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if ($inv->outstanding_balance > 0)
                                        <button type="button"
                                            @click="activeInvoice = @js($inv); payModalOpen = true"
                                            class="px-2.5 py-1 text-xs font-semibold bg-emerald-600 text-white rounded hover:bg-emerald-700">
                                            Bayar
                                        </button>
                                    @else
                                        <span class="text-xs text-emerald-600 font-semibold"><i class="fa-solid fa-check"></i> Lunas</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-400">Belum ada tagihan supplier.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin-card>

        {{-- Create Invoice Modal --}}
        <div x-show="createInvoiceOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.away="createInvoiceOpen = false" class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6">
                <div class="flex items-center justify-between border-b pb-3 mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Catat Tagihan Supplier (Supplier Invoice)</h3>
                    <button @click="createInvoiceOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form method="POST" action="{{ route('admin.supplier-invoices.store') }}" class="space-y-4">
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
                            <label class="block text-xs font-semibold text-gray-700 mb-1">No. Faktur dari Supplier</label>
                            <input type="text" name="supplier_invoice_number" placeholder="INV/SUP/2026/001" class="w-full px-3 py-2 text-sm border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Referensi PO (Untuk 3-Way Matching)</label>
                            <select name="purchase_order_id" class="w-full px-3 py-2 text-sm border rounded-lg">
                                <option value="">-- Opsional: Pilih PO --</option>
                                @foreach ($purchaseOrders as $po)
                                    <option value="{{ $po->id }}">{{ $po->po_number }} (Total PO: Rp {{ number_format($po->total_amount, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Referensi Goods Receipt (GR)</label>
                            <select name="goods_receipt_id" class="w-full px-3 py-2 text-sm border rounded-lg">
                                <option value="">-- Opsional: Pilih GR --</option>
                                @foreach ($goodsReceipts as $gr)
                                    <option value="{{ $gr->id }}">{{ $gr->gr_number }} (Nilai Barang Diterima: Rp {{ number_format($gr->total_received_value, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Invoice *</label>
                            <input type="date" name="invoice_date" value="{{ now()->toDateString() }}" required class="w-full px-3 py-2 text-sm border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Termin / Jatuh Tempo (Hari)</label>
                            <input type="number" name="payment_terms" value="14" min="0" class="w-full px-3 py-2 text-sm border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nominal Subtotal Barang (Rp) *</label>
                            <input type="number" name="subtotal" required min="0" step="0.01" placeholder="900000" class="w-full px-3 py-2 text-sm border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nominal Pajak PPN (Rp)</label>
                            <input type="number" name="tax_amount" value="0" min="0" step="0.01" class="w-full px-3 py-2 text-sm border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Diskon Tagihan (Rp)</label>
                            <input type="number" name="discount_amount" value="0" min="0" step="0.01" class="w-full px-3 py-2 text-sm border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Biaya Pengiriman (Rp)</label>
                            <input type="number" name="shipping_cost" value="0" min="0" step="0.01" class="w-full px-3 py-2 text-sm border rounded-lg">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button type="button" @click="createInvoiceOpen = false" class="px-4 py-2 text-sm bg-gray-100 rounded-lg">Batal</button>
                        <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">Simpan Tagihan</x-admin-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Quick Pay Modal --}}
        <div x-show="payModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.away="payModalOpen = false" class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
                <div class="flex items-center justify-between border-b pb-3 mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Bayar Tagihan <span x-text="activeInvoice.invoice_number"></span></h3>
                    <button @click="payModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form method="POST" action="{{ route('admin.supplier-payments.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="supplier_invoice_id" :value="activeInvoice.id">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Pembayaran *</label>
                        <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required class="w-full px-3 py-2 text-sm border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nominal Pembayaran (Penuh / Sebagian) *</label>
                        <input type="number" name="amount" :value="activeInvoice.outstanding_balance" min="1" step="0.01" required class="w-full px-3 py-2 text-sm border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Metode Pembayaran *</label>
                        <select name="payment_method" required class="w-full px-3 py-2 text-sm border rounded-lg">
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash / Tunai</option>
                            <option value="giro">Giro / Cek</option>
                            <option value="e_wallet">E-Wallet / QRIS</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">No. Referensi / Bukti Transfer</label>
                        <input type="text" name="reference_number" placeholder="TRF-BCA-001" class="w-full px-3 py-2 text-sm border rounded-lg">
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button type="button" @click="payModalOpen = false" class="px-4 py-2 text-sm bg-gray-100 rounded-lg">Batal</button>
                        <x-admin-button type="submit" variant="primary" icon="fa-solid fa-check">Proses Pembayaran</x-admin-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
