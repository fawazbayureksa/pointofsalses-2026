@extends('layouts.admin')

@section('title', 'Panduan Penggunaan (Guide Manual)')
@section('page-title', 'Panduan Penggunaan Sistem')

@section('content')
    <div x-data="{
        activeTab: 'onboarding',
        completedSteps: JSON.parse(localStorage.getItem('pos_guide_completed_steps') || '[]'),
        openFaq: 1,
        toggleStep(stepId) {
            if (this.completedSteps.includes(stepId)) {
                this.completedSteps = this.completedSteps.filter(id => id !== stepId);
            } else {
                this.completedSteps.push(stepId);
            }
            localStorage.setItem('pos_guide_completed_steps', JSON.stringify(this.completedSteps));
        },
        isCompleted(stepId) {
            return this.completedSteps.includes(stepId);
        },
        resetProgress() {
            this.completedSteps = [];
            localStorage.removeItem('pos_guide_completed_steps');
        },
        get progressPercent() {
            return Math.round((this.completedSteps.length / 7) * 100);
        }
    }" class="max-w-6xl mx-auto space-y-6">

        {{-- ── Hero Banner ──────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl p-6 md:p-8 text-gray-900 shadow-sm border border-gray-200">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center space-x-2 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full mb-3">
                        <span>PANDUAN MEMULAI CEPAT (BAHASA INDONESIA)</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900">
                        Selamat Datang di Sistem Point of Sales!
                    </h1>
                    <p class="text-gray-600 text-sm md:text-base mt-2 leading-relaxed">
                        Terima kasih telah berlangganan. Ikuti <strong>7 langkah persiapan awal</strong> di bawah ini agar toko Anda siap menerima transaksi kasir, mengelola stok multi-outlet, dan memantau laporan penjualan secara <em>real-time</em>.
                    </p>
                </div>

                {{-- Progress Tracker Card --}}
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 min-w-[260px]">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Progres Persiapan Toko</span>
                        <span class="text-sm font-bold text-emerald-600" x-text="`${completedSteps.length}/7 Selesai`"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-emerald-500 h-2.5 rounded-full transition-all duration-500"
                            :style="`width: ${progressPercent}%`"></div>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                        <span x-text="progressPercent === 100 ? '🎉 Toko siap beroperasi!' : 'Centang langkah yang sudah selesai'"></span>
                        <button x-show="completedSteps.length > 0" @click="resetProgress()"
                            class="text-gray-500 hover:text-gray-800 underline ml-2">
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Navigation Tabs ──────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-1.5 flex flex-wrap gap-1">
            <button @click="activeTab = 'onboarding'"
                :class="activeTab === 'onboarding' ? 'bg-slate-800 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'"
                class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2.5 rounded-lg text-sm font-medium transition-colors">
                <i class="fa-solid fa-list-check mr-2"></i>
                Langkah Awal Setelah Berlangganan
            </button>
            <button @click="activeTab = 'pos_flow'"
                :class="activeTab === 'pos_flow' ? 'bg-slate-800 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'"
                class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2.5 rounded-lg text-sm font-medium transition-colors">
                <i class="fa-solid fa-cash-register mr-2"></i>
                Alur Transaksi Kasir (POS)
            </button>
            <button @click="activeTab = 'features'"
                :class="activeTab === 'features' ? 'bg-slate-800 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'"
                class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2.5 rounded-lg text-sm font-medium transition-colors">
                <i class="fa-solid fa-layer-group mr-2"></i>
                Panduan Menu & Fitur
            </button>
            <button @click="activeTab = 'faq'"
                :class="activeTab === 'faq' ? 'bg-slate-800 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'"
                class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2.5 rounded-lg text-sm font-medium transition-colors">
                <i class="fa-solid fa-circle-question mr-2"></i>
                Tanya Jawab (FAQ)
            </button>
        </div>

        {{-- ── TAB 1: 7 Langkah Awal Setelah Berlangganan ───────────────────── --}}
        <div x-show="activeTab === 'onboarding'" x-transition class="space-y-4">

            {{-- Step 1: Pengaturan Toko, Pajak & Mata Uang --}}
            <div :class="isCompleted(1) ? 'border-emerald-300 bg-emerald-50/30' : 'border-gray-200 bg-white'"
                class="rounded-xl border shadow-sm p-5 md:p-6 transition-all">
                <div class="flex flex-col md:flex-row md:items-start gap-4">
                    <button @click="toggleStep(1)"
                        :class="isCompleted(1) ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white text-slate-700 border-gray-300 hover:border-emerald-500'"
                        class="w-10 h-10 rounded-xl border-2 flex items-center justify-center font-bold text-sm flex-shrink-0 transition-colors"
                        title="Tandai selesai">
                        <template x-if="!isCompleted(1)"><span>1</span></template>
                        <template x-if="isCompleted(1)"><i class="fa-solid fa-check"></i></template>
                    </button>

                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700">Langkah 1</span>
                            <h3 class="text-lg font-bold text-gray-900">Atur Profil Bisnis, Pajak & Mata Uang</h3>
                        </div>
                        <p class="text-sm text-gray-600 mt-1.5 leading-relaxed">
                            Sebelum membuat produk dan bertransaksi, pastikan identitas toko, format mata uang (misal: <strong>IDR / Rp</strong>), serta tarif pajak (PPN/PB1 jika berlaku) sudah sesuai dengan kebijakan bisnis Anda.
                        </p>
                        <ul class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs text-gray-600">
                            <li class="flex items-center bg-gray-50 px-3 py-2 rounded-lg border border-gray-100">
                                <i class="fa-solid fa-gear text-slate-500 mr-2"></i>
                                <span><strong>Settings:</strong> Nama toko & info umum</span>
                            </li>
                            <li class="flex items-center bg-gray-50 px-3 py-2 rounded-lg border border-gray-100">
                                <i class="fa-solid fa-percent text-slate-500 mr-2"></i>
                                <span><strong>Tax:</strong> Persentase PPN / pajak transaksi</span>
                            </li>
                            <li class="flex items-center bg-gray-50 px-3 py-2 rounded-lg border border-gray-100">
                                <i class="fa-solid fa-dollar-sign text-slate-500 mr-2"></i>
                                <span><strong>Currency:</strong> Simbol & format mata uang</span>
                            </li>
                        </ul>
                    </div>

                    @can('manage settings')
                        <div class="flex flex-wrap md:flex-col gap-2 flex-shrink-0">
                            <a href="{{ route('admin.settings.index') }}"
                                class="inline-flex items-center justify-center px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-medium rounded-lg transition-colors">
                                <i class="fa-solid fa-gear mr-1.5"></i> Buka Pengaturan
                            </a>
                            <a href="{{ route('admin.settings.tax') }}"
                                class="inline-flex items-center justify-center px-3.5 py-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-xs font-medium rounded-lg transition-colors">
                                <i class="fa-solid fa-percent mr-1.5"></i> Atur Pajak
                            </a>
                        </div>
                    @endcan
                </div>
            </div>

            {{-- Step 2: Buat Outlet / Cabang --}}
            <div :class="isCompleted(2) ? 'border-emerald-300 bg-emerald-50/30' : 'border-gray-200 bg-white'"
                class="rounded-xl border shadow-sm p-5 md:p-6 transition-all">
                <div class="flex flex-col md:flex-row md:items-start gap-4">
                    <button @click="toggleStep(2)"
                        :class="isCompleted(2) ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white text-slate-700 border-gray-300 hover:border-emerald-500'"
                        class="w-10 h-10 rounded-xl border-2 flex items-center justify-center font-bold text-sm flex-shrink-0 transition-colors"
                        title="Tandai selesai">
                        <template x-if="!isCompleted(2)"><span>2</span></template>
                        <template x-if="isCompleted(2)"><i class="fa-solid fa-check"></i></template>
                    </button>

                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700">Langkah 2 • Wajib</span>
                            <h3 class="text-lg font-bold text-gray-900">Buat & Aktifkan Outlet (Toko / Cabang)</h3>
                        </div>
                        <p class="text-sm text-gray-600 mt-1.5 leading-relaxed">
                            Setiap stok barang dan transaksi kasir terhubung ke sebuah <strong>Outlet</strong>. Buat minimal 1 outlet utama (contoh: <em>Outlet Pusat</em>), lengkapi alamat dan nomor telepon yang akan tampil pada struk belanja, lalu pastikan statusnya <strong>Active</strong>.
                        </p>
                        <div class="mt-3 bg-blue-50/70 border border-blue-100 rounded-lg p-3 text-xs text-blue-800 flex items-start space-x-2">
                            <i class="fa-solid fa-circle-info mt-0.5 text-blue-500"></i>
                            <span><strong>Tips:</strong> Jika Anda memiliki beberapa cabang, Anda dapat menambahkan semua cabang di menu <em>Outlets</em> untuk memantau stok dan omzet per cabang secara terpisah.</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap md:flex-col gap-2 flex-shrink-0">
                        <a href="{{ route('admin.outlets.create') }}"
                            class="inline-flex items-center justify-center px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors">
                            <i class="fa-solid fa-plus mr-1.5"></i> Tambah Outlet
                        </a>
                        <a href="{{ route('admin.outlets.index') }}"
                            class="inline-flex items-center justify-center px-3.5 py-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-xs font-medium rounded-lg transition-colors">
                            <i class="fa-solid fa-store mr-1.5"></i> Daftar Outlet
                        </a>
                    </div>
                </div>
            </div>

            {{-- Step 3: Buat Kategori Produk --}}
            <div :class="isCompleted(3) ? 'border-emerald-300 bg-emerald-50/30' : 'border-gray-200 bg-white'"
                class="rounded-xl border shadow-sm p-5 md:p-6 transition-all">
                <div class="flex flex-col md:flex-row md:items-start gap-4">
                    <button @click="toggleStep(3)"
                        :class="isCompleted(3) ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white text-slate-700 border-gray-300 hover:border-emerald-500'"
                        class="w-10 h-10 rounded-xl border-2 flex items-center justify-center font-bold text-sm flex-shrink-0 transition-colors"
                        title="Tandai selesai">
                        <template x-if="!isCompleted(3)"><span>3</span></template>
                        <template x-if="isCompleted(3)"><i class="fa-solid fa-check"></i></template>
                    </button>

                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-700">Langkah 3</span>
                            <h3 class="text-lg font-bold text-gray-900">Susun Kategori Produk</h3>
                        </div>
                        <p class="text-sm text-gray-600 mt-1.5 leading-relaxed">
                            Kelompokkan produk Anda ke dalam kategori (contoh: <em>Makanan Utama, Minuman Kopi, Snack, Aksesoris</em>). Kategori membantu kasir memfilter produk dengan cepat di layar POS serta mempermudah analisis laporan penjualan per kategori.
                        </p>
                    </div>

                    <div class="flex flex-wrap md:flex-col gap-2 flex-shrink-0">
                        <a href="{{ route('admin.categories.create') }}"
                            class="inline-flex items-center justify-center px-3.5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-medium rounded-lg transition-colors">
                            <i class="fa-solid fa-plus mr-1.5"></i> Buat Kategori
                        </a>
                        <a href="{{ route('admin.categories.index') }}"
                            class="inline-flex items-center justify-center px-3.5 py-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-xs font-medium rounded-lg transition-colors">
                            <i class="fa-solid fa-tags mr-1.5"></i> Lihat Kategori
                        </a>
                    </div>
                </div>
            </div>

            {{-- Step 4: Tambah Produk & Harga --}}
            <div :class="isCompleted(4) ? 'border-emerald-300 bg-emerald-50/30' : 'border-gray-200 bg-white'"
                class="rounded-xl border shadow-sm p-5 md:p-6 transition-all">
                <div class="flex flex-col md:flex-row md:items-start gap-4">
                    <button @click="toggleStep(4)"
                        :class="isCompleted(4) ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white text-slate-700 border-gray-300 hover:border-emerald-500'"
                        class="w-10 h-10 rounded-xl border-2 flex items-center justify-center font-bold text-sm flex-shrink-0 transition-colors"
                        title="Tandai selesai">
                        <template x-if="!isCompleted(4)"><span>4</span></template>
                        <template x-if="isCompleted(4)"><i class="fa-solid fa-check"></i></template>
                    </button>

                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800">Langkah 4 • Wajib</span>
                            <h3 class="text-lg font-bold text-gray-900">Tambahkan Produk, SKU & Hubungkan ke Outlet</h3>
                        </div>
                        <p class="text-sm text-gray-600 mt-1.5 leading-relaxed">
                            Masukkan katalog barang atau jasa yang Anda jual. Saat membuat produk, perhatikan poin penting berikut:
                        </p>
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs text-gray-700">
                            <div class="flex items-start space-x-2 bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <i class="fa-solid fa-barcode text-amber-600 mt-0.5"></i>
                                <div>
                                    <strong>SKU & Barcode:</strong> Isi kode unik/barcode agar produk bisa dicari otomatis dengan *barcode scanner*.
                                </div>
                            </div>
                            <div class="flex items-start space-x-2 bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <i class="fa-solid fa-coins text-amber-600 mt-0.5"></i>
                                <div>
                                    <strong>Harga Modal (Cost) & Harga Jual (Price):</strong> Isi keduanya agar sistem dapat menghitung margin laba kotor.
                                </div>
                            </div>
                            <div class="flex items-start space-x-2 bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <i class="fa-solid fa-store text-amber-600 mt-0.5"></i>
                                <div>
                                    <strong>Penugasan Outlet:</strong> Centang di outlet mana saja produk tersebut tersedia dan isi stok awalnya.
                                </div>
                            </div>
                            <div class="flex items-start space-x-2 bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <i class="fa-solid fa-boxes-stacked text-amber-600 mt-0.5"></i>
                                <div>
                                    <strong>Track Stock:</strong> Aktifkan opsi pelacakan stok jika barang fisik harus berkurang otomatis saat terjual.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap md:flex-col gap-2 flex-shrink-0">
                        <a href="{{ route('admin.products.create') }}"
                            class="inline-flex items-center justify-center px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium rounded-lg transition-colors">
                            <i class="fa-solid fa-plus mr-1.5"></i> Tambah Produk
                        </a>
                        <a href="{{ route('admin.products.index') }}"
                            class="inline-flex items-center justify-center px-3.5 py-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-xs font-medium rounded-lg transition-colors">
                            <i class="fa-solid fa-box mr-1.5"></i> Daftar Produk
                        </a>
                    </div>
                </div>
            </div>

            {{-- Step 5: Kelola Stok Awal & Peringatan Stok Menipis --}}
            <div :class="isCompleted(5) ? 'border-emerald-300 bg-emerald-50/30' : 'border-gray-200 bg-white'"
                class="rounded-xl border shadow-sm p-5 md:p-6 transition-all">
                <div class="flex flex-col md:flex-row md:items-start gap-4">
                    <button @click="toggleStep(5)"
                        :class="isCompleted(5) ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white text-slate-700 border-gray-300 hover:border-emerald-500'"
                        class="w-10 h-10 rounded-xl border-2 flex items-center justify-center font-bold text-sm flex-shrink-0 transition-colors"
                        title="Tandai selesai">
                        <template x-if="!isCompleted(5)"><span>5</span></template>
                        <template x-if="isCompleted(5)"><i class="fa-solid fa-check"></i></template>
                    </button>

                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700">Langkah 5</span>
                            <h3 class="text-lg font-bold text-gray-900">Cek Stok Inventori & Batas Minimum (*Low Stock*)</h3>
                        </div>
                        <p class="text-sm text-gray-600 mt-1.5 leading-relaxed">
                            Buka menu <strong>Inventory &rarr; Stock</strong> untuk memastikan jumlah stok awal di setiap outlet sudah benar. Anda juga dapat melakukan <strong>Adjust Stock</strong> (penyesuaian stok masuk/keluar/rusak) atau <strong>Transfer Stock</strong> (kirim stok antar cabang) kapan saja. Semua riwayat perubahan tercatat otomatis di menu <em>Movements</em>.
                        </p>
                    </div>

                    <div class="flex flex-wrap md:flex-col gap-2 flex-shrink-0">
                        <a href="{{ route('admin.inventory.stock') }}"
                            class="inline-flex items-center justify-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
                            <i class="fa-solid fa-warehouse mr-1.5"></i> Kelola Stok
                        </a>
                        <a href="{{ route('admin.inventory.movements') }}"
                            class="inline-flex items-center justify-center px-3.5 py-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-xs font-medium rounded-lg transition-colors">
                            <i class="fa-solid fa-arrow-right-arrow-left mr-1.5"></i> Riwayat Stok
                        </a>
                    </div>
                </div>
            </div>

            {{-- Step 6: Tambah Akun Kasir / Staf & Pelanggan --}}
            <div :class="isCompleted(6) ? 'border-emerald-300 bg-emerald-50/30' : 'border-gray-200 bg-white'"
                class="rounded-xl border shadow-sm p-5 md:p-6 transition-all">
                <div class="flex flex-col md:flex-row md:items-start gap-4">
                    <button @click="toggleStep(6)"
                        :class="isCompleted(6) ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white text-slate-700 border-gray-300 hover:border-emerald-500'"
                        class="w-10 h-10 rounded-xl border-2 flex items-center justify-center font-bold text-sm flex-shrink-0 transition-colors"
                        title="Tandai selesai">
                        <template x-if="!isCompleted(6)"><span>6</span></template>
                        <template x-if="isCompleted(6)"><i class="fa-solid fa-check"></i></template>
                    </button>

                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-teal-100 text-teal-800">Langkah 6</span>
                            <h3 class="text-lg font-bold text-gray-900">Tambahkan Akun Staf/Kasir & Data Pelanggan</h3>
                        </div>
                        <p class="text-sm text-gray-600 mt-1.5 leading-relaxed">
                            Jangan gunakan akun pemilik utama untuk operasional kasir harian jika Anda memiliki karyawan. Buat akun khusus untuk kasir atau supervisor di menu <strong>Users</strong> dan atur hak aksesnya di menu <strong>Roles</strong>. Anda juga bisa mulai mendaftarkan pelanggan tetap di menu <strong>Customers</strong>.
                        </p>
                    </div>

                    <div class="flex flex-wrap md:flex-col gap-2 flex-shrink-0">
                        @can('manage users')
                            <a href="{{ route('admin.users.index') }}"
                                class="inline-flex items-center justify-center px-3.5 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-medium rounded-lg transition-colors">
                                <i class="fa-solid fa-user-gear mr-1.5"></i> Kelola User/Kasir
                            </a>
                        @endcan
                        <a href="{{ route('admin.customers.index') }}"
                            class="inline-flex items-center justify-center px-3.5 py-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-xs font-medium rounded-lg transition-colors">
                            <i class="fa-solid fa-users mr-1.5"></i> Data Pelanggan
                        </a>
                    </div>
                </div>
            </div>

            {{-- Step 7: Mulai Transaksi di Kasir (POS) --}}
            <div :class="isCompleted(7) ? 'border-emerald-300 bg-emerald-50/30' : 'border-gray-200 bg-white'"
                class="rounded-xl border shadow-sm p-5 md:p-6 transition-all">
                <div class="flex flex-col md:flex-row md:items-start gap-4">
                    <button @click="toggleStep(7)"
                        :class="isCompleted(7) ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white text-slate-700 border-gray-300 hover:border-emerald-500'"
                        class="w-10 h-10 rounded-xl border-2 flex items-center justify-center font-bold text-sm flex-shrink-0 transition-colors"
                        title="Tandai selesai">
                        <template x-if="!isCompleted(7)"><span>7</span></template>
                        <template x-if="isCompleted(7)"><i class="fa-solid fa-check"></i></template>
                    </button>

                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Langkah 7 • Siap Jualan!</span>
                            <h3 class="text-lg font-bold text-gray-900">Buka Kasir (Cashier / POS) & Lakukan Transaksi Pertama</h3>
                        </div>
                        <p class="text-sm text-gray-600 mt-1.5 leading-relaxed">
                            Sekarang toko Anda sudah siap! Buka menu <strong>Cashier / POS</strong>, pilih outlet tempat Anda bertugas, klik atau scan produk untuk memasukkan ke keranjang, pilih metode pembayaran (Tunai / Kartu / QRIS / Transfer), lalu cetak struk pembayaran untuk pelanggan.
                        </p>
                    </div>

                    <div class="flex flex-wrap md:flex-col gap-2 flex-shrink-0">
                        <a href="{{ route('admin.pos.index') }}"
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                            <i class="fa-solid fa-cash-register mr-1.5"></i> Buka Kasir POS
                        </a>
                        <a href="{{ route('admin.orders.index') }}"
                            class="inline-flex items-center justify-center px-3.5 py-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-xs font-medium rounded-lg transition-colors">
                            <i class="fa-solid fa-shopping-cart mr-1.5"></i> Riwayat Order
                        </a>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── TAB 2: Alur Transaksi Kasir (POS) ────────────────────────────── --}}
        <div x-show="activeTab === 'pos_flow'" x-cloak x-transition class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 flex items-center">
                    <i class="fa-solid fa-cash-register text-emerald-600 mr-2.5"></i>
                    Cara Menggunakan Layar Kasir (POS) Harian
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Panduan langkah demi langkah bagi kasir saat melayani pelanggan di toko.
                </p>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 relative">
                        <span class="w-7 h-7 rounded-full bg-slate-800 text-white text-xs font-bold flex items-center justify-center mb-3">1</span>
                        <h4 class="font-semibold text-gray-900 text-sm">Pilih Outlet & Pelanggan</h4>
                        <p class="text-xs text-gray-600 mt-1.5 leading-relaxed">
                            Pastikan outlet yang aktif di bagian atas layar POS sudah sesuai. Pilih nama pelanggan jika merupakan member, atau biarkan sebagai <em>Walk-in Customer</em> (pelanggan umum).
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 relative">
                        <span class="w-7 h-7 rounded-full bg-slate-800 text-white text-xs font-bold flex items-center justify-center mb-3">2</span>
                        <h4 class="font-semibold text-gray-900 text-sm">Pilih / Scan Produk</h4>
                        <p class="text-xs text-gray-600 mt-1.5 leading-relaxed">
                            Gunakan kolom pencarian (nama/SKU/barcode) atau filter kategori, lalu klik kartu produk. Atur jumlah (*qty*) atau berikan diskon jika ada promo.
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 relative">
                        <span class="w-7 h-7 rounded-full bg-slate-800 text-white text-xs font-bold flex items-center justify-center mb-3">3</span>
                        <h4 class="font-semibold text-gray-900 text-sm">Proses Pembayaran</h4>
                        <p class="text-xs text-gray-600 mt-1.5 leading-relaxed">
                            Periksa subtotal, pajak, dan total tagihan. Pilih metode pembayaran (<strong>Cash, Card, QRIS, Transfer</strong>), masukkan nominal uang diterima untuk menghitung kembalian otomatis.
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 relative">
                        <span class="w-7 h-7 rounded-full bg-emerald-600 text-white text-xs font-bold flex items-center justify-center mb-3">4</span>
                        <h4 class="font-semibold text-gray-900 text-sm">Cetak Struk & Selesai</h4>
                        <p class="text-xs text-gray-600 mt-1.5 leading-relaxed">
                            Setelah pembayaran berhasil disimpan, stok produk akan otomatis berkurang. Anda dapat langsung menekan tombol <strong>Print Receipt</strong> untuk mencetak struk kasir.
                        </p>
                    </div>
                </div>

                <div class="mt-6 pt-5 border-t border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="text-xs text-gray-500">
                        <i class="fa-solid fa-lightbulb text-amber-500 mr-1"></i>
                        <strong>Bagaimana jika ada pembatalan atau *refund*?</strong> Buka menu <strong>Sales &rarr; Orders</strong> atau <strong>Payments</strong>, pilih pesanan terkait, lalu gunakan tombol <em>Refund Payment</em>.
                    </div>
                    <a href="{{ route('admin.pos.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors flex-shrink-0">
                        <i class="fa-solid fa-arrow-up-right-from-square mr-1.5"></i> Praktek di Layar POS
                    </a>
                </div>
            </div>
        </div>

        {{-- ── TAB 3: Panduan Menu & Fitur Lengkap ──────────────────────────── --}}
        <div x-show="activeTab === 'features'" x-cloak x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fa-solid fa-gauge-high"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Dashboard & Ringkasan Bisnis</h3>
                        <p class="text-xs text-gray-500">Menu: Dashboard</p>
                    </div>
                </div>
                <p class="text-xs text-gray-600 leading-relaxed">
                    Menampilkan KPI utama secara *real-time*: total penjualan hari ini vs kemarin, pendapatan bulanan, rata-rata nilai transaksi, grafik tren penjualan 7 hari terakhir, 5 produk terlaris, serta peringatan produk yang stoknya hampir habis (*Low Stock Alert*).
                </p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i class="fa-solid fa-shopping-cart"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Manajemen Pesanan & Pembayaran</h3>
                        <p class="text-xs text-gray-500">Menu: Sales &rarr; Orders & Payments</p>
                    </div>
                </div>
                <p class="text-xs text-gray-600 leading-relaxed">
                    Pantau seluruh transaksi yang berstatus <em>Pending</em>, <em>Completed</em>, atau <em>Cancelled</em>. Anda dapat melihat detail item per struk, mencetak ulang struk (*Reprint*), melunasi pesanan *pending*, atau memproses pengembalian dana (*Refund*).
                </p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="fa-solid fa-warehouse"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Inventori Multi-Outlet & Mutasi Stok</h3>
                        <p class="text-xs text-gray-500">Menu: Inventory &rarr; Stock & Movements</p>
                    </div>
                </div>
                <p class="text-xs text-gray-600 leading-relaxed">
                    Kelola stok produk di masing-masing cabang secara akurat. Gunakan fitur <strong>Adjust Stock</strong> saat stok opname atau barang masuk dari supplier, dan gunakan <strong>Transfer Stock</strong> untuk memindahkan barang dari satu outlet ke outlet lainnya.
                </p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-9 h-9 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Laporan Lanjutan (Advanced Reports)</h3>
                        <p class="text-xs text-gray-500">Menu: Advanced Reports</p>
                    </div>
                </div>
                <p class="text-xs text-gray-600 leading-relaxed">
                    Tersedia 5 laporan komprehensif: <strong>Overview</strong> (ringkasan eksekutif), <strong>Sales</strong> (omzet per periode/outlet), <strong>Products</strong> (kinerja & margin produk), <strong>Customers</strong> (pelanggan paling loyal), dan <strong>Inventory</strong> (valuasi nilai stok gudang).
                </p>
            </div>

        </div>

        {{-- ── TAB 4: FAQ (Tanya Jawab) ─────────────────────────────────────── --}}
        <div x-show="activeTab === 'faq'" x-cloak x-transition class="space-y-3">

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                <button @click="openFaq = openFaq === 1 ? null : 1"
                    class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                    <span class="font-semibold text-sm text-gray-900">1. Mengapa produk yang baru saya buat belum muncul di layar Kasir (POS)?</span>
                    <i :class="openFaq === 1 ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs text-gray-400"></i>
                </button>
                <div x-show="openFaq === 1" x-collapse class="px-6 pb-4 text-xs text-gray-600 leading-relaxed border-t border-gray-100 pt-3">
                    Pastikan 3 hal berikut:
                    <ol class="list-decimal list-inside mt-1.5 space-y-1">
                        <li>Status produk sudah diaktifkan (<strong>Active</strong>).</li>
                        <li>Produk tersebut sudah dihubungkan/dicentang pada <strong>Outlet</strong> yang sedang dipilih di layar POS.</li>
                        <li>Jika produk menggunakan fitur <em>Track Stock</em>, pastikan stok produk di outlet tersebut lebih dari <code>0</code>.</li>
                    </ol>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                <button @click="openFaq = openFaq === 2 ? null : 2"
                    class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                    <span class="font-semibold text-sm text-gray-900">2. Bagaimana cara menambahkan stok barang baru yang datang dari Supplier?</span>
                    <i :class="openFaq === 2 ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs text-gray-400"></i>
                </button>
                <div x-show="openFaq === 2" x-collapse class="px-6 pb-4 text-xs text-gray-600 leading-relaxed border-t border-gray-100 pt-3">
                    Buka menu <strong>Inventory &rarr; Stock</strong>, cari produk yang ingin ditambah stoknya, lalu klik tombol <strong>Adjust Stock</strong>. Pilih outlet tujuan, pilih tipe penyesuaian penambahan stok, masukkan jumlah barang yang masuk beserta catatannya (misal: <em>Restock PO #102</em>), lalu simpan.
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                <button @click="openFaq = openFaq === 3 ? null : 3"
                    class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                    <span class="font-semibold text-sm text-gray-900">3. Apakah saya bisa membatasi akses kasir agar tidak bisa mengubah pengaturan atau melihat laporan keuangan?</span>
                    <i :class="openFaq === 3 ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs text-gray-400"></i>
                </button>
                <div x-show="openFaq === 3" x-collapse class="px-6 pb-4 text-xs text-gray-600 leading-relaxed border-t border-gray-100 pt-3">
                    Ya, sangat bisa. Melalui menu <strong>User Management &rarr; Roles</strong>, Anda dapat mengatur izin (*permissions*) apa saja yang dimiliki oleh peran Kasir. Saat membuat akun staf di menu <strong>Users</strong>, cukup berikan *role* yang sesuai agar menu pengaturan sistem dan manajemen pengguna tidak tampil di akun mereka.
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                <button @click="openFaq = openFaq === 4 ? null : 4"
                    class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                    <span class="font-semibold text-sm text-gray-900">4. Bagaimana cara mengecek masa aktif langganan atau meng-upgrade paket saya?</span>
                    <i :class="openFaq === 4 ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs text-gray-400"></i>
                </button>
                <div x-show="openFaq === 4" x-collapse class="px-6 pb-4 text-xs text-gray-600 leading-relaxed border-t border-gray-100 pt-3">
                    Klik ikon profil Anda di pojok kanan atas layar untuk melihat nama Tenant dan status paket berlangganan Anda saat ini (<em>Trial / Basic / Pro</em>). Anda juga dapat mengunjungi halaman <a href="{{ route('subscription.plans') }}" class="text-blue-600 underline font-medium">Pilihan Paket Langganan</a> kapan saja.
                </div>
            </div>

        </div>

    </div>
@endsection
