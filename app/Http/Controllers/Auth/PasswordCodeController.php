<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\Turnstile;
use App\Services\SecurityCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordCodeController extends Controller
{
    public function show(Request $request): View
    {
        $email = $request->session()->get('password_reset_email', $request->query('email'));

        return view('auth.password-code', [
            'email' => $email,
        ]);
    }

    public function verify(Request $request, SecurityCodeService $codes): RedirectResponse
    {
        $rules = [
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ];

        if (config('app.env') !== 'local') {
            $rules['cf-turnstile-response'] = ['required', new Turnstile];
        }

        $request->validate($rules);

        $record = $codes->verify(
            $request->input('email'),
            'password_reset',
            $request->input('code'),
            false
        );

        $request->session()->put('password_reset_email', $record->email);
        $request->session()->put('password_reset_verified_until', now()->addMinutes(10));

        return redirect()->route('password.reset', ['token' => 'custom']);
    }
}
