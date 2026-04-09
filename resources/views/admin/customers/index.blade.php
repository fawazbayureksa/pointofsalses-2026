@extends('layouts.admin')

@section('title', 'Customers')

@section('page-title', 'Customers Management')

@section('content')
    <div class="space-y-6">
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-circle-exclamation text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Errors</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

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

        @php
            $tableHeaders = [
                ['label' => 'ID', 'key' => 'id'],
                ['label' => 'Name', 'key' => 'name'],
                ['label' => 'Email', 'key' => 'email'],
                ['label' => 'Phone', 'key' => 'phone'],
                [
                    'label' => 'Membership',
                    'slot' => function ($customer) {
                        $tierBadges = [
                            'regular' => 'bg-gray-100 text-gray-700',
                            'silver' => 'bg-slate-200 text-slate-700',
                            'gold' => 'bg-yellow-100 text-yellow-800',
                            'platinum' => 'bg-purple-100 text-purple-800',
                        ];
                        if ($customer->is_member) {
                            $badge = $tierBadges[$customer->membership_tier] ?? $tierBadges['regular'];
                            return '<span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full ' .
                                $badge .
                                '">' .
                                ucfirst($customer->membership_tier) .
                                '</span>';
                        }
                        return '<span class="text-xs text-gray-400">—</span>';
                    },
                ],
                [
                    'label' => 'Loyalty Points',
                    'slot' => function ($customer) {
                        return '<span class="text-sm font-medium text-blue-600">' .
                            number_format($customer->loyalty_points) .
                            ' pts</span>';
                    },
                ],
                [
                    'label' => 'Status',
                    'slot' => function ($customer) {
                        if ($customer->is_active) {
                            return '<span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>';
                        }
                        return '<span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800">Inactive</span>';
                    },
                ],
                ['label' => 'Created At', 'key' => 'created_at'],
            ];
        @endphp

        <x-admin-card title="All Customers">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <input type="text" placeholder="Search customers..."
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
                    <select
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <select name="membership"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Members</option>
                        <option value="member">Members Only</option>
                        <option value="non_member">Non-Members</option>
                    </select>
                </div>
                @can('create customers')
                    <x-admin-button variant="primary" icon="fa-solid fa-plus" x-data @click="$dispatch('open-create-modal')">
                        Add New Customer
                    </x-admin-button>
                @endcan
            </div>

            <x-admin-table :headers="$tableHeaders" :rows="$customers ?? []" :actions="fn($customer) => view('admin.customers.actions', ['customer' => $customer])->render()" />

            <div class="mt-4">
                {{ ($customers ?? collect())->links() }}
            </div>
        </x-admin-card>
    </div>

    @include('admin.customers.create-modal')
    @include('admin.customers.edit-modal')
@endsection
