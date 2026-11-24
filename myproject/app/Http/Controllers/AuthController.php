<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(string $role = 'admin'): View
    {
        abort_unless(in_array($role, ['admin', 'member'], true), 404);

        return view($role === 'member' ? 'auth.member-login' : 'auth.login');
    }

    public function login(Request $request, string $role = 'admin'): RedirectResponse
    {
        abort_unless(in_array($role, ['admin', 'member'], true), 404);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([...$credentials, 'role' => $role], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Those credentials do not match our records.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended($role === 'member' ? route('member.dashboard') : route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
