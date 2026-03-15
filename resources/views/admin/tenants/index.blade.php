@extends('layouts.admin')

@section('title', 'Tenants')

@section('page-title', 'Tenants Management')

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

        <x-admin-card title="All Tenants">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <input type="text" placeholder="Search tenants..."
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
                    <select
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                <x-admin-button variant="primary" icon="fa-solid fa-plus" x-data @click="$dispatch('open-create-modal')">
                    Add New Tenant
                </x-admin-button>
            </div>

            <x-admin-table :headers="[
                ['label' => 'ID', 'key' => 'id'],
                ['label' => 'Name', 'key' => 'name'],
                ['label' => 'Domain', 'slot' => fn($t) => $t->domains->first()?->domain ?? '-'],
                ['label' => 'Plan', 'key' => 'plan'],
                ['label' => 'Status', 'key' => 'status'],
                ['label' => 'Created At', 'key' => 'created_at'],
            ]" :rows="$tenants ?? []" :actions="function ($tenant) {
                return view('admin.tenants.actions', ['tenant' => $tenant])->render();
            }" />
        </x-admin-card>
    </div>

    @include('admin.tenants.create-modal')
    @include('admin.tenants.edit-modal')
@endsection
