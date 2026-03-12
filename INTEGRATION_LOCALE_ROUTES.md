# Integration Guide: Register Email Verification dengan Locale Prefix

## 📍 Current Architecture

Aplikasi menggunakan locale prefix di routes: `/{locale}/`

- Contoh: `/en/register`, `/id/register`
- Locale values: `en` atau `id`

---

## 🔧 Integration Steps

### Option 1: Keep Auth Routes Outside Locale (RECOMMENDED)

Jika ingin auth routes tetap global (tanpa locale prefix):

**File: `routes/auth.php` (sudah di-apply)**

```php
// Routes tanpa locale prefix
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    // ... other auth routes
});
```

**Access URL:**

```
http://localhost:8000/register          (bukan /en/register)
http://localhost:8000/login
http://localhost:8000/verify-email
```

---

### Option 2: Move Auth Routes Inside Locale Group

Jika ingin auth routes dengan locale prefix:

**Modify: `bootstrap/app.php` atau `routes/web.php`**

Add di dalam locale group:

```php
Route::group([
    'prefix' => '{locale}',
    'middleware' => ['setlocale'],
    'where' => ['locale' => 'en|id'],
], function () {
    // ... existing routes

    // Auth routes inside locale group
    Route::middleware('guest')->group(function () {
        Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
        Route::post('register', [RegisteredUserController::class, 'store']);
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        // ... include all auth routes here
    });
});
```

**Access URL:**

```
http://localhost:8000/en/register
http://localhost:8000/id/register
http://localhost:8000/en/login
http://localhost:8000/id/login
```

---

## 🌍 Localization Setup

### 1. Language Files

Buat/Update file language untuk Bahasa Indonesia:

**File: `resources/lang/id/validation.php`**

```php
return [
    'required' => ':attribute harus diisi.',
    'email' => ':attribute harus berupa email yang valid.',
    'unique' => ':attribute sudah terdaftar.',
    'min' => ':attribute minimal :min karakter.',
    'confirmed' => ':attribute tidak cocok dengan konfirmasinya.',
];
```

**File: `resources/lang/id/auth.php`**

```php
return [
    'failed' => 'Email atau password tidak sesuai.',
    'password' => 'Password yang Anda masukkan tidak sesuai.',
    'throttle' => 'Terlalu banyak percobaan login. Coba lagi dalam :seconds detik.',
];
```

### 2. Update Livewire Component

**File: `app/Livewire/Auth/Register.php`**

Gunakan translation helper:

```php
protected $messages = [
    'name.required' => __('validation.required', ['attribute' => 'Nama']),
    'email.unique' => __('validation.unique', ['attribute' => 'Email']),
];
```

### 3. Locale Detection

Laravel otomatis detect locale dari URL dengan middleware `setlocale`

---

## 🔀 View Template Localization

### Form Labels

Update blade view:

**File: `resources/views/livewire/auth/register.blade.php`**

```php
<label>{{ __('auth.register.name') }}</label>
<label>{{ __('auth.register.email') }}</label>
<label>{{ __('auth.register.password') }}</label>
```

**Create language files:**

`resources/lang/id/auth.php`:

```php
return [
    'register' => [
        'name' => 'Nama Lengkap',
        'email' => 'Email',
        'password' => 'Kata Sandi',
        'password_confirmation' => 'Konfirmasi Kata Sandi',
        'submit' => 'Daftar',
    ],
];
```

---

## 📝 URL Route Naming

### Current Route Names (Non-Locale)

```php
route('register')              // /register
route('login')                 // /login
route('verification.notice')   // /verify-email
route('password.request')      // /forgot-password
```

### Locale-aware Route Names

Jika menggunakan locale prefix, add ke route:

```php
Route::get('{locale}/register', [...])
    ->whereIn('locale', ['en', 'id'])
    ->name('register');
```

**Usage dalam blade:**

```blade
<!-- Non-locale route -->
<a href="{{ route('register') }}">Register</a>

<!-- Locale-aware route -->
<a href="{{ route('register', ['locale' => app()->getLocale()]) }}">Register</a>
```

---

## 🔗 Locale Switching in Views

### Add Language Switcher

**File: `resources/views/layouts/app.blade.php`** atau component

```blade
<div class="language-switcher">
    <a href="{{ route('register', ['locale' => 'en']) }}">English</a>
    <a href="{{ route('register', ['locale' => 'id']) }}">Bahasa Indonesia</a>
</div>
```

### Middleware for Locale

Laravel sudah provide `setlocale` middleware di routes.

**Dalam controller/livewire:**

```php
// Get current locale
$locale = app()->getLocale(); // 'en' atau 'id'

// Set locale
app()->setLocale('id');

// Get locale from URL
$locale = request()->route('locale'); // dari parameter {locale}
```

---

## 🎯 Implementation Scenarios

### Scenario 1: Keep Auth Routes Global (RECOMMENDED)

✅ **Pros:**

- Simpler URL structure
- No locale complications
- Works with email links (no locale in email)
- Standard Laravel setup

❌ **Cons:**

- Different URL structure for auth vs main app

**Setup:**

- Keep `routes/auth.php` as is (non-locale)
- Register routes at: `/register`, `/login`
- Email verification links: `/verify-email/{id}/{hash}`

---

### Scenario 2: Locale-specific Auth Routes

✅ **Pros:**

- Consistent URL structure
- All routes have locale prefix
- Localized auth experience

❌ **Cons:**

- Need to generate locale-aware URL in notification
- More complex routing
- Email links need locale parameter

**Setup:**

- Move auth routes inside locale group
- Register routes at: `/{locale}/register`
- Email verification links: `/{locale}/verify-email/{id}/{hash}`
- Need custom verification URL generation

---

## 🔐 Verification Link with Locale

Jika menggunakan Scenario 2, update notification:

**File: `app/Notifications/VerifyEmailNotification.php`**

```php
protected function buildMailMessage($url)
{
    // Add locale to verification URL
    $locale = app()->getLocale();
    $url = str_replace('verify-email', app()->getLocale() . '/verify-email', $url);

    return (new MailMessage)
        ->subject(__('auth.verify.subject'))
        ->line(__('auth.verify.message'))
        ->action(__('auth.verify.button'), $url);
}
```

---

## ✅ Quick Checklist

### If Using Global Auth Routes (Recommended)

- [x] Auth routes in `/routes/auth.php` (non-locale)
- [x] Livewire Register component
- [x] Email templates
- [x] Verification notice view
- [x] All working correctly

### If Using Locale-specific Auth Routes

- [ ] Move auth routes to locale group
- [ ] Create locale language files
- [ ] Update Livewire component for translation
- [ ] Update views with `__()` helpers
- [ ] Test with both locales
- [ ] Update verification URL generation

---

## 🧪 Testing

### Test Global Auth Routes

```bash
# Access register
curl http://localhost:8000/register

# Register new user
# Check email
# Click verification link

# Test localized content
# Change MAIL_FROM_NAME in .env
# Should display correctly
```

### Test Locale-specific Routes

```bash
# Access with locale
curl http://localhost:8000/en/register
curl http://localhost:8000/id/register

# Verify form labels in correct language
# Test email in correct language
# Test verification URL has locale
```

---

## 📚 File Locations to Update

| Scenario          | Files to Update                       |
| ----------------- | ------------------------------------- |
| **Global Routes** | None (use defaults)                   |
| **Locale Routes** | `routes/web.php`, `resources/lang/**` |

---

**Rekomendasi:** Gunakan **Scenario 1 (Global Auth Routes)** untuk kesederhanaan dan standard Laravel practice.

Jika diperlukan locale-specific routes nanti, dokumentasi ini siap untuk implementasi.
