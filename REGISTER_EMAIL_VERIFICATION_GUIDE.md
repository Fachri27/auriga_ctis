# Fitur Register dengan Email Verification menggunakan Livewire

## 📋 Ringkasan

Implementasi lengkap fitur Register dengan Email Verification menggunakan Laravel 11 + Livewire tanpa API (pure Laravel). User harus memverifikasi email sebelum dapat mengakses fitur-fitur di aplikasi.

---

## 🏗️ Arsitektur & Komponen

### 1. **Livewire Component: Register**

**File:** `app/Livewire/Auth/Register.php`

**Fungsi:**

- Menangani form registrasi dengan real-time validation
- Menyimpan data user ke database
- Mengirim email verification notification
- Auto-login user setelah registrasi
- Redirect ke email verification notice

**Property Penting:**

```php
public string $name = '';              // Nama user
public string $email = '';             // Email user
public string $password = '';          // Password
public string $password_confirmation; // Konfirmasi password
public bool $showPassword = false;     // Toggle tampilan password
public bool $isSubmitting = false;     // State loading
```

**Method Utama:**

- `register()` - Handle submit form
- `resetForm()` - Reset semua field
- `togglePasswordVisibility()` - Toggle show/hide password
- `checkEmail()` - Real-time validation email

**Validation Rules:**

```php
'name' => ['required', 'string', 'max:255']
'email' => ['required', 'string', 'email', 'unique:users,email']
'password' => ['required', 'confirmed', Password::defaults()]
```

---

### 2. **Blade View: Register Form**

**File:** `resources/views/livewire/auth/register.blade.php`

**Fitur:**

- ✅ Form validation dengan error messages
- ✅ Password visibility toggle
- ✅ Loading state indicator
- ✅ Real-time form validation
- ✅ Responsive design dengan TailwindCSS
- ✅ Professional UI dengan gradient

**Komponen Form:**

- Nama lengkap dengan validation
- Email dengan check icon jika valid
- Password dengan toggle visibility
- Confirm password
- Submit button dengan loading animation
- Reset button

---

### 3. **User Model dengan Email Verification**

**File:** `app/Models/User.php`

**Perubahan:**

```php
// Implement interface MustVerifyEmail
use Illuminate\Contracts\Auth\MustVerifyEmail;
class User extends Authenticatable implements MustVerifyEmail
```

**Casts:**

```php
'email_verified_at' => 'datetime',
'password' => 'hashed',
```

**Method Otomatis yang Tersedia:**

- `hasVerifiedEmail()` - Check apakah email sudah verified
- `markEmailAsVerified()` - Mark email sebagai verified
- `sendEmailVerificationNotification()` - Send verification email

---

### 4. **Notification: Email Verification**

**File:** `app/Notifications/VerifyEmailNotification.php`

**Fungsi:**

- Custom notification untuk email verification
- Pesan yang friendly dalam Bahasa Indonesia
- Format yang professional dan user-friendly

**Isi Email:**

- Greeting: "Halo [Nama User]"
- Instruksi verifikasi
- Button "Verifikasi Email" dengan link
- Link berlaku 24 jam
- Warning jika tidak mendaftar
- Info kontak support

---

### 5. **Template Email HTML**

**File:** `resources/views/emails/verify-email.blade.php`

**Fitur:**

- ✅ Responsive email design
- ✅ Professional styling dengan gradient
- ✅ Clear CTA button
- ✅ Alternative link jika button tidak bekerja
- ✅ Info boxes dengan iconography
- ✅ Footer dengan copyright

**Sections:**

- Header dengan brand color
- Greeting personal
- Message dan instruksi
- CTA Button besar
- Alternative verification link
- Info boxes (24 jam expire)
- Important security notice
- Help/support section
- Professional footer

---

### 6. **Email Verification Verification Notice**

**File:** `resources/views/auth/verify-email.blade.php`

**Fungsi:**

- Tampilan setelah register berhasil
- Instruksi step-by-step untuk verifikasi
- Button untuk kirim ulang email
- Help section untuk troubleshooting
- Logout button

**Fitur:**

- ✅ Clear visual hierarchy
- ✅ Status message untuk resend
- ✅ Numbered instructions
- ✅ Helper tips untuk spam folder
- ✅ Professional design dengan TailwindCSS

---

### 7. **Controller: Registered User**

**File:** `app/Http/Controllers/Auth/RegisteredUserController.php`

**Perubahan:**

- Sekarang hanya menampilkan view dengan Livewire component
- Logic registration di-handle oleh Livewire component
- Lebih clean dan maintainable

```php
public function create()
{
    return view('auth.register');
}
```

---

### 8. **Middleware: Ensure Email Verified**

**File:** `app/Http/Middleware/EnsureEmailIsVerified.php`

**Fungsi:**

- Memproteksi routes yang memerlukan verified email
- Redirect ke verification notice jika email belum verified
- Allow unverified user untuk akses verification routes

**Usage:**

```php
Route::middleware('verified')->group(function () {
    // Routes yang require verified email
});
```

---

### 9. **Routes Configuration**

**File:** `routes/auth.php`

**Struktur:**

```
Guest Routes (tidak login)
├── GET /register - Tampil form register
├── POST /register - Submit form register
├── GET /login - Tampil form login
└── POST /login - Submit form login

Authenticated Routes (sudah login)
├── Verification Routes (semua user)
│   ├── GET /verify-email - Verification notice
│   ├── GET /verify-email/{id}/{hash} - Verify link
│   └── POST /email/verification-notification - Resend email
│
└── Verified Routes (hanya verified users)
    ├── GET /confirm-password
    ├── POST /confirm-password
    ├── PUT /password
    └── POST /logout
```

**Middleware Protection:**

- `guest` - Hanya untuk yang belum login
- `auth` - Hanya untuk yang sudah login
- `verified` - Hanya untuk yang sudah verified email
- `signed` - Untuk verify email link (signed URL)
- `throttle:6,1` - Rate limit untuk email verification

---

## 🔄 User Flow

### 1. **Registration Flow**

```
User buka /register
    ↓
Lihat Livewire Register Form
    ↓
Input: Name, Email, Password, Password Confirmation
    ↓
Livewire validate real-time
    ↓
Submit form
    ↓
Livewire component:
  - Validate input
  - Hash password
  - Create user di database
  - Fire Registered event (auto-send verification email)
  - Auto-login user
  - Redirect ke /verify-email
```

### 2. **Email Verification Flow**

```
User lihat verification notice
    ↓
Buka email di inbox
    ↓
Klik "Verifikasi Email" di email
    ↓
Laravel process verification link
    ↓
Set email_verified_at di database
    ↓
Redirect ke dashboard
    ↓
User sekarang fully verified
```

### 3. **Resend Verification Email**

```
User belum terima email
    ↓
Di halaman /verify-email, klik "Kirim Ulang Email Verifikasi"
    ↓
Livewire submit POST ke /email/verification-notification
    ↓
Send email baru dengan link baru
    ↓
Tamapilkan success message
    ↓
User dapat klik link di email baru
```

### 4. **Login Unverified User (Prevention)**

```
Unverified user coba login
    ↓
POST /login dengan email & password benar
    ↓
User ter-authenticate
    ↓
Middleware 'verified' cek: hasVerifiedEmail()?
    ↓
Jika FALSE → Redirect ke /verify-email
    ↓
Tampilkan notification: "Please verify your email first"
```

---

## 🔐 Security Features

### 1. **Email Verification Link Protection**

- Signed URL dengan SHA-256 hash
- Tidak bisa di-tamper tanpa secret key
- Rate limit: 6 attempts per 1 minute

### 2. **Password Security**

- Password di-hash menggunakan bcrypt
- Password confirmation required
- Minimum 8 characters
- Must contain: uppercase, lowercase, number, symbol

### 3. **Unverified User Prevention**

- User tidak bisa akses protected routes tanpa verified email
- Middleware 'verified' mengecek `email_verified_at`
- Automatic redirect ke verification page

### 4. **Email Uniqueness**

- Email harus unique di database
- Real-time validation di client-side
- Server-side validation juga

### 5. **CSRF Protection**

- Semua form dengan `@csrf`
- Livewire auto-include CSRF token
- Safe dari CSRF attack

---

## 📧 Email Configuration

### Konfigurasi di `.env`

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Aplikasi Anda"
```

### Provider yang Supported

- SMTP (Mailtrap, SendGrid, AWS SES, dll)
- Mailgun
- Postmark
- Sendmail
- Log (untuk development)

### Testing Email (Development)

**Gunakan Mailtrap untuk testing:**

1. Buat akun di [mailtrap.io](https://mailtrap.io)
2. Copy credentials ke `.env`
3. Run: `php artisan tinker`
4. Trigger email: `Auth::user()->sendEmailVerificationNotification()`

---

## 🎨 UI/UX Features

### Register Form

- ✅ Gradient background
- ✅ Smooth animations
- ✅ Real-time validation feedback
- ✅ Password visibility toggle
- ✅ Loading spinner
- ✅ Error messages with icons
- ✅ Success indicators

### Verification Notice

- ✅ Clear instructions
- ✅ Step-by-step guide
- ✅ Visual status indicators
- ✅ Numbered steps
- ✅ Help section
- ✅ Troubleshooting tips

### Email Template

- ✅ Responsive design
- ✅ Brand colors
- ✅ Clear CTA button
- ✅ Alternative link
- ✅ Professional layout
- ✅ Dark/Light mode ready

---

## 🚀 Cara Penggunaan

### 1. **Setup Database**

```bash
php artisan migrate
```

### 2. **Akses Register Page**

```
http://localhost:8000/register
```

### 3. **Register User Baru**

- Isi form dengan data
- Submit
- Auto-redirect ke verification notice

### 4. **Verify Email**

- Buka email yang dikirim
- Klik verification link
- Email terverifikasi
- Redirect ke dashboard

### 5. **Test Unverified Prevention**

- Register user baru
- Jangan verify email
- Coba logout lalu login
- System akan redirect ke verification page

---

## 📝 Customization

### 1. **Ubah Pesan Email Notification**

Edit: `app/Notifications/VerifyEmailNotification.php`

```php
protected function buildMailMessage($url)
{
    return (new MailMessage)
        ->subject('Custom Subject')
        ->line('Custom message')
        ->action('Custom Button Text', $url);
}
```

### 2. **Ubah Template Email HTML**

Edit: `resources/views/emails/verify-email.blade.php`

- Ubah warna, font, layout sesuai brand

### 3. **Ubah Validation Messages**

Edit: `app/Livewire/Auth/Register.php`

```php
protected $messages = [
    'email.unique' => 'Custom unique message',
    // ...
];
```

### 4. **Tambah Routes yang Require Verification**

Edit: `routes/auth.php`

```php
Route::middleware('verified')->group(function () {
    // Add your protected routes here
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});
```

### 5. **Ubah Redirect Setelah Verify**

Edit: `app/Http/Controllers/Auth/VerifyEmailController.php`

```php
// Default redirect ke dashboard
return redirect()->intended(route('dashboard', absolute: false));
```

---

## 🧪 Testing

### Test Manual Flow

```bash
# 1. Register user baru
# 2. Check email di Mailtrap
# 3. Klik verification link
# 4. Verify success
```

### Test Unverified Login Prevention

```bash
# 1. Register user
# 2. Logout
# 3. Login dengan email tersebut
# 4. Coba akses protected route
# 5. Akan redirect ke verification page
```

### Test Resend Email

```bash
# 1. Register user
# 2. Di verification page, klik "Kirim Ulang"
# 3. Check email menerima link baru
# 4. Verify dengan link baru
```

---

## 📚 File Structure

```
app/
├── Livewire/
│   └── Auth/
│       └── Register.php                    # Livewire component
├── Http/
│   ├── Controllers/
│   │   └── Auth/
│   │       └── RegisteredUserController.php # Simplified controller
│   └── Middleware/
│       └── EnsureEmailIsVerified.php       # Custom middleware
├── Mail/
│   └── VerifyEmailMail.php                 # Custom mailable (optional)
├── Models/
│   └── User.php                            # Implements MustVerifyEmail
└── Notifications/
    └── VerifyEmailNotification.php         # Custom notification

resources/
├── views/
│   ├── auth/
│   │   ├── register.blade.php              # Register layout
│   │   └── verify-email.blade.php          # Verification notice
│   ├── emails/
│   │   └── verify-email.blade.php          # Email template
│   └── livewire/
│       └── auth/
│           └── register.blade.php          # Register form component

routes/
└── auth.php                                # Auth routes with middleware

config/
└── auth.php                                # Auth configuration
```

---

## ✅ Checklist Implementasi

- [x] User Model implements MustVerifyEmail
- [x] Livewire Register Component dengan validation
- [x] Register form dengan TailwindCSS
- [x] Email verification notification
- [x] Custom email template HTML
- [x] Verification notice page
- [x] Middleware untuk protected routes
- [x] Prevention unverified login
- [x] Routes dengan middleware setup
- [x] Real-time validation
- [x] Error handling
- [x] Loading states
- [x] Success messages

---

## 🐛 Troubleshooting

### Email tidak terkirim

- Cek `.env` MAIL configuration
- Cek credentials di mail provider
- Cek spam folder
- Use `php artisan tinker` untuk manual test

### User tidak bisa verify

- Cek link di email sudah correct
- Check URL signature di app key
- Verify link expire 24 jam
- Cek database email_verified_at nullable

### Livewire component error

- Run: `php artisan livewire:publish --assets`
- Clear cache: `php artisan cache:clear`
- Check namespace: `App\Livewire\Auth\Register`

---

## 📞 Support

Untuk bantuan lebih lanjut, silakan hubungi tim development atau refer ke dokumentasi:

- Laravel: https://laravel.com/docs
- Livewire: https://livewire.laravel.com
- Laravel Email Verification: https://laravel.com/docs/authentication#verifying-emails

---

**Versi:** 1.0
**Last Updated:** 2024
**Language:** Indonesian
