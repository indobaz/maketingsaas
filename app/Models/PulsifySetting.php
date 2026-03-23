<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class PulsifySetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'is_encrypted',
        'description',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        if (! $setting->is_encrypted) {
            return $setting->value ?? $default;
        }

        try {
            return $setting->value !== null ? Crypt::decryptString($setting->value) : $default;
        } catch (Throwable) {
            return $default;
        }
    }

    public static function set(string $key, string $value, bool $encrypt = false): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $encrypt ? Crypt::encryptString($value) : $value,
                'is_encrypted' => $encrypt,
            ]
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getSmtp(): ?array
    {
        $host = (string) static::get('smtp.host', '');
        $port = (int) static::get('smtp.port', 587);
        $username = (string) static::get('smtp.username', '');
        $password = (string) static::get('smtp.password', '');
        $fromEmail = (string) static::get('smtp.from_email', '');
        $fromName = (string) static::get('smtp.from_name', '');

        if ($host === '' || $username === '' || $fromEmail === '') {
            return null;
        }

        return [
            'host' => $host,
            'port' => $port > 0 ? $port : 587,
            'username' => $username,
            'password' => $password,
            'from_email' => $fromEmail,
            'from_name' => $fromName !== '' ? $fromName : config('mail.from.name', 'Pulsify'),
        ];
    }
}
