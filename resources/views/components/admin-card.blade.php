@props([
    'title' => null,
    'actions' => null,
    'collapsible' => false,
    'collapsed' => false,
])

<div x-data="{ isOpen: {{ $collapsed ? 'false' : 'true' }} }" class="bg-white rounded-lg shadow-sm border border-gray-200">
    @if($title || $actions)
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center">
                @if($title)
                    <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
                @endif
            </div>
            <div class="flex items-center space-x-2">
                @if($actions)
                    {{ $actions }}
                @endif
                @if($collapsible)
                    <button @click="isOpen = !isOpen" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fa-solid" :class="isOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                @endif
            </div>
        </div>
    @endif

    <div x-show="isOpen" x-collapse class="p-6">
        {{ $slot }}
    </div>
</div>
