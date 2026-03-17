<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->with('error', 'Invalid email or password');
        }

        if ($user->email_verified_at === null) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->with('error_html', 'Please verify your email first. <a href="'.url('/verify-email').'" class="alert-link">Verify email</a>');
        }

        if ($user->status !== 'active') {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->with('error', 'Your account has been deactivated. Contact your admin.');
        }

        if (!Hash::check($validated['password'], $user->password)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->with('error', 'Invalid email or password');
        }

        $remember = $request->boolean('remember');
        Auth::login($user, $remember);

        $user->forceFill(['last_login_at' => now()])->save();

        return redirect('/dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'You have been logged out');
    }
}

