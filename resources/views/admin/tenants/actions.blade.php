@props(['tenant'])

<div class="flex items-center justify-end space-x-2">
    <a href="{{ route('admin.tenants.show', $tenant->id) }}" class="text-blue-600 hover:text-blue-900">
        <i class="fa-solid fa-eye"></i>
    </a>
    <button
        @click="$dispatch('open-edit-modal', {
        id: @json($tenant->id),
        name: @json($tenant->name),
        slug: @json($tenant->slug ?? ''),
        email: @json($tenant->email ?? ''),
        phone: @json($tenant->phone ?? ''),
        status: @json($tenant->status ?? 'active'),
        plan: @json($tenant->plan ?? 'basic'),
        subscription_status: @json($tenant->subscription_status ?? 'trial'),
        trial_ends_at: @json($tenant->trial_ends_at ? $tenant->trial_ends_at->format('Y-m-d\TH:i') : ''),
        subscription_ends_at: @json($tenant->subscription_ends_at ? $tenant->subscription_ends_at->format('Y-m-d\TH:i') : ''),
        is_subscription_exempt: @json((bool) $tenant->is_subscription_exempt),
        business_type: @json($tenant->business_type ?? ''),
        address: @json($tenant->address ?? '')
    })"
        class="text-green-600 hover:text-green-900">
        <i class="fa-solid fa-edit"></i>
    </button>
    <button
        @click="if(confirm('Are you sure you want to delete this tenant?')) window.location.href='{{ route('admin.tenants.destroy', $tenant->id) }}'"
        class="text-red-600 hover:text-red-900">
        <i class="fa-solid fa-trash"></i>
    </button>
</div>
