<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ConfirmsPasswords;

class ConfirmPasswordController extends Controller
{
    // Confirm Password Controller - handles password confirmations + trait ConfirmsPasswords

    use ConfirmsPasswords;

    // Where to redirect users when the intended url fails
    protected $redirectTo = '/home';

    // Create a new controller instance
    public function __construct()
    {
        $this->middleware('auth');
    }
}
