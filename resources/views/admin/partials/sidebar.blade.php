<aside x-cloak
    x-data="{
        menuSearch: '',
        hasResults: true,
        init() {
            this.$nextTick(() => {
                this.restoreScroll();
                setTimeout(() => this.restoreScroll(), 60);
            });
        },
        restoreScroll() {
            if (!this.$refs.navScroll) return;

            const savedScroll = sessionStorage.getItem('sidebar_scroll_top');
            const activeLink = this.$refs.navScroll.querySelector('.active-menu-item');

            if (savedScroll !== null) {
                this.$refs.navScroll.scrollTop = parseInt(savedScroll, 10);
            }

            if (activeLink) {
                const navRect = this.$refs.navScroll.getBoundingClientRect();
                const linkRect = activeLink.getBoundingClientRect();
                const isOutOfView = linkRect.top < (navRect.top + 20) || linkRect.bottom > (navRect.bottom - 20);

                if (savedScroll === null || isOutOfView) {
                    activeLink.scrollIntoView({ block: 'nearest', behavior: 'instant' });
                    sessionStorage.setItem('sidebar_scroll_top', this.$refs.navScroll.scrollTop);
                }
            }
        },
        updateSearch() {
            if (!this.menuSearch.trim()) {
                this.hasResults = true;
                this.$nextTick(() => this.restoreScroll());
                return;
            }
            if (this.$refs.navScroll) {
                this.$refs.navScroll.scrollTop = 0;
            }
            this.$nextTick(() => {
                if (this.$refs.menuList) {
                    const links = this.$refs.menuList.querySelectorAll('a.sidebar-link');
                    this.hasResults = Array.from(links).some(el => el.style.display !== 'none');
                }
            });
        },
        matches(title, keywords = '') {
            if (!this.menuSearch.trim()) return true;
            const q = this.menuSearch.toLowerCase().trim();
            return title.toLowerCase().includes(q) || (keywords && keywords.toLowerCase().includes(q));
        }
    }"
    x-effect="menuSearch; updateSearch()"
    @keydown.window="
        if (($event.ctrlKey || $event.metaKey) && $event.key.toLowerCase() === 'k') {
            $event.preventDefault();
            sidebarOpen = true;
            $nextTick(() => $refs.sidebarSearchInput && $refs.sidebarSearchInput.focus());
        }
    "
    :class="sidebarOpen ? 'w-64' : 'w-20'"
    class="bg-slate-800 text-white transition-all duration-300 flex-shrink-0 min-h-screen relative flex flex-col">
    <div class="flex flex-col h-full">
        {{-- ── Sidebar Header ── --}}
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <h1 x-show="sidebarOpen" class="text-xl font-bold text-white tracking-tight">POS Admin</h1>
            <button @click="sidebarOpen = !sidebarOpen" class="p-2 hover:bg-slate-700 rounded-lg transition-colors" title="Toggle Sidebar">
                <i :class="sidebarOpen ? 'fa-solid fa-chevron-left' : 'fa-solid fa-chevron-right'" class="w-5 h-5"></i>
            </button>
        </div>

        {{-- ── Sidebar Menu Search ── --}}
        <div class="px-3 pt-3 pb-2 border-b border-slate-700/60">
            <div x-show="sidebarOpen" x-transition class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                <input type="text"
                    x-ref="sidebarSearchInput"
                    x-model="menuSearch"
                    @keydown.escape="if (menuSearch) { menuSearch = ''; } else { $el.blur(); }"
                    placeholder="Search menu... (Ctrl+K)"
                    class="w-full bg-slate-700/60 hover:bg-slate-700 focus:bg-slate-900 text-white placeholder-slate-400 text-xs rounded-lg pl-8 pr-7 py-2 border border-slate-600/80 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all">
                <button x-show="menuSearch.length > 0"
                    @click="menuSearch = ''; $refs.sidebarSearchInput.focus()"
                    type="button"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white p-1 rounded-full transition-colors"
                    title="Clear search">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>
            <div x-show="!sidebarOpen" class="flex justify-center">
                <button @click="sidebarOpen = true; $nextTick(() => $refs.sidebarSearchInput && $refs.sidebarSearchInput.focus())"
                    type="button"
                    class="w-10 h-10 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-colors"
                    title="Search menu (Ctrl+K)">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </button>
            </div>
        </div>

        {{-- ── Sidebar Navigation ── --}}
        <nav x-ref="navScroll"
            @scroll.passive="if (!menuSearch.trim()) sessionStorage.setItem('sidebar_scroll_top', $el.scrollTop)"
            class="flex-1 overflow-y-auto py-4">
            <ul x-ref="menuList"
                @click="if ($event.target.closest('a.sidebar-link')) { sessionStorage.setItem('sidebar_scroll_top', $refs.navScroll.scrollTop); }"
                class="space-y-1 px-3">
                {{-- Dashboard --}}
                <li x-show="matches('Dashboard', 'home beranda ringkasan')">
                    <a href="{{ route('admin.dashboard') }}"
                        :class="currentPage.includes('dashboard') ? 'bg-slate-700 text-white active-menu-item' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-gauge-high w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Dashboard</span>
                    </a>
                </li>

                {{-- Guide Manual --}}
                <li x-show="matches('Guide Manual', 'panduan buku petunjuk onboarding bantuan faq')">
                    <a href="{{ route('admin.guide') }}"
                        :class="currentPage.includes('guide') ? 'bg-slate-700 text-white active-menu-item' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-book-open w-5 text-center text-emerald-400"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3 flex items-center justify-between flex-1">
                            <span>Guide Manual</span>
                            <span class="text-[10px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-1.5 py-0.5 rounded">ID</span>
                        </span>
                    </a>
                </li>

                {{-- Notifications --}}
                <li x-show="matches('Notifications', 'notifikasi pemberitahuan pesan alerts')">
                    @php
                        $sidebarUnreadCount = Auth::user()->unreadNotifications()->count();
                    @endphp
                    <a href="{{ route('admin.notifications.index') }}"
                        :class="currentPage.includes('notifications') ? 'bg-slate-700 text-white active-menu-item' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-bell w-5 text-center text-amber-400"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3 flex items-center justify-between flex-1">
                            <span>Notifications</span>
                            @if ($sidebarUnreadCount > 0)
                                <span class="text-[10px] font-bold bg-red-500 text-white px-1.5 py-0.5 rounded-full">
                                    {{ $sidebarUnreadCount > 99 ? '99+' : $sidebarUnreadCount }}
                                </span>
                            @endif
                        </span>
                    </a>
                </li>

                {{-- Tenant Management (Super Admin) --}}
                @role('super_admin')
                    <li x-show="matches('Tenants', 'tenant management penyewa toko merchant') || matches('Subscriptions', 'tenant management langganan paket billing pro')">
                        <div x-show="sidebarOpen" class="pt-4 pb-2">
                            <span class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Tenant Management</span>
                        </div>
                        <a href="{{ route('admin.tenants.index') }}"
                            x-show="matches('Tenants', 'tenant management penyewa toko merchant')"
                            :class="currentPage.includes('tenants') ? 'bg-slate-700 text-white active-menu-item' :
                                'text-slate-300 hover:bg-slate-700 hover:text-white'"
                            class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-building w-5 text-center"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3">Tenants</span>
                        </a>
                        <a href="{{ route('admin.subscription.index') }}"
                            x-show="matches('Subscriptions', 'tenant management langganan paket billing pro')"
                            :class="currentPage.includes('subscription') ? 'bg-slate-700 text-white active-menu-item' :
                                'text-slate-300 hover:bg-slate-700 hover:text-white'"
                            class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-crown w-5 text-center text-amber-400"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3 flex items-center justify-between flex-1">
                                <span>Subscriptions</span>
                                <span class="text-[10px] font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30 px-1.5 py-0.5 rounded">PRO</span>
                            </span>
                        </a>
                    </li>
                @endrole

                {{-- Subscription & Plan (Tenant) --}}
                @if (Auth::user()->tenant)
                    @php
                        $sidebarTenant = Auth::user()->tenant;
                    @endphp
                    <li x-show="matches('Subscription', 'plan langganan paket trial vip') || matches('Upgrade & Plans', 'plan paket harga billing pro ent enterprise') || matches('Billing & Invoices', 'plan tagihan invoice pembayaran billing kwitansi')">
                        <div x-show="sidebarOpen" class="pt-4 pb-2">
                            <span class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Subscription &amp; Plan</span>
                        </div>
                        <a href="{{ route('admin.subscription.index') }}"
                            x-show="matches('Subscription', 'plan langganan paket trial vip')"
                            :class="currentPage.includes('subscription') ? 'bg-slate-700 text-white active-menu-item' :
                                'text-slate-300 hover:bg-slate-700 hover:text-white'"
                            class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-crown w-5 text-center text-amber-400"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3 flex items-center justify-between flex-1">
                                <span>Subscription</span>
                                @if ($sidebarTenant->isOnTrial())
                                    <span class="text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 px-1.5 py-0.5 rounded">
                                        {{ $sidebarTenant->trialDaysLeft() }}d Trial
                                    </span>
                                @elseif ($sidebarTenant->isExempt())
                                    <span class="text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-1.5 py-0.5 rounded">
                                        VIP
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 px-1.5 py-0.5 rounded uppercase">
                                        {{ $sidebarTenant->plan === 'basic' ? 'Free' : ($sidebarTenant->plan === 'professional' ? 'Pro' : 'Ent') }}
                                    </span>
                                @endif
                            </span>
                        </a>
                        <a href="{{ route('admin.subscription.index') }}#plans"
                            x-show="matches('Upgrade & Plans', 'plan paket harga billing pro ent enterprise')"
                            class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors text-slate-300 hover:bg-slate-700 hover:text-white">
                            <i class="fa-solid fa-rocket w-5 text-center text-indigo-400"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3">Upgrade &amp; Plans</span>
                        </a>
                        <a href="{{ route('admin.subscription.index') }}#billing"
                            x-show="matches('Billing & Invoices', 'plan tagihan invoice pembayaran billing kwitansi')"
                            class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors text-slate-300 hover:bg-slate-700 hover:text-white">
                            <i class="fa-solid fa-file-invoice-dollar w-5 text-center text-slate-400"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3">Billing &amp; Invoices</span>
                        </a>
                    </li>
                @endif

                {{-- Outlet Management --}}
                <li x-show="matches('Outlets', 'outlet cabang toko branch store lokasi')">
                    <div x-show="sidebarOpen" class="pt-4 pb-2">
                        <span class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Outlet Management</span>
                    </div>
                    <a href="{{ route('admin.outlets.index') }}"
                        :class="currentPage.includes('outlets') ? 'bg-slate-700 text-white active-menu-item' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-store w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Outlets</span>
                    </a>
                </li>

                {{-- Products --}}
                <li x-show="matches('Products', 'produk barang item katalog') || matches('Categories', 'kategori category jenis produk')">
                    <div x-show="sidebarOpen" class="pt-4 pb-2">
                        <span class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Products</span>
                    </div>
                    <a href="{{ route('admin.products.index') }}"
                        x-show="matches('Products', 'produk barang item katalog')"
                        :class="currentPage.includes('products') && !currentPage.includes('categories') ?
                            'bg-slate-700 text-white active-menu-item' : 'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-box w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Products</span>
                    </a>
                    <a href="{{ route('admin.categories.index') }}"
                        x-show="matches('Categories', 'kategori category jenis produk')"
                        :class="currentPage.includes('categories') ? 'bg-slate-700 text-white active-menu-item' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-tags w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Categories</span>
                    </a>
                </li>

                {{-- Sales --}}
                <li x-show="matches('Cashier / POS', 'sales kasir pos transaksi register point of sale checkout') || matches('Orders', 'sales pesanan order riwayat transaksi invoice') || matches('Payments', 'sales pembayaran payments kas cash qris card transfer edc')">
                    <div x-show="sidebarOpen" class="pt-4 pb-2">
                        <span class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Sales</span>
                    </div>
                    <a href="{{ route('admin.pos.index') }}"
                        x-show="matches('Cashier / POS', 'sales kasir pos transaksi register point of sale checkout')"
                        :class="currentPage.includes('pos') ? 'bg-slate-700 text-white active-menu-item' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-cash-register w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Cashier / POS</span>
                    </a>
                    <a href="{{ route('admin.orders.index') }}"
                        x-show="matches('Orders', 'sales pesanan order riwayat transaksi invoice')"
                        :class="currentPage.includes('orders') ? 'bg-slate-700 text-white active-menu-item' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-shopping-cart w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Orders</span>
                    </a>
                    <a href="{{ route('admin.orders.payments') }}"
                        x-show="matches('Payments', 'sales pembayaran payments kas cash qris card transfer edc')"
                        :class="currentPage.includes('orders-payments') ? 'bg-slate-700 text-white active-menu-item' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-money-bill w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Payments</span>
                    </a>
                </li>

                {{-- Customers --}}
                <li x-show="matches('Customers', 'pelanggan member loyalitas pembeli crm')">
                    <div x-show="sidebarOpen" class="pt-4 pb-2">
                        <span class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Customers</span>
                    </div>
                    <a href="{{ route('admin.customers.index') }}"
                        :class="currentPage.includes('customers') ? 'bg-slate-700 text-white active-menu-item' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-users w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Customers</span>
                    </a>
                </li>

                {{-- Inventory --}}
                <li x-show="matches('Stock', 'inventory stok inventori gudang barang persediaan opname') || matches('Movements', 'inventory mutasi perpindahan transfer stock movement riwayat')">
                    <div x-show="sidebarOpen" class="pt-4 pb-2">
                        <span class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Inventory</span>
                    </div>
                    <a href="{{ route('admin.inventory.stock') }}"
                        x-show="matches('Stock', 'inventory stok inventori gudang barang persediaan opname')"
                        :class="currentPage.includes('inventory/stock') ? 'bg-slate-700 text-white active-menu-item' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-warehouse w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Stock</span>
                    </a>
                    <a href="{{ route('admin.inventory.movements') }}"
                        x-show="matches('Movements', 'inventory mutasi perpindahan transfer stock movement riwayat')"
                        :class="currentPage.includes('inventory/movements') ? 'bg-slate-700 text-white active-menu-item' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-arrow-right-arrow-left w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Movements</span>
                    </a>
                </li>

                {{-- Supplier Management (Optional Module) --}}
                @if (supplier_management_enabled())
                    <li x-show="matches('Suppliers', 'supplier pemasok vendor distributor') || matches('Supplier Products', 'supplier produk katalog harga beli pemasok') || matches('Purchase Orders', 'supplier po pembelian purchase order pesanan beli') || matches('Goods Receiving', 'supplier penerimaan barang surat jalan gr receiving gudang') || matches('Supplier Invoices', 'supplier faktur tagihan hutang invoice payable') || matches('Supplier Payments', 'supplier pembayaran hutang bayar payment payable') || matches('Supplier Reports', 'supplier laporan pemasok procurement report hutang analisis')">
                        <div x-show="sidebarOpen" class="pt-4 pb-2 flex items-center justify-between">
                            <span class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Supplier Management</span>
                        </div>
                        <a href="{{ route('admin.suppliers.index') }}"
                            x-show="matches('Suppliers', 'supplier pemasok vendor distributor')"
                            :class="currentPage.includes('admin/suppliers') ? 'bg-slate-700 text-white active-menu-item' :
                                'text-slate-300 hover:bg-slate-700 hover:text-white'"
                            class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-truck-field w-5 text-center"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3">Suppliers</span>
                        </a>
                        <a href="{{ route('admin.supplier-products.index') }}"
                            x-show="matches('Supplier Products', 'supplier produk katalog harga beli pemasok')"
                            :class="currentPage.includes('supplier-products') ? 'bg-slate-700 text-white active-menu-item' :
                                'text-slate-300 hover:bg-slate-700 hover:text-white'"
                            class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-boxes-packing w-5 text-center"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3">Supplier Products</span>
                        </a>
                        <a href="{{ route('admin.purchase-orders.index') }}"
                            x-show="matches('Purchase Orders', 'supplier po pembelian purchase order pesanan beli')"
                            :class="currentPage.includes('purchase-orders') ? 'bg-slate-700 text-white active-menu-item' :
                                'text-slate-300 hover:bg-slate-700 hover:text-white'"
                            class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-file-signature w-5 text-center"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3">Purchase Orders</span>
                        </a>
                        <a href="{{ route('admin.goods-receipts.index') }}"
                            x-show="matches('Goods Receiving', 'supplier penerimaan barang surat jalan gr receiving gudang')"
                            :class="currentPage.includes('goods-receipts') ? 'bg-slate-700 text-white active-menu-item' :
                                'text-slate-300 hover:bg-slate-700 hover:text-white'"
                            class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-dolly w-5 text-center"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3">Goods Receiving</span>
                        </a>
                        <a href="{{ route('admin.supplier-invoices.index') }}"
                            x-show="matches('Supplier Invoices', 'supplier faktur tagihan hutang invoice payable')"
                            :class="currentPage.includes('supplier-invoices') ? 'bg-slate-700 text-white active-menu-item' :
                                'text-slate-300 hover:bg-slate-700 hover:text-white'"
                            class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-file-invoice w-5 text-center"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3">Supplier Invoices</span>
                        </a>
                        <a href="{{ route('admin.supplier-payments.index') }}"
                            x-show="matches('Supplier Payments', 'supplier pembayaran hutang bayar payment payable')"
                            :class="currentPage.includes('supplier-payments') ? 'bg-slate-700 text-white active-menu-item' :
                                'text-slate-300 hover:bg-slate-700 hover:text-white'"
                            class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-money-check-dollar w-5 text-center"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3">Supplier Payments</span>
                        </a>
                        <a href="{{ route('admin.supplier-reports.index') }}"
                            x-show="matches('Supplier Reports', 'supplier laporan pemasok procurement report hutang analisis')"
                            :class="currentPage.includes('supplier-reports') ? 'bg-slate-700 text-white active-menu-item' :
                                'text-slate-300 hover:bg-slate-700 hover:text-white'"
                            class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-chart-column w-5 text-center"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3">Supplier Reports</span>
                        </a>
                    </li>
                @endif

                {{-- Advanced Reports --}}
                <li x-show="matches('Overview', 'reports advanced laporan ringkasan') || matches('Sales', 'reports advanced laporan penjualan omzet') || matches('Products', 'reports advanced laporan produk margin') || matches('Customers', 'reports advanced laporan pelanggan') || matches('Inventory', 'reports advanced laporan stok inventori')">
                    <div x-show="sidebarOpen" class="pt-4 pb-2">
                        <span class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Advanced Reports</span>
                    </div>
                    <a href="{{ route('admin.reports.index') }}"
                        x-show="matches('Overview', 'reports advanced laporan ringkasan')"
                        :class="currentPage.includes('reports') && !currentPage.includes('reports/sales') && !currentPage.includes('reports/products') && !currentPage.includes('reports/customers') && !currentPage.includes('reports/inventory') ? 'bg-slate-700 text-white active-menu-item' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Overview</span>
                    </a>
                    <a href="{{ route('admin.reports.sales') }}"
                        x-show="matches('Sales', 'reports advanced laporan penjualan omzet')"
                        :class="currentPage.includes('reports/sales') ? 'bg-slate-700 text-white active-menu-item' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-chart-line w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Sales</span>
                    </a>
                    <a href="{{ route('admin.reports.products') }}"
                        x-show="matches('Products', 'reports advanced laporan produk margin')"
                        :class="currentPage.includes('reports/products') ? 'bg-slate-700 text-white active-menu-item' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-box w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Products</span>
                    </a>
                    <a href="{{ route('admin.reports.customers') }}"
                        x-show="matches('Customers', 'reports advanced laporan pelanggan')"
                        :class="currentPage.includes('reports/customers') ? 'bg-slate-700 text-white active-menu-item' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-users w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Customers</span>
                    </a>
                    <a href="{{ route('admin.reports.inventory') }}"
                        x-show="matches('Inventory', 'reports advanced laporan stok inventori')"
                        :class="currentPage.includes('reports/inventory') ? 'bg-slate-700 text-white active-menu-item' :
                            'text-slate-300 hover:bg-slate-700 hover:text-white'"
                        class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-warehouse w-5 text-center"></i>
                        <span x-show="sidebarOpen" x-transition class="ml-3">Inventory</span>
                    </a>
                </li>

                {{-- User Management --}}
                @can('manage users')
                    <li x-show="matches('Users', 'user management pengguna kasir staf admin karyawan') || matches('Roles', 'user management role peran hak akses') || matches('Permissions', 'user management permission izin otorisasi')">
                        <div x-show="sidebarOpen" class="pt-4 pb-2">
                            <span class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">User Management</span>
                        </div>
                        <a href="{{ route('admin.users.index') }}"
                            x-show="matches('Users', 'user management pengguna kasir staf admin karyawan')"
                            :class="currentPage.includes('users') ? 'bg-slate-700 text-white active-menu-item' :
                                'text-slate-300 hover:bg-slate-700 hover:text-white'"
                            class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-user-gear w-5 text-center"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3">Users</span>
                        </a>
                        @role('super_admin')
                            <a href="{{ route('admin.roles.index') }}"
                                x-show="matches('Roles', 'user management role peran hak akses')"
                                :class="currentPage.includes('roles') ? 'bg-slate-700 text-white active-menu-item' :
                                    'text-slate-300 hover:bg-slate-700 hover:text-white'"
                                class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                                <i class="fa-solid fa-shield-halved w-5 text-center"></i>
                                <span x-show="sidebarOpen" x-transition class="ml-3">Roles</span>
                            </a>
                            <a href="{{ route('admin.permissions.index') }}"
                                x-show="matches('Permissions', 'user management permission izin otorisasi')"
                                :class="currentPage.includes('permissions') ? 'bg-slate-700 text-white active-menu-item' :
                                    'text-slate-300 hover:bg-slate-700 hover:text-white'"
                                class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                                <i class="fa-solid fa-key w-5 text-center"></i>
                                <span x-show="sidebarOpen" x-transition class="ml-3">Permissions</span>
                            </a>
                        @endrole
                    </li>
                @endcan

                {{-- System Settings --}}
                @can('manage settings')
                    <li x-show="matches('Settings', 'system settings pengaturan toko umum profil') || matches('Tax', 'system settings pajak ppn pb1') || matches('Currency', 'system settings mata uang rupiah idr format')">
                        <div x-show="sidebarOpen" class="pt-4 pb-2">
                            <span class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">System Settings</span>
                        </div>
                        <a href="{{ route('admin.settings.index') }}"
                            x-show="matches('Settings', 'system settings pengaturan toko umum profil')"
                            :class="currentPage.includes('settings') && !currentPage.includes('tax') && !currentPage.includes('currency') ? 'bg-slate-700 text-white active-menu-item' :
                                'text-slate-300 hover:bg-slate-700 hover:text-white'"
                            class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-gear w-5 text-center"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3">Settings</span>
                        </a>
                        <a href="{{ route('admin.settings.tax') }}"
                            x-show="matches('Tax', 'system settings pajak ppn pb1')"
                            :class="currentPage.includes('tax') ? 'bg-slate-700 text-white active-menu-item' :
                                'text-slate-300 hover:bg-slate-700 hover:text-white'"
                            class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-percent w-5 text-center"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3">Tax</span>
                        </a>
                        <a href="{{ route('admin.settings.currency') }}"
                            x-show="matches('Currency', 'system settings mata uang rupiah idr format')"
                            :class="currentPage.includes('currency') ? 'bg-slate-700 text-white active-menu-item' :
                                'text-slate-300 hover:bg-slate-700 hover:text-white'"
                            class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-dollar-sign w-5 text-center"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3">Currency</span>
                        </a>
                    </li>
                @endcan

                {{-- Activity Logs --}}
                @can('view activity logs')
                    <li x-show="matches('Audit Logs', 'activity logs audit riwayat aktivitas log')">
                        <div x-show="sidebarOpen" class="pt-4 pb-2">
                            <span class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Activity Logs</span>
                        </div>
                        <a href="{{ route('admin.logs.index') }}"
                            :class="currentPage.includes('logs') ? 'bg-slate-700 text-white active-menu-item' :
                                'text-slate-300 hover:bg-slate-700 hover:text-white'"
                            class="sidebar-link flex items-center px-3 py-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-clock-rotate-left w-5 text-center"></i>
                            <span x-show="sidebarOpen" x-transition class="ml-3">Audit Logs</span>
                        </a>
                    </li>
                @endcan

                {{-- No search results empty state --}}
                <li x-show="menuSearch.trim().length > 0 && !hasResults" class="px-3 py-8 text-center text-slate-400">
                    <div class="w-10 h-10 rounded-full bg-slate-700/60 flex items-center justify-center mx-auto mb-2 text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </div>
                    <p x-show="sidebarOpen" class="text-xs font-medium text-slate-300">No matching menu</p>
                    <p x-show="sidebarOpen" class="text-[11px] text-slate-400 mt-1">
                        Try searching with different keywords
                    </p>
                    <button x-show="sidebarOpen" @click="menuSearch = ''" type="button" class="mt-3 text-xs text-indigo-400 hover:text-indigo-300 underline font-medium">
                        Clear search
                    </button>
                </li>
            </ul>
        </nav>

        {{-- ── Sidebar User Footer ── --}}
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
