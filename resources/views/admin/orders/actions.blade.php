@props(['order'])

<div class="flex items-center justify-end space-x-2">
    <button @click="$dispatch('open-show-modal', { id: {{ $order->id }} })" class="text-blue-600 hover:text-blue-900">
        <i class="fa-solid fa-eye"></i>
    </button>
    @can('edit orders')
        <a href="{{ route('admin.orders.edit', $order->id) }}" class="text-green-600 hover:text-green-900">
            <i class="fa-solid fa-edit"></i>
        </a>
    @endcan
    @can('delete orders')
        <button @click="if(confirm('Are you sure you want to delete this order?')) window.location.href='{{ route('admin.orders.destroy', $order->id) }}'" 
                class="text-red-600 hover:text-red-900">
            <i class="fa-solid fa-trash"></i>
        </button>
    @endcan
</div>
