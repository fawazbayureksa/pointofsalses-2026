@props(['product'])

<div class="flex items-center justify-end space-x-2">
    <a href="{{ route('admin.products.show', $product->id) }}" class="text-blue-600 hover:text-blue-900">
        <i class="fa-solid fa-eye"></i>
    </a>
    @can('edit products')
        <button
            @click="window.dispatchEvent(new CustomEvent('open-edit-modal', { detail: {
                id: {{ $product->id }},
                name: {{ Js::from($product->name) }},
                sku: {{ Js::from($product->sku) }},
                barcode: {{ Js::from($product->barcode ?? '') }},
                description: {{ Js::from($product->description ?? '') }},
                price: {{ Js::from((string) $product->price) }},
                cost_price: {{ Js::from((string) ($product->cost_price ?? '')) }},
                unit: {{ Js::from($product->unit ?? '') }},
                category_id: {{ $product->category_id ?? 'null' }},
                is_active: {{ $product->is_active ? 1 : 0 }},
                track_stock: {{ $product->track_stock ? 1 : 0 }}
            }}))"
            class="text-green-600 hover:text-green-900">
            <i class="fa-solid fa-edit"></i>
        </button>
    @endcan
    @can('delete products')
        <button
            @click="if(confirm('Are you sure you want to delete this product?')) window.location.href='{{ route('admin.products.destroy', $product->id) }}'"
            class="text-red-600 hover:text-red-900">
            <i class="fa-solid fa-trash"></i>
        </button>
    @endcan
</div>
