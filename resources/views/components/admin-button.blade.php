@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'loading' => false,
    'disabled' => false,
    'href' => null,
])

@php
    $variantClasses = [
        'primary' => 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500',
        'secondary' => 'bg-gray-600 hover:bg-gray-700 text-white focus:ring-gray-500',
        'success' => 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
        'warning' => 'bg-yellow-500 hover:bg-yellow-600 text-white focus:ring-yellow-500',
        'ghost' => 'bg-transparent hover:bg-gray-100 text-gray-700 focus:ring-gray-500',
    ];

    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];
@endphp

@if($href)
    <a href="{{ $href }}" 
       {{ $disabled ? 'tabindex="-1" class="opacity-50 cursor-not-allowed"' : '' }}
       class="inline-flex items-center justify-center font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors {{ $variantClasses[$variant] }} {{ $sizeClasses[$size] }}">
        @if($loading)
            <i class="fa-solid fa-circle-notch fa-spin mr-2"></i>
        @elseif($icon)
            <i class="{{ $icon }} mr-2"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" 
            {{ $disabled ? 'disabled' : '' }}
            {{ $loading ? 'disabled' : '' }}
            class="inline-flex items-center justify-center font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors {{ $variantClasses[$variant] }} {{ $sizeClasses[$size] }} {{ $disabled || $loading ? 'opacity-50 cursor-not-allowed' : '' }}">
        @if($loading)
            <i class="fa-solid fa-circle-notch fa-spin mr-2"></i>
        @elseif($icon)
            <i class="{{ $icon }} mr-2"></i>
        @endif
        {{ $slot }}
    </button>
@endif
