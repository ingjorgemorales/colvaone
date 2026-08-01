<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DataPolicy;
use App\Models\DataPolicyAcceptance;
use App\Models\User;
use App\Rules\Turnstile;
use App\Services\AuthEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login', [
            'activePolicy' => DataPolicy::query()->where('is_active', true)->latest('published_at')->first(),
        ]);
    }

    public function store(Request $request, AuthEventService $events): RedirectResponse
    {
        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
            'accept_policy' => ['accepted'],
        ];

        if (config('app.env') !== 'local') {
            $rules['cf-turnstile-response'] = ['required', new Turnstile];
        }

        $credentials = $request->validate($rules);

        $key = Str::lower($request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $events->record($request, 'login_rate_limited', false, email: $request->input('email'), reason: 'Too many attempts.');
            throw ValidationException::withMessages([
                'email' => 'Demasiados intentos. Intenta nuevamente en '.RateLimiter::availableIn($key).' segundos.',
            ]);
        }

        $user = User::query()->where('email', $credentials['email'])->first();

        if ($user && ! $user->is_active) {
            RateLimiter::hit($key, 60);
            $events->record($request, 'login_blocked_inactive_user', false, $user, reason: 'Inactive user.');
            throw ValidationException::withMessages([
                'email' => 'El usuario esta inactivo. Contacta al administrador.',
            ]);
        }

        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            $events->record($request, 'login_failed', false, $user, $credentials['email'], 'Invalid credentials.');
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no son validas.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user();
        $user->forceFill(['last_login_at' => now()])->save();

        $policy = DataPolicy::query()->where('is_active', true)->latest('published_at')->first();

        if ($policy) {
            DataPolicyAcceptance::query()->firstOrCreate(
                ['data_policy_id' => $policy->id, 'user_id' => $user->id],
                [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'accepted_at' => now(),
                ],
            );
        }

        $events->record($request, 'login_success', true, $user);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request, AuthEventService $events): RedirectResponse
    {
        $events->record($request, 'logout', true, $request->user());

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
