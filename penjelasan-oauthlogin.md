# Penjelasan Alur Login Google OAuth dengan OTP Verification

Dokumentasi ini menjelaskan alur lengkap fitur login menggunakan Google OAuth (Socialite) dengan verifikasi OTP via email.

---

## Overview Alur

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 1. HALAMAN LOGIN                                                        │
│    - Tampil tombol "Sign in with Google"                                  │
│    - Klik → Redirect ke Google OAuth                                    │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ 2. GOOGLE OAUTH CALLBACK                                                │
│    - Socialite::driver('google')->user()                                │
│    - Cek apakah user sudah ada di database                              │
│    - Kalau belum: create user baru (status_verif = 'Verify')          │
│    - Kalau sudah: langsung login                                        │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ 3. CEK STATUS VERIFIKASI                                                │
│    - Cek status_verif === 'Active'                                      │
│    - Kalau Active: redirect ke dashboard sesuai role                    │
│    - Kalau bukan Active: kirim OTP dan redirect ke verify               │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ 4. HALAMAN VERIFIKASI OTP                                               │
│    - Input 6 digit OTP (6 box input)                                    │
│    - Cek OTP hash (md5) dan expiry (5 menit)                            │
│    - Kalau valid: status_verif = 'Active', redirect dashboard         │
│    - Kalau invalid: error message                                       │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Bagian 1: Halaman Login

### 1.1 View Login dengan Tombol Google

**File yang terkait:** `resources/views/auth/login.blade.php`

**Lampiran Kode (Baris 53-56):**
```html
<div class="mb-2 d-grid gap-2">
    <a href="{{ route('google-redirect') }}" 
       class="btn btn-block btn-google auth-form-btn" 
       style="background-color: #DB4437; color: white;">
        <i class="mdi mdi-google me-2"></i>Sign in with Google
    </a>
</div>
```

**Penjelasan Rinci:**

1. **Tombol Google OAuth:**
   ```html
   <a href="{{ route('google-redirect') }}">
   ```
   - `route('google-redirect')` → URL `/auth/google/redirect`
   - Mengarah ke SocialiteController::redirect()
   - Warna merah (#DB4437) adalah brand color Google

2. **Icon Google:**
   ```html
   <i class="mdi mdi-google me-2"></i>
   ```
   - Menggunakan Material Design Icons
   - `me-2` = margin-end (bootstrap spacing)

**Dioper kemana:**
Klik tombol → redirect ke route `google-redirect` → SocialiteController::redirect()

---

### 1.2 Route OAuth

**File yang terkait:** `routes/web.php`

**Lampiran Kode (Baris 14-15):**
```php
Route::get('auth/google/redirect', 
    [App\Http\Controllers\SocialiteController::class, 'redirect'])
    ->name('google-redirect');
Route::get('auth/google/callback', 
    [App\Http\Controllers\SocialiteController::class, 'callback'])
    ->name('google-callback');
```

**Penjelasan:**
- `/auth/google/redirect` - Inisiasi OAuth flow, redirect ke Google
- `/auth/google/callback` - Callback dari Google setelah user consent

---

## Bagian 2: SocialiteController - OAuth Flow

### 2.1 Method redirect() - Ke Google

**File yang terkait:** `app/Http/Controllers/SocialiteController.php`

**Lampiran Kode (Baris 15-17):**
```php
public function redirect() {
    return Socialite::driver('google')->redirect();
}
```

**Penjelasan Rinci:**

1. **Socialite Driver:**
   ```php
   Socialite::driver('google')
   ```
   - Load driver Google dari config/services.php
   - Mengambil GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET dari .env

2. **Redirect:**
   ```php
   ->redirect()
   ```
   - Generate URL Google OAuth dengan parameter:
     - `client_id` dari config
     - `redirect_uri` = callback URL
     - `scope` = email, profile
     - `response_type=code`
   - Redirect user ke halaman consent Google

**Dioper kemana:**
User di Google → pilih akun → consent → Google redirect ke `/auth/google/callback`

---

### 2.2 Method callback() - Proses Data Google

**File yang terkait:** `app/Http/Controllers/SocialiteController.php`

**Lampiran Kode (Baris 19-84):**
```php
public function callback() {
    // 1. Ambil data user dari Google
    $user_google = Socialite::driver('google')->stateless()->user();
    
    // 2. Cek apakah user sudah ada di database
    $user_db = User::where('google_id', $user_google->getId())->first();

    // 3. Kalau belum ada, buat user baru
    if (!$user_db) {
        $user_db = new User();
        $user_db->nama = $user_google->getName();
        $user_db->email = $user_google->getEmail();
        $user_db->google_id = $user_google->getId();
        $user_db->status_verif = 'Verify';  // Default: perlu verifikasi
        $user_db->save();
    }

    // 4. Login user ke session
    auth('web')->login($user_db);
    session()->regenerate();
    
    // 5. Set session data
    session()->put([
        'iduser' => $user_db->iduser,
        'nama' => $user_db->nama,
        'email' => $user_db->email,
    ]);

    // 6. Cek status verifikasi
    if (empty($user_db->status_verif) || $user_db->status_verif !== 'Active') {
        // Generate OTP dan kirim email
        $otp = rand(100000, 999999);
        $user_db->otp = md5($otp);
        $user_db->otp_expire_at = Carbon::now()->addMinutes(5);
        $user_db->save();

        // Kirim email
        Mail::to($user_db->email)->send(new SendEmail([
            'otp' => $otp,
            'userName' => $user_db->nama,
            'mailMessage' => 'Terima kasih telah mendaftar...',
            'subject' => 'Kode OTP Verifikasi'
        ]));

        return redirect()->route('index-verify');  // Ke halaman OTP
    }

    // 7. Kalau sudah Active, redirect ke dashboard sesuai role
    if ($user_db->idrole == 1) {
        return redirect()->route('dashboard');
    } elseif ($user_db->idrole == 2) {
        return redirect()->route('vendor.dashboard');
    } elseif ($user_db->idrole == 3) {
        return redirect()->route('pelanggan.dashboard');
    }

    return redirect()->route('dashboard');
}
```

**Penjelasan Rinci per Bagian:**

1. **Ambil Data Google User:**
   ```php
   $user_google = Socialite::driver('google')->stateless()->user();
   ```
   - `stateless()` - mode tanpa session state (simpler untuk callback)
   - `->user()` - return object dengan data:
     - `getId()` → Google ID unik
     - `getName()` → Nama lengkap
     - `getEmail()` → Email
     - `getAvatar()` → URL foto profil

2. **Cek User di Database:**
   ```php
   $user_db = User::where('google_id', $user_google->getId())->first();
   ```
   - Cari berdasarkan `google_id`
   - Google ID adalah identifier unik dari Google

3. **Create User Baru:**
   ```php
   if (!$user_db) {
       $user_db = new User();
       $user_db->nama = $user_google->getName();
       $user_db->email = $user_google->getEmail();
       $user_db->google_id = $user_google->getId();
       $user_db->status_verif = 'Verify';
       $user_db->save();
   }
   ```
   - Kalau user belum ada → create baru
   - `status_verif = 'Verify'` → perlu verifikasi OTP dulu
   - Password tidak diisi (login via OAuth)

4. **Login ke Session:**
   ```php
   auth('web')->login($user_db);
   session()->regenerate();
   ```
   - `auth('web')` - guard web (session-based)
   - `session()->regenerate()` - security best practice untuk prevent session fixation

5. **Set Session Data:**
   ```php
   session()->put([
       'iduser' => $user_db->iduser,
       'nama' => $user_db->nama,
       'email' => $user_db->email,
   ]);
   ```
   - Simpan data user ke session untuk akses di view

6. **Cek Status Verifikasi:**
   ```php
   if (empty($user_db->status_verif) || $user_db->status_verif !== 'Active')
   ```
   - Kalau `status_verif` kosong atau bukan 'Active' → perlu verifikasi

7. **Generate OTP:**
   ```php
   $otp = rand(100000, 999999);
   $user_db->otp = md5($otp);
   $user_db->otp_expire_at = Carbon::now()->addMinutes(5);
   ```
   - Random 6 digit (100000 - 999999)
   - Hash dengan MD5 (plaintext tidak disimpan)
   - Expire dalam 5 menit menggunakan Carbon

8. **Kirim Email:**
   ```php
   Mail::to($user_db->email)->send(new SendEmail([...]));
   ```
   - Kirim ke email dari Google
   - `SendEmail` - Mailable class
   - Parameter: OTP plain, nama, pesan, subject

9. **Redirect:**
   - Ke halaman OTP kalau perlu verifikasi
   - Ke dashboard sesuai role kalau sudah Active

**Dioper kemana:**
- Kalau perlu verifikasi → VerifController@index (halaman OTP)
- Kalau sudah Active → Dashboard sesuai role

---

## Bagian 3: VerifController - OTP Verification

### 3.1 Method index() - Tampilkan Halaman OTP

**File yang terkait:** `app/Http/Controllers/VerifController.php`

**Lampiran Kode (Baris 12-15):**
```php
public function index()
{
    return view('auth.verify');
}
```

**Penjelasan:**
- Simple method: return view `auth/verify.blade.php`
- Route ini pakai middleware `auth` (hanya user login yang bisa akses)

---

### 3.2 Halaman Verifikasi OTP

**File yang terkait:** `resources/views/auth/verify.blade.php`

**Lampiran Kode - Form OTP (Baris 85-104):**
```html
<form method="POST" action="{{ route('check-verify') }}" class="pt-3" id="otp-form">
  @csrf
  
  <div class="form-group">
    <label for="otp" class="mb-2">Kode OTP</label>
    <div class="otp-input-group" id="otp-inputs">
      <!-- 6 input box untuk OTP -->
      <input type="text" class="otp-input" maxlength="1" inputmode="numeric" placeholder="0">
      <input type="text" class="otp-input" maxlength="1" inputmode="numeric" placeholder="0">
      <input type="text" class="otp-input" maxlength="1" inputmode="numeric" placeholder="0">
      <input type="text" class="otp-input" maxlength="1" inputmode="numeric" placeholder="0">
      <input type="text" class="otp-input" maxlength="1" inputmode="numeric" placeholder="0">
      <input type="text" class="otp-input" maxlength="1" inputmode="numeric" placeholder="0">
    </div>
    <!-- Hidden input untuk gabungkan 6 digit -->
    <input type="hidden" name="otp" id="otp-value">
  </div>

  <div class="mt-3 d-grid gap-2">
    <button type="submit" class="btn btn-block btn-gradient-primary">
      VERIFIKASI
    </button>
  </div>
</form>
```

**Lampiran Kode - JavaScript OTP Input (Baris 140-201):**
```javascript
const otpInputs = document.querySelectorAll('.otp-input');
const otpValue = document.getElementById('otp-value');
const otpForm = document.getElementById('otp-form');

otpInputs.forEach((input, index) => {
  // Handle input number
  input.addEventListener('input', (e) => {
    // Only allow numbers
    if (!/^\d$/.test(e.target.value)) {
      e.target.value = '';
      return;
    }

    // Combine all OTP values
    const otp = Array.from(otpInputs).map(i => i.value).join('');
    otpValue.value = otp;

    // Move to next input
    if (e.target.value && index < otpInputs.length - 1) {
      otpInputs[index + 1].focus();
    }
  });

  // Handle backspace dan arrow keys
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Backspace') {
      e.target.value = '';
      if (index > 0) {
        otpInputs[index - 1].focus();
      }
    }
    if (e.key === 'ArrowLeft' && index > 0) {
      otpInputs[index - 1].focus();
    }
    if (e.key === 'ArrowRight' && index < otpInputs.length - 1) {
      otpInputs[index + 1].focus();
    }
  });

  // Handle paste OTP
  input.addEventListener('paste', (e) => {
    e.preventDefault();
    const pastedData = (e.clipboardData || window.clipboardData).getData('text');
    const digits = pastedData.replace(/\D/g, '').split('');
    
    digits.forEach((digit, i) => {
      if (i < otpInputs.length) {
        otpInputs[i].value = digit;
      }
    });

    const otp = Array.from(otpInputs).map(i => i.value).join('');
    otpValue.value = otp;
    
    // Auto submit kalau 6 digit lengkap
    if (digits.length === otpInputs.length) {
      otpForm.submit();
    } else {
      otpInputs[Math.min(digits.length, otpInputs.length - 1)].focus();
    }
  });
});
```

**Penjelasan Rinci:**

1. **6 Input Box:**
   - Tiap box `maxlength="1"` - hanya 1 digit
   - `inputmode="numeric"` - keyboard numeric di mobile
   - CSS membuat tampilan kotak per digit

2. **Validasi Numeric:**
   ```javascript
   if (!/^\d$/.test(e.target.value)) {
       e.target.value = '';
       return;
   }
   ```
   - Regex `/^\d$/` - hanya 1 digit angka
   - Kalau bukan angka → clear input

3. **Auto-focus Logic:**
   ```javascript
   if (e.target.value && index < otpInputs.length - 1) {
       otpInputs[index + 1].focus();
   }
   ```
   - Setelah ketik angka, otomatis pindah ke box berikutnya
   - Backspace → pindah ke box sebelumnya
   - Arrow keys → navigasi manual

4. **Paste OTP:**
   ```javascript
   const digits = pastedData.replace(/\D/g, '').split('');
   ```
   - Regex `/\D/g` - remove semua non-digit
   - Distribusikan ke tiap input box
   - Kalau 6 digit lengkap → auto submit

5. **Hidden Input:**
   ```javascript
   const otp = Array.from(otpInputs).map(i => i.value).join('');
   otpValue.value = otp;
   ```
   - Gabungkan 6 digit jadi 1 string
   - Kirim ke server via hidden input `otp-value`

---

### 3.3 Method checkOtp() - Validasi OTP

**File yang terkait:** `app/Http/Controllers/VerifController.php`

**Lampiran Kode (Baris 17-49):**
```php
public function checkOtp(Request $request)
{
    $request->validate([
        'otp' => 'required|digits:6',
    ]);

    $user = $request->user();

    Log::info('OTP Verification attempt - Email: ' . $user->email . ', OTP input: ' . $request->otp);

    // 1. Check expiration FIRST
    if (Carbon::now() > $user->otp_expire_at) {
        Log::error('OTP expired - Email: ' . $user->email);
        return back()->withErrors(['otp' => 'Kode OTP sudah expired'])->withInput();
    }

    // 2. Check hash
    $otp_hash = md5($request->otp);
    Log::info('OTP Hash comparison - Input hash: ' . $otp_hash . ', DB hash: ' . $user->otp);
    
    if ($user->otp !== $otp_hash) {
        Log::error('OTP mismatch - Email: ' . $user->email);
        return back()->withErrors(['otp' => 'Kode OTP salah'])->withInput();
    }

    // 3. Success - mark as verified
    $user->status_verif = 'Active';
    $user->save();

    Log::info('OTP verified successfully - Email: ' . $user->email);

    return redirect()->route('dashboard');
}
```

**Penjelasan Rinci:**

1. **Validasi Input:**
   ```php
   $request->validate(['otp' => 'required|digits:6']);
   ```
   - Wajib diisi
   - Harus 6 digit angka
   - Laravel auto-return error kalau tidak valid

2. **Ambil User:**
   ```php
   $user = $request->user();
   ```
   - Dari session auth (user yang sedang login)
   - Middleware `auth` memastikan user sudah login

3. **Cek Expiration:**
   ```php
   if (Carbon::now() > $user->otp_expire_at)
   ```
   - Bandingkan waktu sekarang dengan waktu expire
   - Cek expire DULU sebelum hash comparison (security)
   - Kalau expired → error

4. **Hash Comparison:**
   ```php
   $otp_hash = md5($request->otp);
   if ($user->otp !== $otp_hash)
   ```
   - MD5 hash dari input user
   - Bandingkan dengan hash di database
   - MD5 cukup untuk OTP (bukan untuk password)

5. **Mark as Verified:**
   ```php
   $user->status_verif = 'Active';
   $user->save();
   ```
   - Update status jadi 'Active'
   - User tidak perlu verifikasi lagi di login berikutnya

**Dioper kemana:**
- Kalau sukses → redirect ke dashboard
- Kalau gagal → back ke halaman OTP dengan error message

---

### 3.4 Method resendOtp() - Kirim Ulang OTP

**File yang terkait:** `app/Http/Controllers/VerifController.php`

**Lampiran Kode (Baris 51-83):**
```php
public function resendOtp(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return back()->withErrors(['error' => 'Silakan login terlebih dahulu']);
    }

    Log::info('Resend OTP - Email: ' . $user->email);

    $otp = rand(100000, 999999);
    $user->otp = md5($otp);
    $user->otp_expire_at = Carbon::now()->addMinutes(5);
    $save_result = $user->save();

    Log::info('Save OTP - Result: ' . ($save_result ? 'SUCCESS' : 'FAILED'));

    try {
        $mailableClass = 'App\\Mail\\SendEmail';
        Mail::to($user->email)->send(new $mailableClass([
            'otp' => $otp,
            'userName' => $user->nama,
            'mailMessage' => 'Kode OTP Anda telah dikirim ulang...',
            'subject' => 'Kode OTP Verifikasi (Pengiriman Ulang)'
        ]));
        Log::info('Resend email sent successfully');
        return back()->with('success', 'Kode OTP telah dikirim ulang');
    } catch (\Exception $e) {
        Log::error('Resend email failed: ' . $e->getMessage());
        return back()->withErrors(['error' => 'Gagal mengirim OTP']);
    }
}
```

**Penjelasan:**
- Generate OTP baru (random 6 digit)
- Update hash dan expire time di database
- Kirim email dengan subject "Pengiriman Ulang"
- Return success message atau error

---

## Bagian 4: Email Template

### 4.1 SendEmail Mailable Class

**File yang terkait:** `app/Mail/SendEmail.php`

**Lampiran Kode:**
```php
class SendEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public $message)
    {
        // $message berisi array: otp, userName, mailMessage, subject
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('ihya@mboy.com', 'Ihya'),
            replyTo: [new Address('ihya@mboy.com','Ihya')],
            subject: $this->message['subject'] ?? 'Notifikasi',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.mail',
            with: $this->message,
        );
    }
}
```

**Penjelasan:**
- `public $message` - property public untuk akses di view
- `envelope()` - konfigurasi from, reply-to, subject
- `content()` - view `resources/views/mails/mail.blade.php`

---

### 4.2 Template Email

**File yang terkait:** `resources/views/mails/mail.blade.php`

**Lampiran Kode - OTP Section:**
```html
<div class="otp-box">
    <div class="otp-label">Kode Verifikasi OTP</div>
    <div class="otp-code">{{ $otp ?? '------' }}</div>
    <div class="otp-validity">Kode berlaku selama 5 menit</div>
</div>

<div class="warning-box">
    <strong>⚠️ Keamanan Penting</strong>
    Jangan bagikan kode OTP ini kepada siapapun.
</div>
```

**Penjelasan:**
- `{{ $otp }}` - menampilkan OTP plain (6 digit)
- CSS inline untuk compatibility email client
- Warning security di tampilkan jelas

---

## Bagian 5: Model dan Database

### 5.1 Model User

**File yang terkait:** `app/Models/User.php`

**Lampiran Kode:**
```php
class User extends Authenticatable
{
    protected $table = 'user';
    protected $primaryKey = 'iduser';
    public $timestamps = false;

    protected $fillable = [
        'nama', 'email', 'password', 'google_id', 
        'otp', 'otp_expire_at', 'status_verif', 'idrole'
    ];
}
```

**Penjelasan Kolom OAuth/OTP:**

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `google_id` | VARCHAR | ID unik dari Google |
| `otp` | VARCHAR | MD5 hash dari OTP (6 digit) |
| `otp_expire_at` | DATETIME | Waktu expire OTP (+5 menit) |
| `status_verif` | VARCHAR | 'Verify' / 'Active' |
| `idrole` | INT | 1=Admin, 2=Vendor, 3=Pelanggan |

---

## Ringkasan Alur Lengkap (Flowchart)

```
┌─────────────────────────────────────────────────────────────────────────┐
│ USER BUKA /login                                                        │
│ • Klik "Sign in with Google"                                            │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ GOOGLE OAUTH                                                            │
│ • redirect() → Google Consent Screen                                    │
│ • User pilih akun Google                                                │
│ • Google redirect ke /auth/google/callback                              │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ CALLBACK PROCESSING                                                     │
│ • Socialite::driver('google')->user()                                   │
│ • Get: name, email, google_id                                           │
│ • Cek User::where('google_id', $id)                                     │
│   ├── Belum ada → Create user (status_verif = 'Verify')                 │
│   └── Sudah ada → Skip create                                           │
│ • auth()->login($user)                                                  │
│ • Cek status_verif                                                      │
│   ├── 'Active' → Redirect dashboard sesuai role                         │
│   └── Bukan 'Active' → Generate OTP & Kirim Email                     │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓ (kalau perlu verifikasi)
┌─────────────────────────────────────────────────────────────────────────┐
│ GENERATE & KIRIM OTP                                                    │
│ • $otp = rand(100000, 999999)                                            │
│ • $user->otp = md5($otp)                                                 │
│ • $user->otp_expire_at = Carbon::now()->addMinutes(5)                   │
│ • $user->save()                                                          │
│ • Mail::to($user->email)->send(new SendEmail([...]))                      │
│ • redirect()->route('index-verify')                                     │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ HALAMAN VERIFIKASI OTP                                                  │
│ • Form dengan 6 input box (1 digit per box)                             │
│ • JavaScript: auto-focus, paste support, arrow keys                     │
│ • Submit ke /verify (POST)                                              │
└─────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────────┐
│ CHECK OTP                                                               │
│ • Validate: required|digits:6                                           │
│ • Cek expiration: Carbon::now() > otp_expire_at?                        │
│   └── Ya → Error "OTP expired"                                            │
│ • Hash input: md5($request->otp)                                        │
│ • Bandingkan dengan $user->otp                                          │
│   └── Tidak cocok → Error "OTP salah"                                   │
│ • Cocok → $user->status_verif = 'Active'                                │
│ • $user->save()                                                          │
│ • redirect()->route('dashboard')                                        │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Daftar File Penting

| File | Fungsi |
|------|--------|
| `app/Http/Controllers/SocialiteController.php` | Handle Google OAuth redirect & callback |
| `app/Http/Controllers/VerifController.php` | Handle OTP verification & resend |
| `app/Mail/SendEmail.php` | Mailable class untuk kirim email OTP |
| `app/Models/User.php` | Model dengan kolom google_id, otp, status_verif |
| `resources/views/auth/login.blade.php` | Halaman login dengan tombol Google |
| `resources/views/auth/verify.blade.php` | Halaman input OTP (6 digit) |
| `resources/views/mails/mail.blade.php` | Template email OTP |
| `routes/web.php` | Route definitions |

---

## Catatan Penting untuk Pemula

1. **Google Cloud Console Setup:**
   - Buat project di console.cloud.google.com
   - Enable Google+ API
   - Buat OAuth 2.0 credentials
   - Set authorized redirect URI: `http://localhost:8000/auth/google/callback`

2. **Install Socialite:**
   ```bash
   composer require laravel/socialite
   ```

3. **MD5 untuk OTP:**
   - MD5 bukan untuk password (gunakan bcrypt)
   - OK untuk OTP karena short-lived (5 menit)
   - OTP plain tidak disimpan, hanya hash

4. **Security:**
   - `session()->regenerate()` - prevent session fixation
   - OTP expire 5 menit - prevent brute force
   - Middleware `auth` untuk route verify - protect access

5. **Email Testing:**
   - Gunakan Mailtrap (mailtrap.io) untuk testing
   - Email tidak benar-benar terkirim, ditangkap di Mailtrap inbox

6. **Status Verifikasi:**
   - `Verify` = perlu verifikasi OTP
   - `Active` = sudah terverifikasi, bisa akses dashboard

---

**Dokumentasi ini menjelaskan lengkap alur Google OAuth + OTP Verification di Laravel dengan Socialite.**
