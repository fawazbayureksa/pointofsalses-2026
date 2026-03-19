@props(['outlet'])

<div class="flex items-center justify-end space-x-2">
    <a href="{{ route('admin.outlets.show', $outlet->id) }}" class="text-blue-600 hover:text-blue-900">
        <i class="fa-solid fa-eye"></i>
    </a>
    @can('edit outlets')
        <button
            @click="$dispatch('open-edit-modal', {
            id: {{ $outlet->id }},
            name: @json($outlet->name),
            code: @json($outlet->code),
            phone: @json($outlet->phone),
            email: @json($outlet->email),
            address: @json($outlet->address),
            city: @json($outlet->city),
            is_active: {{ $outlet->is_active ? 1 : 0 }}
        })"
            class="text-green-600 hover:text-green-900">
            <i class="fa-solid fa-edit"></i>
        </button>
    @endcan
    @can('delete outlets')
        <button
            @click="if(confirm('Are you sure you want to delete this outlet?')) window.location.href='{{ route('admin.outlets.destroy', $outlet->id) }}'"
            class="text-red-600 hover:text-red-900">
            <i class="fa-solid fa-trash"></i>
        </button>
    @endcan
</div>
