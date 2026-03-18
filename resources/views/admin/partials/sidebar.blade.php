<aside x-cloak :class="sidebarOpen ? 'w-64' : 'w-20'"
    class="bg-slate-800 text-white transition-all duration-300 flex-shrink-0 min-h-screen relative">
    <div class="flex flex-col h-full">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <h1 x-show="sidebarOpen" class="text-xl font-bold text-white">POS Admin</h1>
            <button @click="sidebarOpen = !sidebarOpen" class="p-2 hover:bg-slate-700 rounded-lg transition-colors">
                <i :class="sidebarOpen ? 'fa-solid fa-chevron-left' : 'fa-solid fa-chevron-right'" class="w-5 h-5"></i>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1 px-3">
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                        :class="currentPage.includes('dashboard') ? 'bg-slate-700 text-white' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-gauge-high w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Dashboard</span>
                    </a>
                </li>
                {{-- @role('super_admin') --}}
                <li>
                    <div class="pt-4 pb-2">
                        <span x-show="sidebarOpen"
                            class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Tenant
                            Management</span>
                    </div>
                    <a href="{{ route('admin.tenants.index') }}"
                        :class="currentPage.includes('tenants') ? 'bg-slate-700 text-white' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-building w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Tenants</span>
                    </a>
                </li>
                {{-- @endrole --}}

                <li>
                    <div class="pt-4 pb-2">
                        <span x-show="sidebarOpen"
                            class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Outlet
                            Management</span>
                    </div>
                    <a href="{{ route('admin.outlets.index') }}"
                        :class="currentPage.includes('outlets') ? 'bg-slate-700 text-white' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-store w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Outlets</span>
                    </a>
                </li>

                <li>
                    <div class="pt-4 pb-2">
                        <span x-show="sidebarOpen"
                            class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Products</span>
                    </div>
                    <a href="{{ route('admin.products.index') }}"
                        :class="currentPage.includes('products') && !currentPage.includes('categories') ?
                            'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-box w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Products</span>
                    </a>
                    <a href="{{ route('admin.categories.index') }}"
                        :class="currentPage.includes('categories') ? 'bg-slate-700 text-white' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-tags w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Categories</span>
                    </a>
                </li>

                <li>
                    <div class="pt-4 pb-2">
                        <span x-show="sidebarOpen"
                            class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Sales</span>
                    </div>
                    <a href="{{ route('admin.orders.index') }}"
                        :class="currentPage.includes('orders') ? 'bg-slate-700 text-white' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-shopping-cart w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Orders</span>
                    </a>
                    {{-- <a href="{{ route('admin.payments.index') }}"
                        :class="currentPage.includes('payments') ? 'bg-slate-700 text-white' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-money-bill w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Payments</span>
                    </a> --}}
                </li>

                <li>
                    <div class="pt-4 pb-2">
                        <span x-show="sidebarOpen"
                            class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Customers</span>
                    </div>
                    <a href="{{ route('admin.customers.index') }}"
                        :class="currentPage.includes('customers') ? 'bg-slate-700 text-white' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-users w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Customers</span>
                    </a>
                </li>

                <li>
                    <div class="pt-4 pb-2">
                        <span x-show="sidebarOpen"
                            class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Inventory</span>
                    </div>
                    {{-- <a href="{{ route('admin.inventory.stock') }}"
                        :class="currentPage.includes('inventory/stock') ? 'bg-slate-700 text-white' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-warehouse w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Stock</span>
                    </a>
                    <a href="{{ route('admin.inventory.movements') }}"
                        :class="currentPage.includes('inventory/movements') ? 'bg-slate-700 text-white' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-arrow-right-arrow-left w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Movements</span>
                    </a> --}}
                </li>

                @can('manage users')
                    <li>
                        <div class="pt-4 pb-2">
                            <span x-show="sidebarOpen"
                                class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">User
                                Management</span>
                        </div>
                        <a href="{{ route('admin.users.index') }}"
                            :class="currentPage.includes('users') ? 'bg-slate-700 text-white' :
                                'text-slate-300 hover:bg-slate-700 hover:text-white'"
                            class="flex items-center px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-user-gear w-5 text-center"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3">Users</span>
                        </a>
                        <a href="{{ route('admin.roles.index') }}"
                            :class="currentPage.includes('roles') ? 'bg-slate-700 text-white' :
                                'text-slate-300 hover:bg-slate-700 hover:text-white'"
                            class="flex items-center px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-shield-halved w-5 text-center"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3">Roles</span>
                        </a>
                        <a href="{{ route('admin.permissions.index') }}"
                            :class="currentPage.includes('permissions') ? 'bg-slate-700 text-white' :
                                'text-slate-300 hover:bg-slate-700 hover:text-white'"
                            class="flex items-center px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-key w-5 text-center"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3">Permissions</span>
                        </a>
                    </li>
                @endcan

                <li>
                    <div class="pt-4 pb-2">
                        <span x-show="sidebarOpen"
                            class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">System
                            Settings</span>
                    </div>
                    {{-- <a href="{{ route('admin.settings.index') }}"
                        :class="currentPage.includes('settings') && !currentPage.includes('tax') && !currentPage.includes(
                                'currency') ? 'bg-slate-700 text-white' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-gear w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Settings</span>
                    </a>
                    <a href="{{ route('admin.settings.tax') }}"
                        :class="currentPage.includes('tax') ? 'bg-slate-700 text-white' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-percent w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Tax</span>
                    </a>
                    <a href="{{ route('admin.settings.currency') }}"
                        :class="currentPage.includes('currency') ? 'bg-slate-700 text-white' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-dollar-sign w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Currency</span>
                    </a> --}}
                </li>

                @can('view activity logs')
                    <li>
                        <div class="pt-4 pb-2">
                            <span x-show="sidebarOpen"
                                class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Activity
                                Logs</span>
                        </div>
                        <a href="{{ route('admin.logs.index') }}"
                            :class="currentPage.includes('logs') ? 'bg-slate-700 text-white' :
                                'text-slate-300 hover:bg-slate-700 hover:text-white'"
                            class="flex items-center px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-clock-rotate-left w-5 text-center"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3">Audit Logs</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </nav>

        <div class="p-4 border-t border-slate-700">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-slate-600 flex items-center justify-center">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div x-show="sidebarOpen" x-transition class="ml-3">
                    <p class="text-sm font-medium">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-slate-400">{{ Auth::user()->email }}</p>
                </div>
            </div>
        </div>
    </div>
</aside>
