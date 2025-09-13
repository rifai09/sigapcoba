<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsulanController;
use App\Http\Controllers\PersetujuanController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\SSOLoginController;

Route::prefix('login/sso')->group(function () {
    Route::get('/', [SSOLoginController::class, 'redirectToKeycloak'])->name('sso.redirect');
    Route::get('/callback', [SSOLoginController::class, 'handleKeycloakCallback'])->name('sso.callback');
});

// Halaman root → redirect ke dashboard bila login, atau ke login bila guest
Route::middleware(['sso.auth'])->group(function () {
    Route::get('/', function () {
        return auth()->check()
            ? redirect()->route('home')
            : view('auth.login');
    });
});

// Dashboard (role-based)
Route::get('/home', [HomeController::class, 'index'])
    ->name('home')
    ->middleware('sso.auth', 'anyauth');

Route::middleware(['sso.auth', 'anyauth'])->group(function () {

    // === STAF: Form Usulan ===
    Route::get('/usulan/create', [UsulanController::class, 'create'])
        ->middleware('role:staf,admin')
        ->name('usulan.create');

    Route::post('/usulan', [UsulanController::class, 'store'])
        ->middleware('role:staf,admin')
        ->name('usulan.store');

    // === AJAX Lokasi (dropdown berjenjang) ===
    Route::get('/get-lantai', [LocationController::class, 'getLantai'])
        ->middleware('role:staf,admin')
        ->name('locations.lantai');

    Route::get('/get-ruang/{lantai}', [LocationController::class, 'getRuang'])
        ->middleware('role:staf,admin')
        ->name('locations.ruang');

    Route::get('/get-subruang/{ruang}', [LocationController::class, 'getSubRuang'])
        ->middleware('role:staf,admin')
        ->name('locations.subruang');

    // === PERSETUJUAN ===
    Route::get('/persetujuan', [PersetujuanController::class, 'index'])
        ->middleware('role:kepala_unit,katimker,kabid,admin')
        ->name('persetujuan.index');

    // histori logs (modal detail) — kini termasuk 'staf'
    Route::get('/persetujuan/logs/{id}', [PersetujuanController::class, 'getLogs'])
        ->middleware('role:kepala_unit,katimker,kabid,admin,staf')
        ->name('persetujuan.logs');

    // Kepala Unit
    Route::post('/persetujuan/kepala-unit/{id}/setujui', [PersetujuanController::class, 'kepalaUnitSetujui'])
        ->middleware('role:kepala_unit,admin')
        ->name('persetujuan.kepalaunit.approve');

    Route::post('/persetujuan/kepala-unit/{id}/tolak', [PersetujuanController::class, 'kepalaUnitTolak'])
        ->middleware('role:kepala_unit,admin')
        ->name('persetujuan.kepalaunit.reject');

    // Katimker
    Route::post('/persetujuan/katimker/{id}/setujui', [PersetujuanController::class, 'katimkerSetujui'])
        ->middleware('role:katimker,admin')
        ->name('persetujuan.katimker.approve');

    Route::post('/persetujuan/katimker/{id}/tolak', [PersetujuanController::class, 'katimkerTolak'])
        ->middleware('role:katimker,admin')
        ->name('persetujuan.katimker.reject');

    // Kabid (final)
    Route::post('/persetujuan/kabid/{id}/setujui', [PersetujuanController::class, 'kabidSetujui'])
        ->middleware('role:kabid,admin')
        ->name('persetujuan.kabid.approve');

    Route::post('/persetujuan/kabid/{id}/tolak', [PersetujuanController::class, 'kabidTolak'])
        ->middleware('role:kabid,admin')
        ->name('persetujuan.kabid.reject');

    // === NOTIFIKASI ===
    Route::get('/notifications/dropdown', [NotificationController::class, 'dropdown'])
        ->name('notifications.dropdown');

    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])
        ->name('notifications.readAll');
});

require __DIR__ . '/auth.php';

// Route::any('{any}', function (\Illuminate\Http\Request $request) {
//   dd([
//       'uri' => $request->getRequestUri(),
//       'path' => $request->path(),
//       'fullUrl' => $request->fullUrl(),
//   ]);
// })->where('any', '.*');
// Route::get('/test-auth', function () {
//   return [
//       'auth_check' => Auth::check(),
//       'guard' => Auth::getDefaultDriver(),
//       'user' => Auth::user(),
//       'session' => session()->all(),
//   ];
// });
