<header class="bg-white shadow-sm border-b border-gray-200">
    <div class="flex items-center justify-between px-6 py-4">
        <div class="flex items-center">
            <h2 class="text-xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h2>
        </div>

        <div class="flex items-center space-x-4">
            <div x-data="{ searchOpen: false }" class="relative">
                <button @click="searchOpen = !searchOpen"
                    class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <div x-show="searchOpen" @click.away="searchOpen = false" x-transition
                    class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 p-4">
                    <input type="text" placeholder="Search..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <div x-data="{ notificationsOpen: false }" class="relative">
                <button @click="notificationsOpen = !notificationsOpen"
                    class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors relative">
                    <i class="fa-solid fa-bell"></i>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
                <div x-show="notificationsOpen" @click.away="notificationsOpen = false" x-transition
                    class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800">Notifications</h3>
                    </div>
                    <div class="max-h-64 overflow-y-auto">
                        <a href="#" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100">
                            <p class="text-sm text-gray-800 font-medium">New order received</p>
                            <p class="text-xs text-gray-500 mt-1">2 minutes ago</p>
                        </a>
                        <a href="#" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100">
                            <p class="text-sm text-gray-800 font-medium">Low stock alert</p>
                            <p class="text-xs text-gray-500 mt-1">15 minutes ago</p>
                        </a>
                        <a href="#" class="block px-4 py-3 hover:bg-gray-50">
                            <p class="text-sm text-gray-800 font-medium">Payment received</p>
                            <p class="text-xs text-gray-500 mt-1">1 hour ago</p>
                        </a>
                    </div>
                    <div class="p-4 border-t border-gray-200">
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-700">View all notifications</a>
                    </div>
                </div>
            </div>

            <div x-data="{ dropdownOpen: false }" class="relative">
                <button @click="dropdownOpen = !dropdownOpen"
                    class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <span class="hidden md:block text-sm font-medium text-gray-700">{{ Auth::user()->name }}</span>
                    <i class="fa-solid fa-chevron-down text-xs text-gray-500"></i>
                </button>
                <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-transition
                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                    <div class="py-2">
                        <a href="{{ route('admin.profile') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fa-solid fa-user mr-2"></i> Profile
                        </a>
                        {{-- <a href="{{ route('admin.settings.index') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fa-solid fa-gear mr-2"></i> Settings
                        </a> --}}
                        {{-- @if (tenancy()->initialized) --}}
                        @if (Auth::user()->tenant)
                            <div class="px-4 py-2 text-xs text-gray-500 border-t border-gray-100">
                                <p class="font-medium">Tenant:</p>
                                <p>{{ Auth::user()->tenant->name }}</p>
                                @if (Auth::user()->tenant->isOnTrial())
                                    <p class="mt-1 text-blue-600 font-semibold">
                                        Trial: {{ Auth::user()->tenant->trialDaysLeft() }}d left
                                    </p>
                                @elseif (Auth::user()->tenant->subscription_skipped)
                                    <p class="mt-1 text-amber-600 font-semibold">Free Access</p>
                                @else
                                    <p class="mt-1 text-gray-500 capitalize">Plan: {{ Auth::user()->tenant->plan }}</p>
                                @endif
                            </div>
                        @endif
                        {{-- @endif --}}
                        <div class="border-t border-gray-200 mt-2 pt-2">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <i class="fa-solid fa-right-from-bracket mr-2"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @if (Auth::user()->tenant && Auth::user()->tenant->isOnTrial())
            @php $daysLeft = Auth::user()->tenant->trialDaysLeft(); @endphp
            <div class="bg-blue-50 border-t border-blue-200 px-6 py-2 flex items-center justify-between text-sm">
                <span class="text-blue-700">
                    <i class="fa-solid fa-clock mr-1"></i>
                    Free trial:
                    @if ($daysLeft > 0)
                        <strong>{{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} remaining</strong>
                    @else
                        <strong>less than 1 day remaining</strong>
                    @endif
                </span>
                <a href="{{ route('subscription.plans') }}"
                   class="text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded-full transition">
                    Upgrade
                </a>
            </div>
        @endif
    </div>
</header>
