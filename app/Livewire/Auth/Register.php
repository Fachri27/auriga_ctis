<?php

namespace App\Livewire\Auth;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\{Auth, Hash};
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use Livewire\Component;

class Register extends Component
{
    /**
     * Form Properties
     */
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * UI State
     */
    public bool $showPassword = false;
    public bool $isSubmitting = false;

    /**
     * Validation Rules
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    /**
     * Custom validation messages
     */
    protected $messages = [
        'name.required' => 'Nama lengkap harus diisi.',
        'name.string' => 'Nama harus berupa teks.',
        'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
        'email.required' => 'Email harus diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email sudah terdaftar di sistem.',
        'email.max' => 'Email tidak boleh lebih dari 255 karakter.',
        'password.required' => 'Kata sandi harus diisi.',
        'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        'password_confirmation.required' => 'Konfirmasi kata sandi harus diisi.',
    ];

    /**
     * Handle form submission
     */
    public function register()
    {
        $this->isSubmitting = true;

        try {
            // Validate input
            $validated = $this->validate();

            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'public', // Default role untuk user baru
            ]);

            // Assign role menggunakan Spatie Permission
            $user->assignRole('public');

            // Login user so they can access verification notice page
            Auth::login($user);

            // Fire registered event (triggers email verification immediately)
            event(new Registered($user));

            // Flash success message
            session()->flash('success', 'Pendaftaran berhasil! Email verifikasi telah dikirim ke ' . $user->email);

            // Redirect to email verification notice page
            return redirect()->route('verification.notice');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Terjadi kesalahan saat registrasi. Silakan coba lagi.');
        } finally {
            $this->isSubmitting = false;
        }
    }

    /**
     * Reset form
     */
    public function resetForm()
    {
        $this->reset(['name', 'email', 'password', 'password_confirmation']);
    }

    /**
     * Toggle password visibility
     */
    public function togglePasswordVisibility()
    {
        $this->showPassword = !$this->showPassword;
    }

    /**
     * Real-time email validation
     */
    #[\Livewire\Attributes\On('check-email')]
    public function checkEmail()
    {
        if (empty($this->email)) {
            return;
        }

        $this->validate(['email' => ['email', 'unique:users,email']]);
    }

    /**
     * Render component
     */
    public function render()
    {
        return view('livewire.auth.register');
    }
}
