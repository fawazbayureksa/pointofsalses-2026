@extends('layouts.admin')

@section('title', 'Supplier Performance & Reports')
@section('page-title', 'Supplier Performance, Price Comparison & Procurement Analytics')

@section('content')
    <div class="space-y-6">
        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Nilai Pembelian (PO)</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalProcurementSpend, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Hutang Aktif (Payables)</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalOutstandingDebt, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Rekomendasi Reorder Stok</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $reorderSuggestions->count() }} Item</p>
            </div>
        </div>

        {{-- 1. Supplier Performance Scorecard --}}
        <x-admin-card title="Evaluasi & Skor Performa Supplier (Supplier Performance Scorecard)">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Supplier</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Frekuensi PO</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total Pembelian</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Ketepatan Waktu</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">% Barang Rusak</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Skor Performa</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Sisa Hutang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($performanceRows as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-gray-900">{{ $row['supplier']->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $row['supplier']->code }} • {{ $row['mapped_products_count'] }} Produk</div>
                                </td>
                                <td class="px-4 py-3 text-center font-semibold">{{ $row['po_count'] }}x PO</td>
                                <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($row['total_purchase_value'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $row['on_time_rate'] >= 85 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $row['on_time_rate'] }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $row['damage_rate'] <= 2 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $row['damage_rate'] }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-bold text-indigo-700">{{ $row['performance_score'] }}</span>
                                    <span class="text-xs text-gray-500 block">{{ $row['grade'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-red-600">
                                    Rp {{ number_format($row['outstanding_payables'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-400">Belum ada data evaluasi supplier.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin-card>

        {{-- 2. Supplier Price Comparison & History --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-admin-card title="Perbandingan Harga Antar Supplier (Price Comparison)">
                <div class="space-y-3">
                    @forelse ($comparableProducts as $prod)
                        <div class="p-3 rounded-lg border bg-gray-50">
                            <div class="font-bold text-sm text-gray-900 mb-2">{{ $prod->name }}</div>
                            <div class="space-y-1">
                                @foreach ($prod->supplierProducts->sortBy('purchase_price') as $idx => $sp)
                                    <div class="flex items-center justify-between text-xs bg-white px-2.5 py-1.5 rounded border">
                                        <div>
                                            <span class="font-semibold">{{ $sp->supplier?->name }}</span>
                                            @if ($idx === 0)
                                                <span class="ml-1 px-1.5 py-0.5 text-[10px] bg-emerald-100 text-emerald-800 font-bold rounded">Termurah</span>
                                            @endif
                                            @if ($sp->is_primary)
                                                <span class="ml-1 px-1.5 py-0.5 text-[10px] bg-blue-100 text-blue-800 font-bold rounded">Utama</span>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <span class="font-bold text-gray-900">Rp {{ number_format($sp->purchase_price, 0, ',', '.') }}</span> / {{ $sp->purchase_unit }}
                                            <span class="text-gray-400 ml-1">(MOQ: {{ $sp->min_order_qty }}, Lead: {{ $sp->lead_time_days }}h)</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-4">Belum ada produk yang dimapping ke supplier.</p>
                    @endforelse
                </div>
            </x-admin-card>

            <x-admin-card title="Histori Kenaikan / Perubahan Harga Beli">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left">Produk &amp; Supplier</th>
                                <th class="px-3 py-2 text-right">Harga Lama</th>
                                <th class="px-3 py-2 text-right">Harga Baru</th>
                                <th class="px-3 py-2 text-center">Perubahan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($priceHistories as $hist)
                                <tr>
                                    <td class="px-3 py-2">
                                        <div class="font-semibold text-gray-900">{{ $hist->product?->name }}</div>
                                        <div class="text-gray-500">{{ $hist->supplier?->name }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-right text-gray-500">
                                        {{ $hist->old_price !== null ? 'Rp ' . number_format($hist->old_price, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-right font-bold">
                                        Rp {{ number_format($hist->new_price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        @php $chg = $hist->price_change_percent; @endphp
                                        @if ($chg === null)
                                            <span class="text-blue-600">Baru</span>
                                        @elseif ($chg > 0)
                                            <span class="text-red-600 font-semibold">+{{ $chg }}%</span>
                                        @elseif ($chg < 0)
                                            <span class="text-emerald-600 font-semibold">{{ $chg }}%</span>
                                        @else
                                            <span>0%</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-gray-400">Belum ada histori perubahan harga.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin-card>
        </div>
    </div>
@endsection
