@extends('layouts.admin')

@section('title', 'Stock Movements')
@section('page-title', 'Stock Movement History')

@section('content')
    <div class="space-y-6">

        {{-- Flash --}}
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-start space-x-3">
                <i class="fa-solid fa-circle-check text-green-400 mt-0.5"></i>
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Filters --}}
        <x-admin-card title="Filters">
            <form method="GET" action="{{ route('admin.inventory.movements') }}"
                class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">

                <select name="product_id"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Products</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}" @selected(request('product_id') == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>

                <select name="outlet_id"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Outlets</option>
                    @foreach ($outlets as $o)
                        <option value="{{ $o->id }}" @selected(request('outlet_id') == $o->id)>{{ $o->name }}</option>
                    @endforeach
                </select>

                <select name="type"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Types</option>
                    <option value="adjustment" @selected(request('type') === 'adjustment')>Adjustment</option>
                    <option value="sale" @selected(request('type') === 'sale')>Sale</option>
                    <option value="return" @selected(request('type') === 'return')>Return</option>
                    <option value="transfer_in" @selected(request('type') === 'transfer_in')>Transfer In</option>
                    <option value="transfer_out" @selected(request('type') === 'transfer_out')>Transfer Out</option>
                </select>

                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="From date">

                <div class="flex items-center space-x-2">
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="To date">
                    <button type="submit"
                        class="px-3 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 whitespace-nowrap">
                        <i class="fa-solid fa-filter mr-1"></i>Filter
                    </button>
                    @if (request()->hasAny(['product_id', 'outlet_id', 'type', 'date_from', 'date_to']))
                        <a href="{{ route('admin.inventory.movements') }}"
                            class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 whitespace-nowrap">
                            <i class="fa-solid fa-xmark mr-1"></i>Clear
                        </a>
                    @endif
                </div>
            </form>
        </x-admin-card>

        {{-- Table --}}
        <x-admin-card title="Movements ({{ $movements->total() }})">
            @if ($movements->isEmpty())
                <p class="text-gray-500 text-center py-10">No stock movements found.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-gray-600">Date</th>
                                <th class="px-4 py-3 font-semibold text-gray-600">Product</th>
                                <th class="px-4 py-3 font-semibold text-gray-600">Outlet</th>
                                <th class="px-4 py-3 font-semibold text-gray-600">Type</th>
                                <th class="px-4 py-3 font-semibold text-gray-600 text-right">Before</th>
                                <th class="px-4 py-3 font-semibold text-gray-600 text-right">Change</th>
                                <th class="px-4 py-3 font-semibold text-gray-600 text-right">After</th>
                                <th class="px-4 py-3 font-semibold text-gray-600">Reason / Ref</th>
                                <th class="px-4 py-3 font-semibold text-gray-600">By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($movements as $movement)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                                        {{ $movement->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-medium text-gray-900">
                                            {{ $movement->product?->name ?? '-' }}
                                        </span>
                                        @if ($movement->product?->sku)
                                            <br><span class="text-xs text-gray-400">{{ $movement->product->sku }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $movement->outlet?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $movement->type_badge_class }}">
                                            {{ $movement->type_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-600">
                                        {{ number_format($movement->quantity_before, 2) }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right font-semibold
                                        {{ $movement->quantity_change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $movement->quantity_change >= 0 ? '+' : '' }}{{ number_format($movement->quantity_change, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-900 font-medium">
                                        {{ number_format($movement->quantity_after, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 text-xs">
                                        @if ($movement->reason)
                                            <span>{{ $movement->reason }}</span>
                                            @if ($movement->reference_id)
                                                <br>
                                            @endif
                                        @endif
                                        @if ($movement->reference_id)
                                            <span class="text-gray-400">
                                                {{ class_basename($movement->reference_type) }}
                                                #{{ $movement->reference_id }}
                                            </span>
                                        @endif
                                        @if (!$movement->reason && !$movement->reference_id)
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 text-xs">
                                        {{ $movement->user?->name ?? 'System' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($movements->hasPages())
                    <div class="mt-4 px-4">
                        {{ $movements->links() }}
                    </div>
                @endif
            @endif
        </x-admin-card>
    </div>
@endsection
