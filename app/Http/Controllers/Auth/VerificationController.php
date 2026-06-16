<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;

class VerificationController extends Controller
{
    // Email Verification Controller - handles email verification for user recently registered, re-send if not received

    use VerifiesEmails;

    // Where to redirect users after verification
    protected $redirectTo = '/home';

    // Create a new controller instance
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }
}
