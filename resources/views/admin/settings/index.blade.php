@extends('layouts.admin')

@section('title', 'Settings')

@section('page-title', 'System Settings')

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

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fa-solid fa-circle-check text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <x-admin-card title="General Settings">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PUT')
            
            <x-admin-form-input name="app_name" label="Application Name" required value="{{ old('app_name', setting('app_name')) }}" />
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="app_email" label="Application Email" type="email" value="{{ old('app_email', setting('app_email')) }}" />
                <x-admin-form-input name="app_phone" label="Application Phone" type="tel" value="{{ old('app_phone', setting('app_phone')) }}" />
            </div>
            
            <x-admin-form-input name="app_address" label="Application Address" type="textarea" value="{{ old('app_address', setting('app_address')) }}" />
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="timezone" label="Timezone" type="select" :options="['UTC' => 'UTC', 'America/New_York' => 'America/New_York', 'America/Los_Angeles' => 'America/Los_Angeles', 'Europe/London' => 'Europe/London', 'Asia/Tokyo' => 'Asia/Tokyo']" value="{{ old('timezone', setting('timezone', 'UTC')) }}" />
                <x-admin-form-input name="date_format" label="Date Format" type="select" :options="['Y-m-d' => 'YYYY-MM-DD', 'm/d/Y' => 'MM/DD/YYYY', 'd/m/Y' => 'DD/MM/YYYY']" value="{{ old('date_format', setting('date_format', 'Y-m-d')) }}" />
            </div>
            
            <x-admin-form-input name="logo" label="Application Logo" type="file" />
            
            <div class="flex items-center justify-end space-x-3 mt-6">
                <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">
                    Save Settings
                </x-admin-button>
            </div>
        </form>
    </x-admin-card>

    <x-admin-card title="Email Settings">
        <form method="POST" action="{{ route('admin.settings.email.update') }}">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="mail_host" label="Mail Host" value="{{ old('mail_host', setting('mail_host')) }}" />
                <x-admin-form-input name="mail_port" label="Mail Port" type="number" value="{{ old('mail_port', setting('mail_port')) }}" />
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="mail_username" label="Mail Username" value="{{ old('mail_username', setting('mail_username')) }}" />
                <x-admin-form-input name="mail_password" label="Mail Password" type="password" placeholder="Leave blank to keep current" />
            </div>
            
            <x-admin-form-input name="mail_encryption" label="Encryption" type="select" :options="['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None']" value="{{ old('mail_encryption', setting('mail_encryption', 'tls')) }}" />
            
            <div class="flex items-center justify-end space-x-3 mt-6">
                <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">
                    Save Email Settings
                </x-admin-button>
            </div>
        </form>
    </x-admin-card>

    <x-admin-card title="Backup Settings">
        <form method="POST" action="{{ route('admin.settings.backup.update') }}">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="backup_enabled" label="Enable Auto Backup" type="checkbox" value="1" :checked="setting('backup_enabled', false)" />
                <x-admin-form-input name="backup_frequency" label="Backup Frequency" type="select" :options="['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly']" value="{{ old('backup_frequency', setting('backup_frequency', 'daily')) }}" />
            </div>
            
            <x-admin-form-input name="backup_retention" label="Backup Retention (Days)" type="number" value="{{ old('backup_retention', setting('backup_retention', 30)) }}" />
            
            <div class="flex items-center justify-end space-x-3 mt-6">
                <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">
                    Save Backup Settings
                </x-admin-button>
            </div>
        </form>
    </x-admin-card>
</div>
@endsection
