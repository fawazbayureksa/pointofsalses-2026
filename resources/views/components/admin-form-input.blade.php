@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'help' => null,
    'options' => [],
    'multiple' => false,
])

<div class="mb-4">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    @if($type === 'select')
        <select 
            name="{{ $name }}" 
            id="{{ $name }}"
            @if($multiple) multiple @endif
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $required ? 'required' : '' }}
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @if($error) border-red-500 @endif @if($disabled) bg-gray-100 text-gray-500 @endif"
        >
            @if(!$required)
                <option value="">Select {{ $label ?? 'option' }}</option>
            @endif
            @foreach($options as $key => $option)
                <option value="{{ $key }}" {{ $value == $key ? 'selected' : '' }}>{{ $option }}</option>
            @endforeach
        </select>
    @elseif($type === 'textarea')
        <textarea 
            name="{{ $name }}" 
            id="{{ $name }}"
            rows="3"
            placeholder="{{ $placeholder }}"
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $required ? 'required' : '' }}
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @if($error) border-red-500 @endif @if($disabled) bg-gray-100 text-gray-500 @endif"
        >{{ old($name, $value) }}</textarea>
    @elseif($type === 'checkbox')
        <div class="flex items-center">
            <input 
                type="checkbox" 
                name="{{ $name }}" 
                id="{{ $name }}"
                value="1"
                {{ $value ? 'checked' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                {{ $readonly ? 'readonly' : '' }}
                {{ $required ? 'required' : '' }}
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 @if($error) border-red-500 @endif"
            >
            @if($label)
                <label for="{{ $name }}" class="ml-2 text-sm text-gray-700">
                    {{ $label }}
                </label>
            @endif
        </div>
    @elseif($type === 'file')
        <input 
            type="file" 
            name="{{ $name }}" 
            id="{{ $name }}"
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $required ? 'required' : '' }}
            {{ $multiple ? 'multiple' : '' }}
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @if($error) border-red-500 @endif @if($disabled) bg-gray-100 text-gray-500 @endif"
        >
    @else
        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $required ? 'required' : '' }}
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @if($error) border-red-500 @endif @if($disabled) bg-gray-100 text-gray-500 @endif"
        >
    @endif

    @if($error)
        <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
    @endif

    @if($help)
        <p class="mt-1 text-sm text-gray-500">{{ $help }}</p>
    @endif
</div>
