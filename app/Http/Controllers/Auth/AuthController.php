<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController
{
    public function create()
    {
        return view('pages.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'role' => ['nullable', 'in:client,farmer,manager,admin'],
        ]);

        // Block repeated guesses: 5 tries per minute for the same email and IP address
        $key = Str::lower($data['identifier']) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['identifier' => "Too many login attempts. Try again in {$seconds} seconds."])->onlyInput('identifier');
        }

        $identity = $data['identifier'];
        $credentials = filter_var($identity, FILTER_VALIDATE_EMAIL)
            ? ['email' => Str::lower($identity), 'password' => $data['password'], 'is_active' => true]
            : ['nid_hash' => User::nidHashFor($identity), 'password' => $data['password'], 'is_active' => true];
        $ok = Auth::attempt($credentials, $request->boolean('remember'));

        if (! $ok) {
            RateLimiter::hit($key);
            return back()->withErrors(['identifier' => 'The NID/email or password is incorrect, or the account is not active yet.'])->onlyInput('identifier');
        }

        $user = Auth::user();

        // The role tab chosen on the login page must match the account
        if (! empty($data['role']) && $data['role'] !== $user->role) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->withErrors(['role' => 'This account is not a ' . $data['role'] . ' account. Choose the right tab.'])->onlyInput('identifier');
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended(route($user->homeRoute()))->with('status', 'Welcome back, ' . $user->name . '!');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'You have been logged out.');
    }
}
