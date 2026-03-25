@extends('layouts.admin')

@section('title', 'Roles')
@section('page-title', 'Role Management')

@section('content')
    <div class="space-y-6">
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── Group permissions by resource (last word) ── --}}
        @php
            use Illuminate\Support\Str;
            $grouped = $permissions
                ->groupBy(fn($p) => Str::afterLast(str_replace('_', ' ', $p->name), ' '))
                ->sortKeys();
        @endphp

        <x-admin-card title="All Roles">
            {{-- Header with Create button --}}
            <div class="flex justify-end mb-4">
                <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-create-role'))"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    <i class="fa-solid fa-plus mr-2"></i>Create Role
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Permissions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created At</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($roles as $role)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $role->id }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $role->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $role->permissions->count() }} permissions
                                    @if ($role->permissions->isNotEmpty())
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach ($role->permissions->take(5) as $perm)
                                                <span
                                                    class="inline-flex px-1.5 py-0.5 text-xs bg-blue-50 text-blue-700 rounded">
                                                    {{ $perm->name }}
                                                </span>
                                            @endforeach
                                            @if ($role->permissions->count() > 5)
                                                <span class="text-xs text-gray-400">+{{ $role->permissions->count() - 5 }}
                                                    more</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $role->created_at->format('Y-m-d') }}</td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    {{-- Edit button: dispatch event with role data --}}
                                    <button type="button"
                                        data-role="{{ json_encode(['id' => $role->id, 'name' => $role->name, 'permissions' => $role->permissions->pluck('id')->toArray()]) }}"
                                        onclick="window.dispatchEvent(new CustomEvent('open-edit-role', { detail: JSON.parse(this.dataset.role) }))"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="inline"
                                        onsubmit="return confirm('Delete this role?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">No roles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $roles->links() }}</div>
        </x-admin-card>
    </div>

    {{-- ══════════════════════════════════════════════
         CREATE ROLE MODAL
    ══════════════════════════════════════════════ --}}
    <div x-data="{ open: false }" @open-create-role.window="open = true">
        <x-admin-modal id="create-role-modal" title="Create Role" size="lg">
            <form method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role Name <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="e.g. cashier">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                        <div class="border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-80 overflow-y-auto">
                            @foreach ($grouped as $resource => $perms)
                                <div class="px-4 py-3">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                                        {{ $resource }}</p>
                                    <div class="grid grid-cols-2 gap-1">
                                        @foreach ($perms as $permission)
                                            <label
                                                class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer hover:text-blue-600">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <span>{{ $permission->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" @click="open = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Create Role
                    </button>
                </div>
            </form>
        </x-admin-modal>
    </div>

    {{-- ══════════════════════════════════════════════
         EDIT ROLE MODAL
    ══════════════════════════════════════════════ --}}
    <div x-data="{
        open: false,
        roleId: null,
        roleName: '',
        rolePermissions: [],
        selectAll(ids) {
            ids.map(String).forEach(id => { if (!this.rolePermissions.includes(id)) this.rolePermissions.push(id); });
        },
        deselectAll(ids) {
            const strIds = ids.map(String);
            this.rolePermissions = this.rolePermissions.filter(id => !strIds.includes(String(id)));
        },
    }"
        @open-edit-role.window="
            roleId = $event.detail.id;
            roleName = $event.detail.name;
            rolePermissions = $event.detail.permissions.map(String);
            open = true;
        ">
        <x-admin-modal id="edit-role-modal" title="Edit Role" size="lg">
            <form method="POST" :action="`/admin/roles/${roleId ?? ''}`">
                @csrf
                <input type="hidden" name="_method" value="PUT">

                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role Name <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" required x-model="roleName"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                        <div class="border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-80 overflow-y-auto">
                            @foreach ($grouped as $resource => $perms)
                                @php $permIds = $perms->pluck('id')->toArray(); @endphp
                                <div class="px-4 py-3">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                            {{ $resource }}</p>
                                        <div class="flex items-center space-x-2 text-xs">
                                            <button type="button"
                                                @click="selectAll({{ \Illuminate\Support\Js::from($permIds) }})"
                                                class="text-blue-600 hover:underline">all</button>
                                            <span class="text-gray-300">|</span>
                                            <button type="button"
                                                @click="deselectAll({{ \Illuminate\Support\Js::from($permIds) }})"
                                                class="text-gray-500 hover:underline">none</button>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-1">
                                        @foreach ($perms as $permission)
                                            <label
                                                class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer hover:text-blue-600">
                                                <input type="checkbox" name="permissions[]"
                                                    value="{{ $permission->id }}" x-model="rolePermissions"
                                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <span>{{ $permission->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" @click="open = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Save Changes
                    </button>
                </div>
            </form>
        </x-admin-modal>
    </div>
@endsection
