<?php

namespace App\Http\Controllers;

use App\Models\PulsifySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SuperAdminController extends Controller
{
    public function smtpSettings(Request $request)
    {
        $this->ensureSuperAdmin($request);

        $smtp = PulsifySetting::getSmtp();

        return view('super-admin.smtp', [
            'smtpConfigured' => $smtp !== null,
            'smtpForm' => $smtp ?? [],
        ]);
    }

    public function updateSmtp(Request $request): RedirectResponse
    {
        $this->ensureSuperAdmin($request);

        $validated = $request->validate([
            'smtp_host' => ['required', 'string', 'max:255'],
            'smtp_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => ['required', 'string', 'max:255'],
            'smtp_password' => ['required', 'string', 'max:500'],
            'smtp_from_email' => ['required', 'email', 'max:255'],
            'smtp_from_name' => ['required', 'string', 'max:255'],
        ]);

        PulsifySetting::set('smtp.host', (string) $validated['smtp_host']);
        PulsifySetting::set('smtp.port', (string) $validated['smtp_port']);
        PulsifySetting::set('smtp.username', (string) $validated['smtp_username']);
        PulsifySetting::set('smtp.password', (string) $validated['smtp_password'], true);
        PulsifySetting::set('smtp.from_email', (string) $validated['smtp_from_email']);
        PulsifySetting::set('smtp.from_name', (string) $validated['smtp_from_name']);

        return redirect()->back()->with('success', 'Platform SMTP updated');
    }

    public function testSmtp(Request $request): JsonResponse
    {
        $this->ensureSuperAdmin($request);

        $smtp = PulsifySetting::getSmtp();
        if ($smtp === null) {
            return response()->json([
                'success' => false,
                'message' => 'Platform SMTP is not configured yet.',
            ]);
        }

        Config::set('mail.mailers.pulsify_platform_test', [
            'transport' => 'smtp',
            'host' => (string) $smtp['host'],
            'port' => (int) $smtp['port'],
            'username' => (string) $smtp['username'],
            'password' => (string) $smtp['password'],
            'timeout' => 30,
            'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
        ]);

        try {
            Mail::purge('pulsify_platform_test');
            Mail::mailer('pulsify_platform_test')->html(
                '<p>This is a test email from Pulsify platform SMTP settings.</p>',
                function ($message) use ($request, $smtp) {
                    $message->from((string) $smtp['from_email'], (string) $smtp['from_name']);
                    $message->to((string) $request->user()->email)->subject('Pulsify platform SMTP test');
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

    private function ensureSuperAdmin(Request $request): void
    {
        $allowed = strtolower((string) config('pulsify.super_admin_email'));
        $current = strtolower((string) ($request->user()->email ?? ''));

        if ($allowed === '' || $current !== $allowed) {
            abort(403, 'Only the configured super admin can access this page.');
        }
    }
}
