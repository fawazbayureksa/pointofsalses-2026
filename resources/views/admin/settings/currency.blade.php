@extends('layouts.admin')

@section('title', 'Currency Settings')

@section('page-title', 'Currency Settings')

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

        <x-admin-card title="Currency Configuration">
            <form method="POST" action="{{ route('admin.settings.currency.update') }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Currency Code
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="currency_code" maxlength="3"
                            value="{{ old('currency_code', $settings->get('currency_code')?->value ?? 'USD') }}"
                            placeholder="e.g. USD, EUR, IDR"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase @error('currency_code') border-red-300 @enderror"
                            required />
                        @error('currency_code')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">ISO 4217 currency code (3 letters)</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Currency Symbol
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="currency_symbol" maxlength="5"
                            value="{{ old('currency_symbol', $settings->get('currency_symbol')?->value ?? '$') }}"
                            placeholder="e.g. $, €, Rp"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('currency_symbol') border-red-300 @enderror"
                            required />
                        @error('currency_symbol')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Symbol Position
                        </label>
                        <select name="currency_position"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="before"
                                {{ ($settings->get('currency_position')?->value ?? 'before') === 'before' ? 'selected' : '' }}>
                                Before amount ($ 100.00)
                            </option>
                            <option value="after"
                                {{ ($settings->get('currency_position')?->value ?? '') === 'after' ? 'selected' : '' }}>
                                After amount (100.00 $)
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Decimal Places
                        </label>
                        <select name="currency_decimals"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach ([0, 1, 2, 3] as $d)
                                <option value="{{ $d }}"
                                    {{ ($settings->get('currency_decimals')?->value ?? '2') == $d ? 'selected' : '' }}>
                                    {{ $d }} {{ $d === 1 ? 'decimal' : 'decimals' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Preview</h4>
                    <p class="text-gray-600 text-sm" x-data="{ symbol: '{{ $settings->get('currency_symbol')?->value ?? '$' }}', pos: '{{ $settings->get('currency_position')?->value ?? 'before' }}' }">
                        Example: <span class="font-semibold text-gray-900">
                            {{ ($settings->get('currency_position')?->value ?? 'before') === 'before'
                                ? ($settings->get('currency_symbol')?->value ?? '$') . '1,234.00'
                                : '1,234.00 ' . ($settings->get('currency_symbol')?->value ?? '$') }}
                        </span>
                    </p>
                </div>

                <div class="flex items-center justify-end space-x-3 mt-6">
                    <a href="{{ route('admin.settings.index') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancel
                    </a>
                    <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">
                        Save Currency Settings
                    </x-admin-button>
                </div>
            </form>
        </x-admin-card>
    </div>
@endsection
