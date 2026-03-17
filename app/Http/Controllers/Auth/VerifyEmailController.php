<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpVerificationMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class VerifyEmailController extends Controller
{
    public function showVerifyForm(Request $request): View|RedirectResponse
    {
        $email = (string) $request->session()->get('verify_email', '');
        if ($email === '') {
            return redirect('/register');
        }

        return view('auth.verify-email', [
            'email' => $email,
            'otpExpiryMinutes' => (int) config('pulsify.otp_expiry_minutes', 15),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $email = (string) $request->session()->get('verify_email', '');
        if ($email === '') {
            return redirect('/register');
        }

        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect('/register');
        }

        $isValid = $user->otp_code !== null
            && hash_equals((string) $user->otp_code, (string) $validated['otp'])
            && $user->otp_expires_at !== null
            && $user->otp_expires_at->isFuture();

        if (!$isValid) {
            return back()
                ->withInput()
                ->with('error', 'Invalid or expired code');
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'status' => 'active',
            'otp_code' => null,
            'otp_expires_at' => null,
        ])->save();

        Auth::login($user);

        $request->session()->forget(['verify_email', 'otp_code']);

        return redirect('/onboarding');
    }

    public function resend(Request $request): RedirectResponse
    {
        $email = (string) $request->session()->get('verify_email', '');
        if ($email === '') {
            return redirect('/register');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect('/register');
        }

        $otp = (string) random_int(100000, 999999);
        $otpExpiryMinutes = (int) config('pulsify.otp_expiry_minutes', 15);

        $user->forceFill([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes($otpExpiryMinutes),
        ])->save();

        try {
            Mail::to($user->email)->send(new OtpVerificationMail($otp, $user->name));
        } catch (\Throwable) {
            return back()->with('warning', 'We could not send the email right now. Please try again.');
        }

        return back()->with('success', 'A new code has been sent');
    }
}

