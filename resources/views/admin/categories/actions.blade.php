@props(['category'])

<div class="flex items-center justify-end space-x-2">
    <a href="{{ route('admin.categories.show', $category->id) }}" class="text-blue-600 hover:text-blue-900">
        <i class="fa-solid fa-eye"></i>
    </a>
    @can('edit categories')
        <button @click="$dispatch('open-edit-modal', { id: {{ $category->id }} })" class="text-green-600 hover:text-green-900">
            <i class="fa-solid fa-edit"></i>
        </button>
    @endcan
    @can('delete categories')
        <button @click="if(confirm('Are you sure you want to delete this category?')) window.location.href='{{ route('admin.categories.destroy', $category->id) }}'" 
                class="text-red-600 hover:text-red-900">
            <i class="fa-solid fa-trash"></i>
        </button>
    @endcan
</div>
