<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function showForgotForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));

        $user = User::where('email', $email)->first();
        if (!$user) {
            return back()->with('success', "If that email exists, we sent a reset link. Check your inbox.");
        }

        $plainToken = Str::random(64);

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($plainToken),
            'created_at' => now(),
        ]);

        try {
            Mail::to($email)->send(new PasswordResetMail($email, $plainToken));
        } catch (\Throwable) {
            // Don't reveal whether the email exists or if mail failed.
        }

        return back()->with('success', "If that email exists, we sent a reset link. Check your inbox.");
    }

    public function showResetForm(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        $token = (string) $request->query('token', '');
        $email = (string) $request->query('email', '');

        if ($token === '' || $email === '') {
            return redirect('/forgot-password');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $email = strtolower(trim($validated['email']));

        $row = DB::table('password_reset_tokens')->where('email', $email)->first();
        if (!$row) {
            return back()->with('error', 'Invalid or expired reset link');
        }

        $createdAt = $row->created_at ? \Illuminate\Support\Carbon::parse($row->created_at) : null;
        if (!$createdAt || $createdAt->lt(now()->subMinutes(60))) {
            return back()->with('error', 'This reset link has expired. Please request a new one.');
        }

        if (!Hash::check($validated['token'], (string) $row->token)) {
            return back()->with('error', 'Invalid or expired reset link');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            // Don't leak account existence; treat as invalid.
            return back()->with('error', 'Invalid or expired reset link');
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return redirect('/login')->with('success', 'Password reset successfully. Please sign in with your new password.');
    }
}

