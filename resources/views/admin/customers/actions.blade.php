@props(['customer'])

<div class="flex items-center justify-end space-x-2">
    <a href="{{ route('admin.customers.show', $customer->id) }}" class="text-blue-600 hover:text-blue-900">
        <i class="fa-solid fa-eye"></i>
    </a>
    @can('edit customers')
        <button @click="$dispatch('open-edit-modal', {
            id: {{ $customer->id }},
            name: @json($customer->name),
            email: @json($customer->email),
            phone: @json($customer->phone),
            address: @json($customer->address),
            customer_code: @json($customer->customer_code),
            gender: @json($customer->gender),
            date_of_birth: @json($customer->date_of_birth?->format('Y-m-d')),
            notes: @json($customer->notes),
            status: @json($customer->status),
            member_since: @json($customer->member_since?->format('Y-m-d')),
            membership_tier: @json($customer->membership_tier)
        })" class="text-green-600 hover:text-green-900">
            <i class="fa-solid fa-edit"></i>
        </button>
    @endcan
    @can('delete customers')
        <button @click="if(confirm('Are you sure you want to delete this customer?')) window.location.href='{{ route('admin.customers.destroy', $customer->id) }}'"
                class="text-red-600 hover:text-red-900">
            <i class="fa-solid fa-trash"></i>
        </button>
    @endcan
</div>
