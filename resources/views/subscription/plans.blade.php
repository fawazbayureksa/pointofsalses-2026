<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Choose Your Plan – {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-950 text-white min-h-screen flex flex-col">

    {{-- Navbar --}}
    <header class="border-b border-gray-800 px-6 py-4 flex items-center justify-between">
        <a href="/" class="text-xl font-bold text-white">
            {{ config('app.name', 'POS System') }}
        </a>
        <div class="flex items-center space-x-4">
            @auth
                <span class="text-sm text-gray-400">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button class="text-sm text-gray-400 hover:text-white transition">Sign Out</button>
                </form>
            @endauth
        </div>
    </header>

    {{-- Flash warning --}}
    @if (session('warning'))
        <div class="bg-amber-900/50 border border-amber-700 text-amber-200 text-sm px-6 py-3 text-center">
            <i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ session('warning') }}
        </div>
    @endif

    {{-- Hero --}}
    <section class="flex-1 py-16 px-4">
        <div class="max-w-5xl mx-auto text-center mb-12">
            <h1 class="text-4xl font-extrabold tracking-tight mb-4">
                Your Free Trial Has Ended
            </h1>
            @if ($tenant)
                <p class="text-gray-400 text-lg">
                    Choose the plan that's right for <strong class="text-white">{{ $tenant->name }}</strong>
                    and get back to selling.
                </p>
            @else
                <p class="text-gray-400 text-lg">Choose the plan that's right for your business.</p>
            @endif
        </div>

        {{-- Plans grid --}}
        <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8">

            {{-- Starter (Free) --}}
            <div class="bg-gray-900 border border-gray-700 rounded-2xl p-8 flex flex-col">
                <div class="mb-6">
                    <span class="text-xs font-semibold uppercase tracking-widest text-gray-400">Starter</span>
                    <p class="mt-3 text-4xl font-extrabold">Free</p>
                    <p class="mt-1 text-gray-400 text-sm">Forever</p>
                </div>
                <ul class="space-y-3 text-sm text-gray-300 flex-1 mb-8">
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-green-400 w-4"></i> 1 Outlet
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-green-400 w-4"></i> Up to 3 Users
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-green-400 w-4"></i> Basic POS
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-green-400 w-4"></i> Basic Inventory
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <i class="fa-solid fa-xmark w-4"></i> Finance Reports
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <i class="fa-solid fa-xmark w-4"></i> Multi-Outlet
                    </li>
                </ul>
                {{-- Downgrade to basic --}}
                <form method="POST" action="{{ route('subscription.select') }}">
                    @csrf
                    <input type="hidden" name="plan" value="basic">
                    <button type="submit"
                        class="w-full py-3 rounded-xl border border-gray-600 text-gray-300 hover:bg-gray-800 font-semibold transition">
                        Continue with Free
                    </button>
                </form>
            </div>

            {{-- Professional --}}
            <div class="bg-gradient-to-b from-indigo-600 to-purple-700 border border-indigo-500 rounded-2xl p-8 flex flex-col relative shadow-xl shadow-indigo-900/40">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                    <span class="bg-amber-400 text-gray-900 text-xs font-bold uppercase tracking-wide px-4 py-1 rounded-full">
                        Most Popular
                    </span>
                </div>
                <div class="mb-6">
                    <span class="text-xs font-semibold uppercase tracking-widest text-indigo-200">Professional</span>
                    <p class="mt-3 text-4xl font-extrabold">Rp 299K</p>
                    <p class="mt-1 text-indigo-200 text-sm">per month</p>
                </div>
                <ul class="space-y-3 text-sm text-white flex-1 mb-8">
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-amber-300 w-4"></i> Up to 5 Outlets
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-amber-300 w-4"></i> Unlimited Users
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-amber-300 w-4"></i> Full POS
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-amber-300 w-4"></i> Full Inventory
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-amber-300 w-4"></i> Finance & Reports
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-amber-300 w-4"></i> Priority Support
                    </li>
                </ul>
                <a href="mailto:{{ config('mail.from.address', 'sales@example.com') }}?subject=Subscribe Professional Plan"
                    class="w-full py-3 rounded-xl bg-white text-indigo-700 font-bold text-center block hover:bg-indigo-50 transition">
                    Subscribe Now
                </a>
            </div>

            {{-- Enterprise --}}
            <div class="bg-gray-900 border border-gray-700 rounded-2xl p-8 flex flex-col">
                <div class="mb-6">
                    <span class="text-xs font-semibold uppercase tracking-widest text-gray-400">Enterprise</span>
                    <p class="mt-3 text-4xl font-extrabold">Custom</p>
                    <p class="mt-1 text-gray-400 text-sm">Contact us for pricing</p>
                </div>
                <ul class="space-y-3 text-sm text-gray-300 flex-1 mb-8">
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-green-400 w-4"></i> Unlimited Outlets
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-green-400 w-4"></i> Unlimited Users
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-green-400 w-4"></i> All Modules
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-green-400 w-4"></i> Multi-Tenant
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-green-400 w-4"></i> Dedicated Support
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-green-400 w-4"></i> Custom Integration
                    </li>
                </ul>
                <a href="mailto:{{ config('mail.from.address', 'sales@example.com') }}?subject=Enterprise Plan Inquiry"
                    class="w-full py-3 rounded-xl border border-gray-600 text-gray-300 hover:bg-gray-800 font-semibold text-center block transition">
                    Contact Sales
                </a>
            </div>

        </div>

        <p class="text-center text-gray-500 text-sm mt-10">
            Need help choosing?
            <a href="mailto:{{ config('mail.from.address', 'support@example.com') }}"
               class="text-indigo-400 hover:underline">Contact our support team</a>.
        </p>
    </section>

</body>
</html>
