# AGENTS.md — Aturan & Panduan Agent AI

Repository: **Point of Sales 2026** (`point-of-sales-26`)

Dokumen ini adalah aturan main untuk semua agent AI (OpenCode, Copilot, Cursor, Claude, dan lainnya) yang bekerja di repository ini. Tujuannya agar hasil kerja **benar, aman, konsisten, dan tidak pernah merusak data pengguna**.

> **Prinsip tertinggi:** Keamanan data > test hijau > kecepatan.
> Jika harus memilih antara "test lolos" dan "database aman", **pilih database aman**.

---

## 0. Profil Proyek

| Item | Nilai |
|---|---|
| Framework | Laravel 12, PHP `^8.2` |
| Autentikasi | Session (web) + Laravel Sanctum (API) |
| Permission | `spatie/laravel-permission` — role: `super_admin`, `tenant_admin`, `manager`, `cashier` |
| Activity log | `spatie/laravel-activitylog` |
| Multi-tenant | Single database, global scope lewat `App\Models\Concerns\BelongsToTenant` |
| Database development | MySQL `db_erp` (lihat `.env`) |
| Database test | SQLite `:memory:` (lihat `phpunit.xml` dan `.env.testing`) |
| Frontend | Blade + Tailwind CSS 4 + Vite |
| Testing | PHPUnit 11 (`php artisan test`) |
| Formatter | Laravel Pint |

Struktur penting:

- `app/Http/Controllers/Admin` — halaman admin berbasis Blade.
- `app/Http/Controllers/Api` — REST API untuk aplikasi mobile/POS.
- `app/Services` — business logic (`OrderService`, `PaymentService`, `InventoryService`, `ConfigService`, `CashierService`).
- `app/Models` — Eloquent, relasi, cast, dan tenant scope.
- `app/Events` + `app/Listeners` — efek samping seperti pengurangan stok dan activity log.
- `database/migrations` — schema; `database/factories` — data untuk test.
- `tests/Unit`, `tests/Feature`, `tests/Feature/Api` — test PHPUnit.
- `docs/` — dokumentasi API dan panduan frontend.

---

## 1. 🔴 ATURAN WAJIB: DATABASE TIDAK BOLEH DI-REFRESH

Bagian ini adalah aturan paling penting. Melanggar aturan ini dianggap **kegagalan task**, walaupun semua test hijau.

### 1.1 Pahami database yang dipakai

- **Database development/proyek:** MySQL `db_erp` dari `.env`. Ini berisi data kerja pengguna. **Jangan pernah menghapus, me-reset, atau me-refresh database ini.**
- **Database test:** SQLite `:memory:` dari `phpunit.xml` dan `.env.testing`. Database ini sementara, hanya hidup selama proses test, dan terpisah dari `db_erp`.
- File `database/database.sqlite` **bukan otomatis** berarti database test. Selalu cek `.env` sebelum menyimpulkan.
- Jangan pernah menganggap "test butuh database bersih" sebagai alasan untuk mengosongkan database proyek.

### 1.2 Perintah yang DILARANG tanpa persetujuan eksplisit pengguna

| Perintah / Tindakan | Alasan |
|---|---|
| `php artisan migrate:fresh` | Menghapus semua tabel dan data. |
| `php artisan migrate:refresh` | Rollback semua migrasi lalu migrate ulang — data hilang. |
| `php artisan migrate:reset` | Menghapus semua tabel. |
| `php artisan migrate:rollback` massal | Bisa menghapus tabel/data. |
| `php artisan db:wipe` | Mengosongkan seluruh database. |
| `php artisan db:seed` / `migrate --seed` di DB dev | Menambah/mengubah data nyata. |
| `DROP DATABASE`, `DROP TABLE`, `TRUNCATE`, `DELETE FROM` tanpa `WHERE` | Menghilangkan data permanen. |
| Menghapus file database (`database/*.sqlite`, file DB lain) | Bisa menghilangkan data. |
| Mengubah `.env` / `.env.testing` agar test memakai MySQL `db_erp` | Test bisa menulis/menghapus data nyata. |
| Menjalankan test yang menulis ke `db_erp` | Test tidak boleh menyentuh data proyek. |

Jika pengguna secara eksplisit meminta reset database:

1. Pastikan targetnya benar (bukan salah database).
2. Ingatkan bahwa **semua data akan hilang**.
3. Minta konfirmasi kedua sebelum menjalankan.
4. Jika memungkinkan, sarankan backup terlebih dahulu.

**Jangan pernah menjadikan "refresh database" sebagai solusi ketika test gagal.**

### 1.3 Aturan test case: wajib terisolasi, tanpa refresh database proyek

1. Semua test WAJIB berjalan dengan konfigurasi test yang sudah ada:
   - `APP_ENV=testing`
   - `DB_CONNECTION=sqlite`
   - `DB_DATABASE=:memory:`
2. Jalankan test lewat `php artisan test` atau `vendor/bin/phpunit`. Jangan meng-override `DB_CONNECTION` / `DB_DATABASE` ke MySQL.
3. **Test case tidak boleh me-refresh database proyek.** Refresh hanya boleh terjadi pada database **in-memory sekali pakai** yang terpisah dari `db_erp`.
4. `RefreshDatabase` / `DatabaseMigrations` hanya boleh dipakai jika koneksi aktif benar-benar `sqlite :memory:`. Trait tersebut **tidak boleh** dijalankan pada koneksi MySQL atau database persistent. Jika ragu, gunakan `DatabaseTransactions` dengan database test khusus.
5. Setiap test harus independen dan bisa dijalankan berulang tanpa mengubah data dev. Gunakan factory, bukan data dari database dev.
6. Jangan memakai `TRUNCATE`, `DELETE` tanpa `WHERE`, atau menghapus tabel di dalam test.
7. Jangan memakai `sleep()`, memanggil API eksternal, atau mengirim email/WhatsApp asli di test.
8. Jika test gagal karena schema atau data, perbaiki setup test di koneksi in-memory — **bukan** dengan me-refresh database dev.
9. Jangan mengubah `phpunit.xml` pada bagian `DB_CONNECTION` / `DB_DATABASE` tanpa persetujuan pengguna.

Cek cepat sebelum menjalankan test:

```bash
# Pastikan konfigurasi test masih menunjuk SQLite in-memory
grep -E "DB_CONNECTION|DB_DATABASE" phpunit.xml .env.testing

# Jalankan test (selalu lewat konfigurasi phpunit.xml)
php artisan test

# Test tertentu saja (lebih cepat saat development)
php artisan test --filter=OrderTest
```

### 1.4 Sebelum menjalankan perintah yang menyentuh database

Lakukan pengecekan dulu:

```bash
# Lihat koneksi dan nama database yang aktif
php artisan tinker --execute="dump(config('database.default')); dump(config('database.connections.'.config('database.default').'.database'));"
```

- Jika hasilnya `mysql` / `db_erp`, itu **database proyek**. Jangan jalankan perintah destruktif.
- Jika hasilnya `sqlite` / `:memory:`, itu **database test sementara**. Perintah schema di dalam test aman.
- Untuk migration baru, lihat SQL-nya dulu dengan `php artisan migrate --pretend` sebelum menjalankan `php artisan migrate`.
- Jika tidak yakin, **berhenti dan tanya pengguna**.

### 1.5 Jika butuh perubahan schema

- Buat **migration baru** dengan `php artisan make:migration ...`.
- Jangan mengedit migration lama yang sudah pernah dijalankan.
- Jalankan `php artisan migrate` (bukan `migrate:fresh`) hanya setelah memastikan target benar dan/atau pengguna menyetujui.
- Jangan menjalankan seeder di database dev tanpa izin.
- Test tetap memakai migrasi di koneksi `:memory:`, bukan database dev.

### 1.6 Jika terjadi kesalahan pada database

- **Berhenti segera.** Jangan mencoba menutupi atau "memperbaiki" dengan perintah destruktif lain.
- Laporkan ke pengguna: perintah apa yang dijalankan, database apa yang terkena, dan dampaknya.
- Bantu berikan langkah pemulihan (backup, restore, atau re-create data) jika memungkinkan.

---

## 2. Prinsip Kerja Agent

1. **Pahami sebelum mengubah.** Baca route, controller, service, model, migration, test, dan dokumentasi yang relevan.
2. **Buat rencana singkat** sebelum menulis kode: apa yang akan diubah, file mana, dan bagaimana mengujinya.
3. **Perubahan minimal dan terarah.** Jangan melakukan refactor besar, rename massal, atau perubahan gaya yang tidak diminta.
4. **Ikuti pola yang sudah ada.** Jangan memperkenalkan arsitektur atau library baru tanpa alasan kuat dan persetujuan pengguna.
5. **Jangan mengarang.** Jangan menebak nama kolom, route, relasi, atau response API. Verifikasi di kode/migration.
6. **Jangan menghapus kode orang lain** hanya karena terlihat tidak terpakai. Cek pemakaiannya dulu.
7. **Utamakan keamanan:** validasi input, otorisasi, tenant isolation, dan jangan pernah menulis secret ke kode/log.
8. **Verifikasi dengan bukti:** jalankan test dan formatter. Jangan mengklaim "sudah beres" tanpa menjalankannya.
9. **Jika ambigu atau berisiko, tanya.** Terutama untuk perubahan database, auth, pembayaran, dan data tenant.
10. **Jangan commit** kecuali pengguna meminta.

---

## 3. Alur Kerja Wajib

1. **Discover** — baca kode dan dokumentasi terkait.
2. **Plan** — tulis rencana singkat (termasuk file yang akan disentuh).
3. **Implement** — perubahan kecil, konsisten, dan type-safe.
4. **Test** — jalankan test yang relevan, lalu seluruh suite jika memungkinkan.
5. **Format** — jalankan Pint pada file yang berubah.
6. **Review** — baca `git diff` sendiri; pastikan tidak ada perubahan tak sengaja.
7. **Report** — jelaskan perubahan, hasil test, dan risiko.

Jika menemukan test yang sudah gagal sebelum perubahan:

- Jangan refresh database untuk memperbaikinya.
- Bedakan kegagalan **pre-existing** dari kegagalan akibat perubahanmu.
- Laporkan keduanya dengan jelas.

---

## 4. Konvensi Kode & Arsitektur

### 4.1 Gaya kode

- Ikuti PSR-12 dan gaya file di sekitarnya.
- Gunakan 4 spasi, type hint, dan return type pada method baru.
- Jangan menambahkan `declare(strict_types=1);` secara massal ke file lama; ikuti gaya file yang diedit.
- Gunakan nama kelas `PascalCase`, method `camelCase`, kolom database `snake_case`.
- Nama test: `test_snake_case_yang_menjelaskan_perilaku`.
- Komentar secukupnya; jelaskan **kenapa**, bukan sekadar mengulang kode.

### 4.2 Controller, service, dan model

- Controller harus tipis: validasi input, panggil service, kembalikan response.
- Business logic kompleks masuk ke `app/Services`.
- Operasi tulis yang melibatkan beberapa tabel harus memakai `DB::transaction`.
- Gunakan Eloquent relationship dan eager loading untuk menghindari N+1.
- Form Request boleh dipakai jika sudah menjadi pola; jangan memaksakan perubahan besar.
- Event/listener dipakai untuk efek samping (stok, activity log). Jangan memindahkan logika tanpa alasan.

### 4.3 Multi-tenant

- Model tenant-aware wajib memakai trait `BelongsToTenant`.
- Selalu pastikan query dibatasi `tenant_id`; jangan membocorkan data antar-tenant.
- `super_admin` memiliki `tenant_id = null` dan boleh lintas tenant.
- Saat membuat data baru, isi `tenant_id` dengan benar; jangan mengandalkan input pengguna mentah-mentah.
- Test tenant isolation wajib mempertimbangkan global scope dan user yang sedang login.

### 4.4 API

- Semua response API dalam JSON.
- Gunakan status code yang tepat: `200`, `201`, `204`, `401`, `403`, `404`, `422`.
- Pertahankan bentuk response yang sudah ada (termasuk envelope pagination).
- Jangan mengubah kontrak API tanpa memperbarui dokumentasi di `docs/api`.
- Endpoint terautentikasi memakai `auth:sanctum`.
- Jangan mengembalikan password, PIN, token, atau data sensitif lain.

### 4.5 Database & model

- Jangan mengedit migration lama; buat migration baru.
- Jangan mengubah nama kolom/tabel tanpa migration dan penyesuaian menyeluruh.
- Perhatikan cast dan tipe data (misalnya stok/uang) agar tidak terjadi pembulatan yang salah.
- Jangan memakai `DB::raw` dengan input pengguna tanpa binding.
- Jangan menyimpan uang sebagai string yang tidak tervalidasi.

### 4.6 Frontend

- Gunakan komponen Blade yang sudah ada (`admin-card`, `admin-table`, `admin-modal`, dll.) jika cocok.
- Ikuti gaya Tailwind dan layout admin yang ada.
- Jangan menambahkan framework JS baru tanpa persetujuan.
- Pastikan tampilan tetap responsif dan tidak merusak halaman lain.

### 4.7 Dependency & konfigurasi

- Jangan menambah dependency Composer/npm tanpa persetujuan.
- Jangan mengubah `composer.lock` secara manual.
- Jangan commit `.env`, kredensial, token, atau dump database.
- Jangan mengubah konfigurasi database test ke MySQL.

---

## 5. Aturan Testing

1. Gunakan **PHPUnit** (gaya class `Tests\TestCase`), bukan Pest, kecuali diminta.
2. Letakkan test di folder yang sesuai: `tests/Unit`, `tests/Feature`, atau `tests/Feature/Api`.
3. Gunakan factory untuk membuat data.
4. Test perilaku dan kontrak, bukan detail implementasi.
5. Setiap bug fix harus disertai test regresi.
6. Uji edge case penting: validasi, otorisasi, tenant isolation, stok, pembayaran, dan perhitungan total.
7. Jangan melemahkan assertion atau menghapus test hanya agar suite hijau.
8. Jangan mengandalkan urutan test atau data sisa dari test lain.
9. Jangan memakai database dev untuk test.
10. **Jangan me-refresh database proyek.** Gunakan SQLite `:memory:` yang sudah dikonfigurasi.

Perintah yang aman:

```bash
php artisan test
php artisan test --filter=OrderTest
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

Jika test membutuhkan schema di `:memory:`, pastikan trait yang dipakai hanya berjalan di koneksi in-memory. Jangan pernah mengarahkannya ke MySQL.

---

## 6. Perintah Berguna

| Kebutuhan | Perintah |
|---|---|
| Install dependency PHP | `composer install` |
| Install dependency JS | `npm install` |
| Jalankan development | `composer run dev` |
| Jalankan server saja | `php artisan serve` |
| Jalankan Vite | `npm run dev` |
| Build asset | `npm run build` |
| Jalankan test | `php artisan test` |
| Test tertentu | `php artisan test --filter=NamaTest` |
| Format kode | `vendor/bin/pint --dirty` |
| Lihat route | `php artisan route:list` |
| Lihat info app | `php artisan about` |
| Bersihkan cache | `php artisan optimize:clear` |
| Migration baru | `php artisan make:migration ...` |
| Jalankan migration baru | `php artisan migrate` (setelah target dipastikan) |
| Preview SQL migration | `php artisan migrate --pretend` |

**Jangan jalankan:** `migrate:fresh`, `migrate:refresh`, `migrate:reset`, `db:wipe`, atau seeder di database dev tanpa izin eksplisit.

---

## 7. Definition of Done

Sebuah task dianggap selesai jika:

- [ ] Perilaku yang diminta sudah diimplementasikan.
- [ ] Perubahan mengikuti arsitektur dan gaya kode yang ada.
- [ ] Test yang relevan lulus.
- [ ] Test regresi ditambahkan untuk bug fix.
- [ ] Tidak ada perintah yang me-refresh/merusak database proyek.
- [ ] Tidak ada perubahan tak sengaja pada `.env`, `phpunit.xml`, atau file milik pengguna.
- [ ] Pint dijalankan pada file yang berubah.
- [ ] `git diff` sudah direview.
- [ ] Dokumentasi diperbarui jika kontrak API/behavior berubah.
- [ ] Ringkasan perubahan dan hasil test dilaporkan.

Jika ada yang tidak bisa dijalankan, jelaskan alasannya. Jangan mengklaim sudah diuji jika belum.

---

## 8. Git & Commit

- Jangan commit atau push kecuali diminta.
- Jangan revert perubahan pengguna yang tidak terkait.
- Jangan menghapus file yang bukan bagian dari task.
- Jaga diff tetap fokus dan mudah direview.
- Gunakan conventional commit jika diminta commit:
  - `feat: ...`
  - `fix: ...`
  - `test: ...`
  - `docs: ...`
  - `refactor: ...`
- Jangan pernah commit `.env`, secret, token, kredensial, atau dump database.

---

## 9. Komunikasi

- Gunakan Bahasa Indonesia kecuali pengguna meminta bahasa lain.
- Jelaskan perubahan dengan jelas dan singkat, sebutkan path file.
- Saat ada trade-off, jelaskan pilihan dan risikonya.
- Saat blocked, jelaskan apa yang sudah dicoba dan apa yang dibutuhkan.
- **Selalu minta konfirmasi sebelum tindakan destruktif pada database.**

Format laporan akhir:

1. **Ringkasan** — apa yang diubah dan kenapa.
2. **File** — daftar file yang disentuh.
3. **Test** — perintah yang dijalankan dan hasilnya.
4. **Catatan** — risiko, keterbatasan, atau follow-up.

---

## 10. Checklist Cepat Sebelum Selesai

- [ ] Sudah baca kode dan konteks yang relevan.
- [ ] Perubahan minimal, tidak melebar ke file lain.
- [ ] Tidak ada `migrate:fresh`, `db:wipe`, `TRUNCATE`, atau `DROP` terhadap database proyek.
- [ ] Test berjalan di SQLite `:memory:`, bukan MySQL `db_erp`.
- [ ] Test bisa dijalankan berulang tanpa mengubah data dev.
- [ ] Test relevan lulus; kegagalan pre-existing dilaporkan.
- [ ] Pint dijalankan.
- [ ] `git diff` sudah direview.
- [ ] Laporan akhir jelas dan jujur.

---

**Ingat:** Agent yang baik bukan yang paling cepat menulis kode, tetapi yang paling bisa dipercaya menjaga data dan kualitas produk.
