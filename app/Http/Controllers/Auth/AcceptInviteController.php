<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PulsifyMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AcceptInviteController extends Controller
{
    public function show(Request $request): View
    {
        $token = (string) $request->query('token', '');

        $user = User::where('otp_code', $token)
            ->where('status', 'invited')
            ->first();

        if (!$user) {
            return view('auth.accept-invite', [
                'error' => 'Invalid or expired invite link',
                'token' => null,
                'company' => null,
                'user' => null,
            ]);
        }

        if ($user->otp_expires_at && $user->otp_expires_at->isPast()) {
            return view('auth.accept-invite', [
                'error' => 'This invite has expired. Ask your admin to resend.',
                'token' => null,
                'company' => null,
                'user' => null,
            ]);
        }

        return view('auth.accept-invite', [
            'error' => null,
            'token' => $token,
            'company' => $user->company,
            'user' => $user,
        ]);
    }

    public function accept(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::where('otp_code', $validated['token'])
            ->where('status', 'invited')
            ->first();

        if (!$user) {
            return back()->with('error', 'Invalid or expired invite link');
        }

        if ($user->otp_expires_at && $user->otp_expires_at->isPast()) {
            return back()->with('error', 'This invite has expired. Ask your admin to resend.');
        }

        $user->forceFill([
            'name' => $validated['name'],
            'password' => Hash::make($validated['password']),
            'status' => 'active',
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ])->save();

        $owner = User::query()
            ->where('company_id', $user->company_id)
            ->where('role', 'owner')
            ->whereNotNull('email')
            ->first();
        if ($owner && $owner->email !== '') {
            $mailer = new PulsifyMailer($owner->company);
            $mailer->send('invite_accepted', (string) $owner->email, (string) ($owner->name ?? 'Owner'), [
                'new_member_name' => (string) $user->name,
                'new_member_email' => (string) $user->email,
                'role' => ucfirst((string) $user->role),
                'company' => (string) ($user->company?->name ?? 'your company'),
                'team_url' => url('/team'),
            ]);
        }

        Auth::login($user);

        $companyName = (string) ($user->company?->name ?? 'your company');

        return redirect('/dashboard')->with('success', "Welcome to {$companyName}! Your account is ready.");
    }
}

