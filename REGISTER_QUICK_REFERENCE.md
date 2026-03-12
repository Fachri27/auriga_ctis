# Quick Reference: Register Email Verification Implementation

## 🎯 Ringkasan Perubahan

Semua file yang telah dibuat/dimodifikasi untuk mengimplementasikan Email Verification:

---

## 📂 File-File Utama

### 1️⃣ Livewire Component (Logic)

```
app/Livewire/Auth/Register.php
```

- Handle form submission
- Validation
- Create user & send email
- Auto-login & redirect

### 2️⃣ Livewire View (UI)

```
resources/views/livewire/auth/register.blade.php
```

- Form fields dengan validation
- TailwindCSS styling
- Loading state
- Error messages

### 3️⃣ User Model

```
app/Models/User.php
```

- Implements MustVerifyEmail interface
- Added email_verified_at cast

### 4️⃣ Notification

```
app/Notifications/VerifyEmailNotification.php
```

- Custom email notification
- Indonesian message
- Professional format

### 5️⃣ Email Template

```
resources/views/emails/verify-email.blade.php
```

- Beautiful HTML email
- Responsive design
- Professional styling

### 6️⃣ Verification Notice

```
resources/views/auth/verify-email.blade.php
```

- Post-registration page
- Instructions & help
- Resend email button

### 7️⃣ Controller

```
app/Http/Controllers/Auth/RegisteredUserController.php
```

- Simplified to just render view
- Logic in Livewire component

### 8️⃣ Middleware

```
app/Http/Middleware/EnsureEmailIsVerified.php
```

- Protect routes requiring verification
- Redirect unverified users

### 9️⃣ Routes

```
routes/auth.php
```

- Added 'verified' middleware
- Protected routes only for verified users

---

## 🔧 Installation Steps

### Step 1: Database Migration (sudah ada)

Pastikan `users` table punya kolom `email_verified_at`:

```sql
ALTER TABLE users ADD COLUMN email_verified_at TIMESTAMP NULL;
```

### Step 2: Environment Configuration

```env
# .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Aplikasi Anda"
```

### Step 3: Publish Assets (jika diperlukan)

```bash
php artisan livewire:publish --assets
php artisan vendor:publish --tag=laravel-mail
```

### Step 4: Clear Cache

```bash
php artisan cache:clear
php artisan config:cache
```

---

## 🧪 Testing Email Verification

### Test dengan Mailtrap

1. Buat akun di https://mailtrap.io
2. Copy credentials ke `.env`
3. Register user di http://localhost:8000/register
4. Cek email di Mailtrap dashboard
5. Klik verification link

### Test Unverified Prevention

```bash
# 1. Register dengan email test@example.com
# 2. Logout
# 3. Login dengan email tersebut
# 4. System akan redirect ke /verify-email
# 5. Verify email
# 6. Sekarang bisa akses protected routes
```

---

## 🌐 URL Routes

| Method | Route                              | Middleware     | Purpose                  |
| ------ | ---------------------------------- | -------------- | ------------------------ |
| GET    | `/register`                        | guest          | Show register form       |
| POST   | `/register`                        | guest          | Process registration     |
| GET    | `/login`                           | guest          | Show login form          |
| POST   | `/login`                           | guest          | Process login            |
| GET    | `/verify-email`                    | auth           | Show verification notice |
| GET    | `/verify-email/{id}/{hash}`        | auth, signed   | Verify email link        |
| POST   | `/email/verification-notification` | auth           | Resend email             |
| PUT    | `/password`                        | auth, verified | Update password          |
| POST   | `/logout`                          | auth           | Logout user              |

---

## 🔐 Middleware Protection

### Routes Protected dengan 'verified'

Semua route dalam grup ini hanya accessible oleh users dengan verified email:

```php
Route::middleware('verified')->group(function () {
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show']);
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update']);
});
```

### Tambah Protected Routes

Untuk menambah route yang require verified email:

```php
// Di routes/auth.php atau routes/web.php
Route::middleware('verified')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
});
```

---

## 📧 Customizing Email

### Ubah Pesan Notification

File: `app/Notifications/VerifyEmailNotification.php`

```php
protected function buildMailMessage($url)
{
    return (new MailMessage)
        ->subject('Verifikasi Email - Aplikasi Anda')
        ->greeting('Halo ' . $this->notifiable->name . '!')
        ->line('Custom message di sini')
        ->action('Tombol Custom', $url)
        ->line('Custom footer text');
}
```

### Ubah Template HTML

File: `resources/views/emails/verify-email.blade.php`

- Edit color scheme, fonts, layout
- Add logo/brand image
- Customize sections

### Ubah Sender

```env
MAIL_FROM_ADDRESS=support@yourapp.com
MAIL_FROM_NAME="Aplikasi Anda"
```

---

## 🎨 Form Customization

### Ubah Validation Messages

File: `app/Livewire/Auth/Register.php`

```php
protected $messages = [
    'name.required' => 'Nama harus diisi',
    'email.unique' => 'Email sudah terdaftar',
    'password.confirmed' => 'Password tidak cocok',
];
```

### Ubah Form Fields

Edit: `resources/views/livewire/auth/register.blade.php`

- Add/remove fields
- Change styling
- Modify validation display

### Ubah Redirect Setelah Register

File: `app/Livewire/Auth/Register.php`

```php
public function register()
{
    // ... validation & user creation

    // Change redirect destination
    $this->redirect(route('your-custom-route'), navigate: true);
}
```

---

## 🐛 Common Issues & Solutions

### ❌ Email tidak terkirim

**Solusi:**

1. Cek `.env` mail configuration
2. Cek mail provider credentials
3. Run `php artisan tinker` → trigger manual email

### ❌ Livewire component error

**Solusi:**

1. Publish assets: `php artisan livewire:publish --assets`
2. Clear cache: `php artisan cache:clear`
3. Check namespace correct

### ❌ Verification link invalid

**Solusi:**

1. Check `APP_KEY` di `.env`
2. Verify URL signature tidak di-tamper
3. Link expire setelah 24 jam

### ❌ User stuck at verification

**Solusi:**

1. Resend email dari button
2. Check spam folder
3. Use `php artisan tinker` untuk manual verify:
    ```php
    User.find(1)->markEmailAsVerified()
    ```

---

## 📊 User Journey Diagram

```
START
  ↓
User akses /register
  ↓
Fill form (name, email, password)
  ↓
Submit → Livewire validate
  ↓
Create user + Hash password
  ↓
Fire Registered event
  ↓ (triggers VerifyEmailNotification)
Send verification email
  ↓
Auto-login user
  ↓
Redirect ke /verify-email (verification notice)
  ↓
User buka email
  ↓
Click "Verifikasi Email" link
  ↓
Laravel verify link (signed URL)
  ↓
Set email_verified_at
  ↓
Redirect to dashboard
  ↓
✅ FULLY VERIFIED - Access all features
```

---

## 🔗 Email Verification Link Flow

```
User click link in email
  ↓
GET /verify-email/{id}/{hash}
  ↓
Middleware 'signed' validate signature
  ↓
VerifyEmailController process
  ↓
Check email_verified_at is null
  ↓
Call markEmailAsVerified()
  ↓
Fire Verified event
  ↓
Redirect to dashboard
```

---

## 💾 Database

### Users Table Structure

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,  -- ← Key field
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'public',
    remember_token VARCHAR(100),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Check User Status

```bash
# Artisan tinker
php artisan tinker

# Check if verified
User.find(1)->hasVerifiedEmail()

# Manually mark as verified
User.find(1)->markEmailAsVerified()

# Get unverified users
User.whereNull('email_verified_at')->get()
```

---

## 🚀 Deployment

### Production Checklist

- [ ] `.env` configured dengan production mail service
- [ ] `APP_KEY` set dan unique
- [ ] Database migrated
- [ ] `APP_DEBUG=false`
- [ ] Email templates tested
- [ ] CORS/CSRF properly configured
- [ ] Rate limiting configured
- [ ] Cache cleared
- [ ] SSL/HTTPS enabled

### Email Service Recommendations

- **SendGrid** - Best for transactional email
- **AWS SES** - Cheap at scale
- **Mailgun** - Good balance
- **Postmark** - Excellent for verification emails

---

## 📚 Additional Resources

- [Laravel Authentication](https://laravel.com/docs/authentication)
- [Laravel Email Verification](https://laravel.com/docs/authentication#verifying-emails)
- [Livewire Documentation](https://livewire.laravel.com/)
- [Laravel Mail](https://laravel.com/docs/mail)
- [TailwindCSS](https://tailwindcss.com/)

---

**Selesai! Fitur Register dengan Email Verification sudah fully implemented.** ✅

Untuk pertanyaan atau troubleshooting, refer ke file `REGISTER_EMAIL_VERIFICATION_GUIDE.md` untuk dokumentasi lengkap.
