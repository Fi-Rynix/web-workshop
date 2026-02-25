<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VerifController extends Controller
{
    public function index()
    {
        return view('auth.verify');
    }

    public function checkOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $user = $request->user();

        Log::info('OTP Verification attempt - Email: ' . $user->email . ', OTP input: ' . $request->otp);

        // Check expiration FIRST
        if (Carbon::now() > $user->otp_expire_at) {
            Log::error('OTP expired - Email: ' . $user->email . ', Expire at: ' . $user->otp_expire_at);
            return back()->withErrors(['otp' => 'Kode OTP sudah expired'])->withInput();
        }

        // Then check hash
        $otp_hash = md5($request->otp);
        Log::info('OTP Hash comparison - Input hash: ' . $otp_hash . ', DB hash: ' . $user->otp);
        
        if ($user->otp !== $otp_hash) {
            Log::error('OTP mismatch - Email: ' . $user->email);
            return back()->withErrors(['otp' => 'Kode OTP salah'])->withInput();
        }

        // Success - mark as verified
        $user->status_verif = 'Active';
        $user->save();

        Log::info('OTP verified successfully - Email: ' . $user->email);

        return redirect()->route('dashboard');
    }

    public function resendOtp(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return back()->withErrors(['error' => 'Silakan login terlebih dahulu']);
        }

        Log::info('Resend OTP - Email: ' . $user->email);

        $otp = rand(100000, 999999);

        $user->otp = md5($otp);
        $user->otp_expire_at = Carbon::now()->addMinutes(5);
        $save_result = $user->save();

        Log::info('Save OTP - Result: ' . ($save_result ? 'SUCCESS' : 'FAILED') . ', Email: ' . $user->email);

        try {
            $mailableClass = 'App\\Mail\\SendEmail';
            Mail::to($user->email)->send(new $mailableClass([
                'otp' => $otp,
                'userName' => $user->nama,
                'mailMessage' => 'Kode OTP Anda telah dikirim ulang. Silakan gunakan kode di bawah ini untuk menyelesaikan verifikasi akun.',
                'subject' => 'Kode OTP Verifikasi (Pengiriman Ulang)'
            ]));
            Log::info('Resend email sent successfully for: ' . $user->email);
            return back()->with('success', 'Kode OTP telah dikirim ulang ke email Anda');
        } catch (\Exception $e) {
            Log::error('Resend email failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal mengirim OTP. Silakan coba lagi.']);
        }
    }

    

}

