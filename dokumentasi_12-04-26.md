# Dokumentasi Project WebWorkshop - 12 April 2026

## Ringkasan Hari Ini
Implementasi **Role-Based System (3 Role)** + **Midtrans Payment Integration** untuk studi kasus e-commerce sederhana (Vendor - Pelanggan).

---

## Struktur Role System

| Role | idrole | Folder Views | Redirect Login | Akses |
|------|--------|--------------|----------------|-------|
| **Admin** | 1 | `pages/` | `/dashboard` | Semua halaman existing (Kategori, Buku, Barang, PDF, Modul 4-5) |
| **Vendor** | 2 | `pages/vendor/` | `/vendor/dashboard` | Dashboard, Kelola Menu, Lihat Pesanan Masuk |
| **Pelanggan** | 3 | `pages/pelanggan/` | `/pelanggan/dashboard` | Dashboard (placeholder untuk Midtrans) |

---

## Database Structure

### Tabel Pesanan (Update Kolom)
Kolom yang sudah ada:
- `idpesanan` (PK)
- `iduser` (FK ke tabel user) ← **Tambahan hari ini**
- `order_id` (unique, untuk Midtrans)
- `nama`
- `timestamp`
- `total`
- `metode_bayar`
- `channel`
- `status_bayar` (boolean)
- `customer_email` ← **Tambahan hari ini**

**Query Alter yang sudah dijalankan:**
```sql
ALTER TABLE pesanan ADD COLUMN iduser INT NOT NULL AFTER idpesanan;
ALTER TABLE pesanan ADD CONSTRAINT fk_pesanan_user FOREIGN KEY (iduser) REFERENCES user(iduser);
CREATE INDEX idx_pesanan_iduser ON pesanan(iduser);
```

### Tabel Lengkap untuk Midtrans
- `vendor` - Data vendor
- `menu` - Menu makanan/minuman (FK: idvendor)
- `pesanan` - Header transaksi
- `detail_pesanan` - Item pesanan (junction: pesanan ↔ menu)

---

## File yang Sudah Dibuat/Diedit

### 1. Models (Sudah lengkap)
```
app/Models/
├── Vendor.php              ✓
├── Menu.php                ✓
├── Pesanan.php             ✓ (sudah include relasi user())
├── DetailPesanan.php       ✓
├── Kategori.php            (existing)
├── Buku.php                (existing)
├── Barang.php              (existing)
├── User.php                (existing)
```

### 2. Controllers (Baru)
```
app/Http/Controllers/
├── Vendor/
│   └── VendorController.php    ✓ (CRUD Menu, Lihat Pesanan)
├── Pelanggan/
│   └── PesananController.php   ✓ (Create Pesanan, Midtrans Snap)
├── Auth/
│   └── LoginController.php     ✓ (Edited: redirect 3 role)
├── SocialiteController.php       ✓ (Edited: redirect 3 role)
└── MidtransService.php         ✓ (di app/Services/)
```

### 3. Views (Struktur Folder)
```
resources/views/pages/
├── vendor/
│   └── dashboard.blade.php     ✓ (placeholder)
├── pelanggan/
│   └── dashboard.blade.php     ✓ (existing)
└── [lainnya existing]
```

### 4. Config & Routes
- `routes/web.php` ✓ (Sudah include routes untuk 3 role)
- `bootstrap/app.php` ✓ (Middleware `check.role` sudah terdaftar)
- `.env` ✓ (Tambahan: ASSET_URL untuk ngrok)

---

## Middleware Role Check

Sudah terdaftar di `bootstrap/app.php`:
```php
'check.role' => App\Http\Middleware\CheckRole::class,
```

Penggunaan di routes:
```php
Route::middleware(['auth', 'check_verif', 'check.role:1'])->group(...) // Admin
Route::middleware(['auth', 'check_verif', 'check.role:2'])->group(...) // Vendor
Route::middleware(['auth', 'check_verif', 'check.role:3'])->group(...) // Pelanggan
```

---

## Midtrans Integration (Progress)

### Sudah Selesai:
1. ✅ Install package: `composer require midtrans/midtrans-php`
2. ✅ MidtransService (`app/Services/MidtransService.php`)
   - `createSnapToken()` - Generate token popup
   - `handleNotification()` - Webhook handler
   - `checkTransactionStatus()` - Cek status
   - `generateOrderId()` - Generate unique ID
   - `getSnapUrl()` / `getClientKey()` - Helper

3. ✅ Pelanggan PesananController (`store()` method)
   - Simpan pesanan ke DB
   - Generate Snap Token
   - Return JSON untuk frontend

4. ✅ Setup ngrok untuk webhook (explained)

### Belum Dibuat (TODO Besok):
1. ⏳ Views Pelanggan:
   - `pages/pelanggan/pesanan/create.blade.php` - Form pemesanan + keranjang + Snap.js
   - `pages/pelanggan/pesanan/index.blade.php` - History pesanan
   - `pages/pelanggan/pesanan/show.blade.php` - Detail pesanan

2. ⏳ Webhook Endpoint:
   - Route: `POST /midtrans/notification`
   - Controller: Handle notification dari Midtrans

3. ⏳ Views Vendor:
   - `pages/vendor/menu/index.blade.php` - List menu
   - `pages/vendor/menu/create.blade.php` - Form tambah menu
   - `pages/vendor/menu/edit.blade.php` - Form edit menu
   - `pages/vendor/pesanan/index.blade.php` - List pesanan masuk
   - `pages/vendor/pesanan/show.blade.php` - Detail pesanan

4. ⏳ Update Sidebar Pelanggan (tambah menu Pesanan)

---

## Environment Configuration (.env)

### Midtrans (Sandbox)
```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SANITIZE=true
MIDTRANS_3DS=true
```

### Ngrok (Development Tunneling)
```env
APP_URL=https://abc123-def.ngrok.io
ASSET_URL=https://abc123-def.ngrok.io
```

---

## Ngrok Setup (Sudah Dijelaskan)

**Command:**
```bash
# Terminal 1
php artisan serve

# Terminal 2
ngrok http 8000
```

**Midtrans Dashboard Settings:**
- Payment Notification URL: `https://[ngrok-url]/midtrans/notification`
- Payment Redirect URL: `https://[ngrok-url]/pelanggan/pesanan`

---

## Flow Midtrans (Besok Diteruskan)

```
[Pelanggan] Pilih Menu → Keranjang → Checkout
                              ↓
                    [Laravel] Simpan Pesanan
                              ↓
                    [MidtransService] createSnapToken()
                              ↓
                    Return Snap Token + Client Key
                              ↓
                    [Frontend] Snap.js Popup
                              ↓
                    Customer Bayar (Gopay/VA/Kartu)
                              ↓
                    [Midtrans] Webhook ke Laravel
                              ↓
                    Update status_bayar = true
                              ↓
                    [Vendor] Lihat Pesanan Masuk
```

---

## Catatan Penting

1. **Model User**: Tidak pakai tabel role, hanya kolom `idrole` di tabel user
2. **Midtrans**: Masih pakai Sandbox environment
3. **URL Generation**: Sudah fix pakai ASSET_URL untuk ngrok
4. **Sidebar**: Sudah support 3 role dengan menu terpisah
5. **Controller Redirect**: LoginController & SocialiteController sudah redirect berdasarkan idrole

---

## Next Steps (Besok)

### Prioritas 1: Views Pelanggan (Midtrans)
- [ ] Form pemesanan dengan keranjang (JavaScript)
- [ ] Integrasi Snap.js popup
- [ ] Webhook handler untuk notification
- [ ] History pesanan dengan status pembayaran

### Prioritas 2: Views Vendor
- [ ] CRUD Menu (index, create, edit)
- [ ] Lihat pesanan yang masuk (detail_pesanan)
- [ ] Detail pesanan dengan item-itemnya

### Prioritas 3: Admin Monitoring (Opsional)
- [ ] Lihat semua pesanan dari semua pelanggan
- [ ] Filter by status pembayaran

---

## Troubleshooting Notes

### Masalah CSS di ngrok
**Solusi:** Tambahkan di `.env`
```env
ASSET_URL=https://[ngrok-url].ngrok.io
```
Lalu `php artisan config:clear`

### Midtrans SSL Error (Windows)
**Solusi:** Download cacert.pem dan tambahkan ke php.ini:
```ini
curl.cainfo = "C:/laragon/etc/ssl/cacert.pem"
openssl.cafile = "C:/laragon/etc/ssl/cacert.pem"
```

---

## File SQL untuk Database

File `alter_pesanan_iduser.sql` sudah dibuat di root project untuk alter tabel pesanan.

---

**Dokumentasi dibuat:** 12 April 2026  
**Status:** Siap lanjut besok tanpa setup ulang konteks
