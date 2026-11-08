<?php

namespace Tests\Feature;

use App\Events\OrderCreated;
use App\Events\PaymentCompleted;
use App\Events\StockUpdated;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $tenantAdmin;

    protected Outlet $outlet;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::firstOrCreate(['name' => 'tenant_admin', 'guard_name' => 'web']);

        $this->tenant = Tenant::create([
            'name' => 'Kedai Kopi Mantap',
            'slug' => 'kedai-kopi-mantap',
            'email' => 'owner@kedaikopi.com',
            'status' => 'active',
            'plan' => 'professional',
            'subscription_status' => 'active',
            'subscription_ends_at' => now()->addDays(30),
            'is_subscription_exempt' => true,
        ]);

        $this->outlet = Outlet::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Outlet Utama',
            'code' => 'OUT-01',
            'address' => 'Jl. Sudirman No. 1',
            'is_active' => true,
        ]);

        $this->tenantAdmin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Budi Owner',
            'email' => 'budi@kedaikopi.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $this->tenantAdmin->assignRole($role);
    }

    public function test_authenticated_user_can_view_notifications_page(): void
    {
        $this->tenantAdmin->notify(new GeneralNotification(
            title: 'Info Pembaruan Sistem',
            message: 'Fitur notifikasi baru telah diaktifkan.',
            actionUrl: route('admin.dashboard')
        ));

        $response = $this->actingAs($this->tenantAdmin)
            ->get(route('admin.notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('Pusat Notifikasi');
        $response->assertSee('Info Pembaruan Sistem');
        $response->assertSee('Fitur notifikasi baru telah diaktifkan.');
    }

    public function test_user_can_filter_notifications_by_status(): void
    {
        $this->tenantAdmin->notify(new GeneralNotification(
            title: 'Notifikasi Satu',
            message: 'Pesan belum dibaca'
        ));

        $this->tenantAdmin->notify(new GeneralNotification(
            title: 'Notifikasi Dua',
            message: 'Pesan sudah dibaca'
        ));

        // Mark second notification as read explicitly
        $secondNotif = $this->tenantAdmin->notifications()
            ->where('data', 'like', '%Notifikasi Dua%')
            ->first();
        $secondNotif->markAsRead();

        // Check unread filter
        $responseUnread = $this->actingAs($this->tenantAdmin)
            ->get(route('admin.notifications.index', ['filter' => 'unread']));

        $responseUnread->assertStatus(200);
        $responseUnread->assertViewHas('notifications', function ($items) {
            return $items->count() === 1 && $items->first()->data['title'] === 'Notifikasi Satu';
        });

        // Check read filter
        $responseRead = $this->actingAs($this->tenantAdmin)
            ->get(route('admin.notifications.index', ['filter' => 'read']));

        $responseRead->assertStatus(200);
        $responseRead->assertViewHas('notifications', function ($items) {
            return $items->count() === 1 && $items->first()->data['title'] === 'Notifikasi Dua';
        });
    }

    public function test_user_can_mark_single_notification_as_read(): void
    {
        $this->tenantAdmin->notify(new GeneralNotification(
            title: 'Tes Baca',
            message: 'Harus ditandai dibaca'
        ));

        $notification = $this->tenantAdmin->unreadNotifications()->first();
        $this->assertNotNull($notification);
        $this->assertNull($notification->read_at);

        $response = $this->actingAs($this->tenantAdmin)
            ->post(route('admin.notifications.read', $notification->id));

        $response->assertSessionHas('success');
        $notification->refresh();
        $this->assertNotNull($notification->read_at);
        $this->assertSame(0, $this->tenantAdmin->unreadNotifications()->count());
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $this->tenantAdmin->notify(new GeneralNotification(title: 'Notif 1', message: 'M1'));
        $this->tenantAdmin->notify(new GeneralNotification(title: 'Notif 2', message: 'M2'));

        $this->assertSame(2, $this->tenantAdmin->unreadNotifications()->count());

        $response = $this->actingAs($this->tenantAdmin)
            ->post(route('admin.notifications.mark-all-read'));

        $response->assertSessionHas('success');
        $this->assertSame(0, $this->tenantAdmin->unreadNotifications()->count());
    }

    public function test_user_can_delete_notification(): void
    {
        $this->tenantAdmin->notify(new GeneralNotification(title: 'Hapus Saya', message: 'Akan dihapus'));

        $notification = $this->tenantAdmin->notifications()->first();
        $this->assertNotNull($notification);

        $response = $this->actingAs($this->tenantAdmin)
            ->delete(route('admin.notifications.destroy', $notification->id));

        $response->assertSessionHas('success');
        $this->assertSame(0, $this->tenantAdmin->notifications()->count());
    }

    public function test_user_can_clear_read_notifications(): void
    {
        $this->tenantAdmin->notify(new GeneralNotification(title: 'Notif Aktif', message: 'Belum dibaca'));
        $this->tenantAdmin->notify(new GeneralNotification(title: 'Notif Lama', message: 'Sudah dibaca'));

        $oldNotif = $this->tenantAdmin->notifications()
            ->where('data', 'like', '%Notif Lama%')
            ->first();
        $oldNotif->markAsRead();

        $response = $this->actingAs($this->tenantAdmin)
            ->post(route('admin.notifications.clear-all'), ['scope' => 'read']);

        $response->assertSessionHas('success');
        $this->assertSame(1, $this->tenantAdmin->notifications()->count());
        $this->assertSame('Notif Aktif', $this->tenantAdmin->notifications()->first()->data['title']);
    }

    public function test_user_can_fetch_unread_count_json(): void
    {
        $this->tenantAdmin->notify(new GeneralNotification(title: 'Halo', message: 'Pesan'));

        $response = $this->actingAs($this->tenantAdmin)
            ->getJson(route('admin.notifications.count'));

        $response->assertStatus(200);
        $response->assertJson(['unread_count' => 1]);
    }

    public function test_order_created_event_dispatches_notification_to_tenant_admin(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pelanggan Setia',
            'email' => 'pelanggan@test.com',
        ]);

        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'customer_id' => $customer->id,
            'user_id' => $this->tenantAdmin->id,
            'order_number' => 'ORD-20261108-0001',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 50000,
            'tax_amount' => 5500,
            'discount_amount' => 0,
            'total_amount' => 55500,
        ]);

        event(new OrderCreated($order));

        $this->assertSame(1, $this->tenantAdmin->notifications()->count());
        $notification = $this->tenantAdmin->notifications()->first();
        $this->assertSame('Pesanan Baru Diterima', $notification->data['title']);
        $this->assertStringContainsString('ORD-20261108-0001', $notification->data['message']);
    }

    public function test_payment_completed_event_dispatches_notification_to_tenant_admin(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pelanggan Dua',
            'email' => 'pelanggan2@test.com',
        ]);

        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'customer_id' => $customer->id,
            'user_id' => $this->tenantAdmin->id,
            'order_number' => 'ORD-20261108-0002',
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal' => 75000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 75000,
        ]);

        $payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => 75000,
            'change_amount' => 0,
            'status' => 'completed',
            'reference_number' => 'PAY-20261108-0001',
        ]);

        event(new PaymentCompleted($payment, $order));

        $this->assertSame(1, $this->tenantAdmin->notifications()->count());
        $notification = $this->tenantAdmin->notifications()->first();
        $this->assertSame('Pembayaran Diterima', $notification->data['title']);
        $this->assertStringContainsString('CASH', $notification->data['message']);
    }

    public function test_low_stock_notification_is_dispatched_when_stock_at_or_below_minimum(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Kopi Arabika 250g',
            'sku' => 'KOP-ARA-250',
            'price' => 65000,
            'cost_price' => 40000,
            'track_stock' => true,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        // Stock falls to 4 (which is <= min_stock of 5)
        event(new StockUpdated($product, stockBefore: 10, stockAfter: 4, reason: 'sale'));

        $this->assertSame(1, $this->tenantAdmin->notifications()->count());
        $notification = $this->tenantAdmin->notifications()->first();
        $this->assertSame('Peringatan Stok Rendah', $notification->data['title']);
        $this->assertStringContainsString('Kopi Arabika 250g', $notification->data['message']);
    }
}
