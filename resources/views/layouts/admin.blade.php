<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 h-full" x-data="{ sidebarOpen: window.innerWidth >= 1024, currentPage: '{{ Request::path() }}' }">
    <div class="flex h-full">
        @include('admin.partials.sidebar')

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            @include('admin.partials.navbar')

            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>

            @include('admin.partials.footer')
        </div>
    </div>

    @include('admin.components.modal')

    @stack('scripts')
</body>
</html>
