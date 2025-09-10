<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\RoutePath;

Route::middleware(['sso.auth', 'guest'])->group(function () {
    Route::get(RoutePath::for('login', '/login'), [Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::class, 'create'])
        ->middleware(['guest:'.config('fortify.guard')])
        ->name('login');
});
Route::middleware('sso.auth', 'anyauth')->group(function () {
    Route::post(RoutePath::for('logout', '/logout'), [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
        ->middleware([config('fortify.auth_middleware', 'auth').':'.config('fortify.guard')])
        ->name('logout');
});
