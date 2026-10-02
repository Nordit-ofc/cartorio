<?php

use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\ProposalController as AdminProposalController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Site\ProposalController;
use App\Http\Controllers\Site\ProposalTrackingController;
use App\Support\Roles;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Site público (migrado do Vue — mesmas URLs)
|--------------------------------------------------------------------------
| Home, Como funciona e Dúvidas ainda não foram migradas (ADR-005). Por decisão do projeto,
| a raiz mostra "Disponível em breve", com o chat que monta a solicitação (SPEC-006).
*/
Route::view('/', 'site.soon')->name('home');

Route::get('/solicitar-proposta', [ProposalController::class, 'create'])->name('proposals.create');
Route::post('/solicitar-proposta', [ProposalController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('proposals.store');
Route::get('/solicitar-proposta/recebida', [ProposalController::class, 'received'])->name('proposals.received');
Route::get('/consultar-solicitacao', ProposalTrackingController::class)
    ->middleware('throttle:30,1')
    ->name('proposals.tracking');
// Link antigo do Vue (/solicitacao/:publicId) continua funcionando.
Route::get('/solicitacao/{protocol}', fn (string $protocol) => redirect()->route('proposals.tracking', ['protocolo' => $protocol]));

/*
|--------------------------------------------------------------------------
| Área restrita
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function (): void {
    Route::get('/entrar', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/entrar', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'active.account'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', HomeController::class)->name('home');

    Route::middleware('role:'.Roles::SUPER_ADMIN)->group(function (): void {
        Route::get('/propostas', [AdminProposalController::class, 'index'])->name('proposals.index');
        Route::get('/propostas/{proposal}', [AdminProposalController::class, 'show'])->name('proposals.show');
        Route::patch('/propostas/{proposal}/status', [AdminProposalController::class, 'updateStatus'])->name('proposals.status');
    });
});

Route::post('/sair', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');
