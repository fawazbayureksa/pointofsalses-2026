@props(['user'])

<div class="flex items-center justify-end space-x-2">
    <a href="{{ route('admin.users.show', $user->id) }}" class="text-blue-600 hover:text-blue-900">
        <i class="fa-solid fa-eye"></i>
    </a>
    @can('edit users')
        <button @click="$dispatch('open-edit-modal', { id: {{ $user->id }} })" class="text-green-600 hover:text-green-900">
            <i class="fa-solid fa-edit"></i>
        </button>
    @endcan
    @can('delete users')
        <button @click="if(confirm('Are you sure you want to delete this user?')) window.location.href='{{ route('admin.users.destroy', $user->id) }}'" 
                class="text-red-600 hover:text-red-900">
            <i class="fa-solid fa-trash"></i>
        </button>
    @endcan
</div>
