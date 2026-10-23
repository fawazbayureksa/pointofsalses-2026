@props(['user'])

@php
    $tenant = $user->tenant;
    $userPayload = [
        'id'                            => $user->id,
        'name'                          => $user->name,
        'email'                         => $user->email,
        'phone'                         => $user->phone ?? '',
        'role'                          => $user->roles->first()?->name ?? '',
        'role_label'                    => $user->roles->pluck('name')->map(fn($r) => ucwords(str_replace('_', ' ', $r)))->join(', ') ?: '-',
        'outlet_id'                     => $user->outlets->first()?->id ?? '',
        'outlet_name'                   => $user->outlets->pluck('name')->join(', ') ?: '-',
        'is_active'                     => $user->is_active ? 1 : 0,
        'has_pin'                       => $user->hasPin(),
        'is_super_admin'                => $user->isSuperAdmin(),
        'tenant_id'                     => $user->tenant_id,
        'business_name'                 => $tenant?->name ?? '',
        'tenant_slug'                   => $tenant?->slug ?? '',
        'plan'                          => $tenant?->plan ?? 'professional',
        'subscription_status'           => $tenant?->subscription_status ?? 'trial',
        'trial_ends_at'                 => $tenant?->trial_ends_at ? $tenant->trial_ends_at->format('Y-m-d\TH:i') : '',
        'trial_ends_at_formatted'       => $tenant?->trial_ends_at ? $tenant->trial_ends_at->format('d M Y, H:i') : '-',
        'trial_days_left'               => $tenant ? $tenant->trialDaysLeft() : 0,
        'is_on_trial'                   => $tenant ? $tenant->isOnTrial() : false,
        'subscription_ends_at'          => $tenant?->subscription_ends_at ? $tenant->subscription_ends_at->format('Y-m-d\TH:i') : '',
        'subscription_ends_at_formatted'=> $tenant?->subscription_ends_at ? $tenant->subscription_ends_at->format('d M Y, H:i') : '-',
        'is_subscription_exempt'        => (bool) ($tenant?->is_subscription_exempt ?? false),
        'has_active_access'             => $user->isSuperAdmin() ? true : ($tenant?->hasActiveAccess() ?? false),
        'created_at_formatted'          => $user->created_at ? $user->created_at->format('d M Y, H:i') : '-',
    ];
@endphp

<div class="flex items-center justify-end space-x-2" x-data>
    <button type="button"
        title="View User Details"
        @click="$dispatch('open-view-modal', @js($userPayload))"
        class="p-1.5 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors">
        <i class="fa-solid fa-eye"></i>
    </button>

    @can('edit users')
        <button type="button"
            title="Set Trial / Expired Date"
            @click="$dispatch('open-edit-modal', { ...@js($userPayload), focusSection: 'subscription' })"
            class="p-1.5 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-lg transition-colors">
            <i class="fa-solid fa-calendar-check"></i>
        </button>

        <button type="button"
            title="Edit User"
            @click="$dispatch('open-edit-modal', @js($userPayload))"
            class="p-1.5 text-green-600 hover:text-green-900 hover:bg-green-50 rounded-lg transition-colors">
            <i class="fa-solid fa-edit"></i>
        </button>
    @endcan

    @can('delete users')
        @if (auth()->id() !== $user->id)
            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="inline"
                onsubmit="return confirm('Are you sure you want to delete {{ addslashes($user->name) }}?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    title="Delete User"
                    class="p-1.5 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </form>
        @endif
    @endcan
</div>
