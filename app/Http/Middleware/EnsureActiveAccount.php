<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Encerra a sessão já aberta de usuário inativo ou de instituição inativa (não só barra o
 * próximo login). O super_admin não tem instituição e passa direto pela segunda checagem.
 */
class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user->active) {
            return $this->endSession($request, 'Este usuário está inativo.');
        }

        if (! $user->isSuperAdmin() && ! $user->tenant?->active) {
            return $this->endSession($request, 'Esta instituição está inativa. Fale com a nordit.');
        }

        return $next($request);
    }

    private function endSession(Request $request, string $message): Response
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
