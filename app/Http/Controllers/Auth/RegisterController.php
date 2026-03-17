<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpVerificationMail;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showRegisterForm(): View|\Illuminate\Http\RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'company_name' => ['nullable', 'string', 'max:255'],
        ]);

        $companyName = trim((string) ($validated['company_name'] ?? ''));
        if ($companyName === '') {
            $companyName = $this->companyNameFromEmail($validated['email']);
        }

        $company = Company::create([
            'name' => $companyName,
            'slug' => $this->uniqueCompanySlug($companyName),
        ]);

        $otp = (string) random_int(100000, 999999);
        $otpExpiryMinutes = (int) config('pulsify.otp_expiry_minutes', 15);

        $user = User::create([
            'company_id' => $company->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'owner',
            'status' => 'invited',
            'email_verified_at' => null,
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes($otpExpiryMinutes),
        ]);

        $request->session()->put('verify_email', $user->email);
        try {
            Mail::to($user->email)->send(new OtpVerificationMail($otp, $user->name));
        } catch (\Throwable) {
            $request->session()->flash(
                'warning',
                'Account created but email failed to send. Use the resend option.'
            );
        }

        return redirect('/verify-email');
    }

    private function uniqueCompanySlug(string $companyName): string
    {
        $base = Str::slug($companyName);
        if ($base === '') {
            $base = 'company';
        }

        $slug = $base;
        $i = 2;
        while (Company::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    private function companyNameFromEmail(string $email): string
    {
        $domain = Str::of($email)->after('@')->lower()->toString();
        $secondLevel = Str::of($domain)->before('.')->toString();

        if ($secondLevel !== '') {
            return Str::ucfirst($secondLevel);
        }

        $username = Str::of($email)->before('@')->toString();
        return Str::ucfirst($username);
    }
}

