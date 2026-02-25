<?php

namespace App\Http\Controllers;

use App\Mail\SendEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect() {
        return Socialite::driver('google')->redirect();
    }

    public function callback() {
        $user_google = Socialite::driver('google')->stateless()->user();
        $user_db = User::where('google_id', $user_google->getId())->first();

        if (!$user_db) {
            $user_db = new User();
            $user_db->nama = $user_google->getName();
            $user_db->email = $user_google->getEmail();
            $user_db->google_id = $user_google->getId();
            $user_db->status_verif = 'Verify';
            $user_db->save();
        }

        auth('web')->login($user_db);
        session()->regenerate();
        
        session()->put([
            'iduser' => $user_db->iduser,
            'nama' => $user_db->nama,
            'email' => $user_db->email,
        ]);

        // Debug: Log user status
        Log::info('Google Login - Email: ' . $user_db->email . ', status_verif: "' . ($user_db->status_verif ?? 'NULL') . '"');

        // Jika status_verif kosong/NULL atau bukan 'Active', maka perlu verifikasi OTP
        if (empty($user_db->status_verif) || $user_db->status_verif !== 'Active') {

            Log::info('Masuk OTP block - Email: ' . $user_db->email);
            
            $otp = rand(100000, 999999);

            $user_db->otp = md5($otp);
            $user_db->otp_expire_at = Carbon::now()->addMinutes(5);
            $save_result = $user_db->save();
            
            Log::info('Save OTP - Result: ' . ($save_result ? 'SUCCESS' : 'FAILED') . ', Email: ' . $user_db->email);
            Log::info('OTP Hash: ' . $user_db->otp . ', Expire: ' . $user_db->otp_expire_at);

            try {
                Mail::to($user_db->email)->send(new SendEmail([
                    'otp' => $otp,
                    'userName' => $user_db->nama,
                    'mailMessage' => 'Terima kasih telah mendaftar. Untuk menyelesaikan proses verifikasi akun Anda, silakan gunakan kode OTP di bawah ini.',
                    'subject' => 'Kode OTP Verifikasi'
                ]));
                Log::info('Email sent successfully for: ' . $user_db->email);
            } catch (\Exception $e) {
                Log::error('Email send failed: ' . $e->getMessage());
            }

            return redirect()->route('index-verify');
        }

        return redirect()->route('dashboard');
    }
}

