@extends('layouts.admin')

@section('title', 'Tax Settings')

@section('page-title', 'Tax Settings')

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

        <div class="flex items-center space-x-2 mb-2">
            <a href="{{ route('admin.settings.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back to Settings
            </a>
        </div>

        <x-admin-card title="Tax Configuration">
            <form method="POST" action="{{ route('admin.settings.tax.update') }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tax Rate (%)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="tax_rate" step="0.01" min="0" max="100"
                            value="{{ old('tax_rate', $settings->get('tax_rate')?->value ?? '0') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('tax_rate') border-red-300 @enderror"
                            required />
                        @error('tax_rate')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tax Label
                        </label>
                        <input type="text" name="tax_label"
                            value="{{ old('tax_label', $settings->get('tax_label')?->value ?? 'Tax') }}"
                            placeholder="e.g. VAT, GST, Sales Tax"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    </div>
                </div>

                <div class="mt-4">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="hidden" name="tax_enabled" value="0" />
                        <input type="checkbox" name="tax_enabled" value="1"
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            {{ ($settings->get('tax_enabled')?->value ?? '0') == '1' ? 'checked' : '' }} />
                        <span class="text-sm font-medium text-gray-700">Enable Tax</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">When enabled, tax will be automatically calculated on all orders.
                    </p>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tax Inclusion
                    </label>
                    <select name="tax_inclusion"
                        class="w-full md:w-auto px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="exclusive"
                            {{ ($settings->get('tax_inclusion')?->value ?? 'exclusive') === 'exclusive' ? 'selected' : '' }}>
                            Exclusive (added on top of price)
                        </option>
                        <option value="inclusive"
                            {{ ($settings->get('tax_inclusion')?->value ?? '') === 'inclusive' ? 'selected' : '' }}>
                            Inclusive (already included in price)
                        </option>
                    </select>
                </div>

                <div class="flex items-center justify-end space-x-3 mt-6">
                    <a href="{{ route('admin.settings.index') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancel
                    </a>
                    <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">
                        Save Tax Settings
                    </x-admin-button>
                </div>
            </form>
        </x-admin-card>
    </div>
@endsection
