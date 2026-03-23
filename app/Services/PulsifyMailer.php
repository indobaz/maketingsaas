<?php

namespace App\Services;

use App\Models\Company;
use App\Models\EmailTemplate;
use App\Models\PulsifySetting;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PulsifyMailer
{
    public function __construct(
        private ?Company $company = null
    ) {
    }

    public static function forUser(User $user): self
    {
        return new self($user->company ?? null);
    }

    public function send(string $templateKey, string $toEmail, string $toName, array $variables = []): bool
    {
        $template = EmailTemplate::getForCompany($this->company?->id, $templateKey);
        if (! $template) {
            Log::error("PulsifyMailer template not found: {$templateKey}");
            return false;
        }

        $subject = $this->replaceVariables((string) $template->subject, $variables);
        $body = $this->replaceVariables((string) $template->body_html, $variables);

        $mailerName = $this->resolveMailerName();

        try {
            $this->sendHtml($mailerName, $toEmail, $toName, $subject, $body);
            return true;
        } catch (Throwable $e) {
            Log::warning("PulsifyMailer failed on {$templateKey}: ".$e->getMessage());

            if ($mailerName === 'pulsify_company') {
                $fallbackMailer = $this->resolvePlatformOrDefaultMailer();
                try {
                    $this->sendHtml($fallbackMailer, $toEmail, $toName, $subject, $body);
                    return true;
                } catch (Throwable $fallbackEx) {
                    Log::warning("PulsifyMailer fallback failed on {$templateKey}: ".$fallbackEx->getMessage());
                    return false;
                }
            }

            return false;
        }
    }

    private function resolveMailerName(): string
    {
        $companySmtp = $this->companySmtp();
        if ($companySmtp !== null) {
            $this->configureMailer('pulsify_company', $companySmtp);
            return 'pulsify_company';
        }

        return $this->resolvePlatformOrDefaultMailer();
    }

    private function resolvePlatformOrDefaultMailer(): string
    {
        $platformSmtp = PulsifySetting::getSmtp();
        if ($platformSmtp !== null) {
            $this->configureMailer('pulsify_platform', $platformSmtp);
            return 'pulsify_platform';
        }

        return (string) config('mail.default', 'smtp');
    }

    /**
     * @param array<string, mixed> $smtp
     */
    private function configureMailer(string $mailerName, array $smtp): void
    {
        Config::set("mail.mailers.{$mailerName}", [
            'transport' => 'smtp',
            'host' => (string) ($smtp['host'] ?? ''),
            'port' => (int) ($smtp['port'] ?? 587),
            'username' => (string) ($smtp['username'] ?? ''),
            'password' => (string) ($smtp['password'] ?? ''),
            'timeout' => 30,
            'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
        ]);

        Config::set("mail.from_overrides.{$mailerName}", [
            'address' => (string) ($smtp['from_email'] ?? config('mail.from.address')),
            'name' => (string) ($smtp['from_name'] ?? config('mail.from.name')),
        ]);

        Mail::purge($mailerName);
    }

    /**
     * @param array<string, mixed> $variables
     */
    private function replaceVariables(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $safeValue = htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $content = str_replace('{{'.$key.'}}', $safeValue, $content);
            $content = str_replace('{{ '.$key.' }}', $safeValue, $content);
        }

        return $content;
    }

    private function sendHtml(string $mailerName, string $toEmail, string $toName, string $subject, string $body): void
    {
        $fromOverride = config("mail.from_overrides.{$mailerName}");
        Mail::mailer($mailerName)->html($body, function ($message) use ($toEmail, $toName, $subject, $fromOverride) {
            if (is_array($fromOverride) && ! empty($fromOverride['address'])) {
                $message->from((string) $fromOverride['address'], (string) ($fromOverride['name'] ?? 'Pulsify'));
            }
            $message->to($toEmail, $toName)->subject($subject);
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    private function companySmtp(): ?array
    {
        if (! $this->company) {
            return null;
        }

        $smtp = data_get($this->company->extra_settings, 'smtp');
        if (! is_array($smtp) || empty($smtp['host']) || empty($smtp['username']) || empty($smtp['from_email'])) {
            return null;
        }

        $password = (string) ($smtp['password'] ?? '');
        if ($password === '') {
            return null;
        }

        try {
            $password = Crypt::decryptString($password);
        } catch (Throwable) {
            return null;
        }

        return [
            'host' => (string) $smtp['host'],
            'port' => (int) ($smtp['port'] ?? 587),
            'username' => (string) $smtp['username'],
            'password' => $password,
            'from_email' => (string) $smtp['from_email'],
            'from_name' => (string) ($smtp['from_name'] ?? 'Pulsify'),
        ];
    }
}
