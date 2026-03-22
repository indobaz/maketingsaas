<?php

namespace App\Http\Controllers;

use App\Models\ContentPillar;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    private const DEFAULT_PRIMARY = '#5F63F2';

    private const INDUSTRIES = [
        'Retail',
        'F&B',
        'Real Estate',
        'Building Materials',
        'Healthcare',
        'Fashion',
        'Automotive',
        'Technology',
        'Other',
    ];

    private const COUNTRIES = [
        'UAE',
        'Saudi Arabia',
        'Kuwait',
        'Qatar',
        'Bahrain',
        'Oman',
        'Other GCC',
        'Other',
    ];

    private const TIMEZONES = [
        'Asia/Dubai',
        'Asia/Riyadh',
        'Asia/Kuwait',
        'Asia/Qatar',
        'UTC',
    ];

    /** @var array<string, array{users: int|null, channels: int|null, label: string}> */
    private const PLAN_LIMITS = [
        'free' => ['users' => 1, 'channels' => 2, 'label' => 'Free'],
        'starter' => ['users' => 3, 'channels' => 5, 'label' => 'Starter'],
        'pro' => ['users' => 10, 'channels' => null, 'label' => 'Pro'],
        'enterprise' => ['users' => null, 'channels' => null, 'label' => 'Enterprise'],
    ];

    public function index(Request $request): View
    {
        $user = $request->user();
        $role = strtolower((string) ($user->role ?? ''));
        if (! in_array($role, ['owner', 'admin'], true)) {
            abort(403, 'You do not have permission to access this page.');
        }

        $company = $user->company;
        if (! $company) {
            abort(404, 'Company not found.');
        }

        $companyId = (int) $company->id;

        $teamMembers = User::query()
            ->where('company_id', $companyId)
            ->orderByRaw("FIELD(role, 'owner','admin','editor','viewer')")
            ->orderBy('status')
            ->orderBy('created_at', 'desc')
            ->get();

        $contentPillars = ContentPillar::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $channelCount = $company->channels()->count();

        $planKey = strtolower((string) ($company->plan ?? 'free'));
        if (! isset(self::PLAN_LIMITS[$planKey])) {
            $planKey = 'free';
        }
        $planLimits = self::PLAN_LIMITS[$planKey];

        $smtpConfigured = filled(data_get($company->extra_settings, 'smtp.host'));

        return view('settings.index', [
            'company' => $company,
            'isOwner' => $role === 'owner',
            'teamMembers' => $teamMembers,
            'contentPillars' => $contentPillars,
            'industries' => self::INDUSTRIES,
            'countries' => self::COUNTRIES,
            'timezones' => self::TIMEZONES,
            'defaultPrimary' => self::DEFAULT_PRIMARY,
            'channelCount' => $channelCount,
            'planKey' => $planKey,
            'planLimits' => $planLimits,
            'smtpConfigured' => $smtpConfigured,
            'smtpForm' => data_get($company->extra_settings, 'smtp', []),
        ]);
    }

    public function updateCompany(Request $request): RedirectResponse
    {
        $this->ensureOwner($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'url', 'max:500'],
            'industry' => ['nullable', 'string', 'max:100', Rule::in(self::INDUSTRIES)],
            'country' => ['nullable', 'string', 'max:100', Rule::in(self::COUNTRIES)],
            'timezone' => ['nullable', 'string', 'max:100', Rule::in(self::TIMEZONES)],
            'logo_url' => ['nullable', 'string', 'max:2000'],
        ]);

        $company = $request->user()->company;
        if (! $company) {
            abort(404);
        }

        $incomingLogo = trim((string) ($validated['logo_url'] ?? ''));
        $currentLogo = (string) ($company->logo_url ?? '');
        if ($incomingLogo !== '') {
            $logoFinal = $incomingLogo;
        } elseif (str_starts_with($currentLogo, 'data:image')) {
            $logoFinal = $currentLogo;
        } else {
            $logoFinal = null;
        }

        $company->forceFill([
            'name' => trim($validated['name']),
            'website' => isset($validated['website']) && $validated['website'] !== '' ? trim($validated['website']) : null,
            'industry' => $validated['industry'] ?? null,
            'country' => $validated['country'] ?? null,
            'timezone' => $validated['timezone'] ?? null,
            'logo_url' => $logoFinal,
        ])->save();

        return redirect()->route('settings.index', ['tab' => 'profile'])->with('success', 'Company profile updated');
    }

    public function updateBrandKit(Request $request): RedirectResponse
    {
        $this->ensureOwner($request);

        $validated = $request->validate([
            'primary_color' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'secondary_color' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
        ]);

        $company = $request->user()->company;
        if (! $company) {
            abort(404);
        }

        $company->forceFill([
            'primary_color' => strtoupper($validated['primary_color']),
            'secondary_color' => strtoupper($validated['secondary_color']),
        ])->save();

        return redirect()->route('settings.index', ['tab' => 'brand'])->with('success', 'Brand colors updated');
    }

    public function updateSmtp(Request $request): RedirectResponse
    {
        $this->ensureOwner($request);

        $validated = $request->validate([
            'smtp_host' => ['required', 'string', 'max:255'],
            'smtp_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => ['required', 'string', 'max:255'],
            'smtp_password' => ['required', 'string', 'max:500'],
            'smtp_from_email' => ['required', 'email', 'max:255'],
            'smtp_from_name' => ['required', 'string', 'max:255'],
        ]);

        $company = $request->user()->company;
        if (! $company) {
            abort(404);
        }

        $extra = $company->extra_settings ?? [];
        $extra['smtp'] = [
            'host' => $validated['smtp_host'],
            'port' => (int) $validated['smtp_port'],
            'username' => $validated['smtp_username'],
            'password' => Crypt::encryptString($validated['smtp_password']),
            'from_email' => $validated['smtp_from_email'],
            'from_name' => $validated['smtp_from_name'],
        ];

        $company->forceFill(['extra_settings' => $extra])->save();

        return redirect()->route('settings.index', ['tab' => 'email'])->with('success', 'Email settings saved');
    }

    public function testSmtp(Request $request): JsonResponse
    {
        $this->ensureOwner($request);

        $company = $request->user()->company;
        if (! $company) {
            return response()->json(['success' => false, 'message' => 'Company not found.']);
        }

        $smtp = data_get($company->extra_settings, 'smtp');
        if (empty($smtp['host'])) {
            return response()->json(['success' => false, 'message' => 'SMTP is not configured. Save your settings first.']);
        }

        try {
            $password = Crypt::decryptString((string) $smtp['password']);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Could not read saved SMTP password. Re-save your email settings.']);
        }

        $host = (string) $smtp['host'];
        $port = (int) ($smtp['port'] ?? 587);
        $username = (string) ($smtp['username'] ?? '');
        $fromEmail = (string) ($smtp['from_email'] ?? '');
        $fromName = (string) ($smtp['from_name'] ?? '');

        config([
            'mail.mailers.pulsify_settings_test' => [
                'transport' => 'smtp',
                'host' => $host,
                'port' => $port,
                'username' => $username,
                'password' => $password,
                'timeout' => 30,
                'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
            ],
            'mail.from' => [
                'address' => $fromEmail,
                'name' => $fromName,
            ],
        ]);

        try {
            Mail::purge('pulsify_settings_test');
            Mail::mailer('pulsify_settings_test')->raw(
                'This is a test email from Pulsify. Your SMTP settings are working correctly.',
                function ($message) use ($request) {
                    $message->to($request->user()->email)
                        ->subject('Pulsify SMTP test');
                }
            );
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send: '.$e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Test email sent to '.$request->user()->email,
        ]);
    }

    private function ensureOwner(Request $request): void
    {
        if (strtolower((string) ($request->user()->role ?? '')) !== 'owner') {
            abort(403, 'Only the workspace owner can perform this action.');
        }
    }
}
