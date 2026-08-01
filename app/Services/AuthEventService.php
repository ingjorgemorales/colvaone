<?php

namespace App\Services;

use App\Models\AuthEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthEventService
{
    public function record(Request $request, string $event, bool $successful, ?User $user = null, ?string $email = null, ?string $reason = null): void
    {
        AuthEvent::create([
            'user_id' => $user?->id,
            'email' => $email ?? $user?->email,
            'event' => $event,
            'successful' => $successful,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'route' => optional($request->route())->getName(),
            'request_id' => $request->headers->get('X-Request-Id', (string) Str::uuid()),
            'reason' => $reason,
            'occurred_at' => now(),
        ]);
    }
}
