# Rencana Implementasi: Fitur Notifikasi Dinamis (Notification System)

Dokumen ini berisi arsitektur, rencana dekomposisi komponen, routing, dan implementasi fitur notifikasi pada aplikasi POS ERP Point of Sales 2026.

---

## 1. Latar Belakang & Masalah
Saat ini tombol notifikasi pada navbar (`resources/views/admin/partials/navbar.blade.php`) masih berupa template statis/dummy:
- Konten notifikasi hardcoded (New order received, Low stock alert, Payment received).
- Indikator badge merah statis (tidak berdasarkan status unread pengguna).
- Belum ada halaman terpisah (*dedicated notifications page*) untuk melihat riwayat lengkap notifikasi, menyaring (filter all/unread/read), atau menandai telah dibaca.
- Kode dropdown notifikasi masih bercampur di dalam `navbar.blade.php`, mengurangi keterbacaan kode (*clean code*).

---

## 2. Tujuan Perubahan
1. **Dekomposisi Komponen (*Clean Code*)**:
   - Memisahkan dropdown notifikasi navbar menjadi komponen parsial mandiri: `resources/views/admin/partials/notifications-dropdown.blade.php`.
   - Mengganti blok notifikasi di `navbar.blade.php` dengan pemanggilan parsial `@include('admin.partials.notifications-dropdown')`.
2. **Halaman Khusus Notifikasi**:
   - Membuat halaman mandiri `resources/views/admin/notifications/index.blade.php` dengan layout admin, filter status (*All*, *Unread*, *Read*), pagination, tombol tindakan baca (*Mark as Read* / *Mark all as Read*), dan hapus (*Delete*).
3. **Database & Notification Backend**:
   - Menggunakan sistem notifikasi standar Laravel (`Illuminate\Notifications\DatabaseNotification`).
   - Tabel `notifications` telah dibuat melalui migration terisolasi tanpa merusak database dev.
   - Model `User` sudah menggunakan trait `Notifiable`.
4. **Kelas Notifikasi & Otomasi Event**:
   - Menyediakan kelas notifikasi spesifik domain:
     - `OrderCreatedNotification` (pesanan baru masuk).
     - `PaymentReceivedNotification` (pembayaran telah diterima).
     - `LowStockNotification` (peringatan stok produk menipis di bawah batas minimum).
     - `GeneralNotification` (notifikasi pengumuman / sistem umum).
   - Menghubungkan event domain (`OrderCreated`, `PaymentCompleted`, `StockUpdated`) ke listeners notifikasi secara aman.
5. **Kepatuhan Aturan AGENTS.md**:
   - Tidak melakukan perintah destruktif (`migrate:fresh`, `db:wipe`, dll).
   - Pengujian berjalan di SQLite `:memory:`.
   - Format kode menggunakan Laravel Pint.

---

## 3. Struktur Berkas yang Akan Dibuat & Diubah

| Berkas | Jenis Tindakan | Deskripsi |
|---|---|---|
| `database/migrations/2026_11_08_083907_create_notifications_table.php` | Sudah dibuat & dimigrate | Tabel standar notifikasi Laravel |
| `app/Notifications/OrderCreatedNotification.php` | Baru | Kelas notifikasi untuk order baru |
| `app/Notifications/PaymentReceivedNotification.php` | Baru | Kelas notifikasi pembayaran selesai |
| `app/Notifications/LowStockNotification.php` | Baru | Kelas notifikasi stok menipis |
| `app/Notifications/GeneralNotification.php` | Baru | Kelas notifikasi umum serbaguna |
| `app/Services/NotificationService.php` | Baru | Helper service untuk mempermudah pengiriman notifikasi ke user / tenant |
| `app/Listeners/SendOrderNotification.php` | Baru | Listener untuk event `OrderCreated` |
| `app/Listeners/SendPaymentNotification.php` | Baru | Listener untuk event `PaymentCompleted` |
| `app/Listeners/SendLowStockNotification.php` | Baru | Listener untuk event `StockUpdated` jika stok <= `min_stock` |
| `app/Providers/EventServiceProvider.php` | Modifikasi | Registrasi listener notifikasi ke event yang relevan |
| `app/Http/Controllers/Admin/NotificationController.php` | Baru | Controller untuk halaman notifikasi, mark read, mark all read, delete |
| `routes/web.php` | Modifikasi | Registrasi rute notifikasi admin |
| `resources/views/admin/partials/notifications-dropdown.blade.php` | Baru | Komponen mandiri dropdown notifikasi navbar |
| `resources/views/admin/partials/navbar.blade.php` | Modifikasi | Mengganti dummy markup dengan pemanggilan komponen dropdown |
| `resources/views/admin/notifications/index.blade.php` | Baru | Halaman mandiri manajemen notifikasi pengguna |
| `resources/views/admin/partials/sidebar.blade.php` | Modifikasi | Menambahkan menu "Notifications" dengan badge jumlah belum dibaca |
| `tests/Feature/AdminNotificationTest.php` | Baru | Pengujian PHPUnit untuk alur notifikasi admin |

---

## 4. Alur Kerja & API Rute

### Rute Web (`routes/web.php`)
```php
Route::middleware(['auth', 'tenant.active', 'subscription.active'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // ...
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
            Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
            Route::delete('/clear/all', [NotificationController::class, 'clearAll'])->name('clear-all');
        });
    });
```

### Format Data Notifikasi (`data` column JSON)
```json
{
  "title": "New Order Received",
  "message": "Order #ORD-20261108-001 with total Rp 150.000 has been created.",
  "icon": "fa-solid fa-cart-shopping",
  "color": "blue",
  "action_url": "/admin/orders?search=ORD-20261108-001",
  "type": "order"
}
```

---

## 5. Rencana Pengujian
1. Membuat feature test `tests/Feature/AdminNotificationTest.php` dengan `RefreshDatabase`:
   - Admin dapat membuka halaman daftar notifikasi (`/admin/notifications`).
   - Dropdown notifikasi menampilkan data notifikasi nyata pengguna.
   - Admin dapat menandai satu notifikasi sebagai telah dibaca (*mark as read*).
   - Admin dapat menandai semua notifikasi sebagai telah dibaca (*mark all as read*).
   - Admin dapat menghapus notifikasi.
   - Listener notifikasi mendispatch notifikasi dengan benar ke pengguna tenant saat order/pembayaran/stok rendah terjadi.
2. Memastikan seluruh test baru lulus di database in-memory SQLite.
3. Menjalankan Laravel Pint untuk memastikan gaya kode bersih dan sesuai standar.
