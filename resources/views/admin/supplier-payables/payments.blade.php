@extends('layouts.admin')

@section('title', 'Supplier Payments')
@section('page-title', 'Supplier Payments (Pembayaran Hutang Supplier)')

@section('content')
    <div class="space-y-6">
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-sm text-green-800">
                <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
            </div>
        @endif

        <x-admin-card title="Catat Pembayaran Hutang Supplier">
            <form method="POST" action="{{ route('admin.supplier-payments.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                @csrf
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Tagihan (Invoice) *</label>
                    <select name="supplier_invoice_id" required class="w-full px-3 py-2 text-sm border rounded-lg">
                        <option value="">-- Pilih Invoice Belum Lunas --</option>
                        @foreach ($unpaidInvoices as $inv)
                            <option value="{{ $inv->id }}">
                                {{ $inv->invoice_number }} - {{ $inv->supplier?->name }} (Sisa: Rp {{ number_format($inv->outstanding_balance, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nominal Bayar (Rp) *</label>
                    <input type="number" name="amount" required min="1" step="0.01" placeholder="500000" class="w-full px-3 py-2 text-sm border rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Metode &amp; Tgl *</label>
                    <div class="flex gap-1">
                        <select name="payment_method" class="w-1/2 px-2 py-2 text-xs border rounded-lg">
                            <option value="bank_transfer">Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="giro">Giro</option>
                        </select>
                        <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required class="w-1/2 px-2 py-2 text-xs border rounded-lg">
                    </div>
                </div>
                <div>
                    <x-admin-button type="submit" variant="primary" icon="fa-solid fa-money-check-dollar">
                        Simpan Pembayaran
                    </x-admin-button>
                </div>
            </form>
        </x-admin-card>

        <x-admin-card title="Riwayat Pembayaran Supplier">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No. Pembayaran</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Supplier</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No. Invoice</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Tanggal Bayar</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Metode</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($payments as $pay)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono font-bold text-emerald-700">{{ $pay->payment_number }}</td>
                                <td class="px-4 py-3 font-medium">{{ $pay->supplier?->name }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-blue-600">{{ $pay->invoice?->invoice_number }}</td>
                                <td class="px-4 py-3 text-center text-xs">{{ $pay->payment_date?->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-center text-xs">{{ $pay->payment_method_label }}</td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-700">Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada riwayat pembayaran supplier.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin-card>
    </div>
@endsection
