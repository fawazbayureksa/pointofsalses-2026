@extends('layouts.admin')

@section('title', 'POS - Cashier')
@section('page-title', 'Point of Sale')

@push('styles')
    <style>
        .product-card {
            transition: transform .1s, box-shadow .1s;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, .12);
        }

        .product-card.out-of-stock {
            opacity: .5;
            cursor: not-allowed;
        }

        .category-tab.active {
            background: #2563eb;
            color: #fff;
        }

        .cart-item-row:not(:last-child) {
            border-bottom: 1px solid #f3f4f6;
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
        }

        /* Camera scanner modal */
        .scan-line {
            position: absolute;
            left: 10%;
            right: 10%;
            height: 2px;
            background: rgba(37, 99, 235, 0.8);
            box-shadow: 0 0 6px 2px rgba(37, 99, 235, 0.5);
            animation: scan 2s linear infinite;
            top: 0;
        }

        @keyframes scan {
            0%   { top: 10%; }
            50%  { top: 90%; }
            100% { top: 10%; }
        }

        .scan-corner {
            position: absolute;
            width: 20px;
            height: 20px;
            border-color: #2563eb;
            border-style: solid;
        }
        .scan-corner-tl { top: 8px;  left: 8px;  border-width: 3px 0 0 3px; }
        .scan-corner-tr { top: 8px;  right: 8px; border-width: 3px 3px 0 0; }
        .scan-corner-bl { bottom: 8px; left: 8px;  border-width: 0 0 3px 3px; }
        .scan-corner-br { bottom: 8px; right: 8px; border-width: 0 3px 3px 0; }
    </style>
@endpush

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
        $productsJson = $products
            ->map(
                fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'barcode' => $p->barcode ?? null,
                    'price' => (float) $p->price,
                    'category_id' => $p->category_id,
                    'category' => $p->category?->name ?? 'Uncategorized',
                    'unit' => $p->unit ?? 'pcs',
                    'image' => $p->image ? Storage::url($p->image) : null,
                    'stock' => $p->outlets->sum(fn($o) => $o->pivot->stock ?? 0),
                ],
            )
            ->values();
    @endphp

    <div x-data="posApp({{ Js::from($productsJson) }}, {{ Js::from($outlets->values()) }}, {{ Js::from($customers->values()) }})" class="flex h-[calc(100vh-130px)] gap-4">
        {{-- ============================================================ --}}
        {{-- LEFT PANEL: Product Browser --}}
        {{-- ============================================================ --}}
        <div class="flex-1 flex flex-col bg-white rounded-xl shadow overflow-hidden">

            {{-- Search + Category Filter --}}
            <div class="p-4 border-b border-gray-100 space-y-3">
                {{-- Barcode Scanner Input --}}
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <i class="fa-solid fa-barcode absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" x-ref="barcodeInput" x-model="barcodeInput"
                            @keydown.enter.prevent="scanBarcode()"
                            placeholder="Scan barcode or type SKU, then press Enter…"
                            :class="{
                                'border-green-400 bg-green-50 focus:ring-green-400': barcodeStatus === 'found',
                                'border-red-400 bg-red-50 focus:ring-red-400': barcodeStatus === 'notfound',
                                'border-orange-400 bg-orange-50 focus:ring-orange-400': barcodeStatus === 'outofstock',
                                'border-gray-200': barcodeStatus === null
                            }"
                            class="w-full pl-9 pr-24 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2">
                        <span x-show="barcodeStatus === 'found'"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-green-600 text-xs font-semibold">
                            <i class="fa-solid fa-check mr-1"></i>Added!
                        </span>
                        <span x-show="barcodeStatus === 'notfound'"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-red-500 text-xs font-semibold">
                            <i class="fa-solid fa-times mr-1"></i>Not found
                        </span>
                        <span x-show="barcodeStatus === 'outofstock'"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-orange-500 text-xs font-semibold">
                            <i class="fa-solid fa-ban mr-1"></i>Out of stock
                        </span>
                    </div>
                    {{-- Camera scan button --}}
                    <button @click="openCamera()" title="Scan with camera"
                        class="shrink-0 flex items-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                        <i class="fa-solid fa-camera"></i>
                        <span class="hidden sm:inline">Camera</span>
                    </button>
                </div>
                <div class="relative">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" x-model="search" placeholder="Search products…"
                        class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-center gap-2 overflow-x-auto pb-1">
                    <button @click="selectedCategory = null"
                        :class="selectedCategory === null ? 'active' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="category-tab shrink-0 px-3 py-1 rounded-full text-xs font-medium">All</button>
                    @foreach ($categories as $cat)
                        <button @click="selectedCategory = {{ $cat->id }}"
                            :class="selectedCategory === {{ $cat->id }} ? 'active' :
                                'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="category-tab shrink-0 px-3 py-1 rounded-full text-xs font-medium">{{ $cat->name }}</button>
                    @endforeach
                </div>
            </div>

            {{-- Product Grid --}}
            <div class="flex-1 overflow-y-auto p-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div class="product-card border border-gray-200 rounded-lg p-3 flex flex-col gap-1"
                            :class="product.stock <= 0 ? 'out-of-stock' : ''"
                            @click="product.stock > 0 && addToCart(product)">
                            <div
                                class="bg-gray-50 rounded aspect-square flex items-center justify-center mb-1 overflow-hidden">
                                <template x-if="product.image">
                                    <img :src="product.image" :alt="product.name"
                                        class="w-full h-full object-cover rounded">
                                </template>
                                <template x-if="!product.image">
                                    <i class="fa-solid fa-box text-gray-300 text-2xl"></i>
                                </template>
                            </div>
                            <div class="text-xs font-semibold text-gray-800 leading-tight line-clamp-2"
                                x-text="product.name"></div>
                            <div class="text-xs text-gray-400" x-text="product.category"></div>
                            <div class="mt-auto flex items-center justify-between">
                                <span class="text-sm font-bold text-blue-600"
                                    x-text="'Rp ' + formatNumber(product.price)"></span>
                                <span class="text-xs px-1.5 py-0.5 rounded-full"
                                    :class="product.stock > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'"
                                    x-text="product.stock > 0 ? product.stock + ' ' + product.unit : 'Out'"></span>
                            </div>
                        </div>
                    </template>
                    <template x-if="filteredProducts.length === 0">
                        <div class="col-span-4 py-12 text-center text-gray-400">
                            <i class="fa-solid fa-box-open text-4xl mb-2"></i>
                            <p class="text-sm">No products found</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- RIGHT PANEL: Cart & Checkout --}}
        {{-- ============================================================ --}}
        <div class="w-[400px] shrink-0 flex flex-col bg-white rounded-xl shadow overflow-hidden">

            {{-- Cart Header --}}
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">Cart</h2>
                    <p class="text-xs text-gray-400" x-text="cart.length + ' item(s)'"></p>
                </div>
                <button @click="clearCart()" class="text-xs text-red-500 hover:text-red-700" x-show="cart.length > 0">
                    <i class="fa-solid fa-trash mr-1"></i>Clear
                </button>
            </div>

            {{-- Outlet + Customer --}}
            <div class="px-5 py-3 grid grid-cols-2 gap-3 border-b border-gray-100">
                <div>
                    <label class="text-xs font-medium text-gray-500 block mb-1">Outlet *</label>
                    <select x-model="selectedOutlet"
                        class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select outlet</option>
                        @foreach ($outlets as $outlet)
                            <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500 block mb-1">Customer</label>
                    <select x-model="selectedCustomer"
                        class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Walk-in / Guest</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Cart Items --}}
            <div class="flex-1 overflow-y-auto px-5 py-2">
                <template x-if="cart.length === 0">
                    <div class="py-12 text-center text-gray-300">
                        <i class="fa-solid fa-cart-shopping text-4xl mb-2"></i>
                        <p class="text-sm">Cart is empty</p>
                    </div>
                </template>

                <template x-for="(item, idx) in cart" :key="item.product_id">
                    <div class="cart-item-row py-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-800 truncate" x-text="item.name"></div>
                                <div class="text-xs text-gray-400"
                                    x-text="'Rp ' + formatNumber(item.price) + ' / ' + item.unit"></div>
                            </div>
                            <button @click="removeFromCart(idx)" class="text-gray-300 hover:text-red-500 shrink-0 mt-0.5">
                                <i class="fa-solid fa-times text-xs"></i>
                            </button>
                        </div>
                        <div class="mt-2 flex items-center gap-2">
                            {{-- Qty stepper --}}
                            <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                                <button @click="updateQty(idx, item.qty - 1)"
                                    class="px-2 py-1 text-gray-500 hover:bg-gray-100 text-sm">−</button>
                                <input type="number" x-model.number="item.qty"
                                    @input="item.qty = Math.max(0.001, item.qty)" min="0.001" step="1"
                                    class="w-12 text-center text-sm py-1 border-0 focus:outline-none">
                                <button @click="updateQty(idx, item.qty + 1)"
                                    class="px-2 py-1 text-gray-500 hover:bg-gray-100 text-sm">+</button>
                            </div>
                            {{-- Per-item discount --}}
                            <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden flex-1">
                                <span
                                    class="px-2 text-xs text-gray-400 bg-gray-50 border-r border-gray-200 py-1">Disc</span>
                                <input type="number" x-model.number="item.discount" min="0" step="100"
                                    placeholder="0" class="flex-1 px-2 py-1 text-xs text-right focus:outline-none w-full">
                            </div>
                            {{-- Line total --}}
                            <div class="text-sm font-semibold text-blue-600 shrink-0"
                                x-text="'Rp ' + formatNumber(lineTotal(item))"></div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Order Summary --}}
            <div class="px-5 py-3 border-t border-gray-100 space-y-1.5 bg-gray-50">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Subtotal</span>
                    <span x-text="'Rp ' + formatNumber(cartSubtotal)"></span>
                </div>

                {{-- Order-level discount --}}
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600 shrink-0">Discount</span>
                    <select x-model="discountType" class="text-xs border border-gray-200 rounded px-1.5 py-1">
                        <option value="fixed">Rp</option>
                        <option value="percentage">%</option>
                    </select>
                    <input type="number" x-model.number="discountAmount" min="0" step="100" placeholder="0"
                        class="flex-1 text-right text-xs border border-gray-200 rounded px-2 py-1 focus:outline-none">
                    <span class="text-sm text-gray-600 shrink-0"
                        x-text="'-Rp ' + formatNumber(orderDiscountValue)"></span>
                </div>

                <div class="flex justify-between text-sm text-gray-600">
                    <span>Tax</span>
                    <span x-text="'Rp ' + formatNumber(taxAmount)"></span>
                </div>

                <div class="flex justify-between text-lg font-bold text-gray-900 border-t border-gray-200 pt-2">
                    <span>TOTAL</span>
                    <span x-text="'Rp ' + formatNumber(total)"></span>
                </div>
            </div>

            {{-- Payment --}}
            <div class="px-5 py-3 border-t border-gray-100 space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs font-medium text-gray-500 block mb-1">Payment Method *</label>
                        <select x-model="paymentMethod"
                            class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="qris">QRIS</option>
                            <option value="transfer">Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 block mb-1">
                            <span x-show="paymentMethod === 'cash'">Amount Tendered</span>
                            <span x-show="paymentMethod !== 'cash'">Amount</span>
                        </label>
                        <input type="number" x-model.number="amountTendered" :placeholder="formatNumber(total)"
                            min="0" step="1000"
                            class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-right">
                    </div>
                </div>

                {{-- Change --}}
                <div x-show="paymentMethod === 'cash' && amountTendered > 0"
                    class="flex justify-between text-sm font-medium bg-green-50 rounded-lg px-3 py-2">
                    <span class="text-green-700">Change</span>
                    <span class="text-green-700 font-bold"
                        x-text="'Rp ' + formatNumber(Math.max(0, amountTendered - total))"></span>
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500 block mb-1">Notes</label>
                    <textarea x-model="notes" rows="2" placeholder="Optional notes…"
                        class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>
            </div>

            {{-- Submit --}}
            <div class="px-5 pb-4 pt-2">
                <button @click="submitOrder()" :disabled="!canSubmit"
                    :class="canSubmit ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
                    class="w-full py-3 rounded-xl text-white font-semibold text-base transition-colors">
                    <i class="fa-solid fa-cash-register mr-2"></i>
                    Process Order
                </button>
                <p class="text-xs text-red-500 mt-1 text-center" x-show="submitError" x-text="submitError"></p>
            </div>
        </div>

        {{-- Hidden form for submission --}}
        <form id="pos-form" method="POST" action="{{ route('admin.orders.store') }}" class="hidden">
            @csrf
            <input type="hidden" name="outlet_id" x-bind:value="selectedOutlet">
            <input type="hidden" name="customer_id" x-bind:value="selectedCustomer">
            <input type="hidden" name="notes" x-bind:value="notes">
            <input type="hidden" name="discount_amount" x-bind:value="orderDiscountValue">
            <input type="hidden" name="discount_type" x-bind:value="discountType">
            <input type="hidden" name="payment_method" x-bind:value="paymentMethod">
            <input type="hidden" name="amount_tendered" x-bind:value="effectiveTendered">
            <div id="pos-items-container"></div>
        </form>

        {{-- ============================================================ --}}
        {{-- Camera Barcode Scanner Modal --}}
        {{-- ============================================================ --}}
        <div x-show="cameraOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
            @keydown.escape.window="stopCamera()">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md flex flex-col">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-semibold text-gray-800">
                            <i class="fa-solid fa-camera mr-2 text-blue-500"></i>Scan with Camera
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">Point your camera at a barcode</p>
                    </div>
                    <button @click="stopCamera()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fa-solid fa-times text-lg"></i>
                    </button>
                </div>

                {{-- Camera Preview --}}
                <div class="px-5 py-4">
                    <div class="relative bg-black rounded-xl overflow-hidden aspect-[4/3]">
                        <video x-ref="cameraVideo" class="w-full h-full object-cover" autoplay muted playsinline></video>
                        {{-- Scanning overlay --}}
                        <div class="absolute inset-0 pointer-events-none" x-show="!cameraError">
                            <div class="scan-line"></div>
                            <div class="scan-corner scan-corner-tl"></div>
                            <div class="scan-corner scan-corner-tr"></div>
                            <div class="scan-corner scan-corner-bl"></div>
                            <div class="scan-corner scan-corner-br"></div>
                        </div>
                        {{-- Error overlay --}}
                        <div x-show="cameraError" class="absolute inset-0 flex flex-col items-center justify-center bg-black/80 p-4">
                            <i class="fa-solid fa-video-slash text-white text-3xl mb-3"></i>
                            <p class="text-white text-sm text-center" x-text="cameraError"></p>
                        </div>
                    </div>

                    <p class="text-xs text-gray-400 text-center mt-3">
                        Supports EAN-13, EAN-8, UPC-A, Code 128, Code 39, QR Code
                    </p>
                </div>

                <div class="px-5 pb-4">
                    <button @click="stopCamera()"
                        class="w-full py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                        <i class="fa-solid fa-times mr-1"></i>Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function posApp(allProducts, outlets, customers) {
                return {
                    // -- Data --
                    allProducts,
                    outlets,
                    customers,
                    cart: [],
                    search: '',
                    selectedCategory: null,
                    selectedOutlet: outlets.length === 1 ? String(outlets[0].id) : '',
                    selectedCustomer: '',
                    discountType: 'fixed',
                    discountAmount: 0,
                    paymentMethod: 'cash',
                    amountTendered: 0,
                    notes: '',
                    submitError: '',
                    barcodeInput: '',
                    barcodeStatus: null,
                    _productIndex: {},
                    cameraOpen: false,
                    cameraError: '',
                    _scannerStream: null,
                    _scannerInterval: null,
                    _zxingReader: null,
                    _cameraIdealWidth: 1280,

                    // -- Lifecycle --
                    init() {
                        // Build O(1) barcode/SKU index for fast lookups
                        this._productIndex = {};
                        this.allProducts.forEach(p => {
                            if (p.barcode) this._productIndex[p.barcode] = p;
                            if (p.sku) this._productIndex[p.sku] = p;
                        });
                        this.$nextTick(() => this.$refs.barcodeInput.focus());
                    },

                    // -- Computed --
                    get filteredProducts() {
                        return this.allProducts.filter(p => {
                            const matchesSearch = !this.search ||
                                p.name.toLowerCase().includes(this.search.toLowerCase()) ||
                                (p.sku && p.sku.toLowerCase().includes(this.search.toLowerCase())) ||
                                (p.barcode && p.barcode.toLowerCase().includes(this.search.toLowerCase()));
                            const matchesCat = this.selectedCategory === null || p.category_id === this
                                .selectedCategory;
                            return matchesSearch && matchesCat;
                        });
                    },
                    get cartSubtotal() {
                        return this.cart.reduce((sum, item) => sum + this.lineTotal(item), 0);
                    },
                    get orderDiscountValue() {
                        if (!this.discountAmount || this.discountAmount <= 0) return 0;
                        if (this.discountType === 'percentage') {
                            return Math.round(this.cartSubtotal * (Math.min(100, this.discountAmount) / 100));
                        }
                        return Math.min(this.cartSubtotal, this.discountAmount);
                    },
                    get taxAmount() {
                        return 0; // extend: read from settings
                    },
                    get total() {
                        return Math.max(0, this.cartSubtotal - this.orderDiscountValue + this.taxAmount);
                    },
                    get effectiveTendered() {
                        return this.amountTendered > 0 ? this.amountTendered : this.total;
                    },
                    get canSubmit() {
                        return this.cart.length > 0 &&
                            this.selectedOutlet &&
                            this.paymentMethod &&
                            (this.paymentMethod !== 'cash' || this.effectiveTendered >= this.total);
                    },

                    // -- Methods --
                    lineTotal(item) {
                        return Math.max(0, (item.price * item.qty) - (item.discount || 0));
                    },
                    formatNumber(n) {
                        return Math.round(n).toLocaleString('id-ID');
                    },
                    addToCart(product) {
                        const existing = this.cart.findIndex(i => i.product_id === product.id);
                        if (existing >= 0) {
                            this.cart[existing].qty++;
                        } else {
                            this.cart.push({
                                product_id: product.id,
                                name: product.name,
                                price: product.price,
                                unit: product.unit,
                                qty: 1,
                                discount: 0,
                            });
                        }
                    },
                    scanBarcode() {
                        const code = this.barcodeInput.trim();
                        this.barcodeInput = '';
                        if (!code) return;
                        const product = this._productIndex[code] || null;
                        if (!product) {
                            this.barcodeStatus = 'notfound';
                        } else if (product.stock <= 0) {
                            this.barcodeStatus = 'outofstock';
                        } else {
                            this.addToCart(product);
                            this.barcodeStatus = 'found';
                        }
                        setTimeout(() => { this.barcodeStatus = null; }, 1500);
                    },
                    async openCamera() {
                        this.cameraError = '';
                        this.cameraOpen = true;
                        await this.$nextTick();
                        try {
                            const stream = await navigator.mediaDevices.getUserMedia({
                                video: { facingMode: { ideal: 'environment' }, width: { ideal: this._cameraIdealWidth } }
                            });
                            this._scannerStream = stream;
                            const video = this.$refs.cameraVideo;
                            video.srcObject = stream;
                            await video.play();
                            this._startScanLoop();
                        } catch (err) {
                            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                                this.cameraError = 'Camera access denied. Please allow camera permission in your browser.';
                            } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                                this.cameraError = 'No camera found on this device.';
                            } else {
                                this.cameraError = 'Camera unavailable: ' + err.message;
                            }
                        }
                    },
                    stopCamera() {
                        if (this._scannerInterval) {
                            clearInterval(this._scannerInterval);
                            this._scannerInterval = null;
                        }
                        if (this._zxingReader) {
                            try { this._zxingReader.reset(); } catch { /* reset() may throw if stream already closed */ }
                            this._zxingReader = null;
                        }
                        if (this._scannerStream) {
                            this._scannerStream.getTracks().forEach(t => t.stop());
                            this._scannerStream = null;
                        }
                        this.cameraOpen = false;
                        this.cameraError = '';
                        this.$nextTick(() => this.$refs.barcodeInput.focus());
                    },
                    async _startScanLoop() {
                        if ('BarcodeDetector' in window) {
                            // Native BarcodeDetector (Chrome 83+, Edge 83+, Samsung Internet)
                            let supported = ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39', 'code_93', 'qr_code', 'data_matrix'];
                            try {
                                const detected = await BarcodeDetector.getSupportedFormats();
                                supported = supported.filter(f => detected.includes(f));
                            } catch {}
                            const detector = new BarcodeDetector({ formats: supported.length ? supported : ['ean_13', 'code_128'] });
                            const video = this.$refs.cameraVideo;
                            this._scannerInterval = setInterval(async () => {
                                if (!this.cameraOpen || !video.readyState || video.readyState < 2) return;
                                try {
                                    const barcodes = await detector.detect(video);
                                    if (barcodes.length > 0) {
                                        const code = barcodes[0].rawValue;
                                        this.stopCamera();
                                        this.barcodeInput = code;
                                        this.scanBarcode();
                                    }
                                } catch {}
                            }, 300);
                        } else {
                            // Fallback: @zxing/browser (Firefox and other browsers)
                            try {
                                await this._loadScript(
                                    'https://cdn.jsdelivr.net/npm/@zxing/browser@0.1.5/umd/index.min.js',
                                    'sha384-OLBgp1GsljhM2TJ+sbHjaiH9txEUvgdDTAzHv2P24donTt6/529l+9Ua0vFImLlb'
                                );
                                const video = this.$refs.cameraVideo;
                                this._zxingReader = new ZXingBrowser.BrowserMultiFormatReader();
                                this._zxingReader.decodeFromVideoElement(video, (result, err) => {
                                    if (result && this.cameraOpen) {
                                        const code = result.getText();
                                        this.stopCamera();
                                        this.barcodeInput = code;
                                        this.scanBarcode();
                                    }
                                });
                            } catch (err) {
                                this.cameraError = 'Barcode detection not available in this browser. Please use Chrome or Edge.';
                            }
                        }
                    },
                    _loadScript(src, integrity) {
                        return new Promise((resolve, reject) => {
                            if (document.querySelector(`script[src="${src}"]`)) { resolve(); return; }
                            const s = document.createElement('script');
                            s.src = src;
                            if (integrity) {
                                s.integrity = integrity;
                                s.crossOrigin = 'anonymous';
                            }
                            s.onload = resolve;
                            s.onerror = () => reject(new Error('Failed to load ' + src));
                            document.head.appendChild(s);
                        });
                    },
                    removeFromCart(idx) {
                        this.cart.splice(idx, 1);
                    },
                    updateQty(idx, val) {
                        const qty = parseFloat(val);
                        if (qty <= 0) {
                            this.removeFromCart(idx);
                        } else {
                            this.cart[idx].qty = qty;
                        }
                    },
                    clearCart() {
                        if (confirm('Clear all items from cart?')) {
                            this.cart = [];
                        }
                    },
                    submitOrder() {
                        this.submitError = '';
                        if (!this.selectedOutlet) {
                            this.submitError = 'Please select an outlet.';
                            return;
                        }
                        if (this.cart.length === 0) {
                            this.submitError = 'Cart is empty.';
                            return;
                        }
                        if (this.paymentMethod === 'cash' && this.effectiveTendered < this.total) {
                            this.submitError = 'Amount tendered is less than total.';
                            return;
                        }

                        // Build items inputs dynamically
                        const container = document.getElementById('pos-items-container');
                        container.innerHTML = '';
                        this.cart.forEach((item, i) => {
                            container.innerHTML += `
                    <input type="hidden" name="items[${i}][product_id]"      value="${item.product_id}">
                    <input type="hidden" name="items[${i}][quantity]"         value="${item.qty}">
                    <input type="hidden" name="items[${i}][discount_amount]"  value="${item.discount || 0}">
                `;
                        });

                        document.getElementById('pos-form').submit();
                    },
                };
            }
        </script>
    @endpush
@endsection
