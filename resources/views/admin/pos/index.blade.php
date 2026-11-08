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
            0% {
                top: 10%;
            }

            50% {
                top: 90%;
            }

            100% {
                top: 10%;
            }
        }

        .scan-corner {
            position: absolute;
            width: 20px;
            height: 20px;
            border-color: #2563eb;
            border-style: solid;
        }

        .scan-corner-tl {
            top: 8px;
            left: 8px;
            border-width: 3px 0 0 3px;
        }

        .scan-corner-tr {
            top: 8px;
            right: 8px;
            border-width: 3px 3px 0 0;
        }

        .scan-corner-bl {
            bottom: 8px;
            left: 8px;
            border-width: 0 0 3px 3px;
        }

        .scan-corner-br {
            bottom: 8px;
            right: 8px;
            border-width: 0 3px 3px 0;
        }
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
                    'track_stock' => (bool) ($p->track_stock ?? true),
                    'has_outlet_assignment' => $p->outlets->isNotEmpty(),
                    'outlet_stocks' => $p->outlets
                        ->mapWithKeys(fn($o) => [(string) $o->id => (float) ($o->pivot->stock ?? 0)])
                        ->all(),
                    'stock' => (float) $p->outlets->sum(fn($o) => $o->pivot->stock ?? 0),
                ],
            )
            ->values();

        $initialOutletId =
            isset($defaultOutletId) && $defaultOutletId !== null
                ? (string) $defaultOutletId
                : ($outlets->count() === 1
                    ? (string) $outlets->first()->id
                    : '');

        $resolvedTaxConfig = $taxConfig ?? [
            'enabled' => false,
            'rate' => 0.0,
            'label' => 'Tax',
            'inclusion' => 'exclusive',
        ];

        $resolvedEdcConfig =
            $edcConfig ??
            (function_exists('edc_config')
                ? edc_config()
                : [
                    'enabled' => false,
                    'connection_mode' => 'manual',
                    'banks' => ['BCA', 'Mandiri', 'BRI', 'BNI', 'CIMB Niaga'],
                    'require_reference' => true,
                    'serial_baud_rate' => 9600,
                ]);

        $initialError = $errors->first() ?? (session('error') ?? '');
    @endphp

    <div x-data="posApp(
        {{ Js::from($productsJson) }},
        {{ Js::from($outlets->values()) }},
        {{ Js::from($customersJson) }},
        {{ Js::from($initialOutletId) }},
        {{ Js::from($resolvedTaxConfig) }},
        {{ Js::from($initialError) }},
        {{ Js::from($resolvedEdcConfig) }},
        {{ Js::from(route('admin.pos.customer-display')) }}
    )" class="flex flex-col h-[calc(100vh-130px)] gap-3">

        {{-- Server-side Validation & Flash Error Alerts --}}
        @if ($errors->any())
            <div x-data="{ show: true }" x-show="show"
                class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-start justify-between gap-3 shrink-0">
                <div class="flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5"></i>
                    <div class="text-sm text-red-700">
                        <p class="font-semibold text-red-800">Failed to create order</p>
                        <ul class="list-disc list-inside mt-1 space-y-0.5 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button @click="show = false" type="button" class="text-red-400 hover:text-red-600">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div x-data="{ show: true }" x-show="show"
                class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-2.5 text-sm text-red-700">
                    <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button @click="show = false" type="button" class="text-red-400 hover:text-red-600">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        @endif

        @if (session('success'))
            <div x-data="{ show: true }" x-show="show"
                class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 flex items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-2.5 text-sm text-green-700">
                    <i class="fa-solid fa-circle-check text-green-500"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button @click="show = false" type="button" class="text-green-400 hover:text-green-600">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        @endif

        @if ($outlets->isEmpty())
            <div
                class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-2.5 text-sm text-amber-800">
                    <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                    <span>
                        <strong>No active outlet found.</strong> You cannot create an order without an active outlet. Please
                        create or activate an outlet first.
                    </span>
                </div>
                <a href="{{ route('admin.outlets.index') }}"
                    class="shrink-0 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg transition-colors">
                    <i class="fa-solid fa-store mr-1"></i>Manage Outlets
                </a>
            </div>
        @endif

        <div class="flex-1 min-h-0 flex gap-4">
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
                                :class="isOutOfStock(product) ? 'out-of-stock' : ''"
                                @click="!isOutOfStock(product) && addToCart(product)">
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
                                        :class="!isOutOfStock(product) ? 'bg-green-100 text-green-700' :
                                            'bg-red-100 text-red-600'"
                                        x-text="stockLabel(product)"></span>
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
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-2">
                    <div>
                        <h2 class="text-base font-semibold text-gray-800">Cart</h2>
                        <p class="text-xs text-gray-400" x-text="cart.length + ' item(s)'"></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="openCustomerDisplay()" type="button"
                            title="Open Customer-Facing Display (Mirror Screen)"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-green-200 bg-green-50 hover:bg-green-100 text-green-700 text-xs font-medium transition-colors">
                            <i class="fa-solid fa-tv"></i>
                            <span>Customer Screen</span>
                        </button>
                        <button @click="clearCart()" type="button" class="text-xs text-red-500 hover:text-red-700"
                            x-show="cart.length > 0">
                            <i class="fa-solid fa-trash mr-1"></i>Clear
                        </button>
                    </div>
                </div>

                {{-- Outlet + Customer --}}
                <div class="px-5 py-3 grid grid-cols-2 gap-3 border-b border-gray-100">
                    <div>
                        <label class="text-xs font-medium text-gray-500 block mb-1">Outlet *</label>
                        <select x-model="selectedOutlet" @change="submitError = ''; syncCustomerDisplay()"
                            :class="!selectedOutlet && submitError ? 'border-red-400 bg-red-50' : 'border-gray-200'"
                            class="w-full text-sm border rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">{{ $outlets->isEmpty() ? 'No outlet available' : 'Select outlet' }}
                            </option>
                            @foreach ($outlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                            @endforeach
                        </select>
                        @if ($outlets->isEmpty())
                            <a href="{{ route('admin.outlets.index') }}"
                                class="text-[11px] text-amber-600 hover:underline mt-1 inline-block">
                                + Create outlet first
                            </a>
                        @endif
                    </div>
                    <div class="relative">
                        <label class="text-xs font-medium text-gray-500 block mb-1">Customer</label>
                        <div class="relative">
                            <i
                                class="fa-solid fa-user absolute left-2 top-1/2 -translate-y-1/2 text-gray-300 text-xs pointer-events-none"></i>
                            <input type="text" x-model="customerQuery" @focus="customerDropdownOpen = true"
                                @input="customerDropdownOpen = true" @keydown.escape="customerDropdownOpen = false"
                                :placeholder="selectedCustomerObj ? selectedCustomerObj.name : 'Walk-in / Guest'"
                                class="w-full text-sm border border-gray-200 rounded-lg pl-6 pr-6 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button x-show="selectedCustomerObj" @click="clearCustomer()" type="button" tabindex="-1"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </div>
                        <div x-show="customerDropdownOpen" @click.outside="customerDropdownOpen = false"
                            class="absolute z-50 top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto mt-0.5">
                            <button @click="clearCustomer()" type="button"
                                class="w-full text-left px-3 py-2 text-xs text-gray-400 hover:bg-gray-50 border-b border-gray-100 flex items-center gap-1.5">
                                <i class="fa-solid fa-person-walking"></i> Walk-in / Guest
                            </button>
                            <template x-for="c in filteredCustomers" :key="c.id">
                                <button @click="selectCustomer(c)" type="button"
                                    class="w-full text-left px-3 py-2 hover:bg-blue-50 flex items-center justify-between gap-2 border-b border-gray-50">
                                    <div class="min-w-0">
                                        <span x-text="c.name"
                                            class="text-sm font-medium text-gray-800 block truncate"></span>
                                        <span x-show="c.phone" x-text="c.phone" class="text-xs text-gray-400"></span>
                                    </div>
                                    <div x-show="c.is_member" class="shrink-0 text-right">
                                        <span x-text="c.membership_tier"
                                            class="text-xs px-1.5 py-0.5 rounded-full bg-yellow-100 text-yellow-700 capitalize block mb-0.5"></span>
                                        <span x-text="formatNumber(c.loyalty_points) + ' pts'"
                                            class="text-xs text-blue-500"></span>
                                    </div>
                                </button>
                            </template>
                            <div x-show="filteredCustomers.length === 0 && customerQuery"
                                class="px-3 py-3 text-xs text-gray-400 text-center italic">No customers found</div>
                        </div>
                    </div>
                </div>

                {{-- Member info card --}}
                <div x-show="selectedCustomerObj && selectedCustomerObj.is_member"
                    class="px-5 pt-2 pb-3 bg-green-50/50 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                                :class="{
                                    'bg-gray-400': selectedCustomerObj?.membership_tier === 'regular',
                                    'bg-slate-500': selectedCustomerObj?.membership_tier === 'silver',
                                    'bg-yellow-500': selectedCustomerObj?.membership_tier === 'gold',
                                    'bg-purple-600': selectedCustomerObj?.membership_tier === 'platinum'
                                }"
                                x-text="selectedCustomerObj?.name?.charAt(0)?.toUpperCase()"></div>
                            <div>
                                <div class="text-xs font-semibold text-gray-800 truncate max-w-[110px]"
                                    x-text="selectedCustomerObj?.name"></div>
                                <div class="text-xs text-gray-500 capitalize"
                                    x-text="(selectedCustomerObj?.membership_tier ?? '') + ' Member'"></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-500">Current Points</div>
                            <div class="text-sm font-bold text-blue-600"
                                x-text="formatNumber(selectedCustomerObj?.loyalty_points ?? 0) + ' pts'"></div>
                        </div>
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
                                <button @click="removeFromCart(idx)"
                                    class="text-gray-300 hover:text-red-500 shrink-0 mt-0.5">
                                    <i class="fa-solid fa-times text-xs"></i>
                                </button>
                            </div>
                            <div class="mt-2 flex items-center gap-2">
                                {{-- Qty stepper --}}
                                <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                                    <button @click="updateQty(idx, item.qty - 1)"
                                        class="px-2 py-1 text-gray-500 hover:bg-gray-100 text-sm">−</button>
                                    <input type="number" x-model.number="item.qty" @input="enforceItemQty(idx)"
                                        min="0.001" step="1"
                                        class="w-12 text-center text-sm py-1 border-0 focus:outline-none">
                                    <button @click="updateQty(idx, item.qty + 1)"
                                        class="px-2 py-1 text-gray-500 hover:bg-gray-100 text-sm">+</button>
                                </div>
                                {{-- Per-item discount --}}
                                <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden flex-1">
                                    <span
                                        class="px-2 text-xs text-gray-400 bg-gray-50 border-r border-gray-200 py-1">Disc</span>
                                    <input type="number" x-model.number="item.discount" @input="syncCustomerDisplay()"
                                        min="0" step="100" placeholder="0"
                                        class="flex-1 px-2 py-1 text-xs text-right focus:outline-none w-full">
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
                        <select x-model="discountType" @change="syncCustomerDisplay()"
                            class="text-xs border border-gray-200 rounded px-1.5 py-1">
                            <option value="fixed">Rp</option>
                            <option value="percentage">%</option>
                        </select>
                        <input type="number" x-model.number="discountAmount" @input="syncCustomerDisplay()"
                            min="0" step="100" placeholder="0"
                            class="flex-1 text-right text-xs border border-gray-200 rounded px-2 py-1 focus:outline-none">
                        <span class="text-sm text-gray-600 shrink-0"
                            x-text="'-Rp ' + formatNumber(orderDiscountValue)"></span>
                    </div>

                    <div class="flex justify-between text-sm text-gray-600">
                        <span x-text="taxConfig.label || 'Tax'">Tax</span>
                        <span x-text="'Rp ' + formatNumber(taxAmount)"></span>
                    </div>

                    <div x-show="selectedCustomerObj && selectedCustomerObj.is_member && total > 0"
                        class="flex justify-between text-sm text-green-700 bg-green-50 rounded-lg px-3 py-1.5">
                        <span class="flex items-center gap-1"><i class="fa-solid fa-star text-xs"></i> Points to
                            earn</span>
                        <span x-text="'+' + pointsToEarn + ' pts'" class="font-semibold"></span>
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
                            <select x-model="paymentMethod" @change="onPaymentMethodChange()"
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
                            <input type="number" x-model.number="amountTendered" @input="syncCustomerDisplay()"
                                :placeholder="formatNumber(total)" min="0" step="1000"
                                class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-right">
                        </div>
                    </div>

                    {{-- Configurable Bank EDC Terminal Panel (Active only when Tenant EDC setting is enabled) --}}
                    <div x-show="edcConfig.enabled && paymentMethod === 'card'" x-cloak
                        class="bg-green-50/70 border border-green-200 rounded-xl p-3 space-y-2.5">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 text-xs font-semibold text-green-800">
                                <i class="fa-solid fa-credit-card text-green-600"></i>
                                <span>EDC Terminal Verification</span>
                            </div>
                            <button
                                x-show="edcConfig.connection_mode === 'ecr_serial' || edcConfig.connection_mode === 'both'"
                                @click="sendToEdcTerminal()" type="button" :disabled="edcWaiting || total <= 0"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white text-[11px] font-semibold transition-colors">
                                <i class="fa-solid" :class="edcWaiting ? 'fa-spinner fa-spin' : 'fa-bolt'"></i>
                                <span x-text="edcWaiting ? 'Waiting EDC…' : 'Send to EDC'"></span>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-[11px] font-medium text-green-700 block mb-1">Bank Acquirer</label>
                                <select x-model="edcBank" @change="syncCustomerDisplay()"
                                    class="w-full text-xs border border-green-200 bg-white rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <template x-for="bank in (edcConfig.banks || ['BCA', 'Mandiri', 'BRI', 'BNI'])"
                                        :key="bank">
                                        <option :value="bank" x-text="bank"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="text-[11px] font-medium text-green-700 block mb-1">Card Type</label>
                                <select x-model="edcCardType" @change="syncCustomerDisplay()"
                                    class="w-full text-xs border border-green-200 bg-white rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="Debit">Debit Card</option>
                                    <option value="Credit">Credit Card</option>
                                    <option value="Contactless">Contactless / Tap</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="text-[11px] font-medium text-green-700 block mb-1">
                                Approval Code / Reference No.
                                <span x-show="edcConfig.require_reference" class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" x-model="edcApprovalCode"
                                    @input="edcApproved = !!edcApprovalCode.trim(); submitError = ''; syncCustomerDisplay()"
                                    placeholder="e.g. APPR 6-digit / RRN from EDC slip"
                                    class="w-full text-xs border border-green-200 bg-white rounded-lg pl-2.5 pr-7 py-1.5 font-mono uppercase focus:outline-none focus:ring-2 focus:ring-green-500">
                                <span x-show="edcApprovalCode.trim().length > 0"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 text-emerald-600 text-xs">
                                    <i class="fa-solid fa-circle-check"></i>
                                </span>
                            </div>
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
                    <div x-show="submitError" x-cloak
                        class="mb-2 bg-red-50 border border-red-200 text-red-700 rounded-lg px-3 py-2 text-xs flex items-start gap-2">
                        <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 shrink-0"></i>
                        <span class="flex-1" x-text="submitError"></span>
                        <button @click="submitError = ''" type="button"
                            class="text-red-400 hover:text-red-600 shrink-0">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                    <button @click="submitOrder()" type="button"
                        :class="canSubmit ? 'bg-blue-600 hover:bg-blue-700' : 'bg-blue-500/80 hover:bg-blue-600'"
                        class="w-full py-3 rounded-xl text-white font-semibold text-base transition-colors">
                        <i class="fa-solid fa-cash-register mr-2"></i>
                        Process Order
                    </button>
                </div>
            </div>
        </div>

        {{-- Hidden form for submission --}}
        <form id="pos-form" method="POST" action="{{ route('admin.orders.store') }}" class="hidden">
            @csrf
            <input type="hidden" name="outlet_id" x-bind:value="selectedOutlet">
            <input type="hidden" name="customer_id" x-bind:value="selectedCustomer">
            <input type="hidden" name="notes" x-bind:value="notes">
            <input type="hidden" name="discount_amount" x-bind:value="submitDiscountAmount">
            <input type="hidden" name="discount_type" x-bind:value="discountType">
            <input type="hidden" name="tax_amount" x-bind:value="taxAmount">
            <input type="hidden" name="payment_method" x-bind:value="paymentMethod">
            <input type="hidden" name="amount_tendered" x-bind:value="effectiveTendered">
            <input type="hidden" name="reference_number" x-bind:value="computedReferenceNumber">
            <div id="pos-items-container"></div>
        </form>

        {{-- ============================================================ --}}
        {{-- Camera Barcode Scanner Modal --}}
        {{-- ============================================================ --}}
        <div x-show="cameraOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
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
                        <div x-show="cameraError"
                            class="absolute inset-0 flex flex-col items-center justify-center bg-black/80 p-4">
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
            function posApp(
                allProducts,
                outlets,
                customers,
                initialOutletId = '',
                taxConfig = null,
                initialError = '',
                edcConfig = null,
                customerDisplayUrl = ''
            ) {
                let displayChannel = null;
                let displayWindow = null;

                return {
                    // -- Data --
                    allProducts,
                    outlets,
                    customers,
                    taxConfig: taxConfig || {
                        enabled: false,
                        rate: 0,
                        label: 'Tax',
                        inclusion: 'exclusive'
                    },
                    edcConfig: edcConfig || {
                        enabled: false,
                        connection_mode: 'manual',
                        banks: ['BCA', 'Mandiri', 'BRI', 'BNI', 'CIMB Niaga'],
                        require_reference: true,
                        serial_baud_rate: 9600
                    },
                    customerDisplayUrl: customerDisplayUrl || '',
                    cart: [],
                    search: '',
                    selectedCategory: null,
                    selectedOutlet: initialOutletId ?
                        String(initialOutletId) : (outlets.length === 1 ? String(outlets[0].id) : ''),
                    selectedCustomer: '',
                    selectedCustomerObj: null,
                    customerQuery: '',
                    customerDropdownOpen: false,
                    discountType: 'fixed',
                    discountAmount: 0,
                    paymentMethod: 'cash',
                    amountTendered: 0,
                    notes: '',
                    submitError: initialError || '',
                    edcBank: (edcConfig && Array.isArray(edcConfig.banks) && edcConfig.banks.length > 0) ?
                        edcConfig.banks[0] : 'BCA',
                    edcCardType: 'Debit',
                    edcApprovalCode: '',
                    edcWaiting: false,
                    edcApproved: false,
                    barcodeInput: '',
                    barcodeStatus: null,
                    _productIndex: {},
                    _productById: {},
                    cameraOpen: false,
                    cameraError: '',
                    _scannerStream: null,
                    _scannerInterval: null,
                    _zxingReader: null,
                    _cameraIdealWidth: 1280,

                    // -- Lifecycle --
                    init() {
                        // Build O(1) barcode/SKU and ID index for fast lookups
                        this._productIndex = {};
                        this._productById = {};
                        this.allProducts.forEach(p => {
                            this._productById[p.id] = p;
                            if (p.barcode) this._productIndex[p.barcode] = p;
                            if (p.sku) this._productIndex[p.sku] = p;
                        });

                        if ('BroadcastChannel' in window) {
                            try {
                                displayChannel = new BroadcastChannel('pos_customer_display');
                                displayChannel.onmessage = (event) => {
                                    if (event.data && event.data.type === 'request_sync') {
                                        this.syncCustomerDisplay();
                                    }
                                };
                            } catch {}
                        }

                        window.addEventListener('storage', (e) => {
                            if (e.key === 'pos_customer_display_ping') {
                                this.syncCustomerDisplay();
                            }
                        });

                        window.addEventListener('message', (event) => {
                            if (event.origin === window.location.origin && event.data && event.data.type ===
                                'request_sync') {
                                this.syncCustomerDisplay();
                            }
                        });

                        [
                            'cart',
                            'selectedOutlet',
                            'selectedCustomer',
                            'discountType',
                            'discountAmount',
                            'paymentMethod',
                            'amountTendered',
                            'edcBank',
                            'edcCardType',
                            'edcApprovalCode',
                            'edcWaiting',
                            'edcApproved'
                        ].forEach((prop) => {
                            this.$watch(prop, () => this.syncCustomerDisplay());
                        });

                        this.syncCustomerDisplay();
                        this.$nextTick(() => this.$refs.barcodeInput?.focus());
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
                    get filteredCustomers() {
                        if (!this.customerQuery) return this.customers;
                        const q = this.customerQuery.toLowerCase();
                        return this.customers.filter(c =>
                            c.name.toLowerCase().includes(q) ||
                            (c.phone && c.phone.includes(q))
                        );
                    },
                    get pointsToEarn() {
                        return Math.floor(this.total / 1000);
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
                    get submitDiscountAmount() {
                        if (!this.discountAmount || this.discountAmount <= 0) return 0;
                        if (this.discountType === 'percentage') {
                            return Math.min(100, Math.max(0, this.discountAmount));
                        }
                        return this.orderDiscountValue;
                    },
                    get taxAmount() {
                        if (!this.taxConfig || !this.taxConfig.enabled || !this.taxConfig.rate || this.taxConfig.rate <=
                            0) {
                            return 0;
                        }
                        if (this.taxConfig.inclusion === 'inclusive') {
                            return 0;
                        }
                        const taxable = Math.max(0, this.cartSubtotal - this.orderDiscountValue);
                        return Math.round(taxable * (this.taxConfig.rate / 100));
                    },
                    get total() {
                        return Math.max(0, this.cartSubtotal - this.orderDiscountValue + this.taxAmount);
                    },
                    get effectiveTendered() {
                        return this.amountTendered > 0 ? this.amountTendered : this.total;
                    },
                    get computedReferenceNumber() {
                        if (this.paymentMethod !== 'card' || !this.edcConfig || !this.edcConfig.enabled) {
                            return '';
                        }
                        const code = (this.edcApprovalCode || '').trim().toUpperCase();
                        if (!code) {
                            return '';
                        }
                        const bank = this.edcBank || 'EDC';
                        const cardType = this.edcCardType || 'Card';
                        return `${bank} (${cardType}) - APPR:${code}`;
                    },
                    get canSubmit() {
                        const edcReady = !this.edcConfig?.enabled ||
                            !this.edcConfig?.require_reference ||
                            this.paymentMethod !== 'card' ||
                            this.edcApprovalCode.trim().length > 0;

                        return this.cart.length > 0 &&
                            this.selectedOutlet &&
                            this.paymentMethod &&
                            edcReady &&
                            (this.paymentMethod !== 'cash' || this.effectiveTendered >= this.total);
                    },

                    // -- Methods --
                    openCustomerDisplay() {
                        const url = this.customerDisplayUrl || '/admin/pos/customer-display';
                        displayWindow = window.open(
                            url,
                            'POSCustomerDisplay',
                            'width=1100,height=720,menubar=no,toolbar=no,location=no,status=no'
                        );
                        this.syncCustomerDisplay();
                        setTimeout(() => this.syncCustomerDisplay(), 400);
                        setTimeout(() => this.syncCustomerDisplay(), 1000);
                    },
                    syncCustomerDisplay(stateOverride = null) {
                        const selectedOutletObj = this.outlets.find(o => String(o.id) === String(this.selectedOutlet));
                        const payload = {
                            state: stateOverride || (this.cart.length > 0 ? 'active' : 'idle'),
                            outletName: selectedOutletObj ? selectedOutletObj.name : '',
                            customer: this.selectedCustomerObj ? {
                                name: this.selectedCustomerObj.name,
                                is_member: !!this.selectedCustomerObj.is_member,
                                membership_tier: this.selectedCustomerObj.membership_tier,
                                loyalty_points: this.selectedCustomerObj.loyalty_points
                            } : null,
                            items: this.cart.map(item => ({
                                product_id: item.product_id,
                                name: item.name,
                                price: Number(item.price || 0),
                                unit: item.unit,
                                qty: Number(item.qty || 0),
                                discount: Number(item.discount || 0)
                            })),
                            subtotal: this.cartSubtotal,
                            discount: this.orderDiscountValue,
                            taxLabel: this.taxConfig?.label || 'Tax',
                            taxAmount: this.taxAmount,
                            total: this.total,
                            pointsToEarn: this.pointsToEarn,
                            paymentMethod: this.paymentMethod,
                            amountTendered: Number(this.amountTendered || 0),
                            change: this.paymentMethod === 'cash' && this.amountTendered > 0 ?
                                Math.max(0, this.amountTendered - this.total) : 0,
                            edcWaiting: this.edcWaiting,
                            edcApproved: this.edcApproved && this.paymentMethod === 'card' && !!this.edcConfig?.enabled,
                            edcBank: this.edcBank,
                            edcReference: this.computedReferenceNumber,
                            updatedAt: Date.now()
                        };

                        try {
                            localStorage.setItem('pos_customer_display_state', JSON.stringify(payload));
                        } catch {}

                        if (displayChannel) {
                            try {
                                displayChannel.postMessage({
                                    type: 'sync_state',
                                    payload
                                });
                            } catch {}
                        }

                        if (displayWindow && !displayWindow.closed) {
                            try {
                                displayWindow.postMessage({
                                    type: 'sync_state',
                                    payload
                                }, window.location.origin);
                            } catch {}
                        }
                    },
                    onPaymentMethodChange() {
                        if (this.paymentMethod !== 'card') {
                            this.edcWaiting = false;
                        }
                        this.submitError = '';
                        this.syncCustomerDisplay();
                    },
                    async sendToEdcTerminal() {
                        if (this.total <= 0) return;
                        this.submitError = '';
                        this.edcWaiting = true;
                        this.edcApproved = false;
                        this.syncCustomerDisplay();

                        // Attempt WebSerial ECR protocol if available & user selects a port, otherwise simulate ECR response
                        if ('serial' in navigator && this.edcConfig?.connection_mode === 'ecr_serial') {
                            try {
                                const port = await navigator.serial.requestPort();
                                await port.open({
                                    baudRate: Number(this.edcConfig.serial_baud_rate || 9600)
                                });
                                const writer = port.writable.getWriter();
                                const encoder = new TextEncoder();
                                await writer.write(encoder.encode(`SALE|${Math.round(this.total)}|${this.edcBank}\r\n`));
                                writer.releaseLock();
                                await port.close();
                            } catch {
                                // Fallback to manual/simulated approval code entry if WebSerial port is cancelled or unavailable
                            }
                        }

                        setTimeout(() => {
                            this.edcWaiting = false;
                            if (!this.edcApprovalCode.trim()) {
                                const randomAppr = String(Math.floor(100000 + Math.random() * 900000));
                                this.edcApprovalCode = randomAppr;
                            }
                            this.edcApproved = true;
                            this.syncCustomerDisplay();
                        }, 1200);
                    },
                    productStock(product) {
                        if (!product || product.track_stock === false) {
                            return null;
                        }
                        if (
                            this.selectedOutlet &&
                            product.outlet_stocks &&
                            Object.prototype.hasOwnProperty.call(product.outlet_stocks, String(this.selectedOutlet))
                        ) {
                            return Number(product.outlet_stocks[String(this.selectedOutlet)]);
                        }
                        if (product.has_outlet_assignment === false) {
                            return null;
                        }
                        return Number(product.stock ?? 0);
                    },
                    isOutOfStock(product) {
                        const s = this.productStock(product);
                        return s !== null && s <= 0;
                    },
                    stockLabel(product) {
                        if (this.isOutOfStock(product)) {
                            return 'Out';
                        }
                        const s = this.productStock(product);
                        if (s === null) {
                            return 'In Stock';
                        }
                        return s + ' ' + product.unit;
                    },
                    lineTotal(item) {
                        return Math.max(0, (item.price * item.qty) - (item.discount || 0));
                    },
                    formatNumber(n) {
                        return Math.round(n).toLocaleString('id-ID');
                    },
                    addToCart(product) {
                        this.submitError = '';
                        const maxStock = this.productStock(product);
                        const existing = this.cart.findIndex(i => i.product_id === product.id);
                        if (existing >= 0) {
                            const nextQty = this.cart[existing].qty + 1;
                            if (maxStock !== null && nextQty > maxStock) {
                                this.submitError = `Insufficient stock for '${product.name}'. Available: ${maxStock}`;
                                return;
                            }
                            this.cart[existing].qty = nextQty;
                        } else {
                            if (maxStock !== null && maxStock < 1) {
                                this.submitError = `Insufficient stock for '${product.name}'. Available: ${maxStock}`;
                                return;
                            }
                            this.cart.push({
                                product_id: product.id,
                                name: product.name,
                                price: product.price,
                                unit: product.unit,
                                qty: 1,
                                discount: 0,
                            });
                        }
                        this.syncCustomerDisplay();
                    },
                    scanBarcode() {
                        const code = this.barcodeInput.trim();
                        this.barcodeInput = '';
                        if (!code) return;
                        const product = this._productIndex[code] || null;
                        if (!product) {
                            this.barcodeStatus = 'notfound';
                        } else if (this.isOutOfStock(product)) {
                            this.barcodeStatus = 'outofstock';
                        } else {
                            this.addToCart(product);
                            this.barcodeStatus = 'found';
                        }
                        setTimeout(() => {
                            this.barcodeStatus = null;
                        }, 1500);
                    },
                    async openCamera() {
                        this.cameraError = '';
                        this.cameraOpen = true;
                        await this.$nextTick();
                        try {
                            const stream = await navigator.mediaDevices.getUserMedia({
                                video: {
                                    facingMode: {
                                        ideal: 'environment'
                                    },
                                    width: {
                                        ideal: this._cameraIdealWidth
                                    }
                                }
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
                            try {
                                this._zxingReader.reset();
                            } catch {
                                /* reset() may throw if stream already closed */
                            }
                            this._zxingReader = null;
                        }
                        if (this._scannerStream) {
                            this._scannerStream.getTracks().forEach(t => t.stop());
                            this._scannerStream = null;
                        }
                        this.cameraOpen = false;
                        this.cameraError = '';
                        this.$nextTick(() => this.$refs.barcodeInput?.focus());
                    },
                    async _startScanLoop() {
                        if ('BarcodeDetector' in window) {
                            // Native BarcodeDetector (Chrome 83+, Edge 83+, Samsung Internet)
                            let supported = ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39', 'code_93',
                                'qr_code', 'data_matrix'
                            ];
                            try {
                                const detected = await BarcodeDetector.getSupportedFormats();
                                supported = supported.filter(f => detected.includes(f));
                            } catch {}
                            const detector = new BarcodeDetector({
                                formats: supported.length ? supported : ['ean_13', 'code_128']
                            });
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
                                this.cameraError =
                                    'Barcode detection not available in this browser. Please use Chrome or Edge.';
                            }
                        }
                    },
                    _loadScript(src, integrity) {
                        return new Promise((resolve, reject) => {
                            if (document.querySelector(`script[src="${src}"]`)) {
                                resolve();
                                return;
                            }
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
                        this.syncCustomerDisplay();
                    },
                    updateQty(idx, val) {
                        this.submitError = '';
                        const qty = parseFloat(val);
                        if (isNaN(qty) || qty <= 0) {
                            this.removeFromCart(idx);
                            return;
                        }
                        const item = this.cart[idx];
                        const product = this._productById[item.product_id];
                        const maxStock = product ? this.productStock(product) : null;
                        if (maxStock !== null && qty > maxStock) {
                            this.cart[idx].qty = maxStock;
                            this.submitError = `Insufficient stock for '${item.name}'. Available: ${maxStock}`;
                        } else {
                            this.cart[idx].qty = qty;
                        }
                        this.syncCustomerDisplay();
                    },
                    enforceItemQty(idx) {
                        const item = this.cart[idx];
                        if (!item) return;
                        const qty = Math.max(0.001, parseFloat(item.qty) || 0.001);
                        const product = this._productById[item.product_id];
                        const maxStock = product ? this.productStock(product) : null;
                        if (maxStock !== null && qty > maxStock) {
                            item.qty = maxStock;
                            this.submitError = `Insufficient stock for '${item.name}'. Available: ${maxStock}`;
                        } else {
                            item.qty = qty;
                        }
                        this.syncCustomerDisplay();
                    },
                    clearCart() {
                        if (confirm('Clear all items from cart?')) {
                            this.cart = [];
                            this.edcApprovalCode = '';
                            this.edcApproved = false;
                            this.edcWaiting = false;
                            this.submitError = '';
                            this.syncCustomerDisplay('idle');
                        }
                    },
                    selectCustomer(c) {
                        this.selectedCustomer = String(c.id);
                        this.selectedCustomerObj = c;
                        this.customerQuery = '';
                        this.customerDropdownOpen = false;
                        this.syncCustomerDisplay();
                    },
                    clearCustomer() {
                        this.selectedCustomer = '';
                        this.selectedCustomerObj = null;
                        this.customerQuery = '';
                        this.customerDropdownOpen = false;
                        this.syncCustomerDisplay();
                    },
                    submitOrder() {
                        this.submitError = '';
                        if (this.outlets.length === 0) {
                            this.submitError =
                                'Order failed to create: You do not have an active outlet yet. Please create or activate an outlet first.';
                            return;
                        }
                        if (!this.selectedOutlet) {
                            this.submitError = 'Order failed to create: Please select an outlet first.';
                            return;
                        }
                        if (this.cart.length === 0) {
                            this.submitError = 'Order failed to create: Cart is empty. Please add at least one product.';
                            return;
                        }
                        for (const item of this.cart) {
                            const product = this._productById[item.product_id];
                            const maxStock = product ? this.productStock(product) : null;
                            if (maxStock !== null && item.qty > maxStock) {
                                this.submitError =
                                    `Insufficient stock for '${item.name}' at the selected outlet. Available: ${maxStock}`;
                                return;
                            }
                        }
                        if (this.paymentMethod === 'cash' && this.effectiveTendered < this.total) {
                            this.submitError = 'Order failed to create: Amount tendered is less than total.';
                            return;
                        }
                        if (
                            this.paymentMethod === 'card' &&
                            this.edcConfig?.enabled &&
                            this.edcConfig?.require_reference &&
                            !this.edcApprovalCode.trim()
                        ) {
                            this.submitError =
                                'Order failed to create: Please enter the EDC Approval Code / Reference Number from the EDC terminal.';
                            return;
                        }

                        this.syncCustomerDisplay('paid');

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
