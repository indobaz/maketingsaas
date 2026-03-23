<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanySetup
{
    /**
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return $next($request);
        }

        if (($user->role ?? null) !== 'owner') {
            return $next($request);
        }

        if (!$user->company_id) {
            return redirect('/onboarding');
        }

        $company = $user->company;
        if (!$company) {
            return redirect('/onboarding');
        }

        $isOnboarding = $request->is('onboarding') || $request->is('onboarding/*');
        if ((bool) ($company->onboarding_completed ?? false) === true) {
            return $next($request);
        }

        if (($company->industry ?? null) === null && !$isOnboarding) {
            return redirect('/onboarding');
        }

        if (!$isOnboarding) {
            return redirect('/onboarding');
        }

        return $next($request);
    }
}

