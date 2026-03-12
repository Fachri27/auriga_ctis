<?php

namespace App\Livewire;

use App\Models\User;
use DB;
use Livewire\{Component, WithPagination};
use Spatie\Permission\Models\Role;

class UserList extends Component
{
    use WithPagination;

    public $search;

    public $roles = [];

    public function mount()
    {
        $this->roles = Role::pluck('name')->toArray();
    }

    public function updateRole($id, $role)
    {
        // update role ambil dari spatie permission
        $user = User::findOrFail($id);
        $user->syncRoles([$role]);

        session()->flash('success', 'Update role berhasil');
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        session()->flash('success', 'User berhasil dihapus');
    }

    public function render()
    {
        $users = User::with('roles')->where('name', 'like', '%'.$this->search.'%')->latest()->paginate(10);

        return view('livewire.user-list', compact('users'))->layout('layouts.internal');
    }
}
