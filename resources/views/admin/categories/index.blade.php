@extends('layouts.admin')

@section('title', 'Categories')

@section('page-title', 'Product Categories')

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

        <x-admin-card title="All Categories">
            @php
                $tableHeaders = [
                    ['label' => 'ID', 'key' => 'id'],
                    ['label' => 'Name', 'key' => 'name'],
                    ['label' => 'Slug', 'key' => 'slug'],
                    ['label' => 'Parent', 'slot' => fn($c) => $c->parent?->name ?? '—'],
                    ['label' => 'Products', 'slot' => fn($c) => $c->products_count ?? 0],
                    [
                        'label' => 'Status',
                        'slot' => fn($c) => $c->is_active
                            ? '<span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>'
                            : '<span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800">Inactive</span>',
                    ],
                    ['label' => 'Created At', 'key' => 'created_at'],
                ];
            @endphp
            <div class="mb-4 flex items-center justify-between">
                <input type="text" placeholder="Search categories..."
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
                @can('create categories')
                    <x-admin-button variant="primary" icon="fa-solid fa-plus" x-data @click="$dispatch('open-create-modal')">
                        Add New Category
                    </x-admin-button>
                @endcan
            </div>

            <x-admin-table :headers="$tableHeaders" :rows="$categories ?? []" :actions="function ($category) {
                return view('admin.categories.actions', ['category' => $category])->render();
            }" />
        </x-admin-card>
    </div>

    @include('admin.categories.create-modal')
    @include('admin.categories.edit-modal')
@endsection
