<?php

namespace App\Livewire\Permission;

use Livewire\Component;
use Spatie\Permission\Models\{Permission, Role};

class ManagePermission extends Component {
    public $editPermissionId = null;
    public $editPermissionName = '';

    public function startEditPermission($id)
    {
        $permission = Permission::find($id);
        if ($permission) {
            $this->editPermissionId = $id;
            $this->editPermissionName = $permission->name;
        }
    }

    public function updatePermission()
    {
        $id = $this->editPermissionId;
        $name = trim($this->editPermissionName);
        if (!$id || !$name) return;
        $permission = Permission::find($id);
        if (!$permission) return;
        if (Permission::where('name', $name)->where('id', '!=', $id)->exists()) {
            session()->flash('error', 'Permission sudah ada!');
            return;
        }
        $permission->name = $name;
        $permission->save();
        $this->editPermissionId = null;
        $this->editPermissionName = '';
        $this->loadPermissions();
        session()->flash('success', 'Permission berhasil diupdate!');
    }

    public function cancelEditPermission()
    {
        $this->editPermissionId = null;
        $this->editPermissionName = '';
    }

    public function deletePermission($id)
    {
        $permission = Permission::find($id);
        if ($permission) {
            $permission->delete();
            $this->loadPermissions();
            session()->flash('success', 'Permission berhasil dihapus!');
        }
    }

    public $roles;
    public $roleName;
    public $permissions = [];
    public $selectedPermissions = [];
    public $newPermission = '';

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
        // Reset selectedPermissions sebelum isi ulang
        $this->selectedPermissions = [];
        $this->selectedPermissions = $role ? $role->permissions->pluck('name')->toArray() : [];

        logger('PERMISSION TERLOAD', $this->selectedPermissions);
    }

    public function addPermission()
    {
        $name = trim($this->newPermission);
        if (!$name) return;
        if (Permission::where('name', $name)->exists()) {
            session()->flash('error', 'Permission sudah ada!');
            return;
        }
        Permission::create(['name' => $name]);
        $this->newPermission = '';
        $this->loadPermissions();
        session()->flash('success', 'Permission berhasil ditambahkan!');
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
