<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class SystemController extends Controller
{
    public function users()
    {
        return view('admin.system.users');
    }

    public function roles()
    {
        return view('admin.system.roles');
    }

    public function audits()
    {
        return view('admin.system.audit-logs');
    }
}
