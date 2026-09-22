<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\SavedLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request, SavedLogin $savedLogin): Response
    {
        return response()->view('auth.login', ['savedAccount' => $savedLogin->user($request)])
            ->header('Cache-Control', 'private, no-store');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, SavedLogin $savedLogin): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if ($request->boolean('remember')) {
            $savedLogin->save($request, $request->user());
        } else {
            $savedLogin->forget($request);
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function continueSaved(Request $request, SavedLogin $savedLogin): RedirectResponse
    {
        $user = $savedLogin->user($request);
        if (! $user) {
            $savedLogin->forget($request);

            return redirect()->route('login')->with('status', 'Please sign in again to save this account on your device.');
        }

        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();
        $savedLogin->save($request, $user);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function forgetSaved(Request $request, SavedLogin $savedLogin): RedirectResponse
    {
        $savedLogin->forget($request);

        return redirect()->route('login')->with('status', 'Account removed from this device.');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
