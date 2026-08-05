<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function create(): mixed
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $username = Str::lower(trim($request->string('username')->toString()));
        $throttleKey = Str::transliterate($username).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->auditLogger->denied(null, 'auth.login', null, 'Batas percobaan login terlampaui.');

            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik."]);
        }

        $credentials = [
            'username' => $username,
            'password' => $request->string('password')->toString(),
            'is_active' => true,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);
            $this->auditLogger->denied(null, 'auth.login', null, 'Kredensial tidak valid atau akun tidak aktif.');

            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => 'Username atau password tidak sesuai.']);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();
        $this->auditLogger->succeeded($request->user(), 'auth.login');

        if ($request->user()->requiresPasswordChange()) {
            return redirect()->route('dashboard');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user !== null) {
            $this->auditLogger->succeeded($user, 'auth.logout');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda berhasil keluar dari aplikasi.');
    }
}
