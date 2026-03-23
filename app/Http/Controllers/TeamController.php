<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PulsifyMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        $companyId = Auth::user()->company_id;

        $members = User::where('company_id', $companyId)
            ->orderByRaw("FIELD(role, 'owner','admin','editor','viewer')")
            ->orderBy('status')
            ->orderBy('created_at', 'desc')
            ->get();

        $pendingCount = $members->where('status', 'invited')->count();

        return view('team.index', [
            'members' => $members,
            'pendingCount' => $pendingCount,
        ]);
    }

    public function invite(Request $request): RedirectResponse
    {
        $actor = Auth::user();
        if (!in_array($actor->role, ['owner', 'admin'], true)) {
            abort(403, 'You do not have permission to access this page.');
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::in(['admin', 'editor', 'viewer'])],
        ]);

        $email = strtolower(trim($validated['email']));
        $companyId = $actor->company_id;

        $existsInCompany = User::where('company_id', $companyId)
            ->where('email', $email)
            ->exists();

        if ($existsInCompany) {
            return back()
                ->withInput()
                ->with('error', 'This email is already a member of your company.');
        }

        $existsInOtherCompany = User::where('email', $email)
            ->whereNotNull('company_id')
            ->where('company_id', '!=', $companyId)
            ->exists();

        if ($existsInOtherCompany) {
            $request->session()->flash('warning', 'This email is already registered in another company, but the invite was still created.');
        }

        $token = Str::random(64);

        $invitee = User::create([
            'company_id' => $companyId,
            'name' => null,
            'email' => $email,
            'phone' => null,
            'password' => null,
            'role' => $validated['role'],
            'status' => 'invited',
            'email_verified_at' => null,
            'otp_code' => $token,
            'otp_expires_at' => now()->addDays(7),
            'invited_by' => $actor->id,
        ]);

        try {
            $mailer = new PulsifyMailer($actor->company);
            $mailer->send('team_invite', (string) $invitee->email, '', [
                'inviter_name' => (string) $actor->name,
                'company' => (string) ($actor->company?->name ?? 'your team'),
                'role' => (string) $validated['role'],
                'invite_url' => url('/invite/accept').'?token='.$token,
                'expiry_days' => '7',
            ]);
        } catch (\Throwable) {
            $request->session()->flash('warning', 'Invite created but email failed to send. Please configure SMTP and use resend.');
        }

        return back()->with('success', "Invite sent to {$email}");
    }

    public function resendInvite(Request $request, User $user): RedirectResponse
    {
        $actor = Auth::user();
        if (!in_array($actor->role, ['owner', 'admin'], true)) {
            abort(403, 'You do not have permission to access this page.');
        }

        if ($user->company_id !== $actor->company_id) {
            abort(403, 'You do not have permission to access this page.');
        }

        if ($user->status !== 'invited') {
            return back()->with('error', 'Only invited users can be resent an invite.');
        }

        $token = Str::random(64);
        $user->forceFill([
            'otp_code' => $token,
            'otp_expires_at' => now()->addDays(7),
        ])->save();

        try {
            $mailer = new PulsifyMailer($actor->company);
            $mailer->send('team_invite', (string) $user->email, '', [
                'inviter_name' => (string) $actor->name,
                'company' => (string) ($actor->company?->name ?? 'your team'),
                'role' => (string) $user->role,
                'invite_url' => url('/invite/accept').'?token='.$token,
                'expiry_days' => '7',
            ]);
        } catch (\Throwable) {
            return back()->with('warning', 'Could not send the email right now. Please try again.');
        }

        return back()->with('success', "Invite resent to {$user->email}");
    }

    public function removeUser(Request $request, User $user): RedirectResponse
    {
        $actor = Auth::user();
        if ($actor->role !== 'owner') {
            abort(403, 'You do not have permission to access this page.');
        }

        if ($user->company_id !== $actor->company_id) {
            abort(403, 'You do not have permission to access this page.');
        }

        if ($user->id === $actor->id) {
            return back()->with('error', 'You cannot remove yourself.');
        }

        $user->forceFill(['status' => 'inactive'])->save();

        return back()->with('success', "{$user->email} has been removed.");
    }
}

