<?php

namespace App\Services;

use App\Mail\OtpMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OtpService
{
    public function generateAndSend(string $email, string $purpose, ?int $userId = null): void
    {
        // Rate limiting check for resends
        $recentOtp = DB::table('otp_codes')
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->latest('created_at')
            ->first();

        $cooldown = app()->environment('local') ? 5 : config('marketplace.otp.resend_cooldown_seconds', 60);
        if ($recentOtp) {
            $availableAt = \Carbon\Carbon::parse($recentOtp->created_at)->addSeconds($cooldown);
            if ($availableAt->isFuture()) {
                $secondsRemaining = max(1, (int) ceil(abs(now()->diffInSeconds($availableAt))));
                throw new \Exception("Please wait {$secondsRemaining} seconds before requesting a new code.");
            }
        }

        // Generate 6 digit code
        $code = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(config('marketplace.otp.expiry_minutes'));

        DB::table('otp_codes')->insert([
            'user_id' => $userId,
            'email' => $email,
            'code_hash' => Hash::make($code),
            'purpose' => $purpose,
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (app()->environment('local')) {
            session()->put('dev_otp_code', $code);
        }

        try {
            Mail::to($email)->send(new OtpMail($code, $purpose));
        } catch (\Exception $e) {
            \Log::error("SMTP Error: " . $e->getMessage());
            // For testing purposes on Render when SMTP fails, save it to session
            session()->flash('test_otp_code', $code);
            session()->flash('error', 'Email failed to send. Using testing mode.');
        }
    }

    public function verify(string $email, string $code, string $purpose): bool
    {
        $otp = DB::table('otp_codes')
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->latest('created_at')
            ->first();

        if (!$otp) {
            throw new \Exception("No active verification code found.");
        }

        if ($otp->locked_until && now()->isBefore($otp->locked_until)) {
            throw new \Exception("Too many failed attempts. Try again later.");
        }

        if (now()->isAfter($otp->expires_at)) {
            throw new \Exception("Verification code has expired.");
        }

        if (!Hash::check($code, $otp->code_hash)) {
            $attempts = $otp->attempts + 1;
            $maxAttempts = config('marketplace.otp.max_attempts');
            
            $updateData = ['attempts' => $attempts];
            if ($attempts >= $maxAttempts) {
                $updateData['locked_until'] = now()->addMinutes(config('marketplace.otp.lockout_minutes'));
            }
            
            DB::table('otp_codes')->where('id', $otp->id)->update($updateData);
            
            throw new \Exception("Invalid verification code.");
        }

        // Success
        DB::table('otp_codes')->where('id', $otp->id)->update([
            'verified_at' => now()
        ]);

        return true;
    }
}
