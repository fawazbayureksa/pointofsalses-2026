@props(['product'])

<div class="flex items-center justify-end space-x-2">
    <a href="{{ route('admin.products.show', $product->id) }}" class="text-blue-600 hover:text-blue-900">
        <i class="fa-solid fa-eye"></i>
    </a>
    @can('edit products')
        <button @click="$dispatch('open-edit-modal', {
            id: {{ $product->id }},
            name: @json($product->name),
            sku: @json($product->sku),
            barcode: @json($product->barcode),
            description: @json($product->description),
            price: @json((string) $product->price),
            cost_price: @json((string) $product->cost_price),
            stock: @json((string) $product->stock),
            low_stock_threshold: @json((string) $product->low_stock_threshold),
            unit: @json($product->unit),
            category_id: {{ $product->category_id ?? 'null' }},
            outlet_id: {{ $product->outlet_id ?? 'null' }},
            is_active: {{ $product->is_active ? 1 : 0 }},
            track_stock: {{ $product->track_stock ? 1 : 0 }}
        })" class="text-green-600 hover:text-green-900">
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
