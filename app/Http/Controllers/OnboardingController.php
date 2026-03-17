<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OnboardingController extends Controller
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

    public function index(): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        $company = $user->company;
        if (!$company) {
            return redirect('/onboarding/step1');
        }

        if ($this->isOnboardingComplete($company)) {
            return redirect('/dashboard');
        }

        if (!$this->hasCompanyDetails($company)) {
            return redirect('/onboarding/step1');
        }

        if (!$this->hasBusinessInfo($company)) {
            return redirect('/onboarding/step2');
        }

        return redirect('/onboarding/step3');
    }

    public function step1(): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        $company = $user->company;
        if ($company && $this->isOnboardingComplete($company)) {
            return redirect('/dashboard');
        }

        return view('onboarding.step1', [
            'company' => $company,
            'step' => 1,
        ]);
    }

    public function saveStep1(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        $company = $user->company;
        if (!$company) {
            return redirect('/onboarding/step1')->with('error', 'Company not found for your account.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:500'],
        ]);

        $logoData = trim((string) $request->input('logo_data', ''));
        $logoUrl = null;
        if ($logoData !== '' && str_starts_with($logoData, 'data:image/')) {
            $logoUrl = $logoData;
        }

        $company->forceFill([
            'name' => trim($validated['name']),
            'website' => $validated['website'] !== null ? trim((string) $validated['website']) : null,
            'logo_url' => $logoUrl ?? $company->logo_url,
        ])->save();

        return redirect('/onboarding/step2');
    }

    public function step2(): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        $company = $user->company;
        if (!$company) {
            return redirect('/onboarding/step1');
        }

        if ($this->isOnboardingComplete($company)) {
            return redirect('/dashboard');
        }

        if (!$this->hasCompanyDetails($company)) {
            return redirect('/onboarding/step1');
        }

        return view('onboarding.step2', [
            'company' => $company,
            'step' => 2,
            'industries' => self::INDUSTRIES,
            'countries' => self::COUNTRIES,
            'timezones' => self::TIMEZONES,
        ]);
    }

    public function saveStep2(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        $company = $user->company;
        if (!$company) {
            return redirect('/onboarding/step1');
        }

        $validated = $request->validate([
            'industry' => ['required', 'string', 'in:'.implode(',', self::INDUSTRIES)],
            'country' => ['required', 'string', 'in:'.implode(',', self::COUNTRIES)],
            'timezone' => ['required', 'string', 'in:'.implode(',', self::TIMEZONES)],
        ]);

        $company->forceFill([
            'industry' => $validated['industry'],
            'country' => $validated['country'],
            'timezone' => $validated['timezone'],
        ])->save();

        return redirect('/onboarding/step3');
    }

    public function step3(): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        $company = $user->company;
        if (!$company) {
            return redirect('/onboarding/step1');
        }

        if ($this->isOnboardingComplete($company)) {
            return redirect('/dashboard');
        }

        if (!$this->hasCompanyDetails($company)) {
            return redirect('/onboarding/step1');
        }

        if (!$this->hasBusinessInfo($company)) {
            return redirect('/onboarding/step2');
        }

        return view('onboarding.step3', [
            'company' => $company,
            'step' => 3,
            'defaultPrimary' => self::DEFAULT_PRIMARY,
        ]);
    }

    public function saveStep3(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        $company = $user->company;
        if (!$company) {
            return redirect('/onboarding/step1');
        }

        $validated = $request->validate([
            'primary_color' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'secondary_color' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
        ]);

        $company->forceFill([
            'primary_color' => strtoupper($validated['primary_color']),
            'secondary_color' => strtoupper($validated['secondary_color']),
        ])->save();

        return redirect('/dashboard')->with('success', 'Welcome to Pulsify! Your workspace is ready.');
    }

    private function hasCompanyDetails(mixed $company): bool
    {
        $name = trim((string) ($company->name ?? ''));
        return $name !== '';
    }

    private function hasBusinessInfo(mixed $company): bool
    {
        return ($company->industry ?? null) !== null
            && ($company->country ?? null) !== null
            && ($company->timezone ?? null) !== null;
    }

    private function isOnboardingComplete(mixed $company): bool
    {
        return ($company->industry ?? null) !== null
            && (string) ($company->primary_color ?? '') !== self::DEFAULT_PRIMARY;
    }
}

