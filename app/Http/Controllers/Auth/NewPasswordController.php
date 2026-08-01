<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthEventService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View
    {
        if (! $this->hasValidVerifiedSession($request)) {
            return view('auth.forgot-password');
        }

        return view('auth.reset-password', [
            'request' => $request,
        ]);
    }

    public function store(Request $request, AuthEventService $events): RedirectResponse
    {
        if (! $this->hasValidVerifiedSession($request)) {
            return redirect()->route('password.request')->withErrors(['email' => 'La verificacion vencio. Solicita un codigo nuevo.']);
        }

        $request->validate([
            'password' => ['required', 'confirmed', PasswordRule::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        $email = $request->session()->get('password_reset_email');
        $user = User::where('email', $email)->firstOrFail();

        $user->forceFill([
            'password' => Hash::make($request->input('password')),
            'must_change_password' => false,
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        $request->session()->forget(['password_reset_email', 'password_reset_verified_until']);

        $events->record($request, 'password_reset_completed', true, $user, email: $email);

        return redirect()->route('login')->with('status', 'Contrasena actualizada. Ya puedes iniciar sesion.');
    }

    private function hasValidVerifiedSession(Request $request): bool
    {
        $email = $request->session()->get('password_reset_email');
        $until = $request->session()->get('password_reset_verified_until');

        return $email && $until && now()->lessThan($until);
    }
}
