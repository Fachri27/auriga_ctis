<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class UserForm extends Component
{
    public $user;
    public $userId = null;
    public $name;
    public $email;
    public $password = null;
    public $password_confirmation = null;
    public $roles = [];
    public $allRoles;


    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->userId),
            ],
            'password' => $this->password
                ? 'confirmed|min:8'
                : 'nullable',
            'roles' => 'required',
        ];
    }

    public function mount($userId = null)
    {
        $this->allRoles = Role::pluck('name')->toArray();

        if ($userId) {
            $this->user = User::with('roles')->findOrFail($userId);

            $this->name = $this->user->name;
            $this->email = $this->user->email;
            $this->roles = $this->user->roles->first()?->name;
        }
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->userId) {
            $user = User::findOrFail($this->userId);

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if (!empty($this->password)) {
                $user->update([
                    'password' => Hash::make($this->password),
                ]);
            }
        } else {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);
        }

        // Role Spatie
        $user->syncRoles([$this->roles]);

        session()->flash('success', 'User berhasil disimpan');
        return redirect()->route('user.index');
    }

    public function render()
    {
        return view('livewire.user-form')->layout('layouts.internal');
    }
}
