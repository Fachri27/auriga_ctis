# Code Snippets: Ready to Use

Kumpulan code snippets yang sudah siap digunakan untuk berbagai kebutuhan.

---

## 1️⃣ Tambah Route yang Require Verified Email

### Tambah di `routes/auth.php`

```php
Route::middleware('auth')->group(function () {
    // Existing verification routes...

    // Routes that require verified email
    Route::middleware('verified')->group(function () {
        // Add your protected routes here
        Route::get('dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('profile', [ProfileController::class, 'show'])
            ->name('profile');

        Route::resource('posts', PostController::class)
            ->only(['index', 'create', 'store', 'edit', 'update']);
    });

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
```

---

## 2️⃣ Custom Email Notification dengan Logo

### Update `app/Notifications/VerifyEmailNotification.php`

```php
<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends BaseVerifyEmail
{
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject('Verifikasi Email Anda')
            ->greeting('Halo ' . $this->notifiable->name . '!')
            ->line('Terima kasih telah mendaftar di aplikasi kami.')
            ->line('Silakan klik tombol di bawah untuk memverifikasi alamat email Anda.')
            ->action('Verifikasi Email', $url)
            ->line('Link ini akan berlaku selama 24 jam.')
            ->line('Jika Anda tidak melakukan registrasi, abaikan email ini.')
            ->salutation('Salam, Tim Aplikasi');
    }
}
```

---

## 3️⃣ Livewire Component dengan Additional Fields

### Update `app/Livewire/Auth/Register.php`

```php
public string $phone = '';
public string $company = '';

protected function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'unique:users,email'],
        'phone' => ['required', 'regex:/^(\+62|62|0)[0-9]{9,12}$/'],
        'company' => ['nullable', 'string', 'max:255'],
        'password' => ['required', 'confirmed', Password::defaults()],
    ];
}

public function register()
{
    $this->isSubmitting = true;
    $validated = $this->validate();

    try {
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'company' => $validated['company'],
            'password' => Hash::make($validated['password']),
            'role' => 'public',
        ]);

        event(new Registered($user));
        Auth::login($user);
        $this->redirect(route('verification.notice'), navigate: true);
    } catch (\Exception $e) {
        $this->dispatch('notify', type: 'error', message: 'Terjadi kesalahan.');
        $this->isSubmitting = false;
    }
}
```

---

## 4️⃣ Manual Email Verification (Artisan Tinker)

### Verify User Manually

```bash
php artisan tinker

# Verify specific user by ID
User.find(1)->markEmailAsVerified()

# Verify by email
User.where('email', 'test@example.com')->first()->markEmailAsVerified()

# Get all unverified users
User.whereNull('email_verified_at')->get()

# Manually send verification email
User.find(1)->sendEmailVerificationNotification()

# Check if user verified
User.find(1)->hasVerifiedEmail()
```

---

## 5️⃣ Test Email Locally (Mailtrap)

### Setup untuk Testing

```env
# .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sandbox.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Aplikasi Test"
```

### Test dengan Log Driver

```env
# .env - untuk development
MAIL_MAILER=log
```

Cek log di: `storage/logs/laravel.log`

---

## 6️⃣ Custom Email Template Minimal

### Buat di `resources/views/emails/verify-minimal.blade.php`

```blade
<div style="font-family: Arial, sans-serif; max-width: 600px;">
    <h2>Verifikasi Email</h2>

    <p>Halo {{ $notifiable->name }},</p>

    <p>Klik tombol di bawah untuk memverifikasi email Anda:</p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $url }}"
           style="background-color: #4F46E5; color: white; padding: 10px 30px;
                  text-decoration: none; border-radius: 5px; display: inline-block;">
            Verifikasi Email
        </a>
    </div>

    <p>Atau gunakan link ini:</p>
    <p>{{ $url }}</p>

    <hr>

    <p style="color: #666; font-size: 12px;">
        Link berlaku 24 jam. Jika Anda tidak melakukan pendaftaran, abaikan email ini.
    </p>
</div>
```

---

## 7️⃣ Form Validation Custom Messages

### Update `app/Livewire/Auth/Register.php`

```php
protected $messages = [
    'name.required' => 'Nama lengkap tidak boleh kosong.',
    'name.string' => 'Nama harus berupa teks.',
    'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',

    'email.required' => 'Alamat email harus diisi.',
    'email.email' => 'Format email tidak valid.',
    'email.unique' => 'Email sudah terdaftar di sistem kami.',
    'email.max' => 'Email tidak boleh lebih dari 255 karakter.',

    'password.required' => 'Kata sandi tidak boleh kosong.',
    'password.min' => 'Kata sandi minimal 8 karakter.',
    'password.regex' => 'Kata sandi harus mengandung huruf besar, huruf kecil, angka, dan simbol.',
    'password.confirmed' => 'Konfirmasi kata sandi tidak sesuai.',

    'password_confirmation.required' => 'Konfirmasi kata sandi tidak boleh kosong.',
];
```

---

## 8️⃣ Redirect User Setelah Verify

### Override di `app/Http/Controllers/Auth/VerifyEmailController.php`

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false) . '?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // Redirect ke halaman custom
        return redirect()->intended(route('dashboard', absolute: false))
            ->with('message', 'Email Anda telah berhasil diverifikasi!');
    }
}
```

---

## 9️⃣ Protect Route Group dengan Verified Middleware

### Contoh di `routes/api.php` atau `routes/web.php`

```php
// Require verified email untuk semua endpoint ini
Route::middleware(['auth', 'verified'])->group(function () {
    // User profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // User dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // User data
    Route::get('/my-data', [DataController::class, 'index']);
    Route::post('/my-data', [DataController::class, 'store']);
    Route::delete('/my-data/{id}', [DataController::class, 'destroy']);

    // Settings
    Route::get('/settings', [SettingsController::class, 'show']);
    Route::put('/settings', [SettingsController::class, 'update']);
});
```

---

## 🔟 Queue Email untuk Better Performance

### Buat Mailable Queueable

**File: `app/Mail/VerifyEmailQueue.php`**

```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmailQueue extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $user,
        public $verificationUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verifikasi Email Anda',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verify-email',
        );
    }
}
```

### Update Notification untuk Queue

```php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends Notification implements ShouldQueue
{
    public $queue = 'default';
    public $timeout = 1800; // 30 minutes

    // ... rest of code
}
```

### Jalankan Queue Worker

```bash
php artisan queue:work
# atau untuk production
php artisan queue:work --daemon
```

---

## 1️⃣1️⃣ Resend Email Manual (Admin)

### Buat Command

```bash
php artisan make:command ResendVerificationEmail
```

**File: `app/Console/Commands/ResendVerificationEmail.php`**

```php
<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResendVerificationEmail extends Command
{
    protected $signature = 'email:resend-verification {email}';
    protected $description = 'Resend verification email ke user';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error('User tidak ditemukan');
            return 1;
        }

        if ($user->hasVerifiedEmail()) {
            $this->warn('Email sudah terverifikasi');
            return 0;
        }

        $user->sendEmailVerificationNotification();
        $this->info('Email verifikasi berhasil dikirim ke: ' . $email);

        return 0;
    }
}
```

### Gunakan

```bash
php artisan email:resend-verification test@example.com
```

---

## 1️⃣2️⃣ Event Listener untuk Email Verified

### Buat Listener

```bash
php artisan make:listener SendWelcomeEmailAfterVerification
```

**File: `app/Listeners/SendWelcomeEmailAfterVerification.php`**

```php
<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWelcomeEmailAfterVerification implements ShouldQueue
{
    public function handle(Verified $event): void
    {
        // Send welcome email
        \Mail::to($event->user->email)->send(
            new \App\Mail\WelcomeEmail($event->user)
        );

        // Log event
        \Log::info('User verified: ' . $event->user->email);

        // Update user status
        $event->user->update(['verified_at' => now()]);
    }
}
```

### Register di `app/Providers/EventServiceProvider.php`

```php
protected $listen = [
    'Illuminate\Auth\Events\Verified' => [
        'App\Listeners\SendWelcomeEmailAfterVerification',
    ],
];
```

---

## Database Queries

### Check Verification Status

```sql
-- Unverified users
SELECT * FROM users WHERE email_verified_at IS NULL;

-- Verified users
SELECT * FROM users WHERE email_verified_at IS NOT NULL;

-- Count unverified
SELECT COUNT(*) FROM users WHERE email_verified_at IS NULL;

-- Update manually (jangan gunakan di production!)
UPDATE users SET email_verified_at = NOW() WHERE id = 1;

-- Delete verification
UPDATE users SET email_verified_at = NULL WHERE id = 1;
```

---

## Testing Email

### Manual Test Menggunakan Code

```php
// Di routes/web.php (hanya untuk testing)
Route::get('/test-email', function () {
    $user = Auth::user();
    $user->sendEmailVerificationNotification();
    return 'Email sent!';
})->middleware('auth');
```

### Using Faker untuk Testing

```php
// Di tinker atau command
User.factory()->create([
    'name' => 'Test User',
    'email' => 'test@example.com',
])->sendEmailVerificationNotification()
```

---

**Semua snippets di atas sudah siap digunakan. Copy-paste sesuai kebutuhan!** ✨
