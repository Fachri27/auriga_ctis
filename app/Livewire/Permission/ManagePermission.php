<?php

namespace App\Livewire\Permission;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ManagePermission extends Component
{
    public $roles;
    public $roleName;
    public $permissions = [];
    public $selectedPermissions = [];

    public function mount()
    {
        $this->roles = Role::orderBy('name')->get();
        $this->roleName = $this->roles->first()->name ?? null;

        $this->loadPermissions();
    }

    public function updatedRoleName()
    {
        logger('ROLE BERUBAH KE', [$this->roleName]);
        $this->loadPermissions();
    }

    private function loadPermissions()
    {
        // $role = Role::with('permissions')
        //     ->where('name', $this->roleName)
        //     ->first();

        // $this->permissions = Permission::orderBy('name')->get();

        // $this->selectedPermissions = $role
        //     ? $role->permissions->pluck('name')->toArray()
        //     : [];

        if (!$this->roleName) return;

    $role = Role::where('name', $this->roleName)->first();

    $this->permissions = Permission::orderBy('name')->get();
    $this->selectedPermissions = $role->permissions->pluck('name')->toArray();

            logger('PERMISSION TERLOAD', $this->selectedPermissions);

    }

    public function updatedSelectedPermissions()
    {
        if (!$this->roleName) return;

        $role = Role::where('name', $this->roleName)->first();

        $role->syncPermissions($this->selectedPermissions);

        logger('PERMISSION DISIMPAN', $this->selectedPermissions);
    }


    public function render()
    {
        return view('livewire.permission.manage-permission')->layout('layouts.internal');
    }
}
