<?php

use App\Models\CashierShift;
use App\Models\Category;
use App\Models\Customer;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\SupplierPayment;
use App\Models\SupplierPriceHistory;
use App\Models\SupplierProduct;
use App\Models\SupplierReturn;
use App\Models\SupplierReturnItem;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const DEMO_TENANT_SLUG = 'kopi-nusantara-demo';

    public function up(): void
    {
        // Skip automatic demo seeding during standard unit/feature tests unless explicitly enabled,
        // so tests asserting empty/isolated tables remain unaffected.
        if (app()->runningUnitTests() && ! config('database.seed_demo_in_tests', false)) {
            return;
        }

        // Ensure stock_movements.type supports 'goods_receipt' and 'supplier_return' on MySQL databases
        // that were created when stock_movements.type was still a 5-value ENUM.
        if (DB::getDriverName() === 'mysql' && Schema::hasTable('stock_movements')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->string('type', 40)->default('adjustment')->change();
            });
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        DB::transaction(function () {
            $now = Carbon::now();

            // ── 0. Roles & Permissions ─────────────────────────────────────────
            $this->seedRolesAndPermissions();

            // ── 1. Tenant (Professional Plan - Active for Live Demo) ───────────
            $tenant = Tenant::withTrashed()->updateOrCreate(
                ['slug' => self::DEMO_TENANT_SLUG],
                [
                    'name' => 'Kopi & Roastery Nusantara',
                    'email' => 'halo@kopinusantara.id',
                    'phone' => '+62 21 5550199',
                    'contact_name' => 'Budi Santoso',
                    'business_type' => 'cafe_retail',
                    'address' => 'Jl. Senopati No. 88, Kebayoran Baru, Jakarta Selatan',
                    'plan' => 'professional',
                    'status' => 'active',
                    'trial_ends_at' => $now->copy()->subDays(10),
                    'subscription_skipped' => false,
                    'subscription_status' => 'active',
                    'subscription_ends_at' => $now->copy()->addYear(),
                    'is_subscription_exempt' => false,
                    'deleted_at' => null,
                    'settings' => [
                        'supplier_management_enabled' => true,
                        'supplier_management_override' => 'auto',
                        'billing_history' => [
                            [
                                'id' => 'INV-DEMO-202601',
                                'date' => $now->copy()->subDays(10)->toIso8601String(),
                                'plan' => 'professional',
                                'plan_name' => 'Professional',
                                'billing_cycle' => 'annual',
                                'amount' => 2870000,
                                'payment_method' => 'Bank Transfer (BCA Virtual Account)',
                                'status' => 'paid',
                                'description' => 'Langganan Tahunan Paket Professional (Multi-Outlet & ERP)',
                                'period_start' => $now->copy()->subDays(10)->format('d M Y'),
                                'period_end' => $now->copy()->addYear()->format('d M Y'),
                            ],
                        ],
                    ],
                ]
            );

            // ── 2. Step 1 (Guide): Tenant Settings, Tax & Currency ─────────────
            $settingsData = [
                'app_name' => ['value' => 'Kopi & Roastery Nusantara', 'type' => 'string', 'group' => 'general'],
                'app_email' => ['value' => 'halo@kopinusantara.id', 'type' => 'string', 'group' => 'general'],
                'app_phone' => ['value' => '+62 21 5550199', 'type' => 'string', 'group' => 'general'],
                'app_address' => ['value' => 'Jl. Senopati No. 88, Kebayoran Baru, Jakarta Selatan', 'type' => 'string', 'group' => 'general'],
                'currency' => ['value' => 'IDR', 'type' => 'string', 'group' => 'general'],
                'currency_code' => ['value' => 'IDR', 'type' => 'string', 'group' => 'currency'],
                'currency_symbol' => ['value' => 'Rp', 'type' => 'string', 'group' => 'currency'],
                'currency_position' => ['value' => 'before', 'type' => 'string', 'group' => 'currency'],
                'currency_decimals' => ['value' => '0', 'type' => 'integer', 'group' => 'currency'],
                'tax_rate' => ['value' => '11', 'type' => 'integer', 'group' => 'tax'],
                'tax_label' => ['value' => 'PPN (11%)', 'type' => 'string', 'group' => 'tax'],
                'tax_enabled' => ['value' => '1', 'type' => 'boolean', 'group' => 'tax'],
                'tax_inclusion' => ['value' => 'exclusive', 'type' => 'string', 'group' => 'tax'],
                'receipt_footer' => ['value' => 'Terima kasih telah berbelanja di Kopi Nusantara! IG: @kopinusantara.id', 'type' => 'string', 'group' => 'general'],
                'timezone' => ['value' => 'Asia/Jakarta', 'type' => 'string', 'group' => 'general'],
                'date_format' => ['value' => 'd/m/Y', 'type' => 'string', 'group' => 'general'],
                'low_stock_alert' => ['value' => 'true', 'type' => 'boolean', 'group' => 'general'],
                'supplier_management_enabled' => ['value' => 'true', 'type' => 'boolean', 'group' => 'modules'],
                'po_approval_threshold' => ['value' => '5000000', 'type' => 'float', 'group' => 'modules'],
            ];

            foreach ($settingsData as $key => $cfg) {
                Setting::withoutGlobalScopes()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'key' => $key],
                    [
                        'value' => $cfg['value'],
                        'type' => $cfg['type'],
                        'is_public' => false,
                        'group' => $cfg['group'],
                    ]
                );
            }

            // ── 3. Step 2 (Guide): Outlets / Multi-Branch ──────────────────────
            $outletPusat = Outlet::withoutGlobalScopes()->withTrashed()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'JKT-SNP'],
                [
                    'name' => 'Outlet Pusat Senopati',
                    'phone' => '+62 21 5550199',
                    'email' => 'senopati@kopinusantara.id',
                    'address' => 'Jl. Senopati No. 88, Kebayoran Baru',
                    'city' => 'Jakarta Selatan',
                    'is_active' => true,
                    'deleted_at' => null,
                ]
            );

            $outletKemang = Outlet::withoutGlobalScopes()->withTrashed()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'JKT-KMG'],
                [
                    'name' => 'Cabang Kemang Village',
                    'phone' => '+62 21 5550288',
                    'email' => 'kemang@kopinusantara.id',
                    'address' => 'Jl. Pangeran Antasari No. 36, Kemang',
                    'city' => 'Jakarta Selatan',
                    'is_active' => true,
                    'deleted_at' => null,
                ]
            );

            $outletBandung = Outlet::withoutGlobalScopes()->withTrashed()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'BDG-DGO'],
                [
                    'name' => 'Cabang Dago Bandung',
                    'phone' => '+62 22 4200177',
                    'email' => 'dago@kopinusantara.id',
                    'address' => 'Jl. Ir. H. Juanda No. 112, Coblong',
                    'city' => 'Bandung',
                    'is_active' => true,
                    'deleted_at' => null,
                ]
            );

            // ── 4. Step 6 (Guide): Users (Owner, Manager, Cashiers with PIN) ───
            $hashedPassword = Hash::make('password');
            $hashedPin = Hash::make('123456');

            $owner = User::withTrashed()->updateOrCreate(
                ['email' => 'owner@kopinusantara.id'],
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Budi Santoso (Owner)',
                    'password' => $hashedPassword,
                    'pin' => $hashedPin,
                    'phone' => '08119001101',
                    'email_verified_at' => $now,
                    'is_active' => true,
                    'deleted_at' => null,
                ]
            );
            $owner->syncRoles(['tenant_admin']);
            $owner->outlets()->sync([
                $outletPusat->id => ['is_default' => true],
                $outletKemang->id => ['is_default' => false],
                $outletBandung->id => ['is_default' => false],
            ]);

            $manager = User::withTrashed()->updateOrCreate(
                ['email' => 'manager@kopinusantara.id'],
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Andi Pratama (Store Manager)',
                    'password' => $hashedPassword,
                    'pin' => $hashedPin,
                    'phone' => '08119001102',
                    'email_verified_at' => $now,
                    'is_active' => true,
                    'deleted_at' => null,
                ]
            );
            $manager->syncRoles(['manager']);
            $manager->outlets()->sync([
                $outletPusat->id => ['is_default' => true],
                $outletKemang->id => ['is_default' => false],
                $outletBandung->id => ['is_default' => false],
            ]);

            $cashier1 = User::withTrashed()->updateOrCreate(
                ['email' => 'kasir.senopati@kopinusantara.id'],
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Siti Rahma (Kasir Senopati)',
                    'password' => $hashedPassword,
                    'pin' => $hashedPin,
                    'phone' => '08119001103',
                    'email_verified_at' => $now,
                    'is_active' => true,
                    'deleted_at' => null,
                ]
            );
            $cashier1->syncRoles(['cashier']);
            $cashier1->outlets()->sync([
                $outletPusat->id => ['is_default' => true],
            ]);

            $cashier2 = User::withTrashed()->updateOrCreate(
                ['email' => 'kasir.kemang@kopinusantara.id'],
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Rizky Saputra (Kasir Kemang)',
                    'password' => $hashedPassword,
                    'pin' => $hashedPin,
                    'phone' => '08119001104',
                    'email_verified_at' => $now,
                    'is_active' => true,
                    'deleted_at' => null,
                ]
            );
            $cashier2->syncRoles(['cashier']);
            $cashier2->outlets()->sync([
                $outletKemang->id => ['is_default' => true],
                $outletBandung->id => ['is_default' => false],
            ]);

            // ── 5. Step 3 (Guide): Product Categories ──────────────────────────
            $categoriesConfig = [
                ['name' => 'Minuman Kopi', 'slug' => 'minuman-kopi', 'sort_order' => 1],
                ['name' => 'Minuman Non-Kopi', 'slug' => 'minuman-non-kopi', 'sort_order' => 2],
                ['name' => 'Makanan Utama', 'slug' => 'makanan-utama', 'sort_order' => 3],
                ['name' => 'Pastry & Snack', 'slug' => 'pastry-snack', 'sort_order' => 4],
                ['name' => 'Biji Kopi & Retail', 'slug' => 'biji-kopi-retail', 'sort_order' => 5],
                ['name' => 'Merchandise & Aksesoris', 'slug' => 'merchandise-aksesoris', 'sort_order' => 6],
            ];

            $categories = [];
            foreach ($categoriesConfig as $catData) {
                $categories[$catData['slug']] = Category::withoutGlobalScopes()->withTrashed()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'slug' => $catData['slug']],
                    [
                        'name' => $catData['name'],
                        'is_active' => true,
                        'sort_order' => $catData['sort_order'],
                        'deleted_at' => null,
                    ]
                );
            }

            // ── 6. Step 4 & 5 (Guide): Products & Multi-Outlet Inventory ───────
            $productsConfig = [
                // Minuman Kopi
                [
                    'sku' => 'KPN-COF-001',
                    'barcode' => '8992026001011',
                    'category' => 'minuman-kopi',
                    'name' => 'Es Kopi Susu Gula Aren Nusantara',
                    'description' => 'Signature espresso house blend dengan susu segar dan gula aren organik.',
                    'price' => 28000,
                    'cost_price' => 11500,
                    'unit' => 'cup',
                    'stock' => [$outletPusat->id => [120, 25], $outletKemang->id => [85, 20], $outletBandung->id => [70, 20]],
                ],
                [
                    'sku' => 'KPN-COF-002',
                    'barcode' => '8992026001028',
                    'category' => 'minuman-kopi',
                    'name' => 'Hot Caffe Latte Arabica',
                    'description' => 'Double shot Arabica Toraja dengan steamed silky milk.',
                    'price' => 34000,
                    'cost_price' => 13500,
                    'unit' => 'cup',
                    'stock' => [$outletPusat->id => [95, 20], $outletKemang->id => [60, 15], $outletBandung->id => [55, 15]],
                ],
                [
                    'sku' => 'KPN-COF-003',
                    'barcode' => '8992026001035',
                    'category' => 'minuman-kopi',
                    'name' => 'Iced Americano Single Origin',
                    'description' => 'Black coffee segar dari biji kopi single origin pilihan.',
                    'price' => 26000,
                    'cost_price' => 9500,
                    'unit' => 'cup',
                    'stock' => [$outletPusat->id => [110, 20], $outletKemang->id => [75, 15], $outletBandung->id => [65, 15]],
                ],
                [
                    'sku' => 'KPN-COF-004',
                    'barcode' => '8992026001042',
                    'category' => 'minuman-kopi',
                    'name' => 'Caramel Macchiato Iced',
                    'description' => 'Paduan espresso, vanilla syrup, susu segar, dan saus karamel premium.',
                    'price' => 38000,
                    'cost_price' => 15500,
                    'unit' => 'cup',
                    'stock' => [$outletPusat->id => [80, 15], $outletKemang->id => [50, 15], $outletBandung->id => [45, 15]],
                ],
                // Minuman Non-Kopi
                [
                    'sku' => 'KPN-NCF-001',
                    'barcode' => '8992026002018',
                    'category' => 'minuman-non-kopi',
                    'name' => 'Uji Matcha Latte Iced',
                    'description' => 'Pure ceremonial grade Uji Matcha Jepang dengan fresh milk.',
                    'price' => 36000,
                    'cost_price' => 15000,
                    'unit' => 'cup',
                    'stock' => [$outletPusat->id => [75, 15], $outletKemang->id => [8, 15], $outletBandung->id => [40, 15]], // Low stock at Kemang
                ],
                [
                    'sku' => 'KPN-NCF-002',
                    'barcode' => '8992026002025',
                    'category' => 'minuman-non-kopi',
                    'name' => 'Belgian Signature Chocolate',
                    'description' => 'Minuman cokelat Belgia pekat disajikan dingin atau hangat.',
                    'price' => 35000,
                    'cost_price' => 14000,
                    'unit' => 'cup',
                    'stock' => [$outletPusat->id => [65, 15], $outletKemang->id => [45, 15], $outletBandung->id => [50, 15]],
                ],
                [
                    'sku' => 'KPN-NCF-003',
                    'barcode' => '8992026002032',
                    'category' => 'minuman-non-kopi',
                    'name' => 'Lychee Yakult Tea Refresher',
                    'description' => 'Teh melati segar dengan buah leci asli dan probiotik.',
                    'price' => 30000,
                    'cost_price' => 11000,
                    'unit' => 'cup',
                    'stock' => [$outletPusat->id => [90, 15], $outletKemang->id => [55, 15], $outletBandung->id => [50, 15]],
                ],
                // Makanan Utama
                [
                    'sku' => 'KPN-FOD-001',
                    'barcode' => '8992026003015',
                    'category' => 'makanan-utama',
                    'name' => 'Nasi Goreng Kampung Sei Sapi',
                    'description' => 'Nasi goreng rempah nusantara dengan irisan sei sapi asap dan telur mata sapi.',
                    'price' => 52000,
                    'cost_price' => 24000,
                    'unit' => 'porsi',
                    'stock' => [$outletPusat->id => [45, 10], $outletKemang->id => [30, 10], $outletBandung->id => [35, 10]],
                ],
                [
                    'sku' => 'KPN-FOD-002',
                    'barcode' => '8992026003022',
                    'category' => 'makanan-utama',
                    'name' => 'Spaghetti Aglio Olio Smoked Duck',
                    'description' => 'Pasta al dente dengan bawang putih, olive oil, chili flakes, dan bebek asap.',
                    'price' => 58000,
                    'cost_price' => 26500,
                    'unit' => 'porsi',
                    'stock' => [$outletPusat->id => [40, 10], $outletKemang->id => [25, 10], $outletBandung->id => [25, 10]],
                ],
                [
                    'sku' => 'KPN-FOD-003',
                    'barcode' => '8992026003039',
                    'category' => 'makanan-utama',
                    'name' => 'Chicken Katsu Curry Rice Bowl',
                    'description' => 'Nasi hangat dengan ayam katsu renyah dan saus kari Jepang gurih.',
                    'price' => 48000,
                    'cost_price' => 21000,
                    'unit' => 'porsi',
                    'stock' => [$outletPusat->id => [50, 10], $outletKemang->id => [35, 10], $outletBandung->id => [30, 10]],
                ],
                // Pastry & Snack
                [
                    'sku' => 'KPN-SNK-001',
                    'barcode' => '8992026004012',
                    'category' => 'pastry-snack',
                    'name' => 'French Butter Croissant',
                    'description' => 'Croissant renyah berlapis dengan French Elle & Vire butter.',
                    'price' => 28000,
                    'cost_price' => 12500,
                    'unit' => 'pcs',
                    'stock' => [$outletPusat->id => [6, 12], $outletKemang->id => [22, 10], $outletBandung->id => [18, 10]], // Low stock at Pusat
                ],
                [
                    'sku' => 'KPN-SNK-002',
                    'barcode' => '8992026004029',
                    'category' => 'pastry-snack',
                    'name' => 'Almond Kouign-Amann Pastry',
                    'description' => 'Pastry karamel renyah dengan taburan sliced almond panggang.',
                    'price' => 34000,
                    'cost_price' => 15000,
                    'unit' => 'pcs',
                    'stock' => [$outletPusat->id => [32, 10], $outletKemang->id => [20, 10], $outletBandung->id => [20, 10]],
                ],
                [
                    'sku' => 'KPN-SNK-003',
                    'barcode' => '8992026004036',
                    'category' => 'pastry-snack',
                    'name' => 'Truffle Parmesan French Fries',
                    'description' => 'Kentang goreng crispy dengan minyak truffle dan keju parmesan parut.',
                    'price' => 36000,
                    'cost_price' => 14500,
                    'unit' => 'porsi',
                    'stock' => [$outletPusat->id => [55, 10], $outletKemang->id => [40, 10], $outletBandung->id => [35, 10]],
                ],
                // Biji Kopi & Retail
                [
                    'sku' => 'KPN-RTL-001',
                    'barcode' => '8992026005019',
                    'category' => 'biji-kopi-retail',
                    'name' => 'Biji Kopi Arabica Toraja Sapan 250g',
                    'description' => 'Whole bean roast profile medium, notes dark chocolate, citrus, dan brown sugar.',
                    'price' => 115000,
                    'cost_price' => 68000,
                    'unit' => 'pouch',
                    'stock' => [$outletPusat->id => [38, 10], $outletKemang->id => [24, 8], $outletBandung->id => [20, 8]],
                ],
                [
                    'sku' => 'KPN-RTL-002',
                    'barcode' => '8992026005026',
                    'category' => 'biji-kopi-retail',
                    'name' => 'Biji Kopi Aceh Gayo Wine Process 250g',
                    'description' => 'Specialty single origin Aceh Gayo dengan aroma fruity wine yang khas.',
                    'price' => 135000,
                    'cost_price' => 82000,
                    'unit' => 'pouch',
                    'stock' => [$outletPusat->id => [4, 8], $outletKemang->id => [14, 6], $outletBandung->id => [12, 6]], // Low stock at Pusat
                ],
                [
                    'sku' => 'KPN-RTL-003',
                    'barcode' => '8992026005033',
                    'category' => 'biji-kopi-retail',
                    'name' => 'Drip Bag Coffee Nusantara Box (Isi 5)',
                    'description' => 'Kopi seduh praktis isi 5 sachet single origin Indonesia.',
                    'price' => 65000,
                    'cost_price' => 35000,
                    'unit' => 'box',
                    'stock' => [$outletPusat->id => [42, 10], $outletKemang->id => [25, 8], $outletBandung->id => [28, 8]],
                ],
                // Merchandise & Aksesoris
                [
                    'sku' => 'KPN-MRC-001',
                    'barcode' => '8992026006016',
                    'category' => 'merchandise-aksesoris',
                    'name' => 'Tumbler Stainless Kopi Nusantara 500ml',
                    'description' => 'Vacuum insulated tumbler tahan panas/dingin hingga 12 jam.',
                    'price' => 185000,
                    'cost_price' => 95000,
                    'unit' => 'pcs',
                    'stock' => [$outletPusat->id => [20, 5], $outletKemang->id => [12, 5], $outletBandung->id => [0, 5]], // Out of stock at Bandung
                ],
                [
                    'sku' => 'KPN-MRC-002',
                    'barcode' => '8992026006023',
                    'category' => 'merchandise-aksesoris',
                    'name' => 'Ceramic Pour Over V60 Dripper Set',
                    'description' => 'Alat seduh manual V60 keramik ukuran 02 lengkap dengan sendok takar (Slow Mover Demo).',
                    'price' => 225000,
                    'cost_price' => 125000,
                    'unit' => 'pcs',
                    'stock' => [$outletPusat->id => [15, 3], $outletKemang->id => [8, 3], $outletBandung->id => [6, 3]],
                ],
            ];

            $products = [];
            foreach ($productsConfig as $item) {
                $product = Product::withoutGlobalScopes()->withTrashed()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'sku' => $item['sku']],
                    [
                        'category_id' => $categories[$item['category']]->id,
                        'name' => $item['name'],
                        'barcode' => $item['barcode'],
                        'description' => $item['description'],
                        'price' => $item['price'],
                        'cost_price' => $item['cost_price'],
                        'unit' => $item['unit'],
                        'is_active' => true,
                        'track_stock' => true,
                        'deleted_at' => null,
                    ]
                );

                $syncOutlets = [];
                foreach ($item['stock'] as $outletId => [$stockQty, $threshold]) {
                    $syncOutlets[$outletId] = [
                        'stock' => $stockQty,
                        'low_stock_threshold' => $threshold,
                    ];
                }
                $product->outlets()->sync($syncOutlets);
                $products[$item['sku']] = $product;
            }

            // ── 7. Step 6 (Guide): Customers & Membership Tiers ────────────────
            $customersConfig = [
                [
                    'customer_code' => 'MBR-KPN-001',
                    'name' => 'putri.ayu@gmail.com',
                    'display_name' => 'Putri Ayu Lestari',
                    'phone' => '081288001101',
                    'address' => 'Jl. Gunawarman No. 15, Jakarta Selatan',
                    'gender' => 'female',
                    'date_of_birth' => '1994-05-14',
                    'loyalty_points' => 1450,
                    'member_since' => $now->copy()->subMonths(8)->toDateString(),
                    'membership_tier' => Customer::TIER_PLATINUM,
                    'notes' => 'Pelanggan VIP, favorit Es Kopi Susu Gula Aren less sugar & Biji Kopi Toraja.',
                    'created_at' => $now->copy()->subMonths(8),
                ],
                [
                    'customer_code' => 'MBR-KPN-002',
                    'name' => 'hendra.wijaya@yahoo.com',
                    'display_name' => 'Hendra Wijaya',
                    'phone' => '081288001102',
                    'address' => 'Apartemen District 8 Senopati Tower Eternity, Jakarta Selatan',
                    'gender' => 'male',
                    'date_of_birth' => '1988-11-22',
                    'loyalty_points' => 890,
                    'member_since' => $now->copy()->subMonths(6)->toDateString(),
                    'membership_tier' => Customer::TIER_GOLD,
                    'notes' => 'Sering memesan paket meeting pagi dan croissant.',
                    'created_at' => $now->copy()->subMonths(6),
                ],
                [
                    'customer_code' => 'MBR-KPN-003',
                    'name' => 'nadia.kusuma@gmail.com',
                    'display_name' => 'Nadia Kusuma Wardhani',
                    'phone' => '081288001103',
                    'address' => 'Jl. Bangka Raya No. 40, Kemang, Jakarta Selatan',
                    'gender' => 'female',
                    'date_of_birth' => '1997-02-09',
                    'loyalty_points' => 620,
                    'member_since' => $now->copy()->subMonths(4)->toDateString(),
                    'membership_tier' => Customer::TIER_GOLD,
                    'notes' => 'Pecinta Uji Matcha Latte oat milk.',
                    'created_at' => $now->copy()->subMonths(4),
                ],
                [
                    'customer_code' => 'MBR-KPN-004',
                    'name' => 'dimas.prakoso@gmail.com',
                    'display_name' => 'Dimas Prakoso',
                    'phone' => '081288001104',
                    'address' => 'Jl. Cipete Raya No. 19, Jakarta Selatan',
                    'gender' => 'male',
                    'date_of_birth' => '1995-08-30',
                    'loyalty_points' => 310,
                    'member_since' => $now->copy()->subMonths(3)->toDateString(),
                    'membership_tier' => Customer::TIER_SILVER,
                    'notes' => 'Langganan kerja remote (WFC) di cabang Kemang.',
                    'created_at' => $now->copy()->subMonths(3),
                ],
                [
                    'customer_code' => 'MBR-KPN-005',
                    'name' => 'clara.devina@gmail.com',
                    'display_name' => 'Clara Devina',
                    'phone' => '081288001105',
                    'address' => 'Jl. Dipatiukur No. 72, Bandung',
                    'gender' => 'female',
                    'date_of_birth' => '1999-12-03',
                    'loyalty_points' => 240,
                    'member_since' => $now->copy()->subMonths(2)->toDateString(),
                    'membership_tier' => Customer::TIER_SILVER,
                    'notes' => 'Member aktif Cabang Dago Bandung.',
                    'created_at' => $now->copy()->subMonths(2),
                ],
                [
                    'customer_code' => 'MBR-KPN-006',
                    'name' => 'fajar.nugroho@gmail.com',
                    'display_name' => 'Fajar Nugroho',
                    'phone' => '081288001106',
                    'address' => 'Jl. Wolter Monginsidi No. 12, Jakarta Selatan',
                    'gender' => 'male',
                    'date_of_birth' => '1992-04-18',
                    'loyalty_points' => 115,
                    'member_since' => $now->copy()->subDays(12)->toDateString(),
                    'membership_tier' => Customer::TIER_REGULAR,
                    'notes' => 'Member baru bulan ini.',
                    'created_at' => $now->copy()->subDays(12),
                ],
                [
                    'customer_code' => 'MBR-KPN-007',
                    'name' => 'kevin.halim@gmail.com',
                    'display_name' => 'Kevin Halim',
                    'phone' => '081288001107',
                    'address' => 'Jl. Suryo No. 25, Kebayoran Baru, Jakarta Selatan',
                    'gender' => 'male',
                    'date_of_birth' => '1991-07-25',
                    'loyalty_points' => 95,
                    'member_since' => $now->copy()->subDays(2)->toDateString(),
                    'membership_tier' => Customer::TIER_REGULAR,
                    'notes' => 'Baru mendaftar member minggu ini.',
                    'created_at' => $now->copy()->subDays(2),
                ],
            ];

            $customers = [];
            foreach ($customersConfig as $cData) {
                $customer = Customer::withoutGlobalScopes()->withTrashed()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'customer_code' => $cData['customer_code']],
                    [
                        'name' => $cData['display_name'],
                        'email' => $cData['name'],
                        'phone' => $cData['phone'],
                        'address' => $cData['address'],
                        'gender' => $cData['gender'],
                        'date_of_birth' => $cData['date_of_birth'],
                        'notes' => $cData['notes'],
                        'loyalty_points' => $cData['loyalty_points'],
                        'is_active' => true,
                        'member_since' => $cData['member_since'],
                        'membership_tier' => $cData['membership_tier'],
                        'deleted_at' => null,
                    ]
                );
                DB::table('customers')->where('id', $customer->id)->update([
                    'created_at' => $cData['created_at'],
                    'updated_at' => $now,
                ]);
                $customers[$cData['customer_code']] = $customer;
            }

            // ── 8. Suppliers, Catalog, POs, Goods Receipts & Payables ──────────
            $supKopindo = Supplier::withoutGlobalScopes()->withTrashed()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'SUP-KPN-001'],
                [
                    'name' => 'PT Toraja Arabica Nusantara',
                    'company_name' => 'PT Toraja Arabica Nusantara',
                    'contact_person' => 'Bpk. Yohanis Rante',
                    'phone' => '08114200911',
                    'email' => 'sales@torajaarabica.co.id',
                    'address' => 'Kawasan Industri Makassar Blok B2 No. 14',
                    'city' => 'Makassar',
                    'npwp' => '01.345.678.9-801.000',
                    'tax_info' => 'PKP - PPN 11%',
                    'bank_name' => 'Bank Central Asia (BCA)',
                    'bank_account_number' => '7890123456',
                    'bank_account_holder' => 'PT Toraja Arabica Nusantara',
                    'default_payment_terms' => 30,
                    'is_active' => true,
                    'notes' => 'Pemasok utama biji kopi specialty Arabica Toraja & Aceh Gayo.',
                    'deleted_at' => null,
                ]
            );

            $supDairy = Supplier::withoutGlobalScopes()->withTrashed()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'SUP-KPN-002'],
                [
                    'name' => 'CV Dairy & Barista Supplyindo',
                    'company_name' => 'CV Dairy & Barista Supplyindo',
                    'contact_person' => 'Ibu Melisa Tan',
                    'phone' => '021-88991122',
                    'email' => 'order@baristasupply.id',
                    'address' => 'Jl. Raya Perjuangan No. 45, Kebon Jeruk',
                    'city' => 'Jakarta Barat',
                    'npwp' => '02.456.789.0-035.000',
                    'tax_info' => 'PKP - PPN 11%',
                    'bank_name' => 'Bank Mandiri',
                    'bank_account_number' => '1180098765432',
                    'bank_account_holder' => 'CV Dairy Barista Supplyindo',
                    'default_payment_terms' => 14,
                    'is_active' => true,
                    'notes' => 'Pemasok susu segar, bubuk Uji Matcha, cokelat Belgia, dan sirup.',
                    'deleted_at' => null,
                ]
            );

            $supBakery = Supplier::withoutGlobalScopes()->withTrashed()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'SUP-KPN-003'],
                [
                    'name' => 'PT Artisan Bakery & Merchandise',
                    'company_name' => 'PT Artisan Bakery & Merchandise',
                    'contact_person' => 'Chef Armand Halim',
                    'phone' => '021-7220988',
                    'email' => 'b2b@artisanbakery.id',
                    'address' => 'Jl. Ciputat Raya No. 89, Pondok Pinang',
                    'city' => 'Jakarta Selatan',
                    'npwp' => '03.567.890.1-019.000',
                    'tax_info' => 'Non-PKP',
                    'bank_name' => 'Bank Negara Indonesia (BNI)',
                    'bank_account_number' => '0456789123',
                    'bank_account_holder' => 'PT Artisan Bakery & Merchandise',
                    'default_payment_terms' => 7,
                    'is_active' => true,
                    'notes' => 'Pemasok croissant harian dan merchandise tumbler custom.',
                    'deleted_at' => null,
                ]
            );

            // Supplier Product Mappings & Price Histories
            $supplierMappings = [
                [$supKopindo, $products['KPN-RTL-001'], 'TRJ-250G', 68000, 65000, 'pouch', 10, 3],
                [$supKopindo, $products['KPN-RTL-002'], 'GYO-250G', 82000, 79000, 'pouch', 10, 3],
                [$supKopindo, $products['KPN-RTL-003'], 'DRP-BOX5', 35000, 35000, 'box', 15, 2],
                [$supDairy, $products['KPN-NCF-001'], 'MTC-UJI', 15000, 14000, 'cup', 20, 1],
                [$supDairy, $products['KPN-NCF-002'], 'CHC-BLG', 14000, 14000, 'cup', 20, 1],
                [$supBakery, $products['KPN-SNK-001'], 'CRS-BTR', 12500, 12000, 'pcs', 20, 1],
                [$supBakery, $products['KPN-MRC-001'], 'TMB-500', 95000, 95000, 'pcs', 10, 5],
            ];

            foreach ($supplierMappings as [$sup, $prod, $supSku, $newPrice, $oldPrice, $unit, $minQty, $leadDays]) {
                SupplierProduct::withoutGlobalScopes()->updateOrCreate(
                    ['supplier_id' => $sup->id, 'product_id' => $prod->id],
                    [
                        'tenant_id' => $tenant->id,
                        'supplier_sku' => $supSku,
                        'purchase_price' => $newPrice,
                        'is_primary' => true,
                        'min_order_qty' => $minQty,
                        'lead_time_days' => $leadDays,
                        'purchase_unit' => $unit,
                        'conversion_factor' => 1,
                        'notes' => 'Katalog kontrak harga resmi pemasok',
                    ]
                );

                SupplierPriceHistory::withoutGlobalScopes()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'supplier_id' => $sup->id,
                        'product_id' => $prod->id,
                        'new_price' => $newPrice,
                    ],
                    [
                        'old_price' => $oldPrice,
                        'purchase_unit' => $unit,
                        'source' => 'purchase_order',
                        'changed_by' => $owner->id,
                        'notes' => 'Update harga kontrak kuartal berjalan',
                        'recorded_at' => $now->copy()->subDays(15),
                    ]
                );
            }

            // PO 1: Fully Received & Fully Paid
            $po1 = PurchaseOrder::withoutGlobalScopes()->withTrashed()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'po_number' => 'PO-KPN-2026-001'],
                [
                    'outlet_id' => $outletPusat->id,
                    'supplier_id' => $supKopindo->id,
                    'order_date' => $now->copy()->subDays(18)->toDateString(),
                    'expected_date' => $now->copy()->subDays(15)->toDateString(),
                    'status' => PurchaseOrder::STATUS_RECEIVED,
                    'payment_terms' => 30,
                    'subtotal' => 2710000,
                    'discount_amount' => 0,
                    'tax_rate' => 11,
                    'tax_amount' => 298100,
                    'shipping_cost' => 50000,
                    'total_amount' => 3058100,
                    'notes' => 'Restock bulanan Biji Kopi Arabica Toraja & Drip Bag untuk Outlet Pusat Senopati.',
                    'created_by' => $manager->id,
                    'approved_by' => $owner->id,
                    'approved_at' => $now->copy()->subDays(18),
                    'ordered_at' => $now->copy()->subDays(17),
                    'deleted_at' => null,
                ]
            );

            $po1Item1 = PurchaseOrderItem::updateOrCreate(
                ['purchase_order_id' => $po1->id, 'product_id' => $products['KPN-RTL-001']->id],
                [
                    'product_name' => $products['KPN-RTL-001']->name,
                    'product_sku' => $products['KPN-RTL-001']->sku,
                    'purchase_unit' => 'pouch',
                    'conversion_factor' => 1,
                    'quantity_ordered' => 25,
                    'quantity_received' => 25,
                    'unit_price' => 68000,
                    'discount_amount' => 0,
                    'tax_amount' => 187000,
                    'subtotal' => 1700000,
                ]
            );

            $po1Item2 = PurchaseOrderItem::updateOrCreate(
                ['purchase_order_id' => $po1->id, 'product_id' => $products['KPN-RTL-003']->id],
                [
                    'product_name' => $products['KPN-RTL-003']->name,
                    'product_sku' => $products['KPN-RTL-003']->sku,
                    'purchase_unit' => 'box',
                    'conversion_factor' => 1,
                    'quantity_ordered' => 20,
                    'quantity_received' => 20,
                    'unit_price' => 50500,
                    'discount_amount' => 0,
                    'tax_amount' => 111100,
                    'subtotal' => 1010000,
                ]
            );

            $gr1 = GoodsReceipt::withoutGlobalScopes()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'gr_number' => 'GR-KPN-2026-001'],
                [
                    'purchase_order_id' => $po1->id,
                    'outlet_id' => $outletPusat->id,
                    'supplier_id' => $supKopindo->id,
                    'received_date' => $now->copy()->subDays(15)->toDateString(),
                    'supplier_delivery_note' => 'SJ-TRJ-88210',
                    'status' => 'completed',
                    'is_late' => false,
                    'notes' => 'Seluruh barang diterima lengkap dengan kondisi segel baik.',
                    'received_by' => $manager->id,
                ]
            );

            GoodsReceiptItem::updateOrCreate(
                ['goods_receipt_id' => $gr1->id, 'product_id' => $products['KPN-RTL-001']->id],
                [
                    'purchase_order_item_id' => $po1Item1->id,
                    'quantity_ordered' => 25,
                    'quantity_received' => 25,
                    'quantity_damaged' => 0,
                    'conversion_factor' => 1,
                    'stock_added' => 25,
                    'unit_cost' => 68000,
                    'batch_number' => 'BATCH-TRJ-1026',
                    'expiry_date' => $now->copy()->addMonths(10)->toDateString(),
                ]
            );

            GoodsReceiptItem::updateOrCreate(
                ['goods_receipt_id' => $gr1->id, 'product_id' => $products['KPN-RTL-003']->id],
                [
                    'purchase_order_item_id' => $po1Item2->id,
                    'quantity_ordered' => 20,
                    'quantity_received' => 20,
                    'quantity_damaged' => 0,
                    'conversion_factor' => 1,
                    'stock_added' => 20,
                    'unit_cost' => 50500,
                    'batch_number' => 'BATCH-DRP-1026',
                    'expiry_date' => $now->copy()->addMonths(12)->toDateString(),
                ]
            );

            $inv1 = SupplierInvoice::withoutGlobalScopes()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'invoice_number' => 'BINV-KPN-2026-001'],
                [
                    'supplier_id' => $supKopindo->id,
                    'purchase_order_id' => $po1->id,
                    'goods_receipt_id' => $gr1->id,
                    'supplier_invoice_number' => 'INV/TRJ/2026/1088',
                    'invoice_date' => $now->copy()->subDays(15)->toDateString(),
                    'due_date' => $now->copy()->addDays(15)->toDateString(),
                    'payment_terms' => 30,
                    'subtotal' => 2710000,
                    'tax_amount' => 298100,
                    'discount_amount' => 0,
                    'shipping_cost' => 50000,
                    'total_amount' => 3058100,
                    'amount_paid' => 3058100,
                    'return_deduction' => 0,
                    'outstanding_balance' => 0,
                    'matching_status' => 'matched',
                    'status' => SupplierInvoice::STATUS_PAID,
                    'notes' => 'Three-way matching PO, GR, dan Faktur Pajak sesuai.',
                    'created_by' => $owner->id,
                ]
            );

            SupplierPayment::withoutGlobalScopes()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'payment_number' => 'SPAY-KPN-2026-001'],
                [
                    'supplier_id' => $supKopindo->id,
                    'supplier_invoice_id' => $inv1->id,
                    'payment_date' => $now->copy()->subDays(10)->toDateString(),
                    'amount' => 3058100,
                    'payment_method' => 'bank_transfer',
                    'reference_number' => 'BCA-TRF-99201822',
                    'notes' => 'Pelunasan penuh via KlikBCA Bisnis.',
                    'created_by' => $owner->id,
                ]
            );

            // PO 2: Received with Partially Paid Invoice + Supplier Return (Showcases Payables & Returns)
            $po2 = PurchaseOrder::withoutGlobalScopes()->withTrashed()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'po_number' => 'PO-KPN-2026-002'],
                [
                    'outlet_id' => $outletPusat->id,
                    'supplier_id' => $supBakery->id,
                    'order_date' => $now->copy()->subDays(8)->toDateString(),
                    'expected_date' => $now->copy()->subDays(6)->toDateString(),
                    'status' => PurchaseOrder::STATUS_RECEIVED,
                    'payment_terms' => 7,
                    'subtotal' => 2400000,
                    'discount_amount' => 0,
                    'tax_rate' => 0,
                    'tax_amount' => 0,
                    'shipping_cost' => 0,
                    'total_amount' => 2400000,
                    'notes' => 'Pengadaan Tumbler Merchandise 20 pcs dan Croissant 40 pcs.',
                    'created_by' => $manager->id,
                    'approved_by' => $owner->id,
                    'approved_at' => $now->copy()->subDays(8),
                    'ordered_at' => $now->copy()->subDays(8),
                    'deleted_at' => null,
                ]
            );

            $po2Item1 = PurchaseOrderItem::updateOrCreate(
                ['purchase_order_id' => $po2->id, 'product_id' => $products['KPN-MRC-001']->id],
                [
                    'product_name' => $products['KPN-MRC-001']->name,
                    'product_sku' => $products['KPN-MRC-001']->sku,
                    'purchase_unit' => 'pcs',
                    'conversion_factor' => 1,
                    'quantity_ordered' => 20,
                    'quantity_received' => 20,
                    'unit_price' => 95000,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'subtotal' => 1900000,
                ]
            );

            $po2Item2 = PurchaseOrderItem::updateOrCreate(
                ['purchase_order_id' => $po2->id, 'product_id' => $products['KPN-SNK-001']->id],
                [
                    'product_name' => $products['KPN-SNK-001']->name,
                    'product_sku' => $products['KPN-SNK-001']->sku,
                    'purchase_unit' => 'pcs',
                    'conversion_factor' => 1,
                    'quantity_ordered' => 40,
                    'quantity_received' => 40,
                    'unit_price' => 12500,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'subtotal' => 500000,
                ]
            );

            $gr2 = GoodsReceipt::withoutGlobalScopes()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'gr_number' => 'GR-KPN-2026-002'],
                [
                    'purchase_order_id' => $po2->id,
                    'outlet_id' => $outletPusat->id,
                    'supplier_id' => $supBakery->id,
                    'received_date' => $now->copy()->subDays(6)->toDateString(),
                    'supplier_delivery_note' => 'SJ-ABM-4412',
                    'status' => 'completed',
                    'is_late' => false,
                    'notes' => 'Diterima di gudang Senopati. 2 tumbler ditemukan penyok saat unboxing.',
                    'received_by' => $manager->id,
                ]
            );

            GoodsReceiptItem::updateOrCreate(
                ['goods_receipt_id' => $gr2->id, 'product_id' => $products['KPN-MRC-001']->id],
                [
                    'purchase_order_item_id' => $po2Item1->id,
                    'quantity_ordered' => 20,
                    'quantity_received' => 20,
                    'quantity_damaged' => 2,
                    'conversion_factor' => 1,
                    'stock_added' => 20,
                    'unit_cost' => 95000,
                    'batch_number' => 'BATCH-TMB-1126',
                ]
            );

            GoodsReceiptItem::updateOrCreate(
                ['goods_receipt_id' => $gr2->id, 'product_id' => $products['KPN-SNK-001']->id],
                [
                    'purchase_order_item_id' => $po2Item2->id,
                    'quantity_ordered' => 40,
                    'quantity_received' => 40,
                    'quantity_damaged' => 0,
                    'conversion_factor' => 1,
                    'stock_added' => 40,
                    'unit_cost' => 12500,
                    'batch_number' => 'BATCH-CRS-1126',
                    'expiry_date' => $now->copy()->addDays(4)->toDateString(),
                ]
            );

            $inv2 = SupplierInvoice::withoutGlobalScopes()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'invoice_number' => 'BINV-KPN-2026-002'],
                [
                    'supplier_id' => $supBakery->id,
                    'purchase_order_id' => $po2->id,
                    'goods_receipt_id' => $gr2->id,
                    'supplier_invoice_number' => 'INV/ABM/2026/0519',
                    'invoice_date' => $now->copy()->subDays(6)->toDateString(),
                    'due_date' => $now->copy()->addDays(5)->toDateString(),
                    'payment_terms' => 7,
                    'subtotal' => 2400000,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'shipping_cost' => 0,
                    'total_amount' => 2400000,
                    'amount_paid' => 1000000,
                    'return_deduction' => 190000,
                    'outstanding_balance' => 1210000,
                    'matching_status' => 'matched',
                    'status' => SupplierInvoice::STATUS_PARTIALLY_PAID,
                    'notes' => 'DP Rp 1.000.000 telah dibayar, dikurangi retur 2 pcs tumbler penyok Rp 190.000. Sisa tagihan Rp 1.210.000.',
                    'created_by' => $owner->id,
                ]
            );

            SupplierPayment::withoutGlobalScopes()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'payment_number' => 'SPAY-KPN-2026-002'],
                [
                    'supplier_id' => $supBakery->id,
                    'supplier_invoice_id' => $inv2->id,
                    'payment_date' => $now->copy()->subDays(5)->toDateString(),
                    'amount' => 1000000,
                    'payment_method' => 'bank_transfer',
                    'reference_number' => 'BNI-TRF-5519201',
                    'notes' => 'Pembayaran termin pertama (Down Payment).',
                    'created_by' => $owner->id,
                ]
            );

            $supReturn = SupplierReturn::withoutGlobalScopes()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'return_number' => 'SRET-KPN-2026-001'],
                [
                    'supplier_id' => $supBakery->id,
                    'outlet_id' => $outletPusat->id,
                    'purchase_order_id' => $po2->id,
                    'goods_receipt_id' => $gr2->id,
                    'supplier_invoice_id' => $inv2->id,
                    'return_date' => $now->copy()->subDays(5)->toDateString(),
                    'total_refund_amount' => 190000,
                    'resolution_type' => 'deduct_payable',
                    'status' => 'completed',
                    'reason' => '2 pcs Tumbler Stainless mengalami penyok bodi saat pengiriman ekspedisi.',
                    'created_by' => $manager->id,
                ]
            );

            SupplierReturnItem::updateOrCreate(
                ['supplier_return_id' => $supReturn->id, 'product_id' => $products['KPN-MRC-001']->id],
                [
                    'quantity' => 2,
                    'unit_cost' => 95000,
                    'subtotal' => 190000,
                    'deduct_stock' => true,
                    'condition_notes' => 'Penyok pada bagian tutup dan bodi bawah',
                ]
            );

            // PO 3: Ordered & Ready for Live Demo of "Receive Goods"
            $po3 = PurchaseOrder::withoutGlobalScopes()->withTrashed()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'po_number' => 'PO-KPN-2026-003'],
                [
                    'outlet_id' => $outletKemang->id,
                    'supplier_id' => $supDairy->id,
                    'order_date' => $now->copy()->subDays(1)->toDateString(),
                    'expected_date' => $now->copy()->addDays(1)->toDateString(),
                    'status' => PurchaseOrder::STATUS_ORDERED,
                    'payment_terms' => 14,
                    'subtotal' => 1190000,
                    'discount_amount' => 0,
                    'tax_rate' => 11,
                    'tax_amount' => 130900,
                    'shipping_cost' => 25000,
                    'total_amount' => 1345900,
                    'notes' => 'Restock darurat Uji Matcha Latte (stok menipis di Cabang Kemang) dan Belgian Chocolate.',
                    'created_by' => $manager->id,
                    'approved_by' => $owner->id,
                    'approved_at' => $now->copy()->subDays(1),
                    'ordered_at' => $now->copy()->subDays(1),
                    'deleted_at' => null,
                ]
            );

            PurchaseOrderItem::updateOrCreate(
                ['purchase_order_id' => $po3->id, 'product_id' => $products['KPN-NCF-001']->id],
                [
                    'product_name' => $products['KPN-NCF-001']->name,
                    'product_sku' => $products['KPN-NCF-001']->sku,
                    'purchase_unit' => 'cup',
                    'conversion_factor' => 1,
                    'quantity_ordered' => 50,
                    'quantity_received' => 0,
                    'unit_price' => 15000,
                    'discount_amount' => 0,
                    'tax_amount' => 82500,
                    'subtotal' => 750000,
                ]
            );

            PurchaseOrderItem::updateOrCreate(
                ['purchase_order_id' => $po3->id, 'product_id' => $products['KPN-NCF-002']->id],
                [
                    'product_name' => $products['KPN-NCF-002']->name,
                    'product_sku' => $products['KPN-NCF-002']->sku,
                    'purchase_unit' => 'cup',
                    'conversion_factor' => 1,
                    'quantity_ordered' => 30,
                    'quantity_received' => 0,
                    'unit_price' => 14666.67,
                    'discount_amount' => 0,
                    'tax_amount' => 48400,
                    'subtotal' => 440000,
                ]
            );

            // ── 9. Cashier Shifts (Yesterday Closed + Today Active Open) ───────
            CashierShift::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'user_id' => $cashier1->id,
                    'outlet_id' => $outletPusat->id,
                    'started_at' => $now->copy()->subDay()->setTime(8, 0, 0),
                ],
                [
                    'ended_at' => $now->copy()->subDay()->setTime(21, 0, 0),
                    'starting_cash' => 500000,
                    'ending_cash' => 1850000,
                    'notes' => 'Shift kemarin ditutup sesuai perhitungan kas fisik tanpa selisih.',
                ]
            );

            CashierShift::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'user_id' => $cashier1->id,
                    'outlet_id' => $outletPusat->id,
                    'ended_at' => null,
                ],
                [
                    'started_at' => $now->copy()->setTime(8, 0, 0),
                    'starting_cash' => 500000,
                    'ending_cash' => null,
                    'notes' => 'Modal awal laci kasir pagi (Siap transaksi demo POS).',
                ]
            );

            // ── 10. Step 7 (Guide): Orders, OrderItems & Payments ──────────────
            // Includes historical monthly orders (for 6-month trend), last 7 days, yesterday, and today
            $ordersBlueprint = [
                // 5 months ago
                [
                    'order_number' => 'ORD-KPN-2606-001',
                    'outlet' => $outletPusat,
                    'cashier' => $cashier1,
                    'customer' => $customers['MBR-KPN-001'],
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'qris',
                    'when' => $now->copy()->subMonths(5)->setDay(12)->setTime(10, 30),
                    'discount' => 0,
                    'items' => [
                        ['sku' => 'KPN-COF-001', 'qty' => 15],
                        ['sku' => 'KPN-SNK-001', 'qty' => 10],
                        ['sku' => 'KPN-RTL-001', 'qty' => 4],
                    ],
                ],
                // 4 months ago
                [
                    'order_number' => 'ORD-KPN-2607-001',
                    'outlet' => $outletPusat,
                    'cashier' => $cashier1,
                    'customer' => $customers['MBR-KPN-002'],
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'card',
                    'when' => $now->copy()->subMonths(4)->setDay(15)->setTime(14, 15),
                    'discount' => 0,
                    'items' => [
                        ['sku' => 'KPN-COF-002', 'qty' => 18],
                        ['sku' => 'KPN-FOD-001', 'qty' => 10],
                        ['sku' => 'KPN-RTL-002', 'qty' => 3],
                    ],
                ],
                // 3 months ago
                [
                    'order_number' => 'ORD-KPN-2608-001',
                    'outlet' => $outletKemang,
                    'cashier' => $cashier2,
                    'customer' => $customers['MBR-KPN-003'],
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'qris',
                    'when' => $now->copy()->subMonths(3)->setDay(18)->setTime(11, 45),
                    'discount' => 0,
                    'items' => [
                        ['sku' => 'KPN-NCF-001', 'qty' => 20],
                        ['sku' => 'KPN-FOD-002', 'qty' => 12],
                        ['sku' => 'KPN-MRC-001', 'qty' => 3],
                    ],
                ],
                // 2 months ago
                [
                    'order_number' => 'ORD-KPN-2609-001',
                    'outlet' => $outletPusat,
                    'cashier' => $cashier1,
                    'customer' => $customers['MBR-KPN-001'],
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'transfer',
                    'when' => $now->copy()->subMonths(2)->setDay(20)->setTime(16, 0),
                    'discount' => 50000,
                    'items' => [
                        ['sku' => 'KPN-COF-001', 'qty' => 25],
                        ['sku' => 'KPN-SNK-002', 'qty' => 15],
                        ['sku' => 'KPN-RTL-001', 'qty' => 6],
                    ],
                ],
                // Last month
                [
                    'order_number' => 'ORD-KPN-2610-001',
                    'outlet' => $outletPusat,
                    'cashier' => $cashier1,
                    'customer' => $customers['MBR-KPN-002'],
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'card',
                    'when' => $now->copy()->subMonth()->setDay(10)->setTime(13, 20),
                    'discount' => 0,
                    'items' => [
                        ['sku' => 'KPN-COF-001', 'qty' => 20],
                        ['sku' => 'KPN-FOD-001', 'qty' => 12],
                        ['sku' => 'KPN-RTL-003', 'qty' => 8],
                    ],
                ],
                // 6 days ago
                [
                    'order_number' => 'ORD-KPN-D6-001',
                    'outlet' => $outletPusat,
                    'cashier' => $cashier1,
                    'customer' => $customers['MBR-KPN-004'],
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'qris',
                    'when' => $now->copy()->subDays(6)->setTime(10, 15),
                    'discount' => 0,
                    'items' => [
                        ['sku' => 'KPN-COF-001', 'qty' => 4],
                        ['sku' => 'KPN-SNK-001', 'qty' => 3],
                        ['sku' => 'KPN-FOD-003', 'qty' => 2],
                    ],
                ],
                // 5 days ago
                [
                    'order_number' => 'ORD-KPN-D5-001',
                    'outlet' => $outletKemang,
                    'cashier' => $cashier2,
                    'customer' => $customers['MBR-KPN-003'],
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'card',
                    'when' => $now->copy()->subDays(5)->setTime(12, 40),
                    'discount' => 0,
                    'items' => [
                        ['sku' => 'KPN-NCF-001', 'qty' => 3],
                        ['sku' => 'KPN-FOD-002', 'qty' => 3],
                        ['sku' => 'KPN-SNK-003', 'qty' => 2],
                    ],
                ],
                // 4 days ago
                [
                    'order_number' => 'ORD-KPN-D4-001',
                    'outlet' => $outletBandung,
                    'cashier' => $cashier2,
                    'customer' => $customers['MBR-KPN-005'],
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'cash',
                    'when' => $now->copy()->subDays(4)->setTime(15, 10),
                    'discount' => 0,
                    'items' => [
                        ['sku' => 'KPN-COF-002', 'qty' => 4],
                        ['sku' => 'KPN-SNK-002', 'qty' => 4],
                        ['sku' => 'KPN-RTL-001', 'qty' => 2],
                    ],
                ],
                // 3 days ago
                [
                    'order_number' => 'ORD-KPN-D3-001',
                    'outlet' => $outletPusat,
                    'cashier' => $cashier1,
                    'customer' => $customers['MBR-KPN-006'],
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'qris',
                    'when' => $now->copy()->subDays(3)->setTime(11, 0),
                    'discount' => 0,
                    'items' => [
                        ['sku' => 'KPN-COF-004', 'qty' => 5],
                        ['sku' => 'KPN-FOD-001', 'qty' => 4],
                        ['sku' => 'KPN-NCF-003', 'qty' => 3],
                    ],
                ],
                // 2 days ago
                [
                    'order_number' => 'ORD-KPN-D2-001',
                    'outlet' => $outletPusat,
                    'cashier' => $cashier1,
                    'customer' => $customers['MBR-KPN-007'],
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'transfer',
                    'when' => $now->copy()->subDays(2)->setTime(14, 30),
                    'discount' => 15000,
                    'items' => [
                        ['sku' => 'KPN-RTL-002', 'qty' => 2],
                        ['sku' => 'KPN-MRC-001', 'qty' => 1],
                        ['sku' => 'KPN-COF-003', 'qty' => 3],
                    ],
                ],
                // Yesterday (Order 1)
                [
                    'order_number' => 'ORD-KPN-D1-001',
                    'outlet' => $outletPusat,
                    'cashier' => $cashier1,
                    'customer' => $customers['MBR-KPN-001'],
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'qris',
                    'when' => $now->copy()->subDay()->setTime(9, 45),
                    'discount' => 0,
                    'items' => [
                        ['sku' => 'KPN-COF-001', 'qty' => 4],
                        ['sku' => 'KPN-SNK-001', 'qty' => 4],
                    ],
                ],
                // Yesterday (Order 2)
                [
                    'order_number' => 'ORD-KPN-D1-002',
                    'outlet' => $outletKemang,
                    'cashier' => $cashier2,
                    'customer' => null, // Walk-in customer
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'cash',
                    'when' => $now->copy()->subDay()->setTime(13, 15),
                    'discount' => 0,
                    'items' => [
                        ['sku' => 'KPN-FOD-001', 'qty' => 2],
                        ['sku' => 'KPN-NCF-002', 'qty' => 2],
                    ],
                ],
                // Today (Order 1 - Completed Morning Rush)
                [
                    'order_number' => 'ORD-KPN-TDY-001',
                    'outlet' => $outletPusat,
                    'cashier' => $cashier1,
                    'customer' => $customers['MBR-KPN-001'],
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'qris',
                    'when' => $now->copy()->setTime(8, 30),
                    'discount' => 10000,
                    'items' => [
                        ['sku' => 'KPN-COF-001', 'qty' => 5],
                        ['sku' => 'KPN-SNK-001', 'qty' => 5],
                        ['sku' => 'KPN-RTL-001', 'qty' => 2],
                    ],
                ],
                // Today (Order 2 - Completed Lunch Order)
                [
                    'order_number' => 'ORD-KPN-TDY-002',
                    'outlet' => $outletPusat,
                    'cashier' => $cashier1,
                    'customer' => $customers['MBR-KPN-002'],
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'card',
                    'when' => $now->copy()->setTime(11, 15),
                    'discount' => 0,
                    'items' => [
                        ['sku' => 'KPN-FOD-001', 'qty' => 3],
                        ['sku' => 'KPN-FOD-002', 'qty' => 2],
                        ['sku' => 'KPN-COF-002', 'qty' => 3],
                        ['sku' => 'KPN-NCF-001', 'qty' => 2],
                    ],
                ],
                // Today (Order 3 - Completed Walk-in Cash at Kemang)
                [
                    'order_number' => 'ORD-KPN-TDY-003',
                    'outlet' => $outletKemang,
                    'cashier' => $cashier2,
                    'customer' => null,
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'cash',
                    'when' => $now->copy()->setTime(12, 10),
                    'discount' => 0,
                    'items' => [
                        ['sku' => 'KPN-COF-003', 'qty' => 2],
                        ['sku' => 'KPN-FOD-003', 'qty' => 2],
                        ['sku' => 'KPN-SNK-003', 'qty' => 1],
                    ],
                ],
                // Today (Order 4 - Pending Dine-In Open Bill for Live Demo)
                [
                    'order_number' => 'ORD-KPN-TDY-004',
                    'outlet' => $outletPusat,
                    'cashier' => $cashier1,
                    'customer' => $customers['MBR-KPN-004'],
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'payment_method' => null,
                    'when' => $now->copy()->subMinutes(25),
                    'discount' => 0,
                    'notes' => 'Meja VIP 04 (Open Bill - Siap didemokan pelunasan/cetak struk)',
                    'items' => [
                        ['sku' => 'KPN-COF-004', 'qty' => 2],
                        ['sku' => 'KPN-SNK-002', 'qty' => 2],
                    ],
                ],
                // Today (Order 5 - Refunded Order with Supervisor Authorization)
                [
                    'order_number' => 'ORD-KPN-TDY-005',
                    'outlet' => $outletPusat,
                    'cashier' => $cashier1,
                    'customer' => null,
                    'status' => 'refunded',
                    'payment_status' => 'refunded',
                    'payment_method' => 'cash',
                    'when' => $now->copy()->subHours(2),
                    'discount' => 0,
                    'authorized_by' => $manager->id,
                    'notes' => 'Contoh transaksi refund (Pelanggan salah pilih varian, diotorisasi oleh Manager)',
                    'items' => [
                        ['sku' => 'KPN-NCF-003', 'qty' => 1],
                    ],
                ],
            ];

            $lastCompletedOrder = null;

            foreach ($ordersBlueprint as $oData) {
                $subtotal = 0;
                $preparedItems = [];

                foreach ($oData['items'] as $line) {
                    $prod = $products[$line['sku']];
                    $qty = $line['qty'];
                    $unitPrice = (float) $prod->price;
                    $lineSubtotal = $unitPrice * $qty;
                    $lineTax = round($lineSubtotal * 0.11, 2);
                    $subtotal += $lineSubtotal;

                    $preparedItems[] = [
                        'product' => $prod,
                        'qty' => $qty,
                        'unit_price' => $unitPrice,
                        'cost_price' => (float) $prod->cost_price,
                        'tax_amount' => $lineTax,
                        'subtotal' => $lineSubtotal,
                    ];
                }

                $discountAmount = (float) ($oData['discount'] ?? 0);
                $taxAmount = round(($subtotal - $discountAmount) * 0.11, 2);
                $totalAmount = $subtotal + $taxAmount - $discountAmount;

                $order = Order::withoutGlobalScopes()->withTrashed()->updateOrCreate(
                    ['order_number' => $oData['order_number']],
                    [
                        'tenant_id' => $tenant->id,
                        'outlet_id' => $oData['outlet']->id,
                        'user_id' => $oData['cashier']->id,
                        'cashier_name' => $oData['cashier']->name,
                        'customer_id' => $oData['customer']?->id,
                        'status' => $oData['status'],
                        'subtotal' => $subtotal,
                        'tax_amount' => $taxAmount,
                        'discount_amount' => $discountAmount,
                        'discount_type' => $discountAmount > 0 ? 'fixed' : null,
                        'total_amount' => $totalAmount,
                        'payment_status' => $oData['payment_status'],
                        'notes' => $oData['notes'] ?? 'Transaksi POS Kopi Nusantara',
                        'authorized_by' => $oData['authorized_by'] ?? null,
                        'completed_at' => $oData['status'] === 'completed' ? $oData['when'] : null,
                        'deleted_at' => null,
                    ]
                );

                DB::table('orders')->where('id', $order->id)->update([
                    'created_at' => $oData['when'],
                    'updated_at' => $oData['when'],
                ]);

                foreach ($preparedItems as $itemLine) {
                    $oi = OrderItem::updateOrCreate(
                        [
                            'order_id' => $order->id,
                            'product_id' => $itemLine['product']->id,
                        ],
                        [
                            'product_name' => $itemLine['product']->name,
                            'product_sku' => $itemLine['product']->sku,
                            'unit_price' => $itemLine['unit_price'],
                            'cost_price' => $itemLine['cost_price'],
                            'quantity' => $itemLine['qty'],
                            'discount_amount' => 0,
                            'tax_amount' => $itemLine['tax_amount'],
                            'subtotal' => $itemLine['subtotal'],
                        ]
                    );

                    DB::table('order_items')->where('id', $oi->id)->update([
                        'created_at' => $oData['when'],
                        'updated_at' => $oData['when'],
                    ]);
                }

                if ($oData['payment_method'] !== null) {
                    $cashPaid = $oData['payment_method'] === 'cash'
                        ? ceil($totalAmount / 50000) * 50000
                        : $totalAmount;

                    $payment = Payment::withoutGlobalScopes()->updateOrCreate(
                        ['order_id' => $order->id],
                        [
                            'payment_method' => $oData['payment_method'],
                            'amount' => $cashPaid,
                            'change_amount' => max(0, $cashPaid - $totalAmount),
                            'status' => $oData['status'] === 'refunded' ? 'refunded' : 'completed',
                            'reference_number' => $oData['payment_method'] === 'cash'
                                ? null
                                : strtoupper($oData['payment_method']).'-'.substr(md5($oData['order_number']), 0, 8),
                            'notes' => $oData['status'] === 'refunded' ? 'Dana dikembalikan tunai ke pelanggan' : 'Pembayaran berhasil',
                            'paid_at' => $oData['when'],
                        ]
                    );

                    DB::table('payments')->where('id', $payment->id)->update([
                        'created_at' => $oData['when'],
                        'updated_at' => $oData['when'],
                    ]);
                }

                if ($oData['status'] === 'completed') {
                    $lastCompletedOrder = $order;
                }
            }

            // ── 11. Stock Movements Ledger (Goods Receipt, Sale, Transfer, Adjustment)
            StockMovement::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();

            $movementsData = [
                [
                    'product' => $products['KPN-RTL-001'],
                    'outlet' => $outletPusat,
                    'type' => 'goods_receipt',
                    'before' => 15,
                    'change' => 25,
                    'after' => 40,
                    'reason' => 'Penerimaan Barang PO #PO-KPN-2026-001 (GR-KPN-2026-001)',
                    'ref' => $gr1,
                    'user' => $manager,
                    'when' => $now->copy()->subDays(15),
                ],
                [
                    'product' => $products['KPN-MRC-001'],
                    'outlet' => $outletPusat,
                    'type' => 'goods_receipt',
                    'before' => 2,
                    'change' => 20,
                    'after' => 22,
                    'reason' => 'Penerimaan Barang PO #PO-KPN-2026-002 (GR-KPN-2026-002)',
                    'ref' => $gr2,
                    'user' => $manager,
                    'when' => $now->copy()->subDays(6),
                ],
                [
                    'product' => $products['KPN-MRC-001'],
                    'outlet' => $outletPusat,
                    'type' => 'supplier_return',
                    'before' => 22,
                    'change' => -2,
                    'after' => 20,
                    'reason' => 'Retur Supplier #SRET-KPN-2026-001 (2 pcs tumbler penyok)',
                    'ref' => $supReturn,
                    'user' => $manager,
                    'when' => $now->copy()->subDays(5),
                ],
                [
                    'product' => $products['KPN-RTL-001'],
                    'outlet' => $outletPusat,
                    'type' => 'transfer_out',
                    'before' => 44,
                    'change' => -6,
                    'after' => 38,
                    'reason' => 'Transfer stok antar cabang ke Cabang Kemang Village',
                    'ref' => null,
                    'user' => $manager,
                    'when' => $now->copy()->subDays(4),
                ],
                [
                    'product' => $products['KPN-RTL-001'],
                    'outlet' => $outletKemang,
                    'type' => 'transfer_in',
                    'before' => 18,
                    'change' => 6,
                    'after' => 24,
                    'reason' => 'Terima transfer stok dari Outlet Pusat Senopati',
                    'ref' => null,
                    'user' => $manager,
                    'when' => $now->copy()->subDays(4),
                ],
                [
                    'product' => $products['KPN-SNK-001'],
                    'outlet' => $outletPusat,
                    'type' => 'adjustment',
                    'before' => 11,
                    'change' => 0,
                    'after' => 11,
                    'reason' => 'Stok Opname Mingguan - Fisik sesuai sistem',
                    'ref' => null,
                    'user' => $manager,
                    'when' => $now->copy()->subDays(2),
                ],
                [
                    'product' => $products['KPN-COF-001'],
                    'outlet' => $outletPusat,
                    'type' => 'sale',
                    'before' => 125,
                    'change' => -5,
                    'after' => 120,
                    'reason' => 'Penjualan POS #ORD-KPN-TDY-001',
                    'ref' => $lastCompletedOrder,
                    'user' => $cashier1,
                    'when' => $now->copy()->setTime(8, 30),
                ],
            ];

            foreach ($movementsData as $m) {
                $sm = StockMovement::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'product_id' => $m['product']->id,
                    'outlet_id' => $m['outlet']->id,
                    'type' => $m['type'],
                    'quantity_before' => $m['before'],
                    'quantity_change' => $m['change'],
                    'quantity_after' => $m['after'],
                    'reason' => $m['reason'],
                    'reference_type' => $m['ref'] ? get_class($m['ref']) : null,
                    'reference_id' => $m['ref']?->id,
                    'user_id' => $m['user']->id,
                ]);

                DB::table('stock_movements')->where('id', $sm->id)->update([
                    'created_at' => $m['when'],
                    'updated_at' => $m['when'],
                ]);
            }
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        DB::transaction(function () {
            $tenant = Tenant::withTrashed()->where('slug', self::DEMO_TENANT_SLUG)->first();

            if (! $tenant) {
                return;
            }

            // Remove Supplier & Procurement records for this demo tenant
            SupplierReturn::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();
            SupplierPayment::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();
            SupplierInvoice::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();
            GoodsReceipt::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();
            PurchaseOrder::withoutGlobalScopes()->withTrashed()->where('tenant_id', $tenant->id)->forceDelete();
            SupplierPriceHistory::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();
            SupplierProduct::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();
            Supplier::withoutGlobalScopes()->withTrashed()->where('tenant_id', $tenant->id)->forceDelete();

            // Remove Stock Movements, Orders, Cashier Shifts, Products, Categories, Customers, Outlets, Settings, Users
            StockMovement::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();
            Order::withoutGlobalScopes()->withTrashed()->where('tenant_id', $tenant->id)->forceDelete();
            CashierShift::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();
            Product::withoutGlobalScopes()->withTrashed()->where('tenant_id', $tenant->id)->forceDelete();
            Category::withoutGlobalScopes()->withTrashed()->where('tenant_id', $tenant->id)->forceDelete();
            Customer::withoutGlobalScopes()->withTrashed()->where('tenant_id', $tenant->id)->forceDelete();
            Outlet::withoutGlobalScopes()->withTrashed()->where('tenant_id', $tenant->id)->forceDelete();
            Setting::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();

            $users = User::withTrashed()->where('tenant_id', $tenant->id)->get();
            foreach ($users as $user) {
                $user->roles()->detach();
                $user->outlets()->detach();
                $user->forceDelete();
            }

            $tenant->forceDelete();
        });
    }

    private function seedRolesAndPermissions(): void
    {
        $permissions = [
            'create outlets',
            'edit outlets',
            'delete outlets',
            'manage outlets',
            'create products',
            'edit products',
            'delete products',
            'manage products',
            'view products',
            'create categories',
            'edit categories',
            'delete categories',
            'create customers',
            'edit customers',
            'delete customers',
            'create orders',
            'edit orders',
            'delete orders',
            'manage orders',
            'view orders',
            'view payments',
            'manage payments',
            'create users',
            'edit users',
            'delete users',
            'manage users',
            'authorize refund',
            'authorize void',
            'view reports',
            'view cashier reports',
            'view suppliers',
            'manage suppliers',
            'manage purchase orders',
            'approve purchase orders',
            'receive goods',
            'manage supplier invoices',
            'manage supplier payments',
            'manage settings',
            'manage tenants',
            'view activity logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = [
            'super_admin' => [],
            'tenant_admin' => [
                'create outlets', 'edit outlets', 'delete outlets', 'manage outlets',
                'create products', 'edit products', 'delete products', 'manage products', 'view products',
                'create categories', 'edit categories', 'delete categories',
                'create customers', 'edit customers', 'delete customers',
                'create orders', 'edit orders', 'delete orders', 'manage orders', 'view orders',
                'view payments', 'manage payments',
                'create users', 'edit users', 'delete users', 'manage users',
                'authorize refund', 'authorize void',
                'view reports', 'view cashier reports',
                'view suppliers', 'manage suppliers', 'manage purchase orders', 'approve purchase orders',
                'receive goods', 'manage supplier invoices', 'manage supplier payments',
                'manage settings', 'view activity logs',
            ],
            'manager' => [
                'create products', 'edit products', 'view products', 'manage products',
                'create categories', 'edit categories',
                'create customers', 'edit customers',
                'create orders', 'edit orders', 'manage orders', 'view orders',
                'view payments',
                'authorize refund', 'authorize void',
                'view reports', 'view cashier reports',
                'view suppliers', 'manage suppliers', 'manage purchase orders', 'approve purchase orders',
                'receive goods', 'manage supplier invoices', 'manage supplier payments',
                'view activity logs',
            ],
            'cashier' => [
                'view products', 'create orders', 'view orders', 'view payments', 'create customers',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            if (! empty($rolePermissions)) {
                $role->syncPermissions($rolePermissions);
            }
        }
    }
};
