@props([
    'id' => 'modal',
    'title' => null,
    'size' => 'md',
])

<div :id="id" x-data="{ open: false }" class="relative z-50">
    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-50"></div>

    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="fixed inset-0 z-10 overflow-y-auto"
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:w-full"
                 :class="{
                     'sm:max-w-md': {{ $size === 'sm' ? 'true' : 'false' }},
                     'sm:max-w-lg': {{ $size === 'md' ? 'true' : 'false' }},
                     'sm:max-w-2xl': {{ $size === 'lg' ? 'true' : 'false' }},
                     'sm:max-w-4xl': {{ $size === 'xl' ? 'true' : 'false' }}
                 }">
                
                @if($title)
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 flex items-center justify-between">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            {{ $title }}
                        </h3>
                        <button @click="open = false" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                @endif
                
                <div class="px-4 py-5 sm:p-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>
