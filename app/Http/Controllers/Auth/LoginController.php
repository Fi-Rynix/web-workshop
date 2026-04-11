<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SendEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::query()
        ->where('email', $request->email)
        ->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan'])->withInput();
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password salah'])->withInput();
        }

        Auth::login($user);

        $request->session()->put([
            'iduser' => $user->iduser,
            'nama' => $user->nama,
            'email' => $user->email,
        ]);

        // Debug: Log user status
        Log::info('Login attempt - Email: ' . $user->email . ', status_verif: "' . ($user->status_verif ?? 'NULL') . '"');

        // cek sudah verif belum akunnya
        // Jika status_verif kosong/NULL atau bukan 'Active', maka perlu verifikasi OTP
        if (empty($user->status_verif) || $user->status_verif !== 'Active') {

            Log::info('Masuk OTP block - Email: ' . $user->email);

            $otp = rand(100000, 999999);

            $user->otp = md5($otp);
            $user->otp_expire_at = Carbon::now()->addMinutes(5);
            $save_result = $user->save();

            Log::info('Save OTP - Result: ' . ($save_result ? 'SUCCESS' : 'FAILED') . ', Email: ' . $user->email);
            Log::info('OTP Hash: ' . $user->otp . ', Expire: ' . $user->otp_expire_at);

            try {
                Mail::to($user->email)->send(new SendEmail([
                    'otp' => $otp,
                    'userName' => $user->nama,
                    'mailMessage' => 'Terima kasih telah mendaftar. Untuk menyelesaikan proses verifikasi akun Anda, silakan gunakan kode OTP di bawah ini.',
                    'subject' => 'Kode OTP Verifikasi'
                ]));
                Log::info('Email sent successfully for: ' . $user->email);
            } catch (\Exception $e) {
                Log::error('Email send failed: ' . $e->getMessage());
            }

            return redirect()->route('index-verify');
        }

        // Redirect berdasarkan role
        if ($user->idrole == 1) {
            return redirect()->route('dashboard');
        } elseif ($user->idrole == 2) {
            return redirect()->route('vendor.dashboard');
        } elseif ($user->idrole == 3) {
            return redirect()->route('pelanggan.dashboard');
        }

        // Default fallback
        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('login')->with('success', 'Logout berhasil');
    }


}