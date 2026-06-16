<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    // Password Reset Controller - handles password reset requests + trait ResetsPasswords

    use ResetsPasswords;

    // Where to redirect users after resetting their password
    protected $redirectTo = '/home';
}
