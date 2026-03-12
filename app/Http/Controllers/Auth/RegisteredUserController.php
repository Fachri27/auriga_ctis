<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Livewire\Auth\Register as LivewireRegister;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view with Livewire component.
     */
    public function create()
    {
        return view('auth.register');
    }
}
