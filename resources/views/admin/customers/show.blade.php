@extends('layouts.admin')

@section('title', $customer->name)

@section('page-title', 'Customer Details')

@section('content')
    <div class="space-y-6">

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-circle-check text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div>
            <a href="{{ route('admin.customers.index') }}"
                class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                Back to Customers
            </a>
        </div>

        {{-- Member Card --}}
        @if ($customer->is_member)
            @php
                $tierColors = [
                    'regular'  => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'border' => 'border-gray-300', 'badge' => 'bg-gray-200 text-gray-700'],
                    'silver'   => ['bg' => 'bg-slate-100', 'text' => 'text-slate-800', 'border' => 'border-slate-300', 'badge' => 'bg-slate-200 text-slate-700'],
                    'gold'     => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-800', 'border' => 'border-yellow-300', 'badge' => 'bg-yellow-100 text-yellow-800'],
                    'platinum' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-800', 'border' => 'border-purple-300', 'badge' => 'bg-purple-100 text-purple-800'],
                ];
                $tierIcons = [
                    'regular'  => 'fa-solid fa-star',
                    'silver'   => 'fa-solid fa-star',
                    'gold'     => 'fa-solid fa-crown',
                    'platinum' => 'fa-solid fa-gem',
                ];
                $colors = $tierColors[$customer->membership_tier] ?? $tierColors['regular'];
                $icon   = $tierIcons[$customer->membership_tier] ?? $tierIcons['regular'];
            @endphp
            <div class="rounded-lg border-2 {{ $colors['border'] }} {{ $colors['bg'] }} p-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <i class="{{ $icon }} text-2xl {{ $colors['text'] }}"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider {{ $colors['text'] }}">Member Card</p>
                            <p class="text-lg font-bold {{ $colors['text'] }}">{{ $customer->name }}</p>
                            @if ($customer->customer_code)
                                <p class="text-sm {{ $colors['text'] }} opacity-75">{{ $customer->customer_code }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $colors['badge'] }}">
                            <i class="{{ $icon }} mr-1 text-xs"></i>
                            {{ ucfirst($customer->membership_tier) }}
                        </span>
                        <p class="text-xs {{ $colors['text'] }} opacity-75 mt-1">
                            Member since {{ $customer->member_since->format('M d, Y') }}
                        </p>
                        <p class="text-xs font-medium {{ $colors['text'] }} mt-1">
                            <i class="fa-solid fa-coins mr-1"></i>{{ number_format($customer->loyalty_points) }} pts
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Customer Info --}}
            <div class="lg:col-span-2 space-y-6">
                <x-admin-card title="Customer Information">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</span>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->name }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Email</span>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->email ?? '—' }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</span>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->phone ?? '—' }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</span>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->gender ? ucfirst($customer->gender) : '—' }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Date of Birth</span>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->date_of_birth ? $customer->date_of_birth->format('M d, Y') : '—' }}</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Member Code</span>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->customer_code ?? '—' }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Address</span>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->address ?? '—' }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Status</span>
                                <p class="mt-1">
                                    @if ($customer->is_active)
                                        <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800">Inactive</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Loyalty Points</span>
                                <p class="mt-1 text-sm font-semibold text-blue-600">{{ number_format($customer->loyalty_points) }} pts</p>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Customer Since</span>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>

                    @if ($customer->notes)
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</span>
                            <p class="mt-1 text-sm text-gray-700">{{ $customer->notes }}</p>
                        </div>
                    @endif

                    @can('edit customers')
                        <div class="mt-4 pt-4 border-t border-gray-200 flex space-x-3">
                            <button @click="$dispatch('open-edit-modal', {
                                id: {{ $customer->id }},
                                name: @json($customer->name),
                                email: @json($customer->email),
                                phone: @json($customer->phone),
                                address: @json($customer->address),
                                customer_code: @json($customer->customer_code),
                                gender: @json($customer->gender),
                                date_of_birth: @json($customer->date_of_birth?->format('Y-m-d')),
                                notes: @json($customer->notes),
                                status: @json($customer->status),
                                member_since: @json($customer->member_since?->format('Y-m-d')),
                                membership_tier: @json($customer->membership_tier)
                            })" x-data
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                <i class="fa-solid fa-edit mr-2"></i>Edit Customer
                            </button>
                        </div>
                    @endcan
                </x-admin-card>

                {{-- Order History --}}
                <x-admin-card title="Order History">
                    @if ($customer->orders->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($customer->orders->sortByDesc('created_at') as $order)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-blue-600">
                                                <a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                {{ $order->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($order->total_amount, 2) }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full
                                                    {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                                    {{ !in_array($order->status, ['completed', 'pending', 'cancelled']) ? 'bg-gray-100 text-gray-800' : '' }}
                                                ">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fa-solid fa-receipt text-4xl text-gray-300 mb-3"></i>
                            <p class="text-sm text-gray-500">No orders yet.</p>
                        </div>
                    @endif
                </x-admin-card>
            </div>

            {{-- Membership Panel --}}
            <div class="space-y-6">
                {{-- Membership Stats --}}
                <x-admin-card title="Membership">
                    <div class="space-y-4">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <p class="text-3xl font-bold text-blue-600">{{ number_format($customer->loyalty_points) }}</p>
                            <p class="text-sm text-blue-500 mt-1">Loyalty Points</p>
                        </div>

                        @if ($customer->is_member)
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Membership Tier</span>
                                @php
                                    $tierBadges = [
                                        'regular'  => 'bg-gray-100 text-gray-800',
                                        'silver'   => 'bg-slate-200 text-slate-800',
                                        'gold'     => 'bg-yellow-100 text-yellow-800',
                                        'platinum' => 'bg-purple-100 text-purple-800',
                                    ];
                                    $badge = $tierBadges[$customer->membership_tier] ?? $tierBadges['regular'];
                                @endphp
                                <p class="mt-1">
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $badge }}">
                                        {{ ucfirst($customer->membership_tier) }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Member Since</span>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->member_since->format('M d, Y') }}</p>
                            </div>
                            @can('edit customers')
                                <div x-data="{ open: false }" class="pt-2 border-t border-gray-200">
                                    <button @click="open = !open"
                                        class="w-full text-left text-sm text-blue-600 hover:text-blue-800 font-medium">
                                        <i class="fa-solid fa-chevron-down mr-1" :class="open ? 'rotate-180' : ''" style="transition: transform .2s"></i>
                                        Update Membership Tier
                                    </button>
                                    <div x-show="open" x-cloak class="mt-3">
                                        <form method="POST" action="{{ route('admin.customers.enroll', $customer) }}">
                                            @csrf
                                            <input type="hidden" name="member_since" value="{{ $customer->member_since->format('Y-m-d') }}">
                                            <div class="mb-3">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Tier</label>
                                                <select name="membership_tier"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    @foreach (\App\Models\Customer::tierLabels() as $value => $label)
                                                        <option value="{{ $value }}" {{ $customer->membership_tier === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button type="submit"
                                                class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                                Update Tier
                                            </button>
                                        </form>
                                    </div>
                                    <form method="POST" action="{{ route('admin.customers.unenroll', $customer) }}" class="mt-2"
                                        onsubmit="return confirm('Remove membership for this customer?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="w-full px-4 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100">
                                            <i class="fa-solid fa-user-minus mr-1"></i>Remove Membership
                                        </button>
                                    </form>
                                </div>
                            @endcan
                        @else
                            <div class="text-center py-2">
                                <i class="fa-solid fa-id-card text-3xl text-gray-300 mb-2"></i>
                                <p class="text-sm text-gray-500">Not a member yet.</p>
                            </div>
                            @can('edit customers')
                                <div x-data="{ open: false }" class="pt-2 border-t border-gray-200">
                                    <button @click="open = !open"
                                        class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                                        <i class="fa-solid fa-user-plus mr-1"></i>Enroll as Member
                                    </button>
                                    <div x-show="open" x-cloak class="mt-3">
                                        <form method="POST" action="{{ route('admin.customers.enroll', $customer) }}">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Member Since <span class="text-red-500">*</span></label>
                                                <input type="date" name="member_since" value="{{ date('Y-m-d') }}"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Tier</label>
                                                <select name="membership_tier"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    @foreach (\App\Models\Customer::tierLabels() as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button type="submit"
                                                class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                                                Enroll Member
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endcan
                        @endif
                    </div>
                </x-admin-card>

                {{-- Order Summary --}}
                <x-admin-card title="Order Summary">
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Total Orders</span>
                            <span class="font-medium text-gray-900">{{ $customer->orders->count() }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Completed</span>
                            <span class="font-medium text-green-600">{{ $customer->orders->where('status', 'completed')->count() }}</span>
                        </div>
                        <div class="flex justify-between text-sm border-t border-gray-200 pt-3">
                            <span class="text-gray-500">Total Spent</span>
                            <span class="font-semibold text-gray-900">
                                {{ number_format($customer->orders->where('status', 'completed')->sum('total_amount'), 2) }}
                            </span>
                        </div>
                    </div>
                </x-admin-card>
            </div>
        </div>
    </div>

    @include('admin.customers.edit-modal')
@endsection
