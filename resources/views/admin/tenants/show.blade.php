@extends('layouts.admin')

@section('title', 'Tenant Detail')

@section('page-title', 'Tenant Detail')

@section('content')
    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <a href="{{ route('admin.tenants.index') }}"
                class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Tenants
            </a>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.tenants.edit', $tenant->id) }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    <i class="fa-solid fa-edit mr-2"></i> Edit Tenant
                </a>
            </div>
        </div>

        {{-- Tenant Info --}}
        <x-admin-card title="Tenant Information">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $tenant->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Slug</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $tenant->slug }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Status</dt>
                    <dd class="mt-1">
                        @if ($tenant->status === 'active')
                            <span
                                class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Active</span>
                        @elseif($tenant->status === 'suspended')
                            <span
                                class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Suspended</span>
                        @else
                            <span
                                class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 rounded-full">Inactive</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Plan</dt>
                    <dd class="mt-1 text-sm text-gray-900 capitalize">{{ $tenant->plan ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Trial Ends At</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if ($tenant->trial_ends_at)
                            {{ $tenant->trial_ends_at->format('d M Y, H:i') }}
                            @if ($tenant->isOnTrial())
                                <span class="ml-2 px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-700 rounded-full">
                                    {{ $tenant->trialDaysLeft() }} day(s) left
                                </span>
                            @else
                                <span class="ml-2 px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-700 rounded-full">Expired</span>
                            @endif
                        @else
                            -
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Subscription Skip</dt>
                    <dd class="mt-1">
                        @if ($tenant->subscription_skipped)
                            <span class="px-2 py-1 text-xs font-semibold bg-amber-100 text-amber-800 rounded-full">Skipped (free access)</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-600 rounded-full">Not skipped</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $tenant->email ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Phone</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $tenant->phone ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Business Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $tenant->business_type ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Created At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $tenant->created_at->format('d M Y, H:i') }}</dd>
                </div>
                @if ($tenant->address)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-gray-500 uppercase">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $tenant->address }}</dd>
                    </div>
                @endif
            </dl>
        </x-admin-card>

        {{-- Outlets --}}
        <x-admin-card title="Outlets ({{ $tenant->outlets->count() }})">
            @if ($tenant->outlets->isEmpty())
                <p class="text-sm text-gray-500 py-4 text-center">No outlets yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">City</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($tenant->outlets as $outlet)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $outlet->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $outlet->code ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $outlet->city ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @if ($outlet->is_active)
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Active</span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-admin-card>

        {{-- Users --}}
        <x-admin-card title="Users ({{ $tenant->users->count() }})">
            @if ($tenant->users->isEmpty())
                <p class="text-sm text-gray-500 py-4 text-center">No users yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($tenant->users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $user->roles->pluck('name')->map(fn($r) => ucfirst($r))->join(', ') ?: '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($user->is_active)
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Active</span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-admin-card>

    </div>
@endsection
