<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Entrada da área restrita. Por enquanto só o sysadmin tem telas (propostas); usuários das
 * instituições veem um aviso até a plataforma do cliente existir.
 */
class HomeController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->user()->isSuperAdmin()) {
            return redirect()->route('admin.proposals.index');
        }

        return view('admin.soon');
    }
}
