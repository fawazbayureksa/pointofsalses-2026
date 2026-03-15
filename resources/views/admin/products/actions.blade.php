@props(['product'])

<div class="flex items-center justify-end space-x-2">
    <a href="{{ route('admin.products.show', $product->id) }}" class="text-blue-600 hover:text-blue-900">
        <i class="fa-solid fa-eye"></i>
    </a>
    @can('edit products')
        <button @click="$dispatch('open-edit-modal', { id: {{ $product->id }} })" class="text-green-600 hover:text-green-900">
            <i class="fa-solid fa-edit"></i>
        </button>
    @endcan
    @can('delete products')
        <button @click="if(confirm('Are you sure you want to delete this product?')) window.location.href='{{ route('admin.products.destroy', $product->id) }}'" 
                class="text-red-600 hover:text-red-900">
            <i class="fa-solid fa-trash"></i>
        </button>
    @endcan
</div>
