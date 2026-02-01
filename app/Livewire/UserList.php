<?php

namespace App\Livewire;

use App\Models\User;
use DB;
use Livewire\Component;
use Livewire\WithPagination;
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
        // $user = DB::table('users')->where('id', $id);
        $user = User::findOrFail($id);
        $user->syncRoles([$role]);

        return session('success', 'Update role berhasil');
    }

    public function render()
    {
        $users = User::where('name', 'like', '%'.$this->search.'%')->latest()->paginate(10);

        return view('livewire.user-list', compact('users'))->layout('layouts.internal');
    }
}
