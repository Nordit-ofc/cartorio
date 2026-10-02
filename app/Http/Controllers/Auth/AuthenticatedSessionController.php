<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Login por e-mail e senha da área restrita (sysadmin e, depois, usuários das instituições). */
class AuthenticatedSessionController extends Controller
{
    /** Bloqueio temporário após 5 tentativas inválidas em 15 minutos. */
    public const MAX_ATTEMPTS = 5;

    public const DECAY_SECONDS = 15 * 60;

    /** Mensagem única para e-mail inexistente ou senha errada — não revela se a conta existe. */
    public const FAILED_MESSAGE = 'E-mail ou senha inválidos.';

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate(
            ['email' => ['required', 'email'], 'password' => ['required', 'string']],
            [
                'email.required' => 'Informe o e-mail.',
                'email.email' => 'Informe um e-mail válido.',
                'password.required' => 'Informe a senha.',
            ]
        );

        $throttleKey = Str::lower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $minutes = (int) ceil(RateLimiter::availableIn($throttleKey) / 60);
            throw ValidationException::withMessages([
                'email' => "Muitas tentativas de acesso. Tente novamente em {$minutes} minuto(s).",
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);
            throw ValidationException::withMessages(['email' => self::FAILED_MESSAGE]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user();
        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        return redirect()->intended(route('admin.home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
