<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class Turnstile implements ValidationRule
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (config('app.env') === 'local') {
            return;
        }

        $secretKey = config('services.turnstile.secret');

        if (empty($secretKey)) {
            Log::critical('Turnstile secret key missing from configuration.');
            $fail('Error de configuracion del captcha.');
            return;
        }

        if (empty($value)) {
            $fail('El captcha es requerido.');
            return;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->retry(2, 100)
                ->post(self::VERIFY_URL, [
                    'secret' => $secretKey,
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);

            $data = $response->json();

            if ($response->failed() || !($data['success'] ?? false)) {
                Log::warning('Turnstile verification failed', [
                    'ip' => request()->ip(),
                    'response' => $data,
                ]);
                $fail('Verificacion del captcha fallida. Intenta de nuevo.');
            }
        } catch (Throwable $e) {
            Log::error('Turnstile exception', [
                'message' => $e->getMessage(),
            ]);
            $fail('No se pudo verificar el captcha. Intenta mas tarde.');
        }
    }
}
