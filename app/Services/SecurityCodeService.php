<?php

namespace App\Services;

use App\Models\SecurityCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SecurityCodeService
{
    public function issue(?User $user, string $email, string $purpose, int $minutes): string
    {
        $email = $this->normalizeEmail($email);
        $code = (string) random_int(100000, 999999);

        SecurityCode::where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        SecurityCode::create([
            'user_id' => $user?->id,
            'email' => $email,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes($minutes),
            'created_at' => now(),
        ]);

        return $code;
    }

    public function verify(string $email, string $purpose, string $code, bool $consume = false): SecurityCode
    {
        $record = SecurityCode::where('email', $this->normalizeEmail($email))
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->latest('created_at')
            ->first();

        if (! $record || now()->greaterThan($record->expires_at)) {
            throw ValidationException::withMessages(['code' => 'El codigo es invalido o ya vencio. Solicita uno nuevo.']);
        }

        if ($record->attempts >= 3) {
            $record->update(['consumed_at' => now()]);
            throw ValidationException::withMessages(['code' => 'Superaste los intentos permitidos. Solicita un codigo nuevo.']);
        }

        if (! Hash::check($code, $record->code_hash)) {
            $record->increment('attempts');
            throw ValidationException::withMessages(['code' => 'El codigo ingresado no es correcto.']);
        }

        $record->forceFill([
            'verified_at' => now(),
            'consumed_at' => $consume ? now() : $record->consumed_at,
        ])->save();

        return $record;
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }
}
