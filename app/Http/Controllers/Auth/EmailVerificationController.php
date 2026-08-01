<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthEventService;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(): View
    {
        return view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request, AuthEventService $events): RedirectResponse
    {
        $request->fulfill();
        $events->record($request, 'email_verified', true, $request->user());

        return redirect()->route('dashboard')->with('status', 'Correo verificado correctamente.');
    }

    public function send(Request $request, AuthEventService $events): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->user()->sendEmailVerificationNotification();
        $events->record($request, 'email_verification_sent', true, $request->user());

        return back()->with('status', 'verification-link-sent');
    }
}
