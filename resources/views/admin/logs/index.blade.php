@extends('layouts.admin')

@section('title', 'Activity Logs')

@section('page-title', 'Activity Logs')

@section('content')
<div class="space-y-6">
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fa-solid fa-circle-exclamation text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Errors</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <x-admin-card title="Audit Logs">
        <form method="GET" action="{{ route('admin.logs.index') }}" class="mb-4">
            <div class="flex items-center space-x-4">
                <select name="user_id" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Users</option>
                    @foreach($users ?? [] as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
                
                <select name="action" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Actions</option>
                    <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                    <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>Login</option>
                    <option value="logout" {{ request('action') === 'logout' ? 'selected' : '' }}>Logout</option>
                </select>
                
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="From">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="To">
                
                <x-admin-button type="submit" variant="primary" icon="fa-solid fa-filter">
                    Filter
                </x-admin-button>
                
                <a href="{{ route('admin.logs.index') }}" class="text-sm text-gray-600 hover:text-gray-800">
                    Clear Filters
                </a>
            </div>
        </form>

        @php
            $tableHeaders = [
                ['label' => 'ID', 'key' => 'id'],
                ['label' => 'User', 'slot' => function($row) {
                    $user = $row->causer;
                    if ($user) {
                        return '<div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-bold">' . strtoupper(substr($user->name, 0, 1)) . '</div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">' . e($user->name) . '</p>
                                <p class="text-xs text-gray-500">' . e($user->email) . '</p>
                            </div>
                        </div>';
                    }
                    return '<span class="text-gray-500">System</span>';
                }],
                ['label' => 'Description', 'key' => 'description'],
                ['label' => 'Subject Type', 'slot' => function($row) {
                    return $row->subject_type ? class_basename($row->subject_type) : '-';
                }],
                ['label' => 'Subject ID', 'slot' => function($row) {
                    return $row->subject_id ? '#' . $row->subject_id : '-';
                }],
                ['label' => 'IP Address', 'key' => 'ip_address'],
                ['label' => 'Created At', 'key' => 'created_at'],
            ];
            $tableActions = function($row) {
                return '<button onclick="showLogDetails(' . $row->id . ')" class="text-blue-600 hover:text-blue-900" title="View Details">
                    <i class="fa-solid fa-eye"></i>
                </button>';
            };
        @endphp

        <x-admin-table
            :headers="$tableHeaders"
            :rows="$logs ?? []"
            :actions="$tableActions"
        />

        @if(isset($logs) && method_exists($logs, 'links'))
            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        @endif
    </x-admin-card>
</div>

<div x-data="{ open: false, log: null }" id="log-details-modal">
    <x-admin-modal id="view-log-modal" title="Activity Log Details" size="lg">
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">User</p>
                    <p class="text-gray-900" x-text="log?.causer?.name ?? 'System'"></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">IP Address</p>
                    <p class="text-gray-900" x-text="log?.ip_address ?? '-'"></p>
                </div>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500">Description</p>
                <p class="text-gray-900" x-text="log?.description ?? '-'"></p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Subject Type</p>
                    <p class="text-gray-900" x-text="log?.subject_type ?? '-'"></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Subject ID</p>
                    <p class="text-gray-900" x-text="log?.subject_id ? '#' + log.subject_id : '-'"></p>
                </div>
            </div>

            <div x-show="log?.properties && Object.keys(log.properties).length > 0">
                <p class="text-sm font-medium text-gray-500 mb-2">Properties</p>
                <pre class="bg-gray-50 p-4 rounded-lg text-sm overflow-x-auto" x-text="JSON.stringify(log?.properties, null, 2)"></pre>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500">Created At</p>
                <p class="text-gray-900" x-text="log?.created_at ?? '-'"></p>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
            <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                Close
            </button>
        </div>
    </x-admin-modal>
</div>

<script>
function showLogDetails(logId) {
    const logData = @json($logs?->keyBy('id') ?? collect());
    const log = logData[logId];
    if (log) {
        document.querySelector('#log-details-modal').__x.$data.log = log;
        document.querySelector('#log-details-modal').__x.$data.open = true;
    }
}
</script>
@endsection
