<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Display - {{ $storeName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-blue-50/50 text-slate-800 h-full overflow-hidden select-none" x-data="customerDisplayApp({{ Js::from($storeName) }}, {{ Js::from($receiptFooter) }})">

    <div class="flex flex-col h-full">
        {{-- Top Bar --}}
        <header
            class="px-6 py-4 bg-linear-to-r from-blue-700 to-blue-600 border-b border-blue-700 shadow-sm flex items-center justify-between shrink-0 text-white">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-blue-600 font-bold text-lg shadow-sm">
                    <i class="fa-solid fa-store"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-tight text-white" x-text="outletName || storeName"></h1>
                    <div class="flex items-center gap-2 text-xs text-blue-100">
                        <span class="inline-flex items-center gap-1.5 text-white font-medium">
                            <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                            Live Customer Display
                        </span>
                        <span>•</span>
                        <span x-text="currentTime"></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                {{-- Member Info Badge --}}
                <template x-if="customer">
                    <div
                        class="flex items-center gap-3 bg-white/15 backdrop-blur-sm border border-white/25 rounded-xl px-4 py-2">
                        <div class="w-8 h-8 rounded-full bg-white text-blue-700 flex items-center justify-center text-xs font-bold uppercase shadow-sm"
                            x-text="customer.name ? customer.name.charAt(0) : 'M'"></div>
                        <div>
                            <div class="text-xs text-blue-100">Pelanggan</div>
                            <div class="text-sm font-semibold text-white" x-text="customer.name"></div>
                        </div>
                        <template x-if="customer.is_member">
                            <div class="pl-3 border-l border-white/25 text-right">
                                <span
                                    class="text-[11px] px-2 py-0.5 rounded-full bg-amber-300 text-amber-950 capitalize font-semibold block"
                                    x-text="customer.membership_tier || 'Member'"></span>
                                <span class="text-xs text-white font-bold"
                                    x-text="formatNumber(customer.loyalty_points || 0) + ' pts'"></span>
                            </div>
                        </template>
                    </div>
                </template>

                <button @click="toggleFullscreen()" type="button" title="Toggle Fullscreen"
                    class="px-3 py-2 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-medium transition">
                    <i class="fa-solid fa-expand mr-1"></i> Fullscreen
                </button>
            </div>
        </header>

        {{-- STATE 1: IDLE (Empty Cart) --}}
        <template x-if="state === 'idle' && items.length === 0">
            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center">
                <div
                    class="bg-white border border-blue-100 rounded-3xl shadow-sm px-12 py-14 max-w-xl w-full flex flex-col items-center">
                    <div
                        class="w-24 h-24 rounded-3xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600 text-5xl mb-6 shadow-inner">
                        <i class="fa-solid fa-basket-shopping"></i>
                    </div>
                    <h2 class="text-3xl font-extrabold text-blue-950 tracking-tight mb-2"
                        x-text="'Selamat Datang di ' + (outletName || storeName)"></h2>
                    <p class="text-slate-500 text-base max-w-md">
                        Daftar pesanan dan rincian harga Anda akan tampil secara otomatis di layar ini saat kasir
                        memindai produk.
                    </p>
                </div>
            </div>
        </template>

        {{-- STATE 2: COMPLETED / THANK YOU --}}
        <template x-if="state === 'paid'">
            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center">
                <div
                    class="bg-white border border-blue-100 rounded-3xl shadow-sm px-10 py-10 max-w-lg w-full flex flex-col items-center">
                    <div
                        class="w-24 h-24 rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 text-5xl mb-6 animate-bounce">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <h2 class="text-3xl font-extrabold text-blue-950 mb-2">Pembayaran Berhasil!</h2>
                    <p class="text-slate-500 text-base mb-6" x-text="receiptFooter"></p>

                    <div class="bg-blue-50/70 border border-blue-100 rounded-2xl p-6 w-full space-y-3">
                        <div class="flex justify-between text-sm text-slate-600">
                            <span>Total Pembayaran</span>
                            <span class="font-bold text-blue-900" x-text="'Rp ' + formatNumber(total)"></span>
                        </div>
                        <div class="flex justify-between text-sm text-slate-600">
                            <span>Metode Bayar</span>
                            <span class="font-semibold uppercase text-blue-600" x-text="paymentMethod"></span>
                        </div>
                        <template x-if="paymentMethod === 'cash' && change > 0">
                            <div
                                class="flex justify-between text-lg font-bold text-emerald-600 pt-2 border-t border-blue-200">
                                <span>Kembalian Anda</span>
                                <span x-text="'Rp ' + formatNumber(change)"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        {{-- STATE 3: ACTIVE CHECKOUT (Mirroring Cart & Price) --}}
        <template x-if="state !== 'paid' && items.length > 0">
            <div class="flex-1 min-h-0 grid grid-cols-12 gap-6 p-6">
                {{-- Left: Order Item List --}}
                <div
                    class="col-span-7 lg:col-span-8 bg-white border border-blue-100 rounded-2xl shadow-sm flex flex-col overflow-hidden">
                    <div class="px-6 py-4 bg-blue-50/60 border-b border-blue-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-receipt text-blue-600"></i>
                            <h2 class="text-base font-bold text-blue-950">Daftar Pesanan Anda</h2>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-blue-600 text-white text-xs font-semibold shadow-sm"
                            x-text="totalQty + ' item'"></span>
                    </div>

                    <div class="flex-1 overflow-y-auto divide-y divide-blue-50 px-6">
                        <template x-for="(item, index) in items"
                            :key="item.product_id + '-' + item.qty + '-' + (item.discount || 0)">
                            <div class="py-4 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm shrink-0"
                                        x-text="index + 1"></div>
                                    <div class="min-w-0">
                                        <div class="text-base font-semibold text-slate-800 truncate" x-text="item.name">
                                        </div>
                                        <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                                            <span
                                                x-text="item.qty + ' ' + (item.unit || 'pcs') + ' × Rp ' + formatNumber(item.price)"></span>
                                            <template x-if="item.discount > 0">
                                                <span
                                                    class="px-1.5 py-0.5 rounded bg-rose-50 border border-rose-200 text-rose-600 font-medium"
                                                    x-text="'Diskon -Rp ' + formatNumber(item.discount)"></span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-lg font-bold text-blue-700"
                                        x-text="'Rp ' + formatNumber(lineTotal(item))"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Right: Price Breakdown & Payment Status --}}
                <div class="col-span-5 lg:col-span-4 flex flex-col gap-4">
                    <div
                        class="bg-white border border-blue-100 rounded-2xl shadow-sm p-6 flex-1 flex flex-col justify-between">
                        <div class="space-y-3">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-2">Ringkasan Tagihan
                            </h3>

                            <div class="flex justify-between text-sm text-slate-600">
                                <span>Subtotal</span>
                                <span class="font-semibold text-slate-800"
                                    x-text="'Rp ' + formatNumber(subtotal)"></span>
                            </div>

                            <template x-if="discount > 0">
                                <div class="flex justify-between text-sm text-emerald-600">
                                    <span>Diskon Pesanan</span>
                                    <span class="font-semibold" x-text="'-Rp ' + formatNumber(discount)"></span>
                                </div>
                            </template>

                            <template x-if="taxAmount > 0">
                                <div class="flex justify-between text-sm text-slate-600">
                                    <span x-text="taxLabel || 'Pajak'"></span>
                                    <span class="font-semibold text-slate-800"
                                        x-text="'Rp ' + formatNumber(taxAmount)"></span>
                                </div>
                            </template>

                            <template x-if="customer && customer.is_member && pointsToEarn > 0">
                                <div
                                    class="flex justify-between items-center text-xs bg-blue-50 border border-blue-200 text-blue-700 rounded-xl px-3 py-2">
                                    <span><i class="fa-solid fa-star mr-1 text-amber-500"></i>Estimasi Poin
                                        Member</span>
                                    <span class="font-bold" x-text="'+' + formatNumber(pointsToEarn) + ' pts'"></span>
                                </div>
                            </template>
                        </div>

                        <div
                            class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-5 mt-4 text-white shadow-sm">
                            <div class="text-xs uppercase tracking-wider text-blue-100 mb-1">Total Pembayaran</div>
                            <div class="text-4xl font-black text-white tracking-tight"
                                x-text="'Rp ' + formatNumber(total)"></div>
                        </div>
                    </div>

                    {{-- Payment / EDC / Cash Status Box --}}
                    <div class="bg-white border border-blue-100 rounded-2xl shadow-sm p-5">
                        {{-- EDC Waiting Prompt --}}
                        <template x-if="edcWaiting">
                            <div
                                class="bg-blue-50 border-2 border-blue-400 rounded-xl p-4 text-center space-y-2 animate-pulse">
                                <i class="fa-solid fa-credit-card text-3xl text-blue-600"></i>
                                <div class="text-sm font-bold text-blue-950">
                                    Silakan Tap / Masukkan Kartu di Mesin EDC
                                    <span x-show="edcBank" x-text="'(' + edcBank + ')'"></span>
                                </div>
                                <div class="text-xs text-blue-700">
                                    Periksa nominal <strong x-text="'Rp ' + formatNumber(total)"></strong> pada layar
                                    EDC lalu masukkan PIN Anda.
                                </div>
                            </div>
                        </template>

                        {{-- EDC Approved Prompt --}}
                        <template x-if="!edcWaiting && edcApproved">
                            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center space-y-1">
                                <i class="fa-solid fa-circle-check text-2xl text-emerald-600"></i>
                                <div class="text-sm font-bold text-emerald-900">Pembayaran EDC Disetujui</div>
                                <div class="text-xs text-emerald-700 font-medium" x-text="edcReference"></div>
                            </div>
                        </template>

                        {{-- Standard Payment Method Info --}}
                        <template x-if="!edcWaiting && !edcApproved">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-500">Metode Pembayaran</span>
                                    <span
                                        class="px-3 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 font-bold uppercase text-xs"
                                        x-text="paymentMethod || 'CASH'"></span>
                                </div>

                                <template x-if="paymentMethod === 'cash' && amountTendered > 0">
                                    <div class="pt-2 border-t border-blue-100 space-y-1.5">
                                        <div class="flex justify-between text-sm text-slate-600">
                                            <span>Uang Diterima</span>
                                            <span class="font-semibold text-slate-800"
                                                x-text="'Rp ' + formatNumber(amountTendered)"></span>
                                        </div>
                                        <div class="flex justify-between text-base font-bold text-emerald-600">
                                            <span>Kembalian</span>
                                            <span x-text="'Rp ' + formatNumber(change)"></span>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="paymentMethod === 'qris'">
                                    <div
                                        class="pt-2 border-t border-blue-100 text-xs text-blue-700 text-center font-medium">
                                        <i class="fa-solid fa-qrcode mr-1 text-blue-600"></i> Silakan scan QRIS sesuai
                                        nominal tagihan.
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <script>
        function customerDisplayApp(storeName, receiptFooter) {
            let broadcastChannel = null;
            let lastSnapshot = '';

            return {
                storeName,
                receiptFooter,
                state: 'idle',
                outletName: '',
                customer: null,
                items: [],
                subtotal: 0,
                discount: 0,
                taxLabel: 'Tax',
                taxAmount: 0,
                total: 0,
                pointsToEarn: 0,
                paymentMethod: 'cash',
                amountTendered: 0,
                change: 0,
                edcWaiting: false,
                edcApproved: false,
                edcBank: '',
                edcReference: '',
                currentTime: '',

                get totalQty() {
                    return this.items.reduce((sum, i) => sum + (Number(i.qty) || 0), 0);
                },

                init() {
                    this.updateClock();
                    setInterval(() => this.updateClock(), 1000);

                    // 1. Load initial snapshot from localStorage immediately
                    this.readFromStorage(true);

                    // 2. Poll localStorage every 350ms to guarantee auto-refresh in all browser/window modes
                    setInterval(() => {
                        this.readFromStorage(false);
                    }, 350);

                    // 3. Listen via storage event across tabs/windows
                    window.addEventListener('storage', (e) => {
                        if (e.key === 'pos_customer_display_state' && e.newValue) {
                            lastSnapshot = e.newValue;
                            try {
                                this.applyPayload(JSON.parse(e.newValue));
                            } catch {}
                        }
                    });

                    // 4. Listen via native BroadcastChannel (stored in closure, never wrapped by Alpine Proxy)
                    if ('BroadcastChannel' in window) {
                        try {
                            broadcastChannel = new BroadcastChannel('pos_customer_display');
                            broadcastChannel.onmessage = (event) => {
                                if (event.data && event.data.type === 'sync_state' && event.data.payload) {
                                    this.applyPayload(event.data.payload);
                                }
                            };
                            broadcastChannel.postMessage({
                                type: 'request_sync'
                            });
                        } catch {}
                    }

                    // 5. Listen via window.postMessage (when opened via window.open from POS)
                    window.addEventListener('message', (event) => {
                        if (event.origin === window.location.origin && event.data && event.data.type ===
                            'sync_state' && event.data.payload) {
                            this.applyPayload(event.data.payload);
                        }
                    });
                    if (window.opener && !window.opener.closed) {
                        try {
                            window.opener.postMessage({
                                type: 'request_sync'
                            }, window.location.origin);
                        } catch {}
                    }

                    // 6. Also request sync via localStorage ping
                    try {
                        localStorage.setItem('pos_customer_display_ping', String(Date.now()));
                    } catch {}
                },

                readFromStorage(force = false) {
                    try {
                        const raw = localStorage.getItem('pos_customer_display_state');
                        if (!raw) return;
                        if (!force && raw === lastSnapshot) return;
                        lastSnapshot = raw;
                        this.applyPayload(JSON.parse(raw));
                    } catch {}
                },

                applyPayload(payload) {
                    if (!payload || typeof payload !== 'object') return;
                    const nextItems = Array.isArray(payload.items) ?
                        payload.items.map(i => ({
                            ...i
                        })) : [];

                    this.items = nextItems;
                    this.state = payload.state || (nextItems.length > 0 ? 'active' : 'idle');
                    this.outletName = payload.outletName || '';
                    this.customer = payload.customer ? {
                        ...payload.customer
                    } : null;
                    this.subtotal = Number(payload.subtotal || 0);
                    this.discount = Number(payload.discount || 0);
                    this.taxLabel = payload.taxLabel || 'Tax';
                    this.taxAmount = Number(payload.taxAmount || 0);
                    this.total = Number(payload.total || 0);
                    this.pointsToEarn = Number(payload.pointsToEarn || 0);
                    this.paymentMethod = payload.paymentMethod || 'cash';
                    this.amountTendered = Number(payload.amountTendered || 0);
                    this.change = Number(payload.change || 0);
                    this.edcWaiting = Boolean(payload.edcWaiting);
                    this.edcApproved = Boolean(payload.edcApproved);
                    this.edcBank = payload.edcBank || '';
                    this.edcReference = payload.edcReference || '';
                },

                lineTotal(item) {
                    return Math.max(0, (Number(item.price) * Number(item.qty)) - (Number(item.discount) || 0));
                },

                formatNumber(n) {
                    return Math.round(Number(n) || 0).toLocaleString('id-ID');
                },

                updateClock() {
                    const now = new Date();
                    this.currentTime = now.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },

                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen?.();
                    } else {
                        document.exitFullscreen?.();
                    }
                }
            };
        }
    </script>
</body>

</html>
