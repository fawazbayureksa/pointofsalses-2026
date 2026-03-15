@extends('layouts.admin')

@section('title', 'Outlets')

@section('page-title', 'Outlets Management')

@section('content')
<div class="space-y-6">
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fa-solid fa-circle-exclamation text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Errors</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(session('success'))
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

    <x-admin-card title="All Outlets">
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <input type="text" placeholder="Search outlets..." 
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
                <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            @can('create outlets')
                <x-admin-button variant="primary" icon="fa-solid fa-plus">
                    <button x-data @click="$dispatch('open-create-modal')">Add New Outlet</button>
                </x-admin-button>
            @endcan
        </div>

        <x-admin-table 
            :headers="[
                ['label' => 'ID', 'key' => 'id'],
                ['label' => 'Name', 'key' => 'name'],
                ['label' => 'Address', 'key' => 'address'],
                ['label' => 'Phone', 'key' => 'phone'],
                ['label' => 'Status', 'key' => 'status'],
                ['label' => 'Created At', 'key' => 'created_at'],
            ]"
            :rows="$outlets ?? []"
            :actions="function($outlet) {
                return view('admin.outlets.actions', ['outlet' => $outlet])->render();
            }"
        />
    </x-admin-card>
</div>

@include('admin.outlets.create-modal')
@include('admin.outlets.edit-modal')
@endsection
